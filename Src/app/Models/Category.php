<?php

namespace Src\App\Models;

use Src\Config\Sql;
use Exception;

class Category extends Model
{
    public static function listAll()
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_categories ORDER BY descategory');
        return $results;

    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select('Call sp_categories_save(:idcategory, :pdescategory )', array(
            ':idcategory' => $this->getidcategory(),
            ':pdescategory' => $this->getdescategory()
        ));

        $this->setData($results[0]);
        Category::updateFile();
    }

    public function get($idCategory)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_categories where idcategory = :idcategory ',array(
            ':idcategory' => $idCategory
        ));

        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query('DELETE FROM tb_categories WHERE idcategory = :idcategory', array(
           ':idcategory' => $this->getidcategory()
        ));
        Category::updateFile();

    }

    public static function updateFile()
    {
        $categories = Category::listAll();
        $html = [];

        foreach ($categories as $row){
            array_push($html,'<li class="nav-item" ><a class="nav-link" href=/categories/' . $row['idcategory'] . '>' . $row['descategory'] . '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'Src'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'categories'.DIRECTORY_SEPARATOR.'categories-menu.html',implode('',$html));
    }
    public function getProducts($related = true)
    {
        $sql = new Sql();

        if($related)
        {
           $results = $sql->select("
            select * from tb_products where idproduct in(
            SELECT a.idproduct
            FROM tb_products a
            INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
            )",array(':idcategory'=>$this->getidcategory()));
        }else
        {
            $results =  $sql->select("
            select * from tb_products where idproduct not in(
            SELECT a.idproduct
            FROM tb_products a
            INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
            WHERE b.idcategory = :idcategory
            )",array('idcategory' => $this->getidcategory()));

        }

        return $results ;
    }

    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query('insert into tb_categoriesproducts (idcategory, idproduct) values (:idcategory, :idproduct)',
        array(
            ':idcategory' => $this->getidcategory(),
            ':idproduct' =>  $product->getidproduct()
        ));
    }

    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query('delete from tb_categoriesproducts where idcategory = :idcategory and idproduct = :idproduct',
            array(
                ':idcategory' => $this->getidcategory(),
                ':idproduct' =>  $product->getidproduct()
            ));
    }

    public function getProductsPage($page = 1 , $perPage = 1)
    {
        $start = ($page - 1 ) * $perPage;
        $sql = new Sql();



        $results = $sql->select('
                    SELECT sql_calc_found_rows * 
                    FROM tb_products a INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
                    INNER JOIN tb_categories c ON c.idcategory = b.idcategory WHERE c.idcategory = :idcategory 
                    limit '
                    . $start . ',' . $perPage . ';'
                    ,
                    [
                    ':idcategory' => $this->getidcategory(),
                    ]);


        $resultTotal= $sql->select('select found_rows() as nrtotal;');


        return [
            'data' => $results,
            'total' => $resultTotal[0]['nrtotal'],
            'page' => (int)ceil($resultTotal[0]['nrtotal'] / $perPage )
            ];
    }



}