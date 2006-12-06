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
 * Klasse fachbereich (FAS-Online)
 * @create 04-12-2006
 */

class fachbereich
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $fachbereich_id;		// @var integer
	var $bezeichnung;		// @var string
	var $kurzbz;			// @var string
	var $farbe;			// @var string
	var $studiengang_kz;	// @var integer
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	var $ext_id;			// @var bigint
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $fachb_id ID des zu ladenden Fachbereiches
	 */
	function fachbereich($conn, $fachb_id=null)
	{
		$this->conn = $conn;
		if($fachb_id != null)
			$this->load($fachb_id);
	}
	
	/**
	 * Laedt alle verfuegbaren Fachbereiche
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM tbl_fachbereich order by name;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$fachb_obj = new fachbereich($this->conn);
			
			$fachb_obj->fachbereich_id 	= $row->fachbereich_pk;
			$fachb_obj->erhalter_id   		= $row->erhalter_fk;
			$fachb_obj->name           		= $row->name;
			$fachb_obj->updateamum     	= $row->creationdate;
			$fachb_obj->updatevon     		= $row->creationuser;
			
			$this->result[] = $fachb_obj;
		}
		return true;
	}
	
	/**
	 * Laedt einen Fachbereich
	 * @param $fachb_id ID des zu ladenden Fachbereiches
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($fachb_id)
	{
		if(!is_numeric($fachb_id) || $fachb_id == '')
		{
			$this->errormsg = 'fachb_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM tbl_fachbereich WHERE fachbereich_pk = '$fachb_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->fachbereich_id 	= $row->fachbereich_pk;
			$this->erhalter_id    		= $row->erhalter_fk;
			$this->name           		= $row->name;
			$this->updateamum     	= $row->creationdate;
			$this->updatevon      	= $row->creationuser;
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
	 * @param $fachb_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($fachb_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
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
		$this->bezeichnung = str_replace("'",'´',$this->bezeichnung);
		$this->kurzbz = str_replace("'",'´',$this->kurzbz);

		
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)           
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->kurzbz)>16)
		{
			$this->errormsg = "Kurzbez darf nicht laenger als 16 Zeichen sein bei <b>$this->ext_id</b> - $this->kurzbz";
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
			//Pruefen ob fachbereich_id eine gueltige Zahl ist
			if(!is_numeric($this->fachbereich_id) || $this->fachbereich_id == '')
			{
				$this->errormsg = 'fachbereich_id muss eine gueltige Zahl sein';
				return false;
			}
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO tbl_fachbereich (fachbereich_id, bezeichnung, kurzbz, farbe, ext_id, insertamum, insertvon, 
				updateamum, updatevon, studiengang_kz) VALUES ('.
				$this->addslashes($this->fachbereich_id).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->kurzbz).', '.
				$this->addslashes($this->farbe).', '.
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).', '.
				$this->addslashes($this->studiengang_kz).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob fachbereich_id eine gueltige Zahl ist
			if(!is_numeric($this->fachbereich_id) || $this->fachbereich_id == '')
			{
				$this->errormsg = 'fachbereich_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = 'UPDATE tbl_fachbereich SET '. 
				'fachbereich_id='.$this->addslashes($this->fachbereich_id).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'kurzbz='.$this->addslashes($this->kurzbz).', '.
				'farbe='.$this->addslashes($this->farbe).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).' '.
				'WHERE fachbereich_id = '.$this->addslashes($this->fachbereich_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			/*//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}*/
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