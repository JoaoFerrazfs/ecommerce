<?php
namespace App\view;

use Rain\Tpl;

class PageSite {

    private $tpl;
    private $options = [];
    private $defaults = [
        'data' => [],
    ];

    public function __construct($options = array(), $tplDir="/App/view/templates/site/")
    {

        $this->options = array_merge($this->defaults,$options);

        $config = array(
            "tpl_dir"       => $_SERVER['DOCUMENT_ROOT'].$tplDir,
            "cache_dir"     => $_SERVER['DOCUMENT_ROOT'].'/App/view/views-cache/',
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        $this->tpl = new Tpl;

        $this->setData($this->options['data']);

        $this->tpl->draw('header');

    }

    private function setData($data = array())
    {
        foreach ($data as $key =>$value){
            $this->tpl->assign($key,$value);
        }
    }

    public function setTpl($name, $data = array(),$returnHTML = false)
    {
        $this->setData($this->options['data']);
        return $this->tpl->draw($name,$returnHTML);
    }

    public function __destruct()
    {
        $this->tpl->draw('footer');

    }
}

?>
