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
/*
 * Created on 02.12.2004
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
// header fuer no cache
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/vnd.mozilla.xul+xml");
// xml
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../vilesci/config.inc.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/lehreinheit.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/functions.inc.php');

// Datenbank Verbindung
if (!$conn = pg_pconnect(CONN_STRING))
   	$error_msg='Es konnte keine Verbindung zum Server aufgebaut werden!';
//ini_set('display_errors','0');
$user = get_uid();

/*
// test
$einheit_kurzbz='';
$grp='1';
$ver='A';
$sem=6;
$stg_kz=257;
*/

$hier='';
$einheit_kurzbz=(isset($_GET['einheit'])?$_GET['einheit']:'');
$grp=(isset($_GET['grp'])?$_GET['grp']:'');
$ver=(isset($_GET['ver'])?$_GET['ver']:'');
$sem=(isset($_GET['sem'])?$_GET['sem']:'');
$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:-1);
$uid=(isset($_GET['uid'])?$_GET['uid']:'');
$fachbereich_kurzbz=(isset($_GET['fachbereich_kurzbz'])?$_GET['fachbereich_kurzbz']:'');

loadVariables($conn, $user);

$stg_arr = array();
$stg_obj = new studiengang($conn);
$stg_obj->getAll('typ, kurzbzlang', false);
foreach ($stg_obj->result as $row)
{
	$stg_arr[$row->studiengang_kz]=$row->kuerzel;
}

// LVAs holen
$lvaDAO=new lehrveranstaltung($conn, null, true);
if($uid!='' && $stg_kz!=-1) // Alle LVs eines Mitarbeiters
{
	//$lvaDAO->loadLVAfromMitarbeiter($stg_kz, $uid, $semester_aktuell);
	$qry = "SELECT distinct on(lehrveranstaltung_id) * FROM campus.vw_lehreinheit WHERE
	        studiensemester_kurzbz='".addslashes($semester_aktuell)."' AND
	        mitarbeiter_uid='".addslashes($uid)."'";
	if($stg_kz!='') //$stg_kz!='0'
		$qry .=" AND studiengang_kz='".addslashes($stg_kz)."'";

}
elseif($fachbereich_kurzbz!='') // Alle LVs eines Fachbereiches
{
	$qry = "SELECT distinct on(lehrveranstaltung_id) * FROM campus.vw_lehreinheit WHERE
	        studiensemester_kurzbz='".addslashes($semester_aktuell)."' AND
	        fachbereich_kurzbz='".addslashes($fachbereich_kurzbz)."'";
	if($uid!='')
		$qry.=" AND mitarbeiter_uid='".addslashes($uid)."'";
}
else
{
	$qry = "SELECT lehrveranstaltung_id, kurzbz as lv_kurzbz, bezeichnung as lv_bezeichnung, studiengang_kz, semester, sprache,
				ects as lv_ects, semesterstunden, anmerkung, lehre, lehreverzeichnis as lv_lehreverzeichnis, aktiv,
				planfaktor as lv_planfaktor, planlektoren as lv_planlektoren, planpersonalkosten as lv_planpersonalkosten,
				plankostenprolektor as lv_plankostenprolektor, lehrform_kurzbz as lv_lehrform_kurzbz
			FROM lehre.tbl_lehrveranstaltung
			WHERE aktiv ";
	if($stg_kz!='')
		$qry.=" AND	studiengang_kz='".addslashes($stg_kz)."'";
	if($sem!='')
		$qry.=" AND semester='".addslashes($sem)."'";

	$qry.=" UNION SELECT DISTINCT lehrveranstaltung_id, kurzbz as lv_kurzbz, bezeichnung as lv_bezeichnung, studiengang_kz,
				semester, tbl_lehrveranstaltung.sprache, ects as lv_ects, semesterstunden, tbl_lehrveranstaltung.anmerkung,
				tbl_lehrveranstaltung.lehre, lehreverzeichnis as lv_lehreverzeichnis, aktiv, planfaktor as lv_planfaktor,
				planlektoren as lv_planlektoren, planpersonalkosten as lv_planpersonalkosten,
				plankostenprolektor as lv_plankostenprolektor, tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz
			FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING (lehrveranstaltung_id)
			WHERE NOT aktiv ";
	if($stg_kz!='')
		$qry.=" AND studiengang_kz='".addslashes($stg_kz)."'";

	$qry.=" AND studiensemester_kurzbz='".addslashes($semester_aktuell)."'";
	if($sem!='')
		$qry.=" AND semester='".addslashes($sem)."'";
}

//echo $qry;

$rdf_url='http://www.technikum-wien.at/lehrveranstaltung_einheiten';
if(!$result = pg_query($conn, $qry))
	die(pg_last_error($conn).'<BR>'.$qry);
?>

<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:LVA="<?php echo $rdf_url; ?>/rdf#"
>

<?php

	//foreach ($lvaDAO->lehrveranstaltungen as $row_lva)
	while($row_lva = pg_fetch_object($result))
	{
		//Fachbereichskoordinatoren laden
		$qry_fbk = "SELECT kurzbz FROM public.tbl_mitarbeiter WHERE mitarbeiter_uid =
						(
						SELECT 
							COALESCE(koordinator, uid) as koordinator
						FROM
							lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrfach, public.tbl_benutzerfunktion 
						WHERE
							tbl_lehrveranstaltung.lehrveranstaltung_id='$row_lva->lehrveranstaltung_id' AND
							tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
							tbl_lehreinheit.lehrfach_id=tbl_lehrfach.lehrfach_id AND
							tbl_lehrfach.fachbereich_kurzbz=tbl_benutzerfunktion.fachbereich_kurzbz AND
							tbl_benutzerfunktion.funktion_kurzbz='fbk' AND 
							tbl_benutzerfunktion.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz LIMIT 1 ) ";
		
		$result_fbk = pg_query($conn, $qry_fbk);
		$fbk='';
		while($row_fbk = pg_fetch_object($result_fbk))
		{
			$fbk.=$row_fbk->kurzbz.' ';
		}

		if($fbk!='')
			$fbk='FBK: '.$fbk;

		//Lehrveranstaltung
		echo "
		<RDF:Description  id=\"".$row_lva->lehrveranstaltung_id."\"  about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" >
			<LVA:lehrveranstaltung_id>".$row_lva->lehrveranstaltung_id."</LVA:lehrveranstaltung_id>
			<LVA:kurzbz><![CDATA[".$row_lva->lv_kurzbz."]]></LVA:kurzbz>
			<LVA:bezeichnung><![CDATA[".$row_lva->lv_bezeichnung."]]></LVA:bezeichnung>
			<LVA:studiengang_kz>".$row_lva->studiengang_kz."</LVA:studiengang_kz>
			<LVA:studiengang>".$stg_arr[$row_lva->studiengang_kz]."</LVA:studiengang>
			<LVA:semester>".$row_lva->semester."</LVA:semester>
			<LVA:sprache><![CDATA[".$row_lva->sprache."]]></LVA:sprache>
			<LVA:ects>".$row_lva->lv_ects."</LVA:ects>
			<LVA:semesterstunden>".$row_lva->semesterstunden."</LVA:semesterstunden>
			<LVA:anmerkung><![CDATA[".$row_lva->anmerkung."]]></LVA:anmerkung>
			<LVA:lehre>".($row_lva->lehre=='t'?'Ja':'Nein')."</LVA:lehre>
			<LVA:lehreverzeichnis><![CDATA[".$row_lva->lv_lehreverzeichnis."]]></LVA:lehreverzeichnis>
			<LVA:aktiv>".($row_lva->aktiv=='t'?'Ja':'Nein')."</LVA:aktiv>
			<LVA:planfaktor>".$row_lva->lv_planfaktor."</LVA:planfaktor>
			<LVA:planlektoren>".$row_lva->lv_planlektoren."</LVA:planlektoren>
			<LVA:planpersonalkosten>".$row_lva->lv_planpersonalkosten."</LVA:planpersonalkosten>
			<LVA:plankostenprolektor>".$row_lva->lv_plankostenprolektor."</LVA:plankostenprolektor>

			<LVA:lehreinheit_id></LVA:lehreinheit_id>
			<LVA:lehrform_kurzbz>$row_lva->lv_lehrform_kurzbz</LVA:lehrform_kurzbz>
			<LVA:stundenblockung></LVA:stundenblockung>
			<LVA:wochenrythmus></LVA:wochenrythmus>
			<LVA:startkw></LVA:startkw>
			<LVA:raumtyp></LVA:raumtyp>
			<LVA:raumtypalternativ></LVA:raumtypalternativ>
			<LVA:gruppen></LVA:gruppen>
			<LVA:lektoren>$fbk</LVA:lektoren>
			<LVA:fachbereich></LVA:fachbereich>
		</RDF:Description>";
		$hier.="
      	<RDF:li>
      		<RDF:Seq about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" >";

		//zugehoerige LE holen
		$le = new lehreinheit($conn, null, true);

		if(!$le->load_lehreinheiten($row_lva->lehrveranstaltung_id, $semester_aktuell, $uid, $fachbereich_kurzbz))
			echo "Fehler: $le->errormsg";

		foreach ($le->lehreinheiten as $row_le)
		{
			//Lehrfach holen
			$qry = "SELECT kurzbz, bezeichnung FROM lehre.tbl_lehrfach WHERE lehrfach_id='$row_le->lehrfach_id'";
			$result_lf = pg_query($conn, $qry);
			$row_lf = pg_fetch_object($result_lf);

			//Gruppen holen
			$qry = "SELECT upper(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel, * FROM lehre.tbl_lehreinheitgruppe LEFT JOIN public.tbl_studiengang USING(studiengang_kz) WHERE lehreinheit_id='$row_le->lehreinheit_id'";
			$result_grp = pg_query($conn, $qry);
			$grp='';
			while($row_grp=pg_fetch_object($result_grp))
			{
				if($row_grp->gruppe_kurzbz=='')
					$grp.=' '.$row_grp->kuerzel.trim($row_grp->semester).trim($row_grp->verband).trim($row_grp->gruppe);
				else
					$grp.=' '.$row_grp->gruppe_kurzbz;
			}
			//Lektoren und Stunden holen
			$qry = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id='$row_le->lehreinheit_id'";
			$result_lkt = pg_query($conn, $qry);
			$lkt='';
			$semesterstunden='';
			while($row_lkt = pg_fetch_object($result_lkt))
			{
				$lkt.=$row_lkt->kurzbz.' ';
				$semesterstunden.=$row_lkt->semesterstunden.' ';
			}
			$qry = "SELECT tbl_fachbereich.bezeichnung FROM public.tbl_fachbereich, lehre.tbl_lehrfach, lehre.tbl_lehreinheit WHERE tbl_fachbereich.fachbereich_kurzbz=tbl_lehrfach.fachbereich_kurzbz AND tbl_lehrfach.lehrfach_id=tbl_lehreinheit.lehrfach_id AND tbl_lehreinheit.lehreinheit_id='$row_le->lehreinheit_id'";
			$fachbereich='';
			if($result_fb = pg_query($conn, $qry))
				if($row_fb = pg_fetch_object($result_fb))
					$fachbereich = $row_fb->bezeichnung;

			echo "
      		<RDF:Description  id=\"".$row_le->lehreinheit_id."\"  about=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."/$row_le->lehreinheit_id\" >
				<LVA:lehrveranstaltung_id>".$row_lva->lehrveranstaltung_id."</LVA:lehrveranstaltung_id>
				<LVA:kurzbz><![CDATA[".$row_lf->kurzbz."]]></LVA:kurzbz>
				<LVA:bezeichnung><![CDATA[".$row_lf->bezeichnung."]]></LVA:bezeichnung>
				<LVA:studiengang_kz>".$row_lva->studiengang_kz."</LVA:studiengang_kz>
				<LVA:studiengang>".$stg_arr[$row_lva->studiengang_kz]."</LVA:studiengang>
				<LVA:semester>".$row_lva->semester."</LVA:semester>
				<LVA:sprache><![CDATA[".$row_le->sprache."]]></LVA:sprache>
				<LVA:ects></LVA:ects>
				<LVA:semesterstunden><![CDATA[".$semesterstunden."]]></LVA:semesterstunden>
				<LVA:anmerkung><![CDATA[".$row_le->anmerkung."]]></LVA:anmerkung>
				<LVA:lehre>".($row_le->lehre?'Ja':'Nein')."</LVA:lehre>
				<LVA:lehreverzeichnis></LVA:lehreverzeichnis>
				<LVA:aktiv></LVA:aktiv>
				<LVA:planfaktor></LVA:planfaktor>
				<LVA:planlektoren></LVA:planlektoren>
				<LVA:planpersonalkosten></LVA:planpersonalkosten>
				<LVA:plankostenprolektor></LVA:plankostenprolektor>

				<LVA:lehreinheit_id>$row_le->lehreinheit_id</LVA:lehreinheit_id>
				<LVA:studiensemester_kurzbz>$row_le->studiensemester_kurzbz</LVA:studiensemester_kurzbz>
				<LVA:lehrfach_id>$row_le->lehrfach_id</LVA:lehrfach_id>
				<LVA:lehrform_kurzbz>$row_le->lehrform_kurzbz</LVA:lehrform_kurzbz>
				<LVA:stundenblockung>$row_le->stundenblockung</LVA:stundenblockung>
				<LVA:wochenrythmus>$row_le->wochenrythmus</LVA:wochenrythmus>
				<LVA:startkw>$row_le->start_kw</LVA:startkw>
				<LVA:raumtyp>$row_le->raumtyp</LVA:raumtyp>
				<LVA:raumtypalternativ>$row_le->raumtypalternativ</LVA:raumtypalternativ>
				<LVA:anmerkung><![CDATA[$row_le->anmerkung]]></LVA:anmerkung>
				<LVA:unr>$row_le->unr</LVA:unr>
				<LVA:lvnr>$row_le->lvnr</LVA:lvnr>
				<LVA:gruppen><![CDATA[$grp]]></LVA:gruppen>
				<LVA:lektoren><![CDATA[".$lkt."]]></LVA:lektoren>
				<LVA:fachbereich><![CDATA[".$fachbereich."]]></LVA:fachbereich>
      		</RDF:Description>";

			$hier.="
			<RDF:li resource=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id.'/'.$row_le->lehreinheit_id."\" />";
		}
		//<RDF:li resource=\"".$rdf_url.'/'.$row_lva->lehrveranstaltung_id."\" />
		$hier.="
      		</RDF:Seq>
      	</RDF:li>";
	}

	$hier="
  	<RDF:Seq about=\"".$rdf_url."/liste\">".$hier."
  	</RDF:Seq>";

	echo $hier;
?>


</RDF:RDF>
