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

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');

	$adress=MAIL_ADMIN;

	$user=get_uid();

	if (isset($_GET['uid']))
		$uid=$_GET['uid'];
	if (isset($_GET['stdsem']))
		$stdsem=$_GET['stdsem'];

	if ($uid!=$user)
	{
		mail($adress,"Unerlaubter Zugriff auf Lehrveranstaltungen",
			"User $user hat versucht die LVAs von User $uid zu betrachten!",
			"From: vilesci@".DOMAIN);
		die("Keine Berechtigung!");
	}

	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Studiensemester abfragen.
	$sql_query='SELECT * FROM public.tbl_studiensemester WHERE ende>=now() ORDER BY start';
	$result_stdsem=pg_query($conn, $sql_query);
	$num_rows_stdsem=pg_num_rows($result_stdsem);
	if (!isset($stdsem))
		$stdsem=pg_result($result_stdsem,0,"studiensemester_kurzbz");


	//Lehrveranstaltungen abfragen.
	$sql_query="SELECT * FROM campus.vw_lehreinheit
		WHERE studiensemester_kurzbz='$stdsem' AND mitarbeiter_uid='$uid'";
	$sql_query.=" ORDER BY stg_kurzbz,semester,verband,gruppe";
	$result=pg_query($conn, $sql_query);
	$num_rows=pg_num_rows($result);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Reservierungsliste</title>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<script language="Javascript">
	<!--
	function printhelp()
	{
		alert('Erklärung\n'+
			'LVNR: Interne FAS-Nummer der Lehrveranstaltung\n'+
			'STG-S-V-G: Studiengang-Semester-Verband-Gruppe\n'+
			'Gruppe: Spezialgruppen (Module, Projektgruppen, Spezialisierungsgruppen)\n'+
			'Block: blockung (1->Einzelstunden; 2->Doppelstunden; ...)\n'+
			'WR: Wochenrythmus (1->jede Woche; 2->jede 2. Woche; ...)\n'+
			'Std: gesamte Semesterstunden\n'+
			'KW: Kalenderwoche in der die Lehrveranstaltung startet');
	}
	-->
	</script>
</head>
<body id="inhalt">
	<H2>
		<table class="tabcontent">
		<tr>
			<td>
				&nbsp;<a class="Item" href="index.php">Userprofil</a> &gt;&gt;
				&nbsp;Lehrveranstaltungen (<?php echo $stdsem;?>)
			</td>
			<td align="right"></td>
		</tr>
		</table>
	</H2>
	<?php
	for ($i=0;$i<$num_rows_stdsem;$i++)
	{
		$row=pg_fetch_object($result_stdsem);
		echo '<A class="Item" href="lva_liste.php?uid='.$uid.'&stdsem='.$row->studiensemester_kurzbz.'">'.$row->studiensemester_kurzbz.'</A> - ';
	}
	if ($num_rows>0)
	{
		echo '<BR><BR><H3>Lehrveranstaltungen - <a href="#" onclick="printhelp()" class="Item">Hilfe</a></H3><table border="0">';
		echo '<tr class="liste"><th>LVNR</th><th>Lehrfach</th><th>Lehrform</th><th>LV Bezeichnung</th><th>Lehrfach Bezeichnung</th><th>Lektor</th><th>STG</th><th>S</th><th>V</th><th>G</th><th>Gruppe</th><th>Raumtyp</th><th>Alternativ</th><th>Block</th><th>WR</th><th>Std</th><th>KW</th><th>Anmerkung</th></tr>';
		for ($i=0; $i<$num_rows; $i++)
		{
			$zeile=$i % 2;
			$row=pg_fetch_object($result);

			echo '<tr class="liste'.$zeile.'">';
			echo '<td>'.$row->lvnr.'</td>';
			echo '<td>'.$row->lehrfach.'</td>';
			echo '<td>'.$row->lehrform_kurzbz.'</td>';
			$qry = "SELECT bezeichnung FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$row->lehrveranstaltung_id'";
			$result_lv = pg_query($conn, $qry);
			$row_lv = pg_fetch_object($result_lv);
			echo '<td>'.$row_lv->bezeichnung.'</td>';
			echo '<td>'.$row->lehrfach_bez.'</td>';
			echo '<td>'.$row->lektor.'</td>';
			echo '<td>'.strtoupper($row->stg_typ.$row->stg_kurzbz).'</td>';
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
		
	//Betreuungen

	$mitarbeiter = new benutzer($conn);
	$mitarbeiter->load($uid);
	
	$qry = "SELECT 
				tbl_lehrveranstaltung.bezeichnung, tbl_projektarbeit.titel, 
				(SELECT nachname || ' ' || vorname FROM public.tbl_benutzer JOIN public.tbl_person USING(person_id) 
				 WHERE uid=student_uid) as student, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester
			FROM 
				lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_projektarbeit, lehre.tbl_projektbetreuer
			WHERE
				tbl_lehreinheit.lehreinheit_id=tbl_projektarbeit.lehreinheit_id AND
				tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND
				tbl_lehreinheit.studiensemester_kurzbz='$stdsem' AND
				tbl_projektarbeit.projektarbeit_id=tbl_projektbetreuer.projektarbeit_id AND
				tbl_projektbetreuer.person_id='$mitarbeiter->person_id'";
	
	$stg_obj = new studiengang($conn);
	$stg_obj->getAll();
	
	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)>0)
		{
			echo '<H3>Betreuungen</H3>';
			echo '<table>';
			echo '<tr class="liste">';
			echo '<th>Stg</th>';
			echo '<th>Sem</th>';
			echo '<th>Lehrveranstaltung</th>';
			echo '<th>Student</th>';
			echo '<th>Titel der Projektarbeit</th>';
			echo '</tr>';
			
			while($row = pg_fetch_object($result))
			{
				echo '<tr>';
				
				echo '<td>'.$stg_obj->kuerzel_arr[$row->studiengang_kz].'</td>';
				echo '<td>'.$row->semester.'</td>';
				echo '<td>'.$row->bezeichnung.'</td>';
				echo '<td>'.$row->student.'</td>';
				echo '<td>'.$row->titel.'</td>';
				
				echo '</tr>';
			}
			echo '</table>';
		}
	}
	
	
	//Koordination
	
	$qry = "SELECT 
				distinct
				tbl_lehrveranstaltung.studiengang_kz, tbl_lehrfach.fachbereich_kurzbz, tbl_lehrveranstaltung.bezeichnung, 
				tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.semester
			FROM 
				lehre.tbl_lehrveranstaltung, 
				lehre.tbl_lehreinheit,
				lehre.tbl_lehrfach
			WHERE
				tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
				tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
				tbl_lehreinheit.studiensemester_kurzbz='$stdsem' AND
				(tbl_lehrveranstaltung.koordinator='$user' OR 
				 (tbl_lehrveranstaltung.studiengang_kz, fachbereich_kurzbz) IN (SELECT studiengang_kz, fachbereich_kurzbz FROM public.tbl_benutzerfunktion 
				 										  WHERE funktion_kurzbz='fbk' AND uid='$uid')
				 )";
	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)>0)
		{
			echo '<H3>Koordination</H3>';
			echo '<table>';
			echo '<tr class="liste">';
			echo '<th>Stg</th>';
			echo '<th>Sem</th>';
			echo '<th>Institut</th>';
			echo '<th>LV</th>';
			echo '<th>Lektoren</th>';
			echo '</tr>';
			while($row = pg_fetch_object($result))
			{
				echo '<tr>';
				echo '<td>'.$stg_obj->kuerzel_arr[$row->studiengang_kz].'</td>';
				echo '<td>'.$row->semester.'</td>';
				echo '<td>'.$row->fachbereich_kurzbz.'</td>';
				echo '<td>'.$row->bezeichnung.'</td>';
				$qry = "SELECT 
							titelpre, titelpost, vorname, nachname
						FROM 
							lehre.tbl_lehreinheitmitarbeiter,
							public.tbl_benutzer,
							public.tbl_person,
							lehre.tbl_lehreinheit
						WHERE 
							tbl_lehreinheitmitarbeiter.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
							tbl_lehreinheit.lehrveranstaltung_id='$row->lehrveranstaltung_id' AND
							tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
							tbl_benutzer.person_id=tbl_person.person_id AND
							tbl_lehreinheit.studiensemester_kurzbz='$stdsem'";
				$lektoren='';
				if($result_lkt = pg_query($conn, $qry))
				{
					while($row_lkt = pg_fetch_object($result_lkt))
					{
						if($lektoren!='')
							$lektoren.=',';
						$lektoren.=trim($row_lkt->titelpre.' '.$row_lkt->vorname.' '.$row_lkt->nachname.' '.$row_lkt->titelpost);
					}
				}
				echo '<td>'.$lektoren.'</td>';
				echo '</tr>';
			}
			echo '</table>';
		}
	}
echo "<BR>Fehler und Feedback bitte an den betreffenden Studiengang!<BR>
		 <HR>";
?>
</body>
</html>