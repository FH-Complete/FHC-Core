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

class prestudent extends person
{
	//Tabellenspalten
	var $prestudent_id;	// varchar(16)
	var $aufmerksamdurch_kurzbz;
	var $studiengang_kz;
	var $berufstaetigkeit_code;
	var $ausbildungcode;
	var $zgv_code;
	var $zgvort;
	var $zgvdatum;
	var $zgvmas_code;
	var $zgvmaort;
	var $zgvmadatum;
	var $aufnahmeschluessel;
	var $facheinschlberuf;
	var $anmeldungreihungstest;
	var $reihungstestangetreten;
	var $reihungstest_id;
	var $punkte;
	var $bismelden;
	
	// ErgebnisArray
	var $result=array();
	var $num_rows=0;
		
	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional einen Prestudent
	// * @param $conn        	Datenbank-Connection
	// *        $prestudent_id            Prestudent der geladen werden soll (default=null)
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung 
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function prestudent($conn, $prestudent_id=null, $unicode=false)
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
		
		if($prestudent_id != null)
			$this->load($prestudent_id);
	}
	
	// ***********************************************************
	// * Laedt Prestudent mit der uebergebenen ID
	// * @param $uid ID der Person die geladen werden soll
	// ***********************************************************
	function load($prestudent_id)
	{
		$qry = "SELECT * FROM public.tbl_prestudent WHERE prestudent_id='$prestudent_id'";
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->prestudent_id = $row->prestudent_id;
				$this->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->berufstaetigkeit_code = $row->berufstaetigkeit_code;
				$this->ausbildungcode = $row->ausbildungcode;
				$this->zgv_code = $row->zgv_code;
				$this->zgvort = $row->zgvort;
				$this->zgvdatum = $row->zgvdatum;
				$this->zgvmas_code = $row->zgvmas_code;
				$this->zgvmaort = $row->zgvmaort;
				$this->zgvmadatum = $row->zgvmadatum;
				$this->aufnahmeschluessel = $row->aufnahmeschluessel;
				$this->facheinschlberuf = ($row->facheinschlberuf=='t'?true:false);
				$this->anmeldungreihungstest = $row->anmeldungreihungstest;
				$this->reihungstestangetreten = ($row->reihungstestangetreten=='t'?true:false);
				$this->reihungstest_id = $row->reihungstest_id;
				$this->punkte = $row->punkte;
				$this->bismelden = ($row->bismelden=='t'?true:false);
				$this->person_id = $row->person_id;
				
				if(!person::load($row->person_id))
					return false;
				else 
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
		//Personen Datensatz speichern
		//if(!person::save())
		//	return false;
			
		//Variablen auf Gueltigkeit pruefen
		if(!prestudent::validate())
			return false;
		
		if($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_prestudent (aufmerksamdurch_kurzbz, person_id, studiengang_kz, berufstaetigkeit_code, ausbildungcode, zgv_code, zgvort, zgvdatum, zgvmas_code, zgvmaort, zgvmadatum, aufnahmeschluessel, facheinschlberuf, reihungstest_id, anmeldungreihungstest, reihungstestangetreten, punkte, bismelden, insertamum, insertvon, updateamum, updatevon, ext_id) VALUES('.
			       $this->addslashes($this->aufmerksamdurch_kurzbz).",".
			       $this->addslashes($this->person_id).",".
			       $this->addslashes($this->studiengang_kz).",".
			       $this->addslashes($this->berufstaetigkeit_code).",".
			       $this->addslashes($this->ausbildungcode).",".
			       $this->addslashes($this->zgv_code).",".
			       $this->addslashes($this->zgvort).",".
			       $this->addslashes($this->zgvdatum).",".
			       $this->addslashes($this->zgvmas_code).",".
			       $this->addslashes($this->zgvmaort).",".
			       $this->addslashes($this->zgvmadatum).",".
			       $this->addslashes($this->aufnahmeschluessel).",".
			       ($this->facheinschlberuf?'true':'false').",".
			       $this->addslashes($this->reihungstest_id).",".
			       $this->addslashes($this->anmeldungreihungstest).",".
			       ($this->reihungstestangetreten?'true':'false').",".
			       $this->addslashes($this->punkte).",".
			       ($this->bismelden?'true':'false').",".
			       $this->addslashes($this->insertamum).",".
			       $this->addslashes($this->insertvon).",".
			       $this->addslashes($this->updateamum).",".
			       $this->addslashes($this->updatevon).",".
			       $this->addslashes($this->ext_id).");";
		}
		else
		{			
			$qry = 'UPDATE public.tbl_prestudent SET'.
			       ' aufmerksamdurch_kurzbz='.$this->addslashes($this->aufmerksamdurch_kurzbz).",".
			       ' person_id='.$this->addslashes($this->person_id).",".
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).",".
			       ' berufstaetigkeit_code='.$this->addslashes($this->berufstaetigkeit_code).",".
			       ' ausbildungcode='.$this->addslashes($this->ausbildungcode).",".
			       ' zgv_code='.$this->addslashes($this->zgv_code).",".
			       ' zgvort='.$this->addslashes($this->zgvort).",".
			       ' zgvdatum='.$this->addslashes($this->zgvdatum).",".
			       ' zgvmas_code='.$this->addslashes($this->zgvmas_code).",".
			       ' zgvmaort='.$this->addslashes($this->zgvmaort).",".
			       ' zgvmadatum='.$this->addslashes($this->zgvmadatum).",".
			       ' aufnahmeschluessel='.$this->addslashes($this->aufnahmeschluessel).",".
			       ' facheinschlberuf='.($this->facheinschlberuf?'true':'false').",".
			       ' reihungstest_id='.$this->addslashes($this->reihungstest_id).",".
			       ' anmeldungreihungstest='.$this->addslashes($this->anmeldungreihungstest).",".
			       ' reihungstestangetreten='.($this->reihungstestangetreten?'true':'false').",".
			       ' punkte='.$this->addslashes($this->punkte).",".
			       ' bismelden='.($this->bismelden?'true':'false').",".
			       ' updateamum='.$this->addslashes($this->updateamum).",".
			       ' updatevon='.$this->addslashes($this->updatevon).",".
			       ' ext_id='.$this->addslashes($this->ext_id).
			       " WHERE prestudent_id='".addslashes($this->prestudent_id)."';";
		}
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else 
		{	
			$this->errormsg = 'Fehler beim Speichern des Prestudent-Datensatzes:'.$qry;
			return false;
		}
	}

	// ******************************************************************
	// * Laden aller Prestudenten, die an $datum zum Reihungstest geladen sind.
	// * Wenn $equal auf true gesetzt ist wird genau dieses Datum verwendet,
	// * ansonsten werden auch alle mit späterem Datum geladen.
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ******************************************************************	
	function getPrestudentRT($datum, $equal=false)
	{
		$sql_query='SELECT DISTINCT * FROM public.vw_prestudent WHERE rt_datum';
		if ($equal)
			$sql_query.='=';
		else
			$sql_query.='>=';
		$sql_query.="'$datum' ORDER BY nachname,vorname";
		
		if(!$result=pg_query($this->conn,$sql_query))
		{	
			$this->errormsg = 'Fehler beim Speichern des Benutzer-Datensatzes:'.$sql_query;
			return false;
		}
		
		$this->num_rows=0;
		
		while($row = pg_fetch_object($result))
		{
			$ps=new prestudent($this->conn);
			$ps->prestudent_id = $row->prestudent_id;
			$ps->person_id = $row->person_id;
			$ps->reihungstest_id = $row->reihungstest_id;
			$ps->staatsbuergerschaft = $row->staatsbuergerschaft;
			$ps->geburtsnation = $row->geburtsnation;
			$ps->sprache = $row->sprache;
			$ps->anrede = $row->anrede;
			$ps->titelpost = $row->titelpost;
			$ps->titelpre = $row->titelpre;
			$ps->nachname = $row->nachname;
			$ps->vorname = $row->vorname;
			$ps->vornamen = $row->vornamen;
			$ps->gebdatum = $row->gebdatum;
			$ps->gebort = $row->gebort;
			$ps->gebzeit = $row->gebzeit;
			$ps->foto = $row->foto;
			$ps->anmerkungen = $row->anmerkungen;
			$ps->homepage = $row->homepage;
			$ps->svnr = $row->svnr;
			$ps->ersatzkennzeichen = $row->ersatzkennzeichen;
			$ps->familienstand = $row->familienstand;
			$ps->geschlecht = $row->geschlecht;
			$ps->anzahlkinder = $row->anzahlkinder;
			$ps->aktiv = $row->aktiv;
			$ps->aufmerksamdurch_kurzbz = $row->aufmerksamdurch_kurzbz;
			$ps->studiengang_kz = $row->studiengang_kz;
			$ps->berufstaetigkeit_code = $row->berufstaetigkeit_code;
			$ps->ausbildungcode = $row->ausbildungcode;
			$ps->zgv_code = $row->zgv_code;
			$ps->zgvort = $row->zgvort;
			$ps->zgvdatum = $row->zgvdatum;
			$ps->zgvmas_code = $row->zgvmas_code;
			$ps->zgvmaort = $row->zgvmaort;
			$ps->zgvmadatum = $row->zgvmadatum;
			$ps->aufnahmeschluessel = $row->aufnahmeschluessel;
			$ps->facheinschlberuf = $row->facheinschlberuf;
			$ps->anmeldungreihungstest = $row->anmeldungreihungstest;
			$ps->reihungstestangetreten = $row->reihungstestangetreten;
			$ps->punkte = $row->punkte;
			$ps->bismelden = $row->bismelden;
			$ps->rt_studiengang_kz = $row->rt_studiengang_kz;
			$ps->rt_ort = $row->rt_ort;
			$ps->rt_datum = $row->rt_datum;
			$ps->rt_uhrzeit = $row->rt_uhrzeit;
			$ps->updateamum = $row->updateamum;
			$ps->updatevon = $row->updatevon;
			$ps->insertamum = $row->insertamum;
			$ps->insertvon = $row->insertvon;
			$ps->ext_id = $row->ext_id;
			$this->result[]=$ps;
			$this->num_rows++; 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	 	
		}	
		
	}
}
?>
