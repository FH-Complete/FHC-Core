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

class infoscreen extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $infoscreen_id;
	public $bezeichnung;
	public $beschreibung;
	public $ipadresse;
	
	public $infoscreen_content_id;
	public $content_id;
	public $gueltigvon;
	public $gueltigbis;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	
	/**
	 * Konstruktor
	 * @param infoscreen_id ID des zu ladenden Datensatzes
	 */
	public function __construct($infoscreen_id=null)
	{
		parent::__construct();

		if(!is_null($infoscreen_id))
			$this->load($infoscreen_id);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param infoscreen_id ID des zu ladenden Datensatzes
	 */
	public function load($infoscreen_id)
	{
		//infoscreen_id auf Gueltigkeit pruefen
		if(!is_numeric($infoscreen_id) || $infoscreen_id == '')
		{
			$this->errormsg = 'infoscreen_id muss eine gültige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE infoscreen_id='".addslashes($infoscreen_id)."';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->infoscreen_id = $row->infoscreen_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->ipadresse = $row->ipadresse;
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
	 * Laedt einen InfoscreenContent Datensatz
	 * @param infoscreen_content_id ID des zu ladenden Datensatzes
	 */
	public function loadContent($infoscreen_content_id)
	{
		//infoscreen_content_id auf Gueltigkeit pruefen
		if(!is_numeric($infoscreen_content_id) || $infoscreen_content_id == '')
		{
			$this->errormsg = 'infoscreen__content_id muss eine gültige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM campus.tbl_infoscreen_content WHERE infoscreen_content_id='".addslashes($infoscreen_content_id)."';";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->infoscreen_content_id = $row->infoscreen_content_id;
				$this->infoscreen_id = $row->infoscreen_id;
				$this->content_id = $row->content_id;
				$this->gueltigvon = $row->gueltigvon;
				$this->gueltigbis = $row->gueltigbis;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
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
	 * Laedt alle Infoscreens
	 * @return boolean
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM campus.tbl_infoscreen";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new infoscreen();
				
				$obj->infoscreen_id = $row->infoscreen_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->ipadresse = $row->ipadresse;
				
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
	 * Speichert einen Infoscreen in der Datenbank
	 * @return boolean
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = "BEGIN;INSERT INTO campus.tbl_infoscreen(bezeichnung, beschreibung, ipadresse) VALUES(".
					$this->addslashes($this->bezeichnung).','.
					$this->addslashes($this->beschreibung).','.
					$this->addslashes($this->ipadresse).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_infoscreen SET '.
					' bezeichnung='.$this->addslashes($this->bezeichnung).','.
					' beschreibung='.$this->addslashes($this->beschreibung).','.
					' ipadresse='.$this->addslashes($this->ipadresse).' '.
					' WHERE infoscreen_id='.$this->addslashes($this->infoscreen_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_infoscreen_infoscreen_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->infoscreen_id = $row->id;
						$this->db_query('COMMIT');
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
	 * Speichert eine Contentzuordnung in der Datenbank
	 * @return boolean
	 */
	public function saveContent($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		if($new)
		{
			$qry = "BEGIN;INSERT INTO campus.tbl_infoscreen_content(infoscreen_id, content_id, 
					gueltigvon, gueltigbis, insertamum, insertvon, updateamum, updatevon) VALUES(".
					$this->addslashes($this->infoscreen_id).','.
					$this->addslashes($this->content_id).','.
					$this->addslashes($this->gueltigvon).','.
					$this->addslashes($this->gueltigbis).','.
					$this->addslashes($this->insertamum).','.
					$this->addslashes($this->insertvon).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_infoscreen_content SET '.
					' infoscreen_id='.$this->addslashes($this->infoscreen_id).','.
					' content_id='.$this->addslashes($this->content_id).','.
					' gueltigvon='.$this->addslashes($this->gueltigvon).','.
					' gueltigbis='.$this->addslashes($this->gueltigbis).','.
					' updateamum='.$this->addslashes($this->updateamum).','.
					' updatevon='.$this->addslashes($this->updatevon).' '.
					' WHERE infoscreen_content_id='.$this->addslashes($this->infoscreen_content_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_infoscreen_content_infoscreen_content_id') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->infoscreen_content_id = $row->id;
						$this->db_query('COMMIT');
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
	 * Liefert den Infoscreen anhand der IP-Adresse
	 * @param $ipadresse
	 */
	public function getInfoscreen($ipadresse)
	{
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE ipadresse='".addslashes($ipadresse)."'";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{			
				$this->infoscreen_id = $row->infoscreen_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->ipadresse = $row->ipadresse;
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
	 * 
	 * Liefert den Content der am betreffenden Infoscreen angezeigt werden soll
	 * @param $infoscreen_id id des Infoscreens
	 * @param $aktuell wenn true werden nur die aktuell gueltigen Contents geliefert
	 */
	public function getScreenContent($infoscreen_id, $aktuell=true)
	{
		if(!is_numeric($infoscreen_id))
		{
			$this->errormsg = 'InfoscreenID ist ungueltig';
			return false;
		}
		$qry = "SELECT 
					* 
				FROM 
					campus.tbl_infoscreen_content
				WHERE 
					(infoscreen_id='".addslashes($infoscreen_id)."' OR infoscreen_id is null)";
		if($aktuell)
			$qry.="
					AND (gueltigvon<=now() OR gueltigvon is null)
					AND (gueltigbis>=now() OR gueltigbis is null)";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new infoscreen();
				
				$obj->infoscreen_content_id = $row->infoscreen_content_id;
				$obj->infoscreen_id = $row->infoscreen_id;
				$obj->content_id = $row->content_id;
				$obj->gueltigvon = $row->gueltigvon;
				$obj->gueltigbis = $row->gueltigbis;
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
	 * Entfernt eine Content Zuordnung
	 * 
	 * @param $infoscreen_content_id
	 * @return boolean
	 */
	public function deleteContent($infoscreen_content_id)
	{
		if(!is_numeric($infoscreen_content_id))
		{
			$this->errormsg = 'ID ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM campus.tbl_infoscreen_content WHERE infoscreen_content_id='".addslashes($infoscreen_content_id)."'";
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
}
?>