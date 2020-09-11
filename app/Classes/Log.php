<?php

namespace App\Classes;

use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Log
{
    static $instance = null;

    public static function getInstance(): Logger
    {
        if (!isset(self::$instance)) {
            self::$instance = new Logger($_ENV['APP_NAME']);
            self::$instance->pushHandler(new StreamHandler(__BASEDIR__ . '/events.log', Logger::DEBUG));
            self::$instance->pushHandler(new FirePHPHandler());
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
