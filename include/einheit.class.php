<?php

/**
 * Eine Einheit fasst mehrere Studenten zusammen. Die Besonderheit ist,
 * dass die Studenten aus unterschiedlichen Studiengaengen stammen koennen.
 * Das wird z.B. für die Freifaecher benoetigt.
 * @author Christian Paminger, Werner Masik (werner@gefi.at)
 * @version 1.0
 * @created 22-Okt-2004 11:50:18
 * @updated 28.10.2004 (WM)
 */
class einheit
{

	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var integer
	 */
	var $stg_kz;
	/**
	 * @var string nur zur info beim Suchergebnis, wird nicht gespeichert
	 */
	var $stg_kurzbz;
	/**
	 * @var string
	 */
	var $bezeichnung;
	/**
	 * @var integer
	 */
	var $semester;
	/**
	 * @var integer
	 */
	var $typ;
	/**
	 * @var string
	 */
	var $mailgrp_kurzbz;
	/**
	 * @var string
	 */
	var $errormsg;

	/**
	 * @var DB-Resource
	 */
	var $conn;

	function einheit($conn,$kurzbz='')
	{
           $this->conn = $conn;
		
	   if (strlen($kurzbz)>0) 
           {
	      $this->kurzbz=$kurzbz;
	      $this->load();

           }
	}


	/**
	 * Speichert die Einheit in der Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'. INSERT oder DELETE wird
	 * durch 'new' bestimmt.
	 * @param string $kurzbz_new Kurzbezeichnung ist optional
	 * @return boolean true=ok, false=fehler
	 */
	function save($kurzbz_new='')
	{
		if (is_null($this->conn))
        {
			return false;
		}

		if (strlen($this->kurzbz)==0)
		{
			$this->errormsg="<i>kurzbz</i> nicht gesetzt";
			return false;
		}

		if ($this->new)
		{
			if (is_numeric($this->semester)) $semester=$this->semester;
			else $semester='NULL';
			if (is_numeric($this->typ)) $typ=$this->typ;
			else $typ='NULL';
			if(!strlen($this->mailgrp_kurzbz)>0)
			    $this->mailgrp_kurzbz='NULL';
			$qry="insert into tbl_einheit(einheit_kurzbz,studiengang_kz,bezeichnung,semester,typ,mailgrp_kurzbz) ".
				 "values('".$this->kurzbz."','".$this->stg_kz."','".$this->bezeichnung."',$semester,$typ,$this->mailgrp_kurzbz)";
		} else
		{
			if (is_numeric($this->semester)) $semester='semester='.$this->semester;
			else $semester='semester=NULL';
			if (is_numeric($this->typ)) $typ='typ='.$this->typ;
			else $typ='NULL';
			if(!strlen($this->mailgrp_kurzbz)>0)
			    $this->mailgrp_kurzbz='mailgrp_kurzbz=NULL';
			else 
			    $this->mailgrp_kurzbz="mailgrp_kurzbz='$this->mailgrp_kurzbz'";
			    
			$qry="update tbl_einheit set studiengang_kz=".$this->stg_kz.",bezeichnung='".$this->bezeichnung."',".
				 "$semester,$typ,".$this->mailgrp_kurzbz.",einheit_kurzbz='".(strlen($kurzbz_new)>0?$kurzbz_new:$this->kurzbz)."' ".
				 "where einheit_kurzbz='".$this->kurzbz."'";

		}
		//echo $qry;
		if(!($erg=pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;

	}

	/**
	 * @return boolean true=ok, false=fehler
	 */
	function load($kurzbz='')
	{
		// optional: kurzbz setzen
		if (strlen($uid)>0)
			$this->kurzbz=$kurzbz;
		// uid vorhanden?
		if (strlen($this->kurzbz)==0) {
			$this->errormsg='<i>einheit_kurzbz</i> nicht gesetzt';
			return false;
		}
		if (is_null($this->conn)) {
			return false;
		}
		$qry="select einheit_kurzbz,studiengang_kz,bezeichnung,semester,typ, mailgrp_kurzbz ".
			 "from tbl_einheit ".
			 "where einheit_kurzbz='".$this->kurzbz."'";

		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		if($num_rows!=1) {
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

		$this->stg_kz=$row->studiengang_kz;
		$this->bezeichnung=$row->bezeichnung;
		$this->semester=$row->semester;
		$this->typ=$row->typ;
		$this->mailgrp_kurzbz=$row->mailgrp_kurzbz;

		return true;

	}

	/**
	 * Loescht die Funktion aus der Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function delete()
	{
		if (is_null($this->conn)) {
			return false;
		}
		$qry="delete from tbl_einheit where einheit_kurzbz='".$this->kurzbz."'";

		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;
	}

	function addStudent($uid) {
		if (is_null($this->conn)) {
			return false;
		}
		$qry="insert into tbl_einheitstudent(einheit_kurzbz,uid,updateamum,updatevon) ".
			 "values('".$this->kurzbz."','".$uid."',now(),'".$_SERVER['PHP_AUTH_USER']."')";

		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;
	}

	function deleteStudent($uid) {
		if (is_null($this->conn)) {
			return false;
		}
		$qry="delete from tbl_einheitstudent ".
			 "where einheit_kurzbz='".$this->kurzbz."' and uid='$uid'";

		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		return true;
	}


	/**
	 * Alle Einheiten zurueckgeben
	 * @return array Array der einheiten
	 */
	function getAll($studiengang_kz='')
	{
		if (is_null($this->conn)) {
			return false;
		}
		if (strlen($studiengang_kz)>0)
		{
			$where=" where tbl_einheit.studiengang_kz='".$studiengang_kz."' ";
		} else
		{
			$where="";
		}
		$qry="select tbl_einheit.*,tbl_studiengang.kurzbz from tbl_einheit join tbl_studiengang using(studiengang_kz) ".
             "$where order by einheit_kurzbz;";
		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new einheit($this->conn);
			$l->kurzbz=$row->einheit_kurzbz;
			$l->stg_kz=$row->studiengang_kz;
			$l->stg_kurzbz=$row->kurzbz;
			$l->bezeichnung=$row->bezeichnung;
			$l->semester=$row->semester;
			$l->typ=$row->typ;
			$l->mailgrp_kurzbz=$row->mailgrp_kurzbz;
			$result[]=$l;
		}
		return $result;
	}

	/**
	 * Liste aller Studenten zurueckgeben
	 * @param string $studiengang_kz optional
	 * @return array	Array mit allen Studenten, false bei fehler
	 */
	function getStudenten($studiengang_kz='')
	{
		if (is_null($this->conn)) {
			return false;
		}
		if (strlen($studiengang_kz)>0)
		{
			$where=" and tbl_einheit.studiengang_kz='".$studiengang_kz."' ";
		} else
		{
			$where="";
		}
		$qry="select einheit_kurzbz,uid ".
			 "from tbl_einheitstudent join tbl_student using(uid) join tbl_person using(uid) ".
			 " join tbl_einheit using (einheit_kurzbz) ".
			 "where einheit_kurzbz='".$this->kurzbz."' $where ".
			 "order by upper(tbl_person.nachname),upper(tbl_person.vornamen)";

		if(!($erg=pg_exec($this->conn, $qry))) {
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new student($this->conn,$row->uid);
			$result[]=$l;
		}
		return $result;
	}
	
	/**
	 * Liefert die Anzahl der Studenten in einer Einheit
	 * @param string kurzbzlang
	 * @return anzahl der Studenten
	 */
	function countStudenten($einheit_kurzbz)
	{
		$qry = "Select count(*) as anzahl from tbl_einheitstudent where einheit_kurzbz='$einheit_kurzbz'";
		if($result=pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
				return $row->anzahl;
			else
			{
				$this->errormsg = pg_errormessage($this->conn);
				return false;
			}
		}
		else 
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}
	}

}
?>