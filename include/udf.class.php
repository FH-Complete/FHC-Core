<?php
/* Copyright (C) 2009 Technikum-Wien
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
 * Authors: 
 */

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/../config/global.config.inc.php');

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
	 * 
	 */
	public function getTitlesPerson()
	{
		return $this->_loadTitles($this->_getUDFDefinition($this->loadPersonJsons()));
	}
	
	/**
	 * 
	 */
	public function getTitlesPrestudent()
	{
		return $this->_loadTitles($this->_getUDFDefinition($this->loadPrestudentJsons()));
	}
	
	/**
	 * 
	 */
	public function loadPersonJsons()
	{
		return $this->_loadJsons('public', 'tbl_person');
	}
	
	/**
	 * 
	 */
	public function loadPrestudentJsons()
	{
		return $this->_loadJsons('public', 'tbl_prestudent');
	}
	
	/**
	 * 
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
	 * 
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
	 * 
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
     * 
     */
    private function _sortJsonSchemas(&$jsonSchemasArray)
    {
		// 
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
     * 
     */
    private function _getUDFDefinition($jsons)
    {
		$names = array();
		
		// 
		if ($jsons != null && ($jsonsDecoded = json_decode($jsons)) != null)
		{
			if (is_object($jsonsDecoded) || is_array($jsonsDecoded))
			{
				// 
				if (is_object($jsonsDecoded))
				{
					$jsonsDecoded = array($jsonsDecoded);
				}
				
				$this->_sortJsonSchemas($jsonsDecoded); // 
				
				foreach($jsonsDecoded as $udfJsonShema)
				{
					if (isset($udfJsonShema->name) && isset($udfJsonShema->title))
					{
						$names[] = array('name' => $udfJsonShema->name, 'title' => $udfJsonShema->title);
					}
				}
			}
		}
		
		return $names;
    }
    
    /**
     * 
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
		
		return $titles;
	}
}