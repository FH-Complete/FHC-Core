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
 * Authors: Manfred Kindl < manfred.kindl@technikum-wien.at >
 */
/**
 * Script zum Zusammenlegen mehrfacher Anmeldungen zu einem Reihungstestmodul in einem Studiensemester.
 * Es werden zwei Listen mit Bewerbungen eines Studiensemesters angezeigt, die sich für ein Modul mehrfach angemeldet haben
 * Links wird die Bewerberbung markiert, der rechts markierten zusammengelegt werden soll.
 * Die linke Bewerbung wird danach entfernt und eine Mail an den/die BewerberIn verschickt
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/person.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/reihungstest.class.php');
require_once('../../include/gebiet.class.php');
require_once('../../include/datum.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/mail.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/kontakt.class.php');
require_once('../../include/studienplan.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$uid = get_uid();
$datum_obj = new datum();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/reihungstest'))
	die($rechte->errormsg);

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

$studiensemester = new Studiensemester();
$studiensemester->getAll('DESC');

$stsem_akt = new studiensemester();
$stsem_akt = $stsem_akt->getaktorNext();

$studiensemester_kurzbz = (isset($_REQUEST['studiensemester_kurzbz']) ? $_REQUEST['studiensemester_kurzbz'] : $stsem_akt);
$prestudent_id = (isset($_REQUEST['prestudent_id']) ? $_REQUEST['prestudent_id'] : '');
$studienplan_id = (isset($_REQUEST['studienplan_id']) ? $_REQUEST['studienplan_id'] : '');

$studienplan = new studienplan();
$studienplan->loadStudienplan($studienplan_id);

$sprache = getSprache();
$p = new phrasen($sprache);
//var_dump($_POST);
if(isset($_POST['zusammenlegen']))
{
	if (isset($_POST['radio']) && isset($_POST['checkbox']))
	{
		$rt_person_ids = $_POST['checkbox'];
		$mail_neue_Termine = array();
		$load_rt_person = new reihungstest();
		if ($load_rt_person->loadReihungstestPerson($_POST['radio']))
		{
			$rt_termin_neu = new reihungstest($load_rt_person->rt_id);
			$mail_neuer_Termin = $datum_obj->formatDatum($rt_termin_neu->datum, 'd.m.Y').', '.$datum_obj->formatDatum($rt_termin_neu->uhrzeit,'H:i');
			$mail_alte_Termine = '';

			foreach ($rt_person_ids AS $key=>$value)
			{
				$neuzuteilung = new reihungstest();
				if ($neuzuteilung->loadReihungstestPerson($value))
				{
					$neuzuteilung->new = false;
					$id_alt = $neuzuteilung->rt_id;
					$neuzuteilung->rt_person_id = $neuzuteilung->rt_person_id;
					$neuzuteilung->anmeldedatum = $neuzuteilung->anmeldedatum;
					$neuzuteilung->teilgenommen = $neuzuteilung->teilgenommen;
					$neuzuteilung->punkte = $neuzuteilung->punkte;
					$neuzuteilung->studienplan_id = $neuzuteilung->studienplan_id;
					$neuzuteilung->rt_id = $load_rt_person->rt_id;
					$neuzuteilung->person_id = $neuzuteilung->person_id;
					$neuzuteilung->ort_kurzbz = $neuzuteilung->ort_kurzbz;
                    $neuzuteilung->updateamum = date('Y-m-d H:i:s');
                    $neuzuteilung->updatevon = $uid;

					if (!$neuzuteilung->savePersonReihungstest())
					{
						echo '<span class="input_error">Fehler beim Speichern der Daten: '.$db->convert_html_chars($neuzuteilung->errormsg).'</span>';
					}
					else
					{
						$rt_termin = new reihungstest($id_alt);
						$mail_alte_Termine .= '<li>'.$datum_obj->formatDatum($rt_termin->datum, 'd.m.Y').', '.$datum_obj->formatDatum($rt_termin->uhrzeit,'H:i').'</li>';
					}
				}
			}
			$kontakt = new kontakt();
			$kontakt->load_persKontakttyp($neuzuteilung->person_id, 'email');
			$mailadresse = '';
			if(count($kontakt->result)>0)
			{
				if ($kontakt->zustellung == true)
					$mailadresse = $kontakt->kontakt;
				elseif ($kontakt->zustellung == false)
					$mailadresse = $kontakt->kontakt;
				else
					echo '<span class="input_error">Es ist keine gueltige Zustelladresse für diese Person hinterlegt</span>';
			}
			else
				echo '<span class="input_error">Es ist keine Mailadresse für diese Person hinterlegt</span>';

			$person = new person();
			$person->load($neuzuteilung->person_id);
			if($person->geschlecht=='m')
				$anrede=$p->t('reihungstest/anredeMaennlich');
			else
				$anrede=$p->t('reihungstest/anredeWeiblich');

			$mail = new mail($mailadresse, 'no-reply', $p->t('reihungstest/betreff'), $p->t('reihungstest/mailtextHtml'));
			$text = $p->t('reihungstest/mailtext',array($person->vorname, $person->nachname, $anrede, $mail_neuer_Termin, $mail_alte_Termine));
			$mail->setHTMLContent($text);
			if(!$mail->send())
				echo '<span class="input_error">Fehler beim senden der E-Mail: '.$db->convert_html_chars($mail->errormsg).'</span>';
		}
		else
			echo '<span class="input_error">Fehler beim Laden der Daten: '.$db->convert_html_chars($load_rt_person->errormsg).'</span>';
	}
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../skin/fhcomplete.css" rel="stylesheet" type="text/css">
	<link href="../../skin/vilesci.css" rel="stylesheet" type="text/css">
	<link href="../../skin/jquery.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link href="../../skin/tablesort.css" rel="stylesheet" type="text/css"/>
	<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript">

	$(document).ready(function()
	{
		$(".tablesorter").each(function(i,v)
		{
			$("#"+v.id).tablesorter(
			{
				widgets: ["zebra"],
				sortList: [[2,0]],
				headers: {0: { sorter: false},1: { sorter: false}}
			});
		});
		$("#studienplan_autocomplete").autocomplete({
			source: "reihungstestverwaltung_autocomplete.php?autocomplete=studienplan",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].bezeichnung+' ('+ui.content[i].studienplan_id+')';
					ui.content[i].label=ui.content[i].bezeichnung+' ('+ui.content[i].studienplan_id+')';
				}
			},
			select: function(event, ui)
			{
				//Ausgewaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#studienplan_id").val(ui.item.studienplan_id);
			}
		});

	});
	function disable(id,person)
	{
		var checkboxes = document.getElementsByClassName('checkbox_'+person);
		if (document.getElementById('radio_'+id).checked == true)
		{
			document.getElementsByClassName('checkbox_'+person).disabled = false;
			for (var i=0, iLen=checkboxes.length; i<iLen; i++)
			{
				checkboxes[i].disabled = false;
			}
			if (document.getElementById('checkbox_'+id).checked == true)
				document.getElementById('checkbox_'+id).checked= false;
			document.getElementById('checkbox_'+id).disabled = true;
		}
		else
			document.getElementById('checkbox_'+id).disabled = false;
	}
	</script>

	<title>Reihungstest Anmeldungen</title>
</head>
<body>
<H1>Zusammenlegen von Reihungstest-Anmeldungen</H1>

<?php
echo "<form name='auswahl' action='reihungstest_zusammenlegung.php' method='GET'>";

// Studiensemester DropDown
echo "<SELECT name='studiensemester_kurzbz' onchange='this.form.submit()'>";

echo "<OPTION value='".$_SERVER['PHP_SELF']."?studiensemester_kurzbz='>Alle Studiensemester</OPTION>";
foreach ($studiensemester->studiensemester as $row)
{
	if($row->studiensemester_kurzbz == $studiensemester_kurzbz)
		$selected='selected';
	else
		$selected='';

		echo '<OPTION value="'.$row->studiensemester_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->studiensemester_kurzbz).'</OPTION>'.'\n';
}
echo "</SELECT>";

// Studienplan autocomplete
echo '<input id="studienplan_id" type="hidden" name="studienplan_id" value="'.$studienplan_id.'">';
echo '<input style="padding-left: 3px; margin-left: 5px;" id="studienplan_autocomplete" type="text" size="40" placeholder="Studienplan" value="'.$studienplan->bezeichnung.'">';
echo '<button type="submit">Anzeigen</button>';
echo "</form>";
echo '<br>';

if ($studienplan->bezeichnung != '')
	echo '<h2>'.$studienplan->bezeichnung.' ('.$studienplan_id.')</h2>';

$qry = "	SELECT rt_person.rt_person_id
				,rt_person.rt_id
				,ablauf.gebiet_id
				,person.person_id
				,person.nachname
				,person.vorname
				,rt_person.studienplan_id
				,stufe
				,teilgenommen
			FROM PUBLIC.tbl_person person
			JOIN PUBLIC.tbl_rt_person rt_person USING (person_id)
			JOIN PUBLIC.tbl_reihungstest ON (rt_id = tbl_reihungstest.reihungstest_id)
			JOIN testtool.tbl_ablauf ablauf ON (rt_person.studienplan_id = ablauf.studienplan_id)
			WHERE EXISTS (
					SELECT rt_person_d.rt_id
						,ablauf_d.gebiet_id
						,person_d.person_id
						,person_d.nachname
						,person_d.vorname
						,rt_person_d.studienplan_id
					FROM PUBLIC.tbl_person person_d
					JOIN PUBLIC.tbl_rt_person rt_person_d USING (person_id)
					JOIN PUBLIC.tbl_reihungstest ON (rt_id = tbl_reihungstest.reihungstest_id)
					JOIN testtool.tbl_ablauf ablauf_d ON (rt_person_d.studienplan_id = ablauf_d.studienplan_id)
					WHERE ablauf.gebiet_id = ablauf_d.gebiet_id
						AND person.person_id = person_d.person_id
						AND rt_person.rt_id <> rt_person_d.rt_id
					)
				AND studiensemester_kurzbz =".$db->db_add_param($studiensemester_kurzbz);
if ($studienplan_id != '')
	$qry .=	" AND rt_person.studienplan_id =".$db->db_add_param($studienplan_id);

$qry .= "ORDER BY nachname, vorname, person_id, rt_id";

//var_dump($gebiete_arr);
$person = 0;
$result_arr = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if ($person != $row->person_id)
			$i = 0;
		$reihungstest = new reihungstest();
		$reihungstest->load($row->rt_id);
		$gebietbezeichnungen = array();
		$qry_gebiete = "SELECT gebiet_id, reihung, bezeichnung FROM testtool.tbl_ablauf JOIN testtool.tbl_gebiet USING (gebiet_id) WHERE studienplan_id = ".$db->db_add_param($row->studienplan_id)." ORDER BY reihung";
		if($result_gebiete = $db->db_query($qry_gebiete))
		{
			while($row_gebiete = $db->db_fetch_object($result_gebiete))
			{
				$gebietbezeichnungen[$row_gebiete->gebiet_id] = $row_gebiete->bezeichnung;
			}
		}

		$result_arr[$row->person_id]['nachname'] = $row->nachname;
		$result_arr[$row->person_id]['vorname'] = $row->vorname;
		$result_arr[$row->person_id]['data'][$i] = $row;
		$result_arr[$row->person_id]['data'][$i]->reihungstest_id = $reihungstest->reihungstest_id;
		$result_arr[$row->person_id]['data'][$i]->anmerkung = $reihungstest->anmerkung;
		$result_arr[$row->person_id]['data'][$i]->datum = $reihungstest->datum;
		$result_arr[$row->person_id]['data'][$i]->uhrzeit = $reihungstest->uhrzeit;
		$result_arr[$row->person_id]['data'][$i]->studiengang_kz = $reihungstest->studiengang_kz;
		$result_arr[$row->person_id]['data'][$i]->ort_kurzbz = $reihungstest->ort_kurzbz;
		$result_arr[$row->person_id]['data'][$i]->gebiete_bezeichnung = $gebietbezeichnungen;
		$i++;
		$person = $row->person_id;
	}
}
//var_dump($result_arr);
$person_id = 0;
foreach ($result_arr as $key=>$value)
{
	$rt_array = array();
	foreach ($result_arr[$key]['data'] as $keyrow=>$row)
	{
		$rt_array[] = $row->rt_id;
	}
	$rt_array = array_unique($rt_array);
	if (count($rt_array)>1)
	{
		echo '<h3>'.$result_arr[$key]['nachname'].' '.$result_arr[$key]['vorname'].' ('.$key.')</h3>';
		echo '<form name="form_table" action="reihungstest_zusammenlegung.php?studiensemester_kurzbz='.$db->convert_html_chars($studiensemester_kurzbz).'" method="POST">';
		echo '<table id="t'.$key.'" class="tablesorter"><thead><tr>';
		echo '<th style="width: 1%; text-align: center">Behalten</th>';
		echo '<th style="width: 1%; text-align: center">Neu Zuteilen</th>';
		echo "<th>Reihungstest</th>";
		echo "<th>Stufe</th>";
		echo "<th>Gebiet</th>";
		echo "<th>Studienplan</th>";
		echo "<th>Teilgenommen</th>";
		echo "</tr></thead><tbody>";

		foreach ($result_arr[$key]['data'] as $keyrow=>$row)
		{
			$studienplan = new studienplan();
			$studienplan->loadStudienplan($row->studienplan_id);

			echo "<tr>";

			echo '<td style="text-align: center"><input type="radio" name="radio" value="'.$row->rt_person_id.'" onclick="disable(\''.$row->rt_person_id.'\',\''.$row->person_id.'\')" id="radio_'.$row->rt_person_id.'"></td>';
			echo '<td style="text-align: center"><input type="checkbox" class="checkbox_'.$row->person_id.'" name="checkbox[]" value="'.$row->rt_person_id.'" id="checkbox_'.$row->rt_person_id.'" disabled></td>';
			echo '<td title="'.$row->datum.'"><b>#'.$row->rt_id.'</b> - <i>'.$datum_obj->formatDatum($row->datum,'d.m.Y').', '.$datum_obj->formatDatum($row->uhrzeit,'H:i').'</i> - '.$studiengang->kuerzel_arr[$row->studiengang_kz].' '.$db->convert_html_chars($row->anmerkung).'</td>';
			echo "<td>$row->stufe</td>";
			echo '<td>';
					$i = 0;
					foreach ($row->gebiete_bezeichnung as $key=>$value)
					{
						echo ($i>0?', ':'');
						echo ($row->gebiet_id == $key?'<b>'.$value.'</b>':$value);
						$i++;
					}
			echo '</td>';
			echo '<td>'.$studienplan->bezeichnung.' ('.$row->studienplan_id.')</td>';;
			echo '<td>'.($row->teilgenommen=='t'?'Ja':'Nein').'</td>';
			echo "</tr>";
			$person_id = $row->person_id;
			$i++;
		}
		echo "</tbody></table>";
		echo '<input type="hidden" name="zusammenlegen" value="zusammenlegen">';
		echo '<input type="hidden" name="studienplan_id" value="'.$studienplan_id.'">';
		echo '<button type="submit">Markierte Anmeldungen neu zuteilen und Mail an BewerberIn verschicken</button>';
		echo "</form>";
	}
}

?>
</tr>
</table>
</body>
</html>
