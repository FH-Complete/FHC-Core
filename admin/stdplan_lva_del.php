<?php
/**
 * Liste der in FAS geloeschten Lehrveranstaltungen
 */
	include('../vilesci/config.inc.php');
	include('../include/functions.inc.php');

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Variablen laden
	$error_msg.=loadVariables($conn,$REMOTE_USER);

	if ($type=='new')
	{
		$sql_query="INSERT INTO tbl_personmailgrp (uid, mailgrp_kurzbz) VALUES ('".$_POST['personid']."','".$_POST['mailgrpid']."')";
		//echo $sql_query;
		if(!$result=pg_exec($conn, $sql_query))
			$error=pg_errormessage($conn);
	}
	elseif ($type=='del')
	{
		$sql_query='DELETE FROM tbl_lehrveranstaltung WHERE lehrveranstaltung_id='.$_GET['lva_id'];
		if(!$result=pg_exec($conn, $sql_query))
			$error=pg_errormessage($conn);
	}
	elseif ($type=='delall')
	{
		$sql_query='DELETE FROM tbl_stundenplan WHERE lehrveranstaltung_id='.$_GET['lva_id'];
		$sql_query.=';DELETE FROM tbl_stundenplandev WHERE lehrveranstaltung_id='.$_GET['lva_id'];
		$sql_query.=';DELETE FROM tbl_lehrveranstaltung WHERE lehrveranstaltung_id='.$_GET['lva_id'];
		if(!$result=pg_exec($conn, $sql_query))
			$error=pg_errormessage($conn);
	}

	$sql_query="SELECT * FROM tbl_lehrveranstaltung WHERE fas_id NOT IN
		(SELECT fas_id FROM vw_fas_lehrveranstaltung WHERE  (studiensemester_kurzbz='$semester_aktuell' ) ) AND (fas_id!=0 OR fas_id IS NOT NULL) AND ( (studiensemester_kurzbz='$semester_aktuell' ) )";

	if(!$result=pg_exec($conn, $sql_query))
		die (pg_errormessage($conn));
	$num_rows=pg_numrows($result);
?>

<html>
<head>
<title>Detail Studenten</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<LINK rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
</head>

<body class="background_main">
<?php
	if (isset($error))
		echo $error;
	elseif ($type=='del')
		echo 'Lehrveranstaltung wurde gel&ouml;scht!';
?>
<h2><a href="index.html">Admin</a> Lehrveranstaltungen (im FAS geloescht)</h2>
Anzahl:<?php echo $num_rows; ?>
<br>
<br>
<table border="0">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>">
	<th></th><th></th><th>ID</th><th>lvnr</th><th>unr</th><th>lektor</th><th>Lehrfach</th>
	<th>KZ</th><th>FB</th><th>S</th><th>V</th><th>G</th><th>Einheit</th>
	<th>Raumtyp</th><th>RaumtypA</th><th>SS</th><th>SB</th>
	<th>WR</th><th>KW</th><th>Semester</th><th>Anmerkung</th>
	<th>fas_id</th></tr>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$row=pg_fetch_object($result,$i);
		?>
		<tr class="liste<?php echo ($i%2); ?>">
		<td><a href="stdplan_lva_del.php?type=del&lva_id=<?php echo $row->lehrveranstaltung_id; ?>" class="linkblue">Delete</a></td>
		<td><a href="stdplan_lva_del.php?type=delall&lva_id=<?php echo $row->lehrveranstaltung_id; ?>" class="linkblue">DeleteAll</a></td>
		<td><?php echo $row->lehrveranstaltung_id; ?></td>
		<td><?php echo $row->lvnr; ?></td>
		<td><?php echo $row->unr; ?></td>
		<td><?php echo $row->lektor; ?></td>
		<td><?php echo $row->lehrfach_nr; ?></td>
		<td><?php echo $row->studiengang_kz; ?></td>
		<td><?php echo $row->fachbereich_id; ?></td>
		<td><?php echo $row->semester; ?></td>
		<td><?php echo $row->verband; ?></td>
		<td><?php echo $row->gruppe; ?></td>
		<td><?php echo $row->einheit_kurzbz; ?></td>
		<td><?php echo $row->raumtyp; ?></td>
		<td><?php echo $row->raumtypalternativ; ?></td>
		<td><?php echo $row->semesterstunden; ?></td>
		<td><?php echo $row->stundenblockung; ?></td>
		<td><?php echo $row->wochenrythmus; ?></td>
		<td><?php echo $row->start_kw; ?></td>
		<td><?php echo $row->studiensemester_kurzbz; ?></td>
		<td><?php echo $row->anmerkung; ?></td>
		<td><?php echo $row->fas_id; ?></td>
		</tr>
		<?php
	}
?>
</table>

</body>
</html>
