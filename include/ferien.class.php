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
 * Klasse ferien (FAS-Online)
 * @create 07-12-2006
 */

class ferien
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var ferien Objekt

	//Tabellenspalten
	var $bezeichnung;		// @var varchar(64)
	var $studiengang_kz;	// @var integer
	var $vondatum;		// @var date
	var $bisdatum;		// @var date

	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $bezeichnung und studiengang_kz ID der zu ladenden Ferien
	 */
	function ferien($conn, $bezeichnung=null, $studiengang_kz=null)
	{
		$this->conn = $conn;
		if($bezeichnung != null && $studiengang_kz != null && !is_numeric($studiengang_kz))
			$this->load($bezeichnung, studiengang_kz);
	}

	/**
	 * Laedt alle verfuegbaren Feriendaten
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = 'SELECT * FROM lehre.tbl_ferien order by bezeichnung, studiengang_kz;';

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$ferien_obj = new ferien($this->conn);
			$ferien_obj->bezeichnung 		= $row->bezeichnung;
			$ferien_obj->studiengang_kz	= $row->studiengang_kz;
			$ferien_obj->vondatum 		= $row->vondatum;
			$ferien_obj->bisdatum 		= $row->bisdatum;

			$this->result[] = $ferien_obj;
		}
		return true;
	}

	/**
	 * Laedt einen Feriendatensatz
	 * @param $bezeichnung, studiengang_kz ID der zu ladenden Ferien
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($bezeichnung, $studiengang_kz)
	{
		if($studiengang_kz == '' || !is_numeric($studiengang_kz) || $bezeichnung=='')
		{
			$this->errormsg = 'ID ungültig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_ferien WHERE bezeichnung = '$this->bezeichnung' AND studiengang_kz = '$this->studiengang_kz';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=pg_fetch_object($res))
		{
			$this->bezeichnung		= $row->bezeichnung;
			$this->studiengang_kz	= $row->studiengang_kz;
			$this->vondatum		= $row->vondatum;
			$this->bisdatum		= $row->bisdatum;
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
	 * @param $bezeichnung, studiengang_kz id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($bezeichnung, $studiengang_kz)
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
		$this->bezeichnung = str_replace("'",'´',$this->bezeichnung);

		//Laenge Pruefen
		if(strlen($this->bezeichnung)>64)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 64 Zeichen sein bei <b>$this->studiengang_kz</b> - $this->bezeichnung";
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
			//Neuen Datensatz anlegen

			if($this->studiengang_kz == '' || !is_numeric($this->studiengang_kz) || $this->bezeichnung=='')
			{
				$this->errormsg = 'ID ungültig';
				return false;
			}
			$qry = 'INSERT INTO lehre.tbl_ferien (bezeichnung, studiengang_kz, vondatum, bisdatum) VALUES ('.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->vondatum).', '.
				$this->addslashes($this->bisdatum).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			if($this->studiengang_kz == '' || !is_numeric($this->studiengang_kz) || $this->bezeichnung=='')
			{
				$this->errormsg = 'ID der Ferien ungültig';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_ferien SET '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz).', '.
				'vondatum='.$this->addslashes($this->vondatum).', '.
				'bisdatum='.$this->addslashes($this->bisdatum).';';
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes - '.$this->uid;
			return false;
		}
	}

	function isferien($timestamp)
	{
		foreach ($this->ferien AS $f)
			if ($timestamp>=$f->vontimestamp && $timestamp<=$f->bistimestamp)
				return true;
		return false;
	}
}
?>