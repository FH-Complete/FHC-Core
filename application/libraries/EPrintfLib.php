<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Enhanced printf utility library
 * Usefull to print messages in a fancy and cool way from CLI and web interface
 */
class EPrintfLib
{
	// Prefixes and separator for messages
	const MSG_PREFIX = "[-]";
	const INFO_PREFIX = "[I]";
	const ERROR_PREFIX = "[E]";
	const SEPARATOR = "------------------------------";
	// Console colors codes
	const ERROR_COLOR = 31;
	const INFO_COLOR = 33;

	const PRINT_QUERY_LEN = 60;

	// HTML colors names
	private $HTML_COLORS = array(31 => "red", 33 => "orange");
	// Used to set if the migration process is called via command line or via browser
	private $cli;

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// Get code igniter instance
        $this->ci =& get_instance();

		$this->setCli();
	}

	// -------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Prints a formatted message
	 */
	public function printMessage($message)
	{
		$this->_print(EPrintfLib::MSG_PREFIX, $message);
	}

	/**
	 * Prints a formatted info
	 */
	public function printInfo($info)
	{
		$this->_print(EPrintfLib::INFO_PREFIX, $info, EPrintfLib::INFO_COLOR);
	}

	/**
	 * Prints a formatted error
	 */
	public function printError($error)
	{
		$this->_print(EPrintfLib::ERROR_PREFIX, $error, EPrintfLib::ERROR_COLOR);
	}

	/**
	 * Print only the end of line
	 */
	public function printEOL()
	{
		echo $this->getEOL();
	}

	// -------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Set property cli to false if the migration process is called via command line
	 * otherwise to false if it's called via browser
	 */
	private function setCli()
	{
		if ($this->ci->input->is_cli_request())
		{
			$this->cli = true;
		}
		else
		{
			$this->cli = false;
		}
	}

	/**
	 * Returns the character of end of line
	 * PHP_EOL platform dependent if cli is true
	 * Tag <br> if cli is false
	 */
	private function getEOL()
	{
		if ($this->cli === true)
		{
			return PHP_EOL;
		}
		else
		{
			return "<br>";
		}
	}

	/**
	 * Returns the string needed to color the output
	 */
	private function getColored($color)
	{
		$colored = "%s";

		if (!is_null($color))
		{
			if ($this->cli === true)
			{
				$colored = "\033[".$color."m%s\033[37m";
			}
			else
			{
				$colored = "<font color=\"".$this->HTML_COLORS[$color]."\">%s</font>";
			}
		}

		return $colored;
	}

	/**
	 * Print a message, even colored if specified
	 */
	private function _print($prefix, $text, $color = null)
	{
		printf($this->getColored($color), sprintf("%s %s".$this->getEOL(), $prefix, $text));
	}
}
