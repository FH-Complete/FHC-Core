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
		$qry = "SELECT * FROM public.tbl_akte WHERE akte_id='".addslashes($akte_id)."';";
		
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
				$this->gedruckt = ($row->gedruckt=='t'?true:false);
				$this->titel = $row->titel;
				$this->bezeichnung = $row->bezeichnung;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->uid = $row->uid;
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
		
		$qry = "DELETE FROM public.tbl_akte WHERE akte_id = '".addslashes($akte_id)."';";
		
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
					bezeichnung, updateamum, updatevon, insertamum, insertvon, ext_id, uid) VALUES (".
			       $this->addslashes($this->person_id).', '.
			       $this->addslashes($this->dokument_kurzbz).', '.
			       $this->addslashes($this->inhalt).', '.
			       $this->addslashes($this->mimetype).', '.
			       $this->addslashes($this->erstelltam).', '.
			       ($this->gedruckt?'true':'false').', '.
			       $this->addslashes($this->titel).', '.
			       $this->addslashes($this->bezeichnung).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).', '.
			       $this->addslashes($this->uid).');';
			       
		}
		else 
		{
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE public.tbl_akte SET".
				  " person_id=".$this->addslashes($this->person_id).",".
				  " dokument_kurzbz=".$this->addslashes($this->dokument_kurzbz).",".
				  " inhalt=".$this->addslashes($this->inhalt).",".
				  " mimetype=".$this->addslashes($this->mimetype).",".
				  " erstelltam=".$this->addslashes($this->erstelltam).",".
				  " gedruckt=".($this->gedruckt?'true':'false').",".
				  " titel=".$this->addslashes($this->titel).",".
				  " bezeichnung=".$this->addslashes($this->bezeichnung).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).",".
				  " uid=".$this->addslashes($this->uid).
				  " WHERE akte_id='".addslashes($this->akte_id)."'";
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
					titel, bezeichnung, updateamum, insertamum, updatevon, insertvon, uid 
				FROM public.tbl_akte WHERE person_id='".addslashes($person_id)."'";
		if($dokument_kurzbz!=null)
			$qry.=" AND dokument_kurzbz='".addslashes($dokument_kurzbz)."'";
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
				$akten->gedruckt = ($row->gedruckt=='t'?true:false);
				$akten->titel = $row->titel;
				$akten->bezeichnung = $row->bezeichnung;
				$akten->updateamum = $row->updateamum;
				$akten->updatevon = $row->updatevon;
				$akten->insertamum = $row->insertamum;
				$akten->insertvon = $row->insertvon;
				$akten->uid = $row->uid;
				
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