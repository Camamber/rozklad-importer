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
        if (!isset($_GET['group']) && !isset($_GET['group_id'])) {
            return include('views/home.tpl.php');
        }

        if (isset($_GET['group'])) {
            $groupIds = Cache::remember('fetchGroupIds' . $_GET['group'], 12 * 60 * 60, function () {
                return $this->rozkladParserService->fetchGroupIds($_GET['group']);
            });

            if (count($groupIds) > 1) {
                return include('views/home.tpl.php');
            } else if (count($groupIds) > 0) {
                $groupId = end($groupIds)['id'];
                header('Location: ' . filter_var($_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/?group_id=' . $groupId, FILTER_SANITIZE_URL));
                return;
            } else {
                throw new \App\Exceptions\GroupNotFoundException($_GET['group']);
            }
        }

        $schedule = $this->checkSchedule($_GET['group_id']);

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

    private function checkSchedule($groupId)
    {
        $schedule = Cache::remember('parseSchedule' . $groupId, 12 * 60 * 60, function () use ($groupId) {
            return $this->rozkladParserService->parse($groupId);
        });
        if (!count($schedule['weeks'][0]) && !count($schedule['weeks'][1])) {
            throw new \App\Exceptions\EmptyScheduleException($schedule['group'], $groupId);
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

    public function privacy($request)
    {
        return include('views/privacy.tpl.php');
    }
}
