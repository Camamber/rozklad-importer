<?php

namespace App\Controllers;

use App\Classes\Cache;
use App\Classes\GoogleClient;
use App\Models\Group;
use App\Services\RozkladParserService;
use App\Services\ScheduleImporterService;

class ControllerNew
{
    public function show($request)
    {
        $groups = [];
        if (!isset($_GET['group']) && !isset($_GET['group_id'])) {
            setcookie("group", "", time() - 3600);
            return include('views/home.tpl.php');
        }
        
        if (isset($_GET['group'])) {
            $groups = Group::select(['id', 'title'])->where('title', $_GET['group'])->get();

            if ($groups->count() > 1) {
                $groups = $groups->toArray();
                return include('views/home.tpl.php');
            } else if ($groups->count() > 0) {
                $groupId = $groups->first()->id;
                header('Location: ' . filter_var($_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/?group_id=' . $groupId, FILTER_SANITIZE_URL));
                return;
            } else {
                setcookie("group", $_GET['group']);
                throw new \App\Exceptions\GroupNotFoundException($_GET['group']);
            }
        }

        $group = Group::with('schedules')->where('id', $_GET['group_id'])->first();
        setcookie("group", $group->title);

        if(!$group) {
            throw new \App\Exceptions\GroupNotFoundException($_GET['group_id']);
        }

        if(!$group->schedules->count()) {
            throw new \App\Exceptions\EmptyScheduleException($group->title, $group->uuid);
        }

        $schedule = $group->schedules->first()->schedule;
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
