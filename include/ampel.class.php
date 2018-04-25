<?php
/* Copyright (C) 2011 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and 
 */
/**
 * Klasse Ampel
 *  
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class ampel extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $ampel_id;		// bigint
	public $kurzbz;			// varchar(64)
	public $beschreibung = array();	// text[]
	public $benutzer_select;// text
	public $deadline;		// date
	public $vorlaufzeit;	// smallint
	public $verfallszeit;	// smallint
	public $email;			// boolean
	public $verpflichtend;	// boolean
	public $buttontext;		// varchar(64)[]
	public $insertamum;		// timestamp
	public $insertvon;		// varchar(32)
	public $updateamum;		// timestamp
	public $updatevon;		// varchar(32)
	
	public $ampel_benutzer_id;	// bigint
	public $uid;				// varchar(32)
	 
	/**
	 * Konstruktor - Laedt optional eine Ampel
	 * @param $amepl_id
	 */
	public function __construct($ampel_id=null)
	{
		parent::__construct();
				
		if(!is_null($ampel_id))
			$this->load($ampel_id);
	}

	/**
	 * Laedt eine Ampel mit der uebergebenen ID
	 * 
	 * @param $ampel_id
	 * @return boolean
	 */
	public function load($ampel_id)
	{
		if(!is_numeric($ampel_id))
		{
			$this->errormsg = 'Ampel ID ist ungueltig';
			return false;
		}
		
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$buttontext = $sprache->getSprachQuery('buttontext');
		
		$qry = "SELECT *,".$beschreibung.", ".$buttontext." FROM public.tbl_ampel WHERE ampel_id=".$this->db_add_param($ampel_id, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->ampel_id = $row->ampel_id;
				$this->kurzbz = $row->kurzbz;
				$this->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$this->benutzer_select = $row->benutzer_select;
				$this->deadline = $row->deadline;
				$this->vorlaufzeit = $row->vorlaufzeit;
				$this->verfallszeit = $row->verfallszeit;
				$this->email = $row->email;
				$this->verpflichtend = $row->verpflichtend;
				$this->buttontext = $sprache->parseSprachResult('buttontext', $row);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;

				return true;
			}
			else
			{
				$this->errormsg = 'Ampel mit dieser ID exisitert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Ampel';
			return false;
		}
	}
	
	/**
	 * Laedt alle vorhandenen Ampeln
	 * @param aktiv lade nur aktive Ampeln
	 */
	public function getAll($aktiv=false)
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$buttontext = $sprache->getSprachQuery('buttontext');
		
		$qry = "SELECT *,".$beschreibung.", ".$buttontext." FROM public.tbl_ampel";
		if($aktiv)
		{
			$qry .= " WHERE (NOW()>(deadline-(vorlaufzeit || ' days')::interval)::date)";
			$qry .= " AND (NOW()<(deadline+(verfallszeit || ' days')::interval)::date)";
		}
		$qry .= " ORDER BY deadline";
						
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ampel();
					
				$obj->ampel_id = $row->ampel_id;
				$obj->kurzbz = $row->kurzbz;
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$obj->benutzer_select = $row->benutzer_select;
				$obj->deadline = $row->deadline;
				$obj->vorlaufzeit = $row->vorlaufzeit;
				$obj->verfallszeit = $row->verfallszeit;
				$obj->email = $this->db_parse_bool($row->email);
				$obj->verpflichtend = $this->db_parse_bool($row->verpflichtend);
				$obj->buttontext = $sprache->parseSprachResult('buttontext', $row);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
					
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft ob ein User eine Ampel schon bestaetigt hat
	 * 
	 * @param $user
	 * @param $ampel_id
	 * @return boolean
	 */
	public function isBestaetigt($user, $ampel_id)
	{
		$qry = "SELECT 1 FROM public.tbl_ampel_benutzer_bestaetigt WHERE ampel_id=".$this->db_add_param($ampel_id, FHC_INTEGER)." AND uid=".$this->db_add_param($user);
	
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Prueft ob ein User zu einer Ampel zugeteilt ist
	 * @param $user
	 * @param $benutzer_select
	 */
	public function isZugeteilt($user, $benutzer_select)
	{
		$qry = "SELECT CASE WHEN ".$this->db_add_param($user)." IN (".$benutzer_select.") THEN true ELSE false END as zugeteilt";

		if($result_zugeteilt = $this->db_query($qry))
		{
			if($row_zugeteilt = $this->db_fetch_object($result_zugeteilt))
			{
				if($row_zugeteilt->zugeteilt=='t')
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle aktuellen Ampeln eines Users
	 * @param string $user User, dessen Ampeln geladen werden sollen
	 * @param boolean $bestaetigt Default false
	 * 				wenn true, werden alle Ampeln geladen 
	 * 				wenn false, werden nur die Ampeln geladen die noch NICHT bestaetigt wurden
	 */
	public function loadUserAmpel($user, $bestaetigt=false)   
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$buttontext = $sprache->getSprachQuery('buttontext');		
		
		//all ampeln except where now is before the vorlaufzeit
		$qry = "SELECT *,".$beschreibung.", ".$buttontext." FROM public.tbl_ampel";
		
		//only ampeln that are not confirmed
		if(!$bestaetigt)
		{
			$qry.=" WHERE NOT EXISTS
						(SELECT ampel_id 
						 FROM public.tbl_ampel_benutzer_bestaetigt 
						 WHERE uid=".$this->db_add_param($user)." AND ampel_id=tbl_ampel.ampel_id)";
		}

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				if($this->isZugeteilt($user, $row->benutzer_select))
				{
					$obj = new ampel();
					
					$obj->ampel_id = $row->ampel_id;
					$obj->kurzbz = $row->kurzbz;
					$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
					$obj->benutzer_select = $row->benutzer_select;
					$obj->deadline = $row->deadline;
					$obj->vorlaufzeit = $row->vorlaufzeit;
					$obj->verfallszeit = $row->verfallszeit;
					$obj->email = $row->email;
					$obj->verpflichtend = $row->verpflichtend;
					$obj->buttontext = $sprache->parseSprachResult('buttontext', $row);
					$obj->insertamum = $row->insertamum;
					$obj->insertvon = $row->insertvon;
					
					$this->result[] = $obj;
				}
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
	 * Prueft die Daten vor dem Speichern
	 * @return boolean
	 */
	public function validate()
	{
		$benutzer_select = mb_strtolower($this->benutzer_select);
		
		if(mb_strstr($benutzer_select, 'update ') || mb_strstr($benutzer_select, 'insert ') || mb_strstr($benutzer_select, 'delete '))
		{
			$this->errormsg = 'Der Benutzer Select darf nur Selects beinhalten';
			return false;
		}
		
		if(!mb_strstr($benutzer_select,'select '))
		{
			$this->errormsg = 'Der Benutzer Select muss einen Select-Befehl beinhalten';
			return false;
		}
		return true;
	}
	
	/**
	 * Speichert eine Ampel
	 * @param $new
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if(!$this->validate())
			return false;
			
		$sprache = new sprache();
		$sprache->loadIndexArray();
			
		if($this->new)
		{
			$qry = "BEGIN;INSERT INTO public.tbl_ampel (kurzbz, ";
			
			foreach($this->beschreibung as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" beschreibung[$idx],";
			}
			foreach($this->buttontext as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" buttontext[$idx],";
			}
			
			$qry.=" benutzer_select, deadline, 
					vorlaufzeit, verfallszeit, email, verpflichtend, insertamum, insertvon , updateamum, updatevon) VALUES(".
					$this->db_add_param($this->kurzbz).',';
			reset($this->beschreibung);
			foreach($this->beschreibung as $key=>$value)
				$qry.=$this->db_add_param($value).',';
			reset($this->buttontext);
			foreach($this->buttontext as $key=>$value)
				$qry.=$this->db_add_param($value).',';
								
			$qry .= $this->db_add_param($this->benutzer_select).','.
					$this->db_add_param($this->deadline).','.
					$this->db_add_param($this->vorlaufzeit).','.
					$this->db_add_param($this->verfallszeit).','.
					$this->db_add_param($this->email, FHC_BOOLEAN).','.
					$this->db_add_param($this->verpflichtend, FHC_BOOLEAN).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_ampel SET'.
					' kurzbz = '.$this->db_add_param($this->kurzbz).',';
			reset($this->beschreibung);
			foreach($this->beschreibung as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=' beschreibung['.$idx.'] = '.$this->db_add_param($value).',';
			}
			reset($this->buttontext);
			foreach($this->buttontext as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=' buttontext['.$idx.'] = '.$this->db_add_param($value).',';
			}
			
			$qry.=  ' benutzer_select = '.$this->db_add_param($this->benutzer_select).','.
					' deadline = '.$this->db_add_param($this->deadline).','.
					' vorlaufzeit = '.$this->db_add_param($this->vorlaufzeit).','.
					' verfallszeit = '.$this->db_add_param($this->verfallszeit).','.
					' email = '.$this->db_add_param($this->email, FHC_BOOLEAN).','.
					' verpflichtend = '.$this->db_add_param($this->verpflichtend, FHC_BOOLEAN).','.
					' updateamum ='.$this->db_add_param($this->updateamum).','.
					' updatevon ='.$this->db_add_param($this->updatevon).
					' WHERE ampel_id='.$this->db_add_param($this->ampel_id, FHC_INTEGER).';';					
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.seq_ampel_ampel_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->ampel_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
			}
			else
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht eine Ampel
	 
	 * @param $ampel_id
	 */
	public function delete($ampel_id)
	{
		if(!is_numeric($ampel_id))
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM public.tbl_ampel WHERE ampel_id=".$this->db_add_param($ampel_id);
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Ampel';
			return false;
		}
	}
	
	/**
	 * Loescht eine Bestaetigung einer Ampel
	
	 * @param $ampel_id
	 */
	public function deleteAmpelBenutzer($ampel_benutzer_bestaetigt_id)
	{
		if(!is_numeric($ampel_benutzer_bestaetigt_id))
		{
			$this->errormsg='ID ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM public.tbl_ampel_benutzer_bestaetigt WHERE ampel_benutzer_bestaetigt_id=".$this->db_add_param($ampel_benutzer_bestaetigt_id);
	
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Bestaetigung';
			return false;
		}
	}
	
	/**
	 * Bestaetigt die Ampel eines Users
	 * @param $user
	 * @param $ampel_id
	 * @return boolean
	 */
	public function bestaetigen($user, $ampel_id)
	{
		$qry = 'INSERT INTO public.tbl_ampel_benutzer_bestaetigt(ampel_id, uid, insertamum, insertvon) VALUES('.
				$this->db_add_param($ampel_id, FHC_INTEGER).','.
				$this->db_add_param($user).','.
				'now(),'.
				$this->db_add_param($user).');';
				
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt Ampeln und Mitarbeiter zu einer OE/Ampel
	 * @param $oe_arr
	 * @param $ampel_id
	 */
	public function loadAmpelMitarbeiter($oe_arr, $ampel_id)
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		$buttontext = $sprache->getSprachQuery('buttontext');
		
		if(!is_numeric($ampel_id) && $ampel_id!='')
		{
			$this->errormsg = 'Ampel ID ist ungueltig';
			return false;
		}
		
		// Ampeln holen
		$qry = "SELECT *,".$beschreibung.", ".$buttontext." FROM public.tbl_ampel";
		if($ampel_id!='')
			$qry.=" WHERE ampel_id=".$this->db_add_param($ampel_id, FHC_INTEGER);
			
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				// Alle Mitarbeiter/Studenten dazu holen
				$qry = "SELECT 
							distinct on (tbl_ampel_benutzer_bestaetigt.ampel_benutzer_bestaetigt_id, a.uid) *,
							tbl_ampel_benutzer_bestaetigt.insertamum,tbl_ampel_benutzer_bestaetigt.insertvon							
						FROM 
							(".$row->benutzer_select.") a 
							JOIN campus.vw_benutzer USING(uid)
							LEFT JOIN public.tbl_benutzerfunktion USING(uid)
							LEFT JOIN public.tbl_ampel_benutzer_bestaetigt on(public.tbl_ampel_benutzer_bestaetigt.uid=a.uid AND ampel_id=".$this->db_add_param($row->ampel_id, FHC_INTEGER).")
						WHERE
							(tbl_ampel_benutzer_bestaetigt.ampel_id is null OR tbl_ampel_benutzer_bestaetigt.ampel_id=".$this->db_add_param($row->ampel_id).")
							AND
							(
								(funktion_kurzbz='oezuordnung' AND oe_kurzbz in(".$this->implode4SQL($oe_arr)."))
								OR
								(funktion_kurzbz is null 
								 AND (SELECT oe_kurzbz FROM 
								 	  public.tbl_studiengang JOIN public.tbl_student USING(studiengang_kz) 
								 	  WHERE vw_benutzer.uid=tbl_student.student_uid)
								 	 in(".$this->implode4SQL($oe_arr).")
								)
							)
						";

				if($result_ma = $this->db_query($qry))
				{
					while($row_ma = $this->db_fetch_object($result_ma))
					{
						$obj = new ampel();
						
						$obj->ampel_id = $row->ampel_id;
						$obj->kurzbz = $row->kurzbz;
						$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
						$obj->benutzer_select = $row->benutzer_select;
						$obj->deadline = $row->deadline;
						$obj->vorlaufzeit = $row->vorlaufzeit;
						$obj->verfallszeit = $row->verfallszeit;
						$obj->email = $row->email;
						$obj->verpflichtend = $row->verpflichtend;
						$obj->buttontext = $sprache->parseSprachResult('buttontext', $row);
						$obj->insertamum = $row->insertamum;
						$obj->insertvon = $row->insertvon;
						
						$obj->vorname = $row_ma->vorname;
						$obj->nachname = $row_ma->nachname;
						$obj->titelpre = $row_ma->titelpre;
						$obj->titelpost = $row_ma->titelpost;
						$obj->oe_kurzbz = $row_ma->oe_kurzbz;
						
						$obj->insertamum_best = $row_ma->insertamum;
						$obj->insertvon_best = $row_ma->insertvon;
						$obj->ampel_benutzer_bestaetigt_id = $row_ma->ampel_benutzer_bestaetigt_id;
						
						$this->result[] = $obj;
					}
				}
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
