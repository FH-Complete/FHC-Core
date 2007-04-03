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

class vorschlag
{
	//Tabellenspalten
	var $vorschlag_id;
	var $frage_id;
	var $nummer;
	var $antwort;
	var $text;
	var $bild;
		
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen vorschlag
	// * @param $conn        	Datenbank-Connection
	// *        $frage_id       Frage die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function vorschlag($conn, $vorschlag_id=null, $unicode=false)
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
		
		if($vorschlag_id != null)
			$this->load($vorschlag_id);
	}
	
	// ***********************************************************
	// * Laedt Vorschlag mit der uebergebenen ID
	// * @param $vorschlag_id ID des Vorschlages der geladen werden soll
	// ***********************************************************
	function load($vorschlag_id)
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE vorschlag_id='".addslashes($vorschlag_id)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->vorschlag_id = $row->vorschlag_id;
				$this->frage_id = $row->frage_id;
				$this->antwort = $row->antwort;
				$this->nummer = $row->nummer;
				$this->text = $row->text;
				$this->bild = $row->bild;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $vorschlag_id";
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
			$qry = 'INSERT INTO testtool.tbl_vorschlag (frage_id, nummer, antwort, text, bild) VALUES('.
			       "'".addslashes($this->frage_id)."',".
			       $this->addslashes($this->nummer).",".
				   $this->addslashes($this->antwort).",".
			       $this->addslashes($this->text).",".
			       $this->addslashes($this->bild).");";
		}
		else
		{
			$qry = 'UPDATE testtool.tbl_vorschlag SET'.
			       ' frage_id='.$this->addslashes($this->frage_id).','.
			       ' nummer='.$this->addslashes($this->nummer).','.
			       ' antwort='.$this->addslashes($this->antwort).','.
			       ' text='.$this->addslashes($this->text);
			if($this->bild!='')
				$qry.=' , bild='.$this->addslashes($this->bild);
			$qry.=" WHERE vorschlag_id='".addslashes($this->vorschlag_id)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern der Frage:'.$qry;
			return false;
		}
	}

	function getVorschlag($frage_id)
	{
		$qry = "SELECT * FROM testtool.tbl_vorschlag WHERE frage_id='".addslashes($frage_id)."' ORDER BY nummer";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{		
				$vs = new vorschlag($this->conn);		
				$vs->vorschlag_id = $row->vorschlag_id;
				$vs->frage_id = $row->frage_id;
				$vs->nummer = $row->nummer;
				$vs->antwort = $row->antwort;
				$vs->text = $row->text;
				$vs->bild = $row->bild;
				
				$this->result[] = $vs;
				
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	function delete($vorschlag_id)
	{
		$qry = "DELETE FROM testtool.tbl_vorschlag WHERE vorschlag_id='".addslashes($vorschlag_id)."'";
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim loeschen';
			return false;
		}
	}
}
?>
