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

$preoutgoing_id = isset($_GET['preoutgoing_id'])?$_GET['preoutgoing_id']:null;
$action = isset($_GET['action'])?$_GET['action']:'personendetails';
$method = isset($_GET['method'])?$_GET['method']:null;

$user = get_uid();
$message = '';
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$datum = new datum(); 

// Setzt die gemeinsam ausgewählte Universität
if($method == 'setAuswahl')
{
    $preoutgoing = new preoutgoing(); 
    if(!$preoutgoing->setStatus($preoutgoing_id, 'freigabe'))
        $message = "<span class='error'>Fehler beim Speichern aufgetreten</span>";
    else
        $message = "<span class='ok'>".$preoutgoing->errormsg."</span>";
    
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
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<title>Incoming</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
        <link href="../../skin/tablesort.css" rel="stylesheet" type="text/css">
        <link href="../../skin/jquery.css" rel="stylesheet"  type="text/css"/>
        <script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
        <script src="../../include/js/jquery.js" type="text/javascript"></script> 
        <script type="text/javascript">
        $(document).ready(function() 
        { 
            $( "#datepicker_zeitraumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_zeitraumbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_sprachkursbis" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumvon" ).datepicker($.datepicker.regional['de']);
            $( "#datepicker_praktikumbis" ).datepicker($.datepicker.regional['de']);
            
            $('#ansprechperson').autocomplete('../../cis/private/outgoing/outgoing_autocomplete.php', 
            {
            minChars:2,
            matchSubset:1,matchContains:1,
            width:500,
            extraParams:{'work':'outgoing_ansprechperson_search'	
            }
            }).result(function(event, item) {
	  		  $('#ansprechperson_uid').val(item[1]);
            });
            
            $('#betreuer').autocomplete('../../cis/private/outgoing/outgoing_autocomplete.php', 
            {
            minChars:2,
            matchSubset:1,matchContains:1,
            width:500,
            extraParams:{'work':'outgoing_ansprechperson_search'	
            }
            }).result(function(event, item) {
	  		  $('#betreuer_uid').val(item[1]);
            });
            
                $("#myTable").tablesorter(
                {
                    sortList: [[2,0]],
                    widgets: ["zebra"]
                }); 
        } 
        ); 
			
        </script> 
	</head>
	<body>

<?php
/*
if(!$rechte->isBerechtigt('inout/outgoing', null, 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');
 
 */

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
echo '<div style="float:right">'.$message.'</div>';
switch($action)
{
	case 'personendetails':
		print_personendetails();
		break;
	case 'dokumente':
		print_dokumente();
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
        if($fi->name == '')
            echo " <tr><td  colspan=2 $style>".$i.": ".$firmaAuswahl->name." [".$mobilitätsprogramm->kurzbz."] <a href='".$_SERVER['PHP_SELF']."?method=setAuswahl&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Auswahl </a><a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Delete</a></td></tr>";
        else
            echo " <tr><td  colspan=2 $style>".$i.": ".$fi->name." [Freemover] <a href='".$_SERVER['PHP_SELF']."?method=setAuswahl&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Auswahl </a><a href='".$_SERVER['PHP_SELF']."?method=deleteFirma&outgoingFirma_id=".$fi->preoutgoing_firma_id."&preoutgoing_id=".$out->preoutgoing_id."'>Delete</a></td></tr>";
        $i++;
    }
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
        <tr><td>&nbsp;</td></tr>
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
        </tr>
        <tr>
            <td>Studienbeihilfe:</td><td><input type="checkbox" name="studienbeihilfe" '.$checkedStudienbeihilfe.'></td>
        </tr>
        <tr>
            <td>
                <input type="submit" value="Speichern">
            </td>
        </tr>


</table></fieldset></form>'; 
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