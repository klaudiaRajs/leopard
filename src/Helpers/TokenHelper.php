<?php


namespace MyApp\Helpers;


use MyApp\Analyzer\Token;

class TokenHelper{

    public static $fileMetaData;
    private $tokens;

    public function __construct($tokens){
        $this->tokens = $tokens;
    }

    public static function getNextNonWhitespaceTokenStaticAccess($counter, $tokens){
        if (!isset($tokens[$counter + 1])) {
            return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
        }

        if ($tokens[$counter + 1]->tokenIdentifier !== T_WHITESPACE) {
            return $tokens[$counter + 1];
        }

        do {
            if (!isset($tokens[$counter + 1])) {
                return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
            }
            $counter++;
        }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $tokens[$counter];
    }

    public static function getPreviousNonWhitespaceTokenStaticAccess($counter, $tokens){

        if (!isset($tokens[$counter - 1])) {
            return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
        }

        if ($tokens[$counter - 1]->tokenIdentifier !== T_WHITESPACE) {
            return $tokens[$counter - 1];
        }

        do {
            $counter--;
        }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $tokens[$counter];
    }

    public function getNextNonWhitespaceToken($counter){
        if (!isset($this->tokens[$counter + 1])) {
            return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
        }

        if ($this->tokens[$counter + 1]->tokenIdentifier !== T_WHITESPACE) {
            return $this->tokens[$counter + 1];
        }

        do {
            if (!isset($this->tokens[$counter + 1])) {
                return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
            }
            $counter++;
        }while($this->tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $this->tokens[$counter];
    }

    public function getPreviousNonWhitespaceToken($counter){

        if (!isset($this->tokens[$counter - 1])) {
            return new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1);
        }

        if ($this->tokens[$counter - 1]->tokenIdentifier !== T_WHITESPACE) {
            return $this->tokens[$counter - 1];
        }

        do {
            $counter--;
        }while($this->tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $this->tokens[$counter];
    }

    public function isVariablePartOfStaticCall(Token $analyzedToken){
        $previousToken = $this->tokens[$analyzedToken->tokenHash - 1];
        if ($analyzedToken->tokenIdentifier == T_VARIABLE && $previousToken->tokenIdentifier == T_DOUBLE_COLON) {
            return true;
        }
        return false;
    }
}