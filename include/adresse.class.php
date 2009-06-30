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
	public $firma_id;			//  integer
	public $updateamum;			//  timestamp
	public $updatevon;			//  string
	public $insertamum;      	//  timestamp
	public $insertvon;      	//  string
	public $ext_id;				//  integer

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
		$qry = "SELECT * FROM public.tbl_adresse WHERE adresse_id='".addslashes($adresse_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->adresse_id		= $row->adresse_id;
			$this->heimatadresse 	= ($row->heimatadresse=='t'?true:false);
			$this->zustelladresse	= ($row->zustelladresse=='t'?true:false);
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
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id='".addslashes($pers_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$adr_obj = new adresse();

			$adr_obj->adresse_id      = $row->adresse_id;
			$adr_obj->heimatadresse = ($row->heimatadresse=='t'?true:false);
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
			$adr_obj->zustelladresse  = ($row->zustelladresse=='t'?true:false);

			$this->result[] = $adr_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle Adressen zu der Firma die uebergeben wird
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
		$qry = "SELECT * FROM public.tbl_adresse WHERE firma_id='".addslashes($firma_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object($res))
		{
			$adr_obj = new adresse();

			$adr_obj->adresse_id      = $row->adresse_id;
			$adr_obj->heimatadresse = ($row->heimatadresse=='t'?true:false);
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
			$adr_obj->zustelladresse  = ($row->zustelladresse=='t'?true:false);

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
			      $this->addslashes($this->person_id).', '.
			      $this->addslashes($this->name).', '.
			      $this->addslashes($this->strasse).', '.
			      $this->addslashes($this->plz).', '.
			      $this->addslashes($this->typ).', '.
			      $this->addslashes($this->ort).', '.
			      $this->addslashes($this->nation).', now(), '.
			      $this->addslashes($this->insertvon).', '.
			      $this->addslashes($this->gemeinde).', '.
			      ($this->heimatadresse?'true':'false').', '.
			      ($this->zustelladresse?'true':'false').', '.
			      ($this->firma_id!=null?$this->addslashes($this->firma_id):'null').', now(), '.
			      $this->addslashes($this->updatevon).', '.
			      $this->addslashes($this->ext_id).');';
		}
		else
		{
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->adresse_id))
			{
				$this->errormsg = 'adresse_id muss eine gültige Zahl sein: '.$this->adresse_id."\n";
				return false;
			}
			$qry='UPDATE public.tbl_adresse SET'.
				' person_id='.$this->addslashes($this->person_id).', '.
				' name='.$this->addslashes($this->name).', '.
				' strasse='.$this->addslashes($this->strasse).', '.
				' plz='.$this->addslashes($this->plz).', '.
		      	' typ='.$this->addslashes($this->typ).', '.
		      	' ort='.$this->addslashes($this->ort).', '.
		      	' nation='.$this->addslashes($this->nation).', '.
		      	' gemeinde='.$this->addslashes($this->gemeinde).', '.
		      	' firma_id='.$this->addslashes($this->firma_id).','.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->addslashes($this->updatevon).', '.
		      	' heimatadresse='.($this->heimatadresse?'true':'false').', '.
		      	' zustelladresse='.($this->zustelladresse?'true':'false').' '.
		      	'WHERE adresse_id='.$this->adresse_id.';';
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
						return true;
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
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Adress-Datensatzes';
			return false;
		}
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
		$qry="DELETE FROM public.tbl_adresse WHERE adresse_id='".addslashes($adresse_id)."';";

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