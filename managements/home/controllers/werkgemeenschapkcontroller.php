<?php

namespace Managements\Home;

use Application\Controller as Application_Controller;

/**
* WerkgemeenschapkController
*/
class WerkgemeenschapkController extends Application_Controller
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
        $this->template = "werkgemeenschapk.html";
    }    
}