<?php

namespace FW;

/**
* Config
*/
class Config
{
	//autoload
	protected $autoload_directories;

	//routing parameters
	protected $use_environment;
	protected $default_environment;
	protected $action_list;

	//model-view-controller parameters
	protected $default_name;
	protected $default_management;
	protected $default_action;
	protected $default_parameters;
	protected $default_template;

	//database parameters
	protected $db_host;
	protected $db_username;
	protected $db_password;
	protected $db_name;

	protected $charset;
	protected $engine;
	protected $collate;
	protected $default_column_type;
	protected $default_reference_column_changes;
	protected $default_relation_delete_value;
	protected $default_relation_update_value;
	protected $log_queries;
	
	protected $default_join;
	protected $default_combine;
	protected $default_operand;
	protected $default_order_by_type;

	//form parameters
	protected $default_form_action;
	protected $default_form_redirect;

	//session parameters
	protected $alert_types;
	protected $default_alert_type;

	//error pages
	protected $error_page_404;
	protected $error_page_503;
	
	function __construct()
	{
		$this->init_parameters();	
	}

	/**
	 * Initialize the config parameters.
	 */
	protected function init_parameters()
	{
		//autoload
		$this->autoload_directories = array(
			"application",
			"controllers",
			"models",
			"views",
		);

		//routing parameters
		$this->use_environment = true;
		$this->default_environment = "admin";
		$this->action_list = array(
			"index",
			"edit",
			"save",
			"delete",
		);

		//model-view-controller parameters
		$this->default_management = "home";
		$this->default_name = "Managements\\Home\\HomeController";
		$this->default_action = "index";
		$this->default_parameters = array();
		$this->default_template = "index.html";

		//database parameters
		$this->charset = "utf8";
		$this->engine = "InnoDB";
		$this->collate = "utf8_unicode_ci";
		$this->default_column_type = "varchar";
		$this->default_reference_column_changes = array(
            "null" => false,
            "auto increment" => false,
        );
        $this->default_relation_type = "mo";
        $this->default_relation_delete_value = "no action";
        $this->default_relation_update_value = "no action";
		$this->log_queries = true;
		$this->default_join = "inner";
		$this->default_combine = "and";
		$this->default_operand = "=";
		$this->default_order_by_type = "asc";

		//form parameters
		$this->default_form_action = "save";
		$this->default_form_redirect = "index";

		//session parameters
		$this->alert_types = array(
			"success",
			"danger",
			"warning",
		);
		$this->default_alert_type = "success";	

		//error pages
		$this->error_page_404 = "/error/index/404/";
		$this->error_page_503 = "/error/index/503/";
	}

	//GETTERS AND SETTERS

	/**
	 * Get multiple data items.
	 */
	public function get_multiple_data($data_names)
	{
		if(is_array($data_names)) {
			$data = array();
			foreach ($data_names as $data_name) {
				$data[$data_name] = $this->get_data($data_name);
			}
			return $data;
		}

		return false;
	}

	/**
	 * Get one data item.
	 */
	public function get_data($data_name)
	{
		if(is_string($data_name)) {
			if(method_exists($this, $data_name)) {
				return $this->$data_name();
			}

			if(property_exists($this, $data_name)) {
				return $this->$data_name;
			}
		}

		return false;
	}
}