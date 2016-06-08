<?php

namespace FW;

use Managements\Form as Form;

/**
* Controller
*/
class Controller
{

	protected $config;
	protected $database;
	protected $session;
	
	//controller
	protected $management;
	protected $name;
	protected $action;
	protected $parameters;

	//environment
	protected $environment;

	//application
	protected $application_directory;
	protected $application_name;

	//templating
	protected $twig;
	protected $template;
	protected $context;
	protected $mobile;
	protected $favicon;

	//context
	private $stylesheets;
	private $headscripts;
	private $bodyscripts;
	private $handlebars_templates;

	//subcontrollers
	protected $subcontrollers;

	
	function __construct($management, $action, $parameters)
	{
		preg_match("/[a-zA-Z]+Controller$/", get_class($this), $match);
		$this->name = strtolower(substr($match[0], 0, -10));
		$this->management = $management;
		$this->action = $action;
		$this->parameters = $parameters;

		$this->init_parameters();
	}

	//INIT

	/**
	 * Initialize the parameters.
	 */
	private function init_parameters()
	{
		$this->stylesheets = array();
		$this->headscripts = array();
		$this->bodyscripts = array();
		$this->handlebars_templates = array();
		$this->subcontrollers = array();
		$this->template = $this->action.".html";
	}

	/**
	 * Initialize all data
	 */
	public function init()
	{
		$this->init_stylesheets();
		$this->init_scripts();
		$this->init_handlebars_templates();
		$this->init_alerts();
		$this->init_forms();
	}

	/**
	 * Initialize stylesheets
	 */
	protected function init_stylesheets()
	{

	}

	/**
	 * Initialize scripts
	 */
	protected function init_scripts()
	{
		$this->add_bodyscript("/fw/resources/scripts/jquery-2.1.1.min.js");
		$this->add_bodyscript("/fw/resources/scripts/handlebars-v2.0.0.js");
		$this->add_bodyscript("/fw/resources/scripts/handlebars-init.js");
	}

	/**
	 * Initialize scripts
	 */
	protected function init_handlebars_templates()
	{

	}

	/**
	 * Initialize alerts
	 */
	protected function init_alerts()
	{
		if(!empty($this->session)) {
			$alerts = $this->session->get_alert_messages();
			$this->context["alerts"] = $alerts;
			$this->session->delete_alert_messages();
		}
	}

	/**
	 * Initialize forms
	 */
	protected function init_forms()
	{
		if(empty($this->forms)) {
			$this->forms = array(
				"'".$this->name."'",
			);
		}
	}

	//END INIT

	public function execute_action($init = true)
	{
		if($init) {
			$this->init();
		}
		if(method_exists($this, $this->action)) {
			return call_user_func(array($this, $this->action));
		}
	}

	//INDEX

	/**
	 * Index action
	 */
	public function index()
	{
		
	}

	//END INDEX

	//EDIT

	/**
	 * Edit action
	 */
	public function edit()
	{
		$this->subcontrollers["form"] = new Form\FormController($this->config, "Form", "index", array());


		// $this->context["forms"] = $this->form_controller->get_forms($this->forms);
		// var_dump($this->context["forms"]);
	}

	//END EDIT

	//SAVE

	/**
	 * Save action
	 */
	public function save()
	{
		$this->change_before_save();
		$models = $this->format_post_to_save_models();
		$result = $this->save_models($models);
		$this->process_result($result);
		$success_save = $this->execute_after_save($result);
		if($success_save && $success_alert = $this->get_save_success_alert()) {
			$this->session->add_alert_message($success_alert);
		} else if($danger_alert = $this->get_save_danger_alert()) {
			$this->session->add_alert_message($danger_alert, "danger");
		}
		//header("Location: ".$_POST["redirect"]);
	}

	/**
	 * Change data from the POST variable before it has to be saved.
	 */
	protected function change_before_save()
	{
		
	}

	/**
	 * Get the data from the POST variable and put it into models to save it.
	 */
	private function format_post_to_save_models()
	{
		$models = array();
		foreach ($_POST["save"] as $table_name => $data) {
			$models[$table_name] = array();
			$database_models = $this->database->get_model_by_table_name($table_name);
			$model = array_shift($database_models);
			$model_name = $model["name"];

			if($this->is_multiple($data)) {
				foreach ($data as $entry) {
					$model = new $model_name();
					$model->add_insert_data($data);
					$models[$table_name][] = $model;
				}
			} else {
				$model = new $model_name();
				$model->add_insert_data($data);
				$models[$table_name] = $model;
			}
		}
		return $models;
	}

	/**
	 * Save the models.
	 */
	protected function save_models($models)
	{
		return $this->database->insert($models);
	}

	/**
	 * Check if the result was the expected.
	 */
	protected function process_result(&$result)
	{
		
	}

	/**
	 * Execute some actions after the models are saved.
	 */
	protected function execute_after_save($result)
	{
		return true;
	}

	/**
	 * Get the alert if the save was a succes.
	 */
	protected function get_save_success_alert()
	{
		return false;
	}

	/**
	 * Get the alert if the save has failed.
	 */
	protected function get_save_danger_alert()
	{
		return false;
	}

	/**
	 * Check if their are multiple models to store.
	 */
	private function is_multiple($data)
	{
		if(is_array($data)) {
			return !(bool) count(array_filter(array_keys($data), "is_string"));
		} else {
			return false;
		}
	}

	//END SAVE

	//FORMS

	/**
	 * If data is needed for the forms, like select boxes
	 * this function calls an get_formname_fieldname_field function to get the data
	 */
	public function init_field_data(&$data)
	{
		$method_name = "get_{$data["form_name"]}_{$data["name"]}_field";
		if(method_exists($this, $method_name)) {
			$this->$method_name($data);
		}
	}

	//END FORMS

	//RESOURCES

	/**
	 * Order the resources
	 */
	private function order_resources()
	{
		asort($this->stylesheets, SORT_NUMERIC);
		asort($this->headscripts, SORT_NUMERIC);
		asort($this->bodyscripts, SORT_NUMERIC);
		$this->stylesheets = array_keys($this->stylesheets);
		$this->headscripts = array_keys($this->headscripts);
		$this->bodyscripts = array_keys($this->bodyscripts);
	}

	public function add_stylesheet($stylesheet, $position = null, $before = false)
	{
		$this->add_resource($stylesheet, "stylesheets", $position, $before);
	}

	public function add_headscript($headscript, $position = null, $before = false)
	{
		$this->add_resource($headscript, "headscripts", $position, $before);
	}

	public function add_bodyscript($bodyscript, $position = null, $before = false)
	{
		$this->add_resource($bodyscript, "bodyscripts", $position, $before);
	}

	public function add_handlebars_template($template)
	{
		$this->add_resource($template, "handlebars_templates");
	}

	private function add_resource($resource, $resource_name, $position = null, $before = false)
	{
		if($position === null) {
			$max_order_number = count($this->$resource_name) == 0 ? 0 : max($this->$resource_name);
			$this->{$resource_name}[$resource] = $max_order_number + 1;
		} else if(is_int($position)) {
			if($before) {
				$this->$resource_name = array($resource => $position) + $this->$resource_name;
			} else {
				$this->{$resource_name}[$resource] = $position;
			}
		} else if(is_string($position) && array_key_exists($position, $this->$resource_name)) {
			if($before) {
				$this->$resource_name = array($resource => $this->resource[$position]) + $this->$resource_name;
				
			} else {
				$this->{$resource_name}[$resource] = $this->resource[$position];
			}
		}
	}

	//END RESOURCES

	//TEMPLATING

	/**
	 * Build the context to send to the template.
	 */
	public function get_context()
	{
		$this->context["environment"] = $this->get_environment();
		$this->context["controller"] = array(
			"name" => $this->name,
		);
		$this->order_resources();
		$this->context["stylesheets"] = $this->stylesheets;
		$this->context["headscripts"] = $this->headscripts;
		$this->context["bodyscripts"] = $this->bodyscripts;
		$this->context["url"] = $this->get_url();
		$this->handlebars_templates = array_keys($this->handlebars_templates);
		$this->get_content($this->handlebars_templates);
		$this->context["handlebars_templates"] = $this->handlebars_templates;
		$this->context["favicon"] = $this->get_favicon();
		$this->context["mobile"] = $this->mobile;
		return $this->context;
	}

	private function get_favicon()
	{
		switch(strtolower(pathinfo($this->favicon, PATHINFO_EXTENSION))) {
			case "png":
				$type = "png";
			break;
			case "ico":
				$type = "x-icon";
			break;
		}

		return array(
			"type" => $type,
			"name" => $this->favicon,
		);
	}

	private function get_content(&$links)
	{
		foreach ($links as &$link) {
			if(file_exists($this->application_directory.$link)) {
				$link = file_get_contents($this->application_directory.$link);
			}
		}
	}

	/**
	 * Get the template.
	 */
	public function get_template()
	{
		if($this->template[0] == "@") {
			//absolute template
			return $this->template;
		} else {
			//relative template
			$prefix = "";
			$file = $this->application_directory.DIRECTORY_SEPARATOR."managements".DIRECTORY_SEPARATOR.$this->management.DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR.$this->name.DIRECTORY_SEPARATOR.$this->template;
			if($this->name != $this->management && file_exists($file)) {
				$prefix = $this->name.DIRECTORY_SEPARATOR;
			}
			return "@".$this->get_name()."/".$prefix.$this->template;
		}
	}

	//END TEMPLATING

	//GETTERS AND SETTERS
	public function set_application_directory($application_directory)
	{
		$this->application_directory = $application_directory;
	}

	public function set_application_name($application_name)
	{
		$this->application_name = $application_name;
	}

	public function set_environment($environment)
	{
		$this->environment = $environment;
	}

	public function get_environment()
	{
		return $this->environment;
	}

	public function get_management()
	{
		return $this->management;
	}

	public function get_name()
	{
		return $this->name;
	}

	public function get_action()
	{
		return $this->action;
	}

	public function get_parameters()
	{
		return $this->parameters;
	}

	public function set_config($config)
	{
		$this->config = $config;
	}

	public function get_config()
	{
		return $this->config;
	}

	public function set_database($database)
	{
		$this->database = $database;
	}

	public function get_database()
	{
		return $this->database;
	}

	public function set_session($session)
	{
		$this->session = $session;
	}

	public function get_session()
	{
		return $this->session;
	}

	public function set_twig($twig)
	{
		$this->twig = $twig;
	}

	public function get_twig()
	{
		return $this->twig;
	}

	/**
	 * Get the base path. Frequently used in images and other resources.
	 */
	public function get_base_path()
	{
		$environment = empty($this->environment) ? "" : $this->environment."/";
		return "/".$environment.$this->get_name()."/";
	}

	public function get_url()
	{
		return $this->get_base_path().$this->action."/".implode("/", $this->parameters)."/";
	}

	public function set_subcontroller($name, $subcontroller, $overwrite = false)
	{
		if((!$overwrite && !array_key_exists($name, $this->subcontrollers)) || $overwrite) {
			$this->subcontrollers[$name] = $subcontroller;
		}
	}

	public function get_subcontroller($name)
	{
		if(array_key_exists($name, $this->subcontrollers)) {
			return $this->subcontrollers[$name];
		} 
		return false;
	}
}