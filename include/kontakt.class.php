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
 * Klasse kontakt 
 * @create 20-12-2006
 */

class kontakt
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	var $done=false;	// @var boolean
	
	//Tabellenspalten
	Var $kontakt_id;	// @var integer
	var $person_id;	// @var integer
	var $firma_id;		// @var integer
	var $kontakttyp;	// @var string
	var $anmerkung;	// @var string
	var $kontakt;		// @var string
	var $zustellung;	// @var boolean
	var $ext_id;		// @var integer
	var $insertamum;	// @var timestamp
	var $insertvon;	// @var bigint
	var $updateamum;	// @var timestamp
	var $updatevon;	// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function kontakt($conn,$kontakt_id=null, $unicode=false)
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
	 * Laedt die Funktion mit der ID $kontakt_id
	 * @param  $kontakt_id ID der zu ladenden  Email
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($kontakt_id)
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
		if(strlen($this->kontakttyp)>32)
		{
			$this->errormsg = 'kontakttyp darf nicht länger als 32 Zeichen sein  - firma_id: '.$row->email_id;
			return false;
		}
		if(strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'anmerkung darf nicht länger als 64 Zeichen sein - firma_id: '.$row->email_id;
			return false;
		}
		if(strlen($this->kontakt)>128)
		{
			$this->errormsg = 'kontakt darf nicht länger als 128 Zeichen sein - firma_id: '.$row->email_id;
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
	 * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO tbl_kontakt (person_id, firma_id, kontakttyp, anmerkung, kontakt, zustellung, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->kontakttyp).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->kontakt).', '.
			     ($this->zustellung?'true':'false').', '. 
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			     $this->done=true;			
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			
			//Pruefen ob kontakt_id eine gueltige Zahl ist
			if(!is_numeric($this->kontakt_id))
			{
				$this->errormsg = 'kontakt_id muss eine gueltige Zahl sein: '.$this->kontakt_id.' ('.$this->person_id.')';
				return false;
			}
			$qry="SELECT * FROM tbl_kontakt WHERE kontakt_id='$this->kontakt_id';";
			if($resultz = pg_query($this->conn, $qry))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->person_id!=$this->person_id) 				$update=true;
					if($rowz->firma_id!=$this->firma_id) 				$update=true;
					if($rowz->kontakttyp!=$this->kontakttyp)				$update=true;
					if($rowz->anmerkung!=$this->anmerkung)			$update=true;
					if($rowz->kontakt!=$this->kontakt) 					$update=true;
					if($rowz->zustellung!=$this->zustellung) 				$update=true;
					if($rowz->ext_id!=$this->ext_id)	 				$update=true;
				
					if($update)
					{
						$qry='UPDATE tbl_kontakt SET '.
							'person_id='.$this->addslashes($this->person_id).', '. 
							'firma_id='.$this->addslashes($this->firma_id).', '. 
							'kontakttyp='.$this->addslashes($this->kontakttyp).', '. 
							'anmerkung='.$this->addslashes($this->anmerkung).', '.  
							'kontakt='.$this->addslashes($this->kontakt).', '. 
							'zustellung='.($this->zustellung?'true':'false').', '.
							'ext_id='.$this->addslashes($this->ext_id).', '. 
						     	'updateamum= now(), '.
						     	'updatevon='.$this->addslashes($this->updatevon).' '.
							'WHERE kontakt_id='.$this->addslashes($this->kontakt_id).';';
							$this->done=true;
					}
				}
			}
		}
		//echo $qry;
		if ($this->done)
		{
			if(pg_query($this->conn, $qry))
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
		else 
		{
			return true;
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