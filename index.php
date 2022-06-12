<?php
require_once("vendor/autoload.php");

use App\database\Sql;


$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {

    $sql = new Sql();
    $result = $sql->select("select * from tb_users");
    echo json_encode($result);

});

$app->run();

?>