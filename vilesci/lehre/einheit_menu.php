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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/gruppe.class.php');
require_once('../../include/gruppemanager.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/student.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (isset($_GET['studiengang_kz']))
	$studiengang_kz=$_GET['studiengang_kz'];
else if(isset($_POST['studiengang_kz']))
	$studiengang_kz = $_POST['studiengang_kz'];
else
	$studiengang_kz='';

if ($studiengang_kz != '')
{
	$studiengang_oe = new studiengang($studiengang_kz);
	if ($studiengang_oe->oe_kurzbz != '')
		$oe_studiengang = $studiengang_oe->oe_kurzbz;
	else
		$oe_studiengang = '';
}

if (isset($_GET['searchItems']) && trim($_GET['searchItems']) != '')
{
	$searchItems = explode(' ', trim($_GET['searchItems']));
}
else
{
	$searchItems = array();
}

if (isset($_GET['sem']))

	$sem=$_GET['sem'];
else
	$sem=null;

if (isset($_GET['ss']))

	$ss=$_GET['ss'];
else
	$ss=null;

$uid = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('lehre/gruppe', null, 's'))
	die($rechte->errormsg);

?>
<html>
	<head>
		<title>Gruppe-Verwaltung</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<link rel="stylesheet" href="../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">

		<?php
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		<?php
			// html Vorlage für ein manager span
			const MANAGER_HTML = "<span class='manager-uid'>%s - %s %s&nbsp;"
			."<img class='manager-delete-image' src='../../skin/images/cross.png' title='Manager entfernen' alt='Manager entfernen'>"
			."<input type='hidden' name='gruppemanager[]' value='%s'>"
			."</span>";
		?>
		<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script language="JavaScript" type="text/javascript">
		function conf_del()
		{
			return confirm('Diese Gruppe wirklich löschen?');
		}
		function setManagerDeleteEvent()
		{
			var deleteImages = $('.manager-delete-image');
			deleteImages.off("click");
			deleteImages.click(function()
			{
				// closest manager uid parent
				$(this).closest('.manager-uid').remove();
				adjustManagerTableCellSize();
			});
		}
		function showIfManagerAssignable()
		{
			var generiert = $("#generiert").prop('checked');

			if (generiert === true)
			{
				$("#gruppemanager").prop("disabled", true);
				$("#genGruppenManagerHinweis").removeClass("hiddenNotice");
			}
			else
			{
				$("#gruppemanager").prop("disabled", false);
				$("#genGruppenManagerHinweis").addClass("hiddenNotice");
			}
		}
		function adjustManagerTableCellSize()
		{
			// Tabellenzelle vergrössern wenn es Administratorelemente gibt
			if ($("input[name='gruppemanager[]']").length)
				$("#gruppenmanager-cell").addClass("gruppenmanager-cell");
			else
				$("#gruppenmanager-cell").removeClass("gruppenmanager-cell");
		}
		$(document).ready(function()
		{
			$("#t1").tablesorter(
			{
				sortList: [[0,0]],
				widgets: ["saveSort", "zebra", "filter", "stickyHeaders"],
				headers: { 12: { filter: false,  sorter: false }},
				widgetOptions : {filter_saveFilters : true}
			});

			$('.resetsaved').click(function()
			{
				$("#t1").trigger("filterReset");
				location.reload(forceGet);
				return false;
			});

			$( "#mailgrp" ).click(function()
			{
				$( "#domain_text" ).toggle();
				$('#gesperrt').prop('disabled', function(i, v) { return !v; });
			});

			// Löschen von Gruppemanager html bei Klick
			setManagerDeleteEvent();

			// Hinzufügen von Managern deaktiviert wenn generierte Gruppe
			showIfManagerAssignable();

			// autocomplete für user input Feld
			$("#gruppemanager").autocomplete({
				source: "einheit_autocomplete.php?work=searchUser",
				minLength:3,
				response: function(event, ui)
				{
					// Value und Label fuer die Anzeige setzen
					for(i in ui.content)
					{
						ui.content[i].value=ui.content[i].uid;
						ui.content[i].label=ui.content[i].uid+" - "+ui.content[i].vorname+" "+ui.content[i].nachname;
					}
				},
				// bei Auswahl einer uid
				select: function(event, ui)
				{
					// Administrator html holen und Werte ersetzen
					var managerHtml = "<?php echo MANAGER_HTML ?>";
					var managerHtmlValues = [ui.item.uid, ui.item.vorname, ui.item.nachname, ui.item.uid];

					for (var i = 0; i < managerHtmlValues.length; i++)
					{
						managerHtml = managerHtml.replace(/%s/, managerHtmlValues[i]);
					}

					// wenn noch nicht vorhanden, Administrator unterhalb des Inputfeldes einfügen
					if (!$("input[name='gruppemanager[]'][value='"+ui.item.uid+"']").length)
					{
						var counter = 0;
						$(".manager-uid-container").children().each(
							function() {
								if ($(this).hasClass('manager-uid'))
									counter++;
								else
									return false;
							}
						);

						// nach 5 Managern in nächste Zeile springen
						if (counter >= 5)
						{
							$(".manager-uid-container").prepend(
								"<p class='manager-separator'></p>"
							);
						}

						$(".manager-uid-container").prepend(
							managerHtml
						);

						// Loeschen Event für neuen Administrator setzen
						setManagerDeleteEvent();
						// Größe der Administrator Tabellenzelle anpassen
						adjustManagerTableCellSize();
					}

					// Feld leeren
					$("#gruppemanager").val('');
					return false; // prevent default, damit text nicht im Inputfeld bleibt
				}
			});

			// Hinzufügen von Managern deaktiviert wenn Gruppe auf generiert setzen
			$("#generiert").click(showIfManagerAssignable);
		});
		</script>
		<style>
		.tablesorter-default input.tablesorter-filter
		{
			padding: 0 4px;
		}
		#newFormTable tr:hover
		{
			background-color: #d1d1d1;
		}
		col.first-table-column
		{
			width: 130px;
		}
		.manager-uid
		{
			border: 1px solid black;
			padding: 2px;
			margin-right: 2px;
			white-space: nowrap;
		}
		.manager-delete-image
		{
			position: relative;
			top: 4px;
			cursor: pointer;
		}
		.manager-separator
		{
			margin: 3px;
		}
		.gruppenmanager-cell
		{
			padding-bottom: 3px;
		}
		.hiddenNotice
		{
			display: none;
		}
		</style>
	</head>
<body>
	<H2>Gruppen - Verwaltung</H2>
<?php

if (isset($_POST['newFrm']) || isset($_GET['newFrm']))
{
	if($rechte->isBerechtigt('lehre/gruppe', null, 'sui'))
		doEdit(null,true);
	else
		echo '<span class="error">'.$rechte->errormsg.'</span>';
}
else if (isset($_GET['edit']))
{
	if($rechte->isBerechtigt('lehre/gruppe', null, 'sui'))
		doEdit(addslashes($_GET['kurzbz']),false);
	else
		echo '<span class="error">'.$rechte->errormsg.'</span>';
}
else if (isset($_POST['type']) && $_POST['type']=='save')
{
	printDropDown();
	doSave();
	getUebersicht();
}
else if (isset($_GET['type']) && $_GET['type']=='delete')
{
	printDropDown();

	if($rechte->isBerechtigt('lehre/gruppe', $oe_studiengang, 'suid'))
	{
		$grp_kurzbz = $_GET['einheit_id'];

		$e=new gruppe();
		if(!$e->delete($grp_kurzbz))
			echo $e->errormsg;
	}
	else
		echo '<span class="error">'.$rechte->errormsg.'</span>';

	getUebersicht();
}
else
{
	printDropDown();
	if ($studiengang_kz != '' || count($searchItems) > 0)
		getUebersicht();
}

function printDropDown()
{
	global $rechte, $studiengang_kz, $searchItems;
	//Studiengang Drop Down anzeigen
	$types = new studiengang();
	$types->getAllTypes();
	$typ = '';
	$stud = new studiengang();
	if(!$stud->getAll('typ, kurzbz'))
		echo 'Fehler beim Laden der Studiengaenge:'.$stud->errormsg;

	// Studiengang AuswahlFilter
	echo '<form accept-charset="UTF-8" name="frm_studiengang" action="'.$_SERVER['PHP_SELF'].'" method="GET">';
	echo 'Studiengang: <SELECT name="studiengang_kz" onchange="document.frm_studiengang.submit()">';
	echo '<OPTION value="">-- PLEASE SELECT --</OPTION>';

	foreach($stud->result as $row)
	{
		if($rechte->isBerechtigt('lehre/gruppe', $row->oe_kurzbz, 's'))
		{
			if ($typ != $row->typ || $typ=='')
			{
				if ($typ!='')
					echo '</optgroup>';
					echo '<optgroup label="'.($types->studiengang_typ_arr[$row->typ]!=''?$types->studiengang_typ_arr[$row->typ]:$row->typ).'">';
			}
			/*if($studiengang_kz=='')
				$studiengang_kz=$row->studiengang_kz;*/

			echo '<OPTION value="'.$row->studiengang_kz.'"'.($studiengang_kz==$row->studiengang_kz?'selected':'').'>'.$row->kuerzel.' - '.$row->bezeichnung.'</OPTION>';
			$typ = $row->typ;
		}
	}

	echo '</SELECT>';
	echo '<br>oder</br>';
	echo 'Suche: <input name="searchItems" type="text" size="50" value="'.implode(' ',$searchItems).'"/>';
	echo '<input type="submit" value="Anzeigen" />';
	echo '</form>';
}

function doSave()
{
	global $rechte;

	$studiengang = new studiengang($_POST['studiengang_kz']);
	if ($studiengang->oe_kurzbz != '')
		$oe_studiengang = $studiengang->oe_kurzbz;
	else
		$oe_studiengang = '';

	if($rechte->isBerechtigt('lehre/gruppe', $oe_studiengang, 'sui'))
	{
		$e = new gruppe();

		if ($_POST['new']=='true')
		{
			$e->new = true;
			$e->gruppe_kurzbz = $_POST['kurzbz'];
			$e->insertamum = date('Y-m-d H:i:s');
			$e->insertvon = get_uid();
		}
		else
		{
			$e->load($_POST['kurzbz']);
			$e->new = false;
		}

		$user = get_uid();

		$e->updateamum = date('Y-m-d H:i:s');
		$e->updatevon = $user;
		$e->bezeichnung = $_POST['bezeichnung'];
		$e->beschreibung = $_POST['beschreibung'];
		$e->studiengang_kz = $_POST['studiengang_kz'];
		$e->semester = $_POST['semester'];
		$e->mailgrp = isset($_POST['mailgrp']);
		$e->sichtbar = isset($_POST['sichtbar']);
		$e->lehre = isset($_POST['lehre']);
		$e->generiert = isset($_POST['generiert']);
		$e->aktiv = isset($_POST['aktiv']);
		$e->gesperrt = isset($_POST['gesperrt']);
		$e->zutrittssystem = isset($_POST['zutrittssystem']);
		$e->aufnahmegruppe = isset($_POST['aufnahmegruppe']);
		$e->sort = $_POST['sort'];
		$e->content_visible = isset($_POST['content_visible']);


		// gruppemanager immer array, leer wenn keine angegeben
		$gruppemanager_uids = isset($_POST['gruppemanager']) && is_array($_POST['gruppemanager']) ? $_POST['gruppemanager'] : array();

		// Prüfung: generierte Gruppen haben keine Manager
		if (count($gruppemanager_uids) > 0 && $e->generiert === true)
		{
			echo "<span class='error'>Generierte Gruppen dürfen keine Administratoren haben!</span>";
			return;
		}

		if(!$e->save())
			echo "<span class='error'>".$e->errormsg."</span>";
		else // wenn Gruppe erfolgreich gespeichert, Gruppenmanager speichern
		{
			// Gruppe gemäss Konvention in Großbuchstaben
			$gruppe_kurzbz = mb_strtoupper($_POST['kurzbz']);

			// bestehende Gruppenmanager laden
			$bestehende_gruppemanager_uids = array();
			$gruppemanager_hlp = new gruppemanager();
			if ($gruppemanager_hlp->load_uids($gruppe_kurzbz))
			{
				foreach ($gruppemanager_hlp->uids as $uid_obj)
				{
					$bestehende_gruppemanager_uids[] = $uid_obj->uid;
				}
			}

			foreach ($gruppemanager_uids as $gruppemanager_uid)
			{
				// wenn Gruppenmanager noch nicht zugewiesen
				if (!in_array($gruppemanager_uid, $bestehende_gruppemanager_uids))
				{
					// Gruppemanager speichern
					$gruppemgr = new gruppemanager();
					$gruppemgr->uid = $gruppemanager_uid;
					$gruppemgr->gruppe_kurzbz = $gruppe_kurzbz;
					$gruppemgr->insertamum = date('Y-m-d H:i:s');
					$gruppemgr->insertvon = $user;

					if(!$gruppemgr->save())
						echo $gruppemgr->errormsg;
				}
			}

			// zu löschende Gruppemanager ermitteln
			$geloeschte_gruppemanager_uids = array_diff($bestehende_gruppemanager_uids, $gruppemanager_uids);

			// Nicht mehr vorhandene Gruppenmanager löschen
			$gruppemanager_hlp = new gruppemanager();
			foreach ($geloeschte_gruppemanager_uids as $geloeschte_uid)
			{
				if (!$gruppemanager_hlp->delete($geloeschte_uid, $gruppe_kurzbz))
					echo $gruppemanager_hlp->errormsg;
			}
		}
	}
}

function doEdit($kurzbz,$new=false)
{
	global $db, $rechte, $studiengang, $searchItems;
	if (!$new)
	{
		$e = new gruppe($kurzbz);
		echo '<a href="einheit_menu.php?studiengang_kz='.$e->studiengang_kz.'&searchItems='.implode(' ',$searchItems).'">Zurück zur &Uuml;bersicht</a><br>';
	}
	else
	{
		$e = new gruppe();
		$e->lehre = false;
	}
	?>
	<form name="gruppe" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
		<p><b>Gruppe <?php echo ($new?'hinzufügen':'bearbeiten'); ?></b><br>
		<table id="newFormTable" border="0">
		<colgroup>
			<col span="1" class="first-table-column">
		</colgroup>
		<tbody>
			<tr>
				<td>Kurzbezeichnung</td>
				<td>
					<input type="text" name="kurzbz" size="45" maxlength="32" value="<?php echo $e->gruppe_kurzbz; ?>" style="text-transform: uppercase" required="required">
					<span id="domain_text" <?php echo ($e->mailgrp?'style="display: inline"':'style="display: none"');?>>@<?php echo DOMAIN;?></span>
				</td>
				<td><i>Name der Gruppe im System bzw. Name des Verteilers</i></td>
			</tr>
			<tr>
				<td>Bezeichnung</td>
				<td>
					<input type="text" name="bezeichnung" size="45" maxlength="32" value="<?php echo $e->bezeichnung; ?>">
				</td>
				<td><i>Bezeichnung</i></td>
			</tr>
			<tr>
				<td>Beschreibung</td>
				<td>
					<input type="text" name="beschreibung" size="80" maxlength="128" value="<?php echo $e->beschreibung; ?>">
				</td>
				<td><i>Beschreibungstext im CIS</i></td>
			</tr>
			<tr>
				<td>Studiengang</td>
				<td>
					<SELECT name="studiengang_kz">
							<option value="">- auswählen -</option>
						<?php
							// Auswahl des Studiengangs
							$types = new studiengang();
							$types->getAllTypes();
							$typ = '';
							$stg = new studiengang();
							$stg->getAll('typ, kurzbz');
							foreach($stg->result as $studiengang)
							{
								if($rechte->isBerechtigt('lehre/gruppe', $studiengang->oe_kurzbz, 'sui'))
								{
									if ($typ != $studiengang->typ || $typ=='')
									{
										if ($typ!='')
											echo '</optgroup>';
											echo '<optgroup label="'.($types->studiengang_typ_arr[$studiengang->typ]!=''?$types->studiengang_typ_arr[$studiengang->typ]:$studiengang->typ).'">';
									}
									if($studiengang->studiengang_kz == $e->studiengang_kz)
										$selected = 'selected="selected"';
									else
										$selected='';

									echo '<option value="'.$studiengang->studiengang_kz.'" '.$selected.'>'.$db->convert_html_chars($studiengang->kuerzel.' - '.$studiengang->bezeichnung).'</option>';
									$typ = $studiengang->typ;
								}
							}
						?>
					</SELECT>
				</td>
				<td></td>
			</tr>
			<tr>
				<td>Gruppenadministrator</td>
				<?php
					$gruppemanagerCellClass = '';
					$gruppemanager_hlp = new gruppemanager();
					$gruppemanager_uids_result = $gruppemanager_hlp->load_uids($e->gruppe_kurzbz);

					// richtige Tabellenzeige Grösse wenn Administratoren vorhanden
					if ($gruppemanager_uids_result === true)
					{
						$gruppemanagerCellClass = ' class="gruppenmanager-cell"';
					}
				?>
				<td<?php echo $gruppemanagerCellClass ?> id="gruppenmanager-cell">
					<input type="text" name="gruppemanager" id="gruppemanager" autofocus="autofocus" />
					<span class="hiddenNotice" id="genGruppenManagerHinweis">
						Generierte Gruppen dürfen keine Administratoren haben.
					</span>
					<?php
						echo "<div class='manager-uid-container'>";
						// alle Manager der Gruppe anzeigen
						if ($gruppemanager_uids_result === true)
						{
							$count = 1;
							foreach ($gruppemanager_hlp->uids as $uid_obj)
							{
								$ben = new benutzer($uid_obj->uid);
								// Vorlagestring durch Werte ersetzen und ausgeben
								echo sprintf(MANAGER_HTML, $uid_obj->uid, $ben->vorname, $ben->nachname, $uid_obj->uid);
								if ($count % 5 == 0) // neue Zeile nach 5 Elementen
									echo "<p class='manager-separator'></p>";
								$count++;
							}
						}
					?>
				</td>
				<td>Optional, Administratoren, die Benutzer zur Gruppe entfernen/hinzufügen können</td>
			</tr>
			<tr>
				<td>Semester</td>
				<td><input type="text" name="semester" size="2" maxlength="1" value="<?php echo $e->semester ?>"></td>
				<td><i>Optional</i></td>
			</tr>
			<tr>
				<td>Aktiv</td>
				<td><input type='checkbox' name='aktiv' <?php echo ($e->aktiv?'checked':'');?>></td>
				<td><i>Aktiviert die Gruppe in allen Systemen</i></td>
			</tr>
			<tr>
				<td>Sichtbar</td>
				<td><input type='checkbox' name='sichtbar' <?php echo ($e->sichtbar?'checked':'');?>></td>
				<td><i>Soll die Gruppe im CIS sichtbar sein?</i></td>
			</tr>
			<tr>
				<td>Lehre</td>
				<td><input type='checkbox' name='lehre' id='lehre' <?php echo ($e->lehre?'checked':'');?>></td>
				<td><i>Wird die Gruppe in der Lehre als Unterrichtsgruppe verwendet?</i></td>
			</tr>
			<tr>
				<td>ContentVisible</td>
				<td><input type='checkbox' name='content_visible' id='content_visible' <?php echo ($e->content_visible?'checked':'');?>></td>
				<td><i>Soll die Gruppe verwendet werden, um im CMS Zugriffsberechtigungen zu steuern?</i></td>
			</tr>
			<tr>
				<td>Generiert</td>
				<td><input type='checkbox' name='generiert' id='generiert' <?php echo ($e->generiert?'checked':'');?>></td>
				<td><i>Wenn gesetzt, können keine Personen manuell hinzugefügt werden. Generierte Gruppen werden meist von Sync-scripten befüllt</i></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr style="padding-top: 50px;">
				<td>Mailgrp</td>
				<td><input type='checkbox' name='mailgrp' id='mailgrp' <?php echo ($e->mailgrp?'checked':'');?>></td>
				<td><i>Soll die Gruppe auch ein Mailverteiler sein?</i></td>
			</tr>
			<tr>
				<td>Gesperrt</td>
				<td><input type='checkbox' name='gesperrt' id='gesperrt' <?php echo ($e->gesperrt?'checked':''); echo ($e->mailgrp?'':'disabled="disabled"');?>></td>
				<td><i>Gesperrte Verteiler können nicht von allen Personen beschickt werden</i></td>
			</tr>
			<tr>
				<td>Zutrittssystem</td>
				<td><input type='checkbox' name='zutrittssystem' <?php echo ($e->zutrittssystem?'checked':'');?>></td>
				<td><i>Wird die Gruppe für die Zutrittssteuerung im Gebäude verwendet?</i></td>
			</tr>
			<tr>
				<td>Aufnahmegruppe</td>
				<td><input type='checkbox' name='aufnahmegruppe' <?php echo ($e->aufnahmegruppe?'checked':'');?>></td>
				<td><i>Wird die Gruppe als Aufnahmegruppe im Bewerbungsverfahren und beim Reihungstest verwendet?</i></td>
			</tr>
			<tr>
				<td>Sort</td>
				<td><input type='number' name='sort' maxlength="5" min="-32768" max="32767" size="5" value="<?php echo $e->sort;?>"></td>
				<td><i>Positive oder Negative ganze Zahl zw. -32768 und 32767 zur relativen Sortierung</i></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			</tbody>
		</table>
	<input type="hidden" name="pk" value="<?php echo $e->gruppe_kurzbz ?>" />
	<input type="hidden" name="new" value="<?php echo ($new?'true':'false') ?>" />
	<input type="hidden" name="type" value="save">
	<input type="submit" name="save" value="Speichern">
	</p>
	<hr>
</form>
<?php
}

function getUebersicht()
{
	global $studiengang_kz, $semester, $rechte, $searchItems;
	if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');

	$gruppe=new gruppe();
	// Wenn $searchstring gesetz ist, nach gruppe suchen, sonst gruppe mit $studiengang_kz un $semester laden
	if (count($searchItems) > 0)
	{
		$gruppe->searchGruppen($searchItems, null, null);
	}
	else
	{
		$gruppe->getgruppe($studiengang_kz,$semester);
	}

	echo '<h3>&Uuml;bersicht</h3>';
	echo '<button type="button" class="resetsaved" title="Reset Filter">Reset Filter</button>';

	echo "<table id='t1' class='tablesorter'>";
	echo "<thead>
			<tr class='liste'>
				<th>Kurzbz.</th>
				<th>Bezeichnung</th>
				<th>Beschreibung</th>
				<!--<th>Stg.</th>-->
				<th>Sem.</th>
				<th data-placeholder='t or f'>Aktiv</th>
				<th data-placeholder='t or f'>Sichtbar</th>
				<th data-placeholder='t or f'>Lehre</th>
				<th data-placeholder='t or f'>ContentVisible</th>
				<th data-placeholder='t or f'>Generiert</th>
				<th data-placeholder='t or f'>Mailgrp</th>
				<th data-placeholder='t or f'>Gesperrt</th>
				<th data-placeholder='t or f'>Zutrittssystem</th>
				<th data-placeholder='t or f'>Aufnahmegruppe</th>
				<th colspan=\"3\">Aktion</th>
			</tr>
			</thead><tbody>";

	$i=0;
	$studiengang = new studiengang($studiengang_kz);
	if ($studiengang->oe_kurzbz != '')
		$oe_studiengang = $studiengang->oe_kurzbz;
	else
		$oe_studiengang = '';

	foreach ($gruppe->result as $e)
	{
		echo '<tr>';

		echo "<td>$e->gruppe_kurzbz </td>";
		echo "<td>$e->bezeichnung </td>";
		echo "<td>$e->beschreibung </td>";
		//echo "<td>".$stg->kuerzel_arr[$e->studiengang_kz]."</td>";
		echo "<td>$e->semester </td>";
		echo "<td><img title='Aktiv' height='16px' src='../../skin/images/".($e->aktiv?"true.png":"false.png")."' alt='".($e->aktiv?"true.png":"false.png")."'></td>";
		echo "<td><img title='Sichtbar' height='16px' src='../../skin/images/".($e->sichtbar?"true.png":"false.png")."' alt='".($e->sichtbar?"true.png":"false.png")."'></td>";
		echo "<td><img title='Lehre' height='16px' src='../../skin/images/".($e->lehre?"true.png":"false.png")."' alt='".($e->lehre?"true.png":"false.png")."'></td>";
		echo "<td><img title='ContentVisible' height='16px' src='../../skin/images/".($e->content_visible?"true.png":"false.png")."' alt='".($e->content_visible?"true.png":"false.png")."'></td>";
		echo "<td><img title='Generiert' height='16px' src='../../skin/images/".($e->generiert?"true.png":"false.png")."' alt='".($e->generiert?"true.png":"false.png")."'></td>";
		echo "<td><img title='Mailgrp' height='16px' src='../../skin/images/".($e->mailgrp?"true.png":"false.png")."' alt='".($e->mailgrp?"true.png":"false.png")."'></td>";
		echo "<td><img title='Gesperrt' height='16px' src='../../skin/images/".($e->gesperrt?"true.png":"false.png")."' alt='".($e->gesperrt?"true.png":"false.png")."'></td>";
		echo "<td><img title='Zutrittssystem' height='16px' src='../../skin/images/".($e->zutrittssystem?"true.png":"false.png")."' alt='".($e->zutrittssystem?"true.png":"false.png")."'></td>";
		echo "<td><img title='Aufnahmegruppe' height='16px' src='../../skin/images/".($e->aufnahmegruppe?"true.png":"false.png")."' alt='".($e->aufnahmegruppe?"true.png":"false.png")."'></td>";
		// src="../../skin/images/'.($row->projektarbeit=='t'?'true.png':'false.png').'"
		//echo "<td>".$gruppe->countStudenten($e->gruppe_kurzbz)."</td>"; Auskommentiert, da sonst die Ladezeit der Seite zu lange ist
		echo "<td style='padding-right: 5px'><a href='einheit_det.php?kurzbz=$e->gruppe_kurzbz&searchItems=".implode(' ',$searchItems)."'>Personen</a></td>";

		if($rechte->isBerechtigt('lehre/gruppe', $oe_studiengang, 'su'))
			echo "<td style='padding-right: 5px'><a href=\"einheit_menu.php?edit=1&kurzbz=$e->gruppe_kurzbz&searchItems=".implode(' ',$searchItems)."\">Edit</a></td>";

		if($rechte->isBerechtigt('lehre/gruppe', $oe_studiengang, 'suid'))
			echo "<td><a href=\"einheit_menu.php?einheit_id=$e->gruppe_kurzbz&studiengang_kz=$e->studiengang_kz&type=delete\" onclick='return conf_del()'>Delete</a></td>";

		echo "</tr>\n";
	}

	echo '</tbody></table>';
}

?>
</body>
</html>
