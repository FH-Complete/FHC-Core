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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */
/*
 * GUI fuer die FreeBusy Verwaltung
 *
 * Mit diesem Tool koennen FreeBusy URLs aus verschiedenen Quellen zu einer
 * FreeBusy URL zusammengefasst werden
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/freebusy.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/benutzerberechtigung.class.php');

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();
$sprache = getSprache();
$p = new phrasen($sprache);

$action = (isset($_REQUEST['action'])?$_REQUEST['action']:'');
$id = (isset($_REQUEST['id'])?$_REQUEST['id']:'');

// Administratoren duerfen die UID als Parameter uebergeben um die Umfragen von anderen Personen anzuzeigen
if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($user);
	if($rechte->isBerechtigt('admin'))
	{
		$user = $_GET['uid'];
	}
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/jquery.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<title>'.$p->t('freebusy/titel').'</title>

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
			sortList: [[0,1]],
			widgets: [\'zebra\']
		});
	});

	function seturl()
	{
		url = $("#typ option:selected").attr("url");
		$("#url").val(url);
	}
	</script>
</head>
<body>
<h1>'.$p->t('freebusy/titel').'</h1>';

echo $p->t('freebusy/beschreibungstext1').'<br />';
echo '<br /><span style="font-weight:bold">'.$p->t('freebusy/PfadZuFreebusyUrl').'</span> '.APP_ROOT.'cis/public/freebusy.php/'.$user;

echo '<br /><br />';
if($action=='delete')
{
	//Loeschen von Eintraegen
	$fb = new freebusy();
	if($fb->load($id))
	{
		//Nur Loeschen wenn es der eigene Eintrag ist
		if($fb->uid==$user)
		{
			if(!$fb->delete($id))
				echo '<span class="error">'.$p->t('global/fehlerBeimLoeschenDesEintrags').'</span>';
			else
				echo '<span class="ok">'.$p->t('global/eintragWurdeGeloescht').'</span>';
		}
		else
		{
			die($p->t('global/keineBerechtigungZumAendernDesDatensatzes'));
		}
	}
	else
		echo '<span class="error">'.$p->t('global/fehlerBeimLadenDesDatensatzes').'</span>';
}
elseif($action=='save')
{
	//Speichern von Eintraegen

	$id = (isset($_POST['id'])?$_POST['id']:'');
	$aktiv = isset($_POST['aktiv']);

	if(isset($_POST['bezeichnung']))
		$bezeichnung = $_POST['bezeichnung'];
	else
		die($p->t('global/fehlerBeiDerParameteruebergabe'));

	if(isset($_POST['typ']))
		$typ = $_POST['typ'];
	else
		die($p->t('global/fehlerBeiDerParameteruebergabe'));

	if(isset($_POST['url']))
		$url = $_POST['url'];
	else
		die($p->t('global/fehlerBeiDerParameteruebergabe'));

	//Pruefen ob die URL geoeffnet werden kann
	$fp = @fopen($url,'r');
	if (!$fp)
		echo '<span class="error">'.$p->t('global/fehleraufgetreten').' '.$p->t('freebusy/urlKannNichtGeladenWerden').'</span>';
	else
	{
		fclose($fp);

		$fb = new freebusy();
		if($id!='')
		{
			if(!$fb->load($id))
				die($p->t('global/fehleraufgetreten'));
			if($fb->uid!=$user)
				die($p->t('global/keineBerechtigungZumAendernDesDatensatzes'));


			$fb->new=false;
		}
		else
		{
			$fb->new=true;
			$fb->insertamum = date('Y-m-d H:i:s');
			$fb->insertvon = $user;
			$fb->uid = $user;
		}

		$fb->updateamum = date('Y-m-d H:i:s');
		$fb->updatevon = $user;
		$fb->bezeichnung = $bezeichnung;
		$fb->url = $url;
		$fb->freebusytyp_kurzbz = $typ;
		$fb->aktiv = $aktiv;

		if($fb->save())
			echo '<span class="ok">'.$p->t('global/erfolgreichgespeichert').'</span>';
		else
			echo '<span class="error">'.$p->t('global/fehleraufgetreten').'</span>';
	}

}

//Tabelle mit den vorhandenen Eintraegen anzeigen
$fb = new freebusy();
$fb->getFreeBusy($user);

echo '<table id="myTable" class="tablesorter">
	<thead>
	<tr>
		<th>'.$p->t('global/bezeichnung').'</th>
		<th>'.$p->t('freebusy/typ').'</th>
		<th>'.$p->t('freebusy/url').'</th>
		<th>'.$p->t('freebusy/aktiv').'</th>
		<th colspan="2">'.$p->t('global/aktion').'</th>
	</tr>
	</thead>
	<tbody>';

	echo '<tr>';
	echo '<td>'.$p->t('freebusy/LVPlanBezeichnung').'</td>';
	echo '<td>'.$p->t('freebusy/LVPlanTyp').'</td>';
	echo '<td></td>';
	echo '<td>'.$p->t('global/ja').'</td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '</tr>';

	//zeitsperren
	echo '<tr>';
	echo '<td>'.$p->t('freebusy/ZeitsperrenBezeichnung').'</td>';
	echo '<td>'.$p->t('freebusy/ZeitsperrenTyp').'</td>';
	echo '<td></td>';
	echo '<td>'.$p->t('global/ja').'</td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '</tr>';

foreach($fb->result as $row)
{
	$typ = new freebusy();
	$typ->loadTyp($row->freebusytyp_kurzbz);
	echo '<tr>';
	echo '<td>'.$db->convert_html_chars($row->bezeichnung).'</td>';
	echo '<td>'.$db->convert_html_chars($typ->bezeichnung).'</td>';
	echo '<td>'.$db->convert_html_chars($row->url).'</td>';
	echo '<td>'.($row->aktiv?$p->t('global/ja'):$p->t('global/nein')).'</td>';
	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?action=edit&id='.$row->freebusy_id.'">'.$p->t('global/bearbeiten').'</a></td>';
	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?action=delete&id='.$row->freebusy_id.'">'.$p->t('global/löschen').'</a></td>';
	echo '</tr>';
}
echo '</tbody>
</table>';
echo '<a href="'.$_SERVER['PHP_SELF'].'?action=neu">'.$p->t('freebusy/neuerEintrag').'</a>';

//Formular zum Anlegen und Editieren anzeigen
if($action=='edit' || $action=='neu')
{
	$fb = new freebusy();

	if($action=='neu')
	{
		$new = true;
		echo '<hr><h3>'.$p->t('global/neu').'</h3>';
	}
	else
	{
		$new=false;
		echo '<hr><h3>'.$p->t('global/editieren').'</h3>';
		if(!$fb->load($id))
			die($p->t('global/fehlerBeimLadenDesDatensatzes'));
	}

	echo '<form action="'.$_SERVER['PHP_SELF'].'?action=save" method="POST">';
	echo '<input type="hidden" name="id" value="'.$fb->freebusy_id.'" />';
	echo '<table>
		<tr>
			<th>'.$p->t('global/bezeichnung').'</th>
			<th>'.$p->t('freebusy/typ').'</th>
			<th>'.$p->t('freebusy/url').'</th>
			<th>'.$p->t('freebusy/aktiv').'</th>
		</tr>
		<tr>
			<td><input type="text" name="bezeichnung" size="20" maxlength="256" value="'.$db->convert_html_chars($fb->bezeichnung).'"/></td>
			<td>
				<select name="typ" id="typ" onchange="seturl()">';
	echo '<OPTION value="" >-- '.$p->t('global/auswahl').' --</OPTION>';
	$fbtyp = new freebusy();
	$fbtyp->getTyp();
	foreach($fbtyp->result as $row)
	{
		if($row->freebusytyp_kurzbz==$fb->freebusytyp_kurzbz)
			$selected='selected';
		else
			$selected='';

		$vorlage = mb_str_replace('$uid',$user, $row->url_vorlage);
		echo '<OPTION value="'.$db->convert_html_chars($row->freebusytyp_kurzbz).'" '.$selected.' url="'.$db->convert_html_chars($vorlage).'">'.$db->convert_html_chars($row->bezeichnung),'</OPTION>';
	}
	echo '
				</select>
			</td>
			<td><input type="text" id="url" name="url" size="60" maxlength="1024" value="'.$db->convert_html_chars($fb->url).'"/></td>
			<td><input type="checkbox" name="aktiv" '.($fb->aktiv?'checked="checked"':'').' /></td>
			<td><input type="submit" value="'.$p->t('global/speichern').'" /></td>
		</tr>
		</table>';
	echo '</form>';
}
?>
</body>
</html>
