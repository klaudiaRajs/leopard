<?php

class SyntaxColourHelper{
    public static function colourSyntax($line)
    {
        $keywords = ['abstract', 'and', 'array', 'break', 'callable', 'case', 'catch', 'class', 'clone',
            'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
            'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'false', 'final', 'for', 'foreach',
            'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof',
            'interface', 'isset', 'namespace', 'new', 'null', 'print', 'private', 'parent', 'protected', 'public', 'require',
            'require_once', 'return', 'static', 'switch', 'self', 'throw', 'trait', 'try', 'true', 'unset', 'use', 'while', 'xor'
        ];

        if (strpos($line, "*") !== false && (strpos($line, "*") < 5 || strpos($line, "/**") < 5 || strpos($line, "*/") < 5)) {
            $line = self::markComment($line);
        } else {
            $line = self::markString($line);
            foreach ($keywords as $keyword) {
                if (strpos($line, $keyword) !== false) {
                    $line = self::markKeywords($keyword, $line);
                }
            }
            $line = self::markVariables($line);
            $line = self::markObjectProperties($line);
            $line = self::markCommentsForOneLine($line);
            $line = self::markCommas($line);
        }
        return $line;
    }

    public static function markComment($line)
    {
        return ('<span style="color:#619647;">' . $line . '</span>');
    }

    public static function markKeywords($keyword, $line)
    {
        if (self::keyWordInString($keyword, $line)) {
            return $line;
        }
        return self::getStructure('/' . $keyword . '\b/', $line, '#CB772F');
    }

    public static function keyWordInString($keyword, $line)
    {

        if (strpos($line, 'style="') !== (strpos($line, '"') - 6)) {
            if (strpos($line, '"') < strpos($line, $keyword) && strpos($line, $keyword) < strpos($line, '"', strpos($line, $keyword)    )) {
                return false;
            }
        }
        return true;
    }

    public static function markString($line)
    {
        return self::getStructure("/(\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\')|(?<!\<span style=)(\".*\")(?!\>)/", $line, "#619647");
    }

    public static function markSemicolons($line)
    {
        if (strpos($line, ");") !== false) {
            return str_replace(");", ')<span style="color:#CC7832;">;</span>', $line);
        }
    }

    public static function markCommas($line)
    {
        return str_replace(",", '<span style="color:#CC7832;">,</span>', $line);
    }

    public static function markCommentsForOneLine($line)
    {
        return self::getStructure('/\/\*\*.*\*\//', $line, "#619647");
    }

    public static function markObjectProperties($line)
    {
        return self::getStructure('/\->\w+/', $line, "#FFC66D");
    }

    public static function markVariables($line)
    {
        return self::getStructure('/\$\w+/', $line, "#9876AA");
    }

    private static function getStructure($pattern, $line, $color)
    {
        preg_match_all($pattern, $line, $value);

        if (count($value) > 0) {
            foreach ($value[0] as $variable) {
                $toBeReplaces = '<span style="color:' . $color . ';">' . $variable . '</span>';
                $line = str_replace($variable, $toBeReplaces, $line);
            }
            return $line;
        } else {
            return null;
        }
    }
}