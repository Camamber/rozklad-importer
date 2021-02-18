<?php

namespace App\Classes;

class Router
{

    private static $routes = [];

    public static function get(string $route, string $handler)
    {
        self::register($route, 'GET', $handler);
    }

    public static function post(string $route, string $handler)
    {
        self::register($route, 'POST', $handler);
    }

    private static function register(string $route, string $method, string $handler)
    {
        $handlerClassMethod = explode('@', $handler);

        self::$routes[$method . '_' . $_ENV['APP_ROOT_PATH'] . $route] = [
            'class' => '\\App\\Controllers\\' . $handlerClassMethod[0],
            'method' => $handlerClassMethod[1]
        ];
    }

    public static function buildRoute()
    {
        set_exception_handler(array(self::class, "handleException"));

        /*Определяем контроллер*/
        $key = $_SERVER['REQUEST_METHOD'] . '_' . strtok($_SERVER["REQUEST_URI"], '?');
        if (isset(self::$routes[$key])) {
            $route = self::$routes[$key];

            $controllerName = $route['class'];
            $action = $route['method'];
        } else {
            return header("HTTP/1.0 404 Not Found");
        }

        $controller = new $controllerName();
        $controller->$action($_REQUEST);
    }


    public static function handleException(\Exception $exception)
    {
        $message = sprintf("%s: %s\n in %s:%d", get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        Log::error($message);

        if (!($exception instanceof \App\Exceptions\AppException)) {
            $exception = \App\Exceptions\AppException::fromException($exception);
        }

        $controller = new \App\Controllers\ErrorController();
        $controller->index(['type' => get_class($exception), 'message' => $exception->getPublicMessage(), 'image' => $exception->getPublicImage()], $exception->getCode());
    }
}
