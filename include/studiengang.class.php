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

class studiengang
{
	var $conn;    			// resource DB-Handle
	var $new;      			// boolean
	var $errormsg;			// string
	var $result = array();	// studiengang Objekt
	
	var $studiengang_kz;	// integer
	var $kurzbz;			// varchar(5)
	var $kurzbzlang;		// varchar(10)
	var $bezeichnung;		// varchar(128)
	var $english;			// varchar(128)
	var $typ;				// char(1)
	var $farbe;				// char(6)
	var $email;				// varchar(64)
	var $max_semester;		// smallint
	var $max_verband;		// char(1)
	var $max_gruppe;		// char(1)
	var $erhalter_kz;		// smallint
	var $bescheid;			// varchar(256)
	var $bescheidbgbl1;		// varchar(16)
	var $bescheidbgbl2;		// varchar(16)
	var $bescheidgz;		// varchar(16)
	var $bescheidvom;		// Date
	var $ext_id;			// bigint
	
	var $kuerzel;			// = typ + kurzbz (Bsp: BBE)
	
	// **************************************************************
	// * Konstruktor
	// * @param conn Connection zur Datenbank
	// *        studiengang_kz Kennzahl des zu ladenden Studienganges
	// **************************************************************
	function studiengang($conn, $studiengang_kz=null, $unicode=false)
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
		
		if($studiengang_kz != null)
			$this->load($studiengang_kz);
	}
		
	// *****************************************************
	// * Laedt einen Studiengang
	// * @param stg_id ID des Studienganges der zu laden ist
	// * @return true wenn ok, false im Fehlerfall
	// *****************************************************
	function load($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_studiengang WHERE studiengang_kz='$studiengang_kz'";
		
		if($res = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($res))
			{			
				$this->studiengang_kz=$row->studiengang_kz;
				$this->kurzbz=$row->kurzbz;
				$this->kurzbzlang=$row->kurzbzlang;
				$this->bezeichnung=$row->bezeichnung;
				$this->english=$row->english;
				$this->typ=$row->typ;
				$this->farbe=$row->farbe;
				$this->email=$row->email;
				$this->max_semester=$row->max_semester;
				$this->max_verband=$row->max_verband;
				$this->max_semester=$row->max_semester;
				$this->erhalter_kz=$row->erhalter_kz;
				$this->bescheid=$row->bescheid;
				$this->bescheidbgbl1=$row->bescheidbgbl1;
				$this->bescheidbgbl2=$row->bescheidbgbl2;
				$this->bescheidgz=$row->bescheidgz;
				$this->bescheidvom=$row->bescheidvom;
				$this->ext_id=$row->ext_id;
				$this->kuerzel = strtoupper($row->typ.$row->kurzbz);
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
	// * Liefert alle Studiengaenge
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function getAll($order=null)
	{
		$qry = "SELECT * FROM public.tbl_studiengang";
		
		if($order!=null)
		 	$qry .=" ORDER BY $order";
		
		if(!$res = pg_query($this->conn, $qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$stg_obj = new studiengang($this->conn);
			
			$stg_obj->studiengang_kz=$row->studiengang_kz;
			$stg_obj->kurzbz=$row->kurzbz;
			$stg_obj->kurzbzlang=$row->kurzbzlang;
			$stg_obj->bezeichnung=$row->bezeichnung;
			$stg_obj->english=$row->english;
			$stg_obj->typ=$row->typ;
			$stg_obj->farbe=$row->farbe;
			$stg_obj->email=$row->email;
			$stg_obj->max_semester=$row->max_semester;
			$stg_obj->max_verband=$row->max_verband;
			$stg_obj->max_semester=$row->max_semester;
			$stg_obj->erhalter_kz=$row->erhalter_kz;
			$stg_obj->bescheid=$row->bescheid;
			$stg_obj->bescheidbgbl1=$row->bescheidbgbl1;
			$stg_obj->bescheidbgbl2=$row->bescheidbgbl2;
			$stg_obj->bescheidgz=$row->bescheidgz;
			$stg_obj->bescheidvom=$row->bescheidvom;
			$stg_obj->ext_id=$row->ext_id;
			$stg_obj->kuerzel = strtoupper($row->typ.$row->kurzbz);
			
			$this->result[] = $stg_obj;
		}
		
		return true;
	}
	
	/**
	 * Loescht einen Studiengang
	 * @param $stg_id ID des zu loeschenden Studienganges
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($stg_id)
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
	
	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->errormsg = 'Noch nicht implementiert';
		return false;
	}
}
?>