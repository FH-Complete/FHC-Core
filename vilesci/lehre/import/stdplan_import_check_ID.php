<?php
	include('../../config.inc.php');
	$conn=pg_connect(CONN_STRING);

	// IDs updaten
	//$sql_query="UPDATE untis SET untis.lektor_id=(SELECT lektor.id WHERE untis.lektor=lektor.kurzbz)";
	//$result=pg_exec($conn, $sql_query);
	//$num_rows=pg_numrows($result);

?>

<html>
<head>
<title>Check ID</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../include/styles.css" type="text/css">
</head>
<body class="background_main">
<H1>ID's werden überprüft</H1>
<?php

	//*****************************************************************************************
	// Lektoren

	// Tabelle Untis nach nichtvergebenen IDs abfragen
	$sql_query="SELECT DISTINCT lektor FROM untis WHERE lektor_uid IS NULL";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	$countok=0;
	if ($num_rows>0)
	{
		for ($i=0; $i<$num_rows; $i++)
		{
			$row=pg_fetch_object($result,$i);
			$sql_query="UPDATE untis SET lektor_uid=(SELECT uid FROM tbl_mitarbeiter WHERE kurzbz='$row->lektor') WHERE lektor='$row->lektor'";
			//echo $sql_query;
			$result_update=pg_exec($conn, $sql_query);
			if (pg_cmdtuples($result_update)==0)
				echo 'UID fuer Lektoren Kurzbezeichnung '.$row->lektor.' konnte nicht gefunden werden!<br>';
			else
			{
				echo pg_cmdtuples($result_update).' Einträge für '.$row->lektor.' wurden upgedatet!<br>';
				$countok++;
			}
		}
		echo $countok.' Lektoren UIDs wurden erfolgreich vergeben!<br>';
		echo $num_rows-$countok.' Lektoren IDs konnten nicht gefunden werden!<br><br>';
	}
	else
		echo 'Keine nicht vergebenen UIDs bei Lektor gefunden!<br><br>';
	flush();

	//*****************************************************************************************
	// Ort

	// Tabelle Untis nach nichtvergebenen IDs abfragen
	$sql_query="SELECT ort FROM untis WHERE ort_kurzbz IS NULL GROUP BY ort";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);

	if ($num_rows>0)
	{
		for ($i=0; $i<$num_rows;$i++)
		{
			$row=pg_fetch_object($result,$i);
			$sql_query="UPDATE untis SET ort_kurzbz=(SELECT ort_kurzbz FROM tbl_ort WHERE ort_kurzbz='$row->ort') WHERE ort='$row->ort'";
			$result_update=pg_exec($conn, $sql_query);
			if (pg_cmdtuples($result_update)==0)
				echo 'ID fuer Ort Kurzbezeichnung '.$row->ort.' konnte nicht gefunden werden!<br>';
			else
				echo pg_cmdtuples($result_update).' Einträge für '.$row->ort.' wurden upgedatet!<br>';
		}
		echo 'Ort kurzbzs wurden erfolgreich vergeben!<br><br>';
	}
	else
		echo 'Keine nicht vergebenen kurzbzs bei Ort gefunden!<br><br>';
	flush();

	//*****************************************************************************************
	// Lehrfach

	// Tabelle Untis nach nichtvergebenen IDs abfragen
	$sql_query="SELECT DISTINCT lehrfach FROM untis WHERE (lehrfach_nr IS NULL OR lehrfach_nr=0) AND lehrfach NOT LIKE '\\\\_%'";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);

	if ($num_rows>0)
	{
		for ($i=0; $i<$num_rows;$i++)
		{
			$row=pg_fetch_object($result,$i);
			$sql_query="UPDATE untis SET lehrfach_nr=
				(SELECT lehrfach_nr FROM tbl_lehrfach WHERE kurzbz='".substr($row->lehrfach,0,3)."'
				AND lehrform_kurzbz='".substr($row->lehrfach,3)."')
				WHERE lehrfach='$row->lehrfach'";
			$result_update=pg_exec($conn, $sql_query);
			if (pg_cmdtuples($result_update)==0)
				echo 'ID fuer Lehrfach Kurzbezeichnung '.$row->lehrfach.' konnte nicht gefunden werden!<br>';
			else
				echo pg_cmdtuples($result_update).' Einträge für '.$row->lehrfach.' wurden upgedatet!<br>';
		}
		echo 'Lehrfach Nr wurden erfolgreich vergeben!<br><br>';
	}
	else
		echo 'Keine nicht vergebenen Nummern bei Lehrfach gefunden!<br><br>';
	flush();

	//*****************************************************************************************
	// Module

	// Tabelle Untis nach nichtvergebenen IDs abfragen
	$sql_query="SELECT DISTINCT lehrfach FROM untis WHERE (lehrfach_nr IS NULL OR einheit_kurzbz='' OR einheit_kurzbz IS NULL) AND lehrfach LIKE '\\\\_%'";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);

	if ($num_rows>0)
	{
		for ($i=0; $i<$num_rows;$i++)
		{
			$row=pg_fetch_object($result,$i);
			$sql_query="UPDATE untis SET einheit_kurzbz=(SELECT einheit_kurzbz FROM tbl_einheit
				WHERE einheit_kurzbz='".trim(substr($row->lehrfach,1))."')
				,lehrfach_nr=0 WHERE lehrfach='$row->lehrfach'";
			//echo $sql_query;
			$result_update=pg_exec($conn, $sql_query);
			if (pg_cmdtuples($result_update)==0)
				echo 'Einheit Kurzbezeichnung '.$row->lehrfach.' konnte nicht gefunden werden!<br>';
			else
				echo pg_cmdtuples($result_update).' Einträge für '.$row->lehrfach.' wurden upgedatet!<br>';
		}
		echo 'Einheiten wurden erfolgreich vergeben!<br><br>';
	}
	else
		echo 'Keine nicht vergebenen Einheiten gefunden!<br><br>';
	flush();

	//*****************************************************************************************
	// Lehrverband

	// Tabelle Untis nach nichtvergebenen IDs abfragen
	$sql_query="SELECT DISTINCT klassenbez FROM untis WHERE studiengang_kz IS NULL";
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);

	if ($num_rows>0)
	{
		for ($i=0; $i<$num_rows;$i++)
		{
			$row=pg_fetch_object($result,$i);
			$stgkz=substr($row->klassenbez,0,3);
			$semester=substr($row->klassenbez,4,1);
			$verband=substr($row->klassenbez,5,1);
			$gruppe=substr($row->klassenbez,6,1);
			$sql_query="UPDATE untis SET studiengang_kz=(SELECT studiengang_kz FROM tbl_studiengang WHERE kurzbz='$stgkz'), semester='$semester', verband='$verband', gruppe='$gruppe' WHERE klassenbez='$row->klassenbez'";
			//echo $sql_query;
			$result_update=pg_query($conn, $sql_query);
			if (pg_cmdtuples($result_update)==0)
				echo 'Klassenbezeichnung '.$row->klassenbez.' konnte nicht gefunden werden!<br>';
			else
				echo pg_cmdtuples($result_update).' Einträge für '.$row->klassenbez.' wurden upgedatet!<br>';
		}
		echo 'Klassen IDs wurden erfolgreich vergeben!<br><br>';
	}
	else
		echo 'Keine nicht vergebenen Klassenbez gefunden!<br><br>';
?>

</body>
</html>