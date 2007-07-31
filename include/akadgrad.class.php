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

class akadgrad
{
	var $conn;    // @var resource DB-Handle
	var $new;      // @var boolean
	var $errormsg; // @var string
	var $result = array(); // @var email Objekt
	
	//Tabellenspalten
	var $akadgrad_id;
	var $akadgrad_kurzbz;
	var $studiengang_kz;
	var $titel;
	var $geschlecht;
	
	// ***********************************************
	// * Konstruktor
	// * @param conn    Connection zur Datenbank
	// *        akadgrad_id ID des zu ladenden Datensatzes
	// ***********************************************
	function akadgrad($conn, $akadgrad_id=null, $unicode=false)
	{
		$this->conn = $conn;
		if($unicode!=null)
		{
			if($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";
				
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		
		if($akadgrad_id != null)
			$this->load($akadgrad_id);
	}
	
	// ***********************************************
	// * Laedt einen Datensatz
	// * @param akadgrad_id ID des zu ladenden Datensatzes
	// ***********************************************
	function load($akadgrad_id)
	{
		//akadgrad_id auf gueltigkeit pruefen
		if(!is_numeric($akadgrad_id) || $akadgrad_id == '')
		{
			$this->errormsg = 'akadgrad_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//laden des Datensatzes
		$qry = "SELECT * FROM lehre.tbl_akadgrad WHERE akadgrad_id='$akadgrad_id';";
		
		if($result = pg_query($this->conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$this->akadgrad_id = $row->akadgrad_id;
				$this->akadgrad_kurzbz = $row->akadgrad_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->titel = $row->titel;
				$this->geschlecht = $row->geschlecht;
				return true;
			}
			else 
			{
				$this->errormsg = 'Fehler bei der Datenbankabfrage';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei der Datenbankabfrage';
			return false;
		}
	}
}
?>