<?php

namespace App\Controllers;

use App\Exceptions\EmptyScheduleException;
use App\Exceptions\GroupNotFoundException;

class ErrorController
{
    
    public function index($errObj, $statusCode = 500)
    {
        if($errObj['type'] == GroupNotFoundException::class) {
            $errObj['type'] = 'not_found';
        }
        if($errObj['type'] == EmptyScheduleException::class) {
            $errObj['type'] = 'empty';
        }
        header("HTTP/1.0 {$statusCode} Server error");
        return include('views/error.tpl.php');
    }
}
