<?php

namespace MyApp\Analyzer;

class TokenView {
    public $tokenIdx;
    public $tokenContent;
    public $tokenName;
    public $tokenMessage;
    public $tokenKey;

    public function __construct($tokenIdx, $content, $tokenName, $tokenMessage, $tokenKey = null)
    {
        $this->tokenIdx = $tokenIdx;
        $this->content = $content;
        $this->tokenName = $tokenName;
        $this->tokenMessage = $tokenMessage;
        $this->tokenKey = $tokenKey;
    }

    public static function fromToken(token $token)
    {
        return new TokenView($token->tokenIdentifier, $token->content, $token->tokenName, $token->tokenMessage, $token->tokenKey);
    }
}
