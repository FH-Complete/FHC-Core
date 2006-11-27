<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/

/**
 * @class lv_verteilung
 *
 * @author Andreas Österreicher
 *
 * @date 13.9.2005
 *
 * @version $Revision: 1.2 $
 *
 * @brief Bearbeitungsschritte für LV Veranstaltungen
 */
class lv_verteilung
{

	// @var integer interne Lehrveranstaltungs-ID (Zaehler aus DB)
	var $lehrveranstaltung_id;
	// @var string Lehrveranstaltungsnummer
	var $lvnr;
	// @var string Unterrichtsnummer; zum patizipieren verwendet
	var $unr;
	// @var string
	var $einheit_kurzbz;
	// @var string
	var $lektor;
	// @var integer
	var $lehrfach_nr;
	// @var string
	var $lehrfach_kurzbz;
	// @var string
	var $lehrform;
	// @var integer
	var $studiengang_kz;
	// @var string
	var $studiengang_kurzbz;
	// @var integer
	var $fachbereich_id;
	// @var integer
	var $semester;
	// @var string
	var $verband;
	// @var string
	var $gruppe;
	// @var string
	var $raumtyp;
	// @var string
	var $raumtypalternativ;
	// @var integer
	var $semesterstunden;
	// @var integer
	var $stundenblockung;
	// @var integer
	var $wochenrythmus;
	// @var integer
	var $start_kw;
	// @var string
	var $anmerkung;
	// @var string
	var $studiensemester_kurzbz;
	// @var string
	var $fas_id;
	// @var string
	var $lehrevz;
	// @var bool;
	var $lehre;
	// @var string
	var $lehrfach_bz;

	// @var bool
	var $new;
	// @var string
	var $errormsg;
	// @var SQL Connection
	var $connection;
	// @var Array für Rückgabe
	var $retwert = array();
	// @var int
	var $anz;

	/********************************************************************
	 * @brief Konstruktor - Liefert die Connection als Parameter
	 *
	 * @param $conn Connection
	 ********************************************************************/
	function lv_verteilung($conn)
	{
		$this->connection = $conn;
	}

	function load($lv_id)
	{
		$sql_query = "SELECT * FROM tbl_lehrveranstaltung where lehrveranstaltung_id='$lv_id'";

		$result = pg_exec($this->connection,$sql_query);

		if($row=pg_fetch_object($result))
		{
			$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$this->lvnr = $row->lvnr;
			$this->einheit_kurzbz = $row->einheit_kurzbz;
			$this->lektor = $row->lektor;
			$this->lehrfach_nr = $row->lehrfach_nr;
			$this->lehrform = $row->lehrform_kurzbz;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->fachbereich_id = $row->fachbereich_id;
			$this->semester = $row->semester;
			$this->verband = $row->verband;
			$this->gruppe = $row->gruppe;
			$this->raumtyp = $row->raumtyp;
			$this->raumtypalternativ = $row->raumtypalernativ;
			$this->semesterstunden = $row->semesterstunden;
			$this->stundenblockung = $row->stundenblockung;
			$this->wochenrythmus = $row->wochenrythmus;
			$this->start_kw = $row->start_kw;
			$this->anmerkung = $row->anmerkung;
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->fas_id = $row->fas_id;
			$this->unr = $row->unr;
			$this->lehre = $row->lehre;
		}
		else
		{
			$this->errormsg = "Kein Datensatz mit dieser ID vorhanden";
			return false;
		}

		return true;
	}

	/********************************************************************
	 * @brief Prüft ob die Variablen gültige Werte enthalten
	 *
	 * @return true=OK false=Fehler
	 ********************************************************************/
	function checkvars()
	{
		if(!is_numeric($this->semesterstunden))
		{
			$this->errormsg = "Fehler: Semesterstunden muss eine Zahl sein";
			return false;
		}

		if(!is_numeric($this->stundenblockung))
		{
			$this->errormsg = "Fehler: Stundenblockung muss eine Zahl sein";
			return false;
		}

		if(!is_numeric($this->wochenrythmus))
		{
			$this->errormsg = "Fehler: Wochenrythmus muss eine Zahl sein";
			return false;
		}

		if(strlen($this->lvnr)==0)
		{
			$this->errormsg = "Fehler: LVNR muss eingegeben werden";
			return false;
		}

		if(!is_numeric($this->start_kw) AND strlen($this->start_kw)>0)
		{
			$this->errormsg = "Fehler: Start-KW muss eine Zahl sein";
			return false;
		}

		return true;
	}

	/********************************************************************
	 * @brief Speichert die Daten in der Datenbank ab. Wenn new auf true
	 *        gesetzt ist wird INSERT ausgeführt sonst UPDATE
	 * @param keine
	 *
	 * @return true=Ok false=Fehler
	 ********************************************************************/
	function save()
	{
		// Connection überprüfen
		if (is_null($this->connection))
		{
			$this->errormsg = "Fehler: Keine Datenbank connection vorhanden";
			return false;
		}

		if ($this->new) {
			$qry="INSERT INTO tbl_lehrveranstaltung(lvnr,unr,einheit_kurzbz,".
				 "lektor,lehrfach_nr,lehrform_kurzbz,studiengang_kz,fachbereich_id,semester,verband,".
				 "gruppe,raumtyp,raumtypalternativ,semesterstunden,stundenblockung,".
				 "wochenrythmus,start_kw,anmerkung,studiensemester_kurzbz,lehre)".
				 "values(".
				 "'".$this->lvnr."',".
				 (strlen($this->unr)>0?"'".$this->unr."'":'NULL').",".
				 (strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').",".
				 "'".$this->lektor."',".
				 (strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:'0').",".
				 "'".$this->lehrform."',".
				 (strlen($this->studiengang_kz)>0?$this->studiengang_kz:'0').",".
				 (strlen($this->fachbereich_id)>0?$this->fachbereich_id:'NULL').",".
				 (strlen($this->semester)>0?$this->semester:'NULL').",".
				 (strlen($this->verband)>0?"'".$this->verband."'":'NULL').",".
				 (strlen($this->gruppe)>0?"'".$this->gruppe."'":'NULL').",".
				 (strlen($this->raumtyp)>0?"'".$this->raumtyp."'":'0').",".
				 (strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":'NULL').",".
				 (strlen($this->semesterstunden)>0?$this->semesterstunden:'1').",".
				 (strlen($this->stundenblockung)>0?$this->stundenblockung:'1').",".
				 (strlen($this->wochenrythmus)>0?$this->wochenrythmus:'1').",".
				 (strlen($this->start_kw)>0?$this->start_kw:'NULL').",".
				 (strlen($this->anmerkung)>0?"'".$this->anmerkung."'":'NULL').",".
				 (strlen($this->studiensemester_kurzbz)>0?"'".$this->studiensemester_kurzbz."'":'0').",".
				 (($this->lehre=='on')?'true':'false').
				 ")";
		}
		else
		{
			$qry="UPDATE tbl_lehrveranstaltung ".
				 "SET lvnr='".$this->lvnr."',".
				 "unr=".(strlen($this->unr)>0?"'".$this->unr."'":'NULL').",".
				 "einheit_kurzbz=".(strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').",".
				 "lektor='".$this->lektor."',".
				 "lehrfach_nr=".(strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:'0').",".
				 "lehrform_kurzbz='".$this->lehrform."',".
				 "studiengang_kz=".(strlen($this->studiengang_kz)>0?$this->studiengang_kz:'0').",".
				 "fachbereich_id=".(strlen($this->fachbereich_id)>0?$this->fachbereich_id:'NULL').",".
				 "semester=".(strlen($this->semester)>0?$this->semester:'NULL').",".
				 "verband=".(strlen($this->verband)>0?"'".$this->verband."'":'NULL').",".
				 "gruppe=".(strlen($this->gruppe)>0?"'".$this->gruppe."'":'NULL').",".
				 "raumtyp=".(strlen($this->raumtyp)>0?"'".$this->raumtyp."'":'0').",".
				 "raumtypalternativ=".(strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":'NULL').",".
				 "semesterstunden=".(strlen($this->semesterstunden)>0?$this->semesterstunden:'1').",".
				 "stundenblockung=".(strlen($this->stundenblockung)>0?$this->stundenblockung:'1').",".
				 "wochenrythmus=".(strlen($this->wochenrythmus)>0?$this->wochenrythmus:'1').",".
				 "start_kw=".(strlen($this->start_kw)>0?$this->start_kw:'NULL').",".
				 "anmerkung=".(strlen($this->anmerkung)>0?"'".$this->anmerkung."'":'NULL').",".
				 "studiensemester_kurzbz=".(strlen($this->studiensemester_kurzbz)>0?"'".$this->studiensemester_kurzbz."'":'0').",".
				 "lehre=".(($this->lehre=='on')?'true':'false').
				 " WHERE lehrveranstaltung_id='".$this->lehrveranstaltung_id."'";
		}
		//echo $qry.':'.$this->lehre;
		if($this->checkvars())
		{
			if(!($erg=pg_exec($this->connection, $qry)))
			{
				$this->errormsg=pg_errormessage($this->connection);
				return false;
			}
		}
		else
		{
			return false;
		}
		return true;
	}



	/********************************************************************
	 * @brief Liefert die Datensätze aus der Tabelle tbl_lehrveranstaltung
	 *        die zu diesen Kriterien passen
	 * @param $stsem  Studiensemester / -1 wenn keines gewählt
	 *        $sem    Semester        / -1 wenn keines gewählt
	 *        $stg    Studiengang     / -1 wenn keiner gewählt
	 *        $lektor Lektor          / -1 wenn keiner gewählt
	 * @return $result=Array mit den Elementen false=Fehler
	 ********************************************************************/
	function getTab($stsem, $sem, $stg, $lektor, $order)
	{
		$sql_query="SELECT a.semester As sem,a.lehre as lvlehre, * FROM (SELECT * FROM public.tbl_lehrveranstaltung";
		$and = false;

		//Zusammenstöpseln des SQL Strings
		if($lektor!=-1 OR $stsem!=-1 OR $stg!=-1)
		{
		   $sql_query = $sql_query." WHERE";
		}

		//Zusammenstöpseln des SQL Strings
		if($lektor!=-1)
		{
				$sql_query = $sql_query." lektor='$lektor'";
				$and=true;
		}

		if($stsem!=-1)
		{
			if($and)
				$sql_query = $sql_query." AND studiensemester_kurzbz='$stsem'";
			else
			    $sql_query = $sql_query." studiensemester_kurzbz='$stsem'";
			$and=true;
		}

		if($stg!=-1)
		{
			if($sem!=-1)
			{
				if($and)
					$sql_query = $sql_query." AND studiengang_kz='$stg' AND semester='$sem'";
				else
					$sql_query = $sql_query." studiengang_kz='$stg' AND semester='$sem'";
			}
			else
			{
				if($and)
					$sql_query = $sql_query." AND studiengang_kz='$stg'";
				else
					$sql_query = $sql_query." studiengang_kz='$stg'";
			}
		}

		$sql_query = $sql_query.") AS a, tbl_lehrfach b WHERE a.lehrfach_nr=b.lehrfach_nr ORDER BY $order";
		//echo $sql_query;

		$result = pg_exec($this->connection,$sql_query);
		$this->anz = pg_numrows($result);
		while($row=pg_fetch_object($result))
		{
			$lv = new lv_verteilung($connection);
			$lv->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv->lvnr = $row->lvnr;
			$lv->einheit_kurzbz = $row->einheit_kurzbz;
			$lv->lektor = $row->lektor;
			$lv->lehrfach_nr = $row->lehrfach_nr;
			$lv->lehrform = $row->lehrform_kurzbz;
			$lv->studiengang_kz =  $row->studiengang_kz;
			$lv->fachbereich_id = $row->fachbereich_id;
			$lv->semester = $row->sem;
			$lv->verband = $row->verband;
			$lv->gruppe = $row->gruppe;
			$lv->raumtyp = $row->raumtyp;
			$lv->raumtypalternativ = $row->raumtypalternativ;
			$lv->semesterstunden = $row->semesterstunden;
			$lv->stundenblockung = $row->stundenblockung;
			$lv->wochenrythmus = $row->wochenrythmus;
			$lv->start_kw = $row->start_kw;
			$lv->anmerkung = $row->anmerkung;
			$lv->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$lv->fas_id = $row->fas_id;
			$lv->unr = $row->unr;
			$lv->lehrfach_kurzbz = $row->kurzbz;
			$lv->lehrevz = $row->lehrevz;
			$lv->lehre = $row->lvlehre;
			$lv->lehrfach_bz = $row->bezeichnung;
			$this->retwert[] = $lv;
		}

		if($this->anz>0)
			return true;
		else
		    return false;
	}
}