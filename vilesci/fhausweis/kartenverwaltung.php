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
 * Authors: Karl Burkhart 	<burkhart@technikum-wien.at>
 */

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/student.class.php');
require_once('../../include/fotostatus.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/betriebsmittel.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/prestudent.class.php');
require_once('../../include/studiensemester.class.php');

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

define("anzahlSemester","10"); 
$buchstabenArray = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','Ä','Ö','Ü');

$studiengang = new studiengang(); 
$studiengang->getAll('oe_kurzbz', true);

$fotostatus = new fotostatus(); 
$fotostatus->getAllStatusKurzbz(); 

$statusStudent=(isset($_REQUEST['select_statusStudent'])?$_REQUEST['select_statusStudent']:null);
$statusMitarbeiter=(isset($_REQUEST['select_statusMitarbeiter'])?$_REQUEST['select_statusMitarbeiter']:null);
$typMitarbeiter =(isset($_REQUEST['select_typ_mitarbeiter'])?$_REQUEST['select_typ_mitarbeiter']:null);
$studiengang_kz=(isset($_REQUEST['select_studiengang'])?$_REQUEST['select_studiengang']:null);
$semester=(isset($_REQUEST['select_semester'])?$_REQUEST['select_semester']:null);   
$buchstabe=(isset($_REQUEST['select_buchstabe'])?$_REQUEST['select_buchstabe']:null);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet"  href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
    <link href="../../skin/tablesort.css" rel="stylesheet" type="text/css">
    <link href="../../skin/jquery.css" rel="stylesheet"  type="text/css"/>

    <script src="../../include/js/jquery.js" type="text/javascript"></script> 
    <script type="text/javascript">
        $(document).ready(function() 
        { 
            $("#myTableFiles").tablesorter(
            {
                sortList: [[0,0]],
                widgets: ["zebra"]
            }); 
        });

        </script>    
	<title>FH-Ausweis Kartenverwaltung</title>
</head>
<?php 
if(!$rechte->isBerechtigt('basis/fhausweis', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

echo '<body>
<h2>FH-Ausweis Kartenverwaltung</h2>
<fieldset style="display: inline">
    <legend>Studentensuche</legend>
    <form method="POST" name="form_filterStudent">
        <table border="0">
            <tr>
                <td>Studiengang:</td>
                <td><select name="select_studiengang">
                <option value="incoming">Incoming</option>
                ';
				
                foreach($studiengang->result as $stud)
                {
                    if($stud->studiengang_kz < '10000')
                        echo '<option value='.$stud->studiengang_kz.' '.($studiengang_kz==$stud->studiengang_kz?'selected':'').'>'.mb_strtoupper($stud->oe_kurzbz).' | '.mb_strtoupper($stud->kurzbzlang).'</option>';
                }
echo'           </select></td>
                <td>Semester:</td>
                <td><select name="select_semester">';
                echo '<option>alle</option>';    
                for($i = 1;$i<=anzahlSemester;$i++)
                    echo '<option '.($semester==$i?'selected':'').'>'.$i.'</option>';

echo'           </select>
                </td>
                <td>letzter Status:</td>
                <td><select name="select_statusStudent">';
                foreach($fotostatus->result as $foto)
                {
                    echo '<option value="'.$foto->fotostatus_kurzbz.'" '.($statusStudent==$foto->fotostatus_kurzbz?'selected':'').'>'.ucfirst($foto->fotostatus_kurzbz).'</option>';
                }
echo'           <option value="nichtGedrucktAkzept" '.($statusStudent=='nichtGedrucktAkzept'?'selected':'').'>Akzeptiert und nicht gedruckt</option>
				<option value="nichtGedruckt" '.($statusStudent=='nichtGedruckt'?'selected':'').'>nicht gedruckt</option>
                <option value="gedrucktNichtAusgegeben" '.($statusStudent=='gedrucktNichtAusgegeben'?'selected':'').'>Gedruckt nicht ausgegeben</option>
                </select></td>
                <td><input name="btn_submitStudent" type="submit" value="Anzeigen"></td>
            </tr>
        </table>
    </form>
</fieldset>

<fieldset style="display:inline;">
    <legend>Mitarbeitersuche</legend>
    <form method="POST" name="form_filterMitarbeiter">
        <div style="float: right;"> 
            <table border="0" >
                <tr>
                    <td>Typ:</td>
                    <td><select name="select_typ_mitarbeiter">
                    <option value="intern" '.($typMitarbeiter=='intern'?'selected':'').'>Fixangestellte</option>
                    <option value="extern" '.($typMitarbeiter=='extern'?'selected':'').'>Externe mit Lehrauftrag in letzten 3 Sem.</option>
                    </select>
                    <td>Anfangsbuchstabe:</td>
                    <td><select name="select_buchstabe">
                    <option value="">*</option>';
                    foreach($buchstabenArray as $b)
                    {
                            echo '<option value="'.$b.'" '.($b==$buchstabe?'selected':'').'>'.$b.'</option>';
                    }
echo'               </select>
                    <td>letzter Status:</td>
                    <td><select name="select_statusMitarbeiter">';
                    foreach($fotostatus->result as $foto)
                    {
                        echo '<option value="'.$foto->fotostatus_kurzbz.'" '.($statusMitarbeiter==$foto->fotostatus_kurzbz?'selected':'').'>'.ucfirst($foto->fotostatus_kurzbz).'</option>';
                    }
echo'               <option value="nichtGedruckt" '.($statusMitarbeiter=='nichtGedruckt'?'selected':'').'>Akzeptiert und nicht gedruckt</option>
                    <option value="gedrucktNichtAusgegeben" '.($statusMitarbeiter=='gedrucktNichtAusgegeben'?'selected':'').'>Gedruckt nicht ausgegeben</option>
                    </select></td>
                    <td><input name="btn_submitMitarbeiter" type="submit" value="Anzeigen"></td>
                </tr>
            </table>
        </div>
    </form>
</fieldset>';

// zeige alle Studenten an
if(isset($_REQUEST['btn_submitStudent']))
{
    $uids = '';
    if($semester == 'alle')
        $semester = null;
    
    $studenten = new student(); 

    if($studiengang_kz=='incoming')
    	$studenten->getIncoming();
    else
    	$studenten->getStudentsStudiengang($studiengang_kz, $semester);
    $studentenArray = $studenten->result; 
    
   // $studentenArray = $studenten->getStudents($studiengang_kz,$semester,null,null,null,'WS2011');
    echo '
        <form method="POST" name="form_studentenkarten" action="kartezuweisen.php">
        <table id="myTableFiles" class="tablesorter">
        <thead>
            <tr>
                <th>Name</th>
                <th>Geburtsdatum</th>
                <th>Matrikelnummer</th>
                <th>UID</th>
                <th>person_id</th>
            </tr>
        </thead>
        <tbody>';
    
    foreach($studentenArray as $stud)
    {
        // Wenn letzter Status nich Student ist -> nicht anzeigen
        $prestudent = new prestudent(); 
        $prestudent->getLastStatus($stud->prestudent_id);
        if($prestudent->status_kurzbz == 'Student' || ($studiengang_kz=='incoming' && $prestudent->status_kurzbz='Incoming'))
        {
            if($statusStudent=='gedrucktNichtAusgegeben')
            {
                // gedruckt aber noch nicht ausgegeben
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($stud->person_id); 
                $betriebsmittel = new betriebsmittel(); 

                // status akzeptiert und noch nicht gedruckt
                if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($stud->uid) == true && $betriebsmittel->zutrittskarteAusgegeben($stud->uid) == false)
                {
                    echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
                    $uids.=';'.$stud->uid;     
                }
            }
            else if($statusStudent == 'nichtGedrucktAkzept')
            {
                // akzeptiert und nicht gedruckt
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($stud->person_id); 
                $betriebsmittel = new betriebsmittel(); 

                // status akzeptiert und noch nicht gedruckt
                if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($stud->uid) == false)
                {
                    echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
                    $uids.=';'.$stud->uid;     
                }
            }
       		else if($statusStudent == 'nichtGedruckt')
            {
                // akzeptiert und nicht gedruckt
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($stud->person_id); 
                $betriebsmittel = new betriebsmittel(); 

                // noch nicht gedruckt
                if($betriebsmittel->zutrittskartePrinted($stud->uid) == false)
                {
                    echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.' ('.$fotostatus->fotostatus_kurzbz.')</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
                    $uids.=';'.$stud->uid;     
                }
            }
            else
            {
                // letzten Status anzeigen
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($stud->person_id); 

                // überprüfen ob letzer Status der gesuchte ist
                if($fotostatus->fotostatus_kurzbz == $statusStudent)
                {
                    echo '<tr><td>'.$stud->nachname.' '.$stud->vorname.'</td><td>'.$stud->gebdatum.'</td><td>'.$stud->matrikelnr.'</td><td>'.$stud->uid.'</td><td>'.$stud->person_id.'<input type="hidden" name="users[]" value="'.$stud->uid.'"></td></tr>';
                    $uids.=';'.$stud->uid; 
                }
            }
        }
    }
    echo '
        </tbody>
        </table>
        <table>
            <tr>
                <td><input type="submit" value="Karten zuteilen" name="btn_kartezuteilenStudent"><input type="button" value="Karten drucken" onclick=\'window.open("../../content/zutrittskarte.php?data='.$uids.'");\'></td>
            </tr>
        </table>
        </form>';

}
// Zeige alle Mitarbeiter an
if(isset($_REQUEST['btn_submitMitarbeiter']))
{
    $studSemArray = array(); 
    
    $studiensemester = new studiensemester(); 
    $studSemArray[]=$studiensemester->getakt();
    $studSemArray[]=$studiensemester->getPrevious();
    $studSemArray[]=$studiensemester->getBeforePrevious();
    
    $fixangestellt = true; 
    if($_REQUEST['select_typ_mitarbeiter'] == 'extern')
        $fixangestellt = false; 
        
    $mitarbeiter = new mitarbeiter(); 
    $mitarbeiter->getMitarbeiterForZutrittskarte($buchstabe, $fixangestellt, $studSemArray);

    $uids = '';
    
    echo '
        <form method="POST" name="form_mitarbeiterkarten" action="kartezuweisen.php">
        <table id="myTableFiles" class="tablesorter">
        <thead>
            <tr>
                <th>Name</th>
                <th>Geburtsdatum</th>
                <th>Personalnummer</th>
                <th>UID</th>
                <th>person_id</th>
            </tr>
        </thead>
        <tbody>';
    
        foreach($mitarbeiter->result as $mit)
        {
            if($statusMitarbeiter=='gedrucktNichtAusgegeben')
            {
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($mit->person_id); 
                $betriebsmittel = new betriebsmittel(); 
                
                // status akzeptiert, gedruckt aber noch nicht ausgegeben
                if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($mit->uid) == true && $betriebsmittel->zutrittskarteAusgegeben($mit->uid) == false)
                {
                    $uids.=';'.$mit->uid; 
                    echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
                }
            }
            else if($statusMitarbeiter == 'nichtGedruckt')
            {
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($mit->person_id); 
                $betriebsmittel = new betriebsmittel(); 
                
                // status akzeptiert und noch nicht gedruckt
                if($fotostatus->fotostatus_kurzbz == 'akzeptiert' && $betriebsmittel->zutrittskartePrinted($mit->uid) == false)
                {
                    $uids.=';'.$mit->uid; 
                    echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
                }
            }
            else
            {
                $fotostatus = new fotostatus();
                $fotostatus->getLastFotoStatus($mit->person_id); 

                // überprüfen ob letzer Status der gesuchte ist
                if($fotostatus->fotostatus_kurzbz == $statusMitarbeiter)
                {
                    $uids.=';'.$mit->uid; 
                    echo '<tr><td>'.$mit->nachname.' '.$mit->vorname.'</td><td>'.$mit->gebdatum.'</td><td>'.$mit->personalnummer.'</td><td>'.$mit->uid.'</td><td>'.$mit->person_id.'<input type="hidden" name="users[]" value="'.$mit->uid.'"></td></tr>';
                }
            }
        }
    echo '
        </tbody>
        </table>
        <table>
            <tr>
                <td><input type="submit" value="Karten zuteilen" name="btn_kartezuteilenMitarbeiter">&nbsp;<input type="button" value="Karten drucken" onclick=\'window.open("../../content/zutrittskarte.php?data='.$uids.'");\'></td>
            </tr>
        </table>
        </form>
        </body></html>';
    }

?>
