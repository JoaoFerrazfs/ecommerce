<?php
namespace App\view;

class PageAdmin extends PageSite{

    public function __construct($options = array(), $tplDir = "/App/view/views/admin/templates/")
    {
        parent::__construct($options, $tplDir);
    }


}