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

class studiensemester
{
	var $conn;     // resource DB-Handle
	var $errormsg; // string
	var $new;      // boolean
	var $studiensemester = array(); // studiensemester Objekt

	//Tabellenspalten
	var $studiensemester_kurzbz; // varchar(16)
	var $start; // date
	var $ende;  // date
	var $bezeichnung;

	// ***********************************************************************
	// * Konstruktor - Uebergibt die Connection und laedt optional ein LF
	// * @param $conn        Datenbank-Connection
	// *        $studiensemester_kurzbz StSem das geladen werden soll (default=null)
	// *        $unicode     Gibt an ob die Daten mit UNICODE Codierung
	// *                     oder LATIN9 Codierung verarbeitet werden sollen
	// ***********************************************************************
	function studiensemester($conn, $studiensemester_kurzbz=null, $unicode=false)
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

		if($studiensemester_kurzbz != null)
			$this->load($studiensemester_kurzbz);
	}

	// **************************************************************
	// * Laedt das Studiensemester mit der uebergebenen ID
	// * @param $studiensemester_kurzbz Stsem das geladen werden soll
	// **************************************************************
	function load($studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM public.tbl_studiensemester WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."'";

		if(!$result=pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim lesen des Studiensemesters';
			return false;
		}

		if($row = pg_fetch_object($result))
		{
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
			$this->bezeichnung = $row->bezeichnung;
		}
		else
		{
			$this->errormsg = "Es ist kein Studiensemester mit der Kurzbezeichung $studiensemester_kurzbz vorhanden";
			return false;
		}

		return true;
	}

	// *******************************************
	// * Prueft die Variablen vor dem Speichern
	// * auf Gueltigkeit.
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		if(strlen($this->studiensemester_kurzbz)>16)
		{
			$this->errormsg = 'Studiensemester Kurzbezeichnung darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if($this->studiensemester_kurzbz=='')
		{
			$this->errormsg = 'Es muss eine Kurzbezeichnung eingegeben werden';
			return false;
		}
		return true;
	}

	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden Datenbankkritische
	// * zeichen mit backslash versehen und das ergbnis
	// * unter hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}

	// ************************************************************
	// * Speichert das Studiensemester in die Datenbank
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
			$qry = "INSERT INTO public.tbl_studiensemester (studiensemester_kurzbz, start, ende)
			        VALUES('".addslashes($this->studiensemester_kurzbz)."',".
					$this->addslashes($this->start).','.
					$this->addslashes($this->ende).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_studiensemester SET'.
			       ' start='.$this->addslashes($this->start).','.
			       ' ende='.$this->addslashes($this->ende).
			       " WHERE studiensemester_kurzbz='$this->studiensemester_kurzbz'";
		}

		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Studiensemesters:'.$qry;
			return false;
		}
	}

	// ******************************************************************
	// * Liefert das Aktuelle Studiensemester
	// * @return aktuelles Studiensemester oder false wenn es keines gibt
	// ******************************************************************
	function getakt()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE start <= now() AND ende >= now()";

		if(!$res=pg_query($this->conn,$qry))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}

		if(pg_num_rows($res)>0)
		{
		   $erg = pg_fetch_object($res);
		   return $erg->studiensemester_kurzbz;
		}
		else
		{
			$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
			return false;
		}
	}

	// ******************************************************************
	// * Liefert ein Studiensemester mit Startdatum vom naechstgelegenen Studiensemester und
	// * dem Startdatum vom folgeden Studiensemester als Endedatum
	// * @return boolean
	// ******************************************************************
	function getNearestTillNext()
	{
		/*$qry = "SELECT * FROM public.vw_studiensemester ORDER BY delta LIMIT 1";
		if(!$res=pg_query($this->conn,$qry))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}

		if(!$erg1 = pg_fetch_object($res))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}

		$qry = "SELECT * FROM public.vw_studiensemester WHERE ORDER BY delta LIMIT 1";
		if(!$res=pg_query($this->conn,$qry))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}

		if(!$erg2 = pg_fetch_object($res))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}*/

		if(!$nearest=$this->getNearest())
			return false;
		$start=$this->start;
		$studiensemester_kurzbz=$this->studiensemester_kurzbz;
		if (!$next=$this->getNextFrom($this->studiensemester_kurzbz))
			return false;
		$ende=$this->start;

		$this->studiensemester_kurzbz=$studiensemester_kurzbz;
		$this->start=$start;
		$this->ende=$ende;

		return true;
	}

	/**
	 * Liefert das Aktuelle Studiensemester oder das darauffolgende
	 * @param $semester wenn das semester uebergeben wird, dann werden nur die studiensemester
	 *                  geliefert die in dieses semester fallen (Bei geradem semester nur SS sonst WS)
	 * @return Studiensemester oder false wenn es keines gibt
	 */
	function getaktorNext($semester='')
	{
		if(($stsem=$this->getakt()) && $semester=='')
		   return $stsem;
		else
		{
			$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE true";
			//$qry = "SELECT studiensemester_kurzbz FROM public.vw_studiensemester ";
			if($semester!='')
			{
				if($semester%2==0)
					$ss='SS';
				else
					$ss='WS';

				$qry.= " AND substring(studiensemester_kurzbz from 1 for 2)='$ss' ";
			}
			$qry.= " AND ende >= now() ORDER BY ende LIMIT 1";

			if(!$res=pg_query($this->conn,$qry))
		    {
				$this->errormsg = pg_errormessage($this->conn);
				return false;
		    }

			if(pg_num_rows($res)>0)
			{
			   $erg = pg_fetch_object($res);
			   return $erg->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
				return false;
			}
		}
	}

	/**
	 * Liefert das naechstgelegenste Studiensemester
	 * @return Studiensemester oder false wenn es keines gibt
	 */
	function getNearest($semester='')
	{
		$qry = "SELECT studiensemester_kurzbz, start, ende FROM public.vw_studiensemester ";
		if($semester!='')
		{
			if($semester%2==0)
				$ss='SS';
			else
				$ss='WS';

			$qry.= " WHERE substring(studiensemester_kurzbz from 1 for 2)='$ss' ";
		}
		$qry.=' ORDER BY delta LIMIT 1';
		//echo $qry;

		if(!$res=pg_query($this->conn,$qry))
		{
			$this->errormsg = pg_errormessage($this->conn);
			return false;
		}

		if(pg_num_rows($res)>0)
		{
			$erg = pg_fetch_object($res);
			$this->studiensemester_kurzbz=$erg->studiensemester_kurzbz;
			$this->start=$erg->start;
			$this->ende=$erg->ende;
			return $erg->studiensemester_kurzbz;
		}
		else
		{
			$this->errormsg = "Kein aktuelles Studiensemester vorhanden";
			return false;
		}
	}


	function getAll()
	{
		$qry = "SELECT * FROM public.tbl_studiensemester ORDER BY ende";

		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$stsem_obj = new studiensemester($this->conn);

				$stsem_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$stsem_obj->start = $row->start;
				$stsem_obj->ende = $row->ende;

				$this->studiensemester[] = $stsem_obj;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studiensemester';
			return false;
		}
	}

	// ****
	// * Liefert das naechste Studiensemester
	// * Wenn art=WS dann wird das naechste Wintersemester geliefert
	// * Wenn art=SS dann wird das naechste Sommersemester geliefert
	// ****
	function getNextStudiensemester($art='')
	{
		$qry = "SELECT * FROM public.tbl_studiensemester WHERE start>now() ";

		if($art!='')
			$qry.= " AND substring(studiensemester_kurzbz from 1 for 2)='".addslashes($art)."' ";

		$qry.=" ORDER BY start LIMIT 1";

		if(!$result=pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Studiensemesters';
			return false;
		}

		if($row = pg_fetch_object($result))
		{
			$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			$this->start = $row->start;
			$this->ende = $row->ende;
		}
		else
		{
			$this->errormsg = "Es wurde kein entsprechendes Studiensemester gefunden";
			return false;
		}

		return true;
	}

	// ****
	// * Liefert das vorige Studiensemester
	// ****
	function getPrevious()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 1";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorangegangenes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorangegangenen Studiensemesters';
		}
	}
	// ****
	// * Liefert das vorvorige Studiensemester
	// ****
	function getBeforePrevious()
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende<now() ORDER BY ende DESC LIMIT 2";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result,1))
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorjähriges Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorjährigen Studiensemesters';
		}
	}

	// ****
	// * Liefert das Studiensemester vor $studiensemester_kurzbz
	// ****
	function getPreviousFrom($studiensemester_kurzbz)
	{
		$qry = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester
				WHERE ende<(SELECT start FROM public.tbl_studiensemester
				            WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."')
		        ORDER BY ende DESC LIMIT 1";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein vorangegangenes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des vorangegangenen Studiensemesters';
		}
	}

	// ****
	// * Liefert das Studiensemester nach $studiensemester_kurzbz
	// ****
	function getNextFrom($studiensemester_kurzbz)
	{
		$qry = "SELECT studiensemester_kurzbz, start, ende FROM public.tbl_studiensemester
				WHERE start>(SELECT ende FROM public.tbl_studiensemester
				            WHERE studiensemester_kurzbz='".addslashes($studiensemester_kurzbz)."')
		        ORDER BY start LIMIT 1";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->start = $row->start;
				$this->ende = $row->ende;
				return $row->studiensemester_kurzbz;
			}
			else
			{
				$this->errormsg = 'Es wurde kein folgendes Studiensemester gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln des folgenden Studiensemesters';
		}
	}

	// ****
	// * Springt von Studiensemester $studiensemester_kurzbz um $wert Studiensemester vor/zurueck
	// ****
	function jump($studiensemester_kurzbz, $wert)
	{
		if($wert>0)
		{
			$op='>';
			$sort='ASC';
		}
		elseif($wert<0)
		{
			$op='<';
			$sort='DESC';
		}
		else
		{
			$op='=';
			$sort='';
		}

		$qry = "SELECT studiensemester_kurzbz
				FROM
					(
					SELECT studiensemester_kurzbz, start
					FROM public.tbl_studiensemester
					WHERE start$op(SELECT start FROM public.tbl_studiensemester
					               WHERE studiensemester_kurzbz='$studiensemester_kurzbz')
					ORDER BY start $sort
					LIMIT ".abs($wert)."
					) as foo
				ORDER BY start DESC LIMIT 1";

		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				return $row->studiensemester_kurzbz;
			}
			else
				return $studiensemester_kurzbz;
		}
	}
}
?>