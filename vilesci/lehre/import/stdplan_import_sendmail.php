<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../../config/vilesci.config.inc.php');
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			

	include('wochendatum.inc.php');
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

	$num_rows=0;
	if($result=$db->db_query($sql_query))
		$num_rows=$db->db_num_rows($result);
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
		$row=$db->db_fetch_object($result,$i);
		if ($lektor_id!=$row->lektor_id)
		{
			if (($lektor_id!=0) && $sendmail)
			{
				$text.="\nhttp://cis.technikum-wien.at\n\nFehler und Feedback bitte an mailto:stpl@technikum-wien.at";
				$sql_query="SELECT emailtw FROM lektor WHERE id=$lektor_id";
				$result_sendto=$db->db_query($sql_query);
				$row_sendto=$db->db_fetch_object($result_sendto,0);
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

				$num_checkmail=0;
				if ($result_checkmail=$db->db_query($sql_query))
						$num_checkmail=$db->db_num_rows($result_checkmail);
			
				if ($num_checkmail==0)
				{
					$text.="$date\t$row->stunde_id\t$row->semester$row->verband$row->gruppe\r\n";
					$stundenanzahl++;
					$sendmail=1;
				}
			}
		}
		$sql_query="UPDATE untis SET checkmail='t' WHERE id=$row->id";
		$result_insert=$db->db_query($sql_query);
	}
	if ($sendmail)
	{
		$text.="\nhttp://cis.technikum-wien.at\n\nFehler und Feedback bitte an mailto:stpl@technikum-wien.at";
		$sql_query="SELECT emailtw FROM lektor WHERE id=$lektor_id";
		$result_sendto=$db->db_query($sql_query);
		$row_sendto=$db->db_fetch_object($result_sendto,0);
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