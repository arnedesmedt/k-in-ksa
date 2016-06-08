<?php

namespace Managements\Home;

use Application\Controller as Application_Controller;

/**
* HomeController
*/
class HomeController extends Application_Controller
{
    public function index()
    {
        $this->context["menu"] = array(
            array(
                "title" => "De toren",
                "url" => "/home/toren",
            ),
            array(
                "title" => "De open K",
                "url" => "/home/openk",
            ),
            array(
                "title" => "De werkgemeenschap K",
                "url" => "/home/werkgemeenschapk",
            ),
        );
    }

    public function init_stylesheets()
    {
        parent::init_stylesheets();
    }

    public function init_scripts()
    {
        parent::init_scripts();
        $this->add_bodyscript("/managements/home/resources/scripts/home.js");
    }
    
}