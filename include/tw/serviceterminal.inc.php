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
/**
 * Service Terminal Funktionen
 */
require_once(dirname(__FILE__).'/../addon.class.php');

// die aktiven Addons werden durchsucht, ob eines davon eigene Funktionen vorsieht
// falls ja, wird die Version des Addons genommen, ansonsten die Default Funktionalitaet
$serviceterminal_addon_found=false;
$serviceterminal_addons = new addon();

foreach($serviceterminal_addons->aktive_addons as $addon)
{
	$serviceterminal_addon_filename = dirname(__FILE__).'/../../addons/'.$addon.'/vilesci/serviceterminal.inc.php';
	
	if(file_exists($serviceterminal_addon_filename))
	{
		include($serviceterminal_addon_filename);
		$serviceterminal_addon_found=true;
		break;
	}
}

if(!$serviceterminal_addon_found)
{
	function ServiceTerminalCheckVerlaengerung($uid)
	{
		if(!check_lektor($uid))	
	    {

			$konto = new konto(); 
			if($aktSemester= $konto->getLastStudienbeitrag($uid))
		    {
				return array(true,'Studienbeitrag für Semester '.$aktSemester.' bezahlt');
			}
			else
			{
				return array(false,'Verlängerung der Karte ist derzeit nicht möglich da der Studienbeitrag noch nicht bezahlt wurde');
			}
		}
		else
			return array(false,'Für Mitarbeiter ist eine Kartenverlängerung nicht möglich');
	}

	function ServiceTerminalGetDrucktext($uid)
	{
		// hole Semester des letzten eingezahlten Studienbeitrages
		$konto = new konto(); 
		if(!$aktSemester= $konto->getLastStudienbeitrag($uid))
		{
		    return array('datum'=>'', 'errorMessage'=>'Fehler beim Auslesen des Studienganges. Bitte wenden Sie sich an den Service Desk.');  
		}  
		
		return array('datum'=>'Gueltig fuer/valid for '.$aktSemester, 'errorMessage'=>'');  
	}
}
?>
