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
require_once(dirname(__FILE__).'/basis_db.class.php');

class akte extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $akte_id;
	public $person_id;
	public $dokument_kurzbz;
	public $inhalt;
	public $mimetype;
	public $erstelltam;
	public $gedruckt;
	public $titel;
	public $bezeichnung;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $uid;	
	public $ext_id;
	public $dms_id;
	
	/**
	 * Konstruktor
	 * @param akte_id ID des zu ladenden Datensatzes
	 */
	public function __construct($akte_id=null)
	{
		parent::__construct();

		if(!is_null($akte_id))
			$this->load($akte_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param akte_id ID des zu ladenden Datensatzes
	 */
	public function load($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM public.tbl_akte WHERE akte_id=".$this->db_add_param($akte_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->akte_id = $row->akte_id;
				$this->person_id = $row->person_id;
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->inhalt = $row->inhalt;
				$this->mimetype = $row->mimetype;
				$this->erstelltam = $row->erstelltam;
				$this->gedruckt = $this->db_parse_bool($row->gedruckt);
				$this->titel = $row->titel;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->uid = $row->uid;
				$this->dms_id = $row->dms_id;
				return true;		
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
			
	/**
	 * Loescht einen Datensatz
	 * @param akte_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($akte_id)
	{
		//akte_id auf gueltigkeit pruefen
		if(!is_numeric($akte_id) || $akte_id == '')
		{
			$this->errormsg = 'akte_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_akte WHERE akte_id=".$this->db_add_param($akte_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}
	}
	
	/**
	 * Prueft die Variablen vor dem Speichern
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		if($this->person_id=='')
		{
			$this->errormsg = 'Person ID muss angegeben werden';
			return false;
		}
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'DokumentKurzbz muss angegeben werden';
			return false;
		}

		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $akte_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(!$this->validate())
			return false;
		
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz anlegen	
			$qry = "BEGIN;INSERT INTO public.tbl_akte (person_id, dokument_kurzbz, inhalt, mimetype, erstelltam, gedruckt, titel, 
					bezeichnung, updateamum, updatevon, insertamum, insertvon, ext_id, uid, dms_id) VALUES (".
			       $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			       $this->db_add_param($this->dokument_kurzbz).', '.
			       $this->db_add_param($this->inhalt).', '.
			       $this->db_add_param($this->mimetype).', '.
			       $this->db_add_param($this->erstelltam).', '.
			       $this->db_add_param($this->gedruckt, FHC_BOOLEAN).', '.
			       $this->db_add_param($this->titel).', '.
			       $this->db_add_param($this->bezeichnung).', '.
			       $this->db_add_param($this->updateamum).', '.
			       $this->db_add_param($this->updatevon).', '.
			       $this->db_add_param($this->insertamum).', '.
			       $this->db_add_param($this->insertvon).', '.
			       $this->db_add_param($this->ext_id).', '.
			       $this->db_add_param($this->uid).','.
			       $this->db_add_param($this->dms_id).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_akte SET".
				  " person_id=".$this->db_add_param($this->person_id, FHC_INTEGER).",".
				  " dokument_kurzbz=".$this->db_add_param($this->dokument_kurzbz).",".
				  " inhalt=".$this->db_add_param($this->inhalt).",".
				  " mimetype=".$this->db_add_param($this->mimetype).",".
				  " erstelltam=".$this->db_add_param($this->erstelltam).",".
				  " gedruckt=".$this->db_add_param($this->gedruckt,FHC_BOOLEAN).",".
				  " titel=".$this->db_add_param($this->titel).",".
				  " bezeichnung=".$this->db_add_param($this->bezeichnung).",".
				  " updateamum=".$this->db_add_param($this->updateamum).",".
				  " updatevon=".$this->db_add_param($this->updatevon).",".
				  " ext_id=".$this->db_add_param($this->ext_id).",".
				  " uid=".$this->db_add_param($this->uid).",".
				  " dms_id=".$this->db_add_param($this->dms_id, FHC_INTEGER).
				  " WHERE akte_id=".$this->db_add_param($this->akte_id, FHC_INTEGER);
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_akte_akte_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->akte_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Liefert die Akten einer Person
	 *
	 * @param $person_id
	 * @param $dokument_kurzbz
	 * @return true wenn ok, sonst false
	 */
	public function getAkten($person_id, $dokument_kurzbz=null)
	{
		$qry = "SELECT 
					akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt, 
					titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid, dms_id
				FROM public.tbl_akte WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		if($dokument_kurzbz!=null)
			$qry.=" AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);
		$qry.=" ORDER BY erstelltam";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();
				
				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				
				$this->result[] = $akten;
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
	 * Liefert die Akten die ein Outgoing sehen darf
	 *
	 * @param $person_id
	 * @return true wenn ok, sonst false
	 */
	public function getAktenOutgoing($person_id)
	{
		$qry = "SELECT 
					akte_id, person_id, dokument_kurzbz, mimetype, erstelltam, gedruckt, 
					titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid 
				FROM public.tbl_akte WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);

			$qry.=" AND dokument_kurzbz IN ('Lebenslf','Motivat','LearnAgr')";
		$qry.=" ORDER BY erstelltam";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$akten = new akte();
				
				$akten->akte_id = $row->akte_id;
				$akten->person_id = $row->person_id;
				$akten->dokument_kurzbz = $row->dokument_kurzbz;
				//$akte->inhalt = $row->inhalt;
				$akten->mimetype = $row->mimetype;
				$akten->erstelltam = $row->erstelltam;
				$akten->gedruckt = $this->db_parse_bool($row->gedruckt);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				$akten->dms_id = $row->dms_id;
				
				$this->result[] = $akten;
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
