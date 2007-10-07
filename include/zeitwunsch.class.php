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

class zeitwunsch
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $zeitwuensche = array(); // zeitwunsch Objekt

	//Tabellenspalten
	var $stunde;			// smalint
	var $mitarbeiter_uid;	// varchar(16)
	var $tag;				// smalint
	var $gewicht;			// smalint
	var $min_stunde;
	var $max_stunde;

	// *************************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional eine Lehrform
	// * @param $conn        	Datenbank-Connection
	// *        $uid			Uid des Mitarbeiters
	// *        $tag            Tag des Zeitwunsches
	// *        $stunde         Stunde des Zeitwunsches
	// *        $unicode     	Gibt an ob die Daten mit UNICODE Codierung
	// *                     	oder LATIN9 Codierung verarbeitet werden sollen
	// *************************************************************************
	function zeitwunsch($conn, $mitarbeiter_uid=null, $tag=null, $stunde=null, $unicode=false)
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

		if($mitarbeiter_uid != null && $tag!=null && $stunde!=null)
			$this->load($mitarbeiter_uid, $tag, $stunde);
	}

	function init()
	{
		$sql_query='SELECT min(stunde),max(stunde) FROM lehre.tbl_stunde';
		if(!$result_stunde=pg_query($this->conn, $sql_query))
			die(pg_last_error($this->conn));
		$this->min_stunde=pg_result($result_stunde,0,'min');
		$this->max_stunde=pg_result($result_stunde,0,'max');
	}

	// *********************************************************
	// * Laedt einen Zeitwunsch
	// * @param
	// *********************************************************
	function load($mitarbeiter_uid, $tag, $stunde)
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
		if(strlen($this->mitarbeiter_uid)>16)
		{
			$this->errormsg = 'UID darf nicht laenger als 16 Zeichen sein.';
			return false;
		}
		if($this->mitarbeiter_uid == '')
		{
			$this->errormsg = 'UID muss angegeben werden';
			return false;
		}
		if(!is_numeric($this->stunde))
		{
			$this->errormsg = 'Stunde muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->gewicht))
		{
			$this->errormsg = 'Gewicht muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->tag))
		{
			$this->errormsg = 'Tag muss eine gueltige Zahl sein';
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
	// * Speichert einen Zeitwunsch in die Datenbank
	// * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	// * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	// * @return true wenn erfolgreich, false im Fehlerfall
	// ************************************************************
	function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = "INSERT INTO campus.tbl_zeitwunsch (mitarbeiter_uid, tag, stunde, gewicht)
			        VALUES('".addslashes($this->mitarbeiter_uid)."',".
					$this->tag.','.$this->stunde.','.$this->gewicht.');';
		}
		else
		{
			$qry = 'UPDATE campus.tbl_zeitwunsch SET'.
			       ' gewicht='.$this->gewicht.
			       " WHERE mitarbeiter_uid='".addslashes($this->mitarbeiter_uid)."' AND
			         tag=".$this->tag.' AND stunde='.$this->stunde;
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Zeitwunsches:'.$qry;
			return false;
		}
	}

	/**
	 * Zeitwunsch einer Person laden
	 * @return boolean Ergebnis steht in Array $zeitwunsch wenn true
	 */
	function loadPerson($uid,$datum=null)
	{
		// Zeitwuensche abfragen
		if(!$result=pg_query($this->conn, "SELECT * FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid='$uid'"))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			while ($row=pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;
			return true;
		}
	}


	/**
	 * Zeitwunsch der Personen in Lehreinheiten laden
	 * @return array mit Fachbereichen oder false=fehler
	 */
	function loadZwLE($le_id,$datum=null)
	{
		$this->init();
		// SUB-Select fuer LVAs
		$sql_query_leid='';
		$sql_query_le='SELECT DISTINCT mitarbeiter_uid FROM campus.vw_lehreinheit WHERE ';
		for ($i=0;$i<count($le_id);$i++)
			$sql_query_leid.=' OR lehreinheit_id='.$le_id[$i];
		$sql_query_leid=substr($sql_query_leid,3);
		$sql_query_le.=$sql_query_leid;

		// Schlechteste Zeitwuensche holen
		$sql_query='SELECT tag,stunde,min(gewicht) AS gewicht
				FROM campus.tbl_zeitwunsch WHERE mitarbeiter_uid IN ('.$sql_query_le.') GROUP BY tag,stunde';

		// Zeitwuensche abfragen
		if(!$result=pg_query($this->conn, $sql_query))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
			while ($row=pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;

		// Zeitsperren fuer die aktuelle Woche holen
		if ($datum!=null)
		{
			$beginn=montag($datum);
			$ende=jump_day($beginn,7);
			$beginniso=date("Y-m-d",$beginn);
			$endeiso=date("Y-m-d",$ende);
			$sql_query="SELECT vondatum,vonstunde,bisdatum,bisstunde
						FROM campus.tbl_zeitsperre
						WHERE mitarbeiter_uid IN ($sql_query_le)
							AND vondatum<='$endeiso' AND bisdatum>'$beginniso'";
			//echo $sql_query;
			// Zeitsperren abfragen
			if(!$result=pg_query($this->conn, $sql_query))
			{
				$this->errormsg=pg_last_error($this->conn);
				return false;
			}
			while ($row=pg_fetch_object($result))
			{
				echo "\nTagBeginn: ".$row->vondatum;
				echo "\nTagEnde: ".$row->bisdatum;
				echo "\nStundeBeginn: ".$row->vonstunde;
				echo "\nStundeEnde: ".$row->bisstunde;
				if ($row->vonstunde==null || $row->vondatum==null)
					return true;
				$stundebeginn=$row->vonstunde;
				$stundeende=$row->bisstunde;
				$beginnDB=mktime(0,0,0,substr($row->vondatum,5,2),substr($row->vondatum,8,2),substr($row->vondatum,0,4));
				$endeDB=mktime(0,0,0,substr($row->bisdatum,5,2),substr($row->bisdatum,8,2),substr($row->bisdatum,0,4));
				echo "\nTagBeginnDB: ".$beginnDB;
				echo "\nTagEndeDB: ".$endeDB;
				echo "\nTagBeginn: ".$beginn;
				echo "\nTagEnde: ".$ende;
				if ($beginn<$beginnDB)
					$beginn=$beginnDB;
				else
					$stundebeginn=$this->min_stunde;
				if ($ende>$endeDB)
					$ende=$endeDB;
				else
					$stundeende=$this->max_stunde;
				$tagbeginn=date("w",$beginn);
				$tagende=date("w",$ende);
				if ($tagende==0)
				{
					$tagende=6;
					$stundeende=$this->max_stunde;
				}
				echo "\nTagBeginn: ".$tagbeginn;
				echo "\nTagEnde: ".$tagende;
				echo "\nStundeBeginn: ".$stundebeginn;
				echo "\nStundeEnde: ".$stundeende;
				$first=false;
				for ($t=1;$t<=6;$t++)
					for ($h=$this->min_stunde;$h<=$this->max_stunde;$h++)
					{
						if ($first)
						{
							$h=$stundebeginn;
							$first=false;
						}
						if ($t>=$tagbeginn && $t<=$tagende)
							if ($t==$tagbeginn && $h>=$stundebeginn && ($t<$tagende || $h<=$stundeende))
							{
								$this->zeitwunsch[$t][$h]=-3;
								echo 'Zeitsperre eingetragen:'.$t.$h;
							}
							elseif($t==$tagende && $h<=$stundeende && ($t>$tagbeginn || $h>=$stundebeginn))
							{
								$this->zeitwunsch[$t][$h]=-3;
								echo 'Zeitsperre eingetragen:'.$t.$h;
							}
							elseif ($t>$tagbeginn && $t<$tagende)
							{
								$this->zeitwunsch[$t][$h]=-3;
								echo 'Zeitsperre eingetragen:'.$t.$h;
							}
					}
			}
		}
		return true;
	}

}
?>
