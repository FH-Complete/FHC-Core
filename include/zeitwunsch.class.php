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
	var $mitarbeiter_uid;	// varchar(32)
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
		// ggf Mitarbeiter laden
		if($mitarbeiter_uid != null && $tag!=null && $stunde!=null)
			$this->load($mitarbeiter_uid, $tag, $stunde);

		$this->init();
	}

	function init()
	{
		// Stundenraster abfragen
		$sql='SELECT min(stunde) AS min_stunde,max(stunde) AS max_stunde FROM lehre.tbl_stunde;';
		if(!$result=pg_query($this->conn, $sql))
		{
			$this->errormsg=pg_last_error($this->conn);
			return false;
		}
		else
		{
			$row=pg_fetch_object($result);
			$this->min_stunde=$row->min_stunde;
			$this->max_stunde=$row->max_stunde;
		}
		return true;
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
		if(strlen($this->mitarbeiter_uid)>32)
		{
			$this->errormsg = 'UID darf nicht laenger als 32 Zeichen sein.';
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
			while ($row=pg_fetch_object($result))
				$this->zeitwunsch[$row->tag][$row->stunde]=$row->gewicht;

		if (!is_null($datum))
		{
			$beginn=montag($datum);
			$start=date('Y-m-d',$beginn);
			$ende=date('Y-m-d',jump_day($beginn,7));

			// Zeitsperren abfragen
			$sql="SELECT vondatum,vonstunde,bisdatum,bisstunde
				FROM campus.tbl_zeitsperre
				WHERE mitarbeiter_uid='$uid' AND vondatum<='$ende' AND bisdatum>'$start'";
			if(!$result=pg_query($this->conn, $sql))
			{
				$this->errormsg=pg_last_error($this->conn);
				return false;
			}
			else
			{
				while ($row=pg_fetch_object($result))
				{
					$beginn=montag($datum);
					for ($i=1;$i<=7;$i++)
					{
						$date_iso=date('Y-m-d',$beginn);
						//echo "\n".$date_iso."\n".$row->vondatum."\n";
						if ($date_iso>$row->vondatum && $date_iso<$row->bisdatum)
							for ($j=$this->min_stunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
						{
							if (is_null($row->vonstunde))
								$row->vonstunde=$this->min_stunde;
							if (is_null($row->bisstunde))
								$row->bisstunde=$this->max_stunde;
							for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
								$this->zeitwunsch[$i][$j]=-3;
						}
						$beginn=jump_day($beginn,1);
					}
				}
			}
		}
		return true;
	}


	/**
	 * Zeitwunsch der Personen in Lehreinheiten laden
	 * @return true oder false
	 */
	function loadZwLE($le_id,$datum=null)
	{
		//$this->init();
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

		// ***********************************************************
		// Zeitsperren fuer die aktuelle Woche holen

		if (!is_null($datum))
		{
			$beginn=montag($datum);
			$start=date('Y-m-d',$beginn);
			$ende=date('Y-m-d',jump_day($beginn,7));

			// Zeitsperren abfragen
			$sql="SELECT vondatum,vonstunde,bisdatum,bisstunde
				FROM campus.tbl_zeitsperre
				WHERE mitarbeiter_uid IN ($sql_query_le) AND vondatum<='$ende' AND bisdatum>'$start'";
			if(!$result=pg_query($this->conn, $sql))
			{
				$this->errormsg=pg_last_error($this->conn);
				return false;
			}
			while ($row=pg_fetch_object($result))
			{
				$beginn=montag($datum);
				for ($i=1;$i<=7;$i++)
				{
					$date_iso=date('Y-m-d',$beginn);
					//echo "\n".$date_iso."\n".$row->vondatum."\n";
					if ($date_iso>$row->vondatum && $date_iso<$row->bisdatum)
						for ($j=$this->min_stunde;$j<=$this->max_stunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					if ($date_iso==$row->vondatum && $date_iso<$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						for ($j=$row->vonstunde;$j<=$this->max_stunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					if ($date_iso>$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$this->min_stunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					if ($date_iso==$row->vondatum && $date_iso==$row->bisdatum)
					{
						if (is_null($row->vonstunde))
							$row->vonstunde=$this->min_stunde;
						if (is_null($row->bisstunde))
							$row->bisstunde=$this->max_stunde;
						for ($j=$row->vonstunde;$j<=$row->bisstunde;$j++)
							$this->zeitwunsch[$i][$j]=-3;
					}
					$beginn=jump_day($beginn,1);
				}
			}
		}
		return true;
	}

}
?>
