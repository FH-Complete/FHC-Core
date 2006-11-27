<?php 
/**
 * @author Christian Paminger, Werner Masik (werner@gefi.at)
 * @version 1.0
 * @created 22-Okt-2004 
 * @updated 29.10.2004 (WM)
 */
class mitarbeiter extends person
{
	/**
	 * @var boolean
	 */
	var $personalnr;
	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var boolean true=lektor, false=sonstiger MA
	 */
	var $lektor;
	/**
	 * @var boolean
	 */
	var $fixangestellt;
	/**
	 * @var string
	 */
	var $telefonklappe;
	/**
	 * @var funktion
	 */
	var $funktion;
	/**
	 * @var mitarbeiter
	 */
	var $mitarbeiter;
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var string?
	 */
	var $updateamum;
	/**
	 * @var string
	 */
	var $updatevon;
	/**
	 * @var string
	 */
	var $ort_kurzbz='0';
	  

	function mitarbeiter($conn,$uid='')
	{
		$this->conn=$conn;
		if (strlen($uid)>0) {
			$this->uid=$uid;
			$this->load();
		}
	}

	/**
	 * @return boolean true=ok, false=fehler
	 */
	function save()
	{
		global $auth;
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
			$qry="INSERT INTO tbl_mitarbeiter(uid,personalnummer,kurzbz,".
				 "lektor,fixangestellt,telefonklappe,updateamum,updatevon, ort_kurzbz)".
				 "values(".
				 "'".$this->uid."',".
				 "'".$this->personalnummer."',".
				 "'".$this->kurzbz."','".
				 ($this->lektor?'t':'f')."','".
				 ($this->fixangestellt?'t':'f')."',".
				 "'".$this->telefonklappe."',".
				 "now(),'".$_SERVER['PHP_AUTH_USER']."', ".($this->ort_kurzbz!='0'?"'$this->ort_kurzbz'":'NULL').
				 ")";			
		} else
		{
			$qry="UPDATE tbl_mitarbeiter ".
				 "SET personalnummer='".$this->personalnummer."',".
				 "kurzbz='".$this->kurzbz."',".
				 "lektor='".($this->lektor?'t':'f')."',".
				 "fixangestellt='".($this->fixangestellt?'t':'f')."',".
				 "telefonklappe='".$this->telefonklappe."',".
				 "updateamum=now(),updatevon='".$_SERVER['PHP_AUTH_USER']."', ort_kurzbz=".($this->ort_kurzbz!='0'?"'$this->ort_kurzbz'":'NULL').
				 " WHERE uid='".$this->uid."'";	
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
		// MA-Daten holen
		$sql_query="SELECT m.personalnummer,m.kurzbz,m.lektor,m.fixangestellt,m.telefonklappe,m.updateamum,m.updatevon, m.ort_kurzbz ".
		           "FROM tbl_mitarbeiter as m ".
	               "WHERE uid='".$this->uid."'";
		if(!($erg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		$num_rows=pg_numrows($erg);
		if($num_rows!=1) {
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);
	
		$this->personalnummer=$row->personalnummer;
		$this->kurzbz=$row->kurzbz;
		$this->lektor=$row->lektor=='t'?true:false;	
		$this->fixangestellt=$row->fixangestellt=='t'?true:false;	
		$this->telefonklappe=$row->telefonklappe;
		$this->updateamum=$row->updateamum;
		$this->updatevon=$row->updatevon;
		$this->ort_kurzbz=$row->ort_kurzbz;
				
		
		return true;
	}

	/**
	 * Loescht den Mitarbeiter aus der Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 * vererbt, wenn Person gelöscht wird, sollte wahrscheinlich auch
	 * automatisch der Eintrag in den anderen Tabellen gelöscht werden
	 */
	 /*
	function delete()
	{
		if (is_null($conn=$this->getConnection())) {
			return false;	
		}	
		
		
		return true;
	}*/

	/**
	 * gibt array mit allen Lektoren zurück
	 * @return array mit Lektoren
	 */
	function getLektoren() 
	{
		if (is_null($conn=$this->getConnection())) 
		{
			return false;	
		}	
		$sql_query="set datestyle to german;SELECT tbl_person.*,".
				   "m.personalnummer,m.kurzbz,m.lektor,m.fixangestellt,m.telefonklappe, m.ort_kurzbz ".
		           "FROM tbl_person join tbl_mitarbeiter as m using(uid) ".
	               "WHERE m.lektor=true ".
	               "ORDER by upper(tbl_person.nachname),upper(tbl_person.vornamen)";
		if(!($erg=@pg_exec($conn, $sql_query))) {
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);		
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
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
			// Lektorendaten
			$l->personalnummer=$row->personalnummer;
			$l->kurzbz=$row->kurzbz;
			$l->lektor=$row->lektor=='t'?true:false;	
			$l->fixangestellt=$row->fixangestellt=='t'?true:false;	
			$l->telefonklappe=$row->telefonklappe;
			$l->ort_kurzbz=$row->ort_kurzbz;
			// Lektor in Array speichern
			$result[]=$l;
		}
		return $result;
	}

	/**
	 * gibt array mit allen Mitarbeitern zurück
	 * @param $order gibt die spalte an nach der Sortiert werden soll
	 * @return array mit MA
	 */
	function getAll($order='upper(tbl_person.nachname),upper(tbl_person.vornamen)') 
	{
		if (is_null($conn=$this->getConnection())) 
		{
			return false;	
		}	
		$sql_query="set datestyle to german;SELECT tbl_person.*,".
				   "m.personalnummer,m.kurzbz,m.lektor,m.fixangestellt,m.telefonklappe, m.ort_kurzbz ".
		           "FROM tbl_person join tbl_mitarbeiter as m using(uid) ".	               
	               "ORDER by $order";
		if(!($erg=@pg_exec($conn, $sql_query))) 
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);		
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
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
			// Lektorendaten
			$l->personalnummer=$row->personalnummer;
			$l->kurzbz=$row->kurzbz;
			$l->lektor=($row->lektor=='t'?'true':'false');	
			$l->fixangestellt=($row->fixangestellt=='t'?'true':'false');	
			$l->telefonklappe=$row->telefonklappe;
			$l->ort_kurzbz=$row->ort_kurzbz;
			// MA in Array speichern
			$result[]=$l;
		}
		return $result;
		
	}
	
	/**
	 * gibt array mit allen Mitarbeitern zurueck
	 * @return array mit Mitarbeitern
	 */
	function getMitarbeiter($lektor=true,$fixangestellt=null,$stg_kz=null,$fachbereich_id=null) 
	{
		if (is_null($conn=$this->getConnection())) 
		{
			return false;	
		}	
		$sql_query='SELECT DISTINCT vw_mitarbeiter.* FROM vw_mitarbeiter 
					LEFT OUTER JOIN tbl_personfunktion USING(uid)
					WHERE';
		if (!$lektor)
			$sql_query.=' NOT';
		$sql_query.=' lektor';
		if ($fixangestellt!=null)
		{
			$sql_query.=' AND';
			if (!$fixangestellt)
				$sql_query.=' NOT';
			$sql_query.=' fixangestellt';
		}
		if ($stg_kz!=null)
			$sql_query.=' AND studiengang_kz='.$stg_kz;
		if ($fachbereich_id!=null)
			$sql_query.=' AND fachbereich_id='.$fachbereich_id;
	    $sql_query.=' ORDER BY nachname, vornamen, kurzbz';
	    //echo $sql_query;
		if(!($erg=@pg_query($conn, $sql_query))) 
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		$num_rows=pg_numrows($erg);		
		$result=array();
		for($i=0;$i<$num_rows;$i++)
		{
   			$row=pg_fetch_object($erg,$i);
			$l=new mitarbeiter($this->conn);
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
			// Lektorendaten
			$l->personalnummer=$row->personalnummer;
			$l->kurzbz=$row->kurzbz;
			$l->lektor=$row->lektor=='t'?true:false;	
			$l->fixangestellt=$row->fixangestellt=='t'?true:false;	
			$l->telefonklappe=$row->telefonklappe;
			//$l->ort_kurzbz=$row->ort_kurzbz;
			// Lektor in Array speichern
			$result[]=$l;
		}
		return $result;
	}
}
?>