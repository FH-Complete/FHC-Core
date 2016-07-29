<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if(!defined("BASEPATH")) exit("No direct script access allowed");

class PCRM extends APIv1_Controller
{
	// Black list of resources that are no allowed to be used
	private static $RESOURCES_BLACK_LIST = array("LogLib", "FilesystemLib", "MigrationLib", "REST_Controller");
	
	/**
	 * Message API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return void
	 */
	public function getCall()
	{
		$parameters = $this->_getParameters($this->get());
		$validation = $this->_validateCall($parameters);
		
		// If the validation was passed
		if($validation->error == EXIT_SUCCESS)
		{
			$loaded = null;
			// Check if the resource is already loaded, it works only with libraries and drivers
			if (($loaded = $this->load->is_loaded($parameters->resourceName)) === false)
			{
				// If the given resource is a model
				if(strpos($parameters->resourceName, "_model") !== false)
				{
					try
					{
						// Try to load it
						$loaded = $this->load->model($parameters->resourcePath . $parameters->resourceName);
					}
					catch(Exception $e)
					{
						// Errors while loading the model
						$loaded = null;
						$result = $this->_error($e->getMessage());
					}
				}
				// If the given resource is a library
				else if(strpos($parameters->resourceName, "Lib") !== false)
				{
					// The method "library" of the class CI_Loader provided by CI has some limitations,
					// so to be able to check errors was used this workaround
					try
					{
						// Gets all the configured resources paths
						$packagePaths = $this->load->get_package_paths();
						// Looking for a file in every paths with the same name of the resource
						$found = null;
						for ($i = 0; $i < count($packagePaths) && is_null($found); $i++)
						{
							if (file_exists($packagePaths[$i] . "libraries/" . $parameters->resourcePath . $parameters->resourceName . ".php"))
							{
								$found = $packagePaths[$i] . "libraries/" . $parameters->resourcePath . $parameters->resourceName . ".php";
							}
						}
						
						// If the file was found
						if (!is_null($found))
						{
							// Load the file
							$loaded = $this->load->file($found);
							// If the resource is not present inside the file
							if (!class_exists($parameters->resourceName))
							{
								$loaded = null;
								// Same phrase error as load->model()
								$result = $this->_error($found . " exists, but doesn't declare class " . $parameters->resourceName);
							}
						}
						else
						{
							$loaded = null;
							// Same phrase error as load->model()
							$result = $this->_error("Unable to load the requested class: " . $parameters->resourceName);
						}
					}
					catch(Exception $e)
					{
						$result = $this->_error($e->getMessage());
					}
				}
				// Wrong selection!
				else
				{
					$result = $this->_error("Neither a lib nor model: " . $parameters->resourcePath . $parameters->resourceName);
				}
			}
			
			// If the resource was found and loaded
			if(!is_null($loaded))
			{
				try
				{
					// Get informations about the function
					$reflectionMethod = new ReflectionMethod($parameters->resourceName, $parameters->function);
					// If the number of given parameters is equal to the number of parameters required by the function
					if ($reflectionMethod->getNumberOfRequiredParameters() == count($parameters->parameters))
					{
						// If the function is static
						if ($reflectionMethod->isStatic() === true)
						{
							$classMethod = $parameters->resourceName . "::" . $parameters->function;
						}
						// If the function is not static
						else
						{
							$classMethod = array(new $parameters->resourceName(), $parameters->function);
						}
						
						// If the function of that resource is callable
						if(is_callable($classMethod))
						{
							// Call resource->function()
							$resultCall = @call_user_func_array($classMethod, $parameters->parameters);
							// If errors occurred while running it
							if($resultCall === false)
							{
								$result = $this->_error("Error running " . $parameters->resourceName . "->" . $parameters->function . "()");
							}
							// Returns the result of resource->function()
							else
							{
								$result = $resultCall;
							}
						}
						else
						{
							$result = $this->_error($parameters->resourceName . "->" . $parameters->function . "() is not callable!");
						}
					}
					else
					{
						$result = $this->_error("Wrong parameters number");
					}
				}
				catch(Exception $e)
				{
					$result = $this->_error($e->getMessage());
				}
			}
		}
		else
		{
			$result = $validation;
		}

		// Print the result
		$this->response($result, REST_Controller::HTTP_OK);
	}

	/**
	 * @return void
	 */
	public function postCall()
	{
		$validation = $this->_validatePostMessage($this->post());

		if(is_object($validation) && $validation->error == EXIT_SUCCESS)
		{
			$result = $this->messagelib->sendMessage(
					$this->post()['person_id'], $this->post()['subject'], $this->post()['body'], PRIORITY_NORMAL, $this->post()['relationmessage_id'], $this->post()['oe_kurzbz']
			);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}

	/**
	 * Gets the parameters from the call
	 */
	private function _getParameters($parametersArray)
	{
		$parameters = new stdClass();
		$parameters->parameters = array();
		$count = 0;

		foreach($parametersArray as $parameterName => $parameterValue)
		{
			// The name of the resource, path included
			if($parameterName == "resource")
			{
				// Separates the resource path from the resource name
				$splittedResource = preg_split("/\//", $parameterValue);
				$parameters->resourceName = $splittedResource[count($splittedResource) - 1];
				$parameters->resourcePath = str_replace($parameters->resourceName, "", $parameterValue);
			}
			// The name of the function
			else if($parameterName == "function")
			{
				$parameters->function = $parameterValue;
			}
			// It is assumed that all other parameters are parameters to be passed to the function
			// They will be passed to the function in the same order in which they are passed to
			// this controller
			else
			{
				$parameters->parameters[$count++] = $parameterValue;
			}
		}

		return $parameters;
	}

	/**
	 * Validate the given parameters
	 */
	private function _validateCall($parameters)
	{
		if (!is_object($parameters))
		{
			return $this->_error("Parameter is not an object");
		}
		if (!isset($parameters->resourcePath))
		{
			return $this->_error("Resource path is not specified");
		}
		if (!isset($parameters->resourceName))
		{
			return $this->_error("Resource name is not specified");
		}
		if (!isset($parameters->function))
		{
			return $this->_error("Function is not specified");
		}
		if (!is_array($parameters->parameters))
		{
			return $this->_error("Parameters are not specified");
		}
		if (in_array($parameters->resourceName, PCRM::$RESOURCES_BLACK_LIST))
		{
			return $this->_error("You are trying to access to unauthorized resources");
		}

		return $this->_success("Input data are valid");
	}
}