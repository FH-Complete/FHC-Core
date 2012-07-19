<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/person.class.php');

class benutzer extends person
{
	//Tabellenspalten
	public $uid;			// varchar(32)
	public $bnaktiv=true;	// boolean
	public $alias;			// varchar(256)
	public $bn_ext_id;
	public $result = array();
		
	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional einen Benutzer
	 * @param $uid            Benutzer der geladen werden soll (default=null)
	 */
	public function __construct($uid=null)
	{
		parent::__construct();
		
		if($uid != null)
			$this->load($uid);
	}
	
	/**
	 * Laedt Benutzer mit der uebergebenen ID
	 * @param $uid ID der Person die geladen werden soll
	 */
	public function load($uid)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($uid);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->uid = $row->uid;
				$this->bnaktiv = $this->db_parse_bool($row->aktiv);
				$this->alias = $row->alias;
				if(!person::load($row->person_id))
					return false;
				else 
					return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $uid";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden der Benutzerdaten";
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
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if($this->uid == '')
		{
			$this->errormsg = 'UID muss eingegeben werden '.$this->uid;
			return false;
		}
		if(mb_strlen($this->alias)>256)
		{
			$this->errormsg = 'Alias darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_bool($this->bnaktiv))
		{
			$this->errormsg = 'aktiv muss ein boolscher wert sein';
			return false;
		}
		
		if($this->alias!='')
		{
			$qry = "SELECT * FROM public.tbl_benutzer WHERE alias=".$this->db_add_param($this->alias)." AND uid!=".$this->db_add_param($this->uid);
			if($this->db_query($qry))
			{
				if($this->db_num_rows()>0)
				{
					$this->errormsg = 'Dieser Alias ist bereits vergeben';
					return false;
				}
			}
			else 
			{
				$this->errormsg = 'Fehler beim Pruefen des Alias';
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Speichert die Benutzerdaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * ansonsten der Datensatz mit $uid upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null, $saveperson=true)
	{
		if($saveperson)
		{
			//Personen Datensatz speichern
			if(!person::save())
				return false;
		}
		
		if($new==null)
			$new = $this->new;
			
		//Variablen auf Gueltigkeit pruefen
		if(!benutzer::validate())
			return false;
		
		if($new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_benutzer (uid, aktiv, alias, person_id, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES('.
			       $this->db_add_param($this->uid).",".
			       $this->db_add_param($this->bnaktiv,FHC_BOOLEAN).",".
			       $this->db_add_param($this->alias).",".
			       $this->db_add_param($this->person_id, FHC_INTEGER).",".
			       $this->db_add_param($this->insertamum).",".
			       $this->db_add_param($this->insertvon).",".
			       $this->db_add_param($this->updateamum).",".
			       $this->db_add_param($this->updatevon).",".
			       $this->db_add_param($this->bn_ext_id).");";
		}
		else
		{			
			//Wenn der Aktiv Status geaendert wurde, dann auch updateaktivamum und updateaktivvon setzen
			$upd='';
			$qry = "SELECT aktiv FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($this->uid);
			if($this->db_query($qry))
			{
				if($row = $this->db_fetch_object())
				{
					$aktiv = $this->db_parse_bool($row->aktiv);
					
					if($aktiv!=$this->bnaktiv)
						$upd =" updateaktivam=".$this->db_add_param($this->updateamum).", updateaktivvon=".$this->db_add_param($this->updatevon).",";
				}
			}
					
			$qry = 'UPDATE public.tbl_benutzer SET'.
			       ' aktiv='.$this->db_add_param($this->bnaktiv, FHC_BOOLEAN).','.
			       ' alias='.$this->db_add_param($this->alias).','.
			       ' person_id='.$this->db_add_param($this->person_id).','.
			       ' updateamum='.$this->db_add_param($this->updateamum).','.$upd.
			       ' updatevon='.$this->db_add_param($this->updatevon).
			       ' WHERE uid='.$this->db_add_param($this->uid).';';
		}
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes:'.$qry;
			return false;
		}
	}
	
	/**
	 * Prueft ob die UID bereits existiert
	 * @param uid
	 */
	public function uid_exists($uid)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE uid=".$this->db_add_param($uid);
		
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
			{
				$this->errormsg = '';
				return true;
			}
			else 
			{
				$this->errormsg = '';
				return false;
			}
				
		}
		else 
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
		
	}
	
	/**
	 * Prueft ob der alias bereits existiert
	 * @param $alias
	 */
	public function alias_exists($alias)
	{
		$qry = "SELECT * FROM public.tbl_benutzer WHERE alias=".$this->db_add_param($alias);
		
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
			{
				$this->errormsg = '';
				return true;
			}
			else 
			{
				$this->errormsg = '';
				return false;
			}
				
		}
		else 
		{
			$this->errormsg = 'Fehler bei DatenbankAbfrage';
			return false;
		}
	}
	
	public function search($searchItems)
	{
		$qry = "SELECT * FROM (SELECT
					distinct on (uid) vorname, nachname, uid, titelpre, titelpost,alias,
					(SELECT UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz)
					 FROM public.tbl_student JOIN public.tbl_studiengang USING(studiengang_kz)
					 WHERE student_uid=tbl_benutzer.uid) as studiengang,
					 (SELECT studiengang_kz FROM public.tbl_student
					 WHERE student_uid=tbl_benutzer.uid) as studiengang_kz,
					(SELECT tbl_kontakt.kontakt || ' - ' ||telefonklappe 
					FROM public.tbl_mitarbeiter
					LEFT JOIN public.tbl_kontakt USING(standort_id) 
					WHERE 
						mitarbeiter_uid=tbl_benutzer.uid
						AND (tbl_kontakt.kontakttyp='telefon' OR tbl_kontakt.kontakttyp is null)
						) as klappe
				FROM
					public.tbl_person 
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					tbl_benutzer.aktiv
					AND (";
		
		$qry.=" lower(vorname || ' ' || nachname) like lower('%".addslashes(implode(' ',$searchItems))."%')"; 
		$qry.=" OR lower(nachname || ' ' || vorname) like lower('%".addslashes(implode(' ',$searchItems))."%')";
		$qry.=" OR lower(uid) like lower('%".addslashes(implode(' ',$searchItems))."%')";
		
		foreach($searchItems as $value)
		{
			$qry.=" OR lower(uid) = lower('".addslashes($value)."')"; 
		}
		$qry.=")) a ORDER BY nachname, vorname";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzer();
				
				$obj->titelpre = $row->titelpre;
				$obj->vorname  = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpost = $row->titelpost;
				$obj->uid = $row->uid;
				$obj->studiengang = $row->studiengang;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->telefonklappe = $row->klappe;
				$obj->alias = $row->alias;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Benutzer einer Person
	 * @param $person_id
	 * @param $aktiv optional wenn true werden nur aktive benutzer geladen, sonst alle
	 */
	function getBenutzerFromPerson($person_id, $aktiv=true)
	{
		$qry = "SELECT 
					person_id, titelpre, vorname, nachname, titelpost, uid
				FROM 
					public.tbl_benutzer
					JOIN public.tbl_person USING(person_id)
				WHERE
					person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($aktiv)
			$qry.=" AND tbl_benutzer.aktiv=true ";
		
		$qry .= "ORDER BY tbl_person.insertamum";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzer();
				
				$obj->person_id = $row->person_id;
				$obj->titelpre = $row->titelpre;
				$obj->vorname  = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpost = $row->titelpost;
				$obj->uid = $row->uid;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>