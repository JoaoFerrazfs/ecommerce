<?php
session_start();
require_once "./vendor/autoload.php";

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \Slim\Factory\AppFactory;
use App\view\PageSite;
use App\view\PageAdmin;
use App\Model\User;
use App\Model\Category;


$app = AppFactory::create();
$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->get(
    '/', function (Request $request, Response $response) {
    $page = new PageSite();
    $page->setTpl('index');
    return $response;
}
);

$app->get(
    '/admin', function (Request $request, Response $response) {
    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl("index");
    return $response;
}
);


$app->get(
    '/admin/login', function (Request $request, Response $response) {


    $page = new PageAdmin(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("login");
    return $response;
}
);

$app->post(
    '/admin/login', function (Request $request, Response $response) {
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

$app->get(
    '/admin/users', function (Request $request, Response $response) {

    User::verifyLogin();
    $users = User::listAll();
    $page = new PageAdmin();
    $page->setTpl(
        'userManager/users', array(
            'users' => $users
        )
    );


    return $response;

}
)->setName('userList');

$app->get(
    '/admin/users/create', function (Request $request, Response $response) {

    User::verifyLogin();
    $page = new PageAdmin();
    $page->setTpl('userManager/users-create');
    return $response;

}
);

$app->get(
    '/admin/users/{iduser}/delete', function (Request $request, Response $response, $idUser) {
    User::verifyLogin();

    $user = new User();

    $user->get($idUser);

    $user->delete();


    return $response
        ->withHeader('Location', '/admin/users')
        ->withStatus(302);

}
);

$app->get(
    '/admin/users/{iduser}', function (Request $request, Response $response, $iduser) {
    User::verifyLogin();
    $user = new User();
    $user->get($iduser);
    $page = new PageAdmin();


    $page->setTpl(
        'userManager/users-update', array(
            'user' => $user->getValues()
        )
    );
    return $response;

}
);

$app->post(
    '/admin/users/create', function (Request $request, Response $response) use ($app) {

    User::verifyLogin();
    $user = new User();
    $_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;
    $user->setData($_POST);

    $user->save();

    return $response
        ->withHeader('Location', '/admin/users')
        ->withStatus(302);


}
);

$app->post('/admin/users/{iduser}', function (Request $request, Response $response, $iduser) {
    User::verifyLogin();

    $user = new User();

    $_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1 : 0;

    $user->get($iduser);

    $user->setData($_POST);

    $user->update();


    return $response
        ->withHeader('Location', '/admin/users')
        ->withStatus(302);

}
);

$app->get('/admin/forgot/reset', function (Request $request, Response $response) {

    $user = User::validForgotDecrypt($_GET['code']);
    $page = new PageAdmin(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("forgot-reset", array(
        'name' => $user['deperson'],
        'code' => $_GET['code']
    ));

    return $response;

});

$app->post("/admin/forgot/reset",function (Request $request, Response $response){

    $user = new User();
    $forgot = User::validForgotDecrypt($_POST['code']);
    User::setForgotUsed($forgot['idrecovery']);

    $user->get((int)$forgot['iduser']);

    $password = password_hash($_POST['password'], PASSWORD_DEFAULT,['cost'=>12]);

    $user->setPassword($password);

    $page = new PageAdmin(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("forgot-reset-success");
    return $response;


});

$app->get('/admin/categories',function (Request $request, Response $response){
    $page = new PageAdmin();
    $categories = Category::listAll();

    $page->setTpl('categoriesManager/categories',array(
        'categories' => $categories
    ));
    return $response;
});

$app->get('/admin/categories/create',function (Request $request, Response $response){
    $page = new PageAdmin();
    $page->setTpl('categoriesManager/categories-create');
    return $response;
});

$app->post('/admin/categories/create',function (Request $request, Response $response){
    $category = new Category();
    $category->setData($_POST);
    $category->save();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);
});

$app->get('/admin/categories/{idCategory}/delete',function (Request $request, Response $response, $idCategory){
    $category = new Category();
    $category->get($idCategory['idCategory']);
    $category->delete();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);
});

$app->get('/admin/categories/{idCategory}',function (Request $request, Response $response, $idCategory){
    $page = new PageAdmin();
    $category = new Category();
    $category->get($idCategory['idCategory']);

    $page->setTpl('categoriesManager/categories-update', array(
        'category' => $category->getValues()
    ));

});

$app->post('/admin/categories/{idCategory}',function (Request $request, Response $response, $idCategory){
    $category = new Category();
    $category->get($idCategory['idCategory']);

    $category->setData($_POST);
    $category->save();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);

});

$app->get('/categories/{idcategory}',function (Request $request, Response $response, $idcategory){
    $category = new Category();
    $page = new PageSite();
    $category->get($idcategory['idcategory']);

    $page->setTpl('categories/category', array(
        'category' => $category->getValues(),
        'products' => []
    ));

});


$app->run();
