<?php
require_once("./vendor/autoload.php");

use \Slim\Slim;
use App\view\PageSite;
use App\view\PageAdmin;

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

$app->get('/login', function() {
    $page = new PageAdmin(
        [
            "header"=>false,
            "footer"=>false,
            "pag"=>'admin/auth/'
        ]
);
    $page->setTpl("login");
});

$app->run();

?>