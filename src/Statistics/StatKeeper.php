<?php


namespace MyApp\Statistics;


class StatKeeper{

    private static $stats;
    public static $currentFile;

    public static function addProgress(int $found, string $problem, int $lineNumber, int $introduced = null){
        if( !self::$stats ){
            self::$stats = [];
        }

        if (!key_exists(self::$currentFile, self::$stats)) {
            self::$stats[self::$currentFile] = [];
        }
        if( !isset(self::$stats[self::$currentFile]['found']) ){
            self::$stats[self::$currentFile]['found'] = 0;
        }

        if ($introduced) {
            self::$stats[self::$currentFile]['introduced'] = '';
            self::$stats[self::$currentFile]['introduced'] = $introduced;
        }
        self::$stats[self::$currentFile]['found'] += $found;
        self::$stats[self::$currentFile]['problems'][$problem][] = $lineNumber;
    }

    public static function saveProgress(){
        $fileWithPath = __DIR__ . "/../../stats/" . date('Ymdhis');

        file_put_contents($fileWithPath, json_encode(self::$stats));
        return $fileWithPath;
    }

    public static function saveSimilarity($similarityResults){
        $files = glob(__DIR__ . "\..\..\stats\*");
        if( count($files) > 1 ){
            foreach($files as $file){
                //Make sure that this is a file and not a directory.
                if(is_file($file)){
                    //Use the unlink function to delete the file.
                    unlink($file);
                }
            }
        }
        $fileWithPath = __DIR__ . "/../../stats/similarity-" . uniqid() . '-' . self::$currentFile;
        file_put_contents($fileWithPath, json_encode($similarityResults));
        return $fileWithPath;
    }
}