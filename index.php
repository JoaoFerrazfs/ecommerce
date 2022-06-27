<?php
session_start();
require_once "./vendor/autoload.php";
use \Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);


require_once('Src/routes/admin.php');
require_once('Src/routes/auth.php');
require_once('Src/routes/site.php');




$app->run();
