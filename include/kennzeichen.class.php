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
 * Authors: Alexei Karpenko <karpenko@technikum-wien.at>,
 */
/**
 * Klasse kennzeichen
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class kennzeichen extends basis_db
{
	public $new;       // boolean
	public $result = array(); // adresse Objekt

	//Tabellenspalten
	public $kennzeichen_id;	// integer
	public $person_id;	// integer

	public $kennzeichentyp_kurzbz;	// string
	public $inhalt;	// string
	public $aktiv;	// boolean
	public $insertamum;	// timestamp
	public $insertvon;	// string
	public $updateamum;	// timestamp
	public $updatevon;	// string

	/**
	 * Konstruktor
	 * @param $kennzeichen_id ID des Kennzeichens das geladen werden soll (Default=null)
	 */
	public function __construct($kennzeichen_id=null)
	{
		parent::__construct();
		$this->new = true;

		if(!is_null($kennzeichen_id))
			$this->load($kennzeichen_id);
	}

	/**
	 * Laedt ein Kennzeichen mit der ID $kennzeichen_id
	 * @param  $kennzeichen_id ID des zu ladenden Kennzeichens
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($kennzeichen_id)
	{
		if (!is_numeric($kennzeichen_id))
		{
			$this->errormsg = 'Kennzeichen Id ist ungueltig';
			return false;
		}

		$qry = "SELECT
					*
				FROM
					public.tbl_kennzeichen
				WHERE
					kennzeichen_id = " . $this->db_add_param($kennzeichen_id, FHC_INTEGER) . ";";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->kennzeichen_id = $row->kennzeichen_id;
				$this->person_id = $row->person_id;
				$this->kennzeichentyp_kurzbz = $row->kennzeichentyp_kurzbz;
				$this->inhalt = $row->inhalt;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
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

	/**
	 * Prueft die Kennzeichen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		//Gesamtlaenge pruefen
		if(mb_strlen($this->kennzeichentyp_kurzbz)>32)
		{
			$this->errormsg = 'Kennzeichentyp darf nicht länger als 32 Zeichen sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $kennzeichen_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		if(!is_null($new))
			$this->new = $new;

		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_kennzeichen (person_id, kennzeichentyp_kurzbz, inhalt, aktiv, insertamum, insertvon) VALUES('.
				$this->db_add_param($this->person_id, FHC_INTEGER).', '.
				$this->db_add_param($this->kennzeichentyp_kurzbz).', '.
				$this->db_add_param($this->inhalt).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', now(), '.
				$this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob kennzeichen_id eine gueltige Zahl ist
			if(!is_numeric($this->kennzeichen_id))
			{
				$this->errormsg = 'kennzeichen_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE public.tbl_kennzeichen SET '.
				'person_id='.$this->db_add_param($this->person_id,FHC_INTEGER).', '.
				'kennzeichentyp_kurzbz='.$this->db_add_param($this->kennzeichentyp_kurzbz).', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE kennzeichen_id='.$this->db_add_param($this->kennzeichen_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			//Sequence auslesen um die eingefuegte ID zu ermitteln
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_kennzeichen_id_seq') as id;";

				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kennzeichen_id = $row->id;
						$this->db_query('COMMIT;');
						return true;
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen er Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
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
	 * Laedt Kennzeichen einer Person
	 * @param person_id
	 * @param kennzeichentyp_kurzbz_arr filtern nach Kennzeichentyp
	 * @return boolean
	 */
	public function load_pers($person_id, $kennzeichentyp_kurzbz_arr)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		if(!is_array($kennzeichentyp_kurzbz_arr))
		{
			$this->errormsg = 'Kennzeichen sind ungueltig';
			return false;
		}

		$qry = "
			SELECT
				kz.kennzeichen_id, kz.person_id, kz.kennzeichentyp_kurzbz, inhalt, aktiv, updateamum, updatevon, insertamum, insertvon
			FROM
				public.tbl_kennzeichen kz
			WHERE
				person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
				AND aktiv = TRUE
				AND kennzeichentyp_kurzbz IN (".$this->implode4SQL($kennzeichentyp_kurzbz_arr).")
			ORDER BY
				kz.kennzeichentyp_kurzbz, kz.kennzeichen_id;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new kennzeichen();

				$obj->kennzeichen_id = $row->kennzeichen_id;
				$obj->person_id = $row->person_id;
				$obj->kennzeichentyp_kurzbz = $row->kennzeichentyp_kurzbz;
				$obj->inhalt = $row->inhalt;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;

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
