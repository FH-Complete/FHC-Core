<?php
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/pruefungCis.class.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Prüfung</title>
		<link rel="stylesheet" href="../../../../skin/fhcomplete.css">
		<link rel="stylesheet" href="../../../../skin/style.css.php">
	</head>
	<body>
	<h1>Prüfungen</h1>
	<?php

	$pruefungsverwaltung = false;

	$uid = get_uid();
	$db = new basis_db();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	$studiensemester = new studiensemester();
	$pruefung = new pruefungCis();
	$pruefung->getPruefungByMitarbeiter($uid, $studiensemester->getaktorNext());
	if(!empty($pruefung->result) || $rechte->isBerechtigt('lehre/pruefungsanmeldungAdmin'))
		$pruefungsverwaltung = true;

	echo '<ul>';
	if ($rechte->isBerechtigt('lehre/pruefungsfenster'))
		echo '<li><a href="pruefungsfenster_anlegen.php">Prüfungsfenster verwalten</a></li>';

	if($pruefungsverwaltung)
	{
		echo '<li><a href="pruefungstermin_festlegen.php">Prüfungstermine verwalten</a></li>';
		echo '<li><a href="pruefungsanmeldungen_verwalten.php">Anmeldungen verwalten</a></li>';
		echo '<li><a href="pruefungsbewertung.php">Bewertung</a><br><br></li>';
	}

	echo '<li><a href="pruefungsanmeldung.php">Anmeldung zur Prüfung</a></li>';

	echo '</ul>';
	?>
	</body>
</html>
