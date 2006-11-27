<?php 

class fachbereich
{

	/**
	 * @var integer
	 */
	var $id;
	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var string
	 */
	var $bezeichnung;
	/**
	 * @var string;
	 */
	var $farbe;
	/**
	 * @var integer
	 */
	var $studiengang_kz;
	/**
	 * @var boolean  default true
	 */
	var $boolean = true;
	/**
	 * 
	 * @var studiengang_kurzbz
	 */
	var $studiengang_kurzbz;
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var resource
	 */
	var $conn;


	function fachbereich($conn)
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
		$qry = "select fachbereich_id,tbl_fachbereich.kurzbz,tbl_fachbereich.bezeichnung,tbl_fachbereich.farbe,tbl_fachbereich.studiengang_kz,".
			   "tbl_studiengang.kurzbz as studiengang_kurzbz ".
			   "from tbl_fachbereich join tbl_studiengang using(studiengang_kz) ".
			   "order by tbl_Fachbereich.kurzbz";
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
			$fb = new fachbereich($this->conn);
			$fb->id = $row->fachbereich_id;
			$fb->kurzbz = $row->kurzbz;
			$fb->bezeichnung = $row->bezeichnung;
			$fb->farbe = $row->farbe;
			$fb->studiengang_kz = $row->studiengang_kz;
			$fb->studiengang_kurzbz = $row->studiengang_kurzbz;
			// in array speichern
			$result[] = $fb;
		}
		return $result;
	}

}
?>