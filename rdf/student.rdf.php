<?php
/* Copyright (C) 2006 fhcomplete.org
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
// header fuer no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");
// xml
if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
else
	echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
// DAO
require_once('../config/vilesci.config.inc.php');
require_once('../include/functions.inc.php');
require_once('../include/person.class.php');
require_once('../include/benutzer.class.php');
require_once('../include/student.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/datum.class.php');
require_once('../include/studiensemester.class.php');
require_once('../include/prestudent.class.php');
require_once('../include/studiengang.class.php');
require_once('../include/lehrveranstaltung.class.php');
require_once('../include/mitarbeiter.class.php');
require_once('../include/organisationsform.class.php');
require_once('../include/konto.class.php');
require_once('../include/reihungstest.class.php');
require_once('../include/studienordnung.class.php');
require_once('../include/studienplan.class.php');
require_once('../include/kontakt.class.php');
require_once('../include/notiz.class.php');

// *********** Funktionen *************************
function convdate($date)
{
	list($d,$m,$y) = explode('.',$date);
	return $y.'-'.$m.'-'.$d;
}

function checkfilter($row, $filter2, $buchungstyp = null)
{
	global $studiensemester_kurzbz, $kontofilterstg, $studiengang_kz;
	$db = new basis_db();
	$studstatusgrund = array();

	if($filter2=='dokumente')
	{
		// Alle Personen die noch nicht alle Dokumente gebracht haben
		$qry = "SELECT count(*) as anzahl FROM public.tbl_dokumentstudiengang WHERE
				dokument_kurzbz NOT IN(
					SELECT dokument_kurzbz FROM tbl_dokumentprestudent WHERE prestudent_id=".$db->db_add_param($row->prestudent_id).")
				AND studiengang_kz=".$db->db_add_param($row->studiengang_kz);
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->anzahl==0)
					return false;
	}
	elseif($filter2=='konto')
	{
		// Alle Personen die offene Buchungen haben
		$qry = "SELECT sum(betrag) as summe FROM tbl_konto WHERE person_id=".$db->db_add_param($row->person_id, FHC_INTEGER);
		if($kontofilterstg=='true')
			$qry.=" AND studiengang_kz=".$db->db_add_param($row->studiengang_kz);
		if($buchungstyp != null && $buchungstyp != "alle")
			$qry.=" AND buchungstyp_kurzbz=".$db->db_add_param($buchungstyp);

		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->summe=='0.00' || $row_filter->summe=='' || $row_filter->summe=='0')
					return false;
	}
	elseif($filter2=='studiengebuehr')
	{
		// Alle Personen die keine Studiengebuehrbelastung haben
		// Incoming werden nicht beruecksichtigt
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);

		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE
					studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
					person_id=".$db->db_add_param($row->person_id, FHC_INTEGER)." AND
					buchungstyp_kurzbz='Studiengebuehr'";
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->anzahl>0 || $prestudent->status_kurzbz=='Incoming')
					return false;
	}
	elseif(strstr($filter2,'buchungstyp;'))
	{
		// Alle Personen die keine Belastung auf den uebergebenen Buchungstyp haben
		// Incoming werden nicht beruecksichtigt
		list($filter, $buchungstyp) = explode(';',$filter2);
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);

		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE
					studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
					person_id=".$db->db_add_param($row->person_id, FHC_INTEGER)." AND
					buchungstyp_kurzbz=".$db->db_add_param($buchungstyp);
		if($db->db_query($qry))
			if($row_filter = $db->db_fetch_object())
				if($row_filter->anzahl>0 || $prestudent->status_kurzbz=='Incoming')
					return false;
	}
	elseif($filter2=='zgvohnedatum')
	{
		//Alle Personen die den ZGV Typ eingetragen haben aber noch kein Datum
		$qry = "SELECT zgv_code, zgvdatum, zgvmas_code, zgvmadatum,zgvdoktor_code, zgvdoktordatum
			FROM public.tbl_prestudent WHERE prestudent_id=".$db->db_add_param($row->prestudent_id);
		if($db->db_query($qry))
		{
			if($row_filter = $db->db_fetch_object())
			{
				if(($row_filter->zgv_code!='' && $row_filter->zgvdatum=='')
				|| ($row_filter->zgvmas_code!='' && $row_filter->zgvmadatum=='')
				|| ($row_filter->zgvdoktor_code!='' && $row_filter->zgvdoktordatum=='')
				)
					return true;
				else
					return false;
			}
		}
	}
	elseif($filter2=='ausbildungsvertragakzeptiert')
	{
		//Alle Personen die den archivierten Ausbildungsvertrag akzeptiert haben
		$qry = "SELECT count(*) as anzahl FROM public.tbl_akte
				WHERE person_id=".$db->db_add_param($row->person_id, FHC_INTEGER)."
				AND dokument_kurzbz='Ausbvert' AND archiv=true AND stud_selfservice=true AND akzeptiertamum IS NOT NULL";
		if($db->db_query($qry))
		{
			if($row_filter = $db->db_fetch_object())
			{
				if($row_filter->anzahl > 0)
					return true;
				else
					return false;
			}
		}
	}
	elseif ( preg_match('/^stud-statusgrund-([0-9]+)$/', $filter2, $studstatusgrund) )
	{
	    // Alle Studenten mit Statusgrund in tbl_prestudentstatus
	    $qry = "SELECT count(*) AS anzahl FROM public.tbl_prestudentstatus ps JOIN
				    public.tbl_prestudent p ON p.prestudent_id = ps.prestudent_id AND
				    ps. studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz)." AND
				    p. person_id=".$db->db_add_param($row->person_id, FHC_INTEGER)." AND
				    p.studiengang_kz=" . $db->db_add_param($studiengang_kz, FHC_INTEGER) . " AND
				    ps.statusgrund_id = " . $db->db_add_param($studstatusgrund[1], FHC_INTEGER);
	    //echo $qry . "\n";
	    $filtered = ( $db->db_query($qry) && ($row_filter = $db->db_fetch_object()) && ($row_filter->anzahl > 0) )
		      ? true
		      : false;
	    return $filtered;
	}
	return true;
}

function draw_content_liste($row)
{
	global $rdf_url, $datum_obj, $stg_arr;
	$status='';

	$prestudent = new prestudent();
	$prestudent->getLastStatus($row->prestudent_id);
	$status = $prestudent->status_kurzbz;
	$orgform = $prestudent->orgform_kurzbz;
	$studienplan_bezeichnung=$prestudent->studienplan_bezeichnung;
	echo '
	<RDF:li>
		<RDF:Description  id="'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'" >
			<STUDENT:person_id><![CDATA['.$row->person_id.']]></STUDENT:person_id>
			<STUDENT:prestudent_id><![CDATA['.$row->prestudent_id.']]></STUDENT:prestudent_id>
			<STUDENT:uid><![CDATA['.(isset($row->uid)?$row->uid:'').']]></STUDENT:uid>
			<STUDENT:titelpre><![CDATA['.$row->titelpre.']]></STUDENT:titelpre>
			<STUDENT:titelpost><![CDATA['.$row->titelpost.']]></STUDENT:titelpost>
			<STUDENT:wahlname><![CDATA['.$row->wahlname.']]></STUDENT:wahlname>
			<STUDENT:vornamen><![CDATA['.$row->vornamen.']]></STUDENT:vornamen>
			<STUDENT:vorname><![CDATA['.$row->vorname.']]></STUDENT:vorname>
			<STUDENT:nachname><![CDATA['.$row->nachname.']]></STUDENT:nachname>
			<STUDENT:geschlecht><![CDATA['.$row->geschlecht.']]></STUDENT:geschlecht>
			<STUDENT:svnr>'.($row->svnr==''?'&#xA0;':'<![CDATA['.$row->svnr.']]>').'</STUDENT:svnr>
			<STUDENT:ersatzkennzeichen>'.($row->ersatzkennzeichen==''?'&#xA0;':'<![CDATA['.$row->ersatzkennzeichen.']]>').'</STUDENT:ersatzkennzeichen>
			<STUDENT:geburtsdatum><![CDATA['.$datum_obj->convertISODate($row->gebdatum).']]></STUDENT:geburtsdatum>
			<STUDENT:geburtsdatum_iso><![CDATA['.$row->gebdatum.']]></STUDENT:geburtsdatum_iso>
			<STUDENT:semester><![CDATA['.(isset($row->semester)?$row->semester:'').']]></STUDENT:semester>
			<STUDENT:verband><![CDATA['.(isset($row->verband)?$row->verband:'').']]></STUDENT:verband>
			<STUDENT:gruppe><![CDATA['.(isset($row->gruppe)?$row->gruppe:'').']]></STUDENT:gruppe>
			<STUDENT:matrikelnummer><![CDATA['.(isset($row->matrikelnr)?$row->matrikelnr:'').']]></STUDENT:matrikelnummer>
			<STUDENT:mail_privat><![CDATA['.$row->email_privat.']]></STUDENT:mail_privat>
			<STUDENT:mail_intern><![CDATA['.(isset($row->uid)?$row->uid.'@'.DOMAIN:'').']]></STUDENT:mail_intern>
			<STUDENT:status><![CDATA['.$status.']]></STUDENT:status>
			<STUDENT:status_datum><![CDATA['.$datum_obj->formatDatum($prestudent->datum,'d.m.Y').']]></STUDENT:status_datum>
			<STUDENT:status_bestaetigung><![CDATA['.($prestudent->bestaetigtam!=''?$datum_obj->formatDatum($prestudent->bestaetigtam,'d.m.Y'):'-').']]></STUDENT:status_bestaetigung>
			<STUDENT:status_datum_iso><![CDATA['.$datum_obj->formatDatum($prestudent->datum,'Y-m-d').']]></STUDENT:status_datum_iso>
			<STUDENT:status_bestaetigung_iso><![CDATA['.($prestudent->bestaetigtam!=''?$datum_obj->formatDatum($prestudent->bestaetigtam,'Y-m-d'):'-').']]></STUDENT:status_bestaetigung_iso>
			<STUDENT:zugangscode><![CDATA['.$row->zugangscode.']]></STUDENT:zugangscode>
			<STUDENT:bpk><![CDATA['.$row->bpk.']]></STUDENT:bpk>

			<STUDENT:anmerkungen>'.($row->anmerkungen==''?'&#xA0;':'<![CDATA['.$row->anmerkungen.']]>').'</STUDENT:anmerkungen>
			<STUDENT:anmerkungpre>'.($row->anmerkung==''?'&#xA0;':'<![CDATA['.$row->anmerkung.']]>').'</STUDENT:anmerkungpre>
			<STUDENT:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz>
			<STUDENT:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></STUDENT:studiengang>
			<STUDENT:orgform><![CDATA['.$orgform.']]></STUDENT:orgform>
			<STUDENT:studienplan_bezeichnung><![CDATA['.$studienplan_bezeichnung.']]></STUDENT:studienplan_bezeichnung>
			<STUDENT:studienplan_id><![CDATA['.$prestudent->studienplan_id.']]></STUDENT:studienplan_id>
			<STUDENT:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></STUDENT:aufmerksamdurch_kurzbz>
			<STUDENT:punkte><![CDATA['.$row->punkte.']]></STUDENT:punkte>
			<STUDENT:dual><![CDATA['.($row->dual=='t'?'true':'false').']]></STUDENT:dual>
			<STUDENT:dual_bezeichnung><![CDATA['.($row->dual=='t'?'Ja':'Nein').']]></STUDENT:dual_bezeichnung>
			<STUDENT:matr_nr><![CDATA['.$row->matr_nr.']]></STUDENT:matr_nr>
			<STUDENT:mentor><![CDATA['.$row->mentor.']]></STUDENT:mentor>
			<STUDENT:gsstudientyp_kurzbz><![CDATA['.($row->gsstudientyp_kurzbz).']]></STUDENT:gsstudientyp_kurzbz>
			<STUDENT:aufnahmegruppe_kurzbz><![CDATA['.($row->aufnahmegruppe_kurzbz).']]></STUDENT:aufnahmegruppe_kurzbz>
			<STUDENT:aktiv><![CDATA['.((isset($row->bnaktiv) && $row->bnaktiv=='t')?'true':'false').']]></STUDENT:aktiv>
		</RDF:Description>
	</RDF:li>';
}

function draw_content($row)
{
	global $rdf_url, $datum_obj;
	$db = new basis_db();
	$status='';

	$mail_privat = '';
	$qry_mail = "SELECT kontakt FROM public.tbl_kontakt WHERE kontakttyp='email' AND person_id=".$db->db_add_param($row->person_id)." AND zustellung=true ORDER BY kontakt_id DESC LIMIT 1";
	if($db->db_query($qry_mail))
	{
		if($row_mail = $db->db_fetch_object())
		{
			$mail_privat = $row_mail->kontakt;
		}
	}

	$aktiv = "-";
	if(isset($row->bnaktiv))
	{
		switch($row->bnaktiv)
		{
			case true:
				$aktiv = "true";
				break;
			case false:
				$aktiv = "false";
				break;
			default:
				$aktiv = "-";
		}
	}

	$studiengang = new studiengang();
	$stgleiter = $studiengang->getLeitung($row->studiengang_kz);
	$stgl='';
	$i = 0;
	foreach ($stgleiter as $stgleiter_uid)
	{
		$stgl_ma = new mitarbeiter($stgleiter_uid);
		$stgl .= trim(($i>0?', ':'').$stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
		$i++;
	}

	// Anzahl Notizen der Person laden
	$notiz = new notiz();
	$anzahl_notizen = $notiz->getAnzahlNotizen(null, null, null, null, null, $row->person_id, null, null, null, null, null);

	if($row->prestudent_id!='')
	{
		$prestudent = new prestudent();
		$prestudent->getLastStatus($row->prestudent_id);
		$status = $prestudent->status_kurzbz;
		$orgform = $prestudent->orgform_kurzbz;
		$studienplan_bezeichnung=$prestudent->studienplan_bezeichnung;

		if($status=='Aufgenommener' || $status=='Bewerber' || $status=='Wartender' || $status=='Interessent')
			$semester_prestudent = $prestudent->ausbildungssemester;
		else
			$semester_prestudent = '';
	echo '
	<RDF:li>
		<RDF:Description  id="'.$row->prestudent_id.'"  about="'.$rdf_url.'/'.$row->prestudent_id.'" >
			<STUDENT:person_id><![CDATA['.$row->person_id.']]></STUDENT:person_id>
			<STUDENT:titelpre><![CDATA['.$row->titelpre.']]></STUDENT:titelpre>
			<STUDENT:titelpost><![CDATA['.$row->titelpost.']]></STUDENT:titelpost>
			<STUDENT:vornamen><![CDATA['.$row->vornamen.']]></STUDENT:vornamen>
			<STUDENT:wahlname><![CDATA['.$row->wahlname.']]></STUDENT:wahlname>
			<STUDENT:vorname><![CDATA['.$row->vorname.']]></STUDENT:vorname>
			<STUDENT:nachname><![CDATA['.$row->nachname.']]></STUDENT:nachname>
			<STUDENT:geburtsdatum><![CDATA['.$datum_obj->convertISODate($row->gebdatum).']]></STUDENT:geburtsdatum>
			<STUDENT:geburtsdatum_iso><![CDATA['.$row->gebdatum.']]></STUDENT:geburtsdatum_iso>
			<STUDENT:homepage><![CDATA['.$row->homepage.']]></STUDENT:homepage>
			<STUDENT:gebort><![CDATA['.$row->gebort.']]></STUDENT:gebort>
			<STUDENT:gebzeit><![CDATA['.$row->gebzeit.']]></STUDENT:gebzeit>
			<STUDENT:anmerkungen>'.($row->anmerkungen==''?'&#xA0;':'<![CDATA['.$row->anmerkungen.']]>').'</STUDENT:anmerkungen>
			<STUDENT:anrede><![CDATA['.$row->anrede.']]></STUDENT:anrede>
			<STUDENT:svnr><![CDATA['.$row->svnr.']]></STUDENT:svnr>
			<STUDENT:ersatzkennzeichen><![CDATA['.$row->ersatzkennzeichen.']]></STUDENT:ersatzkennzeichen>
			<STUDENT:familienstand><![CDATA['.$row->familienstand.']]></STUDENT:familienstand>
			<STUDENT:geschlecht><![CDATA['.$row->geschlecht.']]></STUDENT:geschlecht>
			<STUDENT:anzahlkinder><![CDATA['.$row->anzahlkinder.']]></STUDENT:anzahlkinder>
			<STUDENT:staatsbuergerschaft><![CDATA['.$row->staatsbuergerschaft.']]></STUDENT:staatsbuergerschaft>
			<STUDENT:geburtsnation><![CDATA['.$row->geburtsnation.']]></STUDENT:geburtsnation>
			<STUDENT:sprache><![CDATA['.$row->sprache.']]></STUDENT:sprache>
			<STUDENT:status><![CDATA['.$status.']]></STUDENT:status>
			<STUDENT:status_datum><![CDATA['.$datum_obj->formatDatum($prestudent->datum,'d.m.Y').']]></STUDENT:status_datum>
			<STUDENT:status_datum_iso><![CDATA['.$datum_obj->formatDatum($prestudent->datum,'Y-m-d').']]></STUDENT:status_datum_iso>
			<STUDENT:status_bestaetigung><![CDATA['.($prestudent->bestaetigtam!=''?$datum_obj->formatDatum($prestudent->bestaetigtam,'d.m.Y'):'-').']]></STUDENT:status_bestaetigung>
			<STUDENT:status_bestaetigung_iso><![CDATA['.($prestudent->bestaetigtam!=''?$datum_obj->formatDatum($prestudent->bestaetigtam,'Y-m-d'):'-').']]></STUDENT:status_bestaetigung_iso>
			<STUDENT:orgform><![CDATA['.$orgform.']]></STUDENT:orgform>
			<STUDENT:studienplan_bezeichnung><![CDATA['.$studienplan_bezeichnung.']]></STUDENT:studienplan_bezeichnung>
			<STUDENT:studienplan_id><![CDATA['.$prestudent->studienplan_id.']]></STUDENT:studienplan_id>
			<STUDENT:mail_privat><![CDATA['.$mail_privat.']]></STUDENT:mail_privat>
			<STUDENT:mail_intern><![CDATA['.(isset($row->uid)?$row->uid.'@'.DOMAIN:'').']]></STUDENT:mail_intern>
			<STUDENT:zugangscode><![CDATA['.$row->zugangscode.']]></STUDENT:zugangscode>
			<STUDENT:link_bewerbungstool><![CDATA['.CIS_ROOT.'addons/bewerbung/cis/registration.php?code='.$row->zugangscode.'&emailAdresse='.$mail_privat.']]></STUDENT:link_bewerbungstool>
			<STUDENT:bpk><![CDATA['.$row->bpk.']]></STUDENT:bpk>

			<STUDENT:aktiv><![CDATA['.$aktiv.']]></STUDENT:aktiv>
			<STUDENT:uid><![CDATA['.(isset($row->uid)?$row->uid:'').']]></STUDENT:uid>
			<STUDENT:matrikelnummer><![CDATA['.(isset($row->matrikelnr)?$row->matrikelnr:'').']]></STUDENT:matrikelnummer>
			<STUDENT:alias><![CDATA['.(isset($row->alias)?$row->alias:'').']]></STUDENT:alias>
			<STUDENT:semester><![CDATA['.(isset($row->semester)?$row->semester:$semester_prestudent).']]></STUDENT:semester>
			<STUDENT:verband><![CDATA['.(isset($row->verband)?$row->verband:'').']]></STUDENT:verband>
			<STUDENT:gruppe><![CDATA['.(isset($row->gruppe)?$row->gruppe:'').']]></STUDENT:gruppe>
			<STUDENT:studiengang_kz_student><![CDATA['.(is_a($row,'student')?$row->studiengang_kz:'').']]></STUDENT:studiengang_kz_student>
			<STUDENT:matr_nr><![CDATA['.$row->matr_nr.']]></STUDENT:matr_nr>
			<STUDENT:studiengang_studiengangsleitung><![CDATA['.$stgl.']]></STUDENT:studiengang_studiengangsleitung>
			<STUDENT:anzahl_notizen><![CDATA['.$anzahl_notizen.']]></STUDENT:anzahl_notizen>
	';
	}
}

function draw_prestudent($row)
{
	global $rdf_url, $datum_obj, $stg_arr;
	$reihungstest = new reihungstest($row->reihungstest_id);
	$rt_datum = $reihungstest->datum;
	$prioRelativ = new prestudent();
	$prioRelativ = $prioRelativ->getRelativePriorisierungFromAbsolut($row->prestudent_id, $row->priorisierung);
	if($row->prestudent_id!='')
	{
		$foerderrelevant = '';

		if ($row->foerderrelevant === true)
			$foerderrelevant = 'true';
		elseif ($row->foerderrelevant === false)
			$foerderrelevant = 'false';

		echo '
				<STUDENT:prestudent_id><![CDATA['.$row->prestudent_id.']]></STUDENT:prestudent_id>
				<STUDENT:studiengang_kz_prestudent><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz_prestudent>
				<STUDENT:studiengang_kz><![CDATA['.$row->studiengang_kz.']]></STUDENT:studiengang_kz>
				<STUDENT:aufmerksamdurch_kurzbz><![CDATA['.$row->aufmerksamdurch_kurzbz.']]></STUDENT:aufmerksamdurch_kurzbz>
				<STUDENT:studiengang><![CDATA['.$stg_arr[$row->studiengang_kz].']]></STUDENT:studiengang>
				<STUDENT:berufstaetigkeit_code><![CDATA['.$row->berufstaetigkeit_code.']]></STUDENT:berufstaetigkeit_code>
				<STUDENT:ausbildungcode><![CDATA['.$row->ausbildungcode.']]></STUDENT:ausbildungcode>
				<STUDENT:zgv_code><![CDATA['.$row->zgv_code.']]></STUDENT:zgv_code>
				<STUDENT:zgvort><![CDATA['.$row->zgvort.']]></STUDENT:zgvort>
				<STUDENT:zgvdatum><![CDATA['.$datum_obj->convertISODate($row->zgvdatum).']]></STUDENT:zgvdatum>
				<STUDENT:zgvdatum_iso><![CDATA['.$row->zgvdatum.']]></STUDENT:zgvdatum_iso>
				<STUDENT:zgvnation><![CDATA['.$row->zgvnation.']]></STUDENT:zgvnation>
				<STUDENT:zgv_erfuellt><![CDATA['.$row->zgv_erfuellt.']]></STUDENT:zgv_erfuellt>
				<STUDENT:zgvmas_code><![CDATA['.$row->zgvmas_code.']]></STUDENT:zgvmas_code>
				<STUDENT:zgvmaort><![CDATA['.$row->zgvmaort.']]></STUDENT:zgvmaort>
				<STUDENT:zgvmadatum><![CDATA['.$datum_obj->convertISODate($row->zgvmadatum).']]></STUDENT:zgvmadatum>
				<STUDENT:zgvmadatum_iso><![CDATA['.$row->zgvmadatum.']]></STUDENT:zgvmadatum_iso>
				<STUDENT:zgvmanation><![CDATA['.$row->zgvmanation.']]></STUDENT:zgvmanation>
				<STUDENT:zgvmas_erfuellt><![CDATA['.$row->zgvmas_erfuellt.']]></STUDENT:zgvmas_erfuellt>
				<STUDENT:zgvdoktor_code><![CDATA['.$row->zgvdoktor_code.']]></STUDENT:zgvdoktor_code>
				<STUDENT:zgvdoktorort><![CDATA['.$row->zgvdoktorort.']]></STUDENT:zgvdoktorort>
				<STUDENT:zgvdoktordatum><![CDATA['.$datum_obj->convertISODate($row->zgvdoktordatum).']]></STUDENT:zgvdoktordatum>
				<STUDENT:zgvdoktordatum_iso><![CDATA['.$row->zgvdoktordatum.']]></STUDENT:zgvdoktordatum_iso>
				<STUDENT:zgvdoktornation><![CDATA['.$row->zgvdoktornation.']]></STUDENT:zgvdoktornation>
				<STUDENT:zgvdoktor_erfuellt><![CDATA['.$row->zgvdoktor_erfuellt.']]></STUDENT:zgvdoktor_erfuellt>
				<STUDENT:ausstellungsstaat><![CDATA['.$row->ausstellungsstaat.']]></STUDENT:ausstellungsstaat>
				<STUDENT:aufnahmeschluessel><![CDATA['.$row->aufnahmeschluessel.']]></STUDENT:aufnahmeschluessel>
				<STUDENT:facheinschlberuf><![CDATA['.($row->facheinschlberuf?'true':'false').']]></STUDENT:facheinschlberuf>
				<STUDENT:reihungstest_id><![CDATA['.$row->reihungstest_id.']]></STUDENT:reihungstest_id>
				<STUDENT:anmeldungreihungstest><![CDATA['.$datum_obj->convertISODate($row->anmeldungreihungstest).']]></STUDENT:anmeldungreihungstest>
				<STUDENT:anmeldungreihungstest_iso><![CDATA['.$row->anmeldungreihungstest.']]></STUDENT:anmeldungreihungstest_iso>
				<STUDENT:reihungstestangetreten><![CDATA['.($row->reihungstestangetreten?'true':'false').']]></STUDENT:reihungstestangetreten>
				<STUDENT:punkte><![CDATA['.$row->punkte.']]></STUDENT:punkte>
				<STUDENT:bismelden><![CDATA['.($row->bismelden?'true':'false').']]></STUDENT:bismelden>
				<STUDENT:foerderrelevant><![CDATA['.($foerderrelevant).']]></STUDENT:foerderrelevant>
				<STUDENT:dual><![CDATA['.($row->dual?'true':'false').']]></STUDENT:dual>
				<STUDENT:dual_bezeichnung><![CDATA['.($row->dual?'Ja':'Nein').']]></STUDENT:dual_bezeichnung>
				<STUDENT:anmerkungpre><![CDATA['.$row->anmerkung.']]></STUDENT:anmerkungpre>
				<STUDENT:mentor><![CDATA['.$row->mentor.']]></STUDENT:mentor>
				<STUDENT:gsstudientyp_kurzbz><![CDATA['.$row->gsstudientyp_kurzbz.']]></STUDENT:gsstudientyp_kurzbz>
				<STUDENT:aufnahmegruppe_kurzbz><![CDATA['.$row->aufnahmegruppe_kurzbz.']]></STUDENT:aufnahmegruppe_kurzbz>
				<STUDENT:priorisierung><![CDATA['.$row->priorisierung.']]></STUDENT:priorisierung>
				<STUDENT:priorisierung_realtiv><![CDATA['.$prioRelativ.' ('.$row->priorisierung.')'.']]></STUDENT:priorisierung_realtiv>
				<STUDENT:standort_code><![CDATA['.$row->standort_code.']]></STUDENT:standort_code>
			</RDF:Description>
		</RDF:li>';
	}
}

function draw_empty_content()
{
	echo '<RDF:li>
		<RDF:Description  id=""  about="" >
			<STUDENT:person_id><![CDATA[]]></STUDENT:person_id>
			<STUDENT:titelpre><![CDATA[]]></STUDENT:titelpre>
			<STUDENT:titelpost><![CDATA[]]></STUDENT:titelpost>
			<STUDENT:vornamen><![CDATA[]]></STUDENT:vornamen>
			<STUDENT:wahlname><![CDATA[]]></STUDENT:wahlname>
			<STUDENT:vorname><![CDATA[]]></STUDENT:vorname>
			<STUDENT:nachname><![CDATA[KEINE RESULTATE]]></STUDENT:nachname>
			<STUDENT:geburtsdatum><![CDATA[]]></STUDENT:geburtsdatum>
			<STUDENT:geburtsdatum_iso><![CDATA[]]></STUDENT:geburtsdatum_iso>
			<STUDENT:homepage><![CDATA[]]></STUDENT:homepage>
			<STUDENT:gebort><![CDATA[]]></STUDENT:gebort>
			<STUDENT:gebzeit><![CDATA[]]></STUDENT:gebzeit>
			<STUDENT:anmerkungen><![CDATA[]]></STUDENT:anmerkungen>
			<STUDENT:anrede><![CDATA[]]></STUDENT:anrede>
			<STUDENT:svnr><![CDATA[]]></STUDENT:svnr>
			<STUDENT:ersatzkennzeichen><![CDATA[]]></STUDENT:ersatzkennzeichen>
			<STUDENT:familienstand><![CDATA[]]></STUDENT:familienstand>
			<STUDENT:geschlecht><![CDATA[]]></STUDENT:geschlecht>
			<STUDENT:anzahlkinder><![CDATA[]]></STUDENT:anzahlkinder>
			<STUDENT:staatsbuergerschaft><![CDATA[]]></STUDENT:staatsbuergerschaft>
			<STUDENT:geburtsnation><![CDATA[]]></STUDENT:geburtsnation>
			<STUDENT:sprache><![CDATA[]]></STUDENT:sprache>
			<STUDENT:status><![CDATA[]]></STUDENT:status>
			<STUDENT:status_datum><![CDATA[]]></STUDENT:status_datum>
			<STUDENT:status_datum_iso><![CDATA[]]></STUDENT:status_datum_iso>
			<STUDENT:status_bestaetigung><![CDATA[]]></STUDENT:status_bestaetigung>
			<STUDENT:status_bestaetigung_iso><![CDATA[]]></STUDENT:status_bestaetigung_iso>
			<STUDENT:orgform><![CDATA[]]></STUDENT:orgform>
			<STUDENT:studienplan_bezeichnung><![CDATA[]]></STUDENT:studienplan_bezeichnung>
			<STUDENT:studienplan_id><![CDATA[]]></STUDENT:studienplan_id>
			<STUDENT:mail_privat><![CDATA[]]></STUDENT:mail_privat>
			<STUDENT:mail_intern><![CDATA[]]></STUDENT:mail_intern>
			<STUDENT:zugangscode><![CDATA[]]></STUDENT:zugangscode>
			<STUDENT:link_bewerbungstool><![CDATA[]]></STUDENT:link_bewerbungstool>
			<STUDENT:bpk><![CDATA[]]></STUDENT:bpk>

			<STUDENT:aktiv><![CDATA[]]></STUDENT:aktiv>
			<STUDENT:uid><![CDATA[]]></STUDENT:uid>
			<STUDENT:matrikelnummer><![CDATA[]]></STUDENT:matrikelnummer>
			<STUDENT:alias><![CDATA[]]></STUDENT:alias>
			<STUDENT:semester><![CDATA[]]></STUDENT:semester>
			<STUDENT:verband><![CDATA[]]></STUDENT:verband>
			<STUDENT:gruppe><![CDATA[]]></STUDENT:gruppe>
			<STUDENT:studiengang_kz_student><![CDATA[]]></STUDENT:studiengang_kz_student>
			<STUDENT:matr_nr><![CDATA[]]></STUDENT:matr_nr>
			<STUDENT:studiengang_studiengangsleitung><![CDATA[]]></STUDENT:studiengang_studiengangsleitung>
			<STUDENT:anzahl_notizen><![CDATA[]]></STUDENT:anzahl_notizen>

			<STUDENT:prestudent_id><![CDATA[]]></STUDENT:prestudent_id>
			<STUDENT:studiengang_kz_prestudent><![CDATA[]]></STUDENT:studiengang_kz_prestudent>
			<STUDENT:studiengang_kz><![CDATA[]]></STUDENT:studiengang_kz>
			<STUDENT:aufmerksamdurch_kurzbz><![CDATA[]]></STUDENT:aufmerksamdurch_kurzbz>
			<STUDENT:studiengang><![CDATA[]]></STUDENT:studiengang>
			<STUDENT:berufstaetigkeit_code><![CDATA[]]></STUDENT:berufstaetigkeit_code>
			<STUDENT:ausbildungcode><![CDATA[]]></STUDENT:ausbildungcode>
			<STUDENT:zgv_code><![CDATA[]]></STUDENT:zgv_code>
			<STUDENT:zgvort><![CDATA[]]></STUDENT:zgvort>
			<STUDENT:zgvdatum><![CDATA[]]></STUDENT:zgvdatum>
			<STUDENT:zgvdatum_iso><![CDATA[]]></STUDENT:zgvdatum_iso>
			<STUDENT:zgvnation><![CDATA[]]></STUDENT:zgvnation>
			<STUDENT:zgvmas_code><![CDATA[]]></STUDENT:zgvmas_code>
			<STUDENT:zgvmaort><![CDATA[]]></STUDENT:zgvmaort>
			<STUDENT:zgvmadatum><![CDATA[]]></STUDENT:zgvmadatum>
			<STUDENT:zgvmadatum_iso><![CDATA[]]></STUDENT:zgvmadatum_iso>
			<STUDENT:zgvmanation><![CDATA[]]></STUDENT:zgvmanation>
			<STUDENT:ausstellungsstaat><![CDATA[]]></STUDENT:ausstellungsstaat>
			<STUDENT:aufnahmeschluessel><![CDATA[]]></STUDENT:aufnahmeschluessel>
			<STUDENT:facheinschlberuf><![CDATA[]]></STUDENT:facheinschlberuf>
			<STUDENT:reihungstest_id><![CDATA[]]></STUDENT:reihungstest_id>
			<STUDENT:anmeldungreihungstest><![CDATA[]]></STUDENT:anmeldungreihungstest>
			<STUDENT:anmeldungreihungstest_iso><![CDATA[]]></STUDENT:anmeldungreihungstest_iso>
			<STUDENT:reihungstestangetreten><![CDATA[]]></STUDENT:reihungstestangetreten>
			<STUDENT:punkte><![CDATA[]]></STUDENT:punkte>
			<STUDENT:bismelden><![CDATA[]]></STUDENT:bismelden>
			<STUDENT:dual><![CDATA[]]></STUDENT:dual>
			<STUDENT:dual_bezeichnung><![CDATA[]]></STUDENT:dual_bezeichnung>
			<STUDENT:anmerkungpre><![CDATA[]]></STUDENT:anmerkungpre>
			<STUDENT:mentor><![CDATA[]]></STUDENT:mentor>
			<STUDENT:gsstudientyp_kurzbz><![CDATA[]]></STUDENT:gsstudientyp_kurzbz>
			<STUDENT:aufnahmegruppe_kurzbz><![CDATA[]]></STUDENT:aufnahmegruppe_kurzbz>
			<STUDENT:priorisierung><![CDATA[]]></STUDENT:priorisierung>
			<STUDENT:priorisierung_realtiv><![CDATA[]]></STUDENT:priorisierung_realtiv>
		</RDF:Description>
	</RDF:li>';
}

// ******* Init **************************


if(isset($_SERVER['REMOTE_USER']))
{
	$user = get_uid();
	loadVariables($user);
}

$gruppe_kurzbz=(isset($_GET['gruppe_kurzbz'])?$_GET['gruppe_kurzbz']:null);
$gruppe=(isset($_GET['gruppe'])?$_GET['gruppe']:null);
$verband=(isset($_GET['verband'])?$_GET['verband']:null);
$semester=(isset($_GET['semester'])?$_GET['semester']:null);
$studiengang_kz=(isset($_GET['studiengang_kz'])?$_GET['studiengang_kz']:null);
$studiensemester_kurzbz = (isset($_GET['studiensemester_kurzbz'])?$_GET['studiensemester_kurzbz']:null);
$uid = (isset($_GET['uid'])?$_GET['uid']:null);
$typ = (isset($_GET['typ'])?$_GET['typ']:null);
$prestudent_id = (isset($_GET['prestudent_id'])?$_GET['prestudent_id']:null);
$filter = (isset($_GET['filter'])?$_GET['filter']:null);
$ss = (isset($_GET['ss'])?$_GET['ss']:null);
$filter2 = (isset($_GET['filter2'])?$_GET['filter2']:null);
$orgform = (isset($_GET['orgform'])?$_GET['orgform']:null);
$buchungstyp_filter = (isset($_GET['buchungstyp'])?$_GET['buchungstyp']:null);

$db = new basis_db();

if($studiensemester_kurzbz=='aktuelles')
	$studiensemester_kurzbz = $semester_aktuell;

if(isset($_GET['xmlformat']) && $_GET['xmlformat']=='xml')
	$xmlformat='xml';
else
	$xmlformat='rdf';

$datum_obj = new datum();

// ************ Beginn **************************

if($xmlformat=='rdf')
{
	$stg_arr = array();
	$stg_obj = new studiengang();
	$stg_obj->getAll(null, false);
	foreach ($stg_obj->result as $row)
		$stg_arr[$row->studiengang_kz]=$row->kuerzel;

	$rdf_url='http://www.technikum-wien.at/student';
	echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:STUDENT="'.$rdf_url.'/rdf#"
		xmlns:NC="http://home.netscape.com/NC-rdf#"
	>


	  <RDF:Seq about="'.$rdf_url.'/alle">
	';

	if(isset($uid))
	{
		$student=new student();
		$student->load($uid, $studiensemester_kurzbz);
		$prestd = new prestudent();

		draw_content($student);
		$prestd->load($student->prestudent_id);
		draw_prestudent($prestd);
	}
	if($typ=='student')
	{
		// Studenten holen
		$where = '';
		if ($gruppe_kurzbz!=null)
		{
			$where=" gruppe_kurzbz=".$db->db_add_param($gruppe_kurzbz)." ";
			if($studiensemester_kurzbz!=null)
				$where.=" AND tbl_benutzergruppe.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);
		}
		else
		{
			$where.=" tbl_studentlehrverband.studiengang_kz=".$db->db_add_param($studiengang_kz);
			if ($semester!=null)
				$where.=" AND tbl_studentlehrverband.semester=".$db->db_add_param($semester);
			if ($verband!=null)
				$where.=" AND tbl_studentlehrverband.verband=".$db->db_add_param($verband);
			if ($gruppe!=null)
				$where.=" AND tbl_studentlehrverband.gruppe=".$db->db_add_param($gruppe);

			//Wenn eine Orgform uebergeben wird nur das Semester ausgewaehlt ist, dann
			//nach der Orgform filtern. Bei Verbaenden, Gruppen und Spezialgruppen wird auf
			//die Orgform keine ruecksicht genommen
			if($verband=='' && $gruppe=='' && $orgform!='')
			{
				$where.=" AND '$orgform' = (SELECT orgform_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id";
				if($studiensemester_kurzbz!=null)
					$where.=" AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);
				$where.=" ORDER BY datum desc, insertamum desc, ext_id desc LIMIT 1)";
			}
		}

		//$where.=" AND tbl_studentlehrverband.studiensemester_kurzbz='$studiensemester_kurzbz'";

		$sql_query="
		SELECT
			p.person_id, tbl_student.prestudent_id, tbl_benutzer.uid, titelpre, titelpost,vorname, wahlname, vornamen, geschlecht,
			nachname, gebdatum, tbl_prestudent.anmerkung,ersatzkennzeichen,svnr, tbl_student.matrikelnr, p.anmerkung as anmerkungen,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			tbl_student.studiengang_kz, aufmerksamdurch_kurzbz, mentor, public.tbl_benutzer.aktiv AS bnaktiv,
			(	SELECT kontakt
				FROM public.tbl_kontakt
				WHERE kontakttyp='email' AND person_id=p.person_id AND zustellung
				LIMIT 1
			)
			AS email_privat,
			(SELECT rt_gesamtpunkte as punkte FROM public.tbl_prestudent WHERE prestudent_id=tbl_student.prestudent_id) as punkte,
			 tbl_prestudent.dual as dual, tbl_prestudent.reihungstest_id, tbl_prestudent.anmeldungreihungstest, p.matr_nr,
			 tbl_prestudent.gsstudientyp_kurzbz, tbl_prestudent.aufnahmegruppe_kurzbz, tbl_prestudent.priorisierung, p.zugangscode, p.bpk
		FROM
			public.tbl_student
			JOIN public.tbl_benutzer ON (student_uid=uid)
			JOIN public.tbl_person p USING (person_id)
			JOIN public.tbl_prestudent USING(prestudent_id) ";
		if($gruppe_kurzbz!=null)
			$sql_query.= "JOIN public.tbl_benutzergruppe USING (uid) ";
		$sql_query.="LEFT JOIN public.tbl_studentlehrverband ON (tbl_studentlehrverband.student_uid=tbl_student.student_uid AND tbl_studentlehrverband.studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz).")";
		$sql_query.="WHERE ".$where.' ORDER BY nachname, vorname';

		if($db->db_query($sql_query))
		{
			while($row = $db->db_fetch_object())
			{
				if(checkfilter($row, $filter2, $buchungstyp_filter))
					draw_content_liste($row);
			}
		}
	}
	elseif($typ=='incoming' || $typ=='outgoing')
	{
		if($studiensemester_kurzbz=='')
			$studiensemester_kurzbz=$semester_aktuell;
		if($typ=='incoming')
		{
			$qry = "SELECT prestudent_id FROM public.tbl_prestudentstatus WHERE status_kurzbz='Incoming' AND studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);
		}
		else
		{
			$stsem_obj = new studiensemester();
			$stsem_obj->load($studiensemester_kurzbz);
			$qry = "SELECT prestudent_id
					FROM
						bis.tbl_bisio JOIN public.tbl_student USING(student_uid)
					WHERE
						(
						(tbl_bisio.von>=".$db->db_add_param($stsem_obj->start)." AND tbl_bisio.von<=".$db->db_add_param($stsem_obj->ende).")
						OR
						(tbl_bisio.bis>=".$db->db_add_param($stsem_obj->start)." AND tbl_bisio.bis<=".$db->db_add_param($stsem_obj->ende).")
						OR
						(tbl_bisio.von<=".$db->db_add_param($stsem_obj->start)." AND tbl_bisio.bis>=".$db->db_add_param($stsem_obj->ende).")
						)
						AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus WHERE status_kurzbz='Incoming' AND prestudent_id=tbl_student.prestudent_id)
					";
		}
		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$student=new student();
				if($uid = $student->getUid($row->prestudent_id))
				{
					//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
					//nochmal laden aber ohne studiensemester
					if(!$student->load($uid, $studiensemester_kurzbz))
						$student->load($uid);
				}
				$prestd = new prestudent();
				$prestd->load($row->prestudent_id);
				if($uid!='')
				{
					draw_content($student);
					draw_prestudent($prestd);
				}
				else
				{
					draw_content($prestd);
					draw_prestudent($prestd);
				}
			}
		}
	}
	elseif($typ=='gemeinsamestudien')
	{
		if($studiensemester_kurzbz=='')
			$studiensemester_kurzbz=$semester_aktuell;

		$qry = "SELECT prestudent_id
					FROM
						bis.tbl_mobilitaet
					WHERE
						studiensemester_kurzbz=".$db->db_add_param($studiensemester_kurzbz);

		if($db->db_query($qry))
		{
			while($row = $db->db_fetch_object())
			{
				$student=new student();
				if($uid = $student->getUid($row->prestudent_id))
				{
					//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
					//nochmal laden aber ohne studiensemester
					if(!$student->load($uid, $studiensemester_kurzbz))
						$student->load($uid);
				}
				$prestd = new prestudent();
				$prestd->load($row->prestudent_id);
				if($uid!='')
				{
					draw_content($student);
					draw_prestudent($prestd);
				}
				else
				{
					draw_content($prestd);
					draw_prestudent($prestd);
				}
			}
		}
	}
	elseif(in_array($typ, array('prestudent', 'interessenten', 'bewerber', 'aufgenommen',
		'warteliste', 'absage', 'zgv', 'reihungstestangemeldet', 'reihungstestnichtangemeldet', 'absolvent',
		'diplomand', 'bewerbungnichtabgeschickt', 'bewerbungabgeschickt', 'statusbestaetigt')))
	{
		$prestd = new prestudent();

		if($studiengang_kz!=null)
		{
			if($prestd->loadInteressentenUndBewerber($studiensemester_kurzbz, $studiengang_kz, $semester, $typ, $orgform))
			{
				foreach ($prestd->result as $row)
				{
					if(checkfilter($row, $filter2, $buchungstyp_filter))
					{
						$student=new student();
						if($uid = $student->getUid($row->prestudent_id))
						{
							if(!$student->load($uid, $studiensemester_kurzbz))
								$student->load($uid);
							draw_content($student);
						}
						else
							draw_content($row);
						draw_prestudent($row);
					}
				}
			}
		}
		elseif($prestudent_id!=null)
		{
			if($prestd->load($prestudent_id))
			{
				draw_content($prestd);
				draw_prestudent($prestd);
			}
			else
				echo $prestd->errormsg;
		}
		else
		{
			echo 'Falsche Parameteruebergabe';
		}
	}
	else
	{
		// Sonderzeichen entfernen und String aufsplitten
		// Replace commas with whitespace
		$filter = str_replace(',', ' ', $filter);
		// Replace multiple whitespaces with just one
		$filter = preg_replace('/\s/', ' ', $filter);
		// Trim whitespaces and special characters from the string
		$filter = trim($filter,' 		!.?');
		// Explode string
		$searchItems = explode(' ',$filter);

		$kriterienliste = array("#email","#name","#pid","#preid","#tel", "#ref");
		$suchkriterium = '';

		// Wenn der erste Array-Eintrag einem kriterium der $kriterienliste entspricht -> in $suchkriterium speichern und aus Array entfernen
		if (isset($searchItems[0]) && in_array($searchItems[0], $kriterienliste))
		{
			$suchkriterium = $searchItems[0];
			array_shift($searchItems);
		}
		// Wenn nach dem TRIM Zeichen uebrig bleiben, dann fortfahren
		if(implode(',', $searchItems) != '')
		{
			if($suchkriterium == '#ref')
			{
				foreach($searchItems as $ref)
				{
					$konto = new konto();
					$konto->loadFromZahlungsreferenz($ref);
					$prestudent=new prestudent();
					$prestudent->getPrestudenten($konto->person_id);
					if(!empty($prestudent->result))
					{
						$prestudent_temp = new prestudent($prestudent->result[0]->prestudent_id);
						$student = new student();
						$uid = $student->getUid($prestudent_temp->prestudent_id);

						if($uid!='' && $uid != false)
						{
							if(!$student->load($uid, $studiensemester_kurzbz))
							$student->load($uid);
							draw_content($student);
							draw_prestudent($prestudent_temp);
						}
						else
						{
							draw_content($prestudent_temp);
							draw_prestudent($prestudent_temp);
						}
					}
				}
			}
			if($suchkriterium == '#tel' || $suchkriterium == '#email')
			{
				$searchItems_string_orig = implode(' ', $searchItems);
				$kontakt = new kontakt();
				if ($suchkriterium == '#tel')
					$kontakt->searchKontakt($searchItems_string_orig, 'nummer');
				elseif ($suchkriterium == '#email')
					$kontakt->searchKontakt($searchItems_string_orig, 'email');
				else
					$kontakt->searchKontakt($searchItems_string_orig);

				foreach ($kontakt->result AS $row)
				{
					$prestudent=new prestudent();
					$prestudent->getPrestudenten($row->person_id);
					if(!empty($prestudent->result))
					{
						foreach ($prestudent->result AS $prest)
						{
							$prestudent_temp = new prestudent($prest->prestudent_id);
							$student = new student();
							$uid = $student->getUid($prestudent_temp->prestudent_id);

							if($uid!='' && $uid != false)
							{
								if(!$student->load($uid, $studiensemester_kurzbz))
									$student->load($uid);
								draw_content($student);
								draw_prestudent($prestudent_temp);
							}
							else
							{
								draw_content($prestudent_temp);
								draw_prestudent($prestudent_temp);
							}
						}
					}
				}
			}
			elseif($suchkriterium == '#name')
			{
				// String imploden und trimmen, um preg_split(Zeichenweise trennung) durchfuehren zu koennen
				$searchItems_string_orig = implode(' ', $searchItems);
				$searchItems_string_orig = mb_strtolower(TRIM($searchItems_string_orig));

				$qry = "SELECT prestudent_id
					FROM
						public.tbl_person JOIN public.tbl_prestudent USING (person_id) LEFT JOIN public.tbl_student USING (prestudent_id)
					WHERE
						UPPER(nachname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(vorname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(wahlname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(vorname || ' ' || nachname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(nachname || ' ' || vorname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(wahlname || ' ' || nachname) = UPPER(".$db->db_add_param($searchItems_string_orig).") OR
						UPPER(nachname || ' ' || wahlname) = UPPER(".$db->db_add_param($searchItems_string_orig).");";

				if($db->db_query($qry))
				{
					while ($row = $db->db_fetch_object())
					{
						$student = new student();
						if ($uid = $student->getUid($row->prestudent_id))
						{
							//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
							//nochmal laden aber ohne studiensemester
							if (!$student->load($uid, $studiensemester_kurzbz))
							{
								$student->load($uid);
							}
						}
						$prestd = new prestudent();
						$prestd->load($row->prestudent_id);

						if ($uid != '')
						{
							draw_content($student);
							draw_prestudent($prestd);
						}
						else
						{
							draw_content($prestd);
							draw_prestudent($prestd);
						}
					}
				}
			}
			else
			{
				// String imploden und trimmen, um preg_split(Zeichenweise trennung) durchfuehren zu koennen
				$searchItems_string_orig = implode(' ', $searchItems);
				$searchItems_string = generateSpecialCharacterString($searchItems_string_orig);

				$qry = "SELECT
							prestudent_id
						FROM
							public.tbl_person JOIN public.tbl_prestudent USING (person_id) LEFT JOIN public.tbl_student USING (prestudent_id)
						WHERE
							1=1
						AND";

				// Wenn der urspruengliche Suchbegriff NICHT numerisch ist, Namenssuche durchführen
				if (!is_numeric($searchItems_string_orig))
				{
					$qry .= "	UPPER(vorname || ' ' || nachname) ~* UPPER(".$db->db_add_param($searchItems_string).") OR
								UPPER(nachname || ' ' || vorname) ~* UPPER(".$db->db_add_param($searchItems_string).") OR
								UPPER(nachname || ' ' || wahlname) ~* UPPER(".$db->db_add_param($searchItems_string).") OR
								UPPER(wahlname || ' ' || nachname) ~* UPPER(".$db->db_add_param($searchItems_string).") OR
								student_uid ~* LOWER(".$db->db_add_param($searchItems_string).")";
				}
				else
				{
					if ($suchkriterium == '#pid')
						$qry .= "	person_id = ".$db->db_add_param($searchItems_string_orig).";";
					elseif ($suchkriterium == '#preid')
						$qry .= "	prestudent_id = ".$db->db_add_param($searchItems_string_orig).";";
					else
						$qry .= "	matrikelnr = ".$db->db_add_param($searchItems_string_orig)." OR
									matr_nr = ".$db->db_add_param($searchItems_string_orig)." OR
									svnr = ".$db->db_add_param($searchItems_string_orig).";";
				}
				if($result = $db->db_query($qry))
				{
					if ($db->db_num_rows($result) == 0)
					{
						draw_empty_content();
					}
					else
					{
						while ($row = $db->db_fetch_object())
						{
							$student = new student();
							if ($uid = $student->getUid($row->prestudent_id))
							{
								//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
								//nochmal laden aber ohne studiensemester
								if (!$student->load($uid, $studiensemester_kurzbz))
								{
									$student->load($uid);
								}
							}
							$prestd = new prestudent();
							$prestd->load($row->prestudent_id);
							if ($uid != '')
							{
								draw_content($student);
								draw_prestudent($prestd);
							}
							else
							{
								draw_content($prestd);
								draw_prestudent($prestd);
							}
						}
					}
				}
			}
		}
		elseif(isset($prestudent_id))
		{
			$student=new student();
			if($uid = $student->getUid($prestudent_id))
			{
				//Wenn kein Eintrag fuers aktuelle Studiensemester da ist, dann
				//nochmal laden aber ohne studiensemester
				if(!$student->load($uid, $studiensemester_kurzbz))
					$student->load($uid);
			}
			$prestd = new prestudent();
			$prestd->load($prestudent_id);
			if($uid!='')
			{
				draw_content($student);
				draw_prestudent($prestd);
			}
			else
			{
				draw_content($prestd);
				draw_prestudent($prestd);
			}
		}
	}

	echo "</RDF:Seq>\n</RDF:RDF>";
}
else
{
	//XML
	$uids = explode(';',$uid);
	echo '<studenten>';
	foreach ($uids as $uid)
	{
		if($uid!='')
		{
			$student = new student();
			$student->load($uid);

			$studiengang = new studiengang();
			$studiengang->load($student->studiengang_kz);

			$stgleiter = $studiengang->getLeitung($student->studiengang_kz);
			$stgl='';
			$i = 0;
			foreach ($stgleiter as $stgleiter_uid)
			{
				$stgl_ma = new mitarbeiter($stgleiter_uid);
				$stgl .= trim(($i>0?', ':'').$stgl_ma->titelpre.' '.$stgl_ma->vorname.' '.$stgl_ma->nachname.' '.$stgl_ma->titelpost);
				$i++;
			}

//			$stg_typ = new studiengang();
//			$stg_typ->getStudiengangTyp($studiengang->typ);
//			$typ=$stg_typ->bezeichnung;
			switch($studiengang->typ)
			{
				case 'd':	$typ = 'FH-Diplom-Studiengang';
							break;
				case 'm':	$typ = 'FH-Master-Studiengang';
							break;
				case 'b':	$typ = 'FH-Bachelor-Studiengang';
							break;
				default:	$typ = 'FH-Studiengang';
			}

			$qry = "SELECT *
					FROM PUBLIC.tbl_benutzerfunktion
					JOIN campus.vw_mitarbeiter USING (uid)
					WHERE funktion_kurzbz = 'rek'
						AND (
							tbl_benutzerfunktion.datum_von <= now()
							OR tbl_benutzerfunktion.datum_von IS NULL
							)
						AND (
							tbl_benutzerfunktion.datum_bis >= now()
							OR tbl_benutzerfunktion.datum_bis IS NULL
							)
					ORDER BY tbl_benutzerfunktion.insertamum DESC LIMIT 1";
			$rektor = '';
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$rektor = $row->titelpre.' '.$row->vorname.' '.$row->nachname.' '.$row->titelpost;
				}
			}

			$studiengbeginn = '';
			$studiensemester_kurzbz='';
			$qry = "SELECT * FROM public.tbl_prestudentstatus JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE prestudent_id='$student->prestudent_id' AND status_kurzbz in('Student','Incoming') ORDER BY datum LIMIT 1";
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$studienbeginn = $row->start;
					$studiensemester = $row->studiensemester_kurzbz;
				}
			}

			$stsem = new studiensemester();
			//$aktstsem = $stsem->getaktorNext();

			$stsem->load($ss);

			$qry = "SELECT * FROM public.tbl_prestudentstatus WHERE prestudent_id='$student->prestudent_id' AND studiensemester_kurzbz='$ss' ORDER BY datum DESC";
			$semester=0;
			if($db->db_query($qry))
			{
				if($row = $db->db_fetch_object())
				{
					$semester = $row->ausbildungssemester;
					$studienplan_id = $row->studienplan_id;
				}
			}

			if(isset($studienplan_id) && $studienplan_id!='')
			{
				$stpl = new studienplan();
				$stpl->loadStudienplan($studienplan_id);

				$sto = new studienordnung();
				$sto->loadStudienordnung($stpl->studienordnung_id);

				$sto_studiengang_bezeichnung = $sto->studiengangbezeichnung;
				$sto_studiengang_bezeichnung_englisch = $sto->studiengangbezeichnung_englisch;
			}
			else
			{
				$sto_studiengang_bezeichnung='';
				$sto_studiengang_bezeichnung_englisch='';
			}

			//für ao. Studierende wird der Studiengang der Lehrveranstaltungen benötigt, die sie besuchen
			$lv_studiengang_kz='';
			$lv_studiengang_bezeichnung='';
			$lv_studiengang_typ='';
			$lv_studiengang_art='';

			$lv=new lehrveranstaltung();
			$lv->load_lva_student($student->uid);
			if(count($lv->lehrveranstaltungen)>0)
			{
				$lv_studiengang_kz=$lv->lehrveranstaltungen[0]->studiengang_kz;
				//Wenn die LV an der ersten Stelle ein Freifach (Stg 0) ist, nimm die naechste sofern eine vorhanden
				if($lv_studiengang_kz==0)
				{
					for ($i = 0; $i < count($lv->lehrveranstaltungen); $i++)
					{
						$lv_studiengang_kz=$lv->lehrveranstaltungen[$i]->studiengang_kz;
						if ($lv_studiengang_kz!=0)
							break;
					}
				}

				$lv_studiengang=new studiengang();
				$lv_studiengang->load($lv_studiengang_kz);
				$lv_studiengang_bezeichnung=$lv_studiengang->bezeichnung;
				$lv_studiengang_typ=$lv_studiengang->typ;

				switch($lv_studiengang->typ)
				{
					case 'd':	$lv_studiengang_art = 'Diplom';
								break;
					case 'm':	$lv_studiengang_art = 'Master';
								break;
					case 'b':	$lv_studiengang_art = 'Bachelor';
								break;
				}
			}
			$prestudent = new prestudent($student->prestudent_id);
			$prestudent->getLastStatus($student->prestudent_id);

			$orgform_bezeichnung = new organisationsform();
			$orgform_bezeichnung->load($studiengang->orgform_kurzbz);

			$orgform_student_bezeichnung = new organisationsform();
			$orgform_student_bezeichnung->load($prestudent->orgform_kurzbz);

			//Wenn Lehrgang, dann Erhalter-KZ vor die LV-Studiengangs-Kz hängen
			if ($lv_studiengang_kz<0)
			{
				$stg = new studiengang();
				$stg->load($lv_studiengang_kz);

				$lv_studiengang_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($lv_studiengang_kz));
			}
			else
				$lv_studiengang_kz = sprintf("%04s", abs($lv_studiengang_kz));

			//Wenn Lehrgang, dann Erhalter-KZ vor die Studiengangs-Kz hängen
			if ($student->studiengang_kz<0)
			{
				$stg = new studiengang();
				$stg->load($student->studiengang_kz);

				$stg_kz = sprintf("%03s", $stg->erhalter_kz).sprintf("%04s", abs($student->studiengang_kz));
			}
			else
				$stg_kz = sprintf("%04s", abs($student->studiengang_kz));
			if (($semester % 2) == 0)
				$studienjahr =  $semester/2;
			else
				$studienjahr = intval($semester/2)+1;

			echo '
			<student>
				<uid><![CDATA['.$student->uid.']]></uid>
				<person_id><![CDATA['.$student->person_id.']]></person_id>
				<titelpre><![CDATA['.$student->titelpre.']]></titelpre>
				<titelpost><![CDATA['.$student->titelpost.']]></titelpost>
				<vornamen><![CDATA['.$student->vornamen.']]></vornamen>
				<wahlname><![CDATA['.$student->wahlname.']]></wahlname>
				<vorname><![CDATA['.$student->vorname.']]></vorname>
				<nachname><![CDATA['.$student->nachname.']]></nachname>
				<matrikelnummer><![CDATA['.$student->matrikelnr.']]></matrikelnummer>
				<matr_nr><![CDATA['.$student->matr_nr.']]></matr_nr>
				<geburtsdatum><![CDATA['.$datum_obj->convertISODate($student->gebdatum).']]></geburtsdatum>
				<geburtsdatum_iso><![CDATA['.$student->gebdatum.']]></geburtsdatum_iso>
				<geburtsort><![CDATA['.$student->gebort.']]></geburtsort>
				<semester><![CDATA['.$semester.']]></semester>
				<verband><![CDATA['.$student->verband.']]></verband>
				<gruppe><![CDATA['.$student->gruppe.']]></gruppe>
				<studienjahr><![CDATA['.$studienjahr.']]></studienjahr>
				<student_orgform_kurzbz><![CDATA['.$prestudent->orgform_kurzbz.']]></student_orgform_kurzbz>
				<student_orgform_bezeichnung><![CDATA['.$orgform_student_bezeichnung->bezeichnung.']]></student_orgform_bezeichnung>
				<studiengang_kz><![CDATA['.$stg_kz.']]></studiengang_kz>
				<studiengang_bezeichnung><![CDATA['.$studiengang->bezeichnung.']]></studiengang_bezeichnung>
				<studiengang_art><![CDATA['.$typ.']]></studiengang_art>
				<studiengang_typ><![CDATA['.$studiengang->typ.']]></studiengang_typ>
				<studiengang_orgform_kurzbz><![CDATA['.$studiengang->orgform_kurzbz.']]></studiengang_orgform_kurzbz>
				<studiengang_orgform_bezeichnung><![CDATA['.$orgform_bezeichnung->bezeichnung.']]></studiengang_orgform_bezeichnung>
				<studiengang_studiengangsleitung><![CDATA['.$stgl.']]></studiengang_studiengangsleitung>
				<studiengang_bezeichnung_sto><![CDATA['.$sto_studiengang_bezeichnung.']]></studiengang_bezeichnung_sto>
				<studiengang_bezeichnung_sto_englisch><![CDATA['.$sto_studiengang_bezeichnung_englisch.']]></studiengang_bezeichnung_sto_englisch>
				<lv_studiengang_kz><![CDATA['.$lv_studiengang_kz.']]></lv_studiengang_kz>
				<lv_studiengang_bezeichnung><![CDATA['.$lv_studiengang_bezeichnung.']]></lv_studiengang_bezeichnung>
				<lv_studiengang_typ><![CDATA['.$lv_studiengang_typ.']]></lv_studiengang_typ>
				<lv_studiengang_art><![CDATA['.$lv_studiengang_art.']]></lv_studiengang_art>
				<anrede><![CDATA['.$student->anrede.']]></anrede>
				<geschlecht><![CDATA['.$student->geschlecht.']]></geschlecht>
				<svnr><![CDATA['.$student->svnr.']]></svnr>
				<ersatzkennzeichen><![CDATA['.$student->ersatzkennzeichen.']]></ersatzkennzeichen>
				<familienstand><![CDATA['.$student->familienstand.']]></familienstand>
				<rektor><![CDATA['.$rektor.']]></rektor>
				<studienbeginn_beginn><![CDATA['.$datum_obj->convertISODate($studienbeginn).']]></studienbeginn_beginn>
				<studiensemester_beginn><![CDATA['.$studiensemester.']]></studiensemester_beginn>
				<studiensemester_aktuell><![CDATA['.$stsem->studiensemester_kurzbz.']]></studiensemester_aktuell>
				<studienjahr_kurzbz><![CDATA['.$stsem->studienjahr_kurzbz.']]></studienjahr_kurzbz>
				<studiensemester_aktuell_bezeichnung><![CDATA['.$stsem->bezeichnung.']]></studiensemester_aktuell_bezeichnung>
				<studienbeginn_aktuell><![CDATA['.$datum_obj->convertISODate($stsem->start).']]></studienbeginn_aktuell>
				<tagesdatum><![CDATA['.date('d.m.Y').']]></tagesdatum>
				<max_semester><![CDATA['.$studiengang->max_semester.']]></max_semester>
				<anmerkungpre><![CDATA['.$prestudent->anmerkung.']]></anmerkungpre>
				<aktiv><![CDATA['.$student->aktiv.']]></aktiv>
			</student>';
		}
	}
	echo '</studenten>';
}
?>
