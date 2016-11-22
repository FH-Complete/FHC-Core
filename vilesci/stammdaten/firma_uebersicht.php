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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/firma.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/studiengang.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$user = get_uid();

//Berechtigung pruefen
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$berechtigung_kurzbz = 'basis/firma:begrenzt';
if(!$rechte->isBerechtigt($berechtigung_kurzbz))
	die('Sie haben keine Berechtigung fuer diese Seite ');

// Parameter uebernehmen
$suchen = (isset($_GET['suchen'])?$_GET['suchen']:null);
$filter = (isset($_GET['filter'])?$_GET['filter']:'');
$firmentypfilter = (isset($_GET['firmentypfilter'])?$_GET['firmentypfilter']:'');
$oe_kurzbz = (isset($_GET['oe_kurzbz'])?$_GET['oe_kurzbz']:'');
?>
<html>
<head>
	<title>Firma Uebersicht</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="background_main">
	<h2>Firmen - &Uuml;bersicht</h2>
<?php 
echo '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>'; 
	//Suche
	echo '<form onsubmit="parent.frames[1].location.href =\'firma_details.php\';parent.frames[2].location.href =\'firma_detailwork.php\';" action="'.$_SERVER['PHP_SELF'].'" method="GET"><td>';
	echo '&nbsp;Suche: <input type="text" name="filter" value="'.$filter.'">';
	echo '&nbsp;Typ: <SELECT name="firmentypfilter">
			<option value="">-- Alle --</option>';
	$firma = new firma();
	$firma->getFirmenTypen();
	foreach ($firma->result as $row)
	{
		if($row->firmentyp_kurzbz==$firmentypfilter)
			$selected='selected';
		else 
			$selected='';
		echo "<option value='$row->firmentyp_kurzbz' $selected>$row->firmentyp_kurzbz</option>";
	}
	echo '</SELECT>';
	echo '&nbsp;Organisationseinheit: <SELECT name="oe_kurzbz">
			<option value="">-- Alle --</option>';
	$oe = new organisationseinheit();
	$oe->getAll(true,null,'organisationseinheittyp_kurzbz, bezeichnung');
	

	foreach ($oe->result as $row)
	{
		$stg = new studiengang();
		$stg->getStudiengaengeFromOe($row->oe_kurzbz,null);
		$stg_bezeichnung = '';
		if ($row->organisationseinheittyp_kurzbz=='Studiengang' && isset($stg->result[0]))
			$stg_bezeichnung = '('.$stg->result[0]->bezeichnung.')';
		if($row->oe_kurzbz==$oe_kurzbz)
			$selected='selected';
		else
			$selected='';
		echo '<option value="'.$row->oe_kurzbz.'" '.$selected.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung.' '.$stg_bezeichnung).'</option>';
	}
	echo '</SELECT>';
	echo '&nbsp;<input type="submit" name="suchen" value="Suchen">';
	echo '</td></form>';
	
	echo "<td align='right'><input type='button' onclick='parent.detail_firma.location=\"firma_details.php?neu=true\"' value='Neue Firma anlegen'/></td>";
	echo '</tr></table>';
	echo creatList($suchen,$filter,$firmentypfilter,$oe_kurzbz);
	
?>
</body>
</html>
<?php

function creatList($suchen,$filter,$firmentypfilter,$oe_kurzbz)
{
	// Initialisieren HTML Listenausgabe
	$htmlstr = "";
	$firma_finanzamt = new firma();
	$firmentyp_finanzamt='Finanzamt';
	$firma_finanzamt->errormsg='';
	$firma_finanzamt->result=array();
	$oes='';
	$oes = new organisationseinheit();
	$oes = $oes->getChilds($oe_kurzbz);
	//echo "Filter: ".$filter." Firmentypfilter: ".$firmentypfilter."<br>";
	if (!is_null($suchen)) // Nur wenn Suchknopf gedrueckt wurde
		$firma_finanzamt->searchFirma($filter,$firmentypfilter,false,$oes);	
		
    if($firma_finanzamt->errormsg)
		return 'Fehler beim Laden der Firma<br>';
	
	if ($firma_finanzamt->result)
	{
	    $htmlstr .= "</form><table id='t1' class='liste table-autosort:1 table-stripeclass:alternate table-autostripe'>\n";
		$htmlstr .= "   <thead><tr class='liste'>\n";
	    $htmlstr .= "       <th class='table-sortable:numeric'>ID</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Name</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Anmerkung</th>";
		$htmlstr .= "       <th class='table-sortable:default'>Kurzbz</th>";
		$htmlstr .= "       <th class='table-sortable:default'>Standort</th>";

	    $htmlstr .= "       <th class='table-sortable:default'>Plz</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Ort</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Strasse</th>";		

	    $htmlstr .= "       <th class='table-sortable:default'>Typ</th>";
		$htmlstr .= "       <th class='table-sortable:default'>Aktiv</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Gesperrt</th>";
	    $htmlstr .= "       <th class='table-sortable:default'>Schule</th>";
				
	    //$htmlstr .= "       <th class='table-sortable:default'>Ext ID</th>";

	    $htmlstr .= "   </tr></thead><tbody>\n";
	    $i = 0;
		foreach ($firma_finanzamt->result as $row)
	    {
			// Adresse
			$row->adresse_neu=$row->plz.' '.$row->ort;			
			
			$htmlstr .= "   <tr class='liste". ($i%2) ."'>\n";
	        $htmlstr .= "       <td><a onclick=\"parent.frames[2].location.href ='firma_detailwork.php';\" href='firma_details.php?firma_id=".$row->firma_id."' target='detail_firma'>".$row->firma_id."</a></td>\n";
			$htmlstr .= "       <td><a onclick=\"parent.frames[2].location.href ='firma_detailwork.php';\" href='firma_details.php?firma_id=".$row->firma_id."' target='detail_firma'>".$row->name."</a></td>\n";
	        $htmlstr .= "       <td title='".$row->anmerkung."'>".StringCut($row->anmerkung,27)."</td>\n";
			$htmlstr .= "       <td>".$row->kurzbz."</td>\n";
			$htmlstr .= "       <td>".StringCut($row->bezeichnung,27)."</td>\n";
			// Adresse
	        $htmlstr .= "       <td>$row->plz</td>\n";
	        $htmlstr .= "       <td>$row->ort</td>\n";
	        $htmlstr .= "       <td>$row->strasse</td>\n";
	        $htmlstr .= "       <td>$row->firmentyp_kurzbz</td>\n";
			$htmlstr .= "       <td>".($row->aktiv=='t'?'Ja':'Nein')."</td>\n";
			$htmlstr .= "       <td>".($row->gesperrt=='t'?'Ja':'Nein')."</td>\n";
	        $htmlstr .= "       <td>".($row->schule=='t'?'Ja':'Nein')."</td>\n";
	        //$htmlstr .= "       <td>$row->ext_id</td>\n";
	        $htmlstr .= "   </tr>\n";
	        $i++;
	    }
	    $htmlstr .= "</tbody></table>\n";
	}
	else 
	{
		$htmlstr .= "<br>Keine Einträge gefunden";
	}
	return $htmlstr;
}
?>
