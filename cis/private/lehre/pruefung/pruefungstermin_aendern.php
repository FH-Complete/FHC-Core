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
 * Authors: Gerald Raab <raab@technikum-wien.at>
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

if (!ctype_digit($_GET['termin_id']))
	die('Wrong Parameter');
else
	$termin_id = $_GET['termin_id'];

if (isset($_GET["speichern"]))
{
	$prfgtermin = new pruefungstermin($termin_id);
	$von = $_GET["datum"]." ".$_GET["vonzeit"];
	$bis = $_GET["datum"]." ".$_GET["biszeit"];
	$prfgtermin->von = $von;
	$prfgtermin->bis = $bis;
	$prfgtermin->save();
}

if (isset($_GET["sendmail"]))
{
	$prfgtermin = new pruefungstermin($termin_id);
	$datum = explode(" ", $prfgtermin->von)[0];
	$vonzeit = substr(explode(" ", $prfgtermin->von)[1],0,5);
	$biszeit = substr(explode(" ", $prfgtermin->bis)[1],0,5);
	$pruefung_id = $prfgtermin->pruefung_id;

	$pruefung = new pruefungCis($pruefung_id);
	$pruefung->getLehrveranstaltungenByPruefung();
	$lvstr = "";
	foreach ($pruefung->lehrveranstaltungen as $lv)
	{
		$lv_objekt = new lehrveranstaltung($lv->lehrveranstaltung_id);
		$lvstr .= "*".$lv_objekt->bezeichnung."* ";
	}
	$maildebug = 'Mail sent to:<br>';
	$anmeldung = new pruefungsanmeldung();
	$anmeldungen = $anmeldung->getAnmeldungenByTermin($termin_id);
	foreach ($anmeldungen as $row)
	{
		$uid = $row->uid;
		$to = $uid.'@'.DOMAIN;
		$from = 'no-reply@'.DOMAIN;
		$subject = $p->t('pruefung/emailVerschiebungSubject');
		$text = $p->t('pruefung/emailVerschiebung', array($lvstr, $datum, $vonzeit));
		$msg = new mail($to, $from, $subject, $text);
		if ($msg->send())
			$maildebug .= $to." OK<br>";
		else
			$maildebug .= $to.' Error<br>';
	}
}

$prfgtermin = new pruefungstermin($termin_id);
$datum = explode(" ", $prfgtermin->von)[0];
$vonzeit = substr(explode(" ", $prfgtermin->von)[1],0,5);
$biszeit = substr(explode(" ", $prfgtermin->bis)[1],0,5);
$pruefung_id = $prfgtermin->pruefung_id;

$pruefung = new pruefungCis($pruefung_id);
$pruefung->getLehrveranstaltungenByPruefung();
$lvstr = "";
foreach ($pruefung->lehrveranstaltungen as $lv)
{
	$lv_objekt = new lehrveranstaltung($lv->lehrveranstaltung_id);
	$lvstr .= "*".$lv_objekt->bezeichnung."* ";
}

$uids = '';
$anmeldung = new pruefungsanmeldung();
$anmeldungen = $anmeldung->getAnmeldungenByTermin($termin_id);
foreach ($anmeldungen as $row)
{
	$uids .= $row->uid.'@'.DOMAIN.'<br>';
}

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
		<form name="change_termin" action="./pruefungstermin_aendern.php" method="GET">
		<table>
			<tr>
				<td><?php echo $p->t('global/lehrveranstaltung'); ?></td>
				<td><b><?php echo $lvstr; ?></b></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/datum'); ?></td>
				<td><input type="text" name="datum" value="<?php echo $datum ?>" maxlength="10"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/von'); ?></td>
				<td><input type="text" name="vonzeit" value="<?php echo $vonzeit ?>" maxlength="5"></td>
			</tr>
			<tr>
				<td><?php echo $p->t('global/bis'); ?></td>
				<td><input type="text" name="biszeit" value="<?php echo $biszeit ?>" maxlength="5"></td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="hidden" name="termin_id" value="<?php echo $termin_id ?>">
					<input type="submit" name="speichern" value="<?php echo $p->t('global/speichern'); ?>">
				</td>
			</tr>
			<tr>
				<td><?php echo $p->t('pruefung/pruefungsbewertungAnmeldungen'); ?>:</td>
				<td><?php echo $uids; ?></td>
			</tr>
			<tr>
				<td colspan="2">
				<br>***<br>
				<?php  echo nl2br($p->t('pruefung/emailVerschiebung', array($lvstr, $datum, $vonzeit))); ?>
				<br>***<br>
				</td>
			</tr>
			<tr>
				<td></td>
				<td><a href="pruefungstermin_aendern.php?sendmail=yes&termin_id=<?php echo $termin_id; ?>"><input type="button" name="Sendmail" value="Send Infomail"></a></td>
			</tr>
			<tr><td colspan="2"><?php echo $maildebug; ?></td></tr>
		</table>
		</form>
	</center>
	</body>
</html>
