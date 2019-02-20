<?php

namespace MyApp\Controller;

use MyApp\Statistics\StatKeeper;
use MyApp\View\ViewRenderer;
use MyApp\Analyzer\Tokenizer;
use MyApp\Analyzer\TokenPresenter;

class FileAnalyzer{
    public $statResultFilePath;

    public function analyzeUpload($fileName, int $introduceProblems, StatKeeper $statKeeper){
        $fileContents = file_get_contents(__DIR__ . "/../../upload/" . $fileName);
        $tokenizer = new Tokenizer($fileContents, $fileName, $statKeeper);

        $tokens = $tokenizer->getAll();

        $tokens = $tokenizer->getTokenMessages($tokens, $introduceProblems);

        $formattedContents = TokenPresenter::getFormattedContents($tokens);

        return ViewRenderer::render('CodePresenter', ['fileContents' => $formattedContents]);
    }

    public function analyzeResults(){
        $result = json_decode(file_get_contents($this->statResultFilePath), true);
        $resultSummary = '';
        foreach ($result as $fileName => $fileResult) {
            if (is_array($fileResult)) {
                $resultSummary .= '<br><br><h5>File: ' . $fileName . ': </h5><br>' .
                    "This file was prepared to contain <number>" . $fileResult['introduced'] .
                    '</number> poor programming practices. The algorithm identified <number>' .
                    $fileResult['found'] . '</number>';
            }
        }
        return ViewRenderer::render('ResultSummary', ['resultSummary' => $resultSummary]);
    }
}