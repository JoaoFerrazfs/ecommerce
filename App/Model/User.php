<?php

namespace App\Model;

use App\database\Sql;
use Exception;

class User extends Model
{
    const SESSION = 'User';
    const KEYCRYPT = 'Joao-Pedro-97283';
    public static function login($login, $password)
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_users WHERE deslogin = :LOGIN', array(
            ':LOGIN' => $login,
        ));

        if (!count($results)) {
            throw new \Exception("Usuário inexistente ou senha inválida", 1);
        }

        $data = $results[0];

        if (password_verify($password, $data['despassword'])) {

            $user = new User();
            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValues();
            return $user;

        } else {
            throw new Exception('Usuário inexistente ou senha inválida');
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        return true;
        if (!isset(
                $_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]['iduser'] > 0
            ||
            (bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin
        ) {
            header('Location: /admin/login');
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }

    public static function listAll()
    {
        $sql = new Sql();
        $results = $sql->select('SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY desperson');
        return $results;

    }

    public function save()
    {
        $sql = new Sql();

        $results = $sql->select('Call sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)', array(
            ':desperson' => $this->getdesperson(),
            ':deslogin' => $this->getdeslogin(),
            ':despassword' => $this->getdespassword(),
            ':desemail' => $this->getdesemail(),
            ':nrphone' => $this->getnrphone(),
            ':inadmin' => $this->getinadmin()
        ));

        $this->setData($results[0]);

    }

    public function get($idUser)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser',
            array(
                ':iduser' => $idUser['iduser']
            ));

        $this->setData($results[0]);
    }

    public function update()
    {
        $sql = new Sql();

        $results = $sql->select('Call sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)', array(
            ':iduser' => $this->getiduser(),
            ':desperson' => $this->getdesperson(),
            ':deslogin' => $this->getdeslogin(),
            ':despassword' => $this->getdespassword(),
            ':desemail' => $this->getdesemail(),
            ':nrphone' => $this->getnrphone(),
            ':inadmin' => $this->getinadmin()
        ));

        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();

        $sql->query('Call sp_users_delete(:iduser)', array(
            ':iduser' => $this->getiduser()
        ));
    }

    public static function getForgot($email)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT *
                               FROM tb_persons a 
                               INNER JOIN tb_users b USING (idperson)
                               WHERE a.desemail = :email', array(
                ':email' => $email
            )
        );



        if(!count($results))
        {
            throw new Exception('Não foi possivel recuperar a senha .');
        }else
        {
            $data = $results[0];
            $secondSearch = $sql->select('Call sp_userspasswordsrecoveries_create(:iduser, :desip)',array(
                ':iduser' => $data['iduser'],
                ':desip' => $_SERVER['REMOTE_ADDR']
            ));

            if(!count($secondSearch))
            {
                throw new Exception('Não foi possível recuperar a senha.');
            }
            else
            {
                $dataRecovery = $secondSearch[0];

                $code = base64_encode(openssl_encrypt($dataRecovery['idrecovery'],'AES-256-CBC', User::KEYCRYPT,$option = 0));
                $link = 'http://localhost:3000/admin/forgot/reset?code='.$code;

                $mailer = new Mailer($data['desemail'], $data['desperson'],'Redefinir senha da Impeto Store', 'forgot',array(
                    'name' => $data['desperson'],
                    'link' => $link,
                ));

                $mailer->send();

                return $data;


            }
        }
    }

    public static function validForgotDecrypt($code)
    {
        $idRecovery= openssl_decrypt(base64_decode($code), 'AES-256-CBC',User::KEYCRYPT,$option = 0);

        $sql = new Sql();

         $results = $sql->select('SELECT * FROM tb_userspasswordsrecoveries a 
    inner join tb_users b using(iduser) 
    inner join tb_persons c using(idperson) 
    where a.idrecovery = :idRecovery and a.dtrecovery
    is null and date_add(a.dtregister, interval 1 hour) >= now();', array(
        ':idRecovery' => $idRecovery
        ));

         if(!count($results))
         {
             throw new Exception('Não foi possivel recuperar a senha.');
         }else
         {
             return $results[0];
         }

    }

    public static function setForgotUsed($idRecovery)
    {
        $sql = new Sql();
        $sql->query('update tb_userspasswordsrecoveries set dtrecovery = now() where idrecovery = :idrecovery',array(
            ':idrecovery' => $idRecovery
        ));

    }

    public function setPassword($password)
    {
        $sql = new Sql();
        $sql->query('update tb_users set despassword = :password where iduser = :iduser', array(
            ':password' => $password,
            ':iduser' => $this->getiduser()
        ));

    }

}