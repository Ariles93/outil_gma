<?php

namespace App\Core;

class Logger
{
    private static $logFile;

    public static function init($path)
    {
        self::$logFile = $path;
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
    }

    public static function log($message, $level = 'ERROR')
    {
        if (!self::$logFile) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $formattedMessage = "[$date] [$level] $message" . PHP_EOL;

        file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
    }

    public static function error($message, $context = [])
    {
        $contextStr = !empty($context) ? json_encode($context) : '';
        self::log($message . ' ' . $contextStr, 'ERROR');
    }

    public static function info($message)
    {
        self::log($message, 'INFO');
    }
}
