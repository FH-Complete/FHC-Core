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
require_once('../../../../include/lehrveranstaltung.class.php');


$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

//if(!$rechte->isBerechtigt('lehre/pruefungsanmeldung'))
//	die('Sie haben keine Berechtigung für diese Seite');


$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->load_lva_student("if11b044");
//foreach ($lehrveranstaltung->lehrveranstaltungen as $lv)
//{
//    echo $lv->bezeichnung."</br>";
//}
//var_dump($lehrveranstaltung->lehrveranstaltungen[1]);

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Prüfungsanmeldung</title>
        <script src="../../../../include/js/datecheck.js"></script>
        <script src="../../../../include/js/jquery1.9.min.js"></script>
        <script src="./pruefung.js"></script>
        <link rel="stylesheet" href="../../../../skin/jquery-ui-1.9.2.custom.min.css">
        <link rel="stylesheet" href="../../../../skin/fhcomplete.css">
        <link rel="stylesheet" href="../../../../skin/style.css.php">
        <link rel="stylesheet" href="../../../../skin/tablesort.css">
        <style type="text/css">
            #prfDetails {
                /*border: 1px solid black;*/
            }
            
            #anmeldung {
                width: 30%;
            }
            
            div {
                float: left;
            }
        </style>
        
    </head>
    <body>
        <script>
            $(document).ready(function(){
               loadPruefungen(); 
            });
        </script>
        <h1>Prüfungsanmeldung</h1>
        <div id="anmeldung">
            <form action="pruefungsanmeldung.php" method="POST">
                <table>
                    <tr>
                        <td>
                            Prüfung:
                        </td>
                        <td>
                            <select id="pruefungen" onChange="showPruefungsDetails();">
                                <!--Prüfungen werden durch Js geladen-->
                                <option value="null">Prüfung auswählen</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Termin:
                        </td>
                        <td>
                            <select id="prfTermine" disabled="true">
                                <!-- verfügbare Termine werden durch JS geladen-->
                                <option value="null">Zuerst Prüfung auswählen</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">
                            Bemerkungen:
                        </td>
                        <td>
                            <textarea id="prfWuensche" rows="7" cols="20"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input type="button" value="Anmelden" onclick="saveAnmeldung();"/></td>
                    </tr>
                </table> 
            </form>
            <div id="message">
                
            </div>
        </div>
        <div>
            <h1>Prüfungsdetails</h1>
            <div id="prfDetails">
                <span>Typ: </span><span id="prfTyp"></span></br>
                <span>Methode: </span><span id="prfMethode"></span></br>
                <span>Beschreibung: </span><span id="prfBeschreibung"></span></br>
            </div>
        </div>
        <?php
        // put your code here
        ?>
    </body>
</html>
