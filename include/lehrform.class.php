<?php 

class lehrform
{

	/**
	 * @var string
	 */
	var $bezeichnung;
	/**
	 * @var string
	 */
	var $kurzbz;		
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var resource
	 */
	var $conn;

	function lehrform($conn)
	{
		$this->conn = $conn;
	}

	/**
	 * Alle Fachbereiche zurückgeben
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function getAll()
	{
		if (is_null($this->conn))
		{
			return false;
		}
		$qry = "select lehrform_kurzbz, bezeichnung from tbl_lehrform ".
			   "order by lehrform_kurzbz";
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
			$lf = new lehrform($this->conn);
			$lf->kurzbz = $row->lehrform_kurzbz;			
			$lf->bezeichnung = $row->bezeichnung;			
			// in array speichern
			$result[] = $lf;
		}
		return $result;
	}
}
?>