<?php


class TokenPresenter{
    /** @param token[] $tokens
     * @return string
     */
    static public function getFormattedContents(array $tokens)
    {
        $result = "";

        $lastLine = 1;
        $tokensForLine = [];
        foreach ($tokens as $token) {
            if ($token->tokenIdx == T_WHITESPACE && preg_match('/[\n]+/', $token->content)) {
                if (!empty($tokensForLine)) {
                    $result .= self::getFormattedLine($token->lineNumber, $tokensForLine);
                    $lastLine = $token->lineNumber;
                    $tokensForLine = [];
                }

                //show additional empty lines
                $lines = explode("\n", $token->content);
                foreach ($lines as $index => $line) {
                    //one line is added by default by getFormattedLine
                    if ($index == 0) {
                        continue;
                    }
                    //if last line is empty we skip it, if it has spaces/tabs we add the
                    if ($index == count($lines) - 1 && empty($line)) {
                        continue;
                    }

                    if (empty($line)) {
                        $result .= self::getFormattedLine($token->lineNumber + $index, []);
                        $lastLine = $token->lineNumber + $index;
                    } else {
                        $tokensForLine[] = new TokenView(T_WHITESPACE, $line, token_name(T_WHITESPACE), $token->tokenMessage);
                    }
                }
                continue;
            }

            //it is complex token with a new line
            if (strpos($token->content, "\n") !== false) {
                $lines = explode("\n", $token->content);
                foreach ($lines as $lineNumber => $lineContent) {
                    if (empty($lineContent)) {
                        continue;
                    }
                    //if this is first iteration there may be already something for this line so we just include the next token
                    $tokensForLine[] = new TokenView($token->tokenIdx, $lineContent, $token->tokenName, $token->tokenMessage);
                    $result .= self::getFormattedLine($token->lineNumber + $lineNumber, $tokensForLine);
                    $lastLine = $token->lineNumber + $lineNumber;
                    $tokensForLine = [];
                }
                continue;
            }

            $tokensForLine[] = TokenView::fromToken($token);
        }
        if (!empty($tokensForLine)) {
            $result .= self::getFormattedLine($lastLine + 1, $tokensForLine);
        }
        return $result;
    }

    static private function getFormattedLine($lineNumber, array $tokens)
    {
        $lineNumberFormat = '<span style="color:#a6a6a6; padding-right: 10px; border-right: 1px solid gray; margin-right: 5px;">%s</span>';
        $formattedLineNumber = sprintf($lineNumberFormat, str_pad($lineNumber, 3, ' ', STR_PAD_LEFT));

        $formattedTokens = '';
        foreach ($tokens as $token) {
            $formattedTokens .= self::getFormattedToken($token);
        }

        $formattedLine = "<pre style='margin: 0; padding: 0'>";
        $formattedLine .= $formattedLineNumber;
        $formattedLine .= $formattedTokens;
        $formattedLine .= "</pre>\n";
        return $formattedLine;
    }

    private static function getFormattedToken(TokenView $token)
    {
        $keywords = [T_ABSTRACT, 'and', T_ARRAY, T_BREAK, T_CALLABLE, T_CASE, T_CATCH, T_CLASS, T_CLONE,
            T_CONTINUE, T_DECLARE, T_DEFAULT, 'die', T_DO, T_ECHO, T_ELSE, T_ELSEIF, T_EMPTY, T_ENDDECLARE, T_ENDFOR,
            T_ENDFOREACH, T_ENDIF, T_ENDSWITCH, T_ENDWHILE, T_EVAL, T_EXIT, T_EXTENDS, T_FINAL, T_FOR, T_FOREACH,
            T_FUNCTION, T_GLOBAL, T_GOTO, T_IF, T_IMPLEMENTS, T_INCLUDE, T_INCLUDE_ONCE, T_INSTANCEOF, T_INSTEADOF,
            T_INTERFACE, T_ISSET, T_NAMESPACE, T_NEW, 'null', T_PRINT, T_PRIVATE, 'parent', T_PROTECTED, T_PUBLIC, T_REQUIRE,
            T_REQUIRE_ONCE, T_RETURN, T_STATIC, T_SWITCH, 'self', T_THROW, T_TRAIT, T_TRY, T_UNSET, T_USE, T_WHILE, 'xor'
        ];

        //choose colour
        $bold = false;
        $colour = '#A9B7C6';
        if (in_array($token->tokenIdx, $keywords)) {
            $colour = '#CB772F';
            $bold = true;
        }
        switch($token->tokenIdx) {
            case T_COMMENT:
            case T_DOC_COMMENT:
            case T_CONSTANT_ENCAPSED_STRING:
                $colour = '#619647';
                break;
            case token::SEMICOLON:
            case token::COMMA:
            case T_OPEN_TAG:
                $colour = '#CC7832';
                break;
            case T_STRING:
                if (preg_match('/true|false/i', $token->content)) {
                    $colour = '#CB772F';
                    $bold = true;
                }
                break;
            case T_VARIABLE:
                $colour = '#9876AA';
                break;
        }

        //choose text
        $text = htmlentities($token->content);
        $style = [];
        if ($colour) {
            $style[] = "color: $colour";
        }
        if ($bold) {
            $style[] = "font-weight: bold";
        }
        if ($token->tokenMessage){
            $style[] = "border-bottom: 1px solid red";
            $style[] = "font-size: 1.2em";
        }

        $name = $token->tokenName;

        if ($token->tokenMessage !== null ) {
            $name = $token->tokenName . " " . $token->tokenMessage;
        }

        return sprintf('<span style="%s;" title="%s">%s</span>', implode(';', $style), $name, $text);
    }
}
