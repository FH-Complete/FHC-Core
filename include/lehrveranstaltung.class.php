<?php
/* Copyright (C) 2021 fhcomplete.org
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 * 			Stefan Puraner	<puraner@technikum-wien.at> and
 *			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
require_once(dirname(__FILE__) . '/basis_db.class.php');
require_once(dirname(__FILE__) . '/functions.inc.php');
require_once(dirname(__FILE__) . '/studiengang.class.php');

class lehrveranstaltung extends basis_db
{
	public $new;	 // boolean
	public $lehrveranstaltungen = array(); //  lehrveranstaltung Objekt
	public $lehrveranstaltung_id; // serial
	public $studiengang_kz;   // integer
	public $bezeichnung;	  // string
	public $kurzbz;	   // string
	public $lehrform_kurzbz;	 // string
	public $semester;	   // smallint
	public $ects;	   // numeric(5,2)
	public $semesterstunden;	 // smallint
	public $anmerkung;	// string
	public $lehre = true;	// boolean
	public $lehreverzeichnis;  // string
	public $aktiv = true;	// boolean
	public $ext_id;	 // bigint
	public $insertamum;	// timestamp
	public $insertvon;	// string
	public $planfaktor;	// numeric(3,2)
	public $planlektoren;   // integer
	public $planpersonalkosten;  // numeric(7,2)
	public $plankostenprolektor; // numeric(6,2)
	public $updateamum;	// timestamp
	public $updatevon;	// string
	public $sprache = 'German';  // varchar(16)
	public $sort;	 // smallint
	public $incoming = 5;	// smallint
	public $zeugnis = true;   // boolean
	public $projektarbeit;   // boolean
	public $koordinator;   // varchar(16)
	public $bezeichnung_english; // varchar(256)
	public $orgform_kurzbz;	// varchar(3)
	public $lehrtyp_kurzbz;	// varchar(32)
	public $lehrmodus_kurzbz;	//varchar(32)
	public $oe_kurzbz;	// varchar(32)
	public $raumtyp_kurzbz;	// varchar(16)
	public $anzahlsemester;	// smallint
	public $semesterwochen;	// smallint
	public $lvnr;	// varchar(32)
	public $bezeichnung_arr = array();
	public $semester_alternativ; // smallint
	public $farbe;
	public $lehrauftrag=true;
	public $lehrveranstaltung_template_id; // integer


	public $studienplan_lehrveranstaltung_id;
	public $studienplan_lehrveranstaltung_id_parent;
	public $stpllv_pflicht=true;
	public $stpllv_koordinator;
	public $stpllv_semester;

	public $sws;
	public $lvs;
	public $alvs;
	public $lvps;
	public $las;

	public $benotung=true;
	public $lvinfo=true;
	public $curriculum=true;
	public $export=true;

	/**
	 * Konstruktor
	 * @param $lehrveranstaltung_id ID der zu ladenden Lehrveranstaltung
	 */
	public function __construct($lehrveranstaltung_id = null)
	{
		parent::__construct();

		if (!is_null($lehrveranstaltung_id))
			$this->load($lehrveranstaltung_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param $lehrveranstaltung_id  ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrveranstaltung_id)
	{
		if (!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER);

		if (!$this->db_query($qry)) {
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		if ($row = $this->db_fetch_object()) {
			$this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$this->studiengang_kz = $row->studiengang_kz;
			$this->bezeichnung = $row->bezeichnung;
			$this->kurzbz = $row->kurzbz;
			$this->lehrform_kurzbz = $row->lehrform_kurzbz;
			$this->semester = $row->semester;
			$this->ects = $row->ects;
			$this->semesterstunden = $row->semesterstunden;
			$this->anmerkung = $row->anmerkung;
			$this->lehre = $this->db_parse_bool($row->lehre);
			$this->lehreverzeichnis = $row->lehreverzeichnis;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->ext_id = $row->ext_id;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->planfaktor = $row->planfaktor;
			$this->planlektoren = $row->planlektoren;
			$this->planpersonalkosten = $row->planpersonalkosten;
			$this->plankostenprolektor = $row->plankostenprolektor;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->sprache = $row->sprache;
			$this->sort = $row->sort;
			$this->incoming = $row->incoming;
			$this->zeugnis = $this->db_parse_bool($row->zeugnis);
			$this->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$this->koordinator = $row->koordinator;
			$this->bezeichnung_english = $row->bezeichnung_english;
			$this->orgform_kurzbz = $row->orgform_kurzbz;
			$this->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$this->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$this->oe_kurzbz = $row->oe_kurzbz;
			$this->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$this->anzahlsemester = $row->anzahlsemester;
			$this->semesterwochen = $row->semesterwochen;
			$this->lvnr = $row->lvnr;
			$this->semester_alternativ = $row->semester_alternativ;
			$this->farbe = $row->farbe;
			$this->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;

			$this->sws = $row->sws;
			$this->lvs = $row->lvs;
			$this->alvs = $row->alvs;
			$this->lvps = $row->lvps;
			$this->las = $row->las;

			$this->benotung = $this->db_parse_bool($row->benotung);
			$this->lvinfo = $this->db_parse_bool($row->lvinfo);
			$this->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			// FIXME: LV-Bezeichnung richtig mehrsprachig machen
			// Zwischenzeitlich 'Italian' zum bezeichnung_arr dazugegeben
			$this->bezeichnung_arr['German'] = $this->bezeichnung;
			$this->bezeichnung_arr['Italian'] = $this->bezeichnung;
			$this->bezeichnung_arr['English'] = $this->bezeichnung_english;
			if ($this->bezeichnung_arr['English'] == '')
				$this->bezeichnung_arr['English'] = $this->bezeichnung_arr['German'];
		}

		return true;
	}

	/**
	 * Liefert alle Lehrveranstaltungen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung;";

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$lv_obj->anzahlsemester = $row->anzahlsemester;
			$lv_obj->semesterwochen = $row->semesterwochen;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;

			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}

	/**
	 * Liefert alle Lehrveranstaltungen die den gefilterten Attributen entsprechen
	 * @param $studiengang_kz integer Kennzahl des Studiengangs.
	 * @param $semester integer Ausbildungssemester.
	 * @param $lehreverzeichnis string LehreVZ
	 * @param $lehre boolean Lehre
	 * @param $aktiv boolean
	 * @param $sort smallint Sortierung
	 * @param $oe_kurzbz string Organisationseinheit
	 * @param $lehrtyp string lehrtyp_kurzbz
	 * @param $lehrmodus string lehrmodus_kurzbz
	 * @param $orgform string Organisationsform
	 * @param $lehrmodus string lehrmodus_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_lva($studiengang_kz=null, $semester = null, $lehreverzeichnis = null, $lehre = null, $aktiv = null, $sort = null, $oe_kurzbz=null, $lehrtyp=null, $lehrmodus=null, $orgform=null)
	{
		//Variablen pruefen
		if($semester == "null")
			$semester = null;

		if($lehreverzeichnis == "null")
			$lehreverzeichnis = null;

		if (!is_null($studiengang_kz) && (!is_numeric($studiengang_kz) && $studiengang_kz != ''))
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if (!is_null($semester) && (!is_numeric($semester) && $semester != ''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if (!is_null($aktiv) && !is_bool($aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if (!is_null($lehre) && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE 1=1";

		if (!is_null($studiengang_kz))
			$qry.=" AND studiengang_kz=" . $this->db_add_param($studiengang_kz, FHC_INTEGER);

		//Select Befehl zusammenbauen
		if (!is_null($lehreverzeichnis))
			$qry .= " AND lehreverzeichnis=" . $this->db_add_param($lehreverzeichnis);

		if (!is_null($semester) && $semester != '')
			$qry .= " AND semester=" . $this->db_add_param($semester, FHC_INTEGER);
		else
			$qry .= " AND semester is not null ";

		if (!is_null($lehre))
			$qry .= " AND lehre=" . ($lehre ? 'true' : 'false');

		if (!is_null($aktiv) && $aktiv)
			$qry .= " AND aktiv ";

		if (!is_null($lehre) && $lehre)
			$qry .= " AND lehre ";

		if(!is_null($oe_kurzbz))
			$qry .= " AND oe_kurzbz=".$this->db_add_param($oe_kurzbz);

		if(!is_null($lehrtyp))
			$qry .= " AND lehrtyp_kurzbz=".$this->db_add_param($lehrtyp);

		if(!is_null($lehrmodus))
			$qry .= " AND lehrmodus_kurzbz=".$this->db_add_param($lehrmodus);

		if(!is_null($orgform) && $orgform!='')
			$qry .= " AND orgform_kurzbz=".$this->db_add_param($orgform);

		if (is_null($sort) || empty($sort))
			$qry .= " ORDER BY semester, bezeichnung";
		else
			$qry .= " ORDER BY $sort ";

		//Datensaetze laden
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$lv_obj->anzahlsemester = $row->anzahlsemester;
			$lv_obj->semesterwochen = $row->semesterwochen;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['Italian'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$this->lehrveranstaltungen[] = $lv_obj;
		}
		return true;
	}

	/**
	 * Liefert alle Lehrveranstaltungen zu einem Studiengang/Semester
	 * @param $studiengang_kz
	 * @param $semester
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_lva_le($studiengang_kz, $studiensemester_kurzbz = null, $semester = null, $lehreverzeichnis = null, $lehre = null, $aktiv = null, $sort = null)
	{
		//Variablen pruefen

		if (!is_numeric($studiengang_kz) || $studiengang_kz === '')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if (!is_null($semester) && (!is_numeric($semester) && $semester != ''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if (!is_null($aktiv) && !is_bool($aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if (!is_null($lehre) && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}

		$qry = "SELECT
					distinct lehre.tbl_lehrveranstaltung.*, tbl_lehreinheit.studiensemester_kurzbz
				FROM
					lehre.tbl_lehrveranstaltung,lehre.tbl_lehreinheit
				WHERE
					tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id
					AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		//Select Befehl zusammenbauen
		if (!is_null($lehreverzeichnis))
			$qry .= " AND lehreverzeichnis=" . $this->db_add_param($lehreverzeichnis);

		if (!is_null($semester) && $semester != '')
			$qry .= " AND semester=" . $this->db_add_param($semester);
		else
			$qry .= " AND semester is not null ";

		if (!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz != '')
			$qry .= " AND tbl_lehreinheit.studiensemester_kurzbz=" . $this->db_add_param($studiensemester_kurzbz);

		if (!is_null($lehre))
			$qry .= " AND lehre=" . ($lehre ? 'true' : 'false');

		if (!is_null($aktiv) && $aktiv)
			$qry .= " AND aktiv ";

		if (!is_null($lehre) && $lehre)
			$qry .= " AND lehre ";

		if (is_null($sort) || empty($sort))
			$qry .= " ORDER BY semester, bezeichnung";
		else
			$qry .= " ORDER BY $sort ";

		//Datensaetze laden
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$lv_obj->anzahlsemester = $row->anzahlsemester;
			$lv_obj->semesterwochen = $row->semesterwochen;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$lv_obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;

			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}

	/**
	 * Liefert alle Lehrveranstaltungen eines Studenten (alle Semester)
	 * @param $student_uid
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_lva_student($student_uid, $studiensemester_kurzbz=NULL)
	{
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung
				WHERE lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM campus.vw_student_lehrveranstaltung
											  WHERE uid=" . $this->db_add_param($student_uid);
		if($studiensemester_kurzbz !== NULL)
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		$qry .= ") OR lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM lehre.tbl_zeugnisnote WHERE student_uid=" . $this->db_add_param($student_uid);
		if($studiensemester_kurzbz !== NULL)
			$qry .= ' AND studiensemester_kurzbz='.$this->db_add_param ($studiensemester_kurzbz);
		$qry .= ") ORDER BY semester, bezeichnung";

		//Datensaetze laden
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		while ($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$lv_obj->anzahlsemester = $row->anzahlsemester;
			$lv_obj->semesterwochen = $row->semesterwochen;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}

	/**
	 * Zaehlt alle Lehrveranstaltungen einer Organisationsform in einem Studiengang
	 * @param $studiengang_kz
	 * @param $orgform_kurzbz
	 * @return false im Fehlerfall, ansonsten das Ergebnis
	 */
	public function count_lva_orgform($studiengang_kz, $orgform_kurzbz=null)
	{
		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry='SELECT count(*) as count FROM lehre.tbl_lehrveranstaltung
			WHERE studiengang_kz='.$this->db_add_param($studiengang_kz).' AND orgform_kurzbz'.(is_null($orgform_kurzbz)?' is null':"=".$this->db_add_param($orgform_kurzbz));

		$return=array();
		if($db_result=$this->db_query($qry))
		{
			if($row=$this->db_fetch_object($db_result))
			{
				return $row->count;
			}
		}
		$this->errormsg = 'Fehler bei der Datenbankabfrage';
		return false;
	}

	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		//Laenge Pruefen
		if (mb_strlen($this->bezeichnung) > 128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->kurzbz) > 16)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->anmerkung) > 64)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->lehreverzeichnis) > 16)
		{
			$this->errormsg = 'Lehreverzeichnis darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->lvnr) > 32)
		{
			$this->errormsg = 'LVNR darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if (!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		if ($this->semester != '' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester ist ungueltig';
			return false;
		}
		if ($this->planfaktor != '' && !is_numeric($this->planfaktor))
		{
			$this->errormsg = 'Planfaktor ist ungueltig';
			return false;
		}
		if ($this->planlektoren != '' && !is_numeric($this->planlektoren))
		{
			$this->errormsg = 'Planlektoren ist ungueltig';
			return false;
		}
		if ($this->ects != '' && !is_numeric($this->ects))
		{
			$this->errormsg = 'ECTS sind ungueltig';
			return false;
		}
		if ($this->ects > 40)
		{
			$this->errormsg = 'ECTS darf nicht groesser als 40 sein';
			return false;
		}
		if ($this->semesterstunden != '' && !isint($this->semesterstunden))
		{
			$this->errormsg = 'Semesterstunden muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if ($this->sort != '' && !isint($this->sort))
		{
			$this->errormsg = 'Sort muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if ($this->incoming != '' && !isint($this->incoming))
		{
			$this->errormsg = 'Sort muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if ($this->anzahlsemester != '' && !isint($this->sort))
		{
			$this->errormsg = 'Anzahl Semester muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if ($this->semesterwochen != '' && !isint($this->sort))
		{
			$this->errormsg = 'Semesterwochen muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if ($this->lehrveranstaltung_template_id != '')
		{
			if (!isint($this->lehrveranstaltung_template_id)) {
				$this->errormsg = 'Lehrveranstaltung Template Id muss eine gültige ganze Zahl sein';
				return false;
			} elseif($this->lehrtyp_kurzbz == 'tpl') {
				$this->errormsg = 'Lehrveranstaltung Template Id darf bei Lehrveranstaltungen des Typs "Template" nicht gesetzt werden';
				return false;
			} else {
				$template = new lehrveranstaltung($this->lehrveranstaltung_template_id);
				if ($template->errormsg) {
					$this->errormsg = 'Lehrveranstaltung Template: ' . $template->errormsg;
					return false;
				} elseif ($template->lehrtyp_kurzbz != 'tpl') {
					$this->errormsg = 'Lehrveranstaltung Template: Als Lehrtyp muss Template ausgewählt sein';
					return false;
				}
			}
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		if ($new == null)
			$new = $this->new;

		//Gueltigkeit der Variablen pruefen
		if (!$this->validate())
			return false;

		if ($new)
		{
			//Neuen Datensatz anlegen
			$qry = 'BEGIN; INSERT INTO lehre.tbl_lehrveranstaltung (studiengang_kz, bezeichnung, kurzbz, lehrform_kurzbz,
				semester, ects, semesterstunden,  anmerkung, lehre, lehreverzeichnis, aktiv, insertamum,
				insertvon, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor, updateamum, updatevon, sort,
				zeugnis, projektarbeit, sprache, koordinator, bezeichnung_english, orgform_kurzbz, incoming, lehrtyp_kurzbz, oe_kurzbz,
				raumtyp_kurzbz, anzahlsemester, semesterwochen, lvnr, semester_alternativ, farbe, lehrveranstaltung_template_id,sws,lvs,alvs,lvps,las,benotung,lvinfo,
				lehrauftrag, lehrmodus_kurzbz) VALUES ('.
					$this->db_add_param($this->studiengang_kz). ', '.
					$this->db_add_param($this->bezeichnung). ', '.
					$this->db_add_param($this->kurzbz). ', '.
					$this->db_add_param($this->lehrform_kurzbz). ', '.
					$this->db_add_param($this->semester). ', '.
					$this->db_add_param($this->ects). ', '.
					$this->db_add_param($this->semesterstunden). ', '.
					$this->db_add_param($this->anmerkung). ', '.
					$this->db_add_param($this->lehre, FHC_BOOLEAN). ','.
					$this->db_add_param($this->lehreverzeichnis). ', '.
					$this->db_add_param($this->aktiv, FHC_BOOLEAN). ', '.
					$this->db_add_param($this->insertamum). ', '.
					$this->db_add_param($this->insertvon). ', '.
					$this->db_add_param($this->planfaktor). ', '.
					$this->db_add_param($this->planlektoren). ', '.
					$this->db_add_param($this->planpersonalkosten). ', '.
					$this->db_add_param($this->plankostenprolektor). ', '.
					$this->db_add_param($this->updateamum). ', '.
					$this->db_add_param($this->updatevon). ','.
					$this->db_add_param($this->sort). ','.
					$this->db_add_param($this->zeugnis, FHC_BOOLEAN). ','.
					$this->db_add_param($this->projektarbeit, FHC_BOOLEAN). ','.
					$this->db_add_param($this->sprache). ','.
					$this->db_add_param($this->koordinator). ','.
					$this->db_add_param($this->bezeichnung_english). ','.
					$this->db_add_param($this->orgform_kurzbz). ','.
					$this->db_add_param($this->incoming).','.
					$this->db_add_param($this->lehrtyp_kurzbz).','.
					$this->db_add_param($this->oe_kurzbz). ','.
					$this->db_add_param($this->raumtyp_kurzbz). ','.
					$this->db_add_param($this->anzahlsemester). ','.
					$this->db_add_param($this->semesterwochen). ','.
					$this->db_add_param($this->lvnr).','.
					$this->db_add_param($this->semester_alternativ).','.
					$this->db_add_param($this->farbe).','.
					$this->db_add_param($this->lehrveranstaltung_template_id, FHC_INTEGER).','.
					$this->db_add_param($this->sws).','.
					$this->db_add_param($this->lvs).','.
					$this->db_add_param($this->alvs).','.
					$this->db_add_param($this->lvps).','.
					$this->db_add_param($this->las).','.
					$this->db_add_param($this->benotung, FHC_BOOLEAN).','.
					$this->db_add_param($this->lvinfo, FHC_BOOLEAN).','.
					$this->db_add_param($this->lehrauftrag, FHC_BOOLEAN).','.
					$this->db_add_param($this->lehrmodus_kurzbz)
					.');';
		}
		else
		{
			//bestehenden Datensatz akualisieren
			//Pruefen ob lehrveranstaltung_id eine gueltige Zahl ist
			if (!is_numeric($this->lehrveranstaltung_id) || $this->lehrveranstaltung_id == '')
			{
				$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_lehrveranstaltung SET ' .
					'studiengang_kz=' . $this->db_add_param($this->studiengang_kz, FHC_INTEGER) . ', ' .
					'bezeichnung=' . $this->db_add_param($this->bezeichnung) . ', ' .
					'kurzbz=' . $this->db_add_param($this->kurzbz) . ', ' .
					'lehrform_kurzbz=' . $this->db_add_param($this->lehrform_kurzbz) . ', ' .
					'semester=' . $this->db_add_param($this->semester, FHC_INTEGER) . ', ' .
					'ects=' . $this->db_add_param($this->ects) . ', ' .
					'semesterstunden=' . $this->db_add_param($this->semesterstunden, FHC_INTEGER) . ', ' .
					'anmerkung=' . $this->db_add_param($this->anmerkung) . ', ' .
					'lehre=' . $this->db_add_param($this->lehre, FHC_BOOLEAN) . ', ' .
					'lehreverzeichnis=' . $this->db_add_param($this->lehreverzeichnis) . ', ' .
					'aktiv=' . $this->db_add_param($this->aktiv, FHC_BOOLEAN) . ', ' .
					'planfaktor=' . $this->db_add_param($this->planfaktor) . ', ' .
					'planlektoren=' . $this->db_add_param($this->planlektoren, FHC_INTEGER) . ', ' .
					'planpersonalkosten=' . $this->db_add_param($this->planpersonalkosten) . ', ' .
					'plankostenprolektor=' . $this->db_add_param($this->plankostenprolektor) . ', ' .
					'updateamum=' . $this->db_add_param($this->updateamum) . ',' .
					'updatevon=' . $this->db_add_param($this->updatevon) . ',' .
					'sort=' . $this->db_add_param($this->sort) . ',' .
					'incoming=' . $this->db_add_param($this->incoming, FHC_INTEGER) . ',' .
					'zeugnis=' . $this->db_add_param($this->zeugnis, FHC_BOOLEAN) . ',' .
					'projektarbeit=' . $this->db_add_param($this->projektarbeit, FHC_BOOLEAN) . ',' .
					'koordinator=' . $this->db_add_param($this->koordinator) . ',' .
					'sprache=' . $this->db_add_param($this->sprache) . ',' .
					'bezeichnung_english=' . $this->db_add_param($this->bezeichnung_english) . ',' .
					'orgform_kurzbz=' . $this->db_add_param($this->orgform_kurzbz) . ',' .
					'lehrtyp_kurzbz=' . $this->db_add_param($this->lehrtyp_kurzbz) . ',' .
					'lehrmodus_kurzbz=' . $this->db_add_param($this->lehrmodus_kurzbz) . ',' .
					'oe_kurzbz=' . $this->db_add_param($this->oe_kurzbz) . ',' .
					'raumtyp_kurzbz=' . $this->db_add_param($this->raumtyp_kurzbz) . ',' .
					'anzahlsemester=' . $this->db_add_param($this->anzahlsemester, FHC_INTEGER) . ',' .
					'semesterwochen=' . $this->db_add_param($this->semesterwochen, FHC_INTEGER) . ',' .
					'lvnr = ' . $this->db_add_param($this->lvnr) . ', ' .
					'semester_alternativ = '.$this->db_add_param($this->semester_alternativ).', '.
					'farbe = '.$this->db_add_param($this->farbe).', '.
					'lehrveranstaltung_template_id = '.$this->db_add_param($this->lehrveranstaltung_template_id, FHC_INTEGER).', '.
					'sws = '.$this->db_add_param($this->sws).', '.
					'lvs = '.$this->db_add_param($this->lvs).', '.
					'alvs = '.$this->db_add_param($this->alvs).', '.
					'lvps = '.$this->db_add_param($this->lvps).', '.
					'las = '.$this->db_add_param($this->las).', '.
					'benotung = '.$this->db_add_param($this->benotung, FHC_BOOLEAN).', '.
					'lvinfo = '.$this->db_add_param($this->lvinfo, FHC_BOOLEAN).', '.
					'lehrauftrag = '.$this->db_add_param($this->lehrauftrag, FHC_BOOLEAN).' '.
					'WHERE lehrveranstaltung_id = ' . $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER, false) . ';';
		}

		if ($this->db_query($qry))
		{
			if ($new)
			{
				$qry = "SELECT currval('lehre.tbl_lehrveranstaltung_lehrveranstaltung_id_seq') as id";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
					{
						$this->lehrveranstaltung_id = $row->id;
						$this->db_query('COMMIT;');
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
			$this->db_query('ROLLBACK');
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Lehrveranstaltung zu der ein Mitarbeiter
	 * in einem Studiensemester zugeordnet ist
	 * @param studiengang_kz, uid, studiensemester_kurzbz
	 * @return true wenn ok, false wenn Fehler
	 */
	public function loadLVAfromMitarbeiter($studiengang_kz, $uid, $studiensemester_kurzbz)
	{
		if (!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE ";
		if ($studiengang_kz != 0)
			$qry.="tbl_lehrveranstaltung.studiengang_kz=" . $this->db_add_param($studiengang_kz) . " AND ";

		$qry.= "tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.studiensemester_kurzbz = " . $this->db_add_param($studiensemester_kurzbz) . " AND
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid=" . $this->db_add_param($uid) . ";";
		if ($this->db_query($qry))
		{
			while ($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrveranstaltung();

				$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->kurzbz = $row->kurzbz;
				$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lv_obj->semester = $row->semester;
				$lv_obj->ects = $row->ects;
				$lv_obj->semesterstunden = $row->semesterstunden;
				$lv_obj->anmerkung = $row->anmerkung;
				$lv_obj->lehre = $this->db_parse_bool($row->lehre);
				$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
				$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$lv_obj->ext_id = $row->ext_id;
				$lv_obj->insertamum = $row->insertamum;
				$lv_obj->insertvon = $row->insertvon;
				$lv_obj->planfaktor = $row->planfaktor;
				$lv_obj->planlektoren = $row->planlektoren;
				$lv_obj->planpersonalkosten = $row->planpersonalkosten;
				$lv_obj->plankostenprolektor = $row->plankostenprolektor;
				$lv_obj->updateamum = $row->updateamum;
				$lv_obj->updatevon = $row->updatevon;
				$lv_obj->sprache = $row->sprache;
				$lv_obj->sort = $row->sort;
				$lv_obj->incoming = $row->incoming;
				$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$lv_obj->koordinator = $row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$lv_obj->oe_kurzbz = $row->oe_kurzbz;
				$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$lv_obj->anzahlsemester = $row->anzahlsemester;
				$lv_obj->semesterwochen = $row->semesterwochen;
				$lv_obj->lvnr = $row->lvnr;
				$lv_obj->semester_alternativ = $row->semester_alternativ;
				$lv_obj->farbe = $row->farbe;
				$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$lv_obj->benotung = $this->db_parse_bool($row->benotung);
				$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
				$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($lv_obj->bezeichnung_arr['English'] == '')
					$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

				$this->lehrveranstaltungen[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Lesen aus der Datenbank';
			return false;
		}
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param 	$stg Studiengangs_kz
	 * 			$sem Semester
	 * 			$order Sortierkriterium
	 * @return array mit Lehrferanstaltungen oder false=fehler
	 */
	public function getTab($stg = null, $sem = null, $order = 'lehrveranstaltung_id')
	{
		if ($stg != null && !is_numeric($stg))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if ($sem != null && !is_numeric($sem))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		$sql_query = "SELECT * FROM lehre.tbl_lehrveranstaltung";

		if ($stg != null || $sem != null)
			$sql_query .= " WHERE true";

		if ($stg != null)
			$sql_query .= " AND studiengang_kz=".$this->db_add_param($stg);

		if ($sem != null)
			$sql_query .= " AND semester=".$this->db_add_param($sem);

		$sql_query .= " ORDER BY $order";

		if ($this->db_query($sql_query))
		{
			while ($row = $this->db_fetch_object())
			{
				$l = new lehrveranstaltung();

				$l->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$l->kurzbz = $row->kurzbz;
				$l->bezeichnung = $row->bezeichnung;
				$l->lehrform_kurzbz = $row->lehrform_kurzbz;
				$l->studiengang_kz = $row->studiengang_kz;
				$l->sprache = $row->sprache;
				$l->ects = $row->ects;
				$l->semesterstunden = $row->semesterstunden;
				$l->anmerkung = $row->anmerkung;
				$l->lehre = $row->lehre;
				$l->lehreverzeichnis = $row->lehreverzeichnis;
				$l->aktiv = $row->aktiv;
				$l->planfaktor = $row->planfaktor;
				$l->planlektoren = $row->planlektoren;
				$l->planpersonalkosten = $row->planpersonalkosten;
				$l->plankostenprolektor = $row->plankostenprolektor;
				$l->updateamum = $row->updateamum;
				$l->updatevon = $row->updatevon;
				$l->insertamum = $row->insertamum;
				$l->insertvon = $row->insertvon;
				$l->sort = $row->sort;
				$l->incoming = $row->incoming;
				$l->zeugnis = $this->db_parse_bool($row->zeugnis);
				$l->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$l->koordinator = $row->koordinator;
				$l->bezeichnung_english = $row->bezeichnung_english;
				$l->orgform_kurzbz = $row->orgform_kurzbz;
				$l->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$l->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$l->oe_kurzbz = $row->oe_kurzbz;
				$l->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$l->anzahlsemester = $row->anzahlsemester;
				$l->semesterwochen = $row->semesterwochen;
				$l->lvnr = $row->lvnr;
				$l->semester_alternativ = $row->semester_alternativ;
				$l->farbe = $row->farbe;
				$l->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$l->benotung = $this->db_parse_bool($row->benotung);
				$l->lvinfo = $this->db_parse_bool($row->lvinfo);
				$l->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$l->bezeichnung_arr['German'] = $row->bezeichnung;
				$l->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($l->bezeichnung_arr['English'] == '')
					$l->bezeichnung_arr['English'] = $l->bezeichnung_arr['German'];

				$this->lehrveranstaltungen[] = $l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}

	/**
	 * Laedt die LVs die als Array uebergeben werden
	 * @param $ids Array mit den LV ids
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($ids)
	{
		if (count($ids) == 0)
			return true;

		$ids = $this->db_implode4SQL($ids);

		$qry = 'SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id in(' . $ids . ')';
		$qry .=" ORDER BY bezeichnung";

		if (!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object($result))
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
			$lv_obj->anzahlsemester = $row->anzahlsemester;
			$lv_obj->semesterwochen = $row->semesterwochen;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}

	/**
	 * Laedt alle Lehrveranstaltungen eines Studienplans
	 *
	 * @param $studienplan_id ID des Studienplans
	 * @param $semeser Semester optional
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadLehrveranstaltungStudienplan($studienplan_id, $semester = null, $order=null)
	{
		if (!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'StudienplanID ist ungueltig';
			return false;
		}

		$qry = "SELECT tbl_lehrveranstaltung.*,
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
			tbl_studienplan_lehrveranstaltung.semester as stpllv_semester,
			tbl_studienplan_lehrveranstaltung.pflicht as stpllv_pflicht,
			tbl_studienplan_lehrveranstaltung.koordinator as stpllv_koordinator,
			tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent,
			tbl_studienplan_lehrveranstaltung.sort stpllv_sort,
			tbl_studienplan_lehrveranstaltung.curriculum,
			tbl_studienplan_lehrveranstaltung.export,
			tbl_studienplan_lehrveranstaltung.genehmigung
		FROM lehre.tbl_lehrveranstaltung
		JOIN lehre.tbl_studienplan_lehrveranstaltung
		USING(lehrveranstaltung_id)
		WHERE tbl_studienplan_lehrveranstaltung.studienplan_id=" . $this->db_add_param($studienplan_id, FHC_INTEGER);
		if (defined("CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN") && CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN)
			$qry .= " AND tbl_lehrveranstaltung.lehrtyp_kurzbz != 'modul'";
		if (!is_null($semester))
		{
			$qry.=" AND tbl_studienplan_lehrveranstaltung.semester=" . $this->db_add_param($semester, FHC_INTEGER);
		}
		if(is_null($order))
			$qry.=" ORDER BY stpllv_sort, semester, sort";
		else
			$qry.=' ORDER BY '.$order;
		$this->lehrveranstaltungen = array();
		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new lehrveranstaltung();

				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->kurzbz = $row->kurzbz;
				$obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$obj->semester = $row->semester;
				$obj->ects = $row->ects;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->anmerkung = $row->anmerkung;
				$obj->lehre = $this->db_parse_bool($row->lehre);
				$obj->lehreverzeichnis = $row->lehreverzeichnis;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->planfaktor = $row->planfaktor;
				$obj->planlektoren = $row->planlektoren;
				$obj->planpersonalkosten = $row->planpersonalkosten;
				$obj->plankostenprolektor = $row->plankostenprolektor;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->sprache = $row->sprache;
				$obj->sort = $row->sort;
				$obj->incoming = $row->incoming;
				$obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$obj->koordinator = $row->koordinator;
				$obj->bezeichnung_english = $row->bezeichnung_english;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$obj->anzahlsemester = $row->anzahlsemester;
				$obj->semesterwochen = $row->semesterwochen;
				$obj->lvnr = $row->lvnr;
				$obj->semester_alternativ = $row->semester_alternativ;
				$obj->farbe = $row->farbe;
				$obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$obj->stpllv_sort = $row->stpllv_sort;
				$obj->benotung = $this->db_parse_bool($row->benotung);
				$obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$obj->bezeichnung_arr['German'] = $row->bezeichnung;
				$obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($obj->bezeichnung_arr['English'] == '')
					$obj->bezeichnung_arr['English'] = $obj->bezeichnung_arr['German'];

				$obj->sws = $row->sws;
				$obj->lvs = $row->lvs;
				$obj->alvs = $row->alvs;
				$obj->lvps = $row->lvps;
				$obj->las = $row->las;

				$obj->stpllv_semester = $row->stpllv_semester;
				$obj->stpllv_pflicht = $this->db_parse_bool($row->stpllv_pflicht);
				$obj->stpllv_koordinator = $row->stpllv_koordinator;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$obj->curriculum = $this->db_parse_bool($row->curriculum);
				$obj->export = $this->db_parse_bool($row->export);
				$obj->genehmigung = $this->db_parse_bool($row->genehmigung);
				$obj->new = false;

				$this->lehrveranstaltungen[] = $obj;
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
	 * Liefert die Lehrveranstaltungen als verschachtelten Tree
	 */
	public function getLehrveranstaltungTree()
	{
		$tree = array();
		foreach ($this->lehrveranstaltungen as $row)
		{
			if ($row->studienplan_lehrveranstaltung_id_parent == ''
				|| (defined("CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN")
					&& CIS_PROFIL_STUDIENPLAN_MODULE_AUSBLENDEN))
			{
				$tree[$row->studienplan_lehrveranstaltung_id] = $row;
				$tree[$row->studienplan_lehrveranstaltung_id]->childs = $this->getLehrveranstaltungTreeChilds($row->studienplan_lehrveranstaltung_id);
			}
		}
		return $tree;
	}

	/**
	 * Generiert die Subtrees des Lehrveranstaltungstrees
	 */
	public function getLehrveranstaltungTreeChilds($studienplan_lehrveranstaltung_id)
	{
		$childs = array();
		foreach ($this->lehrveranstaltungen as $row)
		{
			if ($row->studienplan_lehrveranstaltung_id_parent === $studienplan_lehrveranstaltung_id)
			{
				$childs[$row->studienplan_lehrveranstaltung_id] = $row;
				$childs[$row->studienplan_lehrveranstaltung_id]->childs = $this->getLehrveranstaltungTreeChilds($row->studienplan_lehrveranstaltung_id);
			}
		}
		return $childs;
	}

	/**
	 * Generiert die Subtrees des Lehrveranstaltungstrees
	 */
	public function hasChildren($studienplan_lehrveranstaltung_id)
	{
		$childs = array();
		foreach ($this->lehrveranstaltungen as $row)
		{
			if ($row->studienplan_lehrveranstaltung_id_parent === $studienplan_lehrveranstaltung_id)
			{
				$childs[$row->studienplan_lehrveranstaltung_id] = $row;
				$childs[$row->studienplan_lehrveranstaltung_id]->childs = $this->getLehrveranstaltungTreeChilds($row->studienplan_lehrveranstaltung_id);
			}
		}
		if(count($childs) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Baut die Datenstruktur für senden als JSON Objekt auf
	 */
	public function cleanResult()
	{
		$values = array();
		if (count($this->lehrveranstaltungen) > 0)
		{
			foreach ($this->lehrveranstaltungen as $lv)
			{
				$obj = new stdClass();
				$obj->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
				$obj->studiengang_kz = $lv->studiengang_kz;
				$obj->bezeichnung = $lv->bezeichnung;
				$obj->kurzbz = $lv->kurzbz;
				$obj->lehrform_kurzbz = $lv->lehrform_kurzbz;
				$obj->semester = $lv->semester;
				$obj->ects = $lv->ects;
				$obj->semesterstunden = $lv->semesterstunden;
				$obj->lehrtyp_kurzbz = $lv->lehrtyp_kurzbz;
				$obj->lehrmodus_kurzbz = $lv->lehrmodus_kurzbz;
				$obj->studienplan_lehrveranstaltung_id = $lv->studienplan_lehrveranstaltung_id;
				$obj->stpllv_semester = $lv->stpllv_semester;
				$obj->stpllv_pflicht = $lv->stpllv_pflicht;
				$obj->stpllv_koordinator = $lv->stpllv_koordinator;
				$obj->oe_kurzbz = $lv->oe_kurzbz;
				$obj->lvnr = $lv->lvnr;
				$obj->lehrveranstaltung_template_id = $lv->lehrveranstaltung_template_id;
				$obj->benotung = $this->db_parse_bool($lv->benotung);
				$obj->lvinfo =$this->db_parse_bool( $lv->lvinfo);
				$obj->zeugnis = $this->db_parse_bool($lv->zeugnis);
				$obj->lehrauftrag = $this->db_parse_bool($lv->lehrauftrag);

				$values[] = $obj;

			}
		}
		else
		{
			$obj = new stdClass();
			$obj->lehrveranstaltung_id = $this->lehrveranstaltung_id;
			$obj->studiengang_kz = $this->studiengang_kz;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->kurzbz = $this->kurzbz;
			$obj->lehrform_kurzbz = $this->lehrform_kurzbz;
			$obj->semester = $this->semester;
			$obj->ects = $this->ects;
			$obj->semesterstunden = $this->semesterstunden;
			$obj->stpllv_semester = $this->stpllv_semester;
			$obj->stpllv_pflicht = $this->stpllv_pflicht;
			$obj->stpllv_koordinator = $this->stpllv_koordinator;
			$obj->oe_kurzbz = $this->oe_kurzbz;
			$obj->lvnr = $this->lvnr;
			$obj->lehrveranstaltung_template_id = $this->lehrveranstaltung_template_id;
			$obj->benotung = $this->db_parse_bool($this->benotung);
			$obj->lvinfo =$this->db_parse_bool( $this->lvinfo);
			$obj->zeugnis = $this->db_parse_bool($this->zeugnis);
			$obj->lehrauftrag = $this->db_parse_bool($this->lehrauftrag);

			$values[] = $obj;
		}
		return $values;
	}

	/**
	 * Baut die Baumstruktur für jsTree in Studienordnung auf
	 * @param $tree Array von Lehrveranstaltungen
	 * @return Array mit der Baumstruktur
	 */
	protected function cleanTreeResult($tree)
	{
		$values = array();
		if (count($tree) > 0)
		{
			foreach ($tree as $lv)
			{
				$obj = new stdClass();
				$obj->lehrveranstaltung_id = $lv->lehrveranstaltung_id;
				$obj->studiengang_kz = $lv->studiengang_kz;
				$obj->bezeichnung = $lv->bezeichnung;
				$obj->bezeichnung_english = $lv->bezeichnung_english;
				$obj->kurzbz = $lv->kurzbz;
				$obj->lehrform_kurzbz = $lv->lehrform_kurzbz;
				$obj->semester = $lv->semester;
				$obj->ects = $lv->ects;
				$obj->semesterstunden = $lv->semesterstunden;
				$obj->studienplan_lehrveranstaltung_id = $lv->studienplan_lehrveranstaltung_id;
				$obj->lehrtyp_kurzbz = $lv->lehrtyp_kurzbz;
				$obj->lehrmodus_kurzbz = $lv->lehrmodus_kurzbz;
				$obj->stpllv_semester = $lv->stpllv_semester;
				$obj->stpllv_pflicht = $lv->stpllv_pflicht;
				$obj->stpllv_koordinator = $lv->stpllv_koordinator;
				$obj->lvnr = $lv->lvnr;
				$obj->lehrveranstaltung_template_id = $lv->lehrveranstaltung_template_id;
				$obj->stpllv_sort = $lv->stpllv_sort;
				$obj->oe_kurzbz = $lv->oe_kurzbz;
				$obj->sws = $lv->sws;
				$obj->alvs = $lv->alvs;
				$obj->lvs = $lv->lvs;
				$obj->lvps = $lv->lvps;
				$obj->las = $lv->las;
				$obj->semesterwochen = $lv->semesterwochen;
				$obj->orgform_kurzbz = $lv->orgform_kurzbz;
				$obj->incoming = $lv->incoming;
				$obj->sprache = $lv->sprache;
				$obj->benotung = $lv->benotung;
				$obj->lvinfo = $lv->lvinfo;
				$obj->zeugnis = $lv->zeugnis;
				$obj->curriculum = $lv->curriculum;
				$obj->export = $lv->export;
				$obj->genehmigung = $lv->genehmigung;
				$obj->lehrauftrag = $lv->lehrauftrag;
				$obj->lehre = $lv->lehre;
				$obj->children = array();
				if(count($lv->childs) > 0)
				{
					$obj->children = $this->cleanTreeResult($lv->childs);
				}
				$values[] = $obj;

			}
		}
		else
		{
			$obj = new stdClass();
			$obj->lehrveranstaltung_id = $this->lehrveranstaltung_id;
			$obj->studiengang_kz = $this->studiengang_kz;
			$obj->bezeichnung = $this->bezeichnung;
			$obj->kurzbz = $this->kurzbz;
			$obj->lehrform_kurzbz = $this->lehrform_kurzbz;
			$obj->semester = $this->semester;
			$obj->ects = $this->ects;
			$obj->semesterstunden = $this->semesterstunden;
			$obj->stpllv_semester = $this->stpllv_semester;
			$obj->stpllv_pflicht = $this->stpllv_pflicht;
			$obj->stpllv_koordinator = $this->stpllv_koordinator;
			$obj->lvnr = $this->lvnr;
			$obj->lehrveranstaltung_template_id = $this->lehrveranstaltung_template_id;
			$obj->benotung = $this->db_parse_bool($this->benotung);
			$obj->lvinfo =$this->db_parse_bool( $this->lvinfo);
			$obj->zeugnis = $this->db_parse_bool($this->zeugnis);
			$obj->curriculum = $this->db_parse_bool($this->curriculum);
			$obj->lehrauftrag = $this->lehrauftrag;

			$values[] = $obj;
		}
		return $values;
	}

	/**
	 * Baut die Datenstruktur für jsTree in Studienordnung auf
	 * @param $studienplan_id ID des Studienpland
	 */
	public function getLvTree($studienplan_id)
	{
		$values = array();
		$this->loadLehrveranstaltungStudienplan($studienplan_id);
		$tree = $this->getLehrveranstaltungTree();
		$values = $this->cleanTreeResult($tree);
		unset($this->lehrveranstaltungen);
		$this->lehrveranstaltungen=array();
		return $values;
	}

	/**
	 * Lädt alle kompatiblen LVs zu einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 */
	public function loadLVkompatibel($lehrveranstaltung_id)
	{
		if (!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT lehrveranstaltung_id_kompatibel FROM lehre.tbl_lehrveranstaltung_kompatibel
			WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			$data = array();
			while($row = $this->db_fetch_object())
			{
				$data[] = $row->lehrveranstaltung_id_kompatibel;
			}
			return $data;
		}
	}

	/**
	 * Lädt alle kompatiblen LVs zu einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 */
	public function getLVkompatibel($lehrveranstaltung_id)
	{
		if (!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id IN (
			SELECT lehrveranstaltung_id_kompatibel
			FROM lehre.tbl_lehrveranstaltung_kompatibel
			WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).");";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrveranstaltung();

				$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->kurzbz = $row->kurzbz;
				$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lv_obj->semester = $row->semester;
				$lv_obj->ects = $row->ects;
				$lv_obj->semesterstunden = $row->semesterstunden;
				$lv_obj->anmerkung = $row->anmerkung;
				$lv_obj->lehre = $this->db_parse_bool($row->lehre);
				$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
				$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$lv_obj->ext_id = $row->ext_id;
				$lv_obj->insertamum = $row->insertamum;
				$lv_obj->insertvon = $row->insertvon;
				$lv_obj->planfaktor = $row->planfaktor;
				$lv_obj->planlektoren = $row->planlektoren;
				$lv_obj->planpersonalkosten = $row->planpersonalkosten;
				$lv_obj->plankostenprolektor = $row->plankostenprolektor;
				$lv_obj->updateamum = $row->updateamum;
				$lv_obj->updatevon = $row->updatevon;
				$lv_obj->sprache = $row->sprache;
				$lv_obj->sort = $row->sort;
				$lv_obj->incoming = $row->incoming;
				$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$lv_obj->koordinator = $row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$lv_obj->farbe = $row->farbe;
				$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$lv_obj->benotung = $this->db_parse_bool($row->benotung);
				$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
				$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($lv_obj->bezeichnung_arr['English'] == '')
					$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

				$this->lehrveranstaltungen[] = $lv_obj;
			}
			return true;
		}
	}

    /**
     * Lädt alle Lehrveranstaltungen zu denen die übergebene LV ID kompatibel ist
     * @param $lehrveranstaltung_id ID der Lehrveranstaltung
     */
    public function getLVkompatibelTo($lehrveranstaltung_id, $studienplan_ids=array())
    {
        if (!is_numeric($lehrveranstaltung_id))
        {
            $this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
            return false;
        }

        if((!is_array($studienplan_ids)) && (count($studienplan_ids) < 1))
		{
            $this->errormsg = 'Es muss ein Array von Studienplan_IDs mit mindestens einem Element übergeben werden.';
			return false;
		}

        $studienplaene = "";
        foreach($studienplan_ids as $stplId)
		{
			$studienplaene .= $stplId.",";
		}
        $studienplaene = rtrim($studienplaene, ",");

        $qry = "SELECT * FROM lehre.tbl_lehrveranstaltung
					JOIN lehre.tbl_studienplan_lehrveranstaltung USING (lehrveranstaltung_id)
 					WHERE lehrveranstaltung_id IN (
						SELECT lehrveranstaltung_id
						FROM lehre.tbl_lehrveranstaltung_kompatibel
							WHERE lehrveranstaltung_id_kompatibel=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).")
							AND studienplan_id IN(".$studienplaene.");";

        if($this->db_query($qry))
        {
            while($row = $this->db_fetch_object())
            {
                $lv_obj = new lehrveranstaltung();

                $lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
                $lv_obj->studiengang_kz = $row->studiengang_kz;
                $lv_obj->bezeichnung = $row->bezeichnung;
                $lv_obj->kurzbz = $row->kurzbz;
                $lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
                $lv_obj->semester = $row->semester;
                $lv_obj->ects = $row->ects;
                $lv_obj->semesterstunden = $row->semesterstunden;
                $lv_obj->anmerkung = $row->anmerkung;
                $lv_obj->lehre = $this->db_parse_bool($row->lehre);
                $lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
                $lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
                $lv_obj->ext_id = $row->ext_id;
                $lv_obj->insertamum = $row->insertamum;
                $lv_obj->insertvon = $row->insertvon;
                $lv_obj->planfaktor = $row->planfaktor;
                $lv_obj->planlektoren = $row->planlektoren;
                $lv_obj->planpersonalkosten = $row->planpersonalkosten;
                $lv_obj->plankostenprolektor = $row->plankostenprolektor;
                $lv_obj->updateamum = $row->updateamum;
                $lv_obj->updatevon = $row->updatevon;
                $lv_obj->sprache = $row->sprache;
                $lv_obj->sort = $row->sort;
                $lv_obj->incoming = $row->incoming;
                $lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
                $lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
                $lv_obj->koordinator = $row->koordinator;
                $lv_obj->bezeichnung_english = $row->bezeichnung_english;
                $lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
                $lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
                $lv_obj->farbe = $row->farbe;
                $lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
                $lv_obj->benotung = $this->db_parse_bool($row->benotung);
                $lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
                $lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

                $lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
                $lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
                if ($lv_obj->bezeichnung_arr['English'] == '')
                    $lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

                $this->lehrveranstaltungen[] = $lv_obj;
            }
            return true;
        }
    }

	/**
	 * Speichert eine Kombination aus LV und ihrer kompatiblen Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der kompatiblen Lehrveranstaltung
	 */
	public function saveKompatibleLehrveranstaltung($lehrveranstaltung_id, $lehrveranstaltung_id_kompatibel)
	{
		$qry = 'SELECT
					*
				FROM
					lehre.tbl_lehrveranstaltung_kompatibel
				WHERE
					lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).'
					AND lehrveranstaltung_id_kompatibel='.$this->db_add_param($lehrveranstaltung_id_kompatibel, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if(!$this->db_fetch_object())
			{
				$qry = 'INSERT INTO lehre.tbl_lehrveranstaltung_kompatibel (lehrveranstaltung_id, lehrveranstaltung_id_kompatibel)
				VALUES ('.$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).', '.
						$this->db_add_param($lehrveranstaltung_id_kompatibel, FHC_INTEGER).');';

				if($this->db_query($qry))
				{
					return true;
				}
				else
				{
					$this->errormsg = 'Fehler beim Speichern des Datensatzes';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Lehrveranstaltung bereits vorhanden';
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
	 * Löscht eine kompatible Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param $lehrveranstaltung_id ID der kompatiblen Lehrveranstaltung
	 */
	public function deleteKompatibleLehrveranstaltung($lehrveranstaltung_id, $lehrveranstaltung_id_kompatibel)
	{
		$qry = 'DELETE FROM lehre.tbl_lehrveranstaltung_kompatibel WHERE
			lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).' AND
			lehrveranstaltung_id_kompatibel='.$this->db_add_param($lehrveranstaltung_id_kompatibel, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Lädt Lehrveranstaltungen nach ihrer Organisationseinheit
	 * @param $oe_kurzbz Kurzbezeichnung der Organisationseinheit
	 * @param $aktiv optional, true wenn nur aktive LVs
	 * @param $lehrtyp optional, gewünschter Lehrtyp
	 */
	public function load_lva_oe($oe_kurzbz, $aktiv=null, $lehrtyp=null, $sort=null, $semester=null, $lehrmodus=null)
	{

		if (is_null($oe_kurzbz))
		{
			$this->errormsg = 'OE KurzBz darf nicht null sein';
			return false;
		}
		if (!is_null($aktiv) && !is_bool($aktiv))
		{
			$this->errormsg = 'Aktivkz muss ein boolscher Wert sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE oe_kurzbz=" . $this->db_add_param($oe_kurzbz, FHC_STRING);

		//Select Befehl zusammenbauen

		if (!is_null($aktiv) && $aktiv)
			$qry .= " AND aktiv ";

		if(!is_null($lehrtyp))
			$qry .= " AND lehrtyp_kurzbz=".$this->db_add_param($lehrtyp);

		if(!is_null($lehrmodus))
			$qry .= " AND lehrmodus_kurzbz=".$this->db_add_param($lehrmodus);

		if(!is_null($semester))
			$qry .= " AND semester=".$this->db_add_param ($semester);

		if (is_null($sort) || empty($sort))
			$qry .= " ORDER BY semester, bezeichnung";
		else
			$qry .= " ORDER BY $sort ";
		$qry .= ";";

		//Datensaetze laden
		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz = $row->studiengang_kz;
			$lv_obj->bezeichnung = $row->bezeichnung;
			$lv_obj->kurzbz = $row->kurzbz;
			$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
			$lv_obj->semester = $row->semester;
			$lv_obj->ects = $row->ects;
			$lv_obj->semesterstunden = $row->semesterstunden;
			$lv_obj->anmerkung = $row->anmerkung;
			$lv_obj->lehre = $this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
			$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id = $row->ext_id;
			$lv_obj->insertamum = $row->insertamum;
			$lv_obj->insertvon = $row->insertvon;
			$lv_obj->planfaktor = $row->planfaktor;
			$lv_obj->planlektoren = $row->planlektoren;
			$lv_obj->planpersonalkosten = $row->planpersonalkosten;
			$lv_obj->plankostenprolektor = $row->plankostenprolektor;
			$lv_obj->updateamum = $row->updateamum;
			$lv_obj->updatevon = $row->updatevon;
			$lv_obj->sprache = $row->sprache;
			$lv_obj->sort = $row->sort;
			$lv_obj->incoming = $row->incoming;
			$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator = $row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
			$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
			$lv_obj->lvnr = $row->lvnr;
			$lv_obj->semester_alternativ = $row->semester_alternativ;
			$lv_obj->farbe = $row->farbe;
			$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
			$lv_obj->oe_kurzbz = $row->oe_kurzbz;
			$lv_obj->benotung = $this->db_parse_bool($row->benotung);
			$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
			$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

			$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
			if ($lv_obj->bezeichnung_arr['English'] == '')
				$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

			$this->lehrveranstaltungen[] = $lv_obj;
		}
		return true;
	}


	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $lvid ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($lvid)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($lvid) || $lvid == '')
		{
			$this->errormsg = 'lvid muss eine gültige Zahl sein'."\n";
			return false;
		}

		$qry = "SELECT count(*) as anzahl FROM lehre.tbl_lehreinheit
			WHERE lehrveranstaltung_id=".$this->db_add_param($lvid, FHC_INTEGER)."
			OR lehrfach_id=".$this->db_add_param($lvid, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Zu dieser Lehrveranstaltung existieren Lehreinheiten oder Lehrfächer in der Datenbank. Sie kann daher nicht gelöscht werden.';
					return false;
				}
				else
				{
					//loeschen des Datensatzes
					$qry="DELETE FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$this->db_add_param($lvid, FHC_INTEGER, false);

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
			else
			{
				$this->errormsg = 'Fehler beim Abfragen zugewiesener Lehreinheiten'."\n";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Abfragen zugewiesener Lehreinheiten'."\n";
			return false;
		}
	}

	/**
	 * Sucht nach Lehrveranstaltungen
	 * @param $filter Suchfilter
	 */
	public function search($filter)
	{
		$qry = "SELECT
					tbl_lehrveranstaltung.*, tbl_studiengang.kurzbzlang as studiengang_kurzbzlang
				FROM
					lehre.tbl_lehrveranstaltung
					JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE
					lower(tbl_lehrveranstaltung.bezeichnung || ' ' || tbl_studiengang.kurzbzlang || ' ' || tbl_lehrveranstaltung.semester) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_studiengang.kurzbzlang || ' ' || tbl_lehrveranstaltung.semester || ' ' || tbl_lehrveranstaltung.bezeichnung) like lower('%".$this->db_escape($filter)."%')
			";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$lv_obj = new lehrveranstaltung();

				$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->kurzbz = $row->kurzbz;
				$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lv_obj->semester = $row->semester;
				$lv_obj->ects = $row->ects;
				$lv_obj->semesterstunden = $row->semesterstunden;
				$lv_obj->anmerkung = $row->anmerkung;
				$lv_obj->lehre = $this->db_parse_bool($row->lehre);
				$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
				$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$lv_obj->ext_id = $row->ext_id;
				$lv_obj->insertamum = $row->insertamum;
				$lv_obj->insertvon = $row->insertvon;
				$lv_obj->planfaktor = $row->planfaktor;
				$lv_obj->planlektoren = $row->planlektoren;
				$lv_obj->planpersonalkosten = $row->planpersonalkosten;
				$lv_obj->plankostenprolektor = $row->plankostenprolektor;
				$lv_obj->updateamum = $row->updateamum;
				$lv_obj->updatevon = $row->updatevon;
				$lv_obj->sprache = $row->sprache;
				$lv_obj->sort = $row->sort;
				$lv_obj->incoming = $row->incoming;
				$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$lv_obj->koordinator = $row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$lv_obj->oe_kurzbz = $row->oe_kurzbz;
				$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$lv_obj->anzahlsemester = $row->anzahlsemester;
				$lv_obj->semesterwochen = $row->semesterwochen;
				$lv_obj->lvnr = $row->lvnr;
				$lv_obj->semester_alternativ = $row->semester_alternativ;
				$lv_obj->farbe = $row->farbe;
				$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$lv_obj->benotung = $this->db_parse_bool($row->benotung);
				$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$lv_obj->studiengang_kurzbzlang = $row->studiengang_kurzbzlang;

				$this->lehrveranstaltungen[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg='Fehler bei Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Liefert die Anzahl der ECTS Punkte die ein Student in einem Studiensemester
	 * bereits verbraucht hat (fuer reduzierte Studiengebuehr)
	 * @param $uid UID
	 * @param $studiensemester_kurzbz
	 * @return numeric - Anzahl der ECTS Punkte
	 */
	public function getUsedECTS($uid, $studiensemester_kurzbz)
	{
		$qry = "
		SELECT sum(ects) as ectssumme FROM (
			SELECT
				lehrveranstaltung_id, ects
			FROM
				campus.vw_student_lehrveranstaltung
			WHERE
				uid=".$this->db_add_param($uid)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
			UNION
			SELECT
				lehrveranstaltung_id, ects
			FROM
				lehre.tbl_zeugnisnote
				JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
			WHERE
				student_uid=".$this->db_add_param($uid)."
				AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
		) a";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->ectssumme;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der ECTS Punkte';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der ECTS Punkte';
			return false;
		}
	}

	/**
	 * lädt die Lehrveranstaltungen zum zugehörigen Mitarbeiter
	 * @param String $uid User ID des Mitarbeiters
	 * @param String $studiensemster_kurzbz Kurzbezeichnung des Studiensemesters
	 */
	public function getLVByMitarbeiter($uid, $studiensemester_kurzbz = null)
	{
		$qry = 'SELECT DISTINCT tbl_lehrveranstaltung.* FROM lehre.tbl_lehrveranstaltung
					JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				WHERE
					mitarbeiter_uid='.$this->db_add_param($uid);

		if($studiensemester_kurzbz != null)
		{
			$qry .= ' AND tbl_lehreinheit.studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz).';';
		}

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrveranstaltung();

				$lv_obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$lv_obj->studiengang_kz = $row->studiengang_kz;
				$lv_obj->bezeichnung = $row->bezeichnung;
				$lv_obj->kurzbz = $row->kurzbz;
				$lv_obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$lv_obj->semester = $row->semester;
				$lv_obj->ects = $row->ects;
				$lv_obj->semesterstunden = $row->semesterstunden;
				$lv_obj->anmerkung = $row->anmerkung;
				$lv_obj->lehre = $this->db_parse_bool($row->lehre);
				$lv_obj->lehreverzeichnis = $row->lehreverzeichnis;
				$lv_obj->aktiv = $this->db_parse_bool($row->aktiv);
				$lv_obj->ext_id = $row->ext_id;
				$lv_obj->insertamum = $row->insertamum;
				$lv_obj->insertvon = $row->insertvon;
				$lv_obj->planfaktor = $row->planfaktor;
				$lv_obj->planlektoren = $row->planlektoren;
				$lv_obj->planpersonalkosten = $row->planpersonalkosten;
				$lv_obj->plankostenprolektor = $row->plankostenprolektor;
				$lv_obj->updateamum = $row->updateamum;
				$lv_obj->updatevon = $row->updatevon;
				$lv_obj->sprache = $row->sprache;
				$lv_obj->sort = $row->sort;
				$lv_obj->incoming = $row->incoming;
				$lv_obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$lv_obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$lv_obj->zeugnis = $row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
				$lv_obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$lv_obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$lv_obj->oe_kurzbz = $row->oe_kurzbz;
				$lv_obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$lv_obj->anzahlsemester = $row->anzahlsemester;
				$lv_obj->semesterwochen = $row->semesterwochen;
				$lv_obj->lvnr = $row->lvnr;
				$lv_obj->semester_alternativ = $row->semester_alternativ;
				$lv_obj->farbe = $row->farbe;
				$lv_obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$lv_obj->benotung = $this->db_parse_bool($row->benotung);
				$lv_obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$lv_obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$lv_obj->bezeichnung_arr['German'] = $row->bezeichnung;
				$lv_obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($lv_obj->bezeichnung_arr['English'] == '')
					$lv_obj->bezeichnung_arr['English'] = $lv_obj->bezeichnung_arr['German'];

				$this->lehrveranstaltungen[] = $lv_obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Lehrveranstaltungen konnten nicht geladen werden.";
		}

	}

	/**
	 * Lädt alle Studenten UIDs die die angegebenen LV besuchen (optional mit Studiensemester)
	 * @param integer $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param string $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
	 * @return boolean|array false, wenn eine Fehler auftritt; Array mit UIDs wenn erfolgreich
	 */
	public function getStudentsOfLv($lehrveranstaltung_id, $studiensemester_kurzbz=null, $lehreinheit_id=null)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = "Lehrveranstaltung ID muss eine gültige Zahl sein.";
			return false;
		}

		$qry = 'SELECT distinct uid FROM campus.vw_student_lehrveranstaltung WHERE '
				. 'lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id);

		if(!is_null($studiensemester_kurzbz))
		{
			$qry .= ' AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz);
		}
		if(!is_null($lehreinheit_id))
		{
			$qry .= ' AND lehreinheit_id='.$this->db_add_param($lehreinheit_id);
		}
		$qry .= ';';

		if($this->db_query($qry))
		{
			$result = array();
			while($row = $this->db_fetch_object())
			{
				array_push($result, $row->uid);
			}
			return $result;
		}
		return false;
	}

	/**
	 *
	 * @param type $lv_id
	 * @param type $semester -> Ausbildungssemester
	 * @return boolean
	 */
	public function getALVS($lv_id, $semester)
	{

		if($semester=='')
		{
			$this->errormsg = "Kein Semester übergeben";
			return false;
		}

		$ss = ($semester%2==0)?'SS':'WS';

		$qry_ss = "SELECT studiensemester_kurzbz, start, ende
				FROM public.tbl_studiensemester
				WHERE substring(studiensemester_kurzbz from 1 for 2)='$ss'
				AND start < now() ORDER BY start DESC LIMIT 1";

		if(!$result = $this->db_query($qry_ss))
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

		if(!$row= $this->db_fetch_object($result))
		{
			$this->errormsg = "Kein Semester gefunden";
			return false;

		}

		$qry_alvs = "SELECT sum(lm.semesterstunden) as alvs
				FROM lehre.tbl_lehrveranstaltung
				JOIN lehre.tbl_lehreinheit USING (lehrveranstaltung_id)
				JOIN lehre.tbl_lehreinheitmitarbeiter lm USING (lehreinheit_id)
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lv_id, FHC_STRING)."
				AND studiensemester_kurzbz = ".$this->db_add_param($row->studiensemester_kurzbz).";";

		if(!$result_alvs=$this->db_query($qry_alvs))
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}

		if($row_alvs = $this->db_fetch_object($result_alvs))
		{
			return $row_alvs->alvs;
		}
		else
		{
			$this->errormsg = $qry_alvs;
			return false;
		}
	}

	/**
	 * Lädt alle Lehreinheit_IDs eine Lehrveranstaltung (optional mit Studiensemester)
	 * @param integer $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param string $uid UID eines Studenten
	 * @param string $studiensemester_kurzbz Kurzbezeichnung des Studiensemesters
	 * @return boolean|array false, wenn eine Fehler auftritt; Array mit UIDs wenn erfolgreich
	 */
	public function getLehreinheitenOfLv($lehrveranstaltung_id, $uid, $studiensemester_kurzbz=null)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = "Lehrveranstaltung ID muss eine gültige Zahl sein.";
			return false;
		}

		$qry = 'SELECT lehreinheit_id FROM campus.vw_student_lehrveranstaltung WHERE '
			. 'lehrveranstaltung_id='.$this->db_add_param($lehrveranstaltung_id)
			. ' AND uid='.$this->db_add_param($uid);

		if(!is_null($studiensemester_kurzbz))
		{
			$qry .= ' AND studiensemester_kurzbz='.$this->db_add_param($studiensemester_kurzbz);
		}
		$qry .= ' ORDER BY lehreinheit_id;';

		if($this->db_query($qry))
		{
			$result = array();
			while($row = $this->db_fetch_object())
			{
				array_push($result, $row->lehreinheit_id);
			}
			return $result;
		}
		return false;
	}

	/**
	 * Prueft ob das Lehrverzeichnis bereits anderwertig verwendet wird
	 * @param $lehreverzeichnis
	 * @param $studiengang_kz
	 * @param $semester
	 */
	public function lehreverzeichnisExists($lehreverzeichnis, $studiengang_kz, $semester)
	{
		$qry = 'SELECT
					1
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					lehreverzeichnis='.$this->db_add_param($lehreverzeichnis).'
					AND studiengang_kz='.$this->db_add_param($studiengang_kz).'
					AND semester='.$this->db_add_param($semester).';';

		if($this->db_query($qry))
		{
			if($this->db_num_rows() > 0)
			{
				return true;
			}
			return false;
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Daten";
			return false;
		}
	}

	/**
	 * Lädt alle Lehrveranstaltungen eines Studienplans
	 * Optionale Filterung nach Lehrtyp und Semester
	 * @param type $studienplan_id
	 * @param type $lehrtyp_kurzbz
	 * @param type $semester
	 * @return boolean
	 */
	public function getLVFromStudienplanByLehrtyp($studienplan_id, $lehrtyp_kurzbz=NULL, $semester=NULL)
	{
		if (!is_numeric($studienplan_id) || $studienplan_id === '')
		{
			$this->errormsg = 'StudienplanID ist ungueltig';
			return false;
		}

		$qry = "SELECT DISTINCT tbl_lehrveranstaltung.*
		FROM lehre.tbl_lehrveranstaltung
		JOIN lehre.tbl_studienplan_lehrveranstaltung
		USING(lehrveranstaltung_id)
		WHERE tbl_studienplan_lehrveranstaltung.studienplan_id=" . $this->db_add_param($studienplan_id, FHC_INTEGER);

		if (!is_null($lehrtyp_kurzbz))
		{
			$qry.=" AND tbl_lehrveranstaltung.lehrtyp_kurzbz=" . $this->db_add_param($lehrtyp_kurzbz, FHC_STRING);
		}

		if (!is_null($lehrmodus_kurzbz))
		{
			$qry.=" AND tbl_lehrveranstaltung.lehrmodus_kurzbz=" . $this->db_add_param($lehrmodus_kurzbz, FHC_STRING);
		}

		if (!is_null($semester))
		{
			$qry.=" AND tbl_studienplan_lehrveranstaltung.semester=" . $this->db_add_param($semester, FHC_INTEGER);
		}
		$qry.=" ORDER BY bezeichnung;";

		$this->lehrveranstaltungen = array();
		if ($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$obj = new lehrveranstaltung();

				$obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->kurzbz = $row->kurzbz;
				$obj->lehrform_kurzbz = $row->lehrform_kurzbz;
				$obj->semester = $row->semester;
				$obj->ects = $row->ects;
				$obj->semesterstunden = $row->semesterstunden;
				$obj->anmerkung = $row->anmerkung;
				$obj->lehre = $this->db_parse_bool($row->lehre);
				$obj->lehreverzeichnis = $row->lehreverzeichnis;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->ext_id = $row->ext_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->planfaktor = $row->planfaktor;
				$obj->planlektoren = $row->planlektoren;
				$obj->planpersonalkosten = $row->planpersonalkosten;
				$obj->plankostenprolektor = $row->plankostenprolektor;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->sprache = $row->sprache;
				$obj->sort = $row->sort;
				$obj->incoming = $row->incoming;
				$obj->zeugnis = $this->db_parse_bool($row->zeugnis);
				$obj->projektarbeit = $this->db_parse_bool($row->projektarbeit);
				$obj->koordinator = $row->koordinator;
				$obj->bezeichnung_english = $row->bezeichnung_english;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->lehrtyp_kurzbz = $row->lehrtyp_kurzbz;
				$obj->lehrmodus_kurzbz = $row->lehrmodus_kurzbz;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->raumtyp_kurzbz = $row->raumtyp_kurzbz;
				$obj->anzahlsemester = $row->anzahlsemester;
				$obj->semesterwochen = $row->semesterwochen;
				$obj->lvnr = $row->lvnr;
				$obj->semester_alternativ = $row->semester_alternativ;
				$obj->farbe = $row->farbe;
				$obj->lehrveranstaltung_template_id = $row->lehrveranstaltung_template_id;
				$obj->benotung = $this->db_parse_bool($row->benotung);
				$obj->lvinfo = $this->db_parse_bool($row->lvinfo);
				$obj->lehrauftrag = $this->db_parse_bool($row->lehrauftrag);

				$obj->bezeichnung_arr['German'] = $row->bezeichnung;
				$obj->bezeichnung_arr['English'] = $row->bezeichnung_english;
				if ($obj->bezeichnung_arr['English'] == '')
					$obj->bezeichnung_arr['English'] = $obj->bezeichnung_arr['German'];

				$obj->new = false;

				$this->lehrveranstaltungen[] = $obj;
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
	 * Gibt alle Organisationseinheiten der Studiengänge zurück, mit denen
	 * die Lehrveranstaltung über Studienpläne verknüpft ist
	 * @return boolean|array false im Fehlerfall, sonst ein Array
	 */
	public function getAllOe()
	{
		$oe = array();

		$qry = 'SELECT DISTINCT oe_kurzbz
				FROM lehre.tbl_studienplan_lehrveranstaltung
				JOIN lehre.tbl_studienplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
				WHERE lehrveranstaltung_id = '.$this->db_add_param($this->lehrveranstaltung_id);

		if($result = $this->db_query($qry))
		{
			while ($row = $this->db_fetch_object($result))
			{
				$oe[] = $row->oe_kurzbz;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Daten";
			return false;
		}

		// oe_kurzbz des Studiengangs der LVA hinzufügen
		$stg = new studiengang($this->studiengang_kz);

		if(!in_array($stg->oe_kurzbz, $oe))
		{
			$oe[] = $stg->oe_kurzbz;
		}

		return $oe;
	}

	/**
	 * Laedt den LV-Leiter einer Lehrveranstaltung, wenn keiner der Lektoren als LVLeiter eingetragen ist,
	 * wird der erstbeste Lektor geliefert
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung
	 * @param $studiensemester_kurzbz Studiensemester
	 * @return UID des Mitarbeiters
	 */
	public function getLVLeitung($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					mitarbeiter_uid,
					CASE WHEN lehrfunktion_kurzbz='LV-Leitung' THEN 1 ELSE 2 END as sort
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				WHERE
					tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
					AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				ORDER BY sort LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->mitarbeiter_uid;
			}
			else
			{
				$this->errormsg = 'Keine Eintrag gefunden';
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
	 * Laedt den LV-Leiter einer Lehrveranstaltung
	 * ist keiner der Lektoren als LV-Leitung eingetragen, wird Null zurückgegeben
	 * @param int $lehrveranstaltung_id ID der Lehrveranstaltung.
	 * @param char $studiensemester_kurzbz Studiensemester.
	 * @return char $mitarbeiter_uid UID des Mitarbeiters oder NULL, wenn keine LV-Leitung vorhanden
	 */
	public function getEingetrageneLVLeitung($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					mitarbeiter_uid
				FROM
					lehre.tbl_lehreinheit
					JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
				WHERE
					tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
					AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND lehrfunktion_kurzbz='LV-Leitung';";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->mitarbeiter_uid;
			}
			else
			{
				$this->errormsg = 'Keine Eintrag gefunden';
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
	 * Liefert den Koordinator einer Lehrveranstaltung
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 */
	public function getKoordinator($lehrveranstaltung_id, $studiensemester_kurzbz=null)
	{
		$qry = "
		SELECT a.uid, vorname, nachname, titelpre, titelpost
		FROM
		(
			SELECT
				koordinator as uid
			FROM
				lehre.tbl_lehrveranstaltung
			WHERE
				lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
			UNION
			SELECT
				uid
			FROM
				lehre.tbl_lehreinheit
				JOIN lehre.tbl_lehrveranstaltung as lehrfach on(tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id)
				JOIN public.tbl_fachbereich ON(lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz)
				JOIN public.tbl_benutzerfunktion ON(tbl_fachbereich.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz)
			WHERE
				tbl_benutzerfunktion.funktion_kurzbz='fbk'
				AND (tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now())
				AND (tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
				AND tbl_lehreinheit.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
				AND tbl_benutzerfunktion.oe_kurzbz=(
					SELECT
						tbl_studiengang.oe_kurzbz
					FROM
						lehre.tbl_lehrveranstaltung
						JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					)
				AND EXISTS(
					SELECT
						lehrveranstaltung_id
					FROM
						lehre.tbl_lehrveranstaltung
					WHERE
						lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
						AND koordinator is null
					)
				";
		if(!is_null($studiensemester_kurzbz))
				$qry.=" AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);
		$qry.="
		) as a
		JOIN campus.vw_mitarbeiter ON(a.uid=vw_mitarbeiter.uid)
		WHERE vw_mitarbeiter.aktiv";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->uid = $row->uid;
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpost = $row->titelpost;
				$obj->titelpre = $row->titelpre;

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
	 * Prüft ob eine Lehrvernstaltung in Studienplordnungen verwendet wird die
 	 * nicht mehr in bearbeitung sind. Diese sind fuer die bearbeitung gesperrt
	 * @param integer $lehrveranstaltung_id
	 * @return boolean true wenn gesperrt
	 * @return boolean false wenn nicht gesperrt
	 * @return boolean false und errormsg im Fehlerfall
	 */
	public function isGesperrt($lehrveranstaltung_id)
	{
		$qry = "SELECT
					count(*) as anzahl
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienplan_lehrveranstaltung USING(studienplan_id)
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				WHERE
					tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER)."
					AND tbl_studienordnung.status_kurzbz<>'development'";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->anzahl>0)
				{
					return true;
				}
				else
				{
					return false;
				}
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
	 * Prueft ob eine Lehrveranstaltung im gewaehlten Studiensemester angeboten wird.
	 * Dazu wird geprueft ob die LV einem aktuellen Studienplan zugeordnet ist, und ob ein Lehrauftrag vorhanden ist.
	 *
	 * @param $lehrveranstaltung_id ID der Lehrveranstaltung.
	 * @param $studiensemester_kurzbz Kurzbz des Studiensemesters.
	 * @return boolean true wenn angeboten, false wenn nicht angeboten
	 */
	public function isOffered($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					*
				FROM
					lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id)."
				AND studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)."
				AND EXISTS (
					SELECT
						*
					FROM
						lehre.tbl_studienplan_lehrveranstaltung
						JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
					WHERE
						lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
						AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					)";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_num_rows($result)>0)
			{
				return true;
			}
			else
			{
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
	 * Sucht nach LV Templates und gibt Id und Label ("bezeichnung [kurzbz]") aus
	 * Diese funktion ist für autocomplete gedacht
	 * 
	 * @param string $filter Suchfilter
	 * @return array
	 */
	public function loadTemplates($filter)
	{
		$filter = strtolower($filter);
		$qry = "SELECT
					tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.kurzbz
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					tbl_lehrveranstaltung.lehrtyp_kurzbz = 'tpl' AND (
						CAST(tbl_lehrveranstaltung.lehrveranstaltung_id AS TEXT) LIKE '%".$this->db_escape($filter)."%' OR 
						LOWER(tbl_lehrveranstaltung.bezeichnung) LIKE '%".$this->db_escape($filter).	"%' OR 
						LOWER(tbl_lehrveranstaltung.kurzbz) LIKE '%".$this->db_escape($filter).	"%'
					)
			";
		$lehrveranstaltungen = [];
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$lehrveranstaltungen[] = $row;
			}
			return $lehrveranstaltungen;
		}
		else
		{
			$this->errormsg='Fehler bei Datenbankabfrage';
			return [];
		}
	}

	/**
	 * Lädt Template und gibt Id und Label ("bezeichnung [kurzbz]") zurück
	 * Diese funktion ist für autocomplete gedacht
	 * 
	 * @param string $name
	 * @return stdClass | null
	 */
	public function loadTemplateByName($name)
	{
		$qry = "SELECT
					tbl_lehrveranstaltung.lehrveranstaltung_id as id, CONCAT(tbl_lehrveranstaltung.bezeichnung, ' [', tbl_lehrveranstaltung.kurzbz, ']') as label
				FROM
					lehre.tbl_lehrveranstaltung
				WHERE
					tbl_lehrveranstaltung.lehrtyp_kurzbz = 'tpl' AND (
						CAST(tbl_lehrveranstaltung.lehrveranstaltung_id AS TEXT) = '".($name ? $this->db_escape($name) : 0)."' OR tbl_lehrveranstaltung.bezeichnung = '".$this->db_escape($name).	"' OR tbl_lehrveranstaltung.kurzbz = '".$this->db_escape($name).	"'
					)
			";
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				return $row;
			}
		}
		else
		{
			$this->errormsg='Fehler bei Datenbankabfrage ' .$this->db_last_error();
		}
		return null;
	}

	/**
	 * Prueft ob die Lehrveranstaltungen in dem Studiensemestern angeboten wird.
	 * Dazu wird geprueft ob die LVs einem aktuellen Studienplan zugeordnet ist und ob ein Lehrauftrag vorhanden ist.
	 *
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @return array
	 */
	public function getOfferedSemester($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					DISTINCT(studiensemester_kurzbz)
				FROM
					lehre.tbl_lehreinheit
				WHERE lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id)."
				AND studiensemester_kurzbz IN (".$this->db_implode4SQL($studiensemester_kurzbz).")
				AND EXISTS (
					SELECT
						*
					FROM
						lehre.tbl_studienplan_lehrveranstaltung
						JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
					WHERE
						lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id)."
						AND studiensemester_kurzbz IN (".$this->db_implode4SQL($studiensemester_kurzbz).")
					)";

		$studiensemester = [];
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$studiensemester[] = $row;
			}
			return $studiensemester;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft ob die Lehrveranstaltungen in den gewaehlten Studiensemestern angeboten wird.
	 * Dazu wird geprueft ob die LVs einem aktuellen Studienplan zugeordnet ist, und ob ein Lehrauftrag vorhanden ist.
	 *
	 * @param $lehrveranstaltung_id
	 * @param $studiensemester_kurzbz
	 * @return array
	 */
	public function getOfferedLVs($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$qry = "SELECT
					DISTINCT(tbl_lehreinheit.lehrveranstaltung_id)
				FROM
					lehre.tbl_lehreinheit
				WHERE tbl_lehreinheit.lehrveranstaltung_id IN (".$this->db_implode4SQL($lehrveranstaltung_id).")
				AND tbl_lehreinheit.studiensemester_kurzbz IN (".$this->db_implode4SQL($studiensemester_kurzbz).")
				AND EXISTS (
					SELECT
						*
					FROM
						lehre.tbl_studienplan_lehrveranstaltung
						JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
					WHERE
						tbl_lehreinheit.lehrveranstaltung_id IN (".$this->db_implode4SQL($lehrveranstaltung_id).")
						AND tbl_lehreinheit.studiensemester_kurzbz IN (".$this->db_implode4SQL($studiensemester_kurzbz).")
					)";

		$lehrveranstaltungen = [];
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$lehrveranstaltungen[] = $row;
			}
			return $lehrveranstaltungen;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>
