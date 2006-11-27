<?php
/**
 * Basisklasse fuer Studenten
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @updated 03-Feb-2005
 */
class lfvt
{
	/**
	 * interne Lehrveranstaltungs-ID (Z?hler aus DB)
	 * @var integer
	 */
	var $lehrveranstaltung_id;
	/**
	 * Lehrveranstaltungsnummer
	 * @var string
	 */
	var $lvnr;
	/**
	 * Unterrichtsnummer; zum patizipieren verwendet
	 * @var string
	 */
	var $unr;
	/**
	 * @var string
	 */
	var $einheit_kurzbz;
	/**
	 * @var string ID aus Datenbank
	 */
	var $lektor;
	/**
	 * @var string Lektor-Nachname+Vorname+Titel zum leichteren Anzeigen
	 */
	var $lektorPrettyPrint;
	/**
	 * @var integer
	 */
	var $lehrfach_nr;
	/**
	 * @var string
	 */
	var $lehrform;
	/**
	 * @var integer
	 */
	var $studiengang_kz;
	/**
	 * @var integer
	 */
	var $fachbereich_id;
	/**
	 * @var integer
	 */
	var $semester;
	/**
	 * @var string
	 */
	var $verband;
	/**
	 * @var string
	 */
	var $gruppe;
	/**
	 * @var string
	 */
	var $raumtyp;
	/**
	 * @var string
	 */
	var $raumtypalternativ;
	/**
	 * @var integer
	 */
	var $semesterstunden;
	/**
	 * @var integer
	 */
	var $stundenblockung;
	/**
	 * @var integer
	 */
	var $wochenrythmus;
	/**
	 * @var integer
	 */
	var $start_kw;
	/**
	 * @var string
	 */
	var $anmerkung;
	/**
	 * @var string
	 */
	var $studiensemester_kurzbz;
	/**
	 * @var string
	 */
	var $fas_id;
	/**
	 * @var boolean;
	 */
	var $new=true;
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var string beschreibung von foreign key
	 */
	var $fachbereich;
	/**
	 * @var string beschreibung von foreign key
	 */
	var $lehrfach;
	
	/**
	 * @var string beschreibung von foreign key
	 */
	var $conn;

	function lfvt($conn,$id='')
	{
		$this->conn=$conn;
		if (strlen($id)>0) {
			$this->$lehrveranstaltung_id=$id;
			$this->load();
		}
	}

	/**
	 * Ladet die Attribute der LVA aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function load($id='')
	{
		// optional: id setzen
		if (strlen($id)>0)
			$this->$lehrveranstaltung_id=$id;
		// id vorhanden?
		if (strlen($this->$this->$lehrveranstaltung_id)==0) {
			$this->errormsg='<i>lehrveranstaltung_id</i> nicht gesetzt.';
			return false;
		}
		// LVA-Daten holen
		$sql_query="SELECT lva.* ".
		           "FROM tbl_lehrveranstaltung as lva ".
	               "WHERE lehrveranstaltung_id='".$this->lehrveranstaltung_id."'";
		if(!($erg=pg_exec($this->conn, $sql_query)))
			die(pg_errormessage($this->conn));
		$num_rows=pg_numrows($erg);
		if($num_rows!=1) {
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
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
		if (!isset($this->unr)) {
			$this->errormsg='unr fehlt';
			return false;
		}
		if (!isset($this->lvnr)) {
			$this->errormsg='lvnr fehlt';
			return false;
		}
		if (!isset($this->lektor)) {
			$this->errormsg='lektor fehlt';
			return false;
		}
		if (!isset($this->lehrfach_nr)) {
			$this->errormsg='lehrfach_nr fehlt';
			return false;
		}
		if (!isset($this->lehrform))
		{
			$this->errormsg='lehrform fehlt';
			return false;
		}
		if (!isset($this->studiengang_kz)) {
			$this->errormsg='studiengang_kz fehlt';
			return false;
		}
		if (!isset($this->fachbereich_id)) {
			$this->errormsg='fachbereich_id fehlt';
			return false;
		}
		if (!isset($this->raumtyp)) {
			$this->errormsg='raumtyp fehlt';
			return false;
		}
		if (!isset($this->semesterstunden)) {
			$this->errormsg='semesterstunden fehlt';
			return false;
		}
		if (!isset($this->stundenblockung)) {
			$this->errormsg='stundenblockung fehlt';
			return false;
		}
		if (!isset($this->wochenrythmus)) {
			$this->errormsg='wochenrythmus fehlt';
			return false;
		}
		if (!isset($this->studiensemester_kurzbz)) {
			$this->errormsg='studiensemester_kurzbz fehlt';
			return false;
		}

		if ($this->new) {
			$qry="INSERT INTO tbl_lehrveranstaltung(lvnr,unr,einheit_kurzbz,".
				 "lektor,lehrfach_nr,lehrform_kurzbz,studiengang_kz,fachbereich_id,semester,verband,".
				 "gruppe,raumtyp,raumtypalternativ,semesterstunden,stundenblockung,".
				 "wochenrythmus,start_kw,anmerkung,studiensemester_kurzbz)\n".
				 "values(".
				 "'".$this->lvnr."',".
				 "'".$this->unr."',".
				 (strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').",".
				 "'".$this->lektor."',".
				 (strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:'NULL').",".
				 "'".$this->lehrform."',".
				 (strlen($this->studiengang_kz)>0?$this->studiengang_kz:'NULL').",".
				 (strlen($this->fachbereich_id)>0?$this->fachbereich_id:'NULL').",".
				 (strlen($this->semester)>0?$this->semester:'NULL').",".
				 "'".$this->verband."',".
				 "'".$this->gruppe."',".
				 (strlen($this->raumtyp)>0?"'".$this->raumtyp."'":'NULL').",".
				 (strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":'NULL').",".
				 (strlen($this->semesterstunden)>0?$this->semesterstunden:'NULL').",".
				 (strlen($this->stundenblockung)>0?$this->stundenblockung:'NULL').",".
				 (strlen($this->wochenrythmus)>0?$this->wochenrythmus:'NULL').",".
				 (strlen($this->start_kw)>0?$this->start_kw:'NULL').",".
				 (strlen($this->anmerkung)>0?"'".$this->anmerkung."'":'NULL').','.
				 (strlen($this->studiensemester_kurzbz)>0?"'".$this->studiensemester_kurzbz."'":'NULL').
				 ")";
		} else
		{
			$qry="UPDATE tbl_lehrveranstaltung ".
				 "SET lvnr='".$this->lvnr."',".
				 "unr='".$this->unr."',".
				 "einheit_kurzbz=".(strlen($this->einheit_kurzbz)>0?"'".$this->einheit_kurzbz."'":'NULL').",".
				 "lektor='".$this->lektor."',\n".
				 "lehrfach_nr=".(strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:'NULL').",".
				 "lehrform_kurzbz='".$this->lehrform."',\n".
				 "studiengang_kz=".(strlen($this->studiengang_kz)>0?$this->studiengang_kz:'NULL').",".
				 "fachbereich_id=".(strlen($this->fachbereich_id)>0?$this->fachbereich_id:'NULL').",".
				 "semester=".(strlen($this->semester)>0?$this->semester:'NULL').",\n".
				 "verband='".$this->verband."',".
				 "gruppe='".$this->gruppe."',".
				 "raumtyp='".$this->raumtyp."',".
				 "raumtypalternativ=".(strlen($this->raumtypalternativ)>0?"'".$this->raumtypalternativ."'":'NULL').",\n".
				 "semesterstunden=".(strlen($this->semesterstunden)>0?$this->semesterstunden:'NULL').",".
				 "stundenblockung=".(strlen($this->stundenblockung)>0?$this->stundenblockung:'NULL').",".
				 "wochenrythmus=".(strlen($this->wochenrythmus)>0?$this->wochenrythmus:'NULL').",\n".
				 "start_kw=".($this->start_kw>0?$this->start_kw:'NULL').",".
				 "anmerkung=".(strlen($this->anmerkung)>0?"'".$this->anmerkung."'":'NULL').
				 " WHERE lehrveranstaltung_id=".$this->lehrveranstaltung_id;
		}
		//echo "<br>".$qry.'start_kw: <'.$this->start_kw.'>';
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn).' sql: '.$qry;
			return false;
		}
		if ($this->new) {
			// neue Lehrveranstaltungs-ID herausfinden und speichern
			$lastoid=pg_getlastoid($erg);
			$qry="select lehrveranstaltung_id from tbl_lehrveranstaltung where oid=$lastoid";
			if(!($erg=pg_exec($this->conn, $qry)))
			{
				$this->errormsg=pg_errormessage($this->conn).' sql: '.$qry;;
				return false;
			}
			$row=pg_fetch_object($erg,0);
			$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$this->new=false;
		}
		return true;
	}

	function delete() {
		global $auth;
		if ($this->new) {
			$this->errormsg='Datensatz mit new=true kann nicht gel?scht werden.';
			return false;
		}
		$qry="delete from tbl_lehrveranstaltung where lehrveranstaltung_id=".addslashes($this->lehrveranstaltung_id);
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn)." \nSQL: ".$qry;
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
	function getLVAs($einheit_kurzbz, $grp, $ver, $sem, $stg_kz,$lektor, $stsem='')
	{
		if (strlen($einheit_kurzbz)>0)
		{
			// einheit?
			//$join=" join tbl_einheitstudent on (m.uid=tbl_einheitstudent.uid) ";
			$where=" lva.einheit_kurzbz='".$einheit_kurzbz."'";
		}
		if (strlen($grp)>0)
		{
			// Gruppe
			$where.=(strlen($where)>0?' and ':'')." lva.gruppe='".$grp."' ";
		}
		if (strlen($ver)>0)
		{
			// Verband
			$where.=(strlen($where)>0?' and ':'')." lva.verband='".$ver."' ";
		}
		if (strlen($sem)>0)
		{
			// Semester
			$where.=(strlen($where)>0?' and ':'')." lva.semester=".$sem." ";
		}
		if (strlen($stg_kz)>0)
		{
			// Studiengang
			$where.=(strlen($where)>0?' and ':'')." lva.studiengang_kz='".$stg_kz."' ";
		}
		if (strlen($lektor)>0)
		{
			// Lektor
			$where.=(strlen($where)>0?' and ':'')." lva.lektor='".$lektor."' ";
		}
		if (strlen($stsem)>0)
		{
			// Studiensemester
			$where.=(strlen($where)>0?' and ':'')." lva.studiensemester_kurzbz='".$stsem."' ";
		}
		$sql_query="set datestyle to german;SELECT lva.*,tbl_lehrfach.bezeichnung as lehrfach_bezeichnung, ".
				   " tbl_person.nachname  || ', ' || tbl_person.titel || ' ' || tbl_person.vornamen as lektorName ".
		           "FROM tbl_lehrveranstaltung as lva join tbl_lehrfach using(lehrfach_nr) ".
				   " left join tbl_person on(lva.lektor=tbl_person.uid) ".
		           (strlen($where)>1?'WHERE '.$where:'').
	               "ORDER by upper(lva.unr),upper(lva.lvnr)";
	   //echo $sql_query;
		if(!($erg=pg_exec($this->conn, $sql_query))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new lfvt($this->conn);
			$l->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$l->lvnr=$row->lvnr;
			$l->unr=$row->unr;
			$l->einheit_kurzbz=$row->einheit_kurzbz;
			$l->lektor=$row->lektor;
			$l->lektorPrettyPrint=$row->lektorname;
			$l->lehrfach_nr=$row->lehrfach_nr;
			$l->lehrform=$row->lehrform_kurzbz;
			$l->studiengang_kz=$row->studiengang_kz;
			$l->fachbereich_id=$row->fachbereich_id;
			$l->semester=$row->semester;
			$l->verband=strlen($row->verband)>0?$row->verband:null;
			$l->gruppe=$row->gruppe;
			$l->raumtyp=$row->raumtyp;
			$l->raumtypalternativ=$row->raumtypalternativ;
			$l->semesterstunden=$row->semesterstunden;
			$l->stundenblockung=$row->stundenblockung;
			$l->wochenrythmus=$row->wochenrythmus;
			$l->start_kw=$row->start_kw;
			$l->anmerkung=$row->anmerkung;
			$l->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$l->lehrfach=$row->lehrfach_bezeichnung;
			// lva in Array speichern
			$result[]=$l;
		}
		return $result;
	}

}
