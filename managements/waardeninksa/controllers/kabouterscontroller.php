<?php

namespace Managements\Waardeninksa;

use Application\Controller as Application_Controller;

/**
* KaboutersController
*/
class KaboutersController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "kabouters.html";
    }    
}