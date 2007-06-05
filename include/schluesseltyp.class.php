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
 * Klasse schluesseltyp (FAS-Online)
 * @create 03-01-2006
 */

class schluesseltyp
{
	var $conn;     				// resource DB-Handle
	var $errormsg; 			// string
	var $new;      				// boolean
	//var $schluesseltyp = array(); 	// schluesseltyp Objekt

	//Tabellenspalten
	var $schluesseltyp;			//string
	var $beschreibung;   		//string
	var $anzahl; 				//smallint
	var $kaution;				//numeric(5,2)

	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $code      Zu ladende Schluesseltyp
	 */
	function schluesseltyp($conn, $code=null, $unicode=false)
	{
		$this->conn = $conn;

		if($unicode)
			$qry 			= "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry 			= "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	= 'Encoding konnte nicht gesetzt werden';
			return false;
		}
	}


	/**
	 * Laedt die Funktion mit der ID $schluesseltyp
	 * @param  $code code des zu ladenden  schluesseltyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($code)
	{
		$this->errormsg 		= 'Noch nicht implementiert';
		return false;
	}

	/**
	 * Laedt alle schluesseltypen
	 */
	function getAll()
	{
		$this->errormsg 		= 'Noch nicht implementiert';
		return false;
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
		$qry1='SELECT * FROM public.tbl_schluesseltyp WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
		if($result1=pg_query($this->conn,$qry1))
		{
			if(pg_num_rows($result1)>0) //eintrag gefunden
			{
				if($row1 = pg_fetch_object($result1))
				{
					if($row1->anzahl=null)
					{
						$dbanzahl=0;
					}
					else
					{
						$dbanzahl=$row1->anzahl;
					}
					$qry='UPDATE public.tbl_schluesseltyp SET '.
					'anzahl ='.$dbanzahl."+".$this->anzahl.' '.
					'WHERE beschreibung='.$this->addslashes($this->beschreibung).';';
					echo nl2br($qry."\n");
				}
			}
			else
			{
				$qry='INSERT INTO public.tbl_schluesseltyp (schluesseltyp, beschreibung, anzahl, kaution) VALUES('.
					$this->addslashes($this->schluesseltyp).', '.
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
				$this->errormsg = 'Fehler beim Speichern des Schluesseltypen-Datensatzes: '.$this->schluesseltyp.' '.$qry;
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Zugriff auf den Schluesseltypen-Datensatz: '.$this->schluesseltyp.' '.$qry;
			return false;
		}
	}
}
?>