<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *
 */
/**
 * Klasse fuer Datenbankabstraktion
 */

require_once(dirname(__FILE__).'/basis.class.php');

abstract class db extends basis
{
	protected static $db_conn=null;
	protected $db_result=null;
	protected $debug=false;

	function __construct()
	{
		if(!defined('FHC_INTEGER'))
		{
			define('FHC_INTEGER',1);
			define('FHC_STRING',2);
			define('FHC_BOOLEAN',3);
			define('FHC_LANG_ARRAY',4);
		}
		if (is_null(db::$db_conn))
			$this->db_connect();
	}

	abstract function db_connect();
	abstract function db_query($sql);
	abstract function db_fetch_object($result=null, $i=null);
	abstract function db_fetch_array($result=null);
	abstract function db_fetch_row($result=null, $i=null);
	abstract function db_fetch_assoc($result=null, $i=null);
	abstract function db_result($result = null, $i, $item);
	abstract function db_num_rows($result=null);
	abstract function db_num_fields($result=null);
	abstract function db_field_name($result=null, $i);
	abstract function db_affected_rows($result=null);
	abstract function db_result_seek($result=null, $offset);
	abstract function db_last_error();
	abstract function db_free_result($result=null);
	abstract function db_version();
	abstract function db_escape($var);
	abstract function db_null_value($var, $qoute=true);
	abstract function db_qoute($var);
	abstract function db_add_param($var, $type=FHC_STRING, $nullable=true);
	abstract function db_parse_bool($var);
	abstract function db_implode4SQL($var);
	abstract function db_getResultJSON($result = null);
	abstract function db_parse_array($var);


	/**
	 * Erzeugt aus den Funktionsparameter eine SQL Abfrage
	 * --- Wird in der Art Sonderzeichen gefunden wird dieses als FunktionsParmeter verarbeitet
	 * @param art die SQL Abfrage die erzeugt werden soll Default ist 'select'
	 * @param distinct - nur wenn art ist 'select' ist
	 * @param fields welche Datenbankfelder sind betroffen
	 * @param table Datenbanktabelle die betroffen ist/sind
	 * @param where Bedingung zum lesen in der Datenbank
	 * @param order Sortierung der Anfrage - nur wenn art ist 'select' ist
	 * @param limit Anzahl der Datenmenge die geliefert werden soll - nur wenn art ist 'select' ist
	 * @param sql der Kpl. SQL String zur Datenbearbeitung der DB

	 * @return false und errormsg wenn ein Fehler aufgetreten ist, Datenbankobjekt wenn alles OK
	 */
	public function SQL($pArt='select',$pDistinct=false,$pFields='',$pTable='',$pWhere='',$pOrder='',$pLimit='',$pSql='')
	{
		$this->errormsg='';
		$result=false;

		$sql=(!is_null($pSql)?trim($pSql):'');
		$art=(!is_null($pArt)?trim($pArt):'');
		$distinct=($pDistinct?true:false);
		$fields=(!is_null($pFields)?trim($pFields):'');
		$table=(!is_null($pTable)?trim($pTable):'');
		$where=(!is_null($pWhere)?trim($pWhere):'');
		$order=(!is_null($pOrder)?trim($pOrder):'');
		$limit=(is_numeric($pLimit)?$pLimit:'');

		if (empty($sql) && empty($art))
		{
			$this->errormsg='die SQL Art fehlt!';
			return $result;
		}
		else if (empty($sql) && empty($table))
		{
			$this->errormsg='die SQL Tabelle fehlt!';
			return $result;
		}

		// DB Abfrage zusammenbauen
		if (!empty($pSql))
		{
			$sql=$pSql;
		}
		else
		{
			$sql.=$art. ' ';
			if ($art=='select')
				$sql.=($distinct?' distinct ':'');
			$sql.=($fields?$fields:' * ');
			$sql.=($table?' from '.trim($table).' ':'');
			if (strstr('where',strtolower($where)))
				$sql.=($where?' '.trim($where).' ':'');
			else
				$sql.=($where?' where '.trim($where).' ':'');
			if ($art=='select')
			{
			    // FIXME: If $where is e.g. 'orderstatus' because there's a column named orderstatus, this would
			    // fail horribly? Same for the 'where'-stuff above. -MP
				if (strstr('order',strtolower($where)))
					$sql.=($order?trim($order).' ':'');
				else
					$sql.=($order?' order by '.trim($order).' ':'');
			}
			if ($art=='select')
			{
				if (strstr('limit',strtolower($where)))
					$sql.=($limit?trim($limit).' ':'');
				else
					$sql.=($limit?' limit '.trim($limit).' ':'');
			}
		}

		if (!$results=$this->db_query($sql))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}

		if ($art!='select' && empty($pSql))
			return true;

		if (!$num=$this->db_num_rows($results))
		{
			$this->errormsg='keine Daten gefunden';
			return false;
		}
		// Lesen aller DB Daten
		$rows=array();
		while($row = $this->db_fetch_object($results))
			$rows[]=$row;
		return $rows;
	}

	/**
	 * Replace the password names with the related passwords in a SQL string, to decrypt data from the DB
	 */
	protected function replaceSQLDecryptionPassword($sql)
	{
		$newSQL = null;

		// If the global constant CI_ENVIRONMENT is not defined then return a failure
		if (!defined('CI_ENVIRONMENT')) return null;

		if(!defined('BASEPATH'))
			define('BASEPATH', 'LEGACY_WORKAROUND'); // little trick to load a CI config file

		// Tries to include the CI config file that contains password for the database encryption
		// If the include fails then return a failure
		if (!include(dirname(__FILE__).'/../application/config/'.CI_ENVIRONMENT.'/db_crypt.php')) return null;

		// Array that will contains all the DB decryption password
		$decryptionPasswordArray = array();
		// Array that will contains all the DB decryption password names
		$decryptionPasswordNamesArray = array();

		// For each password found in the config array
		foreach ($config['encryption_passwords'] as $name => $password)
		{
			// Copy the password name using this template: '{$'<password name>'}'
			$decryptionPasswordArray[] = $password;
			$decryptionPasswordNamesArray[] = '${'.$name.'}';
		}

		// Replace the password names with the password values
		$newSQL = str_replace($decryptionPasswordNamesArray, $decryptionPasswordArray, $sql);

		// In case the replacement is a failure
		if ($newSQL == '' || $newSQL == null) return null;

		return $newSQL; // OK
	}
}
require_once(dirname(__FILE__).'/'.DB_SYSTEM.'.class.php');

?>
