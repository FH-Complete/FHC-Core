<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
/**
 * Klasse projekttask
 * @create 2011-05-23
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projektphase extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $projektphase_id;    //integer
	public $projekt_kurzbz;	    //string
	public $projektphase_fk;	    //string
	public $bezeichnung;	    //string
	public $beschreibung;	    //string
	public $start;		    //date 	
	public $ende;		    //date 	
	public $personentage;	    //integer
	public $budget;	    	// numeric
	public $insertamum;	    // timestamp
	public $insertvon;	    // bigint
	public $updateamum;	    // timestamp
	public $updatevon;	    // bigint


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projektphase_id=null)
	{
		parent::__construct();

		if($projektphase_id != null) 	
			$this->load($projektphase_id);
	}

	/**
	 * Laedt die Projektphase mit der ID $projektphase_id
	 * @param  $projektphase_id ID der zu ladenden Projektphase
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projektphase_id)
	{
		if(!is_numeric($projektphase_id))
		{
			$this->errormsg = 'Projektarbeit_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_projektphase WHERE projektphase_id='$projektphase_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->projektphase_id = $row->projektphase_id;
				$this->projektphase_fk = $row->projektphase_fk;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->start = $row->start;
				$this->ende = $row->ende;
				$this->personentage = $row->personentage;
				$this->budget = $row->budget;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
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
	 * Laedt die Projektphasen mit zu einem Projekt
	 * @param  $projekt_kurzbz Projekt der zu ladenden Projektphasen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjektphasen($projekt_kurzbz)
	{
		$this->result=array();
		$qry = "SELECT * FROM fue.tbl_projektphase WHERE projekt_kurzbz='$projekt_kurzbz' ORDER BY start;";
		//echo "\n".$qry."\n";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektphase();
				
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->projektphase_fk = $row->projektphase_fk;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
				//$obj->personentage = $row->personentage;
				$obj->budget = $row->budget;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
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

		//Gesamtlaenge pruefen
		if ($this->projekttyp_kurzbz==null)
		{
			$this->errormsg='Projekttyp_kurzbz darf nicht NULL sein!';
		}
		if ($this->lehreinheit_id==null)
		{
			$this->errormsg='Lehreinheit_id darf nicht NULL sein!';
		}
		if(mb_strlen($this->projekttyp_kurzbz)>16)
		{
			$this->errormsg = 'Projektyp_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel)>1024)
		{
			$this->errormsg = 'Titel darf nicht länger als 1024 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel_english)>1024)
		{
			$this->errormsg = 'Titel darf nicht länger als 1024 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->themenbereich)>64)
		{
			$this->errormsg = 'Themenbereich darf nicht länger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		/*if(!is_numeric($this->note))
		{
			$this->errormsg = 'Note muß ein numerischer Wert sein - student_uid: '.$this->student_uid;
			return false;
		}*/
		if($this->punkte!='' && !is_numeric($this->punkte))
		{
			$this->errormsg = 'Punkte muss ein numerischer Wert sein';
			return false;
		}
		if($this->faktor!='' && !is_numeric($this->faktor))
		{
			$this->errormsg = 'Faktor muss ein numerischer Wert sein';
			return false;
		}
		if($this->stundensatz!='' && !is_numeric($this->stundensatz))
		{
			$this->errormsg = 'Stundensatz muss ein numerischer Wert sein';
			return false;
		}
		if($this->gesamtstunden!='' && !is_numeric($this->gesamtstunden))
		{
			$this->errormsg = 'Gesamtstunden muss ein numerischer Wert sein';
			return false;
		}
		if(!is_bool($this->freigegeben))
		{
			$this->errormsg = 'freigegeben ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		//if(!$this->validate())
		//	return false;

		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO fue.tbl_projektphase (projekt_kurzbz, projektphase_fk, bezeichnung, 
				beschreibung, start, ende, budget, insertvon, insertamum, updatevon, updateamum) VALUES ('.
			     $this->addslashes($this->projekt_kurzbz).', ';
			if ($this->projektphase_fk=='')
				$qry.='NULL, ';
			else
				$qry.=$this->projektphase_fk.', ';
			$qry.=$this->addslashes($this->bezeichnung).', '.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->start).', '.
			     $this->addslashes($this->ende).', '.
			     $this->addslashes($this->budget).', '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).', now() );';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob projekt_kurzbz eine gueltige Zahl ist
			if(!is_numeric($this->projekt_kurzbz))
			{
				$this->errormsg = 'projekt_kurzbz muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE lehre.tbl_projektarbeit SET '.
				'projekttyp_kurzbz='.$this->addslashes($this->projekttyp_kurzbz).', '.
				'titel='.$this->addslashes($this->titel).', '.
				'titel_english='.$this->addslashes($this->titel_english).', '.
				'lehreinheit_id='.$this->addslashes($this->lehreinheit_id).', '.
				'student_uid='.$this->addslashes($this->student_uid).', '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'note='.$this->addslashes($this->note).', '.
				'punkte='.$this->addslashes($this->punkte).', '.
				'beginn='.$this->addslashes($this->beginn).', '.
				'ende='.$this->addslashes($this->ende).', '.
				'faktor='.$this->addslashes($this->faktor).', '.
				'freigegeben='.($this->freigegeben?'true':'false').', '.
				'gesperrtbis='.$this->addslashes($this->gesperrtbis).', '.
				'stundensatz='.$this->addslashes($this->stundensatz).', '.
				'gesamtstunden='.$this->addslashes($this->gesamtstunden).', '.
				'themenbereich='.$this->addslashes($this->themenbereich).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'updateamum= now(), '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.tbl_projektphase_projektphase_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->projekt_kurzbz = $row->id;
						$this->db_query('COMMIT');
						return true;
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
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $projekt_kurzbz ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($projekt_kurzbz)
	{
		if(!is_numeric($projekt_kurzbz))
		{
			$this->errormsg = 'Projektarbeit_id ist ungueltig';
			return true;
		}
		
		$qry = "DELETE FROM lehre.tbl_projektarbeit WHERE projekt_kurzbz='$projekt_kurzbz'";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}		
	}
	
	/**
	 * Laedt alle Projektarbeiten eines Studenten
	 * @param student_uid
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getProjektarbeit($student_uid)
	{
		$qry = "SELECT * FROM lehre.tbl_projektarbeit WHERE student_uid='".addslashes($student_uid)."'";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektarbeit();
				
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projekttyp_kurzbz = $row->projekttyp_kurzbz;
				$obj->titel = $row->titel;
				$obj->titel_english = $row->titel_english;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->student_uid = $row->student_uid;
				$obj->firma_id = $row->firma_id;
				$obj->note = $row->note;
				$obj->punkte = $row->punkte;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->faktor = $row->faktor;
				$obj->freigegeben = ($row->freigegeben=='t'?true:false);
				$obj->gesperrtbis = $row->gesperrtbis;
				$obj->stundensatz = $row->stundensatz;
				$obj->gesamtstunden = $row->gesamtstunden;
				$obj->themenbereich = $row->themenbereich;
				$obj->anmerkung = $row->anmerkung;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
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
	 * Laedt alle Projektarbeiten eines Studienganges/Studiensemesters
	 * @param studiengang_kz, studiensemester_kurzbz
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getProjektarbeitStudiensemester($studiengang_kz, $studiensemester_kurzbz)
	{
		$qry = "SELECT 
					tbl_projektarbeit.* 
				FROM 
					lehre.tbl_projektarbeit, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung
				WHERE 
					tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
					tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND
					tbl_lehreinheit.studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektarbeit();
				
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projekttyp_kurzbz = $row->projekttyp_kurzbz;
				$obj->titel = $row->titel;
				$obj->titel_english = $row->titel_english;
				$obj->lehreinheit_id = $row->lehreinheit_id;
				$obj->student_uid = $row->student_uid;
				$obj->firma_id = $row->firma_id;
				$obj->note = $row->note;
				$obj->punkte = $row->punkte;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->faktor = $row->faktor;
				$obj->freigegeben = ($row->freigegeben=='t'?true:false);
				$obj->gesperrtbis = $row->gesperrtbis;
				$obj->stundensatz = $row->stundensatz;
				$obj->gesamtstunden = $row->gesamtstunden;
				$obj->themenbereich = $row->themenbereich;
				$obj->anmerkung = $row->anmerkung;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
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
