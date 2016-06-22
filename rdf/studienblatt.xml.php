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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Karl Burkhart <burkhart@technikum-wien.at>,
 *          Manfred Kindl <manfred.kindl@technikum-wien.at> and
 *          Andreas Moik <moik@technikum-wien.at>.
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

$pid_arr = (isset($_REQUEST['prestudent_id'])?$_REQUEST['prestudent_id']:null);
$studiensemester = (isset($_REQUEST['ss'])?$_REQUEST['ss']:null);

$pid_arr = explode(";",$pid_arr);

echo "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>\n"; 
echo "<studienblaetter>\n";

$pid = isset($pid_arr[1])?$pid_arr[1]:$pid_arr[0];

$konto =  new konto();
$ps_help = new prestudent();
// an 2ter stelle da im Aufruf vom FAS ;<uid>; der erste immer '' ist
if($ps_help->load($pid))
{
    $studiengang = new studiengang();
    $studiengang->load($ps_help->studiengang_kz);
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
    echo "\t<studiengang>".$studiengang->bezeichnung."</studiengang>\n";
}

foreach($pid_arr as $pid)
{
	if(!is_numeric($pid))
		continue;

	echo "\t<studienblatt>\n";

	$ps = new prestudent();
	if($ps->load($pid))
	{
			$datum_aktuell = date('d.m.Y');
			$gebdatum = date('d.m.Y',strtotime($ps->gebdatum));
			$prestudent = new prestudent($ps->prestudent_id);
			$prestudent->getLastStatus($ps->prestudent_id,$studiensemester,'Student');
			$studienordnung = new studienordnung();
			$studienordnung->getStudienordnungFromStudienplan($prestudent->studienplan_id);
			$studiengang = new studiengang();
			$studiengang->load($studienordnung->studiengang_kz);
			$studienplan = new studienplan();
			$studienplan->loadStudienplan($prestudent->studienplan_id);
			$staatsbuergerschaft = new nation();
			$staatsbuergerschaft->load($ps->staatsbuergerschaft);
			
			
            $svnr = ($ps->svnr == '')?'Ersatzkennzeichen: '.$ps->ersatzkennzeichen:$ps->svnr;
			
			switch($ps->geschlecht)
            {
                case 'm':
                    $geschlecht = 'Männlich'; 
                    break;
                case 'w': 
                    $geschlecht = 'Weiblich'; 
                    break; 
                case 'u':
                    $geschlecht = 'Unbekannt'; 
                    break; 
                default: 
                    $geschlecht =''; 
            }
            
            //Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
            if ($studienordnung->studiengang_kz<0)
            {
            	$stg = new studiengang();
            	$stg->load($studienordnung->studiengang_kz);
            
            	$studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($studienordnung->studiengang_kz));
            }
            else
            	$studiengang_kz = sprintf("%04s", abs($studienordnung->studiengang_kz));
            
			echo "\t\t<quote>1</quote>\n"; // TODO EINE ergibt das einen sinn?
			echo "\t\t<personenkz>".$ps->uid."</personenkz>\n";// TODO EINE ergibt das einen sinn?
			echo "\t\t<geschlecht>".$geschlecht."</geschlecht>\n";
			echo "\t\t<anrede>".$ps->anrede."</anrede>\n";
			echo "\t\t<vorname>".$ps->vorname." ".$ps->vornamen."</vorname>\n";
			echo "\t\t<vornamen>".$ps->vornamen."</vornamen>\n";
			echo "\t\t<nachname>".$ps->nachname."</nachname>\n";
			echo "\t\t<titelpre>".$ps->titelpre."</titelpre>\n";
			echo "\t\t<titelpost>".$ps->titelpost."</titelpost>\n";
			echo "\t\t<gebdatum>".$gebdatum."</gebdatum>\n";
			echo "\t\t<gebort>".$ps->gebort."</gebort>\n";
			echo "\t\t<staatsbuergerschaft>".$staatsbuergerschaft->langtext."</staatsbuergerschaft>\n";
			echo "\t\t<svnr>".$svnr."</svnr>\n";
			echo "\t\t<matrikelnr>".trim($ps->perskz)."</matrikelnr>\n";
			echo "\t\t<studiengang>".$studienordnung->studiengangbezeichnung."</studiengang>\n";
			echo "\t\t<studiengang_englisch>".$studienordnung->studiengangbezeichnung_englisch."</studiengang_englisch>\n";
            echo "\t\t<studiengang_kurzbz>".$studienordnung->studiengangkurzbzlang."</studiengang_kurzbz>\n";
			echo "\t\t<studiengang_kz>".$studiengang_kz."</studiengang_kz>\n";
            echo "\t\t<studiengangSprache>".$studienplan->sprache."</studiengangSprache>"; 
            echo "\t\t<ects_gesamt>".$studienordnung->ects."</ects_gesamt>"; 
            echo "\t\t<ects_pro_semester>".($studienplan->regelstudiendauer!=0?$studienordnung->ects/$studienplan->regelstudiendauer:0)."</ects_pro_semester>";
            
            echo "\t\t<aktuellesJahr>".date('Y')."</aktuellesJahr>"; 
            
            echo "\t\t<ausbildungssemester_aktuell>".$prestudent->ausbildungssemester."</ausbildungssemester_aktuell>";
            
            $studiensemester_aktuell = new studiensemester();
            $studiensemester_aktuell->load($studiensemester);
            
            echo "\t\t<studiensemester_aktuell>".$studiensemester_aktuell->bezeichnung."</studiensemester_aktuell>";
			
			// check ob Oeh-Beitrag bezahlt wurde
			$oehbeitrag = $konto->getOehBeitragGesamt($ps->uid, $studiensemester_aktuell->studiensemester_kurzbz);
			echo "\t\t<oehbeitrag>".str_replace('.', ',', $oehbeitrag)."</oehbeitrag>";
            
            // check ob Quereinsteiger
            $ausbildungssemester = ($prestudent->getFirstStatus($ps->prestudent_id, 'Student'))?$prestudent->ausbildungssemester:'';
            echo "\t\t<semesterStudent>".$ausbildungssemester."</semesterStudent>";
            
            $studiensemester_beginn = new studiensemester();
            $studienbeginn = ($prestudent->getFirstStatus($ps->prestudent_id, 'Student'))?$prestudent->studiensemester_kurzbz:'';
            $studiensemester_beginn->load($studienbeginn);
            
            echo "\t\t<studiensemester_beginn>".$studiensemester_beginn->bezeichnung."</studiensemester_beginn>";
            echo "\t\t<studiensemester_beginndatum>".date('d.m.Y',strtotime($studiensemester_beginn->start))."</studiensemester_beginndatum>";
	
            $prestudent->getLastStatus($ps->prestudent_id,$studiensemester,'Student');
            $studiensemester_abschluss = new studiensemester();
            $abschluss = $studiensemester_abschluss->jump($prestudent->studiensemester_kurzbz, $studienplan->regelstudiendauer-$prestudent->ausbildungssemester);
            $studiensemester_abschluss->load($abschluss);
            echo "\t\t<voraussichtlichLetztesStudiensemester>".$studiensemester_abschluss->bezeichnung."</voraussichtlichLetztesStudiensemester>";
            echo "\t\t<voraussichtlichLetztesStudiensemester_datum>".date('d.m.Y',strtotime($studiensemester_abschluss->ende))."</voraussichtlichLetztesStudiensemester_datum>";
            
            $studiensemester_endedatum = new studiensemester();
            $studiensemester_endedatum->load($studiensemester_endedatum->getaktorNext(1));
            
            echo "\t\t<studiensemester_endedatum>".date('d.m.Y',strtotime($studiensemester_endedatum->ende))."</studiensemester_endedatum>";
            
            $status_aktuell = ($prestudent->getLastStatus($ps->prestudent_id,null,null))?$prestudent->status_kurzbz:'';
            
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
                    break;
                default: 
                    $studierendenstatus_aktuell =''; 
            }
            
            echo "\t\t<studierendenstatus_aktuell>".$studierendenstatus_aktuell."</studierendenstatus_aktuell>\n";
	    echo "\t\t<datum_reifepruefung>".$prestudent->zgvdatum."</datum_reifepruefung>\n";
	    $zgv = new zgv($prestudent->zgv_code);
	    echo "\t\t<schulform_zgv>".$zgv->zgv_kurzbz."</schulform_zgv>\n";
	    echo "\t\t<studienplan_bezeichnung>".$studienplan->bezeichnung."</studienplan_bezeichnung>\n";
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
            
            echo "\t\t<titel_kurzbz>".$titel_kurzbz."</titel_kurzbz>\n"; 
			echo "\t\t<studiengang_typ>".$studTyp."</studiengang_typ>\n";
			echo "\t\t<studienplan_sprache>".$studienplan->sprache."</studienplan_sprache>\n";
			echo "\t\t<regelstudiendauer>".$studienplan->regelstudiendauer."</regelstudiendauer>\n";
			
			$akadgrad = new akadgrad();
			$akadgrad->getAkadgradStudent($ps->prestudent_id);
			
			echo "\t\t<akadgrad>".$akadgrad->titel."</akadgrad>\n";
			echo "\t\t<akadgrad_kurzbz>".$akadgrad->akadgrad_kurzbz."</akadgrad_kurzbz>\n";
			
			//für ao. Studierende wird die StgKz der Lehrveranstaltungen benötigt, die sie besuchen
			$lv_studiengang_kz='';
			$lv_studiengang_bezeichnung='';
			$lv_studiengang_typ='';

			$stg_typ=new studiengang();
			$lv=new lehrveranstaltung();
			$lv->load_lva_student($ps->prestudent_id);
			if(count($lv->lehrveranstaltungen)>0)
			{
				$lv_studiengang_kz=$lv->lehrveranstaltungen[0]->studiengang_kz;
				$lv_studiengang=new studiengang();
				$lv_studiengang->load($lv_studiengang_kz);
				$lv_studiengang_bezeichnung=$lv_studiengang->bezeichnung;
	            $stg_typ->getStudiengangTyp($lv_studiengang->typ); 
				$lv_studiengang_typ=$stg_typ->bezeichnung;
			}
			
			echo "\t\t<lv_studiengang_kz>".sprintf('%04s', $lv_studiengang_kz)."</lv_studiengang_kz>";
			echo "\t\t<lv_studiengang_typ>$lv_studiengang_typ</lv_studiengang_typ>";
			echo "\t\t<lv_studiengang_bezeichnung>$lv_studiengang_bezeichnung</lv_studiengang_bezeichnung>";
			
			echo "\t\t<datum_aktuell>".$datum_aktuell."</datum_aktuell>\n";

			$adresse = new adresse();
			$adresse->load_pers($ps->person_id);
			
			foreach($adresse->result as $row_adresse)
			{
				if($row_adresse->zustelladresse)
				{
					echo "\t\t<strasse><![CDATA[$row_adresse->strasse]]></strasse>\n";
					echo "\t\t<plz>".$row_adresse->plz." ".$row_adresse->ort."</plz>\n";
					echo "\t\t<nation>".$row_adresse->nation."</nation>\n";
					break;
				}
			}
			foreach($adresse->result as $row_adresse)
			{
				if($row_adresse->heimatadresse)
				{
					echo "\t\t<heimat_strasse><![CDATA[$row_adresse->strasse]]></heimat_strasse>\n";
					echo "\t\t<heimat_plz>".$row_adresse->plz." ".$row_adresse->ort."</heimat_plz>\n";
					echo "\t\t<heimat_nation>".$row_adresse->nation."</heimat_nation>\n";
					break;
				}
			}
			$prestudent = new prestudent();
			$prestudent->getLastStatus($ps->prestudent_id, null, 'Student');
			
			if($prestudent->orgform_kurzbz!='')
				$orgform = $prestudent->orgform_kurzbz;
			else
				$orgform = $studienplan->orgform_kurzbz;
				
			$orgform_bez = new organisationsform();
			$orgform_bez->load($orgform);
			
			echo "\t\t<orgform>".$orgform."</orgform>\n";
			echo "\t\t<orgform_bezeichnung>".$orgform_bez->bezeichnung."</orgform_bezeichnung>\n";
			
			//Studiengangsleiter auslesen
			$stg_oe_obj = new studiengang($studienordnung->studiengang_kz);
			if ($studienordnung->studiengang_kz=='')
				$stgleiter = $stg_oe_obj->getLeitung($ps_help->studiengang_kz);
			else
				$stgleiter = $stg_oe_obj->getLeitung($studienordnung->studiengang_kz);
			$stgl='';
			foreach ($stgleiter as $stgleiter_uid)
			{
				$stgl_ma = new mitarbeiter($stgleiter_uid);
				$stgl .= trim($stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
			}
			
			echo "\t\t<stgl>$stgl</stgl>\n";
	} 
	echo "\t</studienblatt>\n";
}
echo "</studienblaetter>"; 

?>
