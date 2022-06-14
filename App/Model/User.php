<?php

namespace App\Model;

use App\database\Sql;
use App\Model\Model;
use mysql_xdevapi\Exception;

class User extends Model
{
    public static function login ($login, $password)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_users WHERE deslogin = :LOGIN', array(
            ':login' => $login,
        ));

        if(!count($results)){
            throw new \Exception("Usuário inexistente ou senha inválida",1);
        }

        $data = $results[0];

        if(password_verify($password,$data['despassword'])){
            $user = new User();
            $user->setiduser($data['iduser']);
        }else{
            throw new /Exception('Usuário inexistente ou senha inválida');
        }
    }
}