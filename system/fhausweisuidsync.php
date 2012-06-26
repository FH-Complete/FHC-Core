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
 * Authors: Andreas Oesterreicher 	<andreas.oesterreicher@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/person.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);
if(!$rechte->isBerechtigt('admin'))
	die('Sie haben keine Berechtigung für diese Seite');
	
//Alle Kartenuser holen
$qry = "
SELECT 
	nummer, betriebsmittelperson_id, person_id
FROM 
	wawi.tbl_betriebsmittelperson 
	JOIN wawi.tbl_betriebsmittel USING(betriebsmittel_id)
WHERE
	tbl_betriebsmittelperson.uid is null
	AND tbl_betriebsmittelperson.retouram is null
	AND tbl_betriebsmittel.betriebsmitteltyp='Zutrittskarte'
	AND EXISTS (SELECT * FROM public.tbl_benutzer WHERE person_id=tbl_betriebsmittelperson.person_id AND aktiv=true)";

$db = new basis_db();

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if(!$user = getUidFromCardNumber($row->nummer))
		{
			$qry = "SELECT uid FROM public.tbl_benutzer WHERE aktiv AND person_id=".$db->db_add_param($row->person_id);
			if($result_user = $db->db_query($qry))
			{
				//Wenn nur 1 aktiver User vorhanden ist, wird dieser genommen
				if($db->db_num_rows($result_user)==1)
				{
					if($row_user = $db->db_fetch_object($result_user))
					{
						$user = $row_user->uid;
					}
				}
				else
				{
					$pers = new person();
					$pers->load($row->person_id);
					echo "<br>Fot Found: $row->nummer PersonID: $row->person_id BetriebsmittelpersonID: $row->betriebsmittelperson_id $pers->vorname $pers->nachname<br>";
				}
			}
		}
		if($user!='')
		{
			$qry = "UPDATE wawi.tbl_betriebsmittelperson 
					SET uid=".$db->db_add_param($user)." 
					WHERE betriebsmittelperson_id=".$db->db_add_param($row->betriebsmittelperson_id);
			if($db->db_query($qry))
				echo '+';
			else
				echo '|'; 
		}
	}
}

?>