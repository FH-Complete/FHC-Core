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
 * Klasse benutzerfunktion (FAS-Online)
 * @create 14-03-2006
 */

class benutzerfunktion
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $benutzerfunktion_id;	// @var serial
	var $fachbereich_id;		// @var integer
	var $uid;			// @var varchar(16)
	var $studiengang_kz;	// @var integer
	var $funktion_kurzbz;	// @var varchar(16)
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	var $ext_id;			// @var bigint
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $benutzerfunktion_id ID der zu ladenden Funktion
	 */
	function benutzerfunktion($conn, $benutzerfunktion_id=null)
	{
		$this->conn = $conn;
		if($benutzerfunktion_id != null)
			$this->load($benutzerfunktion_id);
	}
	
	/**
	 * Laedt alle verfuegbaren Benutzerfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM tbl_benutzerfunktion order by benutzerfunktion_id;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$pfunktion_obj = new personenfunktion($this->conn);
			
			$pfunktion_obj->benutzerfunktion_id = $row->benutzerfunktion_id;
			$pfunktion_obj->fachbereich_id = $row->fachbereich_id;
			$pfunktion_obj->uid = $row->uid;
			$pfunktion_obj->studiengang_kz=$row->studiengang_kz;
			$pfunktion_obj->funktion_kurzbz=$row->funtion_kurzbz;
			$pfunktion_obj->insertamum=$row->insertamum;
			$pfunktion_obj->insertvon=$$row->insertvon;
			$pfunktion_obj->updateamum=$row->updateamum;
			$pfunktion_obj->updatevon=$row->updatevon;
			
			$this->result[] = $pfunktion_obj;
		}
		return true;
	}
	
	/**
	 * Laedt eine Benutzerfunktion
	 * @param $bnutzerfunktion_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($benutzerfunktion_id)
	{
		if($benutzerfunktion_id == '')
		{
			$this->errormsg = 'benutzerfunktion_id muß eine gültige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM tbl_benutzerfunktion WHERE benutzerfunktion_id = '$this->benutzerfunktion_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->benutzerfunktion_id	= $row->benutzerfunktion_id;
			$this->fachbereich_id	= $row->fachbereich_id;
			$this->uid			= $row->uid;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->funktion_kurzbz	= $row->funktion_kurzbz;
			$this->insertamum		=$row->insertamum;
			$this->insertvon		=$row->insertvon;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
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
	 * @param $fbenutzerfunktion_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($benutzerfunktion_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		//if(!$this->checkvars())
		//	return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen	
			//Pruefen ob uid vorhanden
			$qry = "SELECT uid FROM tbl_benutzer WHERE uid = '$this->uid';";
			if(!$resx = pg_query($this->conn, $qry))
			{
				$this->errormsg = 'Fehler beim laden des Datensatzes';
				return false;
			}	
			else 
			{
				if (pg_num_rows($resx)==0)
				{
					$this->errormsg = "uid <b>$this->uid</b> in Tabelle tbl_benutzer nicht gefunden!";
					return false;
				}	
			}
			$qry = 'INSERT INTO tbl_benutzerfunktion (fachbereich_id, uid, studiengang_kz, funktion_kurzbz, insertamum, insertvon, 
				updateamum, updatevon) VALUES ('.
				$this->addslashes($this->fachbereich_id).', '.
				$this->addslashes($this->uid).', '.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->funktion_kurzbz).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).'); ';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob benutzerfunktion_id eine gueltige Zahl ist
			if(!is_numeric($this->benutzerfunktion_id) || $this->benutzerfunktion_id == '')
			{
				$this->errormsg = 'benutzerfunktion_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = 'UPDATE tbl_benutzerfunktion SET '. 
				'benutzerfunktion_id='.$this->addslashes($this->benutzerfunktion_id).', '.
				'fachbereich_id='.$this->addslashes($this->fachbereich_id).', '.
				'uid='.$this->addslashes($this->uid).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'funktion_kurzbz='.$this->addslashes($this->funktion_kurzbz).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).'  '.
				'WHERE benutzerfunktion_id = '.$this->addslashes($this->benutzerfunktion_id).';';
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes - '.$this->uid;
			return false;
		}		
	}
}
?>