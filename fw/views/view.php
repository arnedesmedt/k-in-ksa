<?php

namespace FW;


/**
* View
*/
class View
{
    protected $controller;
    protected $config;
    protected $twig; 
    
    protected $mgmt;
    protected $template;
    protected $data;

	function __construct($data = null)
	{
        preg_match_all("/([^\\\\\/]+)/", get_class($this), $matches);  
        $this->mgmt = strtolower($matches[0][1]);
        $this->template = strtolower($matches[0][2]).".html";
        $this->data = $data;
    }

    public function init()
    {
        if(method_exists($this, "init_data")) {
            $this->init_data();
        }
        $this->init_twig();
    }

    protected function init_twig()
    {
        global $application_dir;
        
        $loader = new \Twig_Loader_Filesystem();
        $loader->addPath($application_dir.DIRECTORY_SEPARATOR."managements".DIRECTORY_SEPARATOR.$this->mgmt.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR, $this->mgmt);

        $extra = array();
        if(!empty($this->controller)) {
            $extra = array(
                "debug" => $this->controller->get_config()->get_data("is_localhost"),
            );
        }
        $this->twig = new \Twig_Environment($loader, $extra);

        $this->twig->addExtension(new \Twig_Extension_Debug());
    }

    public function render()
    {
        echo $this->twig->render("@".$this->mgmt."/".$this->template, $this->data);
    }

    //GETTERS AND SETTERS
    public function set_controller($controller)
    {
        $this->controller = $controller;
    }    

    public function set_config($config)
    {
        $this->config = $config;
    }
}