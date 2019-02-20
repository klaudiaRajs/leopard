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
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
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
                    $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
                }
            }
        }
        return $message;
    }

    public function containsGlobal($token){
        if ($this->ifContainsGlobal($token)) {
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
            return Rules::GLOBALS_WARNING;
        }
        return null;
    }

    private function ifContainsGlobal($token){
        if($token->tokenIdentifier == T_VARIABLE && in_array($token->content, Rules::globals())){
            return true;
        }
        return false;
    }

    public function containsUnusedVariables($key, $token_, $tokens){
        $message = null;
        if ($token_->tokenIdentifier == T_VARIABLE && in_array($token_->content, Rules::reservedVariableNames())) {
            return $message;
        }
        if ($token_->tokenIdentifier == T_VARIABLE && $this->ifContainsGlobal($token_) == null) {
            $variable = $token_->content;
            foreach ($tokens as $tokenKey => $token) {
                $message = Rules::UNUSED_VARIABLE_WARNING;
                if ($token_->tokenIdentifier == T_VARIABLE && $token->content == $variable && $tokenKey !== $key) {
                    $message = null;
                    break;
                }
                if ($token_->tokenIdentifier == T_VARIABLE && $token->content == '$this') {
                    if ($tokens[$tokenKey + 2]->tokenIdentifier == T_STRING && $tokens[$tokenKey + 2]->content == str_replace('$', '', $variable)) {
                        $message = null;
                        break;
                    }
                }
            }
            if ($message) {
                $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
            }
        }
        return $message;
    }

    private function ifNameExcludedFromCheck($token, $tokens, $i, int $exlusion){
        $exlusionTokenPosition = $i;
        if ($token->tokenIdentifier == T_STRING) {
            for ($j = 2; $j >= 0; $j--) {
                if ($exlusionTokenPosition <= 1) {
                    continue;
                }
                if ($tokens[$exlusionTokenPosition - 1]->tokenIdentifier == $exlusion) {
                    $exlusionTokenPosition--;
                    continue;
                }
                if ($tokens[$exlusionTokenPosition]->tokenIdentifier == $exlusion) {
                    if ($exlusion == T_CONST) {
                        $this->checkIfConstNamingConventionFollowed($tokens, $i);
                    }
                    return true;
                }
                if ($tokens[$exlusionTokenPosition - 1]->tokenIdentifier == T_WHITESPACE) {
                    $exlusionTokenPosition--;
                    continue;
                }
                if ($tokens[$exlusionTokenPosition - 1]->tokenIdentifier == T_NS_SEPARATOR) {
                    return true;
                }
                $exlusionTokenPosition--;
            }
        }
        return false;
    }

    public function checkIfNamingConventionFollowed($token, $tokens, $i){

        if ($this->ifNameExcludedFromCheck($token, $tokens, $i, T_NAMESPACE) || $this->ifNameExcludedFromCheck($token, $tokens, $i, T_CLASS) || $this->ifNameExcludedFromCheck($token, $tokens, $i, T_CONST)) {
            return null;
        }

        if ($this->isNative($token) || $this->ifContainsGlobal($token) || $token->tokenIdentifier == T_CONSTANT_ENCAPSED_STRING || $token->tokenIdentifier == T_DOC_COMMENT || $token->tokenIdentifier == T_COMMENT) {
            return null;
        }
        if (Rules::nameConvention() == 'camelCase') {
            if (self::checkIfCamelCaseConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
            return Rules::CAMEL_CASE_WARNING;
        }
        if (Rules::nameConvention() == 'Pascal') {
            if (self::checkIfPascalConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
            return Rules::PASCAL_CONVENTION_WARNING;
        }

        if (Rules::nameConvention() == 'underscore') {
            if ($this->checkIfUnderscoreConventionFollowed($token)) {
                return null;
            }
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
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

    public function checkIfNotSingleLetterVariable($token){
        if ($token->partOfFor == null && $token->tokenIdentifier == T_VARIABLE && strlen($token->content) <= 2) {
            $this->statKeeper->addProgress($this->fileName, 1, $this->introduceProblems);
            return Rules::SINGLE_LETTER_VARIABLE_WARNING;
        }
        return null;
    }

    private function checkIfConstNamingConventionFollowed($tokens, $i){
        if( strtoupper($tokens[$i]->content) != $tokens[$i]->content ){
            $tokens[$i]->tokenMessage .= Rules::CONST_NAMING_CONVENTION_WARNING;
        }
    }
}
