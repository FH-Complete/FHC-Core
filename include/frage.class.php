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

class frage
{
	//Tabellenspalten
	var $frage_id;
	var $gebiet_id;
	var $gruppe_kurzbz;
	var $loesung;
	var $nummer;
	var $demo;
	var $text;
	var $bild;
		
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine frage
	// * @param $conn        	Datenbank-Connection
	// *        $frage_id       Frage die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function frage($conn, $frage_id=null, $unicode=false)
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
		
		if($frage_id != null)
			$this->load($frage_id);
	}
	
	// ***********************************************************
	// * Laedt Frage mit der uebergebenen ID
	// * @param $frage_id ID der Frage die geladen werden soll
	// ***********************************************************
	function load($frage_id)
	{
		$qry = "SELECT * FROM testtool.tbl_frage WHERE frage_id='".addslashes($frage_id)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->frage_id = $row->frage_id;
				$this->gebiet_id = $row->gebiet_id;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->loesung = $row->loesung;
				$this->nummer = $row->nummer;
				$this->demo = ($row->demo=='t'?true:false);
				$this->text = $row->text;
				$this->bild = $row->bild;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $frage_id";
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
			$qry = 'INSERT INTO testtool.tbl_frage (frage_id, gebiet_id, gruppe_kurzbz, loesung, nummer, demo, text, bild) VALUES('.
			       "'".addslashes($this->frage_id)."',".
			       $this->addslashes($this->gebiet_id).",'".
			       $this->addslashes($this->gruppe_kurzbz).",'".
			       $this->addslashes($this->loesung).",".
			       $this->addslashes($this->nummer).",".
			       ($this->demo?'true':'false').",'".
			       $this->text."',".
			       $this->addslashes($this->bild).");";
		}
		else
		{			
			$qry = 'UPDATE testtool.tbl_frage SET'.
			       ' frage_id='.$this->addslashes($this->frage_id).','.
			       ' gebiet_id='.$this->addslashes($this->gebiet_id).','.
			       " gruppe_kurzbz='".$this->gruppe_kurzbz."',".
			       ' loesung='.$this->addslashes($this->loesung).','.
			       ' nummer='.$this->addslashes($this->nummer).','.
			       ' demo='.($this->demo?'true':'false').','.
			       ' text='.$this->addslashes($this->text).','.
			       ' bild='.$this->addslashes($this->bild).
			       " WHERE frage_id='".addslashes($this->frage_id)."';";
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

	function getFrage($gebiet_id, $nummer, $gruppe_kurzbz)
	{
		$qry = "SELECT * FROM testtool.tbl_frage WHERE gebiet_id='$gebiet_id' AND nummer='$nummer' AND gruppe_kurzbz='$gruppe_kurzbz'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{				
				$this->frage_id = $row->frage_id;
				$this->gebiet_id = $row->gebiet_id;
				$this->gruppe_kurzbz = $row->gruppe_kurzbz;
				$this->loesung = $row->loesung;
				$this->nummer = $row->nummer;
				$this->demo = ($row->demo=='t'?true:false);
				$this->text = $row->text;
				$this->bild = $row->bild;
				
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler beim laden';
				return false;
			}			
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	
	function getNextFrage($gebiet_id, $gruppe_kurzbz, $frage_id, $demo=false)
	{
		$qry = "SELECT frage_id FROM testtool.tbl_frage WHERE gebiet_id='".addslashes($gebiet_id)."' AND gruppe_kurzbz='".addslashes($gruppe_kurzbz)."' AND nummer".($demo?'<':'>')."(SELECT nummer FROM testtool.tbl_frage WHERE frage_id='".addslashes($frage_id)."') ";
		if($demo)
		{
			$qry.=" AND demo=true";
			$order = 'DESC';
		}
		else 
			$order = 'ASC';
			
		$qry.=" ORDER BY nummer $order LIMIT 1";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
				return $row->frage_id;
			else 
				return false;
		}
		else 
			return false;
	}
}
?>
