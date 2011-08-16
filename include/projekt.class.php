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
 * Klasse projektarbeit
 * @create 08-02-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projekt extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $projekt_kurzbz;	//string
	public $nummer;		//string
	public $titel;			//string
	public $beschreibung;		//string
	public $beginn;		//date 	
	public $ende;			//date 	
	public $oe_kurzbz;		//string
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// bigint
	public $updateamum;			// timestamp
	public $updatevon;			// bigint


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projekt_kurzbz=null)
	{
		parent::__construct();

		if($projekt_kurzbz != null) 	
			$this->load($projekt_kurzbz);
	}

	/**
	 * Laedt die Projektarbeit mit der ID $projekt_kurzbz
	 * @param  $projekt_kurzbz ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projekt_kurzbz)
	{

		
		$qry = "SELECT * FROM fue.tbl_projekt WHERE projekt_kurzbz=".$this->addslashes($projekt_kurzbz);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->nummer= $row->nummer;
				$this->titel= $row->titel;
				$this->beschreibung= $row->beschreibung;
				$this->beginn= $row->beginn;
				$this->ende = $row->ende;
				$this->oe_kurzbz= $row->oe_kurzbz;		

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
	 * @param  $projekt_kurzbz ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekte($oe=null,$uid=null)
	{
		$qry = 'SELECT * FROM fue.tbl_projekt';
		if (!is_null($oe))
			$qry.= " WHERE oe_kurzbz='$oe'";
		$qry.= ' ORDER BY oe_kurzbz;';
		//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projekt();
				
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;

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

		//Gesamtlaenge pruefen
		if ($this->projekt_kurzbz==null)
		{
			$this->errormsg='Projekt kurzbz darf nicht NULL sein!';
		}
		if ($this->oe_kurzbz==null)
		{
			$this->errormsg='OE kurbz darf nicht NULL sein!';
		}
		if(mb_strlen($this->projekt_kurzbz)>16)
		{
			$this->errormsg = 'Projektyp_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->nummer)>8)
		{
			$this->errormsg = 'Nummer darf nicht länger als 8 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel)>256)
		{
			$this->errormsg = 'Titel darf nicht länger als 256 Zeichen sein';
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

			$qry='BEGIN; INSERT INTO fue.tbl_projekt (projekt_kurzbz, nummer, titel,beschreibung, beginn, ende, oe_kurzbz) VALUES('.
		     $this->addslashes($this->projekt_kurzbz).', '.
		     $this->addslashes($this->nummer).', '.
		     $this->addslashes($this->titel).', '.
		     $this->addslashes($this->beschreibung).', '.
		     $this->addslashes($this->beginn).', '.
		     $this->addslashes($this->ende).', '.
		     $this->addslashes($this->oe_kurzbz).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			$qry='UPDATE fue.tbl_projekt SET '.
				'projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).', '.
				'nummer='.$this->addslashes($this->nummer).', '.
				'titel='.$this->addslashes($this->titel).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'beginn='.$this->addslashes($this->beginn).', '.
				'ende='.$this->addslashes($this->ende).', '.
				'oe_kurzbz='.$this->addslashes($this->oe_kurzbz).' '.
				'WHERE projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
				$this->db_query('COMMIT');
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry;
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
