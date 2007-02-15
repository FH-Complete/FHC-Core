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

class zeitsperre
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 			// @var string
	var $result = array(); 	// @var news Objekt 
	
	//Tabellenspalten
	var $zeitsperre_id;			// serial
	var $zeitsperretyp_kurzbz;	// varchar(8)
	var $mitarbeiter_uid;		// varchar(16)
	var $bezeichnung;			// varchar(32)
	var $vondatum;				// date
	var $vonstunde;				// smallint
	var $bisdatum;				// date
	var $bisstunde;				// smallint
	var $erreichbarkeit;		// varchar(5)
	var $vertretung_uid;		// varchar(16)
	var $updateamum;			// timestamp
	var $updatevon;				// string
	var $insertamum;			// timestamp
	var $insertvon;				// string
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $zeitsperre_id ID der zu ladenden Funktion
	 */
	function zeitsperre($conn, $zeitsperre_id=null)
	{
		$this->conn = $conn;
		if($zeitsperre_id != null)
			$this->load($zeitsperre_id);
	}
		
	// **********************************
	// * Laedt alle Zeitsperren bei denen
	// * ende>=now() ist und uid=$uid
	// **********************************
	function getzeitsperren($uid)
	{
		$qry = "SELECT * FROM campus.tbl_zeitsperre WHERE mitarbeiter_uid='".addslashes($uid)."' AND bisdatum>=now() ORDER BY vondatum";
		
		if($result = pg_query($this->conn, $qry))
		{
		
			while($row = pg_fetch_object($result))
			{
		
				$obj = new zeitsperre($this->conn);
				
				$obj->zeitsperre_id = $row->zeitsperre_id;
				$obj->zeitsperretyp_kurzbz = $row->zeitsperretyp_kurzbz;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->vondatum = $row->vondatum;
				$obj->vonstunde = $row->vonstunde;
				$obj->bisdatum = $row->bisdatum;
				$obj->bisstunde = $row->bisstunde;
				$obj->erreichbarkeit = $row->erreichbarkeit;
				$obj->vertretung_uid = $row->vertretung_uid;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Zeitsperren';
			return false;
		}
	}
	
	/**
	 * Laedt eine Zeitsperre
	 * @param $zeitsperre_id ID der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($zeitsperre_id)
	{
		
		if(!is_numeric($zeitsperre_id))
		{
			$this->errormsg = 'zeitsperre_id mu� eine g�ltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperre_id = '$zeitsperre_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->zeitsperre_id = $row->zeitsperre_id;
			$this->zeitsperretyp_kurzbz = $row->zeitsperretyp_kurzbz;
			$this->mitarbeiter_uid = $row->mitarbeiter_uid;
			$this->bezeichnung = $row->bezeichnung;
			$this->vondatum = $row->vondatum;
			$this->vonstunde = $row->vonstunde;
			$this->bisdatum = $row->bisdatum;
			$this->bisstunde = $row->bisstunde;
			$this->erreichbarkeit = $row->erreichbarkeit;
			$this->vertretung_uid = $row->vertretung_uid;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $zeitsperre_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($zeitsperre_id)
	{
		if(!is_numeric($zeitsperre_id))
		{
			$this->errormsg = 'Zeitsperre_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM campus.tbl_zeitsperre WHERE zeitsperre_id='$zeitsperre_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim L&ouml;schen';
			return false;
		}		
	}
	
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		if(strlen($this->bezeichnung)>32)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 32 Zeichen sein";
			return false;
		}
		if(strlen($this->zeitsperretyp_kurzbz)>8)
		{
			$this->errormsg = "Zeitsperretyp_kurzbz darf nicht laenger als 8 Zeichen sein";
			return false;
		}
		if(strlen($this->mitarbeiter_uid)>16)
		{
			$this->errormsg = "Mitarbeiter_uid darf nicht laenger als 8 Zeichen sein";
			return false;
		}
		if(strlen($this->erreichbarkeit)>5)
		{
			$this->errormsg = "Erreichbarkeit darf nicht laenger als 8 Zeichen sein";
			return false;
		}
		
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */	
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen	
						
			$qry = 'INSERT INTO campus.tbl_zeitsperre (zeitsperretyp_kurzbz, mitarbeiter_uid, bezeichnung,'.
			       ' vondatum ,vonstunde, bisdatum, bisstunde, erreichbarkeit, vertretung_uid, insertamum,'. 
			       ' insertvon, updateamum, updatevon) VALUES ('.
					$this->addslashes($this->zeitsperretyp_kurzbz).', '.
					$this->addslashes($this->mitarbeiter_uid).', '.
					$this->addslashes($this->bezeichnung).', '.
					$this->addslashes($this->vondatum).', '.
					$this->addslashes($this->vonstunde).', '.
					$this->addslashes($this->bisdatum).', '.
					$this->addslashes($this->bisstunde).', '.
					$this->addslashes($this->erreichbarkeit).', '.
					$this->addslashes($this->vertretung_uid).', '.
					$this->addslashes($this->insertamum).', '.
					$this->addslashes($this->insertvon).', '.
					$this->addslashes($this->updateamum).', '.
					$this->addslashes($this->updatevon).'); ';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob zeitsperre_id eine gueltige Zahl ist
			if(!is_numeric($this->zeitsperre_id) || $this->zeitsperre_id == '')
			{
				$this->errormsg = 'Zeitsperre_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = 'UPDATE campus.tbl_zeitsperre SET '. 
				'zeitsperretyp_kurzbz='.$this->addslashes($this->zeitsperretyp_kurzbz).', '.
				'mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'vondatum='.$this->addslashes($this->vondatum).', '.
				'vonstunde='.$this->addslashes($this->vonstunde).', '.
				'bisdatum='.$this->addslashes($this->bisdatum).', '.
				'bisstunde='.$this->addslashes($this->bisstunde).', '.
				'erreichbarkeit='.$this->addslashes($this->erreichbarkeit).', '.
				'vertretung_uid='.$this->addslashes($this->vertretung_uid).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).'  '.
				'WHERE zeitsperre_id = '.$this->addslashes($this->zeitsperre_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}		
	}
}
?>