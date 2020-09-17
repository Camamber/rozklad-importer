<?php

namespace App\Exceptions;

use App\Classes\Str;

class GroupNotFoundException extends AppException
{
    public function __construct($group)
    {   
        $translitGroup = (new Str($group))->translit();
        parent::__construct("Group $translitGroup Not Found", 404);
        
        $this->publicMessage = "Cхоже на те, що групи «{$group}» не існує. Або ти щось не так ввів. Як би там не було, не здавайся та спробуй ще раз!";
    }
}
