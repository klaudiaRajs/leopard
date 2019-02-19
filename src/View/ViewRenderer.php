<?php

namespace MyApp\View;

use Symfony\Component\HttpFoundation\Response;

class ViewRenderer {
    static public function render($fileName, array $params = []) {
        ob_start();
        self::renderAndOutput($fileName, $params);
        $buffer = ob_get_contents();
        ob_end_clean();

        return new Response($buffer);
    }

    static private function renderAndOutput($fileName, array $params = []) {
        foreach($params as $key => $value) {
            $$key = $value;
        }
        require __DIR__ . '/../../views/' . $fileName . '.php';
    }
}