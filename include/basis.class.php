<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

/**
* Implementation super class
*/
class basis 
{
	/**
	* Error message
	* @var base_errors $msgs
	*/
	public $errormsg;
	
	/**
	* Constructor
	*
	* @access public
	*/
	public function __construct($db_system='pgsql')
	{
		//empty
	}

	public function getErrorMsg()
	{
		return $this->errormsg;
	}

	/**
	 * wenn $var '' ist wird NULL zurueckgegeben
	 * wenn $var !='' ist werden Datenbankkritische
	 * Zeichen mit Backslash versehen und das Ergbnis
	 * unter Hochkomma gesetzt.
	 */
	protected function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Erzeugt aus den Funktionsparameter eine SLQ Abfrage
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
		
		if (!$results=$this->db_query($sql))
		{
			$this->errormsg=$this->db_last_error();
			return false;
		}
		
		if ($art!='select')
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
}
?>