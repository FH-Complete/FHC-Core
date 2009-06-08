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
	
	var $beschreibung;
	var $firma_name;
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function kontakt($conn,$kontakt_id=null, $unicode=false)
	{
		$this->conn = $conn;
/*		
		if($unicode!=null)
		{
			if ($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else 
				$qry="SET CLIENT_ENCODING TO 'LATIN9';";
			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
*/	
		if($kontakt_id != null)
			$this->load($kontakt_id);
	}
	
	/**
	 * Laedt einen Kontakt mit der ID $kontakt_id
	 * @param  $kontakt_id ID des zu ladenden Kontaktes
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($kontakt_id)
	{
		if(!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name FROM public.tbl_kontakt LEFT JOIN public.tbl_firma USING(firma_id) WHERE kontakt_id='$kontakt_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->kontakt_id = $row->kontakt_id;
				$this->person_id = $row->person_id;
				$this->firma_id = $row->firma_id;
				$this->firma_name = $row->firma_name;
				$this->kontakttyp = $row->kontakttyp;
				$this->anmerkung = $row->anmerkung;
				$this->kontakt = $row->kontakt;
				$this->zustellung = ($row->zustellung=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
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
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='BEGIN;INSERT INTO public.tbl_kontakt (person_id, firma_id, kontakttyp, anmerkung, kontakt, zustellung, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->kontakttyp).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->kontakt).', '.
			     ($this->zustellung?'true':'false').', '. 
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';	
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
			
			$qry='UPDATE public.tbl_kontakt SET '.
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
		}
		
		if(pg_query($this->conn, $qry))
		{
			//Sequence auslesen um die eingefuegte ID zu ermitteln
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_kontakt_kontakt_id_seq') as id";
				
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->kontakt_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen er Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}				
			return true;		
		}
		else 
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	// **
	// * Loescht den Datenensatz mit der ID die uebergeben wird
	// * @param $kontakt_id ID die geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// **
	function delete($kontakt_id)
	{
		if(!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_kontakt WHERE kontakt_id='$kontakt_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}	
	}
	
	// **
	// * Laedt alle Kontaktdaten einer Person
	// **
	function load_pers($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name FROM public.tbl_kontakt LEFT JOIN public.tbl_firma USING(firma_id) WHERE person_id='$person_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new kontakt($this->conn, null, null);
				
				$obj->kontakt_id = $row->kontakt_id;
				$obj->person_id = $row->person_id;
				$obj->firma_id = $row->firma_id;
				$obj->firma_name = $row->firma_name;
				$obj->kontakttyp = $row->kontakttyp;
				$obj->anmerkung = $row->anmerkung;
				$obj->kontakt = $row->kontakt;
				$obj->zustellung = ($row->zustellung=='t'?true:false);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				
				$this->result[] = $obj;
			}
		}
	}
	
	// **************************
	// * Laedt alle Kontakttypen
	// * @return true wenn ok
	// * false im Fehlerfall
	// **************************
	function getKontakttyp()
	{
		$qry = "SELECT * FROM public.tbl_kontakttyp ORDER BY beschreibung";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new kontakt($this->conn, null, null);
				
				$obj->kontakttyp = $row->kontakttyp;
				$obj->beschreibung = $row->beschreibung;
				
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