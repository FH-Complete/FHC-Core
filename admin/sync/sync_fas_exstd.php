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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>, 
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
	require_once('../../vilesci/config.inc.php');
	$adress='tw_tester@technikum-wien.at';
	
	//mail($adress,"FAS Synchro mit PORTAL","BEGIN OF SYNCHRONISATION","From: vilesci@technikum-wien.at");
	$conn=pg_connect(CONN_STRING);
	$conn_fas=pg_connect(CONN_STRING_FAS);

 	// Anzahl der Studenten in VILESCI
	$sql_query="SELECT count(*) AS anz FROM public.tbl_student";
	//echo $sql_query."<br>";
	$result=pg_query($conn, $sql_query);
	$vilesci_anz_std=pg_fetch_result($result,0,'anz');

	// Start Studenten Synchro
	$sql_query="SELECT DISTINCT uid FROM fas_view_vilesci_abbrecher WHERE uid IS NOT NULL AND uid NOT LIKE '' ORDER BY uid";// LIMIT 5";
	//echo $sql_query."<br>";
	flush();
	$result=pg_query($conn_fas, $sql_query);
	$num_rows=pg_num_rows($result);
	$text="Dies ist eine automatische eMail!\r\r";
	$text.="Es wurde eine Synchronisation mit FAS durchgef�hrt.\r";
	$text.="Anzahl der Ex-Studenten vom FAS-Import: $num_rows \r";
	$text.="Anzahl der Studenten in PORTAL: $vilesci_anz_std \r\r";
	echo $text.'<BR>';
	flush();
	$update_error=0;
	$anz_update=0;
	for ($i=0;$i<$num_rows;$i++)
	{
		$row=pg_fetch_object($result,$i);
		$uid=str_replace(' ','',$row->uid);
		// SQL vorbereiten (jeden Studenten vom FAS im vilesci suchen
		$sql_query="SELECT uid, vorname, nachname, semester, studiengang_kz FROM campus.vw_student WHERE uid='$uid'";
		//echo $sql_query;
		$res_std=pg_query($conn, $sql_query);
		$num_rows_std=pg_numrows($res_std);
		// neue Studenten
		if ($num_rows_std>=1)
		{
			$row_std=pg_fetch_object($res_std);
			if ($row_std->semester!=10)
			{
				//Wenn dieser Lehrverband noch nicht existiert wird er eingefuegt
				$sql_query = "SELECT * FROM public.tbl_lehrverband WHERE studiengang_kz='$row_std->studiengang_kz' AND semester='10' AND verband=' ' AND gruppe=' '";
				
				$result_grp = pg_query($conn, $sql_query);
				if(pg_num_rows($result_grp)==0)
				{
					$sql_query = "INSERT INTO public.tbl_lehrverband(studiengang_kz, semester, verband, gruppe, aktiv, bezeichnung) VALUES('$row_std->studiengang_kz','10',' ',' ', false, 'Ex Studenten');";
					pg_query($conn, $sql_query);
						$text.="Neuer Lehrverband wird erzeugt:$row_std->studiengang_kz 10\r";					
				}
					
				$text.="Der Student $row_std->vorname $row_std->nachname ($row_std->uid) wird verschoben.\r";
				$sql_query="UPDATE public.tbl_student SET semester=10, verband=' ', gruppe=' ', updateamum=now(), updatevon='auto' WHERE student_uid LIKE '$uid'";
				echo $sql_query.'<BR>';
				if(!$res_update=pg_query($conn, $sql_query))
				{
					$text.=$sql_query;
					$text.="\rFehler: ".pg_errormessage($conn)."\r";
					$update_error++;
				}
				else
					$anz_update++;
			}
		}
	}
	$text.="$update_error Fehler bei Student-Update!\r";
	$text.="$anz_update Studenten wurden ins 10te Semester verschoben.\r";
	$text.="\rEND OF SYNCHRONISATION\r";
	if (mail($adress,"FAS Synchro mit PORTAL (Ex-Studenten)",$text,"From: vilesci@technikum-wien.at"))
		$sendmail=true;
	else
		$sendmail=false;
?>

<html>
<head>
	<title>FAS-Synchro mit PORTAL (Ex-Studenten)</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<LINK rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>
<body>
<?php
if ($sendmail)
	echo 'Mail wurde verschickt an '.$adress.'!<br>';
else
	echo "Mail konnte nicht verschickt werden!<br>";
echo nl2br($text);

?>
</body>
</html>
