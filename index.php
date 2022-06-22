<?php
session_start();
require_once "./vendor/autoload.php";
use \Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);


require_once ('App/routes/site.php');
require_once ('App/routes/admin.php');
require_once ('App/routes/auth.php');




$app->run();
