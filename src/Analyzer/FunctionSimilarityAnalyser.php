<?php


namespace MyApp\Analyzer;

use MyApp\Helpers\GeneralHelper;
use MyApp\Statistics\StatKeeper;

class FunctionSimilarityAnalyser{

    public function checkFunctionStringSimilarity(array $tokens){
        $tokensPerFunction = $this->divideTokensIntoFunctions($tokens);
        if( count($tokensPerFunction) < 2 ){
            StatKeeper::saveSimilarity("This file does not have enough functions for this comparison", StatKeeper::$currentFile);
            return $tokens;
        }

        $textSimilaritiesPerFunction = $this->analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction);
        $returnVariablesSimilarity = $this->analyzeGroupOfTokenSimilarity($this->getReturnTokensPerFunction($tokensPerFunction));
        $abstractedTextSimilarity = $this->analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction, true); //abstract variables

        $finalResult = [];
        $finalResult = GeneralHelper::getOrderedResult($textSimilaritiesPerFunction, 'pureTextSimilarity', $finalResult);
        $finalResult = GeneralHelper::getOrderedResult($returnVariablesSimilarity, 'returns', $finalResult);
        $finalResult = GeneralHelper::getOrderedResult($abstractedTextSimilarity, 'abstractedText', $finalResult);

        foreach ($finalResult as $function => $comparedFunction) {
            foreach ($comparedFunction as $nestedFunction => $metrics) {
                $sum = GeneralHelper::getSumOfPercentageResults($metrics);
                $average = round(GeneralHelper::getAverage($sum, count($metrics)), 2);
                $finalResult[$function][$nestedFunction]['average'] = $average;
                if ($average > Rules::SIMILARITY_THRESHOLD) {
                    $tokens = $this->markTokensAsSimilar($tokens, $tokensPerFunction, $function, $nestedFunction, $average);
                }
            }
        }

        StatKeeper::saveSimilarity($finalResult);
        return $tokens;
    }


    private function getReturnTokensPerFunction($tokensPerFunction){
        $returnedTokens = [];

        foreach ($tokensPerFunction as $function => $tokens) {
            for ($i = 0; $i < count($tokens); $i++) {
                if ($tokens[$i]->tokenIdentifier == T_RETURN) {
                    for ($j = $i + 1; $j < count($tokens); $j++) {
                        if ($tokens[$j]->tokenIdentifier == Token::SEMICOLON) {
                            break;
                        }
                        if ($tokens[$j]->tokenIdentifier == T_WHITESPACE) {
                            continue;
                        }
                        if ($tokens[$j]->tokenIdentifier == T_VARIABLE || $tokens[$j]->tokenIdentifier == T_STRING || $tokens[$j]->tokenIdentifier == T_CONST)
                            $returnedTokens[$function][] = $tokens[$j];
                    }
                }
            }
        }

        return $returnedTokens;
    }

    private function analyzeGroupOfTokenSimilarity(array $paramsPerFunctionAsListOfTokens){
        $paramsPerFunctionAsArray = [];
        foreach ($paramsPerFunctionAsListOfTokens as $function => $params) {
            $paramsPerFunctionAsArray[$function] = array_map(function($v){
                return $v->content;
            }, $params);
        }
        $functionsToCompared = array_keys($paramsPerFunctionAsArray);
        $count = count($functionsToCompared);

        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $compareFrom = $functionsToCompared[$i];
            for ($j = $i + 1; $j < $count; $j++) {
                $compareTo = $functionsToCompared[$j];
                $result[$compareFrom][$compareTo] = $this->getParamsSimilarity($paramsPerFunctionAsArray[$compareFrom], $paramsPerFunctionAsArray[$compareTo]);
            }
        }
        return $result;
    }

    private function getParamsSimilarity($paramsA, $paramsB){
        $paramsWithExactlyTheSameName = array_values(array_intersect($paramsA, $paramsB));
        $theSameParamsCount = count($paramsWithExactlyTheSameName);

        $theSameParams = [];
        foreach ($paramsWithExactlyTheSameName as $k => $paramName) {
            $theSameParams[] = GeneralHelper::convertIntToCharCode($k);
        }

        $newParamsA = array_values($theSameParams);
        $newParamsB = array_values($theSameParams);
        $idx = 0;
        foreach ($paramsA as $name) {
            if (in_array($name, $paramsWithExactlyTheSameName)) {
                continue;
            }
            $newParamsA[] = GeneralHelper::convertIntToCharCode($theSameParamsCount + $idx++);
        }
        $paramsACount = count($newParamsA);
        $idx = 0;
        foreach ($paramsB as $name) {
            if (in_array($name, $paramsWithExactlyTheSameName)) {
                continue;
            }
            $newParamsB[] = GeneralHelper::convertIntToCharCode($paramsACount + $idx++);
        }

        similar_text(implode('', $newParamsA), implode('', $newParamsB), $percent);
        return $percent;
    }

    private function analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction, $abstracted = false){
        $result = [];

        foreach ($tokensPerFunction as $firstFunction => $function) {
            $tokensToOperateOn = $abstracted ? GeneralHelper::changeFunctionToString($this->getModifiedTokens($function)) : GeneralHelper::changeFunctionToString($function);
            foreach ($tokensPerFunction as $functionName => $function_) {
                if ($firstFunction == $functionName) {
                    continue;
                }
                $functionAsString = $abstracted ? GeneralHelper::changeFunctionToString($this->getModifiedTokens($function_)) : GeneralHelper::changeFunctionToString($function_);
                similar_text($tokensToOperateOn, $functionAsString, $similarity);
                $result[$firstFunction][$functionName] = $similarity;
            }
        }

        return $result;
    }

    private function divideTokensIntoFunctions($tokens){
        $currentFunction = '';
        $tokensPerFunction = [];

        /** @var  Token $token */
        foreach ($tokens as $token) {
            if ($token->partOfFunction == null) {
                continue;
            }
            if (empty($currentFunction) || $currentFunction !== $token->partOfFunction) {
                $currentFunction = $token->partOfFunction;
            }

            $tokensPerFunction[$currentFunction][] = $token;
        }

        return $tokensPerFunction;
    }

    private function getModifiedTokens(array $tokensPerFunction){
        $temp = $tokensPerFunction;
        $modifiedTokens = [];

        /** @var Token $token */
        foreach ($temp as $token) {
            $newToken = clone($token);
            if ($token->tokenIdentifier == T_VARIABLE) {
                $newToken->content = 'variable';
            }
            $modifiedTokens[] = $newToken;
        }

        return $modifiedTokens;
    }

    private function markTokensAsSimilar(array $originTokens, array $tokensPerFunction, $function, $nestedFunction, $average){
        if( $average >= Rules::SIMILARITY_THRESHOLD ){
            $firstFunctionTokenId = $tokensPerFunction[$function][0]->tokenHash;
            $secondFunctionTokenId = $tokensPerFunction[$nestedFunction][0]->tokenHash;
            $originTokens[$firstFunctionTokenId]->tokenMessage .= Rules::SIMILAR_CHUNK_OF_CODE_WARNING . $nestedFunction . ' by: ' . $average . "\n";
            $originTokens[$secondFunctionTokenId]->tokenMessage .= Rules::SIMILAR_CHUNK_OF_CODE_WARNING . $function . ' by: ' . $average . "\n";
            StatKeeper::addProgress(1, Rules::SIMILAR_CHUNK_OF_CODE_WARNING, $originTokens[$secondFunctionTokenId]->lineNumber);
        }
        return $originTokens;
    }
}