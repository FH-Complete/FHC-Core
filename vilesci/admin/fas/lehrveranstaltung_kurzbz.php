<?php
/**
 * ermoeglicht das Eintragen der Kurzbezeichnung bei Lehrveranstaltungen ohne kurzbz
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>FAS - Lehrveranstaltung</title>
</head>
<body onload="document.getElementsByTagName('input')[0].focus()">

<H1>FAS - Lehrveranstaltung</h1>
<?php
	require_once('../../config.inc.php');

	//DB Verbindung herstellen
	if (!$conn = @pg_pconnect(CONN_STRING_FAS))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$qry = "SET CLIENT_ENCODING TO 'LATIN9';SELECT * FROM studiengang order by studiengangsart, kuerzel";

	if(!$result = pg_query($conn, $qry))
		die('Fehler beim lesen aus der DB');

	if(isset($_GET['studiengang']))
		$stg = $_GET['studiengang'];
	else
		$stg = '';

	if(isset($_POST['kurzbz']) && isset($_GET['lehrveranstaltung_id']) && is_numeric($_GET['lehrveranstaltung_id']))
	{
		$qry = "UPDATE lehrveranstaltung SET kurzbezeichnung='".addslashes(strtoupper($_POST['kurzbz']))."' WHERE lehrveranstaltung_pk='".$_GET['lehrveranstaltung_id']."';";
		if(pg_query($conn, $qry))
			echo "Erfolgreich gespeichert";
		else
			echo "<span style='font-color: Red;'>Fehler beim Speichern</span>";
	}

	echo "<form name='frm_stg' action='$PHP_SELF' method='GET'>";
	//Drop Down fuer Studiengang
	echo "<SELECT name='studiengang' onchange='javascript: document.frm_stg.submit();'>";
	while($row=pg_fetch_object($result))
	{
		switch ($row->studiengangsart)
		{
			case 1: $art='B'; break;
			case 2: $art='M'; break;
			case 3: $art='D'; break;
			default: $art='';
		}
		if($row->studiengang_pk==$stg)
			echo "<OPTION value='$row->studiengang_pk' selected>($art) $row->kuerzel</OPTION>";
		else
			echo "<OPTION value='$row->studiengang_pk'>($art) $row->kuerzel</OPTION>";
	}
	echo "</SELECT>";
	echo '</form>';

	//Lehrveranstaltungen ohne kurzbezeichnung holen
	if($stg=='')
		die('Bitte einen Studiengang auswaehlen');
	$qry = "SELECT *, ausbildungssemester.name as ausbildungssemestername, lehrveranstaltung.name as lehrveranstaltungname FROM lehrveranstaltung, ausbildungssemester, studiensemester WHERE lehrveranstaltung.ausbildungssemester_fk=ausbildungssemester.ausbildungssemester_pk AND lehrveranstaltung.studiensemester_fk=studiensemester.studiensemester_pk AND (lehrveranstaltung.kurzbezeichnung is null OR lehrveranstaltung.kurzbezeichnung='') AND lehrveranstaltung.studiengang_fk='".addslashes($stg)."' ORDER BY lehrveranstaltung_pk";

	if(!$result = pg_query($conn, $qry))
		die('Fehler beim lesen aus der Datenbank');
	$anz = pg_num_rows($result);
	echo " $anz Datensaetze gefunden";
	//Tabelle ausgeben
	echo '<table>';
	echo '<tr class="liste"><td>ID</td><td>Semester</td><td>StSem</td><td>Bezeichnung</td><td>Vorschlag</td><td>Kurzbezeichnung</td>';
	$i=0;
	while($row = pg_fetch_object($result))
	{
		//Vorschlag suchen
		$kuerzel='';
		$qry = "SELECT kurzbezeichnung FROM lehrveranstaltung WHERE studiengang_fk='$row->studiengang_fk' AND ausbildungssemester_fk='$row->ausbildungssemester_fk' AND name='$row->lehrveranstaltungname' AND kurzbezeichnung is not null AND kurzbezeichnung<>''";
		$result_kurzbz = pg_query($conn, $qry);
		while($row_kurzbz = pg_fetch_object($result_kurzbz))
			$kuerzel .= $row_kurzbz->kurzbezeichnung.',';

		$i++;
		echo '<tr class="liste'.($i%2).'">';
		echo "<td>$row->lehrveranstaltung_pk</td>";
		echo "<td>$row->ausbildungssemestername</td>";
		echo "<td>".($row->art=='1'?'WS':'SS')."$row->jahr </td>";
		echo "<td>$row->lehrveranstaltungname</td>";
		echo "<td>$kuerzel</td>";
		//Textfeld zum eingeben der Kurzbezeichnung
		echo "<td><form action='$PHP_SELF?studiengang=$stg&lehrveranstaltung_id=$row->lehrveranstaltung_pk' method='POST'><input type='text' size='5' maxlength='5' tabindex='$i' name='kurzbz'><input type='submit' value='Speichern'></form></td>";
		echo '</tr>';
	}
	echo '</table>';
?>
</body>
</html>