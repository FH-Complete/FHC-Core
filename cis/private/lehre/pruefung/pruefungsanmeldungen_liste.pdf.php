<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>
 */
 /**
  * Erstellt die Prüfungsliste im PDF Format.
  * Wird keine Dokumentenvorlage gefunden wird stattdessen auf
  * die HTML Version umgeleitet
  */
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/dokument_export.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/pruefungsanmeldung.class.php');
require_once('../../../../include/pruefungCis.class.php');
require_once('../../../../include/pruefungstermin.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/mitarbeiter.class.php');
require_once('../../../../include/student.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');
require_once('../../../../include/vorlage.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user=get_uid();

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$berechtigung = new benutzerberechtigung();
$berechtigung->getBerechtigungen($user);

if (isset($_GET['lehrveranstaltung_id']) && is_numeric($_GET['lehrveranstaltung_id']))
	$lvid = $_GET['lehrveranstaltung_id'];
else
	die('Eine gueltige LvID muss uebergeben werden');

if (isset($_GET['studiensemester']))
	$studiensemester = $_GET['studiensemester'];
else
	die('Eine Studiensemester muss uebergeben werden');

if (isset($_GET['termin_id']) && is_numeric($_GET['termin_id']))
	$termin_id = $_GET['termin_id'];
else
	die('Eine Termin-ID muss uebergeben werden');

$vorlagecheck = new vorlage();
if (!$vorlagecheck->loadVorlage('Pruefungslist'))
	header('Location: pruefungsanmeldungen_liste.php?termin_id='.$termin_id.'&lehrveranstaltung_id='.$lvid.'&studiensemester='.$studiensemester);

if (!$berechtigung->isBerechtigt('admin') && !$berechtigung->isBerechtigt('assistenz') && !check_lektor_lehrveranstaltung($user,$lvid,$studiensemester))
	die('Sie muessen LektorIn der LV oder Admin sein, um diese Seite aufrufen zu koennen');

$output='pdf';

if (isset($_GET['output']) && ($output='odt' || $output='doc'))
	$output=$_GET['output'];



$lv = new lehrveranstaltung();
$lv->load($lvid);

$doc = new dokument_export('Pruefungslist');

$stg = new studiengang();
$stg->load($lv->studiengang_kz);

$studiengang_bezeichnung=$stg->bezeichnung;

$stg->getAllTypes();

$datum = new datum();
$stdsem = new studiensemester($studiensemester);
$pruefungsanmeldung = new pruefungsanmeldung();
$anmeldungen = $pruefungsanmeldung->getAnmeldungenByTermin($termin_id, $lvid, $studiensemester, "bestaetigt");
$lehrveranstaltung = new lehrveranstaltung($lvid);
$einzeln = FALSE;
if (!empty($anmeldungen))
{
	$pruefung = new pruefungCis($anmeldungen[0]->pruefung_id);
	$pruefungstermin = new pruefungstermin($anmeldungen[0]->pruefungstermin_id);
	$mitarbeiter = new mitarbeiter($pruefung->mitarbeiter_uid);
	$dozent = $mitarbeiter->getFullName(FALSE);
	$termin_datum = $datum->formatDatum($pruefungstermin->von, "d.m.Y - H:i");

	$data = array(
		'bezeichnung'=>$lv->bezeichnung,
		'lehrveranstaltung_id'=>$lv->lehrveranstaltung_id,
		'studiengang'=>$studiengang_bezeichnung,
		'studiengang_kz'=>$lv->studiengang_kz,
		'typ'=>$stg->studiengang_typ_arr[$stg->typ],
		'studiensemester'=>$stdsem->bezeichnung,
		'semester'=>$lv->semester,
		'orgform'=>$lv->orgform_kurzbz,
		'dozent'=>$dozent,
		'termin_datum'=>$termin_datum
	);

	$count = 0;
	foreach($anmeldungen as $anmeldung)
	{
		$student = new student($anmeldung->uid);
		$prfTermin = new pruefungstermin($anmeldung->pruefungstermin_id);

		if ($einzeln)
		{
			$date = $datum->formatDatum($prfTermin->von, "Y-m-d H:i:s");
			$date = strtotime($date);
			$date = $date+(60*$pruefungsintervall*($anmeldung->reihung-1));
			$date = $datum->formatDatum($prfTermin->von,"d.m.Y").' - '.date("H:i",$date);
			$count++;
		}
		else
		{
			$date =  $datum->formatDatum($prfTermin->von,"d.m.Y - H:i");
			$count++;
		}
		// Es soll das Datum der Anmeldung angezeigt werden
		if ($anmeldung->datum_anmeldung)
			$date = $datum->formatDatum($anmeldung->datum_anmeldung, "d.m.Y");
		else
			$date = '';

		$data[] = array('student'=>array(
								'count' => $count,
								'vorname' => $student->vorname,
								'nachname' => $student->nachname,
								'matr_nr' => $student->matr_nr,
								'datum' => $date,
								'wuensche' => $anmeldung->wuensche,
								'kommentar' => $anmeldung->kommentar
		));

	}

	$doc->addDataArray($data,'pruefungsliste');

	if (!$doc->create($output))
		die($doc->errormsg);
	$doc->output();
	$doc->close();
}
else
{
	echo $p->t('pruefung/keineBestaetigtenAnmeldungenVorhanden');
}

?>
