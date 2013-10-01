<?php
/*
 * studienplatz.class.php
 * 
 * Copyright 2013 Technikum-Wien
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class studienplatz extends basis_db
{
	private $new;					// boolean
	private $result;				// DB-Result
	public $studienplatz = array();	// Objekt

	//Tabellenspalten
	private $studienplatz_id;		// integer
	private $studiengang_kz;		// integer
	private $orgform_kurzbz; 		// varchar
	private $studiensemester;		// varchar
	private $gpz;					// integer
	private $npz;            		// integer
	private $updateamum;			// timestamp
	private $updatevon;				// varchar
	private $insertamum;      		// timestamp
	private $insertvon;      		// varchar

	/**
	 * Konstruktor
	 * @param $studienplatz_id ID des Studienplatz der geladen werden soll (Default=null)
	 */
	public function __construct($studienplatz_id=null)
	{
		parent::__construct();
		
		if(!is_null($studienplatz_id))
			$this->load($studienplatz_id);
	}

	/**
	 * Laedt die Adresse mit der ID $studienplatz_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadStudienplatz($studienplatz_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($studienplatz_id) || $studienplatz_id == '')
		{
			$this->errormsg = 'Adresse_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_adresse WHERE studienplatz_id=".$this->db_add_param($studienplatz_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->adresse_id		= $row->adresse_id;
			$this->heimatadresse 	= $this->db_parse_bool($row->heimatadresse);
			$this->zustelladresse	= $this->db_parse_bool($row->zustelladresse);
			$this->gemeinde			= $row->gemeinde;
			$this->name				= $row->name;
			$this->nation			= $row->nation;
			$this->ort				= $row->ort;
			$this->person_id		= $row->person_id;
			$this->plz				= $row->plz;
			$this->strasse			= $row->strasse;
			$this->typ				= $row->typ;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
			$this->firma_id			= $row->firma_id;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Adressen zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_pers($pers_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'person_id muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id=".$this->db_add_param($pers_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$adr_obj = new adresse();

			$adr_obj->adresse_id      = $row->adresse_id;
			$adr_obj->heimatadresse   = $this->db_parse_bool($row->heimatadresse);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_id;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->firma_id		  = $row->firma_id;
			$adr_obj->updateamum      = $row->updateamum;
			$adr_obj->updatevon       = $row->updatevon;
			$adr_obj->insertamum      = $row->insertamum;
			$adr_obj->insertvon       = $row->insertvon;
			$adr_obj->zustelladresse  = $this->db_parse_bool($row->zustelladresse);

			$this->result[] = $adr_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle Adressen zu der Firma die uebergeben wird
	 * 
	 * ACHTUNG: Diese Funktion wird nur mehr fuer Lehrauftraege benoetigt.
	 * Die Adresse zu einer Firma wird nun ueber die Tabelle Standort hergestellt!
	 * 
	 * @deprec  2.0
	 * @param $firma_id ID der Firma zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_firma($firma_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($firma_id) || $firma_id == '')
		{
			$this->errormsg = 'firma_id muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_adresse WHERE firma_id=".$this->db_add_param($firma_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$adr_obj = new adresse();

			$adr_obj->adresse_id      = $row->adresse_id;
			$adr_obj->heimatadresse   = $this->db_parse_bool($row->heimatadresse);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_id;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->firma_id		  = $row->firma_id;
			$adr_obj->updateamum      = $row->updateamum;
			$adr_obj->updatevon       = $row->updatevon;
			$adr_obj->insertamum      = $row->insertamum;
			$adr_obj->insertvon       = $row->insertvon;
			$adr_obj->zustelladresse  = $this->db_parse_bool($row->zustelladresse);

			$this->result[] = $adr_obj;
		}
		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->person_id) && $this->person_id!='')
		{
			$this->errormsg='person_id enthaelt ungueltige Zeichen';
			return false;
		}
		//Gesamtlaenge pruefen
		if(mb_strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->strasse)>255)
		{
			$this->errormsg = 'Strasse darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->plz)>10)
		{
			$this->errormsg = 'Plz darf nicht länger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->ort)>255)
		{
			$this->errormsg = 'Ort darf nicht länger als 255 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->nation)>3)
		{
			$this->errormsg = 'Nation darf nicht länger als 3 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->gemeinde)>255)
		{
			$this->errormsg = 'Gemeinde darf nicht länger als 255 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
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
			$qry='BEGIN;INSERT INTO public.tbl_adresse (person_id, name, strasse, plz, typ, ort, nation, insertamum, insertvon,
			     gemeinde, heimatadresse, zustelladresse, firma_id, updateamum, updatevon, ext_id) VALUES('.
			      $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->name).', '.
			      $this->db_add_param($this->strasse).', '.
			      $this->db_add_param($this->plz).', '.
			      $this->db_add_param(trim($this->typ)).', '.
			      $this->db_add_param($this->ort).', '.
			      $this->db_add_param($this->nation).', now(), '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->gemeinde).', '.
			      $this->db_add_param($this->heimatadresse,FHC_BOOLEAN, false).', '.
			      $this->db_add_param($this->zustelladresse,FHC_BOOLEAN, false).', '.
			      $this->db_add_param($this->firma_id, FHC_INTEGER).', now(), '.
			      $this->db_add_param($this->updatevon).', '.
			      $this->db_add_param($this->ext_id, FHC_INTEGER).');';
		}
		else
		{
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->adresse_id))
			{
				$this->errormsg = 'adresse_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_adresse SET'.
				' person_id='.$this->db_add_param($this->person_id, FHC_INTEGER).', '.
				' name='.$this->db_add_param($this->name).', '.
				' strasse='.$this->db_add_param($this->strasse).', '.
				' plz='.$this->db_add_param($this->plz).', '.
		      	' typ='.$this->db_add_param(trim($this->typ)).', '.
		      	' ort='.$this->db_add_param($this->ort).', '.
		      	' nation='.$this->db_add_param($this->nation).', '.
		      	' gemeinde='.$this->db_add_param($this->gemeinde).', '.
		      	' firma_id='.$this->db_add_param($this->firma_id, FHC_INTEGER).','.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).', '.
		      	' heimatadresse='.$this->db_add_param($this->heimatadresse, FHC_BOOLEAN, false).', '.
		      	' zustelladresse='.$this->db_add_param($this->zustelladresse, FHC_BOOLEAN, false).' '.
		      	'WHERE adresse_id='.$this->db_add_param($this->adresse_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('public.tbl_adresse_adresse_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->adresse_id = $row->id;
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Adress-Datensatzes';
			return false;
		}
		return $this->adresse_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $adresse_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($adresse_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($adresse_id) || $adresse_id == '')
		{
			$this->errormsg = 'adresse_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_adresse WHERE adresse_id=".$this->db_add_param($adresse_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}
}
?>
