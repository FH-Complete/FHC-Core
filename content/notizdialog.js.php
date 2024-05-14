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
?>

// ****
// * Laedt die zu bearbeitenden Daten
// ****
function NotizInit(projekt_kurzbz, projektphase_id, projekttask_id, uid, person_id, prestudent_id, bestellung_id, user, lehreinheit_id, anrechnung_id)
{
	var notizbox = document.getElementById('notiz-dialog-box-notiz');
	notizbox.LoadNotizTree(projekt_kurzbz,projektphase_id, projekttask_id, uid, person_id, prestudent_id, bestellung_id, user, lehreinheit_id, null, anrechnung_id);
}
