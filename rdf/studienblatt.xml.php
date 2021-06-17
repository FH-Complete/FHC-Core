<?php
/* Copyright (C) 2013 FH fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *		  Karl Burkhart <burkhart@technikum-wien.at>
 *		  Manfred Kindl <kindlm@technikum-wien.at>
 */
header("Content-type: application/xhtml+xml");
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/studiengang.class.php');
require_once('../include/student.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/adresse.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/akadgrad.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/nation.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/organisationsform.class.php');
require_once('../include/zgv.class.php');
require_once('../include/konto.class.php');
require_once('../include/geschlecht.class.php');

$uid_arr = (isset($_REQUEST['uid'])?$_REQUEST['uid']:null);
$studiensemester = (isset($_REQUEST['ss'])?$_REQUEST['ss']:null);

$uid_arr = explode(";",$uid_arr);

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n";
echo "<studienblaetter>\n";

$uid = isset($uid_arr[1])?$uid_arr[1]:$uid_arr[0];

$konto =  new konto();
$student_help = new student();
// an 2ter stelle da im Aufruf vom FAS ;<uid>; der erste immer '' ist
if($student_help->load($uid))
{
	$studiengang = new studiengang();
	$studiengang->load($student_help->studiengang_kz);
	switch($studiengang->typ)
	{
		case 'b':
			$studTyp = 'Bachelor';
			$titel_kurzbz = 'BSc';
			break;
		case 'm':
			$studTyp = 'Master';
			$titel_kurzbz ='MSc';
			break;
		case 'd':
			$studTyp = 'Diplom';
			break;
		case 'l':
			$studTyp = 'Lehrgang';
			break;
		case 'k':
			$studTyp = 'Kurzstudium';
			break;
		default:
			$studTyp ='';
			$titel_kurzbz = '';
	}
	echo "\t<studiengang_typ>".$studTyp."</studiengang_typ>\n";
	echo "\t<studiengang><![CDATA[".$studiengang->bezeichnung."]]></studiengang>\n";
}

foreach($uid_arr as $uid)
{
	if($uid=='')
		continue;

	echo "\t<studienblatt>\n";

	$student = new student();
	if($student->load($uid))
	{
			$datum_aktuell = date('d.m.Y');
			$gebdatum = date('d.m.Y',strtotime($student->gebdatum));
			$prestudent = new prestudent($student->prestudent_id);
			$prestudent->getLastStatus($student->prestudent_id,$studiensemester);
			$studienordnung = new studienordnung();
			$studienordnung->getStudienordnungFromStudienplan($prestudent->studienplan_id);
			$studiengang = new studiengang();
			$studiengang->load($studienordnung->studiengang_kz);
			$studienplan = new studienplan();
			$studienplan->loadStudienplan($prestudent->studienplan_id);
			$staatsbuergerschaft = new nation();
			$staatsbuergerschaft->load($student->staatsbuergerschaft);


			$svnr = ($student->svnr == '')?'Ersatzkennzeichen: '.$student->ersatzkennzeichen:$student->svnr;

			$geschlecht_obj = new geschlecht();
			$geschlecht_obj->load($student->geschlecht);

			$geschlecht = $geschlecht_obj->bezeichnung_mehrsprachig_arr[DEFAULT_LANGUAGE];
			$geschlecht_englisch = $geschlecht_obj->bezeichnung_mehrsprachig_arr['English'];

			//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
			if ($studienordnung->studiengang_kz<0)
			{
				$stg = new studiengang();
				$stg->load($studienordnung->studiengang_kz);

				$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($studienordnung->studiengang_kz));
			}
			else
				$studiengang_kz = sprintf("%04s", abs($studienordnung->studiengang_kz));

			$sprache_obj = new sprache();
			$sprache_obj->load($studienplan->sprache);
			$studienplan_sprache = $sprache_obj->bezeichnung_arr[DEFAULT_LANGUAGE];
			$studienplan_sprache_englisch = $sprache_obj->bezeichnung_arr['English'];

			echo "\t\t<quote>1</quote>\n";
			echo "\t\t<personenkz><![CDATA[".$uid."]]></personenkz>\n";
			echo "\t\t<geschlecht><![CDATA[".$geschlecht."]]></geschlecht>\n";
			echo "\t\t<geschlecht_englisch><![CDATA[".$geschlecht_englisch."]]></geschlecht_englisch>\n";
			echo "\t\t<anrede><![CDATA[".$student->anrede."]]></anrede>\n";
			echo "\t\t<vorname><![CDATA[".$student->vorname." ".$student->vornamen."]]></vorname>\n";
			echo "\t\t<vornamen><![CDATA[".$student->vornamen."]]></vornamen>\n";
			echo "\t\t<nachname><![CDATA[".$student->nachname."]]></nachname>\n";
			echo "\t\t<titelpre><![CDATA[".$student->titelpre."]]></titelpre>\n";
			echo "\t\t<titelpost><![CDATA[".$student->titelpost."]]></titelpost>\n";
			echo "\t\t<gebdatum><![CDATA[".$gebdatum."]]></gebdatum>\n";
			echo "\t\t<gebort><![CDATA[".$student->gebort."]]></gebort>\n";
			echo "\t\t<staatsbuergerschaft><![CDATA[".$staatsbuergerschaft->langtext."]]></staatsbuergerschaft>\n";
			echo "\t\t<svnr><![CDATA[".$svnr."]]></svnr>\n";
			echo "\t\t<matr_nr><![CDATA[".trim($student->matr_nr)."]]></matr_nr>\n";
			echo "\t\t<matrikelnr><![CDATA[".trim($student->matrikelnr)."]]></matrikelnr>\n";
			echo "\t\t<studiengang><![CDATA[".$studienordnung->studiengangbezeichnung."]]></studiengang>\n";
			echo "\t\t<studiengang_englisch><![CDATA[".$studienordnung->studiengangbezeichnung_englisch."]]></studiengang_englisch>\n";
			echo "\t\t<studiengang_kurzbz><![CDATA[".$studienordnung->studiengangkurzbzlang."]]></studiengang_kurzbz>\n";
			echo "\t\t<studiengang_kz><![CDATA[".$studiengang_kz."]]></studiengang_kz>\n";
			echo "\t\t<studiengangSprache><![CDATA[".$studienplan_sprache."]]></studiengangSprache>";
			echo "\t\t<studiengangSpracheEnglisch><![CDATA[".$studienplan_sprache_englisch."]]></studiengangSpracheEnglisch>";
			echo "\t\t<ects_gesamt><![CDATA[".$studienordnung->ects."]]></ects_gesamt>";
			echo "\t\t<ects_pro_semester><![CDATA[".($studienplan->regelstudiendauer!=0?$studienordnung->ects/$studienplan->regelstudiendauer:0)."]]></ects_pro_semester>";

			echo "\t\t<aktuellesJahr><![CDATA[".date('Y')."]]></aktuellesJahr>";
			$status_aktuell = ($prestudent->getLastStatus($student->prestudent_id,null,null))?$prestudent->status_kurzbz:'';
			$abbrecher = false;

			switch($status_aktuell)
			{
				case 'Student':
					$studierendenstatus_aktuell = 'Aktive/r StudentIn';
					break;
				case 'Unterbrecher':
					$studierendenstatus_aktuell = 'UnterbrecherIn';
					break;
				case 'Absolvent':
					$studierendenstatus_aktuell = 'AbsolventIn';
					break;
				case 'Diplomand':
					$studierendenstatus_aktuell = 'DiplomandIn';
					break;
				case 'Abbrecher':
					$studierendenstatus_aktuell = 'AbbrecherIn';
					$enddatum = date('d.m.Y',strtotime($prestudent->bestaetigtam));
					$abbrecher = true;
					break;
				default:
					$studierendenstatus_aktuell ='';
			}
		echo "\t\t<abbrecher><![CDATA[".($abbrecher?'true':'false')."]]></abbrecher>";

		echo "\t\t<ausbildungssemester_aktuell><![CDATA[".$prestudent->ausbildungssemester."]]></ausbildungssemester_aktuell>";

		$studiensemester_aktuell = new studiensemester();
		$studiensemester_aktuell->load($studiensemester);

		echo "\t\t<studiensemester_aktuell><![CDATA[".$studiensemester_aktuell->bezeichnung."]]></studiensemester_aktuell>";

		// check ob Oeh-Beitrag bezahlt wurde
		$oehbeitrag = $konto->getOehBeitragGesamt($uid, $studiensemester_aktuell->studiensemester_kurzbz);
		echo "\t\t<oehbeitrag><![CDATA[".str_replace('.', ',', $oehbeitrag)."]]></oehbeitrag>";

		// check ob Quereinsteiger
		$ausbildungssemester = ($prestudent->getFirstStatus($student->prestudent_id, 'Student'))?$prestudent->ausbildungssemester:'';
		echo "\t\t<semesterStudent><![CDATA[".$ausbildungssemester."]]></semesterStudent>";

		$studiensemester_beginn = new studiensemester();
		$studienbeginn = ($prestudent->getFirstStatus($student->prestudent_id, 'Student'))?$prestudent->studiensemester_kurzbz:'';
		$studiensemester_beginn->load($studienbeginn);

		echo "\t\t<studiensemester_beginn><![CDATA[".$studiensemester_beginn->bezeichnung."]]></studiensemester_beginn>";
		echo "\t\t<studiensemester_beginndatum><![CDATA[".date('d.m.Y',strtotime($studiensemester_beginn->start))."]]></studiensemester_beginndatum>";

		$prestudent->getLastStatus($student->prestudent_id,$studiensemester);
		$studiensemester_abschluss = new studiensemester();
		if($abbrecher)
			$abschluss = $prestudent->studiensemester_kurzbz;
		else
			$abschluss = $studiensemester_abschluss->jump($prestudent->studiensemester_kurzbz, $studienplan->regelstudiendauer-$prestudent->ausbildungssemester);
		$studiensemester_abschluss->load($abschluss);
		echo "\t\t<voraussichtlichLetztesStudiensemester><![CDATA[".$studiensemester_abschluss->bezeichnung."]]></voraussichtlichLetztesStudiensemester>";

		$studiensemester_endedatum = new studiensemester();
		$studiensemester_endedatum->load($studiensemester_endedatum->getaktorNext(1));

		$status_aktuell = ($prestudent->getLastStatus($student->prestudent_id,null,null))?$prestudent->status_kurzbz:'';

		$enddatum = date('d.m.Y',strtotime($studiensemester_abschluss->ende));

		echo "\t\t<studiensemester_endedatum><![CDATA[".date('d.m.Y',strtotime($studiensemester_endedatum->ende))."]]></studiensemester_endedatum>";

		if($abbrecher)
			echo "\t\t<voraussichtlichLetztesStudiensemester_datum><![CDATA[".date('d.m.Y',strtotime($prestudent->datum))."]]></voraussichtlichLetztesStudiensemester_datum>";
		else
			echo "\t\t<voraussichtlichLetztesStudiensemester_datum><![CDATA[".$enddatum."]]></voraussichtlichLetztesStudiensemester_datum>";

		echo "\t\t<studierendenstatus_aktuell><![CDATA[".$studierendenstatus_aktuell."]]></studierendenstatus_aktuell>\n";
		echo "\t\t<datum_reifepruefung><![CDATA[".$prestudent->zgvdatum."]]></datum_reifepruefung>\n";
		$zgv = new zgv($prestudent->zgv_code);
		echo "\t\t<schulform_zgv><![CDATA[".$zgv->zgv_kurzbz."]]></schulform_zgv>\n";
		echo "\t\t<studienplan_bezeichnung><![CDATA[".$studienplan->bezeichnung."]]></studienplan_bezeichnung>\n";
		echo "\t\t<anmerkungpre><![CDATA[$prestudent->anmerkung]]></anmerkungpre>\n";

		$titel_kurzbz = '';
			switch($studiengang->typ)
			{
				case 'b':
					$studTyp = 'Bachelor';
					$titel_kurzbz = 'BSc';
					break;
				case 'm':
					$studTyp = 'Master';
					$titel_kurzbz ='MSc';
					break;
				case 'd':
					$studTyp = 'Diplom';
					break;
				case 'l':
					$studTyp = 'Lehrgang';
		   			break;
				case 'k':
					$studTyp = 'Kurzstudium';
					break;
				default:
					$studTyp ='';
					$titel_kurzbz = '';
			}

			echo "\t\t<titel_kurzbz><![CDATA[".$titel_kurzbz."]]></titel_kurzbz>\n";
			echo "\t\t<studiengang_typ><![CDATA[".$studTyp."]]></studiengang_typ>\n";
			echo "\t\t<studienplan_sprache><![CDATA[".$studienplan->sprache."]]></studienplan_sprache>\n";
			echo "\t\t<regelstudiendauer><![CDATA[".$studienplan->regelstudiendauer."]]></regelstudiendauer>\n";

			$akadgrad = new akadgrad();
			$akadgrad->getAkadgradStudent($student->uid);

			echo "\t\t<akadgrad><![CDATA[".$akadgrad->titel."]]></akadgrad>\n";
			echo "\t\t<akadgrad_kurzbz><![CDATA[".$akadgrad->akadgrad_kurzbz."]]></akadgrad_kurzbz>\n";

			//für ao. Studierende wird die StgKz der Lehrveranstaltungen benötigt, die sie besuchen
			$lv_studiengang_kz='';
			$lv_studiengang_bezeichnung='';
			$lv_studiengang_typ='';

			$stg_typ=new studiengang();
			$lv=new lehrveranstaltung();
			$lv->load_lva_student($student->uid);
			if(count($lv->lehrveranstaltungen)>0)
			{
				$lv_studiengang_kz=$lv->lehrveranstaltungen[0]->studiengang_kz;
				$lv_studiengang=new studiengang();
				$lv_studiengang->load($lv_studiengang_kz);
				$lv_studiengang_bezeichnung=$lv_studiengang->bezeichnung;
				$stg_typ->getStudiengangTyp($lv_studiengang->typ);
				$lv_studiengang_typ=$stg_typ->bezeichnung;
			}

			echo "\t\t<lv_studiengang_kz><![CDATA[".sprintf('%04s', $lv_studiengang_kz)."]]></lv_studiengang_kz>";
			echo "\t\t<lv_studiengang_typ><![CDATA[$lv_studiengang_typ]]></lv_studiengang_typ>";
			echo "\t\t<lv_studiengang_bezeichnung><![CDATA[$lv_studiengang_bezeichnung]]></lv_studiengang_bezeichnung>";

			echo "\t\t<datum_aktuell><![CDATA[".$datum_aktuell."]]></datum_aktuell>\n";

			$adresse = new adresse();
			$adresse->load_pers($student->person_id);

			foreach($adresse->result as $row_adresse)
			{
				if($row_adresse->zustelladresse)
				{
					echo "\t\t<strasse><![CDATA[$row_adresse->strasse]]></strasse>\n";
					echo "\t\t<plz><![CDATA[".$row_adresse->plz." ".$row_adresse->ort."]]></plz>\n";
					echo "\t\t<nation><![CDATA[".$row_adresse->nation."]]></nation>\n";
					break;
				}
			}
			foreach($adresse->result as $row_adresse)
			{
				if($row_adresse->heimatadresse)
				{
					echo "\t\t<heimat_strasse><![CDATA[$row_adresse->strasse]]></heimat_strasse>\n";
					echo "\t\t<heimat_plz><![CDATA[".$row_adresse->plz." ".$row_adresse->ort."]]></heimat_plz>\n";
					echo "\t\t<heimat_nation><![CDATA[".$row_adresse->nation."]]></heimat_nation>\n";
					break;
				}
			}
			$prestudent = new prestudent();
			$prestudent->getLastStatus($student->prestudent_id, null);

			if($prestudent->orgform_kurzbz!='')
				$orgform = $prestudent->orgform_kurzbz;
			else
				$orgform = $studienplan->orgform_kurzbz;

			$orgform_bez = new organisationsform();
			$orgform_bez->load($orgform);

			echo "\t\t<orgform><![CDATA[".$orgform."]]></orgform>\n";
			echo "\t\t<orgform_bezeichnung><![CDATA[".$orgform_bez->bezeichnung."]]></orgform_bezeichnung>\n";

			//Studiengangsleiter auslesen
			$stg_oe_obj = new studiengang($studienordnung->studiengang_kz);
			if ($studienordnung->studiengang_kz=='')
				$stgleiter = $stg_oe_obj->getLeitung($student_help->studiengang_kz);
			else
				$stgleiter = $stg_oe_obj->getLeitung($studienordnung->studiengang_kz);
			$stgl='';
			foreach ($stgleiter as $stgleiter_uid)
			{
				$stgl_ma = new mitarbeiter($stgleiter_uid);
				$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
			}

			echo "\t\t<stgl><![CDATA[$stgl]]></stgl>\n";
	}
	echo "\t</studienblatt>\n";
}
echo "</studienblaetter>";

?>
