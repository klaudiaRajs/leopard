<?php

namespace MyApp\Controller;

use MyApp\Analyzer\Rules;
use MyApp\Analyzer\StructureAnalyser;
use MyApp\Analyzer\TokenAnalyser;
use MyApp\Config\Config;
use MyApp\Statistics\StatKeeper;
use MyApp\View\ViewRenderer;
use MyApp\Analyzer\Tokenizer;
use MyApp\Analyzer\TokenPresenter;

class FileAnalyzer{
    public $statResultFilePath;

    public function analyzeUpload($fileName, $introduceProblems, StatKeeper $statKeeper, $extraContent = null){
        if( $extraContent ){
            return ViewRenderer::render('CodePresenter', ['fileContents' => $extraContent]);
        }

        if (!$introduceProblems) {
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

        $tokens = $this->getTokenMessages($tokens, $introduceProblems, $statKeeper, $fileName);

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
                    "This file was prepared to contain <number>" . $fileResult['introduced'] .
                    '</number> poor programming practices. The algorithm identified <number>' .
                    $fileResult['found'] . '</number>';
            }
        }

        $resultSummary .= '<br>' . "Analyse more files: " . "<a href=\"" . Config::URL . "addFile\" > Analyse another file </a >";
        return ViewRenderer::render('ResultSummary', ['resultSummary' => $resultSummary]);
    }

    public function getTokenMessages($tokens, $introducedProblems, StatKeeper $statKeeper, $fileName){

        $tokenAnalyzer = new TokenAnalyser($statKeeper, $fileName, $introducedProblems);
        $structureAnalyzer = new StructureAnalyser($statKeeper, $fileName, $introducedProblems);

        $tokens = $structureAnalyzer->markPartOfStructure($tokens);

        $tokens = $structureAnalyzer->isTooLongStructure($tokens, T_FOREACH, Rules::LOOP_LENGTH);
        $tokens = $structureAnalyzer->isTooLongStructure($tokens, T_FOR, Rules::LOOP_LENGTH);

        for ($i = 0; $i < count($tokens); $i++) {
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->containsStatics($tokens[$i]);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->containsDeprecated($tokens[$i]);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->containsGlobal($tokens[$i]);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->containsUnusedVariables($i, $tokens[$i], $tokens);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->checkIfNamingConventionFollowed($tokens[$i], $tokens, $i);
            $tokens[$i]->tokenMessage .= $tokenAnalyzer->checkIfNotSingleLetterVariable($tokens[$i]);
        }

        $tokens = $structureAnalyzer->isTooLongStructure($tokens, T_FUNCTION, Rules::FUNCTION_LENGTH);
        $tokens = $structureAnalyzer->isTooLongStructure($tokens, T_CLASS, Rules::CLASS_LENGTH);
        $tokens = $structureAnalyzer->areLinesTooLong($tokens, Rules::LINE_LENGTH);
        $tokens = $structureAnalyzer->longestRepeatedTokenChain($tokens, Rules::REPEATED_STRING_THRESHOLD);
        $tokens = $structureAnalyzer->identifyFunctionSimilarities($tokens);
        $tokens = $structureAnalyzer->hasFunctionTooManyParameters($tokens);
        $tokens = $structureAnalyzer->findUnusedMethods($tokens);

        return $tokens;
    }
}