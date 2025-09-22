<?php
/* Copyright (C) 2010 FH Technikum Wien
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
 *          Karl Burkhart <karl.burkhart@technikum-wien.at>.
 */
/*
 * Raummitteilung
 *
 * Oeffnet ein E-Mail Fenster mit allen Personen die in einem bestimmten
 * Zeitraum einem Raum zugeteilt sind. (Reservierung und LV-Plan)
 *
 * Dies dient dazu, die Personen im Falle eines Problemes in diesem Raum zu informieren
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/ort.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/variable.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Raummitteilung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../vendor/components/jqueryui/themes/base/jquery-ui.min.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
</head>
<body>
	<h2>Raummitteilung</h2>
<?php
$von = (isset($_POST['von'])?$_POST['von']:date('d.m.Y'));
$bis = (isset($_POST['bis'])?$_POST['bis']:date('d.m.Y', mktime(0,0,0,date('m'),date('d')+7,date('Y'))));
$von_stunde = (isset($_POST['von_stunde'])?$_POST['von_stunde']:1);
$bis_stunde = (isset($_POST['bis_stunde'])?$_POST['bis_stunde']:1);
$ort_kurzbz = (isset($_POST['ort_kurzbz'])?$_POST['ort_kurzbz']:'');
$inkl_studenten = isset($_POST['inkl_studenten']);
if($ort_kurzbz=='')
	$inkl_studenten=true;
$datum_obj = new datum();
$db = new basis_db();
$user = get_uid();
$variable = new variable();
$variable->loadVariables($user);

echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';

echo 'Ort <SELECT name="ort_kurzbz">';
$orte = new ort();
$orte->getAll();

foreach($orte->result as $row)
{
	if($row->aktiv && $row->lehre)
	{
		if($row->ort_kurzbz==$ort_kurzbz)
			$selected='selected';
		else
			$selected='';

		echo '<OPTION value="'.$row->ort_kurzbz.'" '.$selected.'>'.$row->ort_kurzbz.'</OPTION>';
	}
}
echo '</SELECT>';

echo ' Von <input type="text" size="10" maxlength="10" name="von" id="von" value="'.$von.'">
		<script type="text/javascript">
			$("#von" ).datepicker($.datepicker.regional["de"]);
		</script>
		Stunde <SELECT name="von_stunde">';
for($i=1;$i<15;$i++)
{
	if($i==$von_stunde)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$i.'" '.$selected.'>'.$i.'</OPTION>';
}
echo '</SELECT>
		Bis <input type="text" size="10" maxlength="10" name="bis" id="bis" value="'.$bis.'">
		<script type="text/javascript">
			$("#bis" ).datepicker($.datepicker.regional["de"]);
		</script>
		Stunde <SELECT name="bis_stunde">';
for($i=1;$i<15;$i++)
{
	if($i==$bis_stunde)
		$selected='selected';
	else
		$selected='';
	echo '<OPTION value="'.$i.'" '.$selected.'>'.$i.'</OPTION>';
}

echo '</SELECT>';
echo ' inklusive Studenten<input type="checkbox" name="inkl_studenten" '.($inkl_studenten?'checked':'').'>';
echo ' <input type="submit" name="show" value="Anzeigen"/>';
echo '</form>';

if(isset($_POST['show']))
{
	$mails = array();
	$von = $datum_obj->formatDatum($von, 'Y-m-d');
	$bis = $datum_obj->formatDatum($bis, 'Y-m-d');

	if($von===false || $von=='')
		die('Das Von Datum ist ungueltig');
	if($bis===false || $bis=='')
		die('Das Bis Datum ist ungueltig');

	if(!is_numeric($von_stunde) || $von_stunde=='')
		die('Von Stunde ist ungueltig');
	if(!is_numeric($bis_stunde) || $bis_stunde=='')
		die('Bis Stunde ist ungueltig');

	if(!check_ort($ort_kurzbz))
		die('Ort ist ungueltig');

	//LV-Plan
	$qry = "SELECT distinct lehreinheit_id FROM
				lehre.tbl_stundenplan
			WHERE
				tbl_stundenplan.datum>='".addslashes($von)."' AND tbl_stundenplan.datum<='".addslashes($bis)."'
				AND NOT (tbl_stundenplan.datum='".addslashes($von)."' AND tbl_stundenplan.stunde<'".addslashes($von_stunde)."')
				AND NOT (tbl_stundenplan.datum='".addslashes($bis)."' AND tbl_stundenplan.stunde>'".addslashes($bis_stunde)."')
				AND tbl_stundenplan.ort_kurzbz='".addslashes($ort_kurzbz)."'
			";

	if($result = $db->db_query($qry))
	{
		$lehreinheiten=array();
		while($row = $db->db_fetch_object($result))
			$lehreinheiten[]=$row->lehreinheit_id;

		if(count($lehreinheiten)>0)
		{
			$les = $db->implode4SQL($lehreinheiten);
			if($inkl_studenten)
			{
				// Studenten aus dem LV-Plan
				$qry = "SELECT distinct uid FROM campus.vw_student_lehrveranstaltung WHERE lehreinheit_id IN($les)";
				if($result = $db->db_query($qry))
				{
					while($row = $db->db_fetch_object($result))
						$mails[]=$row->uid.'@'.DOMAIN;
				}
			}

			//Lektoren aus dem LV-Plan
			$qry = "SELECT distinct mitarbeiter_uid as uid FROM lehre.tbl_lehreinheitmitarbeiter WHERE lehreinheit_id IN($les)";
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
					$mails[]=$row->uid.'@'.DOMAIN;
			}
		}
	}

	//Reservierung
	$qry = "SELECT *
			FROM
				campus.tbl_reservierung
			WHERE
				datum>='$von' AND datum<='$bis'
				AND NOT (datum='$von' AND stunde<'$von_stunde')
				AND NOT (datum='$bis' AND stunde>'$bis_stunde')
				AND ort_kurzbz='".addslashes($ort_kurzbz)."'";
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$stsem = getStudiensemesterFromDatum($row->datum);
			//Reservierer
			$mails[]=$row->uid.'@'.DOMAIN;
			if($inkl_studenten)
			{
				if($row->studiengang_kz!=0 && $row->gruppe_kurzbz=='')
				{
					//Studierende aus Lehrverbandsgruppen
					$qry = "SELECT
								student_uid as uid
							FROM
								public.tbl_studentlehrverband
							WHERE
								studiensemester_kurzbz='".$stsem."'
								AND studiengang_kz='".$row->studiengang_kz."'";
					if($row->semester!='')
						$qry.=" AND semester='".$row->semester."'";
					if($row->verband!='')
						$qry.=" AND verband='".$row->verband."'";
					if($row->gruppe!='')
						$qry.=" AND gruppe='".$row->gruppe."'";

					if($result_gruppe = $db->db_query($qry))
					{
						while($row_gruppe = $db->db_fetch_object($result_gruppe))
							$mails[] = $row_gruppe->uid.'@'.DOMAIN;
					}
				}
				elseif($row->gruppe_kurzbz!='')
				{
					//Studierende aus den Spezialgruppen
					$qry = "SELECT
								uid
							FROM
								public.tbl_benutzergruppe
							WHERE
								gruppe_kurzbz='".addslashes($row->gruppe_kurzbz)."'
								AND studiensemester_kurzbz='".$stsem."'";
					if($result_gruppe = $db->db_query($qry))
					{
						while($row_gruppe = $db->db_fetch_object($result_gruppe))
							$mails[] = $row_gruppe->uid.'@'.DOMAIN;
					}
				}
			}
		}
	}

	//Zusammenfassen
	$mails = array_unique($mails);
	echo '<br>Anzahl der Empfänger: ',count($mails);

	echo '<br /><br /><a href="#MailSenden" onclick="splitmailto(mails, \'bcc\'); return false;">E-Mail öffnen</a>';
	echo "
		<script type=\"text/Javascript\">
		var mails = '".implode($variable->variable->emailadressentrennzeichen,$mails)."';

		// ****
		// * Teilt die Mailto Links auf kleinere Brocken auf, da der
		// * Link nicht funktioniert wenn er zu lange ist
		// * art = to | cc | bcc
		// ****
		function splitmailto(mails, art)
		{
			var splititem = '".$variable->variable->emailadressentrennzeichen."';
			var splitposition=0;
			var mailto='';
			var loop=true;
			if(mails.length>2048)
				alert('Aufgrund der großen Anzahl an Empfängern, muss die Nachricht auf mehrere E-Mails aufgeteilt werden!');

			while(loop)
			{
				if(mails.length>2048)
				{
					splitposition=mails.indexOf(splititem,1900);
					mailto = mails.substring(0,splitposition);
					mails = mails.substring(splitposition);
				}
				else
				{
					loop=false;
					mailto=mails;
				}

				if(art=='to')
					window.location.href='mailto:'+mailto;
				else
					window.location.href='mailto:?'+art+'='+mailto;
			}
		}
		</script>";

	echo '<hr>enthaltene Personen:<br />';
	//Liste der Personen anzeigen
	foreach($mails as $row)
		echo "<br />$row";


}
?>
</body>
</html>
