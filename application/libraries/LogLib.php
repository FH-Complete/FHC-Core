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

	/**
	 * format
	 */
	private function format($class, $function, $line)
	{
		$formatted = LogLib::CALLER_PREFIX;

		if (!is_null($class) && $class != '')
		{
			$formatted .= $class.LogLib::CLASS_POSTFIX;
		}

		$formatted .= $function.LogLib::LINE_SEPARATOR.$line.LogLib::CALLER_POSTFIX.' ';

		return $formatted;
	}

	/**
	 * getCaller
	 */
	private function getCaller()
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

		if (isset($backtrace_arr[$lineIndex]['line']) && $backgrace_arr[$lineIndex]['line'] != '')
		{
			$line = $backtrace_arr[$lineIndex]['line'];
		}

		return $this->format($class, $function, $line);
	}

	/**
	 * log
	 */
	private function log($level, $message)
	{
		log_message($level, $this->getCaller().$message);
	}

	/**
	 * logDebug
	 */
	public function logDebug($message)
	{
		$this->log(LogLib::DEBUG, $message);
	}

	/**
	 * logInfo
	 */
	public function logInfo($message)
	{
		$this->log(LogLib::INFO, $message);
	}

	/**
	 * logError
	 */
	public function logError($message)
	{
		$this->log(LogLib::ERROR, $message);
	}
}
