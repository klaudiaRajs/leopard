<?php


class SyntaxColourHelper2{

    public function getContents($fileContents)
    {
        echo '<xmp>';print_r($fileContents);exit;
//        $sortedContents = [];
        $sortedContents = $this->getGlobals($fileContents);

        return $this->replaceKeyWords($sortedContents, $fileContents);
    }

    private function getGlobals($file)
    {
        $globals = ['/\$_SESSION/', '/\$_POST/', '/\$_GET/', '/\$_FILES/', '/\$_SERVER/', '/\$_COOKIE/', '/\$_ENV/', '/\$_REQUEST/', '/\$GLOBALS/'];
        $result = [];
        foreach ($globals as $global) {
            preg_match_all($global, $file, $found, PREG_OFFSET_CAPTURE);
            if (!empty($found)) {
                $result[$global] = $found;
            }
        }

        return $result;
    }

    private function replaceKeyWords($sortedContents, $fileContent)
    {
        foreach ($sortedContents as $group) {
            foreach ($group as $fieldName => $occurrings) {
                foreach ($occurrings as $found) {
                        $a = '<span style="color:red;">' . $found[0] . '</span>';
                        $offsetNo = (int)$found[1];
                        $length = strlen($found[0]);
                        $finalOffset = $offsetNo + $length;
//                        var_dump($found);
                        substr_replace($fileContent, $a, $found[1] - 10);
                }
            }
        }

        return $fileContent;
    }


}