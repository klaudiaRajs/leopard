<?php

namespace MyApp\Analyzer;

class Tokenizer{
    /** @var token[] */
    private $tokens;

    public function __construct($fileContents){
        $unifiedContents = str_replace("\r", '', $fileContents);
        $this->parse($unifiedContents);
    }

    public function getAll(){
        return $this->tokens;
    }

    private function parse($fileContents){
        $additional = [
            ';' => Token::SEMICOLON,
            '"' => Token::DOUBLE_QUOTE,
            '[' => Token::SQUARE_BRACKET_OPEN,
            ']' => Token::SQUARE_BRACKET_CLOSE,
            '{' => Token::CURLY_BRACKET_OPEN,
            '}' => Token::CURLY_BRACKET_CLOSE,
            '(' => Token::BRACKET_OPEN,
            ')' => Token::BRACKET_CLOSE,
            '=' => Token::ASSIGNMENT,
            '!' => Token::EXCLAMATION_MARK,
            '.' => Token::DOT,
            ',' => Token::COMMA,
            '?' => Token::QUESTION_MARK,
            ':' => Token::COLON,
            '<' => Token::SMALLER,
            '>' => Token::BIGGER,
            '/' => Token::DIVISION,
            '\\' => Token::BACKSLASH,
            '+' => Token::ADD,
            '*' => Token::MULTIPLICATION,
            '-' => Token::MINUS,
            '&' => Token::AMPERSAND,
        ];
        $tokens = token_get_all($fileContents);
        $lastLine = 1;

        foreach ($tokens as $idx => $token) {
            if (is_array($token)) {
                $this->tokens[] = new Token($idx, $token[0], $token[1], $token[2], token_name($token[0]), null, null, null, null, null, null);
                $lastLine = $token[2];
            } elseif (isset($additional[$token])) {
                if (strpos($token, "\n")) {
                    print_r($token);
                    exit;
                }
                $this->tokens[] = new Token($idx, $additional[$token], $token, $lastLine, $additional[$token], null, null, null, null, null, null);
            } else {
                print_r($token);
                exit;
            }
        }
    }
}
