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
/**
 * Klasse ort (FAS-Online)
 * @create 04-12-2006
 */

class ort
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 			// @var string
	var $result = array(); 	// @var fachbereich Objekt

	//Tabellenspalten
	var $ort_kurzbz;		// @var string
	var $bezeichnung;		// @var string
	var $planbezeichnung;	// @var string
	var $max_person;		// @var integer
	var $lehre;				// @var boolean
	var $reservieren;		// @var boolean
	var $aktiv;				// @var boolean
	var $lageplan;			// @var oid
	var $dislozierung;		// @var smallint
	var $kosten;			// @var numeric(8,2)
	var $ausstattung;
	var $stockwerk;			// @var integer
	var $standort_kurzbz; 	// @var varchar(16)
	var $telefonklappe;		// @var varchar(8)

	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $ort_kurzbz Kurzbz des zu ladenden Ortes
	 */
	function ort($conn, $ort_kurzbz=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE'";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9'";
			
			if(!pg_query($this->conn, $qry))
			{
				$this->errormsg ='Fehler beim Setzen des Encodings';
				return false;
			}
		}
		
		if($ort_kurzbz != null)
			$this->load($ort_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Orte
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_ort order by ort_kurzbz;';

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$ort_obj = new ort($this->conn);

			$ort_obj->ort_kurzbz 		= $row->ort_kurzbz;
			$ort_obj->bezeichnung 		= $row->bezeichnung;
			$ort_obj->planbezeichnung 	= $row->planbezeichnung;
			$ort_obj->max_person 		= $row->max_person;
			$ort_obj->aktiv 			= ($row->aktiv=='t'?true:false);
			$ort_obj->lehre 			= ($row->lehre=='t'?true:false);
			$ort_obj->lageplan 			= $row->lageplan;
			$ort_obj->dislozierung 		= $row->dislozierung;
			$ort_obj->kosten 			= $row->kosten;
			$ort_obj->reservieren		= ($row->reservieren=='t'?true:false);
			$ort_obj->ausstattung		= $row->ausstattung;
			$ort_obj->stockwerk			= $row->stockwerk;
			$ort_obj->standort_kurzbz	= $row->standort_kurzbz;
			$ort_obj->telefonklappe		= $row->telefonklappe;

			$this->result[] = $ort_obj;
		}
		return true;
	}

	/**
	 * Laedt einen Ort
	 * @param $fachb_id ID des zu ladenden Ortes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($ort_kurzbz)
	{
		if($ort_kurzbz == '')
		{
			$this->errormsg = 'kurzbz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_ort WHERE ort_kurzbz = '$ort_kurzbz';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=pg_fetch_object($res))
		{
			$this->ort_kurzbz 		= $row->ort_kurzbz;
			$this->bezeichnung 		= $row->bezeichnung;
			$this->planbezeichnung 	= $row->planbezeichnung;
			$this->max_person 		= $row->max_person;
			$this->aktiv 			= ($row->aktiv=='t'?true:false);
			$this->lehre 			= ($row->lehre=='t'?true:false);
			$this->lageplan 		= $row->lageplan;
			$this->dislozierung 		= $row->dislozierung;
			$this->kosten 		= $row->kosten;
			$this->reservieren		= ($row->reservieren=='t'?true:false);
			$this->ausstattung		= $row->ausstattung;
			$this->stockwerk		= $row->stockwerk;
			$this->standort_kurzbz	= $row->standort_kurzbz;
			$this->telefonklappe	= $row->telefonklappe;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Loescht einen Datensatz
	 * @param $ort_kurzbz ID des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($ort_kurzbz)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{
		$this->bezeichnung = str_replace("'",'�',$this->bezeichnung);
		$this->ort_kurzbz = str_replace("'",'�',$this->ort_kurzbz);
		$this->planbezeichnung = str_replace("'",'�',$this->planbezeichnung);


		//Laenge Pruefen
		if(strlen($this->bezeichnung)>30)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 30 Zeichen sein bei <b>$this->ort_kurzbz</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->planbezeichnung)>30)
		{
			$this->errormsg = "Planbezeichnung darf nicht laenger als 30 Zeichen sein bei <b>$this->ort_kurzbz</b> - $this->planbezeichnung";
			return false;
		}
		if(strlen($this->ort_kurzbz)>8)
		{
			$this->errormsg = "Ort_kurzbz darf nicht laenger als 8 Zeichen sein bei <b>$this->ort_kurzbz/b>";
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;

		if($this->new)
		{
			//Pruefen ob ort_kurzbz eine gueltige Bezeichnung ist
			if($this->ort_kurzbz == '')
			{
				$this->errormsg = 'ort_kurzbz darf nicht leer sein';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_ort (ort_kurzbz, bezeichnung, planbezeichnung, max_person, aktiv, lehre, reservieren, lageplan,
				dislozierung, kosten, stockwerk, standort_kurzbz, telefonklappe) VALUES ('.
				$this->addslashes($this->ort_kurzbz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->planbezeichnung).', '.
				$this->addslashes($this->max_person).', '.
				($this->aktiv?'true':'false').', '.
				($this->lehre?'true':'false').', '.
				($this->reservieren?'true':'false').', '.
				$this->addslashes($this->lageplan).', '.
				$this->addslashes($this->dislozierung).', '.
				$this->addslashes(str_replace(",",".",$this->kosten)).', '.
				$this->addslashes($this->stockwerk).','.
				$this->addslashes($this->standort_kurzbz).','.
				$this->addslashes($this->telefonklappe).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob ort_kurzbz gueltig ist
			if($this->ort_kurzbz == '')
			{
				$this->errormsg = 'ort_kurzbz darf nicht leer sein';
				return false;
			}

			$qry = 'UPDATE public.tbl_ort SET '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'planbezeichnung='.$this->addslashes($this->planbezeichnung).', '.
				'max_person='.$this->addslashes($this->max_person).', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'lehre='.($this->lehre?'true':'false') .', '.
				'reservieren='.($this->reservieren?'true':'false') .', '.
				'lageplan='.$this->addslashes($this->lageplan).', '.
				'dislozierung='.$this->addslashes($this->dislozierung).', '.
				'kosten='.$this->addslashes(str_replace(",",".",$this->kosten)).', '.
				'standort_kurzbz='.$this->addslashes($this->standort_kurzbz).', '.
				'telefonklappe='.$this->addslashes($this->telefonklappe).', '.
				'stockwerk='.$this->addslashes($this->stockwerk).' '.
				'WHERE ort_kurzbz = '.$this->addslashes($this->ort_kurzbz).';';
		}

		if(pg_query($this->conn, $qry))
		{
			/*//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}

			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else
			{
				$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
				return false;
			}*/
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}
}
?>