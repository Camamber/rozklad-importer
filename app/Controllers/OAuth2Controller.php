<?php

namespace App\Controllers;

use App\Classes\GoogleClient;

class OAuth2Controller
{
    public function index($request)
    {
        if (!isset($_GET['code'])) {
            $auth_url = GoogleClient::createAuthUrl();
            header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
        } else {
            $_SESSION['access_token'] = GoogleClient::fetchAccessTokenWithAuthCode($_GET['code']);
            $redirect_uri =  $_SESSION['ORIGIN_URL'];
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }
    }
}
