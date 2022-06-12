<?php
namespace App\view;

class PageAdmin extends PageSite{

    public function __construct($options = array(), $tplDir = "/App/view/templates/admin/")
    {
        parent::__construct($options, $tplDir);
    }

}