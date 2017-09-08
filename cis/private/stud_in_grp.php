<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */

require_once('../../config/cis.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/functions.inc.php');

$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
		<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
		<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../include/js/sizzle-0.9.3.js"></script>
		<script type="text/javascript">	
		$(document).ready(function() 
			{ 
			    $("#table").tablesorter(
				{
					sortList: [[0,0]],
					widgets: [\'zebra\'],
				}); 
			} 
		);
		</script>
	</head>
	<title>'.$p->t('mailverteiler/personenImVerteiler').'</title>
<body>';

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if(!isset($_GET['kz']))
    die($p->t('global/fehlerBeiDerParameteruebergabe'));

    if(isset($_GET['all']))
{
	$qry = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE aktiv=true AND studiengang_kz='".addslashes($_GET['kz'])."' AND semester<10 AND semester>0 ORDER BY nachname, vorname";
}
else
{
	$qry = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE aktiv=true AND studiengang_kz='".addslashes($_GET['kz'])."'";

	if(isset($_GET['sem']))
		$qry.=" AND semester='".addslashes($_GET['sem'])."'";

	if(isset($_GET['verband']))
		$qry.=" AND verband='".addslashes($_GET['verband'])."'";

	if(isset($_GET['grp']))
		$qry.=" AND gruppe='".addslashes($_GET['grp'])."'";

	$qry.= ' ORDER BY nachname, vorname';
}
if($result=$db->db_query($qry))
{
	echo '<p>'.$row=$db->db_num_rows($result).' '.$p->t('mailverteiler/personen');
}
echo '
		<table class="tablesorter" id="table">
		<thead>
	      <tr>
	        <th>'.$p->t('global/nachname').'</th>
	        <th>'.$p->t('global/vorname').'</th>
	        <th>'.$p->t('global/mail').'</th>
	      </tr>
	     </thead><tbody>';

if($result=$db->db_query($qry))
{
	while($row=$db->db_fetch_object($result))
	{
		echo "<tr>";
		echo "  <td>$row->nachname</td>";
		echo "  <td>$row->vorname</td>";
		echo "  <td><a href='mailto:$row->uid@".DOMAIN."' class='Item'>$row->uid@".DOMAIN."</a></td>";
		echo "</tr>";
	}
}
else
	echo $p->t('global/fehlerBeimLesenAusDatenbank');

echo '	</tbody></table>
	</body>
</html>';
?>