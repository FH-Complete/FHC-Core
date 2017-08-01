<?php

class DB_Model extends FHC_Model
{
	// Default schema used by the models
	const DEFAULT_SCHEMA = 'public';
	
	// Default model class name postfix
	const MODEL_POSTFIX = '_model';
	
	// Query used to get the list of columns from a table
	const QUERY_LIST_FIELDS = 'SELECT * FROM %s WHERE 0 = 1';
	
	// Constants used to convert postgresql arrays and booleans to the php equivalent
	const PGSQL_ARRAY_TYPE = '_';
	const PGSQL_BOOLEAN_TYPE = 'bool';
	const PGSQL_BOOLEAN_ARRAY_TYPE = '_bool';
	const PGSQL_BOOLEAN_TRUE = 't';
	const PGSQL_BOOLEAN_FALSE = 'f';
	
	// UDF constants
	const UDF_FIELD_NAME = 'udf_values';
	const UDF_FIELD_TYPE = 'jsonb';
	const UDF_FIELD_PREFIX = 'udf_';
	const UDF_ATTRIBUTE_NAME = 'name';
	const UDF_TYPE_NAME = 'type';
	const UDF_CHKBOX_TYPE = 'checkbox';
	const UDF_DROPDOWN_TYPE = 'dropdown';
	const UDF_MULTIPLEDROPDOWN_TYPE = 'multipledropdown';
	const UDF_FIELD_JSON_DESCRIPTION = 'jsons';
	
	// UDF validation attributes
	const UDF_REGEX = 'regex';
	const UDF_REQUIRED = 'required';
	const UDF_MAX_VALUE = 'max-value';
	const UDF_MIN_VALUE = 'min-value';
	const UDF_REGEX_LANG = 'php';
	const UDF_MAX_LENGTH = 'max-length';
	const UDF_MIN_LENGTH = 'min-length';
	
	// String values of booleans
	const STRING_TRUE = 'true';
	const STRING_FALSE = 'false';
	const STRING_NULL = 'null';
	
	protected $dbTable;  	// Name of the DB-Table for CI-Insert, -Update, ...
	protected $pk;  		// Name of the PrimaryKey for DB-Update, Load, ...
	protected $hasSequence;	// False if this table has a composite primary key that is not using a sequence
							// True if this table has a primary key that uses a sequence
	
	protected $UDFs; // Contains the UDFs
	
	/**
	 * Constructor
	 */
	function __construct($dbTable = null, $pk = null, $hasSequence = true)
	{
		// Call parent constructor
		parent::__construct();
		
		// Set properties
		$this->pk = $pk;
		$this->UDFs = array();
		$this->dbTable = $dbTable;
		$this->hasSequence = $hasSequence;
		
		// Loads DB conns and confs
		$this->load->database();
	}
	
	/**
	 * Insert Data into DB-Table
	 *
	 * @param   array $data  DataArray for Insert
	 * @return  array
	 */
	public function insert($data)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::INSERT_RIGHT)) return $isEntitled;
		
		// UDFs
		if (isError($validate = $this->_manageUDFs($data))) return $validate;
		
		// DB-INSERT
		if ($this->db->insert($this->dbTable, $data))
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
			return error($this->db->error(), FHC_DB_ERROR);
	}

	/**
	 * Replace Data in DB-Table
	 *
	 * @param   array $data  DataArray for Replacement
	 * @return  array
	 *
	 * DEPRECATED: to be updated, not maintained
	 *
	 */
	public function replace($data)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::REPLACE_RIGHT)) return $isEntitled;
		
		// DB-REPLACE
		if ($this->db->replace($this->dbTable, $data))
			return success($this->db->insert_id());
		else
			return error($this->db->error(), FHC_DB_ERROR);
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
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		if (is_null($this->pk))
			return error(FHC_MODEL_ERROR, FHC_NOPK);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::UPDATE_RIGHT)) return $isEntitled;
		
		// UDFs
		if (isError($validate = $this->_manageUDFs($data, $id))) return $validate;
		
		// DB-UPDATE
		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
				$this->db->where($this->_arrayMergeIndex($this->pk, $id));
			else
				$this->db->where($id);
		}
		else
			$this->db->where($this->pk, $id);
		if ($this->db->update($this->dbTable, $data))
			return success($id);
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}
	
	/**
	 * Delete data from DB-Table
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function delete($id)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		if (is_null($this->pk))
			return error(FHC_MODEL_ERROR, FHC_NOPK);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::DELETE_RIGHT)) return $isEntitled;

		// DB-DELETE
		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
				$result = $this->db->delete($this->dbTable, $this->_arrayMergeIndex($this->pk, $id));
			else
				$result = $this->db->delete($this->dbTable, $id);
		}
		else
			$result = $this->db->delete($this->dbTable, array($this->pk => $id));
		if ($result)
			return success($id);
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}

	/**
	 * Load single data from DB-Table
	 *
	 * @param   string $id  ID (Primary Key) for SELECT ... WHERE
	 * @return  array
	 */
	public function load($id = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		if (is_null($this->pk))
			return error(FHC_MODEL_ERROR, FHC_NOPK);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::SELECT_RIGHT)) return $isEntitled;
		
		// DB-SELECT
		// Check for composite Primary Key
		if (is_array($id))
		{
			if (isset($id[0]))
				$result = $this->db->get_where($this->dbTable, $this->_arrayMergeIndex($this->pk, $id));
			else
				$result = $this->db->get_where($this->dbTable, $id);
		}
		elseif (empty($id))
			$result = $this->db->get($this->dbTable);
		else
			$result = $this->db->get_where($this->dbTable, array($this->pk => $id));
		
		if ($result)
			return success($this->toPhp($result));
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}

	/**
	 * Load data from DB-Table with a where clause
	 *
	 * @return  array
	 */
	public function loadWhere($where = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::SELECT_RIGHT)) return $isEntitled;
		
		// Execute query
		$result = $this->db->get_where($this->dbTable, $where);
		
		if ($result)
			return success($this->toPhp($result));
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}
	
	/**
	 * Load data and convert a record into a list of data from the main table,
	 * and linked to every element, the data from the side tables
	 *
	 * TODO:
	 * - Adding support for composed primary key
	 * - Adding support for cascading side tables (useful?)
	 *
	 * NOTE: sub queries are not supported in the from clause
	 *
	 * @return  array
	 */
	public function loadTree($mainTable, $sideTables, $where = null, $sideTablesAliases = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return error(FHC_MODEL_ERROR, FHC_NODBTABLE);
		
		// Checks rights
		if ($isEntitled = $this->_isEntitled(PermissionLib::SELECT_RIGHT)) return $isEntitled;
		
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
				$select .= $tables[$t] . '.' . $fields[$f]->column_name . ' AS ' . $tables[$t] . '_' . $fields[$f]->column_name;
				if ($f < count($fields) - 1) $select .= ', ';
			}
			
			if ($t < count($tables) - 1) $select .= ', ';
			
			$tableColumnsCountArray[$t] = count($fields);
		}
		
		// Adds the select clause
		$this->addSelect($select);
		
		// Execute the query
		$resultDB = $this->db->get_where($this->dbTable, $where);
		// If everything went ok...
		if ($resultDB)
		{
			// Converts the object that contains data, from the returned CI's object to an array
			// with the postgresql array and boolean types converterd
			$resultArray = $this->toPhp($resultDB);
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
						$objTmpArray[$f]->{str_replace($tables[$f] . '_', '', $key)} = $value;
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
						if (($k = $this->findMainTable($mainTableObj, $returnArray)) === false)
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
							else if (array_search($sideTableObj, $returnArray[$k]->{$sideTableProperty}) === false)
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
		if (is_null($joinTable) || is_null($cond) || !in_array($type, array('', 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER')))
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		
		$this->db->join($joinTable, $cond, $type);
		
		return success(true);
	}
	
	/**
	 * Add order clause
	 *
	 * @return  void
	 */
	public function addOrder($field = null, $type = 'ASC')
	{
		// Check Class-Attributes and parameters
		if (is_null($field) || !in_array($type, array('ASC', 'DESC')))
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		
		$this->db->order_by($field, $type);
		
		return success(true);
	}
	
	/**
	 * Add select clause
	 *
	 * @return  void
	 */
	public function addSelect($select, $escape = true)
	{
		// Check Class-Attributes and parameters
		if (is_null($select) || $select == '')
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		
		$this->db->select($select, $escape);
		
		return success(true);
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
		// Check Class-Attributes and parameters
		if (!is_numeric($start) || (is_numeric($start) && $start <= 0))
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		
		if (is_numeric($end) && $end > $start)
		{
			$this->db->limit($start, $end);
		}
		else
		{
			$this->db->limit($start);
		}
		
		return success(true);
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
		if (empty($tmpTable))
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		
		if (!empty($alias))
		{
			$tmpTable .= ' AS ' . $alias;
		}
		
		$this->db->from($tmpTable);
		
		return success(true);
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
			return error(FHC_MODEL_ERROR, FHC_MODEL_ERROR);
		}
		
		$this->db->group_by($fields);
		
		return success(true);
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
		else if ($val == DB_Model::PGSQL_BOOLEAN_FALSE)
		{
			return false;
		}
		
		// If it is null, let it be null
		return $val;
	}

	/**
	 * Convert PG-Array to PHP-Array
	 *
	 * @param   string	$s		PG-String to convert
	 * @param   string	$start	start-point for recursive iterations
	 * @param   string	$end	end-point for recursive iterations
	 * @return  array
	 */
	public function pgArrayPhp($s, $start=0, &$end=NULL)
	{
		if (empty($s) || $s[0]!='{') return NULL;
		$return = array();
		$br = 0;
		$string = false;
		$quote='';
		$len = strlen($s);
		$v = '';
		for ($i=$start+1; $i<$len;$i++)
		{
		    $ch = $s[$i];
		    if (!$string && $ch=='}')
			{
		        if ($v!=='' || !empty($return))
					$return[] = $v;
		        $end = $i;
		        break;
		    }
			else
				if (!$string && $ch=='{')
				    $v = $this->pgArrayPhp($s,$i,$i);
				else
					if (!$string && $ch==',')
					{
				    	$return[] = $v;
				    	$v = '';
					}
					else
						if (!$string && ($ch=='\'' || $ch=='\''))
						{
							$string = true;
							$quote = $ch;
						}
						else
							if ($string && $ch==$quote && $s[$i-1]=='\\')
								$v = substr($v,0,-1).$ch;
							else
								if ($string && $ch==$quote && $s[$i-1]!='\\')
									$string = FALSE;
								else
									$v .= $ch;
		}
		return $return;
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
		if (!empty($string))
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
	 * Return the property UDFs
	 */
	public function getUDFs()
	{
		return $this->UDFs;
	}
	
	/**
	 * Return one selected element of UDFs
	 */
	public function getUDF($udf)
	{
		if (isset($this->UDFs[$udf]))
		{
			return $this->UDFs[$udf];
		}
		
		return null;
	}
	
	/**
	 * Checks if this table has the field udf_values
	 */
	public function hasUDF()
	{
		return $this->fieldExists(DB_Model::UDF_FIELD_NAME);
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
	
	// ----------------------------------------------------------------------------
	
	/**
	 * Invalid ID
	 *
	 * @param   array $i	Array with indexes.
	 * @param   array $v	Array with values.
	 * @return  array
	 */
	protected function _arrayMergeIndex($i,$v)
	{
		if (count($i) != count($v))
			return false;
		for ($j=0; $j < count($i); $j++)
			$a[$i[$j]] = $v[$j];
		return $a;
	}
	
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
		if (!empty($query))
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
			
			// If no errors occurred
			if ($resultDB)
			{
				$result = success($this->toPhp($resultDB));
			}
			else
			{
				$result = error($this->db->error(), FHC_DB_ERROR);
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
		$result->schema = DB_Model::DEFAULT_SCHEMA;
		$result->table = $schemaAndTable;
		
		// If a schema is specified
		if (($pos = strpos($schemaAndTable, '.')) !==  false)
		{
			$result->schema = substr($schemaAndTable, 0, $pos);
			$result->table = substr($schemaAndTable, $pos + 1);
		}
		
		return $result;
	}
	
	// ----------------------------------------------------------------------------
	
	/**
	 * Returns all the UDF for this table
	 */
	private function _getUDFsDefinitions()
	{
		$this->load->model('system/UDF_model', 'UDFModel');
		
		$schema = DB_Model::DEFAULT_SCHEMA;
		$table = $this->dbTable;
		$dotPos = strpos($table, '.');
		
		if (is_numeric($dotPos) && $dotPos > 0)
		{
			$tmpArray = explode('.', $table);
			$schema = $tmpArray[0];
			$table = $tmpArray[1];
		}
		
		$this->UDFModel->addSelect(DB_Model::UDF_FIELD_JSON_DESCRIPTION);
		$udfResults = $this->UDFModel->loadWhere(
			array(
				'schema' => $schema,
				'table' => $table
			)
		);
		
		return $udfResults;
	}
	
	/**
	 * Move UDFs from $data to $UDFs
	 */
	private function _popUDFParameters(&$data)
	{
		foreach($data as $key => $val)
		{
			if (substr($key, 0, 4) == DB_Model::UDF_FIELD_PREFIX)
			{
				$this->UDFs[$key] = $val; // stores UDF value into property UDFs
				unset($data[$key]); // remove from data
			}
		}
	}
	
	/**
	 * Validates UDF value
	 */
	private function _validateUDFs($decodedUDFValidation, $udfName, $udfType, $udfValue)
	{
		$returnArrayValidation = array(); // returned value
		
		// 
		if (((isset($decodedUDFValidation->validation->{DB_Model::UDF_REQUIRED})
			&& $decodedUDFValidation->validation->{DB_Model::UDF_REQUIRED} === false)
			|| !isset($decodedUDFValidation->validation->{DB_Model::UDF_REQUIRED}))
			&& ($udfType == DB_Model::UDF_DROPDOWN_TYPE || $udfType == DB_Model::UDF_MULTIPLEDROPDOWN_TYPE))
		{
			$returnArrayValidation = array();
		}
		else
		{
			// If $udfValue is not an array, then store it inside a new array
			$tmpUdfValues = $udfValue;
			if (!is_array($udfValue))
			{
				$tmpUdfValues = array($udfValue);
			}
			
			// Loops through all the supplied UDFs values
			foreach($tmpUdfValues as $udfValIndx => $udfVal)
			{
				// If the single UDF value is not an array or an object
				if (!is_array($udfVal) && !is_object($udfVal))
				{
					// If the UDF value is numeric (integer, float, double...)
					if (is_numeric($udfVal))
					{
						// If min value attribute is present in the validation for this UDF,
						// then checks if the value of this UDF is compliant to this attribute
						if (isset($decodedUDFValidation->{DB_Model::UDF_MIN_VALUE})
							&& $udfVal < $decodedUDFValidation->{DB_Model::UDF_MIN_VALUE})
						{
							// validation is failed and the error is stored in $returnArrayValidation
							$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_VALUE);
						}
						
						// If max value attribute is present in the validation for this UDF,
						// then checks if the value of this UDF is compliant to this attribute
						if (isset($decodedUDFValidation->{DB_Model::UDF_MAX_VALUE})
							&& $udfVal > $decodedUDFValidation->{DB_Model::UDF_MAX_VALUE})
						{
							// validation is failed and the error is stored in $returnArrayValidation
							$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_VALUE);
						}
					}
					
					$strUdfVal = strval($udfVal); // store in $strUdfVal the string conversion of $udfVal
					// If min length attribute is present in the validation for this UDF,
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{DB_Model::UDF_MIN_LENGTH}) && isset($strUdfVal)
						&& strlen($strUdfVal) < $decodedUDFValidation->{DB_Model::UDF_MIN_LENGTH})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MIN_LENGTH);
					}
					
					// If max length attribute is present in the validation for this UDF,
					// then checks if the value of this UDF is compliant to this attribute
					if (isset($decodedUDFValidation->{DB_Model::UDF_MAX_LENGTH}) && isset($strUdfVal)
						&& strlen($strUdfVal) > $decodedUDFValidation->{DB_Model::UDF_MAX_LENGTH})
					{
						// validation is failed and the error is stored in $returnArrayValidation
						$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_MAX_LENGTH);
					}
					
					// If $udfVal is a string
					if (is_string($udfVal))
					{
						// Search for a php regular expression in the validation of this UDF, if one is found
						// then checks if the value of this UDF is compliant to this attribute
						if (isset($decodedUDFValidation->{DB_Model::UDF_REGEX})
							&& is_array($decodedUDFValidation->{DB_Model::UDF_REGEX}))
						{
							foreach($decodedUDFValidation->{DB_Model::UDF_REGEX} as $regexIndx => $regex)
							{
								if ($regex->language == DB_Model::UDF_REGEX_LANG)
								{
									if (preg_match($regex->expression, $udfVal) != 1)
									{
										// validation is failed and the error is stored in $returnArrayValidation
										$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_REGEX);
									}
								}
							}
						}
					}
				}
				else // otherwise the validation is failed and the error is stored in $returnArrayValidation
				{
					$returnArrayValidation[] = error($udfName, EXIT_VALIDATION_UDF_NOT_VALID_VAL);
				}
			}
		}
		
		// If no UDF validation errors were raised, it's a success!!
		if (count($returnArrayValidation) == 0)
		{
			$returnArrayValidation = success(true);
		}
		
		return $returnArrayValidation;
	}
	
	/**
	 * Manage UDFs
	 */
	private function _manageUDFs(&$data, $id = null)
	{
		$validate = success(true); // returned value
		// Contains a list of validation errors for the UDFs that have not passed the validation
		$notValidUDFsArray = array();
		
		if ($this->hasUDF()) // Checks if this table has UDFs
		{
			$resultUDFsDefinitions = $this->_getUDFsDefinitions(); // retrieves UDFs definitions for this table
			if (hasData($resultUDFsDefinitions)) // standard check if everything is ok and data are present
			{
				// Get udf values from $data & clean udf values from $data
				// NOTE: Must be performed here because the load method populates the property UDFs too
				$this->_popUDFParameters($data);
				
				$requiredUDFsArray = array(); // contains a list of required UDFs
				// Contains the UDFs values to be stored
				// NOTE: the UDFs supplied that are not present in the UDF definition of this table, will be discarded
				$toBeStoredUDFsArray = array();
				
				// Decodes json that define the UDFs for this table
				$decodedUDFDefinitions = json_decode(
					$resultUDFsDefinitions->retval[0]->{DB_Model::UDF_FIELD_JSON_DESCRIPTION}
				);
				
				// Loops through the UDFs definitions
				for($i = 0; $i < count($decodedUDFDefinitions); $i++)
				{
					$decodedUDFDefinition = $decodedUDFDefinitions[$i]; // Definition of a single UDF
					
					// If validation rules are present for this UDF description and the required attribute is === true
					// then add this UDF into $requiredUDFsArray
					if(isset($decodedUDFDefinition->validation)
						&& isset($decodedUDFDefinition->validation->{DB_Model::UDF_REQUIRED})
						&& $decodedUDFDefinition->validation->{DB_Model::UDF_REQUIRED} === true)
					{
						$requiredUDFsArray[$decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME}] = error(
							$decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME},
							EXIT_VALIDATION_UDF_REQUIRED
						);
					}
					
					// Loops through the UDFs values that should be stored
					foreach($this->UDFs as $key => $val)
					{
						// If this is the definition of this UDF
						if ($decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME} == $key)
						{
							if (isset($decodedUDFDefinition->validation)) // If validation rules are present for this UDF
							{
								// Validation!!!
								$validate = $this->_validateUDFs(
									$decodedUDFDefinition->validation, // 
									$decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME}, // 
									$decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME},
									$val // 
								);
								
								// If the validation attribute required is === true for this UDF
								// and this UDF is present in the array $requiredUDFsArray
								// then removes this UDF from the array $requiredUDFsArray
								// because this UDF is present in the property UDFs (the list of UDFs that should be stored)
								// therefore it was supplied
								if (isset($decodedUDFDefinition->validation->{DB_Model::UDF_REQUIRED})
									&& $decodedUDFDefinition->validation->{DB_Model::UDF_REQUIRED} === true
									&& isset($requiredUDFsArray[$decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME}]))
								{
									// 
									if ($decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_CHKBOX_TYPE
										&& ($val == DB_Model::STRING_FALSE || $val === false))
									{
										// NOP
									}
									// 
									else if (($decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_DROPDOWN_TYPE
											|| $decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_MULTIPLEDROPDOWN_TYPE)
											&& ($val == DB_Model::STRING_NULL || $val == null))
									{
										// NOP
									}
									else
									{
										unset($requiredUDFsArray[$decodedUDFDefinition->{DB_Model::UDF_ATTRIBUTE_NAME}]);
									}
								}
							}
							
							// If validation is ok copy the value that is to be stored into $toBeStoredUDFsArray
							if (isSuccess($validate))
							{
								// If this UDF is a checkbox
								if ($decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_CHKBOX_TYPE)
								{
									// Converts from string to boolean
									if ($val == DB_Model::STRING_TRUE) $val = true;
									else if ($val == DB_Model::STRING_FALSE) $val = false;
								}
								else if ($decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_DROPDOWN_TYPE
										|| $decodedUDFDefinition->{DB_Model::UDF_TYPE_NAME} == DB_Model::UDF_MULTIPLEDROPDOWN_TYPE)
								{
									if ($val == DB_Model::STRING_NULL)
									{
										$val = null;
									}
								}
								
								$toBeStoredUDFsArray[$key] = $val;
							}
							else // otherwise store the validation error in $notValidUDFsArray
							{
								$notValidUDFsArray[] = $validate;
							}
						}
					}
				}
				
				// Copies the remaining required UDFs into $notValidUDFsArray
				// because they were not supplied, therefore must be notified as error
				foreach($requiredUDFsArray as $key => $val)
				{
					$notValidUDFsArray[] = array($val);
				}
				
				// If the validation of all the supplied UDFs is ok
				if (count($notValidUDFsArray) == 0)
				{
					// An update is performed, then in this case it preserves the values
					// of the UDF that are not updated
					if ($id != null)
					{
						$record = $this->load($id); // retrive the DB record
						// Checks that everything is ok and that there is only one record
						if (isSuccess($record) && count($record->retval) == 1)
						{
							$recordFields = (array)$record->retval[0]; // convert to an array
							foreach($recordFields as $fieldName => $fieldValue)
							{
								// If this field is an UDF
								if (substr($fieldName, 0, 4) == DB_Model::UDF_FIELD_PREFIX)
								{
									// If this field is not present in the given parameters
									// then copy it from the DB without changes
									if (!array_key_exists($fieldName, $toBeStoredUDFsArray))
									{
										$toBeStoredUDFsArray[$fieldName] = $fieldValue;
									}
								}
							}
						}
					}
					$encodedToBeStoredUDFs = json_encode($toBeStoredUDFsArray); // encode to json
					if ($encodedToBeStoredUDFs !== false) // if encode was ok
					{
						// Save the supplied UDFs values
						$data[DB_Model::UDF_FIELD_NAME] = $encodedToBeStoredUDFs;
					}
				}
				else // otherwise the returning value will be the list of UDFs validation errors
				{
					$validate = error($notValidUDFsArray, EXIT_VALIDATION_UDF);
				}
			}
		}
		
		return $validate;
	}
	
	/**
	 * Checks if the caller is entitled to perform this operation with this right
	 */
	private function _isEntitled($permission)
	{
		// If the caller is _not_ a model _and_ tries to read data, then avoids to check permissions
		// Otherwise checks always the permissions
		if (($permission == PermissionLib::SELECT_RIGHT &&
			substr(get_called_class(), -6) == DB_Model::MODEL_POSTFIX) ||
			$permission != PermissionLib::SELECT_RIGHT)
		{
			// If true is not returned, then an error has occurred
			if (($isEntitled = $this->isEntitled($this->dbTable, $permission, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			{
				// Before returning the object containing the error, reset the build query
				// This is for preventing that other parts of the query will be built before of the next execution
				$this->resetQuery();
				
				return $isEntitled;
			}
		}
	}
	
	/**
	 * Converts array and boolean data types from PgSql to php
	 * NOTE: PostgreSQL php drivers returns:
	 * - A boolean value if the query is of the write type (INSERT, UPDATE, DELETE...)
	 * - A FALSE value on failure
	 * - Otherwise an object filled with data on success
	 */
	private function toPhp($result)
	{
		$toPhp = $result; // if there is nothing to convert then return the result from DB
		
		// If it's an object its fields will be parsed to find booleans and arrays types
		if (is_object($result))
		{
			$toBeConverterdArray = array(); // Fields to be converted
			$metaDataArray = $result->field_data(); // Fields information
			for($i = 0; $i < count($metaDataArray); $i++) // Looking for booleans and arrays
			{
				// If array type or boolean type
				if (strpos($metaDataArray[$i]->type, DB_Model::PGSQL_ARRAY_TYPE) !== false
					|| $metaDataArray[$i]->type == DB_Model::PGSQL_BOOLEAN_TYPE
					|| $metaDataArray[$i]->name == DB_Model::UDF_FIELD_NAME)
				{
					// Name and type of the field to be converted
					$toBeConverted = new stdClass();
					// Set the type of the field to be converted
					$toBeConverted->type = $metaDataArray[$i]->type;
					// Set the name of the field to be converted
					$toBeConverted->name = $metaDataArray[$i]->name;
					// Add the field to be converted to $toBeConverterdArray
					array_push($toBeConverterdArray, $toBeConverted);
				}
			}
			
			// If there is something to convert, otherwhise don't lose time
			if (count($toBeConverterdArray) > 0)
			{
				// Returns the array of objects, each of them represents a DB record
				$resultsArray = $result->result();
				// Looping on results
				for($i = 0; $i < count($resultsArray); $i++)
				{
					// Single element
					$resultElement = $resultsArray[$i];
					// Looping on fields to be converted
					for($j = 0; $j < count($toBeConverterdArray); $j++)
					{
						// Single element
						$toBeConverted = $toBeConverterdArray[$j];
						
						// Array type
						if (strpos($toBeConverted->type, DB_Model::PGSQL_ARRAY_TYPE) !== false)
						{
							$resultElement->{$toBeConverted->name} = $this->pgsqlArrayToPhpArray(
								$resultElement->{$toBeConverted->name},
								$toBeConverted->type == DB_Model::PGSQL_BOOLEAN_ARRAY_TYPE
							);
						}
						// Boolean type
						else if ($toBeConverted->type == DB_Model::PGSQL_BOOLEAN_TYPE)
						{
							$resultElement->{$toBeConverted->name} = $this->pgBoolPhp($resultElement->{$toBeConverted->name});
						}
						// UDF
						else if ($toBeConverted->type == DB_Model::UDF_FIELD_TYPE
							&& substr($toBeConverted->name, 0, 4) == DB_Model::UDF_FIELD_PREFIX)
						{
							$jsonValues = json_decode($resultElement->{$toBeConverted->name}); // decode UDFs values
							if ($jsonValues != null) // if decode is ok
							{
								// For every UDF
								foreach($jsonValues as $key => $value)
								{
									$resultElement->{$key} = $value; // create a new element called like the UDF
									$this->UDFs[$key] = $value; // stores the UDF in the property UDFs
								}
							}
							unset($resultElement->{$toBeConverted->name}); // remove udf_values from the response
						}
					}
				}
				// Returns DB data as an array
				$toPhp = $resultsArray;
			}
			// And returns DB data as an array
			else
			{
				$toPhp = $result->result();
			}
		}
		
		return $toPhp;
	}
	
	/**
	 * Used in loadTree to find the main tables
	 */
	private function findMainTable($mainTableObj, $mainTableArray)
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
}