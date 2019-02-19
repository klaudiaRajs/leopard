<?php

namespace MyApp\Controller;

use MyApp\View\ViewRenderer;
use MyApp\Analyzer\Tokenizer;
use MyApp\Analyzer\TokenPresenter;

class FileAnalyzer{
    public function analyzeUpload($fileName, int $introduceProblems){
        $fileContents = file_get_contents(__DIR__ . "/../../upload/" . $fileName);
        $tokenizer = new Tokenizer($fileContents, $fileName);

        $tokens = $tokenizer->getAll();

        $tokens = $tokenizer->getTokenMessages($tokens, $introduceProblems);

        $formattedContents = TokenPresenter::getFormattedContents($tokens);

        return ViewRenderer::render('CodePresenter', ['fileContents' => $formattedContents]);
    }
}