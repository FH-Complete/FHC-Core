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

class berechtigung
{
	/**
	 * interne userberechtigung_id (Zaehler aus DB)
	 * @var integer
	 */
	var $userberechtigung_id;
	/**
	 * @var integer
	 */
	var $studiengang_kz;
	/**
	 * @var integer
	 */
	var $fachbereich_id;
	/**
	 * @var string
	 */
	var $berechtigung_kurzbz;
	/**
	 * @var string
	 */
	var $uid;
	/**
	 * @var string
	 */
	var $studiensemester_kurzbz;
	/**
	 * @var integer
	 */
	var $start;
	/**
	 * @var integer
	 */
	var $ende;
	/**
	 * @var integer
	 */
	var $starttimestamp;
	/**
	 * @var integer
	 */
	var $endetimestamp;
	/**
	 * @var string
	 */
	var $art;

	/**
	 * @var array
	 */
	var $berechtigungen=array();

	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean

	//Tabellenspalten
	var $beschreibung;			// varchar(256)

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $berechtigung_kurzbz
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function berechtigung($conn, $berechtigung_kurzbz=null, $unicode=false)
	{
		$this->conn = $conn;
		$this->new=true;

		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}

		if($berechtigung_kurzbz!=null)
			$this->load($berechtigung_kurzbz);
	}

	// *********************************************************
	// * Laedt eine Berechtigung
	// * @param berechtigung_kurzbz
	// *********************************************************
	function load($berechtigung_kurzbz)
	{
		return true;
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->berechtigung_kurzbz)>16)
		{
			$this->errormsg = 'Berechtigung_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->beschreibung)>256)
		{
			$this->errormsg = 'Beschreibung darf nicht laenger als 256 Zeichen sein';
			return false;
		}

		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird NULL zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische
	// * Zeichen mit Backslash versehen und das Ergbnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert Berechtigung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'INSERT INTO tbl_berechtigung (berechtigung_kurzbz, beschreibung)
			        VALUES('.$this->addslashes($this->berechtigung_kurzbz).','.
					$this->addslashes($this->beschreibung).');';
		}
		else
		{
			$qry = 'UPDATE tbl_berechtigung SET'.
			       ' beschreibung='.$this->addslashes($this->beschreibung).
			       " WHERE berechtigung_kurzbz='".addslashes($this->berechtigung_kurzbz)."'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Berechtigung:'.$qry;
			return false;
		}
	}
}
?>