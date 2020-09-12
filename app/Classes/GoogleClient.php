<?php

namespace App\Classes;

use App\Models\User;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Oauth2;


class GoogleClient
{
    static $instance;

    public function __construct()
    {
    }

    public static function getInstance(): Google_Client
    {
        if (!isset(self::$instance)) {
            self::$instance = new Google_Client();
            self::$instance->setAuthConfig('client_secret.json');
            self::$instance->setRedirectUri($_ENV['APP_URL'] . $_ENV['APP_ROOT_PATH'] . '/oauth2callback');
            self::$instance->addScope([
                Google_Service_Oauth2::USERINFO_EMAIL,
                Google_Service_Oauth2::USERINFO_PROFILE,
                Google_Service_Calendar::CALENDAR,
                Google_Service_Calendar::CALENDAR_EVENTS,
            ]);
            self::$instance->setAccessType('offline');
        }
        return self::$instance;
    }

    public static function setAccessToken($token)
    {
        return self::getInstance()->setAccessToken($token);
    }

    public static function createAuthUrl()
    {
        return self::getInstance()->createAuthUrl();
    }

    public static function fetchAccessTokenWithAuthCode($code)
    {
        return self::getInstance()->fetchAccessTokenWithAuthCode($code);
    }

    public static function user() {
        $oauth2 = new Google_Service_Oauth2(self::getInstance());
        $userInfo = $oauth2->userinfo->get();
        return User::fromGoogleUser($userInfo);
        
    }
}
