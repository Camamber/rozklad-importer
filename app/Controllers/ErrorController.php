<?php

namespace App\Controllers;

class ErrorController
{
    
    public function index($message, $statusCode = 500)
    {
        header("HTTP/1.0 {$statusCode} Server error");
        return include('views/error.tpl.php');
    }
}
