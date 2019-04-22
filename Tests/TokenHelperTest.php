<?php

namespace MyApp\Tests;

use MyApp\Analyzer\Token;
use MyApp\Helpers\TokenHelper;
use PHPUnit\Framework\TestCase;

class TokenHelperTest extends TestCase{


    public function testIsVariablePartOfStaticCall(){
        $data = [
            0 => new Token(0, T_STRING, "firstWord", 1, '', '', ''),
            1 => new Token(1, T_VARIABLE, " ", 1, '', '', ''),
            2 => new Token(2, T_DOUBLE_COLON, "::", 1, '', '', ''),
            3 => new Token(3, T_VARIABLE, "fourthWord", 1, '', '', ''),
        ];
        $tokenHelper = new TokenHelper($data);
        $this->assertEquals(true, $tokenHelper->isVariablePartOfStaticCall($data[3]));
        $this->assertEquals(false, $tokenHelper->isVariablePartOfStaticCall($data[1]));
        $this->assertEquals(false, $tokenHelper->isVariablePartOfStaticCall($data[2]));
    }

    public function provideGetNextNonWhitespaceToken(){
        return [
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                0,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                1,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                2,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1),
                3,
            ],
        ];
    }

    /** @dataProvider provideGetNextNonWhitespaceToken */
    public function testGetNextNonWhitespaceToken($data, $expected, $counter ){
        $tokenHelper = new TokenHelper($data);
        $this->assertEquals($expected, $tokenHelper->getNextNonWhitespaceToken($counter));
    }

    /** @dataProvider provideGetNextNonWhitespaceToken */
    public function testGetNextNonWhitespaceTokenStatic($data, $expected, $counter){
        $this->assertEquals($expected, TokenHelper::getNextNonWhitespaceTokenStaticAccess($counter, $data));
    }

    public function provideGetPreviousNonWhitespaceToken(){
        return [
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(1, Token::END_OF_FILE, "", 0, "END OF FILE", "", 1),
                0,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                1,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                2,
            ],
            [
                [
                    0 => new Token(0, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
                    1 => new Token(1, T_WHITESPACE, " ", 1, 'T_WHITESPACE', '', ''),
                    2 => new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                    3 => new Token(3, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
                ],
                new Token(2, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
                3,
            ],
        ];
    }

    /** @dataProvider provideGetPreviousNonWhitespaceToken */
    public function testGetPreviousNonWhitespaceToken($data, $expected, $counter){
        $tokenHelper = new TokenHelper($data);
        $this->assertEquals($expected, $tokenHelper->getPreviousNonWhitespaceToken($counter));
    }

    /** @dataProvider provideGetPreviousNonWhitespaceToken */
    public function testGetPreviousNonWhitespaceTokenStaticAccess($data, $expected, $counter){
        $this->assertEquals($expected, TokenHelper::getPreviousNonWhitespaceTokenStaticAccess($counter, $data));
    }
}
