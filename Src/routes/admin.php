<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\App\Controllers\PageAdminController;
use Src\App\Models\User;
use Src\App\Models\Category;
use Src\App\Models\Product;


$app->get(
    '/admin', function (Request $request, Response $response) {
    User::verifyLogin();
    $page = new PageAdminController();
    $page->setTpl("index");
    return $response;
}
);


$app->get(
    '/admin/login', function (Request $request, Response $response) {


    $page = new PageAdminController(
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


$app->get(
    '/admin/users', function (Request $request, Response $response) {

    User::verifyLogin();
    $users = User::listAll();
    $page = new PageAdminController();
    $page->setTpl(
        'userManager'.DIRECTORY_SEPARATOR . 'users', array(
            'users' => $users
        )
    );


    return $response;

}
)->setName('userList');

$app->get(
    '/admin/users/create', function (Request $request, Response $response) {

    User::verifyLogin();
    $page = new PageAdminController();
    $page->setTpl('userManager'.DIRECTORY_SEPARATOR .'users-create');
    return $response;

}
);

$app->get(
    '/admin/users/{iduser}/delete', function (Request $request, Response $response, $iduser) {
    User::verifyLogin();
    $user = new User();
    $user->get($iduser);
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
    $page = new PageAdminController();

    $page->setTpl(
        'userManager'.DIRECTORY_SEPARATOR .'users-update', array(
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
    $page = new PageAdminController(
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

$app->post("/admin/forgot/reset", function (Request $request, Response $response) {

    $user = new User();
    $forgot = User::validForgotDecrypt($_POST['code']);
    User::setForgotUsed($forgot['idrecovery']);
    $user->get((int)$forgot['iduser']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 12]);
    $user->setPassword($password);

    $page = new PageAdminController(
        [
            "header" => false,
            "footer" => false,
            "pag" => 'admin/auth/'
        ]
    );
    $page->setTpl("forgot-reset-success");
    return $response;


});

$app->get('/admin/categories', function (Request $request, Response $response) {
    $page = new PageAdminController();
    $categories = Category::listAll();

    $page->setTpl('categoriesManager'.DIRECTORY_SEPARATOR . 'categories', array(
        'categories' => $categories
    ));
    return $response;
});

$app->get('/admin/categories/create', function (Request $request, Response $response) {
    $page = new PageAdminController();
    $page->setTpl('categoriesManager'.DIRECTORY_SEPARATOR . 'categories-create');
    return $response;
});

$app->post('/admin/categories/create', function (Request $request, Response $response) {
    $category = new Category();
    $category->setData($_POST);
    $category->save();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);
});

$app->get('/admin/categories/{idCategory}/delete', function (Request $request, Response $response, $idCategory) {
    $category = new Category();
    $category->get($idCategory['idCategory']);
    $category->delete();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);
});

$app->get('/admin/categories/{idCategory}', function (Request $request, Response $response, $idCategory) {
    $page = new PageAdminController();
    $category = new Category();
    $category->get($idCategory['idCategory']);

    $page->setTpl('categoriesManager'.DIRECTORY_SEPARATOR . 'categories-update', array(
        'category' => $category->getValues()
    ));

});

$app->post('/admin/categories/{idCategory}', function (Request $request, Response $response, $idCategory) {
    $category = new Category();
    $category->get($idCategory['idCategory']);

    $category->setData($_POST);
    $category->save();

    return $response
        ->withHeader('Location', '/admin/categories')
        ->withStatus(302);

});

$app->get('/admin/produtos',function (Request $request, Response  $response){

    User::verifyLogin();
    $products = Product::listAll();

    $page = new PageAdminController();
    $page->setTpl('productsManager'.DIRECTORY_SEPARATOR . 'products',array(
        'products' => $products
    ));


});

$app->get('/admin/products/create',function (Request $request, Response $response){

    User::verifyLogin();
    $page = new PageAdminController();
    $page->setTpl('productsManager'.DIRECTORY_SEPARATOR . 'products-create');
    return $response;
});

$app->post('/admin/products/create',function (Request $request, Response $response){

    User::verifyLogin();

    $product = new Product();
    $product->setData($_POST);
    $product->setPhoto($_FILES['file']);
    $product->save();

    var_dump($product);

    return $response
        ->withHeader('Location', '/admin/produtos')
        ->withStatus(302);
});

$app->get('/admin/products/{idProduct}',function (Request $request, Response $response,$idProduct){

    User::verifyLogin();

    $product = new Product();
    $product->get($idProduct['idProduct']);

    $page = new PageAdminController();
    $page->setTpl('productsManager'.DIRECTORY_SEPARATOR . 'products-update',array(
        'product' => $product->getValues()
    ));

    return $response;
});

$app->post('/admin/products/{idProduct}',function (Request $request, Response $response,$idProduct){

    User::verifyLogin();

    $product = new Product();
    $product->get($idProduct['idProduct']);
    $product->setData($_POST);
    $product->setPhoto($_FILES['file']);
    $product->save();


    return $response
        ->withHeader('Location', '/admin/produtos')
        ->withStatus(302);
});

