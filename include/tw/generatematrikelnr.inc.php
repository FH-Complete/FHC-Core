<?php

/* Copyright (C) 2016 fhcomplete.org
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
 * Authors: Nikolaus Krondraf <nikolaus.krondraf@technikum-wien.at>
 */

/**
 * Funktion zur Generierung der Matrikelnummer
 * Default: es wird keine Matrikelnummer generiert
 */

require_once(dirname(__FILE__).'/../addon.class.php');

// die aktiven Addons werden durchsucht, ob eines davon eine eigene Matrikelnummern-Generierung vorsieht
// falls ja, wird die Version des Addons genommen, ansonsten die Default Generierung
$generateuid_addon_found=false;
$generateuid_addons = new addon();

foreach($generateuid_addons->aktive_addons as $addon)
{
	$generateuid_addon_filename = dirname(__FILE__).'/../../addons/'.$addon.'/vilesci/generatematrikelnr.inc.php';

	if(file_exists($generateuid_addon_filename))
	{
		include($generateuid_addon_filename);
		$generateuid_addon_found=true;
		break;
	}
}


if(!$generateuid_addon_found)
{
    function generateMatrikelnr($oe_kurzbz)
    {
        return null;
    }
}
