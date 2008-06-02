<?php
/* Copyright (C) 2008 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 */

require_once('../../config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/zeitsperre.class.php');

if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim Connecten zur Datenbank');

$user = get_uid();

if(isset($_GET['year']) && is_numeric($_GET['year']))
	$year = $_GET['year'];
else 
{
	//Bis August das aktuelle Jahr anzeigen
	//Ab September das naechste
	if(date('m')<9)
		$year = date('Y');
	else 
		$year = date('Y')+1;
}

if(isset($_GET['uid']))
	$uid=$_GET['uid'];
else 
	$uid='';

$datum_obj = new datum();

echo '<html>
<head>
	<title>Urlaubsfreigabe</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>

<body id="inhalt">
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;Urlaubsfreigabe</font></td>
	  </tr>
	</table>
	<br>
';
//Untergebene holen
$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE (funktion_kurzbz='fbl' OR funktion_kurzbz='stgl') AND uid='".addslashes($user)."'";

if($result = pg_query($conn, $qry))
{
	$institut='';
	$stge='';
	while($row = pg_fetch_object($result))
	{
		if($row->funktion_kurzbz=='fbl')
		{
			if($institut!='')
				$institut.=',';
			
			$institut.="'".addslashes($row->fachbereich_kurzbz)."'";
		}
		elseif($row->funktion_kurzbz=='stgl')
		{
			if($stge!='')
				$stge.=',';
			$stge.="'".$row->studiengang_kz."'";
		}
			
	}
}

$qry = "SELECT distinct uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='Institut' AND (false ";

if($institut!='')
	$qry.=" OR fachbereich_kurzbz in($institut)"; 
if($stge!='')
	$qry.=" OR studiengang_kz in($stge)";

$qry.=")";

$untergebene='';
if($result = pg_query($conn, $qry))
{
	
	
	while($row = pg_fetch_object($result))
	{
		if($untergebene!='')
			$untergebene.=',';
		$untergebene.="'".addslashes($row->uid)."'";
	}
}

if($untergebene=='')
	die('Es sind Ihnen keine Mitarbeiter zugeteilt für die sie den Urlaub freigeben dürfen');

$qry = "SELECT * FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid in($untergebene)";

$mitarbeiter = array();
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$mitarbeiter[$row->uid]['vorname']=$row->vorname;
		$mitarbeiter[$row->uid]['nachname']=$row->nachname;
		$mitarbeiter[$row->uid]['titelpre']=$row->titelpre;
		$mitarbeiter[$row->uid]['titelpost']=$row->titelpost;
	}
}
if($uid!='' && !isset($mitarbeiter[$uid]) && $uid!=$user)
	die('Sie haben keine Berechtigung fuer diesen Mitarbeiter');

//Freigeben eines Urlaubes
if(isset($_GET['action']) && $_GET['action']=='freigabe')
{
	$zeitsperre = new zeitsperre($conn);
	if($zeitsperre->load($_GET['id']))
	{
		if(isset($mitarbeiter[$zeitsperre->mitarbeiter_uid]))
		{
			$zeitsperre->freigabeamum = date('Y-m-d H:i:s');
			$zeitsperre->freigabevon = $user;
			if(!$zeitsperre->save(false))
				echo "<b>Fehler bei der Freigabe: $zeitsperre->errormsg</b>";
		}
		else 
		{
			echo '<b>Sie haben keine Berechtigung den Urlaub für diesen Mitarbeiter freizugeben</b>';
		}
	}
	else 
	{
		echo '<b>Die Zeitsperre konnte nicht geladen werden</b>';
	}
	
}

//Monat zeichenen
function draw_monat($monat)
{
	global $untergebene, $mitarbeiter, $conn, $year, $datum_obj, $uid;
	
	echo '<td style="border: 1px solid black; height:100px; width: 30%" valign="top">';
	echo '<center><b>';
	echo date('F',mktime(0,0,0,$monat,1,date('Y')));
	echo '</b></center>';
	//Alle Anzeigen bei denen das von- oder bisdatum in dieses monat fallen
	$qry = "SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' 
			AND 
			(
				(date_part('month', vondatum)='$monat' AND date_part('year', vondatum)='".($monat>8?$year-1:$year)."') 
				OR 
				(date_part('month', bisdatum)='$monat' AND date_part('year', bisdatum)='".($monat>8?$year-1:$year)."')
			)";
	if($uid=='')
		$qry.=" AND mitarbeiter_uid in($untergebene)";
	else 
		$qry.=" AND mitarbeiter_uid='".addslashes($uid)."'";
	$qry.="ORDER BY vondatum, mitarbeiter_uid";
	
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			echo $mitarbeiter[$row->mitarbeiter_uid]['nachname'].' '.$datum_obj->formatDatum($row->vondatum,'d.m.Y')." - ".$datum_obj->formatDatum($row->bisdatum,'d.m.Y');
			if($row->freigabeamum=='')
				echo " <a href='".$_SERVER['PHP_SELF']."?action=freigabe&id=$row->zeitsperre_id&year=$year&uid=$uid' class='Item'>Freigabe</a>";
				
			echo '<br>';
		}
	}
	echo '</td>';
}

//Jahr mit Pfeilen zum blaettern anzeigen
echo '<center>';
echo "<a href='".$_SERVER['PHP_SELF']."?uid=$uid&year=".($year-1)."' class='Item' title='Ein Jahr zurück'><img src='../../../skin/images/left.gif'></a>";
echo '&nbsp;<font size="+1"><b>';
echo ($year-1).'/'.$year;
echo '</b></font>&nbsp;';
echo "<a href='".$_SERVER['PHP_SELF']."?uid=$uid&year=".($year+1)."' class='Item' title='Ein Jahr vor'><img src='../../../skin/images/right.gif'></a>";
echo '</center>';

if($uid!='')
{
	echo "<a href='".$_SERVER['PHP_SELF']."?year=$year' class='Item'>Alle Mitarbeiter anzeigen</a><br>";
}
echo '<br>';
//Tabelle mit den Monaten ausgeben
echo '<table cellspacing=0 width="100%" style="border: 1px solid black;"><tr>';
$monat=9;
for($i=0;$i<12;$i++)
{
	if($i%3==0)
	{
		echo '</tr><tr>';
	}
	draw_monat($monat);
	$monat++;
	if($monat>12)
		$monat=1;
}
echo '</tr></table>

	</td>
	</tr>
	</table>
</body>
</html>';
?>