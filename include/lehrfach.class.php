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
require_once(dirname(__FILE__).'/basis_db.class.php');

class lehrfach extends basis_db
{
	public $new;      // boolean
	public $lehrfaecher = array(); // lehrfach Objekt

	//Tabellenspalten
	public $lehrfach_id;		// integer
	public $studiengang_kz;		// integer
	public $fachbereich_kurzbz;	// integer
	public $kurzbz;				// varchar(12)
	public $bezeichnung;		// varchar(255)
	public $farbe;				// char(6)
	public $aktiv;				// boolean
	public $semester;			// smallint
	public $sprache;			// varchar(16)
	public $ext_id;

	/**
	 * Konstruktor - Laedt optional ein Lehrfach
	 * @param $lehrfach_nr Lehrfach das geladen werden soll (default=null)
	 */
	public function __construct($lehrfach_id=null)
	{
		parent::__construct();

		if(!is_null($lehrfach_id))
			$this->load($lehrfach_id);
	}

	/**
	 * Laedt Lehrfach mit der uebergebenen ID
	 * @param $lehrfach_id ID des LF das geladen werden soll
	 */
	public function load($lehrfach_id)
	{
		//lehrfach_id auf Gueltigkeit pruefen
		if(!is_numeric($lehrfach_id) && $lehrfach_id!='')
		{
			$this->errormsg = 'Die lehrfach_nr muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrfach WHERE lehrfach_id=".$this->db_add_param($lehrfach_id, FHC_INTEGER).';';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Lesen des Lehrfaches';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrfach_id = $row->lehrfach_id;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->kurzbz = $row->kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->farbe = $row->farbe;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->semester = $row->semester;
			$this->sprache = $row->sprache;
			$this->ext_id = $row->ext_id;
		}
		else
		{
			$this->errormsg = 'Es ist kein Lehrfach mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(mb_strlen($this->fachbereich_kurzbz)>16)
		{
			$this->errormsg = 'Fachbereich_kurzbz darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kurzbz)>12)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 12 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>255)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 255 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->farbe)>6)
		{
			$this->errormsg = 'Farbe darf nicht laenger als 6 Zeichen sein';
			return false;
		}
		if(!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester muss eine Zahl sein';
			return false;
		}
		if(mb_strlen($this->sprache)>16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		return true;
	}

	/**
	 * Speichert das Lehrfach in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz mit $lehrfach_nr upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			$qry = 'BEGIN;INSERT INTO lehre.tbl_lehrfach (studiengang_kz, fachbereich_kurzbz, kurzbz,
			                                  bezeichnung, farbe, aktiv, semester, sprache)
			        VALUES('.
					$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
					$this->db_add_param($this->fachbereich_kurzbz).','.
					$this->db_add_param($this->kurzbz).','.
					$this->db_add_param($this->bezeichnung).','.
					$this->db_add_param($this->farbe).','.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
					$this->db_add_param($this->semester, FHC_INTEGER).','.
					$this->db_add_param($this->sprache).');';
		}
		else
		{
			//lehrfach_nr auf Gueltigkeit pruefen
			if(!is_numeric($this->lehrfach_id))
			{
				$this->errormsg = 'Lehrfach_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_lehrfach SET'.
			       ' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
			       ' fachbereich_kurzbz='.$this->db_add_param($this->fachbereich_kurzbz).','.
			       ' kurzbz='.$this->db_add_param($this->kurzbz).','.
			       ' bezeichnung='.$this->db_add_param($this->bezeichnung).','.
			       ' farbe='.$this->db_add_param($this->farbe).','.
			       ' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
			       ' semester='.$this->db_add_param($this->semester, FHC_INTEGER).','.
			       ' sprache='.$this->db_add_param($this->sprache).
			       " WHERE lehrfach_id=".$this->db_add_param($this->lehrfach_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				if($this->lehrfach_id=='')
				{
					$qry = "SELECT currval('lehre.tbl_lehrfach_lehrfach_id_seq') as id;";
					if($this->db_query($qry))
					{
						if($row = $this->db_fetch_object())
						{
							$this->lehrfach_id = $row->id;
						}
						else
						{
							$this->errormsg = 'Fehler beim Auslesen der Sequence';
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
				$this->db_query('COMMIT;');
			}
			//Log schreiben
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Lehrfaches:'.$qry;
			return false;
		}
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param $stg Studiengangs_kz
	 * @param $sem Semester
	 * @param $order Sortierkriterium
	 * @param $fachb fachbereich_kurzbz
	 * @return array mit Lehrfaechern oder false=fehler
	 */
	public function getTab($stg=null,$sem=null, $order='lehrfach_id', $fachb=null)
	{
		if($stg!=null && !is_numeric($stg))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if($sem!=null && !is_numeric($sem))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		$sql_query = "SELECT * FROM lehre.tbl_lehrfach";

		if($stg!=null || $sem!=null || $fachb!=null)
		   $sql_query .= " WHERE true";

		if($stg!=null)
		   $sql_query .= " AND studiengang_kz=".$this->db_add_param($stg, FHC_INTEGER);

		if($sem!=null)
			$sql_query .= " AND semester=".$this->db_add_param($sem, FHC_INTEGER);

		if($fachb!=null)
			$sql_query .= " AND fachbereich_kurzbz=".$this->db_add_param($fachb);

		$sql_query .= " ORDER BY $order;";

		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$l = new lehrfach();
				$l->lehrfach_id = $row->lehrfach_id;
				$l->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$l->kurzbz = $row->kurzbz;
				$l->bezeichnung = $row->bezeichnung;
				$l->farbe = $row->farbe;
				$l->aktiv = $this->db_parse_bool($row->aktiv);
				$l->studiengang_kz = $row->studiengang_kz;
				$l->semester = $row->semester;
				$l->sprache = $row->sprache;
				$l->updateamum = $row->updateamum;
				$l->updatevon = $row->updatevon;
				$l->insertamum = $row->insertamum;
				$l->insertvon = $row->insertvon;
				$l->ext_id = $row->ext_id;
				$this->lehrfaecher[]=$l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}
}
?>
