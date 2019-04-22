<?php


namespace MyApp\Analyzer;

use MyApp\Helpers\TokenHelper;

class IndividualTokenAnalyzer{
    /** @var TokenHelper $helper */
    private $helper;

    public function __construct(TokenHelper $helper){
        $this->helper = $helper;
    }

    public function containsGlobal($token){
        if (!$token) {
            throw new \Exception("No token provided");
        }
        if ($this->ifContainsGlobal($token)) {
            return Rules::GLOBALS_WARNING;
        }
        return null;
    }

    public function checkIfNotSingleLetterVariable($token){
        if (!$token) {
            throw new \Exception("No token provided");
        }
        if ($token->partOfFor == null && $token->partOfForeach == null && $token->tokenIdentifier == T_VARIABLE && strlen($token->content) <= 2) {
            return Rules::SINGLE_LETTER_VARIABLE_WARNING;
        }
        return null;
    }

    public function containsStatics($token){
        if (!$token) {
            throw new \Exception("No token provided");
        }
        if( $token->tokenIdentifier == T_STATIC  ){
            return Rules::STATIC_WARNING;
        }
        return null;
    }

    public function containsDeprecated($token){
        if (!$token) {
            throw new \Exception("No token provided");
        }
        $message = null;
        if ($token->tokenIdentifier == T_STRING) {
            foreach (Rules::deprecated() as $function => $solution) {
                if ($token->content == $function) {
                    if ($solution) {
                        $message = Rules::METHOD_DEPRECATED_WITH_SUGGEST_WARNING . $solution;
                    } else {
                        $message = Rules::METHOD_DEPRECATED_WARNING;
                    }
                }
            }
        }
        return $message;
    }

    public function ifContainsGlobal($token){
        if (!$token) {
            throw new \Exception("No token provided");
        }
        if ($token->tokenIdentifier == T_VARIABLE && in_array($token->content, Rules::globals())) {
            return true;
        }
        return false;
    }

    public function isNativeElement($token){
        if (    in_array($token->content, Rules::keyNames())
            ||  in_array($token->content, get_defined_functions()['internal'])
            ||  array_key_exists($token->content, Rules::deprecated())) {
            return true;
        }
        return false;
    }

    public function isExcludedFromCheck($analyzedToken){
        if (!$analyzedToken) {
            throw new \Exception("No token provided");
        }
        return $analyzedToken->tokenIdentifier == T_VARIABLE
            && (in_array($analyzedToken->content, Rules::reservedVariableNames())
                || $this->helper->isVariablePartOfStaticCall( $analyzedToken ));
    }


}