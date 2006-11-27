<?php 
/*
	$Header: /include/lehrfach.class.php,v 1.2 2004/10/16 17:05:38 pam Exp $
	$Log: lehrstunde.class.php,v $
	Revision 1.2 2004/10/16 17:05:38 pam
	Anpassung an neue DB-Struktur.
*/


/****************************************************************************
 * @class 			Lehrfach
 * @author	 		Christian Paminger
 * @date	 		2004/8/21
 * @version			$Revision: 1.2 $
 * Update: 			21.10.2004 von Christian Paminger
 * @brief  			Klasse zur Bearbeitung und Abfrage der Tabelle tbl_ferien
 * Abhaengig:	 	von ?
 *****************************************************************************/

class ferien
{
	 	 	 	
	// @var string
	var $bezeichnung;
	// @var int
	var $studiengang_kz;
	// @var string
	var $vondatum;
	// @var string
	var $bisdatum;
	// @var timestamp
	var $vontimestamp;
	// @var timestamp
	var $bistimestamp;
	
	// @var string
	var $errormsg;
	// @var conn
	var $conn;
	// @var ferien
	var $ferien=array();
	
	function ferien($conn)
	{
		$this->conn=$conn;
	}


	/**
	 * Alle Fachbereiche zurueckgeben
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function getAll($stg_kz)
	{
		$sql_query="SELECT * FROM tbl_ferien WHERE studiengang_kz=0 OR studiengang_kz=$stg_kz ORDER BY vondatum";
		if (!$result=@pg_query($this->conn, $sql_query))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		//$num_rows=pg_numrows($result);
		//for ($i=0; $i<$num_rows; $i++)
		while ($row=@pg_fetch_object($result)) 
		{
			// Record holen
			// Instanz erzeugen
			$f = new ferien($this->conn);
			$f->bezeichnung=$row->bezeichnung;			
			$f->studiengang_kz = $row->studiengang_kz;						
			$f->vondatum=$row->vondatum;
			$f->bisdatum=$row->bisdatum;
			$f->vontimestamp=mktime(0,0,0,substr($row->vondatum,5,2),substr($row->vondatum,8),substr($row->vondatum,0,4));;
			$f->bistimestamp=mktime(23,59,59,substr($row->bisdatum,5,2),substr($row->bisdatum,8),substr($row->bisdatum,0,4));;
			// in array speichern
			$this->ferien[]=$f;
		}
		return true;
	}
	
	function isferien($timestamp)
	{
		foreach ($this->ferien AS $f)
			if ($timestamp>=$f->vontimestamp && $timestamp<=$f->bistimestamp)
				return true;
		return false;
	}
}
?>