<?php

namespace Src\App\Models;

use Src\Config\Sql;


class Cart extends Model
{
    const SESSION = "Cart";
    const SESSION_ERRO = "CartErro";

    public static function getFromSession()
    {
        $cart = new Cart();

        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']) ;

        }
        else
        {
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

    public function addProduct(Product $product)
    {
        $sql = new Sql();

        $sql->query('insert into tb_cartsproducts (idcart, idproduct) values (:idcart, :idproduct)',
        [
            ':idcart' => $this->getidcart(),
            ':idproduct' => $product->getidproduct()
        ]);

        $this->getCalculateTotal();

    }

    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();

        if($all){
            $sql->query('update tb_cartsproducts set dtremoved = now() where idcart =:idcart and idproduct = :idproduct and dtremoved is null',
            [
                ':idcart'=> $this->getidcart(),
                ':idproduct' => $product->getidproduct()
            ]);
        }else{
            $sql->query('update tb_cartsproducts set dtremoved = now() where idcart =:idcart and idproduct = :idproduct and dtremoved is null limit 1',
                [
                    ':idcart'=> $this->getidcart(),
                    ':idproduct' => $product->getidproduct()
                ]);
        }

        $this->getCalculateTotal();

    }

    public  function getProducts()
    {
        $sql = new Sql();
        $results = $sql->select('
        select b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desphoto, count(*)as nrqtd, sum(b.vlprice) as vltotal
        from tb_cartsproducts a 
        inner join tb_products b on a.idproduct = b.idproduct
        where a.idcart = :idcart and a.dtremoved is null
        group by b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desphoto
        order by b.desproduct',
        [
            ':idcart' => $this->getidcart()
        ]);

        return $results ;

    }

    public function getProductsTotals()
{
    $sql = new Sql();

    $results = $sql->select('
    select sum(vlprice) as vlprice,sum(vlwidth) as vlwidth, sum(vlheight) as vlheight, sum(vllength) as vllength, sum(vlweight) as vlweight, count(*) as nrqtd
    from tb_products a
    inner join tb_cartsproducts b on a.idproduct = b.idproduct
    where b.idcart = :idcart and dtremoved is null ;',
        [
           ':idcart' => $this->getidcart()
        ]);

   if(count($results) > 0){
       return $results[0];
   }else{
       return [];
   }
}

public function setFreight($cep)
{
    $cep = str_replace('-','',$cep);

    $totals = $this->getProductsTotals();

    $totals['vlheight'] = $totals['vlheight'] < 2 ?  2 : $totals['vlheight'] ;
    $totals['vllength'] = $totals['vllength'] < 16 ?  2 : $totals['vllength'] ;

    if($totals['nrqtd'] > 0){
        $qs = http_build_query([
            'nCdEmpresa'=> '',
            'sDsSenha'=> '',
            'nCdServico'=> '40010',
            'sCepOrigem'=> '32920000',
            'sCepDestino'=> $cep,
            'nVlPeso'=> $totals['vlweight'],
            'nCdFormato'=> '1',
            'nVlComprimento'=> $totals['vllength'],
            'nVlAltura'=> $totals['vlheight'],
            'nVlLargura'=> $totals['vlwidth'],
            'nVlDiametro'=> '0',
            'sCdMaoPropria'=> 'S',
            'nVlValorDeclarado'=> $totals['vlprice'],
            'sCdAvisoRecebimento'=> 'S',
        ]);
        $xml = simplexml_load_file('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?'.$qs);

        $result = $xml->Servicos->cServico;

        if($result->Msg != ''){
            Cart::setMsgError($result->MsgErro);
        }else{
            Cart::clearMsgError();
        }
        $this->setnrdays($result->PrazoEntrega);
        $this->setvlfreight($this->formatValueToDecimal($result->Valor));
        $this->setdeszipcode($cep);
        $this->setvlsubtotal($totals['vlprice']);

        $this->save();

        return $result;

    }
}

    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.','', $value);
        $value = str_replace(',','.', $value);

        return  $value;

    }

    public static function setMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERRO] = $msg;
    }

    public static function getMsgError()
    {
        $msg = $_SESSION[Cart::SESSION_ERRO] ?? $_SESSION[Cart::SESSION_ERRO] ;

        Cart::clearMsgError();

        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERRO] = NULL ;
    }

    public function  updateFreight()
    {
        if($this->getdeszipcode() != ''){
            $this->setFreight($this->getdeszipcode);
        }
    }

    public function getValues()
    {
        $this->getCalculateTotal();
        return parent::getValues(); // TODO: Change the autogenerated stub
    }

    public function getCalculateTotal(){
        $this->updateFreight();

        $totals = $this->getProductsTotals();

        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight() );

    }

    public function d()
    {

    }


}