<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This is the super class for all those controllers that can only be called from command line
 * It provides also an helper to display the possible calls
 */
abstract class CLI_Controller extends FHC_Controller
{
	const INFO_FORMAT = '%s %s %s %s'; // Info message format
	const REQUIRED_PARAM_FORMAT = ' %s'; // Info message required method parameter format
	const OPTIONAL_PARAM_FORMAT = ' (%s)'; // Info message optional method parameter format
	const BLACKLIST_METHODS = array('__construct', 'index', 'get_instance'); // Methods to NOT display with _printHelp

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Checks if the controller is called from command line
		$this->_isAllowed();
	}

	/**
	 * By default the index is used to print the help message to explain how to call them from command line
	 */
	public function index()
	{
		$this->printHelp();
	}

	/**
	 * Retrieves all the public methods of the called controller and display
	 * a help message to explain how to call them from command line
	 */
	protected function printHelp()
	{
		$this->load->library('EPrintfLib'); // loads the EPrintfLib to format the output

		$this->eprintflib->printInfo('The following are the available commands:');
		$this->eprintflib->printInfo('');

		// Gets a list of public methods of the called controller
		$reflectionClass = new ReflectionClass($this->router->class);
		$methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

		// Loops through them
		foreach ($methods as $method)
		{
			// Disascard the black listed methods
			if (!in_array($method->name, self::BLACKLIST_METHODS))
			{
				// Formatted message
				$info = sprintf(
					self::INFO_FORMAT,		// Message format
					PHP_BINARY,				// PHP binary name
					index_page(),			// CI index page name
					$this->router->class,	// The called controller name
					$method->name			// The called controller current method name
				);

				// Retrieves the parameter names for the current method
				$reflectionMethod = new ReflectionMethod($this->router->class, $method->name);
				$parameters = $reflectionMethod->getParameters();

				// Loops through them
				foreach ($parameters as $parameter)
				{
					$info .= sprintf(
						$parameter->isOptional() ? self::OPTIONAL_PARAM_FORMAT : self::REQUIRED_PARAM_FORMAT, // Parameter message format required/optional
						$parameter->getName() // Current method parameter name
					);
				}

				// Print the info message
				$this->eprintflib->printInfo($info);
				$this->eprintflib->printInfo('');
			}
		}
	}

	/**
	 * Checks if the controller is called from the command line, if NOT the execution is immediately stopped
	 */
	private function _isAllowed()
	{
		if (!$this->input->is_cli_request())
		{
			$this->output->set_status_header(REST_Controller::HTTP_UNAUTHORIZED);

			$this->load->library('EPrintfLib'); // loads the EPrintfLib to format the output

			// Prints the main error message
			$this->eprintflib->printError('You are not authorized to access this content');
			// Prints the called controller name
			$this->eprintflib->printInfo('Controller name: '.$this->router->class);
			// Prints the called controller method name
			$this->eprintflib->printInfo('Method name: '.$this->router->method);

			exit;
		}
	}
}

