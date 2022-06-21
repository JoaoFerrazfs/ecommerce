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
    }
}