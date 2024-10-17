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
require_once('../include/globals.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/basis_db.class.php');
require_once('../include/betriebsmittel.class.php');
require_once('../include/benutzerberechtigung.class.php');
require_once('../include/datum.class.php');
require_once('../include/stunde.class.php');
require_once('../include/mitarbeiter.class.php');

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
	$von = $datum_obj->formatDatum($_REQUEST['von'], 'Y-m-d');
	$bis = $datum_obj->formatDatum($_REQUEST['bis'], 'Y-m-d');

	$db = new basis_db();

	$qry='
	SELECT a.*, tbl_lehrveranstaltung.bezeichnung as lvbezeichnung, tbl_studiengang.kurzbzlang as stg
	FROM
		(
			SELECT
				tbl_stundenplan.datum,
				tbl_stundenplan.ort_kurzbz,
				tbl_stundenplan.lehreinheit_id,
				tbl_lehreinheit.lehrveranstaltung_id,
				min(tbl_stundenplan.stunde) as von,
				max(tbl_stundenplan.stunde) as bis,
				array_agg(tbl_betriebsmittel.beschreibung) as beschreibung,
				array_agg(tbl_stundenplan_betriebsmittel.anmerkung) as anmerkung,
				array_agg(tbl_stundenplan.mitarbeiter_uid) as mitarbeiter_uid,
				array_agg(tbl_stundenplan.titel) as titel
			FROM
				lehre.tbl_stundenplan_betriebsmittel
				JOIN lehre.tbl_stundenplan ON(stundenplandev_id=stundenplan_id)
				JOIN wawi.tbl_betriebsmittel USING(betriebsmittel_id)
				JOIN lehre.tbl_stunde USING(stunde)
				JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
			WHERE
				tbl_stundenplan.datum>='.$db->db_add_param($von).'
				AND tbl_stundenplan.datum<='.$db->db_add_param($bis).'
			GROUP BY datum, tbl_stundenplan.ort_kurzbz, lehreinheit_id, lehrveranstaltung_id
		) a
		JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
		JOIN public.tbl_studiengang USING(studiengang_kz)
	ORDER BY datum, ort_kurzbz,von';

	$stunde = new stunde();
	$stunde->loadAll();
	foreach($stunde->stunden as $row)
	{
		$stunden_arr[$row->stunde]['beginn']=$row->beginn->format('H:i');
		$stunden_arr[$row->stunde]['ende']=$row->ende->format('H:i');
	}
	$stunde->loadAll();
	header("Content-type: application/xhtml+xml");
	$xml = "<?xml version='1.0' encoding='UTF-8' standalone='yes'?>";
	echo '<stundenplan_betriebsmittel>';
	$data = array();
	if($result = $db->db_query($qry))
	{
		$lastdatum = '';
		while($row = $db->db_fetch_object($result))
		{
			if($lastdatum!=$row->datum)
			{
				if($lastdatum!='')
					echo '</tage>';
				echo '<tage>';
				echo '<datum><![CDATA['.$datum_obj->formatDatum($row->datum,'d.m.Y').']]></datum>';
				echo '<wochentag><![CDATA['.$tagbez[1][$datum_obj->formatDatum($row->datum,'w')].']]></wochentag>';

				$lastdatum = $row->datum;
			}

			echo '<item>';
			echo '<ort_kurzbz><![CDATA['.$row->ort_kurzbz.']]></ort_kurzbz>';
			echo '<stunde_von><![CDATA['.$row->von.']]></stunde_von>';
			echo '<stunde_bis><![CDATA['.$row->bis.']]></stunde_bis>';
			echo '<stunde_beginn><![CDATA['.mb_substr($stunden_arr[$row->von]['beginn'],0,5).']]></stunde_beginn>';
			echo '<stunde_ende><![CDATA['.mb_substr($stunden_arr[$row->bis]['ende'],0,5).']]></stunde_ende>';

			$mitarbeiter = array_unique($db->db_parse_array($row->mitarbeiter_uid));
			$ma_obj = new mitarbeiter($mitarbeiter[0]);
			echo '<mitarbeiter_uid><![CDATA['.$ma_obj->uid.']]></mitarbeiter_uid>';
			echo '<nachname><![CDATA['.$ma_obj->nachname.']]></nachname>';
			echo '<vorname><![CDATA['.$ma_obj->vorname.']]></vorname>';

			$beschreibungen = array_unique($db->db_parse_array($row->beschreibung));
			echo '<beschreibungen>';
			foreach($beschreibungen as $beschreibung)
			{
				if($beschreibung!='')
					echo '<beschreibung><![CDATA['.$beschreibung.']]></beschreibung>';
			}
			echo '</beschreibungen>';

			$anmerkungen = array_unique($db->db_parse_array($row->anmerkung));
			echo '<anmerkungen>';
			foreach($anmerkungen as $anmerkung)
			{
				if($anmerkung!='')
					echo '<anmerkung><![CDATA['.$anmerkung.']]></anmerkung>';
			}
			echo '</anmerkungen>';
			$titel = array_filter(array_unique($db->db_parse_array($row->titel)));
			echo '<titel><![CDATA['.implode($titel,',').']]></titel>';
			echo '<lvbezeichnung><![CDATA['.$row->lvbezeichnung.']]></lvbezeichnung>';
			echo '<studiengang_kurzbzlang><![CDATA['.$row->stg.']]></studiengang_kurzbzlang>';
			echo '</item>';
		}
		if($lastdatum!='')
			echo '</tage>';
	}
	echo '</stundenplan_betriebsmittel>';
}
?>
