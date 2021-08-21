<?php
define('__BASEDIR__', __DIR__);
require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Migration\CreateGroupsTable;
use Migration\CreateSchedulesTable;

/** Load .env */
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


/** Connect to MySQL */
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => $_ENV['DB_CONNECTION'],
    'host'      => $_ENV['DB_HOST'],
    'port'      => $_ENV['DB_PORT'],
    'database'  => $_ENV['DB_DATABASE'],
    'username'  => $_ENV['DB_USERNAME'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);
$capsule->setAsGlobal();

$capsule->bootEloquent();

$c = new CreateGroupsTable();
$c->up();

$c = new CreateSchedulesTable();
$c->up();
