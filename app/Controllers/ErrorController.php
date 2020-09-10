<?php

namespace App\Controllers;

class ErrorController
{
    public function index($statusCode)
    {
        header("HTTP/1.0 {$statusCode} Server error");
        return include('views/error.tpl.php');
    }
}
