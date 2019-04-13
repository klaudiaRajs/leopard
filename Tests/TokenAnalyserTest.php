<?php

namespace MyApp\Tests;

use MyApp\Analyzer\Token;
use MyApp\Analyzer\TokenAnalyser;
use MyApp\Statistics\StatKeeper;
use PHPUnit\Framework\TestCase;

class TokenAnalyserTest extends TestCase{

    /** isPrimitiveType */

    public function testIsPrimitiveTypeInt(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "int";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(true, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeIntUpperCase(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "Int";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(false, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeFloat(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "float";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(true, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeFloatUpper(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "Float";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(false, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeString(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "string";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(true, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeStringUpper(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "String";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(false, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeBool(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "bool";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(true, $analyzer->isPrimitiveType($token));
    }

    public function testIsPrimitiveTypeBoolUpper(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $testPrimitive = "Boolean";
        $token = new Token(1, T_STRING, $testPrimitive, 1, "T_STRING", "", 1);

        $this->assertEquals(false, $analyzer->isPrimitiveType($token));
    }

    /** containsStatics */

    public function testContainsStatics(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $staticToTest = "static";
        $token = new Token(1, T_STRING, $staticToTest, 1, "T_STRING", "", 1);

        $this->assertEquals(false, $analyzer->containsStatics($token));
    }

    public function testContainsStaticsValid(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $staticToTest = "static";
        $token = new Token(1, T_STATIC, $staticToTest, 1, "T_STATIC", "", 1);

        $this->assertEquals("Shouldn't use statics if it's not absolutely necessary. ", $analyzer->containsStatics($token));
    }

    /** containsDeprecated */

    public function testContainsDeprecated(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);

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
            $this->assertEquals($solution, $analyzer->containsDeprecated($token));

        }
    }

    /** containsGlobal */

    public function testContainsGlobals(){

        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $data = [
            '$_SESSION', '$_POST', '$_GET', '$_FILES', '$_SERVER', '$_COOKIE', '$_ENV', '$_REQUEST', '$GLOBALS'
        ];

        foreach ($data as $global) {
            $token = new Token(1, T_VARIABLE, $global, 1, "T_VARIABLE", "", 1);
            $this->assertEquals("Shouldn't use global variables. ", $analyzer->containsGlobal($token));
        }
    }

    public function testContainsGlobalsInvalid(){
        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $data = [
            'SESSION', '$POST', '$_get', '$files', '_SERVER', 'cookie', '$globals', '$_GLOBALS'
        ];

        foreach ($data as $global) {
            $token = new Token(1, T_VARIABLE, $global, 1, "T_VARIABLE", "", 1);
            $this->assertEquals(null, $analyzer->containsGlobal($token));
        }
    }

    /** isNative */

    public function testIsNative(){

        $analyzer = new TokenAnalyser(new StatKeeper(), "testFileName.php", 1);
        $fileContents = file_get_contents('C:\xampp\htdocs\projects\LeopardSlim\Tests\internalFunctions.txt.');
        $data = explode(',', $fileContents) ;

        foreach ($data as $internalFunction) {
            $token = new Token(1, T_STRING, $internalFunction, 1, "T_STRING", "", 1);
            $this->assertEquals(true, $analyzer->isNative($token));
        }
    }
}
