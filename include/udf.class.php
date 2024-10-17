<?php
/* Copyright (C) 2009-2021 fhcomplete.net
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/../config/global.config.inc.php');
require_once(dirname(__FILE__).'/benutzerberechtigung.class.php');

/**
 * Used to export UDF in MS Excel format
 */
class UDF extends basis_db
{
	/**
	 * Construct
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Gets the titles (short description) of the UDF related to the table tbl_person
	 */
	public function getTitlesPerson()
	{
		return $this->_loadTitles($this->_getUDFDefinition($this->loadPersonJsons()));
	}

	/**
	 * Gets the titles (short description) of the UDF related to the table tbl_prestudent
	 */
	public function getTitlesPrestudent()
	{
		return $this->_loadTitles($this->_getUDFDefinition($this->loadPrestudentJsons()));
	}

	/**
	 * Loads the UDF definitions related to the table tbl_person
	 */
	public function loadPersonJsons()
	{
		$jsons = null;

		if ($this->existsUDF() && $this->prestudentHasUDF())
		{
			$jsons = $this->_loadJsons('public', 'tbl_person');
		}

		return $jsons;
	}

	/**
	 * Loads the UDF definitions related to the table tbl_prestudent
	 */
	public function loadPrestudentJsons()
	{
		$jsons = null;

		if ($this->existsUDF() && $this->prestudentHasUDF())
		{
			$jsons = $this->_loadJsons('public', 'tbl_prestudent');
		}

		return $jsons;
	}

	/**
	 * Checks if the table system.tbl_udf exists
	 */
	public function existsUDF()
	{
		$existsUDF = false;

		$query = 'SELECT COUNT(*) AS count
					FROM information_schema.columns
				   WHERE table_schema = \'system\'
				     AND table_name = \'tbl_udf\'';

		if (!$this->db_query($query))
		{
			$this->errormsg = 'Error!!!';
		}
		else
		{
			if ($row = $this->db_fetch_object())
			{
				if (isset($row->count) && $row->count > 0)
				{
					$existsUDF = true;
				}
			}
		}

		return $existsUDF;
	}

	/**
	 * Checks if the column udf_values exists in table tbl_person
	 */
	public function personHasUDF()
	{
		$personHasUDF = false;

		$query = 'SELECT COUNT(*) AS count
					FROM information_schema.columns
				   WHERE table_schema = \'public\'
				     AND table_name = \'tbl_person\'
				     AND column_name = \'udf_values\'';

		if (!$this->db_query($query))
		{
			$this->errormsg = 'Error!!!';
		}
		else
		{
			if ($row = $this->db_fetch_object())
			{
				if (isset($row->count) && $row->count > 0)
				{
					$personHasUDF = true;
				}
			}
		}

		return $personHasUDF;
	}

	/**
	 * Checks if the column udf_values exists in table tbl_prestudent
	 */
	public function prestudentHasUDF()
	{
		$prestudentHasUDF = false;

		$query = 'SELECT COUNT(*) AS count
					FROM information_schema.columns
				   WHERE table_schema = \'public\'
				     AND table_name = \'tbl_prestudent\'
				     AND column_name = \'udf_values\'';

		if (!$this->db_query($query))
		{
			$this->errormsg = 'Error!!!';
		}
		else
		{
			if ($row = $this->db_fetch_object())
			{
				if (isset($row->count) && $row->count > 0)
				{
					$prestudentHasUDF = true;
				}
			}
		}

		return $prestudentHasUDF;
	}

	/**
	 * Concatenates a list of values of a dropdown element to a string
	 */
	public function dropdownListValuesToString($listValues, $enum)
	{
		$toWrite = '';

		foreach ($listValues as $value)
		{
			foreach ($enum as $element)
			{
				if (is_object($element))
				{
					if ($element->id == $value)
					{
						$toWrite .= $element->description;
						break;
					}
				}
				else if (is_array($element))
				{
					if ($element[0] == $value)
					{
						$toWrite .= $element[1];
						break;
					}
				}
				else if ($element == $value)
				{
					$toWrite .= $element;
					break;
				}
			}
			$toWrite .= ' ';
		}

		return $toWrite;
	}

	/**
	 * Returns a string that represent the value of a UDF using the given value and description
	 */
	public function encodeToString($decodedJson, $udfDescription)
	{
		$toString = '';
		$udfName = $udfDescription['name'];

		if (isset($decodedJson[$udfName]))
		{
			if (is_string($decodedJson[$udfName]) || is_numeric($decodedJson[$udfName]))
			{
				$toString = $decodedJson[$udfName];
			}
			else if (is_bool($decodedJson[$udfName]))
			{
				$toString = $decodedJson[$udfName] === true ? 'true' : 'false';
			}
			else if(is_array($decodedJson[$udfName]) && isset($udfDescription['enum']))
			{
				$toString = $this->dropdownListValuesToString($decodedJson[$udfName], $udfDescription['enum']);
			}
		}

		return $toString;
	}

	/**
	 * Loads the UDF definitions related to the given schema and table
	 */
	private function _loadJsons($schema, $table)
	{
		$jsons = null;
		$query = 'SELECT jsons FROM system.tbl_udf WHERE schema = \''.$schema.'\' AND "table" = \''.$table.'\'';

		if (!$this->db_query($query))
		{
			$this->errormsg = 'Error occurred while loading jsons';
		}
		else
		{
			if ($row = $this->db_fetch_object())
			{
				$jsons = $row->jsons;
			}
		}

		return $jsons;
	}

	/**
     * Sorts the UDF definitions using the proprierty "sort"
     */
    private function _sortJsonSchemas(&$jsonSchemasArray)
    {
		usort($jsonSchemasArray, function ($a, $b) {
			//
			if (!isset($a->sort))
			{
				$a->sort = 9999;
			}
			if (!isset($b->sort))
			{
				$b->sort = 9999;
			}

			if ($a->sort == $b->sort)
			{
				return 0;
			}

			return ($a->sort < $b->sort) ? -1 : 1;
		});
    }

	/**
	 * Returns an array of associative arrays that contains the couple name and title related to an UDF
	 * These data are retrived from the UDF definitions given as parameter
	 */
	private function _getUDFDefinition($jsons)
	{
		$names = array();
		$uid = get_uid(); // get the UID of the logged person

		if ($uid == null) return names(); // if no logged then it is not possible to loads UDFs

		// Gets the permissions for the logged user
		$berechtigung = new benutzerberechtigung();
		$berechtigung->getBerechtigungen($uid);
 
		if ($jsons != null && ($jsonsDecoded = json_decode($jsons)) != null)
		{
			if (is_object($jsonsDecoded) || is_array($jsonsDecoded))
			{
				if (is_object($jsonsDecoded))
				{
					$jsonsDecoded = array($jsonsDecoded);
				}

				$this->_sortJsonSchemas($jsonsDecoded);

				foreach ($jsonsDecoded as $udfJsonShema)
				{
					// Checks if the requiredPermissions property exists
					if (isset($udfJsonShema->requiredPermissions))
					{
						$isAllowed = false;

						// If requiredPermissions is an array check if at least one of the permissions belongs to the logged user
						if (is_array($udfJsonShema->requiredPermissions))
						{
							foreach ($udfJsonShema->requiredPermissions as $permission)
							{
								$isAllowed = $berechtigung->isBerechtigt($permission);
								if ($isAllowed === true) break;
							}
						}
						else // otherwise check it directly
						{
							$isAllowed = $berechtigung->isBerechtigt($udfJsonShema->requiredPermissions);
						}

						// If the logged user has at least one of the required permissions
						if ($isAllowed === true)
						{
							if (isset($udfJsonShema->name) && isset($udfJsonShema->title))
							{
								$tmpArray = array('name' => $udfJsonShema->name, 'title' => $udfJsonShema->title);

								if (isset($udfJsonShema->type)
									&& ($udfJsonShema->type == 'dropdown' || $udfJsonShema->type == 'multipledropdown')
									&& isset($udfJsonShema->listValues) && isset($udfJsonShema->listValues->enum))
								{
									$tmpArray['enum'] = $udfJsonShema->listValues->enum;
								}

								$names[] = $tmpArray;
							}
						} // otherwise this UDF is discarted because the requiredPermissions is mandatory
					} // otherwise this UDF is discarted because the requiredPermissions is mandatory
				}
			}
		}

		return $names;
	}

    /**
     * Loads UDf titles from phrases
     */
	private function _loadTitles($udfDefinitions)
	{
		$titles = array();
		$in = '';

		for($i = 0; $i < count($udfDefinitions); $i++)
		{
			$udfDefinition = $udfDefinitions[$i];
			$in .= '\''.$udfDefinition['title'].'\'';

			if ($i < count($udfDefinitions) - 1) $in .= ', ';
		}

		if ($in != '')
		{
			$query = 'SELECT pt.text AS title, p.phrase AS phrase
							FROM system.tbl_phrase p INNER JOIN system.tbl_phrasentext pt USING(phrase_id)
						WHERE pt.sprache = \''.DEFAULT_LEHREINHEIT_SPRACHE.'\'
							AND p.phrase IN ('.$in.')';

			if (!$this->db_query($query))
			{
				$this->errormsg = 'Error occurred while loading jsons';
			}
			else
			{
				while ($row = $this->db_fetch_assoc())
				{
					for($i = 0; $i < count($udfDefinitions); $i++)
					{
						$udfDefinition = $udfDefinitions[$i];
						if ($udfDefinition['title'] == $row['phrase'])
						{
							$udfDefinition['description'] = $row['title'];
							$titles[] = $udfDefinition;
						}
					}
				}
			}
		}

		return $titles;
	}
}

