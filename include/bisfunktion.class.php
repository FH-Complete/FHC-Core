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

class bisfunktion extends basis_db 
{
	public $new;      //  boolean
	public $result = array(); //  email Objekt
	
	//Tabellenspalten
	public $bisverwendung_id;
	public $studiengang_kz;
	public $sws;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $studiengang_kz_old;
	
	/**
	 * Konstruktor
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 */
	public function __construct($bisverwendung_id=null, $studiengang_kz=null)
	{
		parent::__construct();
		
		if(!is_null($bisverwendung_id) && !is_null($studiengang_kz))
			$this->load($bisverwendung_id, $studiengang_kz);
	}
	
	/**
	 * Laedt einen Datensatz
	 * @param bisverwendung_id ID des zu ladenden Datensatzes
	 *        studiengang_kz
	 */
	public function load($bisverwendung_id, $studiengang_kz)
	{
		//bisverwendung_id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='$bisverwendung_id' AND studiengang_kz='$studiengang_kz'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bisverwendung_id = $row->bisverwendung_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->sws = $row->sws;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				
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
	 * @param bisverwendung_id ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bisverwendung_id, $studiengang_kz)
	{
		//id auf gueltigkeit pruefen
		if(!is_numeric($bisverwendung_id) || $bisverwendung_id == '')
		{
			$this->errormsg = 'bisverwendung_id muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($studiengang_kz) || $studiengang_kz == '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM bis.tbl_bisfunktion WHERE bisverwendung_id = '$bisverwendung_id' AND studiengang_kz='$studiengang_kz';";
		
		if($this->db_query($qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen';
			return false;
		}
	}
	
	/**
	 * Prueft die Variablen vor dem Speichern auf Gueltigkeit
	 *
	 * @return true wenn ok, sonst false
	 */
	protected function validate()
	{
		
		if($this->sws!='' && !is_numeric($this->sws))
		{
			$this->errormsg = 'SWS muss eine gueltige Zahl sein';
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
			$qry = "INSERT INTO bis.tbl_bisfunktion (bisverwendung_id, studiengang_kz, sws,
					updateamum, updatevon, insertamum, insertvon, ext_id) VALUES (".
			       $this->addslashes($this->bisverwendung_id).', '.
			       $this->addslashes($this->studiengang_kz).', '.
			       $this->addslashes($this->sws).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->ext_id).');';
			       
		}
		else 
		{
			//Bei einem Update bei dem sich der Studiengang aendert muss der "Alte" Studiengang auch angegeben werden
			//da der Studiengang teil des Primaerschluessels ist
			if($this->studiengang_kz_old=='')
				$this->studiengang_kz_old = $this->studiengang_kz;
			
			//Bestehenden Datensatz aktualisieren
			$qry= "UPDATE bis.tbl_bisfunktion SET".
				  " sws=".$this->addslashes($this->sws).",".
				  " studiengang_kz=".$this->addslashes($this->studiengang_kz).",".
				  " updateamum=".$this->addslashes($this->updateamum).",".
				  " updatevon=".$this->addslashes($this->updatevon).",".
				  " ext_id=".$this->addslashes($this->ext_id).
				  " WHERE bisverwendung_id='".addslashes($this->bisverwendung_id)."' AND studiengang_kz='".addslashes($this->studiengang_kz_old)."'";
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt alle Verwendungen eines Mitarbeiters
	 * @param $uid UID des Mitarbeiters
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getBisFunktion($bisverwendung_id, $studiengang_kz=null)
	{
		//laden des Datensatzes
		$qry = "SELECT * FROM bis.tbl_bisfunktion WHERE bisverwendung_id='".addslashes($bisverwendung_id)."'";
		
		if($studiengang_kz!=null)
			$qry.=" AND studiengang_kz='".addslashes($studiengang_kz)."'";

		$qry.=" ORDER BY studiengang_kz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bisfunktion();
				
				$obj->bisverwendung_id = $row->bisverwendung_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->sws = $row->sws;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
	
}
?>