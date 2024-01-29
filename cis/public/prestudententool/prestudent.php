<?php

/* Copyright (C) 2012 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 *
 */

require_once('../../../config/cis.config.inc.php');
require_once('auth.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/prestudent.class.php');
require_once('../../../include/studiengang.class.php');

// Sprache setzen
if(isset($_GET['lang']))
    setSprache($_GET['lang']);

$datum = new datum(); 
$sprache = getSprache(); 
$p = new phrasen($sprache);

// Bei login wird session gesetzt
$person_id = $_SESSION['prestudent/person_id'];

$person = new person(); 
$person->load($person_id);

$prestudent = new prestudent(); 
// hole prestudenten anhand person_id
$prestudent->getPrestudenten($person->person_id);
if(isset($prestudent->result[0]->studiengang_kz ))
    $studiengang_kz = $prestudent->result[0]->studiengang_kz; 
else
    $studiengang_kz = '';

$studiengang = new studiengang(); 
$studiengang->load($studiengang_kz);

$method = (isset($_GET['method'])?$_GET['method']:''); 

// emailadresse der assistenz
$mail = ($studiengang->email!='')?$studiengang->email:MAIL_ADMIN;

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Prestudententool</title>
        <link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
        <link href="../../../include/js/tablesort/table.css" rel="stylesheet" type="text/css">
        <script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
    </head>
    <body>
		<table width="100%" border="0"> <!-- Anzeige Header -->
			<tr>
				<td align="left" width="33%"><a href="prestudent.php">Administration</a></td>
				<td align="center" width="33%"><?php echo $person->titelpre." ".$person->vorname." ".$person->nachname." ".$person->titelpost?>
				<td align ="right" width="33%"><?php 		
				echo $p->t("global/sprache")." ";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> | 
				<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?></td>
			</tr>
		</table>
        <?php
        if($method == 'profil') // Profil anzeigen
        {
            echo '
                <form method="POST" action="prestudent.php?method=profil" name="ProfilForm">
                    <table align="center" style="margin-top:5%;" border="0">
                        <tr>
                            <td>'.$p->t('global/vorname').':</td>
                            <td>'.$person->vorname.'</td>
                            <td rowspan="5"><img id="personimage" src="../../public/bild.php?src=person&person_id='.$person->person_id.'" alt="'.$person->person_id.'" height="100px" width="75px"></td>
                        </tr>
                        <tr>
                            <td>'.$p->t('global/nachname').':</td>
                            <td>'.$person->nachname.'</td>
                        </tr>
                        <tr>
                            <td>'.$p->t('global/geburtsdatum').':</td>
                            <td>'.$datum->formatDatum($person->gebdatum, 'd.m.Y').'</td>
                        </tr>
                        <tr>
                            <td>'.$p->t('global/titel').' Pre</td>
                            <td>'.$person->titelpre.'</td>
                        <tr>
                        <tr>
                            <td>'.$p->t('global/titel').' Post</td>
                            <td>'.$person->titelpost.'</td>
                        </tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td><input type="button" value="'.$p->t('global/zurueck').'" onclick="window.location.href=\'prestudent.php\';"></td>
                            <td colspan="2"><a href="mailto:'.$mail.'">'.$p->t('global/emailAnAssistenz').'</a></td>
                        </tr>
                    </table>
                </form>';
        }
        else // Hauptmenü anzeigen
        {
            echo '
            <br><br><br><br>
            <fieldset>
                <table align ="center" border="0"> <!-- Anzeige Hauptmenü -->
                    <tr>	
                        <td>1. <a href="prestudent.php?method=profil">'.$p->t('incoming/profil').'</a></td>
                    </tr>
                    <tr>';
                    echo "<td>2. <a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$person->person_id\",\"BildUpload\", \"height=500,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>".$p->t('profil/bildHochladen')."</a></td>";
        echo'      </tr>
                </table>
                    <table width="100%" border="0">
                        <tr>
                            <td align="right"><a href="logout.php">'.$p->t('global/abmelden').'</a> </td>
                        </tr>
                </table>
            </fieldset>';
        }
            ?>
    </body>
</html>