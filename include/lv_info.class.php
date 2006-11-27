<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/

/**
 * @class lv_info
 *
 * @author Andreas Oesterreicher
 *
 * @date 29.9.2005
 *
 * @version $Revision: 1.2 $
 *
 * @brief Bearbeitungsschritte fuer die Tabelle LVINFO
 */


class lv_info
{
	//integer
	var $lvinfo_id;
	//integer
	var $studiensemester_kurzbz;
	//String
	var $lehrziele;
	//String
	var $lehrinhalte;
	//String
	var $voraussetzungen;
	//String
	var $basiert_auf;
	//String
	var $kooperiert_mit;
	//String
	var $liefert_fuer;
	//String
	var $unterlagen;
	//String
	var $pruefungsordnung;
	//String
	var $anmerkungen;
	//String
	var $niveau;
	//String
	var $lehrformen;
	//boolean
	var $genehmigt='false';
	//boolean
	var $aktiv='true';
	//timestamp
	var $updateamum;
	//String
	var $updatevon;
	//integer
	var $lehrfach_nr;
	//String
	var $sprache;
	//String
	var $lehrende;
	//String
	var $lehrfach;
	//smalint
	var $semstunden;

	//boolean
	var $neu;
	//string
	var $lastqry;

	//int
	var $anz;

	//Connection
	var $conn;
	//Array
	var $result = array();
	var $errormsg;

	/********************************************************************
	 * @brief Konstruktor
	 *
	 * @param $conn Connection zur DB
	 ********************************************************************/
	function lv_info($conn)
	{
		$this->conn = $conn;
	}

	/********************************************************************
	 * @brief Speichert die Daten in der Datenbank ab. Wenn new auf true
	 *        gesetzt ist wird INSERT ausgefuehrt sonst UPDATE
	 * @param keine
	 *
	 * @return true=Ok false=Fehler
	 ********************************************************************/
	function save()
	{

		if (is_null($this->conn))
		{
			$this->errormsg = "Fehler: Keine Datenbank connection vorhanden";
			return false;
		}

		//Variablen ueberpruefen
		//$this->checkvars();

		if($this->neu)
		{
			$sql_query = "INSERT INTO tbl_lvinfo (studiensemester_kurzbz,lehrziele,lehrinhalte,voraussetzungen,basiert_auf,".
			             "kooperiert_mit, liefert_fuer, unterlagen, pruefungsordnung, anmerkungen, niveau,".
			             "lehrformen, genehmigt, aktiv,updatevon,lehrfach_nr, sprache, lehrende, lehrfach, semstunden)".
			             " VALUES('$this->studiensemester_kurzbz',".
			             (strlen($this->lehrziele)>0?"'".$this->lehrziele."'":'NULL').",".
			             (strlen($this->lehrinhalte)>0?"'".$this->lehrinhalte."'":'NULL').",".
			             (strlen($this->voraussetzungen)>0?"'".$this->voraussetzungen."'":'NULL').",".
			             (strlen($this->basiert_auf)>0?"'".$this->basiert_auf."'":'NULL').",".
			             (strlen($this->kooperiert_mit)>0?"'".$this->kooperiert_mit."'":'NULL').",".
			             (strlen($this->liefert_fuer)>0?"'".$this->liefert_fuer."'":'NULL').",".
			             (strlen($this->unterlagen)>0?"'".$this->unterlagen."'":'NULL').",".
			             (strlen($this->pruefungsordnung)>0?"'".$this->pruefungsordnung."'":'NULL').",".
			             (strlen($this->anmerkungen)>0?"'".$this->anmerkungen."'":'NULL').",".
			             (strlen($this->niveau)>0?"'".$this->niveau."'":'NULL').",".
			             (strlen($this->lehrformen)>0?"'".$this->lehrformen."'":'NULL').",".
			             ($this->genehmigt=="'true'"?'true':'false').",".($this->aktiv=='true'?'true':'false').",'$this->updatevon',$this->lehrfach_nr,'$this->sprache',".
			             (strlen($this->lehrende)>0?"'".$this->lehrende."'":'NULL').",".
			             (strlen($this->lehrfach)>0?"'".$this->lehrfach."'":'NULL').",".
			             ($this->semstunden!=''?"'".$this->semstunden."'":'NULL') .");";

		}
		else
		{
			$sql_query = "UPDATE tbl_lvinfo SET".
			             " studiensemester_kurzbz='$this->studiensemester_kurzbz'".
			             ", lehrziele=".(strlen($this->lehrziele)>0?"'".$this->lehrziele."'":'NULL') .
			             ", lehrinhalte=".(strlen($this->lehrinhalte)>0?"'".$this->lehrinhalte."'":'NULL') .
			             ", voraussetzungen=".(strlen($this->voraussetzungen)>0?"'".$this->voraussetzungen."'":'NULL') .
			             ", basiert_auf=".(strlen($this->basiert_auf)>0?"'".$this->basiert_auf."'":'NULL') .
			             ", kooperiert_mit=".(strlen($this->kooperiert_mit)>0?"'".$this->kooperiert_mit."'":'NULL') .
			             ", liefert_fuer=".(strlen($this->liefert_fuer)>0?"'".$this->liefert_fuer."'":'NULL') .
			             ", unterlagen=".(strlen($this->unterlagen)>0?"'".$this->unterlagen."'":'NULL') .
			             ", pruefungsordnung=".(strlen($this->pruefungsordnung)>0?"'".$this->pruefungsordnung."'":'NULL') .
			             ", anmerkungen=".(strlen($this->anmerkungen)>0?"'".$this->anmerkungen."'":'NULL') .
			             ", niveau=".(strlen($this->niveau)>0?"'".$this->niveau."'":'NULL') .
			             ", lehrformen=".(strlen($this->lehrformen)>0?"'".$this->lehrformen."'":'NULL') .
			             ", lehrende=".(strlen($this->lehrende)>0?"'".$this->lehrende."'":'NULL') .
			             ($this->genehmigt==''?'':", genehmigt=$this->genehmigt").
			             ($this->aktiv==''?'':", aktiv=$this->aktiv").
			             ", updateamum=now()".
			             ", updatevon='".$_SERVER["REMOTE_USER"]."'".
			             ", lehrfach_nr=$this->lehrfach_nr".
			             ", lehrfach='$this->lehrfach'".
			             ($this->semstunden!=''?", semstunden=$this->semstunden ":'').
			             ", sprache='$this->sprache' WHERE lvinfo_id=$this->lvinfo_id";

		}

		$this->lastqry=$sql_query."--$this->genehmigt--";
		//echo $sql_query;
		if(pg_exec($this->conn,$sql_query))
		{
			return true;
		}
		else
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
	}

	/********************************************************************
	 * @brief Fuehrt einen Selectbefehl aus und Schreibt das Ergebnis in
	 *        $result
	 *
	 * @param $sql_query - Select befehl
	 *
	 * @return true=Ok, Erg in $Result false=Fehler, Meldung in $errormsg
	 *         Anzahl der Datensaetze steht in $anz
	 ********************************************************************/
	function getData($sql_query)
	{
		if (is_null($this->conn))
		{
			$this->errormsg = "Fehler: Keine Datenbank connection vorhanden";
			return false;
		}

		if($res=pg_exec($this->conn,$sql_query))
		{
			while($row=pg_fetch_object($res))
			{
			    $elem = new lv_info($this->conn);
			    $elem->lvinfo_id = $row->lvinfo_id;
			    $elem->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			    $elem->lehrziele = $row->lehrziele;
			    $elem->lehrinhalte = $row->lehrinhalte;
			    $elem->voraussetzungen = $row->voraussetzungen;
			    $elem->basiert_auf = $row->basiert_auf;
			    $elem->kooperiert_mit = $row->kooperiert_mit;
			    $elem->liefert_fuer = $row->liefert_fuer;
			    $elem->unterlagen = $row->unterlagen;
			    $elem->pruefungsordnung = $row->pruefungsordnung;
			    $elem->anmerkungen = $row->anmerkungen;
			    $elem->niveau = $row->niveau;
			    $elem->lehrformen = $row->lehrformen;
			    $elem->genehmigt = $row->genehmigt;
			    $elem->aktiv = $row->aktiv;
			    $elem->updateamum = $row->updateamum;
			    $elem->updatevon = $row->updatevon;
			    $elem->lehrfach_nr = $row->lehrfach_nr;
			    $elem->lehrfach = $row->lehrfach;
			    $elem->sprache = $row->sprache;
			    $elem->lehrende = $row->lehrende;
			    $elem->semstunden = $row->semstunden;
			    $this->result[] = $elem;
			}
			$this->anz = pg_num_rows($res);
		}
		else
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
		return true;
	}

	/********************************************************************
	 * @brief Liefert alle Datensaetze der Tabelle tbl_lvinfo
	 *
	 * @param keine
	 *
	 * @return true=Ok, Erg in $Result false=Fehler, Meldung in $errormsg
	 ********************************************************************/
	function getAll()
	{
		$sql_query = "Select * from tbl_lvinfo";
	    return $this->getData($sql_query);
	}

	/********************************************************************
	 * @brief Liefert alle Datensaetze der Tabelle tbl_lvinfo mit der
	 *        lvinfo_id $id
	 * @param $id lvinfo_id
	 *
	 * @return true=Ok, Erg in $Result false=Fehler, Meldung in $errormsg
	 ********************************************************************/
	function getByID($id)
	{
		$sql_query = "SELECT * from tbl_lvinfo WHERE lvinfo_id=$id";
		return $this->getData($sql_query);
	}

	/********************************************************************
	 * @brief Liefert alle Datensaetze mit den Kriterien die ueberg. wurden
	 *
	 * @param $lf Lehrfach
	 *		  $stg Studiengang
	 *		  $sem Semester
	 *        $sprache Sprache
	 *        $studiensemester_kurzbz Studiensemester
	 *
	 * @return true=Ok, Erg in $Result false=Fehler, Meldung in $errormsg
	 ********************************************************************/
	function getByElem($lf,$stg,$sem,$sprache='',$studiensemester_kurzbz='',$order='version DESC')
	{
		if($sprache=='')
		{
			if($studiensemester_kurzbz=='')
			{
			   $sql_query = "SELECT * from tbl_lvinfo, tbl_lehrfach WHERE tbl_lvinfo.lehrfach_nr=tbl_lehrfach.lehrfach_nr ".
			                "AND tbl_lvinfo.lehrfach_nr=$lf AND studiengang_kz = $stg AND semester = $sem AND tbl_lvinfo.aktiv=true ORDER BY $order";
			}
			else
			{
			   $sql_query = "SELECT * from tbl_lvinfo, tbl_lehrfach WHERE tbl_lvinfo.lehrfach_nr=tbl_lehrfach.lehrfach_nr ".
			                "AND tbl_lvinfo.lehrfach_nr=$lf AND studiengang_kz = $stg AND semester = $sem AND studiensemester_kurzbz='$studiensemester_kurzbz' AND tbl_lvinfo.aktiv=true ORDER BY $order";
			}
		}
		else
		{
			if($studiensemester_kurzbz=='')
			{
			   $sql_query = "SELECT * from tbl_lvinfo, tbl_lehrfach WHERE tbl_lvinfo.lehrfach_nr=tbl_lehrfach.lehrfach_nr ".
			                "AND tbl_lvinfo.sprache='$sprache' AND tbl_lvinfo.lehrfach_nr=$lf AND studiengang_kz = $stg AND semester = $sem AND tbl_lvinfo.aktiv=true ORDER BY $order";
			}
			else
			{
			   $sql_query = "SELECT * from tbl_lvinfo, tbl_lehrfach WHERE tbl_lvinfo.lehrfach_nr=tbl_lehrfach.lehrfach_nr ".
			                "AND tbl_lvinfo.sprache='$sprache' AND tbl_lvinfo.lehrfach_nr=$lf AND studiengang_kz = $stg AND semester = $sem AND studiensemester_kurzbz='$studiensemester_kurzbz' AND tbl_lvinfo.aktiv=true ORDER BY $order";
			}
		}
	    return $this->getData($sql_query);
	}

	/********************************************************************
	 * @brief Sieht in der DB nach ob ein Eintrag mit den Kriterien
	 *        vorhanden ist.
	 * @param $studiensemester_kurzbz Studiensemester
	 *        $lf Lehrfach
	 *
	 * @return true=Vorhanden, id des DS in $lvinfo_id false=Nicht vorhanden
	 ********************************************************************/
	function vorhanden($lf, $studiensemester_kurzbz, $sprache)
	{
		$sql_query = "SELECT * from tbl_lvinfo WHERE ".
		             "lehrfach_nr=$lf AND studiensemester_kurzbz='$studiensemester_kurzbz' AND sprache='$sprache' AND aktiv=true";
	    $res=pg_exec($this->conn, $sql_query);
	    if(pg_numrows($res)>0)
	    {
	    	$row=pg_fetch_object($res);
	    	$this->lvinfo_id=$row->lvinfo_id;
	    	return true;
	    }
	    else
	    {
	    	return false;
	    }
	}
}
?>