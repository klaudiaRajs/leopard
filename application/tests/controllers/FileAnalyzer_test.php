<?php


class FileAnalyzer_test extends TestCase{
    public function test_getColorCodedLine(){
        $line = "mcrypt_cfb(\"String\", \"String\", \"string\", 4);";
        $expected = 'mcrypt_cfb(<span style="color:#619647;">String</span><span style="color:#CC7832;">,</span><span style="color:#619647;">String</span><span style="color:#CC7832;">,</span><span style="color:#619647;">String</span><span style="color:#CC7832;">,</span> 4);<span style="color:red;">//Method is deprecated</span>';
        $fileAnalyzer = new FileAnalyzer();
        $this->assertEquals($expected, $fileAnalyzer->getColorCodedLine($line));
    }
}