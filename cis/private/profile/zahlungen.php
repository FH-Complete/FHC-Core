<?php
/* Copyright (C) 2006 fhcomplete.org
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

	require_once('../../../config/cis.config.inc.php');
	require_once('../../../config/global.config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/konto.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/phrasen.class.php');
	require_once('../../../include/benutzerberechtigung.class.php');

	$sprache = getSprache();
	$p = new phrasen($sprache);
	$uid=get_uid();

	if(isset($_GET['uid']))
	{
		// Administratoren duerfen die UID als Parameter uebergeben um die Zahlungen
		// von anderen Personen anzuzeigen

		$rechte = new benutzerberechtigung();
		$rechte->getBerechtigungen($uid);
		if($rechte->isBerechtigt('admin'))
		{
			$uid = $_GET['uid'];
			$getParam = "&uid=" . $uid;
		}
		else
			$getParam = "";
	}
	else
		$getParam='';

	if (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && !ZAHLUNGSBESTAETIGUNG_ANZEIGEN)
	{
		die('Um diese Seite anzuzeigen, ist ein entsprechender Eintrag in der Konfigurationsdatei nötig.');
	}

	$datum_obj = new datum();

	echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
			<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>'.$p->t('tools/zahlungen').'</title>
				<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
				<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>';

				include('../../../include/meta/jquery.php');
				include('../../../include/meta/jquery-tablesorter.php');

echo '			<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
				<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
				<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script>
			</head>
			<style>
				table.tablesorter
				{
					width: auto;
				}
				table.tablesorter tbody td
				{
					padding: 1px 3px;
				}
			</style>
			<script type="text/javascript">
				// Parser für Datum DD.MM.YYYY
				$.tablesorter.addParser({
					id: "customDate",
					is: function(s) {
						//return false;
						//use the above line if you don\'t want table sorter to auto detected this parser
						// match dd.mm.yyyy e.g. 01.01.2001 as regex
						return /\d{1,2}.\d{1,2}.\d{1,4}/.test(s);
					},
					// replace regex-wildcards and return new date
					format: function(s) {
						s = s.replace(/\-/g," ");
						s = s.replace(/:/g," ");
						s = s.replace(/\./g," ");
						s = s.split(" ");
						return $.tablesorter.formatFloat(new Date(s[2], s[1]-1, s[0]).getTime());
					},
					type: "numeric"
				});
				// Parser für Betrag
				$.tablesorter.addParser({
					id: "customCurrancy",
					is: function(s) {
						//return false;
						//use the above line if you don\'t want table sorter to auto detected this parser
						// match dd.mm.yyyy e.g. 01.01.2001 as regex
						//alert(/€\s\d*.*/.test(s))
						return /€\s\d*.*/.test(s);
					},
					// replace regex-wildcards and return new date
					format: function(s) {
						s = s.replace(/\-/g," ");
						s = s.replace(/:/g," ");
						s = s.replace(/\./g," ");
						s = s.split(" ");
						return $.tablesorter.formatFloat(s[1]);
					},
					type: "numeric"
				});
				// Parser für Studiensemester
				$.tablesorter.addParser({
					// set a unique id
					id: "studiensemester",
					is: function(s) {
						// return false so this parser is not auto detected
						return false;
					},
					format: function(s) {
						// format data for normalization
						var result = s.substr(2) + s.substr(0, 2);
						return result;
					},
					// set type, either numeric or text
					type: "text"
				});
				// For correct sorting of Umlauts
				$.tablesorter.characterEquivalents = {
					"a" : "\u00e1\u00e0\u00e2\u00e3\u00e4\u0105\u00e5", // áàâãäąå
					"A" : "\u00c1\u00c0\u00c2\u00c3\u00c4\u0104\u00c5", // ÁÀÂÃÄĄÅ
					"c" : "\u00e7\u0107\u010d", // çćč
					"C" : "\u00c7\u0106\u010c", // ÇĆČ
					"e" : "\u00e9\u00e8\u00ea\u00eb\u011b\u0119", // éèêëěę
					"E" : "\u00c9\u00c8\u00ca\u00cb\u011a\u0118", // ÉÈÊËĚĘ
					"i" : "\u00ed\u00ec\u0130\u00ee\u00ef\u0131", // íìİîïı
					"I" : "\u00cd\u00cc\u0130\u00ce\u00cf", // ÍÌİÎÏ
					"o" : "\u00f3\u00f2\u00f4\u00f5\u00f6\u014d", // óòôõöō
					"O" : "\u00d3\u00d2\u00d4\u00d5\u00d6\u014c", // ÓÒÔÕÖŌ
					"ss": "\u00df", // ß (s sharp)
					"SS": "\u1e9e", // ẞ (Capital sharp s)
					"u" : "\u00fa\u00f9\u00fb\u00fc\u016f", // úùûüů
					"U" : "\u00da\u00d9\u00db\u00dc\u016e" // ÚÙÛÜŮ
				  };
				$(document).ready(function()
				{
					$("#t1").tablesorter(
					{
						// Adding Function for sorting images by title
						textExtraction:function(s)
						{
							if($(s).find(\'img\').length == 0) return $(s).text();
							return $(s).find(\'img\').attr(\'title\');
						},
						sortList: [[0,1],[1,1]],
						widgets: ["zebra"],
						sortLocaleCompare : true,
						headers: { 0: { sorter: "customDate"}, 3: { sorter: "studiensemester"}, 5: { sorter: "customCurrancy"}, 6: { sorter: false}}
					});
				});
			</script>
			<body>';

	$studiengang = new studiengang();
	$studiengang->getAll(null,null);

	$stg_arr = array();
	foreach ($studiengang->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;

	$benutzer = new benutzer();
	if(!$benutzer->load($uid))
		die('Benutzer wurde nicht gefunden');

	echo '<h1>'.$p->t('tools/zahlungen').' - '.$benutzer->vorname.' '.$benutzer->nachname.'</h1>';

	$konto = new konto();
	$konto->getBuchungstyp();
	$buchungstyp = array();

	echo $p->t('tools/zahlungenHinweis');

	foreach ($konto->result as $row)
		$buchungstyp[$row->buchungstyp_kurzbz]=$row->beschreibung;

	$konto = new konto();
	$konto->getBuchungen($benutzer->person_id);
	if(count($konto->result)>0)
	{
		echo '<br><br><table class="tablesorter" id="t1"><thead>';
		echo '<tr>';
		echo '
			<th>'.$p->t('global/datum').'</th>
			<th>'.$p->t('tools/zahlungstyp').'</th>
			<th>'.$p->t('lvplan/stg').'</th>
			<th>'.$p->t('global/studiensemester').'</th>
			<th>'.$p->t('tools/buchungstext').'</th>
			<th>'.$p->t('tools/betrag').'</th>
			<th>'.$p->t('tools/zahlungsbestaetigung').'</th>';
		echo '</tr></thead><tbody>';

		foreach ($konto->result as $row)
		{
			$i=0;  //Zaehler fuer Anzahl Gegenbuchungen
			$count_studiengangszahlung = 0;
			$buchungsnummern='';

			// Für die FHTW sollen nur Zahlungsbestaetigungen von FHTW-Studien angezeigt werden. (Nicht von Lehrgaengen)
			if (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN_FUER_LEHRGAENGE') && !ZAHLUNGSBESTAETIGUNG_ANZEIGEN_FUER_LEHRGAENGE)
			{
				$is_lehrgang = $row['parent']->studiengang_kz < 0 ? true : false;
				if ($is_lehrgang) continue;
			}

			if(!isset($row['parent']))
				continue;
			$betrag = $row['parent']->betrag;
			$count_studiengangszahlung ++;

			if(isset($row['childs']))
			{
				foreach ($row['childs'] as $key => $row_child)
				{
					$betrag += $row_child->betrag;
					$betrag = round($betrag, 2);
					$buchungsnummern = !empty($buchungsnummern) ? ';' : '';
					$buchungsnummern .= $row['childs'][$key]->buchungsnr;
					$i = $key; //Zaehler auf letzten Gegenbuchungseintrag setzen
				}
			}
			else
				$buchungsnummern = $row['parent']->buchungsnr;

			if($betrag<0)
				$style='style="background-color: #FF8888;"';
			elseif($betrag>0)
				$style='style="background-color: #88DD88;"';
			else
				$style='';

			$buchungsdatum = $datum_obj->mktime_fromdate(isset($row['childs'][$i])?$row['childs'][$i]->buchungsdatum:$row['parent']->buchungsdatum);
			$aktdatum = time();
			// Zukünftige Zahlungen werden nicht angezeigt
			if ($buchungsdatum <= $aktdatum)
			{
				echo "<tr>";
				echo '<td '.$style.'>'.date('d.m.Y',$buchungsdatum).'</td>';
				echo '<td '.$style.'>'.$buchungstyp[$row['parent']->buchungstyp_kurzbz].'</td>';
				echo '<td '.$style.'>'.$stg_arr[$row['parent']->studiengang_kz].'</td>';
				echo '<td '.$style.'>'.$row['parent']->studiensemester_kurzbz.'</td>';

				echo '<td '.$style.'>'.$row['parent']->buchungstext.'</td>';
				echo '<td align="right" '.$style.'>€ '.($betrag<0?'-':($betrag>0?'+':'')).sprintf('%.2f',abs($row['parent']->betrag)).'</td>';
				echo '<td align="center" '.$style.'>';
				if($betrag>=0 && $row['parent']->betrag<=0)
				{
					echo '<a href="../pdfExport.php?xml=konto.rdf.php&xsl=Zahlung&uid='.$uid.'&buchungsnummern='.$buchungsnummern.'" title="'.$p->t('tools/bestaetigungDrucken').'"><img src="../../../skin/images/pdfpic.gif" alt="'.$p->t('tools/bestaetigungDrucken').'"></a>';
				}
				elseif($row['parent']->betrag>0)
				{
					//Auszahlung
				}
				else
				{
					echo '<a onclick="window.open(';
					echo "'zahlungen_details.php?buchungsnr=".$row['parent']->buchungsnr.$getParam."','Zahlungsdetails','height=500,width=550,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=no,toolbar=no,location=no,menubar=no,dependent=yes');return false;";
					echo '" href="#">'.$p->t('tools/offen').'</a>(€ '.sprintf('%.2f',$betrag*-1).')';

					echo '</td>';
				}
				echo '</tr>';
			}
		}

		// Wenn die Tabelle keine Eintraege hat, wird eine Tabellenzeile mit entsprechender Information angezeigt.
		if ($count_studiengangszahlung == 0)
		{
			echo "<tr><td colspan='7' style='background-color: white;'>" .$p->t('tools/keineZahlungenVorhanden'). "</td></tr>";
		}

		echo '</tbody></table>';
	}
	else
	{
		echo $p->t('tools/keineZahlungenVorhanden');
	}
	echo '</td></tr></table';

	echo '</body></html>';
?>
