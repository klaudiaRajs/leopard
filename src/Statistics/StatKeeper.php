<?php


namespace MyApp\Statistics;


class StatKeeper{

    private $stats;

    public function __construct(){
        $this->stats = [];
    }

    public function addProgress(string $file, int $found, string $problem, int $lineNumber, int $introduced = null){
        if (!key_exists($file, $this->stats)) {
            $this->stats[$file] = [];
        }
        if( !isset($this->stats[$file]['found']) ){
            $this->stats[$file]['found'] = 0;
        }

        if ($introduced) {
            $this->stats[$file]['introduced'] = '';
            $this->stats[$file]['introduced'] = $introduced;
        }
        $this->stats[$file]['found'] += $found;
        $this->stats[$file]['problems'][$problem][] = $lineNumber;
    }

    public function saveProgress(){
        $fileWithPath = __DIR__ . "/../../stats/" . date('Ymdhis');

        file_put_contents($fileWithPath, json_encode($this->stats));
        return $fileWithPath;
    }

    public function saveSimilarity($similarityResults, $fileName){
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
        $fileWithPath = __DIR__ . "/../../stats/similarity-" . uniqid() . '-' . $fileName;
        file_put_contents($fileWithPath, json_encode($similarityResults));
        return $fileWithPath;
    }
}