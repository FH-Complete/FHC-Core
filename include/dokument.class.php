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
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class dokument extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $dokument_kurzbz;
	public $bezeichnung;
	public $studiengang_kz;
	public $pflicht;
	public $nachreichbar;
	public $bezeichnung_mehrsprachig;
	public $dokumentbeschreibung_mehrsprachig;
	public $ausstellungsdetails = false;
	public $stufe;

	public $prestudent_id;
	public $mitarbeiter_uid;
	public $datum;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $onlinebewerbung;

	/**
	 * Konstruktor - Laedt optional ein Dokument
	 * @param $dokument_kurzbz
	 * @param $prestudent_id
	 */
	public function __construct($dokument_kurzbz=null, $prestudent_id=null)
	{
		parent::__construct();

		if(!is_null($dokument_kurzbz) && !is_null($prestudent_id))
			$this->load($dokument_kurzbz, $prestudent_id);
	}

	/**
	 * Laedt eine Dokument-Prestudent Zuordnung
	 * @param dokument_kurzbz
	 *		prestudent_id
	 */
	public function load($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_dokumentprestudent
				WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)." AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->prestudent_id = $row->prestudent_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->datum = $row->datum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Es wurde kein Datensatz gefunden';
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
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'Dokument_kurzbz muss angegeben werden';
			return false;
		}

		if($this->prestudent_id=='')
		{
			$this->errormsg = 'Prestudent_id muss angegeben werden';
			return false;
		}

		if(!is_numeric($this->prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		if($this->mitarbeiter_uid=='')
		{
			$this->errormsg = 'Mitarbeiter_uid muss angegeben werden';
			return false;
		}
		return true;
	}

	/**
	 * Speichert ein Beispiel in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_dokumentprestudent(dokument_kurzbz, prestudent_id, mitarbeiter_uid, datum, updateamum,
					updatevon, insertamum, insertvon) VALUES('.
					$this->db_add_param($this->dokument_kurzbz).','.
					$this->db_add_param($this->prestudent_id, FHC_INTEGER).','.
					$this->db_add_param($this->mitarbeiter_uid).','.
					$this->db_add_param($this->datum).','.
					$this->db_add_param($this->updateamum).','.
					$this->db_add_param($this->updatevon).','.
					$this->db_add_param($this->insertamum).','.
					$this->db_add_param($this->insertvon).');';
		}
		else
		{
			//never used
			return false;
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuteilung';
			return false;
		}
	}

	/**
	 * Speichert das Dokument in der Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveDokument($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'Dokument_kurzbz muss angegeben werden';
			return false;
		}

		if($new)
		{
			//Prüfung, ob Eintrag bereits vorhanden
			$qry='SELECT dokument_kurzbz FROM public.tbl_dokument
				WHERE dokument_kurzbz='.$this->db_add_param($this->dokument_kurzbz);
			if($this->db_query($qry))
			{
				if($this->db_fetch_object())
				{
					$this->errormsg = 'Eintrag bereits vorhanden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
				return false;
			}
		}

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_dokument(dokument_kurzbz, ';
			foreach($this->bezeichnung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" bezeichnung_mehrsprachig[$idx],";
			}

			foreach($this->dokumentbeschreibung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" dokumentbeschreibung_mehrsprachig[$idx],";
			}
			$qry.='bezeichnung, ausstellungsdetails) VALUES('.
			$this->db_add_param($this->dokument_kurzbz).',';
			foreach($this->bezeichnung_mehrsprachig as $key=>$value)
				$qry.=$this->db_add_param($value).',';
			foreach($this->dokumentbeschreibung_mehrsprachig as $key=>$value)
				$qry.=$this->db_add_param($value).',';
			$qry.= $this->db_add_param($this->bezeichnung).',';
			$qry.= $this->db_add_param($this->ausstellungsdetails, FHC_BOOLEAN).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_dokument SET '.
					'bezeichnung = '.$this->db_add_param($this->bezeichnung).', '.
					'ausstellungsdetails = '.$this->db_add_param($this->ausstellungsdetails, FHC_BOOLEAN).',';
			foreach($this->bezeichnung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" bezeichnung_mehrsprachig[$idx]=".$this->db_add_param($value).",";
			}
			foreach($this->dokumentbeschreibung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" dokumentbeschreibung_mehrsprachig[$idx]=".$this->db_add_param($value).",";
			}
			$qry = mb_substr($qry,0,-1);
			$qry.=' WHERE dokument_kurzbz = '.$this->db_add_param($this->dokument_kurzbz);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuteilung';
			return false;
		}
	}

	/**
	 * Loescht eine Zuordnung
	 * @param dokument_kurzbz
	 *		prestudent_id
	 */
	public function delete($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM public.tbl_dokumentprestudent
				WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz)." AND prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}

	/**
	 * Loescht eine Zuordnung
	 * @param dokument_kurzbz
	 *		stg_kz
	 */
	public function deleteDokumentStg($dokument_kurzbz, $stg_kz)
	{
		if(!is_numeric($stg_kz))
		{
			$this->errormsg = 'stg_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM public.tbl_dokumentstudiengang
				WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz)." AND studiengang_kz=".$this->db_add_param($stg_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}

	/**
	 * Laedt alle Dokumente eines Prestudenten die
	 * er bereits abgegeben hat
	 * @param integer prestudent_id
	 * @param boolean archivdokumente Default true. Wenn false, werden Dokumente, die Archivierbar sind (tbl_vorlage.archivierbar zB Zeugnis, Bescheid, ...) nicht geliefert
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudentDokumente($prestudent_id, $archivdokumente = true)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT tbl_dokumentprestudent.* , tbl_dokument.*
				FROM public.tbl_dokumentprestudent
				JOIN public.tbl_dokument USING(dokument_kurzbz)
				LEFT JOIN public.tbl_vorlage ON (tbl_dokumentprestudent.dokument_kurzbz = tbl_vorlage.vorlage_kurzbz)
				WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER);

		if(!$archivdokumente)
		{
			$qry.="	AND (tbl_vorlage.archivierbar = FALSE OR tbl_vorlage.archivierbar IS NULL)";
		}
		$qry.="	ORDER BY tbl_dokumentprestudent.dokument_kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();

				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->prestudent_id = $row->prestudent_id;
				$dok->mitarbeiter_uid = $row->mitarbeiter_uid;
				$dok->datum = $row->datum;
				$dok->updateamum = $row->updateamum;
				$dok->updatevon = $row->updatevon;
				$dok->insertamum = $row->insertamum;
				$dok->insertvon = $row->insertvon;
				$dok->ext_id = $row->ext_id;

				$this->result[] = $dok;
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
	 * Laedt alle Dokumente fuer einen Stg die der
	 * Prestudent noch nicht abgegeben hat
	 * @param integer studiengang_kz
	 * @param integer prestudent_id
	 * @param boolean archivdokumente Default true. Wenn false, werden Dokumente, die archivierbar sind (tbl_vorlage.archivierbar zB Zeugnis, Bescheid, ...) nicht geliefert
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getFehlendeDokumente($studiengang_kz, $prestudent_id = null, $archivdokumente = true)
	{
		if(!is_null($prestudent_id) && !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}

		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}

		$qry = "SELECT tbl_dokument.* , tbl_dokumentstudiengang.*
				FROM public.tbl_dokument
				JOIN public.tbl_dokumentstudiengang USING(dokument_kurzbz)
				LEFT JOIN public.tbl_vorlage ON (tbl_dokument.dokument_kurzbz = tbl_vorlage.vorlage_kurzbz)
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($prestudent_id))
		{
			$qry.="	AND tbl_dokument.dokument_kurzbz NOT IN (
					SELECT dokument_kurzbz FROM public.tbl_dokumentprestudent WHERE prestudent_id=".$this->db_add_param($prestudent_id,FHC_INTEGER).")";
		}
		if(!$archivdokumente)
		{
			$qry.="	AND (tbl_vorlage.archivierbar = FALSE OR tbl_vorlage.archivierbar IS NULL)";
		}

		$qry.=" ORDER BY tbl_dokument.dokument_kurzbz;";
//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->pflicht = $this->db_parse_bool($row->pflicht);
				$dok->nachreichbar = $this->db_parse_bool($row->nachreichbar);
				$dok->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$dok->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);
				$dok->stufe = $row->stufe;
				$this->result[] = $dok;
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
	 * Liefert die Dokumente eines Studienganges
	 * @param $studiengang_kz
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getDokumente($studiengang_kz)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');
		$qry = "SELECT *,$bezeichnung_mehrsprachig, $dokumentbeschreibung_mehrsprachig FROM public.tbl_dokumentstudiengang JOIN public.tbl_dokument USING(dokument_kurzbz)
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)."
				ORDER BY dokument_kurzbz;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();

				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$dok->dokumentbeschreibung_mehrsprachig = $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
				$dok->pflicht = $this->db_parse_bool($row->pflicht);
				$dok->nachreichbar = $this->db_parse_bool($row->nachreichbar);
				$dok->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$dok->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);
				$dok->stufe = $row->stufe;

				$this->result[] = $dok;
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
	 * Liefert alle Dokumenttypen
	 * @param string $not_in Kommagetrennter String von dokument_kurzbz. Optional. Um bestimmte Dokumente (zB Zeugnis, welcher fix im Core vorhanden sein muss) auszuschließen.
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getAllDokumente($not_in='')
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');
		$qry = "SELECT dokument_kurzbz, bezeichnung, ausstellungsdetails, $bezeichnung_mehrsprachig, $dokumentbeschreibung_mehrsprachig FROM public.tbl_dokument ";

		if($not_in!='')
		{
			$qry .= " WHERE dokument_kurzbz NOT IN (".$this->implode4SQL(explode(',', $not_in)).")";
		}
		$qry .= " ORDER BY bezeichnung;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();

				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$dok->dokumentbeschreibung_mehrsprachig = $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
				$dok->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);

				$this->result[] = $dok;
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
	 * Laedt die Dokument Studiengang Zuordnung
	 *
	 * @param $dokument_kurzbz
	 * @param $studiengang_kz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadDokumentStudiengang($dokument_kurzbz, $studiengang_kz)
	{
		$sprache = new sprache();
		$qry="SELECT *,".$sprache->getSprachQuery('beschreibung_mehrsprachig')." FROM public.tbl_dokumentstudiengang
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz)."
					AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$this->pflicht = $this->db_parse_bool($row->pflicht);
				$this->nachreichbar = $this->db_parse_bool($row->nachreichbar);
				$this->beschreibung_mehrsprachig = $sprache->parseSprachResult('beschreibung_mehrsprachig',$row);
				$this->stufe = $row->stufe;
				return true;
			}
			else
			{
				$this->errormsg='Fehler beim Laden der Daten';
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
	 * Prueft ob die Zuordnung Dokument zu Studiengang bereits vorhanden ist
	 * @param $dokument_kurzbz
	 * @param $studiengang_kz
	 * @return true wenn vorhanden, false wenn nicht vorhanden
	 */
	public function existsDokumentStudiengang($dokument_kurzbz, $studiengang_kz)
	{
		$qry='SELECT
				dokument_kurzbz
			FROM
				public.tbl_dokumentstudiengang
			WHERE
				dokument_kurzbz='.$this->db_add_param($dokument_kurzbz).'
				AND studiengang_kz='.$this->db_add_param($studiengang_kz,FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Speichert die Zuordnung Dokument zu Studiengang
	 * @return true wenn ok false im Fehlerfall
	 */
	public function saveDokumentStudiengang()
	{
		if(!$this->existsDokumentStudiengang($this->dokument_kurzbz, $this->studiengang_kz))
		{
			$qry='INSERT INTO public.tbl_dokumentstudiengang (dokument_kurzbz, studiengang_kz,';
			foreach($this->beschreibung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" beschreibung_mehrsprachig[$idx],";
			}

			$qry.=' pflicht, nachreichbar, onlinebewerbung, stufe)
				VALUES ('.
					$this->db_add_param($this->dokument_kurzbz).','.
					$this->db_add_param($this->studiengang_kz,FHC_INTEGER).',';

			foreach($this->beschreibung_mehrsprachig as $key=>$value)
				$qry.=$this->db_add_param($value).',';

			$qry.=	$this->db_add_param($this->pflicht,FHC_BOOLEAN).','.
					$this->db_add_param($this->nachreichbar,FHC_BOOLEAN).','.
					$this->db_add_param($this->onlinebewerbung,FHC_BOOLEAN).','.
					$this->db_add_param($this->stufe,FHC_INTEGER).')';
		}
		else
		{
			$qry = 'UPDATE public.tbl_dokumentstudiengang SET
						onlinebewerbung='.$this->db_add_param($this->onlinebewerbung, FHC_BOOLEAN).',';

			foreach($this->beschreibung_mehrsprachig as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" beschreibung_mehrsprachig[$idx]=".$this->db_add_param($value).",";
			}
			$qry.='		pflicht='.$this->db_add_param($this->pflicht, FHC_BOOLEAN).',
						nachreichbar='.$this->db_add_param($this->nachreichbar, FHC_BOOLEAN).',
						stufe='.$this->db_add_param($this->stufe, FHC_INTEGER).'
					WHERE
						dokument_kurzbz='.$this->db_add_param($this->dokument_kurzbz).'
						AND studiengang_kz='.$this->db_add_param($this->studiengang_kz);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuordnung';
			return false;
		}
	}

	/**
	 * Laedt einen Dokumenttyp
	 * @param $dokument_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadDokumenttyp($dokument_kurzbz)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');
		$qry="SELECT *, ".$bezeichnung_mehrsprachig.",".$dokumentbeschreibung_mehrsprachig." FROM public.tbl_dokument
				WHERE dokument_kurzbz =".$this->db_add_param($dokument_kurzbz).";";
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$this->dokumentbeschreibung_mehrsprachig =  $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
				$this->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);
				return true;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert alle Dokumente die eine Person abzugeben hat.
	 * Ist notwendig, um bei einer Bewerbung mit mehreren Studiengängen zu wissen, was der Student im Gesamten abzugeben hat
	 * @param integer $person_id
	 * @param boolean $onlinebewerbung Default false. Wenn true, werden nur Dokumente zurueckgegeben, bei denen das Attribut "Onlinebewerbung" true ist
	 */
	public function getAllDokumenteForPerson($person_id, $onlinebewerbung= false)
	{
		$sprache = new sprache();
		$bezeichnung_mehrsprachig = $sprache->getSprachQuery('bezeichnung_mehrsprachig');
		$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');
		$beschreibung_mehrsprachig = $sprache->getSprachQuery('beschreibung_mehrsprachig');
		$qry = "SELECT distinct on (dokument_kurzbz) dokument_kurzbz, bezeichnung, pflicht, nachreichbar, ausstellungsdetails, stufe,
			$bezeichnung_mehrsprachig, $dokumentbeschreibung_mehrsprachig, $beschreibung_mehrsprachig
			FROM public.tbl_dokumentstudiengang
			JOIN public.tbl_prestudent using (studiengang_kz)
			JOIN public.tbl_dokument using (dokument_kurzbz)
			WHERE person_id =".$this->db_add_param($person_id, FHC_INTEGER);

		if($onlinebewerbung)
			$qry.= " AND onlinebewerbung is true";
		else
			$qry.=" ";
		$qry.=" ORDER BY dokument_kurzbz, pflicht desc";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$dok = new dokument();
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->pflicht= $this->db_parse_bool($row->pflicht);
				$dok->nachreichbar= $this->db_parse_bool($row->nachreichbar);
				$dok->bezeichnung_mehrsprachig = $sprache->parseSprachResult('bezeichnung_mehrsprachig', $row);
				$dok->dokumentbeschreibung_mehrsprachig = $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
				$dok->beschreibung_mehrsprachig = $sprache->parseSprachResult('beschreibung_mehrsprachig', $row);
				$dok->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);
				$dok->stufe = $row->stufe;

				$this->result[] = $dok;
			}
			return true;
		}
		else
		{
			$this->errormsg="Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Liefert die Beschreibungstexte des uebergebenen Dokuments und der uebergebenen Studiengaenge
	 * @param array $studiengangs_kz Array mit den Studiengangskennzahlen
	 * @param string $dokument_kurzbz Kurzbz des Dokuments dessen Beschreibungstexte geliefert werden sollen
	 */
	public function getBeschreibungenDokumente($studiengangs_kz, $dokument_kurzbz)
	{
		if(count($studiengangs_kz)==0)
			return true;
		$sprache = new sprache();
		$dokumentbeschreibung_mehrsprachig = $sprache->getSprachQuery('dokumentbeschreibung_mehrsprachig');
		$beschreibung_mehrsprachig = $sprache->getSprachQuery('beschreibung_mehrsprachig');

		$qry = "	SELECT DISTINCT dokument_kurzbz, studiengang_kz, ausstellungsdetails,
					$dokumentbeschreibung_mehrsprachig, $beschreibung_mehrsprachig
					FROM public.tbl_dokumentstudiengang
					JOIN public.tbl_dokument using (dokument_kurzbz)
					WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz, FHC_STRING)."
					AND (dokumentbeschreibung_mehrsprachig IS NOT NULL OR beschreibung_mehrsprachig IS NOT NULL)
					AND studiengang_kz IN (".implode(",", $studiengangs_kz).")
					ORDER BY studiengang_kz";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$dok = new dokument();
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->studiengang_kz = $row->studiengang_kz;
				$dok->dokumentbeschreibung_mehrsprachig = $sprache->parseSprachResult('dokumentbeschreibung_mehrsprachig', $row);
				$dok->beschreibung_mehrsprachig = $sprache->parseSprachResult('beschreibung_mehrsprachig', $row);
				$dok->ausstellungsdetails = $this->db_parse_bool($row->ausstellungsdetails);

				$this->result[] = $dok;
			}
			return true;
		}
		else
		{
			$this->errormsg="Fehler bei der Abfrage aufgetreten";
			return false;
		}

	}

	/**
	 * Loescht einen Dokumenttyp
	 * @param $dokument_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function deleteDokumenttyp($dokument_kurzbz)
	{
		$qry="DELETE FROM public.tbl_dokument WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Löschen fehlgeschlagen';
			return false;
		}
	}

	/**
	 * Prueft ob das Dokument bei einem der Prestudenten einer Person bereits akzeptiert wurde.
	 * Optional kann auch eine studiengang_kz uebergeben werden, ob speziell dort das Dokument akzeptiert wurde
	 * @param $dokument_kurzbz
	 * @param $person_id
	 * @param $studiengang_kz integer oder array aus mehreren studiengang_kz
	 * @return boolean true wenn akzeptiert, false wenn noch nicht akzeptiert
	 */
	function akzeptiert($dokument_kurzbz, $person_id, $studiengang_kz=null)
	{
		if(($studiengang_kz!='' && !is_numeric($studiengang_kz)) && !is_array($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		if(is_array($studiengang_kz))
			$studiengang_kz = $this->implode4SQL($studiengang_kz);

		$qry = "SELECT
					*
				FROM
					public.tbl_dokumentprestudent
					JOIN public.tbl_prestudent USING(prestudent_id)
				WHERE
					dokument_kurzbz=".$this->db_add_param($dokument_kurzbz)."
					AND tbl_prestudent.person_id=".$this->db_add_param($person_id);
		if ($studiengang_kz!='')
			$qry .= " AND studiengang_kz IN (".$studiengang_kz.")";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				return true;
			}
			return false;
		}
	}

	/**
	 * Liefert die Studiengänge bei denen das übergebene Dokument benötigt wird
	 * @param string $dokument_kurzbz Kurzbz des Dokuments
	 * @param integer $person_id Optional. Die Dokumente werden zusätzlich auf die Studiengänge eingeschränkt für die sich eine Person beworben hat.
	 * @return object Objekt mit den Studiengängen oder false.
	 */
	public function getStudiengaengeDokument($dokument_kurzbz, $person_id = null)
	{
		$qry = "	SELECT DISTINCT studiengang_kz,typ||kurzbz AS kuerzel, bezeichnung, english, stufe
					FROM public.tbl_dokumentstudiengang
					JOIN public.tbl_prestudent USING (studiengang_kz)
					JOIN public.tbl_prestudentstatus USING (prestudent_id)
					JOIN public.tbl_studiengang USING (studiengang_kz)
					WHERE dokument_kurzbz = ".$this->db_add_param($dokument_kurzbz)."
					AND person_id =".$this->db_add_param($person_id, FHC_INTEGER)."
					AND tbl_prestudentstatus.status_kurzbz = 'Interessent'
					AND get_rolle_prestudent (prestudent_id, NULL) NOT IN ('Abgewiesener','Abbrecher')

					ORDER BY kuerzel";

		if($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
			{
				while($row = $this->db_fetch_object($result))
				{
					$stg_obj = new basis_db();
					$stg_obj->kuerzel = $row->kuerzel;
					$stg_obj->bezeichnung = $row->bezeichnung;
					$stg_obj->studiengang_kz = $row->studiengang_kz;
					$stg_obj->english = $row->english;
					$stg_obj->stufe = $row->stufe;

					$this->result[] = $stg_obj;
				}
				return $stg_obj;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg="Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Akzeptiert ein bestimmtes Dokument
	 * @param char $dokument_kurzbz Bezeichner Dokument.
	 * @param int $person_id Personenkennzeichen.
	 * @return boolean true wenn akzeptiert bzw geprüft ohne Akzeptieren, false wenn Fehler
	 */
	public function akzeptiereDokument($dokument_kurzbz, $person_id)
	{
		$db = new basis_db();
		$arrayDoksZuAkzeptieren = array();

		//get Prestudent_ids
		$qry = "SELECT
					prestudent_id
				FROM
					tbl_prestudent ps, tbl_studiengang sg
				WHERE
					ps.studiengang_kz = sg.studiengang_kz
				AND sg.typ = 'm'
				AND person_id = ".$this->db_add_param($person_id)."
				AND not exists(
					SELECT *
					from tbl_dokumentprestudent dok
					where dok.prestudent_id = ps.prestudent_id
					and dokument_kurzbz = ".$this->db_add_param($dokument_kurzbz).")";

		//echo var_dump($qry);

		//gibt ein Array von zu akzeptierenden Dokumenten zurück
		if ($db->db_query($qry))
		{
			$num_rows = $db->db_num_rows();
			// Wenn kein ergebnis return 0 sonst ID
			if ($num_rows > 0)
			{
				while ($row = $db->db_fetch_object())
				{
					//echo var_dump($row->prestudent_id);
					$arrayDoksZuAkzeptieren[] = $row->prestudent_id;
				}
				//print_r($arrayDoksZuAkzeptieren);

				//für alle prestudent_ids das Dokument akzeptieren
				$qry = "INSERT INTO public.tbl_dokumentprestudent(dokument_kurzbz, prestudent_id) VALUES";

				foreach ($arrayDoksZuAkzeptieren as $prestudent_id)
				{
					$qry .= "(".$this->db_add_param($dokument_kurzbz). ",". $prestudent_id. ")";

					if (next($arrayDoksZuAkzeptieren) == true)
					{
						$qry .=  ",";
					}
				}
				$qry .=  ";";

				if ($this->db_query($qry))
				{
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Akzeptieren';
					return false;
				}
			}
			return true;
		}

		else
			return false;
	}

	/**
	 * entakzeptiert ein bestimmtes Dokument
	 * @param char $dokument_kurzbz Kurzbezeichnung des zu entakzeptierenden Dokuments.
	 * @param int $person_id Personenkennzeichen.
	 * @return boolean true wenn entakzeptiert bzw geprüft ohne Entakzeptieren, false wenn Fehler
	 */
	public function entakzeptiereDokument($dokument_kurzbz, $person_id)
	{
		$db = new basis_db();
		$arrayDoksZuEntakzeptieren = array();

		//get Prestudent_ids
		$qry = "SELECT
					prestudent_id
				from
					tbl_dokumentprestudent
				join
					tbl_prestudent using (prestudent_id)
				where
					person_id = ".$this->db_add_param($person_id)."
				and dokument_kurzbz = ".$this->db_add_param($dokument_kurzbz);

			//	echo var_dump($qry);

		//gibt ein Array von zu Entakzeptierenden Dokumenten zurück
		if ($db->db_query($qry))
		{
			$num_rows = $db->db_num_rows();
			// Wenn kein ergebnis return 0 sonst ID
			if ($num_rows > 0)
			{
				while ($row = $db->db_fetch_object())
				{
					$arrayDoksZuEntakzeptieren[] = $row->prestudent_id;
				}
				//print_r($arrayDoksZuEntakzeptieren);

				//für alle prestudent_ids das Dokument Entakzeptieren
				$qry = "DELETE FROM public.tbl_dokumentprestudent WHERE prestudent_id in (";

				foreach ($arrayDoksZuEntakzeptieren as $prestudent_id)
				{
					$qry .= $prestudent_id;

					if (next($arrayDoksZuEntakzeptieren) == true)
					{
						$qry .=  ",";
					}
				}
				$qry .=  ") AND dokument_kurzbz = ".$this->db_add_param($dokument_kurzbz).";";

				if ($this->db_query($qry))
				{
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Entakzeptieren';
					return false;
				}
			}
			return true;
		}
		else
			return false;
	}
}
