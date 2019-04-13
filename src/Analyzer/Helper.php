<?php


namespace MyApp\Analyzer;


class Helper{

    public static $fileMetaData;

    public static function getNextNonWhitespaceToken($tokens, $counter){
        if( $tokens[$counter +1]->tokenIdentifier !== T_WHITESPACE){
            return $tokens[$counter+1];
        }

        do {
            $counter++;
        }while($tokens[$counter]->tokenIdentifier == T_WHITESPACE);
        return $tokens[$counter];
    }
}