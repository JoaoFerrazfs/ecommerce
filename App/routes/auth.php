<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\view\PageAdmin;
use App\Model\User;



$app->post('/admin/login', function (Request $request, Response $response) {
    User::login($_POST['login'], $_POST['password']);
    header("Location: /admin");
    exit;
}
);

$app->get('/admin/forgot', function (Request $request, Response $response) {


    $page = new PageAdmin(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("forgot");
    return $response;
}
);

$app->post('/admin/forgot', function (Request $request, Response $response) {
    $user = User::getForgot($_POST['email']);

    var_dump($user);
    return $response
        ->withHeader('Location', '/admin/forgot/sent')
        ->withStatus(302);

});


$app->get('/admin/forgot/sent', function (Request $request, Response $response) {
    $page = new PageAdmin(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("forgot-sent");

    return $response;
}
);

$app->get(
    '/admin/logout', function (Request $request, Response $response) {
    User::logout();
    header('Location: /admin/login');
    return $response;
}
);