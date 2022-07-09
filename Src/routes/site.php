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

$app->get('/cart',function (Request $request, Response $response){

    $cart = Cart::getFromSession();

    $page = new PageSiteController();

    $page->setTpl('cart'.DIRECTORY_SEPARATOR.'cart',
    [
        'cart' => $cart->getValues(),
        'products' => $cart->getProducts(),
        'error' => Cart::getMsgError()
    ]);


    var_dump($cart->getValues());

    return $response;


});

$app->get('/cart/{idproduct}/add',function (Request $request, Response $response, $idproduct){
    $product = new Product();

    $product->get($idproduct['idproduct']);

    $cart = Cart::getFromSession();



    $qtd = (isset($_GET['qty'])) ? (int)$_GET['qty'] : 1;

    for($i = 0 ; $i < $qtd ; $i++)
    {
        $cart->addProduct($product);
    }

    return $response
        ->withHeader('Location', '/cart')
        ->withStatus(302);

});

$app->get('/cart/{idproduct}/minus',function (Request $request, Response $response, $idproduct){
    $product = new Product();

    $product->get($idproduct['idproduct']);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product);

    return $response
        ->withHeader('Location', '/cart')
        ->withStatus(302);

});

$app->get('/cart/{idproduct}/remove',function (Request $request, Response $response, $idproduct){
    $product = new Product();

    $product->get($idproduct['idproduct']);

    $cart = Cart::getFromSession();

    $cart->removeProduct($product,true);

    return $response
        ->withHeader('Location', '/cart')
        ->withStatus(302);

});

$app->post('/cart/freight',function (Request $request, Response $response)
{
    $cart = Cart::getFromSession();

    $cart->setFreight($_POST['cep']);

    header("Location: /cart");

});