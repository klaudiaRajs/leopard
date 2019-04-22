<?php


namespace MyApp\Helpers;


use MyApp\Analyzer\Token;

class GeneralHelper{
    public static function getSumOfPercentageResults(array $metrics){
        $sum = 0.0;
        foreach ($metrics as $similarity) {
            $sum += $similarity;
        }
        return $sum < 0 ? 0 : $sum;
    }

    public static function changeFunctionToString(array $function){
        $functionAsString = '';
        /** @var Token $token */
        foreach ($function as $token) {
            $functionAsString .= $token->content . ' ';
        }
        return $functionAsString;
    }

    public static function convertIntToCharCode(int $k){
        if( $k < 0 ){
            $k = 0;
        }
        if( $k > 50 ){
            $k = 50;
        }
        $asciiId = $k + 65; //A-Z
        if ($asciiId > 90) {
            $asciiId += 7; //move to a-Z
        }
        return chr($asciiId);
    }

    public static function getOrderedResult(array $unorderedResult, string $desc, array $finalResult){
        foreach ($unorderedResult as $function => $otherFunctions) {
            foreach ($otherFunctions as $nestedFunction => $similarity) {
                if (!isset($finalResult[$nestedFunction][$function])) {
                    $finalResult[$function][$nestedFunction][$desc] = $similarity;
                }
            }
        }
        return $finalResult;
    }

    public static function getAverage($sum, $numberOfMetrics){
        if( $sum <= 0 || $numberOfMetrics <= 0 ){
            return 0;
        }
        return $sum / $numberOfMetrics;
    }
}