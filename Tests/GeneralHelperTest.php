<?php

namespace MyApp\Tests;

use MyApp\Analyzer\Token;
use MyApp\Helpers\GeneralHelper;
use PHPUnit\Framework\TestCase;

class GeneralHelperTest extends TestCase{

    public function testChangeFunctionToString(){

        $data = [
            0 => new Token(1, T_STRING, "firstWord", 1, 'T_STRING', '', ''),
            1 => new Token(1, T_STRING, "secondWord", 1, 'T_STRING', '', ''),
            2 => new Token(1, T_STRING, "thirdWord", 1, 'T_STRING', '', ''),
            3 => new Token(1, T_STRING, "fourthWord", 1, 'T_STRING', '', ''),
        ];

        $expected = "firstWord secondWord thirdWord fourthWord ";
        $actual = GeneralHelper::changeFunctionToString($data);
        $this->assertEquals($expected, $actual);
    }

    public function testGetAverage(){

        $data = [
            [
                'sum' => 0,
                'metrics' => 0,
                'average' => 0,
            ], [
                'sum' => 1,
                'metrics' => 1,
                'average' => 1,
            ], [
                'sum' => 10,
                'metrics' => 2,
                'average' => 5,
            ], [
                'sum' => 11,
                'metrics' => 2,
                'average' => 5.5,
            ], [
                'sum' => -5,
                'metrics' => 10,
                'average' => 0,
            ], [
                'sum' => 100000000000000,
                'metrics' => 2,
                'average' => 50000000000000,
            ], [
                'sum' => 0.4,
                'metrics' => 2,
                'average' => 0.2,
            ], [
                'sum' => 232.496722409289,
                'metrics' => 3,
                'average' => 77.498907469763,
            ],
        ];

        foreach ($data as $datum) {
            $this->assertEquals($datum['average'], GeneralHelper::getAverage($datum['sum'], $datum['metrics']));
        }
    }

    public function testGetSumOfPercentageResults(){

        $data = [
            [
                'metrics' =>
                    ['pureTextSimilarity' => 1,
                        'returns' => 1,
                        'abstractedText' => 1,
                    ],
                'expected' => 3,
            ], [
                'metrics' =>
                    ['pureTextSimilarity' => -5,
                        'returns' => -5,
                        'abstractedText' => 1,
                    ],
                'expected' => 0,
            ],[
                'metrics' =>
                    ['pureTextSimilarity' => 100,
                        'returns' => 86,
                        'abstractedText' => 42,
                    ],
                'expected' => 228,
            ],
        ];

        foreach ($data as $datum) {
            $this->assertEquals($datum['expected'], GeneralHelper::getSumOfPercentageResults($datum['metrics']));
        }
    }

    public function provideConvertIntToCharCode(){
        return [
            [0, 'A'],
            [1, 'B'],
            [2, 'C'],
            [-1, 'A'],
            [-100, 'A'],
            [100, 'z'],
        ];
    }

    /** @dataProvider provideConvertIntToCharCode */
    public function testConvertIntoToCharCode($k, $expected){
        $this->assertEquals($expected, GeneralHelper::convertIntToCharCode($k));
    }
}
