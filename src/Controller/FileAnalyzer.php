<?php

namespace MyApp\Controller;

use MyApp\Analyzer\Rules;
use MyApp\Analyzer\TokenAnalyser;
use MyApp\Config\Config;
use MyApp\Statistics\StatKeeper;
use MyApp\View\ViewRenderer;
use MyApp\Analyzer\Tokenizer;
use MyApp\Analyzer\TokenPresenter;

class FileAnalyzer{
    public $statResultFilePath;

    public function analyzeUpload($fileName, $introducedProblems, $extraContent = null){
        if( $extraContent ){
            return ViewRenderer::render('CodePresenter', ['fileContents' => $extraContent]);
        }

        if (!$introducedProblems) {
            return ViewRenderer::render('CodePresenter', ['fileContents' => $fileName]);
        }

        try {
            $fileWithPath = __DIR__ . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "upload" . DIRECTORY_SEPARATOR . $fileName;
            $fileContents = @file_get_contents($fileWithPath);
            if ($fileContents === false) {
                throw new \Exception("No file found: " . $fileName);
            }
        }catch(\Exception $e) {
            return $e->getMessage();
        }

        $tokenizer = new Tokenizer($fileContents);

        $tokens = $tokenizer->getAll();

        StatKeeper::$currentFile = $fileName;
        $tokens = $this->getTokenMessages($tokens, $introducedProblems);

        $formattedContents = TokenPresenter::getFormattedContents($tokens);

        return ViewRenderer::render('CodePresenter', ['fileContents' => $formattedContents]);
    }

    public function analyzeResults(){
        $result = json_decode(@file_get_contents($this->statResultFilePath), true);
        $resultSummary = '';
        if( !is_array($result) ){
            throw new \Exception("Problem with getting statistics for: " . $this->statResultFilePath);
        }
        foreach ($result as $fileName => $fileResult) {
            if (is_array($fileResult)) {
                $resultSummary .= '<br><br><h5>File: ' . $fileName . ': </h5><br>' .
                    "This file was prepared to contain <number>" . (isset($fileResult['introduced']) ?  $fileResult['introduced'] : 1) .
                    '</number> poor programming practices. The algorithm identified <number>' .
                    $fileResult['found'] . '</number>';
            }
        }

        $resultSummary .= '<br>' . "Analyse more files: " . "<a href=\"" . Config::URL . "addFile\" > Analyse another file </a >";
        return ViewRenderer::render('ResultSummary', ['resultSummary' => $resultSummary]);
    }

    public function getTokenMessages($tokens, $introducedProblems){

        $tokenAnalyzer = new TokenAnalyser($introducedProblems, $tokens);


        $tokens = $tokenAnalyzer->markPartOfStructure();

        $tokens = $tokenAnalyzer->isTooLongStructure(T_FOREACH, Rules::LOOP_LENGTH);
        $tokens = $tokenAnalyzer->isTooLongStructure(T_FOR, Rules::LOOP_LENGTH);

        for ($i = 0; $i < count($tokens); $i++) {
            $tokens = $this->markContent($tokenAnalyzer->containsStatics($tokens[$i]), $i, $introducedProblems, $tokens);
            $tokens = $this->markContent($tokenAnalyzer->containsDeprecated($tokens[$i]), $i, $introducedProblems, $tokens);
            $tokens = $this->markContent($tokenAnalyzer->containsGlobal($tokens[$i]), $i, $introducedProblems, $tokens);
            $tokens = $this->markContent($tokenAnalyzer->containsUnusedVariables($tokens[$i]), $i, $introducedProblems, $tokens);
            $tokens = $this->markContent($tokenAnalyzer->checkIfNamingConventionFollowed($tokens[$i]), $i, $introducedProblems, $tokens);
            $tokens = $this->markContent($tokenAnalyzer->checkIfNotSingleLetterVariable($tokens[$i]), $i, $introducedProblems, $tokens);
        }

        $tokens = $tokenAnalyzer->isTooLongStructure(T_FUNCTION, Rules::FUNCTION_LENGTH);
        $tokens = $tokenAnalyzer->isTooLongStructure(T_CLASS, Rules::CLASS_LENGTH);
        $tokens = $tokenAnalyzer->areLinesTooLong();
        $tokens = $tokenAnalyzer->longestRepeatedTokenChain();
        $tokens = $tokenAnalyzer->identifyFunctionSimilarities();
        $tokens = $tokenAnalyzer->hasFunctionTooManyParameters();
        $tokens = $tokenAnalyzer->findUnusedMethods();

        return $tokens;
    }

    private function markContent($result, $i, $introducedProblems, $tokens){
        if( $result !== null ){
            StatKeeper::addProgress(1, $result, $tokens[$i]->lineNumber, $introducedProblems);
            $tokens[$i]->tokenMessage .= $result;
        }
        return $tokens;
    }
}