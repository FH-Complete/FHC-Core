<?php
/* Copyright (C) 2007 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>
 *          Manfred Kindl		< manfred.kindl@technikum-wien.at >
 */
/**
 * Klasse Reihungstest
 * @create 10-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class reihungstest extends basis_db
{
	public $new;			//  boolean
	public $done=false;		//  boolean
	public $result = array();

	//Tabellenspalten
	public $reihungstest_id;//  integer
	public $studiengang_kz;	//  integer
	public $ort_kurzbz;		//  string
	public $anmerkung;		//  string
	public $datum;			//  date
	public $uhrzeit;		//  time without time zone
	public $ext_id;			//  integer
	public $insertamum;		//  timestamp
	public $insertvon;		//  bigint
	public $updateamum;		//  timestamp
	public $updatevon;		//  bigint
	public $freigeschaltet=false;	//  boolean
	public $oeffentlich=false;	//  boolean
	public $max_teilnehmer;	//  integer
	public $studiensemester_kurzbz; //string
	public $stufe; //smallint
	public $anmeldefrist; //date

	/**
	 * Konstruktor
	 * @param $reihungstest_id ID der Adresse die geladen werden soll (Default=null)
	 */
	public function __construct($reihungstest_id=null)
	{
		parent::__construct();

		if(!is_null($reihungstest_id))
			$this->load($reihungstest_id);
	}

	/**
	 * Laedt den Reihungstest mit der ID $reihungstest_id
	 * @param  $reihungstest_id ID des zu ladenden Reihungstests
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($reihungstest_id)
	{
		if(!is_numeric($reihungstest_id))
		{
			$this->errormsg = 'Reihungstest_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_reihungstest WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->reihungstest_id = $row->reihungstest_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->anmerkung = $row->anmerkung;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->ext_id = $row->ext_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->max_teilnehmer = $row->max_teilnehmer;
				$this->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$this->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$this->studiensemester_kurzbz =$row->studiensemester_kurzbz;
				$this->stufe = $row->stufe;
				$this->anmeldefrist = $row->anmeldefrist;
				return true;
			}
			else
			{
				$this->errormsg = 'Reihungstest existiert nicht';
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
	 * Liefert alle Reihungstests
	 * wenn ein Datum uebergeben wird, dann werden alle Reihungstests ab diesem
	 * Datum zurueckgeliefert
	 */
	public function getAll($datum=null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest ";
		if($datum!=null)
			$qry.=" WHERE datum>=".$this->db_add_param($datum);
		$qry.=" ORDER BY datum DESC, uhrzeit";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz =$row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
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
		//Gesamtlaenge pruefen
		if(mb_strlen($this->ort_kurzbz)>32)
		{
			$this->errormsg = 'Ort_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 64 Zeichen sein';
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

			$qry='BEGIN; INSERT INTO public.tbl_reihungstest (studiengang_kz, ort_kurzbz, anmerkung, datum, uhrzeit,
				 insertamum, insertvon, updateamum, updatevon, max_teilnehmer, oeffentlich, freigeschaltet, studiensemester_kurzbz, stufe, anmeldefrist) VALUES('.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->ort_kurzbz).', '.
			     $this->db_add_param($this->anmerkung).', '.
			     $this->db_add_param($this->datum).', '.
			     $this->db_add_param($this->uhrzeit).', now(), '.
			     $this->db_add_param($this->insertvon).', now(), '.
			     $this->db_add_param($this->updatevon).','.
			     $this->db_add_param($this->max_teilnehmer).','.
			     $this->db_add_param($this->oeffentlich, FHC_BOOLEAN).','.
			     $this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).','.
			     $this->db_add_param($this->studiensemester_kurzbz).','.
			     $this->db_add_param($this->stufe, FHC_INTEGER).','.
			     $this->db_add_param($this->anmeldefrist).');';
		}
		else
		{
			$qry='UPDATE public.tbl_reihungstest SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				'ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'datum='.$this->db_add_param($this->datum).', '.
				'uhrzeit='.$this->db_add_param($this->uhrzeit).', '.
		     	'updateamum= now(), '.
		     	'updatevon='.$this->db_add_param($this->updatevon).', '.
		     	'max_teilnehmer='.$this->db_add_param($this->max_teilnehmer).', '.
				'oeffentlich='.$this->db_add_param($this->oeffentlich, FHC_BOOLEAN).', '.
				'freigeschaltet='.$this->db_add_param($this->freigeschaltet, FHC_BOOLEAN).', '.
				'studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).', '.
				'stufe='.$this->db_add_param($this->stufe, FHC_INTEGER).', '.
				'anmeldefrist='.$this->db_add_param($this->anmeldefrist).' '.
				'WHERE reihungstest_id='.$this->db_add_param($this->reihungstest_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				$qry = "SELECT currval('public.tbl_reihungstest_reihungstest_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->reihungstest_id = $row->id;
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
	 * Liefert die Reihungstests eines Studienganges
	 *
	 * @param $studiengang_kz
	 * @param $order (optional)
	 * @return true wenn ok, sonst false
	 */
	public function getReihungstest($studiengang_kz,$order=null,$studiensemester_kurzbz=null)
	{
		$qry = "SELECT * FROM public.tbl_reihungstest WHERE 1=1 ";

		if (is_numeric($studiengang_kz) && $studiengang_kz!='')
			$qry .=" AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER, false);
		if ($studiensemester_kurzbz!=null)
			$qry .=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz, FHC_STRING, false);

		if ($order!=null)
			$qry .=" ORDER BY ".$order;


		$qry.= ";";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	/**
	 * Liefert die Reihungstests der Zukunft und einer bestimmten ID
	 * Und sortiert diese so, dass die des uebergebenen Studienganges zuerst geliefert werden
	 * @param $include_id
	 * @param $studiengang_kz
	 * @return true wenn ok, sonst false
	 */
	public function getZukuenftige($include_id, $studiengang_kz)
	{
		$qry = "
		SELECT *, (SELECT count(*) FROM public.tbl_prestudent WHERE reihungstest_id=a.reihungstest_id) as angemeldete_teilnehmer
		FROM (
			SELECT *, '1' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE datum>=now()-'1 days'::interval AND studiengang_kz=".$this->db_add_param($studiengang_kz)."
			UNION
			SELECT *, '2' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE datum>=now()-'1 days'::interval AND studiengang_kz!=".$this->db_add_param($studiengang_kz)."
			UNION
			SELECT *, '0' as sortierung,(SELECT upper(typ || kurzbz) FROM public.tbl_studiengang WHERE studiengang_kz=tbl_reihungstest.studiengang_kz) as stg FROM public.tbl_reihungstest WHERE reihungstest_id=".$this->db_add_param($include_id)."
			ORDER BY sortierung, stg, datum
			) a";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz =$row->studiensemester_kurzbz;
				$obj->angemeldete_teilnehmer = $row->angemeldete_teilnehmer;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Reihungstests';
			return false;
		}
	}

	public function getStgZukuenftige($stg)
	{
		$qry = "SELECT * "
				. "FROM public.tbl_reihungstest "
				. "WHERE studiengang_kz = ".$this->db_add_param($stg, FHC_INTEGER)." "
				. "AND datum>=now()-'1 days'::interval "
				. "AND oeffentlich;";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new reihungstest();

				$obj->reihungstest_id = $row->reihungstest_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->anmerkung = $row->anmerkung;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->max_teilnehmer = $row->max_teilnehmer;
				$obj->oeffentlich = $this->db_parse_bool($row->oeffentlich);
				$obj->freigeschaltet = $this->db_parse_bool($row->freigeschaltet);
				$obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->stufe = $row->stufe;
				$obj->anmeldefrist = $row->anmeldefrist;

				$this->result[] = $obj;
			}
			return true;
		}
		else
			return false;
	}

	public function getTeilnehmerAnzahl($reihungstest_id) {

		$qry = 'SELECT count(*) AS anzahl '
				. 'FROM public.tbl_prestudent '
				. 'WHERE reihungstest_id = ' . $reihungstest_id;

		$result = $this->db_query($qry);

		$obj = $this->db_fetch_object($result);

		return $obj->anzahl;
	}

	public function delete($reihungstest_id)
	{
	    $qry = "DELETE from public.tbl_reihungstest WHERE reihungstest_id=".$this->db_add_param($reihungstest_id);

	    if(!$this->db_query($qry))
	    {
		$this->errormsg = 'Fehler beim Löschen der Daten';
		return false;
	    }

	    return true;
	}

	/**
	 * Laedt den Reihungstest-Person-Datensatz mit der ID $rt_person_id
	 * @param integer $rt_person_id ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadReihungstestPerson($rt_person_id)
	{
		if(!is_numeric($rt_person_id))
		{
			$this->errormsg = 'rt_person_id ist ungueltig';
			return false;
		}
	
		$qry = "SELECT * FROM public.tbl_rt_person WHERE rt_person_id=".$this->db_add_param($rt_person_id, FHC_INTEGER, false);
	
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->rt_person_id = $row->rt_person_id;
				$this->rt_id = $row->rt_id;
				$this->person_id = $row->person_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->anmeldedatum = $row->anmeldedatum;
				$this->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->punkte = $row->punkte;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des rt_person_id Datensatzes';
			return false;
		}
	}
	
	public function getReihungstestPerson($person_id)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.person_id=".$this->db_add_param($person_id);
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->rt_person_id = $row->rt_person_id;
				$obj->rt_id = $row->rt_id;
				$obj->person_id = $row->person_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->anmeldedatum = $row->anmeldedatum;
				$obj->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->punkte = $row->punkte;

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

	public function getPersonReihungstest($person_id, $rt_id)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.person_id=".$this->db_add_param($person_id)."
					AND rt_id=".$this->db_add_param($rt_id);
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->rt_person_id = $row->rt_person_id;
				$this->rt_id = $row->rt_id;
				$this->person_id = $row->person_id;
				$this->studienplan_id = $row->studienplan_id;
				$this->anmeldedatum = $row->anmeldedatum;
				$this->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->punkte = $row->punkte;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag nicht gefunden';
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
	 * Liefert die Personen, die einem Ort des Reihungstests zugeteilt sind
	 * @param integer $rt_id ID des Reihungstests
	 * @param string $ort_kurzbz Ort des Reihungstests mit der ID $rt_id
	 * @return true wenn ok, sonst false
	 */
	public function getPersonReihungstestOrt($rt_id, $ort_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_person
				WHERE
					tbl_rt_person.rt_id=".$this->db_add_param($rt_id)."
					AND tbl_rt_person.ort_kurzbz=".$this->db_add_param($ort_kurzbz);
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->rt_person_id = $row->rt_person_id;
				$obj->rt_id = $row->rt_id;
				$obj->person_id = $row->person_id;
				$obj->studienplan_id = $row->studienplan_id;
				$obj->anmeldedatum = $row->anmeldedatum;
				$obj->teilgenommen = $this->db_parse_bool($row->teilgenommen);
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->punkte = $row->punkte;

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

	public function savePersonReihungstest()
	{
		if($this->new)
		{
			$qry = "INSERT INTO public.tbl_rt_person(person_id, rt_id, studienplan_id, anmeldedatum, teilgenommen, ort_kurzbz, punkte) VALUES(".
				$this->db_add_param($this->person_id, FHC_INTEGER).','.
				$this->db_add_param($this->rt_id, FHC_INTEGER).','.
				$this->db_add_param($this->studienplan_id, FHC_INTEGER).','.
				$this->db_add_param($this->anmeldedatum).','.
				$this->db_add_param($this->teilgenommen, FHC_BOOLEAN).','.
				$this->db_add_param($this->ort_kurzbz).','.
				$this->db_add_param($this->punkte).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_person SET ".
			 ' rt_id = '.$this->db_add_param($this->rt_id).','.
			 ' studienplan_id = '.$this->db_add_param($this->studienplan_id).','.
			 ' anmeldedatum='.$this->db_add_param($this->anmeldedatum).','.
			 ' teilgenommen='.$this->db_add_param($this->teilgenommen, FHC_BOOLEAN).','.
			 ' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
			 ' punkte='.$this->db_add_param($this->punkte).' '.
			 ' WHERE rt_person_id='.$this->db_add_param($this->rt_person_id, FHC_INTEGER);
		 }

		 if($this->db_query($qry))
		 {
			 return true;
		 }
		 else
		 {
			 $this->errormsg = 'Fehler beim Speichern der Daten';
			 return false;
		 }
	}

	/**
	 * Loescht einen Person Reihungstest Eintrag
	 */
	public function deletePersonReihungstest($person_id, $rt_id)
	{
		$qry = "DELETE FROM public.tbl_rt_person
		 	WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)."
			AND rt_id=".$this->db_add_param($rt_id, FHC_INTEGER);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg='Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Liefert die Orte, die einem Reihungstest zugeordnet sind
	 * @param integer $reihungstest_id ID des Reihungstests, dessen Ort zurueckgegeben werden sollen
	 * @return true wenn ok, sonst false
	 */
	public function getOrteReihungstest($reihungstest_id)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_rt_ort
				WHERE
					tbl_rt_ort.rt_id=".$this->db_add_param($reihungstest_id)."
				ORDER BY
					ort_kurzbz";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
	
				$obj->rt_id = $row->rt_id;
				$obj->ort_kurzbz = $row->ort_kurzbz;
				$obj->uid = $row->uid;
	
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
	 * Speichert eine Raumzuteilung zu einem Reihungstesttermin
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID $reihungstest_id und $ort_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveOrtReihungstest()
	{
		if($this->new)
		{
			$qry = "INSERT INTO public.tbl_rt_ort(rt_id, ort_kurzbz, uid) VALUES(".
					$this->db_add_param($this->rt_id, FHC_INTEGER).','.
					$this->db_add_param($this->ort_kurzbz).','.
					$this->db_add_param($this->uid).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_ort SET ".
				 ' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz).','.
				 ' uid='.$this->db_add_param($this->uid).' '.
				 ' WHERE rt_id='.$this->db_add_param($this->rt_id, FHC_INTEGER).' AND '.
				 ' ort_kurzbz='.$this->db_add_param($this->ort_kurzbz);
		}
	
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Loescht einen Ort zu einem Reihungstest Eintrag
	 */
	public function deleteOrtReihungstest($reihungstest_id, $ort_kurzbz)
	{
		$qry = "DELETE FROM public.tbl_rt_ort
		 	WHERE rt_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER)."
			AND ort_kurzbz=".$this->db_add_param($ort_kurzbz);
	
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg='Fehler beim Löschen der Daten';
			return false;
		}
	}
	
	/**
	 * Speichert eine Studienplanzuordnung zu einem Reihungstesttermin
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID $reihungstest_id und $studienplan_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStudienplanReihungstest()
	{
		if($this->new)
		{
			$qry = "INSERT INTO public.tbl_rt_studienplan(reihungstest_id, studienplan_id) VALUES(".
					$this->db_add_param($this->rt_id, FHC_INTEGER).','.
					$this->db_add_param($this->studienplan_id).');';
		}
		else
		{
			$qry = "UPDATE public.tbl_rt_studienplan SET ".
					' studienplan_id='.$this->db_add_param($this->studienplan_id).' '.
					' WHERE reihungstest_id='.$this->db_add_param($this->rt_id, FHC_INTEGER).' AND '.
					' studienplan_id='.$this->db_add_param($this->studienplan_id);
		}
	
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Studienplan IDs, die einem Reihungstest zugeordnet sind
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getStudienplaeneReihungstest($reihungstest_id)
	{
		$qry = "SELECT
					studienplan_id
				FROM
					public.tbl_rt_studienplan
				WHERE
					tbl_rt_studienplan.reihungstest_id=".$this->db_add_param($reihungstest_id)."
				ORDER BY
					studienplan_id";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();
	
				$obj->studienplan_id = $row->studienplan_id;
	
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
	 * Loescht eine Studienplanzuordnung zu einem Reihungstest Eintrag
	 * @param $reihungstest_id integer ID des Reihungstests
	 * @param $studienplan_id integer ID des Studienplans
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deleteStudienplanReihungstest($reihungstest_id, $studienplan_id)
	{
		$qry = "DELETE FROM public.tbl_rt_studienplan
		 	WHERE reihungstest_id=".$this->db_add_param($reihungstest_id, FHC_INTEGER)."
			AND studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);
	
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->erromsg='Fehler beim Löschen der Studienplanzuordnung';
			return false;
		}
	}
}
