<?php
require __DIR__ . '/vendor/autoload.php';

use App\Classes\Router;

/** Load .env */
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();


/** Register routes */
Router::get('/', 'Controller@show');
Router::get('/success', 'Controller@success');
Router::get('/oauth2callback', 'OAuth2Controller@index');
Router::get('/api/schedule', 'ScheduleController@index');


Router::buildRoute();
