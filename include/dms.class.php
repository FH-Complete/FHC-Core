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
	public $parent_kategorie_kurzbz; 
	public $gruppe_kurzbz; 
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
	
	/**
	 * Speichert einenen DMS Eintrag
	 * @param $new
	 */
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
					"WHERE dms_id='".addslashes($this->dms_id)."';".
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
     * Löscht einen DMS Eintrag mit übergebener ID und Version
     * Wird die letzte Version eines Eintrages gelöscht, wird automatisch der Eintrag mitgelöscht
     * @param $dms_id
     * @param $version 
     */
    public function deleteVersion($dms_id, $version)
    {     
        $qry ="DELETE FROM campus.tbl_dms_version WHERE dms_id = ".$this->db_add_param($dms_id, FHC_INTEGER)." and version =".$this->db_add_param($version, FHC_INTEGER);
        
        if($this->db_query($qry))
        {
             $qry_anzahl ="SELECT 1 FROM campus.tbl_dms_version WHERE dms_id =".$this->db_add_param($dms_id, FHC_INTEGER);
             if($result = $this->db_query($qry_anzahl))
            {
                // Wenn letzte Version gelöscht wurde -> lösche gesamten Eintrag
                if($this->db_num_rows($result) == 0 )
                {
                    if(!$this->deleteDms($dms_id))
                    {
                        $this->errormsg = "Fehler beim Löschen aufgetreten"; 
                       return false;
                    }
                    else
                        return true; 
                }
            }   
        }
        else
        {
            $this->errormsg="Fehler beim Löschen der Version aufgetreten";
            return false;
        }
    }

    /**
     * Löscht einen gesamten DMS Eintrag inklusive aller Versionen
     * @param $dms_id 
     */
    public function deleteDms($dms_id)
    {
        // lösche Versionen
        $qry ="BEGIN;DELETE FROM campus.tbl_dms_version WHERE dms_id =".$this->db_add_param($dms_id, FHC_INTEGER)."; ";
        $qry.="DELETE FROM fue.tbl_projekt_dokument WHERE dms_id=".$this->db_add_param($dms_id, FHC_INTEGER)."; ";
        $qry.="DELETE FROM campus.tbl_dms WHERE dms_id =".$this->db_add_param($dms_id, FHC_INTEGER).";";  
        if($this->db_query($qry))
        {
           $this->db_query('COMMIT');
           return true; 
        }
        else
        {
            $this->db_query('ROLLBACK');
            $this->errormsg = "Fehler beim Löschen des Eintrages aufgetreten"; 
            return false; 
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
	 * 
	 * Löscht die Kategorie der übergebenen kurzbz
	 * Überprüft ob noch dms Einträge an zu löschender Kategorie hängen
	 * @param $kategorie_kurzbz
	 */
	public function deleteKategorie($kategorie_kurzbz)
	{
		$qry_anzahl = "SELECT * FROM campus.tbl_dms WHERE kategorie_kurzbz=".$this->db_add_param($kategorie_kurzbz).";";
		
		if($result = $this->db_query($qry_anzahl))
		{
			// löschen nur möglich wenn keine DMS-Einträge mehr auf Kategorie hängen 
			if($this->db_num_rows($result) == 0 )
			{
				$qry ="DELETE FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz =".$this->db_add_param($kategorie_kurzbz).";";
				if($this->db_query($qry))
				{
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
					return false;
				}
			}
			else
			{
				$this->errormsg = "Löschen Fehlgeschlagen! Es hängen noch DMS Einträge an dieser Kategorie";
				return false; 
			}
		}
		$this->errormsg ="Fehler beim Löschen der Daten"; 
		return false; 
		
		
	}
	
	/**
	 * 
	 * Lädt alle Gruppen der übergebenen Kategorie
	 * @param $kategorie_kurzbz
	 */
	public function loadGruppenForKategorie($kategorie_kurzbz)
	{
		$qry = "SELECT 
					campus.tbl_dms_kategorie_gruppe.kategorie_kurzbz,
					campus.tbl_dms_kategorie_gruppe.insertamum,
					campus.tbl_dms_kategorie_gruppe.insertvon,
					campus.tbl_dms_kategorie_gruppe.gruppe_kurzbz,
					public.tbl_gruppe.bezeichnung
				FROM 
					campus.tbl_dms_kategorie_gruppe
					JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE
					kategorie_kurzbz=".$this->db_add_param($kategorie_kurzbz)." 
				ORDER BY gruppe_kurzbz";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms();
				
				$obj->gruppe_kurzbz = $row->gruppe_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->bezeichnung = $row->bezeichnung;
				
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
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der kurzbz $kategorie_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveGruppeKategorie()
	{
		
		if($this->isGruppeZugeteilt($this->kategorie_kurzbz, $this->gruppe_kurzbz))
		{
			$this->errormsg = 'Diese Gruppe ist bereits zugeordnet';
			return false;
		}

			//Neuen Datensatz einfuegen
			$qry='INSERT INTO campus.tbl_dms_kategorie_gruppe (kategorie_kurzbz, gruppe_kurzbz, insertamum, insertvon) VALUES('.
			      $this->db_add_param($this->kategorie_kurzbz).', '.
			      $this->db_add_param($this->gruppe_kurzbz).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).');';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Zuordner der Gruppe';
			return false;
		}
		return true; 

	}
	
	public function deleteGruppe($kategorie_kurzbz, $gruppe_kurzbz)
	{
		$qry = "DELETE FROM campus.tbl_dms_kategorie_gruppe where kategorie_kurzbz =".$this->db_add_param($kategorie_kurzbz)." AND gruppe_kurzbz =".$this->db_add_param($gruppe_kurzbz).";";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Löschen der Gruppe fehlgeschlagen";
			return false; 
		}
		return true; 
	}
	
	
	/**
	 * Prueft ob eine Gruppenzuteilung vorhanden ist
	 * 
	 * @param kategorie_kurzbz
	 * @param $gruppe_kurzbz
	 * @return boolean
	 */
	public function isGruppeZugeteilt($kategorie_kurzbz, $gruppe_kurzbz)
	{
		$qry = "SELECT 1 FROM campus.tbl_dms_kategorie_gruppe WHERE kategorie_kurzbz='".addslashes($kategorie_kurzbz)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."';";
		
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Zuteilung';
			return false;
		}
	}
	
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der kurzbz $kategorie_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveKategorie()
	{
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO campus.tbl_dms_kategorie (kategorie_kurzbz, bezeichnung, beschreibung, parent_kategorie_kurzbz) VALUES('.
			      $this->db_add_param($this->kategorie_kurzbz).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->beschreibung).', '.
			      $this->db_add_param($this->parent_kategorie_kurzbz).');';
		}
		else
		{
			$qry='UPDATE campus.tbl_dms_kategorie SET'.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' beschreibung='.$this->db_add_param($this->beschreibung).', '.
				' parent_kategorie_kurzbz='.$this->db_add_param($this->parent_kategorie_kurzbz).' '.
		      	'WHERE kategorie_kurzbz='.$this->db_add_param($this->kategorie_kurzbz).';';
		}
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Speichern der Kategorie';
			return false;
		}
		return true; 

	}
	
	/**
	 * 
	 * Laedt Kategorie anhand von kurzbz
	 * @param $kategorie_kurzbz
	 */
	public function loadKategorie($kategorie_kurzbz)
	{
		$qry = "SELECT * FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz = ".$this->db_add_param($kategorie_kurzbz).";";
		
		if($result = $this->db_query($qry))
		{
			// wenn es keine kategorie gibt -> gib false zurück
			if($this->db_num_rows() != 0 )
			{
				if($row = $this->db_fetch_object($result))
				{
					$this->kategorie_kurzbz = $row->kategorie_kurzbz; 
					$this->bezeichnung = $row->bezeichnung; 
					$this->beschreibung = $row->beschreibung; 
					$this->parent_kategorie_kurzbz = $row->parent_kategorie_kurzbz; 
				}
				return true; 
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Liefert alle Kategorien zurück
	 */
	public function getAllKategories()
	{
		$qry ="SELECT * FROM campus.tbl_dms_kategorie ORDER BY bezeichnung;";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new dms(); 
				$obj->kategorie_kurzbz = $row->kategorie_kurzbz; 
				$obj->bezeichnung = $row->bezeichnung; 
				$obj->beschreibung = $row->beschreibung; 
				$obj->parent_kategorie_kurzbz = $row->parent_kategorie_kurzbz; 
				
				$this->result[]= $obj; 
			}
		}
		else
			return false; 
	}
	
	/**
	 * Laedt die Kategorien
	 * @param $parent_kategorie_kurzbz Wenn die Parent Kategorie übergeben wird, werden nur die direkten 
	 *        Unterkategorien geladen
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
	
	/**
	 * 
	 * Liefert ein Array mit den Gruppen welche auf die Kategorie zugreifen duerfen
	 * @param $kategorie_kurzbz
	 */
	function getLockGroups($kategorie_kurzbz)
	{
		$qry = "SELECT gruppe_kurzbz FROM 
				(
				WITH RECURSIVE kategorien(parent_kategorie_kurzbz) as 
				(
					SELECT parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie 
					WHERE kategorie_kurzbz='".addslashes($kategorie_kurzbz)."'
					UNION ALL
					SELECT kategorie_kurzbz FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz='".addslashes($kategorie_kurzbz)."'
					UNION ALL
					SELECT k.parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie k, kategorien 
					WHERE k.kategorie_kurzbz=kategorien.parent_kategorie_kurzbz
				)
				SELECT parent_kategorie_kurzbz
				FROM kategorien
				) a
				JOIN campus.tbl_dms_kategorie_gruppe ON(a.parent_kategorie_kurzbz=kategorie_kurzbz)
				";

		if($result = $this->db_query($qry))
		{
			$gruppen = array();
			while($row = $this->db_fetch_object($result))
			{
				$gruppen[]=$row->gruppe_kurzbz;
			}
			return array_unique($gruppen);
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Prueft ob fuer diese Datei eine Authentifizierung erforderlich ist
	 * @param $dms_id
	 * @return true wenn Auth. erforderlich
	 * 		   false wenn nicht erforderlich
	 * 		   false und errormsg im Fehlerfall
	 */
	function isLocked($dms_id)
	{
		$qry = "SELECT 1 FROM 
				(
				WITH RECURSIVE kategorien(parent_kategorie_kurzbz) as 
				(
					SELECT parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie 
					WHERE kategorie_kurzbz=(SELECT kategorie_kurzbz FROM campus.tbl_dms WHERE dms_id='".addslashes($dms_id)."')
					UNION ALL
					SELECT kategorie_kurzbz FROM campus.tbl_dms WHERE dms_id='".addslashes($dms_id)."'
					UNION ALL
					SELECT k.parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie k, kategorien 
					WHERE k.kategorie_kurzbz=kategorien.parent_kategorie_kurzbz
					
				)
				SELECT parent_kategorie_kurzbz
				FROM kategorien
				) a
				JOIN campus.tbl_dms_kategorie_gruppe ON(a.parent_kategorie_kurzbz=kategorie_kurzbz)
				UNION
				SELECT 1 FROM fue.tbl_projekt_dokument WHERE dms_id='".addslashes($dms_id)."'				
				";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
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
	
	/**
	 * 
	 * Prueft ob der User fuer das Dokument berechtigt ist
	 * Vorher sollte mit isLocked geprueft werden ob das Dokument gesperrt ist
	 * 
	 * @param $dms_id
	 * @param $user
	 * @return boolean
	 */
	function isBerechtigt($dms_id, $user)
	{
		$qry = "SELECT * FROM fue.tbl_projekt_dokument WHERE dms_id='".addslashes($dms_id)."'";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				//Berechtigung auf Projekt

				//Alle Mitarbeiter des Projektes oder dazughoeriger Phasen 
				//duerfen auf die Datei zugreifen
				//auch dann, wenn das Dokument an einer anderen Phase des gleichen Projektes haengt
				while($row = $this->db_fetch_object($result))
				{
					if($row->projekt_kurzbz!='')
					{
						//Datei haengt an Projekt
						$qry = "SELECT 
									1 
								FROM 
									fue.tbl_ressource
									JOIN fue.tbl_projekt_ressource USING(ressource_id)
								WHERE
									(tbl_ressource.student_uid='".addslashes($user)."'
									 OR tbl_ressource.mitarbeiter_uid='".addslashes($user)."')
									 AND 
									 (projekt_kurzbz='".addslashes($row->projekt_kurzbz)."'
									 OR projektphase_id in(
									 WITH RECURSIVE phasen(projektphase_id) as 
									(
										SELECT projektphase_id FROM fue.tbl_projektphase 
										WHERE projekt_kurzbz='".addslashes($row->projekt_kurzbz)."'
										UNION ALL
										SELECT p.projektphase_id FROM fue.tbl_projektphase p, phasen 
										WHERE p.projektphase_fk=phasen.projektphase_id
									)
									SELECT projektphase_id
									FROM phasen))";
					}
					else
					{
						//Datei haengt an Projektphase
						$qry = "SELECT 
									1 
								FROM 
									fue.tbl_ressource
									JOIN fue.tbl_projekt_ressource USING(ressource_id)
								WHERE
									(tbl_ressource.student_uid='".addslashes($user)."'
									 OR tbl_ressource.mitarbeiter_uid='".addslashes($user)."')
									AND
									 (
									 tbl_projekt_ressource.projekt_kurzbz=(Select projekt_kurzbz FROM fue.tbl_projektphase where projektphase_id='".addslashes($row->projektphase_id)."')
									 OR tbl_projekt_ressource.projektphase_id in (
									 WITH RECURSIVE phasen(projektphase_id) as 
									(
										SELECT projektphase_id FROM fue.tbl_projektphase 
										WHERE projekt_kurzbz IN (SELECT projekt_kurzbz FROM fue.tbl_projektphase WHERE projektphase_id= '".addslashes($row->projektphase_id)."')
										UNION ALL
										SELECT p.projektphase_id FROM fue.tbl_projektphase p, phasen 
										WHERE p.projektphase_fk=phasen.projektphase_id
									)
									SELECT projektphase_id
									FROM phasen))";
					}
					
					if($result_user = $this->db_query($qry))
					{
						if($this->db_num_rows($result_user)>0)
						{
							//Zuteilung zu Projekt oder Phase gefunden
							//Zugriff erlaubt
							return true;
						}
					}
					else
					{
						$this->errormsg = 'Fehler bei Abfrage';
						return false;
					}
				}
				
				//Die Datei ist einem Projekt zugewiesen, die Person jedoch nicht
				// -> kein Zugriff
				return false;
			}
		}
		
		//Wenn die Datei zu keinem Projekt gehoert, dann die Gruppenrechte pruefen
		$qry = "SELECT 1 FROM 
				(
				WITH RECURSIVE kategorien(parent_kategorie_kurzbz) as 
				(
					SELECT parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie 
					WHERE kategorie_kurzbz=(SELECT kategorie_kurzbz FROM campus.tbl_dms WHERE dms_id='".addslashes($dms_id)."')
					UNION ALL
					SELECT kategorie_kurzbz FROM campus.tbl_dms WHERE dms_id='".addslashes($dms_id)."'
					UNION ALL
					SELECT k.parent_kategorie_kurzbz FROM campus.tbl_dms_kategorie k, kategorien 
					WHERE k.kategorie_kurzbz=kategorien.parent_kategorie_kurzbz					
				)
				SELECT parent_kategorie_kurzbz
				FROM kategorien
				) a
				JOIN campus.tbl_dms_kategorie_gruppe ON(a.parent_kategorie_kurzbz=kategorie_kurzbz)
				JOIN public.tbl_benutzergruppe USING(gruppe_kurzbz)
				WHERE tbl_benutzergruppe.uid='".addslashes($user)."'";
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				return true;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}		
	}
}
?>