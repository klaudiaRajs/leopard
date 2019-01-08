<?php

class FileAnalyzer extends CI_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->helper(array('form', 'url'));
    }

    function analyzeUpload($fileName){
        $fileContents = file_get_contents(FCPATH . "\uploads\\" . $fileName);
        $tokenizer = new tokenizer($fileContents);

        $tokens = $tokenizer->getAll();

        $tokens = $tokenizer->getTokenMessages($tokens);

        $formattedContents = TokenPresenter::getFormattedContents($tokens);
        $this->load->view('codePresenter', ['fileContents' => $formattedContents]);
    }
}



