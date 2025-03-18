<?php

require 'vendor/autoload.php';
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
// test code, should output:
// api://default

define('DBHOST', $_ENV['DBHOST']);
define('DBNAME', $_ENV['DBNAME']);
define('DBUSER', $_ENV['DBUSER']);
define('DBPASS', $_ENV['DBPASS']);
define('DBPORT', $_ENV['DBPORT']);

$capsule = new Capsule;
$capsule->addConnection([
    "driver" => "mysql",
    "host" => DBHOST,
    "database" => DBNAME,
    "username" => DBUSER,
    "password" => DBPASS
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();