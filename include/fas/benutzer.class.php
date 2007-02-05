<?php
/**
 * Klasse fuer Berechtigungen der User
 * @author Christian Paminger
 * @version 1.0
 * @updated 11-Feb-2004
 */
class benutzer
{
	/**
	 * interne userberechtigung_id (Zaehler aus DB)
	 * @var integer
	 */
	var $userberechtigung_id;
	/**
	 * @var integer
	 */
	var $studiengang_kz;
	/**
	 * @var integer
	 */
	var $fachbereich_id;
	/**
	 * @var string
	 */
	var $berechtigung_kurzbz;
	/**
	 * @var string
	 */
	var $uid;
	/**
	 * @var string
	 */
	var $studiensemester_kurzbz;
	/**
	 * @var integer
	 */
	var $start;
	/**
	 * @var integer
	 */
	var $ende;
	/**
	 * @var integer
	 */
	var $starttimestamp;
	/**
	 * @var integer
	 */
	var $endetimestamp;
	/**
	 * @var string
	 */
	var $art;

	/**
	 * @var array
	 */
	var $berechtigungen=array();

	/**
	 * @var string
	 */

	var $variable;
	
	var $conn; //Vilesci Connection
	/**
	 * @var boolean
	 */
	var $new;
	var $errormsg;

	function benutzer($conn)
	{
		$this->conn=$conn;
		$this->new=true;
	}


	/**
	 * Ladet die Attribute der Berechtigung aus der Datenbank. Bei Fehler ist der
	 * Rueckgabewert 'false' und die Fehlermeldung steht in 'errormsg'.
	 * @return boolean true=ok, false=fehler
	 */
	function load($id)
	{
		// Berechtigung holen
		$sql_query="SELECT * FROM public.tbl_benutzerberechtigung WHERE benutzerberechtigung_id=$id";
	    //echo $sql_query;
		if(!($erg=pg_query($this->conn, $sql_query)))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		$num_rows=pg_num_rows($erg);
		if($num_rows!=1)
		{
			$this->errormsg="Zuwenige oder zuviele Ergebnisse (Anzahl: $num_rows)!";
			return false;
		}
   		$row=pg_fetch_object($erg,0);

		$this->userberechtigung_id=$row->benutzerberechtigung_id;
		$this->studiengang_kz=$row->studiengang_kz;
		$this->fachbereich_id=$row->fachbereich_kurzbz;
		$this->berechtigung_kurzbz=$row->berechtigung_kurzbz;
		$this->uid=$row->uid;
		$this->studiensemester_kurzbz=$row->studiensemester_kurzbz;
		$this->start=$row->start;
		$this->ende=$row->ende;
		$this->art=$row->art;
		$this->new=false;
		
		return true;
	}

	/**
	 * @return boolean true=ok, false=fehler
	 */
	function save()
	{
		/*
		// Connection holen
		if (is_null($conn=$this->getConnection()))
		{
			return false;
		}
		// Daten zur Person speichern

		if (!person::save()) {
			$this->errormsg.="Daten zur LVA konnten nicht gespeichert werden.";
			return false;
		}
		if ($this->new) {
			$qry="INSERT INTO tbl_lehrveranstaltung(lvnr,unr,einheit_kurzbz,".
				 "lektor,lehrfach_nr,studiengang_kz,fachbereich_id,semester,verband,".
				 "gruppe,raumtyp,raumtypalternativ,semesterstunden,stundenblockung,".
				 "wochenrythmus,start_kw,anmerkung)".
				 "values(".
				 "'".$this->lvnr."',".
				 "'".$this->unr."',".
				 "'".$this->einheit_kurzbz."',".
				 "'".$this->lektor."',".
				 (strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:NULL).",".
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
			$qry="UPDATE tbl_lehrveranstaltung ".
				 "SET lvnr='".$this->lvnr."',".
				 "unr='".$this->unr."',".
				 "einheit_kurzbz='".$this->einheit_kurzbz."',".
				 "lektor='".$this->lehrfach_nr."',".
				 "lehrfach_nr=".(strlen($this->lehrfach_nr)>0?$this->lehrfach_nr:NULL).",".
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
		//echo "<br>".$qry;
		if(!@pg_query($conn, $qry))
		{
			$this->errormsg=pg_errormessage($conn);
			return false;
		}
		return true;
		*/
	}


	/**
	 * Rueckgabewert ist ein Array mit den Ergebnissen. Bei Fehler false und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	 * @param string $uid    UserID
	 * @return variabel Array mit LVA; <b>false</b> bei Fehler
	 */
	function getBerechtigungen($uid)
	{
		// Berechtigungen holen
		$sql_query="SELECT * FROM public.tbl_benutzerberechtigung WHERE uid='$uid' AND (start<now() OR start IS NULL) AND (ende>now() OR ende IS NULL)";
	    //echo $sql_query;
		if(!$erg=@pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		//$num_rows=pg_numrows($erg);
		while($row=pg_fetch_object($erg))
		{
   			$b=new benutzer($this->conn);
			$b->userberechtigung_id=$row->benutzerberechtigung_id;
			$b->studiengang_kz=$row->studiengang_kz;
			$b->fachbereich_id=$row->fachbereich_kurzbz;
			$b->berechtigung_kurzbz=$row->berechtigung_kurzbz;
			$b->uid=$row->uid;
			$b->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$b->start=$row->start;
			if ($row->start!=null)
				$b->starttimestamp=mktime(0,0,0,substr($row->start,5,2),substr($row->start,8),substr($row->start,0,4));
			else
				$b->starttimestamp=null;
			$b->ende=$row->ende;
			if ($row->ende!=null)
				$b->endetimestamp=mktime(23,59,59,substr($row->ende,5,2),substr($row->ende,8),substr($row->ende,0,4));
			else
				$b->endetimestamp=null;
			$b->art=$row->art;
			$this->berechtigungen[]=$b;
		}
		return true;
	}
	

	function isBerechtigt($berechtigung,$studiengang_kz=null,$art=null)
	{
		$timestamp=time();
		foreach ($this->berechtigungen as $b)
		{
			if($berechtigung == $b->berechtigung_kurzbz && $studiengang_kz==null && $art==null)
			   if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
			   
			if	($berechtigung==$b->berechtigung_kurzbz 
			     && ($studiengang_kz==$b->studiengang_kz || $b->studiengang_kz==0) && $art==null)
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
					
			if	($berechtigung==$b->berechtigung_kurzbz 
			     && ($studiengang_kz==$b->studiengang_kz || $b->studiengang_kz==0) 
			     && strstr($b->art,$art))
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
		}
		return false;
	}

	/**
	* Gibt Array mit Kennzahlen der Studiengaenge sortiert zurueck.
	* Optional wird auf Berechtigung eingeschraenkt.
	* Wenn Berechtigung ueber alle Studiengaenge steht im ersten Feld 0.
	*/
	function getStgKz($berechtigung=null)
	{
		$studiengang_kz=array();
		$timestamp=time();
		
		foreach ($this->berechtigungen as $b)
			if	($berechtigung==$b->berechtigung_kurzbz || $berechtigung==null)
				$studiengang_kz[]=$b->studiengang_kz;
		$studiengang_kz=array_unique($studiengang_kz);
		sort($studiengang_kz);
		return $studiengang_kz;
	}
	
	/**
	 * Setzt die Studiensemester Variable
	 */
	function setVariableStudiensemester($user,$stsem)
	{
		//Vorhandende Variable aendern
		$qry = "Update public.tbl_variable SET wert='$stsem' WHERE uid='$user' AND name='semester_aktuell'";
		if($result = pg_query($this->conn,$qry))
		{
			if(pg_affected_rows($result)==0)
			{
				//Falls Variable nicht vorhanden ist eine neue anlegen
				$qry = "INSERT INTO public.tbl_variable(uid, name, wert) values('$user', 'semester_aktuell', '$stsem')";
				if(pg_query($this->conn,$qry))
					return true;
				else 
				{
					$this->errormsg.=pg_errormessage($this->conn);
					return false;
				}
			}
			else 
				return true;
		}
		else 
		{
			$this->errormsg.=pg_errormessage($this->conn);
			return false;
		}
	}
	
	function getpossibilities($variable)
	{
		$ret = array();
		
		switch($variable)
		{
			case 'semester_aktuell':
				$qry = "Select * from public.tbl_studiensemester order by start";
				if($result = pg_query($this->conn,$qry))
				{
					while($row=pg_fetch_object($result))
						$ret[] = $row->studiensemester_kurzbz;
				}
				break;
		}		
		return $ret;
	}
	
	function loadVariables($user)
	{			
		if(!($result=pg_query($this->conn, "SELECT * FROM public.tbl_variable WHERE uid='$user'")))
		{
			$this->errormsg.=pg_errormessage($this->conn);
			return false;
		}
		else
			$num_rows=@pg_numrows($result);
		
		while($row=pg_fetch_object($result))
		{				
			$this->variable->{$row->name}=$row->wert;			
		}
		
		if (!isset($this->variable->semester_aktuell))
		{
			if(!($result=pg_query($this->conn, 'SELECT * FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1')))
			{
				$this->errormsg.=pg_errormessage($this->conn);
				return false;
			}
			else
			{
				$num_rows=@pg_numrows($result);
				if ($num_rows>0)
				{
					$row=pg_fetch_object($result);
					$this->variable->semester_aktuell=$row->studiensemester_kurzbz;
				}
			}
		}
		
		if (!isset($this->variable->db_stpl_table))
			$this->variable->db_stpl_table='stundenplan';
			
		if (!isset($this->variable->fas_id))
			$this->variable->fas_id=0;
			
		if (!isset($this->variable->sleep_time))
			$this->variable->sleep_time=300;
			
		return true;
	}
}
?>