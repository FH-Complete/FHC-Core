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

class dokument
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $result = array();
	
	//Tabellenspalten
	var $dokument_kurzbz;
	var $bezeichnung;
	var $studiengang_kz;
	
	var $prestudent_id;
	var $mitarbeiter_uid;
	var $datum;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	var $ext_id;
	
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein dokument
	// * @param $conn        	Datenbank-Connection
	// * 		$dokument_kurzbz
	// * 		$prestudent_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function dokument($conn, $dokument_kurzbz=null, $prestudent_id=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if(!is_null($unicode))
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
				
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = 'Encoding konnte nicht gesetzt werden';
				return false;
			}
		}
		
		if(!is_null($dokument_kurzbz) && !is_null($prestudent_id))
			$this->load($dokument_kurzbz, $prestudent_id);
	}
	
	// *********************************************************
	// * Laedt eine Dokument-Prestudent Zuordnung
	// * @param dokument_kurzbz
	// *        prestudent_id
	// *********************************************************
	function load($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokumentprestudent WHERE prestudent_id='$prestudent_id' AND dokument_kurzbz='".addslashes($dokument_kurzbz)."';";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{								
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->prestudent_id = $row->prestudent_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->datum = $row->datum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;	
			}
			else 
			{
				$this->errormsg = 'Es wurde kein Datensatz gefunden';
				return false;
			}			
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
		
	// *******************************************
	// * Prueft die Variablen vor dem Speichern 
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'Dokument_kurzbz muss angegeben werden';
			return false;
		}
		
		if($this->prestudent_id=='')
		{
			$this->errormsg = 'Prestudent_id muss angegeben werden';
			return false;
		}
		
		if(!is_numeric($this->prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		if($this->mitarbeiter_uid=='')
		{
			$this->errormsg = 'Mitarbeiter_uid muss angegeben werden';
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
	// * Speichert ein Beispiel in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{			
			$qry = 'INSERT INTO public.tbl_dokumentprestudent(dokument_kurzbz, prestudent_id, mitarbeiter_uid, datum, updateamum, 
			        updatevon, insertamum, insertvon, ext_id) VALUES('.
			        $this->addslashes($this->dokument_kurzbz).','.
			        $this->addslashes($this->prestudent_id).','.
			        $this->addslashes($this->mitarbeiter_uid).','.
			        $this->addslashes($this->datum).','.
			        $this->addslashes($this->updateamum).','.
			        $this->addslashes($this->updatevon).','.
			        $this->addslashes($this->insertamum).','.
			        $this->addslashes($this->insertvon).','.
			        $this->addslashes($this->ext_id).');';
		}
		else
		{
			//never used
			return false;
		}
		//echo $qry;
		if(pg_query($this->conn,$qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuteilung';
			return false;
		}
	}

	// *******************************************
	// * Loescht eine Zuordnung
	// * @param dokument_kurzbz
	// *        prestudent_id
	// *******************************************
	function delete($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_dokumentprestudent WHERE dokument_kurzbz='".addslashes($dokument_kurzbz)."' AND prestudent_id='".addslashes($prestudent_id)."'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim loeschen der Zuteilung';
			return false;
		}
	}
	
	// *********************************************
	// * Laedt alle Dokumente eines Prestudenten die
	// * er bereits abgegeben hat
	// * @param prestudent_id
	// * @return true wenn ok, false wenn Fehler
	// *********************************************
	function getPrestudentDokumente($prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokumentprestudent JOIN public.tbl_dokument USING(dokument_kurzbz) WHERE prestudent_id='$prestudent_id' ORDER BY dokument_kurzbz";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$dok = new dokument($this->conn, null, null, null);
				
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->prestudent_id = $row->prestudent_id;
				$dok->mitarbeiter_uid = $row->mitarbeiter_uid;
				$dok->datum = $row->datum;
				$dok->updateamum = $row->updateamum;
				$dok->updatevon = $row->updatevon;
				$dok->insertamum = $row->insertamum;
				$dok->insertvon = $row->insertvon;
				$dok->ext_id = $row->ext_id;
				
				$this->result[] = $dok;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	
	// **********************************************
	// * Laedt alle Dokumente fuer einen Stg die der
	// * Prestudent noch nicht abgegeben hat
	// * @param studiengang_kz
	// *        prestudent_id
	// * @return true wenn ok, false wenn Fehler
	// **********************************************
	function getFehlendeDokumente($studiengang_kz, $prestudent_id=null)
	{
		if(!is_null($prestudent_id) && !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokument JOIN public.tbl_dokumentstudiengang USING(dokument_kurzbz) 
				WHERE studiengang_kz='$studiengang_kz'";

		if(!is_null($prestudent_id))
		{
			$qry.="	AND dokument_kurzbz NOT IN (
					SELECT dokument_kurzbz FROM public.tbl_dokumentprestudent WHERE prestudent_id='$prestudent_id')";
		}
		
		$qry.=" ORDER BY dokument_kurzbz";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$dok = new dokument($this->conn, null, null, null);
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$this->result[] = $dok;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
}
?>