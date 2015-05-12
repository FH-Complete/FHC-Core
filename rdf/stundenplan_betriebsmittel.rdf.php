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
 * Authors: Andreas Oestereicher <oesi@technikum-wien.at>
 */
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/betriebsmittel.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');

$datum_obj = new datum();
if(isset($_REQUEST['stundenplan_ids']) || isset($_REQUEST['stundenplan_betriebsmittel_id']))
{
	$uid = get_uid();
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);

	if(!$rechte->isBerechtigt('lehre/lvplan'))
		die('Sie haben keine Berechtigung (lvplan)');

	$oRdf = new rdf('STUNDENPLANBETRIEBSMITTEL','http://www.technikum-wien.at/stundenplanbetriebsmittel');
	$oRdf->sendHeader();

	$betriebsmittel = new betriebsmittel();

	if(isset($_REQUEST['stundenplan_ids']))
	{
		$stundenplan_ids=$_REQUEST['stundenplan_ids'];

		if($betriebsmittel->getBetriebsmittelStundenplan($stundenplan_ids))
		{
			if(count($betriebsmittel->result)>0)
			{
				foreach($betriebsmittel->result as $row)
				{	
					$i=$oRdf->newObjekt($row->stundenplan_betriebsmittel_id);
					$oRdf->obj[$i]->setAttribut('stundenplan_betriebsmittel_id',$row->stundenplan_betriebsmittel_id,true);
					$oRdf->obj[$i]->setAttribut('beschreibung',$row->beschreibung,true);
					$oRdf->obj[$i]->setAttribut('betriebsmittel_id',$row->betriebsmittel_id,true);
					$oRdf->obj[$i]->setAttribut('anmerkung',$row->anmerkung,true);
					$oRdf->obj[$i]->setAttribut('stunde',$row->stunde,true);
		
					$oRdf->addSequence($row->stundenplan_betriebsmittel_id);
				}
			}
		}
	}
	elseif(isset($_REQUEST['stundenplan_betriebsmittel_id']))
	{
		$stundenplan_betriebsmittel_id=$_REQUEST['stundenplan_betriebsmittel_id'];

		if($betriebsmittel->loadBetriebsmittelStundenplan($stundenplan_betriebsmittel_id))
		{
			$i=$oRdf->newObjekt($betriebsmittel->stundenplan_betriebsmittel_id);
			$oRdf->obj[$i]->setAttribut('stundenplan_betriebsmittel_id',$betriebsmittel->stundenplan_betriebsmittel_id,true);
			$oRdf->obj[$i]->setAttribut('beschreibung',$betriebsmittel->beschreibung,true);
			$oRdf->obj[$i]->setAttribut('betriebsmittel_id',$betriebsmittel->betriebsmittel_id,true);
			$oRdf->obj[$i]->setAttribut('anmerkung',$betriebsmittel->anmerkung,true);

			$oRdf->addSequence($betriebsmittel->stundenplan_betriebsmittel_id);
		}

	}
	else
		die('Falsche Parameteruebergabe');
	$oRdf->sendRdfText();
}
elseif(isset($_REQUEST['von']) && isset($_REQUEST['bis']) && $_REQUEST['xmlformat']=='xml')
{
	$db = new basis_db();
	$qry = '
	SELECT
		tbl_stundenplan.datum,
		tbl_stundenplan.stunde,
		tbl_stunde.beginn,
		tbl_stunde.ende,
		tbl_stundenplan.ort_kurzbz,
		tbl_betriebsmittel.beschreibung,
		tbl_stundenplan_betriebsmittel.anmerkung,
		tbl_lehrveranstaltung.bezeichnung,
		tbl_stundenplan.mitarbeiter_uid,
		tbl_stundenplan.lehreinheit_id
	FROM
		lehre.tbl_stundenplan_betriebsmittel
		JOIN lehre.tbl_stundenplan ON(stundenplandev_id=stundenplan_id)
		JOIN wawi.tbl_betriebsmittel USING(betriebsmittel_id)
		JOIN lehre.tbl_stunde USING(stunde)
		JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
	WHERE
		tbl_stundenplan.datum>='.$db->db_add_param($_REQUEST['von']).'
		AND tbl_stundenplan.datum<='.$db->db_add_param($_REQUEST['bis']).'
	ORDER BY datum, ort_kurzbz, stunde';
	
	header("Content-type: application/xhtml+xml");
	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	echo '<stundenplan_betriebsmittel>';
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
		{
			$obj = array();
			$obj['ort_kurzbz']=$row->ort_kurzbz;
			$obj['stunde']=$row->stunde;
			$obj['beginn']=$row->beginn;
			$obj['ende']=$row->ende;
			$obj['mitarbeiter_uid']=$row->mitarbeiter_uid;
			$obj['beschreibung']=$row->beschreibung;
			$obj['anmerkung']=$row->anmerkung;
			$obj['lvbezeichnung']=$row->bezeichnung;
			$data[$row->datum][$row->lehreinheit_id][$row->stunde][]=$obj;
		}
	}
	foreach($data as $datum=>$tage)
	{
		echo '<tage>';
		echo '<datum><![CDATA['.$datum_obj->formatDatum($datum,'d.m.Y').']]></datum>';
		foreach($tage as $datum=>$lehreinheiten)
		{
			echo '<lehreinheit>';
			foreach($lehreinheiten as $lehreinheit_id=>$stunden)
			{
				echo '<stunde>';
				foreach($stunden as $stunde=>$obj)
				{
					echo '<item>';
					echo '<ort_kurzbz><![CDATA['.$obj['ort_kurzbz'].']]></ort_kurzbz>';
					echo '<stunde><![CDATA['.$obj['stunde'].']]></stunde>';
					echo '<stunde_beginn><![CDATA['.mb_substr($obj['beginn'],0,5).']]></stunde_beginn>';
					echo '<stunde_ende><![CDATA['.mb_substr($obj['ende'],0,5).']]></stunde_ende>';
					echo '<mitarbeiter_uid><![CDATA['.$obj['mitarbeiter_uid'].']]></mitarbeiter_uid>';
					echo '<beschreibung><![CDATA['.$obj['beschreibung'].']]></beschreibung>';
					echo '<anmerkung><![CDATA['.$obj['anmerkung'].']]></anmerkung>';
					echo '<lvbezeichnung><![CDATA['.$obj['lvbezeichnung'].']]></lvbezeichnung>';
					echo '</item>';
				}
				echo '</stunde>';
			}
			echo '</lehreinheit>';
		}
		echo '</tage>';
	}
	echo '</stundenplan_betriebsmittel>';
}
?>
