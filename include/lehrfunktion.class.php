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
class lehrfunktion
{
	var $conn;     // @var resource DB-Connection
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $lehrfunktionen = array(); // @var lehrfunktion Objekt

	var $lehrfunktion_kurzbz; // @var varchar(16)
	var $beschreibung;        // @var varchar(256)
	var $standardfaktor;      // @var numeric(3,2)

	// **
	// * Konstruktor
	// * @param conn Connection zur DB
	// *        lehrfunktion_kurzbz kurzbezeichnung der zu ladenden Funktion
	// *
	function lehrfunktion($conn, $lehrfunktion_kurzbz=null, $unicode=false)
	{
		$this->conn = $conn;
/*
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
			return false;
		}
*/
		if($lehrfunktion_kurzbz!=null)
			$this->load($lehrfunktion_kurzbz);
	}

	/**
	 * Laedt eine Lehrfunktion
	 * @param lehrfunktion_kurzbz ID des Datensatzes der zu laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($lehrfunktion_kurzbz)
	{
		$qry = "SELECT * FROM lehre.tbl_lehrfunktion WHERE lehrfunktion_kurzbz = '".addslashes($lehrfunktion_kurzbz)."';";

		if(!$result = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}

		if($row = pg_fetch_object($result))
		{
			$this->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
			$this->beschreibung = $row->beschreibung;
			$this->standardfaktor = $row->standardfaktor;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}

	/**
	 * Laedt alle Lehrfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM lehre.tbl_lehrfunktion ORDER BY lehrfunktion_kurzbz;";

		if(!$result = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($result))
		{
			$lehrfkt_obj = new lehrfunktion($this->conn);

			$lehrfkt_obj->lehrfunktion_kurzbz = $row->lehrfunktion_kurzbz;
			$lehrfkt_obj->beschreibung = $row->beschreibung;
			$lehrfkt_obj->standardfaktor = $row->standardfaktor;

			$this->lehrfunktionen[] = $lehrfkt_obj;
		}
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}

	/**
	 * Loescht den Datensatz mit der ID die uebergeben wird
	 * @param lehrfunktion_kurzbz ID des zu loeschenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($lehrfunktion_kurzbz)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>