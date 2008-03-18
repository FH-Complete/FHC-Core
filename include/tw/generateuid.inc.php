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
	$art = substr($matrikelnummer, 2, 1);
	$nr = substr($matrikelnummer, 7);
	if($art=='2') //Sommersemester
		$nr = $nr+500;
	elseif($art=='0' && $stgtyp=='m') //Incoming im Masterstudiengang
		$nr = $nr+500;
		

	return $stgkzl.$jahr.($art!='0'?$stgtyp:'x').$nr;
}

// ****
// * Gerneriert die Mitarbeiter UID
// ****
function generateMitarbeiterUID($conn, $vorname, $nachname, $lektor)
{
	$bn = new benutzer($conn);
	
	for($nn=8,$vn=0;$nn!=0;$nn--,$vn++)
	{
		$uid = substr($nachname,0,$nn);
		$uid .= substr($vorname,0,$vn);

		if(!$bn->uid_exists($uid))
			if($bn->errormsg=='')
				return $uid;
	}
}
?>