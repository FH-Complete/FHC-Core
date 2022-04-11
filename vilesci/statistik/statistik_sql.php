<?php
/* Copyright (C) 2011 fhcomplete.org
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
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/statistik.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/webservicelog.class.php');

$statistik_kurzbz = filter_input(INPUT_GET, 'statistik_kurzbz');
$outputformat = filter_input(INPUT_GET, 'outputformat');

$statistik = new statistik();
if(!$statistik->load($statistik_kurzbz))
{
	die($statistik->errormsg);
}

if (!isset($outputformat))
{
	$outputformat='html';
}

if($statistik->berechtigung_kurzbz != '')
{
	$uid = get_uid();

	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if(!$rechte->isBerechtigt($statistik->berechtigung_kurzbz))
	{
		die('Sie haben keine Berechtigung für diese Seite');
	}
}

if ($statistik->loadData())
{
	$csv = $statistik->getCSV();
	$json = $statistik->getJSON();
}
else
{
	echo $statistik->error_msg;
	return;
}

switch ($outputformat)
{
	case 'csv':
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=data.csv");
		header("Pragma: no-cache");
		header("Expires: 0");
		echo $csv;
		return;
	case 'json':
		header("Content-type: application/json");
		header("Content-Disposition: attachment; filename=data.json");
		header("Pragma: no-cache");
		header("Expires: 0");
		//$array= array_map("str_getcsv",explode("\n", $csv));
		echo $json;
		return;
}

$param='';
foreach($_REQUEST as $name=>$value)
{
	if (is_array($value))
	{
		foreach($value as $row)
			$param .= '&'.$name.'[]='.urlencode($row);
	}
	else
		$param .= '&'.$name.'='.urlencode($value);
}

// Log schreiben
$log = new webservicelog();
$log->request_data = $param;
$log->webservicetyp_kurzbz = 'reports';
$log->request_id = $statistik_kurzbz;
$log->beschreibung = 'statistik';
$log->execute_user = (isset($uid) ? $uid : 'unknown');
$log->save(true);

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Statistik</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css"/>
		<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css"/>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
		<?php
		include('../../include/meta/jquery.php');
		include('../../include/meta/jquery-tablesorter.php');
		?>
		<script type="text/javascript">
			$(function() {
				$("#myTable").tablesorter({
					widgets: ['zebra', 'filter', 'stickyHeaders']
				});
			});
		</script>
	</head>
	<body>
		<h2>Statistik - <?php echo $statistik->bezeichnung ?> - <a href="statistik_sql.php?outputformat=csv<?php echo $param;?>">CSV Download</a></h2>
			<?php echo $statistik->getHtmlTable('myTable', 'tablesorter'); ?>
	</body>
</html>
