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
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/benutzer.class.php');

if(!isset($_REQUEST['work']))
	die('Parameter Work missing');

$work = $_REQUEST['work'];
$q = $_REQUEST['q'];

switch($work)
{
	case 'ressource':
			$ort = new ort();

			if(!$ort->filter($q))
				die('Fehler beim Laden der Orte: '.$ort->errormsg);

			foreach($ort->result as $row)
			{
				if($row->aktiv)
					echo html_entity_decode($row->ort_kurzbz.'|Ort|'.$row->bezeichnung."\n");
			}
		 	
			$benutzer = new benutzer();
			
			if(!$benutzer->search(array($q)))
				die('Fehler beim Laden der Benutzer: '.$benutzer->errormsg);
				
			foreach($benutzer->result as $row)
			{
				echo html_entity_decode($row->uid.'|Person|'.$row->nachname.' '.$row->vorname."\n");
			}
			break;
	default:
			die('Invalid Work Parameter');
}
?>