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
require_once(dirname(__FILE__).'/basis_db.class.php');

class firma extends basis_db
{
	public $new;       			// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $firma_id;		// integer
	public $name;			// string
	public $adresse;		// string
	public $email;			// string
	public $telefon;		// string
	public $fax;			// string
	public $anmerkung;		// string
	public $ext_id;			// integer
	public $insertamum;		// timestamp
	public $insertvon;		// bigint
	public $updateamum;		// timestamp
	public $updatevon;		// bigint
	public $firmentyp_kurzbz;	
	public $schule; 		// boolean

	/**
	 * Konstruktor
	 * @param $firma_id ID der Firma die geladen werden soll (Default=null)
	 */
	public function __construct($firma_id=null)
	{
		parent::__construct();
		
		if(!is_null($firma_id))
			$this->load($firma_id);
	}

	/**
	 * Laedt die Firma mit der ID $firma_id
	 * @param  $firma_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}
		
		$qry = "SElECT * FROM public.tbl_firma WHERE firma_id='$firma_id'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->firma_id = $row->firma_id;
				$this->name = $row->name;
				$this->adresse = $row->adresse;
				$this->email  = $row->email;
				$this->telefon = $row->telefon;
				$this->fax = $row->fax;
				$this->anmerkung = $row->anmerkung;
				$this->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->schule = ($row->schule=='t'?true:false);
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
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if(strlen($this->name)>128)
		{
			$this->errormsg = 'Name darf nicht länger als 128 Zeichen sein';
			return false;
		}
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
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
			$qry='INSERT INTO public.tbl_firma (name, adresse, email, telefon, fax, anmerkung, 
					firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id, schule) VALUES('.
			     $this->addslashes($this->name).', '.
			     $this->addslashes($this->adresse).', '.
			     $this->addslashes($this->email).', '.
			     $this->addslashes($this->telefon).', '.
			     $this->addslashes($this->fax).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->firmentyp_kurzbz).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).','.
			     ($this->schule?'true':'false').'); ';
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

			$qry='UPDATE public.tbl_firma SET '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'name='.$this->addslashes($this->name).', '.
				'adresse='.$this->addslashes($this->adresse).', '.
				'email='.$this->addslashes($this->email).', '.
				'telefon='.$this->addslashes($this->telefon).', '.
				'fax='.$this->addslashes($this->fax).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'updateamum= now(), '.
		     	'updatevon='.$this->addslashes($this->updatevon).', '.
		     	'firmentyp_kurzbz='.$this->addslashes($this->firmentyp_kurzbz).', '.
		     	'schule='.($this->schule?'true':'false').' '.
				'WHERE firma_id='.$this->addslashes($this->firma_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_firma_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->firma_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
			$this->errormsg = 'Fehler beim Speichern des Firma-Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $firma_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_firma WHERE firma_id='$firma_id'";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Firmen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SElECT * FROM public.tbl_firma ORDER BY name";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->adresse = $row->adresse;
				$fa->email  = $row->email;
				$fa->telefon = $row->telefon;
				$fa->fax = $row->fax;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Liefert alle vorhandenen Firmentypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmenTypen()
	{
		$qry = "SELECT * FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->beschreibung = $row->beschreibung;
				
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Auslesen der Firmentypen';
			return false;
		}
	}
	
	/**
	 * Laedt alle Firmen eines bestimmen Firmentyps
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmen($firmentyp_kurzbz='')
	{
		$qry = "SElECT * FROM public.tbl_firma";
		
		if($firmentyp_kurzbz!='')
			$qry.=" WHERE firmentyp_kurzbz='".addslashes($firmentyp_kurzbz)."'";
		$qry.=" ORDER BY name";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->adresse = $row->adresse;
				$fa->email  = $row->email;
				$fa->telefon = $row->telefon;
				$fa->fax = $row->fax;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
}
?>