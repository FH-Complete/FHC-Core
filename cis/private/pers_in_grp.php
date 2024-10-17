<?php
/*
 * Copyright (C) 2006 Technikum-Wien
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 * Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 * Rudolf Hangl < rudolf.hangl@technikum-wien.at >
 * Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at >
 */
require_once ('../../config/cis.config.inc.php');
require_once ('../../include/basis_db.class.php');
require_once ('../../include/functions.inc.php');
require_once ('../../include/studiensemester.class.php');
require_once ('../../include/phrasen.class.php');
require_once ('../../include/gruppe.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (! $db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (! $user = get_uid())
	die($p->t('global/fehlerBeimErmittelnDerUID'));

$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();
$ss_nearest_to_akt = $stsem_obj->getNearestFrom($stsem);

if (check_lektor($user))
	$is_lector = true;
else
	$is_lector = false;

$gruppe_kurzbz = $_GET['grp'];

$gruppe = new gruppe($gruppe_kurzbz);

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8">
	<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">';

	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');

?>
	<script src="../../vendor/rmariuzzo/jquery-checkboxes/dist/jquery.checkboxes-1.0.7.min.js" type="text/javascript"></script>
	<script type="text/javascript">
	$(document).ready(function()
		{
			$("#table").tablesorter(
			{
				sortList: [[1,0]],
				widgets: ["zebra", "filter", "stickyHeaders"],
				headers: {	0: {sorter: false, filter: false}},
				widgetOptions : {filter_saveFilters : false,
								filter_functions : {
									// Add select menu to this column
									3 : {
									"M" : function(e, n, f, i, $r, c, data) { return /M/.test(e); },
									"W" : function(e, n, f, i, $r, c, data) { return /W/.test(e); }
									},
									4 : {
									" - " : function(e, n, f, i, $r, c, data) { return /-/.test(e); },
									"Ja" : function(e, n, f, i, $r, c, data) { return /Ja/.test(e); },
									"Nein" : function(e, n, f, i, $r, c, data) { return /Nein/.test(e); }
									}
								}
							}
			})
			// Set number of result rows after filtering
			.bind("filterEnd",function(e, t) 
			{
				var rows = $('table.hasFilters tbody tr:visible').length;
				$("#rowCounter").html(rows);
			});
			
			$("#toggle").on("click", function(e) 
			{
				$("#table").checkboxes("toggle");
				e.preventDefault();
				if ($("input.chkbox:checked").length > 0)
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnMarkierteSenden'); ?>');
				else
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnAlleSenden'); ?>');
			});

			$("#uncheck").on("click", function(e) 
			{
				$("#table").checkboxes("uncheck");
				e.preventDefault();
				if ($("input.chkbox:checked").length > 0)
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnMarkierteSenden'); ?>');
				else
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnAlleSenden'); ?>');
			});

			$("#table").checkboxes("range", true);

			$('.chkbox').change(function()
			{
				if ($("input.chkbox:checked").length > 0)
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnMarkierteSenden'); ?>');
				else
					$("#mailSendButton").html('<?php echo $p->t('mailverteiler/mailAnAlleSenden'); ?>');
			});
		}
	);
	function SendMail()
	{
		// Wenn Checkboxen markiert sind, an diese senden, sonst an alle
		if ($("input.chkbox:checked").length > 0)
		{
			var elements = $("input.chkbox:checked");
		}
		else
		{
			var elements = $("input.chkbox:visible");
		}

		var mailadressen = "";
		var adresse = "";
		var counter = 0;

		// Schleife ueber die einzelnen Elemente
		// Aus Spamgründen dürfen je Nachricht maximal 100 Empfänger enthalten sein
		// Deshalb wird nach 100 Einträgen ein neues window.location.href erzeugt
		// Außerdem darf die URL nicht länger als 2048 Zeichen sein
		$.each(elements, function(index, item)
		{
			adresse = $(this).closest("tr").find("td.clm_email a:first").attr("href");
			adresse = adresse.replace(/^mailto?:/, "") + ";";
			if (counter > 0 && (counter % 100 === 0) || (mailadressen.length + adresse.length > 2048))
			{
				window.location.href = "mailto:?bcc="+mailadressen;
				mailadressen = "";
				counter = 0;
			}
			mailadressen += adresse;
			counter ++;
		});
		window.location.href = "mailto:?bcc="+mailadressen;
	}
	</script>
	<style type="text/css">
	.buttongreen, a.buttongreen
	{
		cursor: pointer;
		color: #FFFFFF;
		margin: 0 5px 5px 0;
		text-decoration: none;
		border-radius: 3px;
		-webkit-border-radius: 3px;
		-moz-border-radius: 3px;
		background-color: #5cb85c;
		border-top: 3px solid #5cb85c;
		border-bottom: 3px solid #5cb85c;
		border-right: 8px solid #5cb85c;
		border-left: 8px solid #5cb85c;
		display: inline-block;
		vertical-align: middle;
	}
	</style>
<?php
echo '	<title>' . $p->t('mailverteiler/personenImVerteiler') . '</title>
</head>
<body id="inhalt">';

$qry = "SELECT
				uid, vorname, nachname, geschlecht, fixangestellt
			FROM
				campus.vw_benutzer
			JOIN
				tbl_benutzergruppe USING (uid)
			LEFT JOIN
				public.tbl_mitarbeiter ON (uid = mitarbeiter_uid)
			WHERE
				gruppe_kurzbz=".$db->db_add_param($gruppe_kurzbz);

			// Fuer den Studiengang EWU wird zusaetzlich das aktuelle Studiensemester ermittelt
			if ($gruppe->studiengang_kz == 10005 && mb_stripos($gruppe_kurzbz,'EWU') === 0)
			{
				$qry .= "	AND (studiensemester_kurzbz IS NULL
				OR studiensemester_kurzbz IN (".$db->db_add_param($stsem).",".$db->db_add_param($ss_nearest_to_akt)."))";
			}
			else
			{
				$qry .= "	AND (studiensemester_kurzbz IS NULL
				OR studiensemester_kurzbz=".$db->db_add_param($stsem).")";
			}

			$qry .= " ORDER BY
				nachname, vorname";

echo '<p>'.$p->t('mailverteiler/anleitungstextMailPersInGroup').'<p>';
echo '<a class="buttongreen" href="#" onclick="SendMail()" id="mailSendButton">' . $p->t('mailverteiler/mailAnAlleSenden') . '</a>';

if ($result = $db->db_query($qry))
{
	echo '<p><span id="rowCounter">' . $row = $db->db_num_rows($result) . '</span> ' . $p->t('mailverteiler/personen') . '</p>';
}

echo '	
		<table class="tablesorter" id="table">
		<thead>
		<tr>
			<th style="text-align: center; width: 80px">
			<nobr>
				<a href="#" data-toggle="checkboxes" data-action="toggle" id="toggle" title="Alle markieren / Invertieren"><img src="../../skin/images/checkbox_toggle.png" name="toggle"></a>&nbsp;&nbsp;
				<a href="#" data-toggle="checkboxes" data-action="uncheck" id="uncheck" title="Keine markieren"><img src="../../skin/images/checkbox_uncheck.png" name="toggle"></a>
			</nobr>
			</th>
			<th>' . $p->t('global/nachname') . '</th>
			<th>' . $p->t('global/vorname') . '</th>
			<th>' . $p->t('global/geschlecht') . '</th>
			<th>' . $p->t('global/fixangestellt') . '</th>
			<th>' . $p->t('global/mail') . '</th>
		</tr></thead><tbody>';

if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		echo '<tr>';
		echo '	<td style="text-align: center"><input type="checkbox" class="chkbox" id="checkbox_'.$row->uid.'" name="checkbox['.$row->uid.']"></td>';
		echo '	<td>'.$row->nachname.'</td>';
		echo '	<td>'.$row->vorname.'</td>';
		echo '	<td>'.strtoupper($row->geschlecht).'</td>';
		echo '	<td>'.($row->fixangestellt != '' ? ($row->fixangestellt == 't' ? 'Ja' : 'Nein') :'-').'</td>';
		echo '	<td class="clm_email"><a href="mailto:'.$row->uid.'@' . DOMAIN . '" class="Item">'.$row->uid .'@' . DOMAIN . '</a></td>';
		echo '</tr>';
	}
}
echo '
		</tbody></table>
	</body>
</html>';
?>
