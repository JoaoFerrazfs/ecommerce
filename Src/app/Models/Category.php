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

        var_dump($results);
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
            array_push($html,'<li><a href=/categories/' . $row['idcategory'] . '>' . $row['descategory'] . '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'Src'.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'site'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'categories'.DIRECTORY_SEPARATOR.'categories-menu.html',implode('',$html));
    }
}