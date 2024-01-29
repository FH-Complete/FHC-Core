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
	public $refreshzeit;
	public $insertamum;
	public $insertvon;
	public $updateamum;
	public $updatevon;
	public $exklusiv;
	
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
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE infoscreen_id=".$this->db_add_param($infoscreen_id, FHC_INTEGER).";";
		
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
		$qry = "SELECT * FROM campus.tbl_infoscreen_content WHERE infoscreen_content_id=".$this->db_add_param($infoscreen_content_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->infoscreen_content_id = $row->infoscreen_content_id;
				$this->infoscreen_id = $row->infoscreen_id;
				$this->content_id = $row->content_id;
				$this->gueltigvon = $row->gueltigvon;
				$this->gueltigbis = $row->gueltigbis;
				$this->refreshzeit = $row->refreshzeit;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->exklusiv = $this->db_parse_bool($row->exklusiv);
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
		$qry = "SELECT * FROM campus.tbl_infoscreen ORDER BY bezeichnung;";
		
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
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->beschreibung).','.
					$this->db_add_param($this->ipadresse).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_infoscreen SET '.
					' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
					' beschreibung='.$this->db_add_param($this->beschreibung).','.
					' ipadresse='.$this->db_add_param($this->ipadresse).' '.
					' WHERE infoscreen_id='.$this->db_add_param($this->infoscreen_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_infoscreen_infoscreen_id') as id;";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->infoscreen_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg='Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg='Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
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
					gueltigvon, gueltigbis, refreshzeit, insertamum, insertvon, updateamum, updatevon, exklusiv) VALUES(".
					$this->db_add_param($this->infoscreen_id, FHC_INTEGER).','.
					$this->db_add_param($this->content_id, FHC_INTEGER).','.
					$this->db_add_param($this->gueltigvon).','.
					$this->db_add_param($this->gueltigbis).','.
					$this->db_add_param($this->refreshzeit).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->exklusiv, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_infoscreen_content SET '.
					' infoscreen_id='.$this->db_add_param($this->infoscreen_id, FHC_INTEGER).','.
					' content_id='.$this->db_add_param($this->content_id, FHC_INTEGER).','.
					' gueltigvon='.$this->db_add_param($this->gueltigvon).','.
					' gueltigbis='.$this->db_add_param($this->gueltigbis).','.
					' refreshzeit='.$this->db_add_param($this->refreshzeit).','.
					' updateamum='.$this->db_add_param($this->updateamum).','.
					' updatevon='.$this->db_add_param($this->updatevon).','.
					' exklusiv='.$this->db_add_param($this->exklusiv, FHC_BOOLEAN).' '.
					' WHERE infoscreen_content_id='.$this->db_add_param($this->infoscreen_content_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('campus.seq_infoscreen_content_infoscreen_content_id') as id;";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->infoscreen_content_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg='Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg='Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
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
		$qry = "SELECT * FROM campus.tbl_infoscreen WHERE ipadresse=".$this->db_add_param($ipadresse).';';
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
	 * @param integer $infoscreen_id id des Infoscreens
	 * @param boolean $aktuell Deafult:true. Wenn true, werden nur die aktuell gueltigen Contents geliefert
	 * @param boolean $exklusiv Deafult:true. Wenn true, werden Contents, die das Attribut exklusiv=true haben, vorrangig vor normalen Terminen geliefert
	 */
	public function getScreenContent($infoscreen_id, $aktuell=true, $exklusiv=true)
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
					(infoscreen_id=".$this->db_add_param($infoscreen_id, FHC_INTEGER)." OR infoscreen_id is null)";
		if($aktuell)
			$qry.="
					AND (gueltigvon<=now() OR gueltigvon is null)
					AND (gueltigbis>=now() OR gueltigbis is null)";
		if($exklusiv)
			$qry.="
					AND CASE WHEN 
					(
						SELECT count(exklusiv) FROM campus.tbl_infoscreen_content 
						WHERE (infoscreen_id=".$this->db_add_param($infoscreen_id, FHC_INTEGER)." OR infoscreen_id is null) 
						AND (gueltigvon<=now() OR gueltigvon is null) 
						AND (gueltigbis>=now() OR gueltigbis is null)
						AND exklusiv=true
					)>0 THEN 					
						exklusiv=true					
					ELSE 					
						1=1					
					END";
		$qry.=" ORDER BY infoscreen_content_id;";
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
				$obj->refreshzeit = $row->refreshzeit;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->exklusiv = $this->db_parse_bool($row->exklusiv);
				
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
		$qry = "DELETE FROM campus.tbl_infoscreen_content WHERE infoscreen_content_id=".$this->db_add_param($infoscreen_content_id, FHC_INTEGER).';';
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