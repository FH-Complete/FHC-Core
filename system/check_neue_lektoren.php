<?php

/**
 * Copyright (C) 2006 Technikum-Wien
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
 * Prueft ob am Vortag neue Lektoren einen Lehrauftrag bekommen haben
 * die vorher noch keinen hatten.
 * Diese werden dann an die Geschaeftsstelle gemeldet damit diese
 * Personen nachgemeldet werden koennen.
 * Wenn kein aktuelles Studiensemester vorhanden ist, wird keine
 * Nachricht versendet.
 */

require_once(dirname(__FILE__).'/../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../include/studiensemester.class.php');
require_once(dirname(__FILE__).'/../include/mail.class.php');
	
$stsem = new studiensemester();
if (!$studiensemester = $stsem->getakt())
	die('Es ist kein aktuelles Studiensemester vorhanden -> Versand nicht noetig');

$db = new basis_db();

// Alle Lektoren holen die am Vortag zu einer Lehreinheit zugeteilt wurden
// und in diesem Studiensemester noch keinen Lehrauftrag haben.

$qry="
SELECT vorname, nachname, titelpre, titelpost, uid FROM public.tbl_person JOIN public.tbl_benutzer USING(person_id)
WHERE uid IN(
	SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter ma JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
	WHERE 
		ma.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
		AND ma.insertamum::date=(now()-'1 day'::interval)::date
		AND tbl_lehreinheit.studiensemester_kurzbz='$studiensemester'
		AND ma.mitarbeiter_uid NOT IN 
			(SELECT mitarbeiter_uid FROM lehre.tbl_lehreinheitmitarbeiter JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			 WHERE tbl_lehreinheit.studiensemester_kurzbz='$studiensemester'
			 AND tbl_lehreinheitmitarbeiter.insertamum::date<(now()-'1 day'::interval)::date
			 AND tbl_lehreinheitmitarbeiter.lehreinheit_id<>ma.lehreinheit_id)
	)
";

if ($result = $db->db_query($qry))
{
	if ($db->db_num_rows($result) > 0)
	{
		$mitarbeiter = '';

		while ($row = $db->db_fetch_object($result))
		{
			$mitarbeiter .= trim($row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost)." ($row->uid)\n";
		}

		$message = "Dies ist eine automatische Mail!\n";
		$message .= "Folgende Lektoren haben in diesem Studiensemester zum ersten Mal einen Lehrauftrag erhalten:\n\n";
		$message .= $mitarbeiter;
		$to = MAIL_GST;
		
		$mail = new mail($to, 'vilesci@'.DOMAIN, 'Neue Lektoren mit Lehrauftrag', $message);
		if ($mail->send())
			echo "Mail wurde an $to versandt: ".$message;
		else 
			echo "Fehler beim Senden des Mails an $to: ".$message;
	}
	else 
	{
		echo 'Es sind keine neuen Lektoren hinzugefuegt worden';
	}
}

?>

