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

class antwort
{
	//Tabellenspalten
	var $antwort_id;
	var $pruefling_id;
	var $vorschlag_id;
		
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
	var $errormsg;
	var $new;
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Antwort
	// * @param $conn        	Datenbank-Connection
	// *        $frage_id       Frage die geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function antwort($conn, $antwort_id=null, $unicode=false)
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
		if($antwort_id != null)
			$this->load($antwort_id);
	}
	
	// ***********************************************************
	// * Laedt antwort mit der uebergebenen ID
	// * @param $antwort_id ID der Frage die geladen werden soll
	// ***********************************************************
	function load($antwort_id)
	{
		$qry = "SELECT * FROM testtool.tbl_antwort WHERE antwort_id='".addslashes($antwort_id)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->antwort_id = $row->antwort_id;
				$this->pruefling_id = $row->pruefling_id;
				$this->vorschlag_id = $row->vorschlag_id;
				return true;
			}
			else 
			{
				$this->errormsg = "Kein Eintrag gefunden fuer $antwort_id";
				return false;
			}				
		}
		else 
		{
			$this->errormsg = "Fehler beim Laden der Antwort";
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
	// * Speichert die Daten in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * ansonsten upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO testtool.tbl_antwort (pruefling_id, vorschlag_id) VALUES('.
			       $this->addslashes($this->pruefling_id).",".
			       $this->addslashes($this->vorschlag_id).");";
		}
		else
		{			
			$qry = 'UPDATE testtool.tbl_antwort SET'.
			       ' vorschlag_id='.$this->addslashes($this->vorschlag_id).','.
			       " pruefling_id='".$this->pruefling_id."'".
			       " WHERE antwort_id='".addslashes($this->antwort_id)."'";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern der Antwort:'.$qry;
			return false;
		}
	}
	
	/**
	 * Loescht einen Eintrag aus der Tabelle tbl_antwort
	 *
	 * @param $antwort_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($antwort_id)
	{
		if(!is_numeric($antwort_id) || $antwort_id=='')
		{
			$this->errormsg = 'Antwort_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM testtool.tbl_antwort WHERE antwort_id='".addslashes($antwort_id)."'";
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Löschen der Antwort';
			return false;
		}
	}
	
	function getAntwort($pruefling_id, $frage_id)
	{
		$qry = "SELECT * FROM testtool.tbl_antwort JOIN testtool.tbl_pruefling_frage USING(pruefling_id) 
				JOIN testtool.tbl_vorschlag USING(vorschlag_id) 
				WHERE 
					tbl_vorschlag.frage_id=tbl_pruefling_frage.frage_id AND 
					pruefling_id='".addslashes($pruefling_id)."' AND 
					tbl_vorschlag.frage_id='".addslashes($frage_id)."'";
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new antwort($this->conn, null, null);
				
				$obj->antwort_id = $row->antwort_id;
				$obj->frage_id = $row->frage_id;
				$obj->vorschlag_id = $row->vorschlag_id;
				$obj->begintime = $row->begintime;
				$obj->endtime = $row->endtime;
				$obj->pruefling_id = $row->pruefling_id;
				
				$this->result[] = $obj;
			}
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Antwort konnte nicht geladen werden';
			return false;
		}
	}
}
?>
