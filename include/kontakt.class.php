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
require_once(dirname(__FILE__).'/basis_db.class.php');

class kontakt extends basis_db
{
	public $new;       // boolean
	public $result = array(); // adresse Objekt
	
	//Tabellenspalten
	public $kontakt_id;	// integer
	public $person_id;	// integer
	public $firma_id;		// integer
	public $kontakttyp;	// string
	public $anmerkung;	// string
	public $kontakt;		// string
	public $zustellung;	// boolean
	public $ext_id;		// integer
	public $insertamum;	// timestamp
	public $insertvon;	// bigint
	public $updateamum;	// timestamp
	public $updatevon;	// bigint
	
	public $beschreibung;
	public $firma_name;
	
	/**
	 * Konstruktor
	 * @param $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($kontakt_id=null)
	{
		parent::__construct();
		
		if(!is_null($kontakt_id))
			$this->load($kontakt_id);
	}
	
	/**
	 * Laedt einen Kontakt mit der ID $kontakt_id
	 * @param  $kontakt_id ID des zu ladenden Kontaktes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($kontakt_id)
	{
		if(!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name 
				FROM public.tbl_kontakt LEFT JOIN public.tbl_firma USING(firma_id) WHERE kontakt_id='$kontakt_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{		
				
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(mb_strlen($this->kontakttyp)>32)
		{
			$this->errormsg = 'kontakttyp darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'anmerkung darf nicht länger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kontakt)>128)
		{
			$this->errormsg = 'kontakt darf nicht länger als 128 Zeichen sein';
			return false;
		}
		$this->errormsg = '';
		return true;		
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kontakt_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
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
				$this->errormsg = 'kontakt_id muss eine gueltige Zahl sein';
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
		
		if($this->db_query($qry))
		{
			//Sequence auslesen um die eingefuegte ID zu ermitteln
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_kontakt_kontakt_id_seq') as id";
				
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kontakt_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen er Sequence';
						$this->db_query('ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
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
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $kontakt_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($kontakt_id)
	{
		if(!is_numeric($kontakt_id))
		{
			$this->errormsg = 'Kontakt_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_kontakt WHERE kontakt_id='$kontakt_id'";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}	
	}
	
	/**
	 * Laedt alle Kontaktdaten einer Person
	 * @param person_id
	 * @return boolean
	 */
	public function load_pers($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT tbl_kontakt.*, tbl_firma.name as firma_name 
				FROM public.tbl_kontakt LEFT JOIN public.tbl_firma USING(firma_id) WHERE person_id='$person_id'";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();
				
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
			
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Kontakttypen
	 * @return true wenn ok
	 * false im Fehlerfall
	 */
	public function getKontakttyp()
	{
		$qry = "SELECT * FROM public.tbl_kontakttyp ORDER BY beschreibung";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kontakt();
				
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