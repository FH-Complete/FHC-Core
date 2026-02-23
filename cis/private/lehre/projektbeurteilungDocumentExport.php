<?php
/* Erstellt einen Lehrauftrag im PDF Format
 *
 * Erstellt ein XML File Transformiert dieses mit
 * Hilfe der XSL-FO Vorlage aus der DB und generiert
 * daraus ein PDF
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/projektarbeit.class.php');

if (!isset($_GET['betreuerart_kurzbz']) || !isset($_GET['person_id']) || !isset($_GET['projektarbeit_id']))
	die('Fehlerhafte Parameteruebergabe');

// passende Vorlage holen
$projektarbeitVorlage = new projektarbeit();
$vorlage = $projektarbeitVorlage->getVorlage($_GET['projektarbeit_id'], $_GET['betreuerart_kurzbz']);

if ($vorlage == null)
	die("<html><body><h3>".$projektarbeitVorlage->errormsg."</h3></body></html>");

// weiterleiten auf Dokumentexport
header('Location: ' . APP_ROOT . '/cis/private/pdfExport.php?xml=projektarbeitsbeurteilung.xml.php'
	.'&xsl='.$vorlage.'&betreuerart_kurzbz=' . $_GET['betreuerart_kurzbz']
	. '&projektarbeit_id=' . $_GET['projektarbeit_id'] . '&person_id=' . $_GET['person_id']
);
die();
