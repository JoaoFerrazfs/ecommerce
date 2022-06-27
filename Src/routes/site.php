<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\App\Controllers\PageSiteController;
use Src\App\Models\Category;


$app->get(
    '/', function (Request $request, Response $response) {
    $page = new PageSiteController();
    $page->setTpl('index');
    return $response;
}
);

$app->get('/categories/{idcategory}',function (Request $request, Response $response, $idcategory){
    $category = new Category();
    $page = new PageSiteController();
    $category->get($idcategory['idcategory']);

    $page->setTpl('categories'.DIRECTORY_SEPARATOR . 'category', array(
        'category' => $category->getValues(),
        'products' => []
    ));

});