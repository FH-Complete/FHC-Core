<?php
/*
	$Header: /include/zeitwunsch.class.php,v 1.2 2004/10/16 17:05:38 pam Exp $
	$Log: zeitwunsch.class.php,v $
	Revision 1.2 2004/10/16 17:05:38 pam
	Anpassung an neue DB-Struktur.
*/


/****************************************************************************
 * @class 			Zeitwunsch
 * @author	 		Christian Paminger
 * @date	 		2004/8/21
 * @version			$Revision: 1.2 $
 * Update: 			21.10.2004 von Christian Paminger
 * @brief  			Klasse zur Bearbeitung und Abfrage der Tabelle tbl_zeitwunsch
 * Abhaengig:	 	von ?
 *****************************************************************************/

class zeitwunsch
{
	// @var string
	var $uid;
	// @var int
	var $stunde;
	// @var int
	var $tag;
	// @var int
	var $gewicht;

	// @var string
	var $errormsg;
	// @var conn
	var $conn;
	// @var ferien
	var $zeitwunsch=array();

	function zeitwunsch($conn)
	{
		$this->conn=$conn;
	}

	/**
	 * Zeitwunsch einer Person laden
	 * @return boolean Ergebnis steht in Array $zeitwunsch wenn true
	 */
	function loadPerson($uid)
	{
		// Zeitwuensche abfragen
		if(!$result=@pg_query($this->conn, "SELECT * FROM tbl_zeitwunsch WHERE uid='$uid'"))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			while ($row=@pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
			return true;
		}
	}


	/**
	 * Zeitwunsch der Personen in Lehrveranstaltungen laden
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function loadLVA($lva_id)
	{
		// SUB-Select fuer LVAs
		$sql_query_lva='SELECT DISTINCT lektor FROM tbl_lehrveranstaltung WHERE ';
		for ($i=0;$i<count($lva_id);$i++)
			$sql_query_lvaid.=' OR lehrveranstaltung_id='.$lva_id[$i];
		$sql_query_lvaid=substr($sql_query_lvaid,3);
		$sql_query_lva.=$sql_query_lvaid;

		// Schlechteste Zeitwuensche holen
		$sql_query='SELECT tag,stunde,min(gewicht) AS gewicht
				FROM tbl_zeitwunsch WHERE uid IN ('.$sql_query_lva.') GROUP BY tag,stunde';

		// Zeitwuensche abfragen
		if(!$result=@pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			while ($row=@pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
			return true;
		}
	}

}
?>