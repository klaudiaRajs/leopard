<?php


class regular_expression_analyzer_helper_test extends TestCase{

    public function test_findUsingStaticMethods()
    {
        $line = "public static function unitTestingAMethod()";
        $expected = 'public static function unitTestingAMethod()<span style="color:red;">//Static</span>';
        $this->assertEquals($expected, RegularExpressionsAnalyzer::findUsingStaticMethods($line));
    }

    public function test_findUsingStaticMethodsStaticInsideSingleString()
    {
        $line = "public function unitTestingAMethod('I am using public static function')";
        $expected = 'public function unitTestingAMethod(\'I am using public static function\')';
        $this->assertEquals($expected, RegularExpressionsAnalyzer::findUsingStaticMethods($line));
    }

    public function test_findUsingStaticMethodsStaticInsideDoubleString()
    {
        $line = 'public function unitTestingAMethod("I am using public static function")';
        $expected = 'public function unitTestingAMethod("I am using public static function")';
        $this->assertEquals($expected, RegularExpressionsAnalyzer::findUsingStaticMethods($line));
    }

    public function test_findDeprecatedFunctions(){
        $line = "sql_regcase(\"TEXT\");";
        $expected = 'sql_regcase("TEXT");<span style="color:red;">//Method is deprecated</span>';
        $this->assertEquals($expected, RegularExpressionsAnalyzer::findDeprecatedFunctions($line));
    }
}