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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
?>
// ********** FUNKTIONEN ********** //

// ****
// * Laedt die Trees
// ****
function loadUDF(person_id, prestudent_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var udfIFrame = document.getElementById('udfIFrame');

	if (udfIFrame != null && udfIFrame.getAttribute('src') == 'about:blank')
	{
		udfIFrame.setAttribute('src', '<?php echo APP_ROOT ?>index.ci.php/system/FAS_UDF?person_id='+person_id+'&prestudent_id='+prestudent_id);
	}
}
