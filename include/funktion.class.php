<?php 


/**
 * @author Christian Paminger, Werner Masik
 * @version 1.0
 * @created 22-Okt-2004
 * @update  28.10.2004
 */
class funktion
{

	/**
	 * @var string
	 */
	var $kurzbz;
	/**
	 * @var string
	 */
	var $bezeichnung;
	/**
	 * @var boolean
	 */
	var $aktiv;
	/**
	 * @var string
	 */
	var $errormsg;
	/**
	 * @var boolean
	 */
	var $new=true;

	/**
	 * @var resource
	 */
	var $conn;

	function funktion($conn)
	{
		$this->conn = $conn;
	}


	/**
	 * Ladet die Attribute der Funktion aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function load($kurzbz)
	{
		$this->kurzbz=$kurzbz;
		$qry="select * from tbl_funktion where funktion_kurzbz='$kurzbz'";
		if (is_null($this->conn)) {
			return false;	
		}		
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_numrows($erg);
		if($num_rows!=1) {
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
		$row=pg_fetch_object($erg,0);		
		$this->bezeichnung=$row->bezeichnung;
		$this->aktiv=$row->aktiv=='t'?true:false;
		$this->new=false;		
		return true;
	}

	/**
	 * Speichert die Funktion in die Datenbank. Bei Fehler ist der Rueckgabewert
	 * 'false' und die Fehlermeldung steht in 'errormsg'. INSERT oder DELETE wird
	 * durch 'new' bestimmt.
	 * @return boolean true=ok, false=fehler
	 */
	function save()
	{
		if (is_null($this->conn)) {
			return false;	
		}	
		if (strlen($this->kurzbz)==0) 
		{
			$this->errormsg="<i>kurzbz</i> nicht gesetzt";
			return false;
		}
		if ($this->new) 
		{
			$qry="insert into tbl_funktion(funktion_kurzbz,bezeichnung,aktiv) ".
				 "values('".$this->kurzbz."','".$this->bezeichnung."',".($this->aktiv?'t':'f').")";
		} else 
		{
			$qry="update tbl_funktion set bezeichnung='".$this->bezeichnung."',".
				 "aktiv=".($this->aktiv?'t':'f')." where funktion_kurzbzb='$this->kurzbz'";
		} 
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}		
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
		if (strlen($this->kurzbz)==0) 
		{
			$this->errormsg="<i>kurzbz</i> nicht gesetzt";
			return false;
		}
		$qry="delete from tbl_funktion where funktion_kurzbz='".$this->kurzbz."'";
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}		
		return true;
	}

	/**
	 * alle Personen mit dieser Funktion holen
	 * @param string $kurzbz Kurzbezeichnung der Funktion (wenn nicht angegeben
	 * wird lokale $kurzbz verwendet)
	 * @return array key=personfunktion_id, value=array('person','studiengang_kz'',
	 * 'studiengang_kurzbz','fachbereich_id', 'fachbereich_kurzbz')  oder false, wenn Fehler
	 */
	function getPersonen($kurzbz='') 
	{
		// Array für Suchergebnis
		$result=array();
		
		if (strlen($kurzbz)==0)
		{
			$search_kurzbz=$this->kurzbz;
		} else
		{
			$search_kurzbz=$kurzbz;
		}
		$qry="select tbl_personfunktion.*,tbl_studiengang.kurzbz as studiengang_kurzbz,tbl_fachbereich.kurzbz as fachbereich_kurzbz from tbl_personfunktion join tbl_person using(uid) ".
			 "left join tbl_studiengang using(studiengang_kz) ".
			 "left join tbl_fachbereich using(fachbereich_id) ".
			 "where funktion_kurzbz='$search_kurzbz' ".
			 "order by upper(tbl_person.nachname)";
		if (is_null($this->conn)) {
			return false;	
		}	
		//echo "'".$qry."'";
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}		
		$num_rows=pg_numrows($erg);
		for ($i=0;$i<$num_rows;$i++) 
		{			
			// Person laden (nicht für große Anzahl von Personen geeignet)
			$temp_person=new person($this->conn);
			$temp_person->load(pg_result($erg,$i,'uid'));
			// und in Array speichern
			$result[pg_result($erg,$i,'personfunktion_id')]=array(
				'person'=>$temp_person,
				'studiengang_kz'=>@pg_result($erg,$i,'studiengang_kz'),
				'studiengang_kurzbz'=>@pg_result($erg,$i,'studiengang_kurzbz'),
				'fachbereich_kurzbz'=>@pg_result($erg,$i,'fachbereich_kurzbz'),
				'fachbereich_id'=>@pg_result($erg,$i,'fachbereich_id') 				
				);
		}		
		return $result;
	}

	/**
	 * Person Funktion dazugeben
	 * @param string $uid User-ID
	 * @param string $studiengang_kz Studiengang-Kennzahl (optional)
	 * @param integer $fachbereich_id optional
	 * @return boolean true=ok, false=fehler
	 */
	function addPerson($uid,$studiengang_kz=null,$fachbereich_id=null) 
	{
		if (is_null($this->conn)) {
			return false;	
		}	
		$targetlist="uid,funktion_kurzbz";
		if (strlen($studiengang_kz)>0) $targetlist.=",studiengang_kz";
		if (strlen($fachbereich_id)>0) $targetlist.=",fachbereich_id";
		$values="'$uid','".$this->kurzbz."'";
		if (strlen($studiengang_kz)>0) $values.=",$studiengang_kz";
		if (strlen($fachbereich_id)>0) $values.=",$fachbereich_id";
		$qry="insert into tbl_personfunktion($targetlist) ".
			 "values($values)";
		//echo $qry;
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}		
		return true;	 
	}
	
	/**
	 * Person Funktion wegnehmen
	 * @param string $uid User-ID
	 * @param string $studiengang_kz Studiengang-Kennzahl (optional)
	 * @param integer $fachbereich_id optional
	 * @return boolean true=ok, false=fehler
	 */
	function removePerson($personfunktion_id) 
	{
		if (is_null($this->conn)) {
			return false;	
		}		
		if (strlen($personfunktion_id)==0) {
			$this->errormsg="personfunktion_id darf nicht NULL sein";
			return false;
		}
		$qry="delete from tbl_personfunktion where personfunktion_id=$personfunktion_id";
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}	
		return true;		 
	}
	
	/**
	 * Personfunktion aktualisieren	 
	 * @param integer $personfunktion ID aus der Zuordnungstabelle
	 * @param string $uid User-ID
	 * @param string $studiengang_kz Studiengang-Kennzahl (optional)
	 * @param integer $fachbereich_id optional
	 * @return boolean true=ok, false=fehler
	 */
	function updatePerson($personfunktion_id,$uid,$studiengang_kz=null,
		$fachbereich_id=null) {
		if (is_null($this->conn)) {
			return false;	
		}
		if (strlen($studiengang_kz)>0) 
		{
			$values.=",studiengang_kz=$studiengang_kz ";
		} else
		{
			$values.=",studiengang_kz=NULL ";
		}
		if (strlen($fachbereich_id)>0) 
		{
			$values.=",fachbereich_id=$fachbereich_id";
		} else 
		{
			if (strlen($studiengang_kz)==0) {
				$this->errormsg="Studiengang oder Fachbereich fehlt.";
				return false;	
			}
			$values.=",fachbereich_id=NULL";
		}
		$qry="update tbl_personfunktion set ".
			 "uid='$uid'$values ".
			 "where personfunktion_id=$personfunktion_id";
		//echo $qry;
		if(!($erg=@pg_exec($this->conn, $qry)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}	
		return true;
	}
}
?>