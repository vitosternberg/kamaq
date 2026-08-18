<?php

namespace App\Core;

abstract class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'site'): void
    {
        $viewFile = BASE_PATH . '/app/views/' . $view . '.php';
        if (!is_file($viewFile)) {
            throw new \RuntimeException("Vista no encontrada: {$view}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        $layoutFile = BASE_PATH . '/app/views/layout/' . $layout . '.php';
        if (is_file($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }
}
