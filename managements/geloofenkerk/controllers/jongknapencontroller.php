<?php

namespace Managements\Geloofenkerk;

use Application\Controller as Application_Controller;

/**
* JongknapenController
*/
class JongknapenController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "jongknapen.html";
    }    
}