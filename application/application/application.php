<?php
namespace Application;

use FW\Application as FW_Application;

require_once __DIR__."/../../fw/application/application.php";


/**
* Application
*/
class Application extends FW_Application
{

    protected function init_controller()
    {
        parent::init_controller();
        $this->controller->set_environment($this->environment);
        $this->controller->set_application_directory($this->application_directory);
        $this->controller->set_application_name($this->application_name);
        $this->controller->set_session($this->session);
        $this->controller->set_config($this->config);
        $this->controller->set_database($this->database);
    }
}