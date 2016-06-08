<?php

namespace FW;

/**
* Session
*/
class Session
{
	protected $config;

	protected $default_alert_type;
	protected $alert_types;

	function __construct($config)
	{
		$this->config = $config;

		$this->init_config_parameters();
		$this->init_session();
	}

	/**
	 * Initialize the config values
	 */
	protected function init_config_parameters()
	{
		$this->alert_types = $this->config->get_data("alert_types");
		$this->default_alert_type = $this->config->get_data("default_alert_type");
	}

	/**
	 * Initialize standard session variables
	 */
	protected function init_session()
	{
		$this->init_alert();
	}

	/**
	 * Initialize the alert functions
	 */
	protected function init_alert()
	{
		if(!array_key_exists("alert", $_SESSION)) {
			$_SESSION["alert"] = array();
			foreach ($this->alert_types as $alert_type) {
				$_SESSION["alert"][$alert_type] = array();
			}
		}
	}

	
	//GETTERS AND SETTERS
	/**
	 * Add an alert message.
	 * @var $message the alert message
	 * @var $type type of message. you can find the default types in the config.php file
	 * @var $key key to get the specific message later back
	 * @var $overwrite if set to true and the key has a value it will be overwritten.
	 */
	public function add_alert_message($message, $type = null, $key = null, $overwrite = true)
	{
		if($type === null || !in_array($type, $this->alert_types)) {
			$type = $this->default_alert_type;
		} 

		if($key === null) {
			$_SESSION["alert"][$type][] = $message;
		} else {
			if((array_key_exists($key, $_SESSION[$type]) && $overwrite) || !array_key_exists($key, $_SESSION[$type])){
				$_SESSION["alert"][$type][$key] = $message;
			}
		}
	}

	/**
	 * Delete alert messages.
	 * @var $type type of message to delete. if not set the whole alert session is deleted.
	 * @var $key message key to delete. if not set a whole type is deleted if the type is set.
	 */
	public function delete_alert_messages($type = null, $key = null)
	{
		if($type === null) {
			unset($_SESSION["alert"]);
			$this->init_alert();
		} else if($key === null && in_array($type, $this->alert_types)) {
			unset($_SESSION["alert"][$type]);
			$_SESSION["alert"][$type] = array();
		} else if(in_array($type, $this->alert_types) && array_key_exists($key, $_SESSION[$type])) {
			unset($_SESSION[$type][$key]);
		} else {
			throw new \Exception("Alert message not found", 1);
		}
	}

	/**
	 * Get an alert message.
	 * @var $type type of messages to get. if not set get all the types
	 * @var $key key of message to get. if not set get all messages in one type if the type is set.
	 */
	public function get_alert_messages($type = null, $key = null)		
	{
		if($type === null) {
			return $_SESSION["alert"];
		} else if($key === null && in_array($type, $this->alert_types)) {
			return $_SESSION["alert"][$type];
		} else if(in_array($type, $this->alert_types) && array_key_exists($key, $_SESSION[$type])) {
			return $_SESSION[$type][$key];
		} else {
			throw new \Exception("Alert message not found", 1);
		}
	}
}