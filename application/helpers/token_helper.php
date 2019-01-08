<?php



class token{
    const SEMICOLON = 'semicolon';
    const DOUBLE_QUOTE = 'doubleQuote';
    const SQUARE_BRACKET_OPEN = 'squareBracketOpen';
    const SQUARE_BRACKET_CLOSE = 'squareBracketClose';
    const CURLY_BRACKET_OPEN = 'curlyBracketOpen';
    const CURLY_BRACKET_CLOSE = 'curlyBracketClose';
    const BRACKET_OPEN = 'bracketOpen';
    const BRACKET_CLOSE = 'bracketClose';
    const ASSIGNMENT = '=';
    const EXCLAMATION_MARK = '!';
    const DOT = '.';
    const COMMA = ',';
    const QUESTION_MARK = '?';
    const COLON = ':';

    public $tokenIdx;
    public $content;
    public $lineNumber;
    public $tokenName;
    public $tokenMessage;

    public function __construct($tokenIdx, $content, $lineNumber, $tokenName, $tokenMessage)
    {
        $this->tokenIdx = $tokenIdx;
        $this->content = $content;
        $this->lineNumber = $lineNumber;
        $this->tokenName = $tokenName;
        $this->tokenMessage = $tokenMessage;
    }
}