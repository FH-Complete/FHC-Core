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
 * Klasse bankverbindung
 * @create 20-12-2006
 */

class resturlaub
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	
	//Tabellenspalten
	var $mitarbeiter_uid;
	var $resturlaubstage;
	var $mehrarbeitsstunden;
	var $urlaubstageprojahr;
	var $updateamum;
	var $updatevon;
	var $insertamum;
	var $insertvon;
	
	var $vorname;
	var $vornamen;
	var $nachname;

	// **
	// * Konstruktor
	// * @param $conn      Connection
	// *        $uid
	// **
	function resturlaub($conn, $uid=null, $unicode=false)
	{
		$this->conn = $conn;
		
		if($unicode!=null)
		{
			if ($unicode)
			{
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			}
			else
			{
				$qry="SET CLIENT_ENCODING TO 'LATIN9';";
			}
			
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
		
		if($uid!=null)
			$this->load($uid);

	}

	// **
	// * Laedt die Resturlaubstage eines Mitarbeiters
	// * @param  $mitarbeiter_uid ID der zu ladenden  Resturlaubstage
	// * @return true wenn ok, false im Fehlerfall
	// **
	function load($mitarbeiter_uid)
	{
		$qry = "SELECT * FROM campus.tbl_resturlaub WHERE mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->resturlaubstage = $row->resturlaubstage;
				$this->mehrarbeitsstunden = $row->mehrarbeitsstunden;
				$this->urlaubstageprojahr = $row->urlaubstageprojahr;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	// **
	// * Prueft die Variablen auf gueltigkeit
	// * @return true wenn ok, false im Fehlerfall
	// **
	function validate()
	{
		if(!is_numeric($this->resturlaubstage))
		{
			$this->errormsg ='Resturlaubstage muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_numeric($this->mehrarbeitsstunden))
		{
			$this->errormsg ='Mehrarbeitsstunden muss eine gueltige Zahl sein';
			return false;
		}
		if($this->urlaubstageprojahr<0)
		{
			$this->errormsg = 'Urlaubsanspruch darf nicht negativ sein';
			return false;
		}
		return true;
	}
	
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	// **
	// * Speichert den aktuellen Datensatz in die Datenbank
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// **
	function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry = 'INSERT INTO campus.tbl_resturlaub  (mitarbeiter_uid, resturlaubstage, mehrarbeitsstunden, urlaubstageprojahr, insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->addslashes($this->mitarbeiter_uid).', '.
			       $this->addslashes($this->resturlaubstage).', '.
			       $this->addslashes($this->mehrarbeitsstunden).', '.
			       $this->addslashes($this->urlaubstageprojahr).', '.
			       $this->addslashes($this->insertamum).', '.
			       $this->addslashes($this->insertvon).', '.
			       $this->addslashes($this->updateamum).', '.
			       $this->addslashes($this->updatevon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry='UPDATE campus.tbl_resturlaub SET '.
			'resturlaubstage='.$this->addslashes($this->resturlaubstage).', '.
			'mehrarbeitsstunden='.$this->addslashes($this->mehrarbeitsstunden).', '.
			'urlaubstageprojahr='.$this->addslashes($this->urlaubstageprojahr).', '.
 			'updateamum='.$this->addslashes($this->updateamum).', '.
 			'updatevon='.$this->addslashes($this->updatevon).
 			' WHERE mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).';';
		}
		//echo $qry;		
		if(pg_query($this->conn, $qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	// ***********************************
	// * Liefert die Resturlaubstage der
	// * Fixangestellten
	// ***********************************
	function getResturlaubFixangestellte()
	{
		$qry = "SELECT * FROM campus.vw_mitarbeiter LEFT JOIN campus.tbl_resturlaub ON(uid=mitarbeiter_uid) 
				WHERE fixangestellt=true ORDER BY nachname, vorname";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new resturlaub($this->conn, null, null);
				
				$obj->mitarbeiter_uid = $row->uid;
				$obj->resturlaubstage = $row->resturlaubstage;
				$obj->mehrarbeitsstunden = $row->mehrarbeitsstunden;
				$obj->urlaubstageprojahr = $row->urlaubstageprojahr;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->vorname = $row->vorname;
				$obj->vornamen = $row->vornamen;
				$obj->nachname = $row->nachname;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>