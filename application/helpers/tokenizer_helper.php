<?php


class tokenizer{
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
            ';' => token::SEMICOLON,
            '"' => token::DOUBLE_QUOTE,
            '[' => token::SQUARE_BRACKET_OPEN,
            ']' => token::SQUARE_BRACKET_CLOSE,
            '{' => token::CURLY_BRACKET_OPEN,
            '}' => token::CURLY_BRACKET_CLOSE,
            '(' => token::BRACKET_OPEN,
            ')' => token::BRACKET_CLOSE,
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

    public function getTokenMessages($tokens){
        foreach ($tokens as $key => $token) {
            $tokens[$key]->tokenMessage = $this->containsStatics($token);
            $tokens[$key]->tokenMessage .= $this->containsDeprecated($token);
            $tokens[$key]->tokenMessage .= $this->containsGlobal($token);
            $tokens[$key]->tokenMessage .= $this->containsUnusedVariables($key, $token, $tokens);
        }

        //@TODO check what is length of a structure
        $tokens = $this->isTooLongStructure($tokens, 'T_FUNCTION', 10);
        $tokens = $this->isTooLongStructure($tokens, 'T_CLASS', 30);
        return $tokens;
    }

    private function containsDeprecated($token){
        $message = null;
        if ($token->tokenName == 'T_STRING') {
            foreach (rules_helper::deprecated() as $function => $solution) {
                if ($token->content == $function) {
                    if ($solution) {
                        $message = "This method is deprecated. Suggested: " . $solution;
                    } else {
                        $message = "This method is deprecated.";
                    }
                }
            }
        }
        return $message;
    }

    private function containsGlobal($token){
        if ($token->tokenName == 'T_VARIABLE' && in_array($token->content, rules_helper::globals())) {
            return "Shouldn't use global variables";
        }
        return null;
    }

    private function containsStatics($token){
        if ($token->tokenName == 'T_STATIC') {
            return "Shouldn't use statics if it's not absolutely necessary";
        }
        return null;
    }

    private function containsUnusedVariables($key, $token_, $tokens){
        $message = null;
        if ($token_->tokenName == "T_VARIABLE" && $this->containsGlobal($token_) == null) {
            $variable = $token_->content;
            foreach ($tokens as $tokenKey => $token) {
                $message = "This is unused variables";
                if ($token->tokenName == "T_VARIABLE" && $token->content == $variable && $tokenKey !== $key) {
                    $message = null;
                    break;
                }
            }
        }
        return $message;
    }

    private function isTooLongStructure($tokens, string $type, int $length){
        $startOfFunction = [];
        $curlyBracketOpen = 0;
        $curlyBracketClose = 0;
        $counter = 0;
        for( $i = 0; $i < count($tokens); $i++ ){
            if( $tokens[$i]->tokenName == $type ){
                $startOfFunction[$counter]['start'] = $tokens[$i]->lineNumber + 1;
                $startOfFunction[$counter]['i'] = $i;

                for( $j = $i; $j < count($tokens); $j++ ){
                    if($tokens[$j]->tokenName == token::CURLY_BRACKET_OPEN){
                        $curlyBracketOpen++;
                    }
                    if($tokens[$j]->tokenName == token::CURLY_BRACKET_CLOSE){
                        $curlyBracketClose++;
                    }
                    if( $curlyBracketOpen > 0 && $curlyBracketOpen == $curlyBracketClose ){
                        $startOfFunction[$counter]['end'] = $tokens[$j]->lineNumber;
                        break;
                    }
                }
                $counter++;
            }
            $curlyBracketOpen = 0;
            $curlyBracketClose = 0;
        }

        foreach( $startOfFunction as $data ){
            if( $data['end'] - $data['start'] > $length ){
                $tokens[$data['i']]->tokenMessage .= " This structure is too long";
            }
        }
        return $tokens;
    }

}