<?php

namespace App\Model;

use App\database\Sql;
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
            array_push($html,'<li><a href=/categories/' . $row['idcategory'] . '>' . $row['descategory'] . '</a></li>');
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/App/view/views/site/templates/categories/categories-menu.html',implode('',$html));
    }
}