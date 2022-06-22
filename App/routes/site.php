<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\view\PageSite;
use App\Model\Category;


$app->get(
    '/', function (Request $request, Response $response) {
    $page = new PageSite();
    $page->setTpl('index');
    return $response;
}
);

$app->get('/categories/{idcategory}',function (Request $request, Response $response, $idcategory){
    $category = new Category();
    $page = new PageSite();
    $category->get($idcategory['idcategory']);

    $page->setTpl('categories/category', array(
        'category' => $category->getValues(),
        'products' => []
    ));

});