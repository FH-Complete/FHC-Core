<?php
/* Copyright (C) 2014 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/lvangebot.class.php');
require_once('../../../include/benutzergruppe.class.php');
require_once('../../../include/lehrveranstaltung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

if (!$user=get_uid())
	die($p->t('global/nichtAngemeldet'));

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body>
<h1>'.$p->t('lehre/abmeldung').'</h1>';

if(!isset($_GET['lvid']) || !isset($_GET['stsem']))
{
	die('Fehlerhafte Parameterübergabe');
}

$lvid = $_GET['lvid'];
$stsem = $_GET['stsem'];

$lvangebot = new lvangebot();
$gruppen = $lvangebot->AbmeldungMoeglich($lvid, $stsem, $user);
if(count($gruppen)>0)
{
	if(isset($_POST['gruppe']))
	{
		$gruppe = $_POST['gruppe'];
		if(in_array($gruppe, $gruppen))
		{
			$benutzergruppe = new benutzergruppe();
			if($benutzergruppe->delete($user, $gruppe))
			{
				echo $p->t('lehre/AbmeldungErfolgreich');
				// Menuebaum neu Laden damit die LV nicht mehr angezeigt wird
				echo '<script>window.parent.menu.location.reload();</script>';
			}
			else
			{
				echo $benutzergruppe->errormsg;
			}
		}
		else
		{
			echo $p->t('lehre/AbmeldungAusGruppeNichtMoeglich');
		}
	}
	else
	{
		foreach($gruppen as $gruppe)
		{
			$lehrveranstaltung = new lehrveranstaltung();
			$lehrveranstaltung->load($lvid);

			if(defined('CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_LEHRFACH_ANZEIGEN)
			{
				$qry = "SELECT 
						lehrfach_id
					FROM 
						lehre.tbl_lehreinheit 
						JOIN lehre.tbl_lehreinheitgruppe USING(lehreinheit_id)
					WHERE 
						gruppe_kurzbz=".$db->db_add_param($gruppe)."
						AND lehrveranstaltung_id=".$db->db_add_param($lvid)."
						AND studiensemester_kurzbz=".$db->db_add_param($stsem);
				if($result = $db->db_query($qry))
				{
					if($row = $db->db_fetch_object($result))
					{
						$lehrveranstaltung->load($row->lehrfach_id);
					}
				}
			}
			echo '<form action="abmeldung.php?lvid='.$lvid.'&stsem='.$stsem.'" method="POST">';
			echo $p->t('lehre/confirmAbmeldung',array($lehrveranstaltung->bezeichnung));
			echo '<input type="hidden" name="gruppe" value="'.$gruppe.'" />';
			echo '<br><br><input type="Submit" value="Abmelden">';
			echo '</form><br><br>';	
		}
	}
}
else
{
	echo $p->t('lehre/nichtzugeteilt');
}

echo '</body>
</html>';

