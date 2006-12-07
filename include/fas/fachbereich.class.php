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
class fachbereich
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 			// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $fachbereich_kurzbz;// @var integer
	var $bezeichnung;		// @var string
	var $farbe;				// @var string
	var $studiengang_kz;	// @var integer
	var $ext_id;			// @var bigint
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $fachb_id ID des zu ladenden Fachbereiches
	 */
	function fachbereich($conn, $fachbereich_kurzbz=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
		else 
			$this->new = true;
			
		if($fachbereich_kurzbz != null)
			$this->load($fachbereich_kurzbz);
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
	function load($fachbereich_kurzbz)
	{
		if(!is_numeric($fachbereich_kurzbz) || $fachbereich_kurzbz == '')
		{
			$this->errormsg = 'fachbereich_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM tbl_fachbereich WHERE fachbereich_kurzbz = '$fachbereich_kurzbz';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->fachbereich_kurzbz 	= $row->fachbereich_kurzbz;
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
	function delete($fachbereich_kurzbz)
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
	function validate()
	{			
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)           
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->fachbereich_kurzbz)>16)
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
		if(!$this->validate())
			return false;
			
		if($this->new)
		{			
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO tbl_fachbereich (fachbereich_kurzbz, bezeichnung, farbe, ext_id, studiengang_kz) VALUES ('.
				$this->addslashes($this->fachbereich_kurzbz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->farbe).', '.
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->studiengang_kz).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren			
			$qry = 'UPDATE tbl_fachbereich SET '. 
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'farbe='.$this->addslashes($this->farbe).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).' '.
				'WHERE fachbereich_kurzbz = '.$this->addslashes($this->fachbereich_kurzbz).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			//Log schreiben
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