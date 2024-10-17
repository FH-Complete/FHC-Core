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
	public $semester;			// smallint
	public $fachbereich_kurzbz;// varchar(16)
	public $uid;				// varchar(16)
	public $studiengang_kz;	// integer
	public $datum;				// @date
	public $updateamum;		// timestamp
	public $updatevon=0;		// string
	public $insertamum;		// timestamp
	public $insertvon=0;		// string
	public $datum_bis;			// @date
	public $content_id;
	
	public $betreff;
	public $verfasser;
	public $text;
	
	
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
	 * Laedt alle News die nicht aelter
	 * als $maxalter Tage sind
	 * @param $maxalter
	 * @param $studiengang_kz
	 * @param $semester
	 * @param $all Sollen alle Eintraege angezeigt werden
	 * @param $fachbereich_kurzbz
	 * @param $maxnews Limit
	 * @param $mischen Sollen die allgemeinen News auch gemischt mit den anderen angezeigt werden
	 */
	public function getnews($maxalter, $studiengang_kz, $semester, $all=false, $fachbereich_kurzbz=null, $maxnews, $mischen=true)
	{
		$qry = "SELECT * FROM campus.tbl_news WHERE true";
		if(trim($maxalter)!='0')
		{
			$qry.= " AND (now()-datum)<interval '$maxalter days'";
		}
		if(!$all)
			$qry.=" AND datum<=now() AND (datum_bis>= now()::date OR datum_bis is null)";
			
		if(trim($fachbereich_kurzbz)!='*')
		{
			if(is_null($fachbereich_kurzbz) || trim($fachbereich_kurzbz)=='')
				$qry.=" AND fachbereich_kurzbz is null";	
			else 
				$qry.=" AND fachbereich_kurzbz=".$this->db_add_param(trim($fachbereich_kurzbz));
		}
				
		if(trim($studiengang_kz)=='0')
			$qry.=" AND studiengang_kz=".$this->db_add_param($studiengang_kz)." ".(trim($semester)!=''?(trim($semester)=='0'?' AND semester=0':''):' AND semester is null');
		elseif(trim($studiengang_kz)=='')
			$qry.='';
		else
			$qry.=" AND ((studiengang_kz=".$this->db_add_param(trim($studiengang_kz))." AND semester=".$this->db_add_param(trim($semester)).") OR (studiengang_kz=".$this->db_add_param(trim($studiengang_kz))." AND semester=0) OR (studiengang_kz=0 AND semester=".$this->db_add_param(trim($semester)).") ".($mischen===true?"OR (studiengang_kz=0 and semester is null)":"").")";	
		$qry.=' ORDER BY datum DESC';
		if(trim($maxnews)!='0')
			$qry.= " LIMIT ".trim($maxnews);

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
				$newsobj->datum		= $row->datum;
				$newsobj->datum_bis = $row->datum_bis;
				$newsobj->updateamum = $row->updateamum;
				$newsobj->updatevon = $row->updateamum;
				$newsobj->insertamum = $row->insertamum;
				$newsobj->insertvon = $row->insertvon;
				$newsobj->content_id = $row->content_id;
				
				$newsobj->betreff = $row->betreff;
				$newsobj->verfasser = $row->verfasser;
				$newsobj->text = $row->text;
				
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
		
		$qry = "SELECT * FROM campus.tbl_news WHERE news_id=".$this->db_add_param($news_id, FHC_INTEGER).";";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
		
		if($row = $this->db_fetch_object())
		{
			$this->news_id = $row->news_id;
			$this->semester	= $row->semester;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->uid = $row->uid;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->datum = $row->datum;
			$this->datum_bis = $row->datum_bis;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->content_id = $row->content_id;
			
			$this->betreff = $row->betreff;
			$this->verfasser = $row->verfasser;
			$this->text = $row->text;
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
		
		if($this->load($news_id))
		{
			$qry = "
				DELETE FROM campus.tbl_news WHERE news_id=".$this->db_add_param($news_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_contentsprache WHERE content_id=".$this->db_add_param($this->content_id, FHC_INTEGER).";
				DELETE FROM campus.tbl_content WHERE content_id=".$this->db_add_param($this->content_id, FHC_INTEGER);
			
			if($this->db_query($qry))
				return true;
			else
			{
				$this->errormsg = 'Fehler beim Löschen';
				return false;
			}
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{	
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
						
			$qry = 'BEGIN;INSERT INTO campus.tbl_news (semester, fachbereich_kurzbz, uid, studiengang_kz, datum, datum_bis, 
						insertamum, insertvon, updateamum, updatevon, content_id) VALUES ('.
				$this->db_add_param($this->semester).', '.
				$this->db_add_param($this->fachbereich_kurzbz).', '.
				$this->db_add_param($this->uid).', '.
				$this->db_add_param($this->studiengang_kz).', '.
				$this->db_add_param($this->datum).', '.
				$this->db_add_param($this->datum_bis).', '.
				$this->db_add_param($this->insertamum).', '.
				$this->db_add_param($this->insertvon).', '.
				$this->db_add_param($this->updateamum).', '.
				$this->db_add_param($this->updatevon).','.
				$this->db_add_param($this->content_id, FHC_INTEGER).'); ';
				
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
				'semester='.$this->db_add_param($this->semester).', '.
				'fachbereich_kurzbz='.$this->db_add_param($this->fachbereich_kurzbz).', '.
				'uid='.$this->db_add_param($this->uid).', '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz).', '.
				'datum='.$this->db_add_param($this->datum).', '.
				'datum_bis='.$this->db_add_param($this->datum_bis).', '.
				'insertamum='.$this->db_add_param($this->insertamum).', '.
				'insertvon='.$this->db_add_param($this->insertvon).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).', '.
				'content_id='.$this->db_add_param($this->content_id, FHC_INTEGER).' '.
				'WHERE news_id = '.$this->db_add_param($this->news_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('campus.tbl_news_news_id_seq') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->news_id=$row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->errormsg='Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else
				{
					$this->errormsg='Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
					return false;
				}
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
