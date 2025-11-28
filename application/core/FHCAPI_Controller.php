<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Controller using JSON
 */
class FHCAPI_Controller extends Auth_Controller
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
	const ERROR_TYPE_PHP = 'php';
	const ERROR_TYPE_EXCEPTION = 'exception';
	const ERROR_TYPE_GENERAL = 'general';
	const ERROR_TYPE_404 = '404';
	const ERROR_TYPE_DB = 'db';
	const ERROR_TYPE_VALIDATION = 'validation';
	const ERROR_TYPE_AUTH = 'auth';

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

			return json_encode($this->returnObj);
		});

		// NOTE(chris): overwrite error_views_path before constructor
		load_class('Config')->set_item('error_views_path', VIEWPATH.'errors'.DIRECTORY_SEPARATOR.'json'.DIRECTORY_SEPARATOR);

		parent::__construct($requiredPermissions);
		
		// For JSON Requests (as opposed to multipart/form-data) get the $_POST variable from the input stream instead
		if ($this->input->get_request_header('Content-Type', true) == 'application/json')
		{
			$_POST = json_decode($this->input->raw_input_stream, true);
		}
		elseif (isset($_POST['_jsondata']))
		{
			$_POST = array_merge($_POST, json_decode($_POST['_jsondata'], true));
			unset($_POST['_jsondata']);
		}
	}


	// ---------------------------------------------------------------
	// Handle Output object
	// ---------------------------------------------------------------

	/**
	 * @param string|array|object	$data
	 * @param string				$type (optional)
	 * @return void
	 */
	public function addError($data, $type = null)
	{
		if (!isset($this->returnObj['errors']))
			$this->returnObj['errors'] = [];

		$error = [];
		
		if (is_array($data)) {
			if ($type == self::ERROR_TYPE_VALIDATION) {
				$error['messages'] = $data;
			} elseif (array_is_list($data)) {
				foreach ($data as $d)
					$this->addError($d, $type);
				return;
			} else {
				$error = $data;
			}
		} elseif (is_object($data)) {
			$error = (array)$data;
		} else {
			$error['message'] = $data;
		}
		
		if ($type)
			$error['type'] = $type;

		if (!isset($error['type']))
			$error['type'] = self::ERROR_TYPE_GENERAL;

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
	 * @param string				$key
	 * @param mixed					$value
	 * @return void
	 */
	public function addMeta($key, $value)
	{
		if (!isset($this->returnObj['meta']))
			$this->returnObj['meta'] = [];
		$this->returnObj['meta'][$key] = $value;
	}

	/**
	 * @param string				$key
	 * @return mixed
	 */
	public function getMeta($key)
	{
		if (!isset($this->returnObj['meta']))
			return null;
		if (!isset($this->returnObj['meta'][$key]))
			return null;
		return $this->returnObj['meta'][$key];
	}

	/**
	 * @param string				$status
	 * @return void
	 */
	public function setStatus($status)
	{
		$this->addMeta('status', $status);
	}


	// ---------------------------------------------------------------
	// Handle Output object - Shortcut functions
	// ---------------------------------------------------------------

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
	 * @param string|array|object					$error
	 * @param string				$type (optional)
	 * @param integer				$status (optional)
	 * @return void
	 */
	protected function terminateWithError($error, $type = null, $status = REST_Controller::HTTP_INTERNAL_SERVER_ERROR)
	{
		$this->output->set_status_header($status);
		$this->addError($error, $type);
		$this->setStatus(self::STATUS_ERROR);
		exit;
	}

	/**
	 * @param stdclass				$result
	 * @param string				$errortype
	 * @return mixed
	 */
	protected function getDataOrTerminateWithError($result, $errortype = self::ERROR_TYPE_GENERAL)
	{
		if (isError($result)) {
			$this->terminateWithError(getError($result), $errortype);
		}
		return $result->retval;
	}

	protected function terminateWithFileOutput($contenttype, $content, $filename=null)
	{
		$this->clearOutputBuffering();
		$this->output->set_status_header(200)
			->set_content_type($contenttype)
			->set_header('Expires: 0')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate')
			->set_header('Pragma: public')
			->set_header('Content-Length: ' . strlen($content));

		if($filename)
		{
			$cleanedfilename = preg_replace('/[^a-zA-Z0-9\-_.]/', '_', $filename);
			$this->output->set_header('Content-Disposition: attachment; filename="'
				. $cleanedfilename . '"');
		}
		else
		{
			$this->output->set_header('Content-Disposition: inline');
		}

		$this->output->set_output($content)
			->_display();
		exit();
	}

	private function clearOutputBuffering()
	{
		while(ob_get_level() > 0)
		{
			ob_end_clean();
		}
	}

	// ---------------------------------------------------------------
	// Security Begin
	// ---------------------------------------------------------------

	/**
	 * Outputs an error message and sets the HTTP Header.
	 * This overwrites the default behaviour to output a json object.
	 *
	 * @param array					$requiredPermissions
	 * @return void
	 */
	protected function _outputAuthError($requiredPermissions)
	{
		$this->output->set_status_header(isLogged() ? REST_Controller::HTTP_FORBIDDEN : REST_Controller::HTTP_UNAUTHORIZED);

		$this->addError([
			'message' => 'You are not allowed to access to this content',
			'controller' => $this->router->class,
			'method' => $this->router->method,
			'required_permissions' => $this->_rpsToString($requiredPermissions, $this->router->method)
		], self::ERROR_TYPE_AUTH);
	}

	// ---------------------------------------------------------------
	// Security End
	// ---------------------------------------------------------------

	/**
	 * Checks the client's total request size (Content-Length) against the minimum
	 * effective PHP limit (min of upload_max_filesize, post_max_size, memory_limit).
	 * This preempts failures that result in vague "missing parameters" errors on large files.
	 *
	 * @return void
	 */
	protected function checkUploadSize() {
		$content_length = (int)$this->input->server('CONTENT_LENGTH');

		//get max serverside size upload
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$max_upload_mb = min($max_upload, $max_post, $memory_limit);    // smallest of 3 config values

		if($content_length >= $max_upload_mb) {
			$this->terminateWithError($this->p->t('global', 'filesizeExceeded'), 'general');
		}
	}
}
