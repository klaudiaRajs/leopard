<?php

namespace MyApp\Analyzer;

use MyApp\Helpers\TokenHelper;

class TokenAnalyser{

    private $tokens;
    private $introduceProblems;
    private $structureAnalyzer;
    private $individualTokenAnalyzer;
    private $tokenInContextAnalyzer;
    private $nameConventionAnalyzer;
    private $helper;

    public function __construct(int $introducedProblems, $tokens){
        $this->tokens = $tokens;
        $this->helper = new TokenHelper($tokens);
        $this->introduceProblems = $introducedProblems;
        $this->structureAnalyzer = new StructureAnalyser($introducedProblems, $tokens, $this->helper);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->helper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $tokens, $this->helper);
        $this->nameConventionAnalyzer = new NameConventionAnalyzer($this->individualTokenAnalyzer, $this->tokenInContextAnalyzer, $tokens);
    }

    public function markPartOfStructure(){
        return $this->structureAnalyzer->markPartOfStructure();
    }

    public function isTooLongStructure(int $type, int $maxNumberOfLines){
        return $this->structureAnalyzer->isTooLongStructure(TokenHelper::$fileMetaData[$type], $maxNumberOfLines);
    }

    public function containsStatics($token){
        return $this->individualTokenAnalyzer->containsStatics($token);
    }

    public function containsDeprecated($token){
        return $this->individualTokenAnalyzer->containsDeprecated($token);
    }

    public function containsGlobal($token){
        return $this->individualTokenAnalyzer->containsGlobal($token);
    }

    public function containsUnusedVariables($token){
        return $this->tokenInContextAnalyzer->isUnusedVariable($token);
    }

    public function checkIfNamingConventionFollowed($token){
        return $this->nameConventionAnalyzer->checkIfNamingConventionFollowed($token);
    }

    public function checkIfNotSingleLetterVariable($token){
        return $this->individualTokenAnalyzer->checkIfNotSingleLetterVariable($token);
    }

    public function areLinesTooLong(){
        return $this->structureAnalyzer->areLinesTooLong(Rules::LINE_LENGTH);
    }

    public function longestRepeatedTokenChain(){
        return $this->structureAnalyzer->longestRepeatedTokenChain();
    }

    public function identifyFunctionSimilarities(){
        return $this->structureAnalyzer->identifyFunctionSimilarities();
    }

    public function hasFunctionTooManyParameters(){
        return $this->structureAnalyzer->hasFunctionTooManyParameters();
    }

    public function findUnusedMethods(){
        return $this->structureAnalyzer->findUnusedMethods();
    }

    public function isNative($token){
        return $this->individualTokenAnalyzer->isNativeElement($token);
    }
}
