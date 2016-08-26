<?php

class DB_Model extends FHC_Model
{
	protected $dbTable;  	// Name of the DB-Table for CI-Insert, -Update, ...
	protected $pk;  		// Name of the PrimaryKey for DB-Update, Load, ...
	protected $hasSequence;	// False if this table has a composite primary key that is not using a sequence
							// True if this table has a primary key that uses a sequence
	
	function __construct($dbTable = null, $pk = null, $hasSequence = true)
	{
		parent::__construct();
		$this->dbTable = $dbTable;
		$this->pk = $pk;
		$this->hasSequence = $hasSequence;
		$this->load->database();
	}

	/** ---------------------------------------------------------------
	 * Insert Data into DB-Table
	 *
	 * @param   array $data  DataArray for Insert
	 * @return  array
	 */
	public function insert($data)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);

		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 'i'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

		// DB-INSERT
		if ($this->db->insert($this->dbTable, $data))
		{
			// If the table has a primary key that uses a sequence
			if ($this->hasSequence === true)
			{
				return $this->_success($this->db->insert_id());
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

				return $this->_success($primaryKeysArray);
			}
		}
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

	/** ---------------------------------------------------------------
	 * Replace Data in DB-Table
	 *
	 * @param   array $data  DataArray for Replacement
	 * @return  array
	 */
	public function replace($data)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 'ui'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

		// DB-REPLACE
		if ($this->db->replace($this->dbTable, $data))
			return $this->_success($this->db->insert_id());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

	/** ---------------------------------------------------------------
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
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		if (is_null($this->pk))
			return $this->_error(lang('fhc_'.FHC_NOPK), FHC_MODEL_ERROR);
		
		// Check rights
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 'u'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

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
			return $this->_success($id);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

	/** ---------------------------------------------------------------
	 * Load single data from DB-Table
	 *
	 * @param   string $id  ID (Primary Key) for SELECT ... WHERE
	 * @return  array
	 */
	public function load($id = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		if (is_null($this->pk))
			return $this->_error(lang('fhc_'.FHC_NOPK), FHC_MODEL_ERROR);
		
		
		// Check rights only if this method is called from a model
		if (substr(get_called_class(), -6) == '_model')
			if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

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
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}

	/** ---------------------------------------------------------------
	 * Load data from DB-Table with a where clause
	 *
	 * @return  array
	 */
	public function loadWhere($where = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		// Check rights
		// Check rights only if this method is called from a model
		if (substr(get_called_class(), -6) == '_model')
			if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

		// Execute query
		$result = $this->db->get_where($this->dbTable, $where);
		
		if ($result)
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	/** ---------------------------------------------------------------
	 * Load data and convert a record into a list of data from the main table,
	 * and linked to every element, the data from the side tables
	 *
	 * TODO:
	 * - Adding support for composed primary key
	 * - Adding support for cascading side tables
	 *
	 * @return  array
	 */
	public function loadList($mainTable, $sideTables, $where = null, $sideTablesAliases = null)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		// Check rights
		// Check rights only if this method is called from a model
		if (substr(get_called_class(), -6) == '_model')
			if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);
		
		// List of tables on which it will work
		$tables = array_merge(array($mainTable), $sideTables);
		// Array that will contain the number of columns of each table
		$tableColumnsCountArray = array();
		
		// Generates the select clause based on the columns of each table
		$select = "";
		for ($t = 0; $t < count($tables); $t++)
		{
			$fields = $this->db->list_fields($tables[$t]); // list of the columns of the current table
			for ($f = 0; $f < count($fields); $f++)
			{
				// To avoid overwriting of the properties within the object returned by CI
				// will be given an alias to every column, that will be composed with the following schema
				// <table name>.<column name> AS <table_name>_<column name>
				$select .= $tables[$t] . "." . $fields[$f] . " AS " . $tables[$t] . "_" . $fields[$f];
				if ($f < count($fields) - 1) $select .= ", ";
			}
			
			if ($t < count($tables) - 1) $select .= ", ";
			
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
			$resultArray = $resultDB->result();
			// Array that will contain all the mainTable records, and to each record the linked data
			// of a side table
			$returnArray = array();
			$returnArrayCounter = 0;	// Array counter
			$prevPK = null;				// Previous primary key
			
			// Iterates the array that contains data from DB
			for ($i = 0; $i < count($resultArray); $i++)
			{
				// Converts an object properties to an associative array
				$objectVars = get_object_vars($resultArray[$i]);
				// Temporary array that will contain a representation of every records returned from DB
				// every element is an associative array that contains all the data of each table
				$objTmpArray = array();
				
				// Gets all the data of a single table from the returned record, and creates an object filled with these data
				for ($f = 0; $f < count($tableColumnsCountArray); $f++)
				{
					$objTmpArray[$f] = new stdClass(); // Object that will represent a data set of a table
					foreach (array_slice($objectVars, $f == 0 ? 0 : $tableColumnsCountArray[$f - 1], $tableColumnsCountArray[$f]) as $key => $value)
					{
						$objTmpArray[$f]->{str_replace($tables[$f] . "_", "", $key)} = $value;
					}
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
						$sideTableProperty = $sideTablesAliases[$t -1];
					}
					
					if ($prevPK == $mainTableObj->{$this->pk})
					{
						array_push($returnArray[$returnArrayCounter - 1]->{$sideTableProperty}, $sideTableObj);
					}
					else
					{
						$mainTableObj->{$sideTableProperty} = array($sideTableObj);
						$returnArray[$returnArrayCounter++] = $mainTableObj;
					}
					
					$prevPK = $mainTableObj->{$this->pk};
				}
			}
			
			// Sets result with the standard success object that contains all the studiengang
			$result = $this->_success($returnArray);
		}
		else
		{
			$result = $this->_error($resultDB);
		}
		
		return $result;
	}

	/** ---------------------------------------------------------------
	 * Add a table to join with
	 *
	 * @return  void
	 */
	public function addJoin($joinTable = null, $cond = null, $type = '')
	{
		// Check parameters
		if (is_null($joinTable) || is_null($cond) || !in_array($type, array('', 'LEFT', 'RIGHT', 'OUTER', 'INNER', 'LEFT OUTER', 'RIGHT OUTER')))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		// Check rights for joined table
		// Check rights only if this method is called from a model
// 		if (substr(get_called_class(), -6) == '_model')
// 			if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($joinTable), 's'))
// 			 return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($joinTable), FHC_MODEL_ERROR);

		$this->db->join($joinTable, $cond, $type);
		
		return $this->_success(true);
	}
	
	/** ---------------------------------------------------------------
	 * Add order clause
	 *
	 * @return  void
	 */
	public function addOrder($field = null, $type = 'ASC')
	{
		// Check Class-Attributes and parameters
		if (is_null($field) || !in_array($type, array('ASC', 'DESC')))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		$this->db->order_by($field, $type);
		
		return $this->_success(true);
	}
	
	/** ---------------------------------------------------------------
	 * Add select clause
	 *
	 * @return  void
	 */
	public function addSelect($select, $escape = true)
	{
		// Check Class-Attributes and parameters
		if (is_null($select) || $select == '')
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		$this->db->select($select, $escape);
		
		return $this->_success(true);
	}
	
	/** ---------------------------------------------------------------
	 * Add distinct clause
	 *
	 * @return  void
	 */
	public function addDistinct()
	{
		$this->db->distinct();
	}
	
	/** ---------------------------------------------------------------
	 * Add limit clause
	 *
	 * @return  void
	 */
	public function addLimit($start = null, $end = null)
	{
		// Check Class-Attributes and parameters
		if (!is_numeric($start) || (is_numeric($start) && $start <= 0))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		
		if (is_numeric($end) && $end > $start)
		{
			$this->db->limit($start, $end);
		}
		else
		{
			$this->db->limit($start);
		}
		
		return $this->_success(true);
	}

	/** ---------------------------------------------------------------
	 * Delete data from DB-Table
	 *
	 * @param   string $id  Primary Key for DELETE
	 * @return  array
	 */
	public function delete($id)
	{
		// Check Class-Attributes
		if (is_null($this->dbTable))
			return $this->_error(lang('fhc_'.FHC_NODBTABLE), FHC_MODEL_ERROR);
		if (is_null($this->pk))
			return $this->_error(lang('fhc_'.FHC_NOPK), FHC_MODEL_ERROR);
		
		// Check rights only if this method is called from a model
		if (substr(get_called_class(), -6) == '_model')
			if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 'd'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);

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
			return $this->_success($id);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	/** ---------------------------------------------------------------
	 * Reset the query builder state
	 *
	 * @return  void
	 */
	public function resetQuery()
	{
		$this->db->reset_query();
	}
	
	/** ---------------------------------------------------------------
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

	/** ---------------------------------------------------------------
	 * Convert PG-Boolean to PHP-Boolean
	 *
	 * @param   char	$b	PG-Char to convert
	 * @return  bool
	 */
	public function pgBoolPhp($b)
	{
		if (is_null($b))
			return null;
		elseif ($b==='t')
			return true;
		else
			return false;
	}

	/** ---------------------------------------------------------------
	 * Convert PG-Array to PHP-Array
	 *
	 * @param   string	$s		PG-String to convert
	 * @param   string	$start	start-point for recursive iterations
	 * @param   string	$end	end-point for recursive iterations
	 * @return  array
	 */
	public function pgArrayPhp($s,$start=0,&$end=NULL)
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
						if (!$string && ($ch=='"' || $ch=="'"))
						{
							$string = true;
							$quote = $ch;
						}
						else
							if ($string && $ch==$quote && $s[$i-1]=="\\")
								$v = substr($v,0,-1).$ch;
							else
								if ($string && $ch==$quote && $s[$i-1]!="\\")
									$string = FALSE;
								else
									$v .= $ch;
		}
		return $return;
	}

	/** ---------------------------------------------------------------
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
	
	/** ---------------------------------------------------------------
	 * Invalid ID
	 *
	 * @param   integer  config.php error code numbers
	 * @return  array
	 */
	protected function _invalid_id($error = '')
	{
		return array(
			'err' => 1,
			'code' => $error,
			'msg' => lang('fhc_' . $error)
		);
	}
}