<?php

namespace Managements\Geloofenkerk;

use Application\Controller as Application_Controller;

/**
* InfoController
*/
class InfoController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "info.html";
    }    
}