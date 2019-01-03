<?php


class TokenView {
    public $tokenIdx;
    public $tokenContent;
    public $tokenName;
    public $tokenMessage;

    public function __construct($tokenIdx, $content, $tokenName, $tokenMessage)
    {
        $this->tokenIdx = $tokenIdx;
        $this->content = $content;
        $this->tokenName = $tokenName;
        $this->tokenMessage = $tokenMessage;
    }

    public static function fromToken(token $token)
    {
        return new TokenView($token->tokenIdx, $token->content, $token->tokenName, $token->tokenMessage);
    }
}