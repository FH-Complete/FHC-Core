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
/*
 * Diese Datei überschreibt die Default-Funktionen zur Passwort Änderung
 * Zur Aktivierung muss die Datei in passwort.inc.php umbenannt werden
 * 
 * Ansonsten wird die Default Funktionalität unter /include/tw/passwort.inc.php verwendet
 */

/**
 * Aendert das Passwort 
 * @param $passwort_alt Altes (aktuelles) Passwort
 * @param $passwort_neu neues Passwort
 * @param $uid - UID/Benutzername des Users
 * @return true wenn erfolgreich - Fehlermeldung im Fehlerfall
 */
function change_password($passwort_alt, $passwort_neu, $uid)
{
	return 'Passwort Änderung fehlgeschlagen.';
}

?>
