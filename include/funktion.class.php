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
 * Klasse funktion (FAS-Online)
 * @create 14-03-2006
 */

class funktion
{
	var $conn;   			// @var resource DB-Handle
	var $new;     			// @var boolean
	var $errormsg; 		// @var string
	var $result = array(); 	// @var fachbereich Objekt

	//Tabellenspalten
	var $funktion_kurzbz;	// @var integer
	var $beschreibung;		// @var string
	var $aktiv;			// @var boolean
	var $ext_id;			// @var bigint


	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $funktion_kurzbz ID der zu ladenden Funktion
	 */
	function funktion($conn, $funktion_kurzbz=null)
	{
		$this->conn = $conn;
		if($funktion_kurzbz != null)
			$this->load($funktion_kurzbz);
	}

	/**
	 * Laedt alle verfuegbaren Funktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll($uid=null)
	{
		if (is_null($uid))
			$qry='SELECT * FROM public.tbl_funktion order by funktion_kurzbz;';
		else
			$qry="SELECT * FROM public.tbl_funktion JOIN public.tbl_benutzerfunktion USING (funktion_kurzbz) WHERE uid='$uid';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden der Datensaetze';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$funktion_obj = new funktion($this->conn);

			$funktion_obj->funktion_kurzbz 	= $row->funktion_kurzbz;
			$funktion_obj->beschreibung    	= $row->beschreibung;
			$funktion_obj->aktiv           		= $row->aktiv;

			$this->result[] = $funktion_obj;
		}
		return true;
	}

	function checkFunktion($funktion)
	{
		foreach ($this->result AS $fkt)
			if ($fkt->funktion_kurzbz==$funktion)
				return true;
		return false;
	}

	/**
	 * Laedt eine Funktion
	 * @param $funktion_kurzbz ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($funktion_kurzbz)
	{
		if($funktion_kurzbz == '')
		{
			$this->errormsg = 'funktion_bz darf nicht leer sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_funktion WHERE funktion_kurzbz = '$funktion_kurzbz';";

		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Fehler beim laden des Datensatzes';
			return false;
		}

		if($row=pg_fetch_object($res))
		{
			$this->funktion_kurzbz	= $row->funktion_kurzbz;
			$this->beschreibung		= $row->beschreibung;
			$this->aktiv			= $row->aktiv;
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
	 * @param $funktion_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($funktion_kurzbz)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{

		//Laenge Pruefen
		if(strlen($this->beschreibung)>64)
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 128 Zeichen sein bei <b>$this->funktion_kurzbz</b> - $this->beschreibung";
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	function save()
	{
		//Gueltigkeit der Variablen pruefen
		if(!$this->checkvars())
			return false;

		if($this->new)
		{
			//Pruefen ob funktion_kurzbz befüllt ist
			if($this->funktion_kurzbz == '')
			{
				$this->errormsg = 'funktion_kurzbz darf nicht leer sein';
				return false;
			}
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_funktion (funktion_kurzbz, beschreibung, aktiv) VALUES ('.
				$this->addslashes($this->funktion_kurzbz).', '.
				$this->addslashes($this->beschreibung).', '.
				($this->aktiv?'true':'false').'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob fachbereich_id eine gueltige Zahl ist
			if( $this->funktion_kurzbz == '')
			{
				$this->errormsg = 'funktion_kurzbz darf nicht leer sein';
				return false;
			}

			$qry = 'UPDATE public.tbl_funktion SET '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'aktiv='.($this->aktiv?'true':'false') .' '.
				'WHERE funktion_kurzbz = '.$this->addslashes($this->funktion_kurzbz).';';
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