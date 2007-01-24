<?php
	include('../../config.inc.php');
	$adress='pam@technikum-wien.at';
	if (!isset($REMOTE_USER))
		$REMOTE_USER='pam';
	$uid=$REMOTE_USER;

	if (isset($_GET['uid']))
		$uid=$_GET['uid'];
	if (isset($_GET['stdsem']))
		$stdsem=$_GET['stdsem'];

	if ($uid!=$REMOTE_USER)
	{
		mail($adress,"Unerlaubter Zugriff auf Lehrveranstaltungen",
			"User $REMOTE_USER hat versucht die LVAs von User $uid zu betrachten!",
			"From: vilesci@technikum-wien.at");
		die("Keine Berechtigung!");
	}

	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Studiensemester abfragen.
	$sql_query='SELECT * FROM public.tbl_studiensemester WHERE ende>=now() ORDER BY start';
	$result_stdsem=pg_exec($conn, $sql_query);
	$num_rows_stdsem=pg_numrows($result_stdsem);
	if (!isset($stdsem))
		$stdsem=pg_result($result_stdsem,0,"studiensemester_kurzbz");


	//Lehrveranstaltungen abfragen.
	$sql_query="SELECT * FROM campus.vw_lehreinheit
		WHERE studiensemester_kurzbz='$stdsem' AND mitarbeiter_uid='$uid'";
	$sql_query.=" ORDER BY stg_kurzbz,semester,verband,gruppe";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Reservierungsliste</title>
	<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>
<body>
	<H2>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td>
				&nbsp;<a href="index.php">Userprofil</a> &gt;&gt;
				&nbsp;Lehrveranstaltungen (<?php echo $stdsem;?>)
			</td>
			<td align="right"><A href="../help/index.html" class="hilfe" target="_blank">HELP&nbsp;</A></td>
		</tr>
		</table>
	</H2>
	<?php
	for ($i=0;$i<$num_rows_stdsem;$i++)
	{
		$row=pg_fetch_object($result_stdsem);
		echo '<A href="lva_liste.php?uid='.$uid.'&stdsem='.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</A> - ';
	}
	if ($num_rows>0)
	{
		echo '<BR><BR><table border="0">';
		echo '<tr class="liste"><th>LVNR</th><th>Lehrfach</th><th>Lehrform</th><th>Bezeichnung</th><th>Lektor</th><th>STG</th><th>S</th><th>V</th><th>G</th><th>Gruppe</th><th>Raumtyp</th><th>Alternativ</th><th>Block</th><th>WR</th><th>Std</th><th>KW</th><th>Anmerkung</th></tr>';
		for ($i=0; $i<$num_rows; $i++)
		{
			$zeile=$i % 2;
			$row=pg_fetch_object($result);

			echo '<tr class="liste'.$zeile.'">';
			echo '<td>'.$row->lvnr.'</td>';
			echo '<td>'.$row->lehrfach.'</td>';
			echo '<td>'.$row->lehrform_kurzbz.'</td>';
			echo '<td>'.$row->lehrfach_bez.'</td>';
			echo '<td>'.$row->lektor.'</td>';
			echo '<td>'.$row->stg_kurzbz.'</td>';
			echo '<td>'.$row->semester.'</td>';
			echo '<td>'.$row->verband.'</td>';
			echo '<td>'.$row->gruppe.'</td>';
			echo '<td>'.$row->gruppe_kurzbz.'</td>';
			echo '<td>'.$row->raumtyp.'</td>';
			echo '<td>'.$row->raumtypalternativ.'</td>';
			echo '<td>'.$row->stundenblockung.'</td>';
			echo '<td>'.$row->wochenrythmus.'</td>';
			echo '<td>'.$row->semesterstunden.'</td>';
			echo '<td>'.$row->start_kw.'</td>';
			echo '<td>'.$row->anmerkung.'</td>';
			echo '</tr>';
		}
		echo '</table>';
	}
	else
		echo 'Keine Datens&auml;tze vorhanden!<BR>';
?>
<BR>Fehler und Feedback bitte an den betreffenden Studiengang!<BR>
<HR>
<H3>Hinweis</H3>
Sonderauftr&auml;ge wie zum Beispiel Praktikums- und Diplomandenbetreuung werden hier nicht angezeigt!<BR>
<H3>Erkl&auml;rung</H3>
	&nbsp;&nbsp;<strong> LVNR: </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Interne FAS-Nummer der Lehrveranstaltung<BR>
	&nbsp;&nbsp;<strong> STG-S-V-G: </strong>Studiengang-Semester-Verband-Gruppe<BR>
	&nbsp;&nbsp;<strong> Einheit: </strong>&nbsp;&nbsp;Spezialgruppen (Module, Projektgruppen, Spezialisierungsgruppen)<BR>
	&nbsp;&nbsp;<strong> Block: </strong>&nbsp;&nbsp;&nbsp;&nbsp;Stundenblockung (1->Einzelstunden; 2->Doppelstunden; ...)<BR>
	&nbsp;&nbsp;<strong> WR: </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Wochenrythmus (1->jede Woche; 2->jede 2. Woche; ...)<BR>
	&nbsp;&nbsp;<strong> Std: </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;gesamte Semesterstunden<BR>
	&nbsp;&nbsp;<strong> KW: </strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kalenderwoche in der die Lehrveranstaltung startet<BR>
</body>
</html>