<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if ($type=='new')
	{
		$sql_query="INSERT INTO modulzuteilung (lektor_id, modul_id, semester) VALUES ($lektorid,$modulid, $semester)";
		//echo $sql_query;
		$result=pg_exec($conn, $sql_query);
	}
	if ($type=='del')
	{
		$sql_query="DELETE FROM modulzuteilung WHERE id=$id";
		//echo $sql_query;
		$result=pg_exec($conn, $sql_query);
	}

	// Daten für Lektorenauswahl
	$sql_query="SELECT id, nachname, vornamen, uid FROM lektor ORDER BY upper(nachname), vornamen, uid";
	$result_lektor=pg_exec($conn, $sql_query);
	if(!$result_lektor)
		die (pg_errormessage($conn));
	// Daten für Modulauswahl
	$sql_query="SELECT id, kurzbz, bezeichnung FROM einheit ORDER BY kurzbz";
	$result_modul=pg_exec($conn, $sql_query);
	if(!$result_modul)
		die (pg_errormessage($conn));

	// Daten für die Zuteilungen
	if (!isset($order))
		$order='upper(nachname), vornamen, uid';
	$sql_query="SELECT modulzuteilung.id, nachname, nachname, vornamen, uid, einheit.kurzbz AS mdkurzbz";
	$sql_query.=" FROM modulzuteilung, lektor, einheit WHERE modulzuteilung.lektor_id=lektor.id";
	$sql_query.=" AND modulzuteilung.modul_id=einheit.id ORDER BY $order";
	//echo $sql_query;
	if(!($erg=pg_exec($conn, $sql_query)))
		die(pg_errormessage($conn));
	$num_rows=pg_numrows($erg);
?>

<html>
<head>
<title>Zuteilung der Lektoren</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<!--<link rel="stylesheet" href="../include/styles.css" type="text/css">-->
<LINK rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h2>Lektoren - Modul Zuteilung</h2>
Anzahl:
<?php echo $num_rows; ?>
<br>
<br>
<table border="<?php echo $cfgBorder;?>">
<tr bgcolor="<?php echo $cfgThBgcolor; ?>">
	<th></th><th>Nachname</th><th>Vornamen</th>
	<th>uid</th>
	<th>Modul</th>
<?php
	for ($i=0; $i<$num_rows; $i++)
	{
		$bgcolor = $cfgBgcolorOne;
     	$i % 2  ? 0: $bgcolor = $cfgBgcolorTwo;

		$row=pg_fetch_object ($erg, $i);

		?>
		<tr bgcolor=<?php echo $bgcolor; ?>>
		<td><a href="modulzuteilung_edit.php?id=<?php echo $row->id.'&type=del'; ?>" class="linkblue">Delete</a></td>
		<td><?php echo $row->nachname; ?></td>
		<td><?php echo $row->vornamen; ?></td>
		<td><A href="mailto:<?php echo $row->uid; ?>@technikum-wien.at" class="linkgreen"><?php echo $row->uid; ?></A></td>
		<td><?php echo $row->mdkurzbz; ?></td>
		</tr>
		<?php
	}
?>
</table>
<FORM name="newpers" method="post" action="modulzuteilung_edit.php">
  <INPUT type="hidden" name="type" value="new">
  Lektor:
  <SELECT name="lektorid">
    <?php
		// Auswahl des Lektors
		$num_rows=pg_numrows($result_lektor);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_lektor, $i);
			echo "<option value=\"$row->id\">$row->nachname $row->vornamen - $row->uid</option>";
		}
		?>
  </SELECT>
  <BR>
  Modul:
  <SELECT name="modulid">
    <?php
		// Auswahl des Moduls
		$num_rows=pg_numrows($result_modul);
		for ($i=0;$i<$num_rows;$i++)
		{
			$row=pg_fetch_object ($result_modul, $i);
			echo "<option value=\"$row->id\">$row->kurzbz - $row->bezeichnung</option>";
		}
		?>
  </SELECT>
  &nbsp;
  Semester:&nbsp;<font face="Arial, Helvetica, sans-serif" size="2"><select name="semester">
	<OPTION value="1" <?php if ($semester==1) echo 'selected'; ?>>1</OPTION>
	<OPTION value="2" <?php if ($semester==2) echo 'selected'; ?>>2</OPTION>
	<OPTION value="3" <?php if ($semester==3) echo 'selected'; ?>>3</OPTION>
	<OPTION value="4" <?php if ($semester==4) echo 'selected'; ?>>4</OPTION>
	<OPTION value="5" <?php if ($semester==5) echo 'selected'; ?>>5</OPTION>
	<OPTION value="6" <?php if ($semester==6) echo 'selected'; ?>>6</OPTION>
	<OPTION value="7" <?php if ($semester==7) echo 'selected'; ?>>7</OPTION>
	<OPTION value="8" <?php if ($semester==8) echo 'selected'; ?>>8</OPTION>
  </select></font>
  <INPUT type="submit" name="Abschicken" value="Hinzuf&uuml;gen">
</FORM>
</body>
</html>