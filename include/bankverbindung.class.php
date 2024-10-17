<?php
/* Copyright (C) 2006 fhcomplete.org
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Klasse bankverbindung
 * @create 20-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class bankverbindung extends basis_db
{
	public $new;				// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $bankverbindung_id;	// integer
	public $person_id;			// integer
	public $name;				// string
	public $anschrift;			// string
	public $bic;				// string
	public $blz;				// string
	public $iban;				// string
	public $kontonr;			// string
	public $typ;				// p=Privatkonto, f=Firmenkonto
	public $verrechnung;		// boolean
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// bigint
	public $updateamum;			// timestamp
	public $updatevon;			// bigint
	public $oe_kurzbz;			// string
	public $orgform_kurzbz;		// string

	/**
	 * Konstruktor
	 * @param $bankverbindung_id ID der Bankverbindung die geladen werden soll (Default=null)
	 */
	public function __construct($bankverbindung_id=null)
	{
		parent::__construct();

		if(!is_null($bankverbindung_id))
			$this->load($bankverbindung_id);
	}

	/**
	 * Laedt die Bankverbindung mit der ID $bankverbindung_id
	 * @param  $bankverbindung_id ID der zu ladenden Bankverbindung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($bankverbindung_id)
	{
		if(!is_numeric($bankverbindung_id))
		{
			$this->errormsg = 'Bankverbindung_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE bankverbindung_id=".$this->db_add_param($bankverbindung_id);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bankverbindung_id = $row->bankverbindung_id;
				$this->person_id = $row->person_id;
				$this->name = $row->name;
				$this->anschrift = $row->anschrift;
				$this->bic = $row->bic;
				$this->blz = $row->blz;
				$this->iban = $row->iban;
				$this->kontonr = $row->kontonr;
				$this->typ = $row->typ;
				$this->verrechnung = $this->db_parse_bool($row->verrechnung);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
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
	protected function validate()
	{
		//Gesamtlaenge pruefen
		//$this->errormsg = 'Eine der Maximiallaengen wurde ueberschritten';
		if(mb_strlen($this->name)>64)
		{
			$this->errormsg = 'Name darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anschrift)>128)
		{
			$this->errormsg = 'Anschrift darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->blz)>16)
		{
			$this->errormsg = 'BLZ darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bic)>16)
		{
			$this->errormsg = 'BIC darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kontonr)>16)
		{
			$this->errormsg = 'KontoNr darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->iban)>32)
		{
			$this->errormsg = 'IBAN darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
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

			$qry = 'BEGIN;INSERT INTO public.tbl_bankverbindung  (person_id, name, anschrift, blz, bic,
					kontonr, iban, typ, oe_kurzbz, orgform_kurzbz, verrechnung, insertamum, insertvon, updateamum, updatevon) VALUES('.
					$this->db_add_param($this->person_id, FHC_INTEGER).', '.
					$this->db_add_param($this->name).', '.
					$this->db_add_param($this->anschrift).', '.
					$this->db_add_param($this->blz).', '.
					$this->db_add_param($this->bic).', '.
					$this->db_add_param($this->kontonr).', '.
					$this->db_add_param($this->iban).', '.
					$this->db_add_param($this->typ).', '.
					$this->db_add_param($this->oe_kurzbz).', '.
					$this->db_add_param($this->orgform_kurzbz).', '.
					$this->db_add_param($this->verrechnung, FHC_BOOLEAN).',  now(), '.
					$this->db_add_param($this->insertvon).', now(), '.
					$this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob bankverbindung_id eine gueltige Zahl ist
			if(!is_numeric($this->bankverbindung_id))
			{
				$this->errormsg = 'bankverbindung_id muss eine gueltige Zahl sein: '.$this->bankverbindung_id.' ('.$this->person_id.')';
				return false;
			}

			$qry='UPDATE public.tbl_bankverbindung SET '.
			'person_id='.$this->db_add_param($this->person_id, FHC_INTEGER).', '.
			'name='.$this->db_add_param($this->name).', '.
 			'anschrift='.$this->db_add_param($this->anschrift).', '.
 			'blz='.$this->db_add_param($this->blz).', '.
 			'bic='.$this->db_add_param($this->bic).', '.
 			'kontonr='.$this->db_add_param($this->kontonr).', '.
 			'iban='.$this->db_add_param($this->iban).', '.
 			'typ='.$this->db_add_param($this->typ).', '.
 			'verrechnung='.$this->db_add_param($this->verrechnung,FHC_BOOLEAN).', '.
 			'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
			'orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
 			'updateamum='.$this->db_add_param($this->updateamum).','.
 			'updatevon='.$this->db_add_param($this->updatevon).' '.
 			'WHERE bankverbindung_id='.$this->db_add_param($this->bankverbindung_id).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('public.tbl_bankverbindung_bankverbindung_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->bankverbindung_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $bankverbindung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($bankverbindung_id)
	{
		if(!is_numeric($bankverbindung_id))
		{
			$this->errormsg = 'Bankverbindung_id ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_bankverbindung WHERE bankverbindung_id=".$this->db_add_param($bankverbindung_id, FHC_INTEGER);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Bankverbindung einer Person
	 * @param  $person_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_pers($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bankverbindung();

				$obj->bankverbindung_id = $row->bankverbindung_id;
				$obj->person_id = $row->person_id;
				$obj->name = $row->name;
				$obj->anschrift = $row->anschrift;
				$obj->bic = $row->bic;
				$obj->blz = $row->blz;
				$obj->iban = $row->iban;
				$obj->kontonr = $row->kontonr;
				$obj->typ = $row->typ;
				$obj->verrechnung = $this->db_parse_bool($row->verrechnung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;

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
	 * Laedt die Bankverbindung einer Organisationseinheit und optional einer OrgForm
	 * @param string $oe_kurzbz
	 * @param string $orgform_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_oe($oe_kurzbz, $orgform_kurzbz = null)
	{
		if($oe_kurzbz==null || $oe_kurzbz=='')
		{
			$this->errormsg = 'keine oe_kurzbz uebergeben';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz);

		if($orgform_kurzbz != '')
		{
			$qry .= " AND orgform_kurzbz=".$this->db_add_param($orgform_kurzbz);
		}

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new bankverbindung();

				$obj->bankverbindung_id = $row->bankverbindung_id;
				$obj->person_id = $row->person_id;
				$obj->name = $row->name;
				$obj->anschrift = $row->anschrift;
				$obj->bic = $row->bic;
				$obj->blz = $row->blz;
				$obj->iban = $row->iban;
				$obj->kontonr = $row->kontonr;
				$obj->typ = $row->typ;
				$obj->verrechnung = $this->db_parse_bool($row->verrechnung);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;

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
	 * Laedt die Bankverbindung einer Person
	 * @param  $person_id
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_pers_verrechnung($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." ORDER BY verrechnung DESC NULLS LAST LIMIT 1";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->bankverbindung_id = $row->bankverbindung_id;
				$this->person_id = $row->person_id;
				$this->name = $row->name;
				$this->anschrift = $row->anschrift;
				$this->bic = $row->bic;
				$this->blz = $row->blz;
				$this->iban = $row->iban;
				$this->kontonr = $row->kontonr;
				$this->typ = $row->typ;
				$this->verrechnung = $this->db_parse_bool($row->verrechnung);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->orgform_kurzbz = $row->orgform_kurzbz;
	
				return true;
			}
			else
			{
				$this->errormsg = 'Keine Bankverbindung gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

}
?>
