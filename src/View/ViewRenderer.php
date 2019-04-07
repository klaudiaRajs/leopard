<?php

namespace MyApp\View;

class ViewRenderer {
    static public function render($fileName, array $params = []) {
        ob_start();
        self::renderAndOutput($fileName, $params);
        $buffer = ob_get_contents();
        ob_end_clean();
        return $buffer;
    }

    static private function renderAndOutput($fileName, array $params = []) {
        foreach($params as $key => $value) {
            $$key = $value;
        }
        require __DIR__ . '/../../views/' . $fileName . '.php';
    }
}