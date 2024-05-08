<?php
/*
 * Copyright 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 * Authors: Stefan Puraner	<puraner@technikum-wien.at>
 */

require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/organisationseinheit.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/pruefungsfenster.class.php');
require_once('../../../../include/phrasen.class.php');
require_once('../../../../include/globals.inc.php');
require_once('../../../../include/sprache.class.php');

$sprache = getSprache();
$lang = new sprache();
$lang->load($sprache);
$p = new phrasen($sprache);

$uid = get_uid();
$db = new basis_db();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('lehre/pruefungsfenster'))
	die('Sie haben keine Berechtigung für diese Seite');

function compareOe($a, $b)
{
	if($a->organisationseinheittyp_kurzbz == $b->organisationseinheittyp_kurzbz)
		return 0;

	return ($a->organisationseinheittyp_kurzbz < $b->organisationseinheittyp_kurzbz) ? -1 : 1;
}

?><!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title><?php echo $p->t('pruefung/titlePruefungsfenster') ?></title>
		<script src="../../../../include/js/datecheck.js"></script>
		<script type="text/javascript" src="../../../../vendor/components/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/jquery.tablesorter.widgets.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/mottie/tablesorter/dist/js/extras/jquery.tablesorter.pager.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../../vendor/components/jqueryui/ui/i18n/datepicker-de.js"></script>
		<link rel="stylesheet" href="../../../../vendor/components/jqueryui/themes/base/jquery-ui.min.css">
		<link rel="stylesheet" href="../../../../skin/fhcomplete.css">
		<link rel="stylesheet" href="../../../../skin/style.css.php">
		<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/theme.default.min.css">
		<link rel="stylesheet" href="../../../../vendor/mottie/tablesorter/dist/css/jquery.tablesorter.pager.min.css">
	</head>
	<body>
		<script>
			$(document).ready(function() {
				$("#startDate").datepicker({
					minDate: new Date()
				});
				$("#endDate").datepicker({
					minDate: +1
				});
				$("#prfTable").tablesorter({
					widgets: ["zebra"]
				});
			});

		</script>
<?php
$datum = new datum();
$method = "";

//Organisationseinheiten, für die der Benutzer berechtigt ist
$oe = $rechte->getOEkurzbz("lehre/pruefungsfenster");

$studiensemester = new studiensemester();
$studiensemester->getAll();

if (isset($_POST["method"]) && $_POST["method"] == "save")
{
	$method = $_POST["method"];
	$studiensemester_selected = (isset($_POST["studiensemester"]) ? $_POST["studiensemester"] : "");
	$oe_kurzbz = (isset($_POST["oe_kurzbz"]) ? $_POST["oe_kurzbz"] : "");
	$startDate = (isset($_POST["startDate"]) ? $datum->formatDatum($_POST["startDate"], "Y-m-d") : "");
	$endDate = (isset($_POST["endDate"]) ? $datum->formatDatum($_POST["endDate"], "Y-m-d") : "");

	if ($endDate != false && $startDate != false)
	{
		if ($datum->DateDiff($startDate, $endDate) >= 0)
		{
			if ($datum->DateDiff(date("Y-m-d"), $endDate) >= 0)
			{
				$pruefungsfenster = new pruefungsfenster();
				$pruefungsfenster->new = true;
				$pruefungsfenster->studiensemester_kurzbz = $studiensemester_selected;
				$pruefungsfenster->oe_kurzbz = $oe_kurzbz;
				$pruefungsfenster->start = $startDate;
				$pruefungsfenster->ende = $endDate;
				if ($pruefungsfenster->save())
				{
					echo $p->t('pruefung/erfolgreichgespeichert');
				}
				else {
					echo $p->t('pruefung/fehler').$pruefungsfenster->errormsg;
				}
			}
			else
			{
				echo $p->t('pruefung/fehlerEndDatumInDerVergangenheit');
			}
		}
		else
		{
			echo $p->t('pruefung/fehlerEndDatumVorStartDatum');
		}
	}
	else
	{
		echo $p->t('pruefung/fehlerDatumNichtKorrekt');
	}
}
else if(isset($_POST["method"]) && $_POST["method"] == "update")
{
	$studiensemester_selected = (isset($_POST["studiensemester"]) ? $_POST["studiensemester"] : "");
	$oe_kurzbz = (isset($_POST["oe_kurzbz"]) ? $_POST["oe_kurzbz"] : "");
	$startDate = (isset($_POST["startDate"]) ? $datum->formatDatum($_POST["startDate"], "Y-m-d") : "");
	$endDate = (isset($_POST["endDate"]) ? $datum->formatDatum($_POST["endDate"], "Y-m-d") : "");

	if ($endDate != false && $startDate != false)
	{
		if ($datum->DateDiff($startDate, $endDate) >= 0)
		{
			if ($datum->DateDiff(date("Y-m-d"), $endDate) >= 0)
			{
				$pruefungsfenster_id = $_POST["id"];
				$pruefungsfenster = new pruefungsfenster();
				$pruefungsfenster->load($pruefungsfenster_id);

				if(in_array($pruefungsfenster->oe_kurzbz, $oe))
				{
					$pruefungsfenster->studiensemester_kurzbz = $studiensemester_selected;
					$pruefungsfenster->oe_kurzbz = $oe_kurzbz;
					$pruefungsfenster->start = $startDate;
					$pruefungsfenster->ende = $endDate;
					if ($pruefungsfenster->save())
					{
						echo $p->t('pruefung/erfolgreichgeaendert');
					}
					else {
						echo $p->t('pruefung/fehler').$pruefungsfenster->errormsg;
					}
				}
				else
				{
					echo $p->t('pruefung/keineBerechtigungZumAendernDesDatensatzes');
				}
			}
			else
			{
				echo $p->t('pruefung/fehlerEndDatumInDerVergangenheit');
			}
		}
		else
		{
			echo $p->t('pruefung/fehlerEndDatumVorStartDatum');
		}
	}
	else
	{
		echo $p->t('pruefung/fehlerDatumNichtKorrekt');
	}
}
else if(isset($_GET["id"]) && $_GET["id"]!= null && isset($_GET["method"]) && $_GET["method"]=="update")
{
	$pruefungsfenster_id = $_GET["id"];
	$pruefungsfenster = new pruefungsfenster();
	$pruefungsfenster->load($pruefungsfenster_id);
	if(!in_array($pruefungsfenster->oe_kurzbz, $oe))
	{
		echo $p->t('pruefung/keineBerechtigungZumAnzeigenDesDatensatzes');
		$pruefungsfenster = new pruefungsfenster();
	}
	$method = $_GET["method"];
}
else if(isset($_GET["id"]) && $_GET["id"]!= null && isset($_GET["method"]) && $_GET["method"]=="delete")
{
	$pruefungsfenster_id = $_GET["id"];
	$pruefungsfenster = new pruefungsfenster();
	$pruefungsfenster->load($pruefungsfenster_id);

	if(in_array($pruefungsfenster->oe_kurzbz, $oe))
	{
		if(!$pruefungsfenster->hasPruefungen($pruefungsfenster_id) && $pruefungsfenster->errormsg === null)
		{

			if($pruefungsfenster->delete($pruefungsfenster_id))
			{
				echo $p->t('pruefung/erfolgreichgeloescht');
			}
			else
			{
				echo "Fehler: ".$pruefungsfenster->errormsg;
			}
		}
		else
		{
			echo $p->t('pruefung/pruefungsfensterKonnteNichtGeloeschtWerdenDaPruefungen');
		}
		$method = $_GET["method"];
	}
	else
	{
		echo $p->t('pruefung/keineBerechtigungZumLoeschenDesDatensatzes');
	}
}

$prfFenster = new pruefungsfenster();
$prfFenster->getAll("start");
if($method != "update")
{
?>
		<h1><?php echo $p->t('pruefung/pruefungsfensterVerwaltung'); ?></h1>
		<h2><?php echo $p->t('pruefung/neuesPruefungsfensterAnlegen'); ?></h2>
		<div>
			<form method="POST" action="pruefungsfenster_anlegen.php">
				<table>
					<tr>
						<td><input type="hidden" name="method" value="save"></td>
					</tr>
					<tr>
						<td><?php echo $p->t('global/studiensemester'); ?>: </td>
						<td>
							<select id="studiensemester" name="studiensemester">
								<?php
								$aktuellesSemester = $studiensemester->getSemesterFromDatum(date("Y-m-d"));
								foreach ($studiensemester->studiensemester as $result)
								{
									if($aktuellesSemester == $result->studiensemester_kurzbz)
									{
										echo '<option selected>'.$result->studiensemester_kurzbz.'</option>';
									}
									else
									{
										echo '<option>'.$result->studiensemester_kurzbz.'</option>';
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $p->t('global/organisationseinheit'); ?>: </td>
						<td>
							<select id="oe_kurzbz" name="oe_kurzbz">
								<?php

								$oe_array = array();

								foreach ($oe as $result)
								{
									$organisationseinheit = new organisationseinheit();
									$organisationseinheit->load($result);
									array_push($oe_array, $organisationseinheit);
								}

								usort($oe_array, "compareOe");

								foreach ($oe_array as $result)
								{
									echo '<option value="'.$result->oe_kurzbz.'">'.$result->organisationseinheittyp_kurzbz.' '.$result->bezeichnung.'</option>';
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $p->t('pruefung/start'); ?>: </td>
						<td><input type="text" id="startDate" name="startDate"></td>
					</tr>
					<tr>
						<td><?php echo $p->t('pruefung/ende'); ?>: </td>
						<td><input type="text" id="endDate" name="endDate"></td>
					</tr>
					<tr>
						<td><input type="submit" value="<?php echo $p->t('global/speichern'); ?>"></td>
					</tr>
				</table>
			</form>
		</div>
		<?php
}
else
{
	?>
	<h1><?php echo $p->t('pruefung/pruefungsfensterVerwaltung'); ?></h1>
	<h2><?php echo $p->t('pruefung/pruefungsfensterBearbeiten'); ?></h2>
		<div>
			<form method="POST" action="pruefungsfenster_anlegen.php">
				<table>
					<tr>
						<td><input type="hidden" name="method" value="update"></td>
					</tr>
					<tr>
						<td><input type="hidden" name="id" value="<?php echo $pruefungsfenster->pruefungsfenster_id; ?>"></td>
					</tr>
					<tr>
						<td><?php echo $p->t('global/studiensemester'); ?>: </td>
						<td>
							<select id="studiensemester" name="studiensemester">
								<?php
								foreach ($studiensemester->studiensemester as $result)
								{
									if($result->studiensemester_kurzbz == $pruefungsfenster->studiensemester_kurzbz)
									{
										echo '<option selected>'.$result->studiensemester_kurzbz.'</option>';
									}
									else
									{
										echo '<option>'.$result->studiensemester_kurzbz.'</option>';
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $p->t('global/organisationseinheit'); ?>: </td>
						<td>
							<select id="oe_kurzbz" name="oe_kurzbz">
								<?php
								foreach ($oe as $result)
								{
									if($result == $pruefungsfenster->oe_kurzbz)
									{
										echo '<option selected>'.$result.'</option>';
									}
									else
									{
										echo '<option>'.$result.'</option>';
									}
								}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td><?php echo $p->t('pruefung/start'); ?>: </td>
						<td><input type="text" id="startDate" name="startDate" value="<?php echo $pruefungsfenster->start; ?>"></td>
					</tr>
					<tr>
						<td><?php echo $p->t('pruefung/ende'); ?>: </td>
						<td><input type="text" id="endDate" name="endDate" value="<?php echo $pruefungsfenster->ende; ?>"></td>
					</tr>
					<tr>
						<td><input type="submit" value="<?php echo $p->t('global/speichern'); ?>"></td>
						<td><a href="pruefungsfenster_anlegen.php"><input type="button" value="<?php echo $p->t('global/abbrechen'); ?>"></a></td>
					</tr>
				</table>
			</form>
		</div>
	<?php
}
/*
 * Wenn ein Datensatz um bearbeiten ausgewählt wurde,
 * wird dieser Block nicht angezeigt.
 */

 if((isset($_GET["id"]) && $method!="update") || !isset($_GET["id"]))
 {
		?>
	<h2><?php echo $p->t('pruefung/pruefungsfensterBearbeiten'); ?></h2>
		<div style="width: 50%;">
			<?php
				if(!empty($prfFenster->result)){

			?>
			<table class="tablesorter" id="prfTable">
				<thead>
					<tr>
						<th><?php echo $p->t('global/studiensemester'); ?></th>
						<th><?php echo $p->t('global/organisationseinheit'); ?></th>
						<th><?php echo $p->t('pruefung/start'); ?></th>
						<th><?php echo $p->t('pruefung/ende'); ?></th>
						<th><?php echo $p->t('global/bearbeiten'); ?></th>
						<th><?php echo $p->t('global/loeschen'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						$organisationseinheit = new organisationseinheit();
						foreach ($prfFenster->result as $result)
						{
							if(in_array($result->oe_kurzbz, $oe))
							{
								$organisationseinheit->load($result->oe_kurzbz);
								echo
								'<tr>
									<td>'.$result->studiensemester_kurzbz.'</td>
									<td>'.$organisationseinheit->organisationseinheittyp_kurzbz." ".$organisationseinheit->bezeichnung.'</td>
									<td>'.$result->start.'</td>
									<td>'.$result->ende.'</td>
									<td><a href="pruefungsfenster_anlegen.php?method=update&id='.$result->pruefungsfenster_id.'">'.$p->t('global/bearbeiten').'</a></td>
									<td><a href="pruefungsfenster_anlegen.php?method=delete&id='.$result->pruefungsfenster_id.'">'. $p->t('global/loeschen').'</a></td>
								</tr>';
							}
						}
					?>
				</tbody>
			</table>
			<?php
				}
				else
				{
					echo
					'<tr>
						<td>'.$p->t('pruefung/keinePruefungsfensterGespeichert').'</td>
					</tr>';
				}
			?>
		</div>
<?php
	}
?>
	</body>
</html>
