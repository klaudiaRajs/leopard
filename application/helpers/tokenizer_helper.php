<?php


class tokenizer{
    /** @var token[] */
    private $tokens;

    public function __construct($fileContents)
    {
        $unifiedContents = str_replace("\r", '', $fileContents);
        $this->parse($unifiedContents);
    }

    public function getAll()
    {
        return $this->tokens;
    }

    private function parse($fileContents)
    {
        $additional = [
            ';' => token::SEMICOLON,
            '"' => token::DOUBLE_QUOTE,
            '[' => token::SQUARE_BRACKET,
            ']' => token::SQUARE_BRACKET,
            '{' => token::CURLY_BRACKET,
            '}' => token::CURLY_BRACKET,
            '(' => token::BRACKET,
            ')' => token::BRACKET,
            '=' => token::ASSIGNMENT,
            '!' => token::EXCLAMATION_MARK,
            '.' => token::DOT,
            ',' => token::COMMA,
            '?' => token::QUESTION_MARK,
            ':' => token::COLON,
        ];
        $tokens = token_get_all($fileContents);
        $lastLine = 1;
        foreach ($tokens as $token) {
            if (is_array($token)) {
                $this->tokens[] = new token($token[0], $token[1], $token[2], token_name($token[0]), null);
                $lastLine = $token[2];
            } elseif (isset($additional[$token])) {
                if (strpos($token, "\n")) {
                    print_r($token);
                    exit;
                }
                $this->tokens[] = new token($additional[$token], $token, $lastLine, $additional[$token], null);
            } else {
                print_r($token);
                exit;
            }
        }
    }
}