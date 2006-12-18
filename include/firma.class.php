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
 * Klasse firma 
 * @create 18-12-2006
 */

class firma
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	
	//Tabellenspalten
	var $firma_id;		// @var integer
	var $name;		// @var string
	var $anmerkung;	// @var string
	var $ext_id;		// @var integer
	var $insertamum;	// @var timestamp
	var $insertvon;	// @var bigint
	var $updateamum;	// @var timestamp
	var $updatevon;	// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $firma_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function firma($conn,$firma_id=null, $unicode=false)
	{
		$this->conn = $conn;
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
		//if($firma_id != null) 	$this->load($firma_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $adress_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($adress_id)
	{
		//noch nicht implementiert
	}
			
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
				
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>64)
		{
			$this->errormsg = 'Name darf nicht länger als 64 Zeichen sein  - firma_id: '.$row->firma_id;
			return false;
		}
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'anmerkung darf nicht länger als 256 Zeichen sein - firma_id: '.$row->firma_id;
			return false;
		}
				
		$this->errormsg = '';
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
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			
			//naechste ID aus der Sequence holen
			$qry="SELECT nextval('public.tbl_firma_firma_id_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Sequence';
				return false;
			}
			$this->firma_id = $row->id;
			
			$qry='INSERT INTO tbl_firma (firma_id, name, anmerkung, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->name).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';			
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			
			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry='UPDATE tbl_firma SET '.
				'firma_id='.$this->addslashes($this->firma_id).', '. 
				'name='.$this->addslashes($this->name).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.  
			     	'updateamum= now(), '.
			     	'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE ext_id='.$this->addslashes($this->ext_id).';';
		}
		//echo $qry;
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			/*$sql = $qry;
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
			}	*/
			return true;		
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $firma_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($firma_id)
	{
		//noch nicht implementiert!	
	}
}
?>