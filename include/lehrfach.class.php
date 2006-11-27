<?php

class lehrfach
{

	/**
	 * @var sint
	 */
	var $lehrfach_nr;
	/**
	 * @var int
	 */
	var $fachbereich_id;
	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var string
	 */
	var $bezeichnung;
	/**
	 * @var string
	 */
	var $lehrevz;
	/**
	 * @var string
	 */
	var $farbe;
	/**
	 * @var string
	 */
	//var $lehrform_kurzbz;
	/**
	 * @var boolean
	 */
	var $aktiv;
	/**
	 * @var int
	 */
	var $studiengang_kz;
	/**
	 * @var real
	 */
	var $ects;

	/**
	 * @var int
	 */
	var $semester;
	/**
	 * @var string
	 */
	var $sprache;

	/**
	 * @var string
	 */
	var $errormsg;
	var $fkterg = array();
	var $conn;

	function lehrfach($conn)
	{

		$this->conn=$conn;
	}

	/**
	 * Ladet einen Datensatz mit der id $id
	 * @param 	$id lehrfach_nr
	 * @return true wenn erfolgreich sonst false
	 */
	function load($id)
	{
		$sql_query = "Select * from tbl_lehrfach where lehrfach_nr=$id";
		if($result=pg_exec($sql_query))
		{
		   if($row=pg_fetch_object($result))
		   {
			   $this->lehrfach_nr = $id;
			   $this->studiengang_kz = $row->studiengang_kz;
			   $this->fachbereich_id = $row->fachbereich_id;
			   $this->kurzbz = $row->kurzbz;
			   $this->bezeichnung = $row->bezeichnung;
			   $this->lehrevz = $row->lehrevz;
			   $this->farbe = $row->farbe;
			   //$this->lehrform = $row->lehrform;
			   $this->aktiv = $row->aktiv;
			   $this->ects = $row->ects;
			   $this->semester = $row->semester;
			   $this->sprache = $row->sprache;
			   return true;
		   }
		}
		return false;
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param 	$stg Studiengangs_kz
	 *			$sem Semester
	 *			$order Sortierkriterium
	 *			$fachb fachbereichs_id
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function getTab($stg='-1',$sem='-1', $order='lehrfach_nr', $fachb='-1',$lehre='')
	{

		$sql_query = "SELECT * FROM tbl_lehrfach";

		if($stg!=-1 || $sem!=-1 || $fachb!=-1)
		   $sql_query .= " WHERE true";

		if($stg!=-1)
		{
		   $sql_query .= " AND studiengang_kz=$stg";
		}

		if($sem!=-1)
		{
			$sql_query .= " AND semester=$sem";
		}

		if($fachb!=-1)
		{
			$sql_query .= " AND fachbereich_id=$fachb";
		}
		
		if($lehre!='')
		{
			$sql_query .= " AND lehre=$lehre";
		}
		
		$sql_query .= " ORDER BY $order";

		if($result=pg_exec($this->conn,$sql_query))
		{
			while($row=pg_fetch_object($result))
			{
				$l = new lehrfach($this->conn);
				$l->lehrfach_nr = $row->lehrfach_nr;
				$l->fachbereich_id = $row->fachbereich_id;
				$l->kurzbz = $row->kurzbz;
				$l->bezeichnung = $row->bezeichnung;
				$l->lehrevz = $row->lehrevz;
				$l->farbe = $row->farbe;
				//$l->lehrform_kurzbz = $row->lehrform_kurzbz;
				$l->aktiv = $row->aktiv;
				$l->ects = $row->ects;
				$l->studiengang_kz = $row->studiengang_kz;
				$l->semester = $row->semester;
				$this->fkterg[]=$l;
			}
		}
		else
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		return true;
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
		$qry = "select * from tbl_lehrfach ".
			   "order by kurzbz";
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
			$lf = new lehrfach($this->conn);
			$lf->lehrfach_nr = $row->lehrfach_nr;
			$lf->fachbereich_id = $row->fachbereich_id;
			$lf->kurzbz = $row->kurzbz;
			$lf->bezeichnung = $row->bezeichnung;
			$lf->lehrelink = $row->lehrelink;
			$lf->farbe = $row->farbe;
			//$lf->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lf->aktiv = $row->aktiv;
			$lf->ects = $row->ects;
			$lf->studiengang_kz = $row->studiengang_kz;
			// in array speichern
			$result[] = $lf;
		}
		return $result;
	}
}
?>