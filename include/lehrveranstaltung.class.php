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

class lehrveranstaltung extends basis_db
{
	public $conn;					// resource DB-Handle
	public $errormsg;				// string
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
	public $zeugnis=true;			// boolean
	public $projektarbeit;			// boolean
	public $koordinator;			// varchar(16)
	public $bezeichnung_english;	// varchar(256)
	public $orgform_kurzbz;
	
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
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$lehrveranstaltung_id';";

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
			$this->lehre=($row->lehre=='t'?true:false);
			$this->lehreverzeichnis=$row->lehreverzeichnis;
			$this->aktiv=($row->aktiv=='t'?true:false);
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
			$this->zeugnis=($row->zeugnis=='t'?true:false);
			$this->projektarbeit=($row->projektarbeit=='t'?true:false);
			$this->koordinator=$row->koordinator;
			$this->bezeichnung_english = $row->bezeichnung_english;
			$this->orgform_kurzbz = $row->orgform_kurzbz;
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
			$lv_obj->lehre=($row->lehre=='t'?true:false);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=($row->aktiv=='t'?true:false);
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
			$lv_obj->zeugnis=($row->zeugnis=='t'?true:false);
			$lv_obj->projektarbeit=($row->projektarbeit=='t'?true:false);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

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
		if($semester!=null && (!is_numeric($semester) || $semester==''))
		{
			$this->errormsg = 'Semester muss eine gueltige Zahl sein';
			return false;
		}
		if($lehre!=null && !is_bool($lehre))
		{
			$this->errormsg = 'Lehre muss ein boolscher Wert sein';
			return false;
		}

		//Select Befehl zusammenbauen
		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz = '$studiengang_kz'";

		if($lehreverzeichnis!=null)
			$qry .= " AND lehreverzeichnis='$lehreverzeichnis'";
		if($semester != null)
			$qry .= " AND semester='$semester'";
		if($lehre!=null)
			$qry .= " AND lehre=".($lehre?'true':'false');
		if($aktiv!=null)
			if ($aktiv)
				$qry .= " AND aktiv";

		$qry .= " AND semester is not null AND lehreverzeichnis<>''";

		if ($sort == "bezeichnung")
			$qry .= " ORDER BY bezeichnung";
		else
			$qry .= " ORDER BY semester, bezeichnung";

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
			$lv_obj->lehre=($row->lehre=='t'?true:false);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=($row->aktiv=='t'?true:false);
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
			$lv_obj->zeugnis=($row->zeugnis=='t'?true:false);
			$lv_obj->projektarbeit=($row->projektarbeit=='t'?true:false);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

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
											  WHERE uid='".addslashes($student_uid)."')
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
			$lv_obj->lehre=($row->lehre=='t'?true:false);
			$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
			$lv_obj->aktiv=($row->aktiv=='t'?true:false);
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
			$lv_obj->zeugnis=($row->zeugnis=='t'?true:false);
			$lv_obj->projektarbeit=($row->projektarbeit=='t'?true:false);
			$lv_obj->koordinator=$row->koordinator;
			$lv_obj->bezeichnung_english = $row->bezeichnung_english;
			$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

			$this->lehrveranstaltungen[] = $lv_obj;
		}

		return true;
	}
	
	/**
	 * Prueft die Gueltigkeit der Variablen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function validates()
	{
		//Laenge Pruefen
		if(strlen($this->bezeichnung)>128)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		if(strlen($this->kurzbz)>16)
		{
			$this->errormsg = 'Kurzbez darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->anmerkung)>64)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(strlen($this->lehreverzeichnis)>16)
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
		if($this->semesterstunden!='' && !is_numeric($this->semesterstunden))
		{
			$this->errormsg = 'Semesterstunden ist ungueltig';
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
				insertvon, planfaktor, planlektoren, planpersonalkosten, plankostenprolektor, updateamum, updatevon, sort,zeugnis, projektarbeit, sprache, koordinator, bezeichnung_english, orgform_kurzbz) VALUES ('.
				$this->addslashes($this->studiengang_kz).', '.
				$this->addslashes($this->bezeichnung).', '.
				$this->addslashes($this->kurzbz).', ';
			if ($this->lehrform_kurzbz=='NULL')
				$qry.= 'NULL, ';
			else
				$qry.= $this->addslashes($this->lehrform_kurzbz).', ';
			$qry.= $this->addslashes($this->semester).', '.
				$this->addslashes($this->ects).', '.
				$this->addslashes($this->semesterstunden).', '.
				$this->addslashes($this->anmerkung).', '.
				($this->lehre?'true':'false').','.
				$this->addslashes($this->lehreverzeichnis).', '.
				($this->aktiv?'true':'false').', '.
				$this->addslashes($this->ext_id).', '.
				$this->addslashes($this->insertamum).', '.
				$this->addslashes($this->insertvon).', '.
				$this->addslashes($this->planfaktor).', '.
				$this->addslashes($this->planlektoren).', '.
				$this->addslashes($this->planpersonalkosten).', '.
				$this->addslashes($this->plankostenprolektor).', '.
				$this->addslashes($this->updateamum).', '.
				$this->addslashes($this->updatevon).','.
				$this->addslashes($this->sort).','.
				($this->zeugnis?'true':'false').','.
				($this->projektarbeit?'true':'false').','.
				$this->addslashes($this->sprache).','.
				$this->addslashes($this->koordinator).','.
				$this->addslashes($this->bezeichnung_english).','.
				$this->addslashes($this->orgform_kurzbz).');';
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
				//'lehrveranstaltung_id= '.$this->addslashes($this->lehrveranstaltung_id) .', '.
				'studiengang_kz='.$this->addslashes($this->studiengang_kz) .', '.
				'bezeichnung='.$this->addslashes($this->bezeichnung) .', '.
				'kurzbz='.$this->addslashes($this->kurzbz) .', '.
				'lehrform_kurzbz=';
			if ($this->lehrform_kurzbz=='NULL')
				$qry.= 'NULL, ';
			else
				$qry.=$this->addslashes($this->lehrform_kurzbz) .', ';
			$qry.= 'semester='.$this->addslashes($this->semester) .', '.
				'ects='.$this->addslashes($this->ects) .', '.
				'semesterstunden='.$this->addslashes($this->semesterstunden) .', '.
				'anmerkung='.$this->addslashes($this->anmerkung) .', '.
				'lehre='.($this->lehre?'true':'false') .', '.
				'lehreverzeichnis='.$this->addslashes($this->lehreverzeichnis) .', '.
				'aktiv='.($this->aktiv?'true':'false') .', '.
				'ext_id='.$this->addslashes($this->ext_id) .', '.
				'insertamum='.$this->addslashes($this->insertamum) .', '.
				'insertvon='.$this->addslashes($this->insertvon) .', '.
				'planfaktor='.$this->addslashes($this->planfaktor) .', '.
				'planlektoren='.$this->addslashes($this->planlektoren) .', '.
				'planpersonalkosten='.$this->addslashes($this->planpersonalkosten) .', '.
				'plankostenprolektor='.$this->addslashes($this->plankostenprolektor) .', '.
				'updateamum='.$this->addslashes($this->updateamum) .','.
				'updatevon='.$this->addslashes($this->updatevon) .','.
				'sort='.$this->addslashes($this->sort) .','.
				'zeugnis='.($this->zeugnis?'true':'false').','.
				'projektarbeit='.($this->projektarbeit?'true':'false').','.
				'koordinator='.$this->addslashes($this->koordinator).','.
				'sprache='.$this->addslashes($this->sprache).','.
				'bezeichnung_english='.$this->addslashes($this->bezeichnung_english).','.
				'orgform_kurzbz='.$this->addslashes($this->orgform_kurzbz).' '.
				'WHERE lehrveranstaltung_id = '.$this->addslashes($this->lehrveranstaltung_id).';';
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
			$qry.="tbl_lehrveranstaltung.studiengang_kz='".addslashes($studiengang_kz)."' AND ";

		$qry.= "tbl_lehrveranstaltung.lehrveranstaltung_id = tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheitmitarbeiter.lehreinheit_id = tbl_lehreinheit.lehreinheit_id AND
				tbl_lehreinheit.studiensemester_kurzbz = '".addslashes($studiensemester_kurzbz)."' AND
				tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".addslashes($uid)."';";
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
				$lv_obj->lehre=($row->lehre=='t'?true:false);
				$lv_obj->lehreverzeichnis=$row->lehreverzeichnis;
				$lv_obj->aktiv=($row->aktiv=='t'?true:false);
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
				$lv_obj->zeugnis=($row->zeugnis=='t'?true:false);
				$lv_obj->projektarbeit=($row->projektarbeit=='t'?true:false);
				$lv_obj->zeugnis=$row->koordinator;
				$lv_obj->bezeichnung_english = $row->bezeichnung_english;
				$lv_obj->orgform_kurzbz = $row->orgform_kurzbz;

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
				$l->zeugnis = ($row->zeugnis=='t'?true:false);
				$l->projektarbeit = ($row->projektarbeit=='t'?true:false);
				$l->koordinator = $row->koordinator;
				$l->bezeichnung_english = $row->bezeichnung_english;
				$l->orgform_kurzbz = $row->orgform_kurzbz;
				
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
}
?>