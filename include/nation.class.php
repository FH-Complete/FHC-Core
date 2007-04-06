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
 * Klasse Nation (FAS-Online)
 * @create 06-04-2006
 */

class nation
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $nation = array(); // nation Objekt
	
	//Tabellenspalten
	var $code;   
	var $sperre; 
	var $kontinent;
	var $entwicklungsstand;
	var $eu;
	var $ewr;
	var $kurztext;
	var $langtext;
	var $engltext;
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $code      Zu ladende Nation
	 */
	function nation($conn, $code=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else 
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
		
		//if($person_id != null)
		//	$this->load($person_id);
	}
	
	
	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $code code der zu ladenden Nation
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($code)
	{			
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Laedt alle Nationen
	 * @param ohnesperre wenn dieser Parameter auf true gesetzt ist werden
	 *        nur die nationen geliefert dessen Buerger bei uns studieren duerfen
	 */
	function getAll($ohnesperre=false)
	{
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM bis.tbl_nation";
		if($ohnesperre)
			$qry .= " WHERE sperre is null";
			
		$qry .=" ORDER BY kurztext";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$nation = new nation($this->conn);
		
			$nation->code = $row->nation_code;
			$nation->sperre = ($row->sperre=='t'?true:false); 
			$nation->kontinent = $row->kontinent;
			$nation->entwicklungsstand = $row->entwicklungsstand;
			$nation->eu = ($row->eu=='t'?true:false);
			$nation->ewr = ($row->ewr=='t'?true:false);
			$nation->kurztext = $row->kurztext;
			$nation->langtext = $row->langtext;
			$nation->engltext = $row->engltext;
			
			$this->nation[] = $nation;
		}
		return true;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	// ************************************************************
	// * Speichert die Personendaten in die Datenbank
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{		
		
		
		$qry='INSERT INTO bis.tbl_nation (nation_code, entwicklungsstand, eu, ewr, kontinent, kurztext, langtext, engltext, sperre) VALUES('.
			$this->addslashes($this->code).', '.
			$this->addslashes($this->entwicklungsstand).', '.
			$this->addslashes($this->eu).', '.
			$this->addslashes($this->ewr).', '.
			$this->addslashes($this->kontinent).', '.
			$this->addslashes($this->kurztext).', '.
			$this->addslashes($this->langtext).', '.
			$this->addslashes($this->engltext).', '.
			$this->addslashes($this->sperre).');';
		

		if(pg_query($this->conn,$qry))
		{
			return true;	
		}
		else
		{			
			$this->errormsg = 'Fehler beim Speichern des Nationen-Datensatzes:'.$this->code.' '.$qry;
			return false;
		}
	}
}
?>