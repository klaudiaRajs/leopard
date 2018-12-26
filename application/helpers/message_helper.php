<?php


class Messages{

    public static function MarkLineWithDeprecatedMethod(){
        return '<span style="color:red;">//Method is deprecated</span>';
    }

    public static function MarkLineWithStatic(){
        return '<span style="color:red;">//Static</span>';
    }

    public static function MarkLineWithGlobals(){
        return '<span style="color:red;">//Global</span>';
    }
}