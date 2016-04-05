<?php
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
/**
 * Database Class
 *
 */

class DBTools extends FHC_Controller
{
	private $cli = false;
	/**
	 * Path to seed classes
	 *
	 * @var string
	 */
	protected $_seed_path = APPPATH.'seeds/';

	/**
	 * Seed basename regex
	 *
	 * @var string
	 */
	protected $_seed_regex = '/^\d{3}_(\w+)$/';

	/**
	 * Initialize DB-Tools Class
	 *
	 * @return	void
	 */
	
    function __construct()
    {
        parent::__construct();
 		
		if ($this->input->is_cli_request())
		{
			$cli=true;
		}
		else
		{
			//$this->output->set_status_header(403, 'Migrations must be run from the CLI');
			//exit;
		}
		
        // can only be run in the development environment
        if (ENVIRONMENT !== 'development') {
            exit('Wowsers! You don\'t want to do that!');
        }
 		$this->load->database('system'); //Use the system-Connection for DB-Manipulation
		$this->load->library('migration');
 
		// If not set, set it
		//$this->_seed_path !== '' OR $this->_seed_path = APPPATH.'seeds/';
		// Add trailing slash if not set
		//$this->_seed_path = rtrim($this->_seed_path, '/').'/';

		// Load seed language
		$this->lang->load('seed');

        // initiate faker
        $this->faker = Faker\Factory::create();
 
        // load any required models
        //$this->load->model('person/Person_model');
		
		log_message('info', 'DB-Tools Controller Initialized');
    }
 
	public function index() 
	{
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.ci.php DBTools migrate [\"version_number\"]    Run all migrations. The version number is optional.\n";
        $result .= "php index.ci.php DBTools seed \"file_name\"              Run the specified seed file.\n";

        echo $result . PHP_EOL;
    }

	/**
	 * Migrate to latest or current version
	 *
	 * @param  string $version One of either "latest" or "current"
	 */
	public function migrate($version = 'latest')
    {
            
	    if ($this->cli && $this->migration->current() === FALSE)
	    {
	            show_error($this->migration->error_string());
	    }
		elseif ($version != 'latest' && $version != 'current')
		{
			$this->_failed('Migration version must be either latest or current');
		}

		if (!$this->migration->$version())
		{
			$this->_failed();
		}

		$this->_succeeded();
    }


	/**
	 * Migrate to a specific version
	 */
	public function version()
	{
		if ($version == 'latest' || $version == 'current')
		{
			$this->index($version);
			exit;
		}

		if (!$this->migrate->version($version))
		{
			$this->_failed();
		}

		$this->_succeeded();
	}

	/**
	 * Roll-back to the last version before current
	 *
	 * @param  int $version	The migration to rollback to, defaults to previous
	 */
	public function rollback($version = null)
	{
		if (is_null($version))
		{
			$version = $this->_get_version() ?: 1;
			$version--;
		}

		// Check it's definitely false, we could be rolling back to v0
		if (false === $this->migration->version($version))
		{
			$this->_failed();
		}

		$this->_succeeded('rolled back');
	}

	/**
	 * ROLLBACK ALL THE THINGS!
	 */
	public function uninstall()
	{
		$this->rollback(0);
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
	/**
	 * Extracts the seed class name from a filename
	 *
	 * @param	string	$seed
	 * @return	string	text portion of a migration filename
	 */
	protected function _get_seed_name($seed)
	{
		$parts = explode('_', $seed);
		array_shift($parts);
		return implode('_', $parts);
	}

	/**
	 * Yay, it worked! Tell the user.
	 *
	 * @param  string $task What did we just do? We...
	 */
	private function _succeeded($task = 'migrated')
	{
		$version = $this->_get_version();
		exit('Successfully '.$task.' to version '.$version);
	}

	/**
	 * Output an error message when it all goes tits up
	 *
	 * @param  string $message Error to output (default to CI's migration error)
	 */
	private function _failed($message = null)
	{
		$message = $message ?: $this->migration->error_string();
		show_error($message);
	}

	/**
	 * Carbon copy of parent::_get_version, but that's protected.
	 *
	 * @return int Currently installed migration number
	 */
	private function _get_version()
	{
		$row = $this->db->get('ci_migrations')->row();
		return $row ? $row->version : 0;
	}
}
