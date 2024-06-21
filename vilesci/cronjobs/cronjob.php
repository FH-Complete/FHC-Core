<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/**
 * Cronjob.php
 *
 * Dieses Script muss in der Crontab eingetragen werden. Von hier aus werden dann
 * die entsprechenden anderen Scripte aufgerufen.
 */
require_once(dirname(__FILE__).'/../../config/vilesci.config.inc.php');
require_once(dirname(__FILE__).'/../../include/cronjob.class.php');
require_once(dirname(__FILE__).'/../../include/datum.class.php');
require_once(dirname(__FILE__).'/../../include/functions.inc.php');

$datum = new datum();
$cj = new cronjob();
if(!$cj->getAll(SERVER_NAME,'reihenfolge',true))
	die('Fehler beim Laden der Cronjobs');

foreach ($cj->result as $cronjob)
{
	$timestamp = $cronjob->getNextExecutionTime();
	if($timestamp && time()>=$timestamp)
	{
		if(!$cronjob->running)
		{
			echo "\n".date('d.m.Y H:i:s').' '.$cronjob->titel.'('.$cronjob->cronjob_id.') execute...<br>'."\n";
			//Starten des Jobs
			if($cronjob->execute())
			{
				echo "\n".date('d.m.Y H:i:s').' '.$cronjob->titel.'('.$cronjob->cronjob_id.') executed<br>'."\n";
				echo implode("\n",$cronjob->output);
			}
			else
			{
				echo "\n".date('d.m.Y H:i:s').' '.$cronjob->titel.'('.$cronjob->cronjob_id.') <b>failed:'.$cronjob->errormsg.'</b><br>'."\n";
			}
		}
	}
}

?>
