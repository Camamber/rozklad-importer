<?php

namespace App\Controllers;

use Google_Client;
use Google_Service_Calendar;

class OAuth2Controller
{
    public function index($request)
    {
        $client = new Google_Client();
        $client->setAuthConfig('client_secret.json');
        $client->setRedirectUri($_ENV['APP_URL'] . '/oauth2callback');
        $client->addScope(Google_Service_Calendar::CALENDAR);

        if (!isset($_GET['code'])) {
            $auth_url = $client->createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        } else {
            $_SESSION['access_token'] = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $redirect_uri =  $_SESSION['ORIGIN_URL']; //$_ENV['APP_URL'] . '/';
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }
}
