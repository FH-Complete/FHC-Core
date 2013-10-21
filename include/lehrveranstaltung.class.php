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
require_once(dirname(__FILE__).'/functions.inc.php');

class lehrveranstaltung extends basis_db
{
	public $new;					// boolean
	public $lehrveranstaltungen = array();	//  lehrveranstaltung Objekt

	public $lehrveranstaltung_id;	// serial
	public $studiengang_kz;			// integer
	public $bezeichnung;   			// string
	public $kurzbz;   				// string
	public $lehrform_kurzbz; 	  	// string
	public $semester;  		 		// smallint
	public $ects;   				// numeric(5,2)
	public $semesterstunden;   		// smallint

	public $anmerkung;				// string
	public $lehre=true;				// boolean
	public $lehreverzeichnis;		// string
	public $aktiv=true;				// boolean
	public $ext_id;					// bigint
	public $insertamum;				// timestamp
	public $insertvon;				// string
	public $planfaktor;				// numeric(3,2)
	public $planlektoren;			// integer
	public $planpersonalkosten;		// numeric(7,2)
	public $plankostenprolektor;	// numeric(6,2)
	public $updateamum;				// timestamp
	public $updatevon;				// string
	public $sprache='German';		// varchar(16)
	public $sort;					// smallint
	public $incoming=5;				// smallint
	public $zeugnis=true;			// boolean
	public $projektarbeit;			// boolean
	public $koordinator;			// varchar(16)
	public $bezeichnung_english;	// varchar(256)
	public $orgform_kurzbz;
	public $bezeichnung_arr = array();

	public $studienplan_lehrveranstaltung_id;
	public $studienplan_lehrveranstaltung_id_parent;
	public $stpllv_pflicht=true;
	public $stpllv_koordinator;
	public $stpllv_semester;

	/**
	 * Konstruktor
	 * @param $lehrveranstaltung_id ID der zu ladenden Lehrveranstaltung
	 */
	public function __construct($lehrveranstaltung_id=null)
	{
		parent::__construct();
		
		if(!is_null($lehrveranstaltung_id))
			$this->load($lehrveranstaltung_id);
	}

	/**
	 * Laedt einen Datensatz
	 * @param $lehrveranstaltung_id  ID des zu ladenden Datensatzes
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($lehrveranstaltung_id)
	{
		if(!is_numeric($lehrveranstaltung_id))
		{
			$this->errormsg = 'Lehrveranstaltung_id muss eine gueltige Zahl sein';
			return false;
		}
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$this->studiengang_kz=$row->studiengang_kz;
			$this->bezeichnung=$row->bezeichnung;
			$this->kurzbz=$row->kurzbz;
			$this->lehrform_kurzbz=$row->lehrform_kurzbz;
			$this->semester=$row->semester;
			$this->ects=$row->ects;
			$this->semesterstunden=$row->semesterstunden;
			$this->anmerkung=$row->anmerkung;
			$this->lehre=$this->db_parse_bool($row->lehre);
			$this->lehreverzeichnis=$row->lehreverzeichnis;
			$this->aktiv=$this->db_parse_bool($row->aktiv);
			$this->ext_id=$row->ext_id;
			$this->insertamum=$row->insertamum;
			$this->insertvon=$row->insertvon;
			$this->planfaktor=$row->planfaktor;
			$this->planlektoren=$row->planlektoren;
			$this->planpersonalkosten=$row->planpersonalkosten;
			$this->plankostenprolektor=$row->plankostenprolektor;
			$this->updateamum=$row->updateamum;
			$this->updatevon=$row->updatevon;
			$this->sprache=$row->sprache;
			$this->sort=$row->sort;
			$this->incoming=$row->incoming;
			$this->zeugnis=$this->db_parse_bool($row->zeugnis);
			$this->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$this->koordinator=$row->koordinator;
			$this->bezeichnung_english = $row->bezeichnung_english;
			$this->orgform_kurzbz = $row->orgform_kurzbz;
			
			$this->bezeichnung_arr['German']=$this->bezeichnung;
			$this->bezeichnung_arr['English']=$this->bezeichnung_english;
			if($this->bezeichnung_arr['English']=='')
				$this->bezeichnung_arr['English']=$this->bezeichnung_arr['German'];
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

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=$this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			$lv_obj->sort=$row->sort;
			$lv_obj->incoming=$row->incoming;
			$lv_obj->zeugnis=$this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

			$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
			$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
			if($lv_obj->bezeichnung_arr['English']=='')
				$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
			
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
	public function load_lva($studiengang_kz, $semester=null, $lehreverzeichnis=null, $lehre=null, $aktiv=null, $sort=null)	 
	{
		//Variablen pruefen

		if(!is_numeric($studiengang_kz) || $studiengang_kz=='')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($semester) && (!is_numeric($semester) && $semester!=''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($aktiv) && !is_bool($aktiv))
		{
			$this->errormsg = 'Aktivkz muss ein boolscher Wert sein';
			return false;
		}
		if(!is_null($lehre) && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung where studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		//Select Befehl zusammenbauen
		if(!is_null($lehreverzeichnis))
			$qry .= " AND lehreverzeichnis=".$this->db_add_param($lehreverzeichnis);
		else
			$qry .= " AND lehreverzeichnis<>'' ";
					
		if(!is_null($semester) && $semester!='')
			$qry .= " AND semester=".$this->db_add_param($semester, FHC_INTEGER);
		else
			$qry .= " AND semester is not null ";
					
		if(!is_null($lehre))
			$qry .= " AND lehre=".($lehre?'true':'false');

		if(!is_null($aktiv) && $aktiv)
				$qry .= " AND aktiv ";

		if(!is_null($lehre) && $lehre)
				$qry .= " AND lehre ";
		
		if ($sort == "bezeichnung")
			$qry .= " ORDER BY bezeichnung";
		elseif (is_null($sort) || empty($sort))
			$qry .= " ORDER BY semester, bezeichnung";
		else
			$qry .= " ORDER BY $sort ";

		//Datensaetze laden
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=$this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			$lv_obj->sort=$row->sort;
			$lv_obj->incoming=$row->incoming;
			$lv_obj->zeugnis=$this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

			$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
			$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
			if($lv_obj->bezeichnung_arr['English']=='')
				$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
				
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
	public function load_lva_le($studiengang_kz, $studiensemester_kurzbz=null, $semester=null, $lehreverzeichnis=null, $lehre=null, $aktiv=null, $sort=null)	 
	{
		//Variablen pruefen

		if(!is_numeric($studiengang_kz) || $studiengang_kz==='')
		{
			$this->errormsg = 'studiengang_kz muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($semester) && (!is_numeric($semester) && $semester!=''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if(!is_null($aktiv) && !is_bool($aktiv))
		{
			$this->errormsg = 'Aktiv muss ein boolscher Wert sein';
			return false;
		}
		if(!is_null($lehre) && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}
		
		$qry = "SELECT distinct lehre.tbl_lehrveranstaltung.*, tbl_lehreinheit.studiensemester_kurzbz FROM lehre.tbl_lehrveranstaltung,lehre.tbl_lehreinheit  where tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id and studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		//Select Befehl zusammenbauen
		if(!is_null($lehreverzeichnis))
			$qry .= " AND lehreverzeichnis=".$this->db_add_param($lehreverzeichnis);
		else
			$qry .= " AND lehreverzeichnis<>'' ";
					
		if(!is_null($semester) && $semester!='')
			$qry .= " AND semester=".$this->db_add_param($semester);
		else
			$qry .= " AND semester is not null ";

		if(!is_null($studiensemester_kurzbz) && $studiensemester_kurzbz!='')
			$qry .= " AND tbl_lehreinheit.studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

			
		if(!is_null($lehre))
			$qry .= " AND lehre=".($lehre?'true':'false');

		if(!is_null($aktiv) && $aktiv)
				$qry .= " AND aktiv ";

		if(!is_null($lehre) && $lehre)
				$qry .= " AND lehre ";
		
		if ($sort == "bezeichnung")
			$qry .= " ORDER BY bezeichnung";
		elseif (is_null($sort) || empty($sort))
			$qry .= " ORDER BY semester, bezeichnung";
		else
			$qry .= " ORDER BY $sort ";

		//Datensaetze laden
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=$this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			$lv_obj->sort=$row->sort;
			$lv_obj->incoming=$row->incoming;
			$lv_obj->zeugnis=$this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;
			
			$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
			$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
			if($lv_obj->bezeichnung_arr['English']=='')
				$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
			
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
	public function load_lva_student($student_uid)
	{
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung 
				WHERE lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM campus.vw_student_lehrveranstaltung 
											  WHERE uid=".$this->db_add_param($student_uid).")
						OR lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM lehre.tbl_zeugnisnote WHERE student_uid=".$this->db_add_param($student_uid).")
				ORDER BY semester, bezeichnung";
		
		//Datensaetze laden
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}
		while($row = $this->db_fetch_object())
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=$this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			$lv_obj->sort=$row->sort;
			$lv_obj->incoming=$row->incoming;
			$lv_obj->zeugnis=$this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

			$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
			$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
			if($lv_obj->bezeichnung_arr['English']=='')
				$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
			
			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validate()
	{
		//Laenge Pruefen
		if(mb_strlen($this->bezeichnung)>128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kurzbz)>16)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->lehreverzeichnis)>16)
		{
			$this->errormsg = 'Lehreverzeichnis darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		if($this->semester!='' && !is_numeric($this->semester))
		{
			$this->errormsg = 'Semester ist ungueltig';
			return false;
		}
		if($this->planfaktor!='' && !is_numeric($this->planfaktor))
		{
			$this->errormsg = 'Planfaktor ist ungueltig';
			return false;
		}
		if($this->planlektoren!='' && !is_numeric($this->planlektoren))
		{
			$this->errormsg = 'Planlektoren ist ungueltig';
			return false;
		}
		if($this->ects!='' && !is_numeric($this->ects))
		{
			$this->errormsg = 'ECTS sind ungueltig';
			return false;
		}
		if($this->ects>40)
		{
			$this->errormsg = 'ECTS darf nicht groesser als 40 sein';
			return false;
		}
		if($this->semesterstunden!='' && !isint($this->semesterstunden))
		{
			$this->errormsg = 'Semesterstunden muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if($this->sort!='' && !isint($this->sort))
		{
			$this->errormsg = 'Sort muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		if($this->incoming!='' && !isint($this->incoming))
		{
			$this->errormsg = 'Sort muss ein eine gueltige ganze Zahl sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new = $this->new;

		//Gueltigkeit der Variablen pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz anlegen
			$qry = 'BEGIN; INSERT INTO lehre.tbl_lehrveranstaltung (studiengang_kz, bezeichnung, kurzbz, lehrform_kurzbz,
				semester, ects, semesterstunden,  anmerkung, lehre, lehreverzeichnis, aktiv, ext_id, insertamum,
				insertvon, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor, updateamum, updatevon, sort,zeugnis, projektarbeit, sprache, koordinator, bezeichnung_english, orgform_kurzbz,incoming) VALUES ('.
				$this->db_add_param($this->studiengang_kz).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->kurzbz).', '.
				$this->db_add_param($this->lehrform_kurzbz).', '.
				$this->db_add_param($this->semester).', '.
				$this->db_add_param($this->ects).', '.
				$this->db_add_param($this->semesterstunden).', '.
				$this->db_add_param($this->anmerkung).', '.
				$this->db_add_param($this->lehre, FHC_BOOLEAN).','.
				$this->db_add_param($this->lehreverzeichnis).', '.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				$this->db_add_param($this->ext_id).', '.
				$this->db_add_param($this->insertamum).', '.
				$this->db_add_param($this->insertvon).', '.
				$this->db_add_param($this->planfaktor).', '.
				$this->db_add_param($this->planlektoren).', '.
				$this->db_add_param($this->planpersonalkosten).', '.
				$this->db_add_param($this->plankostenprolektor).', '.
				$this->db_add_param($this->updateamum).', '.
				$this->db_add_param($this->updatevon).','.
				$this->db_add_param($this->sort).','.
				$this->db_add_param($this->zeugnis, FHC_BOOLEAN).','.
				$this->db_add_param($this->projektarbeit, FHC_BOOLEAN).','.
				$this->db_add_param($this->sprache).','.
				$this->db_add_param($this->koordinator).','.
				$this->db_add_param($this->bezeichnung_english).','.
				$this->db_add_param($this->orgform_kurzbz).','.
				$this->db_add_param($this->incoming).');';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob lehrveranstaltung_id eine gueltige Zahl ist
			if(!is_numeric($this->lehrveranstaltung_id) || $this->lehrveranstaltung_id == '')
			{
				$this->errormsg = 'lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE lehre.tbl_lehrveranstaltung SET '.
				'studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER) .', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung) .', '.
				'kurzbz='.$this->db_add_param($this->kurzbz) .', '.
				'lehrform_kurzbz='.$this->db_add_param($this->lehrform_kurzbz) .', '.
				'semester='.$this->db_add_param($this->semester, FHC_INTEGER) .', '.
				'ects='.$this->db_add_param($this->ects) .', '.
				'semesterstunden='.$this->db_add_param($this->semesterstunden, FHC_INTEGER) .', '.
				'anmerkung='.$this->db_add_param($this->anmerkung) .', '.
				'lehre='.$this->db_add_param($this->lehre, FHC_BOOLEAN) .', '.
				'lehreverzeichnis='.$this->db_add_param($this->lehreverzeichnis) .', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN) .', '.
				'ext_id='.$this->db_add_param($this->ext_id) .', '.
				'planfaktor='.$this->db_add_param($this->planfaktor) .', '.
				'planlektoren='.$this->db_add_param($this->planlektoren) .', '.
				'planpersonalkosten='.$this->db_add_param($this->planpersonalkosten) .', '.
				'plankostenprolektor='.$this->db_add_param($this->plankostenprolektor) .', '.
				'updateamum='.$this->db_add_param($this->updateamum) .','.
				'updatevon='.$this->db_add_param($this->updatevon) .','.
				'sort='.$this->db_add_param($this->sort) .','.
				'incoming='.$this->db_add_param($this->incoming).','.
				'zeugnis='.$this->db_add_param($this->zeugnis, FHC_BOOLEAN).','.
				'projektarbeit='.$this->db_add_param($this->projektarbeit, FHC_BOOLEAN).','.
				'koordinator='.$this->db_add_param($this->koordinator).','.
				'sprache='.$this->db_add_param($this->sprache).','.
				'bezeichnung_english='.$this->db_add_param($this->bezeichnung_english).','.
				'orgform_kurzbz='.$this->db_add_param($this->orgform_kurzbz).' '.
				'WHERE lehrveranstaltung_id = '.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('lehre.tbl_lehrveranstaltung_lehrveranstaltung_id_seq') as id";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
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
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE ";
		if($studiengang_kz!=0)
			$qry.="tbl_lehrveranstaltung.studiengang_kz=".$this->db_add_param($studiengang_kz)." AND ";

		$qry.= "tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.studiensemester_kurzbz = ".$this->db_add_param($studiensemester_kurzbz)." AND
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$this->db_add_param($uid).";";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$lv_obj = new lehrveranstaltung();

				$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
				$lv_obj->studiengang_kz=$row->studiengang_kz;
				$lv_obj->bezeichnung=$row->bezeichnung;
				$lv_obj->kurzbz=$row->kurzbz;
				$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
				$lv_obj->semester=$row->semester;
				$lv_obj->ects=$row->ects;
				$lv_obj->semesterstunden=$row->semesterstunden;
				$lv_obj->anmerkung=$row->anmerkung;
				$lv_obj->lehre=$this->db_parse_bool($row->lehre);
				$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
				$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
				$lv_obj->ext_id=$row->ext_id;
				$lv_obj->insertamum=$row->insertamum;
				$lv_obj->insertvon=$row->insertvon;
				$lv_obj->planfaktor=$row->planfaktor;
				$lv_obj->planlektoren=$row->planlektoren;
				$lv_obj->planpersonalkosten=$row->planpersonalkosten;
				$lv_obj->plankostenprolektor=$row->plankostenprolektor;
				$lv_obj->updateamum=$row->updateamum;
				$lv_obj->updatevon=$row->updatevon;
				$lv_obj->sprache=$row->sprache;
				$lv_obj->sort=$row->sort;
				$lv_obj->incoming=$row->incoming;
				$lv_obj->zeugnis= $this->db_parse_bool($row->zeugnis);
				$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
				$lv_obj->zeugnis=$row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

				$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
				$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
				if($lv_obj->bezeichnung_arr['English']=='')
					$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
				
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
	 *			$sem Semester
	 *			$order Sortierkriterium
	 * @return array mit Lehrferanstaltungen oder false=fehler
	 */
	public function getTab($stg=null,$sem=null, $order='lehrveranstaltung_id')
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
		$sql_query = "SELECT * FROM lehre.tbl_lehrveranstaltung";

		if($stg!=null || $sem!=null)
		   $sql_query .= " WHERE true";

		if($stg!=null)
		   $sql_query .= " AND studiengang_kz='$stg'";

		if($sem!=null)
			$sql_query .= " AND semester='$sem'";

		$sql_query .= " ORDER BY $order";

		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
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
				
				$l->bezeichnung_arr['German']=$row->bezeichnung;
				$l->bezeichnung_arr['English']=$row->bezeichnung_english;
				if($l->bezeichnung_arr['English']=='')
					$l->bezeichnung_arr['English']=$l->bezeichnung_arr['German'];
					
				$this->lehrveranstaltungen[]=$l;
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
	 * Liefert alle Moodlekurs Ids 
	 * @param 	$lehrveranstaltung_id Id der Lehrveranstaltung
	 * @param	$semester Semester
	 * @return array mit Moodlekurs Ids oder false=fehler
	 */
    public function getMoodleKurse($lehrveranstaltung_id, $semester)
    {
        if($lehrveranstaltung_id == '' || $semester == '')
        {
            $this->errormsg = 'Id und Semester muss übergeben werden.';
            return false; 
        }
        
        $qry = "SELECT mdl_course_id FROM lehre.tbl_moodle 
            WHERE studiensemester_kurzbz = ".$this->db_add_param($semester)." 
                AND (lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id)." 
                    OR lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id = ".$this->db_add_param($lehrveranstaltung_id)."));";
                
        $moodleArray = array(); 
        
        if($result = $this->db_query($qry))
		{
            while($row = $this->db_fetch_object($result))
            {
                $moodleArray[] = $row->mdl_course_id; 
            }
            return $moodleArray; 
            
        }
        else
        {
			$this->errormsg = 'Moodlekurs konnte nicht geladen werden';
			return false;
        }
		
    }
    
	/**
	 * Laedt die LVs die als Array uebergeben werden
	 * @param $ids Array mit den LV ids
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadArray($ids)
	{
		if(count($ids)==0)
			return true;
		
		$ids = $this->db_implode4SQL($ids);
						
		$qry = 'SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id in('.$ids.')';
		$qry .=" ORDER BY bezeichnung";

		if(!$result = $this->db_query($qry))
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		} 

		while($row = $this->db_fetch_object($result))
		{
			$lv_obj = new lehrveranstaltung();

			$lv_obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
			$lv_obj->studiengang_kz=$row->studiengang_kz;
			$lv_obj->bezeichnung=$row->bezeichnung;
			$lv_obj->kurzbz=$row->kurzbz;
			$lv_obj->lehrform_kurzbz=$row->lehrform_kurzbz;
			$lv_obj->semester=$row->semester;
			$lv_obj->ects=$row->ects;
			$lv_obj->semesterstunden=$row->semesterstunden;
			$lv_obj->anmerkung=$row->anmerkung;
			$lv_obj->lehre=$this->db_parse_bool($row->lehre);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=$this->db_parse_bool($row->aktiv);
			$lv_obj->ext_id=$row->ext_id;
			$lv_obj->insertamum=$row->insertamum;
			$lv_obj->insertvon=$row->insertvon;
			$lv_obj->planfaktor=$row->planfaktor;
			$lv_obj->planlektoren=$row->planlektoren;
			$lv_obj->planpersonalkosten=$row->planpersonalkosten;
			$lv_obj->plankostenprolektor=$row->plankostenprolektor;
			$lv_obj->updateamum=$row->updateamum;
			$lv_obj->updatevon=$row->updatevon;
			$lv_obj->sprache=$row->sprache;
			$lv_obj->sort=$row->sort;
			$lv_obj->incoming=$row->incoming;
			$lv_obj->zeugnis=$this->db_parse_bool($row->zeugnis);
			$lv_obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

			$lv_obj->bezeichnung_arr['German']=$row->bezeichnung;
			$lv_obj->bezeichnung_arr['English']=$row->bezeichnung_english;
			if($lv_obj->bezeichnung_arr['English']=='')
				$lv_obj->bezeichnung_arr['English']=$lv_obj->bezeichnung_arr['German'];
				
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
	public function loadLehrveranstaltungStudienplan($studienplan_id, $semester=null)
	{
		if(!is_numeric($studienplan_id) || $studienplan_id==='')
		{
			$this->errormsg='StudienplanID ist ungueltig';
			return false;
		}

		$qry = "SELECT 
					tbl_lehrveranstaltung.*,
					tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id,
					tbl_studienplan_lehrveranstaltung.semester as stpllv_semester,
					tbl_studienplan_lehrveranstaltung.pflicht as stpllv_pflicht,
					tbl_studienplan_lehrveranstaltung.koordinator as stpllv_koordinator,
					tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent
				FROM 
					lehre.tbl_lehrveranstaltung
					JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
				WHERE
					tbl_studienplan_lehrveranstaltung.studienplan_id=".$this->db_add_param($studienplan_id, FHC_INTEGER);
		if(!is_null($semester))
		{
			$qry.=" AND tbl_studienplan_lehrveranstaltung.semester=".$this->db_add_param($semester, FHC_INTEGER);
		}
		$qry.=" ORDER BY sort";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new lehrveranstaltung();
				
				$obj->lehrveranstaltung_id=$row->lehrveranstaltung_id;
				$obj->studiengang_kz=$row->studiengang_kz;
				$obj->bezeichnung=$row->bezeichnung;
				$obj->kurzbz=$row->kurzbz;
				$obj->lehrform_kurzbz=$row->lehrform_kurzbz;
				$obj->semester=$row->semester;
				$obj->ects=$row->ects;
				$obj->semesterstunden=$row->semesterstunden;
				$obj->anmerkung=$row->anmerkung;
				$obj->lehre=$this->db_parse_bool($row->lehre);
				$obj->lehreverzeichnis=$row->lehreverzeichnis;
				$obj->aktiv=$this->db_parse_bool($row->aktiv);
				$obj->ext_id=$row->ext_id;
				$obj->insertamum=$row->insertamum;
				$obj->insertvon=$row->insertvon;
				$obj->planfaktor=$row->planfaktor;
				$obj->planlektoren=$row->planlektoren;
				$obj->planpersonalkosten=$row->planpersonalkosten;
				$obj->plankostenprolektor=$row->plankostenprolektor;
				$obj->updateamum=$row->updateamum;
				$obj->updatevon=$row->updatevon;
				$obj->sprache=$row->sprache;
				$obj->sort=$row->sort;
				$obj->incoming=$row->incoming;
				$obj->zeugnis=$this->db_parse_bool($row->zeugnis);
				$obj->projektarbeit=$this->db_parse_bool($row->projektarbeit);
				$obj->koordinator=$row->koordinator;
				$obj->bezeichnung_english = $row->bezeichnung_english;
				$obj->orgform_kurzbz = $row->orgform_kurzbz;

				$obj->bezeichnung_arr['German']=$row->bezeichnung;
				$obj->bezeichnung_arr['English']=$row->bezeichnung_english;
				if($obj->bezeichnung_arr['English']=='')
					$obj->bezeichnung_arr['English']=$obj->bezeichnung_arr['German'];
			
				$obj->stpllv_semester = $row->stpllv_semester;
				$obj->stpllv_pflicht = $this->db_parse_bool($row->stpllv_pflicht);
				$obj->stpllv_koordinator = $row->stpllv_koordinator;
				$obj->studienplan_lehrveranstaltung_id = $row->studienplan_lehrveranstaltung_id;
				$obj->studienplan_lehrveranstaltung_id_parent = $row->studienplan_lehrveranstaltung_id_parent;
				$obj->new=false;

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
		$tree=array();
		foreach($this->lehrveranstaltungen as $row)
		{
			if($row->studienplan_lehrveranstaltung_id_parent=='')
			{
				$tree[$row->studienplan_lehrveranstaltung_id]=$row;
				$tree[$row->studienplan_lehrveranstaltung_id]->childs = $this->getLehrveranstaltungTreeChilds($row->studienplan_lehrveranstaltung_id);
			}
		}
		return $tree;
	}

	/**
	 * Generiert die Subtrees des Lehrveranstaltungstrees
	 */
	protected function getLehrveranstaltungTreeChilds($studienplan_lehrveranstaltung_id)
	{
		$childs = array();
		foreach($this->lehrveranstaltungen as $row)
		{
			if($row->studienplan_lehrveranstaltung_id_parent===$studienplan_lehrveranstaltung_id)
			{
				$childs[$row->studienplan_lehrveranstaltung_id]=$row;
				$childs[$row->studienplan_lehrveranstaltung_id]->childs = $this->getLehrveranstaltungTreeChilds($row->studienplan_lehrveranstaltung_id);
			}
		}
		return $childs;
	}

	/**
	 * Speichert die Zuordnung einer Lehrveranstaltung zu einem Studienplan
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveStudienplanLehrveranstaltung()
	{

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO lehre.tbl_studienplan_lehrveranstaltung (studienplan_id, lehrveranstaltung_id,
				semester,studienplan_lehrveranstaltung_id_parent,pflicht, koordinator,
				insertamum, insertvon) VALUES ('.
			      $this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->stpllv_semester, FHC_INTEGER).', '.
			      $this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER).', '.
			      $this->db_add_param($this->stpllv_pflicht, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->stpllv_koordinator).', '.
			      'now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob studienplan_id eine gueltige Zahl ist
			if(!is_numeric($this->studienplan_lehrveranstaltung_id))
			{
				$this->errormsg = 'studienplan_lehrveranstaltung_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE lehre.tbl_studienplan_lehrveranstaltung SET'.
				' studienplan_id='.$this->db_add_param($this->studienplan_id, FHC_INTEGER).', '.
				' lehrveranstaltung_id='.$this->db_add_param($this->lehrveranstaltung_id, FHC_INTEGER).', '.
				' semester='.$this->db_add_param($this->stpllv_semester, FHC_INTEGER).', '.
				' studienplan_lehrveranstaltung_id_parent='.$this->db_add_param($this->studienplan_lehrveranstaltung_id_parent, FHC_INTEGER).', '.
		      	' pflicht='.$this->db_add_param($this->stpllv_pflicht, FHC_BOOLEAN).', '.
		      	' sprache='.$this->db_add_param($this->sprache).', '.
		      	' koordinator='.$this->db_add_param($this->stpllv_koordinator).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE studienplan_lehrveranstaltung_id='.$this->db_add_param($this->studienplan_lehrveranstaltung_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('lehre.seq_studienplan_studienplan_lehrveranstaltung_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->studienplan_lehrveranstaltung_id = $row->id;
						$this->db_query('COMMIT');
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

		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return $this->studienplan_lehrveranstaltung_id;
	}

}
?>
