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

class pruefling
{
	//Tabellenspalten
	var $pruefling_id;
	var $studiengang_kz;
	var $idnachweis;
	var $registriert;
	var $prestudent_id;
	var $gruppe_kurzbz;
		
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen pruefling
	// * @param $conn        	Datenbank-Connection
	// *        $frage_id       Frage die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function pruefling($conn, $pruefling_id=null, $unicode=false)
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
		
		if($pruefling_id != null)
			$this->load($pruefling_id);
	}
	
	// ***********************************************************
	// * Laedt Pruefling mit der uebergebenen ID
	// * @param $pruefling_id ID der Frage die geladen werden soll
	// ***********************************************************
	function load($pruefling_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE pruefling_id='".addslashes($pruefling_id)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $pruefling_id";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim laden: $qry";
			return false;
		}		
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
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'BEGIN;INSERT INTO testtool.tbl_pruefling (studiengang_kz, idnachweis, registriert, prestudent_id, gruppe_kurzbz) VALUES('.
			       $this->addslashes($this->studiengang_kz).",".
			       $this->addslashes($this->idnachweis).",".
			       $this->addslashes($this->registriert).",".
			       $this->addslashes($this->prestudent_id).",".
			       $this->addslashes($this->gruppe_kurzbz).");";
		}
		else
		{			
			$qry = 'UPDATE testtool.tbl_pruefling SET'.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' idnachweis='.$this->addslashes($this->idnachweis).','.
			       ' registriert='.$this->addslashes($this->registriert).','.
			       ' prestudent_id='.$this->addslashes($this->prestudent_id).','.
			       ' gruppe_kurzbz='.$this->addslashes($this->gruppe_kurzbz).
			       " WHERE pruefling_id='".addslashes($this->pruefling_id)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('testtool.tbl_pruefling_pruefling_id_seq') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->pruefling_id = $row->id;
						pg_query($this->conn, 'COMMIT;');
						return true;
					}
					else 
					{
						pg_query($this->conn, 'ROLLBACK;');
						$this->errormsg = 'Fehler beim lesen der Sequence';
						return false;
					}
				}
				else
				{
					pg_query($this->conn, 'ROLLBACK;');
					$this->errormsg = 'Fehler beim lesen der Sequence';
					return false;
				}
			}
			else 
			{
				return true;
			}
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern der Frage:'.$qry;
			return false;
		}
	}
	
	function getPruefling($prestudent_id)
	{
		$qry = "SELECT * FROM testtool.tbl_pruefling WHERE prestudent_id='".addslashes($prestudent_id)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->pruefling_id = $row->pruefling_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->idnachweis = $row->idnachweis;
				$this->registriert = $row->registriert;
				$this->prestudent_id = $row->prestudent_id;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $prestudent_id";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim laden: $qry";
			return false;
		}		
	}
}
?>
