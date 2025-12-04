<?php

namespace App\Core;

class Router
{
    protected $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function dispatch()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove script name from URI if it exists (e.g. /gestion-materiel/public/index.php)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);

        // Handle Windows paths if necessary
        if (strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }

        // Also remove /index.php if it's at the start of the remaining URI
        if (strpos($uri, '/index.php') === 0) {
            $uri = substr($uri, strlen('/index.php'));
        }

        $uri = '/' . ltrim($uri, '/');

        $method = $_SERVER['REQUEST_METHOD'];

        $matchedCallback = null;

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                $cmp = strcmp($route, $uri);
                if ($cmp === 0) {
                    $matchedCallback = $callback;
                    break;
                } else {
                    // echo "Compare '$route' vs '$uri' = $cmp <br>";
                }
            }
        }

        if ($matchedCallback) {
            if (is_array($matchedCallback)) {
                $controller = new $matchedCallback[0]();
                $action = $matchedCallback[1];
                $controller->$action();
            } else {
                call_user_func($matchedCallback);
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
