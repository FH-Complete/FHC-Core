<?php 

class studiensemester 
{

	/**
	 * @var string 
	 */
	var $kurzbz;
	/**
	 * @var string 
	 */
	var $start;
	/**
	 * @var string
	 */
	var $ende;

	/**
	 * @var string
	 */
	var $errormsg;

	/**
	 * @var conn
	 */
	var $conn;
	
	function studiensemester($conn)
	{
		$this->conn=$conn;
	}

	/**
	* Verbindung zur Datenbank herstellen
	* @return PostgreSQL-Connection oder NULL
	
	function getConnection()
	{
		if (!$conn = @ pg_pconnect(CONN_STRING))
		{
			$this->errormsg = "Es konnte keine Verbindung zum Server "."aufgebaut werden.";
			return null;
		}
		return $conn;
	}*/

	function load()
	{

	}

	/**
	 * Alle Studiensemester zurückgeben
	 * @return array mit Studiensemester oder false=fehler
	 */
	function getAll($order='studiensemester_kurzbz')
	{
		if (is_null($this->conn))
		{
			return false;
		}
		$qry = "select * from tbl_studiensemester ".
			   "order by $order";
		//echo $qry;
		if (!($erg = pg_exec($this->conn, $qry)))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		$result = array();
		$num_rows = pg_numrows($erg);
		for ($i = 0; $i < $num_rows; $i ++)
		{
			// Record holen
			$row = pg_fetch_object($erg, $i);
			// Instanz erzeugen
			$lf = new studiensemester($this->conn);
			$lf->kurzbz = $row->studiensemester_kurzbz;			
			$lf->start = $row->start;
			$lf->ende = $row->ende;
			// in array speichern
			$result[] = $lf;
		}
		return $result;
	}

	/**
	 * Liefert das Aktuelle Studiensemester
	 * @return aktuelles Studiensemester oder false wenn es keines gibt
	 */
	function getakt()
	{
		$qry = "Select studiensemester_kurzbz from tbl_studiensemester where start <= now() and ende >= now()";
		if(!$res=pg_exec($this->conn,$qry))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		
		if(pg_num_rows($res)>0)
		{
		   $erg = pg_fetch_object($res);
		   return $erg->studiensemester_kurzbz;
		}
		else 
		{
			$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
			return false;
		}
	}
	
	/**
	 * Liefert das Aktuelle Studiensemester oder das darauffolgende
	 * @return Studiensemester oder false wenn es keines gibt
	 */
	function getaktorNext()
	{
		if($stsem=$this->getakt())
		   return $stsem;
		else 
		{
			$qry = "Select studiensemester_kurzbz from tbl_studiensemester where ende >= now() ORDER BY ende";
			if(!$res=pg_exec($this->conn,$qry))
		    {
				$this->errormsg = pg_errormessage($this->conn);
				return false;
		    }
		
			if(pg_num_rows($res)>0)
			{
			   $erg = pg_fetch_object($res);
			   return $erg->studiensemester_kurzbz;
			}
			else 
			{
				$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
				return false;
			}
		}
	}
	
	function save()
	{

	}

	function delete()
	{

	}

}
?>