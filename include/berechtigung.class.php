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

	/**
	 * Rueckgabewert ist ein Array mit den Ergebnissen. Bei Fehler false und die
	 * Fehlermeldung liegt in errormsg.
	 * Wenn der Parameter stg_kz NULL ist tritt einheit_kurzbzb in Kraft.
	 * @param string $uid    UserID
	 * @return variabel Array mit LVA; <b>false</b> bei Fehler
	 */
	function getBerechtigungen($uid)
	{
		// Berechtigungen holen
		$sql_query="SELECT * FROM tbl_userberechtigung WHERE uid='$uid' AND (start<now() OR start IS NULL) AND (ende>now() OR ende IS NULL)";
	    //echo $sql_query;
		if(!$erg=@pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_errormessage($this->conn);
			return false;
		}
		//$num_rows=pg_numrows($erg);
		while($row=pg_fetch_object($erg))
		{
   			$b=new berechtigung($this->conn);
			$b->userberechtigung_id=$row->userberechtigung_id;
			$b->studiengang_kz=$row->studiengang_kz;
			$b->fachbereich_id=$row->fachbereich_id;
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
			else
				$b->endetimestamp=null;
			$b->art=$row->art;
			$this->berechtigungen[]=$b;
		}
		return true;
	}

	function isBerechtigt($berechtigung,$studiengang_kz=null,$art=null, $fachbereich_id=null)
	{
		$timestamp=time();
		foreach ($this->berechtigungen as $b)
		{
			//Fachbereichsberechtigung
			if($fachbereich_id!=null)
			{
				//Wenn Fachbereichs oder Adminberechtigung
				if(($berechtigung == $b->berechtigung_kurzbz || $b->berechtigung_kurzbz == 'admin') && ($b->fachbereich_id==$fachbereich_id || $b->fachbereich_id=='0'))
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

			//Wenn Berechtigung fuer Bestimmte Klasse vorhanden ist
			if($berechtigung == $b->berechtigung_kurzbz && $studiengang_kz==null && $art==null && $fachbereich_id==null)
			   if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
			//Wenn Berechtigung fuer Bestimmten Studiengang vorhanden ist
			if	($berechtigung==$b->berechtigung_kurzbz
			     && ($studiengang_kz==$b->studiengang_kz || $b->studiengang_kz==0) && $art==null && $b->fachbereich_id==null)
				if ($b->starttimestamp!=null && $b->endetimestamp!=null)
				{
					if ($timestamp>$b->starttimestamp && $timestamp<$b->endetimestamp)
						return true;
				}
				else
					return true;
			//Wenn Berechtigung mit Studiengang und der richtigen BerechtigungsArt (suid) vorhanden ist
			if	($berechtigung==$b->berechtigung_kurzbz
			     && ($studiengang_kz==$b->studiengang_kz || $b->studiengang_kz==0)
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

	/**
	* Gibt Array mit Kennzahlen der Studiengaenge sortiert zurueck.
	* Optional wird auf Berechtigung eingeschraenkt.
	* Wenn Berechtigung ueber alle Studiengaenge steht im ersten Feld 0.
	*/
	function getStgKz($berechtigung=null)
	{
		$studiengang_kz=array();
		$timestamp=time();

		foreach ($this->berechtigungen as $b)
			if	($berechtigung==$b->berechtigung_kurzbz || $berechtigung==null)
				if($b->fachbereich_id==null)
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
				if($b->fachbereich_id!='' && !in_array($b->fachbereich_id,$fachbereichs_kz))
					$fachbereichs_kz[] = $b->fachbereich_id;
			}
		}
		sort($fachbereichs_kz);
		return $fachbereichs_kz;
	}
}
?>