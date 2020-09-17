<?php

namespace App\Exceptions;

use App\Classes\Str;
use Exception;

class EmptyScheduleException extends AppException
{
    public function __construct($group, $groupId = null)
    {
        $translitGroup = (new Str($group))->translit();
        parent::__construct("Schedule for {$translitGroup} is empty.", 400);
        $link = '<a href="http://rozklad.kpi.ua/Schedules/ViewSchedule.aspx?g=' . $groupId . '">http://rozklad.kpi.ua/' . $translitGroup . '</a>';
        $this->publicMessage = "Розклад для групи «{$group}» виявився порожнім. Розкладу ще немає на сайті {$link}. Спробуй пізніше";
        $this->image = "static/img/empty_group.svg";
    }
}
