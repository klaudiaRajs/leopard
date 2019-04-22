<?php

namespace MyApp\Analyzer;

use MyApp\Helpers\TokenHelper;

class TokenInContextAnalyzer{
    private $individualTokenAnalyzer;
    /** @var TokenHelper $helper */
    private $helper;
    private $tokens;

    public function __construct(IndividualTokenAnalyzer $analyzer, $tokens, TokenHelper $helper){
        $this->individualTokenAnalyzer = $analyzer;
        $this->helper = $helper;
        $this->tokens = $tokens;
    }

    public function isType(Token $token){
        if ($token->tokenIdentifier == T_STRING) {
            $previousNonWhiteSpaceToken = $this->helper->getPreviousNonWhitespaceToken($token->tokenHash);

            $nextNonWhiteSpaceToken = $this->helper->getNextNonWhitespaceToken($token->tokenHash);

            $isVariableType = $this->isTypeOfVariableInParameterList($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);
            $isNewObjectInitialization = $this->isNewObjectInitialization($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);
            $isClassName = $this->isClassName($previousNonWhiteSpaceToken);
            $isNamespace = $this->isNamespaceImport($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken);

            return ($isVariableType || $isNewObjectInitialization || $isNamespace || $isClassName);
        }
        return false;
    }

    public function isPublicFunction(int $tokenIndexOfFunctionName){
        if ($this->tokens[$tokenIndexOfFunctionName]->tokenIdentifier == T_STRING) {
            $previousNonWhiteSpaceToken = $this->helper->getPreviousNonWhitespaceToken($tokenIndexOfFunctionName);

            if ($previousNonWhiteSpaceToken->tokenIdentifier == T_FUNCTION) {
                $accessModifierToken = $this->helper->getPreviousNonWhitespaceToken($previousNonWhiteSpaceToken->tokenHash);
                if( $accessModifierToken->tokenIdentifier == T_STATIC){
                    $accessModifierToken = $this->helper->getPreviousNonWhitespaceToken($accessModifierToken->tokenHash);
                }
                if ($accessModifierToken->tokenIdentifier == T_PUBLIC) {
                    return true;
                }
            }
        }
        return false;
    }

    public function isUnusedVariable(Token $analyzedToken){
        $message = null;
        if ($this->individualTokenAnalyzer->isExcludedFromCheck($analyzedToken)) {
            return $message;
        }

        if ($analyzedToken->tokenIdentifier == T_VARIABLE && $this->individualTokenAnalyzer->ifContainsGlobal($analyzedToken) == null) {
            foreach ($this->tokens as $tokenKey => $token) {
                $message = Rules::UNUSED_VARIABLE_WARNING;
                if ($analyzedToken->tokenIdentifier == T_VARIABLE && $token->content == '$this') {
                    if ($this->isCallOfClassField($analyzedToken, $token) && !$this->arePartOfDifferentClasses($analyzedToken, $token)) {
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
        }
        return $message;
    }

    private function isCallOfClassField($analyzedToken, $token){
        $nextToken = $this->helper->getNextNonWhitespaceToken($token->tokenHash);
        if ($nextToken->tokenIdentifier == T_OBJECT_OPERATOR) {
            return $this->isNextToNextTokenString($token) && $this->isNextToNextTokenEqualTo($analyzedToken, $token) && !$this->arePartOfDifferentClasses($analyzedToken, $token);
        }
        return false;
    }

    private function isNextToNextTokenEqualTo($analyzedToken, $token){
        return $this->tokens[$token->tokenHash + 2]->content == str_replace('$', '', $analyzedToken->content);
    }

    private function isNextToNextTokenString($token){
        return $this->tokens[$token->tokenHash + 2]->tokenIdentifier == T_STRING;
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

    private function isNewObjectInitialization($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        if( $previousNonWhiteSpaceToken->tokenIdentifier == T_NS_SEPARATOR ){
            $previousNonWhiteSpaceToken = TokenHelper::getPreviousNonWhitespaceTokenStaticAccess($previousNonWhiteSpaceToken->tokenHash, $this->tokens);
        }
        if ($previousNonWhiteSpaceToken->tokenIdentifier == T_NEW && $nextNonWhiteSpaceToken->tokenIdentifier == Token::BRACKET_OPEN) {
            return true;
        }
        return false;
    }

    private function isTypeOfVariableInParameterList($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        if( $previousNonWhiteSpaceToken->tokenIdentifier == Token::BRACKET_OPEN &&  $nextNonWhiteSpaceToken->tokenIdentifier == T_VARIABLE ){
            return true;
        }

        if( $previousNonWhiteSpaceToken->tokenIdentifier == Token::COMMA && $nextNonWhiteSpaceToken->tokenIdentifier == T_VARIABLE  ){
            return true;
        }
        return false;
    }

    public function isNamespaceImport($previousNonWhiteSpaceToken, $nextNonWhiteSpaceToken){
        $previousTokensIndicatingNamespace = [T_USE, T_NAMESPACE, T_NS_SEPARATOR];
        $nextTokensIndicatingNamespace = [T_NS_SEPARATOR, Token::SEMICOLON];

        if (in_array($previousNonWhiteSpaceToken->tokenIdentifier, $previousTokensIndicatingNamespace)
            && in_array($nextNonWhiteSpaceToken->tokenIdentifier, $nextTokensIndicatingNamespace)) {
            return true;
        }
        return false;
    }

    private function isClassName(Token $previousNonWhiteSpaceToken){
        if( $previousNonWhiteSpaceToken->tokenIdentifier == T_CLASS || $previousNonWhiteSpaceToken->tokenIdentifier == T_EXTENDS) {
            return true;
        }
        return false;
    }

}