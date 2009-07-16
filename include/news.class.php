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

class news extends basis_db 
{
	public $new;     			// boolean
	public $result = array(); 	// news Objekt 
	
	//Tabellenspalten
	public $news_id;			// serial
	public $betreff;			// varchar(128)
	public $text;				// string
	public $semester;			// smallint
	public $fachbereich_kurzbz;// varchar(16)
	public $uid;				// varchar(16)
	public $studiengang_kz;	// integer
	public $verfasser;			// varchar(64)
	public $datum;				// @date
	public $updateamum;		// timestamp
	public $updatevon=0;		// string
	public $insertamum;		// timestamp
	public $insertvon=0;		// string
	public $datum_bis;			// @date
	
	
	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $news_id ID der zu ladenden Funktion
	 */
	public function __construct($news_id=null)
	{
		parent::__construct();
		
		if($news_id != null)
			$this->load($news_id);
	}
	
	/**
	 * Laedt alle verfuegbaren News
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM campus.tbl_news ORDER BY news_id;';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}
		
		while($row = $this->db_fetch_object())
		{
			$news_obj = new news();
			
			$news_obj->news_id = $row->news_id;
			$news_obj->betreff = $row->betreff;
			$news_obj->text = $row->text;
			$news_obj->semester = $row->semester;
			$news_obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$news_obj->uid = $row->uid;
			$news_obj->studiengang_kz=$row->studiengang_kz;
			$news_obj->verfasser = $row->verfasser;
			$news_obj->datum = $row->datum;
			$news_obj->datum_bis = $row->datum_bis;
			$news_obj->insertamum=$row->insertamum;
			$news_obj->insertvon=$row->insertvon;
			$news_obj->updateamum=$row->updateamum;
			$news_obj->updatevon=$row->updatevon;
			
			$this->result[] = $news_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle News die nicht aelter
	 * als $maxalter Tage sind
	 * @param $maxalter
	 */
	public function getnews($maxalter, $studiengang_kz, $semester, $all=false, $fachbereich_kurzbz=null, $maxnews)
	{
		$qry = "SELECT * FROM campus.tbl_news WHERE true";
		if(trim($maxalter)!='0')
		{
			$qry.= " AND (now()-datum)<interval '$maxalter days'";
		}
		if(!$all)
			$qry.=' AND datum<=now() AND (datum_bis>= now()::date OR datum_bis is null)';
			
		if(trim($fachbereich_kurzbz)!='*')
		{
			if(is_null($fachbereich_kurzbz) || trim($fachbereich_kurzbz)=='')
				$qry.=' AND fachbereich_kurzbz is null';	
			else 
				$qry.=" AND fachbereich_kurzbz='".addslashes(trim($fachbereich_kurzbz))."'";
		}
				
		if(trim($studiengang_kz)=='0')
			$qry.=" AND studiengang_kz='".$studiengang_kz."' ".(trim($semester)!=''?(trim($semester)=='0'?' AND semester=0':''):' AND semester is null');
		elseif(trim($studiengang_kz)=='')
			$qry.='';
		else
			$qry.=" AND ((studiengang_kz='".trim($studiengang_kz)."' AND semester='".trim($semester)."') OR (studiengang_kz='".trim($studiengang_kz)."' AND semester=0) OR (studiengang_kz=0 AND semester='".trim($semester)."') OR (studiengang_kz=0 and semester is null))";
			
		$qry.=' ORDER BY datum DESC, updateamum DESC';
		if(trim($maxnews)!='0')
			$qry.= " LIMIT ".trim($maxnews);
#		echo "$maxalter, $studiengang_kz, $semester, $all, $fachbereich_kurzbz, $maxnews <br> $qry";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$newsobj = new news();
				
				$newsobj->news_id = $row->news_id;
				$newsobj->uid = $row->uid;
				$newsobj->studiengang_kz = $row->studiengang_kz;
				$newsobj->semester = $row->semester;
				$newsobj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$newsobj->betreff = $row->betreff;
				$newsobj->text = $row->text;
				$newsobj->verfasser = $row->verfasser;
				$newsobj->datum		= $row->datum;
				$newsobj->datum_bis = $row->datum_bis;
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
	 * Liefert die News fuer einen Fachbereich
	 *
	 * @param $fachbereich_kurzbz
	 * @param $datum
	 * @return boolean
	 */
	public function getFBNews($fachbereich_kurzbz, $datum)
	{
		$qry = "SELECT * FROM campus.tbl_news WHERE fachbereich_kurzbz='$fachbereich_kurzbz'";
		if($datum!='')
			$qry.=" AND datum='$datum'";
		$qry.=" ORDER BY datum";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$newsobj = new news();
				
				$newsobj->news_id = $row->news_id;
				$newsobj->uid = $row->uid;
				$newsobj->studiengang_kz = $row->studiengang_kz;
				$newsobj->semester = $row->semester;
				$newsobj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$newsobj->betreff = $row->betreff;
				$newsobj->text = $row->text;
				$newsobj->verfasser = $row->verfasser;
				$newsobj->datum		= $row->datum;
				$newsobj->datum_bis = $row->datum_bis;
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
	public function load($news_id)
	{		
		if(!is_numeric($news_id))
		{
			$this->errormsg = 'news_id muß eine gültige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM campus.tbl_news WHERE news_id = '$news_id';";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row = $this->db_fetch_object())
		{
			$this->news_id		= $row->news_id;
			$this->betreff			= $row->betreff;
			$this->text			= $row->text;
			$this->semester		= $row->semester;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->uid			= $row->uid;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->verfasser		= $row->verfasser;
			$this->datum			= $row->datum;
			$this->datum_bis		= $row->datum_bis;
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
	public function delete($news_id)
	{
		if(!is_numeric($news_id))
		{
			$this->errormsg = 'News_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM campus.tbl_news WHERE news_id='$news_id'";
		
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen';
			return false;
		}		
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{	
		//Laenge Pruefen
		if(mb_strlen($this->betreff)>128)           
		{
			$this->errormsg = 'Betreff darf nicht laenger als 128 Zeichen sein';
			return false;
		}		
		if(mb_strlen($this->verfasser)>64)
		{
			$this->errormsg = 'Verfasser darf nicht laenger als 64 Zeichen sein';
			return false;
		}	
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */	
	public function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz anlegen	
						
			$qry = 'INSERT INTO campus.tbl_news (betreff, text, semester, fachbereich_kurzbz, uid, studiengang_kz, verfasser, datum, datum_bis, insertamum, insertvon, 
				updateamum, updatevon) VALUES ('.
				$this->addslashes($this->betreff).', '.
				$this->addslashes($this->text).', '.
				$this->addslashes($this->semester).', '.
				$this->addslashes($this->fachbereich_kurzbz).', '.
				$this->addslashes($this->uid).', '.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->verfasser).', '.
				$this->addslashes($this->getSQLDate($this->datum)).', '.
				$this->addslashes($this->getSQLDate($this->datum_bis)).', '.
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
				'fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).', '.
				'uid='.$this->addslashes($this->uid).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'verfasser='.$this->addslashes($this->verfasser).', '.
				'datum='.$this->addslashes($this->getSQLDate($this->datum)).', '.
				'datum_bis='.$this->addslashes($this->getSQLDate($this->datum_bis)).', '.
				'insertamum='.$this->addslashes($this->insertamum).', '.
				'insertvon='.$this->addslashes($this->insertvon).', '.
				'updateamum='.$this->addslashes($this->updateamum).', '.
				'updatevon='.$this->addslashes($this->updatevon).'  '.
				'WHERE news_id = '.$this->addslashes($this->news_id).';';
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
	 * Ermittelt das Datumsformat fuer SQL
	 * @param $datum das konvertiert werden soll
	 * @return Datum wenn ok, false im Fehlerfall
	 */
	public function getSQLDate($datum)
	{
		if ( is_null($datum) || empty($datum) )
			return $datum;
	
		$date=explode('.',$datum);
		if (@checkdate($date[1], $date[0], $date[2]))
		{
			 return $date[2].'-'.$date[1].'-'.$date[0];	
		}	 	
		if (@checkdate($date[2], $date[0], $date[1]))
		{
			 return $date[0].'-'.$date[1].'-'.$date[2];	
		}	 	

		$date=explode('-',$datum);
		if (@checkdate($date[1], $date[0], $date[2]))
		{
			 return $date[2].'-'.$date[1].'-'.$date[0];	
		}	 	
		if (@checkdate($date[2], $date[0], $date[1]))
		{
			 return $date[0].'-'.$date[1].'-'.$date[2];	
		}	 	
		return false;
	
	}	
}
?>