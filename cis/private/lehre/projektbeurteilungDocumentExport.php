<?php
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/projektarbeit.class.php');
require_once('../../../include/person.class.php');

if (!isset($_GET['betreuerart_kurzbz']) || !isset($_GET['person_id']) || !isset($_GET['projektarbeit_id']))
	die('Fehlerhafte Parameteruebergabe');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$projektarbeit = new projektarbeit();
$projektarbeit->load($_GET['projektarbeit_id']);

$betreuer = new person();
$betreuer->getPersonFromBenutzer($user);

//Überprüft ob es der Betreuer oder der Student ist
if ($betreuer->person_id !== $_GET['person_id'] && $projektarbeit->student_uid !== $user && !$rechte->isBerechtigt('assistenz'))
	die("<html><body><h3>Sie haben keine Berechtigung für diese Aktion.</h3></body></html>");

$projektarbeitVorlage = new projektarbeit();

// passende Vorlage holen
$vorlage = $projektarbeitVorlage->getVorlage($_GET['projektarbeit_id'], $_GET['betreuerart_kurzbz']);


if ($vorlage == null)
	die("<html><body><h3>".$projektarbeitVorlage->errormsg."</h3></body></html>");

// weiterleiten auf Dokumentexport
header('Location: ' . APP_ROOT . '/cis/private/pdfExport.php?xml=projektarbeitsbeurteilung.xml.php'
	.'&xsl='.$vorlage.'&betreuerart_kurzbz=' . $_GET['betreuerart_kurzbz']
	. '&projektarbeit_id=' . $_GET['projektarbeit_id'] . '&person_id=' . $_GET['person_id']. '&uid=' . $user
);
die();
