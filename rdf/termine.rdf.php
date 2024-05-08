<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Andreas Oesterreicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/functions.inc.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/variable.class.php');
require_once('../include/lehrstunde.class.php');
require_once('../include/datum.class.php');
require_once('../include/stunde.class.php');
require_once('../include/anwesenheit.class.php');
require_once('../include/benutzer.class.php');

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('assistenz') && !$rechte->isBerechtigt('lvplan') && !$rechte->isBerechtigt('admin'))
	die($rechte->errormsg);

$variable = new variable();
$variable->loadVariables($user);

$stunde = new stunde();
$stunde->loadAll();

$stunden_arr=array();
foreach($stunde->stunden as $row)
{
	$stunden_arr[$row->stunde]['beginn']=$row->beginn->format('H:i');
	$stunden_arr[$row->stunde]['ende']=$row->ende->format('H:i');
}
$datum_obj = new datum();
$verplanteStunden = array();

$oRdf = new rdf('TERMINE','http://www.technikum-wien.at/termine');

$lehrveranstaltung_id = filter_input(INPUT_GET, 'lehrveranstaltung_id');
$lehreinheit_id = filter_input(INPUT_GET, 'lehreinheit_id');
$mitarbeiter_uid = filter_input(INPUT_GET,'mitarbeiter_uid');
$student_uid = filter_input(INPUT_GET,'student_uid');
$db_stpl_table = filter_input(INPUT_GET,'db_stpl_table');
if(!in_array($db_stpl_table,array('stundenplan','stundenplandev')))
	$db_stpl_table='stundenplan';

$oRdf->sendHeader();
$db = new basis_db();

$lehrstunde = new lehrstunde();
//$variable->variable->db_stpl_table
$lehrstunde->getStundenplanData($db_stpl_table, $lehrveranstaltung_id, $variable->variable->semester_aktuell, $lehreinheit_id, $mitarbeiter_uid, $student_uid);

$i=0;
if(isset($lehrstunde->result) && is_array($lehrstunde->result))
{
	$lektoren_arr=array();
	foreach($lehrstunde->result as $row)
	{
		$i=$oRdf->newObjekt($i);
		$oRdf->obj[$i]->setAttribut('datum',$datum_obj->formatDatum($row->datum,'d.m.Y'),true);
		$oRdf->obj[$i]->setAttribut('stundevon',$row->stundevon,true);
		$oRdf->obj[$i]->setAttribut('stundebis',$row->stundebis,true);
		$oRdf->obj[$i]->setAttribut('uhrzeitvon',$stunden_arr[$row->stundevon]['beginn'],true);
		$oRdf->obj[$i]->setAttribut('uhrzeitbis',$stunden_arr[$row->stundebis]['ende'],true);
		$oRdf->obj[$i]->setAttribut('gruppen',implode(',',$row->gruppen),true);

		$lektoren='';
		foreach($row->lektoren as $rowlkt)
		{
			if(!isset($lektoren_arr[$rowlkt]))
			{
				$lkt_obj = new benutzer();
				$lkt_obj->load($rowlkt);
				$lektoren_arr[$rowlkt]=$lkt_obj->nachname.' '.$lkt_obj->vorname;
			}
			$lektoren .=",".$lektoren_arr[$rowlkt];
		}
		$lektoren = mb_substr($lektoren,1);

		$oRdf->obj[$i]->setAttribut('lektor',$lektoren,true);
		$oRdf->obj[$i]->setAttribut('ort',implode(',',$row->orte),true);
		$oRdf->obj[$i]->setAttribut('lehrfach',$row->lehrfach_bezeichnung,true);
		$oRdf->obj[$i]->setAttribut('lehreinheit_id',$row->lehreinheit_id,true);
		$oRdf->obj[$i]->setAttribut('titel',implode(',',$row->titel),true);

		$anwesenheit = new anwesenheit();
		if($anwesenheit->AnwesenheitExists($row->lehreinheit_id, $row->datum, $student_uid))
			$anwesend='Ja';
		else
			$anwesend='Nein';
		$oRdf->obj[$i]->setAttribut('anwesend',$anwesend,true);
		$oRdf->obj[$i]->setAttribut('datum_iso',$row->datum,true);

        // Terminkollisionen prüfen
        $kollision = "";
        if($lehrveranstaltung_id == '')
        {
            for($x = $row->stundevon; $x <= $row->stundebis; $x++)
            {
                $orte = implode(',',$row->orte);

                if(isset($verplanteStunden[$row->datum]) && in_array($x, $verplanteStunden[$row->datum]))
                {
                    if(!isset($verplanteStunden[$row->datum][$orte]) || !in_array($x, $verplanteStunden[$row->datum][$orte]))
                    {
                        $kollision = "makeItred";
                        break;
                    }
                }

                $verplanteStunden[$row->datum][] = $x;
                $verplanteStunden[$row->datum][$orte][] = $x;
            }
        }
        $oRdf->obj[$i]->setAttribut('kollision',$kollision,true);

		$oRdf->addSequence($i);
		$i++;
	}
}
$oRdf->sendRdfText();
?>
