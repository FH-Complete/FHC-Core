<?php
/******************************************************************************
 * Basisklasse fuer Lehrveranstaltung
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @updated 12-Mar-2005
 *****************************************************************************/

class lehrveranstaltung
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
	// @var string Kurzbz vom Lehrfach
	var $lehrfach;
	// @var string Kurzbz der Lehrform
	var $lehrform;
	// @var string lange Beschreibung vom Lehrfach
	var $lehrfach_bez;
	// @var string Farbe vom Lehrfach
	var $lehrfach_farbe;
	// @var integer
	var $studiengang_kz;
	// @var integer
	var $fachbereich_id;
	// @var string beschreibung von foreign key
	var $fachbereich;
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

	// @var boolean;
	var $new=true;
	// @var DB-Handle;
	var $conn;
	// @var string
	var $errormsg;

	function lehrveranstaltung($conn, $id='')
	{
		$this->conn=$conn;
		$this->errormsg='';
		if (strlen($id)>0)
		{
			$this->lehrveranstaltung_id=$id;
			$this->load($id);
		}
	}


	/*************************************************************************
	 * Prueft die geladene Lehrveranstaltung auf Kollisionen im Stundenplan.
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @return boolean true=ok, false=fehler
	 *************************************************************************/
	function check_lva($datum,$stunde,$ort,$stpl_table)
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table=TABLE_BEGIN.$stpl_table;

		/*// Connection holen
		if (is_null($conn=$this->getConnection()))
		{
			return false;
		}*/

		// Datenbank abfragen
		$sql_query="SELECT $stpl_id FROM $stpl_table
					WHERE datum='$datum' AND stunde=$stunde
					AND ((ort_kurzbz='$ort' OR (uid='$this->lektor' AND uid!='_DummyLektor'))
					AND unr!=$this->unr)"; //AND lehrveranstaltung_id!=$this->lehrveranstaltung_id
		//$this->errormsg=$sql_query;
		if (! $erg_stpl=pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_last_error($this->conn);
			//echo $this->errormsg;
			return false;
		}
		$anzahl=pg_numrows($erg_stpl);
		//Check
		if ($anzahl==0)
			return true;
		else
		{
			$row=pg_fetch_row($erg_stpl);
			$this->errormsg="Kollision mit StundenplanID($stpl_table.$stpl_id): $row[0]";
			return false;
		}
	}

	/*************************************************************************
	 * Speichert die geladene Lehrveranstaltung im Stundenplan.
	 * Rueckgabewert 'false' und die Fehlermeldung steht in '$this->errormsg'.
	 * @param string	datum	gewuenschtes Datum YYYY-MM-TT
	 * @param integer	stunde	gewuenschte Stunde
	 * @param string	ort		gewuenschter Ort
	 * @param string	db_stpl_table	Tabllenname des Stundenplans im DBMS
	 * @param string	user	UID des aktuellen Bentzers
	 * @return boolean true=ok, false=fehler
	 *************************************************************************/
	function save_stpl($datum,$stunde,$ort,$stpl_table, $user)
	{
		// Parameter Checken
		// Bezeichnung der Stundenplan-Tabelle und des Keys
		$stpl_id=$stpl_table.TABLE_ID;
		$stpl_table=TABLE_BEGIN.$stpl_table;

		// Datenbank abfragen
		$sql_query="INSERT INTO $stpl_table
			(unr,uid,datum,	stunde,	ort_kurzbz,lehrfach_nr,lehrform_kurzbz,studiengang_kz,semester,verband,
			gruppe,	einheit_kurzbz,	titel, anmerkung, updatevon, lehrveranstaltung_id)
			VALUES ($this->unr,'$this->lektor','$datum',$stunde,
			'$ort',$this->lehrfach_nr, '$this->lehrform', $this->studiengang_kz,$this->semester,
			'$this->verband','$this->gruppe'";
		if ($this->einheit_kurzbz==null)
			$sql_query.=',NULL';
		else
			$sql_query.=",'$this->einheit_kurzbz'";
		$sql_query.=",'$this->titel','$this->anmerkung','$user',$this->lehrveranstaltung_id)";
		//$this->errormsg=$sql_query.'<br>';
		//return false;
		if (! $erg_stpl=pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_last_error($this->conn);
			//echo $this->errormsg;
			return false;
		}
		return true;
	}

	/**
	 * Ladet die Attribute der LVA aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function load($id='')
	{
		// optional: id setzen
		if ($id!='')
			$this->lehrveranstaltung_id=$id;
		// id vorhanden?
		if (strlen($this->lehrveranstaltung_id)==0)
		{
			$this->errormsg='<i>lehrveranstaltung_id</i> nicht gesetzt.';
			return false;
		}

		// LVA-Daten holen
		$sql_query='SELECT * FROM tbl_lehrveranstaltung WHERE lehrveranstaltung_id='.$this->lehrveranstaltung_id;
	    //$this->errormsg.=$sql_query;
	    //return false;
		if(!($erg=pg_exec($this->conn, $sql_query)))
		{
			$this->errormsg.=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		if($num_rows!=1)
		{
			$this->errormsg.="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

		$this->lvnr=$row->lvnr;
		$this->unr=$row->unr;
		$this->einheit_kurzbz=$row->einheit_kurzbz;
		$this->lektor=$row->lektor;
		$this->lehrfach_nr=$row->lehrfach_nr;
		$this->lehrform=$row->lehrform_kurzbz;
		$this->studiengang_kz=$row->studiengang_kz;
		$this->fachbereich_id=$row->fachbereich_id;
		$this->semester=$row->semester;
		$this->verband=$row->verband;
		$this->gruppe=$row->gruppe;
		$this->raumtyp=$row->raumtyp;
		$this->raumtypalternativ=$row->raumtypalternativ;
		$this->semesterstunden=$row->semesterstunden;
		$this->stundenblockung=$row->stundenblockung;
		$this->wochenrythmus=$row->wochenrythmus;
		$this->start_kw=$row->start_kw;
		$this->anmerkung=$row->anmerkung;
		$this->studiensemester_kurzbz=$row->studiensemester_kurzbz;
		//$this->fas_id=$row->fas_id;
		$this->new=false;
		return true;
	}

	/**
	 * @return boolean true=ok, false=fehler
	 */
	function save()
	{
		global $auth;

		// Daten zur Person speichern

		if (!person::save()) {
			$this->errormsg.="Daten zur LVA konnten nicht gespeichert werden.";
			return false;
		}
		if ($this->new) {
			$sql_query="INSERT INTO tbl_lehrveranstaltung(lvnr,unr,einheit_kurzbz,".
				 "lektor,lehrfach_nr,lehrform_kurzbz,studiengang_kz,fachbereich_id,semester,verband,".
				 "gruppe,raumtyp,raumtypalternativ,semesterstunden,stundenblockung,".
				 "wochenrythmus,start_kw,anmerkung)".
				 "values(".
				 "'".$this->lvnr."',".
				 "'".$this->unr."',".
				 "'".$this->einheit_kurzbz."',".
				 "'".$this->lektor."',".
				 (strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:NULL).",".
				 "'".$this->lehrform."',".
				 (strlen($this->studiengang_kz)>0?$this->studiengang_kz:NULL).",".
				 (strlen($this->fachbereich_id)>0?$this->fachbereich_id:NULL).",".
				 (strlen($this->semester)>0?$this->semester:NULL).",".
				 "'".$this->verband."',".
				 "'".$this->gruppe."',".
				 (strlen($this->raumtyp)>0?"'".$this->raumtyp."'":NULL).",".
				 (strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":NULL).",".
				 (strlen($this->semesterstunden)>0?$this->semesterstunden:NULL).",".
				 (strlen($this->stundenblockung)>0?$this->stundenblockung:NULL).",".
				 (strlen($this->wochenrythmus)>0?$this->wochenrythmus:NULL).",".
				 (strlen($this->start_kw)>0?$this->start_kw:NULL).",".
				 (strlen($this->anmerkung)>0?"'".$this->anmerkung."'":NULL).",".
				 ")";
		} else
		{
			$sql_query="UPDATE tbl_lehrveranstaltung ".
				 "SET lvnr='".$this->lvnr."',".
				 "unr='".$this->unr."',".
				 "einheit_kurzbz='".$this->einheit_kurzbz."',".
				 "lektor='".$this->lektor."',".
				 "lehrfach_nr=".(strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:NULL).",".
				 "lehrform_kurzbz=".(strlen($this->lehrform)>0?$this->lehrform:NULL).",".
				 "studiengang_kz=".(strlen($this->studiengang_kz)>0?$this->studiengang_kz:NULL).",".
				 "fachbereich_id=".(strlen($this->fachbereich_id)>0?$this->fachbereich_id:NULL).",".
				 "semester=".(strlen($this->semester)>0?$this->semester:NULL).",".
				 "verband='".$this->verband."',".
				 "gruppe='".$this->gruppe."',".
				 "raumtyp=".(strlen($this->raumtyp)>0?"'".$this->raumtyp."'":NULL).",".
				 "raumtypalternativ=".(strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":NULL).",".
				 "semesterstunden=".(strlen($this->semesterstunden)>0?$this->semesterstunden:NULL).",".
				 "stundenblockung=".(strlen($this->stundenblockung)>0?$this->stundenblockung:NULL).",".
				 "wochenrythmus=".(strlen($this->wochenrythmus)>0?$this->wochenrythmus:NULL).",".
				 "start_kw=".(strlen($this->start_kw)>0?$this->start_kw:NULL).",".
				 "anmerkung=".(strlen($this->anmerkung)>0?"'".$this->anmerkung."'":NULL).
				 " WHERE lehrveranstaltung_id='".$this->lehrveranstaltung_id."'";
		}
		//echo "<br>".$sql_query;
		if(!($erg=pg_exec($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;
	}


	/**
	 * Rueckgabewert ist ein Array mit den Ergebnissen. Bei Fehler false und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	 * @param string $einheit_kurzbz    Einheit
	 * @param string grp    Gruppe
	 * @param string ver    Verband
	 * @param integer sem    Semester
	 * @param integer stg_kz    Kennzahl des Studiengangs
	 * @return variabel Array mit LVA; <b>false</b> bei Fehler
	 */
	function getLehrveranstaltungSTPL($db_stpl_table,$studiensemester, $type, $stg_kz, $sem, $lektor, $ver=null, $grp=null, $einheit=null)
	{
		$lva_stpl_view=VIEW_BEGIN.'lva_'.$db_stpl_table;

		if (strlen($studiensemester)<=0)
		{
			$this->errormsg='Ausbildungssemester ist nicht gesetzt!';
			return false;
		}
		else $where=" studiensemester_kurzbz='$studiensemester'";

		if ($type=='lektor')
			$where.=" AND lektor_uid='$lektor'";
		elseif ($type=='einheit')
			$where.=" AND einheit='$einheit'";
		elseif ($type=='verband')
		{
			$where.=" AND studiengang_kz='$stg_kz'";
			if ($sem>0)
				$where.=" AND semester=$sem";
			if (strlen($ver)>0 && $ver!=' ')
				$where.=" AND verband='$ver'";
			if (strlen($grp)>0 && $grp!=' ')
				$where.=" AND gruppe='$grp' ";
		}
		$sql_query='SELECT *, semesterstunden-verplant::smallint AS offenestunden
			FROM '.$lva_stpl_view.' JOIN tbl_lehrform ON '.$lva_stpl_view.'.lehrform=tbl_lehrform.lehrform_kurzbz
			WHERE '.$where.' AND verplanen ORDER BY offenestunden DESC, lehrfach, lehrform, semester, verband, gruppe, einheit;';
	    //$this->errormsg=$sql_query;
	    //return false;
		if(!($erg=@pg_exec($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$l=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			//$l[$row->unr]=new lehrveranstaltung();
			$l[$row->unr]->lehrveranstaltung_id[]=$row->lehrveranstaltung_id;
			$l[$row->unr]->lvnr[]=$row->lvnr;
			$l[$row->unr]->unr=$row->unr;
			$l[$row->unr]->fachbereich_id=$row->fachbereich_id;
			$l[$row->unr]->fachbereich=$row->fachbereich_kurzbz;
			$l[$row->unr]->lehrfach_nr=$row->lehrfach_nr;
			$l[$row->unr]->lehrfach[]=$row->lehrfach;
			$l[$row->unr]->lehrfach_bez[]=$row->lehrfach_bez;
			$l[$row->unr]->lehrfach_farbe[]=$row->lehrfach_farbe;
			$l[$row->unr]->lehrform[]=$row->lehrform;
			$l[$row->unr]->lektor_uid[]=$row->lektor_uid;
			$l[$row->unr]->lektor[]=trim($row->lektor);
			$l[$row->unr]->stg_kz[]=$row->studiengang_kz;
			$l[$row->unr]->stg[]=$row->studiengang;
			$l[$row->unr]->einheit[]=$row->einheit;
			$l[$row->unr]->semester[]=$row->semester;
			$l[$row->unr]->verband[]=$row->verband;
			$l[$row->unr]->gruppe[]=$row->gruppe;
			$l[$row->unr]->raumtyp=$row->raumtyp;
			$l[$row->unr]->raumtypalternativ=$row->raumtypalternativ;
			$l[$row->unr]->stundenblockung[]=$row->stundenblockung;
			$l[$row->unr]->wochenrythmus[]=$row->wochenrythmus;
			$l[$row->unr]->semesterstunden[]=$row->semesterstunden;
			$l[$row->unr]->start_kw[]=$row->start_kw;
			$l[$row->unr]->anmerkung[]=$row->anmerkung;
			$l[$row->unr]->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$l[$row->unr]->verplant[]=$row->verplant;
			$l[$row->unr]->offenestunden[]=$row->offenestunden;
			if (isset($l[$row->unr]->verplant_gesamt))
				$l[$row->unr]->verplant_gesamt+=$row->verplant;
			else
				$l[$row->unr]->verplant_gesamt=$row->verplant;
			$lvb=$row->studiengang.'-'.$row->semester;
			if ($row->verband!='' && $row->verband!=' ' && $row->verband!='0' && $row->verband!=null)
				$lvb.=$row->verband;
			if ($row->gruppe!='' && $row->gruppe!=' ' && $row->gruppe!='0' && $row->gruppe!=null)
				$lvb.=$row->gruppe;
			if ($row->einheit!='' && $row->einheit!=null)
				$l[$row->unr]->lehrverband[]=$row->einheit;
			else
				$l[$row->unr]->lehrverband[]=$lvb;
		}
		return $l;
	}
}