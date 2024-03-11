<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller using JSON
 */
class FHCAPI_Controller extends FHC_Controller
{

	/**
	 * Response status
	 * @see https://github.com/omniti-labs/jsend
	 */
	const STATUS_SUCCESS = 'success';
	const STATUS_FAIL = 'fail';
	const STATUS_ERROR = 'error';

	/**
	 * Error types
	 */
	const ERROR_TYPE_PHP = 'php'; // TODO(chris): php types from severity?
	const ERROR_TYPE_EXCEPTION = 'exception';
	const ERROR_TYPE_GENERAL = 'general';
	const ERROR_TYPE_404 = '404';
	const ERROR_TYPE_DB = 'db';
	const ERROR_TYPE_VALIDATION = 'validation';

	/**
	 * Return Object
	 *
	 * @var array
	 */
	private $returnObj = [];


	/**
	 * Constructor
	 *
	 * @param array					$requiredPermissions
	 * @return void
	 */
	public function __construct($requiredPermissions = [])
	{
		if (is_cli())
			show_404();

		parent::__construct();
		
		$this->config->set_item('error_views_path', VIEWPATH.'errors'.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR);

		global $g_result;
		$g_result = $this;

		ob_start(function ($content) {
			$http_response_code = http_response_code();
			// NOTE(chris): For security reasons 404 will be displayed the same everywhere
			if ($http_response_code == REST_Controller::HTTP_NOT_FOUND)
				return $content;

			header('Content-Type: application/json; charset=utf-8');

			if (!isset($this->returnObj['meta']) || !isset($this->returnObj['meta']['status'])) {
				switch ($http_response_code) {
					case 200:
						$this->setStatus(self::STATUS_SUCCESS);
						break;
					case 400:
						$this->setStatus(self::STATUS_FAIL);
						break;
					default:
						$this->setStatus(self::STATUS_ERROR);
						break;
				}
			}

			#$this->returnObj['test'] = implode('/n', headers_list());

			return json_encode($this->returnObj);
		});

		// Load libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');

		// Checks if the caller is allowed to access to this content
		$this->_isAllowed($requiredPermissions);

		// For JSON Requests (as opposed to multipart/form-data) get the $_POST variable from the input stream instead
		if ($this->input->get_request_header('Content-Type', true) == 'application/json')
			$_POST = json_decode($this->security->xss_clean($this->input->raw_input_stream), true);
		elseif (isset($_POST['_jsondata'])) {
			$_POST = array_merge($_POST, json_decode($_POST['_jsondata'], true));
			unset($_POST['_jsondata']);
		}
	}


	// ---------------------------------------------------------------
	// Handle Output object
	// ---------------------------------------------------------------

	/**
	 * @param array					$data
	 * @param string				$type (optional)
	 * @return void
	 */
	public function addError($data, $type = null)
	{
		if (!isset($this->returnObj['errors']))
			$this->returnObj['errors'] = [];

		$error = [];
		
		if (is_array($data)) {
			if ($type == self::ERROR_TYPE_VALIDATION)
				$error['messages'] = $data;
			else
				$error = $data;
		} else {
			$error['message'] = $data;
		}
		
		if ($type)
			$error['type'] = $type;

		$this->returnObj['errors'][] = $error;
	}

	/**
	 * @param mixed					$data
	 * @return void
	 */
	public function setData($data)
	{
		$this->returnObj['data'] = $data;
	}

	/**
	 * @param string				$status
	 * @return void
	 */
	public function setStatus($status)
	{
		if (!isset($this->returnObj['meta']))
			$this->returnObj['meta'] = [];
		$this->returnObj['meta']['status'] = $status;
	}


	// ---------------------------------------------------------------
	// Handle Output object - Shortcut functions
	// ---------------------------------------------------------------

	/**
	 * @param array					$errors
	 * @return void
	 */
	protected function terminateWithValidationErrors($errors)
	{
		$this->output->set_status_header(REST_Controller::HTTP_BAD_REQUEST);
		$this->addError($errors, self::ERROR_TYPE_VALIDATION);
		$this->setStatus(self::STATUS_FAIL);
		exit(EXIT_ERROR);
	}

	/**
	 * @param mixed					$data (optional)
	 * @return void
	 */
	protected function terminateWithSuccess($data = null)
	{
		$this->setData($data);
		$this->setStatus(self::STATUS_SUCCESS);
		exit;
	}

	/**
	 * @param array					$error
	 * @param string				$type (optional)
	 * @return void
	 */
	protected function terminateWithError($error, $type = null)
	{
		$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		$this->addError($error, $type);
		$this->setStatus(self::STATUS_ERROR);
		exit;
	}

	/**
	 * @param stdclass				$result
	 * @param string				$errortype
	 * @return void
	 */
	protected function checkForErrors($result, $errortype = self::ERROR_TYPE_GENERAL)
	{
		// TODO(chris): IMPLEMENT!
		if (isError($result)) {
			$this->terminateWithError(getError($result), $errortype);
		}
		return $result->retval;
	}

	// TODO(chris): complete list


	// ---------------------------------------------------------------
	// Security
	// ---------------------------------------------------------------

	/**
	 * Checks if the caller is allowed to access to this content with the given permissions
	 * If it is not allowed will set the HTTP header with code 401
	 * Wrapper for permissionlib->isEntitled
	 *
	 * @param array					$requiredPermissions
	 * @return void
	 */
	protected function _isAllowed($requiredPermissions)
	{
		// Checks if this user is entitled to access to this content
		if (!$this->permissionlib->isEntitled($requiredPermissions, $this->router->method))
		{
			$this->output->set_status_header(isLogged() ? REST_Controller::HTTP_FORBIDDEN : REST_Controller::HTTP_UNAUTHORIZED);

			$this->addError([
				'message' => 'You are not allowed to access to this content',
				'controller' => $this->router->class,
				'method' => $this->router->method,
				'required_permissions' => $this->_rpsToString($requiredPermissions, $this->router->method)
			]);
			exit; // immediately terminate the execution
		}
	}

	/**
	 * Converts an array of permissions to a string that contains them as a comma separated list
	 * Ex: "<permission 1>, <permission 2>, <permission 3>"
	 *
	 * @param array					$requiredPermissions
	 * @param string				$method
	 * @return void
	 */
	protected function _rpsToString($requiredPermissions, $method)
	{
		if (!isset($requiredPermissions[$method]))
			return '';

		if (!is_array($requiredPermissions[$method]))
			return $requiredPermissions[$method];

		return implode(', ', $requiredPermissions[$method]);
	}
}
