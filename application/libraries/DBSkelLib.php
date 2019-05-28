<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * DBSkel logic
 */
class DBSkelLib
{
	// Configuration parameters
	const CONF_ENABLED = 'dbskel_enabled';
	const CONF_MODE = 'dbskel_mode';

	// Run modes
	const RUN_MODE_DRYRUN = 'dryrun'; // run without changing the database, useful for testing
	const RUN_MODE_NEW = 'new'; // build a new database or if database is already present creates only new objects
	const RUN_MODE_DIFF = 'diff'; // like new, but it also remove object from database that are NOT present in configuration files

	// Configuration file names
	const SCHEMA_FILENAME = 'schema.sql'; // File name that contains schema creation SQL and SQL to comment a schema
	const SEQUENCES_FILENAME = 'sequences.php'; // PHP file that contains all the sequences
	const TABLE_PREFIX = 'TBL-'; // Table file prefix
	const VIEWS_FILENAME = 'views.php'; // PHP file that contains all the views
	const FUNCTIONS_FILENAME = 'functions.php'; // PHP file that contains all the functions
	const GRANTS_FILENAME = 'grants.sql'; // Grants SQL file name
	const EXTRA_FILENAME = 'extra.sql'; // Extra SQL file name

	// JSON file extension
	const JSON_EXT = '.json';

	// Directory that containts the database skel
	const DBSKEL_DIR = APPPATH.'dbskel/';

	// Tables JSON properties name
	const JSON_NAME = 'name';
	const JSON_SQL = 'sql';

	private $_ci; // Code igniter instance

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// Loads DB conns and confs using system settings
		$this->_ci->load->database('system');

		// Loads dbskel configs
		$this->_ci->config->load('dbskel');

		// Loads library EPrintfLib
		$this->_ci->load->library('EPrintfLib');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Starts the DBSkel procedure
	 * Returns false on failure and true on success
	 * All errors/warnings/infos are printed here using EPrintfLib
	 */
	public function start()
	{
		$start = false;

		// Checks if DBSkel is enabled
		if ($this->_ci->config->item(self::CONF_ENABLED) === true)
		{
			// Gets all the directories in application/dbskel
			$start = $this->_processDirectories(glob(self::DBSKEL_DIR.'*', GLOB_ONLYDIR));

			$this->_printSchemaSeparator();
		}
		else
		{
			$this->_printMessage('DBSkel is NOT enabled');
		}

		return $start;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Process every single directory present in dbskel directory
	 */
	private function _processDirectories($directories)
	{
		$processDirectories = false; // failure by default

		// For each directory
		foreach ($directories as $directory)
		{
			$processDirectories = false; // Reset to false at the beginning of each loop

			$this->_printSchemaSeparator();
			$this->_printInfo('Found directory >>> '.basename($directory).' <<<');

			// NOTE: the order in which these methods are called has a meaning!
			// If a step fails then the loop is stopped

			// 1 - Checks file naming convention in current directory
			// NOTE: no need to check a failure! NOT a blocking check!!
			$this->_checkFilenaming($directory);

			$this->_printFileSeparator();

			// 2 - Process schema file
			if (!$this->_processSchemaFile($directory)) break;

			$this->_printFileSeparator();

			// 3 - Process sequence file
			if (!$this->_processSequencesFile($directory)) break;

			$this->_printFileSeparator();

			// 4 - Process table files
			if (!$this->_processTableFiles($directory)) break;

			$this->_printFileSeparator();

			// 5 - Process views file
			if (!$this->_processViewsFile($directory)) break;

			$this->_printFileSeparator();

			// 6 - Process functions file
			if (!$this->_processFunctionsFile($directory)) break;

			$this->_printFileSeparator();

			// 7 - Process grants file
			if (!$this->_processGrantsFile($directory)) break;

			$this->_printFileSeparator();

			// 7 - Process extra file
			if (!$this->_processExtraFile($directory)) break;

			$processDirectories = true; // If all the steps ends successfully

			$this->_printSchemaSeparator();
		}

		return $processDirectories;
	}

	/**
	 * Checks file names are compliant with the file naming convention
	 */
	private function _checkFilenaming($directory)
	{
		$files = array_filter(glob($directory.'/*'), 'is_file');

		// For each file
		foreach ($files as $file)
		{
			$fileName = basename($file); // File name
			// If the file name is NOT compliant with the file naming convention
			if (!$this->_isFilenameValid($fileName))
			{
				$this->_printInfo('Not valid file name, it is going to be ignored: '.$fileName);
			}
		}
	}

	/**
	 * Checks if the file name is compliant with the file naming convention
	 */
	private function _isFilenameValid($fileName)
	{
		return $fileName == self::SCHEMA_FILENAME // Schema file
			|| $fileName == self::SEQUENCES_FILENAME // Sequences file
			|| (substr($fileName, 0, strlen(self::TABLE_PREFIX)) == self::TABLE_PREFIX
				&& substr($fileName, -5, strlen(self::JSON_EXT)) == self::JSON_EXT) // Table files
			|| $fileName == self::VIEWS_FILENAME // Views file
			|| $fileName == self::FUNCTIONS_FILENAME // Function file
			|| $fileName == self::GRANTS_FILENAME // Grants file
			|| $fileName == self::EXTRA_FILENAME; // Extra file
	}

	/**
	 * Process the schema file
	 */
	private function _processSchemaFile($directory)
	{
		// Looks for a schema file
		$files = array_filter(glob($directory.'/'.self::SCHEMA_FILENAME), 'is_file');

		// If a schema file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found schema file: '.$files[0]);

			//...process it!
			if ($this->_isDryrunMode()) // If dry run mode enabled
			{
				$this->_printInfo('Dry run >> would be executed in new and diff mode');
			}
			else // new or diff mode
			{
				return $this->_execSQLFile($files[0]);
			}
		}
		else
		{
			$this->_printMessage('No schema file found');
		}

		return true; // If no files are found then go forward -> is a success
	}

	/**
	 * Process sequences file
	 * - Looks for sequences present in current schema, then sequences that are not present in php file are dropped
	 * - Looks for sequences present in php files, then sequences that are not present in database are installed
	 */
	private function _processSequencesFile($directory)
	{
		// Looks for a sequences file
		$files = array_filter(glob($directory.'/'.self::SEQUENCES_FILENAME), 'is_file');

		// If a sequences file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found sequences file: '.$files[0]);

			//...process it!
			require_once($files[0]); // Read sequences file
			$schema = basename($directory); // retrieves schema name from directory path
			$dbSequencesArray = $this->_listSequencesBySchema($schema); // get list of sequences currently present in DB

			// Loops through list of sequences currently present in database
			foreach ($dbSequencesArray as $dbSequence)
			{
				// If NOT in new mode and if the sequence present in database is NOT present in the list of sequences from php file
				if (!$this->_isNewMode() && !array_key_exists($dbSequence, $sequencesArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> sequence '.$dbSequence.' not found in sequences file >> would be removed in diff mode');
					}
					elseif ($this->_isDiffMode()) // only if in diff mode
					{
						// Then drop it and objects that depends on it from database! If it fails then ends execution
						if (!$this->_execQuery(sprintf('DROP SEQUENCE %s.%s CASCADE', $schema, $dbSequence)))
						{
							$this->_printError('Error occurred while dropping sequence: '.$dbSequence);
							return false;
						}
						else
						{
							$this->_printMessage('Sequence dropped successfully: '.$dbSequence);
						}
					}
				}
			}

			// Loops through list of sequences from php file
			foreach ($sequencesArray as $sequenceName => $sequenceSQL)
			{
				// If the sequence from php file is NOT present in database
				if (!in_array($sequenceName, $dbSequencesArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> sequence '.$sequenceName.' not found database >> would be added in new and diff mode');
					}
					else
					{
						// Then install it! If it fails then ends execution
						if (!$this->_execQuery($sequenceSQL))
						{
							$this->_printError('Error occurred while adding sequence: '.$sequenceName);
							return false;
						}
						else
						{
							$this->_printMessage('Sequence added successfully: '.$sequenceName);
						}
					}
				}
			}
		}
		else
		{
			$this->_printMessage('No sequences file found');
		}

		return true; // If ends of procedure with no failures or no files are found then is a success
	}

	/**
	 * Process table files
	 */
	private function _processTableFiles($directory)
	{
		// Looks for table files
		$files = array_filter(glob($directory.'/'.self::TABLE_PREFIX.'*'.self::JSON_EXT), 'is_file');

		// If table files are found...
		if (count($files) > 0)
		{
			//...process them!
		}
		else
		{
			$this->_printMessage('No table files found');
		}

		return true; // If no files are found then go forward -> is a success
	}

	/**
	 * Process views file
	 * - Looks for views present in current schema, then views that are not present in php file are dropped
	 * - Looks for views present in php files and install them all
	 */
	private function _processViewsFile($directory)
	{
		// Looks for a views file
		$files = array_filter(glob($directory.'/'.self::VIEWS_FILENAME), 'is_file');

		// If a views file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found views file: '.$files[0]);

			//...process it!
			require_once($files[0]); // Read views file
			$schema = basename($directory); // retrieves schema name from directory path
			$dbViewsArray = $this->_listViewsBySchema($schema); // get list of views currently present in DB

			// Loops through list of views currently present in database
			foreach ($dbViewsArray as $dbView)
			{
				// If NOT in new mode and if the view present in database is NOT present in the list of views from php file
				if (!$this->_isNewMode() && !array_key_exists($dbView, $viewsArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> view '.$dbView.' not found in views file >> would be removed in diff mode');
					}
					elseif ($this->_isDiffMode()) // only if in diff mode
					{
						// Then drop it and objects that depends on it from database! If it fails then ends execution
						if (!$this->_execQuery(sprintf('DROP VIEW %s.%s CASCADE', $schema, $dbView)))
						{
							$this->_printError('Error occurred while dropping view: '.$dbView);
							return false;
						}
						else
						{
							$this->_printMessage('View dropped successfully: '.$dbView);
						}
					}
				}
			}

			// Loops through list of views from php file
			foreach ($viewsArray as $viewName => $viewSQL)
			{
				if ($this->_isDryrunMode()) // If dry run mode enabled
				{
					$this->_printInfo('Dry run >> view '.$viewName.' would be added in new and diff mode');
				}
				else
				{
					// Then install it! If it fails then ends execution
					if (!$this->_execQuery($viewSQL))
					{
						$this->_printError('Error occurred while adding view: '.$viewName);
						return false;
					}
					else
					{
						$this->_printMessage('View added successfully: '.$viewName);
					}
				}
			}
		}
		else
		{
			$this->_printMessage('No views file found');
		}

		return true; // If ends of procedure with no failures or no files are found then is a success
	}

	/**
	 * Process functions file
	 */
	private function _processFunctionsFile($directory)
	{
		// Looks for a functions file
		$files = array_filter(glob($directory.'/'.self::FUNCTIONS_FILENAME), 'is_file');

		// If a functions file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found functions file: '.$files[0]);

			//...process it!
			require_once($files[0]); // Read functions file
			$schema = basename($directory); // retrieves schema name from directory path
			$dbFunctionsArray = $this->_listFunctionsBySchema($schema); // get list of functions currently present in DB

			// Loops through list of functions currently present in database
			foreach ($dbFunctionsArray as $dbFunction)
			{
				// If NOT in new mode and if the function present in database is NOT present in the list of functions from php file
				if (!$this->_isNewMode() && !array_key_exists($dbFunction, $functionsArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> function '.$dbFunction.' not found in fucntions file >> would be removed in diff mode');
					}
					elseif ($this->_isDiffMode()) // only if in diff mode
					{
						// Then drop it and objects that depends on it from database! If it fails then ends execution
						if (!$this->_execQuery(sprintf('DROP FUNCTION %s.%s CASCADE', $schema, $dbFunction)))
						{
							$this->_printError('Error occurred while dropping function: '.$dbFunction);
							return false;
						}
						else
						{
							$this->_printMessage('Function dropped successfully: '.$dbFunction);
						}
					}
				}
			}

			// Loops through list of functions from php file
			foreach ($functionsArray as $functionName => $functionSQL)
			{
				if ($this->_isDryrunMode()) // If dry run mode enabled
				{
					$this->_printInfo('Dry run >> view '.$functionName.' would be added in new and diff mode');
				}
				else
				{
					// Then install it! If it fails then ends execution
					if (!$this->_execQuery($functionSQL))
					{
						$this->_printError('Error occurred while adding function: '.$functionName);
						return false;
					}
					else
					{
						$this->_printMessage('Function added successfully: '.$functionName);
					}
				}
			}
		}
		else
		{
			$this->_printMessage('No functions file found');
		}

		return true; // If ends of procedure with no failures or no files are found then is a success
	}

	/**
	 * Process grants file
	 */
	private function _processGrantsFile($directory)
	{
		// Looks for a grants file
		$files = array_filter(glob($directory.'/'.self::GRANTS_FILENAME), 'is_file');

		// If a grants file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found grants file: '.$files[0]);

			//...process it!
			if ($this->_isDryrunMode()) // If dry run mode enabled
			{
				$this->_printInfo('Dry run >> would be executed in new and diff mode');
			}
			else // new or diff mode
			{
				return $this->_execSQLFile($files[0]);
			}
		}
		else
		{
			$this->_printMessage('No grants file found');
		}

		return true; // If no files are found then go forward -> is a success
	}

	/**
	 * Process extra file
	 */
	private function _processExtraFile($directory)
	{
		// Looks for an extra file
		$files = array_filter(glob($directory.'/'.self::EXTRA_FILENAME), 'is_file');

		// If an extra file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found extra file: '.$files[0]);

			//...process it!
			if ($this->_isDryrunMode()) // If dry run mode enabled
			{
				$this->_printInfo('Dry run >> would be executed in new and diff mode');
			}
			else // new or diff mode
			{
				return $this->_execSQLFile($files[0]);
			}
		}
		else
		{
			$this->_printMessage('No extra file found');
		}

		return true; // If no files are found then go forward -> is a success
	}

	/**
	 * Load SQL from a file and then execute such SQL
	 */
	private function _execSQLFile($file)
	{
		$sql = file_get_contents($file); // Read the file content
		if ($sql === false) // If failed
		{
			$this->_printError('Error occurred while reading file: '.$file);
		}
		else // otherwise
		{
			// Exec query
			if ($this->_execQuery($sql) == false) // if failed
			{
				$this->_printError('Error occurred while executing SQL from file: '.$file);
			}
			else // otherwise
			{
				$this->_printMessage('Successfully executed SQL from file: '.$file);
				return true;
			}
		}

		return false;
	}

	/**
	 *
	 */
	private function _isDryrunMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_DRYRUN;
	}

	/**
	 *
	 */
	private function _isDiffMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_DIFF;
	}

	/**
	 *
	 */
	private function _isNewMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_NEW;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private database methods

	/**
	 * Executes the given query
	 */
	private function _execQuery($query)
	{
		if (!@$this->_ci->db->simple_query($query))
		{
			$error = $this->_ci->db->error();
			if (is_array($error) && isset($error['message']))
			{
				$this->_printError($error['message']);
			}

			return false;
		}

		return true;
	}

	/**
	 *
	 */
	private function _listSequencesBySchema($schema)
	{
		$sequencesArray = array();
		$query = sprintf('SELECT sequence_name FROM information_schema.sequences WHERE sequence_schema = \'%s\'', $schema);

		if ($sequences = @$this->_ci->db->query($query))
		{
			foreach ($sequences->result() as $sequence)
			{
				$sequencesArray[] = $sequence->sequence_name;
			}
		}

		return $sequencesArray;
	}

	/**
	 *
	 */
	private function _listViewsBySchema($schema)
	{
		$viewsArray = array();
		$query = sprintf('SELECT table_name FROM information_schema.views WHERE table_schema = \'%s\'', $schema);

		if ($views = @$this->_ci->db->query($query))
		{
			foreach ($views->result() as $view)
			{
				$viewsArray[] = $view->table_name;
			}
		}

		return $viewsArray;
	}

	/**
	 *
	 */
	private function _listFunctionsBySchema($schema)
	{
		$functionsArray = array();
		$query = sprintf('SELECT p.proname as "view_name"
							FROM pg_catalog.pg_proc p
     				   LEFT JOIN pg_catalog.pg_namespace n ON n.oid = p.pronamespace
					   	   WHERE n.nspname <> \'pg_catalog\'
      						 AND n.nspname <> \'information_schema\'
							 AND n.nspname = \'%s\'', $schema);

		if ($functions = @$this->_ci->db->query($query))
		{
			foreach ($functions->result() as $function)
			{
				$functionsArray[] = $function->view_name;
			}
		}

		return $functionsArray;
	}



















	/**
	 * Check if a column exists in a table and schema
	 */
	private function _columnExists($name, $schema, $table)
	{
		$query = sprintf('SELECT %s FROM %s.%s LIMIT 1', $name, $schema, $table);

		if (@$this->_ci->db->simple_query($query))
		{
			return true;
		}

		return false;
	}

	/**
	 * Print an info about the starting of method up
	 */
	private function _startUP()
	{
		$this->eprintflib->printMessage(
			sprintf('%s Start method up of class %s %s', EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the ending of method up
	 */
	private function _endUP()
	{
		$this->eprintflib->printMessage(
			sprintf('%s End method up of class %s %s', EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the starting of method down
	 */
	private function _startDown()
	{
		$this->eprintflib->printMessage(
			sprintf('%s Start method down of class %s %s', EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Print an info about the ending of method down
	 */
	private function _endDown()
	{
		$this->eprintflib->printMessage(
			sprintf('%s End method down of class %s %s', EPrintfLib::SEPARATOR, get_called_class(), EPrintfLib::SEPARATOR)
		);
	}

	/**
	 * Adds a column, with attributes, to a table and schema
	 */
    private function _addColumn($schema, $table, $fields)
	{
		foreach ($fields as $name => $definition)
		{
			if (!$this->columnExists($name, $schema, $table))
			{
				if ($this->_ci->dbforge->add_column($schema.'.'.$table, array($name => $definition)))
				{
					$this->eprintflib->printMessage(sprintf('Column %s.%s.%s of type %s added', $schema, $table, $name, $definition['type']));
				}
				else
				{
					$this->eprintflib->printError(sprintf('Error while adding column %s.%s.%s of type %s', $schema, $table, $name, $definition['type']));
				}
			}
			else
			{
				$this->eprintflib->printMessage(sprintf('Column %s.%s.%s already exists', $schema, $table, $name));
			}
		}
	}

	/**
	 * Modifies a column, and its attributes, of a table and schema
	 */
	private function _modifyColumn($schema, $table, $fields)
	{
		foreach ($fields as $name => $definition)
		{
			if ($this->columnExists($name, $schema, $table))
			{
				if ($this->_ci->dbforge->modify_column($schema.'.'.$table, array($name => $definition)))
				{
					$this->eprintflib->printMessage(sprintf('Column %s.%s.%s has been modified', $schema, $table, $name));
				}
				else
				{
					$this->eprintflib->printError(sprintf('Error while modifying column %s.%s.%s', $schema, $table, $name));
				}
			}
			else
			{
				$this->eprintflib->printMessage(sprintf('Column %s.%s.%s does NOTt exist', $schema, $table, $name));
			}
		}
	}

	/**
	 * Drops a column from a table and schema
	 */
	private function _dropColumn($schema, $table, $field)
	{
		if ($this->columnExists($field, $schema, $table))
		{
			if ($this->_ci->dbforge->drop_column($schema.'.'.$table, $field))
			{
				$this->eprintflib->printMessage(sprintf('Column %s.%s.%s has been dropped', $schema, $table, $field));
			}
			else
			{
				$this->eprintflib->printError(sprintf('Error while dropping column %s.%s.%s', $schema, $table, $field));
			}
		}
		else
		{
			$this->eprintflib->printMessage(sprintf('Column %s.%s.%s does NOT t exist', $schema, $table, $field));
		}
	}

	/**
	 * Sets a column as primary key of a table and schema
	 */
	private function _addPrimaryKey($schema, $table, $name, $fields)
	{
		$stringFields = null;

		if (is_array($fields))
		{
			if (count($fields) > 0)
			{
				$stringFields = '';
				for ($i = 0; $i < count($fields); $i++)
				{
					$stringFields .= $fields[$i];
					if ($i != count($fields) - 1)
					{
						$stringFields .= ', ';
					}
				}
				$query = sprintf('ALTER TABLE %s.%s ADD CONSTRAINT %s PRIMARY KEY (%s)', $schema, $table, $name, $stringFields);
			}
		}
		else
		{
			$query = sprintf('ALTER TABLE %s.%s ADD CONSTRAINT %s PRIMARY KEY (%s)', $schema, $table, $name, $fields);
		}

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf('Added primary key %s on table %s.%s', $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Adding primary key %s on table %s.%s', $name, $schema, $table));
		}
	}

	/**
	 * Sets a column as foreign key of a table and schema
	 */
	private function _addForeingKey($schema, $table, $name, $field, $schemaDest, $tableDest, $fieldDest, $attributes)
	{
		$query = sprintf(
			'ALTER TABLE %s.%s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s.%s (%s) %s',
			$schema,
			$table,
			$name,
			$field,
			$schemaDest,
			$tableDest,
			$fieldDest,
			$attributes
		);

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf('Added foreign key %s on table %s.%s', $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Adding foreign key %s on table %s.%s', $name, $schema, $table));
		}
	}

	/**
	 * Sets a column as unique key of a table and schema
	 */
	private function _addUniqueKey($schema, $table, $name, $fields)
	{
		$stringFields = null;

		if (is_array($fields))
		{
			if (count($fields) > 0)
			{
				$stringFields = '';
				for ($i = 0; $i < count($fields); $i++)
				{
					$stringFields .= $fields[$i];
					if ($i != count($fields) - 1)
					{
						$stringFields .= ', ';
					}
				}
				$query = sprintf('CREATE UNIQUE INDEX %s ON %s.%s (%s)', $name, $schema, $table, $stringFields);
			}
		}
		else
		{
			$query = sprintf('CREATE UNIQUE INDEX %s ON %s.%s (%s)', $name, $schema, $table, $fields);
		}

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf('Added unique key %s on table %s.%s', $name, $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Adding unique key %s on table %s.%s', $name, $schema, $table));
		}
	}

	/**
	 * Grants permissions to a user on a table and schema
	 */
	private function _grantTable($permissions, $schema, $table, $user)
	{
		$stringPermission = null;

		if (is_array($permissions))
		{
			if (count($permissions) > 0)
			{
				$stringPermission = '';
				for ($i = 0; $i < count($permissions); $i++)
				{
					$stringPermission .= $permissions[$i];
					if ($i != count($permissions) - 1)
					{
						$stringPermission .= ', ';
					}
				}
				$query = sprintf('GRANT %s ON TABLE %s.%s TO %s', $stringPermission, $schema, $table, $user);
			}
		}
		else
		{
			$query = sprintf('GRANT %s ON TABLE %s.%s TO %s', $permissions, $schema, $table, $user);
		}

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(
				sprintf(
					'Granted permissions %s on table %s.%s to user %s',
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$table,
					$user
				)
			);
		}
		else
		{
			$this->eprintflib->printError(
				sprintf(
					'Granting permissions %s on table %s.%s to user %s',
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$table,
					$user
				)
			);
		}
	}

	/**
	 * Creates a table in a schema with columns
	 */
	private function _createTable($schema, $table, $fields)
	{
		$this->_ci->dbforge->add_field($fields);

		if ($this->_ci->dbforge->create_table($schema.'.'.$table, true))
		{
			$this->eprintflib->printMessage(sprintf('Table %s.%s created or existing', $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Creating table %s.%s', $schema, $table));
		}
	}

	/**
	 * Drops a table from a schema
	 */
	private function _dropTable($schema, $table)
	{
		if ($this->_ci->dbforge->drop_table($schema.'.'.$table))
		{
			$this->eprintflib->printMessage(sprintf('Table %s.%s has been dropped', $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Dropping table %s.%s', $schema, $table));
		}
	}

	/**
	 * Initializes a sequence with the max value of a column
	 */
	private function _initializeSequence($schemaSrc, $sequence, $schemaDst, $table, $field)
	{
		$query = sprintf('SELECT SETVAL(\'%s.%s\', (SELECT MAX(%s) FROM %s.%s))', $schemaSrc, $sequence, $field, $schemaDst, $table);

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(sprintf('Sequence %s.%s has been initialized', $schemaSrc, $sequence));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Initializing sequence %s.%s', $schemaSrc, $sequence));
		}
	}

	/**
	 * Add comment to a column
	 */
	private function _commentOnColumn($schema, $table, $field, $comment)
	{
		$query = sprintf('COMMENT ON COLUMN %s.%s.%s IS ?', $schema, $table, $field);

		if (@$this->_ci->db->query($query, array($comment)))
		{
			$this->eprintflib->printMessage(sprintf('Comment added to %s.%s.%s', $schema, $table, $field));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Error while adding comment to %s.%s.%s', $schema, $table, $field));
		}
	}

	/**
	 * Add comment to a table
	 */
	private function _commentOnTable($schema, $table, $comment)
	{
		$query = sprintf('COMMENT ON TABLE %s.%s IS ?', $schema, $table, $field);

		if (@$this->_ci->db->query($query, array($comment)))
		{
			$this->eprintflib->printMessage(sprintf('Comment added to %s.%s', $schema, $table));
		}
		else
		{
			$this->eprintflib->printError(sprintf('Error while adding comment to %s.%s', $schema, $table));
		}
	}
	/**
	 * Grants permissions to a user on a sequence
	 */
	private function _grantSequence($permissions, $schema, $sequence, $user)
	{
		$stringPermission = null;

		if (is_array($permissions))
		{
			if (count($permissions) > 0)
			{
				$stringPermission = '';
				for ($i = 0; $i < count($permissions); $i++)
				{
					$stringPermission .= $permissions[$i];
					if ($i != count($permissions) - 1)
					{
						$stringPermission .= ', ';
					}
				}
				$query = sprintf('GRANT %s ON SEQUENCE %s.%s TO %s', $stringPermission, $schema, $sequence, $user);
			}
		}
		else
		{
			$query = sprintf('GRANT %s ON SEQUENCE %s.%s TO %s', $permissions, $schema, $sequence, $user);
		}

		if (@$this->_ci->db->simple_query($query))
		{
			$this->eprintflib->printMessage(
				sprintf(
					'Granted permissions %s on sequence %s.%s to user %s',
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$sequence,
					$user
				)
			);
		}
		else
		{
			$this->eprintflib->printError(
				sprintf(
					'Granting permissions %s on sequence %s.%s to user %s',
					is_null($stringPermission) ? $permissions : $stringPermission,
					$schema,
					$sequence,
					$user
				)
			);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private output methods

	/**
	 *
	 */
	private function _printInfo($string)
	{
		$this->_ci->eprintflib->printInfo($string);
	}

	/**
	 *
	 */
	private function _printError($string)
	{
		$this->_ci->eprintflib->printError($string);
	}

	/**
	 *
	 */
	private function _printMessage($string)
	{
		$this->_ci->eprintflib->printMessage($string);
	}

	/**
	 *
	 */
	private function _printSchemaSeparator()
	{
		$this->_printInfo('--------------------------------------------------------------------------------------------');
	}

	/**
	 *
	 */
	private function _printFileSeparator()
	{
		$this->_printMessage('--------------------------------------------------------------------------------------------');
	}
}
