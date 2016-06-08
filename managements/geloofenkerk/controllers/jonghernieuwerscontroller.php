<?php

namespace Managements\Geloofenkerk;

use Application\Controller as Application_Controller;

/**
* JonghernieuwersController
*/
class JonghernieuwersController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "jonghernieuwers.html";
    }    
}