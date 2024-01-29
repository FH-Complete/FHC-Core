<?php
/* Copyright (C) 2007 fhcomplete.org
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
 *		  Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *		  Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *		  Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class studiengang extends basis_db
{
	public $new;	  			// boolean
	public $result = array();	// studiengang Objekt

	public $studiengang_kz;			// integer
	public $kurzbz;					// varchar(5)
	public $kurzbzlang;				// varchar(10)
	public $bezeichnung;			// varchar(128)
	public $english;				// varchar(128)
	public $typ;					// char(1)
	public $farbe;					// char(6)
	public $email;					// varchar(64)
	public $max_semester;			// smallint
	public $max_verband;			// char(1)
	public $max_gruppe;				// char(1)
	public $erhalter_kz;			// smallint
	public $bescheid;				// varchar(256)
	public $bescheidbgbl1;			// varchar(16)
	public $bescheidbgbl2;			// varchar(16)
	public $bescheidgz;				// varchar(16)
	public $bescheidvom;			// Date
	public $titelbescheidvom;		// Date
	public $ext_id;					// bigint
	public $orgform_kurzbz;			// varchar(3)
	public $zusatzinfo_html;		// text
	public $sprache;				// varchar(16)
	public $testtool_sprachwahl;	// boolean
	public $studienplaetze;			// smallint
	public $oe_kurzbz;				// varchar(32)
	public $onlinebewerbung;		// boolean
	public $melderelevant;			// boolean
	public $foerderrelevant;		// boolean
	public $standort_code;			// integer
	public $melde_studiengang_kz;	// varchar(32)

	public $kuerzel;	// = typ + kurzbz (Bsp: BBE)
	public $kuerzel_arr = array();			// Array mit allen Kurzeln Index=studiengangs_kz
	public $moodle;		// boolean
	public $lgartcode;	//integer
	public $mischform;	// boolean
	public $projektarbeit_note_anzeige; // boolean
	public $bezeichnung_arr = array();
	public $beschreibung;
	public $studiengang_typ_arr = array(); 	// Array mit den Studiengangstypen

	/**
	 * Konstruktor
	 * @param studiengang_kz Kennzahl des zu ladenden Studienganges
	 */
	public function __construct($studiengang_kz=null)
	{
		parent::__construct();

		if(!is_null($studiengang_kz))
			$this->load($studiengang_kz);
	}

	/**
	 * Laedt einen Studiengang
	 * @param studiengang_kz KZ des Studienganges der zu Laden ist
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_studiengang WHERE studiengang_kz = " . $this->db_add_param($studiengang_kz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->kurzbz = $row->kurzbz;
				$this->kurzbzlang = $row->kurzbzlang;
				$this->bezeichnung = $row->bezeichnung;
				$this->english = $row->english;
				$this->typ = $row->typ;
				$this->farbe = $row->farbe;
				$this->email = $row->email;
				$this->max_semester = $row->max_semester;
				$this->max_verband = $row->max_verband;
				$this->max_gruppe = $row->max_gruppe;
				$this->erhalter_kz = $row->erhalter_kz;
				$this->bescheid = $row->bescheid;
				$this->bescheidbgbl1 = $row->bescheidbgbl1;
				$this->bescheidbgbl2 = $row->bescheidbgbl2;
				$this->bescheidgz = $row->bescheidgz;
				$this->bescheidvom = $row->bescheidvom;
				$this->ext_id = $row->ext_id;
				$this->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->zusatzinfo_html = $row->zusatzinfo_html;
				$this->sprache = $row->sprache;
				$this->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$this->studienplaetze = $row->studienplaetze;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->lgartcode = $row->lgartcode;
				$this->telefon = $row->telefon;
				$this->titelbescheidvom = $row->titelbescheidvom;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$this->moodle = $this->db_parse_bool($row->moodle);
				$this->mischform = $this->db_parse_bool($row->mischform);
				$this->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
				$this->melderelevant = $this->db_parse_bool($row->melderelevant);
				$this->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$this->standort_code = $row->standort_code;
				$this->melde_studiengang_kz = $row->melde_studiengang_kz;

				$this->bezeichnung_arr['German'] = $this->bezeichnung;
				$this->bezeichnung_arr['English'] = $this->english;
				$this->bezeichnung_arr['Italian'] = $this->bezeichnung;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	/**
	 * Liefert alle Studiengaenge
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($order=null, $aktiv=true)
	{
		$qry = 'SELECT * FROM public.tbl_studiengang';
		if ($aktiv)
			$qry.=' WHERE aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$stg_obj = new studiengang();

			$stg_obj->studiengang_kz = $row->studiengang_kz;
			$stg_obj->kurzbz = $row->kurzbz;
			$stg_obj->kurzbzlang = $row->kurzbzlang;
			$stg_obj->bezeichnung = $row->bezeichnung;
			$stg_obj->english = $row->english;
			$stg_obj->typ = $row->typ;
			$stg_obj->farbe = $row->farbe;
			$stg_obj->email = $row->email;
			$stg_obj->max_semester = $row->max_semester;
			$stg_obj->max_verband = $row->max_verband;
			$stg_obj->max_gruppe = $row->max_gruppe;
			$stg_obj->erhalter_kz = $row->erhalter_kz;
			$stg_obj->bescheid = $row->bescheid;
			$stg_obj->bescheidbgbl1 = $row->bescheidbgbl1;
			$stg_obj->bescheidbgbl2 = $row->bescheidbgbl2;
			$stg_obj->bescheidgz = $row->bescheidgz;
			$stg_obj->bescheidvom = $row->bescheidvom;
			$stg_obj->ext_id = $row->ext_id;
			$stg_obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
			$stg_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$stg_obj->zusatzinfo_html = $row->zusatzinfo_html;
			$stg_obj->sprache = $row->sprache;
			$stg_obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
			$stg_obj->studienplaetze = $row->studienplaetze;
			$stg_obj->oe_kurzbz = $row->oe_kurzbz;
			$stg_obj->lgartcode = $row->lgartcode;
			$stg_obj->telefon = $row->telefon;
			$stg_obj->titelbescheidvom = $row->titelbescheidvom;
			$stg_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$stg_obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
			$stg_obj->moodle = $this->db_parse_bool($row->moodle);
			$stg_obj->mischform = $this->db_parse_bool($row->mischform);
			$stg_obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
			$stg_obj->melderelevant = $this->db_parse_bool($row->melderelevant);
			$stg_obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
			$stg_obj->standort_code = $row->standort_code;
			$stg_obj->melde_studiengang_kz = $row->melde_studiengang_kz;

			$stg_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$stg_obj->bezeichnung_arr['English'] = $row->english;
			$this->result[] = $stg_obj;
			$this->kuerzel_arr[$row->studiengang_kz] = $stg_obj->kuerzel;
		}

		return true;
	}

	// DEPRECATED! WENN SICH DER NAME EINES STUDIENGANGS AENDERT GIBT DIESE FUNKTION DOPPELTE DATENSAETZE ZURUECK
	// BESSER DIE FUNKTION getAllForOnlinebewerbung VERWENDEN
	/**
	 * Gibt alle Studiengaenge zurueck, fuer die man sich online bewerben kann
	 *
	 * @param string $order Default: typ, studiengangbezeichnung, tbl_lgartcode.bezeichnung ASC. Spalten, nach denen Sortiert werden soll.
	 * @return boolean
	 */
	public function getAllForBewerbung($order = 'typ, studiengangbezeichnung, tbl_lgartcode.bezeichnung ASC')
	{
		$qry = 'SELECT DISTINCT studiengang_kz, typ, organisationseinheittyp_kurzbz, studiengangbezeichnung, standort, studiengangbezeichnung_englisch, lgartcode, tbl_lgartcode.bezeichnung '
				. 'FROM lehre.vw_studienplan '
				. 'LEFT JOIN bis.tbl_lgartcode USING (lgartcode) '
				. 'WHERE onlinebewerbung IS TRUE '
				. 'AND aktiv IS TRUE ';

		$qry .= ' ORDER BY '.$order;

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$this->result[] = $row;
		}

		return true;
	}

	/**
	 * Gibt alle aktiven Studiengaenge und Lehrgaenge (mit Typ) zurueck, bei denen das Attribut onlinebewerbung true ist.
	 *
	 * @param string $order Spalten, nach denen Sortiert werden soll.<br>Default: tbl_studiengang.typ, tbl_lgartcode.bezeichnung ASC, tbl_studiengang.bezeichnung.
	 * @return boolean
	 */
	public function getAllForOnlinebewerbung($order = 'tbl_studiengang.typ, tbl_lgartcode.bezeichnung ASC, tbl_studiengang.bezeichnung')
	{
		$qry = "SELECT
					tbl_studiengang.studiengang_kz,
					tbl_studiengang.typ,
					tbl_studiengangstyp.bezeichnung AS typ_bezeichnung,
					tbl_studiengang.lgartcode,
					tbl_studiengang.bezeichnung AS studiengangbezeichnung,
					tbl_studiengang.english AS studiengangbezeichnung_englisch,
					tbl_organisationseinheit.organisationseinheittyp_kurzbz,
					tbl_organisationseinheit.standort,
					tbl_lgartcode.bezeichnung AS lehrgangsart
				FROM public.tbl_studiengang
				LEFT JOIN bis.tbl_lgartcode USING (lgartcode)
				LEFT JOIN public.tbl_organisationseinheit USING (oe_kurzbz)
				JOIN public.tbl_organisationseinheittyp USING (organisationseinheittyp_kurzbz)
				JOIN public.tbl_studiengangstyp USING (typ)
				WHERE tbl_studiengang.onlinebewerbung IS TRUE
				AND tbl_studiengang.aktiv IS TRUE";

		$qry .= " ORDER BY ".$order;

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$this->result[] = $row;
		}

		return true;
	}

	/**
	 * Laedt alle Studientypen in das Attribut studiengang_typ_array
	 */
	public function getAllTypes()
	{
		$qry='SELECT * FROM public.tbl_studiengangstyp';
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$this->studiengang_typ_arr[$row->typ]=$row->bezeichnung;
			}
		}
		else
			$this->errormsg = 'Fehler beim Laden der Studiengangstypen';
	}

	/**
	 * Gibt aktive OrgFormen eines Studiengangs zurück.
	 * @return array
	 */
	public function getOrgForm($studiengang_kz)
	{
		$qry = 'SELECT distinct tbl_studienplan.orgform_kurzbz '
				. 'FROM lehre.tbl_studienordnung '
				. 'JOIN lehre.tbl_studienplan '
				. 'USING (studienordnung_id) '
				. 'WHERE aktiv '
				. 'AND studiengang_kz = ' . $this->db_add_param($studiengang_kz, FHC_INTEGER)
				. ' AND tbl_studienplan.orgform_kurzbz!='. $this->db_add_param('DDP');

		if($result = $this->db_query($qry))
		{
			$ret = array();

			while($row = $this->db_fetch_object($result))
			{
				$ret[] = $row->orgform_kurzbz;
			}
		}

		return $ret;
	}

	/**
	 * Gibt aktive Sprachen eines Studiengangs zurück.
	 * @return array
	 */
	public function getSprache($studiengang_kz)
	{
		$qry = 'SELECT distinct sprache '
				. 'FROM lehre.tbl_studienordnung '
				. 'JOIN lehre.tbl_studienplan '
				. 'USING (studienordnung_id) '
				. 'WHERE aktiv '
				. 'AND studiengang_kz = ' . $this->db_add_param($studiengang_kz, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			$ret = array();

			while($row = $this->db_fetch_object($result))
			{
				$ret[] = $row->sprache;
			}
		}

		return $ret;
	}

	/**
	 * Laedt die Studiengaenge die als Array uebergeben werden
	 * @param $stgs Array mit den Kennzahlen
	 * @param $order Sortierreihenfolge
	 * @param $aktiv wenn true dann nur aktive sonst alle
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($kennzahlen, $order=null, $aktiv=true)
	{
		if(count($kennzahlen)==0)
			return true;

		$kennzahlen = $this->implode4SQL($kennzahlen);

		$qry = 'SELECT * FROM public.tbl_studiengang WHERE studiengang_kz in('.$kennzahlen.')';
		if ($aktiv)
			$qry.=' AND aktiv=true';

		if($order!=null)
		 	$qry .=" ORDER BY $order";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object($result))
		{
			$stg_obj = new studiengang();

			$stg_obj->studiengang_kz = $row->studiengang_kz;
			$stg_obj->kurzbz = $row->kurzbz;
			$stg_obj->kurzbzlang = $row->kurzbzlang;
			$stg_obj->bezeichnung = $row->bezeichnung;
			$stg_obj->english = $row->english;
			$stg_obj->typ = $row->typ;
			$stg_obj->farbe = $row->farbe;
			$stg_obj->email = $row->email;
			$stg_obj->max_semester = $row->max_semester;
			$stg_obj->max_verband = $row->max_verband;
			$stg_obj->max_gruppe = $row->max_gruppe;
			$stg_obj->erhalter_kz = $row->erhalter_kz;
			$stg_obj->bescheid = $row->bescheid;
			$stg_obj->bescheidbgbl1 = $row->bescheidbgbl1;
			$stg_obj->bescheidbgbl2 = $row->bescheidbgbl2;
			$stg_obj->bescheidgz = $row->bescheidgz;
			$stg_obj->bescheidvom = $row->bescheidvom;
			$stg_obj->ext_id = $row->ext_id;
			$stg_obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
			$stg_obj->orgform_kurzbz = $row->orgform_kurzbz;
			$stg_obj->zusatzinfo_html = $row->zusatzinfo_html;
			$stg_obj->sprache = $row->sprache;
			$stg_obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
			$stg_obj->studienplaetze = $row->studienplaetze;
			$stg_obj->oe_kurzbz = $row->oe_kurzbz;
			$stg_obj->lgartcode = $row->lgartcode;
			$stg_obj->telefon = $row->telefon;
			$stg_obj->titelbescheidvom = $row->titelbescheidvom;
			$stg_obj->aktiv = $this->db_parse_bool($row->aktiv);
			$stg_obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
			$stg_obj->moodle = $this->db_parse_bool($row->moodle);
			$stg_obj->mischform = $this->db_parse_bool($row->mischform);
			$stg_obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
			$stg_obj->melderelevant = $this->db_parse_bool($row->melderelevant);
			$stg_obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
			$stg_obj->standort_code = $row->standort_code;
			$stg_obj->melde_studiengang_kz = $row->melde_studiengang_kz;

			$stg_obj->bezeichnung_arr['German'] = $row->bezeichnung;
			$stg_obj->bezeichnung_arr['English'] = $row->english;

			$this->result[] = $stg_obj;
			$this->kuerzel_arr[$row->studiengang_kz] = $stg_obj->kuerzel;
		}

		return true;
	}

	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	private function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein.';
			return false;
		}
		if(mb_strlen($this->kurzbz)>5)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 5 Zeichen sein.';
			return false;
		}
		if(mb_strlen($this->kurzbzlang)>10)
		{
			$this->errormsg = 'Kurzbezlang darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->english)>128)
		{
			$this->errormsg = 'english darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'studiengang_kz ungueltig!';
			return false;
		}
		if(isset($this->melde_studiengang_kz) && $this->melde_studiengang_kz != '' && !is_numeric($this->melde_studiengang_kz))
		{
			$this->errormsg = 'melde_studiengang_kz ungueltig!';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @param $new boolean Legt fest ob der Datensatz neu angelegt wird oder nicht
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;

		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
		{
			return false;
		}

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = 'INSERT INTO public.tbl_studiengang (studiengang_kz, kurzbz, kurzbzlang, bezeichnung, english,
				typ, farbe, email, telefon, max_verband, max_semester, max_gruppe, erhalter_kz, bescheid, bescheidbgbl1,
				bescheidbgbl2, bescheidgz, bescheidvom, titelbescheidvom, aktiv, onlinebewerbung, orgform_kurzbz, zusatzinfo_html,
				oe_kurzbz, moodle, sprache, testtool_sprachwahl, studienplaetze, lgartcode, mischform,projektarbeit_note_anzeige,
				melderelevant, foerderrelevant, standort_code, melde_studiengang_kz) VALUES ('.
				$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				$this->db_add_param($this->kurzbz).', '.
				$this->db_add_param($this->kurzbzlang).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->english).', '.
				$this->db_add_param($this->typ).', '.
				$this->db_add_param($this->farbe).', '.
				$this->db_add_param($this->email).', '.
				$this->db_add_param($this->telefon).', '.
				$this->db_add_param($this->max_verband).', '.
				$this->db_add_param($this->max_semester).', '.
				$this->db_add_param($this->max_gruppe).', '.
				$this->db_add_param($this->erhalter_kz).', '.
				$this->db_add_param($this->bescheid).', '.
				$this->db_add_param($this->bescheidbgbl1).', '.
				$this->db_add_param($this->bescheidbgbl2).', '.
				$this->db_add_param($this->bescheidgz).', '.
				$this->db_add_param($this->bescheidvom).', '.
				$this->db_add_param($this->titelbescheidvom).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				$this->db_add_param($this->onlinebewerbung, FHC_BOOLEAN).', '.
				$this->db_add_param($this->orgform_kurzbz).', '.
				$this->db_add_param($this->zusatzinfo_html).', '.
				$this->db_add_param($this->oe_kurzbz).', '.
				$this->db_add_param($this->moodle, FHC_BOOLEAN).', '.
				$this->db_add_param($this->sprache).', '.
				$this->db_add_param($this->testtool_sprachwahl, FHC_BOOLEAN).', '.
				$this->db_add_param($this->studienplaetze).', '.
				$this->db_add_param($this->lgartcode).', '.
				$this->db_add_param($this->mischform, FHC_BOOLEAN).','.
				$this->db_add_param($this->projektarbeit_note_anzeige, FHC_BOOLEAN).','.
				$this->db_add_param($this->melderelevant, FHC_BOOLEAN).','.
				$this->db_add_param($this->foerderrelevant, FHC_BOOLEAN).','.
				$this->db_add_param($this->standort_code).','.
				$this->db_add_param($this->melde_studiengang_kz).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			$qry = 'UPDATE public.tbl_studiengang SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
				'kurzbz='.$this->db_add_param($this->kurzbz).', '.
				'kurzbzlang='.$this->db_add_param($this->kurzbzlang).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'english='.$this->db_add_param($this->english).', '.
				'typ='.$this->db_add_param($this->typ).', '.
				'farbe='.$this->db_add_param($this->farbe).', '.
				'email='.$this->db_add_param($this->email).', '.
				'max_verband='.$this->db_add_param($this->max_verband).', '.
				'max_semester='.$this->db_add_param($this->max_semester).', '.
				'max_gruppe='.$this->db_add_param($this->max_gruppe).', '.
				'erhalter_kz='.$this->db_add_param($this->erhalter_kz).', '.
				'bescheid='.$this->db_add_param($this->bescheid).', '.
				'bescheidbgbl1='.$this->db_add_param($this->bescheidbgbl1).', '.
				'bescheidbgbl2='.$this->db_add_param($this->bescheidbgbl2).', '.
				'bescheidgz='.$this->db_add_param($this->bescheidgz).', '.
				'bescheidvom='.$this->db_add_param($this->bescheidvom).', '.
				'titelbescheidvom='.$this->db_add_param($this->titelbescheidvom).', '.
				'telefon='.$this->db_add_param($this->telefon).', '.
				'orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				'onlinebewerbung='.$this->db_add_param($this->onlinebewerbung, FHC_BOOLEAN).', '.
				'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).','.
				'zusatzinfo_html='.$this->db_add_param($this->zusatzinfo_html).', '.
				'moodle='.$this->db_add_param($this->moodle, FHC_BOOLEAN).', '.
				'projektarbeit_note_anzeige='.$this->db_add_param($this->projektarbeit_note_anzeige, FHC_BOOLEAN).', '.
				'sprache='.$this->db_add_param($this->sprache).', '.
				'testtool_sprachwahl='.$this->db_add_param($this->testtool_sprachwahl, FHC_BOOLEAN).', '.
				'studienplaetze='.$this->db_add_param($this->studienplaetze).', '.
				'lgartcode='.$this->db_add_param($this->lgartcode).', '.
				'mischform='.$this->db_add_param($this->mischform, FHC_BOOLEAN).', '.
				'melderelevant='.$this->db_add_param($this->melderelevant, FHC_BOOLEAN).', '.
				'foerderrelevant='.$this->db_add_param($this->foerderrelevant, FHC_BOOLEAN).', '.
				'standort_code='.$this->db_add_param($this->standort_code).', '.
				'melde_studiengang_kz='.$this->db_add_param($this->melde_studiengang_kz).' '.
				'WHERE studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER, false).';';
		}

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

	/**
	 * Setzt Studiengaenge aktiv/inaktiv
	 * benoetigt studiengang_kz und 'on'/'off'
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function toggleAktiv($studiengang_kz)
	{
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "UPDATE public.tbl_studiengang SET aktiv = NOT aktiv WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

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

	/**
	 * Liefert die UIDs der Studiengangsleiter
	 *
	 * @param $studiengang_kz wenn gesetzt werden die Leiter dieses Studienganges geliefert
	 * 						  wenn null werden alle Stgl zurueckgeliefert
	 */
	public function getLeitung($studiengang_kz=null)
	{
		$stgl = array();

		$qry = "SELECT
					uid
				FROM
					public.tbl_benutzerfunktion
					JOIN public.tbl_studiengang USING(oe_kurzbz)
				WHERE
					funktion_kurzbz='Leitung' AND
					(datum_von is null OR datum_von<=now()) AND
					(datum_bis is null OR datum_bis>=now())";

		if(!is_null($studiengang_kz))
			$qry.=" AND studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$stgl[] = $row->uid;
			}
			return $stgl;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Studiengangsleiter';
			return false;
		}
	}

	/**
	 * Laedt einen Studiengang anhand seiner Organisationseinheit
	 * @param $oe_kurzbz
	 * @return boolean
	 */
	public function getStudiengangFromOe($oe_kurzbz)
	{
		$qry ="SELECT * FROM public.tbl_studiengang WHERE oe_kurzbz =".$this->db_add_param($oe_kurzbz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->studiengang_kz = $row->studiengang_kz;
				$this->kurzbz = $row->kurzbz;
				$this->kurzbzlang = $row->kurzbzlang;
				$this->bezeichnung = $row->bezeichnung;
				$this->english = $row->english;
				$this->typ = $row->typ;
				$this->farbe = $row->farbe;
				$this->email = $row->email;
				$this->max_semester = $row->max_semester;
				$this->max_verband = $row->max_verband;
				$this->max_gruppe = $row->max_gruppe;
				$this->erhalter_kz = $row->erhalter_kz;
				$this->bescheid = $row->bescheid;
				$this->bescheidbgbl1 = $row->bescheidbgbl1;
				$this->bescheidbgbl2 = $row->bescheidbgbl2;
				$this->bescheidgz = $row->bescheidgz;
				$this->bescheidvom = $row->bescheidvom;
				$this->ext_id = $row->ext_id;
				$this->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$this->orgform_kurzbz = $row->orgform_kurzbz;
				$this->zusatzinfo_html = $row->zusatzinfo_html;
				$this->sprache = $row->sprache;
				$this->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$this->studienplaetze = $row->studienplaetze;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->lgartcode = $row->lgartcode;
				$this->telefon = $row->telefon;
				$this->titelbescheidvom = $row->titelbescheidvom;
				$this->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$this->moodle = $this->db_parse_bool($row->moodle);
				$this->mischform = $this->db_parse_bool($row->mischform);
				$this->melderelevant = $this->db_parse_bool($row->melderelevant);
				$this->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$this->standort_code = $row->standort_code;
				$this->melde_studiengang_kz = $row->melde_studiengang_kz;
				$this->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);

				$this->bezeichnung_arr['German'] = $this->bezeichnung;
				$this->bezeichnung_arr['English'] = $this->english;
				return true;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage aufgetreten.";
			return false;
		}
	}


	/**
	 * @return Array mit allen Semestern des Studienganges
	 */
	public function getSemesterFromStudiengang($studiengang_kz)
	{
		$qry = "SELECT DISTINCT semester from lehre.tbl_lehrveranstaltung where studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." order by semester asc;";
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		$result = array();
		while ($row = $this->db_fetch_object()) {
			$result[]= $row->semester;
		}
		return $result;
	}

	/**
	 * Laedt einen Studiengangstyp
	 *
	 * @param $typ Studiengangstyp
	 * @return boolean true wenn erfolgreich geladen, false im Fehlerfall
	 */
	public function getStudiengangTyp($typ)
	{
		$qry = "SELECT * FROM public.tbl_studiengangstyp WHERE typ =".$this->db_add_param($typ,FHC_STRING).";";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->typ = $row->typ;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
			}

			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * @param $studiengaenge array
	 * @return array|bool
	 */
	 public function getTypes($studiengaenge) {

		$qry = 'SELECT distinct typ ' .
			'FROM public.tbl_studiengang ' .
			'WHERE studiengang_kz IN (' . implode(',', $studiengaenge) . ')';

		$types = array();

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result)) {

				$types[] = $row->typ;

			}

			return $types;
		}
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten";
			return false;
		}
	}

	/**
	 * Sucht nach einem Studiengang
	 * @param type $searchItem
	 * @return boolean true, wenn ok; false, im Fehlerfall
	 */
	public function search($searchItem)
	{
		$qry = 'SELECT * FROM public.tbl_studiengang WHERE
				LOWER(bezeichnung) LIKE LOWER(\'%'.$this->db_escape((implode(' ',$searchItem))).'%\') OR
				LOWER(english) LIKE LOWER(\'%'.$this->db_escape((implode(' ',$searchItem))).'%\')
				ORDER BY typ,bezeichnung;';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studiengang();

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->kurzbz = $row->kurzbz;
				$obj->kurzbzlang = $row->kurzbzlang;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->english = $row->english;
				$obj->typ = $row->typ;
				$obj->farbe = $row->farbe;
				$obj->email = $row->email;
				$obj->max_semester = $row->max_semester;
				$obj->max_verband = $row->max_verband;
				$obj->max_gruppe = $row->max_gruppe;
				$obj->erhalter_kz = $row->erhalter_kz;
				$obj->bescheid = $row->bescheid;
				$obj->bescheidbgbl1 = $row->bescheidbgbl1;
				$obj->bescheidbgbl2 = $row->bescheidbgbl2;
				$obj->bescheidgz = $row->bescheidgz;
				$obj->bescheidvom = $row->bescheidvom;
				$obj->ext_id = $row->ext_id;
				$obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->zusatzinfo_html = $row->zusatzinfo_html;
				$obj->sprache = $row->sprache;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->studienplaetze = $row->studienplaetze;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->lgartcode = $row->lgartcode;
				$obj->telefon = $row->telefon;
				$obj->titelbescheidvom = $row->titelbescheidvom;
				$obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$obj->moodle = $this->db_parse_bool($row->moodle);
				$obj->mischform = $this->db_parse_bool($row->mischform);
				$obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
				$obj->melderelevant = $this->db_parse_bool($row->melderelevant);
				$obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$obj->standort_code = $row->standort_code;
				$obj->melde_studiengang_kz = $row->melde_studiengang_kz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Studiengangs';
			return false;
		}
	}

	/**
	 * Laedt alle Studiengaenge in einer Organisationseinheit
	 * @param varchar $oe_kurzbz Organisationseinheit
	 * @param boolean $aktiv Wenn true dann werden nur aktive Studiengaenge geliefert
	 * @return boolean
	 */
	public function getStudiengaengeFromOe($oe_kurzbz, $aktiv=true)
	{
		$qry ="WITH RECURSIVE oes(oe_kurzbz) as
			   (
					SELECT oe_kurzbz FROM public.tbl_organisationseinheit WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz)."
					UNION
					SELECT tbl_organisationseinheit.oe_kurzbz FROM public.tbl_organisationseinheit JOIN oes ON(oe_parent_kurzbz=oes.oe_kurzbz)
				)
				SELECT * FROM public.tbl_studiengang JOIN oes USING(oe_kurzbz) WHERE oe_kurzbz = oes.oe_kurzbz";

		if($aktiv===true)
		{
			$qry.=" AND tbl_studiengang.aktiv";
		}

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studiengang();

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->kurzbz = $row->kurzbz;
				$obj->kurzbzlang = $row->kurzbzlang;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->english = $row->english;
				$obj->typ = $row->typ;
				$obj->farbe = $row->farbe;
				$obj->email = $row->email;
				$obj->max_semester = $row->max_semester;
				$obj->max_verband = $row->max_verband;
				$obj->max_gruppe = $row->max_gruppe;
				$obj->erhalter_kz = $row->erhalter_kz;
				$obj->bescheid = $row->bescheid;
				$obj->bescheidbgbl1 = $row->bescheidbgbl1;
				$obj->bescheidbgbl2 = $row->bescheidbgbl2;
				$obj->bescheidgz = $row->bescheidgz;
				$obj->bescheidvom = $row->bescheidvom;
				$obj->ext_id = $row->ext_id;
				$obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->zusatzinfo_html = $row->zusatzinfo_html;
				$obj->sprache = $row->sprache;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->studienplaetze = $row->studienplaetze;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->lgartcode = $row->lgartcode;
				$obj->telefon = $row->telefon;
				$obj->titelbescheidvom = $row->titelbescheidvom;
				$obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$obj->moodle = $this->db_parse_bool($row->moodle);
				$obj->mischform = $this->db_parse_bool($row->mischform);
				$obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
				$obj->melderelevant = $this->db_parse_bool($row->melderelevant);
				$obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$obj->standort_code = $row->standort_code;

				$obj->bezeichnung_arr['German'] = $obj->bezeichnung;
				$obj->bezeichnung_arr['English'] = $obj->english;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage aufgetreten.";
			return false;
		}
	}

	/**
	 * Laedt die Lehrgangstypen
	 * @return boolean true wenn ok sonst false
	 */
	public function getLehrgangstyp()
	{
		$qry = "SELECT * FROM bis.tbl_lgartcode";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new stdClass();

				$obj->lgartcode = $row->lgartcode;
				$obj->kurzbz = $row->kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beantragung = $this->db_parse_bool($row->beantragung);
				$obj->lgart_biscode = $row->lgart_biscode;

				$this->result[]= $obj;
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
	 * Laedt die Studiengänge die innerhalb eines Studiensemesters gültig sind
	 * @param $studiensemester_kurzbz
	 */
	public function loadStudiengangFromStudiensemester($studiensemester_kurzbz)
	{
		$qry = "SELECT
					distinct tbl_studiengang.*
				FROM
					public.tbl_studiengang
					JOIN lehre.tbl_studienordnung USING(studiengang_kz)
					JOIN lehre.tbl_studienplan USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
				WHERE
					tbl_studienplan_semester.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
				ORDER BY
					typ, kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studiengang();

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->kurzbz = $row->kurzbz;
				$obj->kurzbzlang = $row->kurzbzlang;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->english = $row->english;
				$obj->typ = $row->typ;
				$obj->farbe = $row->farbe;
				$obj->email = $row->email;
				$obj->max_semester = $row->max_semester;
				$obj->max_verband = $row->max_verband;
				$obj->max_gruppe = $row->max_gruppe;
				$obj->erhalter_kz = $row->erhalter_kz;
				$obj->bescheid = $row->bescheid;
				$obj->bescheidbgbl1 = $row->bescheidbgbl1;
				$obj->bescheidbgbl2 = $row->bescheidbgbl2;
				$obj->bescheidgz = $row->bescheidgz;
				$obj->bescheidvom = $row->bescheidvom;
				$obj->ext_id = $row->ext_id;
				$obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->zusatzinfo_html = $row->zusatzinfo_html;
				$obj->sprache = $row->sprache;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->studienplaetze = $row->studienplaetze;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->lgartcode = $row->lgartcode;
				$obj->telefon = $row->telefon;
				$obj->titelbescheidvom = $row->titelbescheidvom;
				$obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$obj->moodle = $this->db_parse_bool($row->moodle);
				$obj->mischform = $this->db_parse_bool($row->mischform);
				$obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
				$obj->melderelevant = $this->db_parse_bool($row->melderelevant);
				$obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$obj->standort_code = $row->standort_code;
				$obj->melde_studiengang_kz = $row->melde_studiengang_kz;

				$obj->bezeichnung_arr['German'] = $obj->bezeichnung;
				$obj->bezeichnung_arr['English'] = $obj->english;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage aufgetreten.";
			return false;
		}
	}


	/**
	 * Laedt einen Lehrgangstyp anhand des lgartcodes
	 * @param integer $lgartcode
	 * @return boolean true wenn ok sonst false
	 */
	public function loadLehrgangstyp($lgartcode)
	{
		if(!is_numeric($lgartcode))
		{
			$this->errormsg = 'Lgartcode muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM bis.tbl_lgartcode WHERE lgartcode=".$this->db_add_param($lgartcode);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lgartcode = $row->lgartcode;
			$this->kurzbz = $row->kurzbz;
			$this->bezeichnung = $row->bezeichnung;
			$this->beantragung = $this->db_parse_bool($row->beantragung);
			$this->lgart_biscode = $row->lgart_biscode;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		return true;
	}

	/**
	 * Prueft ob ein Studiengang in einem Studiensemester mehrere Studienplaene mit unterschiedlichen Orgformen hat
	 * @param $studiengang_kz integer Kennzahl des Studiengangs.
	 * @param $studiensemester_kurzbz varchar Studiensemester (optional).
	 * @return true wenn mehrere Orgformen vorhanden sind, false wenn nicht.
	 */
	public function isMischform($studiengang_kz, $studiensemester_kurzbz = null)
	{
		$qry = "SELECT distinct orgform_kurzbz
				FROM
					lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz);

		if(!is_null($studiensemester_kurzbz))
			$qry .= " AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>1)
				return true;
			else
				return false;
		}
	}

	/**
	 * Laedt die Studiengänge die vom übergeben Typ sind
	 * @param string $typ
	 */
	public function loadStudiengaengeFromTyp($typ)
	{
		$qry = "SELECT
					distinct tbl_studiengang.*
				FROM
					public.tbl_studiengang
				WHERE
					tbl_studiengang.typ=".$this->db_add_param($typ)."
				ORDER BY
					kurzbz";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new studiengang();

				$obj->studiengang_kz = $row->studiengang_kz;
				$obj->kurzbz = $row->kurzbz;
				$obj->kurzbzlang = $row->kurzbzlang;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->english = $row->english;
				$obj->typ = $row->typ;
				$obj->farbe = $row->farbe;
				$obj->email = $row->email;
				$obj->max_semester = $row->max_semester;
				$obj->max_verband = $row->max_verband;
				$obj->max_gruppe = $row->max_gruppe;
				$obj->erhalter_kz = $row->erhalter_kz;
				$obj->bescheid = $row->bescheid;
				$obj->bescheidbgbl1 = $row->bescheidbgbl1;
				$obj->bescheidbgbl2 = $row->bescheidbgbl2;
				$obj->bescheidgz = $row->bescheidgz;
				$obj->bescheidvom = $row->bescheidvom;
				$obj->ext_id = $row->ext_id;
				$obj->kuerzel = mb_strtoupper($row->typ . $row->kurzbz);
				$obj->orgform_kurzbz = $row->orgform_kurzbz;
				$obj->zusatzinfo_html = $row->zusatzinfo_html;
				$obj->sprache = $row->sprache;
				$obj->testtool_sprachwahl = $this->db_parse_bool($row->testtool_sprachwahl);
				$obj->studienplaetze = $row->studienplaetze;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->lgartcode = $row->lgartcode;
				$obj->telefon = $row->telefon;
				$obj->titelbescheidvom = $row->titelbescheidvom;
				$obj->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				$obj->moodle = $this->db_parse_bool($row->moodle);
				$obj->mischform = $this->db_parse_bool($row->mischform);
				$obj->projektarbeit_note_anzeige = $this->db_parse_bool($row->projektarbeit_note_anzeige);
				$obj->melderelevant = $this->db_parse_bool($row->melderelevant);
				$obj->foerderrelevant = $this->db_parse_bool($row->foerderrelevant);
				$obj->standort_code = $row->standort_code;
				$obj->melde_studiengang_kz = $row->melde_studiengang_kz;

				$obj->bezeichnung_arr['German'] = $obj->bezeichnung;
				$obj->bezeichnung_arr['English'] = $obj->english;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage aufgetreten.";
			return false;
		}
	}
}
