<?php

namespace Application;

use FW\Controller as FW_Controller;

/**
* Controller
*/
class Controller extends FW_Controller
{
    function __construct($management, $action, $parameters)
    {
        parent::__construct($management, $action, $parameters);
        $this->mobile = false;
        $this->favicon = "/application/resources/favicon.ico";
    }

	protected function init_scripts()
	{
		parent::init_scripts();
        $this->add_bodyscript("/{$this->application_name}/resources/libraries/jquery-ui-1.11.2/jquery-ui.min.js");
		$this->add_bodyscript("/{$this->application_name}/resources/scripts/bootstrap.js");
		$this->add_bodyscript("/{$this->application_name}/resources/scripts/bootstrap-init.js");
        $this->add_bodyscript("/{$this->application_name}/resources/libraries//fancybox/source/jquery.fancybox.pack.js?v=2.1.5");
        $this->add_bodyscript("/{$this->application_name}/resources/scripts/overview.js");
        $this->add_bodyscript("/{$this->application_name}/resources/libraries/resizeimagemap/resizeimagemap.js");
    }

    protected function init_stylesheets()
    {
        parent::init_stylesheets();
        $this->add_stylesheet("/{$this->application_name}/resources/stylesheets/styles.css");
        $this->add_stylesheet("/{$this->application_name}/resources/libraries/font-awesome-4.2.0/css/font-awesome.min.css");
        $this->add_stylesheet("/{$this->application_name}/resources/libraries/jquery-ui-1.11.2/jquery-ui.min.css");
        $this->add_stylesheet("/application/resources/libraries/fancybox/source/jquery.fancybox.css?v=2.1.5");
    }

    public function index()
    {
        $this->context["menu"] = $this->get_menu();

        $ban = explode("\\", str_replace("Controller", "", get_class($this)));
        $ban = strtolower(array_pop($ban));

        $query = "SELECT * FROM method WHERE verdiep = '".strtolower($this->management)."' AND ban = '{$ban}' ORDER BY volgorde, id;";
        $result = $this->database->query($query);
        $this->context["methods"] = $this->database->result_to_array($result);
    }

    protected function get_menu()
    {
        $menu = array(
            array(
                "title" => "Info",
                "url" => "/".strtolower($this->management)."/info",
            ),
            array(
                "title" => "Kabouters",
                "url" => "/".strtolower($this->management)."/kabouters",
            ),
            array(
                "title" => "Pagadders",
                "url" => "/".strtolower($this->management)."/pagadders",
            ),
            array(
                "title" => "Jongknapen",
                "url" => "/".strtolower($this->management)."/jongknapen",
            ),
            array(
                "title" => "Knapen",
                "url" => "/".strtolower($this->management)."/knapen",
            ),
            array(
                "title" => "Jonghernieuwers",
                "url" => "/".strtolower($this->management)."/jonghernieuwers",
            ),
            array(
                "title" => "Hernieuwers",
                "url" => "/".strtolower($this->management)."/hernieuwers",
            ),
        );
        return $menu;
    }

    public function check_login()
    {
        if($_SESSION["logged_in"]) {
            return true;
        } else {
            header("Location: /admin/login");
        }
    }
}