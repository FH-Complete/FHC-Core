<?php
/* Copyright (C) 2008 Technikum-Wien
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
/*******************************************************************************************************
 *			abgabe_lektor_benotung
 *     abgabe_lektor_benotung ist die Benotungsoberfläche des Abgabesystems 
 * 			für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');

//DB Verbindung herstellen
if (!$conn = @pg_pconnect(CONN_STRING))
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$getuid=get_uid();
$htmlstr = "";
$qualitaet='';
$form='';
$hintergrund='';
$punkte1=0;
$punkteges1='';
$punkte2=0;
$punkteges2='';
$punkte3=0;
$punkteges3='';
$summe1='';
$summe2='';
$note='';
$weight1='';
$weight2='';
$weight3='';	

$projektarbeit_id='';
$uid='';
$titel='';

if(isset($_REQUEST['projektarbeit_id']))
{
	if(!isset($_POST['projektarbeit_id']))
	{
		$uid = (isset($_GET['uid'])?$_GET['uid']:'-1');
		$projektarbeit_id = (isset($_GET['projektarbeit_id'])?$_GET['projektarbeit_id']:'-1');
		$titel = (isset($_GET['titel'])?$_GET['titel']:'-1');
	}
	else 
	{
		session_start();
		$_SESSION['projektarbeit_id']=(isset($_POST['projektarbeit_id'])?$_POST['projektarbeit_id']:'-1');
		$_SESSION['uid']=(isset($_POST['uid'])?$_POST['uid']:'-1');
		$_SESSION['titel']=(isset($_POST['titel'])?$_POST['titel']:'');
		
		$_SESSION['qualitaet']=(isset($_POST['qualitaet'])?$_POST['qualitaet']:'');
		$_SESSION['form']=(isset($_POST['form'])?$_POST['form']:'');
		$_SESSION['hintergrund']=(isset($_POST['hintergrund'])?$_POST['hintergrund']:'');
		$_SESSION['punkte1']=(isset($_POST['punkte1'])?$_POST['punkte1']:'');
		$_SESSION['punkteges1']=(isset($_POST['punkteges1'])?$_POST['punkteges1']:'');
		$_SESSION['punkte2']=(isset($_POST['punkte2'])?$_POST['punkte2']:'');
		$_SESSION['punkteges2']=(isset($_POST['punkteges2'])?$_POST['punkteges2']:'');
		$_SESSION['punkte3']=(isset($_POST['punkte3'])?$_POST['punkte3']:'');
		$_SESSION['punkteges3']=(isset($_POST['punkteges3'])?$_POST['punkteges3']:'');
		$_SESSION['summe1']=(isset($_POST['summe1'])?$_POST['summe1']:'');
		$_SESSION['summe2']=(isset($_POST['summe2'])?$_POST['summe2']:'');
		$_SESSION['note']=(isset($_POST['note'])?$_POST['note']:'');

		Header("Location:test.php");
	
	}
}
else 
{
	session_start();
	$projektarbeit_id=$_SESSION['projektarbeit_id'];
	$uid=$_SESSION['uid'];
	$titel=$_SESSION['titel'];
	
	$qualitaet=$_SESSION['qualitaet'];
	$form=$_SESSION['form'];
	$hintergrund=$_SESSION['hintergrund'];
	$punkte1=$_SESSION['punkte1'];
	$punkteges1=$_SESSION['punkteges1'];
	$punkte2=$_SESSION['punkte2'];
	$punkteges2=$_SESSION['punkteges2'];
	$punkte3=$_SESSION['punkte3'];
	$punkteges3=$_SESSION['punkteges3'];
	$summe1=$_SESSION['summe1'];
	$summe2=$_SESSION['summe2'];
	$note=$_SESSION['note'];
}

$sql_query = "SELECT * FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) * FROM lehre.tbl_projektarbeit LEFT JOIN lehre.tbl_projektbetreuer using(projektarbeit_id) 
	LEFT JOIN public.tbl_benutzer on(uid=student_uid) 
	LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
	LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id) 
	LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id) 
	LEFT JOIN public.tbl_studiengang using(studiengang_kz)
	WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
	AND tbl_projektbetreuer.person_id IN (SELECT person_id FROM public.tbl_benutzer 
							WHERE public.tbl_benutzer.person_id=lehre.tbl_projektbetreuer.person_id 
							AND public.tbl_benutzer.uid='sommert')
	AND lehre.tbl_projektarbeit.note IS NULL 
	AND lehre.tbl_projektarbeit.projektarbeit_id=".$projektarbeit_id."
	ORDER BY tbl_projektarbeit.projektarbeit_id, betreuerart_kurzbz desc) as xy 
	ORDER BY nachname";

if(!$erg=pg_query($conn, $sql_query))
{
	die('Fehler beim Laden der Betreuungen');
}
else
{
	if($row=@pg_fetch_object($erg))
	{
		echo '
		<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
		<html>
		<head>
		<title>DA/BA-Benotung</title>
		<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
		<meta http-equiv="content-type" content="text/html; charset=ISO-8859-9" />
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script language="Javascript">

			function berechne() 
			{
				ergebnis=document.getElementById("punkte1").value*document.getElementById("weight1").value;
				document.getElementById("punkteges1").value=ergebnis.toFixed(2);
				ergebnis=document.getElementById("punkte2").value*document.getElementById("weight2").value;
				document.getElementById("punkteges2").value=ergebnis.toFixed(2);';
				if($row->projekttyp_kurzbz=='Diplom')
				{
					echo 'ergebnis=document.getElementById("punkte3").value*document.getElementById("weight3").value;
					document.getElementById("punkteges3").value=ergebnis.toFixed(2);
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value)+parseFloat(document.getElementById("punkte3").value);
					document.getElementById("summe1").value=ergebnis.toFixed(1);
					ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value)+parseFloat(document.getElementById("punkteges3").value);
					document.getElementById("summe2").value=ergebnis.toFixed(2);
					if(document.getElementById("punkte1").value<50 || document.getElementById("punkte2").value<50 || document.getElementById("punkte3").value<50)
					{
						ergebnis=5;
					}
					else
					{
						if(document.getElementById("summe2").value<=50)
						{
							ergebnis=5;
						}
						else if(document.getElementById("summe2").value<65)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<78)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<91)
						{
							ergebnis=2;
						}
						else
						{
							ergebnis=1;
						}
					}
					document.getElementById("note").value=ergebnis.toFixed(0);';
				}
				else
				{
					echo '
					ergebnis=parseFloat(document.getElementById("punkte1").value)+parseFloat(document.getElementById("punkte2").value);
					document.getElementById("summe1").value=ergebnis.toFixed(1);
					ergebnis=parseFloat(document.getElementById("punkteges1").value)+parseFloat(document.getElementById("punkteges2").value);
					document.getElementById("summe2").value=ergebnis.toFixed(2);
					if(document.getElementById("punkte1").value<50 || document.getElementById("punkte2").value<50)
					{
						ergebnis=5;
					}
					else
					{
						if(document.getElementById("summe2").value<=50)
						{
							ergebnis=5;
						}
						else if(document.getElementById("summe2").value<65)
						{
							ergebnis=4;
						}
						else if(document.getElementById("summe2").value<78)
						{
							ergebnis=3;
						}
						else if(document.getElementById("summe2").value<91)
						{
							ergebnis=2;
						}
						else
						{
							ergebnis=1;
						}
					}
					document.getElementById("note").value=ergebnis.toFixed(0);';
				}
			echo '}

		</script>
		</head>
		<body class="Background_main"  style="background-color:#eeeeee;" onload="berechne()">';
				
		
		$htmlstr = "<br><br>";
		$htmlstr .= "<table class='detail' border='1'>\n";
		$htmlstr .= "<form action='$PHP_SELF' method='POST' name='note'>";
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Student: <b>".$uid.", ".$row->vorname." ".$row->nachname."</b></td>";
		$htmlstr .= "<tr><td style='font-size:16px' colspan='5'>Titel: <b>".$titel."</b>";
		$htmlstr .= "<input type='hidden' name='projektarbeit_id' value='".$projektarbeit_id."'>\n";
		$htmlstr .= "<input type='hidden' name='titel' value='".$titel."'>\n";
		$htmlstr .= "<input type='hidden' name='uid' value='".$uid."'>\n";
		$htmlstr .= "</td></tr>";
		$htmlstr .= "<td>&nbsp;</td><td align='center'>Kurze verbale Beurteilung</td><td align='center'>Punkte (0-100)</td><td align='center'>Gewicht</td><td align='center'>Punkte * Gewicht</td>\n";
		$htmlstr .= "<tr>\n";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr .= "<td width='30%'><b>Qualit&auml;t des eigenen Beitrags<br>
			Angewandte Methodik</b><br>z.B. Projektm&auml;&szlig;ige Vorgangsweise<br>
			Wissenschaftlich - systematische Methoden in der Analyse bzw. L&ouml;sungsfindung<br>
			<b>Art der Probleml&ouml;sung</b><br>
			Wurde das Problem tats&auml;chlich gel&ouml;st?<br>
			Eigenst&auml;ndigkeit und Kreativit&auml;t der L&ouml;sung<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Technische Qualit&auml;t der L&ouml;sung</td>";
			$weight1='0.55';
		}
		else 
		{
			$htmlstr .= "<td width='30%'><b>Qualit&auml;t des eigenen Beitrags<br>
			Angewandte Methodik</b><br>z.B. wissenschaftlich fundierte, systematische, ingenieurm&auml;&szlig;ige Vorgangsweise<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Eigenst&auml;ndigkeit und Kreativit&auml;t der L&ouml;sung<br>
			Ist der eigene Beitrag deutlich sichtbar?<br>
			Qualit&auml;t der L&ouml;sung</td>";
			$weight1='0.6';
		}
		$htmlstr .= "<td width='40%'><textarea name='qualitaet' value='".$qualitaet."' cols='60'  rows='10'></textarea></td>\n
		<td width='10%' align='center'><input type='hidden' name='weight' id='weight1' value='".$weight1."'>
		<input  type='text' name='punkte1' value='".$punkte1."' size='5' maxlength='5' id='punkte1' onkeyup='berechne()' style='text-align:right'></td>\n";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr.="<td width='10%' align='center'>0.55</td>";
		}
		else 
		{
			$htmlstr.="<td width='10%' align='center'>0.60</td>";
		}
		$htmlstr .="<td width='10%' align='center'><input  type='text' name='punkteges1' value='".$punkteges1."' id='punkteges1' style='text-align:right' size='5' maxlength='5' readonly></td></tr>\n";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr .= "<td width='30%'><b>Form / Stil</b><br>
			Hat die Diplomarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache: benötigte &Uuml;berarbeitungen seitens der Betreuerin / des Betreuers</td>
			<td width='40%'><textarea name='form' value='".$form."' cols='60'  rows='10'></textarea></td>\n";
			$weight2='0.2';
		}
		else 
		{
			$htmlstr .= "<td width='30%'><b>Form / Stil</b><br>
			Hat die Bachelorarbeit eine klare Stuktur, entspricht der Vorgabe?<br>
			Wird einwandfrei zitiert?<br>
			Abbildungen<br>
			Sprache</td>
			<td width='40%'><textarea name='form' value='".$form."' cols='60'  rows='10'></textarea></td>\n";
			$weight2='0.4';
		}
		$htmlstr .= "<td width='10%' align='center'><input type='hidden' name='weight' id='weight2' value='".$weight2."'>
		<input  type='text' name='punkte2' value='".$punkte2."' size='5' maxlength='5' id='punkte2' onkeyup='berechne()' style='text-align:right'></td>\n";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr .="<td width='10%' align='center'>0.20</td>";
		}
		else
		{
			$htmlstr .="<td width='10%' align='center'>0.40</td>";
		}
		$htmlstr .="<td width='10%' align='center'><input  type='text' name='punkteges2' value='".$punkteges2."' style='text-align:right' size='5' maxlegnth='3' id='punkteges2' readonly></td></tr>\n";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr .= "<td width='30%'><b>Qualit&auml;t der Hintergrundinformation</b><br>
			Werden Gesamtzusammenhänge erkannt, wird Bedeutung und Gewicht der Einflussfaktoren / Daten / Informationen richtig bewertet?<br>
			Intelligente Darstellung des relevanten Stands der Technik und des Firmenumfelds<br>
			Aufdecken und Darstellen von gr&ouml;&szlig;eren (z.B. wirtschaftlichen und sozialen) Zusammenh&auml;ngen und entsprechende Diskussion</td>
			<td width='40%'><textarea name='hintergrund' value='".$hintergrund."' cols='60'  rows='10'></textarea></td>\n
			<td width='10%' align='center'><input type='hidden' name='weight' id='weight3' value='0.25'>
			<input  type='text' name='punkte3' value='".$punkte3."' size='5' maxlength='5' id='punkte3' style='text-align:right' onkeyup='berechne()'></td>\n
			<td width='10%' align='center'>0.25</td>
			<td width='10%' align='center'><input  type='text' name='punkteges3' value='".$punkteges3."' id='punkteges3' style='text-align:right' size='5' maxlength='5' readonly></td></tr>";
		}
		else 
		{
			$htmlstr .= "
			<input  type='hidden' name='punkte3' value='0' id='punkte3'></td>\n
			<input  type='hidden' name='punkteges3' value='0' id='punkteges3'></td>\n
			<input  type='hidden' name='weight3' value='0' id='weight3'></td>\n";
		}
		$htmlstr .= "<td colspan='2'>Gesamtpunkte</td>";
		$htmlstr .="<td align='center'><input  type='text' name='summe1' value='".$summe1."' id='summe1' style='text-align:right' size='5' maxlength='5' readonly></td>
		<td align='center'>1</td>
		<td align='center'><input  type='text' name='summe2' value='".$summe2."' id='summe2' style='text-align:right' size='5' maxlength='5' readonly></td><tr>";
		$htmlstr .= "<td colspan='4'>Note</td><td align='center'><input  type='text' name='note' value='".$note."' id='note' style='text-align:right' size='5' maxlength='5' readonly></td></tr>";
		$htmlstr .="</table>";
		$htmlstr .= "<br><table border='1' align='center' width='60%'>";
		$htmlstr .= "<tr><td>0 - 50 Punkte = 5</td><td>51 - 64 Punkte = 4</td><td>65 - 77 Punkte = 3</td><td>78 - 90 Punkte = 2</td><td>91 - 100 Punkte = 1</td></tr>";
		if($row->projekttyp_kurzbz=='Diplom')
		{
			$htmlstr .= "<tr><td colspan='5'>Ein Kriterium mit weniger als 50 Punkte &rArr; Diplomarbeit gesamt negativ</td></tr>";
		}
		else 
		{
			$htmlstr .= "<tr><td colspan='5'>Ein Kriterium mit weniger als 50 Punkte &rArr; Bachelorarbeit gesamt negativ</td></tr>";
		}
		$htmlstr .= "</table>";
		$htmlstr .= "<br><input type='submit' name='drucken' value='Formular ausdrucken'>";
		$htmlstr .="</form>";
		$htmlstr .="</body></html>";
		echo $htmlstr;
	}
	else 
	{
		die('Betreuung nicht gefunden!');
	}	
}

?>