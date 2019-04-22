<?php

namespace MyApp\Tests;

use MyApp\Analyzer\IndividualTokenAnalyzer;
use MyApp\Analyzer\NameConventionAnalyzer;
use MyApp\Analyzer\Rules;
use MyApp\Analyzer\Token;
use MyApp\Analyzer\TokenInContextAnalyzer;
use MyApp\Helpers\TokenHelper;
use PHPUnit\Framework\TestCase;

class NameConventionAnalyzerTest extends TestCase{

    /** @var NameConventionAnalyzer $nameConventionAnalyzer */
    //    protected $nameConventionAnalyzer;

    public function setUp(){
        //        $data = [
        //            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
        //            1 => new Token(1, T_STRING, 'ObjectType', 1, '', '', 1),
        //            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        //        ];
        //        $tokenHelper = new TokenHelper($data);
        //        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        //        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        //        $this->nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);
    }

    public function providePrimitiveTypes(){
        $data = [];
        foreach (Rules::PRIMITIVE_TYPES as $type) {
            $data[] = [new Token(1, T_STRING, $type, 1, '', '', 1)];
        }
        return $data;
    }

    /** @dataProvider providePrimitiveTypes */
    public function testIsPrimitiveTypeAffirmative($token){
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
            1 => new Token(1, T_STRING, 'ObjectType', 1, '', '', 1),
            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        ];
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);
        $this->assertEquals(true, $nameConventionAnalyzer->isPrimitiveType($token));
    }

    public function provideNonPrimitiveTypes(){
        return [
            [new Token(1, T_STRING, "NameConventionAnalyzer", 1, '', '', 1)],
            [new Token(1, T_STRING, "LongestRepeatedChains", 1, '', '', 1)],
        ];
    }

    /** @dataProvider provideNonPrimitiveTypes */
    public function testIsPrimitiveTypeNonPrimitives($token){
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
            1 => new Token(1, T_STRING, 'ObjectType', 1, '', '', 1),
            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        ];
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);
        $this->assertEquals(false, $nameConventionAnalyzer->isPrimitiveType($token));
    }

    public function provideTestDataForNamingConvention(){
        return [
            ['camelCase',
                new Token(1, T_VARIABLE, '$camelCaseVariable', 1, '', '', 1),
                [
                    1 => new Token(1, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(2, T_VARIABLE, '$PascalConventionVariable', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            [   'underscore',
                new Token(3, T_VARIABLE, '$underscore_convention', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(4, T_VARIABLE, '$_GET', 1, '', '', 1),
                [
                16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
            ]
            ],
            ['camelCase',
                new Token(5, T_VARIABLE, '$_GET', 1, '', '', 1),
                [
                16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
            ]
            ],
            ['underscore',
                new Token(6, T_VARIABLE, '$_GET', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['underscore',
                new Token(7, T_CONSTANT_ENCAPSED_STRING, 'CONSTANT_NAME', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['underscore',
                new Token(8, T_CONSTANT_ENCAPSED_STRING, '$anotherVariable', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['camelCase',
                new Token(9, T_CONSTANT_ENCAPSED_STRING, '/** Comment */', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(10, T_CONSTANT_ENCAPSED_STRING, '//Comment', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(11, T_STRING, '__construct', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(12, T_STRING, 'self', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(13, T_STRING, 'true', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(14, T_VARIABLE, '$this', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(15, T_VARIABLE, 'strlen', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'class', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'ClassName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, '{', 1, '', '', 1),
                    19 => new Token(19, T_VARIABLE, '$a', 1, '', '', 1),
                    20 => new Token(20, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    21 => new Token(21, T_NEW, 'new', 1, '', '', 1),
                    22 => new Token(22, T_STRING, 'ObjectType', 1, '', '', 1),
                    23 => new Token(23, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                    24 => new Token(24, T_VARIABLE, '$abc', 1, '', '', 1),
                    25 => new Token(25, Token::BRACKET_CLOSE, ')', 1, '', '', 1),
                ]
            ],
            ['camelCase',
                new Token(17, T_STRING, 'NameSpaceName', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'namespace', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'NameSpaceName', 1, '', '', 1),
                    18 => new Token(18, T_CURLY_OPEN, 'class', 1, '', '', 1),
                ]
            ],
            ['camelCase',
                new Token(17, T_STRING, 'CONST_NAME', 1, '', '', 1),
                [
                    16 => new Token(16, T_CONST, 'const', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'CONST_NAME', 1, '', '', 1),
                    18 => new Token(18, Token::ASSIGNMENT, '=', 1, '', '', 1),
                    19 => new Token(19, T_STRING, 'stringValueOfConstant', 1, '', '', 1),
                ]
            ],
            ['camelCase',
                new Token(19, T_STRING, 'NameSpaceName', 1, '', '', 1),
                [
                    16 => new Token(16, T_CLASS, 'use', 1, '', '', 1),
                    17 => new Token(17, T_STRING, 'NameSpaceName', 1, '', '', 1),
                    18 => new Token(18, T_NS_SEPARATOR, "\\", 1, '', '', 1),
                    19 => new Token(19, T_STRING, 'OtherNameSpace', 1, '', '', 1),
                    20 => new Token(20, Token::SEMICOLON, ';', 1, '', '', 1),
                    21 => new Token(21, T_CURLY_OPEN, 'class', 1, '', '', 1),
                ]
            ],
            ['underscore',
                new Token(18, T_STRING, '__construct', 1, '', '', 1),
                [
                    16 => new Token(16, T_PUBLIC, 'public', 1, '', '', 1),
                    17 => new Token(17, T_FUNCTION, 'function', 1, '', '', 1),
                    18 => new Token(18, T_STRING, "__construct", 1, '', '', 1),
                    19 => new Token(19, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                ]
            ],
            ['Pascal',
                new Token(19, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                [
                    16 => new Token(16, T_PUBLIC, 'public', 1, '', '', 1),
                    17 => new Token(17, T_FUNCTION, 'function', 1, '', '', 1),
                    18 => new Token(18, T_STRING, "__construct", 1, '', '', 1),
                    19 => new Token(19, Token::BRACKET_OPEN, '(', 1, '', '', 1),
                ]
            ],
        ];
    }

    /** @dataProvider provideTestDataForNamingConvention */
    public function testCheckIfNamingConventionFollowed($namingConvention, $token, $data){
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);
        Rules::setNamingConvention($namingConvention);
        $this->assertEquals(null, $nameConventionAnalyzer->checkIfNamingConventionFollowed($token), $token->tokenHash);
    }

    public function testCheckIfNamingConventionFollowedPrimitiveTypeCase(){
        $token = new Token(1, T_STRING, 'int', 1, '', '', 1);
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
            1 => $token,
            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        ];

        Rules::setNamingConvention("Pascal");
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(null, $nameConventionAnalyzer->checkIfNamingConventionFollowed($token));
    }

    public function testCheckNamingConventionFollowedStaticCall(){
        $data = [
            0 => new Token(0, T_STRING, "firstWord", 1, '', '', ''),
            1 => new Token(1, T_STRING, "StaticClass", 1, '', '', ''),
            2 => new Token(2, T_DOUBLE_COLON, "::", 1, '', '', ''),
            3 => new Token(3, T_VARIABLE, '$fourth_word', 1, 'T_VARIABLE', '', ''),
        ];

        Rules::setNamingConvention("underscore");
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(null, $nameConventionAnalyzer->checkIfNamingConventionFollowed($data[3]), ($data[3]->content));
    }

    public function testCheckNamingConventionFollowedObjectCall(){
        $data = [
            0 => new Token(0, T_NEW, "new", 1, 'T_NEW', '', ''),
            1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
            2 => new Token(2, T_STRING, "Book", 1, '', '', ''),
            3 => new Token(3, T_WHITESPACE, ' ', 1, '', '', ''),
            4 => new Token(3, Token::BRACKET_OPEN, '(', 1, '', '', ''),
        ];

        Rules::setNamingConvention("underscore");
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(null, $nameConventionAnalyzer->checkIfNamingConventionFollowed($data[2]));
    }

    public function provideNotCorrectNamingConventionData(){
        return [
            ['camelCase', new Token(1, T_VARIABLE, '$PascalName', 1, 'T_VARIABLE', '', 1), Rules::CAMEL_CASE_WARNING],
            ['Pascal', new Token(1, T_VARIABLE, '$camelCaseName', 1, 'T_VARIABLE', '', 1), Rules::PASCAL_CONVENTION_WARNING],
            ['underscore', new Token(1, T_VARIABLE, '$camelCase', 1, 'T_VARIABLE', '', 1), Rules::UNDERSCORE_CONVENTION_WARNING],
            ['Pascal', new Token(1, T_VARIABLE, '$underscore_naming', 1, 'T_VARIABLE', '', 1), Rules::PASCAL_CONVENTION_WARNING],
            ['camelCase', new Token(1, T_VARIABLE, '$underscore_convention', 1, 'T_VARIABLE', '', 1), Rules::CAMEL_CASE_WARNING],
            ['underscore', new Token(1, T_VARIABLE, '$PascalName', 1, 'T_VARIABLE', '', 1), Rules::UNDERSCORE_CONVENTION_WARNING],
        ];
    }

    /** @dataProvider provideNotCorrectNamingConventionData */
    public function testCheckNamingConventionFollowedWarning($namingConvention, $token, $warning){
        Rules::setNamingConvention($namingConvention);
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
            1 => new Token(1, T_STRING, 'ObjectType', 1, '', '', 1),
            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        ];
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);
        $this->assertEquals($warning, $nameConventionAnalyzer->checkIfNamingConventionFollowed($token), ($namingConvention . ", " . $token->content));
    }

    public function testCheckNamingConventionFollowedObjectCallWarning(){
        $data = [
            0 => new Token(0, T_NEW, "new", 1, 'T_NEW', '', ''),
            1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
            2 => new Token(2, T_STRING, "book", 1, 'T_STRING', '', ''),
            3 => new Token(3, T_WHITESPACE, ' ', 1, 'T_WHITESPACE', '', ''),
            4 => new Token(3, Token::BRACKET_OPEN, '(', 1, 'BRACKET_OPEN', '', ''),
        ];

        Rules::setNamingConvention("underscore");
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(Rules::PASCAL_CONVENTION_WARNING, $nameConventionAnalyzer->checkIfNamingConventionFollowed($data[2]));
    }

    public function testCheckNamingConventionFollowedStaticCallWarning(){
        $data = [
            0 => new Token(0, T_STRING, "firstWord", 1, '', '', ''),
            1 => new Token(1, T_STRING, "staticClass", 1, '', '', ''),
            2 => new Token(2, T_DOUBLE_COLON, "::", 1, '', '', ''),
            3 => new Token(3, T_VARIABLE, '$fourth_word', 1, '', '', ''),
        ];

        Rules::setNamingConvention("underscore");
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(Rules::PASCAL_CONVENTION_WARNING, $nameConventionAnalyzer->checkIfNamingConventionFollowed($data[1]));
    }

    public function testCheckNamingConventionFollowedObjectTypeWarning(){
        $data = [
            0 => new Token(0, Token::BRACKET_OPEN, '(', 1, '', '', 1),
            1 => new Token(1, T_STRING, 'objectType', 1, '', '', 1),
            2 => new Token(2, T_VARIABLE, '$objectInstance', 1, '', '', 1),
        ];
        $tokenHelper = new TokenHelper($data);
        $individualTokenAnalyzer = new IndividualTokenAnalyzer($tokenHelper);
        $tokenInContextAnalyzer = new TokenInContextAnalyzer($individualTokenAnalyzer, $data, $tokenHelper);
        $nameConventionAnalyzer = new NameConventionAnalyzer($individualTokenAnalyzer, $tokenInContextAnalyzer, $data);

        $this->assertEquals(Rules::PASCAL_CONVENTION_WARNING, $nameConventionAnalyzer->checkIfNamingConventionFollowed($data[1]));
    }
}
