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
 * Authors: Martin Tatzber < tatzberm@technikum-wien.at >
 */
	require_once('../../config/vilesci.config.inc.php');
	require_once('../../include/functions.inc.php');
	require_once('../../include/lvangebot.class.php');
	require_once('../../include/studiensemester.class.php');
	require_once('../../include/datum.class.php');
	require_once('../../include/benutzerberechtigung.class.php');
	require_once('../../include/studiengang.class.php');
	require_once('../../include/lehrveranstaltung.class.php');

	if (!$db = new basis_db())
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
	
	$user = get_uid();
	$reloadstr = '';  // neuladen der liste im oberen frame
	$errorstr='';
	$htmlstr='';
	$datum_obj = new datum();
	
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	$write_admin=false;
	$write_low=false;
	
	$lvangebot_id = (isset($_REQUEST['lvangebot_id'])?$_REQUEST['lvangebot_id']:'-1');
	$lv_id = (isset($_REQUEST['lehrveranstaltung_id'])?$_REQUEST['lehrveranstaltung_id']:'-1');
	
	if (isset($_GET['action']))
		$action=$_GET['action'];
	else if(isset($_POST['neu']))
		$action='neu';
	else
		$action='';
	
	//wenn eine lvangebot_id mitgegeben wurde, wird der entsprechende Eintrag geladen
	$lvangebot = new lvangebot();
	if($lvangebot_id != '-1' && $action != 'neu')
	{
		if (!$lvangebot->load($lvangebot_id))
//			$htmlstr .= "<br><div class='kopf'>LV-Angebot <b>".$lvangebot_id."</b> konnte nicht geladen werden!</div>";
			die('LV-Angebot '.$lvangebot_id.' konnte nicht geladen werden!');
		else
		{
			$new=false;
			$lv_id=$lvangebot->lehrveranstaltung_id;
		}
	}
	else
		$new=true;
	
	$lv_obj = new lehrveranstaltung();
	$lv_obj->load($lv_id);
	$stg_obj = new studiengang();
	$stg_obj->load($lv_obj->studiengang_kz);
	$oe_studiengang = $stg_obj->oe_kurzbz;
	if($rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 'suid'))
		$write_admin=true;
//	if($rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_studiengang, 'suid'))
//		$write_low=true;
	
	
	if($action=='delete')
	{
		if($write_admin)
		{
			if(!$lvangebot->delete($lvangebot_id))
				$errorstr=$this->errormsg;
			else
				//reset, damit Daten nicht noch einmal ins Formular übernommen werden
				$lvangebot=new lvangebot();
		}
		else
			$errorstr='keine Berechtigung zum Löschen aus LV-Angebot';
	}
	
	if(isset($_POST["schick"]))
	{
		if($write_admin)
		{
			if($new)
			{
				$lvangebot->new=true;
				$lvangebot->insertamum=date('Y-m-d H:i:s');
				$lvangebot->insertvon=$user;
			}
			else
			{
				$lvangebot->new=false;
				$lvangebot->updatenamum=date('Y-m-d H:i:s');
				$lvangebot->updatevon=$user;
			}

			$lvangebot->lehrveranstaltung_id=$_POST['lehrveranstaltung_id'];
			$lvangebot->studiensemester_kurzbz=$_POST['studiensemester_kurzbz'];
			//$lvangebot->gruppe_kurzbz=$_POST['gruppe_kurzbz'];
			$lvangebot->incomingplaetze=$_POST['incomingplaetze'];
			$lvangebot->gesamtplaetze=$_POST['gesamtplaetze'];
			$lvangebot->anmeldefenster_start=$datum_obj->formatDatum($_POST['anmeldefenster_start'], 'Y-m-d');
			$lvangebot->anmeldefenster_ende=$datum_obj->formatDatum($_POST['anmeldefenster_ende'],'Y-m-d');

			if(!$lvangebot->save())
				$errorstr = $lvangebot->errormsg;
		}
		else
			$errorstr = 'keine Berechtigung zum Speichern in LV-Angebot';
	}

	$htmlstr .= '<br><div class="kopf">LV-Angebot für Lehrveranstaltung '.$lv_id.'</div>
		<form action="lehrveranstaltung_lvangebot.php" method="POST">';
	if($action!='neu')
		$htmlstr .= '<input type="hidden" name="lvangebot_id" value="'.$lvangebot_id.'">';
	$htmlstr .= '<input type="hidden" name="lehrveranstaltung_id" value="'.$lv_id.'">

			<table class="detail" style="padding-top:10px;">
				<tr><td valign="top">
					<table class="tablesorter" id="t1">
					<thead>
						<tr>
							<th>Studiensemester</th>
							<th title="Incomingplätze">Inc</th>
							<th title="Gesamtplätze">Ges</th>
							<th>Anmeldefenster Start</th>
							<th>Anmeldefenster Ende</th>
							<th></th>
							<th></th>
						</tr>
					</thead>
					<tbody>';
	
	$lvangebot->getAllFromLvId($lv_id);
	foreach($lvangebot->result as $lvang)
	{
/*		if($lvangebot->lvangebot_id==$lvang->lvangebot_id)
		{
			$bstart='<b>';$bende='</b>';
		}
		else
		{
			$bstart='';$bende='';
		} */
		$htmlstr .= '<tr>
			<td>'.$lvang->studiensemester_kurzbz.'</td>
			<td>'.$lvang->incomingplaetze.'</td>
			<td>'.$lvang->gesamtplaetze.'</td>
			<td>'.$datum_obj->formatDatum($lvang->anmeldefenster_start,'d.m.Y').'</td>
			<td>'.$datum_obj->formatDatum($lvang->anmeldefenster_ende,'d.m.Y').'</td>
			<td><a href='.$_SERVER["PHP_SELF"].'?action=edit&lvangebot_id='.$lvang->lvangebot_id.'>Edit</a></td>
			<td><a href='.$_SERVER["PHP_SELF"].'?action=delete&lvangebot_id='.$lvang->lvangebot_id.' onclick="return confdel()">Delete</a></td>
			</tr>';
	}
	$htmlstr .= '</table>
				<input type="submit" name="neu" value="Neu" />
					</td>
					<td valign="top">';
	if($action == 'neu' || $action == 'edit')
	{
		if($action == 'edit')
			$disableDropdown=true;
		else
			$disableDropdown=false;
		$htmlstr .= '		<table>
								<tr>
								<td>Studiensemester</td>
								<td><select name="studiensemester_kurzbz" '.($disableDropdown?'disabled="disabled':'onchange="submitable()"').'>';
		$stsem_arr=new studiensemester();
		$stsem_arr->getAll();
		foreach($stsem_arr->studiensemester as $stsem)
		{
			if($lvangebot->studiensemester_kurzbz==$stsem->studiensemester_kurzbz)
				$selected='selected';
			else
				$selected='';
			$htmlstr .= '<option value="'.$stsem->studiensemester_kurzbz.'" '.$selected.'>'.$stsem->studiensemester_kurzbz.'</option>';
		}
		$htmlstr .= '</select>';
		if($disableDropdown)
			$htmlstr .= '<input type="hidden" name="studiensemester_kurzbz" value="'.$lvangebot->studiensemester_kurzbz.'" />';
		$htmlstr .= '			</td>
								</tr>
								<tr><td>Incomingplätze</td>
								<td>
									<input type="text" name="incomingplaetze" onchange="submitable()" value="'.$lvangebot->incomingplaetze.'"/>
								</td></tr>
								<tr><td>Gesamtplätze</td>
								<td>
									<input type="text" name="gesamtplaetze" onchange="submitable()" value="'.$lvangebot->gesamtplaetze.'"/>
								</td></tr>
								<tr><td>Anmeldefenster Start</td>
								<td>
									<input id="anmeldefenster_start" type="text" name="anmeldefenster_start" onchange="submitable()" value="'.$datum_obj->formatDatum($lvangebot->anmeldefenster_start,'d.m.Y').'"/>
								</td></tr>
								<tr><td>Anmeldefenster Ende</td>
								<td>
									<input id="anmeldefenster_ende" type="text" name="anmeldefenster_ende" onchange="submitable()" value="'.$datum_obj->formatDatum($lvangebot->anmeldefenster_ende,'d.m.Y').'"/>
								</td></tr>
								<tr><td><span id="submsg" style="color:red; visibility:hidden;">Datensatz ge&auml;ndert!&nbsp;&nbsp;</span></td>
								<td><input type="submit" name="schick" value="Speichern" /></td></tr>
							</tbody>
							</table>';
	}
	$htmlstr .= '	</td>
				</tr>
			</table>
		</form>';

	
	$htmlstr .= "<div class='inserterror'>".$errorstr."</div>\n";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Lehrveranstaltung - Details</title>
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<script type="text/javascript" src="../../include/js/mailcheck.js"></script>
	<script type="text/javascript" src="../../include/js/datecheck.js"></script>
	<script type="text/javascript" src="../../include/js/jquery1.9.min.js"></script>	
	<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css"/>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script type="text/javascript">
		$(function() {
			$("#anmeldefenster_start,#anmeldefenster_ende").datepicker();
		});
		
		$(document).ready(function() 
		{ 
			$("#t1").tablesorter(
			{
				widgets: ["zebra"]
			}); 
		}); 
		
		function submitable()
		{
			document.getElementById("submsg").style.visibility="visible";
		}
		
		function confdel()
		{
			return confirm("Wollen Sie diesen Eintrag wirklich löschen?");
		}
	</script>
</head>
<body style="background-color:#eeeeee;">

<?php
	echo $htmlstr;
	echo $reloadstr;
?>

</body>
</html>
