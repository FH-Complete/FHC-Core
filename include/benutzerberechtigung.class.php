<?php
/* Copyright (C) 2009 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/organisationseinheit.class.php');
require_once(dirname(__FILE__).'/studiengang.class.php');
require_once(dirname(__FILE__).'/fachbereich.class.php');
require_once(dirname(__FILE__).'/functions.inc.php');

class benutzerberechtigung extends basis_db
{
	public $new;      // boolean
	public $berechtigungen = array(); // benutzerberechtigung Objekt

	//Tabellenspalten
	public $benutzerberechtigung_id;	// serial
	public $uid;						// varchar(32)
	public $funktion_kurzbz;			// varchar(16)
	public $rolle_kurzbz;				// varchar(32)
	public $berechtigung_kurzbz;		// varchar(16)
	public $art;						// varchar(5)
	public $oe_kurzbz;					// varchar(32)
	public $studiensemester_kurzbz;		// varchar(16)
	public $start;						// date
	public $ende;						// date
	public $negativ;					// boolean
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;

	public $starttimestamp;
	public $endetimestamp;
	
	//Attribute des Mitarbeiters
	public $fix;
	public $lektor;

	/**
	 * Konstruktor - Laedt optional eine Berechtigung
	 * @param $benutzerberechtigung_id
	 */
	public function __construct($benutzerberechtigung_id=null)
	{
		parent::__construct();
		
		if($benutzerberechtigung_id!=null)
			$this->load($benutzerberechtigung_id);
	}

	/**
	 * Laedt eine Benutzerberechtigung
	 * @param benutzerberechtigung_id
	 */
	public function load($benutzerberechtigung_id)
	{
		if(!is_numeric($benutzerberechtigung_id) || $benutzerberechtigung_id=='')
		{
			$this->errormsg = 'benutzerberechtigung_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM system.tbl_benutzerrolle WHERE benutzerberechtigung_id='$benutzerberechtigung_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->benutzerberechtigung_id = $benutzerberechtigung_id;
				$this->rolle_kurzbz = $row->rolle_kurzbz;
				$this->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$this->uid = $row->uid;
				$this->funktion_kurzbz = $row->funktion_kurzbz;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->art = $row->art;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->start = $row->start;
				$this->ende = $row->ende;
				$this->negativ = ($row->negativ=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				
				return true;
			}
			else 
			{
				$this->errormsg = 'Es wurde kein Eintrag mit dieser ID gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Berechtigung';
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->art)>16)
		{
			$this->errormsg = 'Art darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if(mb_strlen($this->berechtigung_kurzbz)>32)
		{
			$this->errormsg = 'Berechtigung_kurzbz darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		
		if($this->rolle_kurzbz=='' && $this->berechtigung_kurzbz=='')
		{
			$this->errormsg = 'Es muss entweder eine Rolle oder eine Berechtigung angegeben werden';
			return false;
		}
		
		if($this->rolle_kurzbz!='' && $this->berechtigung_kurzbz!='')
		{
			$this->errormsg = 'Rolle und Berechtigung kann nicht gleichzeitig angegeben werden';
			return false;
		}
		
		if($this->uid=='' && $this->funktion_kurzbz=='')
		{
			$this->errormsg = 'Ess muss entweder eine UID oder eine Funktion_kurzbz angegeben werden';
			return false;
		}
		
		if($this->uid!='' && $this->funktion_kurzbz!='')
		{
			$this->errormsg = 'UID und Funktion_kurzbz kann nicht gleichzeitig angegeben werden';
			return false;
		}
		
		if($this->funktion_kurzbz!='' && $this->oe_kurzbz!='')
		{
			$this->errormsg = 'Wenn eine Funktion_kurzbz angegeben wird, darf keine Organisationseinheit eingetragen sein';
			return false;
		}
		
		if($this->art=='')
		{
			$this->errormsg = 'Art darf nicht leer sein';
			return false;
		}
		return true;
	}
	
	/**
	 * Speichert Benutzerberechtigung in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO system.tbl_benutzerrolle (rolle_kurzbz, berechtigung_kurzbz, uid, funktion_kurzbz, 
						oe_kurzbz, art, studiensemester_kurzbz, start, ende, negativ, updateamum, updatevon, insertamum, insertvon)
			        VALUES('.$this->addslashes($this->rolle_kurzbz).','.
					$this->addslashes($this->berechtigung_kurzbz).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->funktion_kurzbz).','.
					$this->addslashes($this->oe_kurzbz).','.
					$this->addslashes($this->art).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->start).','.
					$this->addslashes($this->ende).','.
					($this->negativ?'true':'false').','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).');';
		}
		else
		{
			$qry = 'UPDATE system.tbl_benutzerrolle SET'.
				   ' rolle_kurzbz='.$this->addslashes($this->rolle_kurzbz).','.
				   ' berechtigung_kurzbz='.$this->addslashes($this->berechtigung_kurzbz).','.
				   ' uid='.$this->addslashes($this->uid).','.
				   ' funktion_kurzbz='.$this->addslashes($this->funktion_kurzbz).','.
				   ' oe_kurzbz='.$this->addslashes($this->oe_kurzbz).','.
			       ' art='.$this->addslashes($this->art).','.
			       ' studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).','.
			       ' start='.$this->addslashes($this->start).','.
			       ' ende='.$this->addslashes($this->ende).','.
			       ' negativ='.($this->negativ?'true':'false').','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE benutzerberechtigung_id='".addslashes($this->benutzerberechtigung_id)."'";
		}

		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Benutzerberechtigung:'.$qry.$this->db_last_error();
			return false;
		}
	}

	/**
	 * Loescht einen Eintrag aus der Tabelle benutzerrolle
	 * @param benutzerberechtigung_id
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function delete($benutzerberechtigung_id)
	{
		if(!is_numeric($benutzerberechtigung_id) || $benutzerberechtigung_id=='')
		{
			$this->errormsg = 'benutzerberechtigung_id ist ungültig';
			return false;
		}
		
		// Berechtigungen loeschen
		$sql_query="DELETE FROM system.tbl_benutzerrolle where benutzerberechtigung_id = '$benutzerberechtigung_id'";

		if(!$this->db_query($sql_query))
		{
			$this->errormsg='Fehler beim Löschen';
			return false;
		}
		return true;
	}

	/**
	 * Laedt die Benutzerrollen zu einer UID
	 *
	 * @param unknown_type $uid
	 */
	public function loadBenutzerRollen($uid=null, $funktion_kurzbz=null)
	{
		$qry = 'SELECT * FROM system.tbl_benutzerrolle WHERE ';
		
		if(!is_null($uid))
			$qry.= " uid='".addslashes($uid)."'";
		elseif(!is_null($funktion_kurzbz))
			$qry.= " funktion_kurzbz='".addslashes($funktion_kurzbz)."'";
		else 
		{
			$this->errormsg = 'Entweder UID oder funktion_kurzbz muss uebergeben werden';
			return false;
		}
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new benutzerberechtigung();
				
				$obj->benutzerberechtigung_id = $row->benutzerberechtigung_id;
				$obj->rolle_kurzbz = $row->rolle_kurzbz;
				$obj->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$obj->uid = $row->uid;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->art = $row->art;
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				$obj->negativ = ($row->negativ=='t'?true:false);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$this->berechtigungen[] = $obj;
			}
		}
	}
	
	/**
	 * Laedt die Berechtigungen eines Users
	 * @param $uid
	 * @param $all wenn $all auf true gesetzt wird, werden auch bereits abgelaufene
	 *              berechtigungen geladen.
	 */
	public function getBerechtigungen($uid,$all=false)
	{
		// Berechtigungen holen
		
		$qry = "SELECT 
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon
				FROM 
					system.tbl_benutzerrolle JOIN system.tbl_berechtigung USING(berechtigung_kurzbz) 
				WHERE uid='".addslashes($uid)."'
				
				UNION
				
				SELECT 
					benutzerberechtigung_id, tbl_benutzerrolle.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_berechtigung.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_rolleberechtigung.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon
				FROM 
					system.tbl_benutzerrolle JOIN system.tbl_rolle USING(rolle_kurzbz) 
					JOIN system.tbl_rolleberechtigung USING(rolle_kurzbz) 
					JOIN system.tbl_berechtigung ON(tbl_rolleberechtigung.berechtigung_kurzbz=tbl_berechtigung.berechtigung_kurzbz)
				WHERE uid='".addslashes($uid)."'
				
				UNION
				
				SELECT 
					benutzerberechtigung_id, tbl_benutzerfunktion.uid, tbl_benutzerrolle.funktion_kurzbz,
					tbl_benutzerrolle.rolle_kurzbz, tbl_benutzerrolle.berechtigung_kurzbz, tbl_benutzerrolle.art, tbl_benutzerrolle.art art1,
					tbl_benutzerrolle.oe_kurzbz, tbl_benutzerrolle.studiensemester_kurzbz, tbl_benutzerrolle.start,
					tbl_benutzerrolle.ende, tbl_benutzerrolle.negativ, tbl_benutzerrolle.updateamum, tbl_benutzerrolle.updatevon,
					tbl_benutzerrolle.insertamum, tbl_benutzerrolle.insertvon
				FROM 
					system.tbl_benutzerrolle JOIN public.tbl_benutzerfunktion USING(funktion_kurzbz)
				WHERE tbl_benutzerfunktion.uid='".addslashes($uid)."'
				ORDER BY negativ DESC";
					
		if(!$this->db_query($qry))
		{
			$this->errormsg='Fehler beim Laden der Berechtigungen';
			return false;
		}

		while($row=$this->db_fetch_object())
		{
			//wenn die Berechtigung an einer Organisationseinheit haengt, dann werden
			//auch die Berechtigungen fuer die darunterliegenden Organisationseinheiten angelegt
			if($row->oe_kurzbz!='')
			{
				$organisationseinheit = new organisationseinheit();
				$oes = $organisationseinheit->getChilds($row->oe_kurzbz);
			}
			else 
			{
				$oes[]=$row->oe_kurzbz;	
			}
			
			foreach ($oes as $oe_kurzbz)
			{
	   			$b=new benutzerberechtigung();
	
	   			$b->benutzerberechtigung_id = $row->benutzerberechtigung_id;
	   			$b->uid=$row->uid;
	   			$b->funktion_kurzbz=$row->funktion_kurzbz;
	   			$b->rolle_kurzbz = $row->rolle_kurzbz;
	   			$b->berechtigung_kurzbz = $row->berechtigung_kurzbz;
				$b->art=intersect($row->art, $row->art1);
				$b->oe_kurzbz = $oe_kurzbz;
				$b->studiensemester_kurzbz=$row->studiensemester_kurzbz;
				$b->start=$row->start;
				if ($row->start!=null)
					$b->starttimestamp=mktime(0,0,0,mb_substr($row->start,5,2),mb_substr($row->start,8),mb_substr($row->start,0,4));
				else
					$b->starttimestamp=null;
				$b->ende=$row->ende;
				if ($row->ende!=null)
					$b->endetimestamp=mktime(23,59,59,mb_substr($row->ende,5,2),mb_substr($row->ende,8),mb_substr($row->ende,0,4));
				$b->negativ = ($row->negativ=='t'?true:false);
				$b->updateamum = $row->updateamum;
				$b->updatevon = $row->updatevon;
				$b->insertamum = $row->insertamum;
				$b->insertvon = $row->insertvon;
				
				$this->berechtigungen[]=$b;
			}
		}

		// Attribute des Mitarbeiters holen
		$sql_query="SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='".addslashes($uid)."'";
		if(!$this->db_query($sql_query))
		{
			$this->errormsg='Fehler beim Laden der Berechtigungen';
			return false;
		}
		while($row=$this->db_fetch_object())
		{
   			if ($row->fixangestellt=='t')
   				$this->fix=true;
   			else
   				$this->fix=false;

			if ($row->lektor=='t')
   				$this->lektor=true;
   			else
   				$this->lektor=false;
		}
		return true;
	}

	/**
	 * Prueft ob die Berechtigung vorhanden ist. Vor der Verwendung muss die
	 * Funktion getBerechtigungen aufgerufen werden.
	 *
	 * @param $berechtigung
	 * @param $oe_kurzbz 		
	 * 		derzeit kann hier noch die Studiengangskennzahl uebergeben werden, 
	 * 		dies wird in Zukunft aber nicht mehr moeglich sein
	 * @param $art			suid (select|update|insert|delete)
	 * @param $fachbereich_kurzbz DEPRECATED
	 * @return true wenn eine Berechtigung entspricht.
	 */
	public function isBerechtigt($berechtigung_kurzbz,$oe_kurzbz=null,$art=null, $fachbereich_kurzbz=null)
	{
		$timestamp=time();
		
		//Studiengang
		if(is_numeric($oe_kurzbz))
		{
			//Studiengang
			$stg = new studiengang($oe_kurzbz);
			$oe_kurzbz = $stg->oe_kurzbz;
		}
		
		//Fachbereich
		if(!is_null($fachbereich_kurzbz))
		{
			$fb = new fachbereich($fachbereich_kurzbz);
			$oe_kurzbz = $fb->oe_kurzbz;
		}
		
		foreach ($this->berechtigungen as $b)
		{
			//Pruefen ob eine negativ-Berechtigung vorhanden ist
			if($b->berechtigung_kurzbz==$berechtigung_kurzbz && $b->negativ && $oe_kurzbz==$b->oe_kurzbz)
			{
				if (($timestamp>$b->starttimestamp || $b->starttimestamp==null) 
				 && ($timestamp<$b->endetimestamp || $b->endetimestamp==null))
				{
					return false;
				}
			}
		
			if($b->berechtigung_kurzbz==$berechtigung_kurzbz
			   && (is_null($art) || mb_strstr($b->art, $art))
			   && (is_null($oe_kurzbz) || $oe_kurzbz==$b->oe_kurzbz))
			{
				if (($timestamp>$b->starttimestamp || $b->starttimestamp==null) 
				 && ($timestamp<$b->endetimestamp || $b->endetimestamp==null))
				{
						return true;
				}
			}
		}

		//wenn ein Doppelpunkt vorkommt, pruefen ob das Uebergeordnete vorhanden ist
		if($pos=mb_strpos($berechtigung_kurzbz,':')===false)
		{
			return false;
		}
		else 
		{
			return $this->isBerechtigt(substr($berechtigung_kurzbz,0,$pos-1), $oe_kurzbz, $art, $fachbereich_kurzbz);
		}
	}

	/**
	 * Prueft ob die Person Fixangestellt ist
	 * @return true wenn ja, false wenn nein
	 */
	public function isFix()
	{
		if ($this->fix)
			return true;
		else
			return false;
	}

	/**
	 * Gibt Array mit den Studiengangskennzahlen zurueck fuer welche die
	 * Person eine Berechtigung besitzt.
	 * Optional wird auf Berechtigung eingeschraenkt.
	 */
	public function getStgKz($berechtigung_kurzbz=null)
	{
		$studiengang_kz=array();
		$timestamp=time();
		$in='';
		$not='';
		$all=false;
		
		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || $berechtigung_kurzbz==null)
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
						$not .="'".addslashes($b->oe_kurzbz)."',";
					else 
						return array();
				}
				else 
				{
					if(!is_null($b->oe_kurzbz))
						$in .= "'".addslashes($b->oe_kurzbz)."',"; 
					else 
					{
						//Wenn NULL dann berechtigung auf alles
						$all = true;
						break;
					}
				}
			}
		}
			
		if(!$all)
		{
			if($in=='')
				return array();
			else
				$in = ' AND oe_kurzbz IN('.mb_substr($in,0, mb_strlen($in)-1).')';
		}
		else 
		{
			$in='';
			$not='';
		}
		
		if($not!='')
			$not = ' AND oe_kurzbz NOT IN('.mb_substr($not,0, mb_strlen($not)-1).')';
		
		$qry = "SELECT studiengang_kz FROM public.tbl_studiengang WHERE 1=1 $in $not";
		
		if($this->db_query($qry))
			while($row = $this->db_fetch_object())
				$studiengang_kz[]=$row->studiengang_kz;	
					
		$studiengang_kz=array_unique($studiengang_kz);
		sort($studiengang_kz);
		return $studiengang_kz;
	}

	/**
	 * Gibt eine Array mit den Fachbereichen/Instituten zurueck
	 *
	 * @param $berechtigung
	 * @return array mit fachbereichen
	 */
	public function getFbKz($berechtigung_kurzbz=null)
	{
		$fachbereich_kurzbz=array();
		$timestamp=time();
		$in='';
		$not='';
		$all=false;
		
		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || $berechtigung_kurzbz==null)
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
						$not .="'".addslashes($b->oe_kurzbz)."',";
					else 
						return array();
				}
				else 
				{
					if(!is_null($b->oe_kurzbz))
						$in .= "'".addslashes($b->oe_kurzbz)."',"; 
					else 
					{
						//Wenn NULL dann berechtigung auf alles
						$all = true;
						break;
					}
				}
			}
		}
			
		if(!$all)
		{
			if($in=='')
				return array();
			else
				$in = ' AND oe_kurzbz IN('.mb_substr($in,0, mb_strlen($in)-1).')';
		}
		else 
		{
			$in='';
		}
		
		if($not!='')
			$not = ' AND oe_kurzbz NOT IN('.mb_substr($not,0, mb_strlen($not)-1).')';
		
		$qry = "SELECT fachbereich_kurzbz FROM public.tbl_fachbereich WHERE 1=1 $in $not";
		
		if($this->db_query($qry))
			while($row = $this->db_fetch_object())
				$fachbereich_kurzbz[]=$row->fachbereich_kurzbz;	
					
		$fachbereich_kurzbz=array_unique($fachbereich_kurzbz);
		sort($fachbereich_kurzbz);
		return $fachbereich_kurzbz;
	}
	
	/**
	 * Gibt Array mit den Organisationseinheiten zurueck fuer welche die
	 * Person eine Berechtigung besitzt.
	 * Optional wird auf Berechtigung eingeschraenkt.
	 */
	public function getOEkurzbz($berechtigung_kurzbz=null)
	{
		$oe_kurzbz=array();
		$timestamp=time();
		$in='';
		$not='';
		$all=false;
		
		foreach ($this->berechtigungen as $b)
		{
			if	(($berechtigung_kurzbz==$b->berechtigung_kurzbz || $berechtigung_kurzbz==null)
				&& (($timestamp>$b->starttimestamp || $b->starttimestamp==null) && ($timestamp<$b->endetimestamp || $b->endetimestamp==null)))
			{
				if($b->negativ)
				{
					//Negativ-Recht
					if(!is_null($b->oe_kurzbz))
						$not .="'".addslashes($b->oe_kurzbz)."',";
					else 
						return array();
				}
				else 
				{
					$oe_kurzbz[] = $b->oe_kurzbz;
				}
			}
		}
		
		$studiengang_kz=array_unique($oe_kurzbz);
		sort($oe_kurzbz);
		return $oe_kurzbz;
	}

}
?>