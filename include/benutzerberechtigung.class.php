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

class benutzerberechtigung
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $berechtigungen = array(); // benutzerberechtigung Objekt

	//Tabellenspalten
	var $benutzerberechtigung_id;	// int
	var $art;						// varchar(16)
	var $fachbereich_kurzbz;		// int
	var $studiengang_kz;			// int
	var $berechtigung_kurzbz;		// varchar(16)
	var $uid;						// varchar(16)
	var $studiensemester_kurzbz;	// varchar(16)
	var $start;						// date
	var $ende;						// date
	var $starttimestamp;
	var $endetimestamp;

	//Attribute des Mitarbeiters
	var $fix;
	var $lektor;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $benutzerberechtigung_id
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function benutzerberechtigung($conn, $benutzerberechtigung_id=null, $unicode=false)
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

		if($benutzerberechtigung_id!=null)
			$this->load($benutzerberechtigung_id);
	}

	// *********************************************************
	// * Laedt eine Benutzerberechtigung
	// * @param benutzerberechtigung_id
	// *********************************************************
	function load($benutzerberechtigung_id)
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
		if(strlen($this->art)>16)
		{
			$this->errormsg = 'Art darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if(strlen($this->fachbereich_kurzbz)>16)
		{
			$this->errormsg = 'fachbereich_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->studiengang_kz!='' && !is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengangskennzahl muss eine gueltige Zahl sein';
			return false;
		}
		if(strlen($this->berechtigung_kurzbz)>16)
		{
			$this->errormsg = 'Berechtigung_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->berechtigung_kurzbz=='')
		{
			$this->errormsg = 'Berechtigung_kurzbz muss angegeben werden';
			return false;
		}
		if(strlen($this->uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->uid=='')
		{
			$this->errormsg = 'UID muss angegeben werden';
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
	// * Speichert Benutzerberechtigung in die Datenbank
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
			$qry = 'INSERT INTO public.tbl_benutzerberechtigung (art, fachbereich_kurzbz, studiengang_kz, berechtigung_kurzbz,
			                                              uid, studiensemester_kurzbz, start, ende)
			        VALUES('.$this->addslashes($this->art).','.
					$this->addslashes($this->fachbereich_kurzbz).','.
					$this->addslashes($this->studiengang_kz).','.
					$this->addslashes($this->berechtigung_kurzbz).','.
					$this->addslashes($this->uid).','.
					$this->addslashes($this->studiensemester_kurzbz).','.
					$this->addslashes($this->start).','.
					$this->addslashes($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_benutzerberechtigung SET'.
			       ' art='.$this->addslashes($this->art).','.
			       ' fachbereich_kurzbz='.$this->addslashes($this->fachbereich_kurzbz).','.
			       ' studiengang_kz='.$this->addslashes($this->studiengang_kz).','.
			       ' berechtigung_kurzbz='.$this->addslashes($this->berechtigung_kurzbz).','.
			       ' uid='.$this->addslashes($this->uid).','.
			       ' studiensemester_kurzbz='.$this->addslashes($this->studiensemester_kurzbz).','.
			       ' start='.$this->addslashes($this->start).','.
			       ' ende='.$this->addslashes($this->ende).
			       " WHERE benutzerberechtigung_id='".addslashes($this->benutzerberechtigung_id)."'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Benutzerberechtigung:'.$qry;
			return false;
		}
	}

	// ************************************************************
	// * Speichert Benutzerberechtigung in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function delete($benutzerberechtigung_id)
	{
		// Berechtigungen holen
		$sql_query="DELETE from tbl_benutzerberechtigung where benutzerberechtigung_id = '".$benutzerberechtigung_id."'";

		if(!pg_query($this->conn, $sql_query))
		{
			$this->errormsg='Fehler beim l&ouml;schen';
			return false;
		}
		return true;
	}

	//****************************************************************************
	// * Rueckgabewert ist ein Array mit den Ergebnissen. Bei Fehler false und die
	// * Fehlermeldung liegt in errormsg.
	// * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	// * @param string $uid    UserID
	// * @return variable Array mit LVA, false bei Fehler
	// ***************************************************************************
	function getBerechtigungen($uid,$all=false)
	{
		// Berechtigungen holen
		$sql_query="SELECT * FROM public.tbl_benutzerberechtigung WHERE uid='$uid'";
		if (!$all)
			$sql_query .= " AND (start<now() OR start IS NULL) AND (ende>now() OR ende IS NULL)";
		$sql_query .= " order by benutzerberechtigung_id";

		if(!$erg=pg_query($this->conn, $sql_query))
		{
			$this->errormsg='Fehler beim laden der Berechtigungen';
			return false;
		}

		while($row=pg_fetch_object($erg))
		{
   			$b=new benutzerberechtigung($this->conn);

   			$b->benutzerberechtigung_id = $row->benutzerberechtigung_id;
			$b->art=$row->art;
			$b->fachbereich_kurzbz=$row->fachbereich_kurzbz;
			$b->studiengang_kz=$row->studiengang_kz;
			$b->berechtigung_kurzbz=$row->berechtigung_kurzbz;
			$b->uid=$row->uid;
			$b->studiensemester_kurzbz=$row->studiensemester_kurzbz;
			$b->start=$row->start;
			if ($row->start!=null)
				$b->starttimestamp=mktime(0,0,0,substr($row->start,5,2),substr($row->start,8),substr($row->start,0,4));
			else
				$b->starttimestamp=null;
			$b->ende=$row->ende;
			if ($row->ende!=null)
				$b->endetimestamp=mktime(23,59,59,substr($row->ende,5,2),substr($row->ende,8),substr($row->ende,0,4));


			$this->berechtigungen[]=$b;
		}

		// Attribute des Mitarbeiters holen
		$sql_query="SELECT * FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid='$uid'";
		if(!$erg=pg_query($this->conn, $sql_query))
		{
			$this->errormsg='Fehler beim laden der Berechtigungen';
			return false;
		}
		while($row=pg_fetch_object($erg))
		{
   			if ($row->fixangestellt=='t')
   				$this->fix=true;
   			else
   				$this->fix=false;

			if ($row->lektor=='t')
   				$this->lektor=true;
   			else
   				$this->lektor=false;
		}
		return true;
	}

	//****************************************************************************
	// * Rueckgabewert ist TRUE wenn eine Berechtigung entspricht.
	// * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	// * @param string $uid    UserID
	// * @return variable Array mit LVA, false bei Fehler
	// ***************************************************************************
	function isBerechtigt($berechtigung,$studiengang_kz=null,$art=null, $fachbereich_kurzbz=null)
	{
		$timestamp=time();
		foreach ($this->berechtigungen as $b)
		{
			//echo 'Admin<BR>';
			//Admin auf alles ist immer TRUE
			if ($b->berechtigung_kurzbz=='admin' && is_null($b->fachbereich_kurzbz) && is_null($b->studiengang_kz) )
			//if ($b->berechtigung_kurzbz=='admin' && $b->fachbereich_kurzbz===NULL && $b->studiengang_kz===NULL )
				if (!is_null($b->starttimestamp) && !is_null($b->endetimestamp))
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;

			//echo 'Fachbereich<BR>';
			//Fachbereichsberechtigung
			if($fachbereich_kurzbz!=null)
			{
				//Wenn Fachbereichs oder Adminberechtigung
				if(($berechtigung == $b->berechtigung_kurzbz || $b->berechtigung_kurzbz == 'admin') && ($b->fachbereich_kurzbz==$fachbereich_kurzbz || is_null($b->fachbereich_kurzbz)))
				{
					if ($b->starttimestamp!=null && $b->endetimestamp!=null)
					{
						if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
							return true;
					}
					else
						return true;
				}
			}

			//echo 'Klasse<BR>';
			//Wenn Berechtigung fuer Bestimmte Klasse vorhanden ist
			if	(	($berechtigung==$b->berechtigung_kurzbz && is_null($studiengang_kz) && is_null($art) && is_null($fachbereich_kurzbz))
					||
					($b->berechtigung_kurzbz=='admin'
						&& (is_null($studiengang_kz) && (is_null($b->studiengang_kz) || $b->studiengang_kz==$studiengang_kz) )
						&& (is_null($fachbereich_kurzbz) && (is_null($b->fachbereich_kurzbz) || $b->fachbereich_kurzbz==$fachbereich_kurzbz) )
				 	)
				)
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;

			//echo 'Studiengang<BR>';
			//Wenn Berechtigung fuer Bestimmten Studiengang vorhanden ist
			if	($berechtigung==$b->berechtigung_kurzbz && ($studiengang_kz==$b->studiengang_kz || is_null($b->studiengang_kz)) && $art==null && $b->fachbereich_kurzbz==null)
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;

			//Wenn Berechtigung mit Studiengang und der richtigen BerechtigungsArt (suid) vorhanden ist
			if	($berechtigung==$b->berechtigung_kurzbz
			     && ($studiengang_kz==$b->studiengang_kz || is_null($b->studiengang_kz))
			     && strstr($b->art,$art))
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
		}
		return false;
	}

	function isFix()
	{
		if ($this->fix)
			return true;
		else
			return false;
	}

	// ********************************************************************
	// * Gibt Array mit Kennzahlen der Studiengaenge sortiert zurueck.
	// * Optional wird auf Berechtigung eingeschraenkt.
	// * Wenn Berechtigung ueber alle Studiengaenge steht im ersten Feld 0.
	// ********************************************************************
	function getStgKz($berechtigung=null)
	{
		$studiengang_kz=array();
		$timestamp=time();

		foreach ($this->berechtigungen as $b)
			if	($berechtigung==$b->berechtigung_kurzbz || $berechtigung==null)
				if($b->fachbereich_kurzbz==null)
					$studiengang_kz[]=$b->studiengang_kz;
		$studiengang_kz=array_unique($studiengang_kz);
		sort($studiengang_kz);
		return $studiengang_kz;
	}

	function getFbKz($berechtigung=null)
	{
		$fachbereichs_kz=array();
		$timestamp=time();

		foreach($this->berechtigungen as $b)
		{
			if(($berechtigung==$b->berechtigung_kurzbz || $berechtigung==null)
			   && (($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp) || ($b->starttimestamp==null && $b->endetimestamp==null)))
			{
				if($b->fachbereich_kurzbz!='' && !in_array($b->fachbereich_kurzbz,$fachbereichs_kz))
					$fachbereichs_kz[] = $b->fachbereich_kurzbz;
				if($b->fachbereich_kurzbz=='' && ($b->studiengang_kz==0 || $b->studiengang_kz=''))
					$fachbereichs_kz[] = '0';
			}
		}
		sort($fachbereichs_kz);
		return $fachbereichs_kz;
	}
}
?>