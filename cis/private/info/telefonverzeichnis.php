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
 *          Manfred Kindl <manfred.kindl@technikum-wien.at>
 *
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/person.class.php');

$sprache = getSprache();
$p=new phrasen($sprache);

if (isset($_GET['zeilenhoehe']) && is_numeric($_GET['zeilenhoehe']))
	$zeilenhoehe = $_GET['zeilenhoehe'];
else
	$zeilenhoehe = 28;

if (isset($_GET['gruppiert']) && ($_GET['gruppiert']=='on'))
	$gruppiert = true;
else
	$gruppiert = false;

if (isset($_GET['gst_extra']) && ($_GET['gst_extra']=='on'))
	$gst_extra = true;
else
	$gst_extra = false;

if (!$db = new basis_db())
	die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

if (!$user=get_uid())
	die($p->t("global/nichtAngemeldet").'! <a href="javascript:history.back()">Zur&uuml;ck</a>');

if(check_lektor($user))
       $is_lector=true;
  else
       $is_lector=false;

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
<title><?php echo $p->t("telefonverzeichnis/titelTelefonverzeichnis");?></title>
<script language="JavaScript" type="text/javascript">
<!--
	function RefreshImage()
	{
		window.location.reload();
	}
	$(document).ready(function()
	{
		$("#t1").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t2").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t3").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t4").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t5").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t6").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t7").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t10").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
		$("#t11").tablesorter(
		{
			sortList: [[0,0]],
			widgets: ["zebra"]
		});
	});
-->
</script>
<style>
td,th
{
	font-size: 18px;
}
@media print
{
	#nachname,#vorname,#durchwahl,#raum,#person
	{
		background-image: none;
		padding-left: 3px;
	}
	#formular
	{
		display:none;
	}
}
</style>
</head>

<body>
		<h1 style="padding-left: 15px;"><?php echo $p->t("telefonverzeichnis/titelTelefonverzeichnis");?></h1>
		<span id="formular">
		<form  style="padding-left: 15px;" action="<?php $_SERVER['PHP_SELF']; ?>" method="GET">
		Zeilenhöhe: <input type="text" size="3" maxlength="3" id="zeilenhoehe" name="zeilenhoehe" value="<?php echo $zeilenhoehe; ?>">px&nbsp;&nbsp;
		Geschäftsstelle extra:<input type="checkbox" id="gst_extra" name="gst_extra" <?php echo ($gst_extra==true?'checked="checked"':''); ?>> &nbsp;&nbsp;
		Gruppiert nach Standort:<input type="checkbox" id="gruppiert" name="gruppiert" <?php echo ($gruppiert==true?'checked="checked"':''); ?>> &nbsp;&nbsp;

		<input type="submit" value="OK">
		</form>
		</span>

	<table cellpadding="10" class="tabcontent" height="100%" id="inhalt">
	<tr>
		<td>
			<table>

<?php
	if ($gst_extra==true || $gruppiert == true)
	{
		$sql_query = "	SELECT vw_mitarbeiter.person_id, vw_mitarbeiter.vorname, vw_mitarbeiter.nachname, vw_mitarbeiter.telefonklappe, vw_mitarbeiter.ort_kurzbz, vw_mitarbeiter.standort_id, tbl_person.foto_sperre
						FROM campus.vw_mitarbeiter JOIN public.tbl_person USING (person_id) WHERE telefonklappe!='' AND standort_id is not null AND vw_mitarbeiter.aktiv=true AND vw_mitarbeiter.standort_id!='4' ORDER BY standort_id, nachname, vorname";
	}
	else
	{
		$sql_query = "	SELECT vw_mitarbeiter.person_id, vw_mitarbeiter.vorname, vw_mitarbeiter.nachname, vw_mitarbeiter.telefonklappe, vw_mitarbeiter.ort_kurzbz, vw_mitarbeiter.standort_id, tbl_person.foto_sperre
						FROM campus.vw_mitarbeiter JOIN public.tbl_person USING (person_id) WHERE telefonklappe!='' AND standort_id is not null AND vw_mitarbeiter.aktiv=true ORDER BY standort_id, nachname, vorname";
	}

	$result = $db->db_query($sql_query);
	$laststandort='0';
	$i=1;

	if ($gruppiert == false)
	{
		echo '
				<tr>
					<td colspan="3"><h2>'.$p->t("telefonverzeichnis/titelTelefonverzeichnis").' '.CAMPUS_NAME.'</h2></td>
				</tr>
				<tr>
				<td>
					<table class="tablesorter" id="t'.$i.'">
					<thead>
						<!--<th id="person">'.$p->t("global/person").'</th>-->
						<th id="nachname">'.$p->t("global/nachname").'</th>
						<th id="vorname">'.$p->t("global/vorname").'</th>
						<th id="durchwahl">'.$p->t("telefonverzeichnis/durchwahl").'</th>
						<th id="raum">'.$p->t("lvplan/raum").'</th>
					</thead>
					<tbody>';

		while($row = $db->db_fetch_object($result))
		{
			echo '
			<tr>
				<!--<td>';
				/*if ($row->foto_sperre!="t")
				{
					//echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$row->person_id.'" alt="'.$row->person_id.'" height="80px" width="60px">';
				}
				else
				{
					//echo '<img id="personimage" src="../../../skin/images/profilbild_dummy.jpg" alt="Dummy Picture" height="80px" width="60px">';
				}*/
				echo '</td>-->
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;" height="'.$zeilenhoehe.'">'.$row->nachname.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->vorname.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->telefonklappe.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->ort_kurzbz.'</td>
			</tr>';
		}
		echo '
					</table>
					</td>
					</tr>
					';
		$i++;
	}
	else
	{
		while($row = $db->db_fetch_object($result))
		{
			if($laststandort!=$row->standort_id)
			{
				if($laststandort!='0')
				{
					echo '
					</table>
					</td>
					</tr>
					';
					$i++;
				}
				$laststandort = $row->standort_id;
				$qry_standort = "SELECT tbl_kontakt.kontakt as nummer, tbl_firma.name as name FROM public.tbl_standort JOIN public.tbl_firma USING(firma_id) JOIN public.tbl_kontakt USING(standort_id)
						WHERE standort_id=".$db->db_add_param($row->standort_id)."  AND kontakttyp='telefon'";
				if($result_standort = $db->db_query($qry_standort))
				{
					if($row_standort = $db->db_fetch_object($result_standort))
					{
						echo '
						<tr>
							<td colspan="3"><h2>'.$p->t("telefonverzeichnis/titelTelefonverzeichnis").' '.$row_standort->name.': '.$row_standort->nummer.'</h2></td>
						</tr>
						<tr>
						<td>
							<table class="tablesorter" id="t'.$i.'">
							<thead>
								<!--<th id="person">'.$p->t("global/person").'</th>-->
								<th id="nachname">'.$p->t("global/nachname").'</th>
								<th id="vorname">'.$p->t("global/vorname").'</th>
								<th id="durchwahl">'.$p->t("telefonverzeichnis/durchwahl").'</th>
								<th id="raum">'.$p->t("lvplan/raum").'</th>
							</thead>
							<tbody>';
					}
				}
			}
			echo '
			<tr>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;" height="'.$zeilenhoehe.'">'.$row->nachname.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->vorname.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->telefonklappe.'</td>
				<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->ort_kurzbz.'</td>
			</tr>';
		}
		$i++;
	}
	echo '
		</tbody></table>
		</td>
	</tr>
	</table>';

	if ($gst_extra==true || $gruppiert == true)
	{
		$sql_query = "	SELECT vw_mitarbeiter.person_id, vw_mitarbeiter.vorname, vw_mitarbeiter.nachname, vw_mitarbeiter.telefonklappe, vw_mitarbeiter.ort_kurzbz, vw_mitarbeiter.standort_id, tbl_person.foto_sperre
						FROM campus.vw_mitarbeiter JOIN public.tbl_person USING (person_id) WHERE telefonklappe!='' AND standort_id is not null AND vw_mitarbeiter.aktiv=true AND vw_mitarbeiter.standort_id='4' ORDER BY standort_id, nachname, vorname";
		$result = $db->db_query($sql_query);
		$laststandort='0';

			echo '
					<tr>
						<td colspan="3"><h2>'.$p->t("telefonverzeichnis/titelTelefonverzeichnis").' Geschäftsstelle: +43 1 588 39</h2></td>
					</tr>
					<tr>
					<td>
						<table class="tablesorter" id="t'.$i.'">
						<thead>
							<!--<th id="person">'.$p->t("global/person").'</th>-->
							<th id="nachname">'.$p->t("global/nachname").'</th>
							<th id="vorname">'.$p->t("global/vorname").'</th>
							<th id="durchwahl">'.$p->t("telefonverzeichnis/durchwahl").'</th>
							<th id="raum">'.$p->t("lvplan/raum").'</th>
						</thead>
						<tbody>';

			while($row = $db->db_fetch_object($result))
			{
				echo '
				<tr>
					<!--<td>';
					/*if ($row->foto_sperre!="t")
					{
						//echo '<img id="personimage" src="../../public/bild.php?src=person&person_id='.$row->person_id.'" alt="'.$row->person_id.'" height="80px" width="60px">';
					}
					else
					{
						//echo '<img id="personimage" src="../../../skin/images/profilbild_dummy.jpg" alt="Dummy Picture" height="80px" width="60px">';
					}*/
					echo '</td>-->
					<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;" height="'.$zeilenhoehe.'">'.$row->nachname.'</td>
					<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->vorname.'</td>
					<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->telefonklappe.'</td>
					<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->ort_kurzbz.'</td>
				</tr>';
			}
			echo '
						</table>
						</td>
						</tr>
						';
	}
?>
	<table width="100%">
	<tr>
		<td valign="top">
			<?php
			$qry = "SELECT * FROM public.tbl_ort WHERE standort_id is not null AND telefonklappe is not null and aktiv=true ORDER BY standort_id, bezeichnung";
			$laststandort='';
			$i=0;
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					if($laststandort!=$row->standort_id)
					{
						if($laststandort!='')
						{
							echo '</table><br>';
						}
						$qry_standort = "SELECT tbl_firma.name, tbl_kontakt.kontakt as telefon FROM public.tbl_standort JOIN public.tbl_firma USING(firma_id) JOIN public.tbl_kontakt USING(standort_id) WHERE standort_id='$row->standort_id' AND kontakttyp='telefon'";
						if($result_standort = $db->db_query($qry_standort))
						{
							if($row_standort = $db->db_fetch_object($result_standort))
							{
								echo '<h2>'.$p->t("lvplan/raeume").'&nbsp;'.$row_standort->name.': '.$row_standort->telefon.'</h2>';
							}
						}
						echo '
							<table class="tablesorter" id="t10">
							<thead>
							<tr>
								<th class="ContentHeader"><font class="ContentHeader">'.$p->t("lvplan/raeume").'</font></th>
								<th class="ContentHeader"><font class="ContentHeader">'.$p->t("telefonverzeichnis/durchwahl").'</font></th>
								<th class="ContentHeader"><font class="ContentHeader">'.$p->t("lvplan/raum").'</font></th>
							</tr>
							</thead>
							<tbody>';
					}
					$laststandort = $row->standort_id;
					$i++;
					echo '
					<tr>
						<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;" height="'.$zeilenhoehe.'">'.$row->bezeichnung.'</td>
						<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->telefonklappe.'</td>
						<td style="padding-top: 0; padding-bottom: 0; vertical-align: middle;">'.$row->planbezeichnung.' ('.$row->ort_kurzbz.')</td>
					</tr>
					';
				}

				if($laststandort!='')
				{
					echo '</tbody></table>';
				}
			}
			?>
	</table>
</body>
</html>
