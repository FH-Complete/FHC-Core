<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Utility class to be used in the database migration process
 */
class LogLib
{
	const DEBUG = "debug";
	const ERROR = "error";
	const INFO = "info";
	
	const CALLER_PREFIX = "[";
	const CALLER_POSTFIX = "]";
	const CLASS_POSTFIX = "->";
	const LINE_SEPARATOR = ":";
	
	/**
	 * Object initialization
	 */
	public function __construct() {}
	
	private function format($class, $function, $line)
	{
		$formatted = LogLib::CALLER_PREFIX;
		
		if (!is_null($class) && $class != "")
		{
			$formatted .= $class . LogLib::CLASS_POSTFIX;
		}
		
		$formatted .= $function . LogLib::LINE_SEPARATOR . $line . LogLib::CALLER_POSTFIX . " ";
		
		return $formatted;
	}
	
	private function getCaller()
	{
		$classIndex = 3;
		$functionIndex = 3;
		$lineIndex = 2;
		$class = "";
		$function = "";
		$line = "";

		if (isset(debug_backtrace()[$classIndex]["class"]) && debug_backtrace()[$classIndex]["class"] != "")
		{
			$class = debug_backtrace()[$classIndex]["class"];
		}

		if (isset(debug_backtrace()[$functionIndex]["function"]) && debug_backtrace()[$functionIndex]["function"] != "")
		{
			$function = debug_backtrace()[$functionIndex]["function"];
		}

		if (isset(debug_backtrace()[$lineIndex]["line"]) && debug_backtrace()[$lineIndex]["line"] != "")
		{
			$line = debug_backtrace()[$lineIndex]["line"];
		}

		return $this->format($class, $function, $line);
	}

	private function log($level, $message)
	{
		log_message($level, $this->getCaller() . $message);
	}

	/**
	 * 
	 */
	public function logDebug($message)
	{
		$this->log(LogLib::DEBUG, $message);
	}

	/**
	 * 
	 */
	public function logInfo($message)
	{
		$this->log(LogLib::INFO, $message);
	}

	/**
	 * 
	 */
	public function logError($message)
	{
		$this->log(LogLib::ERROR, $message);
	}
}