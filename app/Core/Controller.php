<?php

namespace App\Core;

class Controller
{
    protected function view($view, $data = [])
    {
        extract($data);
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("View file not found: $view");
        }
    }

    protected function redirect($url)
    {
        $fullUrl = url($url);
        header("Location: $fullUrl");
        exit;
    }
}
