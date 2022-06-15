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

$app->get('/admin/users',function (Request $request,Response $response){

    User::verifyLogin();
    $users = User::listAll();

    $page = new PageAdmin();
    $page->setTpl('userManager/users',array(
        'users' => $users
    ));


    return $response;

})->setName('userList');

$app->get('/admin/users/create',function (Request $request,Response $response){

    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('userManager/users-create');
    return $response;

});

$app->get('/admin/users/:iduser/delete',function (Request $request,Response $response, $idUser){
    User::verifyLogin();
    return $response;

});

$app->get('/admin/users/:idusers',function (Request $request,Response $response, $idUser){

    User::verifyLogin();
    $page = new PageAdmin([]);
    $page->setTpl('userManager/users-update');
    return $response;

});

$app->post('/admin/users/create',function (Request $request,Response $response) use ($app){
   User::verifyLogin();
   $user = new User();

   $_POST['inadmin'] = (isset($_POST['inadmin']))? 1:0;
   $user->setData($_POST);

   $user->save();

    return $response
        ->withHeader('Location', '/admin/users')
        ->withStatus(302);


});

$app->post('/admin/users/:iduser',function (Request $request,Response $response, $idUser){
    User::verifyLogin();
    return $response;

});



$app->run();
