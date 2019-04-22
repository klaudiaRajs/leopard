<?php


namespace MyApp\Analyzer;

use MyApp\Helpers\TokenHelper;
use MyApp\Statistics\StatKeeper;

class NameConventionAnalyzer{

    private $individualTokenAnalyzer;
    private $tokenInContextAnalyzer;
    private $tokenHelper;
    private $tokens;

    public function __construct(IndividualTokenAnalyzer $analyzer, TokenInContextAnalyzer $contextAnalyzer, $tokens){
        $this->individualTokenAnalyzer = $analyzer;
        $this->tokenInContextAnalyzer = $contextAnalyzer;
        $this->tokenHelper = new TokenHelper($tokens);
        $this->tokens = $tokens;
    }


    public function isPrimitiveType($token){
        return in_array($token->content, Rules::PRIMITIVE_TYPES);
    }

    public function checkIfNamingConventionFollowed(Token $token){
        if ($this->ifNameExcludedFromCheck($token, $this->tokens, T_CLASS)
            || $this->ifNameExcludedFromCheck($token, $this->tokens, T_CONST)
            || $this->ifNameExcludedFromCheck($token, $this->tokens, T_FUNCTION)) {
            return null;
        }

        if ($this->individualTokenAnalyzer->isNativeElement($token)
            || $this->individualTokenAnalyzer->ifContainsGlobal($token)
            || $token->tokenIdentifier == T_CONSTANT_ENCAPSED_STRING
            || $token->tokenIdentifier == T_DOC_COMMENT
            || $token->tokenIdentifier == T_COMMENT) {
            return null;
        }

        if ($this->tokenInContextAnalyzer->isType($token)) {
            if (!$this->isPrimitiveType($token)) {
                return $this->checkIfPascalConventionFollowed($token) ? null : Rules::PASCAL_CONVENTION_WARNING;
            }
            if ($this->isPrimitiveType($token)) {
                return !$this->checkIfCamelCaseConventionFollowed($token) ? Rules::CAMEL_CASE_WARNING : null;
            }
        }
        if (isset($this->tokens[$token->tokenHash + 1]) && $this->resembleStaticObjectCall($token, TokenHelper::getNextNonWhitespaceTokenStaticAccess($token->tokenHash, $this->tokens), TokenHelper::getPreviousNonWhitespaceTokenStaticAccess($token->tokenHash, $this->tokens))) {
            if( $this->resembleConstant($token) ){
                return $this->checkIfConstNamingConventionFollowed($token);
            }
            return $this->checkIfPascalConventionFollowed($token) ? null : Rules::PASCAL_CONVENTION_WARNING;
        }

        if (Rules::nameConvention() == 'camelCase') {
            if ($this->checkIfCamelCaseConventionFollowed($token)) {
                return null;
            }
            return Rules::CAMEL_CASE_WARNING;
        }
        if (Rules::nameConvention() == 'Pascal') {
            if (self::checkIfPascalConventionFollowed($token)) {
                return null;
            }
            return Rules::PASCAL_CONVENTION_WARNING;
        }

        if (Rules::nameConvention() == 'underscore') {
            if ($this->checkIfUnderscoreConventionFollowed($token)) {
                return null;
            }
            return Rules::UNDERSCORE_CONVENTION_WARNING;
        }
        return null;
    }

    private function checkIfPascalConventionFollowed($token){
        if (in_array($token->tokenIdentifier, Rules::TOKENS_CONTAINING_NAMING)) {
            if ($token->tokenIdentifier == T_VARIABLE) {
                $firstCharacter = mb_substr($token->content, 1, 1, "UTF-8");
                $isUpper = ctype_upper($firstCharacter);
            } else {
                $firstCharacter = mb_substr($token->content, 0, 1, "UTF-8");
                $isUpper = ctype_upper($firstCharacter);
            }
            return ((strpos($token->content, '_') === false) && $isUpper);
        }
        //If the variable does not contain name that should be assessed, we don't return error.
        return true;
    }

    private function checkIfUnderscoreConventionFollowed($token){
        if (in_array($token->tokenIdentifier, Rules::TOKENS_CONTAINING_NAMING)) {
            $word = str_replace('$', '', $token->content);
            $parts = explode("_", $word);
            foreach ($parts as $part) {
                $stringArr = str_split($part);
                foreach ($stringArr as $char) {
                    if (ctype_alpha($char) && !ctype_lower($char)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }


    private function checkIfCamelCaseConventionFollowed($token){
        if (in_array($token->tokenIdentifier, Rules::TOKENS_CONTAINING_NAMING)) {
            $word = str_replace('$', '', $token->content);
            if ($this->resembleConstant($token)) {
                return true;
            }
            if (strpos($token->content, '_') !== false) {
                return false;
            }
            $firstCharacter = mb_substr($word, 0, 1, "UTF-8");

            if (!ctype_lower($firstCharacter)) {
                return false;
            }
        }
        return true;
    }

    private function resembleConstant($token){
        return mb_strtoupper($token->content) == $token->content ? true : false;
    }


    private function resembleStaticObjectCall(Token $stringToken, Token $nextToken, Token $previousToken){
        if( $stringToken->tokenIdentifier == T_STRING  ){
            if( $nextToken->tokenIdentifier == T_DOUBLE_COLON || $previousToken->tokenIdentifier == T_DOUBLE_COLON ){
                return true;
            }
        }
        return false;
    }

    private function ifNameExcludedFromCheck($token, $tokens, int $tokenIdentifier){
        $tokenHelper = new TokenHelper($this->tokens);
        $previousToken = $tokenHelper->getPreviousNonWhitespaceToken($token->tokenHash);
        $nextToken = $tokenHelper->getNextNonWhitespaceToken($token->tokenHash);

        if ($this->tokenInContextAnalyzer->isNamespaceImport($previousToken, $nextToken)) {
            $this->checkIfPascalConventionFollowed($token);
            return true;
        }

        if ($tokenIdentifier == $previousToken->tokenIdentifier) {
            if ($tokenIdentifier == T_CLASS) {
                return $this->checkIfPascalConventionFollowed($token);
            }
            if ($tokenIdentifier == T_FUNCTION) {
                $this->checkIfFunctionFollowsNamingConvention($tokens, $token->tokenHash);
                return true;
            }
            if ($tokenIdentifier == T_CONST) {
                $this->checkIfConstNamingConventionFollowed($token);
                return true;
            }
        }
        return false;
    }

    private function checkIfConstNamingConventionFollowed($token){
        if (strtoupper($token->content) != $token->content) {
            StatKeeper::addProgress(1, Rules::CONST_NAMING_CONVENTION_WARNING, $token->lineNumber);
            return Rules::CONST_NAMING_CONVENTION_WARNING;
        }
        return null;
    }

    private function checkIfFunctionFollowsNamingConvention($tokens, $i){
        if (in_array($tokens[$i]->content, Rules::keyNames())) {
            return;
        }
        if (!$this->checkIfCamelCaseConventionFollowed($tokens[$i])) {
            StatKeeper::addProgress(1, Rules::METHOD_NAMING_CONVENTION, $tokens[$i]->lineNumber);
            $tokens[$i]->tokenMessage .= Rules::METHOD_NAMING_CONVENTION;
        }
    }
}