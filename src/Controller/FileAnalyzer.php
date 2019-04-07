<?php

namespace MyApp\Controller;

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



        $tokenizer = new Tokenizer($fileContents, $fileName, $statKeeper);

        $tokens = $tokenizer->getAll();

        $tokens = $tokenizer->getTokenMessages($tokens, $introduceProblems);

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
}