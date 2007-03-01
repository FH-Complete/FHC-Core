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
/*
 * functions.js.php
 *
 * enthaelt globale JS Funktionen
 */

// ****
// * Liefert den Value aus einer XML-Datasource
// ****
function getTargetHelper(dsource,subj,predi) 
{
	if (dsource.hasArcOut(subj, predi))  
	{
		var target = dsource.GetTarget(subj, predi, true);
		if (target instanceof Components.interfaces.nsIRDFLiteral) 
		{
			return target.Value;
		}
	}
	return "";
}

// ****
// * Gibt eine Message auf die Javascript Console aus
// ****
function debug(msg)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	 var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
                                 .getService(Components.interfaces.nsIConsoleService);
    consoleService.logStringMessage(msg);
}

// ****
// * Liefert einen Timestamp in Sekunden
// * zum anhaengen an eine URL um Caching zu verhindern
// ****
function gettimestamp()
{
	var now = new Date();
	var ret = now.getHours()*60*60*60;
	ret = ret + now.getMinutes()*60*60;
	ret = ret + now.getSeconds()*60;
	ret = ret + now.getMilliseconds();
	return ret;
}