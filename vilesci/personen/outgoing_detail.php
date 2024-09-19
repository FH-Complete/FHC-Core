<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Karl Burkhart 			< burkhart@technikum-wien.at >
 *
 */

// SQL alle preoutgoings die gerade auf Auslandssemester sind
// select * from public.tbl_preoutgoing where dauer_von <= CURRENT_DATE AND dauer_bis >= CURRENT_DATE

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/preoutgoing.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/firma.class.php');
require_once('../../include/mobilitaetsprogramm.class.php');
require_once('../../include/adresse.class.php');
require_once('../../include/nation.class.php');
require_once('../../include/student.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/akte.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/mail.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('inout/outgoing', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$preoutgoing_id = isset($_GET['preoutgoing_id'])?$_GET['preoutgoing_id']:null;
$action = isset($_GET['action'])?$_GET['action']:'personendetails';
$method = isset($_GET['method'])?$_GET['method']:null;

$message = '';
$rechte->getBerechtigungen($user);
$datum = new datum();

if($method== 'deleteLv')
{
    $lv_id = $_GET['lv_id'];
    $preoutgoingLv = new preoutgoing();

    // Wenn die Lv zum preoutgoing gehört wird sie gelöscht

    if($preoutgoingLv->checkLv($lv_id, $preoutgoing_id))
    {
        if(!$preoutgoingLv->deleteLv($lv_id))
            $message ='<span class="error">Fehler beim Löschen der Lehrveranstaltung aufgetreten!</span>';
        else
            $message ='<span class="ok">Erfolgreich gelöscht</span>';
    }
}

// Setzt die gemeinsam ausgewählte Universität
if($method == 'setAuswahl')
{
    $preoutgoing = new preoutgoing();
    $preoutgoing->load($preoutgoing);

    if($preoutgoing->setStatus($preoutgoing_id, 'freigabe'))
    {
        $message = "<span class='ok'>E-Mail an Studenten geschickt</span>";
        sendMailStudent($preoutgoing->uid);
    }
    else
        $message="<span class='error'>Fehler beim Speichern aufgetreten</span>";

    $preoutgoing_firma_id = $_GET['outgoingFirma_id'];
    $preoutgoingFirma = new preoutgoing();
    $preoutgoingFirma->setAuswahlFirmaFalse($preoutgoing_id);
    $preoutgoingFirma->loadFirma($preoutgoing_firma_id);
    $preoutgoingFirma->auswahl = true;
    $preoutgoingFirma->new = false;
    if($preoutgoingFirma->saveFirma())
        $message = $preoutgoingFirma->errormsg;
}

// löscht eine Universität
if($method =="deleteFirma")
{
    if(isset($_GET['outgoingFirma_id']))
    {
        $outgoingFirma_id = $_GET['outgoingFirma_id'];
        $firmaOutgoing = new preoutgoing();
        if(!$firmaOutgoing->deleteFirma($outgoingFirma_id))
            $message = "<span class='error'>Fehler beim Löschen aufgetreten!</span>";
        $message ="<span class='ok'>Erfolgreich gelöscht</span>";
    }
    else
        $message = "<span class='error'>Ungültige Id Übergeben</span>";
}

// Speichert die Daten eines Preoutgoing
if($method=="save")
{
    $outgoing= new preoutgoing();
    $outgoing->load($preoutgoing_id);
    $outgoing->sprachkurs_von = $datum->formatDatum($_REQUEST['sprachkurs_von'], 'Y-m-d');
    $outgoing->sprachkurs_bis = $datum->formatDatum($_REQUEST['sprachkurs_bis'], 'Y-m-d');
    $outgoing->praktikum_von = $datum->formatDatum($_REQUEST['praktikum_von'], 'Y-m-d');
    $outgoing->praktikum_bis = $datum->formatDatum($_REQUEST['praktikum_bis'], 'Y-m-d');
    $outgoing->dauer_von = $datum->formatDatum($_REQUEST['aufenthalt_von'], 'Y-m-d');
    $outgoing->dauer_bis = $datum->formatDatum($_REQUEST['aufenthalt_bis'], 'Y-m-d');
    $outgoing->praktikum = isset($_POST['praktikum'])?true:false;
    $outgoing->bachelorarbeit = isset($_POST['bachelorarbeit'])?true:false;
    $outgoing->masterarbeit = isset($_POST['masterarbeit'])?true:false;
    $outgoing->behinderungszuschuss = isset($_POST['behinderungszuschuss'])?true:false;
    $outgoing->studienbeihilfe = isset($_POST['studienbeihilfe'])?true:false;
    $outgoing->betreuer = $_POST['betreuer_uid'];
    $outgoing->ansprechperson = $_POST['anprechperson_uid'];
    $outgoing->anmerkung_student = $_POST['anmerkungStudent'];
    if($_REQUEST['sprachkurs'] == 'vorbereitend')
    {
        $outgoing->sprachkurs = true;
        $outgoing->intensivsprachkurs = false;
    }
    else if($_REQUEST['sprachkurs']=='intensiv')
    {
        $outgoing->sprachkurs = false;
        $outgoing->intensivsprachkurs = true;
    }
    else
    {
        $outgoing->sprachkurs = false;
        $outgoing->intensivsprachkurs = false;
    }

    if($outgoing->save())
        $message = '<span class="ok">Erfolgreich gespeichert</span>';
    else
        $message = '<span class="error">Es ist ein Fehler beim Speichern aufgetreten</span>';

}

if(isset($_POST['StatusSetzen']))
{
    $status = $_POST['status'];

    $preoutgoing = new preoutgoing();
    $preoutgoing->load($preoutgoing_id);

    // mail an assistenz senden
    if($status =='genehmigt')
    {
        // wenn Student dann Email an zuständige Assistenz
        if(check_student($preoutgoing->uid))
        {
            sendMailAssistenz($preoutgoing->uid);
        }
    }
    $outgoing= new preoutgoing();
    if($outgoing->setStatus($preoutgoing_id, $status))
        $message = '<span class="ok">Erfolgreich gespeichert</span>';
    else
        $message ='<span class="error">Es ist ein Fehler beim Speichern aufgetreten</span>';
}

if(isset($_POST['submit_anmerkung']))
{
    $outgoing = new preoutgoing();
    $outgoing->load($preoutgoing_id);
    $outgoing->anmerkung_admin = $_POST['anmerkungAdmin'];
    if($outgoing->save())
        $message = '<span class="ok">Erfolgreich gespeichert</span>';
    else
        $message = '<span class="error">Es ist ein Fehler beim Speichern aufgetreten</span>';

}

?>

<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Incoming</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
        <link href="../../skin/tablesort.css" rel="stylesheet" type="text/css">
        <link href="../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
        <script src="../../include/js/tablesort/table.js" type="text/javascript"></script>

		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
        <script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>

        <script type="text/javascript">
        $(document).ready(function()
        {
            $( "#datepicker_zeitraumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_zeitraumbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumbis" ).datepicker($.datepicker.regional['de']);

			$('#ansprechperson').autocomplete({
				source: "../../cis/private/outgoing/outgoing_autocomplete.php?autocomplete=mitarbeiter",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value = ui.content[i].nachname + " " + ui.content[i].vorname;
						ui.content[i].label = ui.content[i].nachname + " " + ui.content[i].vorname;
					}
				},
				select: function(event, ui)
				{
					$('#ansprechperson_uid').val(ui.item.uid);
				}
			});

			$('#betreuer').autocomplete({
				source: "../../cis/private/outgoing/outgoing_autocomplete.php?autocomplete=mitarbeiter",
				minLength:2,
				response: function(event, ui)
				{
					//Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value = ui.content[i].nachname + " " + ui.content[i].vorname;
						ui.content[i].label = ui.content[i].nachname + " " + ui.content[i].vorname;
					}
				},
				select: function(event, ui)
				{
					$('#betreuer_uid').val(ui.item.uid);
				}
			});

            // $('#ansprechperson').autocomplete(
			// 	'../../cis/private/outgoing/outgoing_autocomplete.php',
	        //     {
		    //         minChars:2,
		    //         matchSubset:1,matchContains:1,
		    //         width:500,
		    //         extraParams:{'work':'outgoing_ansprechperson_search'
	        //     }
            // }).result(function(event, item) {
			//		$('#ansprechperson_uid').val(item[1]);
            // });

            // $('#betreuer').autocomplete('../../cis/private/outgoing/outgoing_autocomplete.php',
            // {
            // minChars:2,
            // matchSubset:1,matchContains:1,
            // width:500,
            // extraParams:{'work':'outgoing_ansprechperson_search'
            // }
            // }).result(function(event, item) {
			// 		  $('#betreuer_uid').val(item[1]);
            // });
			//
            $("#myTable").tablesorter({
                sortList: [[2,0]],
                widgets: ["zebra"]
            });
        });

        </script>
	</head>
	<body>

<?php
if($preoutgoing_id=='')
	exit;

$out = new preoutgoing();
if(!$out->load($preoutgoing_id))
	$message.= '<span class="error">'.$out->errormsg.'</span>';
$person = new benutzer();
if(!$person->load($out->uid))
	$message.='<span class="error">'.$person->errormsg.'</span>';

echo '<h2>Details - '.$person->vorname.' '.$person->nachname.'</h2>';
print_menu('Personendetails', 'personendetails');
echo ' | ';
print_menu('Dokumente', 'dokumente');
echo ' | ';
print_menu('Lehrveranstaltungen', 'lehrveranstaltungen');
echo ' | ';
print_menu('Anmerkungen', 'anmerkungen');
echo '<div style="float:right">'.$message.'</div>';
switch($action)
{
	case 'personendetails':
		print_personendetails();
		break;
	case 'dokumente':
		print_dokumente();
		break;
    case 'lehrveranstaltungen':
		print_lvs();
        break;
    case 'anmerkungen':
        print_anmerkungen();
		break;
}

function print_personendetails()
{
    global $out;

    $datum = new datum();

    $outgoingFirma = new preoutgoing();
    $outgoingFirma->loadAuswahlFirmen($out->preoutgoing_id);
    $zeitraum_von = $datum->formatDatum($out->dauer_von, 'd.m.Y');
    $zeitraum_bis = $datum->formatDatum($out->dauer_bis, 'd.m.Y');
    $sprachraum_von = $datum->formatDatum($out->sprachkurs_von, 'd.m.Y');
    $sprachraum_bis = $datum->formatDatum($out->sprachkurs_bis, 'd.m.Y');
    $praktikum_von = $datum->formatDatum($out->praktikum_von, 'd.m.Y');
    $praktikum_bis = $datum->formatDatum($out->praktikum_bis, 'd.m.Y');
    $ansprechperson = new benutzer();
    $ansprechperson->load($out->ansprechperson);
    $betreuer = new benutzer();
    $betreuer->load($out->betreuer);
    $checkedPraktikum = $out->praktikum?'checked':'';
    $checkedBachelorarbeit = $out->bachelorarbeit?'checked':'';
    $checkedMasterarbeit = $out->masterarbeit?'checked':'';
    $checkedBehinderung = $out->behinderungszuschuss?'checked':'';
    $checkedStudienbeihilfe = $out->studienbeihilfe?'checked':'';
    $sprachkursSelect = $out->sprachkurs?'selected':'';
    $intensivSprachkursSelect = $out->intensivsprachkurs?'selected':'';
    $benutzer = new benutzer();
    $benutzer->load($out->uid);
    $adresse = new adresse();
    $adresse->load_pers($benutzer->person_id);
    $nation = new nation();
    $nation->load($benutzer->staatsbuergerschaft);
    $student = new student();
    $student->load($benutzer->uid);
    $prestudent = new prestudent();
    $prestudent->getLastStatus($student->prestudent_id);
    $studiengang = new studiengang();
    $studiengang->load($student->studiengang_kz);
    $adr_strasse='';
    $adr_plz = '';
    $adr_ort ='';
    foreach($adresse->result as $res)
    {
        // Hauptwohnsitz anzeigen
        if($res->typ=='h')
        {
            $adr_strasse = $res->strasse;
            $adr_plz = $res->plz;
            $adr_ort = $res->ort;
        }
    }

        $i = 1;
        echo '<form action="'.$_SERVER['PHP_SELF'].'?method=save&preoutgoing_id='.$out->preoutgoing_id.'" method="POST"> <fieldset><table border="0" >
            <tr><td colspan=2"><b>Auswahl Universitäten:</b></td></tr>';
        foreach($outgoingFirma->firmen as $fi)
        {
            $firmaAuswahl = new firma();
            $firmaAuswahl->load($fi->firma_id);
            $style = $fi->auswahl?'style="color:red"':'';

            $mobilitätsprogramm = new mobilitaetsprogramm();
            $mobilitätsprogramm->load($fi->mobilitaetsprogramm_code);
            if($mobilitätsprogramm->kurzbz == '')
                $mobprogramm = 'SUMMERSCHOOL';
            else
                $mobprogramm = $mobilitätsprogramm->kurzbz;
            if($fi->name == '')
                echo " <tr><td  colspan=2 $style>".$i.": ".$firmaAuswahl->name." [".$mobprogramm."] <a href='".$_SERVER['PHP_SELF']."?method=setAuswahl&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Auswahl </a><a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Delete</a></td></tr>";
            else
                echo " <tr><td  colspan=2 $style>".$i.": ".$fi->name." [Freemover] <a href='".$_SERVER['PHP_SELF']."?method=setAuswahl&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Auswahl </a><a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Delete</a></td></tr>";
            $i++;
        }
    if($out->checkStatus($out->preoutgoing_id, 'freigabe'))
    {
    echo '
        <tr>
            <td  colspan=2>&nbsp;</td>
        </tr>
        <tr><td><b>Personendaten:</b></td></tr>
        <tr>
            <td>Vorname:</td><td><input type="text" name="vorname" value="'.$benutzer->vorname.'" disabled></td>
            <td>Strasse:</t><td><input type="text" name="strasse" disabled value="'.$adr_strasse.'"></td>
        </tr>
        <tr>
            <td>Nachname:</td><td><input type="text" name="nachname" value="'.$benutzer->nachname.'" disabled></td>
            <td>PLZ/Ort:</td><td><input type="text" name="plz" size="4" disabled value="'.$adr_plz.'"> <input type="text" name="ort" disabled value="'.$adr_ort.'">
        </tr>
        <tr>
            <td>Geburtsdatum:</td><td><input type="text" name="gebdatum" value="'.$datum->formatDatum($benutzer->gebdatum, 'd.m.Y').'" disabled>
            <td>Staatsbürgerschaft:</td><td><input type="text" name="nationalitaet" value="'.$nation->kurztext.'" disabled></td>
        </tr>
        <tr>
            <td>Geburtsort:</td><td><input type="text" name="gebort" value="'.$benutzer->gebort.'" disabled></td>
            <td>Personenkennzeichen:</d><td><input type="text" name="pers_kz" value="'.$student->matrikelnr.'" disabled></td>
        </tr>
        <tr>
            <td>Studiensemester:</td><td><input type="text" name="studienjahr" value="'.$prestudent->ausbildungssemester.'" disabled></td>
            <td>Studiengang:</td><td><input type="text" name="studiengang" size="50" value="'.$studiengang->bezeichnung.'" disabled></td>
        </tr>
        <tr>
            <td>Studientyp:</td><td><input type="text" name="studientyp" value="'.$studiengang->typ.'" disabled></td>
            <td><a href ="mailto:'.$out->uid.'@'.DOMAIN.'">E-Mail schicken</a></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
        </tr>
        <tr><td><b>Zusätzliche Daten:</b></td></tr>
        <tr>
            <td>Zeitraum Aufenthalt: </td>
            <td><input type="text" value="'.$zeitraum_von.'" size="9" id="datepicker_zeitraumvon" name="aufenthalt_von"> - <input type="text" value="'.$zeitraum_bis.'" size="9" id="datepicker_zeitraumbis" name="aufenthalt_bis"></td>
            <td>Praktikum: </td>
            <td><input type="checkbox" name="praktikum" '.$checkedPraktikum.'></td>
        </tr>
        <tr>
            <td>Ansprechperson Heimatuniversität: </td>
            <td><input type="text" value="'.$ansprechperson->vorname.' '.$ansprechperson->nachname.'" id="ansprechperson" name="ansprechperson"><input type="hidden" id="ansprechperson_uid" name="anprechperson_uid" value="'.$out->ansprechperson.'"></td>
            <td>Zeitraum Praktikum: </td>
            <td><input type="text" name="praktikum_von" id="datepicker_praktikumvon" size ="9" value="'.$praktikum_von.'"> - <input type="text" name="praktikum_bis" id="datepicker_praktikumbis" size="9" value="'.$praktikum_bis.'"></td>
        </tr>
        <tr>
            <td>Sprachkurs: </td>
            <td><select name="sprachkurs">
                <option value="kein">keiner</option>
                <option value="vorbereitend" '.$sprachkursSelect.'>vorbereitender Sprachkurs</option>
                <option value="intensiv" '.$intensivSprachkursSelect.'>Erasmus Intensivsprachkurs</option>
                </select>
            </td>
            <td>Bachelorarbeit: <input type ="checkbox" name="bachelorarbeit" '.$checkedBachelorarbeit.'></td>
            <td>Masterarbeit: <input type="checkbox" name="masterarbeit" '.$checkedMasterarbeit.'></td>
        </tr>
        <tr>
            <td>Zeitraum Sprachkurs:</td>
            <td><input tpye="text" value="'.$sprachraum_von.'" id="datepicker_sprachkursvon" size="9" name="sprachkurs_von"> - <input type="text" value="'.$sprachraum_bis.'" size="9" id="datepicker_sprachkursbis" name="sprachkurs_bis"></td>
            <td>Bachelor-, Masterarbeitsbetreuer: </td><td><input type="text" name="betreuer" id="betreuer" value="'.$betreuer->vorname.' '.$betreuer->nachname.'"> <input type="hidden" name="betreuer_uid" id="betreuer_uid" value="'.$out->betreuer.'"></td>
        </tr>
        <tr>
            <td>Behinderungszuschuss:</td><td><input type="checkbox" name="behinderungszuschuss" '.$checkedBehinderung.'></td>
            <td>Studienbeihilfe:</td><td><input type="checkbox" name="studienbeihilfe" '.$checkedStudienbeihilfe.'></td>
        </tr>
        <tr>
            <td>Anmerkung Student: </td><td colspan="2"><textarea rows="3" cols="25" name="anmerkungStudent">'.$out->anmerkung_student.'</textarea>
        <tr>
            <td>
                <input type="submit" value="Speichern">
            </td>
        </tr>';

}
else
{
    echo '
            <tr>
            <td  colspan=2>&nbsp;</td>
            </tr>
            <tr><td><b>Personendaten:</b></td></tr>
            <tr>
                <td>Vorname:</td><td><input type="text" name="vorname" value="'.$benutzer->vorname.'" disabled></td>
                <td>Strasse:</t><td><input type="text" name="strasse" disabled value="'.$adr_strasse.'"></td>
            </tr>
            <tr>
                <td>Nachname:</td><td><input type="text" name="nachname" value="'.$benutzer->nachname.'" disabled></td>
                <td>PLZ/Ort:</td><td><input type="text" name="plz" size="4" disabled value="'.$adr_plz.'"> <input type="text" name="ort" disabled value="'.$adr_ort.'">
            </tr>
            <tr>
                <td>Geburtsdatum:</td><td><input type="text" name="gebdatum" value="'.$datum->formatDatum($benutzer->gebdatum, 'd.m.Y').'" disabled>
                <td>Staatsbürgerschaft:</td><td><input type="text" name="nationalitaet" value="'.$nation->kurztext.'" disabled></td>
            </tr>
            <tr>
                <td>Geburtsort:</td><td><input type="text" name="gebort" value="'.$benutzer->gebort.'" disabled></td>
                <td>Personenkennzeichen:</d><td><input type="text" name="pers_kz" value="'.$student->matrikelnr.'" disabled></td>
            </tr>
            <tr>
                <td>Studiensemester:</td><td><input type="text" name="studienjahr" value="'.$prestudent->ausbildungssemester.'" disabled></td>
                <td>Studiengang:</td><td><input type="text" name="studiengang" size="50" value="'.$studiengang->bezeichnung.'" disabled></td>
            </tr>
            <tr>
                <td>Studientyp:</td><td><input type="text" name="studientyp" value="'.$studiengang->typ.'" disabled></td>
                <td><a href ="mailto:'.$out->uid.'@'.DOMAIN.'">E-Mail schicken</a></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Anmerkung Student: </td><td colspan="2"><textarea rows="3" cols="25" name="anmerkungStudent">'.$out->anmerkung_student.'</textarea></td>
            </tr>
            </table>';
}
echo '</table></fieldset></form>';
    $outgoingStatus = new preoutgoing();
    $outgoingStatus->getAllStatus($out->preoutgoing_id);
// Status ausgabe
echo '<h3>Status</h3>
    	<table class="tablesorter" id="dokumente">
		<thead>
			<tr>
				<th>Status</th>
				<th>Datum</th>
			</tr>
		</thead>
		<tbody>';
    foreach($outgoingStatus->stati as $status)
    {
        echo '<tr><td>'.$status->preoutgoing_status_kurzbz.'</td><td>'.$status->datum.'</td></tr>';
    }

 echo'</table><form action="'.$_SERVER['PHP_SELF'].'?preoutgoing_id='.$out->preoutgoing_id.'" method="POST">';
  $preoutgoing = new preoutgoing();
    $preoutgoing->getAllStatiKurzbz();
    echo '<tr><td><SELECT name="status">';
    foreach($preoutgoing->stati as $status_filter)
    {
        $selected = '';
        if($status_filter->preoutgoing_status_kurzbz == $status)
            $selected ='selected';
        echo'<option value="'.$status_filter->preoutgoing_status_kurzbz.'" '.$selected.'>'.$status_filter->preoutgoing_status_kurzbz.'</option>';
    }

    echo'</SELECT></td></tr>
        <input type="submit" name="StatusSetzen" value="setzen">';

}

function print_lvs()
{
    global $person, $preoutgoing_id, $datum;

        $preoutgoingLv = new preoutgoing();
        $preoutgoingLv->loadLvs($preoutgoing_id);
        echo '
        <script type="text/javascript">
        $(document).ready(function()
		{
		    $("#Lehrveranstaltung").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		}
        );
        </script>';
        echo '<fieldset>';
        echo'Folgende Lehrveranstaltungen wurden eingetragen <br><br>
        <table id="Lehrveranstaltung" class="tablesorter">
        <thead>
            <tr>
            <th>Bezeichnung</th>
            <th>ECTS</th>
            <th></th>
            </tr>
        </thead>
        <tbody>';
        foreach($preoutgoingLv->lehrveranstaltungen as $lv)
        {
            echo '<tr><td>'.$lv->bezeichnung.'</td><td>'.$lv->ects.'</td><td><a href="'.$_SERVER['PHP_SELF'].'?method=deleteLv&lv_id='.$lv->preoutgoing_lehrveranstaltung_id.'&preoutgoing_id='.$preoutgoing_id.'&action=lehrveranstaltung">löschen</a></tr>';
        }
        echo '</table></fieldset>';
}

function print_dokumente()
{
    global $person, $preoutgoing_id, $datum;

	echo '<fieldset>';
	$akte = new akte();
	$akte->getAktenOutgoing($person->person_id);

	echo '
	Folgende Dokumente wurden hochgeladen:<br><br>
	<script type="text/javascript">
	$(document).ready(function()
		{
		    $("#dokumente").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["zebra"]
			});
		}
	);
	</script>
	<table class="tablesorter" id="dokumente">
		<thead>
			<tr>
				<th>Datum</th>
				<th>Name</th>
				<th>Typ</th>
			</tr>
		</thead>
		<tbody>
		';
	foreach($akte->result as $row)
	{
		echo '<tr>';
		echo '<td>'.$datum->formatDatum($row->erstelltam,'d.m.Y').'</td>';
		echo '<td><a href="../../content/akte.php?id='.$row->akte_id.'">'.$row->titel.'</a></td>';
		echo '<td>'.$row->dokument_kurzbz.'</td>';
		echo '</tr>';

	}
	echo '</tbody></table>';
	echo '</fieldset>';
}

function print_anmerkungen()
{
    global $out;

    echo '<br><br><form action="'.$_SERVER['PHP_SELF'].'?preoutgoing_id='.$out->preoutgoing_id.'&action=anmerkungen" method="POST">';
    echo '<textarea rows="6" cols="30" name="anmerkungAdmin">'.$out->anmerkung_admin.'</textarea><br>';
    echo '<input type="submit" value="Speichern" name="submit_anmerkung">';
    echo '</form>';
}

/**
 * Erstellt einen MenuLink
 * @param $name Name des Links
 * @param $value Action
 */
function print_menu($name, $value)
{
	global $action, $preoutgoing_id;
	if($value==$action)
		$name = '<b>'.$name.'</b>';
	echo '<a href="'.$_SERVER['PHP_SELF'].'?action='.$value.'&amp;preoutgoing_id='.$preoutgoing_id.'">'.$name.'</a>';
}

// sendet eine EMail an die Studiengangsssistenz des Outgoings
function sendMailAssistenz($uid)
{
    $student = new student();
    $student->load($uid);
    $studiengang = new studiengang();
    $studiengang->load($student->studiengang_kz);
    $out = new preoutgoing();
    $out->loadUid($uid);
    $out_auswahl = new preoutgoing();
    $out_auswahl->loadAuswahl($out->preoutgoing_id);
    $mob = new mobilitaetsprogramm();
    $mob->load($out_auswahl->mobilitaetsprogramm_code);
    $firm = new firma();
    $firm->load($out_auswahl->firma_id);

    $emailtext= "Dies ist eine automatisch generierte E-Mail.<br><br>";
    $emailtext.= "Ein Student ist für den Aufenthalt im Ausland gemeldet.<br>";
    $emailtext.= "Uid: ".$student->uid."<br>";
    $emailtext.= "Name: ".$student->vorname." ".$student->nachname."<br>";
    $emailtext.= "Zeitraum-Von: ".$out->dauer_von."<br>";
    $emailtext.= "Zeitraum-Bis: ".$out->dauer_bis."<br>";
    $emailtext.= "Mobilitätsprogramm: ".$mob->kurzbz."<br>";
    $emailtext.= "Universität: ".$firm->name."<br>";

    $mail = new mail($studiengang->email, 'no-reply', 'New Outgoing', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
    $mail->setHTMLContent($emailtext);
    $mail->send();
}

// sendet eine EMail an den Studenten dass Universität ausgewählt wurde
function sendMailStudent($uid)
{
    $email = $uid."@technikum-wien.at";

    $emailtext ="Dies ist eine automatisch generiert E-Mail.<br><br>";
    $emailtext.="Es wurde für Ihr Auslandssemester die Universität bestätigt.<br>";
    $emailtext.="Bitte füllen Sie auf der Registrationsseite Ihre zusätzlichen Daten aus.";

    $mail = new mail($email, 'no-reply','Bestätigung des Auslandsemesters', 'Bitte sehen Sie sich die Nachricht in HTML Sicht an, um den Link vollständig darzustellen.');
    $mail->setHTMLContent($emailtext);
    $mail->send();
}
