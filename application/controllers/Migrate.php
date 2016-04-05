<?php

class Migrate extends FHC_Controller
{
	private $class_version = '1.0';
	private $cli = false;

	public function __construct()
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
		$this->load->database('system'); //Use the system-Connection for DB-Manipulation
		$this->load->library('migration');
		$this->load->library('FHC_Seed');
	}
        

 public function help() {
        $result = "The following are the available command line interface commands\n\n";
        $result .= "php index.ci.php db migrate [\"version_number\"]    Run all migrations. The version number is optional.\n";
        $result .= "php index.ci.php db seed \"file_name\"              Run the specified seed file. Filename is optional.\n";

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

		if (!$this->migration->version())
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
	 * Seed DB with TestData
	 *
	 * @param  string $name Name of the SeedFile
	 */

	public function seed($name = null) 
	{
        $this->fhc_seed->seed($name);
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

	public function about()
	{
		echo "CI-Migrate_CLI v".$this->class_version;
		echo "\nCheck http://github.com/dshoreman/ci-migrate_cli/ for updates";
		exit;
	}
}
