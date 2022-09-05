<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class DB_Model extends CI_Model
{
	// Default schema used by the models
	const DEFAULT_SCHEMA = 'public';

	// Default model class name postfix
	const MODEL_POSTFIX = '_model';

	// Query used to get the list of columns from a table
	const QUERY_LIST_FIELDS = 'SELECT * FROM %s WHERE 0 = 1';

	// Constants used to convert postgresql arrays and booleans to the php equivalent
	const PGSQL_ARRAY_TYPE = '_';
	const PGSQL_BOOLEAN_TRUE = 't';
	const PGSQL_BOOLEAN_FALSE = 'f';
	const PGSQL_BOOLEAN_TYPE = 'bool';
	const PGSQL_BOOLEAN_ARRAY_TYPE = '_bool';
	const PGSQL_INT2_TYPE = 'int2';
	const PGSQL_INT4_TYPE = 'int4';
	const PGSQL_INT8_TYPE = 'int8';
	const PGSQL_FLOAT4_TYPE = 'float4';
	const PGSQL_FLOAT8_TYPE = 'float8';

	protected $dbTable;  	// Name of the DB-Table for CI-Insert, -Update, ...
	protected $pk;  	// Name of the PrimaryKey for DB-Update, Load, ...
	protected $hasSequence;	// False if this table has a composite primary key that is not using a sequence
				// True if this table has a primary key that uses a sequence

	private $executedQueryMetaData;
	private $executedQueryListFields;

	private $debugMode;

	/**
	 * Constructor
	 */
	public function __construct($dbtype = 'default')
	{
		// Call parent constructor
		parent::__construct();

		// Set properties
		$this->hasSequence = true;

		// Loads DB conns and confs
		$this->load->database($dbtype);

		$this->load->model('system/UDF_model', 'UDFModel');

		// Loads the UDF library
		$this->load->library('UDFLib');
		// Loads the logs library
		$this->load->library('LogLib');

		$this->debugMode = isset($this->db->db_debug) && $this->db->db_debug === true;
	}

	// ------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * This method provides a way to setup a database model without declaring one that extends this class
	 */
	public function setup($schema, $table, $primaryKey, $hasSequence = true)
	{
		//
		if (!isEmptyString($schema) && !isEmptyString($table) && !isEmptyString($primaryKey) && is_bool($hasSequence))
		{
			$this->dbTable = $schema.'.'.$table;
			$this->pk = $primaryKey;
			$this->hasSequence = $hasSequence;
		}
	}

	/**
	 * Insert Data into DB-Table
	 *
	 * @param   array $data  DataArray for Insert
	 * @return  array
	 */
	public function insert($data)
	{
		// Check class properties
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		// If this table has UDF and the validation of them is ok
		if (isError($validate = $this->_prepareUDFsWrite($data, $this->dbTable))) return $validate;

		// DB-INSERT
		$insert = $this->db->insert($this->dbTable, $data);

		$this->_logLastQuery();

		if ($insert)
		{
			// If the table has a primary key that uses a sequence
			if ($this->hasSequence === true)
			{
				return success($this->db->insert_id());
			}
			// Avoid to use method insert_id() from CI because it forces to have a sequence
			// and doesn't return the primary key when it's composed by more columns
			else
			{
				$primaryKeysArray = array();

				foreach ($this->pk as $key => $value)
				{
					if (isset($data[$value]))
					{
						$primaryKeysArray[$value] = $data[$value];
					}
				}

				return success($primaryKeysArray);
			}
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	/**
	 * Update Data in DB-Table
	 *
	 * @param   string $id  PK for DB-Table
	 * @param   array $data  DataArray for Insert
	 * @return  array
	 */
	public function update($id, $data)
	{
		// Check class properties
		if (is_null($this->pk)) return error('The given primary key is not valid', EXIT_MODEL);
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		// If this table has UDF and the validation of them is ok
		if (isError($validate = $this->_prepareUDFsWrite($data, $this->dbTable, $id))) return $validate;

		$tmpId = $id;

		// Check for composite Primary Key, prepare the where clause
		if (is_array($id))
		{
			if (isset($id[0]))
			{
				$tmpId = $this->_arrayCombine($this->pk, $id);
			}
		}
		else
		{
			$tmpId = array($this->pk => $id);
		}

		$this->db->where($tmpId);

		// DB-UPDATE
		$update = $this->db->update($this->dbTable, $data);

		$this->_logLastQuery();

		if ($update)
		{
			return success($id);
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	/**
	 * Delete data from DB-Table
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function delete($id)
	{
		// Check class properties
		if (is_null($this->pk)) return error('The given primary key is not valid', EXIT_MODEL);
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		$tmpId = $id;

		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
			{
				$tmpId = $this->_arrayCombine($this->pk, $id);
			}
		}
		else
		{
			$tmpId = array($this->pk => $id);
		}

		// DB-DELETE
		$delete = $this->db->delete($this->dbTable, $tmpId);

		$this->_logLastQuery();

		if ($delete)
		{
			return success($id);
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	/**
	 * Load single data from DB-Table
	 *
	 * @param   string $id  ID (Primary Key) for SELECT ... WHERE
	 * @return  array
	 */
	public function load($id = null)
	{
		// Check class properties
		if (is_null($this->pk)) return error('The given primary key is not valid', EXIT_MODEL);
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		$tmpId = $id;

		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
			{
				$tmpId = $this->_arrayCombine($this->pk, $id);
			}
		}
		elseif ($id != null)
		{
			$tmpId = array($this->pk => $id);
		}

		return $this->loadWhere($tmpId);
	}

	/**
	 * Load data from DB-Table with a where clause
	 *
	 * @return  array
	 */
	public function loadWhere($where = null)
	{
		// Check class properties
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		// Execute query
		$result = $this->db->get_where($this->dbTable, $where);

		$this->_logLastQuery();

		if ($result)
		{
			return success($this->_toPhp($result));
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	/**
	 * Load data and convert a record into a list of data from the main table,
	 * and linked to every element, the data from the side tables
	 * NOTE: sub queries are not supported in the from clause
	 *
	 * @return  array
	 */
	public function loadTree($mainTable, $sideTables, $where = null, $sideTablesAliases = null)
	{
		// Check class properties
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		// List of tables on which it will work
		$tables = array_merge(array($mainTable), $sideTables);
		// Array that will contain the number of columns of each table
		$tableColumnsCountArray = array();

		// Generates the select clause based on the columns of each table
		$select = '';
		for ($t = 0; $t < count($tables); $t++)
		{
			// Get the schema if it is specified
			$schemaAndTable = $this->getSchemaAndTable($tables[$t]);
			// Discard the schema, not needed in the next steps
			$tables[$t] = $schemaAndTable->table;

			// List of the columns of the current table
			// NOTE: $this->db->list_fields($tables[$t]) doesn't work if there are two tables with
			// the same name in two different schemas, use this workaround
			$fields = array();
			if (isSuccess($lstColumns = $this->_list_columns($schemaAndTable->schema, $schemaAndTable->table)))
			{
				$fields = $lstColumns->retval;
			}

			for ($f = 0; $f < count($fields); $f++)
			{
				// To avoid overwriting of the properties within the object returned by CI
				// will be given an alias to every column, that will be composed with the following schema
				// <table name>.<column name> AS <table_name>_<column name>
				$select .= $tables[$t].'.'.$fields[$f]->column_name.' AS '.$tables[$t].'_'.$fields[$f]->column_name;
				if ($f < count($fields) - 1) $select .= ', ';
			}

			if ($t < count($tables) - 1) $select .= ', ';

			$tableColumnsCountArray[$t] = count($fields);
		}

		// Adds the select clause
		$this->addSelect($select);

		// Execute the query
		$resultDB = $this->db->get_where($this->dbTable, $where);

		$this->_logLastQuery();

		// If everything went ok...
		if ($resultDB)
		{
			// Converts the object that contains data, from the returned CI's object to an array
			// with the postgresql array and boolean types converterd
			$resultArray = $this->_toPhp($resultDB);
			// Array that will contain all the mainTable records, and to each record the linked data
			// of a side table
			$returnArray = array();
			$returnArrayCounter = 0;	// Array counter

			// Iterates the array that contains data from DB
			for ($i = 0; $i < count($resultArray); $i++)
			{
				// Converts an object properties to an associative array
				$objectVars = get_object_vars($resultArray[$i]);
				// Temporary array that will contain a representation of every records returned from DB
				// every element is an associative array that contains all the data of each table
				$objTmpArray = array();
				$tableColumnsCountArrayOffset = 0; // Columns offset
				// Gets all the data of a single table from the returned record, and creates an object filled with these data
				for ($f = 0; $f < count($tableColumnsCountArray); $f++)
				{
					$objTmpArray[$f] = new stdClass(); // Object that will represent a data set of a table

					foreach (array_slice($objectVars, $tableColumnsCountArrayOffset, $tableColumnsCountArray[$f]) as $key => $value)
					{
						$objTmpArray[$f]->{str_replace($tables[$f].'_', '', $key)} = $value;
					}

					$tableColumnsCountArrayOffset += $tableColumnsCountArray[$f]; // Increasing the offset
				}

				// Object that represents data of the main table
				$mainTableObj = $objTmpArray[0];
				// Fill $returnArray with all data from mainTable, and for each element will link the data from the side tables
				for ($t = 1; $t < count($tables); $t++)
				{
					// Object that represents data of the side table
					$sideTableObj = $objTmpArray[$t];
					$sideTableProperty = $tables[$t];
					if (is_array($sideTablesAliases))
					{
						$sideTableProperty = $sideTablesAliases[$t - 1];
					}

					// If the side table has data. If it was used a left join all the properties could be null
					// NOTE: Keep this way to be compatible with a php version older than 5.5
					$tmpFilteredArray = array_filter(get_object_vars($sideTableObj));
					if (isset($tmpFilteredArray) && count($tmpFilteredArray) > 0)
					{
						if (($k = $this->_findMainTable($mainTableObj, $returnArray)) === false)
						{
							$mainTableObj->{$sideTableProperty} = array($sideTableObj);
							$returnArray[$returnArrayCounter++] = $mainTableObj;
						}
						else
						{
							if (!isset($returnArray[$k]->{$sideTableProperty}))
							{
								$returnArray[$k]->{$sideTableProperty} = array($sideTableObj);
							}
							elseif (array_search($sideTableObj, $returnArray[$k]->{$sideTableProperty}) === false)
							{
								array_push($returnArray[$k]->{$sideTableProperty}, $sideTableObj);
							}
						}
					}
				}
			}

			// Sets result with the standard success object that contains all the studiengang
			$result = success($returnArray);
		}
		else
		{
			$result = error($resultDB);
		}

		return $result;
	}

	/**
	 * Add a table to join with
	 *
	 * @return  void
	 */
	public function addJoin($joinTable = null, $cond = null, $type = '')
	{
		// Check parameters
		if (is_null($joinTable)
			|| is_null($cond)
			|| !in_array($type, array('', 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER')))
		{
			return error('The joining operation type is not valid', EXIT_MODEL);
		}

		$this->db->join($joinTable, $cond, $type);

		return success();
	}

	/**
	 * Add order clause
	 *
	 * @return  void
	 */
	public function addOrder($field = null, $type = 'ASC')
	{
		// Check parameters
		if (is_null($field)) return error('The field parameter is not valid', EXIT_MODEL);
		if (!in_array($type, array('ASC', 'DESC'))) return error('The order type is not valid', EXIT_MODEL);

		$this->db->order_by($field, $type);

		return success();
	}

	/**
	 * Add select clause
	 *
	 * @return  void
	 */
	public function addSelect($select, $escape = true)
	{
		// Check parameters
		if (is_null($select) || $select == '') return error('The select parameter is not valid', EXIT_MODEL);

		$this->db->select($select, $escape);

		return success();
	}

	/**
	 * Add distinct clause
	 *
	 * @return  void
	 */
	public function addDistinct()
	{
		$this->db->distinct();
	}

	/**
	 * Add limit clause
	 *
	 * @return  void
	 */
	public function addLimit($start = null, $end = null)
	{
		// Check class properties and parameters
		if (!is_numeric($start) || (is_numeric($start) && $start <= 0))
			return error('The start parameter is not valid', EXIT_MODEL);

		if (is_numeric($end) && $end > $start)
		{
			$this->db->limit($start, $end);
		}
		else
		{
			$this->db->limit($start);
		}

		return success();
	}

	/**
	 * Add a table in the from clause
	 *
	 * @return  void
	 */
	public function addFrom($table, $alias = null)
	{
		$tmpTable = trim($table);

		// Check parameters
		if (isEmptyString($tmpTable)) return error('The table parameter is not valid', EXIT_MODEL);

		if (!isEmptyString($alias))
		{
			$tmpTable .= ' AS '.$alias;
		}

		$this->db->from($tmpTable);

		return success();
	}

	/**
	 * Add one or more fields in the group by clause
	 *
	 * @return  void
	 */
	public function addGroupBy($fields)
	{
		if (!isset($fields)
			|| (!is_array($fields) && !is_string($fields))
			|| (is_array($fields) && count($fields) == 0)
			|| (is_string($fields) && $fields == ''))
		{
			return error('The fields parameter is not valid', EXIT_MODEL);
		}

		$this->db->group_by($fields);

		return success();
	}

	/**
	 * Reset the query builder state
	 *
	 * @return  void
	 */
	public function resetQuery()
	{
		$this->db->reset_query();
	}

	/**
	 * This method call the method escape from class CI_DB_driver, therefore:
	 * this method determines the data type so that it can escape only string data.
	 * It also automatically adds single quotes around the data so you don’t have to
	 *
	 * @return  void
	 */
	public function escape($value)
	{
		return $this->db->escape($value);
	}

	/**
	 * This method call the method escape_like_str from class CI_DB_driver, therefore:
	 * this method should be used when strings are to be used in LIKE conditions so that LIKE wildcards (‘%’, ‘_’)
	 * in the string are also properly escaped.
	 * NOTE: The escape_like_str() method uses ‘!’ (exclamation mark) to escape special characters for LIKE conditions.
	 * 		Because this method escapes partial strings that you would wrap in quotes yourself, it cannot automatically
	 *		add the ESCAPE '!' condition for you, and so you’ll have to manually do that.
	 *
	 * @return  void
	 */
	public function escapeLike($value)
	{
		return $this->db->escape_like_str($value);
	}

	/**
	 * Convert PG-Boolean to PHP-Boolean
	 *
	 * @param   char	$b	PG-Char to convert
	 * @return  bool
	 */
	public function pgBoolPhp($val)
	{
		// If true
		if ($val == DB_Model::PGSQL_BOOLEAN_TRUE)
		{
			return true;
		}
		// If false
		elseif ($val == DB_Model::PGSQL_BOOLEAN_FALSE)
		{
			return false;
		}

		// If it is null, let it be null
		return $val;
	}

	/**
	 * Convert PG-Int* to PHP-Integer
	 */
	public function pgIntPhp($val)
	{
		// If it is null, let it be null
		if ($val == null) return $val;

		return intval($val);
	}

	/**
	 * Convert PG-Float* to PHP-Float
	 */
	public function pgFloatPhp($val)
	{
		// If it is null, let it be null
		if ($val == null) return $val;

		return floatval($val);
	}

	/**
	 * Converts from PostgreSQL array to php array
	 * It also takes care about array of booleans
	 */
	public function pgsqlArrayToPhpArray($string, $booleans = false)
	{
		// At least returns an empty array
		$result = array();

		// String that represents the pgsql array, better if not empty
		if (!isEmptyString($string))
		{
			// Magic convertion
			preg_match_all(
				'/(?<=^\{|,)(([^,"{]*)|\s*"((?:[^"\\\\]|\\\\(?:.|[0-9]+|x[0-9a-f]+))*)"\s*)(,|(?<!^\{)(?=\}$))/i',
				$string,
				$matches,
				PREG_SET_ORDER
			);
			foreach ($matches as $match)
			{
				// Single element of the array
				$tmp = $match[3] != '' ? stripcslashes($match[3]) : (strtolower($match[2]) == 'null' ? null : $match[2]);
				// If it is an array of booleans, then converts the single element
				if ($booleans === true)
				{
					$tmp = $this->pgBoolPhp($tmp);
				}
				// Adds it to the result array
				$result[] = $tmp;
			}
		}

		return $result;
	}

	/**
	 * Returns an array that contains a list of columns names of this table
	 */
	public function listFields()
	{
		$listFields = array();

		// Workaround to get metadata from this table
		$result = $this->db->query(sprintf(DB_Model::QUERY_LIST_FIELDS, $this->dbTable));
		if (is_object($result))
		{
			$listFields = $result->list_fields();
		}

		return $listFields;
	}

	/**
	 * Checks if this table has a field == $field
	 */
	public function fieldExists($field)
	{
		$exists = true;

		// If $field is not found in the list of fields of this table
		if (array_search($field, $this->listFields()) === false)
		{
			$exists = false;
		}

		return $exists;
	}

	/**
	 * Returns all the UDF contained in this table ($dbTable)
	 * If no UDF are present, an empty array will be returned
	 * NOTE: only the UDFs that the logged user is allowed to read are loaded by this method
	 */
	public function getUDFs($id, $udfName = null)
	{
		$udfs = array();

		$this->addSelect(UDFLib::COLUMN_NAME); // select only the column with UDF

		$result = $this->load($id);
		if (hasData($result))
		{
			$jsonValues = (array)$result->retval[0];
			// For every UDF
			foreach ($jsonValues as $key => $value)
			{
				if ($udfName != null && $udfName == $key)
				{
					$udfs[$key] = $value;
					break;
				}
				else
				{
					$udfs[$key] = $value;
				}
			}
		}

		return $udfs;
	}

	/**
	 * Checks if this table has the field udf_values and if there is a UDF definition for this table
	 */
	public function udfsExistAndDefined()
	{
		if ($this->fieldExists(UDFLib::COLUMN_NAME))
		{
			$resultUDFsDefinitions = $this->UDFModel->getUDFsDefinitions($this->dbTable);
			if (hasData($resultUDFsDefinitions))
				return true;
			else
				return false;
		}
		else
			return false;
	}

	/**
	 * Get the list of the fields after having executed a query
	 */
	public function getExecutedQueryListFields()
	{
		return $this->executedQueryListFields;
	}

	/**
	 * Get meda data info about the retrieved fields after having executed a query
	 */
	public function getExecutedQueryMetaData()
	{
		return $this->executedQueryMetaData;
	}

	/**
	 * Like execQuery, but it allows only to perform queries to read data
	 */
	public function execReadOnlyQuery($query, $parametersArray = null)
	{
		$result = error('You are allowed to run only query for reading data'); //
		$cleanedQuery = trim(preg_replace('/\t|\n|\r|;/', '', $query)); //

		//
		if (
			(stripos($cleanedQuery, 'INSERT') > 0 || stripos($cleanedQuery, 'INSERT') == false)
			&& (stripos($cleanedQuery, 'UPDATE') > 0 || stripos($cleanedQuery, 'UPDATE') == false)
			&& (stripos($cleanedQuery, 'CREATE') > 0 || stripos($cleanedQuery, 'CREATE') == false)
			&& (stripos($cleanedQuery, 'DELETE') > 0 || stripos($cleanedQuery, 'DELETE') == false)
			&& (stripos($cleanedQuery, 'ALTER') > 0 || stripos($cleanedQuery, 'ALTER') == false)
			&& (stripos($cleanedQuery, 'GRANT') > 0 || stripos($cleanedQuery, 'GRANT') == false)
			&& (stripos($cleanedQuery, 'DROP') > 0 || stripos($cleanedQuery, 'DROP') == false))
		{
			$queryToExec = str_replace(';', '', $query); //

			$result = $this->execQuery($queryToExec, $parametersArray);
		}

		return $result;
	}

	// ------------------------------------------------------------------------------------------
	// Protected methods

	/**
	 * Executes a query and converts array and boolean data types from PgSql to php
	 * @return: boolean false on failure
	 *			boolean if the query is of the write type (INSERT, UPDATE, DELETE...)
	 *			array that represents DB data
	 */
	protected function execQuery($query, $parametersArray = null)
	{
		$result = null;

		// If the query is empty don't lose time
		if (!isEmptyString($query))
		{
			// If there are parameters to bind to the query
			if (is_array($parametersArray) && count($parametersArray) > 0)
			{
				$resultDB = $this->db->query($query, $parametersArray);
			}
			else
			{
				$resultDB = $this->db->query($query);
			}

			$this->_logLastQuery();

			// If no errors occurred
			if ($resultDB)
			{
				$result = success($this->_toPhp($resultDB));
			}
			else
			{
				$result = error($this->db->error(), EXIT_DATABASE);
			}
		}

		return $result;
	}

	/**
	 * Get schema and table name from the parameter
	 * If no schema are specified it will returns the parameter as table name,
	 * and the default schema as schema
	 * Ex:
	 * If the parameters is 'lehre.tbl_studienplan' it will returns the following object:
	 * obj
	 *  |--->schema: lehre
	 *  |--->table: tbl_studienplan
	 */
	protected function getSchemaAndTable($schemaAndTable)
	{
		$result = new stdClass();
		$result->table = $schemaAndTable;
		$result->schema = DB_Model::DEFAULT_SCHEMA;

		// If a schema is specified
		if (($pos = strpos($schemaAndTable, '.')) !==  false)
		{
			$result->schema = substr($schemaAndTable, 0, $pos);
			$result->table = substr($schemaAndTable, $pos + 1);
		}

		return $result;
	}

	// ------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Invalid ID
	 *
	 * @param   array $i	Array with indexes.
	 * @param   array $v	Array with values.
	 * @return  array
	 */
	private function _arrayCombine($idexes, $values)
	{
		if (count($idexes) != count($values)) return null;

		return array_combine($idexes, $values);
	}

	/**
	 * Wrapper method for UDFLib->prepareUDFsWrite
	 */
	private function _prepareUDFsWrite(&$data, $schemaAndTable, $id = null)
	{
		$prepareUDFsWrite = success();

		if ($this->udfsExistAndDefined())
		{
			if ($id != null)
			{
				$prepareUDFsWrite = $this->udflib->prepareUDFsWrite($data, $this->dbTable, $this->_getUDFsNoPerms($id));
			}
			else
			{
				$prepareUDFsWrite = $this->udflib->prepareUDFsWrite($data, $this->dbTable);
			}
		}

		return $prepareUDFsWrite;
	}

	/**
	 * Converts array and boolean data types from PgSql to php
	 * NOTE: PostgreSQL php drivers returns:
	 * - A boolean value if the query is of the write type (INSERT, UPDATE, DELETE...)
	 * - A FALSE value on failure
	 * - Otherwise an object filled with data on success
	 */
	private function _toPhp($result)
	{
		$udfs = false; // if UDFs are inside the given result set
		$toPhp = $result; // if there is nothing to convert then return the result from DB

		// If it's an object its fields will be parsed to find booleans, arrays and UDFs types
		if (is_object($result))
		{
			$toBeConverterdArray = array(); // Fields to be converted

			$this->executedQueryMetaData = $result->field_data(); // Fields information
			$this->executedQueryListFields = $result->list_fields(); // List of the retrieved fields

			// Looking for booleans, arrays and UDFs
			foreach ($this->executedQueryMetaData as $eqmd)
			{
				// If array type, boolean type OR a UDF
				if (strpos($eqmd->type, DB_Model::PGSQL_ARRAY_TYPE) !== false
					|| $eqmd->type == DB_Model::PGSQL_BOOLEAN_TYPE
					|| $eqmd->type == DB_Model::PGSQL_INT2_TYPE
					|| $eqmd->type == DB_Model::PGSQL_INT4_TYPE
					|| $eqmd->type == DB_Model::PGSQL_INT8_TYPE
					|| $eqmd->type == DB_Model::PGSQL_FLOAT4_TYPE
					|| $eqmd->type == DB_Model::PGSQL_FLOAT8_TYPE
					|| $this->udflib->isUDFColumn($eqmd->name, $eqmd->type))
				{
					// If UDFs are inside this result set
					if ($this->udflib->isUDFColumn($eqmd->name, $eqmd->type))
					{
						$udfs = true;
					}
					else // all the other cases
					{
						// Name and type of the field to be converted
						$toBeConverted = new stdClass();
						// Set the type of the field to be converted
						$toBeConverted->type = $eqmd->type;
						// Set the name of the field to be converted
						$toBeConverted->name = $eqmd->name;
						// Add the field to be converted to $toBeConverterdArray
						array_push($toBeConverterdArray, $toBeConverted);
					}
				}
			}

			// Returns the array of objects, each of them represents a DB record
			$resultsArray = $result->result();

			// If in this result set there are UDFs then prepare them
			if ($udfs) $this->udflib->prepareUDFsRead($resultsArray, $this->dbTable);

			// If there is something to convert, otherwhise don't waste time
			if (!isEmptyArray($toBeConverterdArray))
			{
				// Looping on results
				foreach ($resultsArray as $resultElement)
				{
					// Looping on fields to be converted
					foreach ($toBeConverterdArray as $toBeConverted)
					{
						// Array type
						if (strpos($toBeConverted->type, DB_Model::PGSQL_ARRAY_TYPE) !== false)
						{
							$resultElement->{$toBeConverted->name} = $this->pgsqlArrayToPhpArray(
								$resultElement->{$toBeConverted->name},
								$toBeConverted->type == DB_Model::PGSQL_BOOLEAN_ARRAY_TYPE
							);
						}
						// Boolean type
						elseif ($toBeConverted->type == DB_Model::PGSQL_BOOLEAN_TYPE)
						{
							$resultElement->{$toBeConverted->name} = $this->pgBoolPhp($resultElement->{$toBeConverted->name});
						}
						// Integer type
						elseif ($toBeConverted->type == DB_Model::PGSQL_INT2_TYPE
							|| $toBeConverted->type == DB_Model::PGSQL_INT4_TYPE
							|| $toBeConverted->type == DB_Model::PGSQL_INT8_TYPE)
						{
							$resultElement->{$toBeConverted->name} = $this->pgIntPhp($resultElement->{$toBeConverted->name});
						}
						// Float type
						elseif ($toBeConverted->type == DB_Model::PGSQL_FLOAT4_TYPE
							|| $toBeConverted->type == DB_Model::PGSQL_FLOAT8_TYPE)
						{
							$resultElement->{$toBeConverted->name} = $this->pgFloatPhp($resultElement->{$toBeConverted->name});
						}
					}
				}
			}

			// Returns DB data as an array
			$toPhp = $resultsArray;
		}

		return $toPhp;
	}

	/**
	 * Used in loadTree to find the main tables
	 */
	private function _findMainTable($mainTableObj, $mainTableArray)
	{
		for ($i = 0; $i < count($mainTableArray); $i++)
		{
			if ($mainTableObj->{$this->pk} == $mainTableArray[$i]->{$this->pk})
			{
				return $i;
			}
		}

		return false;
	}

	/**
	 * Workaround of CI_DB_driver->_list_columns
	 * CI_DB_driver->list_fields($tableName), that calls CI_DB_postgre_driver->_list_columns,
	 * doesn't work if there are two tables with the same name in two different schemas
	 */
	private function _list_columns($schema, $table)
	{
		$query = 'SELECT column_name
					FROM information_schema.columns
				   WHERE LOWER(table_schema) = ?
					 AND LOWER(table_name) = ?';

		return $this->execQuery($query, array(strtolower($schema), strtolower($table)));
	}

	/**
	 *
	 */
	private function _logLastQuery()
	{
		if ($this->debugMode) $this->loglib->logDebug($this->db->last_query());
	}

	/**
	 * Returns all the UDF contained in this table ($dbTable)
	 * If no UDF are present, an empty array will be returned
	 * NOTE: it returns all the UDFs, does _not_ check the permissions
	 */
	private function _getUDFsNoPerms($id)
	{
		$udfs = array();

		$this->db->select(UDFLib::COLUMN_NAME, true); // get only the UDF column

		// Primary key management
		$tmpId = $id;

		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
			{
				$tmpId = $this->_arrayCombine($this->pk, $id);
			}
		}
		elseif ($id != null)
		{
			$tmpId = array($this->pk => $id);
		}

		// Read the record from the table
		$result = $this->db->get_where($this->dbTable, $tmpId);

		// If was a success and there are data
		if ($result && count($result->result()) == 1)
		{
			// Get the UDF column and decode it from JSON
			$jsonValues = json_decode($result->result()[0]->{UDFLib::COLUMN_NAME});

			// If the JSON convertion was fine convert the object to an array
			if ($jsonValues != null) $udfs = get_object_vars($jsonValues);
		}

		return $udfs;
	}
}

