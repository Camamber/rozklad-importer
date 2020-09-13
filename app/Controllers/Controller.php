<?php

namespace App\Controllers;

use App\Classes\Cache;
use App\Classes\GoogleClient;
use App\Services\RozkladParserService;
use App\Services\ScheduleImporterService;

class Controller
{
    public function show($request)
    {
        if (!isset($_GET['group'])) {
            return include('views/home.tpl.php');
        }
        $group = $_GET['group'];

        $rozkladParserService = new RozkladParserService();
        $groups = $rozkladParserService->fetchGroups($group);
        $group = current(array_filter($groups, function ($g) use ($group) {
            return strtolower($group) == strtolower($g);
        }));
        if (!$group) {
            throw new \App\Exceptions\GroupNotFoundException($group);
        }

        if (isset($_SESSION['access_token']) && $_SESSION['access_token'] && $_SESSION['access_token']['created'] + $_SESSION['access_token']['expires_in'] > time()) {
            GoogleClient::setAccessToken($_SESSION['access_token']);

            $schedule = Cache::remember($group, 60, function () use ($rozkladParserService, $group) {
                return $rozkladParserService->parse($group);
            });

            $scheduleImporterService = new ScheduleImporterService();
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
