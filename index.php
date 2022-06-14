<?php
session_start();
require_once("./vendor/autoload.php");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Factory\AppFactory;
use App\view\PageSite;
use App\view\PageAdmin;
use App\Model\User;


$app = AppFactory::create();
$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get('/', function(Request $request, Response $response) {
    $page = new PageSite();
    $page->setTpl('index');
  return $response;
});

$app->get('/admin', function(Request $request, Response $response) {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
    return $response;
});


$app->get('/admin/login', function(Request $request, Response $response) {


    $page = new PageAdmin(
        [
            "header"=>false,
            "footer"=>false,
            "pag"=>'admin/auth/'
        ]
);
    $page->setTpl("login");
    return $response;
});

$app->post('/admin/login', function(Request $request, Response $response) {
    User::login($_POST['login'], $_POST['password']);
    header("Location: /admin");
    exit;
});

$app->get('/admin/logout',function(Request $request, Response $response){
    User::logout();
    header('Location: /admin/login');
    return  $response;
});


$app->run();
