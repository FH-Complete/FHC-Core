<?php
/* Copyright (C) 2006 Technikum-Wien
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
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/xhtml+xml");

require_once('../config/vilesci.config.inc.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/functions.inc.php');
require_once('../include/rdf.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');

$user = get_uid();

$hier='';
$einheit_kurzbz=(isset($_GET['einheit'])?$_GET['einheit']:'');
$grp=(isset($_GET['grp'])?$_GET['grp']:'');
$ver=(isset($_GET['ver'])?$_GET['ver']:'');
$sem=(isset($_GET['sem'])?$_GET['sem']:'');
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:-1);
$uid=(isset($_GET['uid'])?$_GET['uid']:'');
$fachbereich_kurzbz=(isset($_GET['fachbereich_kurzbz'])?$_GET['fachbereich_kurzbz']:'');
$orgform=(isset($_GET['orgform'])?$_GET['orgform']:'');
$oe_kurzbz = (isset($_GET['oe_kurzbz'])?$_GET['oe_kurzbz']:'');
$filter = (isset($_GET['filter'])?$_GET['filter']:'');

loadVariables($user);

$stg_arr = array();
$stg_obj = new studiengang();
$stg_obj->getAll('typ, kurzbzlang', false);
foreach ($stg_obj->result as $row)
{
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;
}

$db = new basis_db();

// LVAs holen
$lvaDAO=new lehrveranstaltung();
if($uid!='' && $stg_kz!=-1) // Alle LVs eines Mitarbeiters
{
	$qry = "SELECT
				distinct on(lehrveranstaltung_id) * ,'' as studienplan_id, '' as studienplan_bezeichnung
			FROM
				campus.vw_lehreinheit
			WHERE
		        studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
				AND mitarbeiter_uid=".$db->db_add_param($uid);
	if($stg_kz!='')
		$qry .=" AND lv_studiengang_kz=".$db->db_add_param($stg_kz);

}
elseif($fachbereich_kurzbz!='') // Alle LVs eines Fachbereiches
{
	// LVs lt Studienplan
	if($uid=='')
	{
		$qry="
			SELECT
				distinct on (lehrveranstaltung_id)
				tbl_lehrveranstaltung.studiengang_kz as lv_studiengang_kz, tbl_lehrveranstaltung.semester as lv_semester,
				tbl_lehrveranstaltung.kurzbz as lv_kurzbz, tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung, tbl_lehrveranstaltung.ects as lv_ects,
				tbl_lehrveranstaltung.lehreverzeichnis as lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor as lv_planfaktor,
				tbl_lehrveranstaltung.planlektoren as lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten as lv_planpersonalkosten,
				tbl_lehrveranstaltung.plankostenprolektor as lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz as lv_orgform_kurzbz,
				tbl_lehrveranstaltung.lehrveranstaltung_id,
				tbl_lehrveranstaltung.lehrform_kurzbz as lehrform_kurzbz,
				tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz,
				tbl_lehrveranstaltung.bezeichnung_english as lv_bezeichnung_english,
				tbl_lehrveranstaltung.studiengang_kz, tbl_studienplan_lehrveranstaltung.semester, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.semesterstunden,
				tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.aktiv,
				tbl_studienplan.studienplan_id::text, tbl_studienplan.bezeichnung as studienplan_bezeichnung, tbl_lehrveranstaltung.lehrtyp_kurzbz
			FROM
				lehre.tbl_lehrveranstaltung
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
			WHERE
				tbl_lehrveranstaltung.oe_kurzbz=(Select oe_kurzbz from public.tbl_fachbereich where fachbereich_kurzbz=".$db->db_add_param($fachbereich_kurzbz).")
				AND tbl_studienplan_semester.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
				AND tbl_lehrveranstaltung.aktiv
			UNION ";
	}
	else
		$qry='';
	$qry .= "
		SELECT
				distinct on(lehrveranstaltung_id)
				lv_studiengang_kz, lv_semester, lv_kurzbz, lv_bezeichnung, lv_ects,
				lv_lehreverzeichnis, lv_planfaktor, lv_planlektoren, lv_planpersonalkosten,
				lv_plankostenprolektor, lv_orgform_kurzbz, lehrveranstaltung_id,
				lehrform_kurzbz, lv_lehrform_kurzbz, lv_bezeichnung_english, studiengang_kz, semester, anmerkung, sprache, semesterstunden,
				lehre, aktiv,
				'' as studienplan_id, '' as studienplan_bezeichnung,
				(SELECT lehrtyp_kurzbz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=vw_lehreinheit.lehrveranstaltung_id) as lehrtyp_kurzbz
			FROM
				campus.vw_lehreinheit
			WHERE
	        	studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
				AND fachbereich_kurzbz=".$db->db_add_param($fachbereich_kurzbz);
	if($uid!='')
		$qry.=" AND mitarbeiter_uid=".$db->db_add_param($uid);
	else
	{
	$qry.=" AND lehrveranstaltung_id NOT IN (SELECT lehrveranstaltung_id
		FROM
			lehre.tbl_lehrveranstaltung
			JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
			JOIN lehre.tbl_studienplan USING(studienplan_id)
			JOIN lehre.tbl_studienordnung USING(studienordnung_id)
			JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
		WHERE
			tbl_lehrveranstaltung.oe_kurzbz=(Select oe_kurzbz from public.tbl_fachbereich where fachbereich_kurzbz=".$db->db_add_param($fachbereich_kurzbz).")
			AND tbl_studienplan_semester.studiensemester_kurzbz=".$db->db_add_param($semester_aktuell).")";
	}

}
elseif($oe_kurzbz!='') // Alle LVs einer Organisationseinheit
{
	$qry="
		SELECT
			distinct on (lehrveranstaltung_id)
			tbl_lehrveranstaltung.studiengang_kz as lv_studiengang_kz, tbl_lehrveranstaltung.semester as lv_semester,
			tbl_lehrveranstaltung.kurzbz as lv_kurzbz, tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung, tbl_lehrveranstaltung.ects as lv_ects,
			tbl_lehrveranstaltung.lehreverzeichnis as lv_lehreverzeichnis, tbl_lehrveranstaltung.planfaktor as lv_planfaktor,
			tbl_lehrveranstaltung.planlektoren as lv_planlektoren, tbl_lehrveranstaltung.planpersonalkosten as lv_planpersonalkosten,
			tbl_lehrveranstaltung.plankostenprolektor as lv_plankostenprolektor, tbl_lehrveranstaltung.orgform_kurzbz as lv_orgform_kurzbz,
			tbl_lehrveranstaltung.lehrveranstaltung_id,
			tbl_lehrveranstaltung.lehrform_kurzbz as lehrform_kurzbz,
			tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz,
			tbl_lehrveranstaltung.bezeichnung_english as lv_bezeichnung_english,
			tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.anmerkung, tbl_lehrveranstaltung.sprache, tbl_lehrveranstaltung.semesterstunden,
			tbl_lehrveranstaltung.lehre, tbl_lehrveranstaltung.aktiv,
			'' as studienplan_id, '' as studienplan_bezeichnung, tbl_lehrveranstaltung.lehrtyp_kurzbz
		FROM
			lehre.tbl_lehrveranstaltung
		WHERE
			tbl_lehrveranstaltung.oe_kurzbz=".$db->db_add_param($oe_kurzbz)."
			AND tbl_lehrveranstaltung.aktiv
		";

	if(isset($sem) && $sem!='')
		$qry.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($sem);
}
elseif($filter != '')
{
	$additionalfilter = '';
	if(is_numeric($filter))
	{
		$additionalfilter.= " OR lehrveranstaltung_id=".$db->db_add_param($filter)."
			OR lehreinheit_id=".$db->db_add_param($filter);
	}

	$qry = "
		SELECT
				distinct on(lehrveranstaltung_id)
				lv_studiengang_kz, lv_semester, lv_kurzbz, lv_bezeichnung, lv_ects,
				lv_lehreverzeichnis, lv_planfaktor, lv_planlektoren, lv_planpersonalkosten,
				lv_plankostenprolektor, lv_orgform_kurzbz, lehrveranstaltung_id,
				lehrform_kurzbz, lv_lehrform_kurzbz, lv_bezeichnung_english, studiengang_kz, semester, anmerkung, sprache, semesterstunden,
				lehre, aktiv,
				'' as studienplan_id, '' as studienplan_bezeichnung,
				(SELECT lehrtyp_kurzbz FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id=vw_lehreinheit.lehrveranstaltung_id) as lehrtyp_kurzbz
			FROM
				campus.vw_lehreinheit
			WHERE
				studiensemester_kurzbz=".$db->db_add_param($semester_aktuell)."
			 	AND
				(lower(lv_bezeichnung) like '%".$db->db_escape(mb_strtolower($filter))."%'
				OR lower(lv_bezeichnung_english) like '%".$db->db_escape(mb_strtolower($filter))."%'
				$additionalfilter
				)
		";
}
else
{
	if($sem=='')
		$sem=null;
	if($orgform=='')
		$orgform=null;
	$stp_ids=array();
	$stpl_main = new studienplan();

	$stpl_main->getStudienplaeneFromSem($stg_kz, $semester_aktuell, $sem, $orgform);
	foreach($stpl_main->result as $row_stp)
	{
		$stp_ids_arr[]=array('stpid'=>$row_stp->studienplan_id,'semester'=>$row_stp->semester);
		$stp_ids[]=$row_stp->studienplan_id;
	}
	$qry='';

	if(count($stp_ids)>0)
	{
		// Alle Lehrveranstaltungen die lt Studienplan zugeordnet sind
		$qry.= "SELECT lehrveranstaltung_id, kurzbz as lv_kurzbz, tbl_lehrveranstaltung.bezeichnung as lv_bezeichnung, bezeichnung_english as lv_bezeichnung_english, studiengang_kz,
				tbl_studienplan_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache,
				ects as lv_ects, semesterstunden, anmerkung, lehre, lehreverzeichnis as lv_lehreverzeichnis, tbl_lehrveranstaltung.aktiv,
				planfaktor as lv_planfaktor, planlektoren as lv_planlektoren, planpersonalkosten as lv_planpersonalkosten,
				plankostenprolektor as lv_plankostenprolektor, lehrform_kurzbz as lv_lehrform_kurzbz, tbl_lehrveranstaltung.orgform_kurzbz,
				tbl_studienplan_lehrveranstaltung.studienplan_id::text as studienplan_id, tbl_studienplan.bezeichnung as studienplan_bezeichnung, tbl_studienplan_lehrveranstaltung.studienplan_lehrveranstaltung_id_parent::text,
				tbl_lehrveranstaltung.lehrtyp_kurzbz
			FROM
				lehre.tbl_lehrveranstaltung
				JOIN lehre.tbl_studienplan_lehrveranstaltung USING(lehrveranstaltung_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
			WHERE (1!=1 ";

		foreach($stp_ids_arr as $elem)
		{
			$qry.= "OR (
		 		studienplan_id=".$db->db_add_param($elem['stpid'])."
				AND tbl_studienplan_lehrveranstaltung.semester=".$db->db_add_param($elem['semester'])." )";
		}
		$qry.=") AND tbl_lehrveranstaltung.aktiv";
		$qry.=" UNION ";
	}

	// Zusaetzliche alle LVs die eine Lehreinheit zugeordnet haben
	$qry.="SELECT DISTINCT on(lehrveranstaltung_id) lehrveranstaltung_id, kurzbz as lv_kurzbz, bezeichnung as lv_bezeichnung, bezeichnung_english as lv_bezeichnung_english, studiengang_kz,
				semester, tbl_lehrveranstaltung.sprache, ects as lv_ects, semesterstunden, tbl_lehrveranstaltung.anmerkung,
				tbl_lehrveranstaltung.lehre, lehreverzeichnis as lv_lehreverzeichnis, aktiv, planfaktor as lv_planfaktor,
				planlektoren as lv_planlektoren, planpersonalkosten as lv_planpersonalkosten,
				plankostenprolektor as lv_plankostenprolektor, tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz, tbl_lehrveranstaltung.orgform_kurzbz,
				''::text as studienplan_id, '' as studienplan_bezeichnung, '' as studienplan_lehrveranstaltung_id_parent,
				tbl_lehrveranstaltung.lehrtyp_kurzbz
			FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING (lehrveranstaltung_id)
			WHERE 1=1";
	if($stg_kz!='')
		$qry.=" AND studiengang_kz=".$db->db_add_param($stg_kz);

	$qry.=" AND studiensemester_kurzbz=".$db->db_add_param($semester_aktuell);
	if($sem!='')
		$qry.=" AND semester=".$db->db_add_param($sem);
	if($orgform!='')
		$qry.=" AND (orgform_kurzbz=".$db->db_add_param($orgform)." OR orgform_kurzbz is null)";
	if(count($stp_ids)>0)
	{
		// Ohne die vom Studienplan, da diese sonst doppelt sind
		$qry.=" AND NOT EXISTS (SELECT 1 FROM lehre.tbl_studienplan_lehrveranstaltung where studienplan_id in (".$db->db_implode4SQL($stp_ids).")
								AND lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND tbl_lehrveranstaltung.aktiv)";
	}
/*
	$qry = 'SELECT
				lehrveranstaltung_id, lv_kurzbz, lv_bezeichnung, lv_bezeichnung_english, studiengang_kz,
				semester, sprache, lv_ects, semesterstunden, anmerkung, lehre, lv_lehreverzeichnis, aktiv,
				lv_planfaktor, lv_planlektoren, lv_planpersonalkosten, lv_plankostenprolektor, lv_lehrform_kurzbz,
				orgform_kurzbz, studienplan_lehrveranstaltung_id_parent, lehrtyp_kurzbz,
				array_agg(studienplan_id) as studienplan_id, array_agg(studienplan_bezeichnung) as studienplan_bezeichnung
			FROM ('.$qry.') a
			GROUP BY
			lehrveranstaltung_id, lv_kurzbz, lv_bezeichnung, lv_bezeichnung_english, studiengang_kz,
			semester, sprache, lv_ects, semesterstunden, anmerkung, lehre, lv_lehreverzeichnis, aktiv,
			lv_planfaktor, lv_planlektoren, lv_planpersonalkosten, lv_plankostenprolektor, lv_lehrform_kurzbz,
			orgform_kurzbz, studienplan_lehrveranstaltung_id_parent, lehrtyp_kurzbz';
*/
}
//die($qry);
if(!$result = $db->db_query($qry))
	die($db->db_last_error().'<BR>'.$qry);


$oRdf = new rdf('LVA','http://www.technikum-wien.at/lehrveranstaltung_einheiten');
$oRdf->sendHeader();

	//foreach ($lvaDAO->lehrveranstaltungen as $row_lva)
	while($row_lva = $db->db_fetch_object($result))
	{
		//Fachbereichskoordinatoren laden
		$qry_fbk = "SELECT kurzbz FROM public.tbl_mitarbeiter LEFT JOIN public.tbl_benutzer ON(uid=mitarbeiter_uid) WHERE tbl_benutzer.aktiv AND mitarbeiter_uid =
						(
						SELECT
							COALESCE(tbl_lehrveranstaltung.koordinator, uid) as koordinator
						FROM
							lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_benutzerfunktion, public.tbl_studiensemester, public.tbl_studiengang, public.tbl_fachbereich
						WHERE
							tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($row_lva->lehrveranstaltung_id)." AND
							tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
							tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND
							lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz AND
							tbl_fachbereich.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz AND
							tbl_benutzerfunktion.funktion_kurzbz='fbk' AND
							tbl_lehreinheit.studiensemester_kurzbz=tbl_studiensemester.studiensemester_kurzbz AND
							tbl_benutzerfunktion.oe_kurzbz=tbl_studiengang.oe_kurzbz AND
							(tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) AND
							(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now()) AND
							tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz ORDER BY tbl_studiensemester.ende DESC LIMIT 1 ) ";

		if(!$result_fbk = $db->db_query($qry_fbk))
			die('Fehlerhafte Abfrage');

		$fbk='';
		while($row_fbk = $db->db_fetch_object($result_fbk))
		{
			$fbk.=$row_fbk->kurzbz.' ';
		}

		if($fbk!='')
			$fbk='Koordinator: '.$fbk;

		$i=$oRdf->newObjekt($row_lva->lehrveranstaltung_id);
		$oRdf->obj[$i]->setAttribut('lehrveranstaltung_id',$row_lva->lehrveranstaltung_id);
		$oRdf->obj[$i]->setAttribut('kurzbz',$row_lva->lv_kurzbz);
		$oRdf->obj[$i]->setAttribut('bezeichnung',$row_lva->lv_bezeichnung);
		$oRdf->obj[$i]->setAttribut('bezeichnung_english',$row_lva->lv_bezeichnung_english);
		$oRdf->obj[$i]->setAttribut('studiengang_kz',$row_lva->studiengang_kz);
		$oRdf->obj[$i]->setAttribut('studiengang',$stg_arr[$row_lva->studiengang_kz]);
		$oRdf->obj[$i]->setAttribut('semester',$row_lva->semester);
		$oRdf->obj[$i]->setAttribut('sprache',$row_lva->sprache);
		$oRdf->obj[$i]->setAttribut('ects',$row_lva->lv_ects);
		$oRdf->obj[$i]->setAttribut('semesterstunden',$row_lva->semesterstunden);
		$oRdf->obj[$i]->setAttribut('planstunden','');
		$oRdf->obj[$i]->setAttribut('anmerkung',$row_lva->anmerkung);
		$oRdf->obj[$i]->setAttribut('lehre',($row_lva->lehre=='t'?'Ja':'Nein'));
		$oRdf->obj[$i]->setAttribut('lehreverzeichnis',$row_lva->lv_lehreverzeichnis);
		$oRdf->obj[$i]->setAttribut('aktiv',($row_lva->aktiv=='t'?'Ja':'Nein'));
		$oRdf->obj[$i]->setAttribut('planfaktor',$row_lva->lv_planfaktor);
		$oRdf->obj[$i]->setAttribut('planlektoren',$row_lva->lv_planlektoren);
		$oRdf->obj[$i]->setAttribut('planpersonalkosten',$row_lva->lv_planpersonalkosten);
		$oRdf->obj[$i]->setAttribut('plankostenprolektor',$row_lva->lv_plankostenprolektor);
		$oRdf->obj[$i]->setAttribut('orgform_kurzbz',(isset($row_lva->orgform_kurzbz)?$row_lva->orgform_kurzbz:''));
		$oRdf->obj[$i]->setAttribut('studienplan_id',$row_lva->studienplan_id);
		$oRdf->obj[$i]->setAttribut('studienplan_bezeichnung',$row_lva->studienplan_bezeichnung);
		$oRdf->obj[$i]->setAttribut('lehrtyp_kurzbz',$row_lva->lehrtyp_kurzbz);

		$oRdf->obj[$i]->setAttribut('lehreinheit_id','');
		$oRdf->obj[$i]->setAttribut('lehrform_kurzbz',$row_lva->lv_lehrform_kurzbz);
		$oRdf->obj[$i]->setAttribut('stundenblockung','');
		$oRdf->obj[$i]->setAttribut('wochenrythmus','');
		$oRdf->obj[$i]->setAttribut('startkw','');
		$oRdf->obj[$i]->setAttribut('raumtyp','');
		$oRdf->obj[$i]->setAttribut('raumtypalternativ','');
		$oRdf->obj[$i]->setAttribut('gruppen','');
		$oRdf->obj[$i]->setAttribut('lektoren',$fbk);
		$oRdf->obj[$i]->setAttribut('fachbereich','');

		if(isset($row_lva->studienplan_lehrveranstaltung_id_parent) && $row_lva->studienplan_lehrveranstaltung_id_parent!='')
		{

			// Wenn ein Parent vorhanden ist, wird er diesem untergeordnet
			$stpllv = new studienplan();
			if($stpllv->loadStudienplanLehrveranstaltung($row_lva->studienplan_lehrveranstaltung_id_parent))
			{
				$oRdf->addSequence($row_lva->lehrveranstaltung_id, $stpllv->lehrveranstaltung_id);
			}
		}
		else
			$oRdf->addSequence($row_lva->lehrveranstaltung_id);

		//zugehoerige LE holen
		$le = new lehreinheit();

		if(!$le->load_lehreinheiten($row_lva->lehrveranstaltung_id, $semester_aktuell, $uid, $fachbereich_kurzbz))
			echo "Fehler: $le->errormsg";

		foreach ($le->lehreinheiten as $row_le)
		{
			//Lehrfach holen
			$qry = "SELECT kurzbz, bezeichnung FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$row_le->lehrfach_id'";
			$result_lf = $db->db_query($qry);
			$row_lf = $db->db_fetch_object($result_lf);

			//Gruppen holen
			$qry = "SELECT
					upper(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel,
					tbl_lehreinheitgruppe.*,
					tbl_gruppe.direktinskription
					FROM
						lehre.tbl_lehreinheitgruppe
						LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
						LEFT JOIN public.tbl_gruppe USING(gruppe_kurzbz)
					WHERE lehreinheit_id=".$db->db_add_param($row_le->lehreinheit_id);

			$result_grp = $db->db_query($qry);
			$grp='';
			while($row_grp = $db->db_fetch_object($result_grp))
			{
				if($row_grp->gruppe_kurzbz=='')
					$grp.=' '.$row_grp->kuerzel.trim($row_grp->semester).trim($row_grp->verband).trim($row_grp->gruppe);
				else
				{
					// Direkte Gruppen werden nicht angezeigt
					if(!$db->db_parse_bool($row_grp->direktinskription))
						$grp.=' '.$row_grp->gruppe_kurzbz;
				}
			}
			//Lektoren und Stunden holen
			$qry = "SELECT kurzbz, semesterstunden, planstunden FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id='$row_le->lehreinheit_id'";
			$result_lkt = $db->db_query($qry);
			$lkt='';
			$semesterstunden='';
			$planstunden='';
			while($row_lkt = $db->db_fetch_object($result_lkt))
			{
				$lkt.=$row_lkt->kurzbz.' ';
				$semesterstunden.=$row_lkt->semesterstunden.' ';
				$planstunden.=$row_lkt->planstunden.' ';
			}
			$qry = "SELECT
					tbl_organisationseinheit.bezeichnung,
       				tbl_organisationseinheit.organisationseinheittyp_kurzbz
				FROM
					public.tbl_organisationseinheit,
					lehre.tbl_lehrveranstaltung as lehrfach,
					lehre.tbl_lehreinheit
				WHERE
					tbl_organisationseinheit.oe_kurzbz = lehrfach.oe_kurzbz
					AND lehrfach.lehrveranstaltung_id = tbl_lehreinheit.lehrfach_id
					AND tbl_lehreinheit.lehreinheit_id = ".$db->db_add_param($row_le->lehreinheit_id, FHC_INTEGER);

			$fachbereich='';
			if($result_fb = $db->db_query($qry))
				if($row_fb = $db->db_fetch_object($result_fb))
					$fachbereich = $row_fb->bezeichnung.' ('.$row_fb->organisationseinheittyp_kurzbz.')';


			$i=$oRdf->newObjekt($row_lva->lehrveranstaltung_id.'/'.$row_le->lehreinheit_id);
			$oRdf->obj[$i]->setAttribut('lehrveranstaltung_id',$row_lva->lehrveranstaltung_id);
			$oRdf->obj[$i]->setAttribut('kurzbz',$row_lf->kurzbz);
			$oRdf->obj[$i]->setAttribut('bezeichnung',$row_lf->bezeichnung);
			$oRdf->obj[$i]->setAttribut('bezeichnung_english','');
			$oRdf->obj[$i]->setAttribut('studiengang_kz',$row_lva->studiengang_kz);
			$oRdf->obj[$i]->setAttribut('studiengang',$stg_arr[$row_lva->studiengang_kz]);
			$oRdf->obj[$i]->setAttribut('semester',$row_lva->semester);
			$oRdf->obj[$i]->setAttribut('sprache',$row_le->sprache);
			$oRdf->obj[$i]->setAttribut('ects','');
			$oRdf->obj[$i]->setAttribut('semesterstunden',$semesterstunden);
			$oRdf->obj[$i]->setAttribut('planstunden',$planstunden);
			$oRdf->obj[$i]->setAttribut('anmerkung',$row_le->anmerkung);
			$oRdf->obj[$i]->setAttribut('lehre',($row_le->lehre=='t'?'Ja':'Nein'));
			$oRdf->obj[$i]->setAttribut('lehreverzeichnis','');
			$oRdf->obj[$i]->setAttribut('aktiv','');
			$oRdf->obj[$i]->setAttribut('planfaktor','');
			$oRdf->obj[$i]->setAttribut('planlektoren','');
			$oRdf->obj[$i]->setAttribut('planpersonalkosten','');
			$oRdf->obj[$i]->setAttribut('plankostenprolektor','');
			$oRdf->obj[$i]->setAttribut('orgform_kurzbz','');

			$oRdf->obj[$i]->setAttribut('lehreinheit_id',$row_le->lehreinheit_id);
			$oRdf->obj[$i]->setAttribut('studiensemester_kurzbz',$row_le->studiensemester_kurzbz);
			$oRdf->obj[$i]->setAttribut('lehrfach_id',$row_le->lehrfach_id);
			$oRdf->obj[$i]->setAttribut('lehrform_kurzbz',$row_le->lehrform_kurzbz);
			$oRdf->obj[$i]->setAttribut('stundenblockung',$row_le->stundenblockung);
			$oRdf->obj[$i]->setAttribut('wochenrythmus',$row_le->wochenrythmus);
			$oRdf->obj[$i]->setAttribut('startkw',$row_le->start_kw);
			$oRdf->obj[$i]->setAttribut('raumtyp',$row_le->raumtyp);
			$oRdf->obj[$i]->setAttribut('raumtypalternativ',$row_le->raumtypalternativ);
			$oRdf->obj[$i]->setAttribut('unr',$row_le->unr);
			$oRdf->obj[$i]->setAttribut('lvnr',$row_le->lvnr);
			$oRdf->obj[$i]->setAttribut('gruppen',$grp);
			$oRdf->obj[$i]->setAttribut('lektoren',$lkt);
			$oRdf->obj[$i]->setAttribut('fachbereich',$fachbereich);
			$oRdf->obj[$i]->setAttribut('gewicht',$row_le->gewicht);

			$oRdf->addSequence($row_lva->lehrveranstaltung_id.'/'.$row_le->lehreinheit_id,$row_lva->lehrveranstaltung_id);

		}
	}


$oRdf->sendRdfText();
?>
