<?php

namespace Managements\Ksaactiviteiten;

use Application\Controller as Application_Controller;

/**
* PagaddersController
*/
class PagaddersController extends Application_Controller
{
    public function index()
    {
        parent::index();
        $this->template = "pagadders.html";
    }    
}