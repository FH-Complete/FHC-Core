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
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at>
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/mail.class.php');
require_once('../include/ampel.class.php');

$db = new basis_db();
$ampel=new ampel();
$ampel->getAll(true);

foreach($ampel->result as $a)
{
	if(!$a->email)
		continue;
	
	$qry=$a->benutzer_select;
	$message = $a->beschreibung['German'];
	$subject = $a->kurzbz;

	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$uid = $row->uid;
			
			$mail = new mail($uid.'@'.DOMAIN, 'cis@'.DOMAIN, $subject, $message);
			
			// If message has HTML content
			if($message != strip_tags($message)) {
				$mail->setHTMLContent(sprintf('%s', $message));
			}
			
			if($mail->send())
				echo "Email an $uid versandt\n";
			 else
				echo "Fehler beim Versenden des Erinnerungsmails an $uid\n";
		}
	}
}
?>