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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * DMS Dokumenten Management System
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class dms extends basis_db
{
	public $new;
	public $result=array();

	public $dms_id;
	public $version;
	public $oe_kurzbz;
	public $dokument_kurzbz;
	public $kategorie_kurzbz;
	public $filename;
	public $mimetype;
	public $name;
	public $beschreibung;
	public $letzterzugriff;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	public $bezeichnung;
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();		
	}

	/**
	 * Laedt ein Dokument
	 * Wenn keine Version uebergeben wird, wird automatisch die letzte geladen
	 * @param dms_id
	 * @param version optional
	 */
	public function load($dms_id, $version=null)
	{
		$qry = "SELECT tbl_dms.dms_id, * FROM campus.tbl_dms JOIN campus.tbl_dms_version USING(dms_id) WHERE dms_id='".addslashes($dms_id)."'";
		
		if(!is_null($version))
			$qry.=" AND version='".addslashes($version)."'";
			
		$qry.=" ORDER BY version DESC LIMIT 1";
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->dms_id = $row->dms_id;
				$this->version = $row->version;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->kategorie_kurzbz = $row->kategorie_kurzbz;
				$this->filename = $row->filename;
				$this->mimetype = $row->mimetype;
				$this->name = $row->name;
				$this->beschreibung = $row->beschreibung;
				$this->letzterzugriff = $row->letzterzugriff;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				
				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Eintrag gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = "BEGIN;";
			
			if($this->dms_id=='')
			{
				$dms_id="currval('campus.seq_dms_dms_id')";
				
				$qry.="INSERT INTO campus.tbl_dms(oe_kurzbz, dokument_kurzbz, kategorie_kurzbz) 
						VALUES(".
					$this->addslashes($this->oe_kurzbz).','.
					$this->addslashes($this->dokument_kurzbz).','.
					$this->addslashes($this->kategorie_kurzbz).');';
			}
			else
			{
				if(!is_numeric($this->dms_id))
				{
					$this->errormsg = 'dms_id ist ungueltig';
					return false;
				}
				$dms_id=$this->dms_id;
			}
								
			$qry.="INSERT INTO campus.tbl_dms_version(dms_id, version,  
						filename, mimetype, name, beschreibung, letzterzugriff, insertamum, insertvon, 
						updateamum, updatevon) VALUES(".
					$dms_id.','.
					$this->addslashes($this->version).','.
					$this->addslashes($this->filename).','.
					$this->addslashes($this->mimetype).','.
					$this->addslashes($this->name).','.
					$this->addslashes($this->beschreibung).','.
					$this->addslashes($this->letzterzugriff).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry = "UPDATE campus.tbl_dms SET".
				" oe_kurzbz=".$this->addslashes($this->oe_kurzbz).",".
				" dokument_kurzbz=".$this->addslashes($this->dokument_kurzbz).",".
				" kategorie_kurzbz=".$this->addslashes($this->kategorie_kurzbz)." ". 
			" WHERE dms_id='".addslashes($this->dms_id)."';".
			"UPDATE campus.tbl_dms_version SET".
				" filename=".$this->addslashes($this->filename).",".
				" mimetype=".$this->addslashes($this->mimetype).",".
				" name=".$this->addslashes($this->name).",".
				" beschreibung=".$this->addslashes($this->beschreibung).",".
				" letzterzugriff=".$this->addslashes($this->letzterzugriff).",".
				" updateamum=".$this->addslashes($this->updateamum).",".
				" updatevon=".$this->addslashes($this->updatevon).
				" WHERE dms_id='".addslashes($this->dms_id)."' AND version='".addslashes($this->version)."';";
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				if($this->dms_id=='')
				{
					$qry = "SELECT currval('campus.seq_dms_dms_id') as id;";
					if($result = $this->db_query($qry))
					{
						if($row = $this->db_fetch_object($result))
						{
							$this->dms_id = $row->id;
							$this->db_query('COMMIT;');
							return true;
						}
						else
						{
							$this->errormsg='Fehler beim Auslesen der Sequence';
							$this->db_query('ROLLBACK');
							return false;
						}
					}
					else
					{
						$this->errormsg='Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->db_query('COMMIT;');
					return true;
				}
			}
			else
				return true;
		}
	}
	
	/**
	 * Setzt die Zeit des letzten Zugriffs auf die Datei
	 * 
	 * @param $dms_id
	 * @param $version
	 */
	public function touch($dms_id, $version)
	{
		$qry ="UPDATE campus.tbl_dms_version SET letzterzugriff=now() 
			WHERE dms_id='".addslashes($dms_id)."' AND version='".addslashes($version)."';";
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg='Fehler beim Aktualisieren der Zugriffszeit';
			return false;
		}		
	}
	
	/**
	 * Laedt alle Kategorien
	 * @return boolean
	 */
	public function getKategorie($parent_kategorie_kurzbz='')
	{
		$qry = "SELECT * FROM campus.tbl_dms_kategorie WHERE ";
		
		if($parent_kategorie_kurzbz!='')
			$qry.=" parent_kategorie_kurzbz='".addslashes($parent_kategorie_kurzbz)."'";
		else
			$qry.=" parent_kategorie_kurzbz is null";
		$qry.=" ORDER BY bezeichnung";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				
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
	 * Laedt die Dokumente einer Kategorie
	 *
	 * @param $kategorie_kurzbz
	 */
	public function getDocuments($kategorie_kurzbz)
	{
		$qry = "SELECT * FROM campus.tbl_dms JOIN campus.tbl_dms_version USING(dms_id) 
				WHERE (dms_id, version) in(
					SELECT dms_id, max(version)
					FROM campus.tbl_dms_version 
					GROUP BY dms_id)
				AND kategorie_kurzbz='".addslashes($kategorie_kurzbz)."'
				ORDER BY name;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->dms_id = $row->dms_id;
				$obj->version = $row->version; 
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->filename = $row->filename;
				$obj->mimetype = $row->mimetype;
				$obj->name = $row->name;
				$obj->beschreibung = $row->beschreibung;
				$obj->letzterzugriff = $row->letzterzugriff;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Sucht nach Dokumenten
	 *
	 * @param $kategorie_kurzbz
	 */
	public function search($suchstring)
	{
		$qry = "SELECT * FROM campus.tbl_dms  JOIN campus.tbl_dms_version USING(dms_id)
				WHERE lower(name) like lower('%".addslashes($suchstring)."%')
				OR lower(beschreibung) like lower('%".addslashes($suchstring)."%')
				;";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->dms_id = $row->dms_id;
				$obj->version = $row->version; 
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->filename = $row->filename;
				$obj->mimetype = $row->mimetype;
				$obj->name = $row->name;
				$obj->beschreibung = $row->beschreibung;
				$obj->letzterzugriff = $row->letzterzugriff;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
/**
 * 
 * lädt alle Versionen zu einer übergebenen ID
 * @param $id der zu ladenden Dokumente
 */
	public function getAllVersions($id)
	{
		if(!is_numeric($id))
		{
			$this->errormsg = "Falsche Dokument ID"; 
			return false; 
		}
		
		$qry =	"SELECT * FROM campus.tbl_dms JOIN campus.tbl_dms_version USING(dms_id)
				 WHERE dms_id = '".addslashes($id)."' ORDER BY version ASC;";	
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->dms_id = $row->dms_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->filename = $row->filename;
				$obj->mimetype = $row->mimetype;
				$obj->name = $row->name;
				$obj->beschreibung = $row->beschreibung;
				$obj->letzterzugriff = $row->letzterzugriff;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->version = $row->version; 
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Überprüft ob die übergebene Version die aktuellste ist
	 * @param $id
	 * @param $version
	 */
	public function checkVersion($id, $version)
	{
		$qry = "SELECT * FROM campus.tbl_dms_version
		WHERE dms_id = '".addslashes($id)."' and 
		version > '".addslashes($version)."' ;";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else 
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
			return false; 
		}
	}
	
	/**
	 * Laedt die Dokumente eines Projekts
	 *
	 * @param $kategorie_kurzbz
	 */
	public function getDokumenteProjekt($projekt_kurzbz)
	{
		$qry = "SELECT 
					* 
				FROM 
					campus.tbl_dms 
					JOIN campus.tbl_dms_version USING(dms_id)
					JOIN fue.tbl_projekt_dokument USING(dms_id) 
				WHERE (dms_id, version) in(
					SELECT dms_id, max(version)
					FROM campus.tbl_dms_version 
					GROUP BY dms_id)
				AND tbl_projekt_dokument.projekt_kurzbz='".addslashes($projekt_kurzbz)."'
				ORDER BY name;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->dms_id = $row->dms_id;
				$obj->version = $row->version; 
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->filename = $row->filename;
				$obj->mimetype = $row->mimetype;
				$obj->name = $row->name;
				$obj->beschreibung = $row->beschreibung;
				$obj->letzterzugriff = $row->letzterzugriff;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt die Dokumente einer Projektphase
	 *
	 * @param $kategorie_kurzbz
	 */
	public function getDokumenteProjektphase($projektphase_id)
	{
		$qry = "SELECT 
					* 
				FROM 
					campus.tbl_dms 
					JOIN campus.tbl_dms_version USING(dms_id)
					JOIN fue.tbl_projekt_dokument USING(dms_id) 
				WHERE (dms_id, version) in(
					SELECT dms_id, max(version)
					FROM campus.tbl_dms_version 
					GROUP BY dms_id)
				AND tbl_projekt_dokument.projektphase_id='".addslashes($projektphase_id)."'
				ORDER BY name;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->dms_id = $row->dms_id;
				$obj->version = $row->version; 
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->dokument_kurzbz = $row->dokument_kurzbz;
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz;
				$obj->filename = $row->filename;
				$obj->mimetype = $row->mimetype;
				$obj->name = $row->name;
				$obj->beschreibung = $row->beschreibung;
				$obj->letzterzugriff = $row->letzterzugriff;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Speichert die Zuordnung eines Dokuments zu einem Projekt
	 * Wenn die Zuordnung bereits vorhanden ist, geschieht nichts
	 * 
	 * @param $dms_id
	 * @param $projekt_kurzbz
	 * @param $projektphase_id
	 */
	function saveProjektzuordnung($dms_id, $projekt_kurzbz, $projektphase_id)
	{
		$qry = "SELECT * FROM fue.tbl_projekt_dokument WHERE dms_id='".addslashes($dms_id)."'";

		if($projekt_kurzbz!='')
			$qry.=" AND projekt_kurzbz='".addslashes($projekt_kurzbz)."'";
		if($projektphase_id!='')
			$qry.=" AND projektphase_id='".addslashes($projektphase_id)."'";
			
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)==0)
			{
				//keine Zuordnung vorhanden -> anlegen
				$qry = "INSERT INTO fue.tbl_projekt_dokument(projektphase_id, projekt_kurzbz, dms_id) VALUES(".
						$this->addslashes($projektphase_id).','.
						$this->addslashes($projekt_kurzbz).','.
						$this->addslashes($dms_id).');';
						
				if($this->db_query($qry))
				{
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Zuteilen des Dokuments zu einem Projekt';
					return false;
				}
			}
			else
				return true; //Zuordnung bereits vorhanden
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}
}
?>