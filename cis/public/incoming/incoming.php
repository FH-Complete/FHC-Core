<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
require_once '../../../config/cis.config.inc.php';
require_once 'auth.php';
require_once '../../../include/mobilitaetsprogramm.class.php';
require_once '../../../include/person.class.php';
require_once '../../../include/functions.inc.php';
require_once '../../../include/phrasen.class.php';
require_once '../../../include/preincoming.class.php';
require_once '../../../include/nation.class.php';
require_once '../../../include/adresse.class.php';
require_once '../../../include/kontakt.class.php';
require_once '../../../include/studiensemester.class.php';
require_once '../../../include/studiengang.class.php';
require_once '../../../include/lehrveranstaltung.class.php';
require_once '../../../include/studiengang.class.php';
require_once '../../../include/akte.class.php';
require_once '../../../include/datum.class.php';
require_once '../../../include/firma.class.php';
require_once '../../../include/addon.class.php';

if(isset($_GET['lang']))
	setSprache($_GET['lang']);

$sprache = getSprache();
$p=new phrasen($sprache);

$method ="";
$breadcrumb = "";
if(isset($_GET['method']))
{
	$method = htmlspecialchars($_GET['method']);
	if($method == 'austauschprogram')
		$breadcrumb = "> ".$p->t('incoming/austauschprogram');
	elseif($method == 'profil')
		$breadcrumb = "> ".$p->t('incoming/profil');
	elseif($method == 'university')
		$breadcrumb = "> ".$p->t('incoming/universitaet');
	elseif($method == 'lehrveranstaltungen')
		$breadcrumb = "> ".$p->t('incoming/lehrveranstaltungen');
	elseif($method == 'files')
		$breadcrumb = "> ".$p->t('incoming/dateien');

}

$zugangscode = $_SESSION['incoming/user'];

$nation = new nation();
if($sprache == "German")
	$nation->getAll($ohnesperre = true);
else if($sprache == "English")
	$nation->getAll($ohnesperre = true, $orderEnglish= true);

$mobility = new mobilitaetsprogramm();
$mobility->getAll(true);

$person = new person();
$person->getPersonFromZugangscode($zugangscode);

$preincoming = new preincoming();
$preincoming->load($_SESSION['incoming/preincomingid']);

$adresse = new adresse();
$adresse->load_pers($preincoming->person_id);

$kontakt = new kontakt();
$kontakt->load_pers($preincoming->person_id);

$db = new basis_db();

$stsem = new studiensemester();
$stsem->getNextStudiensemester();

$stg = new studiengang();
$stg->getAll();

$date = new datum();

$firma = new firma();
$firma->getFirmen('Partneruniversität');
?>
<html>
	<head>
	<title>Incoming-Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="expires" content="Sat, 01 Dec 2001 00:00:00 GMT">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	<script type="text/javascript" src="../../../include/js/jquery.js"></script>
<?php
// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
    if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
        echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
<script>
$( document ).ready(function()
{
    if(typeof addon  !== \'undefined\')
    {
        for(i in addon)
        {
            addon[i].init("cis/public/incoming/incoming.php", {method:\''.$method.'\'});
        }
    }
});
</script>
';
?>
	<script type="text/javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
				{
					sortList: [[1,0],[3,0],[4,0]],
					widgets: ["zebra"]
				});
				$("#t2").tablesorter(
						{
							sortList: [[0,0]],
							widgets: ["zebra"]
						});
				$("#t3").tablesorter(
						{
							sortList: [[1,0],[3,0],[4,0],[5,0]],
							widgets: ["zebra"]
						});
			});
		</script>

	</head>
	<body>
		<table width="100%" border="0">
			<tr>
				<td align="left" width="33%"><a href="incoming.php">Administration </a> <?php echo $breadcrumb ?> </td>
				<td align="center" width="33%"><?php echo $person->titelpre." ".$person->vorname." ".$person->nachname." ".$person->titelpost?>
				<td align ="right" width="33%"><?php
				echo $p->t("global/sprache")." ";
				echo '<a href="'.$_SERVER['PHP_SELF'].'?lang=English">'.$p->t("global/englisch").'</a> |
				<a href="'.$_SERVER['PHP_SELF'].'?lang=German">'.$p->t("global/deutsch").'</a><br>';?></td>
			</tr>
		</table>
<?php
if($method =="austauschprogram")
{
	// Speichert Austauschprogram in preincoming tabelle
	if(isset($_POST['submit_program']))
	{
		$preincoming->von = $date->formatDatum($_REQUEST['von'],'Y-m-d');
		$preincoming->bis = $date->formatDatum($_REQUEST['bis'],'Y-m-d');
		$preincoming->code = $_REQUEST['code'];
		if($_REQUEST['austausch_kz']== "austausch_auswahl")
			$preincoming->mobilitaetsprogramm_code = '';
		else
			$preincoming->mobilitaetsprogramm_code = $_REQUEST['austausch_kz'];
		$preincoming->updateamum = date('Y-m-d H:i:s');

		if(!$preincoming->save())
			echo $preincoming->errormsg;
		else
			echo $p->t('global/erfolgreichgespeichert');
	}
	// Ausgabe Austauschprogram Formular
	echo '	<form method="POST" action="incoming.php?method=austauschprogram" name="AustauschForm">

				<table  border="0" align ="center" style=margin-top:5%;">
				<tr>
					<td>
					<fieldset>
					<table>
						<tr>
							<td>'.$p->t('incoming/austauschprgramwählen').'</td>
							<td><SELECT name="austausch_kz">
							<option value="austausch_auswahl">-- select --</option>';
							foreach ($mobility->result as $mob)
							{
									$selected="";
									if($mob->mobilitaetsprogramm_code == $preincoming->mobilitaetsprogramm_code)
										$selected = "selected";
									$anzeigetext="";
									if ($mob->kurzbz=='Austausch' && $sprache=='English')
										$anzeigetext = 'Exchange';
									elseif ($mob->kurzbz=='selbst')
										$anzeigetext = 'Freemover';
									else 
										$anzeigetext = $mob->kurzbz;
									echo '<option value="'.$mob->mobilitaetsprogramm_code.'" '.$selected.'>'.$anzeigetext."</option>\n";
							}
	echo '					</td>
						</tr>
						<tr>
							<td>'.$p->t('global/code').'* </td>
							<td><input type="text" name="code" size="40" maxlength="256" value="'.$preincoming->code.'"></td>
						</tr>
						<tr>
							<td>'.$p->t('incoming/studiertvon').' </td>
							<td><input type="text" name="von" size="10"  value="'.$date->formatDatum($preincoming->von,'d.m.Y').'"> (dd.mm.yyyy)</td>
						</tr>
						<tr>
							<td>'.$p->t('incoming/studiertbis').' </td>
							<td><input type="text" name="bis" size="10"  value="'.$date->formatDatum($preincoming->bis,'d.m.Y').'"> (dd.mm.yyyy)</td>
						</tr>
							<td>&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="2" align = "center"><input type="submit" name="submit_program" value="'.$p->t('global/speichern').'" onclick="return checkAustausch()"></td>
						</tr>
					</td>
					</tr>
					</table>
				<tr>
					<td><input type="button" value="'.$p->t('global/zurueck').'" onclick="document.location.href = \'incoming.php?method=university\'"; style="float:left"><input type="button" value="'.$p->t('incoming/weiter').'" onclick="document.location.href = \'incoming.php?method=lehrveranstaltungen\'"; style="float:right"></td>
				</tr>
				<tr>
					<td>* '.$p->t('incoming/wennVorhanden').'</td>
				</tr>

				</table>
			</form>
';
}
else if($method=="lehrveranstaltungen")
{
	if(isset($_GET['id']))
	{	// speichern der LV-ID
		if($_GET['mode']=="add")
		{
			$id= $_GET['id'];

			if($preincoming->addLehrveranstaltung($preincoming->preincoming_id, $_GET['id'], date('Y-m-d H:i:s')))
				echo $p->t('global/erfolgreichgespeichert');
			else
				echo $p->t('global/fehleraufgetreten');
		}
		// löschen der LV-ID
		if($_GET['mode'] == "delete")
		{
			$id= $_GET['id'];

			if($preincoming->deleteLehrveranstaltung($preincoming->preincoming_id, $_GET['id']))
				echo $p->t('global/erfolgreichgelöscht');
			else
				echo $p->t('global/fehleraufgetreten');
		}
	}
	if(isset($_GET['mode']) && $_GET['mode'] == "thesis")
	{
		switch($_GET['thesis'])
		{
			case 'bachelor':
				$preincoming->bachelorthesis=true;
				$preincoming->masterthesis=false;
				$preincoming->research_area=$_GET['research_area'];
				if(!$preincoming->save(false))
					echo $preincoming->errormsg;
				break;
			case 'master':
				$preincoming->bachelorthesis=false;
				$preincoming->masterthesis=true;
				$preincoming->research_area=$_GET['research_area'];
				if(!$preincoming->save(false))
					echo $preincoming->errormsg;
				break;
			case '':
				$preincoming->bachelorthesis=false;
				$preincoming->masterthesis=false;
				$preincoming->research_area='';
				if(!$preincoming->save(false))
					echo $preincoming->errormsg;
				break;
		}
	}
	if(isset($_GET['type']))
	{
		if(isset($_GET['mode']) && $_GET['mode']=='add')
		{
			if($_GET['type']=='deutschkurs1')
				$preincoming->deutschkurs1=true;
			elseif($_GET['type']=='deutschkurs2')
				$preincoming->deutschkurs2=true;
			elseif($_GET['type']=='deutschkurs3')
				$preincoming->deutschkurs3=true;
			if(!$preincoming->save(false))
				echo $preincoming->errormsg;
		}
		elseif(isset($_GET['mode']) && $_GET['mode']=='delete')
		{
			if($_GET['type']=='deutschkurs1')
				$preincoming->deutschkurs1=false;
			if($_GET['type']=='deutschkurs2')
				$preincoming->deutschkurs2=false;
			if($_GET['type']=='deutschkurs3')
				$preincoming->deutschkurs3=false;
			if(!$preincoming->save(false))
				echo $preincoming->errormsg;
		}
	}
	// Übersicht der eigenen LVs
	if(isset($_GET['view']))
	{
		if($_GET['view']=="own")
		{
			$lvs = $preincoming->getLehrveranstaltungen($preincoming->preincoming_id);
			echo '<br><br><br>
			<table border ="0" width="100%">
				<tr>
					<td width="25%"></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen">'.$p->t('incoming/übersichtlehrveranstaltungen').'</a></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen&view=own">'.$p->t('incoming/eigenelehrveranstaltungen').'</a></td>
					<td width="25%"></td>
				</tr>
				<tr><td>&nbsp;</td></tr>
			</table>';

			/* Wird laut Telefonat mit Giedre Jukneviciute am 14.10.2015 derzeit nicht benötigt und soll daher ausgeblendet werden.
			if($preincoming->deutschkurs1 || $preincoming->deutschkurs2 || $preincoming->deutschkurs3)
			{
				//Uebersicht Deutschkurse
				echo '<table width="90%" border="0" align="center" class="table-stripeclass:alternate table-autostripe">
						<thead align="center">
						<tr class="liste">
							<th width="6%"></th>
							<th>'.$p->t('incoming/deutschkurse').'</th>
						</tr>
						</thead>
						<tbody>';

				//Deutschkurs3
				if($preincoming->deutschkurs3)
				{
					echo '<tr>';
					echo '<td> <a href="incoming.php?method=lehrveranstaltungen&mode=delete&type=deutschkurs3&view=own">'.$p->t('global/löschen').'</a></td>';
					echo '<td>'.$p->t('incoming/deutschkurs3').'</td>';
					echo '</tr>';
				}

				//Deutschkurs1
				if($preincoming->deutschkurs1)
				{
					echo '<tr>';
					echo '<td> <a href="incoming.php?method=lehrveranstaltungen&mode=delete&type=deutschkurs1&view=own">'.$p->t('global/löschen').'</a></td>';
					echo '<td>'.$p->t('incoming/deutschkurs1').'</td>';
					echo '</tr>';
				}
				//Deutschkurs2
				if($preincoming->deutschkurs2)
				{
					echo '<tr>';
					echo '<td> <a href="incoming.php?method=lehrveranstaltungen&mode=delete&type=deutschkurs2&view=own">'.$p->t('global/löschen').'</a></td>';
					echo '<td>'.$p->t('incoming/deutschkurs2').'</td>';
					echo '</tr>';
				}

				echo '</tbody></table><br><br>';
			}*/


			echo '
				<table class="tablesorter" id="t1">
				<thead>
				<tr class="liste">
					<th></th>
					<th>'.$p->t('global/studiengang').'</th>
					<th>'.$p->t('abgabetool/typ').'</th>
					<th>'.$p->t('global/semester').'</th>
					<th>'.$p->t('global/lehrveranstaltung').'</th>
					<th>'.$p->t('global/lehrveranstaltung').' '.$p->t('global/englisch').'</th>
					<th>Info</th>
				</tr>
				</thead>
				<tbody>';
			foreach($lvs as $lv)
			{
				$lehrveranstaltung = new lehrveranstaltung();
				$lehrveranstaltung->load($lv);
				$studiengang = new studiengang();
				$studiengang->load($lehrveranstaltung->studiengang_kz);
				$studiengang_language = ($sprache == 'German') ? $studiengang->bezeichnung : $studiengang->english;
				$typ = $studiengang->typ;
				if ($studiengang->typ == 'b')
					$typ = 'BA';
				else if ($studiengang->typ == 'm')
					$typ = 'MA';
				echo '<tr>';
                echo '<td style="display:none">'.$lehrveranstaltung->lehrveranstaltung_id.'</td>';
				echo '<td> <a href="incoming.php?method=lehrveranstaltungen&mode=delete&id='.$lv.'&view=own">'.$p->t('global/löschen').'</a></td>';
				echo '<td>',$studiengang_language,'</td>';
				echo '<td>',$typ,'</td>';
				echo '<td>',$lehrveranstaltung->semester,'</td>';
				echo '<td>',$lehrveranstaltung->bezeichnung,'</td>';
				echo '<td>',$lehrveranstaltung->bezeichnung_english,'</td>';
				echo '<td>
					<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'lvinfo.php?lv='.$lehrveranstaltung->lehrveranstaltung_id.'&amp;language=de\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Deutsch&nbsp;</a>
					<a href="#Englisch" class="Item" onclick="javascript:window.open(\'lvinfo.php?lv='.$lehrveranstaltung->lehrveranstaltung_id.'&amp;language=en\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Englisch</a>
					</td>';
				echo '</tr>';
			}
		}
	}
	// Übersicht aller LVs
	else
	{
		echo '<br><br><br>
			<table border ="0" width="100%">
				<tr>
					<td width="25%" align="center"><input type="button" value="'.$p->t('global/zurueck').'" onclick="document.location.href = \'incoming.php?method=austauschprogram\'";></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen">'.$p->t('incoming/übersichtlehrveranstaltungen').'</a></td>
					<td width="25%" align="center"><a href="incoming.php?method=lehrveranstaltungen&view=own">'.$p->t('incoming/eigenelehrveranstaltungen').'</a></td>
					<td width="25%" align="center"><input type="button" value="'.$p->t('incoming/hauptmenue').'" onclick="document.location.href = \'incoming.php\'";></td>
				</tr>
			</table> <br><br>';

		/* Wird laut Telefonat mit Giedre Jukneviciute am 14.10.2015 derzeit nicht benötigt und soll daher ausgeblendet werden.
		//Uebersicht Deutschkurse
		echo '<table width="90%" border="0" align="center" class="tablesorter" id="t2">
				<thead align="center">
				<tr class="liste">
					<th colspan="2">'.$p->t('incoming/deutschkurse').'</th>
				</tr>
				</thead>
				<tbody>';

		//Deutschkurs3
		echo '<tr>';
		if(!$preincoming->deutschkurs3)
			echo '<td width="6%"><a href="incoming.php?method=lehrveranstaltungen&mode=add&type=deutschkurs3">'.$p->t('global/anmelden').'</a></td>';
		else
			echo '<td width="6%">'.$p->t('global/angemeldet').'</td>';
		echo '<td>'.$p->t('incoming/deutschkurs3').'</td>';
		echo '</tr>';
		//Deutschkurs1
		echo '<tr>';
		if(!$preincoming->deutschkurs1)
			echo '<td width="6%"><a href="incoming.php?method=lehrveranstaltungen&mode=add&type=deutschkurs1">'.$p->t('global/anmelden').'</a></td>';
		else
			echo '<td width="6%">'.$p->t('global/angemeldet').'</td>';
		echo '<td>'.$p->t('incoming/deutschkurs1').'</td>';
		echo '</tr>';
		//Deutschkurs2
		echo '<tr>';
		if(!$preincoming->deutschkurs2)
			echo '<td><a href="incoming.php?method=lehrveranstaltungen&mode=add&type=deutschkurs2">'.$p->t('global/anmelden').'</a></td>';
		else
			echo '<td>'.$p->t('global/angemeldet').'</td>';
		echo '<td>'.$p->t('incoming/deutschkurs2').'</td>';
		echo '</tr>';



		echo '</tbody></table><br><br>';*/


		/*echo '
		<table width="90%" border="0" align="center" class="table-autosort:1 table-stripeclass:alternate table-autostripe">
		<thead>
			<tr class="liste">
				<th>'.$p->t('incoming/thesis').'</th>
			</tr>
		</thead>
		<tbody>
			<tr valign="top">
				<td>
				<form action="incoming.php" method="GET">
				<input type="hidden" name="mode" value="thesis" />
				<input type="hidden" name="method" value="lehrveranstaltungen" />
				<table>
					<tr>
						<td width="50%">
						<input type="radio" name="thesis" value="" '.((!$preincoming->bachelorthesis && !$preincoming->masterthesis)?'checked="checked"':'').'>'.$p->t('incoming/nothesis').'<br>
						<input type="radio" name="thesis" value="bachelor" '.(($preincoming->bachelorthesis)?'checked="checked"':'').'>'.$p->t('incoming/bachelorthesis').'<br>
						<input type="radio" name="thesis" value="master" '.(($preincoming->masterthesis)?'checked="checked"':'').'>'.$p->t('incoming/masterthesis').'
						</td>
						<td valign="top" width ="15%" align="center">
						'.$p->t('incoming/researcharea').':
						</td>
						<td>
						<textarea name="research_area">'.$preincoming->research_area.'</textarea>
						</td>
						<td valign="bottom">
							<input type="submit" value="'.$p->t('global/speichern').'">
						</td>
					</tr>
				</table>
				</form>
			</tr>
		</tbody>
		</table>
		<br><br>
		';*/

		echo '<table width="90%" border="1" align="center">
			<tr style="text-align: center">
				<td style="padding: 20px; color: red"><b>Course application is currently disabled. Please contact the office to apply for courses.</b></td>
			</tr></table>';
		/*echo '
		<form name="filterSemester">
		<table width="90%" border="0" align="center">
			<tr>
				<td>'.$p->t('incoming/studentenImWS').'</td>
			</tr>
			<tr>
				<td>'.$p->t('incoming/studentenImSS').'</td>
			</tr>
			<tr>
				<td>'.$p->t('incoming/filter').':
					<SELECT name="filterLv" onchange=selectChange()>
					<option value="allSemester">'.$p->t('incoming/alleSemester').'</option>';

					// Vorauswahl der Übergebenen Filter
					$WSemesterSelected = '';
					$SSemesterSelected = '';

					if(isset($_GET['filter']))
						if($_GET['filter'] == 'WSemester')
							$WSemesterSelected ='selected';
						elseif($_GET['filter']=='SSemester')
							$SSemesterSelected='selected';

					echo '<option value="WSemester" '.$WSemesterSelected.'>'.$p->t('incoming/wintersemester').'</option>';

					echo '<option value="SSemester" '.$SSemesterSelected.'>'.$p->t('incoming/sommersemester').'</option>';

			echo'</SELECT><br>';
			echo $p->t('courseInformation/unterrichtssprache').':<SELECT name="filterUnterrichtssprache" onchange=selectChange()>
			<option value="">'.$p->t('incoming/alleSprachen').'</option>';

					// Vorauswahl der Übergebenen Filter
					$GermanSelected = '';
					$EnglishSelected = '';

					if(isset($_GET['unterrichtssprache']))
						if($_GET['unterrichtssprache'] == 'German')
							$GermanSelected ='selected';
						elseif($_GET['unterrichtssprache']=='English')
							$EnglishSelected='selected';

					echo '<option value="German" '.$GermanSelected.'>German</option>';

					echo '<option value="English" '.$EnglishSelected.'>English</option>';

			echo'</SELECT><br>';
			echo $p->t('global/studiengang').':<SELECT name="filterStudiengang" onchange=selectChange()>
			<option value="">'.$p->t('incoming/alleStudiengaenge').'</option>';

				// Vorauswahl der Übergebenen Filter

				$studiengang = new studiengang();
				$studiengang->getAll('typ,kurzbz', true);

				foreach ($studiengang->result as $row)
				{
					$selected = '';
					if(isset($_GET['studiengang']) && $_GET['studiengang'] == $row->studiengang_kz)
						$selected='selected';

					$studiengang_language = ($sprache == 'German') ? $row->bezeichnung : $row->english;
					echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.strtoupper($row->typ.$row->kurzbz).' - '.$studiengang_language.'</option>';
				}

			echo'</SELECT>';
			echo '</td>
			</tr>
		</table>

<script language="JavaScript">
	function selectChange()
	{
		filter = document.filterSemester.filterLv.options[document.filterSemester.filterLv.selectedIndex].value;
		filterSprache = document.filterSemester.filterUnterrichtssprache.options[document.filterSemester.filterUnterrichtssprache.selectedIndex].value;
		filterStudiengang = document.filterSemester.filterStudiengang.options[document.filterSemester.filterStudiengang.selectedIndex].value;
		url = [location.protocol, "//", location.host, location.pathname].join("");
		url = url+"?method=lehrveranstaltungen&filter="+filter+"&unterrichtssprache="+filterSprache+"&studiengang="+filterStudiengang;
		document.location=url;
	}
</script>

		</form>
		<br><br>';

		// Filter für Semester setzen
		$filterqry = '';

		if(isset($_GET['filter']))
			if($_GET['filter'] == "WSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (1,3,5)";
			elseif($_GET['filter'] == "SSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (2,4,6)";

		if(isset($_GET['unterrichtssprache']) && $_GET['unterrichtssprache']!='')
			$filterqry .= " AND tbl_lehrveranstaltung.sprache='".$_GET['unterrichtssprache']."'";


		//Uebersicht LVs
		/* Erklaerung der Datumszeitraeume ab Zeile 650:
		 *		|=============== Studiensemester ===============|
		 *	|--------------| 											Incoming beginnt vor SS-Beginn und endet VOR SS-Ende jedoch ueberwiegend innerhalb SS
		 *											|--------------| 	Incoming beginnt VOR SS-Ende und endet NACH SS-Ende, jedoch ueberwiegend innerhalb SS 
		 * 				|------------------------------| 				Incoming ist innerhalb oder GENAU SS da
		 *	|------------------------------------------------------|	Incoming ist VOR SS-Anfang und NACH SS-Ende da, jedoch ueberwiegend ueberlappend mit SS 
		 * ---------------------------------------------------------	Von und Bis ist NULL
		 * -------------------|											Von ist NULL und bis innerhalb SS
		 *									|-----------------------	Bis ist NULL und von innerhalb SS 
		 */
		
		/*$qry = "SELECT
					tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.ects,
					tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache,
					tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming, tbl_lehrveranstaltung.orgform_kurzbz,
					(
					Select count(*)
					FROM (
						SELECT
							person_id
						FROM
							campus.vw_student_lehrveranstaltung
						JOIN public.tbl_benutzer using(uid)
						JOIN public.tbl_student ON(uid=student_uid)
						JOIN public.tbl_prestudentstatus USING(prestudent_id)
						WHERE
							lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
							AND
							lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
							AND
							tbl_lehreinheit.studiensemester_kurzbz='$stsem->studiensemester_kurzbz')
							AND
							tbl_prestudentstatus.status_kurzbz='Incoming'
							AND tbl_prestudentstatus.studiensemester_kurzbz='$stsem->studiensemester_kurzbz'
						UNION
						SELECT
							person_id
						FROM
							public.tbl_preincoming_lehrveranstaltung
						JOIN public.tbl_preincoming using(preincoming_id)
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
						AND
						(
							(bis - '$stsem->start' > '$stsem->start' - von) OR
							('$stsem->start' <= von AND bis >= '$stsem->ende' AND '$stsem->ende' - von > bis - '$stsem->ende') OR
							(von >= '$stsem->start' AND bis <= '$stsem->ende') OR
							(von <= '$stsem->start' AND bis >= '$stsem->ende') OR
							(von IS NULL AND bis IS NULL) OR
							(von IS NULL AND bis <= '$stsem->ende' AND bis > '$stsem->start') OR
							(bis IS NULL AND von < '$stsem->ende' AND von >= '$stsem->start')
						)
						AND aktiv = true
						)a ) as anzahl 
					FROM
						lehre.tbl_lehrveranstaltung 
					JOIN 
						public.tbl_studiengang USING(studiengang_kz) 
					WHERE
						tbl_lehrveranstaltung.incoming>0 AND 
						tbl_lehrveranstaltung.aktiv AND 
						tbl_lehrveranstaltung.lehre AND 
						tbl_lehrveranstaltung.lehrveranstaltung_id IN (
							SELECT lehrveranstaltung_id FROM lehre.tbl_studienplan_lehrveranstaltung 
							JOIN lehre.tbl_studienplan USING (studienplan_id) 
							JOIN lehre.tbl_studienordnung USING (studienordnung_id) 
							WHERE tbl_studienordnung.status_kurzbz='approved' 
							AND tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id) AND 
						((tbl_lehrveranstaltung.studiengang_kz>0 AND tbl_lehrveranstaltung.studiengang_kz<10000) OR tbl_lehrveranstaltung.studiengang_kz=10006)";

					if (isset($_GET['studiengang']) && $_GET['studiengang'] !='')
						$qry .= "AND tbl_lehrveranstaltung.studiengang_kz=".$_GET['studiengang'];

					$qry .= "AND tbl_studiengang.aktiv ".$filterqry." order by studiengang_kz
					";

		echo '<table class="tablesorter" id="t3" width="90%" border="0" align="center">
				<thead align="center">
				<tr>
					<th width="6%"></th>
					<th>'.$p->t('global/studiengang').'</th>
					<th>'.$p->t('abgabetool/typ').'</th>
					<th>'.$p->t('incoming/orgform').'</th>
					<th>'.$p->t('global/semester').'</th>
					<th>'.$p->t('global/lehrveranstaltung').'</th>
					<th>'.$p->t('global/lehrveranstaltung').' '.$p->t('global/englisch').'</th>
					<th>'.$p->t('incoming/ects').'</th>
					<th>'.$p->t('courseInformation/unterrichtssprache').'</th>
					<th>Info</th>
					<th>'.$p->t('incoming/freieplätze').'</th>
				</tr>
				</thead>
				<tbody>';
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$freieplaetze = $row->incoming - $row->anzahl;
				//if($freieplaetze>0)
				//{
					$studiengang = new studiengang();
					$studiengang->load($row->studiengang_kz);
					$studiengang_language = ($sprache == 'German') ? $studiengang->bezeichnung : $studiengang->english;
					$typ = $studiengang->typ;
					if ($studiengang->typ == 'b')
						$typ = 'BA';
					else if ($studiengang->typ == 'm')
						$typ = 'MA';
					else
						$typ = '-';
					echo '<tr>';
                    echo '<td style="display:none">'.$row->lehrveranstaltung_id.'</td>';
					if(!$preincoming->checkLehrveranstaltung($preincoming->preincoming_id, $row->lehrveranstaltung_id) && $freieplaetze>0)
						echo '<td><a href="incoming.php?method=lehrveranstaltungen&mode=add&id='.$row->lehrveranstaltung_id.'">'.$p->t('global/anmelden').'</a></td>';
					elseif (!$preincoming->checkLehrveranstaltung($preincoming->preincoming_id, $row->lehrveranstaltung_id) && $freieplaetze==0)
						echo '<td>'.$p->t('incoming/noVacancies').'</td>';
					else
						echo '<td>'.$p->t('global/angemeldet').'</td>';
					echo '<td>',$studiengang_language,'</td>';
					echo '<td>',$typ,'</td>';
					echo '<td>',$row->orgform_kurzbz,'</td>';
					echo '<td>',$row->semester,'</td>';
					echo '<td>',$row->bezeichnung,'</td>';
					echo '<td>',$row->bezeichnung_english,'</td>';
					echo '<td>',$row->ects,'</td>';
					echo '<td>',$row->sprache,'</td>';
					echo '<td>
							<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'lvinfo.php?lv='.$row->lehrveranstaltung_id.'&amp;language=de\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Deutsch&nbsp;</a>
							<a href="#Englisch" class="Item" onclick="javascript:window.open(\'lvinfo.php?lv='.$row->lehrveranstaltung_id.'&amp;language=en\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Englisch</a>
						  </td>';
					echo '<td>',($freieplaetze<$row->incoming?'<strong>'.$freieplaetze.'/'.$row->incoming.'</strong>':$freieplaetze.'/'.$row->incoming),'</td>';
					echo '</tr>';
				//}
			}
		}
		echo '</tbody></table>';*/
	}
}
else if ($method == "university")
{
	// Wenn Coordinatoren gespeichert sind, gleich laden
	$depCoordinator = new person();
	if($preincoming->person_id_coordinator_dep != "")
		$depCoordinator->load($preincoming->person_id_coordinator_dep);

	$intCoordinator = new person();
	if($preincoming->person_id_coordinator_int != "")
		$intCoordinator->load($preincoming->person_id_coordinator_int);

	// Speichern des Formulares
	if(isset($_POST['submit_program']))
	{
		if(isset($_REQUEST['universitaet']))
		{
			// Textfeld speichern
			$preincoming->universitaet = $_REQUEST['universitaet'];
			$preincoming->updateamum = date('Y-m-d H:i:s');
		}
		if($_REQUEST['firma'] != 'firma_auswahl')
		{
			// Firma aus DropDownliste speichern
			$preincoming->firma_id = $_REQUEST['firma'];
			$preincoming->updateamum = date('Y-m-d H:i:s');
		}
		else
		{
			// Firma aus DropDownliste löschen
			$preincoming->firma_id = "";
			$preincoming->updateamum = date('Y-m-d H:i:s');
		}

			$preincoming->program_name = $_REQUEST['name_of_program'];
			$preincoming->jahre = $_REQUEST['jahre'];
			if(isset($_REQUEST['bachelor']))
				$preincoming->bachelor = true;
			else
				$preincoming->bachelor = false;
			if(isset($_REQUEST['master']))
				$preincoming->master = true;
			 else
		 		$preincoming->master = false;

		 	if(!$preincoming->save())
				echo $preincoming->errormsg;

		// Department Coordinator bearbeiten
		if($_REQUEST['dep_coordinator_id'] == "" && $_REQUEST['nachname_coordinator'] != "")
		{
			// Department Coordinator Person neu anlegen
			$depCoordinator->vorname = $_REQUEST['vorname_coordinator'];
			$depCoordinator->nachname = $_REQUEST['nachname_coordinator'];
			$depCoordinator->geschlecht = "u";
			$depCoordinator->new = true;
			$depCoordinator->aktiv = true;
			$depCoordinator->updateamum = date('Y-m-d H:i:s');
			$depCoordinator->insertamum = date('Y-m-d H:i:s');

			if(!$depCoordinator->save())
			{
				echo $depCoordinator->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}

			// in preincoming speichern
			$preincoming->person_id_coordinator_dep = $depCoordinator->person_id;
			$preincoming->updateamum = date('Y-m-d H:i:s');
			$preincoming->save();
		}
		else if ($_REQUEST['dep_coordinator_id'] != "" && $_REQUEST['nachname_coordinator'] == "" && $_REQUEST['vorname_coordinator'] == "")
		{
			// löscht die Department Coordinator Person
			$preincoming->person_id_coordinator_dep = "";
			if(!$preincoming->save())
					die($preincoming->errormsg);

			if(!$depCoordinator->delete($_REQUEST['dep_coordinator_id']))
			{
					echo $depCoordinator->errormsg;
					die($p->t('global/fehleraufgetreten'));
			}
		}
		else if($_REQUEST['dep_coordinator_id'] != "")
		{
			// Department Coordinator Person updaten
			$depCoordinator->load($_REQUEST['dep_coordinator_id']);
			$depCoordinator->vorname = $_REQUEST['vorname_coordinator'];
			$depCoordinator->nachname = $_REQUEST['nachname_coordinator'];
			$depCoordinator->updateamum = date('Y-m-d H:i:s');
			$depCoordinator->new = false;
			if(!$depCoordinator->save())
			{
				echo $depCoordinator->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}
		// Department Coordinator Kontakt
		$kontakt = new kontakt();
		// wenn textbox != "" hidden_id == ""
		if($_REQUEST['email_coordinator'] != "" && $_REQUEST['dep_coordinator_emailId']== "")
		{
			{
				// Email-Kontakt neu anlegen
				$kontakt->person_id = $depCoordinator->person_id;
				$kontakt->kontakttyp = "email";
				$kontakt->kontakt = $_REQUEST['email_coordinator'];
				$kontakt->new = true;

				if(!$kontakt->save())
				{
					echo $kontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['email_coordinator'] == "" && $_REQUEST['dep_coordinator_emailId']!= ""))
		{
			// lösche Email-Kontakt
			if(!$kontakt->delete($_REQUEST['dep_coordinator_emailId']))
			{
				die($kontakt->errormsg);
			}
		}
		else if($_REQUEST['dep_coordinator_emailId']!= "")
		{
			// Update Email-Kontakt
			$kontakt->person_id = $depCoordinator->person_id;
			$kontakt->kontakttyp = "email";
			$kontakt->kontakt = $_REQUEST['email_coordinator'];
			$kontakt->kontakt_id = $_REQUEST['dep_coordinator_emailId'];
			$kontakt->new = false;

			if(!$kontakt->save())
			{
				echo $kontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}
		// wenn textbox und hidden id == "" dann tu nichts
		if($_REQUEST['fax_coordinator'] != "" && $_REQUEST['dep_coordinator_faxId']== "")
		{
			// Neu anlegen
			$kontakt->person_id = $depCoordinator->person_id;
			$kontakt->kontakttyp = "fax";
			$kontakt->kontakt = $_REQUEST['fax_coordinator'];
			$kontakt->new = true;

			if(!$kontakt->save())
			{
				echo $kontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}
		// wenn id vorhanden und Textbox == "" löschen
		else if(($_REQUEST['fax_coordinator'] == "" && $_REQUEST['dep_coordinator_faxId']!= ""))
		{
			// lösche Kontakt
			if(!$kontakt->delete($_REQUEST['dep_coordinator_faxId']))
				die("$kontakt->errormsg");
		}
		else if($_REQUEST['dep_coordinator_faxId']!= "")
		{
			// Update
			$kontakt->person_id = $depCoordinator->person_id;
			$kontakt->kontakttyp = "fax";
			$kontakt->kontakt = $_REQUEST['fax_coordinator'];
			$kontakt->kontakt_id = $_REQUEST['dep_coordinator_faxId'];
			$kontakt->new = false;

			if(!$kontakt->save())
			{
				echo $kontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		if($_REQUEST['telefon_coordinator'] != "" && $_REQUEST['dep_coordinator_telefonId']== "")
		{
				// Neu anlegen
				$kontakt->person_id = $depCoordinator->person_id;
				$kontakt->kontakttyp = "telefon";
				$kontakt->kontakt = $_REQUEST['telefon_coordinator'];
				$kontakt->new = true;

				if(!$kontakt->save())
				{
					echo $kontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
		}
		else if(($_REQUEST['telefon_coordinator'] == "" && $_REQUEST['dep_coordinator_telefonId']!= ""))
		{
			// lösche Kontakt
			if(!$kontakt->delete($_REQUEST['dep_coordinator_telefonId']))
			{
				die("$kontakt->errormsg");
			}
		}else if($_REQUEST['dep_coordinator_telefonId']!= "")
		{
			// Update
			$kontakt->person_id = $depCoordinator->person_id;
			$kontakt->kontakttyp = "telefon";
			$kontakt->kontakt = $_REQUEST['telefon_coordinator'];
			$kontakt->kontakt_id = $_REQUEST['dep_coordinator_telefonId'];
			$kontakt->new = false;

			if(!$kontakt->save())
			{
				echo $kontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		// International Coordinator bearbeiten
		if($_REQUEST['int_coordinator_id'] == "" && $_REQUEST['nachname_intcoordinator'] != "")
		{
			// Department Coordinator Person
			$intCoordinator->vorname = $_REQUEST['vorname_intcoordinator'];
			$intCoordinator->nachname = $_REQUEST['nachname_intcoordinator'];
			$intCoordinator->geschlecht = "u";
			$intCoordinator->new = true;
			$intCoordinator->aktiv = true;

			if(!$intCoordinator->save())
			{
				echo $intCoordinator->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}

			// in preincoming speichern
			$preincoming->person_id_coordinator_int = $intCoordinator->person_id;
			$preincoming->save();
		}
		else if ($_REQUEST['int_coordinator_id'] != "" && $_REQUEST['nachname_intcoordinator'] == "" && $_REQUEST['vorname_intcoordinator'] == "")
		{
			// löscht die Department Coordinator Person
			$preincoming->person_id_coordinator_int = "";
			if(!$preincoming->save())
					echo $preincoming->errormsg;
			if(!$intCoordinator->delete($_REQUEST['int_coordinator_id']))
			{
					echo $intCoordinator->errormsg;
					die($p->t('global/fehleraufgetreten'));
			}

		}
		else if($_REQUEST['int_coordinator_id'] != "")
		{
			// Person updaten
			$intCoordinator->load($_REQUEST['int_coordinator_id']);
			$intCoordinator->vorname = $_REQUEST['vorname_intcoordinator'];
			$intCoordinator->nachname = $_REQUEST['nachname_intcoordinator'];
			$intCoordinator->new = false;
			if(!$intCoordinator->save())
			{
				echo $intCoordinator->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		$intkontakt = new kontakt();
		// wenn textbox != "" hidden_id == ""
		if($_REQUEST['email_intcoordinator'] != "" && $_REQUEST['int_coordinator_emailId']== "")
		{
			{
				// Neu anlegen
				$intkontakt->person_id = $intCoordinator->person_id;
				$intkontakt->kontakttyp = "email";
				$intkontakt->kontakt = $_REQUEST['email_intcoordinator'];
				$intkontakt->new = true;

				if(!$intkontakt->save())
				{
					echo $intkontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['email_intcoordinator'] == "" && $_REQUEST['int_coordinator_emailId']!= ""))
		{
			// lösche Kontakt
			if(!$intkontakt->delete($_REQUEST['int_coordinator_emailId']))
			{
				die("$intkontakt->errormsg");
			}
		}
		else if($_REQUEST['int_coordinator_emailId']!= "")
		{
			// Update
			$intkontakt->person_id = $intCoordinator->person_id;
			$intkontakt->kontakttyp = "email";
			$intkontakt->kontakt = $_REQUEST['email_intcoordinator'];
			$intkontakt->kontakt_id = $_REQUEST['int_coordinator_emailId'];
			$intkontakt->new = false;

			if(!$intkontakt->save())
			{
				echo $intkontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		if($_REQUEST['telefon_intcoordinator'] != "" && $_REQUEST['int_coordinator_telefonId']== "")
		{
			{
				// Neu anlegen
				$intkontakt->person_id = $intCoordinator->person_id;
				$intkontakt->kontakttyp = "telefon";
				$intkontakt->kontakt = $_REQUEST['telefon_intcoordinator'];
				$intkontakt->new = true;

				if(!$intkontakt->save())
				{
					echo $intkontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['telefon_intcoordinator'] == "" && $_REQUEST['int_coordinator_telefonId']!= ""))
		{
			// lösche Kontakt
			if(!$intkontakt->delete($_REQUEST['int_coordinator_telefonId']))
			{
				die("$intkontakt->errormsg");
			}
		}
		else if($_REQUEST['int_coordinator_telefonId']!= "")
		{
			// Update
			$intkontakt->person_id = $intCoordinator->person_id;
			$intkontakt->kontakttyp = "telefon";
			$intkontakt->kontakt = $_REQUEST['telefon_intcoordinator'];
			$intkontakt->kontakt_id = $_REQUEST['int_coordinator_telefonId'];
			$intkontakt->new = false;

			if(!$intkontakt->save())
			{
				echo $intkontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		if($_REQUEST['fax_intcoordinator'] != "" && $_REQUEST['int_coordinator_faxId']== "")
		{
			{
				// Neu anlegen
				$intkontakt->person_id = $intCoordinator->person_id;
				$intkontakt->kontakttyp = "fax";
				$intkontakt->kontakt = $_REQUEST['fax_intcoordinator'];
				$intkontakt->new = true;

				if(!$intkontakt->save())
				{
					echo $intkontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['fax_intcoordinator'] == "" && $_REQUEST['int_coordinator_faxId']!= ""))
		{
			// lösche Kontakt
			if(!$intkontakt->delete($_REQUEST['int_coordinator_faxId']))
			{
				die("$intkontakt->errormsg");
			}
		}
		else if($_REQUEST['int_coordinator_faxId']!= "")
		{
			// Update
			$intkontakt->person_id = $intCoordinator->person_id;
			$intkontakt->kontakttyp = "fax";
			$intkontakt->kontakt = $_REQUEST['fax_intcoordinator'];
			$intkontakt->kontakt_id = $_REQUEST['int_coordinator_faxId'];
			$intkontakt->new = false;

			if(!$intkontakt->save())
			{
				echo $intkontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		echo $p->t('global/erfolgreichgespeichert');
	}

	// Department Coordinator Kontakt
	$depCoordinatorKontakt = new kontakt();
	$depCoordinatorKontakt->load_pers($preincoming->person_id_coordinator_dep);
	$depTelefon = "";
	$depTelefonId = "";
	$depFax = "";
	$depFaxId = "";
	$depEmail = "";
	$depEmailId = "";
	foreach ($depCoordinatorKontakt->result as $depKontakt)
	{
		if($depKontakt->kontakttyp == "telefon")
		{
			$depTelefon = $depKontakt->kontakt;
			$depTelefonId = $depKontakt->kontakt_id;
		}
		if($depKontakt->kontakttyp == "fax")
		{
			$depFax = $depKontakt->kontakt;
			$depFaxId = $depKontakt->kontakt_id;
		}
		if($depKontakt->kontakttyp == "email")
		{
			$depEmail = $depKontakt->kontakt;
			$depEmailId = $depKontakt->kontakt_id;
		}
	}

	// International Coordinator Kontakt
	$intCoordinatorKontakt = new kontakt();
	$intCoordinatorKontakt->load_pers($intCoordinator->person_id);
	$intTelefon = "";
	$intTelefonId = "";
	$intFax = "";
	$intFaxId = "";
	$intEmail = "";
	$intEmailId = "";
	foreach ($intCoordinatorKontakt->result as $intKontakt)
	{
		if($intKontakt->kontakttyp == "telefon")
		{
			$intTelefon = $intKontakt->kontakt;
			$intTelefonId = $intKontakt->kontakt_id;
		}
		if($intKontakt->kontakttyp == "fax")
		{
			$intFax = $intKontakt->kontakt;
			$intFaxId = $intKontakt->kontakt_id;
		}
		if($intKontakt->kontakttyp == "email")
		{
			$intEmail = $intKontakt->kontakt;
			$intEmailId = $intKontakt->kontakt_id;
		}
	}

	// Wenn die Person gerade gelöscht wurde zeige sie nicht mehr an
	if($preincoming->person_id_coordinator_dep == "")
	{
		$depCoordinator->vorname = "";
		$depCoordinator->nachname ="";
	}
	if($preincoming->person_id_coordinator_int == "")
	{
		$intCoordinator->vorname = "";
		$intCoordinator->nachname = "";
	}

	echo '	<form method="POST" action="incoming.php?method=university" name="UniversityForm">
				<table border ="0" style="margin-top:5%;" align="center" >
				<tr><td>
					<fieldset>
					<table border ="0">
					<tr><td colspan="2"><b>'.$p->t('incoming/heimatuniversitaet').'</b></td></tr>
					<tr>
						<td>'.$p->t('incoming/universitätsname').' </td>
						<td colspan="3"><SELECT name="firma">
						<option value="firma_auswahl">-- other --</option>';
						foreach ($firma->result as $firm)
						{
							$selected = '';
							if($firm->firma_id == $preincoming->firma_id)
								$selected = 'selected';
							echo "<option value='$firm->firma_id' $selected>$firm->name</option>";
						}
echo '					</td>

					</tr>
					<tr >
						<td>'.$p->t('incoming/universitätsnameerweitert').'</td>
						<td colspan="2"><input type="text" name="universitaet" size="40" maxlength="256" value="'.$preincoming->universitaet.'"></td>
					</tr>
					<tr>
						<td>'.$p->t('incoming/studienrichtung').'</td>
						<td colspan="2"><input type="text" name="name_of_program" size=60 value="'.$preincoming->program_name.'"></td>
					</tr>
					<tr>';
			$checked = '';
			if($preincoming->bachelor == true)
				$checked = 'checked';
echo '					<td>'.$p->t('incoming/bachelorstudiengang').'</td>
						<td><input type="checkbox" name="bachelor" '.$checked.'></td>
					</tr>
					<tr>';
			$checked = '';
			if($preincoming->master == true)
				$checked = 'checked';
echo'					<td>'.$p->t('incoming/masterstudiengang').'</td>
						<td><input type="checkbox" name="master" '.$checked.'></td>
					</tr>
					<tr>
						<td>'.$p->t('incoming/jahrestudiert').'</td>
						<td><input type="text" name="jahre" size="2" value="'.$preincoming->jahre.'"></td>
					</tr>
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>

					<tr>
						<td colspan="2"><b>Department Coordinator</b></td>
						<td colspan="2"><b>International Coordinator</b></td></tr>
					<tr>
						<td width="25%">'.$p->t('global/vorname').' </td>
						<td width="25%"><input type="text" name="vorname_coordinator" size="20" maxlength="256" value="'.$depCoordinator->vorname.'">
						<input type="hidden" name = "dep_coordinator_id" id="dep_coordinator_id" value="'.$preincoming->person_id_coordinator_dep.'"></td>
						<td width="25%">'.$p->t('global/vorname').' </td>
						<td width="25%"><input type="text" name="vorname_intcoordinator" size="20" maxlength="256" value="'.$intCoordinator->vorname.'">
						<input type="hidden" name = "int_coordinator_id" id="int_coordinator_id" value="'.$preincoming->person_id_coordinator_int.'"></td>
					</tr>
					<tr>
						<td width="25%">'.$p->t('global/nachname').' </td>
						<td width="25%"><input type="text" name="nachname_coordinator" size="20"  value="'.$depCoordinator->nachname.'"></td>
						<td width="25%">'.$p->t('global/nachname').' </td>
						<td width="25%"><input type="text" name="nachname_intcoordinator" size="20"  value="'.$intCoordinator->nachname.'"></td>
					</tr>
					<tr>
						<td>'.$p->t('global/telefon').' </td>
						<td><input type="text" name="telefon_coordinator" size="20"  value="'.$depTelefon.'">
						<input type="hidden" name = "dep_coordinator_telefonId" id="dep_coordinator_telefonId" value="'.$depTelefonId.'"></td>
						<td>'.$p->t('global/telefon').' </td>
						<td><input type="text" name="telefon_intcoordinator" size="20"  value="'.$intTelefon.'">
						<input type="hidden" name = "int_coordinator_telefonId" id="int_coordinator_telefonId" value="'.$intTelefonId.'"></td>
					</tr>
					<tr>
						<td>'.$p->t('global/fax').' </td>
						<td><input type="text" name="fax_coordinator" size="20"  value="'.$depFax.'">
						<input type="hidden" name = "dep_coordinator_faxId" id="dep_coordinator_faxId" value="'.$depFaxId.'"></td>
						<td>'.$p->t('global/fax').' </td>
						<td><input type="text" name="fax_intcoordinator" size="20"  value="'.$intFax.'">
						<input type="hidden" name = "int_coordinator_faxId" id="int_coordinator_faxId" value="'.$intFaxId.'"></td>
					</tr>
					<tr>
						<td>E-Mail </td>
						<td><input type="text" name="email_coordinator" size="20"  value="'.$depEmail.'">
						<input type="hidden" name = "dep_coordinator_emailId" id="dep_coordinator_emailId" value="'.$depEmailId.'"></td>
						<td>E-Mail </td>
						<td colspan="3"><input type="text" name="email_intcoordinator" size="20"  value="'.$intEmail.'">
						<input type="hidden" name = "int_coordinator_emailId" id="int_coordinator_emailId" value="'.$intEmailId.'"></td>
					</tr>
					</tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td colspan="4" align = "center"><input type="submit" name="submit_program" value="'.$p->t('global/speichern').'" onclick="return checkUniversity()"></td>
					</tr>
					</td>
					</tr>
					</table>
					<tr>
						<td><input type="button" value="'.$p->t('global/zurueck').'" onclick="document.location.href = \'incoming.php?method=profil\'"; style="float:left"><input type="button" value="'.$p->t('incoming/weiter').'" onclick="document.location.href = \'incoming.php?method=austauschprogram\'"; style="float:right"></td>
					</tr>
						</table>
			</form>

		<script type="text/javascript">
		function checkUniversity()
		{
			if(document.AustauschForm.austausch_kz.options[0].selected == true)
			{
				alert("Kein Austauschprogram ausgewählt.");
				return false;
			}
			return true;
		}
		</script>';
}
// Benutzerprofil bearbeiten
else if ($method == "profil")
{
	// Profil speichern
	if(isset($_POST['submit_profil']))
	{
		$save = true;
		$emergencyPerson = new person();

        if($_REQUEST['emergency_name_id'] == "" && $_REQUEST['emergency_nachname'] != "")
		{
			// Emergency Person
			$emergencyPerson->vorname = $_REQUEST['emergency_vorname'];
			$emergencyPerson->nachname = $_REQUEST['emergency_nachname'];
			$emergencyPerson->geschlecht = "u";
			$emergencyPerson->new = true;
			$emergencyPerson->aktiv = true;
			$emergencyPerson->updateamum = date('Y-m-d H:i:s');
			$emergencyPerson->insertamum = date('Y-m-d H:i:s');

			if(!$emergencyPerson->save())
			{
				echo $emergencyPerson->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}

			// in preincoming speichern
			$preincoming->person_id_emergency = $emergencyPerson->person_id;
			$preincoming->updateamum = date('Y-m-d H:i:s');
		}
		else if ($_REQUEST['emergency_name_id'] != "" && $_REQUEST['emergency_nachname'] == "" && $_REQUEST['emergency_vorname'] == "")
		{
			// löscht die Person
			$preincoming->person_id_emergency = "";
			if(!$preincoming->save())
				die($p->t('global/fehleraufgetreten'));

			if(!$emergencyPerson->delete($_REQUEST['emergency_name_id']))
			{
				echo $emergencyPerson->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}
		else if($_REQUEST['emergency_name_id'] != "")
		{
			// Person updaten
			$emergencyPerson->load($_REQUEST['emergency_name_id']);
			$emergencyPerson->vorname = $_REQUEST['emergency_vorname'];
			$emergencyPerson->nachname = $_REQUEST['emergency_nachname'];
			$emergencyPerson->updateamum = date('Y-m-d H:i:s');
			$emergencyPerson->new = false;
			if(!$emergencyPerson->save())
			{
				echo $emergencyPerson->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}
		$emkontakt = new kontakt();
		if($_REQUEST['emergency_email'] != "" && $_REQUEST['emergency_emailId']== "")
		{
			{
				// Neu anlegen
				$emkontakt->person_id = $emergencyPerson->person_id;
				$emkontakt->kontakttyp = "email";
				$emkontakt->kontakt = $_REQUEST['emergency_email'];
				$emkontakt->new = true;

				if(!$emkontakt->save())
				{
					echo $emkontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['emergency_email'] == "" && $_REQUEST['emergency_emailId']!= ""))
		{
			// lösche Kontakt
			if(!$emkontakt->delete($_REQUEST['emergency_emailId']))
			{
				die("$emkontakt->errormsg");
			}
		}
		else if($_REQUEST['emergency_emailId']!= "")
		{
			// Update
			$emkontakt->person_id = $_REQUEST['emergency_name_id'];
			$emkontakt->kontakttyp = "email";
			$emkontakt->kontakt = $_REQUEST['emergency_email'];
			$emkontakt->kontakt_id = $_REQUEST['emergency_emailId'];
			$emkontakt->new = false;

			if(!$emkontakt->save())
			{
				echo $emkontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		if($_REQUEST['emergency_telefon'] != "" && $_REQUEST['emergency_telefonId']== "")
		{
			{
				// Neu anlegen
				$emkontakt->person_id = $emergencyPerson->person_id;
				$emkontakt->kontakttyp = "telefon";
				$emkontakt->kontakt = $_REQUEST['emergency_telefon'];
				$emkontakt->new = true;

				if(!$emkontakt->save())
				{
					echo $emkontakt->errormsg;
					die($p->t('global/fehleraufgetreten'));
				}
			}
		}
		else if(($_REQUEST['emergency_telefon'] == "" && $_REQUEST['emergency_telefonId']!= ""))
		{
			// lösche Kontakt
			if(!$emkontakt->delete($_REQUEST['emergency_telefonId']))
			{
				die("$emkontakt->errormsg");
			}
		}
		else if($_REQUEST['emergency_telefonId']!= "")
		{
			// Update
			$emkontakt->person_id = $_REQUEST['emergency_name_id'];
			$emkontakt->kontakttyp = "telefon";
			$emkontakt->kontakt = $_REQUEST['emergency_telefon'];
			$emkontakt->kontakt_id = $_REQUEST['emergency_telefonId'];
			$emkontakt->new = false;

			if(!$emkontakt->save())
			{
				echo $emkontakt->errormsg;
				die($p->t('global/fehleraufgetreten'));
			}
		}

		$person->titelpost = $_REQUEST['titel_post'];
		$person->vorname = $_REQUEST['vorname'];
		$person->nachname = $_REQUEST['nachname'];
		$person->titelpre = $_REQUEST['titel_pre'];
		$person->gebdatum = $date->formatDatum($_REQUEST['geb_datum'],'Y-m-d');
		$person->staatsbuergerschaft = $_REQUEST['staatsbuerger'];
		$person->geschlecht = $_REQUEST['geschlecht'];
		$person->aktiv = true;
		$person->new = false;
		if(!$person->save())
		{
			echo $person->errormsg;
			$save = false;
		}

		$adresse->result[0]->strasse = $_REQUEST['strasse'];
		$adresse->result[0]->plz = $_REQUEST['plz'];
		$adresse->result[0]->ort = $_REQUEST['ort'];
		$adresse->result[0]->nation = $_REQUEST['nation'];
		$adresse->result[0]->heimatadresse = true;
		$adresse->result[0]->zustelladresse = true;
		$adresse->result[0]->new = false;
		if(!$adresse->result[0]->save())
		{
			echo $adresse->errormsg;
			$save = false;
		}
		foreach($kontakt->result as $kon)
		{
			if($kon->kontakttyp=="email")
			{
				$kon->kontakt = $_REQUEST['email'];
				$kontakt->new = false;
				if(!$kon->save())
				{
					echo $p->t('global/fehleraufgetreten');
					$save = false;
				}
			}
		}

		$preincoming->zgv = $_REQUEST['zgv'];
		$preincoming->zgv_name = $_REQUEST['zgv_name'];
		$preincoming->zgv_ort = $_REQUEST['zgv_ort'];
		$preincoming->anmerkung = $_REQUEST['anmerkung'];
		$preincoming->zgv_datum = $date->formatDatum($_REQUEST['zgv_datum'],'Y-m-d');
		$preincoming->zgvmaster = $_REQUEST['zgv_master'];
		$preincoming->zgvmaster_datum = $date->formatDatum($_REQUEST['zgv_master_datum'],'Y-m-d');
		$preincoming->zgvmaster_ort = $_REQUEST['zgv_master_ort'];
		$preincoming->zgvmaster_name = $_REQUEST['zgv_master_name'];
		if(!$preincoming->save())
			$save = false;

		if($save)
			echo $p->t('global/erfolgreichgespeichert');
	}

	$personEmergency = new person();
	$personEmergencyKontakt = new kontakt();
	$emTelefon = "";
	$emTelefonId = "";
	$emEmail = "";
	$emEmailId = "";

	if($preincoming->person_id_emergency != "")
	{
		$personEmergency->load($preincoming->person_id_emergency);
		$personEmergencyKontakt->load_pers($preincoming->person_id_emergency);

		foreach ($personEmergencyKontakt->result as $emKontakt)
		{
			if($emKontakt->kontakttyp == "telefon")
			{
				$emTelefon = $emKontakt->kontakt;
				$emTelefonId = $emKontakt->kontakt_id;
			}
			if($emKontakt->kontakttyp == "email")
			{
				$emEmail = $emKontakt->kontakt;
				$emEmailId = $emKontakt->kontakt_id;
			}
		}
	}
	// Ausgabe Profil Formular
	echo'<form action="incoming.php?method=profil" method="POST" name="ProfilForm">
	<table align="center" style="margin-top:5%;" >
	<tr valign="top" ><td>
		<fieldset>
		<table>
		<tr><td>
			<tr>
				<td rowspan="4"><img id="personimage" src="../../public/bild.php?src=person&person_id='.$preincoming->person_id.'" alt="'.$preincoming->person_id.'" height="100px" width="75px"></td>';

          echo "<td><a href='#BildUpload' onclick='window.open(\"../bildupload.php?person_id=$person->person_id\",\"BildUpload\", \"height=500,width=500,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes\"); return false;'>".$p->t('profil/bildHochladen')."<a href=\"../../../cms/content.php?content_id=6174\" target=\"_blank\"> <img src=\"../../../skin/images/help.png\" width=\"18px\" height=\"18px\"></img></a></td>";

        echo '
                <td>'.$p->t('incoming/zugangsvoraussetzung').'&sup1;</td>
				<td><input type="text" name="zgv" size=40 value="'.$preincoming->zgv.'"></td>
			</tr>
			<tr>
                <td></td>
				<td>'.$p->t('incoming/abgelegtin').'</td>
				<td><input type="text" name="zgv_name" size=40 value="'.$preincoming->zgv_name.'"></td>
			</tr>
			<tr>
                <td></td>
				<td>'.$p->t('incoming/abgelegtinort').'</td>
				<td><input type="text" name="zgv_ort" size=40 value="'.$preincoming->zgv_ort.'"></td>
			</tr>
			<tr>
                <td></td>
				<td>'.$p->t('incoming/abgelegtam').'</td>
				<td><input type="text" name="zgv_datum" size=40 value="'.$date->formatDatum($preincoming->zgv_datum,'d.m.Y').'"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
			</tr>
			<tr>
				<td>'.$p->t('global/titel').' Pre</td>
				<td><input type="text" size="20" maxlength="64" name="titel_pre" value="'.$person->titelpre.'"></td>
				<td>'.$p->t('incoming/zugangsvoraussetzungmaster').'</td>
				<td><input type="text" name="zgv_master" value="'.$preincoming->zgvmaster.'" size=40></td>
            </tr>
			<tr>
				<td>'.$p->t('global/vorname').'</td>
				<td><input type="text" size="20" maxlength="32" name="vorname" value="'.$person->vorname.'"></td>
                <td>'.$p->t('incoming/abgelegtin').'</td>
				<td><input type="text" name="zgv_master_name" size=40 value="'.$preincoming->zgvmaster_name.'"></td>
            </tr>
			<tr>
                <td>'.$p->t('global/nachname').'</td>
				<td><input type="text" size="20" maxlength="64" name="nachname" value="'.$person->nachname.'"></td>
				<td>'.$p->t('incoming/abgelegtinort').'</td>
				<td><input type="text" name="zgv_master_ort" size=40 value="'.$preincoming->zgvmaster_ort.'"></td>
			</tr>
			<tr>
                <td>'.$p->t('global/titel').' Post</td>
				<td><input type="text" size="20" maxlength="32" name="titel_post" value="'.$person->titelpost.'"></td>
				<td>'.$p->t('incoming/abgelegtam').'</td>
				<td><input type="text" name="zgv_master_datum" size=40 value="'.$date->formatDatum($preincoming->zgvmaster_datum,'d.m.Y').'"></td>
			</tr>
            <tr>
                <td>'.$p->t('global/geburtsdatum').'</td>
				<td><input type="text" size="20" name="geb_datum" value="'.$date->formatDatum($person->gebdatum,'d.m.Y').'" onfocus="this.value=\'\'"> (dd.mm.yyyy)</td>
				<td>&nbsp;</td>
				<td></td>
			</tr>
			<tr>
                <td>'.$p->t('global/staatsbuergerschaft').'</td>
				<td><SELECT name="staatsbuerger">
				<option value="staat_auswahl">-- select --</option>';
				foreach ($nation->nation as $nat)
				{
					$selected="";
					if($person->staatsbuergerschaft == $nat->code)
						$selected = "selected";
					if($sprache == 'English')
						echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->engltext."</option>\n";
					else
						echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
				}

echo'			</SELECT></td>
				<td colspan="2">'.$p->t('incoming/personimernstfall').':</td>
				<td></td>
			</tr>
			<tr>
                <td>'.$p->t('global/geschlecht').'</td>';
	if($person->geschlecht == "m")
		echo '
				<td>    <input type="radio" name="geschlecht" value="m" checked> '.$p->t('global/mann').'
    					<input type="radio" name="geschlecht" value="w">'.$p->t('global/frau').'
    			</td>';
		else
			echo '
				<td>    <input type="radio" name="geschlecht" value="m"> '.$p->t('global/mann').'
    					<input type="radio" name="geschlecht" value="w" checked>'.$p->t('global/frau').'
    			</td>';

        echo    '<td>'.$p->t('global/vorname').'</td>
				<td><input type="text" size="40" name="emergency_vorname" value="'.$personEmergency->vorname.'">
				<input type="hidden" name="emergency_name_id" id="emergency_name_id" value="'.$preincoming->person_id_emergency.'"></td>
            </tr>
			<tr>
				<td></td>
                <td></td>
				<td>'.$p->t('global/nachname').'</td>
				<td><input type="text" size="40" name="emergency_nachname" value="'.$personEmergency->nachname.'"></td>
			</tr>
			<tr>
                <td>'.$p->t('global/strasse').'</td>
				<td><input type="text" size="40" maxlength="256" name="strasse" value="'.$adresse->result[0]->strasse.'"></td>
				<td>'.$p->t('global/telefon').'</td>
				<td><input type="text" size="40" name="emergency_telefon" value="'.$emTelefon.'">
				<input type="hidden" name="emergency_telefonId" id="emergency_telefonId" value="'.$emTelefonId.'"></td>
			</tr>
			<tr>
				<td>'.$p->t('global/plz').'</td>
				<td><input type="text" size="10" maxlength="16" name="plz" value="'.$adresse->result[0]->plz.'"></td>
				<td>Email</td>
				<td><input type="text" size="40" name="emergency_email" value="'.$emEmail.'">
				<input type="hidden" name="emergency_emailId" id="emergency_emailId" value="'.$emEmailId.'"></td>
			</tr>
            <tr>
                <td>'.$p->t('global/ort').'</td>
				<td><input type="text" size="40" maxlength="256" name="ort" value="'.$adresse->result[0]->ort.'"></td>
            </tr>
            <tr valign="top">
                <td>'.$p->t('incoming/nation').'</td>
				<td><SELECT name="nation">
				<option value="nat_auswahl">-- select --</option>';
				foreach ($nation->nation as $nat)
				{
					$selected="";
					if($adresse->result[0]->nation == $nat->code)
						$selected = "selected";
					if($sprache == 'English')
						echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->engltext."</option>\n";
					else
						echo '<option '.$selected.' value="'.$nat->code.'" >'.$nat->langtext."</option>\n";
				}
	echo '		</select></td>
                <td rowspan="4">'.$p->t('global/anmerkung').'</td>
				<td rowspan="4"><textarea name="anmerkung" cols="31" rows="5">'.$preincoming->anmerkung.'</textarea></td>
            </tr>
			<tr>
				<td>E-Mail</td>';
            $email ='';
            foreach($kontakt->result as $kon)
            {
                if($kon->kontakttyp == "email")
                {
                    $email = $kon->kontakt;
                }
            }
	echo'		<td><input type="text" size="40" maxlength="128" name="email" value="'.$email.'"></td>

			</tr>
            <tr>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
			<tr>

                <td align = "center" colspan="4"><input type="submit" name="submit_profil" value="'.$p->t('global/speichern').'" onclick="return checkProfil()">
				<input type="button" value="'.$p->t('incoming/uploadCv').'" onclick="FensterOeffnen(\''.APP_ROOT.'cis/public/incoming/akteupload.php?person_id='.$person->person_id.'&dokumenttyp=Lebenslf\');"></td>
			</tr>
			</table>
		</td>
		</tr>
		<tr>
			<td align="right"><input type="button" value="'.$p->t('incoming/weiter').'" onclick="document.location.href = \'incoming.php?method=university\';"></td>
		</tr>
		<tr>
			<td colspan="5">&sup1; '.$p->t('incoming/zugangsvoraussetzungFussnote').'</td>
			</tr>
		</table>
		<table border =0 align="center" style="margin-top:5%;" >
		<tr><td>

		</td></tr>
		</table>
	</form>

	<script type="text/javascript">

	function FensterOeffnen(adresse)
	{
		MeinFenster = window.open(adresse, "Info", "width=600,height=200");
  		MeinFenster.focus();
	}

	function checkProfil()
	{
		if(document.ProfilForm.staatsbuerger.options[0].selected == true)
		{
			alert("Keine Staatsbürgerschaft ausgewählt.");
			return false;
		}
		if(document.ProfilForm.nation.options[0].selected == true)
		{
			alert("Keine Nation ausgewählt.");
			return false;
		}
		if(document.ProfilForm.nachname.value == "")
		{
			alert("Keinen Nachnamen angegeben.");
			return false;
		}
		return true;
	}
	</script>';
}
else if($method == 'files')
{
	$akte = new akte();

	if(isset($_GET['id']))
	{
		if($_GET['mode']=="delete")
		{
			if($akte->delete($_GET['id']))
				echo($p->t('global/erfolgreichgelöscht'));
			else
				echo($p->t('global/fehleraufgetreten'));
		}
	}
	echo '<script type="text/javascript">
		function FensterOeffnen (adresse)
		{
			MeinFenster = window.open(adresse, "Info", "width=600,height=200");
	  		MeinFenster.focus();
		}
		</script>
		<br><br><br>
		<center>
			<a href="'.APP_ROOT.'cis/public/incoming/akteupload.php?person_id='.$person->person_id.'" onclick="FensterOeffnen(this.href); return false;">',$p->t('incoming/fileupload'),'</a></td>
		</center><br><br>';

	$akte->getAkten($person->person_id);

	if(count($akte->result)>0)
	{
		echo '<table  align="center" border="0">
				<tr>
					<th></th>
					<th>'.$p->t('incoming/name').'</th>
					<th>'.$p->t('global/bezeichnung').'</th>
				</tr>';
		foreach ($akte->result as $ak)
		{
			echo '<tr>
					<td><a href="'.$_SERVER['PHP_SELF'].'?method=files&mode=delete&id='.$ak->akte_id.'" title="delete"><img src="'.APP_ROOT.'skin/images/delete_round.png"</a></td>
					<td><a href="'.APP_ROOT.'cis/public/incoming/akte.php?id='.$ak->akte_id.'">'.$ak->titel.'</a></td>
					<td>'.$ak->bezeichnung.'</td>
				</tr>';
		}
		echo '</table>';
	}
}

// Ausgabe Menü
else
{
	echo '<br><br><br><br>
		<fieldset>
		<table align ="center"  border="0">
				<tr>
					<td>1. <a href="incoming.php?method=profil">'.$p->t('incoming/persönlichedateneditieren').'</a></td>
				</tr>
				<tr>
					<td>2. <a href="incoming.php?method=university">'.$p->t("incoming/eigeneuniversitaet").'</a></td>
				</tr>
				<tr>
					<td>3. <a href ="incoming.php?method=austauschprogram">'.$p->t('incoming/austauschprogram').'</a></td>
				</tr>
				<tr>
					<td>4. <a href="incoming.php?method=lehrveranstaltungen">'.$p->t('incoming/lehrveranstaltungenauswählen').'</a></td>
				</tr>
				<tr>
					<td>5. <a href="'.APP_ROOT.'cms/dms.php?id=8270">'.$p->t('incoming/downloadLearningAgreement').'</a></td>
				</tr>
				<tr>
					<td>6. <a href="'.APP_ROOT.'cis/public/incoming/akteupload.php?person_id='.$person->person_id.'&dokumenttyp=LearnAgr" onclick="FensterOeffnen(this.href); return false;">'.$p->t("incoming/uploadLearningAgreement").'</a></td>
				</tr>
				<tr>
					<td>7. <a href="incoming.php?method=files">'.$p->t("incoming/uploadvondateien").'</a></td>
				</tr>
			</table>
			<table width="100%" border="0">
				<tr>
					<td align="right"><a href="logout.php">Logout</a> </td>
				</tr>
			</table>';

	echo '<script type="text/javascript">
			function FensterOeffnen (adresse)
			{
				MeinFenster = window.open(adresse, "Info", "width=500,height=200");
		  		MeinFenster.focus();
			}
			</script>';

}
?>
	</body>
</html>