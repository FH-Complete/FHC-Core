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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 *	    Stefan Puraner <stefan.puraner@technikum-wien.at>.
 */

require_once('../config/vilesci.config.inc.php');
require_once('../include/basis_db.class.php');
require_once('../include/datum.class.php');
require_once('../include/mail.class.php');
require_once('../include/pruefungCis.class.php');
require_once('../include/pruefungsanmeldung.class.php');

$date = new datum();
$db = new basis_db();

$pruefungen=new pruefungCis();
$pruefungen->getAllPruefungen();

echo date('Y-m-d',strtotime('now + 1 day'));
echo '=====<br>';
echo 'Start<br>';
foreach($pruefungen->result as $p)
{
    if($p->storniert)
	continue;
	
    $p->getTermineByPruefung();
    foreach($p->termine as $termin)
    {
//	    echo $date->formatDatum($termin->von,'Y-m-d');
	if($date->formatDatum($termin->von,'Y-m-d') == date('Y-m-d',strtotime('now + 1 day')))	//Datumsüberprüfung
	{
	    $anm_obj=new pruefungsanmeldung();
	    $anmeldungen=$anm_obj->getAnmeldungenByTermin($termin->pruefungstermin_id, null, null, "bestaetigt");
	    if(empty($anmeldungen))
	    {
		$anmeldungen=$anm_obj->getAnmeldungenByTermin($termin->pruefungstermin_id, null, null, "angemeldet");
		foreach($anmeldungen as $anm)
		{
		    $anm_obj->changeState($anm->pruefungsanmeldung_id,'bestaetigt');
		}
		echo 'true<br>';
	    }
	}
	else
	    echo 'false<br>';
    }
}
echo 'Ende';
?>