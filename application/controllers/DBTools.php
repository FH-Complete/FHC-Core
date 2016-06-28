<?php
 
if (! defined('BASEPATH'))
	exit('No direct script access allowed');
 
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
	protected $seed_path;

	/**
	 * Seed basename regex
	 *
	 * @var string
	 */
	protected $seed_regex = '/^\d{3}_(\w+)$/';

	/**
	 * Initialize DB-Tools Class
	 *
	 * @return	void
	 */
    public function __construct()
    {
        parent::__construct();
 		
		$this->seed_path = APPPATH.'seeds/';
		
		if ($this->input->is_cli_request())
		{
			$cli = true;
		}
		else
		{
			//$this->output->set_status_header(403, 'Migrations must be run from the CLI');
			//exit;
		}
		
        // can only be run in the development environment
        if (ENVIRONMENT !== 'development')
            exit('Wowsers! You don\'t want to do that!');
 		$this->load->database('system'); //Use the system-Connection for DB-Manipulation
		$this->config->load('migration');
		$this->load->library('migration');
 
		// If not set, set it
		$this->seed_path !== '' OR $this->seed_path = APPPATH.'seeds/';
		// Add trailing slash if not set
		$this->seed_path = rtrim($this->seed_path, '/').'/';

		// Load seed language
		$this->lang->load('seed');

        // initiate faker
        $this->faker = Faker\Factory::create();
 
        // load any required models
        //$this->load->model('person/Person_model');
		
		log_message('info', 'DB-Tools Controller Initialized');
    }
 
	/**
	 * Main function index as help
	 *
	 * @return	void
	 */
    public function index()
	{
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.ci.php DBTools migrate [\"version_number\"]    Run migrations. (latest/current) ";
		$result .= "The version number is optional.\n";
        $result .= "php index.ci.php DBTools seed [\"file_name\"]              Run the specified seed (Name of Seed. expl: 'Organisation').\n";

        echo $result.PHP_EOL;
    }

	/**
	 * Migrate to latest or current version
	 *
	 * @param  string $version [optional] One of either "latest" or "current"
	 * @return	void
	 */
	public function migrate($version = 'latest')
    {
		echo 'DB-Migration';
	    if ($version != 'latest' && $version != 'current')
		{
			$this->__failed('Migration version must be either latest or current');
		}
		elseif ($this->cli && !$this->migration->$version())
	    {
	        show_error($this->migration->error_string());
	    }
		elseif (!$this->migration->$version())
		{
			$this->__failed();
		}

		$this->__succeeded();
    }


	/**
	 * Migrate to a specific version
	 *
	 * @return	void
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
			$this->__failed();
		}

		$this->__succeeded();
	}

	/**
	 * Roll-back to the last version before current
	 *
	 * @param  int $version	The migration to rollback to, defaults to previous
	 * @return	void
	 */
	public function rollback($version = null)
	{
		if (is_null($version))
		{
			$version = $this->__getVersion() ?: 1;
			$version--;
		}

		// Check it's definitely false, we could be rolling back to v0
		if (false === $this->migration->version($version))
		{
			$this->__failed();
		}

		$this->__succeeded('rolled back');
	}

	/**
	 * ROLLBACK ALL THE THINGS!
	 *
	 * @return	void
	 */
	public function uninstall()
	{
		$this->rollback(0);
	}

    /**
	 * Seeds DB with Testdata
	 *
	 * @param	string	$name Name of the seed file.
	 * @return	bool
	 */
	public function seed($name = null)
	{
		$seeds = $this->findSeeds();

		if (empty($seeds))
		{
			$this->_error_string = $this->lang->line('seed_none_found');
			return false;
		}

		$method = 'seed';
		$pending = array();

		
		foreach ($seeds as $number => $file)
		{
			include_once($file);
			$class = 'Seed_'.ucfirst(strtolower($this->_getSeedName(basename($file, '.php'))));

			// Validate the seed file structure
			if (! class_exists($class, false))
			{
				$this->_error_string = sprintf($this->lang->line('seed_class_doesnt_exist'), $class);
				return false;
			}
			// method_exists() returns true for non-public methods,
			// while is_callable() can't be used without instantiating.
			// Only get_class_methods() satisfies both conditions.
			elseif (! in_array($method, array_map('strtolower', get_class_methods($class))))
			{
				$this->_error_string = sprintf($this->lang->line('seed_missing_'.$method.'_method'), $class);
				return false;
			}

			$pending[$number] = array($class, $method);
		}
		
		// Now just run the necessary seeds
		foreach ($pending as $number => $seed)
		{
			if (is_null($name))
			{
				log_message('debug', 'Seeding '.$method);
			
				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
			elseif ($seed[0] == 'Seed_'.$name)
			{
				log_message('debug', 'Seeding '.$method);
			
				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
		}
	}
 
    /**
	 * Retrieves list of available seed files
	 *
	 * @return	array	list of seed file paths sorted by version
	 */
	public function findSeeds()
	{
		$seeds = array();

		// Load all *_*.php files in the seeds path
		foreach (glob($this->seed_path.'*_*.php') as $file)
		{
			$name = basename($file, '.php');

			// Filter out non-seed files
			if (preg_match($this->seed_regex, $name))
			{
				$number = $this->_getSeedNumber($name);

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
	 * Truncate DB from Testdata
	 *
	 * @param	string	$name Name of the seed file.
	 * @return	bool
	 */
	public function truncate($name)
	{
		$seeds = $this->findSeeds();

		if (empty($name))
		{
			$this->_error_string = $this->lang->line('seed_none_found');
			return false;
		}

		$method = 'truncate';
		$pending = array();

		
		foreach ($seeds as $number => $file)
		{
			include_once($file);
			$class = 'Seed_'.ucfirst(strtolower($this->_getSeedName(basename($file, '.php'))));

			// Validate the seed file structure
			if (! class_exists($class, false))
			{
				$this->_error_string = sprintf($this->lang->line('seed_class_doesnt_exist'), $class);
				return false;
			}
			// method_exists() returns true for non-public methods,
			// while is_callable() can't be used without instantiating.
			// Only get_class_methods() satisfies both conditions.
			elseif (! in_array($method, array_map('strtolower', get_class_methods($class))))
			{
				$this->_error_string = sprintf($this->lang->line('seed_missing_'.$method.'_method'), $class);
				return false;
			}

			$pending[$number] = array($class, $method);
		}
		
		// Now just run the necessary seeds
		foreach ($pending as $number => $seed)
		{
			if (is_null($name))
			{
				log_message('debug', 'Seeding '.$method);
			
				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
			elseif ($seed[0] == 'Seed_'.$name)
			{
				log_message('debug', 'Seeding '.$method);
			
				$seed[0] = new $seed[0];
				call_user_func($seed);
			}
		}
	}
	/**
	 * Extracts the seed number from a filename
	 *
	 * @param	string	$seed Filename of the seed.
	 * @return	string	Numeric portion of a seed filename
	 */
	protected function _getSeedNumber($seed)
	{
		return sscanf($seed, '%[0-9]+', $number)
			? $number : '0';
	}
	/**
	 * Extracts the seed class name from a filename
	 *
	 * @param	string	$seed Filename of the seed.
	 * @return	string	text portion of a migration filename
	 */
	protected function _getSeedName($seed)
	{
		$parts = explode('_', $seed);
		array_shift($parts);
		return implode('_', $parts);
	}

	/**
	 * Yay, it worked! Tell the user.
	 *
	 * @param  string $task What did we just do? We...
	 * @return void
	 */
	private function __succeeded($task = 'migrated')
	{
		$version = $this->__getVersion();
		exit('Successfully '.$task.' to version '.$version);
	}

	/**
	 * Output an error message when it all goes tits up
	 *
	 * @param  string $message Error to output (default to CI's migration error)
	 * @return void
	 */
	private function __failed($message = null)
	{
		$message = $message ?: $this->migration->error_string();
		show_error($message);
	}

	/**
	 * Carbon copy of parent::__getVersion, but that's protected.
	 *
	 * @return int Currently installed migration number
	 */
	private function __getVersion()
	{
		$row = $this->db->get($this->config->item('migration_table'))->row();
		return $row ? $row->version : 0;
	}
}
