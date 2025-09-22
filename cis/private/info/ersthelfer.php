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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/phrasen.class.php');
	
$sprache = getSprache(); 
$p=new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

if (!$user=get_uid())
	die($p->t("global/nichtAngemeldet").'! <a href="javascript:history.back()">Zur&uuml;ck</a>');

if(check_lektor($user))
       $is_lector=true;
  else
       $is_lector=false;     

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
<script type="text/javascript">
		$(document).ready(function() 
			{ 
				$("#t1").tablesorter(
				{
					sortList: [[0,0],[1,0]],
					widgets: ["zebra"]
				});
				$("#t2").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				}); 
				$("#t3").tablesorter(
				{
					sortList: [[0,1]],
					widgets: ["zebra"]
				});
				$("#t4").tablesorter(
				{
					sortList: [[0,0]],
					widgets: ["zebra"]
				});
			});
</script>
		
<title><?php echo $p->t("notfallbestimmungen/ersthelferUndBrandschutzbeauftragte");?></title>
</head>

<body>
<h1><?php echo $p->t("notfallbestimmungen/ersthelferUndBrandschutzbeauftragte");?></h1>
<table class="cmstable" cellspacing="0" cellpadding="0">
	<tr>
		<td rowspan="3" valign="top">	
			<table cellspacing="0" cellpadding="0">
			<tr valign="top">
				<td width="50%">
				<h2 style="margin-top:0px;"><?php echo $p->t("notfallbestimmungen/ersthelfer");?></h2>
				<?php echo $p->t("notfallbestimmungen/ausbildungErfolgteDurchORK");?><br/>
				<strong><br/><?php echo $p->t("notfallbestimmungen/folgendePersonenStehenZurVerfuegung");?>:<br/></strong>
					<table width="30%">
					<tr>
						<td valign="top">
						
				<!--Ersthelfer auslesen-->	
				
				<?php
					$zeilenzaehl=0;
					$sql_query = "	SELECT uid, 
										vorname,
										nachname,
										COALESCE((
												SELECT tbl_kontakt.kontakt || ' - ' || telefonklappe
												FROM PUBLIC.tbl_mitarbeiter
												LEFT JOIN PUBLIC.tbl_kontakt USING (standort_id)
												WHERE mitarbeiter_uid = vw_mitarbeiter.uid
													AND (
														tbl_kontakt.kontakttyp = 'telefon'
														OR tbl_kontakt.kontakttyp IS NULL
														) limit 1
												), '-') AS telefonklappe,
										COALESCE((
												SELECT planbezeichnung
												FROM PUBLIC.tbl_mitarbeiter
												LEFT JOIN PUBLIC.tbl_ort USING (ort_kurzbz)
												WHERE mitarbeiter_uid = vw_mitarbeiter.uid
												), '-') AS ort_kurzbz,
										beschreibung
									FROM campus.vw_mitarbeiter
									JOIN PUBLIC.tbl_benutzerfunktion USING (uid)
									JOIN PUBLIC.tbl_funktion USING (funktion_kurzbz)
									WHERE funktion_kurzbz = 'ersthelfer'
										AND (
											datum_von <= 'now()'
											OR datum_von IS NULL
											)
										AND (
											datum_bis >= 'now()'
											OR datum_bis IS NULL
											)
										AND campus.vw_mitarbeiter.aktiv = TRUE
									ORDER BY nachname,
										vorname";
					$result = $db->db_query($sql_query);
					if ($db->db_num_rows($result) > 0)
					{
						echo '<table class="tablesorter" id="t1">
						<thead>
							<tr>
								<th>'.$p->t("global/nachname").'</th>
								<th>'.$p->t("global/vorname").'</th>
								<th>'.$p->t("lvplan/raum").'</th>				
								<th>'.$p->t("global/telefonnummer").'</th>
							</tr>
						</thead><tbody>';
						
						while($row = $db->db_fetch_object($result))		
						{
							echo '
							<tr>
								<td width="40%"><a href="../profile/index.php?uid='.$row->uid.'">'.$row->nachname.'</a></td>
								<td width="30%">'.$row->vorname.'</td>
								<td width="15%">'.$row->ort_kurzbz.'</td>
								<td width="30%"><nobr>'.$row->telefonklappe.'</nobr></td>
							</tr>';
						}
						
						echo '
							</tbody></table>';
					}
				?>
					</table>	
			
				</td>
				<td  width="50%">
				
				<!--Brandschutzbeauftragte auslesen-->	
		
				<?php
					$zeilenzaehl=0;
					$sql_query = "SELECT vorname, nachname, telefonklappe, kontakt, ort_kurzbz, beschreibung, vw_mitarbeiter.standort_id, uid
					FROM campus.vw_mitarbeiter 
					JOIN public.tbl_benutzerfunktion USING (uid) 
					JOIN public.tbl_funktion USING (funktion_kurzbz) 
					JOIN public.tbl_kontakt USING (person_id) 
					WHERE funktion_kurzbz='brandbeauftragt' 
					AND (
						datum_von <= 'now()'
						OR datum_von IS NULL
						)
					AND (
						datum_bis >= 'now()'
						OR datum_bis IS NULL
						)
					AND campus.vw_mitarbeiter.aktiv=TRUE 
					AND vw_mitarbeiter.standort_id IS NOT NULL 
					AND kontakttyp='firmenhandy' 
					ORDER BY funktion_kurzbz";
					$result = $db->db_query($sql_query);
					if ($db->db_num_rows($result) > 0)
					{
						echo '<h2 style="margin-top:0px;">'.$p->t("notfallbestimmungen/brandschutzbeauftragte").'</h2>';
						echo '<table class="tablesorter" id="t2">
								<thead>
									<tr>
										<th>Nachname</th>
										<th>Vorname</th>
										<th>Nummer</th>
									</tr>
								</thead><tbody>';
						while($row = $db->db_fetch_object($result))
						{
								echo '
									<tr>
										<td width="40%"><a href="../profile/index.php?uid='.$row->uid.'">'.$row->nachname.'</a></td>
										<td width="30%">'.$row->vorname.'</td>
										<td ><nobr>'.$row->kontakt.'</nobr></td>
									</tr>';
						}
						echo '
							</tbody></table>';
					}
				?>
		
				<!--Rektorat auslesen-->	
		
				<?php
					$zeilenzaehl=0;
					$sql_query = "SELECT uid, vorname, nachname, telefonklappe, kontakt, ort_kurzbz, beschreibung, vw_mitarbeiter.standort_id 
					FROM campus.vw_mitarbeiter 
					JOIN public.tbl_benutzerfunktion USING (uid) 
					JOIN public.tbl_funktion USING (funktion_kurzbz) 
					JOIN public.tbl_kontakt USING (person_id) 
					WHERE funktion_kurzbz IN ('rek','vrek') AND (datum_bis>='now()' OR datum_bis IS NULL) 
					AND campus.vw_mitarbeiter.aktiv=TRUE 
					AND vw_mitarbeiter.standort_id IS NOT NULL 
					AND kontakt LIKE '%61925%' 
					ORDER BY funktion_kurzbz";
					$result = $db->db_query($sql_query);
					if ($db->db_num_rows($result) > 0)
					{
						echo '<h2>'.$p->t("notfallbestimmungen/rektorat").'</h2>';
						echo '<table class="tablesorter" id="t3">
								<thead>
									<tr>
										<th>Nachname</th>
										<th>Vorname</th>
										<th>Beschreibung</th>
										<th>Nummer</th>
									</tr>
								</thead><tbody>';
						while($row = $db->db_fetch_object($result))
						{
								echo '
									<tr>
										<td><a href="../profile/index.php?uid='.$row->uid.'">'.$row->nachname.'</a></td>
										<td>'.$row->vorname.'</td>
										<td>('.$row->beschreibung.')</td>
										<td><nobr>'.$row->kontakt.'</nobr></td>
									</tr>';
						}
						echo '
							</tbody></table>';
					}
				?>
		
				<!--Brandschutzwarte auslesen-->	
		
				<?php
					$zeilenzaehl=0;
					$sql_query = "SELECT uid, vorname, nachname, telefonklappe, ort_kurzbz, beschreibung, standort_id 
					FROM campus.vw_mitarbeiter 
					JOIN public.tbl_benutzerfunktion USING (uid) 
					JOIN public.tbl_funktion USING (funktion_kurzbz) 
					WHERE funktion_kurzbz='brandwart' 
					AND (datum_bis>='now()' OR datum_bis IS NULL) 
					AND campus.vw_mitarbeiter.aktiv=TRUE 
					AND standort_id IS NOT NULL 
					ORDER BY standort_id, nachname, vorname";
					$result = $db->db_query($sql_query);
					if ($db->db_num_rows($result) > 0)
					{
						echo '<h2>'.$p->t("notfallbestimmungen/brandschutzwarte").'</h2>';
						echo '<table class="tablesorter" id="t4">
								<thead>
									<tr>
										<th>Nachname</th>
										<th>Vorname</th>
									</tr>
								</thead><tbody>';
						while($row = $db->db_fetch_object($result))
						{
								echo '
									<tr>
										<td width="40%"><a href="../profile/index.php?uid='.$row->uid.'">'.$row->nachname.'</a></td>
										<td width="30%">'.$row->vorname.'</td>
									</tr>';
						}
						echo '
							</tbody></table>';
					}
				?>
		
				</td>
			</tr>
			</table>
		</td>
		<td class="menubox">
		<p><a href="../../../cms/content.php?content_id=<?php echo $p->t("dms_link/sicherheitAnDerFHTW");?>"><?php echo $p->t("notfallbestimmungen/sicherheitAnDerFHTW");?></a></p>
		</td>
	</tr>
	<tr>
		<td style="width: 20%;" valign="top">&nbsp;</td>
	</tr>
	<tr>
		<td style="width: 20%;" valign="top">&nbsp;</td>
	</tr>
	</table>	
</table>
</body>
</html>
