<?php
/**
 * Created by PhpStorm.
 * User: Klaudia Rajs
 * Date: 14.04.2019
 * Time: 15:59
 */

namespace MyApp\Tests;

use MyApp\Analyzer\IndividualTokenAnalyzer;
use MyApp\Analyzer\Rules;
use MyApp\Analyzer\Token;
use MyApp\Analyzer\TokenInContextAnalyzer;
use MyApp\Helpers\TokenHelper;
use PHPUnit\Framework\TestCase;

class TokenInContextAnalyzerTest extends TestCase{

    /** @var TokenInContextAnalyzer $tokenInContextAnalyzer */
    private $tokenInContextAnalyzer;
    private $individualTokenAnalyzer;
    private $tokenHelper;
    private $tokens;

    public function setUp(){
        $tokens = [];
        $this->tokens = $tokens;
        $this->tokenHelper = new TokenHelper($this->tokens);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->tokenHelper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $this->tokens, $this->tokenHelper);
    }

    public function providePublicFunctions(){
        return [
            [
                [
                    0 => new Token(0, T_PUBLIC, "public", 1, 'T_PUBLIC', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_FUNCTION, "function", 1, 'T_FUNCTION', '', ''),
                    3 => new Token(3, T_STRING, "FunctionName", 1, 'T_STRING', '', ''),
                    4 => new Token(4, T_WHITESPACE, ' ', 1, 'T_WHITESPACE', '', ''),
                    5 => new Token(5, Token::BRACKET_OPEN, '(', 1, '(', '', ''),
                ],
                new Token(3, T_STRING, "FunctionName", 1, '', '', ''),
                true,
            ],
            [
                [
                    0 => new Token(0, T_PUBLIC, "public", 1, 'T_PUBLIC', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_FUNCTION, "function", 1, 'T_FUNCTION', '', ''),
                    3 => new Token(3, T_STRING, 'functionName', 1, 'T_STRING', '', ''),
                    4 => new Token(4, Token::BRACKET_OPEN, '(', 1, '(', '', ''),
                ],
                new Token(3, T_STRING, "functionName", 1, '', '', ''),
                true,
            ],
            [
                [
                    0 => new Token(0, T_PRIVATE, "private", 1, 'T_PRIVATE', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_FUNCTION, "function", 1, 'T_FUNCTION', '', ''),
                    3 => new Token(3, T_STRING, 'functionName', 1, 'T_STRING', '', ''),
                    4 => new Token(4, Token::BRACKET_OPEN, '(', 1, '(', '', ''),
                ],
                new Token(3, T_STRING, "functionName", 1, '', '', ''),
                false,
            ],
        ];
    }

    /** @dataProvider providePublicFunctions */
    public function testIsPublicFunction($tokens, $analyzedToken, $expected){
        $tokenHelper = new TokenHelper($tokens);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $tokens, $tokenHelper);
        $this->assertEquals($expected, $tokenInContextAnalyzer->isPublicFunction($analyzedToken->tokenHash));
    }


    public function testIsUnusedVariable(){
        $data = [
            //Excluded
            0 => new Token(0, T_VARIABLE, '$this', 1, 'T_VARIABLE', '', 1, 'class1'),
            //Unused
            1 => new Token(1, T_VARIABLE, '$abcd', 1, 'T_VARIABLE', '', 1, 'class1'),
            //Field variable
            2 => new Token(2, T_VARIABLE, '$this', 1, 'T_VARIABLE', '', 1, 'class1'),
            3 => new Token(3, T_OBJECT_OPERATOR, '->', 1, 'T_OBJECT_OPERATOR', '', 1, 'class1'),
            4 => new Token(4, T_STRING, 'abc', 1, 'T_STRING', '', 1, 'class1'),
            //Used variable
            5 => new Token(5, T_VARIABLE, '$test', 1, 'T_VARIABLE', '', 1, 'class1', 'function1'),
            6 => new Token(6, T_VARIABLE, '$test', 1, 'T_VARIABLE', '', 1, 'class1', 'function1'),
            //Used without class and function
            7 => new Token(7, T_VARIABLE, '$nonObjectOriented', 1, 'T_VARIABLE', '', 1),
            8 => new Token(8, T_VARIABLE, '$nonObjectOriented', 1, 'T_VARIABLE', '', 1),
            //Class field call from different classes
            9 => new Token(9, T_VARIABLE, '$test2', 1, 'T_VARIABLE', '', 1, 'class1'),
            10 => new Token(10, T_VARIABLE, '$this', 1, 'T_VARIABLE', '', 1, 'class1'),
            11 => new Token(11, T_OBJECT_OPERATOR, '->', 1, 'T_OBJECT_OPERATOR', '', 1, 'class1'),
            12 => new Token(12, T_STRING, 'test2', 1, 'T_STRING', '', 1, 'class1'),

        ];

        $this->tokenHelper = new TokenHelper($data);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->tokenHelper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $data, $this->tokenHelper);

        $this->assertEquals(null, $this->tokenInContextAnalyzer->isUnusedVariable($data[0]));
        $this->assertEquals(Rules::UNUSED_VARIABLE_WARNING, $this->tokenInContextAnalyzer->isUnusedVariable($data[1]));
        $this->assertEquals(null, $this->tokenInContextAnalyzer->isUnusedVariable($data[2]));
        $this->assertEquals(null, $this->tokenInContextAnalyzer->isUnusedVariable($data[5]));
        $this->assertEquals(null, $this->tokenInContextAnalyzer->isUnusedVariable($data[7]));
        $this->assertEquals(null, $this->tokenInContextAnalyzer->isUnusedVariable($data[9]));
    }


    public function testIsTypeVariableInParam(){
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, 'functionName', '', '', '', 1),
            1 => new Token(1, Token::BRACKET_OPEN, '(', '', '', '', 1),
            2 => new Token(2, T_STRING, 'VariableType', 1, '', '', 1),
            3 => new Token(3, T_VARIABLE, '$var', 1, '', '', 1),
            4 => new Token(4, Token::COMMA, ',' . 1, '', '', '', 1),
        ];

        $this->tokenHelper = new TokenHelper($data);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->tokenHelper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $this->tokens, $this->tokenHelper);
        $this->assertEquals(true, $this->tokenInContextAnalyzer->isType($data[2]));
    }

    public function testIsTypeNewObjectInitialization(){
        $data = [
            0 => new Token(0, T_NEW, "new", 1, 'T_NEW', '', ''),
            1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
            2 => new Token(2, T_STRING, "Book", 1, '', '', ''),
            3 => new Token(3, T_WHITESPACE, ' ', 1, '', '', ''),
            4 => new Token(3, Token::BRACKET_OPEN, '(', 1, '', '', ''),
        ];


        $this->tokenHelper = new TokenHelper($data);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->tokenHelper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $this->tokens, $this->tokenHelper);
        $this->assertEquals(true, $this->tokenInContextAnalyzer->isType($data[2]));
    }

    public function provideNamespaceImport(){
        return [
            [
                [
                    0 => new Token(0, T_WHITESPACE, " ", 1, 'T_NEW', '', ''),
                    1 => new Token(1, T_USE, "use", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "Book", 1, '', '', ''),
                    3 => new Token(3, T_NS_SEPARATOR, '/', 1, '', '', ''),
                    4 => new Token(3, T_STRING, 'Namespace', 1, '', '', ''),
                ],
                new Token(2, T_STRING, "Book", 1, '', '', ''),
                true,
            ],
            [
                [
                    0 => new Token(0, T_WHITESPACE, "namespace", 1, 'T_NEW', '', ''),
                    1 => new Token(1, T_USE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "Book", 1, '', '', ''),
                    3 => new Token(3, T_NS_SEPARATOR, '/', 1, '', '', ''),
                    4 => new Token(3, T_STRING, 'Namespace', 1, '', '', ''),
                ],
                new Token(2, T_STRING, "Book", 1, '', '', ''),
                true,
            ],
            [
                [
                    2 => new Token(2, T_STRING, "Book", 1, '', '', ''),
                    4 => new Token(3, T_STRING, 'Namespace', 1, '', '', ''),
                ],
                new Token(2, T_STRING, "Book", 1, '', '', ''),
                false,
            ],
        ];
    }

    /** @dataProvider provideNamespaceImport */
    public function testIsTypeNamespaceImport($tokens, $analyzedToken, $answer){
        $this->tokenHelper = new TokenHelper($tokens);
        $this->individualTokenAnalyzer = new IndividualTokenAnalyzer($this->tokenHelper);
        $this->tokenInContextAnalyzer = new TokenInContextAnalyzer($this->individualTokenAnalyzer, $this->tokens, $this->tokenHelper);
        $this->assertEquals($answer, $this->tokenInContextAnalyzer->isType($analyzedToken));
    }
}
