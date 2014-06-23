<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
require_once(dirname(__FILE__).'/basis_db.class.php');

class notiz extends basis_db
{
	public $new;
	public $result=array();

	//Tabellenspalten
	public $notiz_id;
	public $titel;
	public $text;
	public $verfasser_uid;
	public $bearbeiter_uid;
	public $start;
	public $ende;
	public $erledigt;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	/**
	 * Konstruktor
	 * @param $notiz_id
	 */
	public function __construct($notiz_id = null)
	{
		parent::__construct();

		if($notiz_id != null)
			$this->load($notiz_id);
	}

	/**
	 * Laedt eine Notiz
	 * @param  $notiz_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($notiz_id)
	{
		if(!is_numeric($notiz_id))
		{
			$this->errormsg = 'NotizID ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_notiz WHERE notiz_id=".$this->db_add_param($notiz_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->notiz_id=$row->notiz_id;
				$this->titel=$row->titel;
				$this->text=$row->text;
				$this->verfasser_uid=$row->verfasser_uid;
				$this->bearbeiter_uid=$row->bearbeiter_uid;
				$this->start=$row->start;
				$this->ende=$row->ende;
				$this->erledigt=$this->db_parse_bool($row->erledigt);
				$this->insertamum=$row->insertamum;
				$this->insertvon=$row->insertvon;
				$this->updateamum=$row->updateamum;
				$this->updatevon=$row->updatevon;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
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
	 * Löscht eine Notiz
	 * @param  $notiz_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($notiz_id)
	{
		if(!is_numeric($notiz_id))
		{
			$this->errormsg = 'NotizID ist ungueltig';
			return false;
		}

		$qry = "Delete FROM public.tbl_notiz WHERE notiz_id=".$this->db_add_param($notiz_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
		return true; 
	}	

	/**
	 * Prueft die Daten vor dem Speichern
	 * auf Gueltigkeit
	 */
	public function validate()
	{
		return true;
	}	

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $notiz_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new=$this->new;

		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_notiz (titel, text, verfasser_uid,
					bearbeiter_uid, start, ende, erledigt, insertamum, insertvon,
					updateamum, updatevon) VALUES('.
				$this->db_add_param($this->titel).', '.
				$this->db_add_param($this->text).', '.
				$this->db_add_param($this->verfasser_uid).','.
				$this->db_add_param($this->bearbeiter_uid).','.
				$this->db_add_param($this->start).','.
				$this->db_add_param($this->ende).','.
				$this->db_add_param($this->erledigt,FHC_BOOLEAN).','.
				$this->db_add_param($this->insertamum).','.
				$this->db_add_param($this->insertvon).','.
				$this->db_add_param($this->updateamum).','.
				$this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry='UPDATE public.tbl_notiz SET '.
				'titel='.$this->db_add_param($this->titel).', '.
				'text='.$this->db_add_param($this->text).', '.
				'verfasser_uid='.$this->db_add_param($this->verfasser_uid).', '.
				'bearbeiter_uid='.$this->db_add_param($this->bearbeiter_uid).', '.
				'start='.$this->db_add_param($this->start).', '.
				'ende='.$this->db_add_param($this->ende).', '.
				'erledigt='.$this->db_add_param($this->erledigt,FHC_BOOLEAN).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE notiz_id='.$this->db_add_param($this->notiz_id,FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry="SELECT currval('seq_notiz_notiz_id') as id;";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->notiz_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Speichert die Zuordnung einer Notiz
	 * 
	 */
	public function saveZuordnung()
	{
		$qry = "INSERT INTO public.tbl_notizzuordnung(notiz_id, projekt_kurzbz, projektphase_id, projekttask_id, 
						uid, person_id, prestudent_id, bestellung_id, lehreinheit_id) VALUES(".
				$this->db_add_param($this->notiz_id, FHC_INTEGER).','.
				$this->db_add_param($this->projekt_kurzbz).','.
				$this->db_add_param($this->projektphase_id, FHC_INTEGER).','.
				$this->db_add_param($this->projekttask_id, FHC_INTEGER).','.
				$this->db_add_param($this->uid).','.
				$this->db_add_param($this->person_id, FHC_INTEGER).','.
				$this->db_add_param($this->prestudent_id, FHC_INTEGER).','.
				$this->db_add_param($this->bestellung_id, FHC_INTEGER).','.
				$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * 
	 * Laedt die Notizen
	 * @param $erledigt
	 * @param $projekt_kurzbz
	 * @param $projektphase_id
	 * @param $projekttask_id
	 * @param $uid
	 * @param $person_id
	 * @param $prestudent_id
	 * @param $bestellung_id
	 * @param $user
	 * @param $lehreinheit_id
	 * @return boolean
	 */
	public function getNotiz($erledigt=null, $projekt_kurzbz=null, $projektphase_id=null, $projekttask_id=null, $uid=null, $person_id=null, $prestudent_id=null, $bestellung_id=null, $user=null, $lehreinheit_id=null)
	{
		$qry = "SELECT 
					* 
				FROM 
					public.tbl_notiz 
					LEFT JOIN public.tbl_notizzuordnung USING(notiz_id)
				WHERE 1=1";
		
		if(!is_null($erledigt))
		{
			if($erledigt)
				$qry.=" AND erledigt=true";
			else
				$qry.=" AND erledigt=false";
		}
		if($projekt_kurzbz!='')
			$qry.=" AND projekt_kurzbz=".$this->db_add_param($projekt_kurzbz);
		if($projektphase_id!='')
			$qry.=" AND projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER);
		if($projekttask_id!='')
			$qry.=" AND projekttask_id=".$this->db_add_param($projekttask_id, FHC_INTEGER);
		if($uid!='')
			$qry.=" AND uid=".$this->db_add_param($uid);
		if($person_id!='')
			$qry.=" AND person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($prestudent_id!='')
			$qry.=" AND prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);
		if($bestellung_id!='')
			$qry.=" AND bestellung_id=".$this->db_add_param($bestellung_id, FHC_INTEGER);
		if($user!='')
			$qry.=" AND (verfasser_uid=".$this->db_add_param($user)." OR bearbeiter_uid=".$this->db_add_param($user).")";
		if($lehreinheit_id!='')
			$qry.=" AND lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);

		$qry.=' ORDER BY start, ende, titel';
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new notiz();
				
				$obj->notiz_id=$row->notiz_id;
				$obj->titel=$row->titel;
				$obj->text=$row->text;
				$obj->verfasser_uid=$row->verfasser_uid;
				$obj->bearbeiter_uid=$row->bearbeiter_uid;
				$obj->start=$row->start;
				$obj->ende=$row->ende;
				$obj->erledigt=$this->db_parse_bool($row->erledigt);
				$obj->insertamum=$row->insertamum;
				$obj->insertvon=$row->insertvon;
				$obj->updateamum=$row->updateamum;
				$obj->updatevon=$row->updatevon;
	
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

	 * 

	 * Laedt die Notizen
	 * @param $erledigt
	 * @param $projekt_kurzbz
	 * @param $projektphase_id
	 * @param $projekttask_id
	 * @param $uid
	 * @param $person_id
	 * @param $prestudent_id
	 * @param $bestellung_id
	 * @param $user
	 * @param $lehreinheit_id
	 * @return boolean
	 */
	public function getAnzahlNotizen($erledigt=null, $projekt_kurzbz=null, $projektphase_id=null, $projekttask_id=null, $uid=null, $person_id=null, $prestudent_id=null, $bestellung_id=null, $user=null, $lehreinheit_id=null)
	{
		$qry = "SELECT 
					count(*) as anzahl
				FROM 
					public.tbl_notiz 
					LEFT JOIN public.tbl_notizzuordnung USING(notiz_id)
				WHERE 1=1";
		
		if(!is_null($erledigt))
		{
			if($erledigt)
				$qry.=" AND erledigt=true";
			else
				$qry.=" AND erledigt=false";
		}
		if($projekt_kurzbz!='')
			$qry.=" AND projekt_kurzbz=".$this->db_add_param($projekt_kurzbz);
		if($projektphase_id!='')
			$qry.=" AND projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER);
		if($projekttask_id!='')
			$qry.=" AND projekttask_id=".$this->db_add_param($projekttask_id, FHC_INTEGER);
		if($uid!='')
			$qry.=" AND uid=".$this->db_add_param($uid);
		if($person_id!='')
			$qry.=" AND person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($prestudent_id!='')
			$qry.=" AND prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);
		if($bestellung_id!='')
			$qry.=" AND bestellung_id=".$this->db_add_param($bestellung_id, FHC_INTEGER);
		if($user!='')
			$qry.=" AND (verfasser_uid=".$this->db_add_param($user)." OR bearbeiter_uid=".$this->db_add_param($user).")";
		if($lehreinheit_id!='')
			$qry.=" AND lehreinheit_id=".$this->db_add_param($lehreinheit_id, FHC_INTEGER);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
				return $row->anzahl;
			else
			{
				$this->errormsg='Fehler beim Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
