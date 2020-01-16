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
 *          Gerald Simane-Sequens <gerald.simane@technikum-wien.at>.
 */
/****************************************************************************
 * Script: 			stpl_detail.php
 * Descr:  			Das Script dient zur Detailanzeige eines Eintrags im Stundenplan.
 *					Es wird in Verbandsplan und Reservierungen gesucht.
 * Verzweigungen: 	von stpl_week.php
 * Author: 			Christian Paminger
 * Erstellt: 		21.8.2001
 * Update: 			11.11.2004 von Christian Paminger
 *****************************************************************************/

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/ort.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/mitarbeiter.class.php');

$sprache = getSprache(); 
$p = new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

// Variablen uebernehmen
if (isset($_GET['type']))
	$type=$_GET['type'];
else 
	$type='';

if (isset($_GET['datum']))
	$datum=$_GET['datum'];
if (isset($_GET['stunde']))
	$stunde=$_GET['stunde'];
if (isset($_GET['ort_kurzbz']))
	$ort_kurzbz=$_GET['ort_kurzbz'];
if (isset($_GET['pers_uid']))
	$pers_uid=$_GET['pers_uid'];
if (isset($_GET['stg_kz']))
	$stg_kz=$_GET['stg_kz'];
if (isset($_GET['sem']))
	$sem=$_GET['sem'];
	
if($sem!='' && !is_numeric($sem))
	die($p->t('lvplan/semesterIstUngueltig'));

if($stunde!='' && !is_numeric($stunde))
	die($p->t('lvplan/stundeIstUngueltig'));

if (isset($_GET['ver']))
	$ver=$_GET['ver'];
	
if (isset($_GET['grp']))
	$grp=$_GET['grp'];
if (isset($_GET['gruppe_kurzbz']))
	$gruppe_kurzbz=$_GET['gruppe_kurzbz'];

$datum_obj = new datum();
if(!$datum_obj->checkDatum($datum))
	die($p->t('lvplan/datumIstUngueltig'));

$stsem = getStudiensemesterFromDatum($datum);
//Stundenplan
$sql_query="
SELECT 
	campus.vw_stundenplan.*, lehrfach.bezeichnung, vw_mitarbeiter.titelpre, 
	vw_mitarbeiter.titelpost, vw_mitarbeiter.nachname, vw_mitarbeiter.vorname,
	(SELECT 
		count(*) 
	 FROM 
	 	public.tbl_studentlehrverband 
	 WHERE 
	 	studiengang_kz=vw_stundenplan.studiengang_kz 
	 	AND semester=vw_stundenplan.semester
		AND (verband=vw_stundenplan.verband OR vw_stundenplan.verband is null OR trim(vw_stundenplan.verband)='')
		AND (gruppe=vw_stundenplan.gruppe OR vw_stundenplan.gruppe is null OR trim(vw_stundenplan.gruppe)='')
		AND studiensemester_kurzbz=".$db->db_add_param($stsem).") as anzahl_lvb, 
	(SELECT 
		count(*) 
	 FROM 
	 	public.tbl_benutzergruppe 
	 WHERE 
	 	gruppe_kurzbz=vw_stundenplan.gruppe_kurzbz 
	 	AND studiensemester_kurzbz=".$db->db_add_param($stsem).") as anzahl_grp
FROM 
	campus.vw_stundenplan 
	JOIN lehre.tbl_lehrveranstaltung as lehrfach ON (vw_stundenplan.lehrfach_id=lehrfach.lehrveranstaltung_id)
	JOIN campus.vw_mitarbeiter USING (uid)
WHERE 
	datum=".$db->db_add_param($datum)." 
	AND stunde=".$db->db_add_param($stunde);

if ($type=='lektor')
{
    $sql_query.=" AND vw_stundenplan.uid=".$db->db_add_param($pers_uid);
}
elseif ($type=='ort' || $type=='lva')
    $sql_query.=" AND vw_stundenplan.ort_kurzbz=".$db->db_add_param($ort_kurzbz);
else
{
	if($stg_kz=='' || $sem=='')
		die('Fehlerhafte Parameteruebergabe');
	
	if($type=="verband" && $stg_kz!='' && $sem!='')
	{
		// Studiengangsansicht
	    $sql_query.=" AND vw_stundenplan.studiengang_kz=".$db->db_add_param($stg_kz)." AND (vw_stundenplan.semester=".$db->db_add_param($sem);
	    if ($type=='student')
			$sql_query.=' OR vw_stundenplan.semester='.$db->db_add_param($sem+1);
		$sql_query.=')';
	}
	else
	{
		// Pers. Ansicht
		$sql_query.=" AND EXISTS (SELECT 1 FROM campus.vw_student_lehrveranstaltung 
									WHERE lehreinheit_id=vw_stundenplan.lehreinheit_id AND uid=".$db->db_add_param($pers_uid).")";
	}
	// Manfred weiss nicht mehr warum, aber wir aktivieren 23-09-2009
	// 01-10-2009: jetzt weiss ers wieder Grund: Student sieht sonst die uebergeordneten nicht
    /*
	if (isset($ver) && $ver!='0')
		$sql_query.=" AND (verband='$ver' OR verband IS NULL OR verband='0')";
    if (isset($ver) && $grp!='0')
		$sql_query.=" AND (gruppe='$grp' OR gruppe IS NULL OR gruppe='0')";
	*/
}

$sql_query.=' ORDER BY unr ASC, stg_kurzbz, vw_stundenplan.semester, verband, gruppe, gruppe_kurzbz LIMIT 100';
//echo $sql_query.'<BR>';

$erg_stpl = $db->db_query($sql_query);
$num_rows_stpl = $db->db_num_rows($erg_stpl);

//Reservierungen
$sql_query="
SELECT 
	vw_reservierung.*, vw_mitarbeiter.titelpre, vw_mitarbeiter.titelpost, 
	vw_mitarbeiter.vorname, vw_mitarbeiter.nachname, reserviert_von.titelpre AS titelpre_reserviertvon, reserviert_von.titelpost AS titelpost_reserviertvon, 
	reserviert_von.vorname AS vorname_reserviertvon, reserviert_von.nachname AS nachname_reserviertvon 
FROM 
	campus.vw_reservierung
	JOIN campus.vw_mitarbeiter ON vw_reservierung.uid=vw_mitarbeiter.uid
	LEFT JOIN campus.vw_mitarbeiter reserviert_von ON vw_reservierung.insertvon=reserviert_von.uid
WHERE 
	datum=".$db->db_add_param($datum)." 
	AND stunde=".$db->db_add_param($stunde);

if (isset($ort_kurzbz) && $type=='ort')
    $sql_query.=" AND vw_reservierung.ort_kurzbz=".$db->db_add_param($ort_kurzbz);
if ($type=='lektor')
    $sql_query.=" AND vw_reservierung.uid=".$db->db_add_param($pers_uid);
if ($type=='verband' || $type=='student')
{
    $sql_query.=" AND studiengang_kz=".$db->db_add_param($stg_kz)." 
    AND (semester=".$db->db_add_param($sem)." OR semester=0 OR semester IS NULL)";
}
$sql_query.=' ORDER BY  titel LIMIT 100';
//echo $sql_query.'<BR>';

$erg_repl = $db->db_query($sql_query);
$num_rows_repl = $db->db_num_rows($erg_repl);

echo '<html>
<head>
    <title>'.$p->t('lvplan/lehrveranstaltungsplanDetails').'</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
<H2>'.$p->t('lvplan/lehrveranstaltungsplan').' &rArr; '.$p->t('abgabetool/details').'</H2>
'.$p->t('abgabetool/datum').': '.htmlentities($datum_obj->formatDatum($datum, 'd.m.Y')).'<BR>
'.$p->t('global/stunde').': '.htmlentities($stunde).'<BR><BR>
';

// LVPlan
if ($num_rows_stpl>0)
{
	echo '
	<table class="stdplan">
		<tr>
			<th>'.$p->t('lvplan/unr').'</th>
			<th>'.$p->t('lvaliste/lektor').'</th>
			<th>'.$p->t('lvplan/ort').'</th>
			<th>'.$p->t('lvaliste/lehrfach').'</th>
			<th>'.$p->t('global/bezeichnung').'</th>
			<th>'.$p->t('global/verband').'</th>
			<th>'.$p->t('lvplan/einheit').'</th>
			<th>'.$p->t('lvplan/info').'</th>
		</tr>';

	$ort = new ort();
	$i=0;
	while($row = $db->db_fetch_object($erg_stpl))
	{
		$i++;
	    $unr = $row->unr;
	    $ortkurzbz = $row->ort_kurzbz;
	    $lehrfachkurzbz = $row->lehrfach;
	    $bezeichnung = $row->bezeichnung;
	    $pers_kurzbz = $row->lektor;
	    $titelpre = $row->titelpre;
	    $titelpost = $row->titelpost;
	    $pers_vorname = $row->vorname;
	    $pers_nachname = $row->nachname;
	    $pers_email = $row->uid.'@'.DOMAIN;
	    $stgkurzbz = mb_strtoupper(trim($row->stg_typ.$row->stg_kurzbz));
	    $semester = trim($row->semester);
	    $verband = trim($row->verband);
	    $gruppe = trim($row->gruppe);
	    $gruppe_kurzbz = trim($row->gruppe_kurzbz);
	    $anzahl_lvb = trim($row->anzahl_lvb);
	    $anzahl_grp = trim($row->anzahl_grp);
		$titel = trim($row->titel);
	    $gesamtanzahl = ($anzahl_grp!=0?$anzahl_grp:$anzahl_lvb);
	    $ort->load($ortkurzbz);

	    // no profile link for fake Mitarbeiter like Dummylektor, Personalnr must be > 0
	    $profileLink = true;
	    $mitarbeiter = new mitarbeiter();

	    if ($mitarbeiter->load($row->uid))
		{
			if (isset($mitarbeiter->personalnummer) && is_numeric($mitarbeiter->personalnummer) && (int)$mitarbeiter->personalnummer < 0)
				$profileLink = false;
		}

	    echo '
	    <tr class="liste'.($i%2).'">
	        <td>'.$db->convert_html_chars($unr).'</td>
	        <td>'.($profileLink ? '<A class="Item" href="../profile/index.php?uid='.$row->uid.'" target="_self" onClick="window.resizeTo(1200,880)">' : '').$db->convert_html_chars($titelpre.' '.$pers_vorname.' '.$pers_nachname.' '.$titelpost).($profileLink ? '</A>' : '').'</td>
	        <td  title="'.$db->convert_html_chars($ort->bezeichnung).'">'.(!empty($ortkurzbz)?($ort->content_id!=''?'<a href="../../../cms/content.php?content_id='.$ort->content_id.'" target="_self" onClick="window.resizeTo(1200,880)">'.$db->convert_html_chars($ortkurzbz).'</a>':$db->convert_html_chars($ortkurzbz)):$db->convert_html_chars($ortkurzbz)).'</td>
	        <td>'.$db->convert_html_chars($lehrfachkurzbz).'</td>
	        <td>'.$db->convert_html_chars($bezeichnung).'</td>
	       	<td title="'.$db->convert_html_chars($stgkurzbz.$semester.mb_strtolower($verband).$gruppe).'">
				'.(!is_null($semester) && !empty($semester)?'<A class="Item" title="'.$anzahl_lvb.' '.$p->t('lvplan/studierende').'" href="mailto:'.$stgkurzbz.$semester.mb_strtolower($verband).$gruppe.'@'.DOMAIN .'">':'');
		echo $db->convert_html_chars($stgkurzbz.'-'.$semester.$verband.$gruppe);
		echo (!is_null($semester) && !empty($semester)?'</A>':'');
		echo '
			</td>
	
	        <td><A class="Item" title="'.$anzahl_grp.' Studierende" href="mailto:'.mb_strtolower($gruppe_kurzbz).'@'.DOMAIN.'">
	        '.$db->convert_html_chars($gruppe_kurzbz).'</A></td>
			<td>'.$db->convert_html_chars($titel).'</td>
	        
	    </tr>';    
	}
	echo '</table><BR>';
}

// Reservierungen
if ($num_rows_repl>0)
{
    echo '<h2>'.$p->t('lvplan/reservierungen').'</h2>';
    echo '<table class="stdplan">';
    echo '<tr><th>'.$p->t('global/titel').'</th><th>'.$p->t('lvplan/ort').'</th><th>'.$p->t('global/person').'</th><th>'.$p->t('global/beschreibung').'</th><th>'.$p->t('lvplan/reserviertVon').'</th></tr>';
    $i=0;
    $ort = new ort();
    while($row = $db->db_fetch_object($erg_repl))
    {
    	$i++;
        $titel=$row->titel;
        $ortkurzbz=$row->ort_kurzbz;
        $titelpre=$row->titelpre;
        $titelpost=$row->titelpost;
   		$pers_vorname=$row->vorname;
   		$pers_nachname=$row->nachname;
    	$pers_email=$row->uid.'@'.DOMAIN;
    	$beschreibung=$row->beschreibung;
    	$reserviertvon=$row->insertvon;
		$titelpre_reserviertvon=$row->titelpre_reserviertvon;
		$titelpost_reserviertvon=$row->titelpost_reserviertvon;
		$pers_vorname_reserviertvon=$row->vorname_reserviertvon;
		$pers_nachname_reserviertvon=$row->nachname_reserviertvon;

    	$ort->load($ortkurzbz);
    	
        echo '<tr class="liste'.($i%2).'">';
        echo '<td>'.$db->convert_html_chars($titel).'</td>';
        echo '<td>'.(!empty($ortkurzbz)?($ort->content_id!=''?'<a href="../../../cms/content.php?content_id='.$ort->content_id.'" target="_self" onClick="window.resizeTo(1200,880)">'.$db->convert_html_chars($ortkurzbz).'</a>':$db->convert_html_chars($ortkurzbz)):$db->convert_html_chars($ortkurzbz)).'</td>';
        echo '<td><A href="mailto:'.$pers_email.'">'.$db->convert_html_chars($titelpre.' '.$pers_vorname.' '.$pers_nachname.' '.$titelpost).'</A></td>';
        echo '<td>'.$db->convert_html_chars($beschreibung).'</td>';
		echo '<td>'.$db->convert_html_chars($titelpre_reserviertvon.' '.$pers_vorname_reserviertvon.' '.$pers_nachname_reserviertvon.' '.$titelpost_reserviertvon).'</td>';
    }
    echo '</table><br>';
}
echo '<P>'.$p->t('lvplan/fehlerUndFeedback').' <A class="Item" href="mailto:'.MAIL_LVPLAN.'">'.$p->t('lvplan/lvKoordinationsstelle').'</A>.</P>
</body></html>';
?>
