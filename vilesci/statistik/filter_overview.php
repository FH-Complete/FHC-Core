<?php
/* Copyright (C) 2013 fhcomplete.org
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
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/filter.class.php');
require_once('../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/statistik', null, 'suid'))
	die('Sie haben keine Berechtigung (basis/statistik) für diese Seite');


if(isset($_POST['action']) && $_POST['action']=='delete' && isset($_POST['filter_id']))
{
	$filter = new filter();
	$filter->delete($_POST['filter_id']);

}
$filter = new filter();
if (!$filter->loadAll())
    die($filter->errormsg);
?>
<html>
	<head>
		<title>Filter Übersicht</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
		<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
		<?php
		include("../../include/meta/jquery-tablesorter.php");
		?>
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<script language="JavaScript" type="text/javascript">

			$(function() {
				$("#t1").tablesorter(
				{
					sortList: [[2,0]],
					widgets: ["zebra", "filter", "stickyHeaders"],
					headers: {8: {sorter: false, filter: false}}
				});
			});

			function ConfirmDelete(filter_id)
			{
				if(confirm("Wollen Sie diesen Filter wirklich löschen?"))
				{
					document.forms['form_'+filter_id].submit();
				}
			}
		</script>
	</head>

	<body>
		<a href="filter_details.php" target="frame_filter_details">Neuer Filter</a>

		<form name="formular">
			<input type="hidden" name="check" value="">
		</form>
		<table class="tablesorter" id="t1">
			<thead>
				<tr>
					<th onmouseup="document.formular.check.value=0">
						ID
					</th>
					<th title="Kurzbezeichnung des Filters">
						KurzBz
					</th>
					<th>
						Bezeichnung
					</th>
					<th>
						ValueName
					</th>
					<th>
						Show Value
					</th>
					<th>
						Type
					</th>
					<th>
						HTMLAttributes
					</th>
					<th>
						SQL
					</th>
					<th>
						Action
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($filter->result as $filter): ?>
					<tr>
						<td class="overview-id">
							<a href="filter_details.php?filter_id=<?php echo $filter->filter_id ?>" target="frame_filter_details">
								<?php echo $filter->filter_id ?>
							</a>
							<a href="filter_vorschau.php?filter_id=<?php echo $filter->filter_id ?>" target="_blank">
								<img src="../../skin/images/Filter.svg" class="mini-icon" />
							</a>
						</td>
						<td>
							<a href="filter_details.php?filter_id=<?php echo $filter->filter_id ?>" target="frame_filter_details">
								<?php echo $filter->kurzbz ?>
							</a>
						</td>
						<td>
							<?php echo $db->convert_html_chars($filter->bezeichnung) ?>
						</td>
						<td>
							<?php echo $db->convert_html_chars($filter->valuename) ?>
						</td>
						<td>
							<?php echo $filter->showvalue ? 'Ja' : 'Nein' ?>
						</td>
						<td>
							<?php echo $db->convert_html_chars($filter->type) ?>
						</td>
						<td>
							<?php echo $db->convert_html_chars($filter->htmlattr) ?>
						</td>
						<td>
							<?php echo $db->convert_html_chars(substr($filter->sql,0,32)) ?>...
						</td>
						<td>
							<form action="<?php echo basename(__FILE__) ?>" name="form_<?php echo $filter->filter_id ?>" method="POST">
								<input type="hidden" name="filter_id" value="<?php echo $filter->filter_id ?>">
								<input type="hidden" name="action" value="delete" />
								<a href="#Loeschen" onclick="ConfirmDelete(<?php echo $filter->filter_id ?>);">
									Delete
								</a>
							</form>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</body>
</html>
