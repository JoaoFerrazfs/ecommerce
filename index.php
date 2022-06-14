<?php
require_once("./vendor/autoload.php");

use \Slim\Slim;
use App\view\PageSite;
use App\view\PageAdmin;
use App\Model\User;

$app = new Slim();

$app->config('debug', true);


$app->get('/', function() {
    $page = new PageSite();
    $page->setTpl("index");
});

$app->get('/admin', function() {
    $page = new PageAdmin();
    $page->setTpl("index");
});

$app->get('/admin/login', function() {
    $page = new PageAdmin(
        [
            "header"=>false,
            "footer"=>false,
            "pag"=>'admin/auth/'
        ]
);
    $page->setTpl("login");
});

$app->post('/admin/login', function() {
    var_dump('teste');
    User::login($_POST['login'], $_POST['password']);
    header("Location: /admin");
    exit;
});


$app->run();
