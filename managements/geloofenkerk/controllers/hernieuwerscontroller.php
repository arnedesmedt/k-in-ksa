<?php

namespace Managements\Geloofenkerk;

use Application\Controller as Application_Controller;

/**
* HernieuwersController
*/
class HernieuwersController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "hernieuwers.html";
    }    
}