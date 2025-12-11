<?php


require_once './session_init.php';

require_once('../../config/cis.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once '../../include/externe_ueberwachung.class.php';
if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

if (isset($_SESSION['externe_ueberwachung']) && $_SESSION['externe_ueberwachung'] === true)
{
	$ueberwachung = new externeUeberwachung();
	$url = $ueberwachung->start($_SESSION['prestudent_id'], $_SESSION['reihungstestID'], $_SESSION['sprache']);
	$urlSafe = htmlspecialchars($url, ENT_QUOTES);
	header("Location: $urlSafe");
	$_SESSION['externe_ueberwachung_verified'] = true;
}