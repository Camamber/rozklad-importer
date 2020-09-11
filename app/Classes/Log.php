<?php

namespace App\Classes;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    static $instance = null;

    public static function getInstance(): Logger
    {
        if (!isset(self::$instance)) {
            self::$instance = new Logger('Main');
            self::$instance->pushHandler(new StreamHandler('event.log', Logger::WARNING));
        }

        return self::$instance;
    }

    public static function info(string $message)
    {
        self::getInstance()->info($message);
    }

    public static function error(string $message)
    {
        self::getInstance()->error($message);
    }
}
