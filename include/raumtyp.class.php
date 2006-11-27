<?php

class raumtyp
{

	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var string
	 */
	var $beschreibung;
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var resource
	 */
	var $conn;



	function raumtyp($conn)
	{
   		$this->conn = $conn;
	}


	/**
	 * Alle Raumtypen zurückgeben
	 * @return array mit Raumtypen oder false=fehler
	 */
	function getAll()
	{
		if (is_null($this->conn))
		{
			return false;
		}
		$qry = "select raumtyp_kurzbz,beschreibung from tbl_raumtyp order by raumtyp_kurzbz";
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
			$rt = new raumtyp($this->conn);
			$rt->kurzbz = $row->raumtyp_kurzbz;
			$rt->beschreibung = $row->beschreibung;
			// in array speichern
			$result[] = $rt;
		}
		return $result;
	}

	function test()
	{
	   	$bla= new test();
	}
}
?>