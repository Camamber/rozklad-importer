<?php

namespace App\Controllers;

use App\Classes\Cache;
use Google_Client;
use Google_Service_Calendar;
use App\Services\RozkladParserService;
use App\Services\ScheduleImporterService;
use Exception;

class Controller
{
    public function show($request)
    {
        if (!isset($_GET['group'])) {
            return include('views/home.tpl.php');
        }

        $group = $_GET['group'];
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && $_SESSION['access_token']['created'] + $_SESSION['access_token']['expires_in'] > time()) {
            $client->setAccessToken($_SESSION['access_token']);

            $schedule = Cache::remember($group, 60, function () use ($group) {
                $rozkladParserService = new RozkladParserService();
                return $rozkladParserService->parse($group);
            });

            $scheduleImporterService = new ScheduleImporterService($client);
            $scheduleImporterService->import($schedule);

            header('Location: ' . filter_var($_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/success', FILTER_SANITIZE_URL));
        } else {
            $_SESSION['ORIGIN_URL'] = $_ENV['APP_URL'] . $_SERVER['REQUEST_URI'];

            $redirect_uri = $_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/oauth2callback';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }

    public function success($request)
    {
        return include('views/success.tpl.php');
    }
}
