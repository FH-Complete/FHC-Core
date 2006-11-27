<?php
/**
 * Basisklasse für Personen. Für Mitarbeiter gibt es die Klasse Mitarbeiter und
 * für Studenten Studenten
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @updated 23-Okt-2004: Funktion getConnection() hinzugefügt
 */
class person
{
	var $uid;
	var $titel;
	var $vornamen;
	var $nachname;
	var $gebdatum;
	var $gebort;
	var $gebzeit;
	/**
	 * @var int
	 */
	var $foto;
	var $anmerkungen;
	/**
	 * @var boolean Person ist noch gültig oder nicht (~nicht mehr am Technikum)
	 */
	var $aktiv;
	var $email;
	var $alias;
	var $homepage;
	/**
	 * Funktionen liefern <code>false</code> wenn Fehler auftritt und schreiben
	 * die Fehlermeldung in diese Variable
	 * @var string enthält Fehlermeldung
	 */
	var $errormsg;
	/**
	 *  @var boolean true=Person neu anlegen (INSERT), false=UPDATE
	 */
	var $new=true;
	/**
	 * @var person Person (todo: wozu?)
	 */
	var $person;
	/**
	 * @var resource
	 */
	var $conn;
	var $updateamum;
	var $updatevon;

	function person($conn)
	{
          $this->conn = $conn;
	}


	/**
	 * Verbindung zur Datenbank herstellen
	 * @return PostgreSQL-Connection oder NULL
	 */
	function getConnection() {
		return $this->conn;
	}

	/**
	 * Speichert die Person in die Datenbank. INSERT oder DELETE wird durch 'new'
	 * bestimmt.
	 * @return boolean false, wenn's nicht funtkioniert hat (Fehlermeldung steht
	 * in errormsg)
	 */
	function save()
	{
		if (is_null($conn=$this->getConnection())) {
			return false;
		}
		if (strlen($this->uid)==0)
		{
			$this->errormsg="<i>uid</i> nicht gesetzt";
			return false;
		}
		if ($this->new)
		{
			$qry="insert into tbl_person(uid,titel,vornamen,nachname,gebdatum,".
				 "gebort,gebzeit,anmerkungen,aktiv,".
				 "email,alias,homepage) ".
				 "values('".$this->uid."','".$this->titel."',".
				 "'".$this->vornamen."','".$this->nachname."',".
				 (strlen($this->gebdatum)>0?"'".$this->gebdatum."'":'NULL').
				 ",'".$this->gebort."',".
				 (strlen($this->gebzeit)>0?"'".$this->gebzeit."'":'NULL').
				 ",'".$this->anmerkungen."',".($this->aktiv?'true':'false').",".
				 "'".$this->email."',".($this->alias==''?'null':"'$this->alias'").",'".$this->homepage."'".
				 ")";
		}
		else
		{
			$qry="update tbl_person set ".
				 "titel='".$this->titel."',".
				 "vornamen='".$this->vornamen."',".
				 "nachname='".$this->nachname."',".
				 "gebdatum=".(strlen($this->gebdatum)>0?"'".$this->gebdatum."'":'NULL').",".
				 "gebort='".$this->gebort."',".
				 "gebzeit=".(strlen($this->gebzeit)>0?"'".$this->gebzeit."'":'NULL').",".
				 "anmerkungen='".$this->anmerkungen."',".
				 "aktiv=".($this->aktiv?'true':'false').",".
				 "email='".$this->email."',alias=".($this->alias==''?'null':"'$this->alias'").",".
				 "homepage='".$this->homepage."' ".
				 "where uid='".$this->uid."'";
		}
		$qry="set datestyle to german;".$qry;
		//echo $qry;
		if(!($erg=pg_exec($conn, $qry)))
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		return true;
	}

	/**
	 * Ladet die Attribute der Person aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true, wenn's funktioniert hat; false bei Fehler
	 */
	function load($uid='')
	{
		// optional: uid setzen
		if (strlen($uid)>0)
			$this->uid=$uid;
		// uid vorhanden?
		if (strlen($this->uid)==0) {
			$this->errormsg='<i>uid</i> nicht gesetzt';
			return false;
		}
		if (is_null($conn=$this->getConnection())) {
			return false;
		}
		$sql_query="set datestyle to german;SELECT tbl_person.* ".
		           "FROM tbl_person ".
	               "WHERE uid='".addslashes($this->uid)."'";
		if(!($erg=pg_exec($conn, $sql_query))) {
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		if($num_rows!=1)
		{
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

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
		$this->alias=$row->alias;
		$this->homepage=$row->homepage;
		$this->updateamum=$row->updateamum;
		$this->updatevon=$row->updatevon;
		$this->new=false;
		return true;
	}

	/**
	 * Löscht die Person aus der Datenbank. Bei Fehler ist der Rueckgabewert 'false'
	 * und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true bei Erfolg, false bei Fehler
	 */
	function delete()
	{
		if (is_null($conn=$this->getConnection()))
		{
			return false;
		}
		$qry="delete from tbl_person where uid='".$this->uid."'";
		if(!($erg=pg_exec($conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		return true;
	}



}
?>