<?php

class syntax_colour_helper_test extends TestCase{

    public function test_markComment()
    {
        $line = "//I test marking oneLine comment";
        $expected = '<span style="color:#619647;">//I test marking oneLine comment</span>';
        $this->assertEquals($expected, SyntaxColourHelper::markComment($line));
    }

    public function test_markCommentPhpDoc()
    {
        $line = "/** I test marking oneLine comment */";
        $expected = '<span style="color:#619647;">/** I test marking oneLine comment */</span>';
        $this->assertEquals($expected, SyntaxColourHelper::markComment($line));
    }

    public function test_markCommentForOneLinePhpDoc()
    {
        $line = "/** I test marking oneLine comment */";
        $expected = '<span style="color:#619647;">/** I test marking oneLine comment */</span>';
        $this->assertEquals($expected, SyntaxColourHelper::markCommentsForOneLine($line));
    }

    public function test_markKeywords(){
        $line = "public function getName(){";
        $keyword = "public";
        $result = SyntaxColourHelper::markKeywords($keyword, $line);
        $keyword2 = "function" ;
        $result = SyntaxColourHelper::markKeywords($keyword2, $result);
        $expected = '<span style="color:#CB772F;">public</span> <span style="color:#CB772F;">function</span> getName(){';
        $this->assertEquals($expected, $result);
    }

    public function test_markKeywordsSkipInString(){
        $line = 'public function getName("public function"){';
        $keyword = "public";
        $result = SyntaxColourHelper::markKeywords($keyword, $line);
        $keyword2 = "function" ;
        $result = SyntaxColourHelper::markKeywords($keyword2, $result);
        $expected = '<span style="color:#CB772F;">public</span> <span style="color:#CB772F;">function</span> getName(<span style="color:#619647;">"public function"</span>){';
        $this->assertEquals($expected, $result);
    }

    public function test_markString(){

    }
}