<?php

namespace App\Controllers;

use App\Services\RozkladParserService;

class ScheduleController
{
    private $rozkladParserService;

    public function __construct()
    {
        $this->rozkladParserService = new RozkladParserService();
    }

    public function index($request)
    {
        if (!isset($_GET['group'])) {
            return null;
        }


        $schedule = $this->rozkladParserService->parse($_GET['group']);
        header('Content-Type: application/json');
        echo json_encode($schedule);
    }

    public function id($request)
    {
        if (!isset($_GET['group'])) {
            return null;
        }
        
        $groups = $this->rozkladParserService->fetchGroupIds($_GET['group']);
        header('Content-Type: application/json');
        echo json_encode($groups);
    }

    public function groups($request)
    {
        header('Content-Type: application/json');

        if (!isset($_GET['query']) || strlen($_GET['query']) < 2) {
            echo json_encode([]);
        }
        
        $groups = $this->rozkladParserService->fetchGroups($_GET['query']);
        echo json_encode($groups);
    }
}
