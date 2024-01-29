<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/service.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/datum.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/service'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$datum_obj = new datum();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Service</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');

	echo'
	<script type="text/javascript">

		$(document).ready(function()
			{
			    $("#myTable").tablesorter(
				{
					sortList: [[2,0]],
					widgets: [\'zebra\',\'filter\', \'stickyHeaders\'],
					headers: {9:{sorter:false,filter: false}}
				});
			}
		);

		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}
		</script>
</head>
<body>
<h2>Service &Uuml;bersicht</h2>
<div style="text-align:right">
	<a href="service_details.php?action=new" target="detail_service">Neu</a>
</div>';
if(isset($_GET['action']) && $_GET['action']=='delete')
{
	if(!$rechte->isBerechtigt('basis/service', null, 'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite');

	if(!isset($_GET['service_id']))
		die('Fehlender Parameter ServiceID');

	$service = new service();
	if($service->delete($_GET['service_id']))
		echo '<span class="ok">Eintrag wurde erfolgreich gelöscht</span>';
	else
		echo '<span class="error">'.$serivce->errormsg.'</span>';
}

$oe_kurzbz = (isset($_GET['oe_kurzbz'])?$_GET['oe_kurzbz']:'');

$service = new service();
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo 'Organisationseinheit: ';
echo '<SELECT name="oe_kurzbz">
<OPTION value="">-- Alle --</OPTION>';

$oe = new organisationseinheit();
$oe->getAll();
foreach($oe->result as $row)
{
	if($row->oe_kurzbz==$oe_kurzbz)
		$selected='selected';
	else
		$selected='';

	echo '<OPTION value="'.$row->oe_kurzbz.'" '.$selected.'>'.$row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.'</OPTION>';
}
echo '</SELECT>
<input type="submit" value="Filtern" />
</form>';

$servicekategorie_arr = $service->getKategorieArray();

if($oe_kurzbz!='')
{
	if(!$service->getServicesOrganisationseinheit($oe_kurzbz))
		die($service->errormsg);
}
else
{
	if(!$service->getAll())
		die($service->errormsg);
}
echo '<table class="tablesorter" id="myTable">
	<thead>
		<tr>
			<th>ID</th>
			<th>Bezeichnung</th>
			<th>Beschreibung</th>
			<th>Organisationseinheit</th>
			<th>Kategorie</th>
			<th>Content ID</th>
			<th>Design</th>
			<th>Betrieb</th>
			<th>Operativ</th>
			<th colspan="3">Aktion</th>
		</tr>
	</thead>
	<tbody>';

foreach($service->result as $row)
{
	echo '<tr>';
	echo '<td><a href="service_details.php?action=update&service_id=',$row->service_id,' " target="detail_service">',$row->service_id,'</a></td>';
	echo '<td>',$row->bezeichnung,'</td>';
	echo '<td>',$row->beschreibung,'</td>';
	echo '<td>',$row->oe_kurzbz,'</td>';
	$title = (isset($servicekategorie_arr[$row->servicekategorie_kurzbz])?$servicekategorie_arr[$row->servicekategorie_kurzbz]:'');
	echo '<td><span title="'.$service->convert_html_chars($title).'">',$row->servicekategorie_kurzbz,'</span></td>';
	echo '<td>',$row->content_id,'</td>';
	echo '<td>',$row->design_uid,'</td>';
	echo '<td>',$row->betrieb_uid,'</td>';
	echo '<td>',$row->operativ_uid,'</td>';
	echo '<td><a href="service_details.php?action=update&service_id=',$row->service_id,' " target="detail_service">bearbeiten</a>&nbsp;&nbsp;</td>';
	echo '<td><a href="service_uebersicht.php?action=delete&service_id=',$row->service_id,' " onclick="return confdel()">entfernen</a></td>';
	echo '</tr>';
}
echo '</tbody>
</table>
</body>
</html>';
?>
