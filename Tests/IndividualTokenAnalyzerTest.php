<?php

namespace MyApp\Tests;

use MyApp\Analyzer\IndividualTokenAnalyzer;
use MyApp\Analyzer\Rules;
use MyApp\Analyzer\Token;
use MyApp\Helpers\TokenHelper;
use PHPUnit\Framework\TestCase;

class IndividualTokenAnalyzerTest extends TestCase{

    /** @var IndividualTokenAnalyzer $individualTokenAnalyzer */
    protected $individualTokenAnalyzer;

    public function setUp(){
        $data = [
            0 => new Token(0, T_STRING, "firstWord", 1, '', '', ''),
            1 => new Token(1, T_VARIABLE, " ", 1, '', '', ''),
            2 => new Token(2, T_DOUBLE_COLON, "::", 1, '', '', ''),
            3 => new Token(3, T_VARIABLE, "fourthWord", 1, '', '', ''),
        ];
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer(new TokenHelper($data));
    }

    public function testContainsGlobalException(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->containsGlobal(null);
    }

    public function provideIncorrectGlobalVariables(){
        return [
            ['GET'],
            ['$POST'],
            ['session'],
            ['FILES'],
            ['TEST']
        ];
    }

    /**
     * @dataProvider provideIncorrectGlobalVariables
     */
    public function testContainsGlobalNoWarning($globalsProvider){
        $token = new Token(1, T_VARIABLE, $globalsProvider, 1, '', '', '');
        $this->assertEquals(null, $this->individualTokenAnalyzer->containsGlobal($token));
    }

    public function provideGlobalVariables(){
        return [
            ['$_GET'],
            ['$_POST'],
            ['$_SESSION'],
            ['$_FILES'],
            ['$GLOBALS']
        ];
    }

    /**
     * @dataProvider provideGlobalVariables
     */
    public function testContainsGlobalWarning($globalsProvider){
        $token = new Token(1, T_VARIABLE, $globalsProvider, 1, '', '', '');
        $this->assertEquals(Rules::GLOBALS_WARNING, $this->individualTokenAnalyzer->containsGlobal($token));
    }

    public function testIsExcludedFromCheckException(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->isExcludedFromCheck(null);
    }

    public function provideExcludedNames(){
        return [
            [new Token(1, T_VARIABLE, '$this', 1, '', '', 1)],
            [new Token(3, T_VARIABLE, '$stuff', 1, '', '', 1)],
        ];
    }

    /** @dataProvider provideExcludedNames */
    public function testIsExcludedFromCheckAffirmative($token){
        $this->assertEquals(true, $this->individualTokenAnalyzer->isExcludedFromCheck($token));
    }

    public function provideNotExcludedNames(){
        return [
            [new Token(1, T_VARIABLE, '$abc', 1, '', '', 1)],
            [new Token(2, T_VARIABLE, '$stuff', 1, '', '', 1)],
        ];
    }

    /** @dataProvider provideNotExcludedNames */
    public function testIsExcludedFromCheckExcluded($token){
        $this->assertEquals(false, $this->individualTokenAnalyzer->isExcludedFromCheck($token));
    }

    public function provideStaticResemble(){
        return [
            ['statical'],
            ['static'],
            ['STATIC'],
        ];
    }

    /** @dataProvider provideStaticResemble */
    public function testContainsStaticsNoWarning($staticResemble){
        $token = new Token(1, T_STRING, $staticResemble, 1, '', '', '');
        $this->assertEquals(null, $this->individualTokenAnalyzer->containsStatics($token));
    }

    public function testContainsStaticsWarning(){
        $token = new Token(1, T_STATIC, 'static', 1, '', '', '');
        $this->assertEquals(Rules::STATIC_WARNING, $this->individualTokenAnalyzer->containsStatics($token));
    }

    public function testContainsStaticException(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->containsStatics(null);
    }

    public function provideTokensForSingleLetter(){
        return [
            [new Token(1, T_VARIABLE, '$a', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$1', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$z', 1, "", '', null)],
        ];
    }

    /** @dataProvider provideTokensForSingleLetter */
    public function testCheckIfNotSingleLetterVariable($singleLetter){
        $this->assertEquals(Rules::SINGLE_LETTER_VARIABLE_WARNING, $this->individualTokenAnalyzer->checkIfNotSingleLetterVariable($singleLetter));
    }

    public function provideTokensForSingleLetterWithMoreLetters(){
        return [
            [new Token(1, T_VARIABLE, '$customer', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$ab', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$abc', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$abcd', 1, "", '', null)],
            [new Token(1, T_VARIABLE, '$abcde', 1, "", '', null)],
        ];
    }

    /** @dataProvider provideTokensForSingleLetterWithMoreLetters */
    public function testCheckIfNotSingleLetterVariableNoWarning($singleLetter){
        $this->assertEquals(null, $this->individualTokenAnalyzer->checkIfNotSingleLetterVariable($singleLetter));
    }

    public function testCheckIfNotSingleLetterException(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->checkIfNotSingleLetterVariable(null);
    }


    public function provideNativeElements(){
        return [
            [new Token(1, T_STRING, '__construct', 1, '', '', 1)],
            [new Token(1, T_STRING, 'self', 1, '', '', 1)],
            [new Token(1, T_STRING, 'false', 1, '', '', 1)],
            [new Token(1, T_STRING, 'true', 1, '', '', 1)],
            [new Token(1, T_STRING, 'null', 1, '', '', 1)],
            [new Token(1, T_STRING, '$this', 1, '', '', 1)],
        ];
    }

    /** @dataProvider provideNativeElements */
    public function testIsNativeElementItIs($token){
        $this->assertEquals(true, $this->individualTokenAnalyzer->isNativeElement($token));
    }

    public function provideNonNativeElements(){
        return [
            [new Token(1, T_STRING, 'testString', 1, '', '', 1)],
        ];
    }

    /** @dataProvider provideNonNativeElements */
    public function testIsNativeElementItIsNot($token){
        $this->assertEquals(false, $this->individualTokenAnalyzer->isNativeElement($token));
    }

    public function isNativeElementException(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->isNativeElement(null);
    }

    public function getGlobals(){
        $data = [];
        foreach (Rules::globals() as $global) {
            $data[] = [new Token(1, T_VARIABLE, $global, 1, '', '', 1)];
        }
        return $data;
    }

    /** @dataProvider getGlobals */
    public function testContainsGlobalContains($token){
        $this->assertEquals(true, $this->individualTokenAnalyzer->ifContainsGlobal($token));
    }

    public function testIfContainsGlobals(){
        $this->expectException(\Exception::class);
        $this->individualTokenAnalyzer->ifContainsGlobal(null);
    }

    public function provideVariables(){
        $variables = ['$POST', 'GET', '_$SESSION', '$SESSION'];
        $data = [];
        foreach ($variables as $global) {
            $data[] = [new Token(1, T_VARIABLE, $global, 1, '', '', 1)];
        }
        return $data;
    }

    /** @dataProvider provideVariables */
    public function testContainsGlobalNonGlobal($token){
        $this->assertEquals(false, $this->individualTokenAnalyzer->ifContainsGlobal($token));
    }

    public function testContainsDeprecatedWarning(){
        $data = [
            'call_user_method' => "This method is deprecated. Suggested: " . 'call_user_func()',
            'call_user_method_array' => "This method is deprecated. Suggested: " . 'call_user_func_array()',
            'define_syslog_variables' => "This method is deprecated. ",
            'dl' => "This method is deprecated. ",
            'ereg' => "This method is deprecated. Suggested: " . 'preg_match()',
            'ereg_replace' => "This method is deprecated. Suggested: " . 'preg_replace()',
            'eregi' => "This method is deprecated. Suggested: " . 'preg\_match with the \'i\' modifier',
            'eregi_replace' => "This method is deprecated. Suggested: " . 'preg\_replace with the \'i\' modifier',
            'mcrypt_generic_end' => "This method is deprecated. ",
            'set_magic_quotes_runtime' => "This method is deprecated. ",
            'magic_quotes_runtime' => "This method is deprecated. ",
            'session_register' => "This method is deprecated. Suggested: " . '$\_SESSION',
            'session_unregister' => "This method is deprecated. Suggested: " . '$\_SESSION',
            'session_is_registered' => "This method is deprecated. Suggested: " . '$\_SESSION',
            'set_socket_blocking' => "This method is deprecated. Suggested: " . 'stream\_set\_blocking',
            'split' => "This method is deprecated. Suggested: " . 'preg\_split',
            'spliti' => "This method is deprecated. Suggested: " . 'preg\_split with the \'i\' modifier',
            'sql_regcase' => "This method is deprecated. ",
            'mysql_db_query' => "This method is deprecated. Suggested: " . 'mysql\_select\_db and mysql\_query',
            'mysql_escape_string' => "This method is deprecated. Suggested: " . 'mysql\_real\_escape\_string',
            'mysql_list_dbs' => "This method is deprecated. ",
            'datefmt_set_timezone_id' => "This method is deprecated. Suggested: " . 'datefmt\_set\_timezone',
            'mcrypt_cbc' => "This method is deprecated. ",
            'mcrypt_cfb' => "This method is deprecated. ",
            'mcrypt_ecb' => "This method is deprecated. ",
            'mcrypt_ofb' => "This method is deprecated. ",
            'ldap_sort' => "This method is deprecated. ",
        ];

        foreach ($data as $method => $solution) {
            $token = new Token(1, T_STRING, $method, 1, "T_STRING", "", 1);
            $this->assertEquals($solution, $this->individualTokenAnalyzer->containsDeprecated($token));
            $this->expectException(\Exception::class);
            $this->assertEquals($solution, $this->individualTokenAnalyzer->containsDeprecated(null));
        }
    }

    public function provideNonDeprecatedMethods(){
        return [
            ['get_defined_functions'],
            ['mcrypt_ecb_test'],
        ];
    }

    /** @dataProvider provideNonDeprecatedMethods */
    public function testContainsDeprecated($methodsName){
        $token = new Token(1, T_STRING, $methodsName, 1, "T_STRING", "", 1);
        $this->assertEquals(null, $this->individualTokenAnalyzer->containsDeprecated($token));
    }

}
