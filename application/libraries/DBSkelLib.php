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

	const SEPARATOR = ':'; // Step parameter character separator

	// Run modes
	const RUN_MODE_DRYRUN = 'dryrun'; // run without changing the database, useful for testing
	const RUN_MODE_NEW = 'new'; // build a new database or if database is already present creates only new objects
	const RUN_MODE_DIFF = 'diff'; // like new, but it also remove object from database that are NOT present in configuration files

	const STEP_SCHEMA = 1;
	const STEP_SEQUENCES = 2;
	const STEP_TABLES = 3;
	const STEP_CONSTRAINTS = 4;
	const STEP_VIEWS = 5;
	const STEP_FUNCTIONS = 6;
	const STEP_GRANTS = 7;
	const STEP_EXTRA = 8;

	const MAX_STEPS = 8; // Maximum number of steps

	// Configuration file names
	const SCHEMA_FILENAME = 'schema.sql'; // File name that contains schema creation SQL and SQL to comment a schema
	const SEQUENCES_FILENAME = 'sequences.php'; // PHP file that contains all the sequences
	const TABLE_PREFIX = 'TBL_'; // Table file prefix
	const CONSTRAINTS_FILENAME = 'constraints.php'; // PHP file that contains all the constraints
	const VIEWS_FILENAME = 'views.php'; // PHP file that contains all the views
	const FUNCTIONS_FILENAME = 'functions.php'; // PHP file that contains all the functions
	const GRANTS_FILENAME = 'grants.sql'; // Grants SQL file name
	const EXTRA_FILENAME = 'extra.sql'; // Extra SQL file name

	// PHP file extension
	const PHP_EXT = '.php';

	// Directory that contains the database skel
	const DBSKEL_DIR = APPPATH.'dbskel/';

	// Table properties
	const T_COMMENT = 'comment';
	const T_TYPE = 'type';
	const T_NULL = 'null';
	const T_DEFAULT = 'default';

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
	 * Accept the step parameter that can be used to run only a wanted step
	 */
	public function start($steps, $selectedDirectories)
	{
		$start = false;

		// Checks if DBSkel is enabled
		if ($this->_ci->config->item(self::CONF_ENABLED) === true)
		{
			// Checks if the given steps parameter is fine
			if ($this->_checkParameterStep($steps))
			{
				$this->_printSchemaSeparator();

				$this->_printRunningMode();

				// By default perform all steps
				$stepsArray = range(1, self::MAX_STEPS);
				// If steps parameter is given then use it to select the steps to be performed
				if ($steps != null) $stepsArray = explode(self::SEPARATOR, $steps);

				// Gets all the directories in application/dbskel
				$directories = glob(self::DBSKEL_DIR.'*', GLOB_ONLYDIR);

				// Checks if the selectedDirectories parameter is fine
				if ($this->_checkParameterSelectedDirectories($selectedDirectories, $directories))
				{
					$start = $this->_processDirectories($directories, $stepsArray);

					$this->_printSchemaSeparator();
				}
			}
		}
		else
		{
			$this->_printInfo('DBSkel is NOT enabled');
		}

		return $start;
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Process every single directory present in dbskel directory
	 */
	private function _processDirectories($directories, $stepsArray)
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

			// 0 - Checks file naming convention in current directory
			// NOTE: no need to check a failure! NOT a blocking check!! Always performed!!!
			$this->_checkFilenaming($directory);

			$this->_printFileSeparator();

			// 1 - Process schema file
			if (in_array(self::STEP_SCHEMA, $stepsArray))
			{
				if (!$this->_processSchemaFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 2 - Process sequence file
			if (in_array(self::STEP_SEQUENCES, $stepsArray))
			{
				if (!$this->_processSequencesFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 3 - Process table files
			if (in_array(self::STEP_TABLES, $stepsArray))
			{
				if (!$this->_processTableFiles($directory)) break;

				$this->_printFileSeparator();
			}

			// 4 - Process constraints
			if (in_array(self::STEP_CONSTRAINTS, $stepsArray))
			{
				if (!$this->_processConstraintsFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 5 - Process views file
			if (in_array(self::STEP_VIEWS, $stepsArray))
			{
				if (!$this->_processViewsFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 6 - Process functions file
			if (in_array(self::STEP_FUNCTIONS, $stepsArray))
			{
				if (!$this->_processFunctionsFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 7 - Process grants file
			if (in_array(self::STEP_GRANTS, $stepsArray))
			{
				if (!$this->_processGrantsFile($directory)) break;

				$this->_printFileSeparator();
			}

			// 8 - Process extra file
			if (in_array(self::STEP_EXTRA, $stepsArray))
			{
				if (!$this->_processExtraFile($directory)) break;
			}

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
				&& substr($fileName, -4, strlen(self::PHP_EXT)) == self::PHP_EXT) // Table files
			|| $fileName == self::CONSTRAINTS_FILENAME // Constraints file
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
	 * - Looks for sequences present in current schema, then sequences that are NOT present in php file are dropped (diff mode only)
	 * - Looks for sequences present in php files, then sequences that are NOT present in database are installed
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
			$dbSequencesArray = $this->_listSequencesBySchema($schema); // get list of sequences currently present in DB schema

			// Loops through list of sequences currently present in database
			foreach ($dbSequencesArray as $dbSequence)
			{
				// If NOT in new mode and if the sequence present in database is NOT present in the list of sequences from php file
				if (!$this->_isNewMode() && !array_key_exists($dbSequence, $sequencesArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> sequence '.$dbSequence.' NOT found in sequences file >> would be removed in diff mode');
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
						$this->_printInfo('Dry run >> sequence '.$sequenceName.' NOT found database >> would be added in new and diff mode');
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
				else
				{
					$this->_printMessage('Sequence already present in database: '.$sequenceName);
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
		$files = array_filter(glob($directory.'/'.self::TABLE_PREFIX.'*'.self::PHP_EXT), 'is_file');

		// If table files are found...
		if (count($files) > 0)
		{
			//...process them!
			$schema = basename($directory); // retrieves schema name from directory path
			$dbTablesArray = $this->_listTablesBySchema($schema); // get list of tables currently present in DB schema

			// For each table file
			foreach ($files as $file)
			{
				$this->_printMessage('Found table file: '.$file);

				require_once($file); // Read table file

				// Loops through list of tables currently present in database
				foreach ($dbTablesArray as $dbTable)
				{
					// If NOT in new mode and if the table present in database is NOT present in the php table file
					if (!$this->_isNewMode() && !array_key_exists($dbTable, $tableArray))
					{
						if ($this->_isDryrunMode()) // If dry run mode enabled
						{
							$this->_printInfo('Dry run >> table '.$dbTable.' NOT found in table file >> would be removed in diff mode');
						}
						elseif ($this->_isDiffMode()) // only if in diff mode
						{
							// Then drop it! If it fails then ends execution
							if (!$this->_execQuery(sprintf('DROP TABLE %s.%s', $schema, $dbTable)))
							{
								$this->_printError('Error occurred while dropping table: '.$dbTable);
								return false;
							}
							else
							{
								$this->_printMessage('Table dropped successfully: '.$dbTable);
							}
						}
					}
				}

				// Retrieves all the elements from the $tableArray except the element 'comment'
				$tableElements = array_keys(array_diff_key($tableArray, array(self::T_COMMENT => null)));
				if (is_array($tableElements) && count($tableElements) == 1) // If there is only one element left...
				{
					$tableName = $tableElements[0]; // ...then it is the name of the table

					// If the table from php file is NOT present in database
					if (!in_array($tableName, $dbTablesArray))
					{
						if ($this->_isDryrunMode()) // If dry run mode enabled
						{
							$this->_printInfo('Dry run >> table '.$tableName.' would be created in new and diff mode');
						}
						else // new and diff mode
						{
							// Then create the new table! If it fails then ends execution
							if ($this->_createTable($schema, $tableArray))
							{
								$this->_printMessage('Table created successfully: '.$tableName);
							}
							else
							{
								$this->_printError('Error occurred while creating a new table: '.$tableName);
								return false;
							}
						}
					}
					else // if table is already present in database
					{
						$this->_printMessage('Table already present in database: '.$tableName);

						// Manage the differences between the table present in database and the one present in php file
						return $this->_manageTableColumns($schema, $tableArray);
					}
				}
				else // otherwise the array present in the php table file is not well formatted
				{
					$this->_printError('Table file with a bad format is going to be ignored: '.$file);
				}
			}
		}
		else
		{
			$this->_printMessage('No table files found');
		}

		return true; // If no files are found then go forward -> is a success
	}

	/**
	 * Process constraints file
	 * - Looks for constraints present in current schema, then constraints that are NOT present in php file are dropped (diff mode only)
	 * - Looks for constraints present in php files, then constraints that are NOT present in database are installed
	 */
	private function _processConstraintsFile($directory)
	{
		// Looks for a constraints file
		$files = array_filter(glob($directory.'/'.self::CONSTRAINTS_FILENAME), 'is_file');

		// If a constraints file is found...
		if (count($files) > 0)
		{
			$this->_printMessage('Found constraints file: '.$files[0]);

			//...process it!
			require_once($files[0]); // Read constraints file
			$schema = basename($directory); // retrieves schema name from directory path
			$dbConstraintsArray = $this->_listConstraintsBySchema($schema); // get list of constraints currently present in DB schema
			$dbConstraintsNamesArray = array(); // Contains only the names of the constraints

			// Loops through list of constraints currently present in database
			foreach ($dbConstraintsArray as $dbConstraint)
			{
				$dbConstraintsNamesArray[] = $dbConstraint['name']; // Copy only the name of the constraint

				// If NOT in new mode and if the constraint present in database is NOT present in the list of constraints from php file
				if (!$this->_isNewMode() && !array_key_exists($dbConstraint['name'], $constraintsArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> constraint '.$dbConstraint['name'].' NOT found in constraints file >> would be removed in diff mode');
					}
					elseif ($this->_isDiffMode()) // only if in diff mode
					{
						// Then drop it and objects that depends on it from database! If it fails then ends execution
						if (!$this->_execQuery(sprintf('ALTER TABLE %s.%s DROP CONSTRAINT %s', $schema, $dbConstraint['table'], $dbConstraint['name'])))
						{
							$this->_printError('Error occurred while dropping constraint: '.$dbConstraint['name']);
							return false;
						}
						else
						{
							$this->_printMessage('Constraint dropped successfully: '.$dbConstraint['name']);
						}
					}
				}
			}

			// Loops through list of constraints from php file
			foreach ($constraintsArray as $constraintName => $constraintSQL)
			{
				// If the constraint from php file is NOT present in database
				if (!in_array($constraintName, $dbConstraintsNamesArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> constraint '.$constraintName.' would be added in new and diff mode');
					}
					else
					{
						// Then install it! If it fails then ends execution
						if (!$this->_execQuery($constraintSQL))
						{
							$this->_printError('Error occurred while adding constraint: '.$constraintName);
							return false;
						}
						else
						{
							$this->_printMessage('Constraint added successfully: '.$constraintName);
						}
					}
				}
				else
				{
					$this->_printMessage('Constraint already present in database: '.$constraintName);
				}
			}
		}
		else
		{
			$this->_printMessage('No constraints file found');
		}

		return true; // If ends of procedure with no failures or no files are found then is a success
	}

	/**
	 * Process views file
	 * - Looks for views present in current schema, then views that are NOT present in php file are dropped (diff mode only)
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
			$dbViewsArray = $this->_listViewsBySchema($schema); // get list of views currently present in DB schema

			// Loops through list of views currently present in database
			foreach ($dbViewsArray as $dbView)
			{
				// If NOT in new mode and if the view present in database is NOT present in the list of views from php file
				if (!$this->_isNewMode() && !array_key_exists($dbView, $viewsArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> view '.$dbView.' NOT found in views file >> would be removed in diff mode');
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
	 * - Looks for functions present in current schema, then functions that are NOT present in php file are dropped (diff mode only)
	 * - Looks for functions present in php files and install them all
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
			$dbFunctionsArray = $this->_listFunctionsBySchema($schema); // get list of functions currently present in DB schema

			// Loops through list of functions currently present in database
			foreach ($dbFunctionsArray as $dbFunction)
			{
				// If NOT in new mode and if the function present in database is NOT present in the list of functions from php file
				if (!$this->_isNewMode() && !array_key_exists($dbFunction, $functionsArray))
				{
					if ($this->_isDryrunMode()) // If dry run mode enabled
					{
						$this->_printInfo('Dry run >> function '.$dbFunction.' NOT found in fucntions file >> would be removed in diff mode');
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
	 * Checks if the running mode is 'dryrun'
	 */
	private function _isDryrunMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_DRYRUN;
	}

	/**
	 * Checks if the running mode is 'diff'
	 */
	private function _isDiffMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_DIFF;
	}

	/**
	 * Checks if the running mode is 'new'
	 */
	private function _isNewMode()
	{
		return $this->_ci->config->item(self::CONF_MODE) == self::RUN_MODE_NEW;
	}

	/**
	 * Checks if the parameter step is correct
	 */
	private function _checkParameterStep($steps)
	{
		if ($steps != null) // if it was given
		{
			$stepsArray = explode(self::SEPARATOR, $steps); // split the string in an array
			foreach ($stepsArray as $step)
			{
				if (!is_numeric($step)) // if it is not a number
				{
					$this->_ci->eprintflib->printError('The given parameter must be a number or a string in the following format: 1:3:5');
					return false;
				}
				elseif ($step > self::MAX_STEPS) // if it is a number but > MAX_STEPS
				{
					$this->_ci->eprintflib->printError('The maximun value fot this parameter is: '.self::MAX_STEPS);
					return false;
				}
				elseif ($step < 1) // if it is a number but < 1
				{
					$this->_ci->eprintflib->printError('The minimum value fot this parameter is 1');
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks if the parameter selectedDirectories is correct and stores the result in $directories
	 */
	private function _checkParameterSelectedDirectories($selectedDirectories, &$directories)
	{
		if ($selectedDirectories != null)
		{
			$selectedDirectoriesArray = explode(self::SEPARATOR, $selectedDirectories);

			$found = true;

			foreach ($selectedDirectoriesArray as $key => $value)
			{
				$selectedDirectoriesArray[$key] = self::DBSKEL_DIR.$value;

				if (!in_array(self::DBSKEL_DIR.$value, $directories))
				{
					$found = false;
					break;
				}
			}

			if ($found)
			{
				$directories = $selectedDirectoriesArray;
			}
			else
			{
				$this->_printError('One or more of the given directories does NOT exist');
				return false;
			}
		}

		return true;
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
	 * Retrieves all the sequences present in the given database schema
	 */
	private function _listSequencesBySchema($schema)
	{
		$sequencesArray = array();
		$query = sprintf('SELECT sequence_name
							FROM information_schema.sequences
						   WHERE sequence_schema = \'%s\'', $schema);

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
	 * Retrieves all the tables present in the given database schema
	 */
	private function _listTablesBySchema($schema)
	{
		$tablesArray = array();
		$query = sprintf('SELECT table_name
							FROM information_schema.tables
						   WHERE table_type = \'BASE TABLE\'
						   	 AND table_schema = \'%s\'', $schema);

		if ($tables = @$this->_ci->db->query($query))
		{
			foreach ($tables->result() as $table)
			{
				$tablesArray[] = $table->table_name;
			}
		}

		return $tablesArray;
	}

	/**
	 * Retrieves all the constraints present in the given database schema
	 * Returns an array with all the constraints, each element of the array is an array with two elements:
	 * - name: the name of the constraint
	 * - table: the name of the table where the constraint is applied
	 * NOTE: does not retrieve NOT NULL constraints
	 */
	private function _listConstraintsBySchema($schema)
	{
		$constraintsArray = array();
		$query = sprintf('SELECT constraint_name,
								table_name
							FROM information_schema.table_constraints
						   WHERE table_schema = \'%s\'
						     AND constraint_name NOT LIKE \'%%_not_null\'', $schema); // avoid to retrieve NOT NULL constraints

		if ($constraints = @$this->_ci->db->query($query))
		{
			foreach ($constraints->result() as $constraint)
			{
				$constraintsArray[] = array('name' => $constraint->constraint_name, 'table' => $constraint->table_name);
			}
		}

		return $constraintsArray;
	}

	/**
	 * Retrieves all the views present in the given database schema
	 */
	private function _listViewsBySchema($schema)
	{
		$viewsArray = array();
		$query = sprintf('SELECT table_name
							FROM information_schema.views
						   WHERE table_schema = \'%s\'', $schema);

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
	 * Retrieves all the functions present in the given database schema
	 */
	private function _listFunctionsBySchema($schema)
	{
		$functionsArray = array();
		$query = sprintf('SELECT routine_name
							FROM information_schema.routines
						   WHERE specific_schema != \'pg_catalog\'
						     AND specific_schema != \'information_schema\'
							 AND routine_schema = \'%s\'', $schema);

		if ($functions = @$this->_ci->db->query($query))
		{
			foreach ($functions->result() as $function)
			{
				$functionsArray[] = $function->routine_name;
			}
		}

		return $functionsArray;
	}

	/**
	 * Retrieves all the columns from a database table
	 */
	private function _listColumns($schema, $table)
	{
		$columnsArray = array();
		$query = sprintf('SELECT column_name AS name,
								data_type AS type,
								column_default AS default,
								is_nullable AS nullable,
								character_maximum_length AS string_length,
								numeric_precision AS number_length
							FROM information_schema.columns
						   WHERE table_schema = \'%s\'
	  			   			 AND table_name = \'%s\'', $schema, $table);

		if ($columns = @$this->_ci->db->query($query))
		{
			foreach ($columns->result() as $column)
			{
				$columnsArray[] = $column->routine_name;
			}
		}

		return $columnsArray;
	}

	/**
	 * Creates a new table in database using the given schema and an array that defines the table structure
	 */
	private function _createTable($schema, $tableArray)
	{
		$tableName = '';
		$tableComment = '';
		$tableStructure = null;

		// For each element of the table array from the php file
		foreach ($tableArray as $key => $value)
		{
			if ($key == self::T_COMMENT) // If it is the comment element
			{
				$tableComment = $value;
			}
			else // otherwise is the table structure element
			{
				$tableName = $key;
				$tableStructure = $value;
			}
		}

		// Query to create a table
		$query = sprintf('CREATE TABLE %s.%s (', $schema, $tableName);
		// Query to comment the table and its columns
		$queryComment = sprintf('COMMENT ON TABLE %s.%s IS \'%s\';', $schema, $tableName, $tableComment);

		// For each element of the table structure
		foreach ($tableStructure as $colName => $colStructure)
		{
			$notNull = ''; // by default the column could be null
			if (isset($colStructure[self::T_NULL]) && $colStructure[self::T_NULL] === false)
			{
				$notNull = 'NOT NULL'; // set to NOT NULL
			}

			$default = ''; // by default there is no default for a column
			if (isset($colStructure[self::T_DEFAULT]))
			{
				$default = 'DEFAULT '.$colStructure[self::T_DEFAULT]; // set as given by the table structure
			}

			// Part of the query related to this column
			$query .= sprintf('%s %s %s %s,', $colName, $colStructure[self::T_TYPE], $notNull, $default);

			// If a comment is present for this column then the query is built
			if (isset($colStructure[self::T_COMMENT]))
			{
				$queryComment .= sprintf('COMMENT ON COLUMN %s.%s.%s IS \'%s\';', $schema, $tableName, $colName, $colStructure[self::T_COMMENT]);
			}
		}

		// Removes the last comma from the query
		$query = substr($query, 0, strlen($query) - 1);

		// Close the round bracket
		$query .= ');';

		return $this->_execQuery($query.$queryComment); // executes query and returns its result
	}

	/**
	 * TODO
	 * Changes the structure of a table using the given schema and an array that defines the table structure
	 */
	private function _manageTableColumns($schema, $tableArray)
	{
		$tableName = '';
		$tableComment = '';
		$tableStructure = null;

		// For each element of the table array from the php file
		foreach ($tableArray as $key => $value)
		{
			if ($key == self::T_COMMENT) // If it is the comment element
			{
				$tableComment = $value;
			}
			else // otherwise is the table structure element
			{
				$tableName = $key;
				$tableStructure = $value;
			}
		}

		// Comments the table
		$queryComment = sprintf('COMMENT ON TABLE %s.%s IS \'%s\';', $schema, $tableName, $tableComment);
		if ($this->_isDryrunMode())
		{
			$this->_printInfo('Dry run >> table '.$tableName.' would be commented with: '.$queryComment);
		}
		else // new and diff mode
		{
			if (!$this->_execQuery($queryComment))
			{
				$this->_printError('Error occurred while commenting table: '.$tableName);
				return false;
			}
			else
			{
				$this->_printMessage('Table successfully commented: '.$tableName);
			}
		}

		// Retrieves the list of columns and their attributes from database
		$dbTableColumns = $this->_listColumns($schema, $tableName);

		// For each element of the table structure
		foreach ($tableStructure as $colName => $colStructure)
		{
			$notNull = ''; // by default the column could be null
			if (isset($colStructure[self::T_NULL]) && $colStructure[self::T_NULL] === false)
			{
				$notNull = 'NOT NULL'; // set to NOT NULL
			}

			$default = ''; // by default there is no default for a column
			if (isset($colStructure[self::T_DEFAULT]))
			{
				$default = 'DEFAULT '.$colStructure[self::T_DEFAULT]; // set as given by the table structure
			}

			// Part of the query related to this column
			$query = sprintf('%s %s %s %s,', $colName, $colStructure[self::T_TYPE], $notNull, $default);

			// Comments a column
			if (isset($colStructure[self::T_COMMENT]))
			{
				if ($this->_isDryrunMode())
				{
					$this->_printInfo('Dry run >> column '.$tableName.'.'.$colName.' would be commented with: '.$colStructure[self::T_COMMENT]);
				}
				else // new and diff mode
				{
					$queryComment = sprintf('COMMENT ON COLUMN %s.%s.%s IS \'%s\';', $schema, $tableName, $colName, $colStructure[self::T_COMMENT]);
					if (!$this->_execQuery($queryComment))
					{
						$this->_printError('Error occurred while commenting column: '.$tableName.'.'.$colName);
						return false;
					}
					else
					{
						$this->_printMessage('Column successfully commented: '.$tableName.'.'.$colName);
					}
				}
			}
		}

		return true;
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

	/**
	 *
	 */
	private function _printRunningMode()
	{
		if ($this->_isDryrunMode()) $this->_printInfo('>> DBSkel is running in dry run mode <<');
		elseif ($this->_isNewMode()) $this->_printInfo('>> DBSkel is running in new mode <<');
		elseif ($this->_isDiffMode()) $this->_printInfo('>> DBSkel is running in diff mode <<');
	}
}
