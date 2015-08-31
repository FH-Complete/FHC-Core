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
* Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
*/
/**
 * Benutzerdefinierte Funktion zur Generierung der UIDs
 * Zur Aktivierung muss die Datei in generateuid.inc.php umbenannt werden
 */

/**
 * Generiert die UID für Studierende
 *
 * @param $stgkzl Studiengangskuerzel
 * @param $jahr Studienjahr (zB 2014)
 * @param $stgtyp Studiengangstyp einstellige Ziffer
 * @param $matrikelnummer Personenkennzeichen des Studierenden
 */
function generateUID($stgkzl, $jahr, $stgtyp, $matrikelnummer)
{
	return $matrikelnummer;
}

/**
 * Gerneriert die Mitarbeiter UID
 * Format v.nachname max 20 Zeichen
 * Im Fall von Doppelnamen wird vor dem Bindestrich abgeschnitten
 *
 * @param $vorname Vorname
 * @param $nachname Nachname
 * @param $lektor Boolean true wenn Lektor sonst false
 */
function generateMitarbeiterUID($vorname, $nachname, $lektor, $fixangestellt=true)
{
	$bn = new benutzer();
	$uid='';

	// Wenn ein Bindestrich vorhanden ist (Doppelname), dort abschneiden
	if(mb_strpos($nachname,'-')!==false)
		$nachname = mb_substr($nachname, 0, mb_strpos($nachname,'-'));
	// Nachname wird so lange verkuerzt bis eine eindeutige UID entsteht die noch nicht vergeben ist
	for($nn=18;$nn!=0;$nn--)
	{
		$uid = mb_substr($vorname,0,1);
		$uid .= mb_substr($nachname,0,$nn);

		$uid = mb_str_replace(' ','',$uid);
		$uid = mb_str_replace('-','',$uid);

		$uid = mb_strtolower($uid);
		if(!$bn->uid_exists($uid))
			return $uid;
	}
	return false;
}
