<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Src\App\Controllers\PageSiteController;
use Src\App\Models\Category;
use Src\App\Models\Product;
use Src\App\Models\Cart;


$app->get(
    '/', function (Request $request, Response $response) {
    $page = new PageSiteController();
    $product = Product::listAll();
    $page->setTpl('index',['product'=>$product]);
    return $response;
}
);

$app->get('/categories/{idcategory}',function (Request $request, Response $response, $idcategory){
    $category = new Category();
    $category->get($idcategory['idcategory']);

     $paginate = $_GET['page'] ?? 1 ;

    $productsPagination = $category->getProductsPage($paginate);

    $pages = [];

    for ($i=1 ; $i <= $productsPagination['page'] ;$i++ )
    {
        array_push($pages,
            [
                'link' => '/categories/'.$category->getidcategory().'?page='. $i,
                'page'=>$i
            ]);
    }

    $page = new PageSiteController();
    $category->get($idcategory['idcategory']);

    $page->setTpl('categories'.DIRECTORY_SEPARATOR . 'category', array(
        'category' => $category->getValues(),
        'products' => $productsPagination['data'],
        'pages'    => $pages
    ));


    return $response;
});

$app->get('/products/{desurl}', function (Request $request, Response $response, $desurl){

    $product = new Product();
    $product->getFromUrl($desurl['desurl']);


    $page = new PageSiteController();
    $page->setTpl('product'.DIRECTORY_SEPARATOR.'single-product',
    [
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ]);

    return $response ;

});

$app->get('/cart',function (){

    $cart = Cart::getFromSession();

    $page = new PageSiteController();

    $page->setTpl('cart'.DIRECTORY_SEPARATOR.'cart');

});