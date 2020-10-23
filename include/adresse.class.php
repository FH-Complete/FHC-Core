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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
 */
/**
 * Klasse Adresse
 * @create 13-03-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class adresse extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  adresse Objekt

	//Tabellenspalten
	public $adresse_id;			//  integer
	public $person_id;			//  integer
	public $name; 				//  string
	public $strasse;			//  string
	public $plz;				//  string
	public $ort;            	//  string
	public $gemeinde;			//  string
	public $nation;          	//  string
	public $typ;				//  string
	public $heimatadresse;		//  boolean
	public $zustelladresse;		//  boolean
	public $coname;             //  string
	public $firma_id;			//  integer
	public $updateamum;			//  timestamp
	public $updatevon;			//  string
	public $insertamum;      	//  timestamp
	public $insertvon;      	//  string
	public $ext_id;				//  integer
	public $rechnungsadresse=false;	//  boolean
	public $anmerkung;			//  string
	public $co_name;

	/**
	 * Konstruktor
	 * @param $adress_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($adresse_id=null)
	{
		parent::__construct();

		if(!is_null($adresse_id))
			$this->load($adresse_id);
	}

	/**
	 * Laedt die Adresse mit der ID $adresse_id
	 * @param  $adress_id ID der zu ladenden Adresse
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($adresse_id)
	{
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($adresse_id) || $adresse_id == '')
		{
			$this->errormsg = 'Adresse_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_adresse WHERE adresse_id=".$this->db_add_param($adresse_id, FHC_INTEGER, false);

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
			$this->co_name			= $row->co_name;
			$this->typ				= $row->typ;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
			$this->firma_id			= $row->firma_id;
			$this->rechnungsadresse = $this->db_parse_bool($row->rechnungsadresse);
			$this->anmerkung 		= $row->anmerkung;
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
		$qry.=" ORDER BY zustelladresse DESC";

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
			$adr_obj->co_name		  = $row->co_name;
			$adr_obj->rechnungsadresse 	= $this->db_parse_bool($row->rechnungsadresse);
			$adr_obj->anmerkung 	  = $row->anmerkung;

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
			$adr_obj->co_name		  = $row->co_name;
			$adr_obj->rechnungsadresse = $this->db_parse_bool($row->rechnungsadresse);
			$adr_obj->anmerkung 	  = $row->anmerkung;

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
		if(mb_strlen($this->plz)>16)
		{
			$this->errormsg = 'Plz darf nicht länger als 16 Zeichen sein';
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

		if(mb_strlen($this->co_name)>64)
		{
			$this->errormsg = 'Gemeinde darf nicht länger als 64 Zeichen sein';
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
				gemeinde, heimatadresse, zustelladresse, firma_id, updateamum, updatevon, rechnungsadresse, anmerkung, co_name) VALUES('.
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
				$this->db_add_param($this->updatevon).','.
				$this->db_add_param($this->rechnungsadresse, FHC_BOOLEAN, false).','.
				$this->db_add_param($this->anmerkung).','.
				$this->db_add_param($this->co_name).');';
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
				' zustelladresse='.$this->db_add_param($this->zustelladresse, FHC_BOOLEAN, false).', '.
				' rechnungsadresse='.$this->db_add_param($this->rechnungsadresse, FHC_BOOLEAN, false).','.
				' anmerkung='.$this->db_add_param($this->anmerkung).', '.
				' co_name='.$this->db_add_param($this->co_name).' '.
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

	/**
	 * Laedt die Zustell Adresse einer Person
	 * @param  $person_id ID der Person
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadZustellAdresse($person_id)
	{
		//Pruefen ob person_id eine gueltige Zahl ist
		if(!is_numeric($person_id) || $person_id == '')
		{
			$this->errormsg = 'PersonID muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER, false)."
			ORDER BY zustelladresse DESC LIMIT 1";

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
			$this->rechnungsadresse = $this->db_parse_bool($row->rechnungsadresse);
			$this->anmerkung        = $row->anmerkung;
			$this->co_name			= $row->co_name;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		return true;
	}


	/**
	 * Laedt die Rechnungsadresse zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_rechnungsadresse($pers_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'person_id muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_adresse WHERE rechnungsadresse and person_id=".$this->db_add_param($pers_id, FHC_INTEGER, false);
		$qry.=" limit 1";

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
			$adr_obj->rechnungsadresse 	= $this->db_parse_bool($row->rechnungsadresse);
			$adr_obj->anmerkung 	  = $row->anmerkung;
			$adr_obj->co_name 	  = $row->co_name;

			$this->result[] = $adr_obj;
		}
		return true;
	}
}
?>
