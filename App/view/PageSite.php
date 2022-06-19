<?php
namespace App\view;

use Rain\Tpl;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PageSite {

    protected $tpl;
    protected $options = [];
    protected $defaults = [
        'header' => true,
        'footer' => true,
        'pag' => "",
        'data'   => [],
    ];

    public function __construct($options = array(), $tplDir="/App/view/views/site/templates/")
    {


        $this->options = array_merge($this->defaults,$options);
        $config = array(
            "tpl_dir"       => $this->options['pag'] ? $_SERVER['DOCUMENT_ROOT'].'/App/view/views/'.$this->options['pag'] : $_SERVER['DOCUMENT_ROOT'].$tplDir,
            "cache_dir"     => $_SERVER['DOCUMENT_ROOT'].'/App/view/views-cache/',
            "debug"         => false // set to false to improve the speed
        );



        Tpl::configure( $config );

        $this->tpl = new Tpl;

        $this->setData($this->options['data']);

        if($this->options['header']){
            $header = $this->tpl->draw('header');
        }

    }

    public function setData($data = array())
    {

        foreach ($data as $key =>$value){
            $this->tpl->assign($key,$value);
        }
    }

    public function setTpl($name, $data = array(),$returnHTML = false,)
    {
        $this->setData($data);
        return $this->tpl->draw($name,$returnHTML);
    }

    public function __destruct()
    {
        if($this->options['footer']){
            $this->tpl->draw('footer');
        }

    }
}

?>
