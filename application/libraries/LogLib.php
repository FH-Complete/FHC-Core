<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library usefull for logging!
 */
class LogLib
{
	const DEBUG = 'debug';
	const ERROR = 'error';
	const INFO = 'info';

	const CALLER_PREFIX = '[';
	const CALLER_POSTFIX = ']';
	const CLASS_POSTFIX = '->';
	const LINE_SEPARATOR = ':';

	// --------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * logDebug
	 */
	public function logDebug($message)
	{
		$this->_log(LogLib::DEBUG, $message);
	}

	/**
	 * logInfo
	 */
	public function logInfo($message)
	{
		$this->_log(LogLib::INFO, $message);
	}

	/**
	 * logError
	 */
	public function logError($message)
	{
		$this->_log(LogLib::ERROR, $message);
	}

	// --------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * log
	 */
	private function _log($level, $message)
	{
		log_message($level, $this->_getCaller().$message);
	}

	/**
	 * _getCaller
	 */
	private function _getCaller()
	{
		$classIndex = 3;
		$functionIndex = 3;
		$lineIndex = 2;
		$class = '';
		$function = '';
		$line = '';
		$backtrace_arr = debug_backtrace();
		if (isset($backtrace_arr[$classIndex]['class']) && $backtrace_arr[$classIndex]['class'] != '')
		{
			$class = $backtrace_arr[$classIndex]['class'];
		}

		if (isset($backtrace_arr[$functionIndex]['function']) && $backtrace_arr[$functionIndex]['function'] != '')
		{
			$function = $backtrace_arr[$functionIndex]['function'];
		}

		if (isset($backtrace_arr[$lineIndex]['line']) && $backtrace_arr[$lineIndex]['line'] != '')
		{
			$line = $backtrace_arr[$lineIndex]['line'];
		}

		return $this->_format($class, $function, $line);
	}

	/**
	 * format
	 */
	private function _format($class, $function, $line)
	{
		$formatted = LogLib::CALLER_PREFIX;

		if (!is_null($class) && $class != '')
		{
			$formatted .= $class.LogLib::CLASS_POSTFIX;
		}

		$formatted .= $function.LogLib::LINE_SEPARATOR.$line.LogLib::CALLER_POSTFIX.' ';

		return $formatted;
	}
}
