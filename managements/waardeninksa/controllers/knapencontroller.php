<?php

namespace Managements\Waardeninksa;

use Application\Controller as Application_Controller;

/**
* KnapenController
*/
class KnapenController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "knapen.html";
    }    
}