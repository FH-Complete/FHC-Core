<!DOCTYPE html>
<?php
/*
 * Copyright 2014 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/mail.class.php');

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/pruefungstermin'))
	die('Sie haben keine Berechtigung für diese Seite');

function compareLvIDs($a, $b)
{
    if($a->lehrveranstaltung_id == $b->lehrveranstaltung_id)
        return 0;
    
    return -1;
}

$studiensemester = new studiensemester();
$studiensemester->getAll();

$pruefungsfenster = new pruefungsfenster();
$pruefungsfenster->getAll("start");

$lehrveranstaltung = new lehrveranstaltung();
//TODO
$lehrveranstaltung->getLVByMitarbeiter($uid, $studiensemester->getSemesterFromDatum(date("Y-m-d")));
$lehrveranstaltung->getLVByMitarbeiter("leutgeb", $studiensemester->getSemesterFromDatum(date("Y-m-d")));


$pruefungstermin = new pruefungstermin();
$pruefungstypen = $pruefungstermin->getAllPruefungstypen();

if($_POST["method"] == "save")
{
    $studiensemester_kurzbz = isset($_POST["studiensemester"])?$_POST["studiensemester"]:null;
    $pruefungsfenster_id = isset($_POST["pruefungsfenster"])?$_POST["pruefungsfenster"]:null;
    $pruefungstyp_kurzbz = isset($_POST["pruefungsTyp"])?$_POST["pruefungsTyp"]:null;
    $titel = isset($_POST["titel"])?$_POST["titel"]:null;
    $beschreibung = isset($_POST["beschreibung"])?$_POST["beschreibung"]:null;
    $methode = isset($_POST["methode"])?$_POST["methode"]:null;
    $einzeln = isset($_POST["einzelpruefung"])?true:false;
    $lehrveranstaltungen = isset($_POST["lv"]) ? $_POST["lv"] : null;
    $termine = array();
    $error = false;
    if($pruefungsfenster_id === "null")
    {
        $error = true;
        echo "Bitte geben Sie ein Prüfungsfenster an.";
    }
    
    if(isset($_POST["termin"]))
    {
        if(!$error)
        {
            $pruefungsfenster = new pruefungsfenster();
            $pruefungsfenster->load($pruefungsfenster_id);
            $datum = new datum();
        }
        for($i = 0; $i< sizeof($_POST["termin"]); $i++)
        {
            if(!$error)
            {
                $termin = new stdClass();
                $datum = new datum();
                $date = (isset($_POST["termin"][$i]) && $datum->checkDatum($_POST["termin"][$i])) ? $_POST["termin"][$i] : null;
                $beginn = (isset($_POST["terminBeginn"][$i]) && $datum->checkUhrzeit($_POST["terminBeginn"][$i])) ? $_POST["terminBeginn"][$i] : null;
                $ende = (isset($_POST["terminEnde"][$i]) && $datum->checkUhrzeit($_POST["terminEnde"][$i])) ? $_POST["terminEnde"][$i] : null;
                $termin->min = (isset($_POST["minTeilnehmer"][$i]) && is_numeric($_POST["minTeilnehmer"][$i]) && strlen($_POST["minTeilnehmer"][$i]) > 0) ? $_POST["minTeilnehmer"][$i] : 0;
                $termin->max = (isset($_POST["maxTeilnehmer"][$i]) && is_numeric($_POST["maxTeilnehmer"][$i]) && strlen($_POST["maxTeilnehmer"][$i]) > 0) ? $_POST["maxTeilnehmer"][$i] : null;

                if($datum == null)
                {
                    echo "Fehler: Ung&uuml;ltiges Datum angegeben.</br>";
                    $error = true;
                }
                if($beginn == null)
                {
                    echo "Fehler: Ung&uuml;ltige Beginnzeit angegeben.</br>";
                    $error = true;
                }
                if($ende == null)
                {
                    echo "Fehler: Ung&uuml;ltige Endzeit angegeben.</br>";
                    $error = true;
                }
                if($termin->min === null)
                {
                    echo "Fehler: Ung&uuml;ltige Mindestteilnehmerzahl angegeben.</br>";
                    $error = true;
                }
                if(!(mktime(substr($beginn,0,2), substr($beginn,-2)) < mktime(substr($ende,0,2), substr($ende,-2))))
                {
                    echo "Endzeit liegt vor der Startzeit!</br>";
                    $error = true;
                }
                if(!($termin->min < $termin->max) && $termin->max !== null)
                {
                    echo "Maximalteilnehmerzahlt ist kleiner der Mindestteilnehmerzahl.</br>";
                    $error = true;
                }
                if(!$datum->between($pruefungsfenster->start, $pruefungsfenster->ende, $date))
                {
                    echo "Prüfungstermin liegt nicht innheralb des Prüfungsfensters.</br>";
                    $error = true;
                }
                $termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
                $termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
                array_push($termine, $termin);
            }
        }       
    }

    if(!$error)
    {
        $pruefung = new pruefungCis();
        $pruefung->termine = $termine;
        $pruefung->mitarbeiter_uid = $uid;
        $pruefung->studiensemester_kurzbz = $studiensemester_kurzbz;
        $pruefung->pruefungsfenster_id = $pruefungsfenster_id;
        $pruefung->pruefungstyp_kurzbz = $pruefungstyp_kurzbz;
        $pruefung->titel = $titel;
        $pruefung->beschreibung = $beschreibung;
        $pruefung->methode = $methode;
        $pruefung->einzeln = $einzeln;
        $pruefung->insertvon = $uid;

        if(mb_strlen($pruefung->titel) > 0)
        {
            if($pruefung->pruefungstyp_kurzbz != "undefiniert")
            {
                if($lehrveranstaltungen != null && sizeof($lehrveranstaltungen) > 0)
                {
                    $i=0;
                    foreach ($lehrveranstaltungen as $lv) {
                        if($lv != "null")
                        {
                            array_push($pruefung->lehrveranstaltungen, $lv);
                        }
                    }
                    if($pruefung->save(true))
                    {
                        //TODO Absender und Inhalt fehlen
                        foreach ($pruefung->lehrveranstaltungen as $lvId)
                        {
                            $lv = new lehrveranstaltung($lvId);
                            $text = "Ein Prüfungstermin zu Ihrer Lehrveranstaltung ".$lv->bezeichnung." wurde angelegt.\n"
                                    . "Die Prüfung kann am "
                                    .date('d.m.Y', strtotime($pruefung->termine[0]->beginn))." "
                                    . "in der Zeit von "
                                    .date('H:i', strtotime($pruefung->termine[0]->beginn))." bis "
                                    .date('H:i', strtotime($pruefung->termine[0]->ende))." abgelegt werden.";
                            //$text = "test";
                            $empfaenger = $lv->getStudentsOfLv($lvId, $pruefung->studiensemester_kurzbz);
                            $mailto = "";
                            foreach ($empfaenger as $e) {
                                $mailto .= $e.'@'.DOMAIN.', ';
                            }
                            $email = new mail($mailto, "unknown", "Prüfungstermin für ".$lv->bezeichnung, $text);
                            var_dump($email);
                            echo $email->send();
                        }
                        
                        echo "Datensatz erfolgreich gespeichert!";
                    }
                    else
                    {
                        echo "Fehler: ".$pruefung->errormsg;
                    }
                }
                else
                {
                    echo "Bitte Lehrveranstaltungen angeben.";
                }

            }
            else
            {
                echo "Bitte einen Prüfungstyp auswählen";
            }
        }
        else
        {
            echo "Bitte einen Titel angeben.";
        }
    }
    
} 
else if($_POST["method"] == "update")
{
    $studiensemester_kurzbz = isset($_POST["studiensemester"])?$_POST["studiensemester"]:null;
    $pruefungsfenster_id = isset($_POST["pruefungsfenster"])?$_POST["pruefungsfenster"]:null;
    $pruefungstyp_kurzbz = isset($_POST["pruefungsTyp"])?$_POST["pruefungsTyp"]:null;
    $titel = isset($_POST["titel"])?$_POST["titel"]:null;
    $beschreibung = isset($_POST["beschreibung"])?$_POST["beschreibung"]:null;
    $methode = isset($_POST["methode"])?$_POST["methode"]:null;
    $einzeln = isset($_POST["einzelpruefung"])?true:false;
    $lehrveranstaltungen = isset($_POST["lv"]) ? $_POST["lv"] : null;
    $termine = array();
    $termineNeu = array();
    $error = false;
    if(!$error)
    {
        $pruefungsfenster = new pruefungsfenster();
        $pruefungsfenster->load($pruefungsfenster_id);
        $datum = new datum();
    }
    
    if(isset($_POST["terminNeu"]))
    {
        
        for($i = 0; $i< sizeof($_POST["terminNeu"]); $i++)
        {
            if(!$error)
            {
                $termin = new stdClass();
                $datum = new datum();
                $date = (isset($_POST["terminNeu"][$i]) && $datum->checkDatum($_POST["terminNeu"][$i])) ? $_POST["terminNeu"][$i] : null;
                $beginn = (isset($_POST["terminBeginnNeu"][$i]) && $datum->checkUhrzeit($_POST["terminBeginnNeu"][$i])) ? $_POST["terminBeginnNeu"][$i] : null;
                $ende = (isset($_POST["terminEndeNeu"][$i]) && $datum->checkUhrzeit($_POST["terminEndeNeu"][$i])) ? $_POST["terminEndeNeu"][$i] : null;
                $termin->min = (isset($_POST["minTeilnehmerNeu"][$i]) && is_numeric($_POST["minTeilnehmerNeu"][$i]) && strlen($_POST["minTeilnehmerNeu"][$i]) > 0) ? $_POST["minTeilnehmerNeu"][$i] : 0;
                $termin->max = (isset($_POST["maxTeilnehmerNeu"][$i]) && is_numeric($_POST["maxTeilnehmerNeu"][$i]) && strlen($_POST["maxTeilnehmerNeu"][$i]) > 0) ? $_POST["maxTeilnehmerNeu"][$i] : null;

                if($datum == null)
                {
                    echo "Fehler: Ung&uuml;ltiges Datum angegeben.</br>";
                    $error = true;
                }
                if($beginn == null)
                {
                    echo "Fehler: Ung&uuml;ltige Beginnzeit angegeben.</br>";
                    $error = true;
                }
                if($ende == null)
                {
                    echo "Fehler: Ung&uuml;ltige Endzeit angegeben.</br>";
                    $error = true;
                }
                if($termin->min === null)
                {
                    echo "Fehler: Ung&uuml;ltige Mindestteilnehmerzahl angegeben.</br>";
                    $error = true;
                }
                if(!(mktime(substr($beginn,0,2), substr($beginn,-2)) < mktime(substr($ende,0,2), substr($ende,-2))))
                {
                    echo "Endzeit liegt vor der Startzeit!</br>";
                    $error = true;
                }
                if(!($termin->min < $termin->max) && $termin->max !== null)
                {
                    echo "Maximalteilnehmerzahlt ist kleiner der Mindestteilnehmerzahl.</br>";
                    $error = true;
                }
                if(!$datum->between($pruefungsfenster->start, $pruefungsfenster->ende, $date))
                {
                    echo "Prüfungstermin liegt nicht innheralb des Prüfungsfensters.</br>";
                    $error = true;
                }
                $termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
                $termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
                array_push($termineNeu, $termin);
            }
        }       
    }
    
    if(isset($_POST["termin"]))
    {
        $error = false;
        for($i = 0; $i< sizeof($_POST["termin"]); $i++)
        {
            if(!$error)
            {
                $termin = new stdClass();
                $datum = new datum();
                $terminId = (isset($_POST["termin_id"][$i])) ? $_POST["termin_id"][$i] : null;
                $date = (isset($_POST["termin"][$i]) && $datum->checkDatum($_POST["termin"][$i])) ? $_POST["termin"][$i] : null;
                $beginn = (isset($_POST["terminBeginn"][$i]) && $datum->checkUhrzeit($_POST["terminBeginn"][$i])) ? $_POST["terminBeginn"][$i] : null;
                $ende = (isset($_POST["terminEnde"][$i]) && $datum->checkUhrzeit($_POST["terminEnde"][$i])) ? $_POST["terminEnde"][$i] : null;
                $termin->min = (isset($_POST["minTeilnehmer"][$i]) && is_numeric($_POST["minTeilnehmer"][$i]) && strlen($_POST["minTeilnehmer"][$i]) > 0) ? $_POST["minTeilnehmer"][$i] : 0;
                $termin->max = (isset($_POST["maxTeilnehmer"][$i]) && is_numeric($_POST["maxTeilnehmer"][$i]) && strlen($_POST["maxTeilnehmer"][$i]) > 0) ? $_POST["maxTeilnehmer"][$i] : null;

                if($datum == null)
                {
                    echo "Fehler: Ung&uuml;ltiges Datum angegeben.</br>";
                    $error = true;
                }
                if($beginn == null)
                {
                    echo "Fehler: Ung&uuml;ltige Beginnzeit angegeben.</br>";
                    $error = true;
                }
                if($ende == null)
                {
                    echo "Fehler: Ung&uuml;ltige Endzeit angegeben.</br>";
                    $error = true;
                }
                if($termin->min === null)
                {
                    echo "Fehler: Ung&uuml;ltige Mindestteilnehmerzahl angegeben.</br>";
                    $error = true;
                }
                if(!(mktime(substr($beginn,0,2), substr($beginn,-2)) < mktime(substr($ende,0,2), substr($ende,-2))))
                {
                    echo "Endzeit liegt vor der Startzeit!</br>";
                    $error = true;
                }
                if(!($termin->min < $termin->max) && $termin->max !== null)
                {
                    echo "Maximalteilnehmerzahlt ist kleiner der Mindestteilnehmerzahl.</br>";
                    $error = true;
                }
                if(!$datum->between($pruefungsfenster->start, $pruefungsfenster->ende, $date))
                {
                    echo "Prüfungstermin liegt nicht innheralb des Prüfungsfensters.</br>";
                    $error = true;
                }
                $termin->pruefungstermin_id = $terminId;
                $termin->beginn = date('Y-m-d H:i', strtotime($date." ".$beginn));
                $termin->ende = date('Y-m-d H:i', strtotime($date." ".$ende));
                array_push($termine, $termin);
            }
        }       
    }

    if(!$error)
    {
        $pruefung = new pruefungCis();
        $pruefung_id = $_POST["id"];
        $pruefung->load($pruefung_id);
        $pruefung->termine = $termine;
        foreach ($termineNeu as $t)
        {
            $pruefung->saveTerminPruefung($pruefung_id, $t->beginn, $t->ende, $t->max, $t->min);
        }
        $pruefung->mitarbeiter_uid = $uid;
        $pruefung->studiensemester_kurzbz = $studiensemester_kurzbz;
        $pruefung->pruefungsfenster_id = $pruefungsfenster_id;
        $pruefung->pruefungstyp_kurzbz = $pruefungstyp_kurzbz;
        $pruefung->titel = $titel;
        $pruefung->beschreibung = $beschreibung;
        $pruefung->methode = $methode;
        $pruefung->einzeln = $einzeln;
        $pruefung->updatevon = $uid;

        if(mb_strlen($pruefung->titel) > 0)
        {
            if($pruefung->pruefungstyp_kurzbz != "undefiniert")
            {
                if($lehrveranstaltungen != null && sizeof($lehrveranstaltungen) > 0)
                {
                    $i=0;
                    foreach ($lehrveranstaltungen as $lv)
                    {
                        if($lv != "null")
                        {
                            array_push($pruefung->lehrveranstaltungen, $lv);
                        }
                    }
                    if($pruefung->save(false))
                    {
                        echo "Datensatz erfolgreich gespeichert!";
                    }
                    else
                    {
                        echo "Fehler: ".$pruefung->errormsg;
                    }
                }
                else
                {
                    echo "Bitte Lehrveranstaltungen angeben.";
                }

            }
            else
            {
                echo "Bitte einen Prüfungstyp auswählen";
            }
        }
        else
        {
            echo "Bitte einen Titel angeben.";
        }
    }
    
}
else if(isset ($_GET["method"]) && $_GET["action"] === "deleteTermin")
{
    $pruefungstermin_id = $_GET["termin_id"];
    $pruefung_id = $_GET["id"];
    $pruefung = new pruefungCis();
    $pruefung->load($pruefung_id);
    $pruefung->deleteTerminPruefung($pruefungstermin_id);
}

else if(isset ($_GET["method"]) && $_GET["action"] === "deleteLv")
{
    $lvId = $_GET["lvId"];
    $pruefung_id = $_GET["id"];
    $pruefung = new pruefungCis();
    $pruefung->load($pruefung_id);
    $pruefung->deleteLehrveranstaltungPruefung($lvId, $pruefung_id);
}

else if(isset ($_GET["action"]) && $_GET["action"] === "storno")
{
    $pruefung_id = $_GET["id"];
    $pruefung = new pruefungCis();
//    $pruefung->load($pruefung_id);
    $pruefung->pruefungStornieren($pruefung_id);
    header("Location: pruefungstermin_festlegen.php");
}

$pruefungen = new pruefungCis();
$pruefungen->getPruefungByMitarbeiter($uid, $studiensemester_kurzbz);

foreach($pruefungen->result as $tempPrf)
{
    $tempPrf->getLehrveranstaltungenByPruefung();
    $tempPrf->getTermineByPruefung();
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Prüfungstermin festlegen</title>
        <script src="../../../../include/js/datecheck.js"></script>
        <script src="../../../../include/js/jquery1.9.min.js"></script>
        <script src="./pruefung.js"></script>
        <link rel="stylesheet" href="../../../../skin/jquery-ui-1.9.2.custom.min.css">
        <link rel="stylesheet" href="../../../../skin/fhcomplete.css">
        <link rel="stylesheet" href="../../../../skin/style.css.php">
        <link rel="stylesheet" href="../../../../skin/tablesort.css">
    </head>
    <body>
        <script>
            $(document).ready(function() {
//                $("#termin1").datepicker({
//                    minDate: new Date()
//                });
//                $("#prfTable").tablesorter({
//                    widgets: ["zebra"]
//                });
                loadPruefungsfenster(); 
           });
        </script>
        <?php
            if(!isset($_GET["method"]))
            {
        ?>
        <h1>Prüfungen verwalten</h1>
        <div>
            <form method="post" action="pruefungstermin_festlegen.php">
                <input type="hidden" name="method" value="save">
                <table>
                    <tr>
                        <td>Titel:</td>
                        <td>
                            <input type="text" name="titel">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Beschreibung:</td>
                        <td>
                            <textarea name="beschreibung" rows="5" cols="20"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Studiensemester:</td>
                        <td>
                            <select id="studiensemester" name="studiensemester" onchange="loadPruefungsfenster();" onload="loadPruefungsfenster();">
                                <?php
                                $aktuellesSemester = $studiensemester->getSemesterFromDatum(date("Y-m-d"));
                                foreach ($studiensemester->studiensemester as $result) 
                                {
                                    if($aktuellesSemester == $result->studiensemester_kurzbz)
                                    {
                                        echo '<option selected>'.$result->studiensemester_kurzbz.'</option>';
                                    }
                                    else
                                    {
                                        echo '<option>'.$result->studiensemester_kurzbz.'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungsfenster:</td>
                        <td>
                            <select id="pruefungsfenster" name="pruefungsfenster" onchange="setDatePicker(this);">
                                <!--Daten werden durch JavaScript geladen-->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungstyp:</td>
                        <td>
                            <select name="pruefungsTyp">
                                <?php
                                    if($pruefungstypen !== false)
                                    {
                                        foreach ($pruefungstypen as $typ) {
                                            echo '<option value="'.$typ->pruefungstyp_kurzbz.'">'.$typ->beschreibung.'</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Methode:</td>
                        <td><textarea placeholder="Multiple Choice, etc." rows="5" cols="20" name="methode"></textarea></td>
                    </tr>
                    <tr>
                        <td>Einzelprüfung:</td>
                        <td><input type="checkbox" name="einzelpruefung"></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Lehrveranstaltungen:</td>
                        <td>
                            <div id="lvDropdowns">
                                <select id="lvDropdown1" onchange="lehrveranstaltungDropdownhinzufuegen(this, false);" name="lv[]">
                                    <option value="null">Lehrveranstaltung auswählen...</option>
                                <?php
                                    $studiengang = new studiengang();
                                    foreach($lehrveranstaltung->lehrveranstaltungen as $result)
                                    {
                                        $studiengang->load($result->studiengang_kz);
                                        echo '<option value="'.$result->lehrveranstaltung_id.'">'.$studiengang->kurzbzlang.' | <b>'.$result->bezeichnung.'</b> ('.$result->lehrform_kurzbz.')</option>';
                                    }
                                ?> 
                                </select></br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><a name="termin">Termin:</a></td>
                        <td>
                            <div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Datum</th>
                                            <th>Von</th>
                                            <th>bis</th>
                                        </tr>
                                    </thead>
                                    <tbody id="prfTermin">
                                        <tr>
                                            <td>
                                                <input type="text" id="termin1" name="termin[]">
                                            </td>
                                            <td>
                                                <input type="time" placeholder="00:00" name="terminBeginn[]">
                                            </td>
                                            <td>
                                                <input type="time" placeholder="00:00" name="terminEnde[]">
                                            </td>
                                            <td>
                                                <input type="number" placeholder="0" min="0" name="minTeilnehmer[]">
                                            </td>
                                            <td>
                                                <input type="number" placeholder="10" min="0" name="maxTeilnehmer[]">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <a href="#termin" onclick="terminHinzufuegen();">Termin hinzufügen</a>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td><td><input type="submit" value="Speichern"></td>
                    </tr>
                </table>
            </form>
        </div>
        <div>
            <h2>Prüfungen</h2>
            <div style="width: 75%;">
                <table class="tablesorter" id="prfTable">
                    <thead>
                        <tr>
                            <th>Titel</th>
                            <th>Studiensemester</th>
                            <th>Lehrveranstaltungen</th>
                            <th>Termine</th>
                            <th>Methode</th>
                            <th>Prüfungstyp</th>
                            <th>Einzelprüfung</th>
                            <th>Mitarbeiter</th>
                            <th>storniert</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($pruefungen->result as $prf)
                            {
                                echo 
                                '<tr '.(($prf->storniert == true) ? "style='text-decoration: line-through'" : "").'>
                                    <td><a href="pruefungstermin_festlegen.php?method=update&id='.$prf->pruefung_id.'&prfFensterId='.$prf->pruefungsfenster_id.'">'.$prf->titel.'</a></td>
                                    <td>'.$prf->studiensemester_kurzbz.'</td>
                                    <td>';
                                        foreach($prf->lehrveranstaltungen as $lv)
                                        {
                                            $lehrveranstaltung = new lehrveranstaltung();
                                            $lehrveranstaltung->load($lv->lehrveranstaltung_id);
                                            echo $lehrveranstaltung->bezeichnung.'</br>';
                                        }
                                        echo '</td>
                                    <td>
                                        <table>
                                            <tbody>';
                                                foreach($prf->termine as $termine)
                                                {
                                                    echo '<tr><td>'.date('d.m.Y H:i', strtotime($termine->von))."</td><td>".date('d.m.Y H:i', strtotime($termine->bis)).'</td></tr>';
                                                    //date('Y-m-d H:i', strtotime($termine->von));
                                                }
                                        echo '</tbody>
                                        </table>
                                    </td>
                                    <td>'.(($prf->methode == null) ? "keine Methode angegeben" : $prf->methode).'</td>
                                    <td>'.$prf->pruefungstyp_kurzbz.'</td>
                                    <td>'.(($prf->einzeln == true) ? "TRUE" : "FALSE").'</td>
                                    <td>'.$prf->mitarbeiter_uid.'</td>
                                    <td>'.(($prf->storniert == true) ? "TRUE" : "FALSE").'</td>
                                </tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
            }
            else if ($_GET["method"] == "update")
            {
                $pruefung_id = isset($_GET["id"]) ? $_GET["id"] : false; 

                if(is_numeric($pruefung_id))
                {
                    $pruefung = new pruefungCis();
                    $pruefung->load($pruefung_id);
                    $pruefung->getLehrveranstaltungenByPruefung();
                    $pruefung->getTermineByPruefung();
                }
                else
                {
                    header("Location: pruefungstermin_festlegen.php");
                }
                
                
        ?>
        <h1>Prüfung bearbeiten</h1>
        <div>
            <form method="post" action="pruefungstermin_festlegen.php">
                <input type="hidden" name="id" value="<?php echo $pruefung->pruefung_id;?>">
                <input type="hidden" name="method" value="update">
                <table>
                    <tr>
                        <td>Titel:</td>
                        <td>
                            <input type="text" name="titel" value="<?php echo $pruefung->titel;?>">
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Beschreibung:</td>
                        <td>
                            <textarea name="beschreibung" rows="5" cols="20"><?php echo $pruefung->beschreibung; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td>Studiensemester:</td>
                        <td>
                            <select id="studiensemester" name="studiensemester" onchange="loadPruefungsfenster();" onload="loadPruefungsfenster();">
                                <?php
                                foreach ($studiensemester->studiensemester as $result) 
                                {
                                    if($pruefung->studiensemester_kurzbz == $result->studiensemester_kurzbz)
                                    {
                                        echo '<option selected>'.$result->studiensemester_kurzbz.'</option>';
                                    }
                                    else
                                    {
                                        echo '<option>'.$result->studiensemester_kurzbz.'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungsfenster:</td>
                        <td>
                            <select id="pruefungsfenster" name="pruefungsfenster">
                                <!--Daten werden durch JavaScript geladen-->
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Prüfungstyp:</td>
                        <td>
                            <select name="pruefungsTyp">
                                <?php
                                    if($pruefungstypen !== false)
                                    {
                                        foreach ($pruefungstypen as $typ) {
                                            if($typ->pruefungstyp_kurzbz == $pruefung->pruefungstyp_kurzbz)
                                            {
                                                echo '<option selected value="'.$typ->pruefungstyp_kurzbz.'">'.$typ->beschreibung.'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="'.$typ->pruefungstyp_kurzbz.'">'.$typ->beschreibung.'</option>';
                                            }
                                            
                                        }
                                    }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Methode:</td>
                        <td><textarea placeholder="Multiple Choice, etc." rows="5" cols="20" name="methode"><?php echo $pruefung->methode; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Einzelprüfung:</td>
                        <td><input type="checkbox" <?php echo (($pruefung->einzeln == true) ? " checked ": "") ?> name="einzelpruefung"></td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Lehrveranstaltungen:</td>
                        <td>
                            <div id="lvDropdowns">
                                <?php
                                    $studiengang = new studiengang();
                                    $lv_temp = array();
                                    foreach($pruefung->lehrveranstaltungen as $lv)
                                    {
                                        array_push($lv_temp, $lv->lehrveranstaltung_id);
                                    }
                                    foreach($lehrveranstaltung->lehrveranstaltungen as $result)
                                    {
                                        $studiengang->load($result->studiengang_kz);

                                        if(in_array($result->lehrveranstaltung_id, $lv_temp))
                                        {
                                            echo '<span value="'.$result->lehrveranstaltung_id.'">'.$studiengang->kurzbzlang.' | <b>'.$result->bezeichnung.'</b> ('.$result->lehrform_kurzbz.')</span><a href="pruefungstermin_festlegen.php?method=update&action=deleteLv&lvId='.$result->lehrveranstaltung_id.'&id='.$pruefung_id.'"> löschen</a></br>';
                                        }
                                    }
                                ?> 
                                <div id="lvDropdowns">
                                    <select id="lvDropdown1" onchange="lehrveranstaltungDropdownhinzufuegen(this, false);" name="lv[]">
                                        <option value="null">Lehrveranstaltung auswählen...</option>
                                    <?php
                                        $studiengang = new studiengang();
                                        foreach($lehrveranstaltung->lehrveranstaltungen as $result)
                                        {
                                            $studiengang->load($result->studiengang_kz);
                                            echo '<option value="'.$result->lehrveranstaltung_id.'">'.$studiengang->kurzbzlang.' | <b>'.$result->bezeichnung.'</b> ('.$result->lehrform_kurzbz.')</option>';
                                        }
                                    ?> 
                                    </select></br>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;"><a name="termin">Termin:</a></td>
                        <td>
                            <div>
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Datum</th>
                                            <th>Von</th>
                                            <th>bis</th>
                                            <th>min. Teilnehmer</th>
                                            <th>max. Teilnehmer</th>
                                            <th>Aktion</th>
                                        </tr>
                                    </thead>
                                    <tbody id="prfTermin">
                                        <?php 
                                            foreach($pruefung->termine as $prfTermin)
                                            {
                                                echo '<tr>
                                                    <input type="hidden" name="termin_id[]" value='.$prfTermin->pruefungstermin_id.'>
                                                        <td>
                                                            <input type="text" id="termin1" name="termin[]" value="'.date("d.m.Y",strtotime($prfTermin->von)).'">
                                                        </td>
                                                        <td>
                                                            <input type="time" placeholder="00:00" name="terminBeginn[]" value="'.date("H:i",strtotime($prfTermin->von)).'">
                                                        </td>
                                                        <td>
                                                            <input type="time" placeholder="00:00" name="terminEnde[]" value="'.date("H:i",strtotime($prfTermin->bis)).'">
                                                        </td>
                                                        <td>
                                                            <input type="number" placeholder="0" min="0" name="minTeilnehmer[]" value="'.$prfTermin->min.'">
                                                        </td>
                                                        <td>
                                                            <input type="number" placeholder="10" min="0" name="maxTeilnehmer[]" value="'.$prfTermin->max.'">
                                                        </td>
                                                        <td>
                                                            <a href="pruefungstermin_festlegen.php?method=update&action=deleteTermin&termin_id='.$prfTermin->pruefungstermin_id.'&id='.$pruefung->pruefung_id.'">löschen</a>
                                                        </td>
                                                    </tr>';
                                            }
                                        ?>
                                        
                                    </tbody>
                                </table>
                            </div>
                            <a href="#termin" onclick="terminHinzufuegen('Neu');">Termin hinzufügen</a>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <a href="pruefungstermin_festlegen.php?action=storno&id=<?php echo $pruefung->pruefung_id; ?>">Prüfung stornieren</a>
                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td>
                            <input type="submit" value="Speichern">
                            <a href="pruefungstermin_festlegen.php"><input type="button" value="Abbrechen"></a>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <?php
            }
        ?>
    </body>
</html>
