<?php
/*
 * Copyright 2021 fhcomplete.org
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
 * Authors: Nikolaus Krondraf <krondraf@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');
require_once('../../../../include/mail.class.php');


$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$maildebug = '';
$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$studiensemester = new studiensemester();
$lehrveranstaltung = new lehrveranstaltung();
$lehrveranstaltung->loadLVAfromMitarbeiter(0, $uid, $studiensemester->getaktorNext());
if(empty($lehrveranstaltung->lehrveranstaltungen) && !$rechte->isBerechtigt('lehre/pruefungsterminAdmin'))
	die('Sie haben keine Berechtigung für diese Seite');

if (!ctype_digit($_GET['termin_id']) || !ctype_digit($_GET['pruefung_id']))
	die('Wrong Parameter');
else
{
    $termin_id = $_GET['termin_id'];
    $pruefung_id = $_GET['pruefung_id'];
}

if (isset($_GET["sendmail"], $_GET["message"]) && $_GET["message"] != "")
{
	$pruefung = new pruefungCis($pruefung_id);
	$pruefung->getLehrveranstaltungenByPruefung();
	$lvstr = "";
	foreach ($pruefung->lehrveranstaltungen as $lv)
	{
		$lv_objekt = new lehrveranstaltung($lv->lehrveranstaltung_id);
		$lvstr .= "*".$lv_objekt->bezeichnung."* ";
	}
	$maildebug = 'Mail gesendet an:<br>';
	$anmeldung = new pruefungsanmeldung();
	$anmeldungen = $anmeldung->getAnmeldungenByTermin($termin_id);
	foreach ($anmeldungen as $row)
	{
		$uid = $row->uid;
		$to = $uid.'@'.DOMAIN;
		$from = 'no-reply@'.DOMAIN;
		$subject = $p->t('pruefung/pruefungStorniert');
		$text = $_GET["message"];
		$msg = new mail($to, $from, $subject, $text);
		if ($msg->send())
			$maildebug .= $to." OK<br>";
		else
			$maildebug .= $to.' Fehler<br>';
	}
}

$prfgtermin = new pruefungstermin($termin_id);
$datum = new DateTime(explode(" ", $prfgtermin->von)[0]);
$datum = $datum->format('d.m.Y');
$vonzeit = substr(explode(" ", $prfgtermin->von)[1],0,5);
$biszeit = substr(explode(" ", $prfgtermin->bis)[1],0,5);
$pruefung_id = $prfgtermin->pruefung_id;

$pruefung = new pruefungCis($pruefung_id);
$pruefung->getLehrveranstaltungenByPruefung();
$lvstr = "";
foreach ($pruefung->lehrveranstaltungen as $lv)
{
	$lv_objekt = new lehrveranstaltung($lv->lehrveranstaltung_id);
	$lvstr .= "*".$lv_objekt->bezeichnung."*";
}

$uids = '';
$anmeldung = new pruefungsanmeldung();
$anmeldungen = $anmeldung->getAnmeldungenByTermin($termin_id);
foreach ($anmeldungen as $row)
{
	$uids .= $row->uid.'@'.DOMAIN.'<br>';
}

$nachricht = "Sehr geehrte Studierende,\n\ndie Prüfung zur LV $lvstr am $datum um $vonzeit Uhr wurde abgesagt.";

?><!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title><?php echo $p->t('pruefung/titlePruefungstermin'); ?></title>
	<script src="../../../../include/js/datecheck.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jquery/jquery.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
	<link rel="stylesheet" href="../../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css">
	<script src="./pruefung.js.php"></script>
	<link rel="stylesheet" href="../../../../skin/fhcomplete.css">
	<link rel="stylesheet" href="../../../../skin/style.css.php">
	<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/theme.default.min.css">
	<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css">
	<style type="text/css">
		#message {
			position: fixed;
			top: 0px;
			right: 0px;
			width: 50%;
			height: 2em;
			font-size: 1.5em;
			font-weight: bold;
		}

		.missingFormData {
			border: 2px solid red;
			outline: 2px solid red;
		}

		.modalOverlay {
			position: fixed;
			width: 100%;
			height: 100%;
			top: 0px;
			left: 0px;
			background-color: rgba(0,0,0,0.3); /* black semi-transparent */
		}

		#prfDetails, #prfVerwaltung {
			margin: 1em;
		}
	</style>
	</head>
	<body style="padding-top:20px">
		<center>
        <div id="message"></div>

		<form name="delete_termin" action="./pruefungstermin_loeschen.php" method="get">
            <table>
                <tr>
                    <td><?php echo $p->t('global/lehrveranstaltung'); ?></td>
                    <td><b><?php echo $lvstr; ?></b></td>
                </tr>
                <tr>
                    <td><?php echo $p->t('global/datum'); ?></td>
                    <td><?php echo $datum ?></td>
                </tr>
                <tr>
                    <td><?php echo $p->t('global/von'); ?></td>
                    <td><?php echo $vonzeit ?> Uhr</td>
                </tr>
                <tr>
                    <td><?php echo $p->t('global/bis'); ?></td>
                    <td><?php echo $biszeit ?> Uhr</td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td><?php echo $p->t('pruefung/pruefungsbewertungAnmeldungen'); ?>:</td>
                    <td><?php echo $uids; ?></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <br>***<br>
                            <textarea cols="30" rows="10" name="message" placeholder="Bitte Nachricht an Studierende eingeben"><?php echo $nachricht ?></textarea>
                        <br>***<br>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <p style="color: red; font-weight: bold;">Das Infomail ist optional und muss bei Bedarf vor dem Löschen des Termins versendet werden!</p>
                        <input type="submit" value="Sende Infomail">
                        <a href='#' onclick='terminLoeschenOhneLaden("<?php echo $pruefung_id ?>", "<?php echo $termin_id ?>");'>
                            <input type="button" name="loeschen" value="<?php echo $p->t('global/loeschen'); ?>">
                        </a>
                    </td>
                </tr>
                <tr><td colspan="2"><?php echo $maildebug; ?></td></tr>
            </table>
            <input type="hidden" name="pruefung_id" value="<?php echo $pruefung_id ?>"/>
            <input type="hidden" name="termin_id" value="<?php echo $termin_id; ?>"/>
            <input type="hidden" name="sendmail" value="1"/>
		</form>
	</center>
	</body>
</html>
