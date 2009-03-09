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
	var $lehrveranstaltung_id;		// @var integer
	var $lehrziele;		// @var string
	var $titel;				// @var varchar(256)
	var $methodik;			// @var string
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

	var $lastqry;			//zuletzt ausgefuehrte qry (benoetigt fuer log)
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

			$lvinfo_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lvinfo_obj->lehrziele 			= $row->lehrziele;
			$lvinfo_obj->titel 				= $row->titel;
			$lvinfo_obj->methodik 			= $row->methodik;
			$lvinfo_obj->lehrinhalte 		= $row->lehrinhalte;
			$lvinfo_obj->voraussetzungen 	= $row->voraussetzungen;
			$lvinfo_obj->unterlagen 		= $row->unterlagen;
			$lvinfo_obj->pruefungsordnung 	= $row->pruefungsordnung;
			$lvinfo_obj->anmerkungen 		= $row->anmerkung;
			$lvinfo_obj->kurzbeschreibung	= $row->kurzbeschreibung;
			$lvinfo_obj->genehmigt 			= ($row->genehmigt=='t'?true:false);
			$lvinfo_obj->aktiv 				= ($row->aktiv=='t'?true:false);
			$lvinfo_obj->sprache 			= $row->sprache;
			$lvinfo_obj->insertamum 		= $row->insertamum;
			$lvinfo_obj->insertvon 			= $row->insertvon;
			$lvinfo_obj->updateamum 		= $row->updateamum;
			$lvinfo_obj->updatevon     		= $row->updatevon;

			$this->result[] = $lvinfo_obj;
		}
		return true;
	}

	/**
	 * Laedt eine LVInfo
	 * @param $lehrveranstaltung_id
	 *        $sprache
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehrveranstaltung_id, $sprache)
	{
		if($lehrveranstaltung_id == '' || !is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'lvinfo_id ungültig';
			return false;
		}
		$qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id = '$lehrveranstaltung_id' AND sprache='".addslashes($sprache)."';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		if($row=pg_fetch_object($res))
		{
			$this->lehrveranstaltung_id	= $row->lehrveranstaltung_id;
			$this->lehrziele 			= $row->lehrziele;
			$this->titel 				= $row->titel;
			$this->methodik 			= $row->methodik;
			$this->lehrinhalte 			= $row->lehrinhalte;
			$this->voraussetzungen 		= $row->voraussetzungen;
			$this->unterlagen 			= $row->unterlagen;
			$this->pruefungsordnung 	= $row->pruefungsordnung;
			$this->anmerkungen 			= $row->anmerkung;
			$this->kurzbeschreibung		= $row->kurzbeschreibung;
			$this->genehmigt 			= ($row->genehmigt=='t'?true:false);
			$this->aktiv 				= ($row->aktiv=='t'?true:false);
			$this->sprache 				= $row->sprache;
			$this->insertamum 			= $row->insertamum;
			$this->insertvon 			= $row->insertvon;
			$this->updateamum 			= $row->updateamum;
			$this->updatevon     		= $row->updatevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID ('.$lehrveranstaltung_id.') vorhanden';
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
		if(!is_numeric($lvinfo_id))
		{
			$this->errormsg = 'Lvinfo_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lvinfo_id'";

		if(pg_query($this->conn, $qry))
		{
			$this->lastqry = $qry;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
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
		//Laenge Pruefen
		if(strlen($this->sprache)>16)
		{
			$this->errormsg = "Sprache darf nicht laenger als 16 Zeichen sein bei <b>".$this->lvinfo_id."</b> - $this->sprache";
			return false;
		}
		if(!is_numeric($this->lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveransaltung_id muss eine gueltige Zahl sein';
			return false;
		}
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
			$qry = 'INSERT INTO campus.tbl_lvinfo (lehrveranstaltung_id, sprache, titel, methodik, lehrziele, lehrinhalte, voraussetzungen, unterlagen, pruefungsordnung, anmerkung,
				kurzbeschreibung, genehmigt, aktiv,  insertamum, insertvon, updateamum,
				updatevon) VALUES ('.
				$this->addslashes($this->lehrveranstaltung_id).','.
				$this->addslashes($this->sprache).', '.
				$this->addslashes($this->titel).', '.
				$this->addslashes($this->methodik).', '.
				$this->addslashes($this->lehrziele).', '.
				$this->addslashes($this->lehrinhalte).', '.
				$this->addslashes($this->voraussetzungen).', '.
				$this->addslashes($this->unterlagen).', '.
				$this->addslashes($this->pruefungsordnung).', '.
				$this->addslashes($this->anmerkungen).', '.
				$this->addslashes($this->kurzbeschreibung).', '.
				($this->genehmigt?'true':'false').', '.
				($this->aktiv?'true':'false').', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).');';

		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob lvinfo_id gueltig ist
			if($this->lehrveranstaltung_id == '' || !is_numeric($this->lehrveranstaltung_id))
			{
				$this->errormsg = 'lehrveranstaltung_id '.$this->lehrveranstaltung_id.' ungültig';
				return false;
			}

			$qry = 'UPDATE campus.tbl_lvinfo SET '.
				'titel='.$this->addslashes($this->titel).','.
				'methodik='.$this->addslashes($this->methodik).','.
				'lehrziele='.$this->addslashes($this->lehrziele).', '.
				'lehrinhalte='.$this->addslashes($this->lehrinhalte).', '.
				'voraussetzungen='.$this->addslashes($this->voraussetzungen).', '.
				'pruefungsordnung='.$this->addslashes($this->pruefungsordnung).', '.
				'anmerkung='.$this->addslashes($this->anmerkungen).', '.
				'kurzbeschreibung='.$this->addslashes($this->kurzbeschreibung).', '.
				'unterlagen='.$this->addslashes($this->unterlagen).', '.
				'genehmigt='.($this->genehmigt?'true':'false') .', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE lehrveranstaltung_id = '.$this->addslashes($this->lehrveranstaltung_id)." AND sprache=".$this->addslashes($this->sprache).";";
		}

		if(pg_query($this->conn, $qry))
		{

			$this->lastqry=$qry;
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	function exists($lehrveranstaltung_id, $sprache=null)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT count(*) as anzahl FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lehrveranstaltung_id'";
		
		if(!is_null($sprache))
			$qry = " AND sprache='".addslashes($sprache)."'";

		if($result=pg_query($this->conn, $qry))
		{
			if($row=pg_fetch_object($result))
			{
				if($row->anzahl>0)
				{
					return true;
				}
				else
				{
					return false;
				}
			}
			else
			{
				$this->errormsg ='Fehler bei einer Abfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}
	
	/**
	 * Kopiert eine LVInfo von einer LV in eine andere
	 *
	 * @param $source ID der Lehrveranstaltung von der wegkopiert wird
	 * @param $target ID der Lehrveranstaltung zu der die LV-Info kopiert werden soll
	 * @return true wenn ok, false wenn Fehler
	 */
	function copy($source, $target)
	{
		if(!is_numeric($source) || $source=='')
		{
			$this->errormsg ='source muss eine gueltige Zahl sein';
			return false;
		}
		
		if(!is_numeric($target) || $target=='')
		{
			$this->errormsg ='target muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "
		INSERT INTO campus.tbl_lvinfo(lehrveranstaltung_id, sprache, titel, lehrziele,
			lehrinhalte, methodik, voraussetzungen, unterlagen, pruefungsordnung, anmerkung, kurzbeschreibung, genehmigt,
			aktiv, updateamum, updatevon, insertamum, insertvon) 
		SELECT $target, sprache, titel, lehrziele,
		lehrinhalte, methodik, voraussetzungen, unterlagen, pruefungsordnung, anmerkung, kurzbeschreibung, genehmigt,
		aktiv, updateamum, updatevon, insertamum, insertvon FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id=$source";
		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Kopieren der LVInfo';
			return false;
		}
		
	}
}
?>