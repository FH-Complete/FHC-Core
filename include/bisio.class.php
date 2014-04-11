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
/**
 * Klasse bisio - Incomming/Outgoing
 * @create 2007-05-14
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bisio extends basis_db
{
	public $new;       		// boolean
	public $result = array();	// adresse Objekt

	//Tabellenspalten
	public $bisio_id; 					// serial
	public $mobilitaetsprogramm_code; 	// integer
	public $mobilitaetsprogramm_kurzbz;
	public $nation_code; 				// varchar(3)
	public $von; 						// date
	public $bis; 						// date
	public $zweck_code; 				// varchar(20)
	public $zweck_bezeichnung;
	public $student_uid; 				// varchar(16)
	public $updateamum; 				// timestamp
	public $updatevon; 				// varchar(16)
	public $insertamum; 				// timestamp
	public $insertvon; 				// varchar(16) 
	public $ext_id;					// bigint
	public $ort;
	public $universitaet;
	public $lehreinheit_id;

	/**
	 * Konstruktor
	 * @param $bisio_id  ID die geladen werden soll (Default=null)
	 */
	public function __construct($bisio_id=null)
	{
		parent::__construct();
				
		if(!is_null($bisio_id))
			$this->load($bisio_id);
	}

	/**
	 * Laedt die Funktion mit der ID $buchungsnr
	 * @param  $buchungsnr ID der zu ladenden  Email
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($bisio_id)
	{
		if(!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM bis.tbl_bisio WHERE bisio_id=".$this->db_add_param($bisio_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisio_id = $row->bisio_id;
				$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$this->nation_code = $row->nation_code;
				$this->von = $row->von;
				$this->bis = $row->bis;
				$this->zweck_code = $row->zweck_code;
				$this->student_uid = $row->student_uid;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->ort = $row->ort;
				$this->universitaet = $row->universitaet;
				$this->lehreinheit_id = $row->lehreinheit_id;
				
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->mobilitaetsprogramm_code))
		{
			$this->errormsg = 'Mobilitaetsprogramm ist ungueltig';
			return false;
		}
		
		if(mb_strlen($this->nation_code)>3)
		{
			$this->errormsg = 'Nation ist ungueltig';
			return false;
		}
		
		if(mb_strlen($this->zweck_code)>20)
		{
			$this->errormsg = 'Zweck ist ungueltig';
			return false;
		}
		
		if(mb_strlen($this->student_uid)>32)
		{
			$this->errormsg = 'Student_UID ist ungueltig';
			return false;
		}
		
		if($this->von!='' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->von))
		{			
			$this->errormsg = 'VON-Datum hat ein ungueltiges Format';
			return false;
		}
		
		if($this->bis!='' && !mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->bis))
		{
			$this->errormsg = 'BIS-Datum hat ein ungueltiges Format';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	 * @param $new true wenn insert false wenn update
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN;INSERT INTO bis.tbl_bisio (mobilitaetsprogramm_code, nation_code, von, bis, zweck_code, student_uid, updateamum, updatevon, insertamum, insertvon, ext_id, ort, universitaet, lehreinheit_id) VALUES('.
			     $this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).', '.
			     $this->db_add_param($this->nation_code).', '.
			     $this->db_add_param($this->von).', '.
			     $this->db_add_param($this->bis).', '.
			     $this->db_add_param($this->zweck_code).', '.
			     $this->db_add_param($this->student_uid).', '.
			     $this->db_add_param($this->updateamum).', '.
			     $this->db_add_param($this->updatevon).', '.
			     $this->db_add_param($this->insertamum).', '.
			     $this->db_add_param($this->insertvon).', '.
			     $this->db_add_param($this->ext_id, FHC_INTEGER).','.
			     $this->db_add_param($this->ort).', '.
			     $this->db_add_param($this->universitaet).', '.
			     $this->db_add_param($this->lehreinheit_id, FHC_INTEGER).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry = 'UPDATE bis.tbl_bisio SET '.
				   ' mobilitaetsprogramm_code='.$this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).','.
				   ' nation_code='.$this->db_add_param($this->nation_code).','.
				   ' von='.$this->db_add_param($this->von).','.
				   ' bis='.$this->db_add_param($this->bis).','.
				   ' zweck_code='.$this->db_add_param($this->zweck_code).','.
				   ' student_uid='.$this->db_add_param($this->student_uid).','.
				   ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' ext_id='.$this->db_add_param($this->ext_id, FHC_INTEGER).','.
				   ' ort='.$this->db_add_param($this->ort).','.
				   ' universitaet='.$this->db_add_param($this->universitaet).','.
				   ' lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).
				   " WHERE bisio_id=".$this->db_add_param($this->bisio_id, FHC_INTEGER).";";
		}
		
		if($this->db_query($qry))
		{
				if($new)
				{
					$qry = "SELECT currval('bis.tbl_bisio_bisio_id_seq') as id";
					if($this->db_query($qry))
					{
						if($row = $this->db_fetch_object())
						{
							$this->bisio_id = $row->id;
							$this->db_query('COMMIT;');
						}
						else
						{
							$this->errormsg = 'Fehler beim Auslesen der Sequence';
							$this->db_query('ROLLBACK;');
							return false;
						}
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param bisio_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bisio_id)
	{
		if(!is_numeric($bisio_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_bisio WHERE bisio_id=".$this->db_add_param($bisio_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert alle Incomming/Outgoing 
	 * Eintraege eines Studenten
	 * @param $uid
	 * @return true wenn ok, false wenn fehler
	 */
	public function getIO($uid)
	{
		$qry = "SELECT	tbl_bisio.*, 
						tbl_mobilitaetsprogramm.kurzbz as mobilitaetsprogramm_kurzbz,
						tbl_zweck.bezeichnung as zweck_bezeichnung
			    FROM 
			    	bis.tbl_bisio, 
			    	bis.tbl_zweck, 
			    	bis.tbl_mobilitaetsprogramm 
				WHERE 
					student_uid=".$this->db_add_param($uid)." AND
					tbl_zweck.zweck_code=tbl_bisio.zweck_code AND
					tbl_mobilitaetsprogramm.mobilitaetsprogramm_code=tbl_bisio.mobilitaetsprogramm_code
				ORDER BY bis;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$io = new bisio();
				
				$io->bisio_id = $row->bisio_id;
				$io->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$io->mobilitaetsprogramm_kurzbz = $row->mobilitaetsprogramm_kurzbz;
				$io->nation_code = $row->nation_code;
				$io->von = $row->von;
				$io->bis = $row->bis;
				$io->zweck_code = $row->zweck_code;
				$io->zweck_bezeichnung = $row->zweck_bezeichnung;
				$io->student_uid = $row->student_uid;
				$io->updateamum = $row->updateamum;
				$io->updatevon = $row->updatevon;
				$io->insertamum = $row->insertamum;
				$io->insertvon = $row->insertvon;
				$io->ext_id = $row->ext_id;
				$io->ort = $row->ort;
				$io->universitaet = $row->universitaet;
				$io->lehreinheit_id = $row->lehreinheit_id;
				
				$this->result[] = $io;
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