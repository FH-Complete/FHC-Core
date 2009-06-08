<?php
	include('../../config.inc.php');
	include('wochendatum.inc.php');
	if (!($conn=pg_connect($conn_string)))
		die ("No connection to Database!");
	$tagsek=86400;
?>

<html>
<head>
<title>Send Mails</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<LINK rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
</head>
<body class="background_main">
<H1>eMails werden verschickt</H1>
<?php
	// Untis abfragen
	$sql_query="SELECT id, unr, wochentag, stunde_id, ort_id, lehrfach_id, lektor_id, jahreswochen, studiengang_id, semester, verband, gruppe FROM untis WHERE checkmail='f' AND ort_id>0 AND lehrfach_id>0 AND lektor_id>0 AND studiengang_id>0 AND lehrfach NOT LIKE '\\\\_%' ORDER BY lektor";
	//echo $sql_query;
	$result=pg_exec($conn, $sql_query);
	$num_rows=pg_numrows($result);
	echo $num_rows.' Eintraege in der Tabelle Untis fuer den Stundenplan<BR><BR>Verarbeitung laeuft (0) ';
	flush();
	$lektor_id=0;
	$text="";
	$sendmail=0;
	$stundenanzahl=0;
	
	for ($i=0; $i<$num_rows; $i++)
	{
		if ($i%10==0)
		{
			echo '.';
			flush();
		}
		$row=pg_fetch_object($result,$i);
		if ($lektor_id!=$row->lektor_id)
		{
			if (($lektor_id!=0) && $sendmail)
			{
				$text.="\nhttp://cis.technikum-wien.at\n\nFehler und Feedback bitte an mailto:stpl@technikum-wien.at";
				$sql_query="SELECT emailtw FROM lektor WHERE id=$lektor_id";
				$result_sendto=pg_exec($conn, $sql_query);
				$row_sendto=pg_fetch_object($result_sendto,0);
				$sendto=$row_sendto->emailtw;
				if (!mail($sendto, "Stundenplan Aenderung ($stundenanzahl Stunden)", $text, "From: stpl@technikum-wien.at\r\n"."Reply-To: stpl@technikum-wien.at\r\n"."X-Mailer: PHP/".phpversion() ) )
					die ("<BR>Mail an <b>$sendto</b> konnte nicht verschickt werden.<BR>");
				echo '<BR>Mail verschickt an <b>'.$sendto.'</b><br>Verarbeitung laeuft ('.$i.') ';
				flush();
				$stundenanzahl=0;
				$sendmail=0;
			}
			$lektor_id=$row->lektor_id;
			$text="Dies ist eine automatische eMail!\nFolgende Aenderungen sind in ihrem Stundenplan vorgenommen worden\n\n";
			$text.="Datum\t\tStunde\tVerband\n";
		}
		for ($w=1; $w<=53; $w++)
		{
			if (substr($row->jahreswochen,$w-1,1)=='1')
			{
				$date=$week_date[$w]+($tagsek*($row->wochentag-1));
				$date=getdate($date);
				$tag=$date[mday];
				$monat=$date[mon];
				$jahr=$date[year];
				$date=$jahr.'-'.$monat.'-'.$tag;
				$sql_query="SELECT * FROM stundenplan WHERE studiengang_id=$row->studiengang_id AND semester=$row->semester AND ";
				$sql_query.="verband='$row->verband' AND gruppe='$row->gruppe' AND ort_id=$row->ort_id AND datum='$date' AND ";
				$sql_query.="stunde_id=$row->stunde_id AND lehrfach_id=$row->lehrfach_id AND lektor_id=$row->lektor_id";
				$result_checkmail=pg_exec($conn, $sql_query);
				$num_checkmail=pg_numrows($result_checkmail);
				if ($num_checkmail==0)
				{
					//$row_checkmail=pg_fetch_object($result_checkmail,0);
					$text.="$date\t$row->stunde_id\t$row->semester$row->verband$row->gruppe\r\n";
					//$text.="$row_checkmail->datum\t$row_checkmail->stunde_id\t$row_checkmail->semester$row_checkmail->verband$row_checkmail->gruppe\t$row_checkmail->ortkurzbz\t$row_checkmail->stgkurzbz\t$row_checkmail->lehrfachkurzbz lektor_id lektorkurzbz 
					$stundenanzahl++;
					$sendmail=1;
				}
			}
		}
		$sql_query="UPDATE untis SET checkmail='t' WHERE id=$row->id";
		$result_insert=pg_exec($conn, $sql_query);
	}
	if ($sendmail)
	{
		$text.="\nhttp://cis.technikum-wien.at\n\nFehler und Feedback bitte an mailto:stpl@technikum-wien.at";
		$sql_query="SELECT emailtw FROM lektor WHERE id=$lektor_id";
		$result_sendto=pg_exec($conn, $sql_query);
		$row_sendto=pg_fetch_object($result_sendto,0);
		$sendto=$row_sendto->emailtw;
		$sendto=$row_sendto->emailtw;
		if (!mail($sendto, "Stundenplan Aenderung ($stundenanzahl Stunden)", $text, "From: stpl@technikum-wien.at\r\n"."Reply-To: stpl@technikum-wien.at\r\n"."X-Mailer: PHP/".phpversion() ) )
			die ("<BR>Mail an <b>$sendto</b> konnte nicht verschickt werden.");
		echo 'Mail verschickt an <b>'.$sendto.'</b><br>';
	}
	echo '<BR>Verarbeitung erfolgreich abgeschlossen!';
	
?>

</body>
</html>