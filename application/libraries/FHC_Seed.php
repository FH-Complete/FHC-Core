<?php
/**
 * FH-Complete
 *
 * @package	FHC-Helper
 * @author	FHC-Team
 * @copyright	Copyright (c) 2016 fhcomplete.org
 * @license	GPLv3
 * @link	https://fhcomplete.org
 * @since	Version 1.0.0
 * @filesource
 */
if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * FHC-Seed Library
 *
 * @package		FH-Complete
 * @subpackage	DB
 * @category	Library
 * @author		FHC-Team
 * @link		http://fhcomplete.org/user_guide/libraries/fhc_seed.html
 */

// ------------------------------------------------------------------------

class FHC_Seed
{	
	/**
	 * Path to seed classes
	 *
	 * @var string
	 */
	protected $_seed_path = NULL;

	/**
	 * Seed basename regex
	 *
	 * @var string
	 */
	protected $_seed_regex = '/^\d{3}_(\w+)$/';

	/**
	 * Initialize Seed Class
	 *
	 * @param	array	$config
	 * @return	void
	 */
	public function __construct($config = array())
	{
		// Only run this constructor on main library load
		if ( ! in_array(get_class($this), array('FHC_Seed', config_item('subclass_prefix').'Seed'), TRUE))
		{
			return;
		}

		foreach ($config as $key => $val)
		{
			$this->{'_'.$key} = $val;
		}

		log_message('info', 'Seed Class Initialized');

		// If not set, set it
		$this->_seed_path !== '' OR $this->_seed_path = APPPATH.'seeds/';

		// Add trailing slash if not set
		$this->_seed_path = rtrim($this->_seed_path, '/').'/';

		// Load seed language
		$this->lang->load('seed');
	}


	/**
	 * Seeds DB with Testdata
	 *
	 * @param	string	$name
	 * @return	bool
	 */
	function seed($name = null)
	{
		$seeds = $this->find_seeds();

		if (empty($seeds))
		{
			$this->_error_string = $this->lang->line('seed_none_found');
			return FALSE;
		}

		$method = 'seed';
		$pending = array();
		foreach ($seeds as $number => $file)
		{
			include_once($file);
			$class = 'Seed_'.ucfirst(strtolower($this->_get_seed_name(basename($file, '.php'))));

			// Validate the seed file structure
			if ( ! class_exists($class, FALSE))
			{
				$this->_error_string = sprintf($this->lang->line('seed_class_doesnt_exist'), $class);
				return FALSE;
			}
			// method_exists() returns true for non-public methods,
			// while is_callable() can't be used without instantiating.
			// Only get_class_methods() satisfies both conditions.
			elseif ( ! in_array($method, array_map('strtolower', get_class_methods($class))))
			{
				$this->_error_string = sprintf($this->lang->line('seed_missing_'.$method.'_method'), $class);
				return FALSE;
			}

			$pending[$number] = array($class, $method);
		}
		// Now just run the necessary seeds
		foreach ($pending as $number => $seed)
		{
			log_message('debug', 'Seeding '.$method);

			$seed[0] = new $seed[0];
			call_user_func($seed);
		}
	}

	/**
	 * Retrieves list of available seed files
	 *
	 * @return	array	list of seed file paths sorted by version
	 */
	public function find_seeds()
	{
		$seeds = array();

		// Load all *_*.php files in the seeds path
		foreach (glob($this->_seed_path.'*_*.php') as $file)
		{
			$name = basename($file, '.php');

			// Filter out non-seed files
			if (preg_match($this->_seed_regex, $name))
			{
				$number = $this->_get_seed_number($name);

				// There cannot be duplicate seed numbers
				if (isset($seeds[$number]))
				{
					$this->_error_string = sprintf($this->lang->line('seed_multiple_version'), $number);
					show_error($this->_error_string);
				}

				$seeds[$number] = $file;
			}
		}

		ksort($seeds);
		return $seeds;
	}

	/**
	 * Extracts the seed number from a filename
	 *
	 * @param	string	$seed
	 * @return	string	Numeric portion of a seed filename
	 */
	protected function _get_seed_number($seed)
	{
		return sscanf($seed, '%[0-9]+', $number)
			? $number : '0';
	}
}
