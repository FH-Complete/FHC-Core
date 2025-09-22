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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Funktionen zum Generieren der UIDs
 */
require_once(dirname(__FILE__).'/../addon.class.php');

// die aktiven Addons werden durchsucht, ob eines davon eine eigene UID Generierung vorsieht
// falls ja, wird die Version des Addons genommen, ansonsten die Default Generierung
$generateuid_addon_found=false;
$generateuid_addons = new addon();

foreach($generateuid_addons->aktive_addons as $addon)
{
	$generateuid_addon_filename = dirname(__FILE__).'/../../addons/'.$addon.'/vilesci/generateuid.inc.php';

	if(file_exists($generateuid_addon_filename))
	{
		include($generateuid_addon_filename);
		$generateuid_addon_found=true;
		break;
	}
}


if(!$generateuid_addon_found)
{

	// ****
	// * Generiert die UID
	// * FORMAT: el07b001
	// * $stgkzl: el = studiengangskuerzel
	// * $jahr: 07 = Jahr
	// * $stgtyp: b/m/d/x = Bachelor/Master/Diplom/Incomming
	// * $matrikelnummer
	// * 001 = Laufende Nummer  Wenn StSem==SS dann wird zur Nummer 500 dazugezaehlt
	// *                        Bei Incoming im Masterstudiengang wird auch 500 dazugezaehlt
	// ****
	function generateUID($stgkzl,$jahr, $stgtyp, $matrikelnummer)
	{
		$art = mb_substr($matrikelnummer, 2, 1);
		$nr = mb_substr($matrikelnummer, mb_strlen(trim($matrikelnummer))-3);
		if($art=='2') //Sommersemester
			$nr = $nr+500;
		elseif($art=='0' && $stgtyp=='m') //Incoming im Masterstudiengang
			$nr = $nr+500;
		elseif($art=='4' && $stgtyp=='l') // Lehrgangsteilnehmer im Sommersemester
			$nr = $nr+500;


		return mb_strtolower($stgkzl.$jahr.($art!='0'?$stgtyp:'x').$nr);
	}

	// ****
	// * Gerneriert die Mitarbeiter UID
	// ****
	function generateMitarbeiterUID($vorname, $nachname, $lektor, $fixangestellt=true, $personalnummer=null)
	{
		$bn = new benutzer();
		$reserviert = array();

		// Das File aliases enthaelt die Mailverteiler haendisch gewarteten Mailverteiler die nicht
		// in der FHC Datenbank vorhanden sind.
		// Diese duerfen nicht als UID verwendet werden, da es sonst zu Konflikten kommt
		if(file_exists(DOC_ROOT.'../system/aliases'))
		{
			$aliases = file_get_contents(DOC_ROOT.'../system/aliases');
			$aliases = explode("\n",$aliases);
			foreach($aliases as $alias)
			{
				if(!strstr($alias,'#'))
				{
				 $entry = preg_split("/[\s:]+/", $alias);
				 if($entry[0]!='')
				 	$reserviert[]=$entry[0];
				}
			}
		}

		for($nn=8,$vn=0;$nn!=0;$nn--,$vn++)
		{
			$uid = mb_substr($nachname,0,$nn);
			$uid .= mb_substr($vorname,0,$vn);

			$uid = mb_str_replace(' ','',$uid);
			$uid = mb_str_replace('-','',$uid);

			if(!$bn->uid_exists($uid) && !in_array($uid, $reserviert))
				if($bn->errormsg=='')
					return $uid;
		}
	}
}

?>
