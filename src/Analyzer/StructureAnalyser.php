<?php

namespace MyApp\Analyzer;

use MyApp\Statistics\StatKeeper;

class StructureAnalyser{

    private $statKeeper;
    private $fileName;
    private $introducedProblems;

    public function __construct(StatKeeper $statKeeper, $fileName, $introducedProblems){
        $this->statKeeper = $statKeeper;
        $this->fileName = $fileName;
        $this->introducedProblems = $introducedProblems;
    }

    public function isTooLongStructure($tokens, string $type, int $length){
        $tokens = $this->markTokensAndProgress(Helper::$fileMetaData[$type], $tokens, $length);
        return $tokens;
    }

    private function markTokensAndProgress($functionMetadata, $tokens, $length){
        foreach ($functionMetadata as $data) {
            if ($data['end'] - $data['start'] > $length) {
                $tokens[$data['start-i']]->tokenMessage .= Rules::TOO_LENGTHY_STRUCTURE;
                $this->statKeeper->addProgress($this->fileName, 1, Rules::TOO_LENGTHY_STRUCTURE, $tokens[$data['start-i']]->lineNumber, $this->introducedProblems);
            }
        }
        return $tokens;
    }

    public function areLinesTooLong($tokens, $maxLineLength){
        $tokensForLine = [];
        foreach ($tokens as $key => $token) {
            $token->tokenKey = $key;
            if ($token->tokenIdentifier == T_WHITESPACE && preg_match('/[\n]+/', $token->content)) {
                if (!empty($tokensForLine)) {
                    if ($this->isLineTooLong($tokensForLine, $maxLineLength)) {
                        $errorMarked = false;
                        foreach ($tokensForLine as $token_) {
                            $tokens[$token_->tokenKey]->tokenMessage .= Rules::TOO_LONG_LINE;
                            if (!$errorMarked) {
                                $this->statKeeper->addProgress($this->fileName, 1, Rules::TOO_LONG_LINE, $token->lineNumber, $this->introducedProblems);
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
        return $tokens;
    }

    public function findUnusedMethods($tokens){
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i]->tokenIdentifier == T_FUNCTION) {
                $functionNameFound = false;
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j]->tokenIdentifier == T_STRING) {
                        $errorMarked = false;
                        for ($h = $j + 1; $h < count($tokens); $h++) {
                            if ($tokens[$h]->tokenName == 'bracketOpen') {
                                $errorMessage = $this->checkIfFunctionUsed($tokens, $j);
                                $tokens[$j]->tokenMessage .= $errorMessage;
                                if ($errorMessage && !$errorMarked) {
                                    $this->statKeeper->addProgress($this->fileName, 1, $errorMessage, $tokens[$j]->lineNumber, $this->introducedProblems);
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
        return $tokens;
    }

    public function hasFunctionTooManyParameters($tokens){
        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i]->tokenIdentifier == T_FUNCTION) {
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j]->tokenIdentifier == T_STRING) {
                        for ($h = $j + 1; $h < count($tokens); $h++) {
                            if ($tokens[$h]->tokenName == 'bracketOpen') {
                                for ($k = $h + 1; $k < count($tokens); $k++) {
                                    if ($tokens[$k]->tokenName == 'bracketClose') {
                                        $this->countParams($tokens, $h + 1, $k);
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
        return $tokens;
    }

    public function longestRepeatedTokenChain($tokens, $threshold){
        $repeatedTokens = (new LongestRepeatedChains())->findAll($tokens, $threshold);
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
            foreach ($tokens as $token) {
                if ($token->tokenHash >= $lowest && $token->tokenHash <= $highest) {
                    $token->tokenMessage .= Rules::REPEATED_CHUNK_OF_CODE_WARNING;
                    if (!$errorMarked) {
                        $this->statKeeper->addProgress($this->fileName, 1, Rules::REPEATED_CHUNK_OF_CODE_WARNING, $token->lineNumber, $this->introducedProblems);
                        $errorMarked = true;
                    }
                }
            }
        }
        return $tokens;
    }

    public function identifyFunctionSimilarities($tokens){
        $similarFunctionAnalyzer = new FunctionSimilarityAnalyser();

        return $similarFunctionAnalyzer->checkFunctionStringSimilarity($tokens, $this->statKeeper, $this->fileName);
    }

    private function checkIfFunctionUsed($tokens, int $tokenIndexOfFunctionName){
        $tokenAnalyzer = new TokenAnalyser($this->statKeeper, $this->fileName, $this->introducedProblems);
        if ($tokenAnalyzer->isNative($tokens[$tokenIndexOfFunctionName])) {
            return null;
        }

        if ($this->isPublicFunction($tokens, $tokenIndexOfFunctionName)) {
            return null;
        }

        foreach ($tokens as $index => $token) {
            if ($token->content == $tokens[$tokenIndexOfFunctionName]->content && $tokenIndexOfFunctionName !== $index) {
                $whitespaceCounter = $index + 1;
                while($tokens[$whitespaceCounter]->tokenIdentifier == T_WHITESPACE) {
                    $whitespaceCounter++;
                }
                if ($tokens[$whitespaceCounter]->tokenName == 'bracketOpen') {
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

    private function countParams($tokens, $h, $k){
        $params = 0;
        for ($i = $h; $i <= $k; $i++) {
            if ($tokens[$i]->tokenIdentifier == T_VARIABLE) {
                $params++;
            }
        }
        if ($params >= Rules::MAX_PARAMS) {
            $errorMarked = false;
            for ($i = $h; $i <= $k; $i++) {
                $tokens[$i]->tokenMessage .= Rules::TOO_MANY_PARAMS_WARNING;
                if (!$errorMarked) {
                    $this->statKeeper->addProgress($this->fileName, 1, Rules::TOO_MANY_PARAMS_WARNING, $tokens[$i]->lineNumber, $this->introducedProblems);
                    $errorMarked = true;
                }
            }
        }
    }

    private function isPublicFunction($tokens, int $tokenIndexOfFunctionName){
        $tokenAnalyzer = new TokenAnalyser($this->statKeeper, $this->fileName, $this->introducedProblems);
        if ($tokens[$tokenIndexOfFunctionName]->tokenIdentifier == T_STRING) {
            $previousNonWhiteSpaceToken = $tokenAnalyzer->getPreviousNonWhitespaceToken($tokens, $tokenIndexOfFunctionName);

            if ($previousNonWhiteSpaceToken->tokenIdentifier == T_FUNCTION) {
                $accessModifierToken = $tokenAnalyzer->getPreviousNonWhitespaceToken($tokens, $previousNonWhiteSpaceToken->tokenHash);
                if ($accessModifierToken->tokenIdentifier == T_PUBLIC) {
                    return true;
                }
            }
        }
        return false;
    }

    public function markPartOfStructure($tokens){
        $tokens = $this->getTokensPerClass($tokens, T_CLASS);
        $tokens = $this->getTokensPerClass($tokens, T_FUNCTION);
        $tokens = $this->getTokensPerClass($tokens, T_FOREACH);
        $tokens = $this->getTokensPerClass($tokens, T_FOR);
        return $tokens;
    }

    private function getTokensPerClass($tokens, $type){
        $currentClass = null;
        $fileMetadata = [];
        $curlyBracketOpen = 0;
        $curlyBracketClose = 0;
        $counter = 0;

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i]->tokenIdentifier == $type) {

                if ($type == T_FOREACH) {
                    $functionMetadata[$counter][$type] = "foreach";
                }
                if ($type == T_FOR) {
                    $functionMetadata[$counter][$type] = "for";
                }

                $fileMetadata[$counter]['start'] = $tokens[$i]->lineNumber + 1;
                $fileMetadata[$counter]['start-i'] = $i;

                for ($j = $i; $j < count($tokens); $j++) {
                    if ($tokens[$j]->tokenIdentifier == T_STRING && !isset($fileMetadata[$counter][$type])) {
                        $fileMetadata[$counter][$type]['name'] = $tokens[$j]->content;
                    }

                    if ($tokens[$j]->tokenName == Token::CURLY_BRACKET_OPEN) {
                        $curlyBracketOpen++;
                    }
                    if ($tokens[$j]->tokenName == Token::CURLY_BRACKET_CLOSE) {
                        $curlyBracketClose++;
                    }
                    if ($curlyBracketOpen > 0 && $curlyBracketOpen == $curlyBracketClose) {
                        $fileMetadata[$counter]['end'] = $tokens[$j]->lineNumber;
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
                    $tokens[$i]->partOfClass = $data[$type]['name'];
                } elseif ($type == T_FUNCTION) {
                    $tokens[$i]->partOfFunction = $data[$type]['name'];
                } elseif ($type == T_FOREACH) {
                    $tokens[$i]->partOfForeach = true;
                } elseif ($type == T_FOR) {
                    $tokens[$i]->partOfFor = true;
                }
            }
        }

        Helper::$fileMetaData[$type] = $fileMetadata;

        return $tokens;
    }
}
