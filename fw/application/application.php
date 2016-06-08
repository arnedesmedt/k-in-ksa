<?php
namespace FW;

session_start();
setlocale(LC_ALL, "nl_BE");

require_once __DIR__."/../../fw/application/fw.php";
require_once __DIR__."/../../fw/application/constants.php";

/**
* Application
*/
class Application
{
	protected $autoload_directories;
	protected $localhost;

	protected $use_environment;
	protected $environment;
	protected $environment_is_directory;

	protected $application_directory;
	protected $application_name;

	protected $config;
	protected $router;
	protected $session;
	protected $database;
	protected $twig;
	
	function __construct()
	{
		$this->init_application_data();
		$this->init_config();
		$this->init_environment();
		$this->init_autoloader();
		$this->init_router();
		$this->init_session();
		$this->init_database();
		$this->init_controller();
		$this->init_twig();

		$this->controller->execute_action();

		// //render html output
		echo $this->twig->render($this->controller->get_template(), $this->controller->get_context());
	}

	//INIT

	/**
	 * Initialize the application data.
	 */
	protected function init_application_data()
	{
		$reflection = new \ReflectionClass($this);
		$this->application_directory = dirname(dirname(dirname($reflection->getFileName())));
		$this->application_name = basename(dirname(dirname($reflection->getFileName())));
		$this->localhost = in_array($_SERVER["REMOTE_ADDR"], array("127.0.0.1","::1"));
	}

	/**
	 * Initialize the config object.
	 */
	protected function init_config()
	{
		require_once $this->application_directory.DS.$this->application_name.DS."application".DS."config.php";
		$config_name = "\\".ucfirst($this->application_name)."\\Config";
		$this->config = new $config_name();
	}

	/**
	 * Initialize the environment if it is used.
	 */
	protected function init_environment()
	{
		$this->use_environment = $this->config->get_data("use_environment");

		if ($this->use_environment) {
			$this->environment = array_key_exists("environment", $_GET) ? $_GET["environment"] : $this->config->get_data("default_environment");
			$this->environment_is_directory = file_exists($this->application_directory.DS.$this->environment);
		} else if (array_key_exists("environment", $_GET)) {
			$_GET["path"] = $_GET["environment"].(array_key_exists("path", $_GET) ? "/".$_GET["path"] : "");
		}
	}

	/**
	 * Initialize the autoloader.
	 */
	protected function init_autoloader()
	{
		$this->autoload_directories = $this->config->get_data("autoload_directories");
		spl_autoload_register(array($this, "autoload"));
	}

	/**
	 * Initialize the router.
	 */
	protected function init_router()
	{
		$router_name = "\\".ucfirst($this->application_name)."\\Router";
		$this->router = new $router_name(array_key_exists("path", $_GET) ? $_GET["path"] : "", $this->config);
	}

	/**
	 * Initialize the session.
	 */
	protected function init_session()
	{
		$session_name = "\\".ucfirst($this->application_name)."\\Session";
		$this->session = new $session_name($this->config);
	}

	/**
	 * Initialize the database.
	 */
	protected function init_database()
	{
		$this->database = new Database($this->config, $this->environment);
	}

	/**
	 * Initialize the controller.
	 */
	protected function init_controller()
	{
		$management = $this->router->get_management();
		$name = $this->router->get_name();
		$action = $this->router->get_action();
		$parameters = $this->router->get_parameters();
		$this->controller = new $name($management, $action, $parameters);
		$this->controller->set_database($this->database);
	}

	/**
	 * Initialize twig.
	 */
	private function init_twig()
	{
		require_once $this->application_directory.DS."fw".DS."vendor".DS."twig".DS."twig".DS."lib".DS."Twig".DS."Autoloader.php";
		\Twig_Autoloader::register();

		$loader = new \Twig_Loader_Filesystem();
		$loader->addPath($this->application_directory.DS."fw".DS."views", "fw");
		$loader->addPath($this->application_directory.DS.$this->application_name.DS."views", $this->application_name);
		$loader->addPath($this->application_directory.DS."managements".DS.strtolower($this->controller->get_management()).DS."views", $this->controller->get_name());
		if($this->environment_is_directory && !empty($this->environment)) {
			$loader->addPath($this->application_directory.DS.$this->environment.DS."views", $this->environment);
		}

		$this->twig = new \Twig_Environment($loader, array(
			"debug" => $this->localhost,
		));

		if($this->localhost) {
			$this->twig->addExtension(new \Twig_Extension_Debug());
		}
		$this->controller->set_twig($this->twig);
	}

	//END INIT

	//AUTOLOAD

	/**
	 * Autoload files based on the class name.
	 */
	public function autoload($classname)
	{
		$class_parts = explode("\\", strtolower($classname));
		$last_part = array_pop($class_parts);

		if(count($class_parts) > 0) {
			$path = null;
			foreach ($this->autoload_directories as $directory) {
				$file = implode(DS, $class_parts).DS.$directory.DS.$last_part.".php";
				if(file_exists($file)){
					$path = $file;
					break;
				}
			}
			if($path !== null) {
				require_once $this->application_directory.DS.$path;
			}
		}
	}

	//END AUTOLOAD
}