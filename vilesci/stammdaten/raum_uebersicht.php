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
require_once('../../include/ort.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/standort.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('basis/ort', null, 's'))
	die($rechte->errormsg);

$organisationseinheit = new organisationseinheit();
$organisationseinheit->getAll();
$oe_arr = array();
foreach ($organisationseinheit->result as $oe)
{
	$oe_arr[$oe->oe_kurzbz] = $oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung;
}

$lehre = (isset($_GET['selectlehre'])?true:'');
$reservieren = (isset($_GET['selectreservieren'])?true:'');
$aktiv = (isset($_GET['sendform'])?(isset($_GET['selectaktiv'])?true:''):true);
$standort_id = (isset($_GET['standort_id'])?$_GET['standort_id']:'');
$gebaeudeteil = (isset($_GET['selectgebaeudeteil'])?$_GET['selectgebaeudeteil']:'');
$oe_kurzbz = (isset($_GET['oe_kurzbz'])?$_GET['oe_kurzbz']:'');

// Speichern der Daten
if(isset($_POST['ort_kurzbz']))
{
	// Die Aenderungen werden per Ajax Request durchgefuehrt,
	// daher wird nach dem Speichern mittels exit beendet

	//Lehre Feld setzen
	if(isset($_POST['lehre']))
	{
		if(!$rechte->isBerechtigt('basis/ort', null, 'sui'))
			die($rechte->errormsg);

		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->lehre=($_POST['lehre']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
	
	//Reservieren Feld setzen
	if(isset($_POST['reservieren']))
	{
		if(!$rechte->isBerechtigt('basis/ort', null, 'sui'))
			die($rechte->errormsg);
		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->reservieren=($_POST['reservieren']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
	
	//Aktiv Feld setzen
	if(isset($_POST['aktiv']))
	{
		if(!$rechte->isBerechtigt('basis/ort', null, 'sui'))
			die($rechte->errormsg);
		$lv_obj = new ort();
		if($lv_obj->load($_POST['ort_kurzbz']))
		{
			$lv_obj->aktiv=($_POST['aktiv']=='true'?false:true);
			$lv_obj->updateamum = date('Y-m-d H:i:s');
			$lv_obj->updatevon = $user;
			if($lv_obj->save(false))
				exit('true');
			else 
				exit('Fehler beim Speichern:'.$lv_obj->errormsg);
		}
		else 
			exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
	}
}

$htmlstr = '<a href="raum_details.php" target="detail_raum">Neuer Raum </a><br>';
	
	
$htmlstr .= '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';

$htmlstr.= ' Lehre<INPUT type="checkbox" name="selectlehre" id="selectlehre" '.($lehre=='true'?'checked':'').'>';
$htmlstr.= '  Reservieren<INPUT type="checkbox" name="selectreservieren" id="selectreservieren" '.($reservieren=='true'?'checked':'').'>';
$htmlstr.= '  Aktiv<INPUT type="checkbox" name="selectaktiv" id="selectaktiv" '.($aktiv=='true'?'checked':'').'>';
$htmlstr.= '<br>';

// Select Standort ID
$htmlstr.= 'Standort <SELECT name="standort_id">
			<OPTION value="">-- keine Auswahl --</OPTION>';

$standort = new standort();
if($standort->getStandorteWithTyp('Intern'))
{
	foreach($standort->result as $row)
	{
		if($row->standort_id == $standort_id)
			$selected = 'selected';
		else
			$selected = '';
			
		$htmlstr.='<OPTION value="'.$row->standort_id.'" '.$selected.'>'.$row->kurzbz.'</OPTION>';
	}
}
$htmlstr.= '</SELECT>';

// Input Gebäudeteil
$htmlstr.= '  Gebäudeteil <INPUT type="text" name="selectgebaeudeteil" id="selectgebaeudeteil" value="'.$gebaeudeteil.'" style="width: 30px">';

// Select oe_kurzbz
$oe=new organisationseinheit();
$oe->getAll();
$htmlstr.='  Organisationseinheit <SELECT name="oe_kurzbz">';
$htmlstr.='<OPTION value="">-- keine Auswahl --</option>';
foreach($oe->result as $row_oe)
{
	if($row_oe->oe_kurzbz == $oe_kurzbz)
		$selected = 'selected';
	else
		$selected = '';
	
	$htmlstr .= '<OPTION value="'.$row_oe->oe_kurzbz.'" '.$selected.'>'.$row_oe->organisationseinheittyp_kurzbz.' '.$row_oe->bezeichnung.'</OPTION>';
}
$htmlstr.='</SELECT>';

$htmlstr.= '<input type="hidden" name="sendform">';
$htmlstr .= '<br><br><input type="submit" value="Anzeigen">';
$htmlstr .= '</form>';

$tooltiptext = '
<div class="tooltiptext">
	<div class="table">
		<div class="table-caption">Sie können die folgenden Optionen zum filtern verwenden</div>
		<div class="tr">
			<div class="td"><code>|</code> oder <code>OR</code></div>
			<div class="td">Logisches "oder"</div>
		</div>
		<div class="tr">
			<div class="td"><code>&&</code> oder <code>AND</code></div>
			<div class="td">Logisches "und"</div>
		</div>
		<div class="tr">
			<div class="td"><code>/\d/</code></div>
			<div class="td">Regular Expression</div>
		</div>
		<div class="tr">
			<div class="td"><code>< <= >= ></code></div>
			<div class="td">Alphabetisches oder numerisches größer/kleiner gleich</div>
		</div>
		<div class="tr">
			<div class="td"><code>!</code> oder <code>!=</code></div>
			<div class="td">Verneinung (not)</div>
		</div>
		<div class="tr">
			<div class="td"><code>"</code> oder <code>=</code></div>
			<div class="td">Exake übereinstimmung</div>
		</div>
		<div class="tr">
			<div class="td"><code> - </code> oder <code> to </code></div>
			<div class="td">Bereichssuche (Leerzeichen beachten)</div>
		</div>
		<div class="tr">
			<div class="td"><code>?</code></div>
			<div class="td">Platzhalter für ein einzelnes Zeichen (nicht Leerzeichen)</div>
		</div>
		<div class="tr">
			<div class="td"><code>+</code></div>
			<div class="td">Platzhalter für mehrere Zeichen (nicht Leerzeichen)</div>
		</div>
		<div class="tr">
			<div class="td"><code>~</code></div>
			<div class="td">Unscharfe suche</div>
		</div>
	</div>
</div>
';

if (isset($_GET['sendform']))
{
	$htmlstr .= '
	<table class="tablesorter" id="t1">
	<thead>
		<tr>
			<th><span class="tooltip"><img src="../../skin/images/information.png" height="20px" name="infoicon"/>
				'.$tooltiptext.'
				</span>
			</th>
			<th>Kurzbezeichnung</th>
			<th>Bezeichnung</th>
			<th>Planbezeichnung</th>
			<th>Max. Person</th>
			<th>Arbeitsplaetze</th>
			<th>Quadratmeter</th>
			<th>Organisationseinheit</th>
			<th>Lehre</th>
			<th>Reservieren</th>
			<th>Aktiv</th>
			<th>Kosten</th>
			<th>Stockwerk</th>
	   </tr>
	</thead>
	<tbody>';
	
	$sg = new ort();
	if (!$sg->getOrte($lehre, $reservieren, $aktiv, $standort_id, $gebaeudeteil, $oe_kurzbz))
		die($sg->errormsg);
	
	foreach ($sg->result as $twraum)
	{
		$htmlstr .= "	<tr>\n";
		$htmlstr .= '		<td><a href="raum_details.php?type=raumtyp&ort_kurzbz='.$twraum->ort_kurzbz.'" target="detail_raum" title="Raumtyp zuteilen" ><img src="../../skin/images/entitlement-pot.png" height="20px"/></a></td>';
		$htmlstr .= "		<td><a href='raum_details.php?ort_kurzbz=".$twraum->ort_kurzbz."' target='detail_raum'>".$twraum->ort_kurzbz."</a></td>\n";
		$htmlstr .= "		<td>".$twraum->bezeichnung."</td>\n";
		$htmlstr .= "		<td>".$twraum->planbezeichnung."</td>\n";
		$htmlstr .= "		<td>".$twraum->max_person."</td>\n";
		$htmlstr .= "		<td>".$twraum->arbeitsplaetze."</td>\n";
		$htmlstr .= "		<td>".$twraum->m2."</td>\n";
		$htmlstr .= "		<td>".(isset($oe_arr[$twraum->oe_kurzbz])?$oe_arr[$twraum->oe_kurzbz]:'')."</td>\n";
		
		// Lehre boolean setzen
		
		$htmlstr .= "		<td style='white-space:nowrap;' align='center'><div style='display: none'>".($twraum->lehre==true?"t":"f")."</div> <a href='#Lehre' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"lehre\"); return false'>";
		$htmlstr .= "		<input type='hidden' id='lehre".$twraum->ort_kurzbz."' value='".($twraum->lehre==true?"true":"false")."'>";
		$htmlstr .= "		<img id='lehreimg".$twraum->ort_kurzbz."' title='Lehre' src='../../skin/images/".($twraum->lehre==true?"true.png":"false.png")."' height='20'>";
		$htmlstr .= "		</a></td>";
		
		// Reservieren boolean setzen
		
		$htmlstr .= "		<td style='white-space:nowrap;' align='center'><div style='display: none'>".($twraum->reservieren==true?"t":"f")."</div> <a href='#Reservieren' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"reservieren\"); return false'>";
		$htmlstr .= "		<input type='hidden' id='reservieren".$twraum->ort_kurzbz."' value='".($twraum->reservieren==true?"true":"false")."'>";
		$htmlstr .= "		<img id='reservierenimg".$twraum->ort_kurzbz."' title='Reservieren' src='../../skin/images/".($twraum->reservieren==true?"true.png":"false.png")."' style='margin:0;' height='20'>";
		$htmlstr .= "		</a></td>";
		
		// Aktiv boolean setzen
		
		$htmlstr .= "		<td style='white-space:nowrap;' align='center'><div style='display: none'>".($twraum->aktiv==true?"t":"f")."</div> <a href='#Aktiv' onclick='changeboolean(\"".$twraum->ort_kurzbz."\",\"aktiv\"); return false'>";
		$htmlstr .= "		<input type='hidden' id='aktiv".$twraum->ort_kurzbz."' value='".($twraum->aktiv==true?"true":"false")."'>";
		$htmlstr .= "		<img id='aktivimg".$twraum->ort_kurzbz."' title='Aktiv' src='../../skin/images/".($twraum->aktiv==true?"true.png":"false.png")."' style='margin:0;' height='20'>";
		$htmlstr .= "		</a></td>";
		
		$htmlstr .= "		<td>".$twraum->kosten."</td>\n";
		$htmlstr .= "		<td>".$twraum->stockwerk."</td>\n";
	
		$htmlstr .= "	</tr>\n";
	}
	$htmlstr .= "</tbody></table>\n";
}


?>
<html>
<head>
	<title>R&auml;ume &Uuml;bersicht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">

	<?php 
	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');
	?>
		
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<style>
	table.tablesorter tbody td
	{
		margin: 0;
		padding: 0;
		vertical-align: middle;
	}
	div.table { 
		display: table; 
		border-collapse:collapse; 
	}
	div.tr { 
		display:table-row; 
	}
	div.table-caption { 
		display:table-caption; 
	}
	div.td { 
		display:table-cell; 
		border:thin solid black; 
		padding:5px; 
	}
	 /* Tooltip container */
	.tooltip 
	{
		position: relative;
		display: inline;
	}
	.tooltip .tooltiptext 
	{
		visibility: hidden;
		width: 400px;
		background-color: #555;
		color: #fff;
		text-align: center;
		border-radius: 6px;
		padding: 5px 0;
		
		/* Position the tooltip */
		position: absolute;
		z-index: 1;
		top: -5px;
		left: 105%;
	}
	/* Show the tooltip text when you mouse over the tooltip container */
	.tooltip:hover .tooltiptext 
	{
		visibility: visible;
		opacity: 1;
	}
	</style>
	<script language="JavaScript" type="text/javascript">
	$(document).ready(function() 
	{ 
		$("#t1").tablesorter(
		{
			sortList: [[3,0]],
			widgets: ["zebra", "filter", "stickyHeaders"],
			headers: { 	0: { filter: false,  sorter: false }},
			widgetOptions : {	filter_functions : {
									// Add select menu to this column
									8 : {
									"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									},
									9 : {
									"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									},
									10 : {
									"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
									"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
									}
				}}
		});
	});
		
	function changeboolean(ort_kurzbz, name)
	{
		value=document.getElementById(name+ort_kurzbz).value;
	
		var dataObj = {};
		dataObj["ort_kurzbz"]=ort_kurzbz;
		dataObj[name]=value;

		$.ajax({
			type:"POST",
			url:"raum_uebersicht.php", 
			data:dataObj,
			success: function(data) 
			{
				if(data=="true")
				{
					//Image und Value aendern
					if(value=="true")
						value="false";
					else
						value="true";
					document.getElementById(name+ort_kurzbz).value=value;
					document.getElementById(name+"img"+ort_kurzbz).src="../../skin/images/"+value+".png";
				}
				else 
					alert("ERROR:"+data)
			},
			error: function() { alert("error"); }
		});
	}

	</script>
</head>
<body>
<h2>R&auml;ume &Uuml;bersicht</h2>

<?php 
	echo $htmlstr;
?>

</body>
</html>
