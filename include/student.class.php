<?php
/**
 * Basisklasse für Studenten
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @updated 07-Dez-2004
 */
class student extends person
{
	/**
	 * @var integer
	 */
	var $studiengang_kz;
	/**
	 * @var string
	 */
	var $matrikelnr;
	/**
	 * @var integer
	 */
	var $semester;
	/**
	 * @var $string
	 */
	var $verband;
	/**
	 * @var string
	 */
	var $gruppe;
	/**
	 * @var student
	 */
	var $student;
	/**
	 * @var einheit
	 */
	var $einheit;
	/**
	 * @var studiengangsbezeichnung (nur zum Anzeigen, wird nicht gespeichert)
	 */
	var $stg_bezeichnung;
	/**
	 * @var string fehlermeldung
	 */
	var $errormsg;

	function student($conn,$uid='')
	{
		$this->conn = $conn;
		if (strlen($uid)>0) {
			$this->uid=$uid;
			$this->load();
		}
	}


	/**
	 * Speichert den Studenten in die Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'. INSERT oder DELETE wird
	 * durch 'new' bestimmt.
	 * @return boolean true=ok, false=fehler
	 */
	function save()
	{
		// uid vorhanden?
		if (strlen($this->uid)==0) {
			$this->errormsg='<i>uid</i> nicht gesetzt.';
			return false;
		}
		// Connection holen
		if (is_null($conn=person::getConnection())) {
			return false;
		}
		// Daten zur Person speichern

		if (!person::save()) {
			$this->errormsg.="<br/>Daten zur Person konnten nicht gespeichert werden.";
			return false;
		}
		if ($this->new) {
			$qry="INSERT INTO tbl_student(uid,studiengang_kz,matrikelnr,".
				 "semester,verband,gruppe,updateamum,updatevon)".
				 "values(".
				 "'".$this->uid."',".
				 (ctype_digit($this->studiengang_kz)?$this->studiengang_kz:'NULL').",".
				 "'".$this->matrikelnr."',".
				 (ctype_digit($this->semester)?$this->semester:'NULL').",".
				 "'".$this->verband."','".$this->gruppe."',".
				 "now(),'".$_SERVER['PHP_AUTH_USER']."'".
				 ")";
		} else
		{
			$qry="UPDATE tbl_student ".
				 "SET studiengang_kz=".$this->studiengang_kz.",".
				 "matrikelnr='".$this->matrikelnr."',".
				 "semester=".(ctype_digit($this->semester)?$this->semester:'NULL').",".
				 "verband='".$this->verband."',".
				 "gruppe='".$this->gruppe."',".
				 "updateamum=now(),updatevon='".$_SERVER['PHP_AUTH_USER']."' ".
				 "WHERE uid='".$this->uid."'";
		}
		//echo "<br>".$qry;
		if(!($erg=pg_exec($conn, $qry)))
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		return true;
	}

	/**
	 * Ladet die Attribute des Studenten aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function load($uid='')
	{
		// optional: uid setzen
		if (strlen($uid)>0)
			$this->uid=$uid;
		// uid vorhanden?
		if (strlen($this->uid)==0) {
			$this->errormsg='<i>uid</i> nicht gesetzt.';
			return false;
		}
		// Connection holen
		if (is_null($conn=person::getConnection())) {
			return false;
		}
		// Daten zur Person laden
		if (!person::load()) {
			$this->errormsg.="<br/>Daten zur Person konnten nicht geladen werden.";
			return false;
		}
		// Studentendaten holen
		$sql_query="SELECT s.studiengang_kz,s.matrikelnr,s.semester,s.verband,s.gruppe ".
		           "FROM tbl_student as s ".
	               "WHERE uid='".$this->uid."'";
	    $sql_query="set datestyle to german;
	    		SELECT tbl_person.*, m.studiengang_kz,m.matrikelnr,m.semester,m.verband,m.gruppe,
					tbl_studiengang.studiengang_kz,tbl_studiengang.bezeichnung
				FROM tbl_person join tbl_student as m using(uid) join
					tbl_studiengang on (m.studiengang_kz=tbl_studiengang.studiengang_kz)
				WHERE uid='".$this->uid."'";
		if(!($erg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows=pg_numrows($erg);
		if($num_rows!=1)
		{
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

		$this->studiengang_kz=$row->studiengang_kz;
		$this->matrikelnr=$row->matrikelnr;
		$this->semester=$row->semester;
		$this->verband=$row->verband;
		$this->gruppe=$row->gruppe;
		$this->uid=$row->uid;
		$this->titel=$row->titel;
		$this->vornamen=$row->vornamen;
		$this->nachname=$row->nachname;
		$this->gebdatum=$row->gebdatum;
		$this->gebort=$row->gebort;
		$this->gebzeit=$row->gebzeit;
		$this->foto=$row->foto;
		$this->anmerkungen=$row->anmerkungen;
		$this->aktiv=$row->aktiv=='t'?true:false;
		$this->email=$row->email;
		$this->homepage=$row->homepage;
		$this->updateamum=$row->updateamum;
		$this->updatevon=$row->updatevon;
		$this->stg_bezeichnung=$row->bezeichnung;

		// todo: einheit

		$result[]=$this;
		return $result;
		//return true;
	}

	/**
	 * Loescht den Studenten aus der Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function delete()
	{
		return false;
	}

	/**
	 * Rueckgabewert ist die Anzahl der Ergebnisse. Bei Fehler negativ und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	 * @param string $einheit_kurzbz    Einheit
	 * @param string grp    Gruppe
	 * @param string ver    Verband
	 * @param integer sem    Semester
	 * @param integer stg_kz    Kennzahl des Studiengangs
	 * @return integer Anzahl der gefundenen Einträge; <b>negativ</b> bei Fehler
	 */
	function getStudents($einheit_kurzbz, $grp, $ver, $sem, $stg_kz)
	{
		if (is_null($conn=person::getConnection())) {
			return false;
		}
		$join = '';
		$where = '';
		if (strlen($einheit_kurzbz)>0)
		{
			// einheit?
			$join=" join tbl_einheitstudent on (m.uid=tbl_einheitstudent.uid) ";
			$where=" tbl_einheitstudent.einheit_kurzbz='".$einheit_kurzbz."'";
		} else
		{
			if (strlen($grp)>0)
			{
				// Gruppe
				$where.=(strlen($where)>0?' and ':'')." m.gruppe='".$grp."' ";
			}
			if (strlen($ver)>0)
			{
				// Verband
				$where.=(strlen($where)>0?' and ':'')." m.verband='".$ver."' ";
			}
			if (strlen($sem)>0)
			{
				// Semester
				$where.=(strlen($where)>0?' and ':'')." m.semester=".$sem." ";
			}
			if (strlen($stg_kz)>0)
			{
				// Studiengang
				$where.=(strlen($where)>0?' and ':'')." m.studiengang_kz='".$stg_kz."' ";
			}
		}


		$sql_query="set datestyle to german;SELECT tbl_person.*,".
				   "m.studiengang_kz,m.matrikelnr,m.semester,m.verband,m.gruppe, ".
				   "tbl_studiengang.studiengang_kz,tbl_studiengang.bezeichnung ".
		           "FROM tbl_person join tbl_student as m using(uid) join ".
		           "tbl_studiengang on (m.studiengang_kz=tbl_studiengang.studiengang_kz) $join ".
		           (strlen($where)>1?'WHERE '.$where:'').
	               "ORDER by upper(tbl_person.nachname),upper(tbl_person.vornamen)";
	    //echo $sql_query;
		if(!($erg=@pg_exec($conn, $sql_query))) {
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new student($conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titel=$row->titel;
			$l->vornamen=$row->vornamen;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkungen;
			$l->aktiv=$row->aktiv=='t'?true:false;
			$l->email=$row->email;
			$l->homepage=$row->homepage;
			$l->updateamum=(isset($row->updateamum)?$row->updateamum:'');
			$l->updatevon=(isset($row->updatevon)?$row->updatevon:'');
			// Studentendaten
			$l->matrikelnr=$row->matrikelnr;
			$l->gruppe=$row->gruppe;
			$l->verband=$row->verband;
			$l->semester=$row->semester;
			$l->studiengang_kz=$row->studiengang_kz;
			$l->stg_bezeichnung=$row->bezeichnung;
			// student in Array speichern
			$result[]=$l;
		}
		return $result;
	}

	/**
	 * gibt array mit allen Studenten zurück
	 * @return array Studenten
	 */
	function getAll() {
		if (is_null($conn=person::getConnection())) {
			return false;
		}
		$sql_query="set datestyle to german;SELECT tbl_person.*,".
				   "m.matrikelnr,m.semester,m.verband,m.gruppe, ".
				   "tbl_studiengang.studiengang_kz,tbl_studiengang.bezeichnung ".
		           "FROM tbl_person join tbl_student as m using(uid) ".
		           " join tbl_studiengang on (m.studiengang_kz=tbl_studiengang.studiengang_kz) ".
	               "ORDER by upper(tbl_person.nachname),upper(tbl_person.vornamen)";
	    //echo $sql_query;
		if(!($erg=@pg_exec($conn, $sql_query))) {
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new student($conn);
			// Personendaten
			$l->uid=$row->uid;
			$l->titel=$row->titel;
			$l->vornamen=$row->vornamen;
			$l->nachname=$row->nachname;
			$l->gebdatum=$row->gebdatum;
			$l->gebort=$row->gebort;
			$l->gebzeit=$row->gebzeit;
			$l->foto=$row->foto;
			$l->anmerkungen=$row->anmerkungen;
			$l->aktiv=$row->aktiv=='t'?true:false;
			$l->email=$row->email;
			$l->homepage=$row->homepage;
			$l->updateamum=$row->updateamum;
			$l->updatevon=$row->updatevon;
			// Studentendaten
			$l->matrikelnummer=$row->matrikelnummer;
			$l->gruppe=$row->gruppe;
			$l->verband=$row->verband;
			$l->semester=$row->semester;
			$l->studiengang_kz=$row->studiengang_kz;
			$l->stg_bezeichnung=$row->bezeichnung;
			// student in Array speichern
			$result[]=$l;
		}
		return $result;
	}




}

?>