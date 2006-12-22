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

class news
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 			// @var string
	var $result = array(); 	// @var news Objekt 
	
	//Tabellenspalten
	var $news_id;			// @var serial
	var $betreff;			// @var varchar(128)
	var $text;				// @var string
	var $semester;			// @var smallint
	var $uid;				// @var varchar(16)
	var $studiengang_kz;	// @var integer
	var $verfasser;			// @var varchar(64)
	var $datum;				// @date
	var $updateamum;		// @var timestamp
	var $updatevon=0;		// @var string
	var $insertamum;		// @var timestamp
	var $insertvon=0;		// @var string
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $news_id ID der zu ladenden Funktion
	 */
	function news($conn, $news_id=null)
	{
		$this->conn = $conn;
		if($news_id != null)
			$this->load($news_id);
	}
	
	/**
	 * Laedt alle verfuegbaren Benutzerfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM campus.tbl_news order by news_id;';
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$news_obj = new news($this->conn);
			$news_obj->news_id = $row->news_id;
			$news_obj->betreff = $row->betreff;
			$news_obj->text = $row->text;
			$news_obj->semester = $row->semester;
			$news_obj->uid = $row->uid;
			$news_obj->studiengang_kz=$row->studiengang_kz;
			$news_obj->verfasser = $row->verfasser;
			$news_obj->datum = $row->datum;
			$news_obj->insertamum=$row->insertamum;
			$news_obj->insertvon=$row->insertvon;
			$news_obj->updateamum=$row->updateamum;
			$news_obj->updatevon=$row->updatevon;
			
			$this->result[] = $news_obj;
		}
		return true;
	}
	
	// **********************************
	// * Laedt alle News die nicht aelter
	// * als $maxalter Tage sind
	// * @param $maxalter
	// **********************************
	function getnews($maxalter, $studiengang_kz, $semester, $all=false)
	{
		if(!is_numeric($maxalter) || !is_numeric($studiengang_kz) || ($semester!='' && !is_numeric($semester)))
		{
			$this->errormsg = 'Maxalter, Studiengang und Semester muessen gueltige Zahlen sein';
			return false;
		}
		
		if($maxalter!=0)
		{
			$interval = "(now()-datum)<interval '$maxalter days' AND";
		}
		else 
			$interval = '';
		
		if($all)
			$datum = '';
		else
			$datum = 'AND datum<=now()';
			
		if($studiengang_kz==0)
			$qry = "SELECT * FROM campus.tbl_news WHERE $interval studiengang_kz=".$studiengang_kz." AND semester".($semester!=''?"='$semester'":' is null')." $datum ORDER BY datum DESC, updateamum DESC;";
		else 
			$qry = "SELECT * FROM campus.tbl_news WHERE $interval ((studiengang_kz=$studiengang_kz AND semester=$semester) OR (studiengang_kz=$studiengang_kz AND semester=0) OR (studiengang_kz=0 AND semester=$semester) OR (studiengang_kz=0 and semester is null)) $datum ORDER BY datum DESC, updateamum DESC";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$newsobj = new news($this->conn);
				$newsobj->news_id = $row->news_id;
				$newsobj->uid = $row->uid;
				$newsobj->studiengang_kz = $row->studiengang_kz;
				$newsobj->semester = $row->semester;
				$newsobj->betreff = $row->betreff;
				$newsobj->text = $row->text;
				$newsobj->verfasser = $row->verfasser;
				$newsobj->datum		= $row->datum;
				$newsobj->updateamum = $row->updateamum;
				$newsobj->updatevon = $row->updateamum;
				$newsobj->insertamum = $row->insertamum;
				$newsobj->insertvon = $row->insertvon;
				
				$this->result[] = $newsobj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der News';
			return false;
		}
	}
	
	/**
	 * Laedt eine News
	 * @param $news_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($news_id)
	{
		
		if(!is_numeric($news_id))
		{
			$this->errormsg = 'news_id muß eine gültige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_news WHERE news_id = '$news_id';";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row=pg_fetch_object($res))
		{
			$this->news_id		= $row->news_id;
			$this->betreff			= $row->betreff;
			$this->text			= $row->text;
			$this->semester		= $row->semester;
			$this->uid			= $row->uid;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->verfasser		= $row->verfasser;
			$this->datum			= $row->datum;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
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
	 * @param $news_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($news_id)
	{
		if(!is_numeric($news_id))
		{
			$this->errormsg = 'News_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM campus.tbl_news WHERE news_id='$news_id'";
		
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
		$this->betreff 	= str_replace("'",'´',$this->betreff);
		$this->text 		= str_replace("'",'´',$this->text);
		$this->verfasser 	= str_replace("'",'´',$this->verfasser);

		
		//Laenge Pruefen
		if(strlen($this->betreff)>128)           
		{
			$this->errormsg = "Betreff darf nicht laenger als 128 Zeichen sein bei <b>$this->news_id</b> - $this->betreff";
			return false;
		}		
		if(strlen($this->verfasser)>64)
		{
			$this->errormsg = "Verfasser darf nicht laenger als 64 Zeichen sein bei <b>$this->news_id</b> - $this->verfasser";
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
						
			$qry = 'INSERT INTO campus.tbl_news (betreff, text, semester, uid, studiengang_kz, verfasser,datum, insertamum, insertvon, 
				updateamum, updatevon) VALUES ('.
				$this->addslashes($this->betreff).', '.
				$this->addslashes($this->text).', '.
				$this->addslashes($this->semester).', '.
				$this->addslashes($this->uid).', '.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->verfasser).', '.
				$this->addslashes($this->datum).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).'); ';
				
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob news_id eine gueltige Zahl ist
			if(!is_numeric($this->news_id) || $this->news_id == '')
			{
				$this->errormsg = 'News_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry = 'UPDATE campus.tbl_news SET '. 
				'betreff='.$this->addslashes($this->betreff).', '.
				'text='.$this->addslashes($this->text).', '.
				'semester='.$this->addslashes($this->semester).', '.
				'uid='.$this->addslashes($this->uid).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'verfasser='.$this->addslashes($this->verfasser).', '.
				'datum='.$this->addslashes($this->datum).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).'  '.
				'WHERE news_id = '.$this->addslashes($this->news_id).';';
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