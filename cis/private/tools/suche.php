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
 * Authors:	Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 * 			Manfred Kindl			<manfred.kindl@technikum-wien.at>
 */
/**
 * Globale Suche
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../config/global.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/content.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/dms.class.php');
require_once('../../../include/service.class.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/organisationseinheit.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/benutzerfunktion.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/kontakt.class.php');
require_once('../../../include/bisverwendung.class.php');
require_once('../../../include/webservicelog.class.php');

$uid = get_uid();
$db = new basis_db();
$sprache = getSprache();
$p = new phrasen($sprache);

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
		"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/tablesort.css" type="text/css"/>
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
	<link rel="stylesheet" type="text/css" href="../../../skin/jquery-ui-1.9.2.custom.min.css">';

	include('../../../include/meta/jquery.php');
	include('../../../include/meta/jquery-tablesorter.php');

echo '<title>Globale Suche</title>
</head>
<body>';

echo '<h1>',$p->t('tools/suche'),'</h1>';

$search = (isset($_REQUEST['search'])?$_REQUEST['search']:'');

// Uses htmlspecialchars to avoid XSS issues
$self = htmlspecialchars($_SERVER["PHP_SELF"], ENT_QUOTES, "utf-8");

echo '<form action="',$self,'" name="searchform" method="GET">
	<input type="search" placeholder="'.$p->t('tools/suchbegriff').' ..." size="40" name="search" value="',$db->convert_html_chars($search),'" />
	<img src="../../../skin/images/search.png" onclick="document.searchform.submit()" height="15px" class="suchicon"/>
	</form><br>';

if($search == '')
	exit;

// String aufsplitten und Sonderzeichen entfernen
$searchItems = explode(' ',TRIM(str_replace(',', '', $search),'!.?'));

// Leerzeichen und Whitespaces entfernen
$searchItems = preg_replace("/\s/", '', $searchItems);

// Leere Strings aus Array entfernen
while ($array_key = array_search("", $searchItems))
	unset ($searchItems[$array_key]);

// Wenn nach dem TRIM keine Zeichen uebrig bleiben, dann abbrechen
if(implode(',', $searchItems) == '')
	exit;

// Legt einen Logeintrag für die Suchstatistik an
if (defined('LOG_CONTENT') && LOG_CONTENT==true)
{
	$log = new webservicelog();

	$log->webservicetyp_kurzbz = 'content';
	$log->request_id = '';
	$log->beschreibung = 'suche';
	$log->request_data = implode(';',$searchItems);
	$log->execute_user = $uid;

	$log->save(true);
}

//Easter Egg
$easteregg = array ('antwort','leben','universum','rest','answer','universe','life','everything');
$easteregg_intersect = array_intersect(array_map('strtolower',$searchItems), $easteregg);
if (count($easteregg_intersect)==3)
{
	echo '<table width="100%"><tr><td align="center"><br><br><br><p style="align:center; font-size: 2000%;"><strong>42</strong></p></td></tr></table>';
	exit;
}

$searchPerson = searchPerson($searchItems);
$searchOE = searchOE($searchItems);
$searchOrt = searchOrt($search);
$searchDms = searchDms($searchItems);
$searchContent = searchContent($searchItems);

if (!$searchPerson && !$searchOrt && !$searchDms && !$searchContent && !$searchOE)
	echo $p->t('tools/esWurdenKeineErgebnisseGefunden');


function searchPerson($searchItems)
{
	global $db, $p, $noalias, $uid;
	$bn = new benutzer();
	//search only active and Mitarbeiter with positive Personalnr
	$bn->search($searchItems, 21, true, true);

	if(count($bn->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('global/personen'),'</h2>';
		echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
			    $("#personentable").tablesorter(
				{
					sortList: [[3,0],[1,0],[0,0]],
					widgets: [\'zebra\'],
					headers: {8:{sorter:false}}
				});
			}
		);
		</script>';
		if(count($bn->result)>20)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXPersonenGefunden").'</p>';
		}
		echo '<table class="tablesorter" id="personentable">
			<thead>
				<tr>
					<th>',$p->t('global/anrede'),'</th>
					<th>',$p->t('global/vorname'),'</th>
					<th>',$p->t('global/nachname'),'</th>
					<th>',$p->t('global/studiengang'),'</th>
					<th>',$p->t('freebusy/typ'),'</th>
					<th>',$p->t('global/telefonnummer'),'</th>
					<th>',$p->t('lvplan/raum'),'</th>
					<th>',$p->t('global/mail'),'</th>';

			if(!defined('CIS_SUCHE_LVPLAN_ANZEIGEN') || CIS_SUCHE_LVPLAN_ANZEIGEN)
				echo '<th>',$p->t('lvplan/lvPlan'),'</th>';
			echo '
				</tr>
			</thead>
			<tbody>
			';
		foreach($bn->result as $row)
		{
			$bisverwendung = new bisverwendung();
			$bisverwendung->getLastAktVerwendung($row->uid);

			echo '<tr>';
			//echo '<td>',$row->titelpre,'</td>';
			echo '<td>',$row->anrede,'</td>';

			if ($row->wahlname)
				echo '<td>',$row->wahlname, ' ' ,$row->vorname, '</td>';
			else
					echo '<td>',$row->vorname, '</td>';

			echo '<td>';
			if(!defined('CIS_SUCHE_PROFIL_ANZEIGEN'))
				echo '<a href="../profile/index.php?uid=',$row->uid,'" title="',$row->titelpre,' ',$row->vorname,' ',$row->wahlname, ' ',$row->nachname,' ',$row->titelpost,'">',$row->nachname,'</a>';
			else if(!CIS_SUCHE_PROFIL_ANZEIGEN)
			{
				$mitarbeiter = new Mitarbeiter($uid);
				if($mitarbeiter->errormsg === NULL)
					echo '<a href="../profile/index.php?uid=',$row->uid,'" title="',$row->titelpre,' ',$row->vorname,' ',$row->wahlname, ' ',$row->nachname,' ',$row->titelpost,'">',$row->nachname,'</a>';
				else
					echo $row->nachname;
			}
			else
				echo '<a href="../profile/index.php?uid=',$row->uid,'" title="',$row->titelpre,' ',$row->vorname,' ',$row->wahlname,' ',$row->nachname,' ',$row->titelpost,'">',$row->nachname,'</a>';
			if($row->aktiv==false)
				echo '<span style="color: red"> (ausgeschieden)</span>';
			elseif($bisverwendung->beschausmasscode=='5')
				echo '<span style="color: orange"> (karenziert)</span>';
			echo '</td>';
			//echo '<td>',$row->titelpost,'</td>';
			echo '<td>',($row->studiengang!=''?$row->studiengang:'-'),'</td>';
			echo '<td>',($row->mitarbeiter_uid==NULL ? $p->t('personensuche/studentIn') : ($row->fixangestellt=='t' ? $p->t('personensuche/mitarbeiterIn') : $p->t('personensuche/mitarbeiterInExtern'))),'</td>';
			// Display phone number
			echo '<td>';
			// * if user has telefonklappe, display it
			if ($row->telefonklappe != '')
			{
				 echo $row->telefonklappe;
			}
			// * if user has no telefonklappe, display business handy
			else
			{
				$person = new Person();
				$person->getPersonFromBenutzer($row->uid);
				$person_id = $person->person_id;
				$kontakt = new Kontakt();

				if ($kontakt->load_persKontakttyp($person_id, 'firmenhandy'))
				{
					$is_zugestellt = false;
					foreach ($kontakt->result as $kontakt)
					{
						// display business handy only if zustellung is true
						if ($kontakt->zustellung)
						{
							echo $kontakt->kontakt. "<br>";
							$is_zugestellt = true;
						}
					}
					// if zustellung is false display '-'
					if (!$is_zugestellt)
					{
						echo '-';
					}
				}
				// * if neither telefonklappe nor business handy, display '-'
				else
				{
					echo '-';
				}
			}
			echo '</td>';
			echo '<td>',($row->raum!=''?$row->raum:'-'),'</td>';
			if($row->alias!='' && !in_array($row->studiengang_kz, $noalias))
				$mail = $row->alias.'@'.DOMAIN;
			else
				$mail = $row->uid.'@'.DOMAIN;
			echo '<td><a href="mailto:',$mail,'">',$mail,'</a></td>';
			if(!defined('CIS_SUCHE_LVPLAN_ANZEIGEN') || CIS_SUCHE_LVPLAN_ANZEIGEN)
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?pers_uid='.$row->uid.($row->mitarbeiter_uid==NULL?'&type=student':'&type=lektor').'">'.$p->t('lvplan/lvPlan').'</a></td>';
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table><br>';
		return true;
	}
	else
		return false;
}
function searchOE($searchItems)
{
	global $db, $p, $noalias;

	//Suche nach Studiengaengen mit dem Suchbegriff und merge mit $searchItems
	$stg_oe_array = array();

	$oe = new organisationseinheit();
	$oe->search($searchItems);

	$stg = new studiengang();
	$stg->search($searchItems);
	foreach($stg->result as $row)
	{
		if($row->aktiv===true)
			$oe->result[] = new organisationseinheit($row->oe_kurzbz);
	}

	if(is_array($oe->result) && count($oe->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('global/organisationseinheiten'),'</h2>';
		echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
				$(".tablesorter").each(function(i,v)
				{
					$("#"+v.id).tablesorter(
					{
						sortList: [[2,0],[1,0],[0,0]],
						widgets: [\'zebra\'],
						headers: {8:{sorter:false}}
					})
				});
			}
		);
		</script>';

		foreach($oe->result as $row)
		{
			if($row->aktiv==true)
			{
				$benutzerfunktion = new benutzerfunktion();
				$benutzerfunktion->getOeFunktionen($row->oe_kurzbz,'ass,Leitung,stvLtg,oezuordnung','now()','now()');
				$oebezeichnung = new organisationseinheit();
				$oebezeichnung->load($row->oe_kurzbz);
				if (count($benutzerfunktion->result) > 0)
				{
					echo '<h3>',$row->organisationseinheittyp_kurzbz,' ',$oebezeichnung->bezeichnung,'</h3>';
					echo '<table class="tablesorter" id="t'.$row->oe_kurzbz.'">
					<thead>
						<tr>
							<th>',$p->t('global/vorname'),'</th>
							<th>',$p->t('global/nachname'),'</th>
							<th>',$p->t('global/funktion'),'</th>
							<th>',$p->t('global/telefonnummer'),'</th>
							<th>',$p->t('lvplan/raum'),'</th>
							<th>',$p->t('global/mail'),'</th>
						</tr>
					</thead>
					<tbody>
					';
					foreach($benutzerfunktion->result as $bf)
					{
						$person = new person();
						$person->getPersonFromBenutzer($bf->uid);
						$mitarbeiter = new mitarbeiter();
						$mitarbeiter->load($bf->uid);
						$kontakt = new kontakt();
						$kontakt->loadFirmaKontakttyp($mitarbeiter->standort_id,'telefon');
						$bisverwendung = new bisverwendung();
						$bisverwendung->getLastAktVerwendung($bf->uid);
						$benutzer = new benutzer($bf->uid);
						if ($benutzer->bnaktiv)
						{
							echo '<tr>';
							echo '<td>'.$person->vorname.'</td>';
							echo '<td><a href="../profile/index.php?uid=',$person->uid,'" title="',$person->titelpre,' ',$person->vorname,' ',$person->nachname,' ',$person->titelpost,'">',$person->nachname,'</a></td>';
							echo '<td>'.$bf->bezeichnung;
								if($bisverwendung->beschausmasscode=='5')
									echo '<span style="color: orange"> (karenziert)</span>';
							echo '</td>';

							// Display phone number
							echo '<td>';
								// * if user has telefonklappe, display it
								if ($mitarbeiter->telefonklappe != '')
								{
									 echo $kontakt->kontakt. '-'. $mitarbeiter->telefonklappe;
								}
								// * if user has no telefonklappe, display business handy
								else
								{
									$person_id = $person->person_id;

									if ($kontakt->load_persKontakttyp($person_id, 'firmenhandy'))
									{
										$is_zugestellt = false;
										foreach ($kontakt->result as $kontakt)
										{
											// display business handy only if zustellung is true
											if ($kontakt->zustellung)
											{
												echo $kontakt->kontakt. "<br>";
												$is_zugestellt = true;
											}
										}
										// if zustellung is false display '-'
										if (!$is_zugestellt)
										{
											echo '-';
										}
									}
									// * if neither telefonklappe nor business handy, display '-'
									else
									{
										echo '-';
									}
								}
							echo '</td>';

							echo '<td>',($mitarbeiter->ort_kurzbz!=''?$mitarbeiter->ort_kurzbz:'-'),'</td>';
							//if($row->alias!='' && !in_array($row->studiengang_kz, $noalias)) ??? Was macht $noalias?
							if($person->alias!='')
								$mail = $person->alias.'@'.DOMAIN;
							else
								$mail = $person->uid.'@'.DOMAIN;
							echo '<td><a href="mailto:',$mail,'">',$mail,'</a></td>';
							//if(!defined('CIS_SUCHE_LVPLAN_ANZEIGEN') || CIS_SUCHE_LVPLAN_ANZEIGEN)
								//echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?pers_uid='.$person->uid.($person->mitarbeiter_uid==NULL?'&type=student':'&type=lektor').'">'.$p->t('lvplan/lvPlan').'</a></td>';
							echo '</tr>';
							echo "\n";
						}
					}
					echo "\n";
					echo '</tbody></table><br>';
				}
			}
		}
		return true;
	}
	else
		return false;
}
function searchOrt($search)
{
	global $db, $p, $noalias;
	$ort = new ort();
	$ort->filter($search, true, true, true);

	$uid = get_uid();
	$berechtigung=new benutzerberechtigung();
	$berechtigung->getBerechtigungen($uid);
	if ($berechtigung->isBerechtigt('lehre/reservierung:begrenzt', null, 'sui'))
		$raumres=true;
	else
		$raumres=false;

	if(count($ort->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('lvplan/ort'),'</h2>';
		echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
			    $("#orttable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: [\'zebra\'],
					headers: {8:{sorter:false}}
				});
			}
		);
		</script>
		<table class="tablesorter" id="orttable">
			<thead>
				<tr>
					<th>',$p->t('lvplan/raum'),'</th>
					<th>',$p->t('global/bezeichnung'),'</th>
					<th>',$p->t('lvplan/anzahlPersonen'),'</th>
					<th>',$p->t('tools/telefonklappe'),'</th>
					<th>',$p->t('lvplan/rauminformationenAnzeigen'),'</th>';
					if ($raumres)
						echo '<th>',$p->t('tools/reservieren'),'</th>';
					else
						echo '<th>',$p->t('lvplan/lvPlan'),'</th>';
				echo '</tr>
			</thead>
			<tbody>';
		foreach($ort->result as $row)
		{
			echo '<tr>';
			echo '<td>',$row->planbezeichnung,' (',$row->ort_kurzbz,')</td>';
			echo '<td>',$row->bezeichnung,'</td>';
			echo '<td>',$row->max_person,'</td>';
			echo '<td>',$row->telefonklappe,'</td>';
			echo '<td>'.($row->content_id != '' ?'<a href="../../../cms/content.php?content_id='.$row->content_id.'" target="_self">'.$p->t('lvplan/rauminformationenAnzeigen').'</a>':'-').'</td>';
			if ($raumres)
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'">'.$p->t('tools/reservieren').'</a></td>';
			else
				echo '<td><a href="../../../cis/private/lvplan/stpl_week.php?type=ort&ort_kurzbz='.$row->ort_kurzbz.'">'.$p->t('lvplan/lvPlan').'</a></td>';
			//else
			//	echo '<td></td>';
			echo '</tr>';
			echo "\n";
		}
		echo '</tbody></table><br>';
		return true;
	}
	else
		return false;
}
function searchDms($searchItems)
{
	$mimetypes = array(
		'application/pdf'=>'pdf_icon.png',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>'doc_icon.png',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation'=>'ppt_icon.png',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'=>'xls_icon.png',
		'application/vnd.oasis.opendocument.text'=>'openoffice0.jpg',
		'application/msword'=>'doc_icon.png',
		'application/vnd.ms-excel'=>'xls_icon.png',
		'application/x-zip'=>'zip_icon.png',
		'application/zip'=>'zip_icon.png',
		'application/mspowerpoint'=>'ppt_icon.png',
		'image/jpeg'=>'img_icon.png',
		'image/gif'=>'img_icon.png',
		'image/png'=>'img_icon.png',
	);
	$searchstring = $searchItems;
	global $db, $p, $uid;
	$dms = new dms();
	$dms->searchLastVersion($searchstring, 41);

	if(count($dms->result)>0)
	{
		echo '<h2 style="padding-bottom: 10px;">'.$p->t("tools/dokumente").'</h2>';
		echo '
		<script type="text/javascript">
		$(document).ready(function()
			{
			    $("#dmstable").tablesorter(
				{
					sortList: [[1,0]],
					widgets: [\'zebra\'],
					headers: {0:{sorter:false}}
				});
			}
		);
		</script>';
		if(count($dms->result)>40)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXDokumenteGefunden").'</p>';
		}
		echo '<table class="tablesorter" id="dmstable">
			<thead>
				<tr>
					<th></th>
					<th>',$p->t('global/titel'),'</th>
					<th>',$p->t('tools/aktuelleVersion'),'</th>
					<th>',$p->t('tools/suchbegriffe'),'</th>
				</tr>
			</thead>
			<tbody>
			';
		foreach($dms->result as $row)
		{
			// Berechtigung pruefen
			if ($dms->isLocked($row->dms_id))
			{
				if ($dms->isBerechtigt($row->dms_id, $uid))
				{
					echo '<tr>';
					if(array_key_exists($row->mimetype,$mimetypes))
						echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 20px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'" title="'.$row->name.'">',$row->beschreibung,'</a></td>';
					else
						echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/blank.gif" style="height: 18px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'">',$row->beschreibung,'</a></td>';
					echo '<td style="vertical-align:middle;">',$row->version,'</td>';
					echo '<td style="vertical-align:middle;">',$row->schlagworte,'</td>';
					echo '</tr>';
					echo "\n";
				}
			}
			else
			{
				echo '<tr>';
					if(array_key_exists($row->mimetype,$mimetypes))
						echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/'.$mimetypes[$row->mimetype].'" style="height: 20px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'" title="'.$row->name.'">',$row->beschreibung,'</a></td>';
					else
						echo '<td width="20px" height="20px" style="vertical-align:middle;"><img src="../../../skin/images/blank.gif" style="height: 18px; vertical-align:middle;"></td><td height="20px" style="vertical-align:middle;"><a href="../../../cms/dms.php?id='.$row->dms_id.'">',$row->beschreibung,'</a></td>';
					echo '<td style="vertical-align:middle;">',$row->version,'</td>';
					echo '<td style="vertical-align:middle;">',$row->schlagworte,'</td>';
					echo '</tr>';
					echo "\n";
			}
		}
		echo '</tbody></table><br>';
		return true;
	}
	else
		return false;
}
function searchContent($searchItems)
{
	global $db,$p,$sprache,$uid;
	$cms = new content();
	$cms->search($searchItems, 21);
	$content_id_arr = array();

	if(count($cms->result) > 0)
	{
		echo '<h2 style="padding-bottom: 10px;">',$p->t('tools/content'),'</h2>';
		if(count($cms->result) > 20)
		{
			echo '<p style="color:red;">'.$p->t("tools/esWurdenMehrAlsXInhalteGefunden").'</p>';
		}
		// Jede Sprache getrennt anzeigen aber zuerst die eingestellte Sprache
		$anzeigesprache='';
// var_dump($cms->result);
		foreach($cms->result as $row)
		{
			if ($sprache == $row->sprache)
			{
				if ($anzeigesprache != $row->sprache)
				{
					$anzeigesprache = $row->sprache;
					echo '<h3>',$row->sprache,'</h3>';
					echo '<ul>';
				}

				$berechtigt = new content();
				$berechtigt = $berechtigt->berechtigt($row->content_id, $uid);
				if ($berechtigt)
				{
					// Jede Content_ID nur einmal ausgeben
					if (in_array($row->content_id, $content_id_arr))
						continue;

					// Wenn der Eintrag vom Typ "redirect" ist, checken ob extern verlinkt wird oder nur ein Anker gesetzt ist (erstes Zeichen #)
					// Externe Links werden so ausgegeben, interne Anker gar nicht
					if ($row->template_kurzbz == 'redirect')
					{
						// URL aus content parsen
						$dom = new DOMDocument();
						$dom->loadXML($row->content);
						if($dom->getElementsByTagName('url')!=null
						&& $dom->getElementsByTagName('url')->item(0)!=null)
						{
							$content = $dom->getElementsByTagName('url')->item(0)->nodeValue;
						}
						else
						{
							// Wenn bei redirects keine URL vorhanden ist, dann handelt es sich um Fehlerhafte Einträge
							// diese werden übersprungen
							continue;
						}

						if (substr($content, 0, 1) == '#')
							continue;
						else
						{
							if(mb_strpos($content, 'http') === 0)
							{
								echo '<li><div class="suchergebnis">';
								echo '<a href="'.$content.'" target="blank">',$db->convert_html_chars($row->titel),'</a><br>';
								echo '</div></li>';
							}
							else
							{
								echo '<li><div class="suchergebnis">';
								echo '<a href="../../../cms/content.php?content_id=',$db->convert_html_chars($row->content_id),'&sprache=',$db->convert_html_chars($row->sprache),'">',$db->convert_html_chars($row->titel),'</a><br>';
								echo '</div></li>';
							}
						}
					}
					else
					{
						echo '<li><div class="suchergebnis">';
						echo '<a href="../../../cms/content.php?content_id=',$db->convert_html_chars($row->content_id),'&sprache=',$db->convert_html_chars($row->sprache),'">',$db->convert_html_chars($row->titel),'</a><br>';
						$preview = findAndMark($row->content, $searchItems);

						echo $preview;
						echo '</div></li>';
					}
					$content_id_arr[] .= $row->content_id;
				}
			}
		}
		echo '</ul>';
		$anzeigesprache='';
		$content_id_arr = array();
		foreach ($cms->result as $row)
		{
			if ($sprache != $row->sprache)
			{
				if ($anzeigesprache != $row->sprache)
				{
					$anzeigesprache = $row->sprache;
					echo '</ul>';
					echo '<h3>',$row->sprache,'</h3>';
					echo '<ul>';
				}
				$berechtigt = new content();
				$berechtigt = $berechtigt->berechtigt($row->content_id, $uid);
				if ($berechtigt)
				{
					// Jede Content_ID nur einmal ausgeben
					if (in_array($row->content_id, $content_id_arr))
						continue;

					// Wenn der Eintrag vom Typ "redirect" ist, checken ob extern verlinkt wird oder nur ein Anker gesetzt ist (erstes Zeichen #)
					// Externe Links werden so ausgegeben, interne Anker gar nicht
					if ($row->template_kurzbz == 'redirect')
					{
						// URL aus content parsen
						$dom = new DOMDocument();
						$dom->loadXML($row->content);
						if($dom->getElementsByTagName('url')!=null
						&& $dom->getElementsByTagName('url')->item(0)!=null)
						{
							$content = $dom->getElementsByTagName('url')->item(0)->nodeValue;
						}
						else
						{
							// Wenn bei redirects keine URL vorhanden ist, dann handelt es sich um Fehlerhafte Einträge
							// diese werden übersprungen
							continue;
						}
						if (substr($content, 0, 1) == '#')
							continue;
						else
						{
							if(mb_strpos($content, 'http') === 0)
							{
								echo '<li><div class="suchergebnis">';
								echo '<a href="'.$content.'" target="blank">',$db->convert_html_chars($row->titel),'</a><br>';
								echo '</div></li>';
							}
							else
							{
								echo '<li><div class="suchergebnis">';
								echo '<a href="../../../cms/content.php?content_id=',$db->convert_html_chars($row->content_id),'&sprache=',$db->convert_html_chars($row->sprache),'">',$db->convert_html_chars($row->titel),'</a><br>';
								echo '</div></li>';
							}
						}
					}
					else
					{
						echo '<li><div class="suchergebnis">';
						echo '<a href="../../../cms/content.php?content_id=',$db->convert_html_chars($row->content_id),'&sprache=',$db->convert_html_chars($row->sprache),'">',$db->convert_html_chars($row->titel),'</a><br>';
						$preview = findAndMark($row->content, $searchItems);

						echo $preview;
						echo '</div></li>';
					}
					$content_id_arr[] .= $row->content_id;
				}
			}
		}
		echo '</ul>';
		return true;
	}
	else
		return false;
}
function findAndMark($content, $items)
{
	$item = '';
	foreach($items as $row)
	{
		if($row!='')
		{
			$item = $row;
			break;
		}
	}
	if($item == '')
		return '...';

	//CDATA und HTML Tags entfernen
	$content = mb_str_replace('<[CDATA[', '', $content);
	$content = mb_str_replace(']]>', '', $content);
	$content = strip_tags($content);
	$item = mb_strtolower($item);

	$beginn = mb_strpos(mb_strtolower($content), $item);
	$len = mb_strlen($item);

	//Im Content sind die Umlaute teilweise codiert gespeichert
	//Wenn der Eintrag nicht gefunden wird, wird mit Codierten Zeichen nochmals gesucht
	if($beginn=='')
	{
		$beginn = mb_strpos(mb_strtolower($content), htmlentities($item,ENT_NOQUOTES,'UTF-8'));
		$len = mb_strlen(htmlentities($item,ENT_NOQUOTES,'UTF-8'));
	}

	if($beginn=='')
	{
		$beginn=0;
		$len=0;
	}
	$start = (($beginn-100)>0?($beginn-100):0);
	//echo "start: $start<br>beginn: $beginn<br>beginn-start: ".($beginn-$start);
	//echo "<br>item:".$item.'<br>';
	$preview='';
	if($start!=0)
		$preview='...';

	$preview .= mb_substr($content, $start, ($beginn-$start));
	$preview.='<span class="suchmarker">';
	$preview.= mb_substr($content, $beginn, $len);
	$preview.='</span>';
	$preview.= mb_substr($content, $beginn+$len, 300);

	$laenge = $beginn+$len+300;
	if($laenge<mb_strlen($content))
		$preview.='...';
	return $preview;
}

echo '</body></html>';
?>
