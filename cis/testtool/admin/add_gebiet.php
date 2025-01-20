<?php
/* Copyright (C) 2009 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *			Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>
 */
/**
 * Seite zum Editieren von Testtool-Gebieten
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/gebiet.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/sprache.class.php');

if (!$user = get_uid())
	die('Sie sind nicht angemeldet. Es wurde keine Benutzer UID gefunden ! <a href="javascript:history.back()">Zur&uuml;ck</a>');

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

$sprache = new sprache();
$sprache->getAll(true, 'index');

$sprache_user = getSprache();

$db = new basis_db();

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link href="../../../skin/tablesort.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	<script type="text/javascript">
    $(document).ready(function()
    {
        $("#t1").tablesorter(
        {
            sortList: [[0,0]],
            widgets: ["zebra"]
        });
    });

	function deleteZuordnung(ablauf_id)
	{
		if (confirm("Wollen Sie dieses Zuordnung wirklich entfernen?"))
		{
			$("#data").html(\'<form action="edit_gebiet.php" name="sendform" id="sendform" method="POST"><input type="hidden" name="action" value="deleteZuordnung" /><input type="hidden" name="ablauf_id" value="\'+ablauf_id+\'" /></form>\');
			document.sendform.submit();
		}
		return false;
	}

	 </script>
</head>
<body>
<div id="data"></div>
';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'-1');
$gebiet = new gebiet();

echo '<h1>&nbsp;Gebiet hinzuf&uuml;gen</h1>';

if(!$rechte->isBerechtigt('basis/testtool'))
	die($rechte->errormsg);

$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);

echo '<a href="index.php?stg_kz='.$stg_kz.'" class="Item">Zurück zur Admin Seite</a><br /><br />';

//Dropdown Auswahl Studiengang
echo "Studiengang: <SELECT name='studiengang' id='studiengang' onchange='window.location.href=this.value'><OPTION value='-1'>-- Keine Auswahl --</OPTION>";
$i = 0;
$selected = '';
$result_count = count($studiengang->result);
for ($i = 0; $i < $result_count; $i++)
{
	if ($stg_kz == $studiengang->result[$i]->studiengang_kz)
		$selected = 'selected';
	echo "<OPTION value='".$_SERVER['PHP_SELF']."?stg_kz=".$studiengang->result[$i]->studiengang_kz."' ".$selected.">".strtoupper($studiengang->result[$i]->typ.$studiengang->result[$i]->kurzbz).' ('.$studiengang->result[$i]->bezeichnung.")</OPTION>";
	$selected = '';
}
echo "</SELECT><br /><br /><hr />";

echo '
<form action="'.$_SERVER['PHP_SELF'].'" method="POST">
 <table cellspacing="4">
	<tr>
		<td>Kurzbezeichnung</td>
		<td><input type="text" name="kurzbz" placeholder="Pflichtfeld" maxlength="10" value="'.(isset($_POST['kurzbz'])?$_POST['kurzbz']:'').'" required/></td>
	</tr>
	<tr>
		<td>Bezeichnung (intern)</td>
		<td><input type="text" name="bezeichnung_intern" maxlength="50" value="'.(isset($_POST['bezeichnung_intern'])?$_POST['bezeichnung_intern']:'').'"/></td>
	</tr>';

foreach ($sprache->result as $row)
{
	echo '	<tr>
				<td>Bezeichnung '.$row->bezeichnung_arr[$sprache_user].'</td>
				<td><input type="text" name="bezeichnung_mehrsprachig_'.$row->sprache.'" maxlength="255" value="'.(isset($_POST['bezeichnung_mehrsprachig_'.$row->sprache.''])?$_POST['bezeichnung_mehrsprachig_'.$row->sprache.'']:'').'"/></td>
			</tr>';
}
echo '
	<tr>
		<td>Beschreibung (intern)</td>
		<td><textarea rows="" cols="" name="beschreibung" style="font-size: 9pt">'.(isset($_POST['beschreibung'])?$_POST['beschreibung']:'').'</textarea></td>
	</tr>
	<tr>
		<td>Zeit</td>
		<td><input type="text" name="zeit" placeholder="Pflichtfeld" value="'.(isset($_POST['zeit'])?$_POST['zeit']:'').'" required/> hh:mm:ss</td>
	</tr>
	<tr>
		<td>Multiple Response</td>
		<td><input type="checkbox" name="multiple_respone" '.(isset($_POST['multiple_respone'])?'checked':'').'/></td>
	</tr>
	<tr>
		<td>Kategorien</td>
		<td><input type="checkbox" name="kategorien" '.(isset($_POST['kategorien'])?'checked':'').'/></td>
	</tr>
	<tr>
		<td>Zuf&auml;llige Fragereihenfolge</td>
		<td><input type="checkbox" name="zufaellige_fragereihenfolge" '.(isset($_POST['zufaellige_fragereihenfolge'])?'checked':'').'/></td>
	</tr>
	<tr>
		<td>Zuf&auml;llige Vorschlagreihenfolge</td>
		<td><input type="checkbox" name="zufaellige_vorschlagreihenfolge" '.(isset($_POST['zufaellige_vorschlagreihenfolge'])?'checked':'').'/></td>
	</tr>
	<tr>
		<td>Levelgleichverteilung</td>
		<td><input type="checkbox" name="levelgleichverteilung" '.(isset($_POST['levelgleichverteilung'])?'checked':'').'/></td>
	</tr>
	<tr>
		<td>Maximale Punkteanzahl</td>
		<td><input type="text" name="maximale_punkteanzahl" maxlength="5" value="'.(isset($_POST['maximale_punkteanzahl'])?$_POST['maximale_punkteanzahl']:'').'"/></td>
	</tr>
	<tr>
		<td>Maximale Frageanzahl</td>
		<td><input type="text" name="maximale_fragenanzahl" maxlength="5" value="'.(isset($_POST['maximale_fragenanzahl'])?$_POST['maximale_fragenanzahl']:'').'"/></td>
	</tr>
	<tr>
		<td>Antworten pro Zeile</td>
		<td><input type="text" name="antworten_pro_zeile" placeholder="Pflichtfeld" maxlength="2" value="'.(isset($_POST['antworten_pro_zeile'])?$_POST['antworten_pro_zeile']:'').'" required/></td>
	</tr>
	<tr>
		<td>Start Level</td>
		<td><input type="text" name="start_level" maxlength="5" value="'.(isset($_POST['start_level'])?$_POST['start_level']:'').'"/></td>
	</tr>
	<tr>
		<td>Richtige Fragen bis Levelaufstieg</td>
		<td><input type="text" name="richtige_fragen_bis_levelaufstieg" maxlength="5" value="'.(isset($_POST['richtige_fragen_bis_levelaufstieg'])?$_POST['richtige_fragen_bis_levelaufstieg']:'').'"/></td>
	</tr>
	<tr>
		<td>Falsche Fragen bis Levelabstieg</td>
		<td><input type="text" name="falsche_fragen_bis_levelabstieg" maxlength="5" value="'.(isset($_POST['falsche_fragen_bis_levelabstieg'])?$_POST['falsche_fragen_bis_levelabstieg']:'').'"/></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="submit" value="Speichern"/></td>
	</tr>
 </table>
 <input type="hidden" name="save" value="save"/>
</form>
';

//Speichern der Daten
if (isset($_POST['save']) && $_POST['save'] == 'save')
{
	if (!$rechte->isBerechtigt('basis/testtool', null, 'suid'))
		die($rechte->errormsg);

	if (isset($_POST['kurzbz']) && $_POST['kurzbz'] != '' && isset($_POST['zeit']) && $_POST['zeit'] != '' && isset($_POST['antworten_pro_zeile']) && $_POST['antworten_pro_zeile'] != '')
	{
		//Test, ob kurzbz schon vorhanden
		if ($result = $db->db_query('SELECT kurzbz FROM testtool.tbl_gebiet WHERE kurzbz = '.$db->db_add_param($_POST['kurzbz']).' LIMIT 1;'))
		{
			if ($db->db_num_rows($result) == 0)
			{
				$gebiet = new gebiet();

				$bezeichnung_mehrsprachig = array();
				foreach ($sprache->result as $row_sprache)
				{
					$bezeichnung_mehrsprachig[$row_sprache->sprache] = $_POST['bezeichnung_mehrsprachig_'.$row_sprache->sprache];
				}
				$gebiet->bezeichnung_mehrsprachig = $bezeichnung_mehrsprachig;

				$gebiet->kurzbz = $_POST['kurzbz'];
				$gebiet->bezeichnung = $_POST['bezeichnung_intern'];
				$gebiet->beschreibung = $_POST['beschreibung'];
				$gebiet->zeit = $_POST['zeit'];
				$gebiet->multipleresponse = isset($_POST['multiple_respone']);
				$gebiet->kategorien = isset($_POST['kategorien']);
				$gebiet->maxfragen = $_POST['maximale_fragenanzahl'];
				$gebiet->zufallfrage = isset($_POST['zufaellige_fragereihenfolge']);
				$gebiet->zufallvorschlag = isset($_POST['zufaellige_vorschlagreihenfolge']);
				$gebiet->levelgleichverteilung = isset($_POST['levelgleichverteilung']);
				$gebiet->maxpunkte = $_POST['maximale_punkteanzahl'];
				$gebiet->level_start = $_POST['start_level'];
				$gebiet->level_sprung_auf = $_POST['richtige_fragen_bis_levelaufstieg'];
				$gebiet->level_sprung_ab = $_POST['falsche_fragen_bis_levelabstieg'];
				$gebiet->insertamum = date('Y-m-d H:i:s');
				$gebiet->insertvon = $user;
				$gebiet->antwortenprozeile = $_POST['antworten_pro_zeile'];

				if ($gebiet->save(true))
				{
					echo 'Daten erfolgreich gespeichert';
				}
				else
				{
					echo '<span class="error">Fehler beim Speichern: '.$gebiet->errormsg.'</span>';
				}
			}
			else
				echo '<span class="error">Kurzbezeichnung ist schon vorhanden</span>';
		}
	}
	else
	{
		echo '<span class="error">Bitte f&uuml;llen Sie alle Pflichtfelder aus</span>';
	}
}

echo '</body></html>';
?>
