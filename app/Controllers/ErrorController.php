<?php

namespace App\Controllers;

class ErrorController
{
    
    public function index($errObj, $statusCode = 500)
    {
        header("HTTP/1.0 {$statusCode} Server error");
        return include('views/error.tpl.php');
    }
}
