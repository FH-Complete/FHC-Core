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
	public static $index_arr = array();
	public $sprache; 	// string
	public $locale;
	public $index; 		// int, id des array index
	public $content;	// boolean
	public $bezeichnung_arr;

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
	 * Lädt die Sprache
	 * @param $sprache die geladen werden soll
	 * @return true bei Erfolg, false wenn ein Fehler aufgetreten ist
	 */
	public function load($sprache)
	{
		$qry = "SELECT *,".$this->getSprachQuery('bezeichnung')." FROM public.tbl_sprache WHERE sprache=".$this->db_add_param($sprache, FHC_STRING, false).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->sprache = $row->sprache;
				$this->locale = $row->locale;
				$this->index = $row->index;
				$this->content = $this->db_parse_bool($row->content);
				$this->bezeichnung_arr=$this->parseSprachResult('bezeichnung',$row);
				return true;
			}
			else
			{
				$this->errormsg = 'Sprache nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage.";
			return false;
		}
	}

	/**
	 *
	 * Lädt alle verfügbaren Sprachen
	 * @param boolean $content Default:null Ist die Sprache relevant fuer die Sprachauswahl
	 * @param string $order Default:sprache Spalte, nach der die Ergebnisse sortiert werden
	 * @return true bei Erfolg, false wenn ein Fehler aufgetreten ist.
	 */
	public function getAll($content=null, $order='sprache')
	{
		$qry = "SELECT *,".$this->getSprachQuery('bezeichnung')." FROM public.tbl_sprache";

		if(!is_null($content))
			$qry.= " WHERE content=".$this->db_add_param($content, FHC_BOOLEAN);
		$qry.=" ORDER BY ".$order;

		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage.";
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$sprache = new sprache();
			$sprache->sprache = $row->sprache;
			$sprache->locale = $row->locale;
			$sprache->index = $row->index;
			$sprache->content = $this->db_parse_bool($row->content);
			$sprache->bezeichnung_arr=$this->parseSprachResult('bezeichnung',$row);

			$this->result[] = $sprache;
		}
 			return true;
	}

	/**
	 *
	 * Lädt das Index Array
	 * @return true bei Erfolg, false wenn ein Fehler aufgetreten ist.
	 */
	public function loadIndexArray()
	{
		$qry = "SELECT sprache, index FROM public.tbl_sprache WHERE index is not null ORDER BY index";

		if(!$this->db_query($qry))
		{
			$this->errormsg ="Fehler bei der Abfrage.";
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			self::$index_arr[$row->sprache]=$row->index;
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
		$qry = "DELETE FROM public.tbl_sprache WHERE sprache = ".$this->db_add_param($sprache, FHC_STRING, false).";";

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
		$qry = "SELECT sprache FROM public.tbl_sprache WHERE index=".$this->db_add_param($index, FHC_INTEGER, false).";";

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

	/**
	 *
	 * Liefert den Index einer Sprache zurück
	 * @param $sprache der Sprache die gesucht wird
	 * @return $index, false im Fehlerfall
	 */
	public function getIndexFromSprache($sprache)
	{
		$index = false;
		$qry = "SELECT * FROM public.tbl_sprache WHERE sprache=".$this->db_add_param($sprache).";";

		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler aufgetreten.";
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$index = $row->index;
		}
		return $index;
	}

	public function getAllIndexesSorted()
	{
		$languages = array();

		if(!isset($this->result))
			$this->getAll(true);

		foreach($this->result as $s)
		{
			$languages[$s->index] = $s->sprache;
		}
		ksort($languages);

		//lücken im sprachenarray füllen
		for($i = 1; $i<=max(array_keys($languages));$i++)
		{
			if(!isset($languages[$i]))
				$languages[$i] = false;
		}
		return $languages;
	}

	/**
	 * Liefert einen String mit den aufgespaltenen Elementen fuer mehrsprachige Arrays
	 * Der Result der Query kann dann mittels parseSprachResult wieder in ein Array umgewandelt werden
	 *
	 * @param $feldname
	 * @return string mit den aufgeschluesselten Arrayelementen der Sprache
	 */
	public function getSprachQuery($feldname)
	{
		$result = '';

		if(!isset(self::$index_arr) || count(self::$index_arr)==0)
			$this->loadIndexArray();

		foreach(self::$index_arr as $sprache=>$index)
		{
			$result .= $feldname.'['.$index.'] as '.$feldname.'_'.$index.',';
		}
		return mb_substr($result,0,-1);
	}

	/**
	 * Wandelt den Result von mehrsprachigen Arrays in einer SQL Query in ein PHP Array um
	 *
	 * @param $feldname name der Datenbankspalte
	 * @param $row row des SQL Results
	 * @return array mit den Sprachen der Spalte
	 */
	public function parseSprachResult($feldname, $row)
	{
		$result = array();

		if(!isset(self::$index_arr) || count(self::$index_arr)==0)
			$this->getAll();

		foreach(self::$index_arr as $sprache=>$index)
		{
			$name = $feldname.'_'.$index;
			if(isset($row->$name))
				$result[$sprache] = $row->$name;
			else
				$result[$sprache] = null;
		}
		return $result;
	}

	/**
	 *
	 * Liefert die Bezeichnung einer Sprache in der angegebenen Sprache
	 * @param $sprache Kurzbezeichnung der Sprache dessen Bezeichnung geladen werden soll
	 * @param $anzeigesprache Sprache in der die bezeichnung geladen werden soll
	 */
	public function getBezeichnung($sprache, $anzeigesprache)
	{
		$qry = "SELECT bezeichnung[(SELECT index FROM public.tbl_sprache WHERE sprache=".$this->db_add_param($anzeigesprache).")]
				FROM public.tbl_sprache WHERE sprache=".$this->db_add_param($sprache);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->bezeichnung;
			}
		}
	}
}
