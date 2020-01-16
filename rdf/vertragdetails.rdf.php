<?php
/* Copyright (C) 2009 Technikum-Wien
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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */
require_once('../config/vilesci.config.inc.php');
require_once('../config/global.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/vertrag.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('vertrag/mitarbeiter'))
	die('Sie haben keine Berechtigung für diese Seite');


$vertrag = new vertrag();

if(isset($_GET['person_id']))
{
	$person_id=$_GET['person_id'];
	if(!$vertrag->loadNichtZugeordnet($person_id))
		die('Fehlgeschlagen:'.$vertrag->errormsg);
}
elseif(isset($_GET['vertrag_id']))
{
	$vertrag_id = $_GET['vertrag_id'];
	if(!$vertrag->loadZugeordnet($vertrag_id))
		die('Fehlgeschlagen:'.$vertrag->errormsg);
}
else
	die('Fehlerhafte Parameterübergabe');

$oRdf = new rdf('VER','http://www.technikum-wien.at/vertragdetails');
$oRdf->sendHeader();

foreach($vertrag->result as $row)
{
	switch($row->type)
	{
		case 'Lehrauftrag':
			$key = $row->type.'/'.$row->studiensemester_kurzbz.'/'.$row->lehreinheit_id.'/'.$row->mitarbeiter_uid;
			break;
		case 'Pruefung':
			$key = $row->type.'/'.$row->pruefung_id;
			break;
		case 'Betreuung':
			$key = $row->type.'/'.$row->mitarbeiter_uid.'/'.$row->projektarbeit_id.'/'.$row->betreuerart_kurzbz;
			break;
		default:
			$key = 'unknowntype';
	}
	$i=$oRdf->newObjekt($key);
	$oRdf->obj[$i]->setAttribut('type',$row->type,true);
	$oRdf->obj[$i]->setAttribut('betrag',number_format($row->betrag,2,'.',''),true);
	$oRdf->obj[$i]->setAttribut('studiensemester_kurzbz',$row->studiensemester_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('lehreinheit_id',$row->lehreinheit_id,true);
	$oRdf->obj[$i]->setAttribut('pruefung_id',$row->pruefung_id,true);
	$oRdf->obj[$i]->setAttribut('mitarbeiter_uid',$row->mitarbeiter_uid,true);
	$oRdf->obj[$i]->setAttribut('projektarbeit_id',$row->projektarbeit_id,true);
	$oRdf->obj[$i]->setAttribut('betreuerart_kurzbz',$row->betreuerart_kurzbz,true);
	$oRdf->obj[$i]->setAttribut('bezeichnung',$row->bezeichnung,true);
	if(isset($row->vertragsstunden) && !empty($row->vertragsstunden))
		$oRdf->obj[$i]->setAttribut('vertragsstunden', $row->vertragsstunden, true);
	if(isset($row->vertragsstunden_studiensemester_kurzbz) && !empty($row->vertragsstunden_studiensemester_kurzbz))
		$oRdf->obj[$i]->setAttribut('vertragsstunden_studiensemester_kurzbz', $row->vertragsstunden_studiensemester_kurzbz, true);

	$oRdf->addSequence($key);
}

$oRdf->sendRdfText();
?>
