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
 * Klasse ortraumtyp (FAS-Online)
 * @create 04-12-2006
 */

class ortraumtyp
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt

	//Tabellenspalten
	var $ort_kurzbz;		// @var string
	var $hierarchie;		// @var smallint
	var $raumtyp_kurzbz;	// @var string

	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $ort_kurzbz und hierarchie ID des zu ladenden OrtRaumtyps
	 */
	function ortraumtyp($conn, $ort_kurzbz=null, $hierarchie=0)
	{
		$this->conn = $conn;
		if($ort_kurzbz != null && $hierarchie!=null && is_numeric($hierarchie))
			$this->load($ort_kurzbz, $hierarchie);
	}

	/**
	 * Laedt alle verfuegbaren OrtRaumtypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_ortraumtyp order by ort_kurzbz, hierarchie;';

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$ortraumtyp_obj = new ort($this->conn);

			$ortraumtyp_obj->ort_kurzbz 	= $row->ort_kurzbz;
			$ortraumtyp_obj->hierarchie 	= $row->hierarchie;
			$ortraumtyp_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;

			$this->result[] = $ortraumtyp_obj;
		}
		return true;
	}

	/**
	 * Laedt einen OrtRaumtyp
	 * @param $ortraumtyp, hierarchie ID des zu ladenden OrtRaumtyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($ort_kurzbz, $hierarchie)
	{
		if($ort_kurzbz == '' || !is_numeric($hierarchie) || $hierarchie=='')
		{
			$this->errormsg = 'Kein gültiger Schlüssel vorhanden';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_ortraumtyp WHERE ort_kurzbz = '$ort_kurzbz' AND hierarchie = '$hierarchie';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=pg_fetch_object($res))
		{
			$this->ort_kurzbz 		= $row->ort_kurzbz;
			$this->hierarchie 		= $row->hierarchie;
			$this->raumtyp_kurzbz 	= $row->kurzbz;
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
	 * @param $ort_kurzbz, hierarchie ID des Datensatzes der geloescht werden soll
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
		$this->ort_kurzbz = str_replace("'",'´',$this->ort_kurzbz);
		$this->raumtyp_kurzbz = str_replace("'",'´',$this->raumtyp_kurzbz);


		//Laenge Pruefen
		if(strlen($this->ort_kurzbz)>8)
		{
			$this->errormsg = "Ort_kurzbz darf nicht laenger als 8 Zeichen sein bei <b>$this->kurzbz, $hierarchie</b>";
			return false;
		}
		if(strlen($this->raumtyp_kurzbz)>8)
		{
			$this->errormsg = "Raumtyp_kurzbz darf nicht laenger als 8 Zeichen sein bei <b>$this->kurzbz, $hierarchie</b> - $this->raumtyp_kurzbz";
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
			//Pruefen ob id gültig ist
			if($this->ort_kurzbz == '' || !is_numeric($this->hierarchie) || $this->hierarchie=='')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_ortraumtyp (ort_kurzbz, hierarchie, raumtyp_kurzbz) VALUES ('.
				$this->addslashes($this->ort_kurzbz).', '.
				$this->addslashes($this->hierarchie).', '.
				$this->addslashes($this->raumtyp_kurzbz).');';

		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob id gueltig ist
			if($this->ort_kurzbz == '' || !is_numeric($this->hierarchie) || $this->hierarchie=='')
			{
				$this->errormsg = 'Keine gültige ID';
				return false;
			}

			$qry = 'UPDATE public.tbl_ortraumtyp SET '.
				'raumtyp_kurzbz='.$this->addslashes($this->raumtyp_kurzbz).' '.
				'WHERE ort_kurzbz = '.$this->addslashes($this->ort_kurzbz).' AND hierarchie = '.$this->addslashes($this->hierarchie).';';
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