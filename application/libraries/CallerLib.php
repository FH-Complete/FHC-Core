<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library used to call a method of a model or a library
 */
class CallerLib
{
	const RESOURCE_PARAMETER = 'resource';
	const FUNCTION_PARAMETER = 'function';
	const REG_SPLIT_EXPR = '/\//';
	const LIB_PREFIX = 'Lib';
	const LIB_FILE_EXTENSION = '.php';
	const LIBS_PATH = 'libraries';
	const MODEL_PREFIX = '_model';
	
	// Black list of resources that are no allowed to be used
	private static $RESOURCES_BLACK_LIST = array(
		'CallerLib', // disabled self loading
		'LogLib', // hardly usefull and virtually dangerous
		'MigrationLib', // virtually dangerous, DB manipulation
		'FilesystemLib', // virtually dangerous, direct access to file system
		'PermissionLib' // usefull?
	);
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// Gets CI instance
		$this->ci =& get_instance();
		
		// Loads helper message to manage returning messages
		$this->ci->load->helper('message');
		
		// Loads permission library
		$this->ci->load->library('PermissionLib');
	}
	
	/**
	 * Wrapper method for _call
	 */
	public function callLibrary($callParameters, $permissionType)
	{
		return $this->_call($callParameters, $permissionType);
	}
	
	/**
	 * Wrapper method for _call
	 */
	public function callModel($callParameters, $permissionType)
	{
		return $this->_call($callParameters, $permissionType);
	}
	
	/**
	 * Everything starts here...
	 */
	private function _call($callParameters, $permissionType)
	{
		$result = null;
		$parameters = $this->_getParameters($callParameters);
		$validation = $this->_validateCall($parameters);
		
		// If the validation was passed
		if ($validation->error == EXIT_SUCCESS)
		{
			$loaded = null;
			// If the given resource is a model
			if (strpos($parameters->resourceName, CallerLib::MODEL_PREFIX) !== false)
			{
				// Try to load the model
				$result = $this->_loadModel($parameters->resourcePath, $parameters->resourceName);
				if ($result->error == EXIT_SUCCESS)
				{
					$loaded = $result->retval;
				}
			}
			// If the given resource is a library
			else if (strpos($parameters->resourceName, CallerLib::LIB_PREFIX) !== false)
			{
				// Check if the resource is already loaded, it works only with libraries and drivers
				$isLoaded = $this->ci->load->is_loaded($parameters->resourceName);
				// If not loaded then load it
				if ($isLoaded === false)
				{
					// Checks if the operation is permitted by the API caller
					// Only for libraries, permissions are automatically handled by models
					$result = $this->checkLibraryPermission(
							$parameters->resourcePath,
							$parameters->resourceName,
							$parameters->function,
							$permissionType
					);
					if ($result->error == EXIT_ERROR)
					{
						$loaded = null;
					}
					else
					{
						// Try to load the library
						$result = $this->_loadLibrary($parameters->resourcePath, $parameters->resourceName);
						if ($result->error == EXIT_SUCCESS)
						{
							$loaded = $result->retval;
						}
					}
				}
				// If it is already loaded $isLoaded contains the instance of the library
				else
				{
					$loaded = $isLoaded;
				}
			}
			// Wrong selection!
			else
			{
				$result = error('Neither a lib nor model: ' . $parameters->resourcePath . $parameters->resourceName);
			}
			
			// If the resource was found and loaded
			if (!is_null($loaded))
			{
				$result = $this->_callThis($parameters->resourceName, $parameters->function, $parameters->parameters);
			}
			else
			{
				// Resource not loaded
			}
		}
		else
		{
			$result = $validation;
		}
		
		return $result;
	}
	
	/**
	 * Gets the parameters from the http call
	 * Search for parameters <RESOURCE_PARAMETER> and <FUNCTION_PARAMETER>
	 * <RESOURCE_PARAMETER> is the name of the model or of the library
	 * <FUNCTION_PARAMETER> is the name of the method present in the model/library
	 * All the others parameters will be given to the method in the same order that
	 * they are present in the HTTP call
	 * EX:
	 * URL: ../system/CallerLibrary/Call?resource=<resource>&function=<method>&<par1>=<val1>&<par2>=<val2>&<par3>=<val3>
	 * will call <resource>.<method>(par1, par2, par3)
	 */
	private function _getParameters($parametersArray)
	{
		$parameters = new stdClass();
		$parameters->parameters = array();
		$count = 0;

		foreach ($parametersArray as $parameterName => $parameterValue)
		{
			// The name of the resource, path included
			if ($parameterName == CallerLib::RESOURCE_PARAMETER)
			{
				// Separates the resource path from the resource name
				$splittedResource = preg_split(CallerLib::REG_SPLIT_EXPR, $parameterValue);
				$parameters->resourceName = $splittedResource[count($splittedResource) - 1];
				$parameters->resourcePath = str_replace($parameters->resourceName, '', $parameterValue);
			}
			// The name of the function
			else if ($parameterName == CallerLib::FUNCTION_PARAMETER)
			{
				$parameters->function = $parameterValue;
			}
			// It is assumed that all other parameters are the parameters to be passed to the function
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
			return error('Parameter is not an object');
		}
		if (!isset($parameters->resourcePath))
		{
			return error('Resource path is not specified');
		}
		if (!isset($parameters->resourceName))
		{
			return error('Resource name is not specified');
		}
		if (!isset($parameters->function))
		{
			return error('Function is not specified');
		}
		if (!is_array($parameters->parameters))
		{
			return error('Parameters are not specified');
		}
		if (in_array($parameters->resourceName, CallerLib::$RESOURCES_BLACK_LIST))
		{
			return error('You are trying to access to unauthorized resources');
		}

		return success('Input data are valid');
	}

	/**
	 * Loads a model using the given path and name
	 * 
	 * NOTE: the models automatically handle the permissions
	 */
	private function _loadModel($resourcePath, $resourceName)
	{
		$loaded = null;
		$result = null;
		
		try
		{
			$loaded = $this->ci->load->model($resourcePath . $resourceName);
		}
		catch (Exception $e)
		{
			// Errors while loading the model
			$result = error('Errors while loading the model: ' . $e->getMessage());
		}
		
		if (!is_null($loaded))
		{
			$result = success($loaded);
		}
		
		return $result;
	}
	
	/**
	 * Search for a valid permission for this library that should be present with this format:
	 * '<library path>.<library name>.<library method name>' => '<permission>'
	 */
	private function checkLibraryPermission($resourcePath, $resourceName, $function, $permissionType)
	{
		$result = null;
		$permissionPath = '';
		
		if ($resourcePath != '')
		{
			$permissionPath = $resourcePath;
		}
		
		$permissionPath .= $resourceName . '.' . $function;
		
		if ($this->ci->permissionlib->isEntitled($permissionPath, $permissionType) === false)
		{
			$result = error(FHC_NORIGHT, FHC_NORIGHT);
		}
		else
		{
			$result = success('Has permission');
		}
		
		return $result;
	}
	
	/**
	 * Loads a library using the given path and name
	 * 
	 * The method 'library' of the class CI_Loader provided by CI has some limitations,
	 * so to be able to check errors was used a workaround.
	 * It consists in:
	 * - Checking if the file (identified by parameters $resourcePath and $resourceName) exists
	 * - If exists it will be loaded using the method 'file' from CI_Loader
	 * - Checks if the loaded file contains a class identified by parameter $resourceName
	 * 
	 * If one of the previous tests fails, it will be returned a null value
	 */
	private function _loadLibrary($resourcePath, $resourceName)
	{
		$loaded = null;
		
		try
		{
			// Gets all the configured resources paths
			$packagePaths = $this->ci->load->get_package_paths();
			// Looking for a file in every paths with the same name of the resource
			$found = null;
			for ($i = 0; $i < count($packagePaths) && is_null($found); $i++)
			{
				$file = $packagePaths[$i] . CallerLib::LIBS_PATH . DIRECTORY_SEPARATOR .
						$resourcePath . $resourceName . CallerLib::LIB_FILE_EXTENSION;
				if (file_exists($file))
				{
					$found = $file;
				}
			}

			// If the file was found
			if (!is_null($found))
			{
				// Load the file
				$loaded = $this->ci->load->file($found);
				// If the resource is not present inside the file
				if (!class_exists($resourceName))
				{
					$loaded = null;
					// Same phrase error as load->model() provided by CI
					$result = error($found . ' exists, but doesn\'t declare class ' . $resourceName);
				}
			}
			else
			{
				$loaded = null;
				// Same phrase error as load->model() provided by CI
				$result = error('Unable to load the requested class: ' . $resourceName);
			}
		}
		catch (Exception $e)
		{
			// Errors while loading the library
			$result = error('Errors while loading the library: ' . $e->getMessage());
		}
		
		if (!is_null($loaded))
		{
			$result = success($loaded);
		}
		
		return $result;
	}
	
	/**
	 * Calls a method of a class with the given parameters and returns its result
	 * 
	 * @param string $resourceName identifies the class name
	 * @param string $function identifies the method name
	 * @param array $parameters contains the parameters to be passed to the method
	 */
	private function _callThis($resourceName, $function, $parameters)
	{
		$result = null;
		
		try
		{
			// Get informations about the function
			$reflectionMethod = new ReflectionMethod($resourceName, $function);
			// If the number of given parameters is greater or equal to the number of
			// parameters required by the function
			if (count($parameters) >= $reflectionMethod->getNumberOfRequiredParameters())
			{
				// If the function is static
				if ($reflectionMethod->isStatic() === true)
				{
					$classMethod = $resourceName . '::' . $function;
				}
				// If the function is not static
				else
				{
					$classMethod = array(new $resourceName(), $function);
				}

				// If the resource's function is callable
				if (is_callable($classMethod))
				{
					
					// Call resource->function()
					// @ was applied to prevent really ugly and unmanageable errors
					$resultCall = @call_user_func_array($classMethod, $parameters);
					// If errors occurred while running it
					// NOTE: if the called function via call_user_func_array returns a boolean set as false,
					// it will be recognized like a running error. A little bit tricky ;)
					if ($resultCall === false)
					{
						$result = error('Error running ' . $resourceName . '->' . $function . '()');
					}
					// Returns the result of resource->function()
					else
					{
						$result = success($resultCall);
					}
				}
				else
				{
					$result = error($resourceName . '->' . $function . '() is not callable!');
				}
			}
			else
			{
				$result = error(
					'Number of required parameters: ' . $reflectionMethod->getNumberOfRequiredParameters() .
					'. Given: ' . count($parameters)
				);
			}
		}
		catch (Exception $e)
		{
			$result = error($e->getMessage());
		}
		
		return $result;
	}
}