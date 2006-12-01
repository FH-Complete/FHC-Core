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
 * Klasse ortraumtyp (FAS-Online)
 * @create 14-03-2006
 */

class raumtyp
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $beschreibung;		// @var string
	var $raumtyp_kurzbz;	// @var string
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $ort_kurzbz und hierarchie ID des zu ladenden OrtRaumtyps
	 */
	function raumtyp($conn, $raumtyp_kurzbz=null)
	{
		$this->conn = $conn;
		if($raumtyp_kurzbz != null)
			$this->load($raumtyp_kurzbz);
	}
	
	/**
	 * Laedt alle verfuegbaren OrtRaumtypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM tbl_raumtyp order by raumtyp_kurzbz;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$raumtyp_obj = new ort($this->conn);
			
			$raumtyp_obj->beschreibung = $row->beschreibung;
			$raumtyp_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$raumtyp_obj->insertamum = $row->insertamum;
			$raumtyp_obj->insertvon = $row->insertvon;
			$raumtyp_obj->updateamum = $row->updateamum;
			$raumtyp_obj->updatevon     = $row->updatevon;
			
			$this->result[] = $raumtyp_obj;
		}
		return true;
	}
	
	/**
	 * Laedt einen Raumtyp
	 * @param $raumtyp ID des zu ladenden Raumtyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($raumtyp_kurzbz)
	{
		if($raum_kurzbz == '')
		{
			$this->errormsg = 'Kein gültiger Schlüssel vorhanden';
			return false;
		}
		
		$qry = "SELECT * FROM tbl_raumtyp WHERE raumtyp_kurzbz = '$raumtyp_kurzbz';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->beschreibung = $row->beschreibung;
			$this->raumtyp_kurzbz = $row->kurzbz;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon     = $row->updatevon;
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
	 * @param $raumtyp_kurzbz ID des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($raumtyp_kurzbz)
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
		$this->beschreibung = str_replace("'",'´',$this->beschreibung);
		$this->raumtyp_kurzbz = str_replace("'",'´',$this->raumtyp_kurzbz);

		
		//Laenge Pruefen
		if(strlen($this->beschreibung)>256)           
		{
			$this->errormsg = "Beschreibung darf nicht laenger als 256 Zeichen sein bei <b>$this->raumtyp_kurzbz</b> - ".$this->beschreibung;
			return false;
		}		
		if(strlen($this->raumtyp_kurzbz)>8)
		{
			$this->errormsg = "Raumtyp_kurzbz darf nicht laenger als 8 Zeichen sein bei <b>$this->raumtyp_kurzbz</b>";
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
			//Pruefen ob id gültig ist
			if($this->raumtyp_kurzbz == '')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO tbl_raumtyp (beschreibung, raumtyp_kurzbz, 
				insertamum, insertvon, updateamum, updatevon) VALUES ('.
				$this->addslashes($this->beschreibung).', '.
				$this->addslashes($this->raumtyp_kurzbz).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).');';

		}
		else 
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob id gueltig ist
			if($this->raumtyp_kurzbz == '')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}
			
			$qry = 'UPDATE tbl_raumtyp SET '. 
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE raumtyp_kurzbz = '.$this->addslashes($this->ort_kurzbz).';';
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