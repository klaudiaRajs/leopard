<?php

namespace MyApp\Analyzer;

use MyApp\Statistics\StatKeeper;

class TokenAnalyser{

    private $statKeeper;
    private $fileName;
    private $introduceProblems;

    public function __construct(StatKeeper $statKeeper, string $fileName, int $introducedProblems){
        $this->statKeeper = $statKeeper;
        $this->fileName = $fileName;
        $this->introduceProblems = $introducedProblems;
    }

    public function containsStatics($token){
        if ($token->tokenIdentifier == T_STATIC) {
            $this->statKeeper->addProgress($this->fileName, 1, Rules::STATIC_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::STATIC_WARNING;
        }
        return null;
    }

    public function containsDeprecated($token){
        $message = null;
        if ($token->tokenIdentifier == T_STRING) {
            foreach (Rules::deprecated() as $function => $solution) {
                if ($token->content == $function) {
                    if ($solution) {
                        $message = Rules::METHOD_DEPRECATED_WITH_SUGGEST_WARNING . $solution;
                    } else {
                        $message = Rules::METHOD_DEPRECATED_WARNING;
                    }
                    $this->statKeeper->addProgress($this->fileName, 1, $message, $token->lineNumber, $this->introduceProblems);
                }
            }
        }
        return $message;
    }

    public function containsGlobal($token){
        if ($this->ifContainsGlobal($token)) {
            $this->statKeeper->addProgress($this->fileName, 1, Rules::GLOBALS_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::GLOBALS_WARNING;
        }
        return null;
    }

    public function containsUnusedVariables($key, $analyzedToken, $tokens){
        $message = null;
        if ($this->isExcludedFromCheck($analyzedToken, $tokens, $key)) {
            return $message;
        }

        if ($analyzedToken->tokenIdentifier == T_VARIABLE && $this->ifContainsGlobal($analyzedToken) == null) {
            foreach ($tokens as $tokenKey => $token) {
                $message = Rules::UNUSED_VARIABLE_WARNING;

                if ($analyzedToken->tokenIdentifier == T_VARIABLE && $token->content == '$this') {
                    if ($this->isCallOfClassField($tokens, $tokenKey, $analyzedToken, $token) && !$this->arePartOfDifferentClasses($analyzedToken, $token)) {
                        $message = null;
                        break;
                    }
                }

                if( $analyzedToken->partOfClass == null && $token->partOfClass == null){
                    if ($analyzedToken->content == $token->content){
                        $message = null;
                        break;
                    }
                }

                if ($token->tokenIdentifier !== T_VARIABLE || $this->arePartOfDifferentClasses($analyzedToken, $token) || !$this->arePartOfTheSameFunctions($analyzedToken, $token)) {
                    continue;
                }

                if ($this->isVariableCall($analyzedToken, $token)) {
                    $message = null;
                    break;
                }
            }

            if ($message) {
                $this->statKeeper->addProgress($this->fileName, 1, $message, $analyzedToken->lineNumber, $this->introduceProblems);
            }
        }
        return $message;
    }

    private function ifContainsGlobal($token){
        if ($token->tokenIdentifier == T_VARIABLE && in_array($token->content, Rules::globals())) {
            return true;
        }
        return false;
    }

    private function isExcludedFromCheck($analyzedToken, $tokens, $key){
        return $analyzedToken->tokenIdentifier == T_VARIABLE && (in_array($analyzedToken->content, Rules::reservedVariableNames()) || $this->isVariablePartOfStaticCall($tokens[$key], $tokens[$key - 1]));
    }

    private function isCallOfClassField($tokens, $i, $analyzedToken, $token){
        $nextToken = Helper::getNextNonWhitespaceToken($tokens, $i);
        if ($nextToken->tokenIdentifier == T_OBJECT_OPERATOR) {
            return $tokens[$i + 2]->tokenIdentifier == T_STRING && $tokens[$i + 2]->content == str_replace('$', '', $analyzedToken->content) && !$this->arePartOfDifferentClasses($analyzedToken, $token);
        }
        return false;
    }

    private function isVariableCall($analyzedToken, $token){
        return $token->tokenIdentifier == T_VARIABLE && $token->content == $analyzedToken->content && $token->tokenHash !== $analyzedToken->tokenHash;
    }

    private function arePartOfTheSameFunctions($analyzedToken, $tokenComparedTo){
        return $tokenComparedTo->partOfFunction !== null && $analyzedToken->partOfFunction !== null && $tokenComparedTo->partOfFunction == $analyzedToken->partOfFunction;
    }

    private function arePartOfDifferentClasses($analyzedToken, $tokenComparedTo){
        return $analyzedToken->partOfClass !== null && $tokenComparedTo->partOfClass !== $analyzedToken->partOfClass;
    }

    private function ifNameExcludedFromCheck($token, $tokens, $i, int $exclusion){
        $exclusionTokenPosition = $i;
        if ($token->tokenIdentifier == T_STRING) {
            for ($j = 2; $j >= 0; $j--) {
                if ($exclusionTokenPosition <= 1) {
                    continue;
                }
                if ($tokens[$exclusionTokenPosition - 1]->tokenIdentifier == $exclusion) {
                    $exclusionTokenPosition--;
                    continue;
                }
                if ($tokens[$exclusionTokenPosition]->tokenIdentifier == $exclusion) {
                    if ($exclusion == T_CONST) {
                        $this->checkIfConstNamingConventionFollowed($tokens, $i);
                    }
                    if ($exclusion == T_FUNCTION) {
                        $this->checkIfFunctionFollowsNamingConvention($tokens, $i);
                    }
                    return true;
                }
                if ($tokens[$exclusionTokenPosition - 1]->tokenIdentifier == T_WHITESPACE) {
                    $exclusionTokenPosition--;
                    continue;
                }
                if ($tokens[$exclusionTokenPosition - 1]->tokenIdentifier == T_NS_SEPARATOR) {
                    return true;
                }
                $exclusionTokenPosition--;
            }
        }
        return false;
    }

    public function checkIfNamingConventionFollowed($token, $tokens, $i){

        if ($this->ifNameExcludedFromCheck($token, $tokens, $i, T_CLASS)
            || $this->ifNameExcludedFromCheck($token, $tokens, $i, T_CONST)
            || $this->ifNameExcludedFromCheck($token, $tokens, $i, T_FUNCTION)) {
            return null;
        }

        if ($this->isType($token, $tokens, $i)) {
            if (!$this->isPrimitiveType($token) && $this->checkIfPascalConventionFollowed($token)) {
                return null;
            }
            if( $this->isPrimitiveType($token) && $this->checkIfCamelCaseConventionFollowed($token) ){
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, Rules::PASCAL_CONVENTION_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::PASCAL_CONVENTION_WARNING;
        }

        if (isset($tokens[$i + 1]) && $this->resembleStaticObjectCall($tokens[$i], $tokens[$i + 1])) {
            return null;
        }

        if ($this->isObjectCall($tokens, $token, $i)) {
            return null;
        }

        if ($this->isNative($token) || $this->ifContainsGlobal($token) || $token->tokenIdentifier == T_CONSTANT_ENCAPSED_STRING || $token->tokenIdentifier == T_DOC_COMMENT || $token->tokenIdentifier == T_COMMENT) {
            return null;
        }
        if (Rules::nameConvention() == 'camelCase') {
            if ($this->checkIfCamelCaseConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, Rules::CAMEL_CASE_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::CAMEL_CASE_WARNING;
        }
        if (Rules::nameConvention() == 'Pascal') {
            if (self::checkIfPascalConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, Rules::PASCAL_CONVENTION_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::PASCAL_CONVENTION_WARNING;
        }

        if (Rules::nameConvention() == 'underscore') {
            if ($this->checkIfUnderscoreConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, Rules::UNDERSCORE_CONVENTION_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::UNDERSCORE_CONVENTION_WARNING;
        }
        return null;
    }

    public function isNative($token){
        if (in_array($token->content, Rules::keyNames())) {
            return true;
        }
        if (in_array($token->content, get_defined_functions()['internal'])) {
            return true;
        }
        if (array_key_exists($token->content, Rules::deprecated())) {
            return true;
        }
        return false;
    }

    private function checkIfPascalConventionFollowed($token){
        if (in_array($token->tokenName, Rules::TOKENS_CONTAINING_NAMING)) {
            if ($token->tokenIdentifier == T_VARIABLE) {
                $firstCharacter = mb_substr($token->content, 1, 1, "UTF-8");
                $isUpper = ctype_upper($firstCharacter);
            } else {
                $firstCharacter = mb_substr($token->content, 0, 1, "UTF-8");
                $isUpper = ctype_upper($firstCharacter);
            }
            return ((strpos($token->content, '_') === false) && $isUpper);
        }
        return true;
    }

    private function checkIfUnderscoreConventionFollowed($token){
        if (in_array($token->tokenName, Rules::TOKENS_CONTAINING_NAMING)) {
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
        if (in_array($token->tokenName, Rules::TOKENS_CONTAINING_NAMING)) {
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

    public function checkIfNotSingleLetterVariable($token){
        if ($token->partOfFor == null && $token->tokenIdentifier == T_VARIABLE && strlen($token->content) <= 2) {
            $this->statKeeper->addProgress($this->fileName, 1, Rules::SINGLE_LETTER_VARIABLE_WARNING, $token->lineNumber, $this->introduceProblems);
            return Rules::SINGLE_LETTER_VARIABLE_WARNING;
        }
        return null;
    }

    private function checkIfConstNamingConventionFollowed($tokens, $i){
        if (strtoupper($tokens[$i]->content) != $tokens[$i]->content) {
            $this->statKeeper->addProgress($this->fileName, 1, Rules::CONST_NAMING_CONVENTION_WARNING, $tokens[$i]->lineNumber, $this->introduceProblems);
            $tokens[$i]->tokenMessage .= Rules::CONST_NAMING_CONVENTION_WARNING;
        }
    }

    private function checkIfFunctionFollowsNamingConvention($tokens, $i){
        if (in_array($tokens[$i]->content, Rules::keyNames())) {
            return;
        }
        if (!$this->checkIfCamelCaseConventionFollowed($tokens[$i])) {
            $this->statKeeper->addProgress($this->fileName, 1, Rules::METHOD_NAMING_CONVENTION, $tokens[$i]->lineNumber, $this->introduceProblems);
            $tokens[$i]->tokenMessage .= Rules::METHOD_NAMING_CONVENTION;
        }
    }

    private function resembleStaticObjectCall(Token $stringToken, Token $nextToken){
        return $stringToken->tokenIdentifier == T_STRING && $nextToken->tokenIdentifier == T_DOUBLE_COLON ? true : false;
    }

    private function isObjectCall($tokens, Token $token, $i){
        if ($token->tokenIdentifier == T_STRING) {
            $counter = $i;
            do {
                $counter++;
            }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);

            $result = [
                Token::BRACKET_OPEN => 0,
                T_NEW => 0,
            ];

            if ($tokens[$counter]->tokenIdentifier == Token::BRACKET_OPEN) {
                $result[Token::BRACKET_OPEN] = 1;
            }

            $counter = $i;
            do {
                $counter--;
            }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);

            if ($tokens[$counter]->tokenIdentifier == T_NEW) {
                $result[T_NEW] = 1;
            }

            if ($result[Token::BRACKET_OPEN] == 1 && $result[T_NEW] == 1) {
                return true;
            }
        }
        return false;
    }

    private function isVariablePartOfStaticCall(Token $checkedToken, Token $previousToken){
        if ($checkedToken->tokenIdentifier == T_VARIABLE && $previousToken->tokenIdentifier == T_DOUBLE_COLON) {
            return true;
        }
        return false;
    }

    private function isType(Token $token, $tokens, $i){
        if ($token->tokenIdentifier == T_STRING) {
            $previousNonWhiteSpaceToken = $this->getPreviousNonWhitespaceToken($tokens, $i);

            $nextNonWhiteSpaceToken = Helper::getNextNonWhitespaceToken($tokens, $i);

            $isVariableType = $this->isTypeOfVariableInParameterList($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);
            $isNewObjectInitialization = $this->isNewObjectInitialization($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);
            $isNamespace = $this->isNamespaceImport($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);


            if ($isVariableType || $isNewObjectInitialization || $isNamespace) {
                return true;
            }
        }
        return false;
    }


    public function getPreviousNonWhitespaceToken($tokens, $counter){

        if ($tokens[$counter - 1]->tokenIdentifier !== T_WHITESPACE) {
            return $tokens[$counter - 1];
        }

        do {
            $counter--;
        }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $tokens[$counter];
    }

    private function isNewObjectInitialization($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        if ($previousNonWhiteSpaceToken->tokenIdentifier == T_NEW && $nextNonWhiteSpaceToken->tokenIdentifier == Token::BRACKET_OPEN) {
            return true;
        }
        return false;
    }

    private function isTypeOfVariableInParameterList($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        if (($previousNonWhiteSpaceToken->tokenIdentifier == Token::BRACKET_OPEN || $previousNonWhiteSpaceToken->tokenIdentifier == Token::COMMA) && $nextNonWhiteSpaceToken->tokenIdentifier == T_VARIABLE) {
            return true;
        }
        return false;
    }

    private function isNamespaceImport($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        if (($previousNonWhiteSpaceToken->tokenIdentifier == T_USE || $previousNonWhiteSpaceToken->tokenIdentifier == T_NAMESPACE)
            && ($nextNonWhiteSpaceToken->tokenIdentifier == T_NS_SEPARATOR || $nextNonWhiteSpaceToken->tokenIdentifier == Token::SEMICOLON)) {
            return true;
        }
        return false;
    }

    public function isPrimitiveType($token){
        return in_array($token->content, Rules::PRIMITIVE_TYPES);
    }
}
