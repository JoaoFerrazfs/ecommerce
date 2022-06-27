<?php
namespace Src\App\Controllers;


class PageAdminController extends PageSiteController{

    public function __construct($options = array(), $tplDir = "/Src/resources/views/admin/templates/")
    {
        parent::__construct($options, $tplDir);
    }


}