<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */

/**
 * Klasse Sprache
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class sprache extends basis_db
{
	public $result; 
	
	public $sprache; 	// string
	public $locale; 	
	public $index; 		// int, id des array index
	public $content;	// boolean 
	
	/**
	 * 
	 * Konstruktor
	 * @param Sprache die geladen werden soll (Default=null)
	 */
	public function __construct($sprache = null)
	{
		parent::__construct();
		
		if(!is_null($sprache))
			$this->load($sprache);
	}

	/**
	 * 
	 * Lädt die Sprache der 
	 * @param $sprache die geladen werden soll
	 * @return true bei Erfolg, false wenn ein Fehler aufgetreten ist
	 */
	public function load($sprache)
	{
		$qry = "SELECT * from public.tbl_sprache WHERE sprache = ".addslashes($sprache)."; ";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler bei der Abfrage.";
			return false; 
		}
		if($row = $this->db_fetch_object())
		{
			$this->sprache = $row->sprache; 
			$this->locale = $row->locale; 
			$this->index = $row->index; 
			//$this->content = $row->content; 
		}
		return true; 		
	}
	
	/**
	 * 
	 * Lädt alle verfügbaren Sprachen
	 * @return true bei Erfolg, false wenn ein Fehler aufgetreten ist. 
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM public.tbl_sprache;";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage.";
			return false;
		}
		
		if($row = $this->db_fetch_object())
		{
			$sprache = new sprache();
			$sprache->sprache = $row->sprache;
			$sprache->locale = $row->locale;
			$sprache->index = $row->index;
			//$sprache->content = $row->content; 
			
			$this->result[] = $sprache; 
		}
		return true; 
	}
	
	/**
	 * 
	 * Löscht die übergebene Sprache
	 * @param $sprache die gelöscht werden soll
	 */
	public function delete($sprache)
	{
		$qry = "DELETE FROM public.tbl_sprache WHERE sprache = ".addslashes($sprache).";";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler beim löschen der Sprache aufgetreten.";
			return false; 
		}
		return true; 
		
	}
	
	/**
	 * 
	 * Liefert die Anzahl aller aktiven Sprachen zurück
	 * @return $anzahl der Sprachen, false im Fehlerfall
	 */
	public function getAnzahl()
	{
		$anzahl = 0; 
		$qry = 'SELECT count(sprache) as anzahl FROM public.tbl_sprache WHERE content = true;';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler aufgetreten';
			return false;
		}
		
		if($row = $this->db_fetch_object())
		{
			$anzahl = $row->anzahl; 
		}
		
		return $anzahl; 
	}
	
	/**
	 * 
	 * Liefert die Sprache eines Index zurück
	 * @param $index der Sprache die gesucht wird
	 * @return $sprache, false im Fehlerfall
	 */
	public function getSpracheFromIndex($index)
	{
		$sprache = ''; 
		$qry = "SELECT sprache FROM public.tbl_sprache WHERE index = $index ;";
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler aufgetreten.";
			return false;
		}
		
		if($row = $this->db_fetch_object())
		{
			$sprache = $row->sprache; 
		}
		return $sprache; 
	}
}