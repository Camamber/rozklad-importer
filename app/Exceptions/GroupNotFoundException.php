<?php

namespace App\Exceptions;

use App\Classes\Str;

class GroupNotFoundException extends AppException
{
    public function __construct($group)
    {
        $this->publicMessage = "Cхоже на те, що групи «{$group}» не існує.";
        
        $translitGroup = (new Str($group))->translit();
        parent::__construct("Group $translitGroup Not Found", 404);
    }
}
