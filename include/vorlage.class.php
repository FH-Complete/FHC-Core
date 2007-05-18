<?php
/* Copyright (C) 2007 Technikum-Wien
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

class vorlage
{
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;
	
	//Tabellenspalten
	var $vorlage_kurzbz;
	var $studiengang_kz;
	var $version;
	var $text;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection
	// * @param $conn	Datenbank-Connection
	// *************************************************************************
	function vorlage($conn)
	{
		$this->conn = $conn;
	}

	// ***********************************************************
	// * Laedt Vorschlag mit der uebergebenen ID
	// * @param $vorlage_kurzbz, studiengang_kz, version
	// ***********************************************************
	function load($vorlage_kurzbz, $studiengang_kz, $version)
	{
		return false;
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

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		return true;
	}

	// ******************************************************************
	// * Speichert die Benutzerdaten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * ansonsten der Datensatz mit $uid upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************
	function save()
	{
		return false;
	}

	function getAktuelleVorlage($studiengang_kz, $vorlage_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_vorlagestudiengang WHERE 
				(studiengang_kz=0 OR studiengang_kz='".addslashes($studiengang_kz)."') AND 
				vorlage_kurzbz='".addslashes($vorlage_kurzbz)."' ORDER BY studiengang_kz DESC, version DESC LIMIT 1";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->vorlage_kurzbz = $row->vorlage_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->version = $row->version;
				$this->text = $row->text;
			}
			else 
			{
				$this->errormsg = 'Keine Vorlage gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Vorlage';
			return false;
		}
	}
	
}
?>
