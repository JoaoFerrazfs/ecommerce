<?php

namespace Src\App\Models;

use Src\Config\Sql;
use Src\App\Models\Cart;

class Cart extends Model
{
    const SESSION = "Cart";

    public static function getFromSession()
    {
        $cart = new Cart();
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']) ;
        }else{
            $cart->getFromSessionID();

            if(!$cart->getidcart() > 0){
                $data = [
                    'dessessionid' => session_id()
                ];

                if(User::checkLogin(false)){
                    $user = User::getFromSesssion();
                    $data['iduser'] = $user->getiduser();
                }
                $cart->setData($data);

                $cart->save();

                $cart->setToSession();
            }
        }

        return $cart;

    }

    public function setToSession()
    {
        $_SESSION[Cart::SESSION]['idcart'] = $this->getValues();
    }




    public function getFromSessionID()
    {
        $sql = new Sql();

        $results = $sql->select('select * from tb_carts where dessessionid = :dessessionid',
            [
                ':dessessionid' => session_id()
            ]);

        if(count($results) > 0 ){
            $this->setData($results[0]);
        }
    }

    public function get(int $idCart)
    {
        $sql = new Sql();

        $results = $sql->select('select * from tb_carts where idcart = :idcart',
        [
            ':idcart' => $idCart
        ]);

        if(count($results) > 0 ){
            $this->setData($results[0]);
        }


    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select('call sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)',
        [
            ':idcart' => $this->getiscart(),
            ':dessessionid' => $this->getdessessionid(),
            ':iduser' => $this->getiduser() ,
            ':deszipcode' => $this->getdeszipcode(),
            ':vlfreight' => $this->getvlfreight(),
            ':nrdays' => $this->getnrdays()
        ]);

        $this->setData($results);


    }
}