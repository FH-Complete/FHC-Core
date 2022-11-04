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
 * Authors: Harald Bamberger <harald.bamberger@technikum-wien.at>
 *			Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/lehreinheitgruppe.class.php');
require_once('../../../include/lehreinheit.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/lehreinheitmitarbeiter.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/erhalter.class.php');
require_once('../../../include/datum.class.php');
/**
 * Description of lehrelisthelper
 *
 * @author bambi
 */
class LehreListHelper
{
	protected $db;
	protected $studiensemester;
	protected $lvid;
	protected $lv;
	protected $lehreinheit;
	protected $stg;

	protected $arr_lehrende;
	protected $studentuids;
	protected $data;

	protected $gruppen_string;
	protected $lehrende_string;
	protected $raum_string;

	public function __construct(basis_db $db, $studiensemester, $lvid,
		lehrveranstaltung $lv, studiengang $stg, $lehreinheit='')
	{
		$this->db = $db;
		$this->studiensemester = $studiensemester;
		$this->lvid = $lvid;
		$this->lv = $lv;
		$this->lehreinheit = $lehreinheit;
		$this->stg = $stg;

		$this->arr_lehrende = array();
		$this->studentuids = array();
		$this->data = array();

		$this->gruppen_string = '';
		$this->lehrende_string = '';
		$this->raum_string = '';

		$this->loadMemberGroups();
		$this->loadPlannedRooms();
		$this->initData();
		$this->loadLehrende();
		$this->loadStudierende();
	}

	public function getData()
	{
		return $this->data;
	}

	public function getStudentUids()
	{
		return $this->studentuids;
	}

	public function getArr_Lehrende()
	{
		return $this->arr_lehrende;
	}

	public function getLehrende_String()
	{
		return $this->lehrende_string;
	}

	protected function loadMemberGroups()
	{
		// Teilnehmende Gruppen laden
		$qry = "SELECT DISTINCT ON(kuerzel, semester, verband, gruppe, gruppe_kurzbz)
					UPPER(stg_typ::varchar(1) || stg_kurzbz) as kuerzel,
					semester,
					verband,
					gruppe,
					gruppe_kurzbz
				FROM campus.vw_lehreinheit
				WHERE lehrveranstaltung_id=".$this->db->db_add_param($this->lvid, FHC_INTEGER)."
				AND studiensemester_kurzbz=".$this->db->db_add_param($this->studiensemester);
		if($this->lehreinheit!='')
			$qry.=" AND lehreinheit_id=".$this->db->db_add_param($this->lehreinheit, FHC_INTEGER);

		$this->gruppen_string = '';
		if($result = $this->db->db_query($qry))
		{
			while($row = $this->db->db_fetch_object($result))
			{
				if($this->gruppen_string!='')
					$this->gruppen_string.=', ';
				if($row->gruppe_kurzbz=='')
					$this->gruppen_string.=trim($row->kuerzel.'-'.$row->semester.$row->verband.$row->gruppe);
				else
					$this->gruppen_string.=$row->gruppe_kurzbz;
			}
		}
	}

	protected function loadPlannedRooms()
	{
		// Verplante Räume laden
		$qry = "SELECT distinct(ort_kurzbz)
				FROM lehre.tbl_stundenplan
				WHERE lehreinheit_id in
					(
						SELECT lehreinheit_id
						FROM campus.vw_lehreinheit
						WHERE lehrveranstaltung_id = ".$this->db->db_add_param($this->lvid, FHC_INTEGER)."
						AND studiensemester_kurzbz = ".$this->db->db_add_param($this->studiensemester)."
					)";
		if($this->lehreinheit!='')
			$qry.= " AND tbl_stundenplan.lehreinheit_id = ".$this->db->db_add_param($this->lehreinheit, FHC_INTEGER);


		$this->raum_string = '';
		if($result = $this->db->db_query($qry))
		{
			while($row = $this->db->db_fetch_object($result))
			{
				if($this->raum_string!='')
					$this->raum_string.=', ';
				if($row->ort_kurzbz!='')
					$this->raum_string.=$row->ort_kurzbz;
			}
		}
	}

	protected function initData()
	{
		$studiengang_bezeichnung=$this->stg->bezeichnung;

		$this->stg->getAllTypes();

		$this->data = array(
			'gruppen'=>$this->gruppen_string,
			'bezeichnung'=>$this->lv->bezeichnung,
			'lehrveranstaltung_id'=>$this->lv->lehrveranstaltung_id,
			'studiengang'=>$studiengang_bezeichnung,
			'studiengang_kz'=>$this->lv->studiengang_kz,
			'typ'=>$this->stg->studiengang_typ_arr[$this->stg->typ],
			'ects'=>$this->lv->ects,
			'sprache'=>$this->lv->sprache,
			'studiensemester'=>$this->studiensemester,
			'semester'=>$this->lv->semester,
			'orgform'=>$this->lv->orgform_kurzbz,
			'raum'=>$this->raum_string,
		);
	}

	protected function loadLehrende()
	{
		//Lehrende der LV laden und in ein Array schreiben
		$lehrende = new lehreinheitmitarbeiter();
		$lehrende->getMitarbeiterLV($this->lvid, $this->studiensemester, $this->lehreinheit);
		$this->arr_lehrende = array();
		if (isset($lehrende->result))
		{
			foreach($lehrende->result AS $row)
			{
				$this->data[]=array('lehrende'=>array('uid'=>$row->uid,'name'=>$row->vorname.' '.$row->nachname));
				$this->arr_lehrende[]=mb_strtoupper($row->uid);
				$this->lehrende_string .= (strlen($this->lehrende_string) > 0)
					? ', ' . $row->vorname . ' ' . $row->nachname
					: $row->vorname . ' ' . $row->nachname;
			}
		}
	}

	protected function loadStudierende()
	{
		//Studierende der LV laden und in ein Array schreiben

		$qry = 'SELECT
					distinct on(nachname, vorname, person_id) vorname, nachname, wahlname, matrikelnr, public.tbl_student.student_uid,
					tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
					(SELECT status_kurzbz FROM public.tbl_prestudentstatus
					WHERE prestudent_id=tbl_student.prestudent_id
					ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
					tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis, tbl_student.studiengang_kz AS stg_kz_student,
					tbl_note.lkt_ueberschreibbar, tbl_note.anmerkung, tbl_mitarbeiter.mitarbeiter_uid, tbl_person.matr_nr, tbl_studiengang.kurzbzlang,
					tbl_mobilitaet.mobilitaetstyp_kurzbz, tbl_zeugnisnote.note,
					(CASE WHEN bis.tbl_mobilitaet.studiensemester_kurzbz = vw_student_lehrveranstaltung.studiensemester_kurzbz THEN 1 ELSE 0 END) as doubledegree
				FROM
					campus.vw_student_lehrveranstaltung
					JOIN public.tbl_benutzer USING(uid)
					JOIN public.tbl_person USING(person_id) LEFT JOIN public.tbl_student ON(uid=student_uid)
					LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
					LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
					LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
						AND tbl_zeugnisnote.student_uid=tbl_student.student_uid
						AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
					LEFT JOIN lehre.tbl_note USING (note)
					LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
					LEFT JOIN public.tbl_studiengang ON(tbl_student.studiengang_kz=tbl_studiengang.studiengang_kz)
					LEFT JOIN bis.tbl_mobilitaet USING(prestudent_id)
				WHERE
					vw_student_lehrveranstaltung.lehrveranstaltung_id='.$this->db->db_add_param($this->lvid, FHC_INTEGER).'	AND
					vw_student_lehrveranstaltung.studiensemester_kurzbz='.$this->db->db_add_param($this->studiensemester);


		if($this->lehreinheit!='')
			$qry.=' AND vw_student_lehrveranstaltung.lehreinheit_id='.$this->db->db_add_param($this->lehreinheit, FHC_INTEGER);

		$qry.=' ORDER BY nachname, vorname, person_id, tbl_bisio.bis, doubledegree DESC';

		$stsem_obj = new studiensemester();
		$stsem_obj->load($this->studiensemester);
		$stsemdatumvon = $stsem_obj->start;
		$stsemdatumbis = $stsem_obj->ende;

		$erhalter = new erhalter();
		$erhalter->getAll();

		$a_o_kz = '9'.sprintf("%03s", $erhalter->result[0]->erhalter_kz); //Stg_Kz AO-Studierende auslesen (9005 fuer FHTW)
		$anzahl_studierende = 0;
		$datum = new datum();
		$zusatz = '';

		$this->studentuids = array();
		if($result = $this->db->db_query($qry))
		{
			while($row = $this->db->db_fetch_object($result))
			{
				if($row->status!='Abbrecher' && $row->status!='Unterbrecher')
				{
					$anzahl_studierende++;

					if($row->status=='Incoming') //Incoming
						$zusatz='(i)';
					else
						$zusatz='';

					//Outgoing
					if($row->bisio_id != '' && $row->status != 'Incoming' && ($row->bis > $stsemdatumvon || $row->bis == '')
					&& $row->von < $stsemdatumbis && (anzahlTage($row->von, $row->bis) >= 30))
						$zusatz .= '(o)(ab '.$datum->formatDatum($row->von, 'd.m.Y').')';

					if($row->lkt_ueberschreibbar == 'f') // angerechnet / intern angerechnet / nicht zugelassen
						$zusatz.= '('. $row->anmerkung. ')';

					if($row->mitarbeiter_uid!='') //mitarbeiter
						$zusatz.='(ma)';

					if($row->stg_kz_student==$a_o_kz) //Außerordentliche Studierende
						$zusatz.='(a.o.)';

					if(($row->mobilitaetstyp_kurzbz != '') && ($row->doubledegree == 1)) //Double Degree Student
						$zusatz .= '(d.d.)';

					if(($row->wahlname != ''))
					{
						//als Zusatz speichern
						//$zusatz .= '(Wahlname: ' . $row->wahlname . ')';

						//wenn vorhanden statt Vornamen anzeigen
						$vorname = $row->wahlname;
					}
					else
					{
						$vorname = $row->vorname;
					}


					$this->studentuids[] = $row->student_uid;
					$this->data[]=array('student'=>array(
									'uid' => $row->student_uid,
									'vorname'=>$vorname,
									'nachname'=>$row->nachname,
									'personenkennzeichen'=>trim($row->matrikelnr),
									'matr_nr'=>$row->matr_nr,
									'semester'=>$row->semester,
									'verband'=>trim($row->verband),
									'gruppe'=>trim($row->gruppe),
									'zusatz'=>$zusatz,
									'studiengang_kurzbz'=>$row->kurzbzlang,
									'mobilitaetstyp_kurzbz'=>$row->mobilitaetstyp_kurzbz,
									'note'=>$row->note
									));
				}
			}
			//Anzahl Studierende in Array $data (an erster Stelle) einfuegen
			$this->data = array_reverse($this->data, true);
			$this->data['anzahl_studierende'] = $anzahl_studierende;
			$this->data = array_reverse($this->data, true);
		}
	}
}
