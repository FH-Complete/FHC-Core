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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> 
 *
 */

/**
* Implementation super class
*/
class basis 
{
	/**
	* Error message
	* @var base_errors $msgs
	*/
	public $errormsg;
	
	/**
	* Constructor
	*
	* @access public
	*/
	public function __construct($db_system='pgsql')
	{
		//empty
	}

	public function getErrorMsg()
	{
		return $this->errormsg;
	}

	/**
	 * wenn $var '' ist wird NULL zurueckgegeben
	 * wenn $var !='' ist werden Datenbankkritische
	 * Zeichen mit Backslash versehen und das Ergbnis
	 * unter Hochkomma gesetzt.
	 *
	 * 12/2011 DEPRECATED use db_add_param
	 */
	public function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Splittet ein Array auf um es zB in der IN Klausel eines SQL Befehles zu verwenden
	 * Die einzelnen Elemente werden unter Hochkomma gesetzt und mit Beistrich getrennt.
	 * @param $array
	 */
	public function implode4SQL($array)
	{
		$string = '';
		foreach($array as $row)
		{
			if($string!='')
				$string.=',';
			$string.="'".addslashes($row)."'";
		}
		return $string;
	}
	
	/**
	 * Berechnet die Kalenderwoche eines gegebenen Datums
	 * Datum muss timestamp uebergeben werden
	 * @param $datum
	 */
	function kw($datum)
	{
		//$woche=date("W",mktime($date[hours],$date[minutes],$date[seconds],$date[mon],$date[mday],$date[year]));
		if (!date("w",$datum))
			$datum+=86400;
		//echo date("l j.m.Y - W",$datum);
		$woche=date("W",$datum);
		//if ($woche==53)
		//	$woche=1;
		return $woche;
	}
	
	function jump_week($datum, $wochen)
	{
		$stunde_vor=date("G",$datum);
		// Eine Woche sind 604800 Sekunden
		$datum+=604800*$wochen;
		$stunde_nach=date("G",$datum);
		if ($stunde_nach!=$stunde_vor)
			$datum+=3600;
		return $datum;
	}
	
	/**
	 * Konvertiert eine Zeichenkette, 
	 * damit diese in HTML Dokumenten sicher ausgegeben werden kann
	 * 
	 * @param $value
	 */
	public function convert_html_chars($value)
	{
		return htmlspecialchars($value);
	}
}
?>
