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
 * Klasse betriebsmitteltyp (FAS-Online)
 * @create 13-01-2007
 */

class betriebsmitteltyp
{
	var $conn;     			// resource DB-Handle
	var $errormsg; 			// string
	var $new;      			// boolean
	var $result = array();
	
	//Tabellenspalten
	var $betriebsmitteltyp;	//string
	var $beschreibung;   	//string
	var $anzahl; 			//smallint
	var $kaution;			//numeric(5,2)
	
	// ************************************************
	// * Konstruktor
	// * @param $conn Connection zur DB
	// *        $betriebsmitteltyp
	// ************************************************
	function betriebsmitteltyp($conn, $betriebsmitteltyp=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
				
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	= 'Encoding konnte nicht gesetzt werden';
				return false;
			}
		}
		
		if($betriebsmitteltyp!=null)
			$this->load($betriebsmitteltyp);
	}
		
	// *******************************************************
	// * Laedt die Funktion mit der ID $betriebsmitteltyp
	// * @param  $betriebsmitteltyp
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function load($code)
	{			
		$this->errormsg 		= 'Noch nicht implementiert';
		return false;
	}
	
	// *****************************************
	// * Laedt alle betriebsmitteltypen
	// * @return true wenn ok, false wenn Fehler
	// *****************************************
	function getAll()
	{
		$qry = "SELECT * FROM public.tbl_betriebsmitteltyp ORDER BY betriebsmitteltyp";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$bmt = new betriebsmitteltyp($this->conn, null, null);
				
				$bmt->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bmt->beschreibung = $row->beschreibung;
				$bmt->anzahl = $row->anzahl;
				$bmt->kaution = $row->kaution;
				
				$this->result[] = $bmt;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	
	
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	// ************************************************************
	// * Speichert die Daten in die Datenbank
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{		
		$dbanzahl=0;
		$qry='';
		$qry1='SELECT * FROM public.tbl_betriebsmitteltyp WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
		if($result1=pg_query($this->conn,$qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1 = pg_fetch_object($result1))
				{
					if($row1->anzahl==null)
					{
						$dbanzahl=0;
					}
					else 
					{
						$dbanzahl=$row1->anzahl;
					}
					$qry='UPDATE public.tbl_betriebsmitteltyp SET '.
					'anzahl ='.$dbanzahl."+".$this->anzahl.' '.
					'WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
					echo nl2br($qry."\n");
				}
			}
			else 
			{
				$qry='INSERT INTO public.tbl_betriebsmitteltyp (betriebsmitteltyp, beschreibung, anzahl, kaution) VALUES('.
					$this->addslashes($this->betriebsmitteltyp).', '.
					$this->addslashes($this->beschreibung).', '.
					$this->addslashes($this->anzahl).', '.
					$this->addslashes($this->kaution).');';
					echo nl2br($qry."\n");
			}	
			if(pg_query($this->conn,$qry))
			{
				return true;	
			}
			else
			{			
				$this->errormsg = 'Fehler beim Speichern des Betriebsmitteltypen-Datensatzes: '.$this->betriebsmitteltyp.' '.$qry;
				return false;
			}	
		}
		else
		{			
			$this->errormsg = 'Fehler beim Zugriff auf den Betriebsmitteltypen-Datensatz: '.$this->betriebsmitteltyp.' '.$qry;
			return false;
		}
	}
}
?>