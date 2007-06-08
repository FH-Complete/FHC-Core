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

class erhalter
{
	var $conn;    				// resource DB-Handle
	var $new;      				// boolean
	var $errormsg;			// string
	var $result = array();			// erhalter Objekt
	
	var $erhalter_kz;		// integer
	var $kurzbz;				// varchar(5)
	var $bezeichnung;			// varchar(255)
	var $dvr;				// varchar(8)
	var $logo;				// ctext
	var $zvr;				// char(16)

	
	// **************************************************************
	// * Konstruktor
	// * @param conn Connection zur Datenbank
	// *        
	// **************************************************************
	function erhalter($conn, $erhalter_kz=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode)
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		else
			$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
			
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		
		if($erhalter_kz != null)
			$this->load($erhalter_kz);
	}
		
	// *****************************************************
	// * Laedt einen Erhalter
	// * @param stg_id ID des Studienganges der zu laden ist
	// * @return true wenn ok, false im Fehlerfall
	// *****************************************************
	function load($erhalter_kz)
	{
		if(!is_numeric($erhalter_kz))
		{
			$this->errormsg = 'Erhalter_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_erhalter WHERE erhalter_kz='$erhalter_kz'";
		
		if($res = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($res))
			{			
				$this->erhalter_kz=$row->erhalter_kz;
				$this->kurzbz=$row->kurzbz;
				$this->bezeichnung=$row->bezeichnung;
				$this->dvr=$row->dvr;
				$this->logo=$row->logo;
				$this->zvr=$row->zvr;
			}
		}
		else 
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		return true;		
	}
		
	// *******************************************
	// * Liefert alle Erhalter
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function getAll($order=null)
	{
		$qry = "SELECT * FROM public.tbl_erhalter";
		
		if($order!=null)
		 	$qry .=" ORDER BY $order";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$stg_obj = new erhalter($this->conn);
			
			$stg_obj->erhalter_kz=$row->erhalter_kz;
			$stg_obj->kurzbz=$row->kurzbz;
			$stg_obj->bezeichnung=$row->bezeichnung;
			$stg_obj->dvr=$row->dvr;
			$stg_obj->logo=$row->logo;
			$stg_obj->zvr=$row->zvr;
			
			$this->result[] = $stg_obj;
		}
		//return $this->result;
		return true;
	}
	
	/**
	 * Loescht einen Erhalter
	 * @param $stg_id ID des zu loeschenden Studienganges
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stg_id)
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
		$this->kurzbz = str_replace("'",'´',$this->kurzbz);
		
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>255)           
		{
			$this->errormsg = "Bezeichnung darf nicht laenger als 255 Zeichen sein bei <b>$this->ext_id</b> - $this->bezeichnung";
			return false;
		}
		if(strlen($this->kurzbz)>5)
		{
			$this->errormsg = "Kurzbez darf nicht laenger als 5 Zeichen sein bei <b>$this->ext_id</b> - $this->kurzbz";
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
		{
			return false;
		}
			
		if($this->new)
		{
			//Pruefen ob erhalter_kz gueltig ist
			if(!is_numeric($this->erhalter_kz))
			{
				$this->errormsg = 'erhalter_kz ungueltig! ('.$this->erhalter_kz.')';
				return false;
			}
			//Neuen Datensatz anlegen		
			$qry = 'INSERT INTO public.tbl_erhalter (erhalter_kz, kurzbz, bezeichnung, dvr, logo, zvr) VALUES ('.
				$this->addslashes($this->erhalter_kz).', '.
				$this->addslashes($this->kurzbz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->dvr).', '.
				$this->addslashes($this->logo).', '.
				$this->addslashes($this->zvr).');';
		}
		else 
		{
			//bestehenden Datensatz akualisieren
			
			//Pruefen ob studiengang_kz gueltig ist
			if(!is_numeric($this->erhalter_kz))
			{
				$this->errormsg = 'erhalter_kz ungueltig.';
				return false;
			}
			
			$qry = 'UPDATE public.tbl_studiengang SET '. 
				'erhalter_kz='.$this->addslashes($this->erhalter_kz).', '.
				'kurzbz='.$this->addslashes($this->kurzbz).', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'dvr='.$this->addslashes($this->dvr).', '.
				'logo='.$this->addslashes($this->logo).', '.
				'zvr='.$this->addslashes($this->zvr).' '.
				'WHERE erhalter_kz='.$this->addslashes($this->erhalter_kz).';';
		}
		//echo $qry;
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