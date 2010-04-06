<?php
require_once(dirname(__FILE__).'/basis.class.php');

abstract class db extends basis
{
	protected static $db_conn=null;
	protected $db_result=null;
	protected $debug=false;

	function __construct()
	{
		if (is_null(db::$db_conn))
			$this->db_connect();
	}

	abstract function db_connect();
	abstract function db_query($sql);
	abstract function db_fetch_object($result=null, $i=null);
	abstract function db_fetch_array($result=null);
	abstract function db_fetch_row($result=null, $i=null);
	abstract function db_result($result = null, $i,$item);
	abstract function db_num_rows($result=null);
	abstract function db_num_fields($result=null);
	abstract function db_field_name($result=null, $i);
	abstract function db_affected_rows($result=null);
	abstract function db_last_error();
	abstract function db_free_result($result=null);	
	abstract function db_version();

	
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
	
}
require_once(dirname(__FILE__).'/'.DB_SYSTEM.'.class.php');

?>