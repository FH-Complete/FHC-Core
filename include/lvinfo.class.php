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
 * Klasse lvinfo (FAS-Online)
 * @create 04-12-2006
 */

class lvinfo
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt 
	
	//Tabellenspalten
	var $lvinfo_id;		// @var integer
	var $lehrziele;		// @var string
	var $lehrinhalte;		// @var string
	var $voraussetzungen;	// @var string
	var $unterlagen;		// @var string
	var $pruefungsordnung;	// @var string
	var $anmerkungen;		// @var string
	var $kurzbeschreibung;	// @var string
	var $genehmigt;		// @var boolean
	var $aktiv;			// @var boolean
	var $sprache;			// @var string
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $lvinfo_id ID des zu ladenden Ortes
	 */
	function lvinfo($conn, $lvinfo_id=null)
	{
		$this->conn = $conn;
		if($lvinfo_id != null && is_numeric($lvinfo_id))
			$this->load($lvinfo_id);
	}
	/**
	 * Laedt alle verfuegbaren LVInfos
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM campus.tbl_lvinfo order by lvinfo_id;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$lvinfo_obj = new lvinfo($this->conn);
			
			$lvinfo_obj->lvinfo_id 		= $row->lvinfo_id;
			$lvinfo_obj->lehrziele 		= $row->lehrziele;
			$lvinfo_obj->lehrinhalte 		= $row->lehrinhalte;
			$lvinfo_obj->voraussetzungen 	= $row->voraussetzungen;
			$lvinfo_obj->unterlagen 		= $row->unterlagen;
			$lvinfo_obj->pruefungsordnung 	= $row->pruefungsordnung;
			$lvinfo_obj->anmerkungen 		= $row->anmerkungen;
			$lvinfo_obj->kurzbeschreibung	= $row->kurzbeschreibung;
			$lvinfo_obj->genehmigt 		= $row->genehmigt;
			$lvinfo_obj->aktiv 			= $row->aktiv;
			$lvinfo_obj->sprache 		= $row->sprache;
			$lvinfo_obj->insertamum 		= $row->insertamum;
			$lvinfo_obj->insertvon 		= $row->insertvon;
			$lvinfo_obj->updateamum 		= $row->updateamum;
			$lvinfo_obj->updatevon     		= $row->updatevon;
			
			$this->result[] = $lvinfo_obj;
		}
		return true;
	}
	
	/**
	 * Laedt eine LVInfo
	 * @param $lvinfo_id ID der zu ladenden LVInfo
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lvinfo_id)
	{
		if($lvinfo == '' || !is_numeric($lvinfo_id))
		{
			$this->errormsg = 'lvinfo_id ungültig';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_lvinfo WHERE lvinfo_id = '$lvinfo_id';";
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		if($row=pg_fetch_object($res))
		{
			$this->lvinfo_id 			= $row->lvinfo_id;
			$this->lehrziele 			= $row->lehrziele;
			$this->lehrinhalte 			= $row->lehrinhalte;
			$this->voraussetzungen 		= $row->voraussetzungen;
			$this->unterlagen 			= $row->unterlagen;
			$this->pruefungsordnung 		= $row->pruefungsordnung;
			$this->anmerkungen 		= $row->anmerkungen;
			$this->kurzbeschreibung		= $row->kurzbeschreibung;
			$this->genehmigt 			= $row->genehmigt;
			$this->aktiv 				= $row->aktiv;
			$this->sprache 			= $row->sprache;
			$this->kosten 			= $row->kosten;
			$this->insertamum 			= $row->insertamum;
			$this->insertvon 			= $row->insertvon;
			$this->updateamum 			= $row->updateamum;
			$this->updatevon     			= $row->updatevon;
		}
		else 
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID ('.$lvinfo_id.') vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Loescht einen Datensatz
	 * @param $lvinfo_id ID des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lvinfo_id)
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
		$this->lehrziele 		= str_replace("'",'´',$this->lehrziele);
		$this->lehrinhalte 		= str_replace("'",'´',$this->lehrinhalte);
		$this->voraussetzungen 	= str_replace("'",'´',$this->voraussetzungen);
		$this->unterlagen 		= str_replace("'",'´',$this->unterlagen);
		$this->pruefungsordnung 	= str_replace("'",'´',$this->pruefungsordnung);
		$this->anmerkungen 	= str_replace("'",'´',$this->anmerkungen);
		$this->kurzbeschreibung	= str_replace("'",'´',$this->kurzbeschreibung);
		$this->sprache		= str_replace("'",'´',$this->sprache);
		
		//Laenge Pruefen
		if(strlen($this->sprache)>16)           
		{
			$this->errormsg = "Sprache darf nicht laenger als 16 Zeichen sein bei <b>".$this->$lvinfo_id."</b> - $this->sprache";
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
			//Pruefen ob lvinfo_id eine gueltige Bezeichnung ist
			if($this->lvinfo_id == '' || !is_numeric($this->lvinfo_id))
			{
				$this->errormsg = 'lvinfo_id ungültig';
				return false;
			}
			
			//Neuen Datensatz anlegen		
			$qry = 'BEGIN; INSERT INTO campus.tbl_lvinfo (lehrziele, lehrinhalte, voraussetzungen, unterlagen, pruefungsordnung, anmerkungen, 
				kurzbeschreibung, genehmigt, aktiv, sprache, insertamum, insertvon, updateamum, 
				updatevon) VALUES ('.
				$this->addslashes($this->lehrziele).', '.
				$this->addslashes($this->lehrinhalte).', '.
				$this->addslashes($this->voraussetzungen).', '.
				$this->addslashes($this->unterlagen).', '.
				$this->addslashes($this->pruefungsordnung).', '.
				$this->addslashes($this->anmerkungen).', '.
				$this->addslashes($this->kurzbeschreibung).', '.
				($this->genehmigt?'true':'false').', '. 
				($this->aktiv?'true':'false').', '. 
				$this->addslashes($this->sprache).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).');';

		}
		else 
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob lvinfo_id gueltig ist
			if($this->lvinfo_id == '' || !is_numeric($this->lvinfo_id))
			{
				$this->errormsg = 'lvinfo_id '.$this->lvinfo_id.' ungültig';
				return false;
			}
			
			$qry = 'BEGIN; UPDATE campus.tbl_lvinfo SET '. 
				'lehrziele='.$this->addslashes($this->lehrziele).', '.
				'lehrinhalte='.$this->addslashes($this->lehrinhalte).', '.
				'voraussetzungen='.$this->addslashes($this->voraussetzungen).', '.
				'pruefungsordnung='.$this->addslashes($this->pruefungsordnung).', '.
				'anmerkungen='.$this->addslashes($this->anmerkungen).', '.
				'kurzbeschreibung='.$this->addslashes($this->kurzbeschreibung).', '.
				'genehmigt='.($this->aktiv?'true':'false') .', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'sprache='.$this->addslashes($this->sprache).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE lvinfo_id = '.$this->addslashes($this->lvinfo_id).';';
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
			$qry = "SELECT currval('campus.tbl_lvinfo_lvinfo_id_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler!';
				pg_query($this->conn, "ROLLBACK");
				return false;
			}
			else 
			{
				$this->lvinfo_id = $row->id;
				pg_query($this->conn, "COMMIT");
			}
			
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