<?php

namespace App\Controllers;

use App\Services\RozkladParserService;

class ScheduleController
{

    public function index($request)
    {
        if (!isset($_GET['group'])) {
            return null;
        }

        $rozkladParserService = new RozkladParserService();
        $schedule = $rozkladParserService->parse($_GET['group']);
        header('Content-Type: application/json');
        echo json_encode($schedule);
    }
}
