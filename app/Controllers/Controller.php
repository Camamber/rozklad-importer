<?php

namespace App\Controllers;

use App\Classes\Cache;
use App\Classes\GoogleClient;
use App\Services\RozkladParserService;
use App\Services\ScheduleImporterService;

class Controller
{
    private $rozkladParserService;

    public function __construct()
    {
        $this->rozkladParserService = new RozkladParserService();
    }

    public function show($request)
    {
        if (!isset($_GET['group'])) {
            return include('views/home.tpl.php');
        }
        
        $group = $this->checkGroup($_GET['group']);
        $schedule = $this->checkSchedule($group);

        if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && $_SESSION['access_token']['created'] + $_SESSION['access_token']['expires_in'] > time()) {
            GoogleClient::setAccessToken($_SESSION['access_token']);

            $scheduleImporterService = new ScheduleImporterService();
            $scheduleImporterService->import($schedule);

            header('Location: ' . filter_var($_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/success', FILTER_SANITIZE_URL));
        } else {
            $_SESSION['ORIGIN_URL'] = $_ENV['APP_URL'] . $_SERVER['REQUEST_URI'];

            $redirect_uri = $_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/oauth2callback';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    private function checkGroup($group)
    {
        $groups = $this->rozkladParserService->fetchGroups($group);
        $findedGroup = current(array_filter($groups, function ($g) use ($group) {
            return mb_strtolower($g, 'UTF-8') == mb_strtolower($group, 'UTF-8');
        }));
        if (!$findedGroup) {
            throw new \App\Exceptions\GroupNotFoundException($group);
        }
        return $findedGroup;
    }

    private function checkSchedule($group)
    {
        $schedule = Cache::remember('parseSchedule'.$group, 12*60*60, function () use ($group) {
            return $this->rozkladParserService->parse($group);
        });
        if(!count($schedule['weeks'][0]) && !count($schedule['weeks'][1])) {
            throw new \App\Exceptions\EmptyScheduleException($group);
        }
        return $schedule;
    }

    public function success($request)
    {
        return include('views/success.tpl.php');
    }

    public function maintance($request)
    {
        return include('views/maintance.tpl.php');
    }
}
