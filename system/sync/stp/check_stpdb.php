<?php
// **************************************
// Syncronisiert alle Lehrveranstaltungen
// StPoelten -> VILESCI
// setzt vorraus: - tbl_sprache
//                - tbl_studiengang
// **************************************
	require_once('sync_config.inc.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	$adress='pam@technikum-wien.at';
	//$adress='oesi@technikum-wien.at';
	//$adress='ruhan@technikum-wien.at';

	//$conn=pg_connect(CONN_STRING);
	if (!$conn_ext=mssql_connect (STPDB_SERVER, STPDB_USER, STPDB_PASSWD))
		die('Fehler beim Verbindungsaufbau!');
	mssql_select_db(STPDB_DB, $conn_ext);

	$tabellen=array("studiengang","person","lv","adresse","EMail","Note","SemesterplanEintrag","StudienGebuehren","StudienplanEintrag","StgVertiefung","StudIO","_person_studiengang");

?>

<html>
<head>
	<title>DB-CHECK DB StPoelten</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
	if (isset($_GET['table']))
	{
		$sql='SELECT count(*) AS anz FROM '.$_GET['table'].';';
		if ($ergebnis = mssql_query($sql,$conn_ext))
		{
			$row=mssql_fetch_object($ergebnis);
			echo '<H1>Tabelle: <strong>'.$_GET['table'].'</strong> ('.$row->anz.' Eintraege)</H1>';
		}
		$sql='SELECT TOP 50 * FROM '.$_GET['table'].';';
		if ($ergebnis = mssql_query($sql,$conn_ext))
		{
			$j=0;
			echo '<table><tr>';
			for ($i = 0; $i < mssql_num_fields($ergebnis); $i++)
			{
				$infos = mssql_fetch_field($ergebnis, $i);
				echo '<td>'.$infos->name.' ('.$infos->type.')</td>';
			}
			echo '</tr>';
			while ($row=mssql_fetch_row($ergebnis))
			{
				echo '<tr class="liste'.($j%2).'">';
				for ($i = 0; $i < mssql_num_fields($ergebnis); $i++)
					echo '<td>'.$row[$i].'</td>';
				echo '</tr>';
				$j++;
			}
			echo '</table>';
		}
	}
	else
	{
		foreach ($tabellen AS $tab)
		{
			$sql='SELECT TOP 1 * FROM '.$tab.';';
			if ($ergebnis = mssql_query($sql,$conn_ext))
			{
				echo '<table><tr class="liste"><td colspan="2">Tabelle: <strong><a href="?table='.$tab.'">'.$tab.'</a></strong></td></tr>';
				for ($i = 0; $i < mssql_num_fields($ergebnis); $i++)
				{
					$infos = mssql_fetch_field($ergebnis, $i);
					echo '<tr class="liste'.($i%2).'"><td>'.$infos->name.'</td><td>'.$infos->type.'</td></tr>';
				}
				echo '</table><BR>';
			}
		}
		$sql='SELECT * FROM INFORMATION_SCHEMA.TABLES;';
		if ($ergebnis = mssql_query($sql,$conn_ext))
		{
			$j=0;
			echo '<table><tr class="liste"><td colspan="2"><strong>Alle Tabellen</strong></td></tr>';
			while ($row=mssql_fetch_row($ergebnis))
			{
				echo '<tr class="liste'.($j%2).'">';
				for ($i = 0; $i < mssql_num_fields($ergebnis); $i++)
					echo '<td>'.$row[$i].'</td>';
				echo '</tr>';
				$j++;
			}
			echo '</table>';
		}
	}
?>
</body>
</html>