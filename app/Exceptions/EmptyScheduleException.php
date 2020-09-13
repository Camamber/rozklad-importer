<?php

namespace App\Exceptions;

use App\Classes\Str;
use Exception;

class EmptyScheduleException extends AppException
{
    public function __construct($group)
    {
        $this->publicMessage = "Розклад для групи «{$group}» виявився порожнім.";

        $translitGroup = (new Str($group))->translit();
        parent::__construct("Schedule for {$translitGroup} is empty.", 400);
    }
}
