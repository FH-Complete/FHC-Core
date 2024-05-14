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
 * 			Manfred Kindl			< manfred.kindl@technikum-wien.at >
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/firma.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/studiengang.class.php');

$user = get_uid();
$sprache = getSprache(); 
$p=new phrasen($sprache);

//$rechte = new benutzerberechtigung();
//$rechte->getBerechtigungen($user);
	
//if(!$rechte->isBerechtigt('basis/service'))
//	die('Sie haben keine Berechtigung fuer diese Seite');

$datum_obj = new datum();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>'.$p->t("services/service").'</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../../vendor/jquery/sizzle/sizzle.js"></script> 
	<script type="text/javascript">
	
		$(document).ready(function() 
			{ 
			    $("#myTable").tablesorter(
				{
					sortList: [[0,0]],
					widgets: [\'zebra\']
				}); 
			} 
		);
		
		function ContentPopUp (Adresse) 
		{
		  Content = window.open(Adresse, "Content", "width=800,height=500,scrollbars=yes");
		  Content.focus();
		}
		</script>
</head>
<body>
<h1>'.$p->t("tools/partnerhochschulenUebersicht").'</h1>
<p>'.$p->t("tools/partnerhochschulenEinleitung").'</p>';

$stg_kz = (isset($_GET['stg_kz'])?$_GET['stg_kz']:'');

$firma = new firma();
echo '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
echo $p->t("global/studiengang").': ';
echo '<SELECT onchange="this.form.submit()" name="stg_kz">
<OPTION value="">-- '.$p->t("global/alle").' --</OPTION>';

$studiengaenge = new studiengang();
$studiengaenge->getAll('typ,kurzbz',true);
$typ = '';
$types = new studiengang();
$types->getAllTypes();
foreach($studiengaenge->result as $row)
{
	if ($row->typ == 'b' || $row->typ == 'm')
	{
		//Nur Bachelor, Master und CIR-Studiengang
		if ($typ != $row->typ || $typ=='')
		{
			if ($typ!='')
				echo '</optgroup>';
			echo '<optgroup label="'.($types->studiengang_typ_arr[$row->typ]!=''?$types->studiengang_typ_arr[$row->typ]:$row->typ).'">';
		}
		if($row->studiengang_kz==$stg_kz)
			$selected='selected';
		else
			$selected='';
		
		echo '<OPTION value="'.$row->studiengang_kz.'" '.$selected.'>'.$row->kuerzel.' ('.$row->bezeichnung_arr[$sprache].')</OPTION>';
		$typ = $row->typ;
	}
}
echo '</SELECT>
<input type="submit" value="'.$p->t("services/filtern").'" />
</form>';

if($stg_kz!='')
{
	$studiengaenge->load($stg_kz);
	$firma->get_firmaorganisationseinheit('', $studiengaenge->oe_kurzbz, 'Partneruniversität');
}
else
{
	if(!$firma->getFirmen('Partneruniversität', true))
		die($firma->errormsg);
}
if ($firma->result)
{
	echo '<table class="tablesorter" id="myTable">
		<thead>
			<tr>
				<!--<th>'.$p->t("global/organisationseinheit").'</th>-->
				<th>'.$p->t("global/bezeichnung").'</th>
				<!--<th>'.$p->t("services/leistung").'</th>
				<th>'.$p->t("services/details").'</th>-->
			</tr>
		</thead>
		<tbody>';

	foreach($firma->result as $row)
	{
		// Nur aktive Partnerunis anzeigen
		if ($row->aktiv)
		{
			echo '<tr>';
			echo '<td>',$row->name,'</td>';
			/*echo '<td>'.($row->content_id!=''?'<a href="../../../cms/content.php?content_id='.$row->content_id.'">'.$row->bezeichnung.'</a>':$row->bezeichnung).'</td>';
			 echo '<td>',$row->beschreibung,'</td>';
			 echo '<td>'.($row->content_id!=''?'<a href="../../../cms/content.php?content_id='.$row->content_id.'">Details</a>':'').'</td>';*/
			echo '</tr>';
		}
	}
}
else
{
	echo '<p style="padding:20px;"><b>Kein Eintrag vorhanden</b></p>';
}
echo '</tbody>
</table>
</body>
</html>';
?>