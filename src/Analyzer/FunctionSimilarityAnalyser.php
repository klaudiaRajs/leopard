<?php


namespace MyApp\Analyzer;

use function Couchbase\basicDecoderV1;
use MyApp\Statistics\StatKeeper;

class FunctionSimilarityAnalyser{

    public function checkFunctionStringSimilarity(array $tokens, StatKeeper $statKeeper){

        $tokensPerFunction = $this->divideTokensIntoFunctions($tokens);

        $textSimilaritiesPerFunction = $this->analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction);
        $parametersPassedSimilarity = $this->analyzeGroupOfTokenSimilarity($this->getParamsPerFunction($tokensPerFunction, $tokens));
        $returnVariablesSimilarity = $this->analyzeGroupOfTokenSimilarity($this->getReturnTokensPerFunction($tokensPerFunction));
        $abstractedTextSimilarity = $this->analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction, true); //abstract variables

        $finalResult = [];
        $finalResult = $this->getOrderedResult($textSimilaritiesPerFunction, 'pureTextSimilarity', $finalResult);
        $finalResult = $this->getOrderedResult($parametersPassedSimilarity, 'paramSimilarity', $finalResult);
        $finalResult = $this->getOrderedResult($returnVariablesSimilarity, 'returns', $finalResult);
        $finalResult = $this->getOrderedResult($abstractedTextSimilarity, 'abstractedText', $finalResult);


        foreach ($finalResult as $function => $comparedFunction) {
            foreach ($comparedFunction as $nestedFunction => $metrics) {
                $sum = $this->getSumOfPercentageResults($metrics);
                $average = round($this->getAverage($sum, count($metrics)), 2);
                $finalResult[$function][$nestedFunction]['average'] = $average;
                if ($average > Rules::SIMILARITY_THRESHOLD) {
                    $tokens = $this->markTokensAsSimilar($tokens, $tokensPerFunction, $function, $nestedFunction, $average);
                }
            }
        }

        $statKeeper->saveSimilarity($finalResult);
        return $tokens;
    }

    private function getAverage($sum, $numberOfMetrics){
        return $sum / $numberOfMetrics;
    }

    private function getSumOfPercentageResults(array $metrics){
        $sum = 0.0;
        foreach ($metrics as $similarity) {
            $sum += $similarity;
        }
        return $sum;
    }

    private function getOrderedResult(array $unorderedResult, string $desc, array $finalResult){
        foreach ($unorderedResult as $function => $otherFunctions) {
            foreach ($otherFunctions as $nestedFunction => $similarity) {
                if (!isset($finalResult[$nestedFunction][$function])) {
                    $finalResult[$function][$nestedFunction][$desc] = $similarity;
                }
            }
        }
        return $finalResult;
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
            $theSameParams[] = $this->convertIntToCharCode($k);
        }

        $newParamsA = array_values($theSameParams);
        $newParamsB = array_values($theSameParams);
        $idx = 0;
        foreach ($paramsA as $name) {
            if (in_array($name, $paramsWithExactlyTheSameName)) {
                continue;
            }
            $newParamsA[] = $this->convertIntToCharCode($theSameParamsCount + $idx++);
        }
        $paramsACount = count($newParamsA);
        $idx = 0;
        foreach ($paramsB as $name) {
            if (in_array($name, $paramsWithExactlyTheSameName)) {
                continue;
            }
            $newParamsB[] = $this->convertIntToCharCode($paramsACount + $idx++);
        }

        similar_text(implode('', $newParamsA), implode('', $newParamsB), $percent);
        return $percent;
    }

    private function convertIntToCharCode($k){
        $asciiId = $k + 65; //A-Z
        if ($asciiId > 90) {
            $asciiId += 7; //move to a-Z
        }
        return chr($asciiId);
    }

    private function getParamsPerFunction($tokensPerFunction, $tokens){
        $paramsPerFunction = [];

        foreach ($tokensPerFunction as $functionName => $function) {
            $paramStartIndex = 0;
            /** @var Token $token */
            foreach ($function as $token) {
                if ($token->tokenName == 'bracketOpen') {
                    $paramStartIndex = $token->tokenHash;
                    continue;
                }
                if ($token->tokenName == 'bracketClose') {
                    $paramsPerFunction[$functionName] = $this->extractParams($tokens, $paramStartIndex, $token->tokenHash);
                    break;
                }
            }
        }
        return $paramsPerFunction;
    }

    private function extractParams($tokens, $paramListStart, $paramListEnd){

        $params = [];
        for ($i = $paramListStart; $i <= $paramListEnd; $i++) {
            if ($tokens[$i]->tokenIdentifier == T_VARIABLE) {
                $params[] = $tokens[$i];
            }
        }
        return $params;
    }

    private function analyzeTextSimilarityWithOptionToAbstract($tokensPerFunction, $abstracted = false){
        $result = [];

        foreach ($tokensPerFunction as $firstFunction => $function) {
            $tokensToOperateOn = $abstracted ? $this->changeFunctionToString($this->getModifiedTokens($function)) : $this->changeFunctionToString($function);
            foreach ($tokensPerFunction as $functionName => $function_) {
                if ($firstFunction == $functionName) {
                    continue;
                }
                $functionAsString = $abstracted ? $this->changeFunctionToString($this->getModifiedTokens($function_)) : $this->changeFunctionToString($function_);
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

    private function changeFunctionToString(array $function){
        $functionAsString = '';
        /** @var Token $token */
        foreach ($function as $token) {
            $functionAsString .= $token->content . ' ';
        }
        return $functionAsString;
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

        $firstFunctionTokenId = $tokensPerFunction[$function][0]->tokenHash;
        $secondFunctionTokenId = $tokensPerFunction[$nestedFunction][0]->tokenHash;

        $originTokens[$firstFunctionTokenId]->tokenMessage .= Rules::SIMILAR_CHUNK_OF_CODE_WARNING . $nestedFunction . ' by: ' . $average . "\n";
        $originTokens[$secondFunctionTokenId]->tokenMessage .= Rules::SIMILAR_CHUNK_OF_CODE_WARNING . $function . ' by: ' . $average . "\n";

        return $originTokens;
    }
}