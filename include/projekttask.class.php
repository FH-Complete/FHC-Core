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

class projekttask extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $projekttask_id;	    //integer
	public $projektphase_id;    //integer
	public $bezeichnung;	    //string
	public $beschreibung;	    //string
	public $aufwand;	    //string
	public $mantis_id;	    // integer
	//public $beginn;	    //date 	
	//public $ende;		    //date 	
	public $insertamum;	    // timestamp
	public $insertvon;	    // bigint
	public $updateamum;	    // timestamp
	public $updatevon;	    // bigint


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projekttask_id=null)
	{
		parent::__construct();

		if($projekttask_id != null) 	
			$this->load($projekttask_id);
	}

	/**
	 * Laedt den Projekttask mit der ID $projekttask_id
	 * @param  $projekttask_id ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projekttask_id)
	{
		if(!is_numeric($projekttask_id))
		{
			$this->errormsg = 'Projekttask_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_projekttask WHERE projekttask_id='$projekttask_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekttask_id = $row->projekttask_id;
				$this->projektphase_id = $row->projektphase_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->aufwand = $row->aufwand;
				$this->mantis_id = $row->mantis_id;
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
	 * Laedt die Projektarbeit mit der ID $projekt_kurzbz
	 * @param  $projektphase_id ID der Projektphase, wenn null greift $projekt_kurzbz
	 * @param  $projekt_kurzbz ID des Projekts wenn keine Projektphase angegeben
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekttasks($projektphase_id,$projekt_kurzbz=null)
	{
		if (!is_null($projektphase_id))
                    $qry = 'SELECT * FROM fue.tbl_projekttask WHERE projektphase_id='.$projektphase_id.';';
                else
                    $qry='';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projekttask();
				     	 	 	 	 	 	
				$obj->projekttask_id = $row->projekttask_id;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->aufwand = $row->aufwand;
				$obj->mantis_id = $row->mantis_id;
				//$obj->beginn = $row->beginn;
				//$obj->ende = $row->ende;
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{

		//Gesamtlaenge pruefen}
		if (!is_numeric($this->projektphase_id))
		{
			$this->errormsg='Projektphase_id muss eine Zahl sein!';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 256 Zeichen sein!';
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
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO fue.tbl_projekttask (projektphase_id, bezeichnung, beschreibung, aufwand, mantis_id, insertamum, 
				insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->projektphase_id).', '.
			     $this->addslashes($this->bezeichnung).', '.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->aufwand).', '.
			     $this->addslashes($this->mantis_id).', 
			     now(), '.
			     $this->addslashes($this->insertvon).', 
			     now(), '.
			     $this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry='UPDATE fue.tbl_projekttask SET '.
				'projektphase_id='.$this->addslashes($this->projektphase_id).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'aufwand='.$this->addslashes($this->aufwand).', '.
				'mantis_id='.$this->addslashes($this->mantis_id).', '.
				'updateamum= now(), '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE projekttask_id='.$this->addslashes($this->projekttask_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.seq_projekttask_projekttask_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->projekttask_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry.$this->db_last_error();
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
