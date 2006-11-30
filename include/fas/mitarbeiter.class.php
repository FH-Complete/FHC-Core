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

class mitarbeiter extends benutzer
{
	
    //Tabellenspalten
	var $ausbildungcode;	//integer
	var $personalnummer;	//serial
	var $kurzbz;			//varchar(8)
	var $lektor;			//boolean
	var $fixangestellt;		//boolean
	var $telefonklappe;		//varchar(25)

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $uid            Mitarbeiter der geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function mitarbeiter($conn, $uid=null, $unicode=false)
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
		
		//Mitarbeiter laden
		//if($uid!=null)
		//	$this->load($uid);
	}
	
	// ************************************************
	// * ueberprueft die Variablen auf Gueltigkeit
	// * @return true wenn gueltig, false im Fehlerfall
	// ************************************************
	function validate()
	{	    
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss eingegeben werden';
			return false;
		}
		if($this->ausbildungcode!='' && !is_numeric($this->ausbildungcode))
		{
			$this->errormsg = 'Ausbildungscode ist ungueltig';
			return false;
		}		
		if($this->personalnummer!='' && !is_numeric($this->personalnummer))
		{
			$this->errormsg = 'Personalnummer muss eine gueltige Zahl sein';
			return false;
		}		
		if(strlen($this->kurzbz)>8)
		{
			$this->errormsg = 'kurzbz darf nicht laenger als 8 Zeichen sein';
			return false;
		}
		if(!is_bool($this->lektor))
		{
			$this->errormsg = 'lektor muss boolean sein'.$this->lektor;
			return false;
		}
		if(!is_bool($this->fixangestellt))
		{
			$this->errormsg = 'fixangestellt muss boolean sein';
			return false;
		}
		if(strlen($this->telefonklappe)>25)
		{
			$this->errormsg = 'telefonklappe darf nicht laenger als 25 Zeichen sein';
			return false;
		}
		if(strlen($this->updatevon)>32)
		{
			$this->errormsg = 'updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
			
		return true;
	}
	
	
	// *************************************************
	// * Speichert die Mitarbeiterdaten in die Datenbank
	// * @return true wenn ok, false im Fehlerfall
	// *************************************************
	function save()
	{
		//Variablen checken		
		if(!$this->validate())
			return false;
			
		pg_query($this->conn,'BEGIN;');
		//Basisdaten speichern
		if(!benutzer::save())
		{
			pg_query($this->conn,'ROLLBACK;');
			return false;
		}
		
		if($this->new)
		{
			//Neuen Datensatz anlegen							
			$qry = "INSERT INTO tbl_mitarbeiter(uid, ausbildungcode, personalnummer, kurzbz, lektor, 
			                    fixangestellt, telefonklappe, updateamum, updatevon)
			        VALUES('".addslashes($this->uid)."',".
			 	 	$this->addslashes($this->ausbildungcode).",".
			 	 	$this->addslashes($this->personalnummer).",". //TODO: in Produktivversion nicht angeben
			 	 	$this->addslashes($this->kurzbz).','.
			 	 	($this->lektor?'true':'false').','.
					($this->fixangestellt?'true':'false').','.
					$this->addslashes($this->telefonklappe).','.
					$this->addslashes($this->updateamum).','.
					$this->addslashes($this->updatevon).');';
		}
		else 
		{
			//Bestehenden Datensatz updaten
			$qry = 'UPDATE tbl_mitarbeiter SET'.
			       ' ausbildungcode='.$this->addslashes($this->ausbildungcode).','.
			       " personalnummer=".$this->addslashes($this->personalnummer).",". //TODO: in Produktivversion nicht angeben
			       ' kurzbz='.$this->addslashes($this->kurzbz).','.
			       ' lektor='.($this->lektor?'true':'false').','.
			       ' fixangestellt='.($this->fixangestellt?'true':'false').','.
			       ' telefonklappe='.$this->addslashes($this->telefonklappe).','.
			       ' updateamum='.$this->addslashes($this->updateamum).','.
			       ' updatevon='.$this->addslashes($this->updatevon).
			       " WHERE uid='".addslashes($this->uid)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			pg_query($this->conn,'COMMIT;');
			//Log schreiben
			return true;
		}
		else 
		{			
			pg_query($this->conn,'ROLLBACK;');
			$this->errormsg = 'Fehler beim Speichern des Mitarbeiter-Datensatzes'.$qry;
			return false;
		}
	}
}
?>