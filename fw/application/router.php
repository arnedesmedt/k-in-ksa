<?php

namespace FW;

/**
* Router
*/
class Router
{
	protected $config; 
	protected $action_list;

	protected $path;
	protected $path_parts;

	protected $routes;

	protected $management;
	protected $name;
	protected $action;
	protected $parameters;
	
	function __construct($path, $config)
	{
		$this->config = $config;
		$this->action_list = $this->config->get_data("action_list");

		$this->path = preg_replace("/[- ]/", "_", strtolower($path));
		preg_match_all("/[a-z_]+/", $this->path, $matches);
		$this->path_parts = $matches[0];
		$this->routes = array();

		$this->init_routes();
		$this->init_routing_data();
	}

	/**
	 * Initialize all routes. 
	 * The routes is an array where the keys are routes and the values are arrays who are filled with the following data:
	 * 	- controller => name of the controller
	 *  - action => name of the action
	 * 	- parameters => an array with parameters to use
	 * 	- method => a method that can be called to fill the previous data-entries.
	 */
	protected function init_routes() 
	{
		$this->routes["([^\\/]+)"] = array(
			"method" => "set_controller_action_parameters",
		);
	}

	/**
	 * Set the controller, action and parameters by the default way:
	 * 	- The controllername is the first part of the URL
	 *  - The action is the second part of the URL
	 *  - The parameters are all the other parts of the URL.
	 */
	protected function set_controller_action_parameters($matches, &$route_data)
	{
		$data = $matches[1];
		if(!empty($data)) {
			$management = ucfirst(array_shift($data));
			$controller_parts = array();
			for ($i = 0; !empty($data); ) {
				if(in_array($data[$i], $this->action_list)) {
					break;
				}
				$controller_parts[] = ucfirst(array_shift($data));
			}
		}

		if(empty($controller_parts)) {
			$controller_parts[] = $management;
		}
		$controller = implode("\\", $controller_parts);
		
		if(isset($management)) {
			$route_data["management"] = $management;
			$route_data["controller"] = "Managements\\".$management."\\".$controller."Controller";
		}
		if(!empty($data)) $route_data["action"] = array_shift($data);
		if(!empty($data)) $route_data["parameters"] = $data;
	}

	/**
	 * Initialize routing data based on the path.
	 * The routing data to set is the controller, action and parameters.
	 */
	protected function init_routing_data()
	{
		if($this->path !== null) {
			foreach ($this->routes as $route => &$route_data) {
				if($result = preg_match_all("/".$route."/i", $this->path, $matches)) {
					$this->init_controller_data($route_data, $matches);
					break;
				}
			}
			$this->fill_empty_controller_data();
		} else {
			throw new \Exception("No path was found", 1);
		}
	}

	protected function init_controller_data(&$route_data, $matches)
	{
		//method
		if(array_key_exists("method", $route_data) && !empty($route_data["method"]) && method_exists($this, $route_data["method"])) {
			call_user_func_array(array($this, $route_data["method"]), array($matches, &$route_data));	
		}

		//parameters
		if(array_key_exists("parameters", $route_data) && !empty($route_data["parameters"])) {
			$this->parameters = $route_data["parameters"];
		}

		//action
		if(array_key_exists("action", $route_data) && !empty($route_data["action"])) {
			$this->action = $route_data["action"];
		} 

		//controller
		if(array_key_exists("controller", $route_data) && !empty($route_data["controller"])) {
			$this->name = $route_data["controller"];
		}

		if(array_key_exists("management", $route_data) && !empty($route_data["management"])) {
			$this->management = $route_data["management"];
		}
	}

	protected function fill_empty_controller_data()
	{
		if(empty($this->management)) {
			$this->management = $this->config->get_data("default_management");
		}
		if(empty($this->name)) {
			$this->name = $this->config->get_data("default_name");
		}
		if(empty($this->action)) {
			$this->action = $this->config->get_data("default_action");
		}
		if(empty($this->parameters)) {
			$this->parameters = $this->config->get_data("default_parameters");
		}
	}

	//GETTERS AND SETTERS
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
}