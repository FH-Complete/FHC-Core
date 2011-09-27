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

		$qry = "SELECT * FROM public.tbl_notiz WHERE notiz_id='".addslashes($notiz_id)."'";

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
				$this->erledigt=($row->erledigt=='t'?true:false);
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
				$this->addslashes($this->titel).', '.
				$this->addslashes($this->text).', '.
				$this->addslashes($this->verfasser_uid).','.
				$this->addslashes($this->bearbeiter_uid).','.
				$this->addslashes($this->start).','.
				$this->addslashes($this->ende).','.
				($this->erledigt?'true':'false').','.
				$this->addslashes($this->insertamum).','.
				$this->addslashes($this->insertvon).','.
				$this->addslashes($this->updateamum).','.
				$this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry='UPDATE public.tbl_notiz SET '.
				'titel='.$this->addslashes($this->titel).', '.
				'text='.$this->addslashes($this->text).', '.
				'verfasser_uid='.$this->addslashes($this->verfasser_uid).', '.
				'bearbeiter_uid='.$this->addslashes($this->bearbeiter_uid).', '.
				'start='.$this->addslashes($this->start).', '.
				'ende='.$this->addslashes($this->ende).', '.
				'erledigt='.($this->erledigt?'true':'false').', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE notiz_id='.$this->addslashes($this->notiz_id).';';
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
			$this->errormsg = "Fehler beim Speichern des Datensatzes".$qry;
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
						uid, person_id, prestudent_id, bestellung_id) VALUES(".
				$this->addslashes($this->notiz_id).','.
				$this->addslashes($this->projekt_kurzbz).','.
				$this->addslashes($this->projektphase_id).','.
				$this->addslashes($this->projekttask_id).','.
				$this->addslashes($this->uid).','.
				$this->addslashes($this->person_id).','.
				$this->addslashes($this->prestudent_id).','.
				$this->addslashes($this->bestellung_id).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry;
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
	 * @return boolean
	 */
	public function getNotiz($erledigt=null, $projekt_kurzbz=null, $projektphase_id=null, $projekttask_id=null, $uid=null, $person_id=null, $prestudent_id=null, $bestellung_id=null, $user=null)
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
			$qry.=" AND projekt_kurzbz='".addslashes($projekt_kurzbz)."'";
		if($projektphase_id!='')
			$qry.=" AND projektphase_id='".addslashes($projektphase_id)."'";
		if($projekttask_id!='')
			$qry.=" AND projekttask_id='".addslashes($projekttask_id)."'";
		if($uid!='')
			$qry.=" AND uid='".addslashes($uid)."'";
		if($person_id!='')
			$qry.=" AND person_id='".addslashes($person_id)."'";
		if($prestudent_id!='')
			$qry.=" AND prestudent_id='".addslashes($prestudent_id)."'";
		if($bestellung_id!='')
			$qry.=" AND bestellung_id='".addslashes($bestellung_id)."'";
		if($user!='')
			$qry.=" AND (verfasser_uid='".addslashes($user)."' OR bearbeiter_uid='".addslashes($user)."')";
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
				$obj->erledigt=($row->erledigt=='t'?true:false);
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
	
}
?>