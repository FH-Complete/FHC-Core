<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <stefan.puraner@technikum-wien.at>
 */
/**
 * Klasse Reihungstest
 * @create 10-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class foerdervertrag extends basis_db
{
	public $new;			//  boolean
	public $result = array();

	//Tabellenspalten
	public $foerdervertag_id;//  integer
	public $studiengang_kz;	//  integer
	public $foerdergeber;		//  string
	public $foerdersatz;		//  integer
	public $foerdergruppe;		//  string
	public $gueltigvon;		//  string
	public $gueltigbis;		//  string
	public $erlaeuterungen;		//  string
	public $insertamum;		//  timestamp
	public $insertvon;		//  bigint
	public $updateamum;		//  timestamp
	public $updatevon;		//  bigint

	/**
	 * Konstruktor
	 * @param $reihungstest_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($foerdervertag_id=null)
	{
		parent::__construct();

		if(!is_null($foerdervertag_id))
			$this->load($foerdervertag_id);
	}

	/**
	 * Laedt den Fördervertrag mit der ID $foerdervertag_id
	 * @param  $foerdervertag_id ID des zu ladenden Fördervertrags
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($foerdervertag_id)
	{
		if(!is_numeric($foerdervertag_id))
		{
			$this->errormsg = 'Foerdervertag_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_foerdervertrag WHERE foerdervertag_id=".$this->db_add_param($foerdervertag_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->foerdervertag_id = $row->foerdervertag_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->foerdergeber = $row->foerdergeber;
				$this->foerdersatz = $row->foerdersatz;
				$this->foerdergruppe = $row->foerdergruppe;
				$this->gueltigvon = $row->gueltigvon;
				$this->gueltigbis = $row->gueltigbis;
				$this->erlaeuterungen = $row->erlaeuterungen;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				return true;
			}
			else
			{
				$this->errormsg = 'Foerdervertrag existiert nicht';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Liefert alle Förderverträge
	 */
	public function getAll($stg_kz=null)
	{
		$qry = "SELECT * FROM public.tbl_foerdervertrag ";
		if($stg_kz!=null)
			$qry.=" WHERE studiengang_kz=".$this->db_add_param($stg_kz);
		$qry.=";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new foerdervertrag();

				$obj->foerdervertrag_id = $row->foerdervertrag_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->foerdergeber = $row->foerdergeber;
				$obj->foerdersatz = $row->foerdersatz;
				$obj->foerdergruppe = $row->foerdergruppe;
				$obj->gueltigvon = $row->gueltigvon;
				$obj->gueltigbis = $row->gueltigbis;
				$obj->erlaeuterungen = $row->erlaeuterungen;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Förderverträge.';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg='studiengang_kz enthaelt ungueltige Zeichen';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $reihungstest_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO public.tbl_foerdervertrag (studiengang_kz, foerdergeber, foerdersatz, foerdergruppe, gueltigvon, gueltigbis, erlaeuterungen,
				 insertamum, insertvon) VALUES('.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->foerdergeber).', '.
			     $this->db_add_param($this->foerdersatz).', '.
			     $this->db_add_param($this->foerdergruppe).', '.
			     $this->db_add_param($this->gueltigvon).', '.
			     $this->db_add_param($this->gueltigbis).', '.
			     $this->db_add_param($this->erlaeuterungen).', now(),'.
			     $this->db_add_param($this->insertvon).');';
		}
		else
		{
//			$qry='UPDATE public.tbl_reihungstest SET '.
//				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
//				'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).', '.
//				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
//				'datum='.$this->db_add_param($this->datum).', '.
//				'uhrzeit='.$this->db_add_param($this->uhrzeit).', '.
//		     	'updateamum= now(), '.
//		     	'updatevon='.$this->db_add_param($this->updatevon).', '.
//		     	'max_teilnehmer='.$this->db_add_param($this->max_teilnehmer).', '.
//				'oeffentlich='.$this->db_add_param($this->oeffentlich, FHC_BOOLEAN).', '.
//				'freigeschaltet='.$this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).', '.
//				'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).' '.
//				'WHERE reihungstest_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.seq_foerdervertrag_foerdervertrag_id') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->foerdervertrag_id = $row->id;
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
}
