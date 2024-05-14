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
 *			Manuela Thamer <manuela.thamer@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/addon.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/sprache.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die('Fehler beim Oeffnen der Datenbankverbindung');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['year']) && is_numeric($_GET['year']))
	$year = $_GET['year'];
else
{
	//Bis August das aktuelle Jahr anzeigen
	//Ab September das naechste
	if (date('m') < 9)
		$year = date('Y');
	else
		$year = date('Y') + 1;
}

if (isset($_GET['uid']))
	$uid = $_GET['uid'];
else
	$uid = '';

$datum_obj = new datum();

echo '<html>
<head>
	<title>Urlaubsfreigabe</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<script type="text/javascript" src="../../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
	';

// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
		echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
<script>
$( document ).ready(function()
{
	for(i in addon)
	{
		addon[i].init("cis/private/profile/urlaubsfreigabe.php", {uid:\''.$uid.'\'});
	}
});
</script>';

echo '
</head>

<body>
<h1>Urlaubsfreigabe</h1>
';
//Untergebene holen
$mitarbeiter = new mitarbeiter();
$mitarbeiter->getUntergebene($user);

if (count($mitarbeiter->untergebene) == 0 && !$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('mitarbeiter/urlaube', null, 'suid'))
	die('Es sind Ihnen keine Mitarbeiter zugeteilt für die sie den Urlaub freigeben dürfen');
$untergebene = '';
foreach ($mitarbeiter->untergebene as $row)
{
	if ($untergebene != '')
		$untergebene .= ',';
	$untergebene .= $db->db_add_param($row);
}

if ($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('mitarbeiter/urlaube', null, 'suid'))
{
	if ($untergebene != '')
			$untergebene .= ',';
	$untergebene .= $db->db_add_param($uid);
}
$qry = "SELECT * FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id) WHERE uid in($untergebene)";

$mitarbeiter = array();
if ($result = $db->db_query($qry))
{
	while ($row = $db->db_fetch_object($result))
	{
		$mitarbeiter[$row->uid]['vorname'] = $row->vorname;
		$mitarbeiter[$row->uid]['nachname'] = $row->nachname;
		$mitarbeiter[$row->uid]['titelpre'] = $row->titelpre;
		$mitarbeiter[$row->uid]['titelpost'] = $row->titelpost;
	}
}
if ($uid != '' && !isset($mitarbeiter[$uid]) && $uid != $user && !$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung fuer diesen Mitarbeiter');

//Freigeben eines Urlaubes
if (isset($_GET['action']) && $_GET['action'] == 'freigabe')
{
	$zeitsperre = new zeitsperre();
	if ($zeitsperre->load($_GET['id']))
	{
		if (isset($mitarbeiter[$zeitsperre->mitarbeiter_uid]))
		{
			$zeitsperre->freigabeamum = date('Y-m-d H:i:s');
			$zeitsperre->freigabevon = $user;
			if (!$zeitsperre->save(false))
			{
				echo "<b>Fehler bei der Freigabe: $zeitsperre->errormsg</b>";
			}

			//Bestätigungsmail an Mitarbeiter*in
			$to = $zeitsperre->mitarbeiter_uid. '@'.DOMAIN;
			$person = new person();
			$fullNameVG = $person->getFullNameFromBenutzer($user);
			$fullNameMA = $person->getFullNameFromBenutzer($zeitsperre->mitarbeiter_uid);
			$from = 'noreply@'.DOMAIN;
			$subject = $p->t('urlaubstool/urlaubsfreigabe'). date("d.m.Y", strtotime($zeitsperre->vondatum)). " ".
			$p->t('urlaubstool/bis'). " ". date("d.m.Y", strtotime($zeitsperre->bisdatum));
			$text = $p->t('urlaubstool/diesIstEineAutomatischeMail')."\n";
			$text .= $p->t('urlaubstool/urlaubVon')." ".date("d.m.Y", strtotime($zeitsperre->vondatum))." ".
			$p->t('urlaubstool/bis')." ".date("d.m.Y", strtotime($zeitsperre->bisdatum));
			$text .= $p->t('urlaubstool/urlaubBis', array($fullNameVG));
			$text .= "\n". "\n".  $p->t('urlaubstool/sieKoennenDiesenUnterFolgenderAdresseEinsehen');
			$text .= "\n". APP_ROOT. 'cis/private/profile/urlaubstool.php';

			$mail = new mail($to, $from, $subject, $text);

			if ($mail->send())
			{
				echo "<span style='color:green;'>".$p->t('urlaubstool/bestaetigungsmailWurdeVersandt', array($fullNameMA))."</span>";
			}
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

//Monat zeichnen
function draw_monat($monat)
{
	global $untergebene, $mitarbeiter, $year, $datum_obj, $uid;

	if (!$db = new basis_db())
		die('Fehler beim Oeffnen der Datenbankverbindung');

	echo '<td style="border: 1px solid black; height:100px; width: 30%" valign="top">';
	echo '<center><b>';
	echo date('F', mktime(0,0,0,$monat,1,date('Y')));
	echo " ".($monat > 8?$year-1:$year);
	echo '</b></center>';
	//Alle Anzeigen bei denen das von- oder bisdatum in dieses monat fallen
	$qry = "SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub'
			AND
			(
				(date_part('month', vondatum)='$monat' AND date_part('year', vondatum)='".($monat > 8?$year - 1:$year)."')
				OR
				(date_part('month', bisdatum)='$monat' AND date_part('year', bisdatum)='".($monat > 8?$year - 1:$year)."')
			)";
	if($uid == '')
		$qry.= " AND mitarbeiter_uid in($untergebene)";
	else
		$qry.= " AND mitarbeiter_uid=". $db->db_add_param($uid);
	$qry.="ORDER BY vondatum, mitarbeiter_uid";

	if ($result = $db->db_query($qry))
	{
		while ($row = $db->db_fetch_object($result))
		{
			$vertretung = new benutzer($row->vertretung_uid);
			$freigabe='';
			if($row->freigabeamum!='')
			{
				$freigabe = "Freigabe am ".$datum_obj->formatDatum($row->freigabeamum, 'd.m.Y')." von Benutzer $row->freigabevon";
			}
			echo "<span title='$freigabe'>";
			echo $mitarbeiter[$row->mitarbeiter_uid]['nachname'].' '.$datum_obj->formatDatum($row->vondatum,'d.m.Y')." - ".$datum_obj->formatDatum($row->bisdatum,'d.m.Y');
			if ($vertretung->uid != '')
				echo ' (Vertretung: '.$vertretung->nachname.')';
			if($row->freigabeamum=='')
				echo " <a href='".$_SERVER['PHP_SELF']."?action=freigabe&id=$row->zeitsperre_id&year=$year&uid=$uid' class='Item'>Freigabe</a>";
			echo "</span>";
			echo '<br>';
		}
	}
	echo '</td>';
}

//Jahr mit Pfeilen zum blaettern anzeigen
if ($uid!='')
{
	echo '<table width="100%"><tr><td style="width:33%">';
	echo "<a href='".$_SERVER['PHP_SELF']."?year=$year' class='Item'>Alle Mitarbeiter anzeigen</a><br></td>";
	echo '<td style="width:33%">';
	echo '</td><td style="width:33%">';

	echo '<div id="resturlaub">';
	echo '</div>';

	echo '</td></tr></table>';
}

echo '<br><center>';
echo "<a href='".$_SERVER['PHP_SELF']."?uid=$uid&year=".($year-1)."' class='Item' title='Ein Jahr zurück'><img src='../../../skin/images/left.gif'></a>";
echo '&nbsp;<font size="+1"><b>';
echo ($year-1).'/'.$year;
echo '</b></font>&nbsp;';
echo "<a href='".$_SERVER['PHP_SELF']."?uid=$uid&year=".($year+1)."' class='Item' title='Ein Jahr vor'><img src='../../../skin/images/right.gif'></a>";
echo '</center>';

echo '<br>';
//Tabelle mit den Monaten ausgeben
echo '<table cellspacing=0 width="100%" style="border: 1px solid black;"><tr>';
$monat=9;
for($i = 0;$i < 12;$i++)
{
	if ($i%3 == 0)
	{
		echo '</tr><tr>';
	}
	draw_monat($monat);
	$monat++;
	if ($monat > 12)
		$monat = 1;
}
echo '</tr></table>

</body>
</html>';
?>
