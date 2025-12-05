<?php

if (!function_exists('e')) {
    function e($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('check_csrf')) {
    function check_csrf($token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
    }
}

if (!function_exists('url')) {
    function url($path = '')
    {
        // Hardcoded base path to ensure correct redirection
        $basePath = '/gestion-materiel/public';
        return $basePath . '/' . ltrim($path, '/');
    }
}
