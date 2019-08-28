<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library usefull for logging!
 * This library can log using CodeIgniter log system (file system) or to database
 */
class LogLib
{
	// Log levels
	const INFO = 'info';
	const DEBUG = 'debug';
	const WARNING = 'warning';
	const ERROR = 'error';

	// Default debug trace levels
	const CLASS_INDEX = 3;
	const FUNCTION_INDEX = 3;
	const LINE_INDEX = 2;

	const DB_EXECUTE_USER = 'LogLib'; // Default execute user

	// Caller data names
	const CLASS_NAME = 'className';
	const FUNCTION_NAME = 'functionName';
	const CODE_LINE = 'codeLine';

	// To format the log message prefix when logging to file system
	const CALLER_PREFIX = '[';
	const CALLER_POSTFIX = ']';
	const CLASS_POSTFIX = '->';
	const LINE_SEPARATOR = ':';

	// CodeIgniter configuration log entry name and log debug value
	const CI_LOG_THRESHOLD_NAME = 'log_threshold';
	const CI_LOG_THRESHOLD_DEBUG = 2;

	// LogLib parameters names
	const P_NAME_CLASS_INDEX = 'classIndex';
	const P_NAME_FUNCTION_INDEX = 'functionIndex';
	const P_NAME_LINE_INDEX = 'lineIndex';
	const P_NAME_DB_LOG_TYPE = 'dbLogType';
	const P_NAME_DB_EXECUTE_USER = 'dbExecuteUser';

	// Properties used to retrieve caller data
	private $_classIndex;
	private $_functionIndex;
	private $_lineIndex;

	// Properties used when logging to database
	private $_dbLogType;
	private $_dbExecuteUser;

	/**
	 * Set properties to a default value or overwrites them with the given parameters
	 */
	public function __construct($params = null)
	{
		// Properties default values
		$this->_classIndex = self::CLASS_INDEX;
		$this->_functionIndex = self::FUNCTION_INDEX;
		$this->_lineIndex = self::LINE_INDEX;
		$this->_dbLogType = null;
		$this->_dbExecuteUser = self::DB_EXECUTE_USER;

		// If parameters are given then overwrite the default values
		if (!isEmptyArray($params))
		{
			if (isset($params[self::P_NAME_CLASS_INDEX])) $this->_classIndex = $params[self::P_NAME_CLASS_INDEX];
			if (isset($params[self::P_NAME_FUNCTION_INDEX])) $this->_functionIndex = $params[self::P_NAME_FUNCTION_INDEX];
			if (isset($params[self::P_NAME_LINE_INDEX])) $this->_lineIndex = $params[self::P_NAME_LINE_INDEX];
			if (isset($params[self::P_NAME_DB_LOG_TYPE])) $this->_dbLogType = $params[self::P_NAME_DB_LOG_TYPE];
			if (isset($params[self::P_NAME_DB_EXECUTE_USER])) $this->_dbExecuteUser = $params[self::P_NAME_DB_EXECUTE_USER];
		}
	}

	// --------------------------------------------------------------------------------------------------------------
	// Public methods based on CodeIgniter log system

	/**
	 * Writes a debug log to CodeIgniter log
	 */
	public function logDebug($message)
	{
		$this->_log(self::DEBUG, $message);
	}

	/**
	 * Writes an info log to CodeIgniter log
	 */
	public function logInfo($message)
	{
		$this->_log(self::INFO, $message);
	}

	/**
	 * Writes an error log to CodeIgniter log
	 */
	public function logError($message)
	{
		$this->_log(self::ERROR, $message);
	}

	// --------------------------------------------------------------------------------------------------------------
	// Public methods based on database

	/**
	 * Writes an info log to database
	 */
	public function logInfoDB($requestId, $data)
	{
		$this->_logDB(self::INFO, $requestId, $data);
	}

	/**
	 * Writes a debug log to database
	 */
	public function logDebugDB($requestId, $data)
	{
		$this->_logDB(self::DEBUG, $requestId, $data);
	}

	/**
	 * Writes an warning log to database
	 */
	public function logWarningDB($requestId, $data)
	{
		$this->_logDB(self::WARNING, $requestId, $data);
	}

	/**
	 * Writes an error log to database
	 */
	public function logErrorDB($requestId, $data)
	{
		$this->_logDB(self::ERROR, $requestId, $data);
	}

	// --------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Writes using CodeIgniter log system (file system)
	 */
	private function _log($level, $message)
	{
		log_message($level, $this->_getPrefix($this->_getCaller()).$message);
	}

	/**
	 * Writes logs to database
	 */
	private function _logDB($level, $requestId, $data)
	{
		// If the _dbLogType parameter was not given when this library was loaded
		// NOTE: this message will be displayed only to the developer AND stops the execution
		if ($this->_dbLogType == null)
		{
			show_error('To log to database you need to specify the "'.self::P_NAME_DB_LOG_TYPE.'" parameter when the LogLib is loaded');
		}

		$ci =& get_instance(); // get code igniter instance

		// If only debug log is enabed then is possible to write a debug log, otherwise...
		if ($level == self::DEBUG && $ci->config->item(self::CI_LOG_THRESHOLD_NAME) != self::CI_LOG_THRESHOLD_DEBUG)
		{
			// ...do nothing
		}
		else
		{
			// Loads WebservicelogModel
			$ci->load->model('system/Webservicelog_model', 'WebservicelogModel');

			// Get caller data
			$callerData = $this->_getCaller();

			// Writes a log to database
			$ci->WebservicelogModel->insert(array(
				'webservicetyp_kurzbz' => $this->_dbLogType,
				'request_id' => $requestId,
				'beschreibung' => $this->_getDatabaseDescription($callerData),
				'request_data' => $data,
				'execute_user' => $this->_dbExecuteUser,
				'execute_time' => 'NOW()' // current time
			));
		}
	}

	/**
	 * Retrieves caller's data
	 */
	private function _getCaller()
	{
		$class = '';
		$function = '';
		$line = '';
		$backtrace_arr = debug_backtrace();

		if (isset($backtrace_arr[$this->_classIndex]['class']) && $backtrace_arr[$this->_classIndex]['class'] != '')
		{
			$class = $backtrace_arr[$this->_classIndex]['class'];
		}

		if (isset($backtrace_arr[$this->_functionIndex]['function']) && $backtrace_arr[$this->_functionIndex]['function'] != '')
		{
			$function = $backtrace_arr[$this->_functionIndex]['function'];
		}

		if (isset($backtrace_arr[$this->_lineIndex]['line']) && $backtrace_arr[$this->_lineIndex]['line'] != '')
		{
			$line = $backtrace_arr[$this->_lineIndex]['line'];
		}

		return array(
			self::CLASS_NAME => $class,
			self::FUNCTION_NAME => $function,
			self::CODE_LINE => $line
		);
	}

	/**
	 * Formats the log message prefix (file system based)
	 */
	private function _getPrefix($callerData)
	{
		$formatted = self::CALLER_PREFIX;

		if (!isEmptyString($callerData[self::CLASS_NAME]))
		{
			$formatted .= $callerData[self::CLASS_NAME].self::CLASS_POSTFIX;
		}

		$formatted .= $callerData[self::FUNCTION_NAME].self::LINE_SEPARATOR.$callerData[self::CODE_LINE].self::CALLER_POSTFIX.' ';

		return $formatted;
	}

	/**
	 * Formats the database description for a log
	 */
	private function _getDatabaseDescription($callerData)
	{
		$formatted = $callerData[self::FUNCTION_NAME].self::LINE_SEPARATOR.$callerData[self::CODE_LINE];

		if (!isEmptyString($callerData[self::CLASS_NAME]))
		{
			$formatted = $callerData[self::CLASS_NAME].self::CLASS_POSTFIX.$formatted;
		}

		return $formatted;
	}
}
