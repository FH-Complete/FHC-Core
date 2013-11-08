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
	require_once('../../include/globals.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/ort.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/ortraumtyp.class.php');
	require_once('../../include/raumtyp.class.php');
	require_once('../../include/standort.class.php');
	
	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$user = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	
	if(!$rechte->isBerechtigt('basis/ort',null,'suid'))
		die('Sie haben keine Berechtigung fuer diese Seite');
	
	$reloadstr = '';  // neuladen der liste im oberen frame
	$htmlstr = '';
	$errorstr = ''; //fehler beim insert
	$sel = '';
	$chk = '';

	$sg_var = new ort();
		
	$ort_kurzbz = '';
	$bezeichnung = '';
	$planbezeichnung = '';
	$max_person = '';
	$lehre = "t";
	$reservieren = "f";
	$aktiv = "t";
	$lageplan = '';
	$dislozierung = '';
	$kosten = '';
	$ausstattung = '';
	$stockwerk = '';
	$standort_id = '';
	$telefonklappe = '';
	$content_id='';
	
	$neu = "true";
	
	if(isset($_POST["schick"]))
	{
		if(!$rechte->isBerechtigt('basis/ort', null, 'suid'))
			die('Sie haben keine Berechtigung fuer diese Aktion');
	
		$ort_kurzbz = $_POST["ort_kurzbz"];
		$bezeichnung = $_POST["bezeichnung"];
		$planbezeichnung = $_POST["planbezeichnung"];
		$max_person = $_POST["max_person"];
		$lageplan = $_POST["lageplan"];
		$dislozierung = $_POST["dislozierung"];
		$kosten = $_POST["kosten"];
		$ausstattung = $_POST["ausstattung"];
		$stockwerk = $_POST["stockwerk"];
		$standort_id = $_POST["standort_id"];
		$telefonklappe = $_POST["telefonklappe"];
		$content_id = $_POST['content_id'];

		
		$sg_update = new ort();
		$sg_update->ort_kurzbz = $ort_kurzbz;
		$sg_update->bezeichnung = $bezeichnung;
		$sg_update->planbezeichnung = $planbezeichnung;
		$sg_update->max_person = $max_person;
		$sg_update->lehre = isset($_POST["lehre"]);
		$sg_update->reservieren = isset($_POST["reservieren"]);
		$sg_update->aktiv = isset($_POST["aktiv"]);
		$sg_update->lageplan = $lageplan;
		$sg_update->dislozierung = $dislozierung;
		$sg_update->kosten = $kosten;
		$sg_update->ausstattung = $ausstattung;
		$sg_update->stockwerk = $stockwerk;
		$sg_update->telefonklappe = $telefonklappe;
		$sg_update->standort_id = $standort_id;
		$sg_update->content_id = $content_id;

		
		if ($_POST["neu"] == "true")
			$sg_update->new = 1;

		if(!$sg_update->save())
		{
			$errorstr .= $sg_update->errormsg;
		}
		$reloadstr .= "<script type='text/javascript'>\n";
		$reloadstr .= "	parent.uebersicht_raum.location.href='raum_uebersicht.php';";
		$reloadstr .= "</script>\n";
	}



	if ((isset($_REQUEST['ort_kurzbz'])) && ((!isset($_REQUEST['neu'])) || ($_REQUEST['neu']!= "true")))
	{
		$ort_kurzbz = $_REQUEST["ort_kurzbz"];
		$sg = new ort($ort_kurzbz);
		if ($sg->errormsg!='')
			die($sg->errormsg);
		$ort_kurzbz = $sg->ort_kurzbz;
		$bezeichnung = $sg->bezeichnung;
		$planbezeichnung = $sg->planbezeichnung;
		$max_person = $sg->max_person;
		$lehre = $sg->lehre;
		$reservieren = $sg->reservieren;
		$aktiv = $sg->aktiv;
		$lageplan = $sg->lageplan;
		$dislozierung = $sg->dislozierung;
		$kosten = $sg->kosten;
		$ausstattung = $sg->ausstattung;
		$stockwerk = $sg->stockwerk;
		$standort_id = $sg->standort_id;
		$telefonklappe = $sg->telefonklappe;
		$content_id = $sg->content_id;
		$neu = "false";
	}

	if(isset($_GET['type']) && $_GET['type']=='raumtyp')
	{
		if($ort_kurzbz=='')
			die('OrtKurzbz fehlt');

		$ort = new ort();
		if(!$ort->load($ort_kurzbz))
			die($ort->errormsg);

		if(isset($_GET['method']))
		{
			switch($_GET['method'])
			{
				case 'delete':
					//Zuordnung zu einem Raumtyp entfernen
					$ortraumtyp = new ortraumtyp();
					$ortraumtyp->delete($ort_kurzbz, $_GET['raumtyp_kurzbz']);
					break;
				case 'add':
					//Zuordnung zu einem Raumtyp hinzufuegen
					$ortraumtyp = new ortraumtyp();
					$ortraumtyp->ort_kurzbz = $ort_kurzbz;
					$ortraumtyp->raumtyp_kurzbz = $_POST['raumtyp_kurzbz'];
					$ortraumtyp->hierarchie = $_POST['hierarchie'];
					$ortraumtyp->new=true;
					if(!$ortraumtyp->save())
						$htmlstr.='Fehler beim Speichern '.$ortraumtyp->errormsg;
					break;
				case 'neu':
					// Anlegen eines neuen Raumtyps
					$raumtyp=new raumtyp();
					$raumtyp->new=true;
					$raumtyp->raumtyp_kurzbz=$_POST['raumtyp_kurzbz'];
					$raumtyp->beschreibung = $_POST['beschreibung'];
					$raumtyp->save();
					break;
			}
		}

		$htmlstr.='<h2>Raumtypen - '.$ort->bezeichnung.' ( '.$ort->ort_kurzbz.' )</h2>';

		$ortraumtyp = new ortraumtyp();
		if($ortraumtyp->getRaumtypen($ort_kurzbz))
		{
			$htmlstr.='
				<script>
				$(document).ready(function() 
				{ 
					$("#raumtyptable").tablesorter(
					{
						sortList: [[2,0]],
						widgets: ["zebra"]
					}); 
				});
				</script>
				<table class="tablesorter" id="raumtyptable">
				<thead>
					<th>Raumtyp</th>
					<th>Kurzbz</th>
					<th>Hierarchie</th>
					<th></th>
				</thead>
				<tbody>';
			$hierarchiemax=0;
			foreach($ortraumtyp->result as $row)
			{
				if($row->hierarchie>$hierarchiemax)
					$hierarchiemax=$row->hierarchie;
				$htmlstr.= '<tr>';
				$htmlstr.= '<td>'.$row->beschreibung.'</td>';
				$htmlstr.= '<td>'.$row->raumtyp_kurzbz.'</td>';
				$htmlstr.= '<td>'.$row->hierarchie.'</td>';
				$htmlstr.= '<td><a href="raum_details.php?type=raumtyp&ort_kurzbz='.$ort_kurzbz.'&method=delete&raumtyp_kurzbz='.$row->raumtyp_kurzbz.'">Entfernen</a></td>';
				$htmlstr.= '</tr>';
			}
			$htmlstr.='</tbody></table>';
			$htmlstr.='<form action="raum_details.php?type=raumtyp&ort_kurzbz='.$ort_kurzbz.'&method=add" method="POST">
			Raumtyp:<SELECT name="raumtyp_kurzbz">';

			$raumtyp = new raumtyp();
			$raumtyp->getAll();
			foreach($raumtyp->result as $row)
			{
				$htmlstr.= '<OPTION value="'.$row->raumtyp_kurzbz.'">'.$row->beschreibung.' ('.$row->raumtyp_kurzbz.')</OPTION>';
			}
			$htmlstr.='</SELECT>
			Hierarchie: <input type="text" name="hierarchie" size="1" value="'.($hierarchiemax+1).'">
			<input type="submit" value="Hinzufügen">
			</form>';

			$htmlstr.='<br><br><hr>
			<form action="raum_details.php?type=raumtyp&ort_kurzbz='.$ort_kurzbz.'&method=neu" method="POST">
				Beschreibung: <input type="text" name="beschreibung" maxlength="256">
				Kurzbz: <input type="text" name="raumtyp_kurzbz" size="16" maxlength="16">
				<input type="submit" value="Neuen Raumtyp anlegen">
			</form>';
		
		}
	}
	else
	{	
		if($ort_kurzbz != '')
		    $htmlstr .= "<br><div class='kopf'>Raum <b>".$ort_kurzbz."</b></div>\n";
		else
		    $htmlstr .="<br><div class='kopf'>Neuer Raum</div>\n"; 
		$htmlstr .= "<form action='raum_details.php' method='POST' name='raumform'>\n";
		$htmlstr .= "<table class='detail'>\n";


		$htmlstr .= "	<tr><td colspan='3'>&nbsp;</tr>\n";
		$htmlstr .= "	<tr>\n";

		// erste Spalte start
		$htmlstr .= "		<td valign='top'>\n";

		$htmlstr .= "			<table>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Kurzbezeichnung</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='ort_kurzbz' size='12' maxlength='16' value='".$ort_kurzbz."' onchange='submitable()'></td>\n";
		$htmlstr .= "					<td>Bezeichnung</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='bezeichnung' size='32' maxlength='64' value='".$bezeichnung."' onchange='submitable()'></td>\n";
		$htmlstr .= "					<td>Planbezeichnung</td>\n";
		$htmlstr .= " 					<td><input class='detail' type='text' name='planbezeichnung' size='12' maxlength='8' value='".$planbezeichnung."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Max Person</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='max_person' size='12' maxlength='8' value='".$max_person."' onchange='submitable()'></td>\n";
		$htmlstr .= "					<td>Dislozierung</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='dislozierung' size='16' maxlength='8' value='".$dislozierung."' onchange='submitable()'></td>\n";
		$htmlstr .= "					<td>Kosten</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='kosten' size='18' maxlength='16' value='".$kosten."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td>Stockwerk</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='stockwerk' size='8' maxlength='5' value='".$stockwerk."' onchange='submitable()'></td>\n";
		$htmlstr .= "					<td>Standort</td>\n";
		$htmlstr .= "					<td>";
		$htmlstr .= "					<SELECT name='standort_id'>";
		$htmlstr.="				<OPTION value=''>-- keine Auswahl --</OPTION>\n";

		$standort = new standort();
		if($standort->getStandorteWithTyp('Intern'))
		{
			foreach($standort->result as $row)
			{
				if($row->standort_id==$standort_id)
					$selected='selected';
				else 
					$selected='';
			
				$htmlstr.="<OPTION value='$row->standort_id' $selected>$row->kurzbz</OPTION>\n";
			}
		}

		$htmlstr .= "					</SELECT>";
		$htmlstr .= "					</td>\n";
		$htmlstr .= "					<td>Telefonklappe</td>\n";
		$htmlstr .= "					<td><input class='detail' type='text' name='telefonklappe' size='3' maxlength='8' value='".$telefonklappe."' onchange='submitable()'></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td valign='top'>Lehre</td>\n";
		$htmlstr .= " 					<td>\n";
		if($lehre == 't')
		{
			$chk1 = "checked";
		}
		else
		{
			$chk1 = '';
		}
		$htmlstr .= "					<input type='checkbox' name='lehre' value='t'".$chk1." onchange='submitable()'>";
		$htmlstr .= " 					</td>\n";
		$htmlstr .= "					<td valign='top'>Reservieren</td>\n";
		$htmlstr .= " 					<td>\n";
		if($reservieren == 't')
		{
			$chk2 = "checked";
		}
		else
		{
			$chk2 = '';
		}
		$htmlstr .= "					<input type='checkbox' name='reservieren' value='t'".$chk2." onchange='submitable()'>";
		$htmlstr .= " 					</td>\n";
		$htmlstr .= "					<td valign='top'>Aktiv</td>\n";
		$htmlstr .= " 					<td>\n";
		if($aktiv == 't')
		{
			$chk3 = "checked";
		}
		else
		{
			$chk3 = '';
		}
		$htmlstr .= "					<input type='checkbox' name='aktiv' value='t'".$chk3." onchange='submitable()'>";
		$htmlstr .= " 					</td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "				<tr>\n";
		$htmlstr .= "					<td valign='top'>Lageplan</td>\n";
		$htmlstr .= " 					<td><textarea name='lageplan' cols='37' rows='5' onchange='submitable()'>".$lageplan."</textarea></td>\n";
		$htmlstr .= "					<td valign='top'>Ausstattung</td>\n";
		$htmlstr .= " 					<td><textarea name='ausstattung' cols='37' rows='5' onchange='submitable()'>".$ausstattung."</textarea></td>\n";
		$htmlstr .= "					<td valign='top'>ContentID</td>\n";
		$htmlstr .= " 					<td valign='top'><input type='text' name='content_id' size='5' onchange='submitable()' value='".$content_id."' /></td>\n";
		$htmlstr .= "				</tr>\n";
		$htmlstr .= "</table>\n";
		$htmlstr .= "<br>\n";
		$htmlstr .= "<div align='right' id='sub'>\n";
		$htmlstr .= "	<span id='submsg' style='color:red; visibility:hidden;'>Datensatz ge&auml;ndert!&nbsp;&nbsp;</span>\n";
		$htmlstr .= "	<input type='hidden' name='neu' value='".$neu."'>";
		$htmlstr .= "	<input type='submit' value='Speichern' name='schick'>\n";
		$htmlstr .= "	<input type='button' value='Reset' onclick='unchanged()'>\n";
		$htmlstr .= "</div>";
		$htmlstr .= "</form>";
		$htmlstr .= "<div class='inserterror'>".$errorstr."</div>";
	}
?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Raum - Details</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script src="../../include/js/jquery1.9.min.js"></script>

<script type="text/javascript">
function unchanged()
{
		document.raumform.reset();
		document.raumform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
		checkrequired(document.raumform.ort_kurzbz);
}

function checkrequired(feld)
{
	if(feld.value == '')
	{
		feld.className = "input_error";
		return false;
	}
	else
	{
		feld.className = "input_ok";
		return true;
	}
}

function submitable()
{
	required1 = checkrequired(document.raumform.ort_kurzbz);

	if(!required1)
	{
		document.raumform.schick.disabled = true;
		document.getElementById("submsg").style.visibility="hidden";
	}
	else
	{
		document.raumform.schick.disabled = false;
		document.getElementById("submsg").style.visibility="visible";
	}
}
</script>
</head>
<body>

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
