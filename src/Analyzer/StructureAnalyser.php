<?php

namespace MyApp\Analyzer;

use MyApp\Helpers\TokenHelper;
use MyApp\Statistics\StatKeeper;

class StructureAnalyser{

    private $introducedProblems;
    private $tokens;
    /** @var TokenHelper $helper */
    private $helper;
    /** @var TokenInContextAnalyzer */
    private $tokensInContext;

    public function __construct($introducedProblems, $tokens, TokenHelper $tokenHelper){
        $this->introducedProblems = $introducedProblems;
        $this->tokens = $tokens;
        $this->helper = $tokenHelper;
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($this->helper);
        $this->tokensInContext = new TokenInContextAnalyzer($individualTokenAnalyzer, $this->tokens, $this->helper);
    }

    public function isTooLongStructure($structureMetadata, int $length){
        foreach ($structureMetadata as $data) {
            if ($data['end'] - $data['start'] > $length) {
                $this->tokens[$data['start-i']]->tokenMessage .= Rules::TOO_LENGTHY_STRUCTURE;
                StatKeeper::addProgress(1, Rules::TOO_LENGTHY_STRUCTURE, $this->tokens[$data['start-i']]->lineNumber, $this->introducedProblems);
            }
        }
        return $this->tokens;
    }

    public function areLinesTooLong($maxLineLength){
        $tokensForLine = [];
        foreach ($this->tokens as $key => $token) {
            $token->tokenKey = $key;
            if ($token->tokenIdentifier == T_WHITESPACE && preg_match('/[\n]+/', $token->content)) {
                if (!empty($tokensForLine)) {
                    if ($this->isLineTooLong($tokensForLine, $maxLineLength)) {
                        $errorMarked = false;
                        foreach ($tokensForLine as $token_) {
                            $this->tokens[$token_->tokenKey]->tokenMessage .= Rules::TOO_LONG_LINE;
                            if (!$errorMarked) {
                                StatKeeper::addProgress(1, Rules::TOO_LONG_LINE, $token->lineNumber, $this->introducedProblems);
                                $errorMarked = true;
                            }
                        }
                    }
                    $tokensForLine = [];
                }

                //show additional empty lines
                $lines = explode("\n", $token->content);
                foreach ($lines as $index => $line) {
                    //one line is added by default by getFormattedLine
                    if ($index == 0) {
                        continue;
                    }
                    //if last line is empty we skip it, if it has spaces/tabs we add the
                    if ($index == count($lines) - 1 && empty($line)) {
                        continue;
                    }

                    if (!empty($line)) {
                        $tokensForLine[] = new TokenView(T_WHITESPACE, $line, token_name(T_WHITESPACE), $token->tokenMessage, $token->tokenKey);
                    }
                }
                continue;
            }

            //it is complex token with a new line
            if (strpos($token->content, "\n") !== false) {
                $lines = explode("\n", $token->content);
                foreach ($lines as $lineNumber => $lineContent) {
                    if (empty($lineContent)) {
                        continue;
                    }
                    //if this is first iteration there may be already something for this line so we just include the next token
                    $tokensForLine[] = new TokenView($token->tokenIdentifier, $lineContent, $token->tokenName, $token->tokenMessage, $token->tokenKey);
                    $tokensForLine = [];
                }
                continue;
            }

            $tokensForLine[] = TokenView::fromToken($token);
        }
        return $this->tokens;
    }

    public function findUnusedMethods(){
        for ($i = 0; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i]->tokenIdentifier == T_FUNCTION) {
                $functionNameFound = false;
                for ($j = $i + 1; $j < count($this->tokens); $j++) {
                    if ($this->tokens[$j]->tokenIdentifier == T_STRING) {
                        $errorMarked = false;
                        for ($h = $j + 1; $h < count($this->tokens); $h++) {
                            if ($this->tokens[$h]->tokenName == 'bracketOpen') {
                                $errorMessage = $this->checkIfFunctionUsed($j);
                                $this->tokens[$j]->tokenMessage .= $errorMessage;
                                if ($errorMessage && !$errorMarked) {
                                    StatKeeper::addProgress(1, $errorMessage, $this->tokens[$j]->lineNumber, $this->introducedProblems);
                                    $errorMarked = true;
                                }
                                $functionNameFound = true;
                                break;
                            }
                        }
                        if ($functionNameFound) {
                            break;
                        }
                    }
                    if ($functionNameFound) {
                        break;
                    }
                }
            }
        }
        return $this->tokens;
    }

    public function hasFunctionTooManyParameters(){
        for ($i = 0; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i]->tokenIdentifier == T_FUNCTION) {
                for ($j = $i + 1; $j < count($this->tokens); $j++) {
                    if ($this->tokens[$j]->tokenIdentifier == T_STRING) {
                        for ($h = $j + 1; $h < count($this->tokens); $h++) {
                            if ($this->tokens[$h]->tokenName == 'bracketOpen') {
                                for ($k = $h + 1; $k < count($this->tokens); $k++) {
                                    if ($this->tokens[$k]->tokenName == 'bracketClose') {
                                        $this->countParams($h + 1, $k);
                                        break;
                                    }
                                }
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $this->tokens;
    }

    public function longestRepeatedTokenChain(){
        $repeatedTokens = (new LongestRepeatedChains())->findAll($this->tokens, Rules::REPEATED_STRING_THRESHOLD);
        foreach ($repeatedTokens as $repetition) {
            $lowest = null;
            $highest = null;
            foreach ($repetition as $token) {
                if (is_null($lowest) || $token->tokenHash < $lowest) {
                    $lowest = $token->tokenHash;
                }
                if (is_null($highest) || $token->tokenHash > $highest) {
                    $highest = $token->tokenHash;
                }
            }
            $errorMarked = false;
            foreach ($this->tokens as $token) {
                if ($token->tokenHash >= $lowest && $token->tokenHash <= $highest) {
                    $token->tokenMessage .= Rules::REPEATED_CHUNK_OF_CODE_WARNING;
                    if (!$errorMarked) {
                        StatKeeper::addProgress(1, Rules::REPEATED_CHUNK_OF_CODE_WARNING, $token->lineNumber, $this->introducedProblems);
                        $errorMarked = true;
                    }
                }
            }
        }
        return $this->tokens;
    }

    public function identifyFunctionSimilarities(){
        $similarFunctionAnalyzer = new FunctionSimilarityAnalyser();

        return $similarFunctionAnalyzer->checkFunctionStringSimilarity($this->tokens);
    }

    private function checkIfFunctionUsed(int $tokenIndexOfFunctionName){
        $tokenAnalyzer = new TokenAnalyser($this->introducedProblems, $this->tokens);
        if ($tokenAnalyzer->isNative($this->tokens[$tokenIndexOfFunctionName])) {
            return null;
        }

        if ($this->tokensInContext->isPublicFunction($tokenIndexOfFunctionName)) {
            return null;
        }

        foreach ($this->tokens as $index => $token) {
            if ($token->content == $this->tokens[$tokenIndexOfFunctionName]->content && $tokenIndexOfFunctionName !== $index) {
                $whitespaceCounter = $index + 1;
                while($this->tokens[$whitespaceCounter]->tokenIdentifier == T_WHITESPACE) {
                    $whitespaceCounter++;
                }
                if ($this->tokens[$whitespaceCounter]->tokenName == 'bracketOpen') {
                    return null;
                }
            }
        }
        return Rules::UNUSED_METHOD_WARNING;
    }

    private function isLineTooLong($tokensForLine, $maxLineLength){
        $lineLength = 0;
        foreach ($tokensForLine as $token) {
            $lineLength += strlen($token->content);
        }

        if ($lineLength < $maxLineLength) {
            return false;
        }
        return true;
    }

    private function countParams($h, $k){
        $params = 0;
        $tokensProceedingExcludedParams = [T_FOR, T_FOREACH, T_IF];
        $previousToken = TokenHelper::getPreviousNonWhitespaceTokenStaticAccess($h - 1, $this->tokens);

        if (in_array($previousToken->tokenIdentifier, $tokensProceedingExcludedParams)) {
            return;
        }

        for ($i = $h; $i <= $k; $i++) {
            if ($this->tokens[$i]->tokenIdentifier == T_VARIABLE) {
                $params++;
            }
        }
        if ($params >= Rules::MAX_PARAMS) {
            $errorMarked = false;
            for ($i = $h; $i <= $k; $i++) {
                if (strpos($this->tokens[$i]->tokenMessage, Rules::TOO_MANY_PARAMS_WARNING) === false) {
                    $this->tokens[$i]->tokenMessage .= Rules::TOO_MANY_PARAMS_WARNING;
                    if (!$errorMarked) {
                        StatKeeper::addProgress(1, Rules::TOO_MANY_PARAMS_WARNING, $this->tokens[$i]->lineNumber, $this->introducedProblems);
                        $errorMarked = true;
                    }
                }
            }
        }
    }

    public function markPartOfStructure(){
        $this->tokens = $this->getTokensPerClass(T_CLASS);
        $this->tokens = $this->getTokensPerClass(T_FUNCTION);
        $this->tokens = $this->getTokensPerClass(T_FOREACH);
        $this->tokens = $this->getTokensPerClass(T_FOR);
        return $this->tokens;
    }

    private function getTokensPerClass($type){
        $currentClass = null;
        $fileMetadata = [];
        $curlyBracketOpen = 0;
        $curlyBracketClose = 0;
        $counter = 0;

        for ($i = 0; $i < count($this->tokens); $i++) {
            if ($this->tokens[$i]->tokenIdentifier == $type) {

                if ($type == T_FOREACH) {
                    $functionMetadata[$counter][$type] = "foreach";
                }
                if ($type == T_FOR) {
                    $functionMetadata[$counter][$type] = "for";
                }

                $fileMetadata[$counter]['start'] = $this->tokens[$i]->lineNumber + 1;
                $fileMetadata[$counter]['start-i'] = $i;

                for ($j = $i; $j < count($this->tokens); $j++) {
                    if ($this->tokens[$j]->tokenIdentifier == T_STRING && !isset($fileMetadata[$counter][$type])) {
                        $fileMetadata[$counter][$type]['name'] = $this->tokens[$j]->content;
                    }

                    if ($this->tokens[$j]->tokenName == Token::CURLY_BRACKET_OPEN) {
                        $curlyBracketOpen++;
                    }
                    if ($this->tokens[$j]->tokenName == Token::CURLY_BRACKET_CLOSE) {
                        $curlyBracketClose++;
                    }
                    if ($curlyBracketOpen > 0 && $curlyBracketOpen == $curlyBracketClose) {
                        $fileMetadata[$counter]['end'] = $this->tokens[$j]->lineNumber;
                        $fileMetadata[$counter]['end-i'] = $j;
                        break;
                    }
                }
                $counter++;
            }
            $curlyBracketOpen = 0;
            $curlyBracketClose = 0;
        }

        foreach ($fileMetadata as $data) {
            for ($i = $data['end-i']; $i >= $data['start-i']; $i--) {
                if ($type == T_CLASS) {
                    $this->tokens[$i]->partOfClass = $data[$type]['name'];
                } elseif ($type == T_FUNCTION) {
                    $this->tokens[$i]->partOfFunction = $data[$type]['name'];
                } elseif ($type == T_FOREACH) {
                    $this->tokens[$i]->partOfForeach = true;
                } elseif ($type == T_FOR) {
                    $this->tokens[$i]->partOfFor = true;
                }
            }
        }

        TokenHelper::$fileMetaData[$type] = $fileMetadata;

        return $this->tokens;
    }
}
