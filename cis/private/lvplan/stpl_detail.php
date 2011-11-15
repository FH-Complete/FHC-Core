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
$sql_query='SELECT campus.vw_stundenplan.*, tbl_lehrfach.bezeichnung, vw_mitarbeiter.titelpre, vw_mitarbeiter.titelpost, vw_mitarbeiter.nachname, vw_mitarbeiter.vorname';
$sql_query.=", (SELECT count(*) FROM public.tbl_studentlehrverband 
				WHERE studiengang_kz=vw_stundenplan.studiengang_kz AND semester=vw_stundenplan.semester
				AND (verband=vw_stundenplan.verband OR vw_stundenplan.verband is null OR trim(vw_stundenplan.verband)='')
				AND (gruppe=vw_stundenplan.gruppe OR vw_stundenplan.gruppe is null OR trim(vw_stundenplan.gruppe)='')
				AND studiensemester_kurzbz='".addslashes($stsem)."') as anzahl_lvb
			, (SELECT count(*) FROM public.tbl_benutzergruppe 
				WHERE gruppe_kurzbz=vw_stundenplan.gruppe_kurzbz AND studiensemester_kurzbz='".addslashes($stsem)."') as anzahl_grp";
$sql_query.=' FROM (campus.vw_stundenplan JOIN lehre.tbl_lehrfach USING (lehrfach_id)) JOIN campus.vw_mitarbeiter USING (uid)';
$sql_query.=" WHERE datum='".addslashes($datum)."' AND stunde='".addslashes($stunde)."'";
if ($type=='lektor')
    $sql_query.=" AND vw_stundenplan.uid='".addslashes($pers_uid)."' ";
elseif ($type=='ort')
    $sql_query.=" AND vw_stundenplan.ort_kurzbz='".addslashes($ort_kurzbz)."' ";
else
{
	if($stg_kz=='' || $sem=='')
		die('Fehlerhafte Parameteruebergabe');
	
    $sql_query.=" AND vw_stundenplan.studiengang_kz='".addslashes($stg_kz)."' AND (vw_stundenplan.semester='".addslashes($sem)."'";
    if ($type=='student')
		$sql_query.=' OR vw_stundenplan.semester='.($sem+1);
	$sql_query.=')';
	
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
$erg_stpl=$db->db_query($sql_query);
$num_rows_stpl=$db->db_num_rows($erg_stpl);

//Reservierungen
$sql_query="SELECT vw_reservierung.*, vw_mitarbeiter.titelpre, vw_mitarbeiter.titelpost, vw_mitarbeiter.vorname,vw_mitarbeiter.nachname FROM campus.vw_reservierung, campus.vw_mitarbeiter WHERE datum='".addslashes($datum)."' AND stunde='".addslashes($stunde)."'";
if (isset($ort_kurzbz))
    $sql_query.=" AND vw_reservierung.ort_kurzbz='".addslashes($ort_kurzbz)."'";
if ($type=='lektor')
    $sql_query.=" AND vw_reservierung.uid='".addslashes($pers_uid)."' ";
$sql_query.=" AND vw_reservierung.uid=vw_mitarbeiter.uid";
if ($type=='verband' || $type=='student')
    $sql_query.=" AND studiengang_kz='".addslashes($stg_kz)."' AND (semester='".addslashes($sem)."' OR semester=0 OR semester IS NULL)";
$sql_query.=' ORDER BY  titel LIMIT 100';
//echo $sql_query.'<BR>';
$erg_repl=$db->db_query($sql_query);
$num_rows_repl=$db->db_num_rows($erg_repl);
?>

<html>
<head>
    <title><?php echo $p->t('lvplan/lehrveranstaltungsplanDetails');?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>
<body id="inhalt">
<H2><?php echo $p->t('lvplan/lehrveranstaltungsplan');?> &rArr; <?php echo $p->t('abgabetool/details');?></H2>
<?php echo $p->t('abgabetool/datum');?>: <?php echo htmlentities($datum); ?><BR>
<?php echo $p->t('global/stunde').': '.htmlentities($stunde); ?><BR><BR>

<table class="stdplan">
<?php
if ($num_rows_stpl>0)
echo '<tr> <th>'.$p->t('lvplan/unr').'</th><th>'.$p->t('lvaliste/lektor').'</th><th>'.$p->t('lvplan/ort').'</th><th>'.$p->t('lvaliste/lehrfach').'</th><th>'.$p->t('global/bezeichnung').'</th><th>'.$p->t('global/verband').'</th><th>'.$p->t('lvplan/einheit').'</th><th>'.$p->t('lvplan/info').'</th></tr>';
$ort = new ort();
for ($i=0; $i<$num_rows_stpl; $i++)
{
    $unr=$db->db_result($erg_stpl,$i,"unr");
    $ortkurzbz=$db->db_result($erg_stpl,$i,"ort_kurzbz");
    $lehrfachkurzbz=$db->db_result($erg_stpl,$i,"lehrfach");
    $bezeichnung=$db->db_result($erg_stpl,$i,"bezeichnung");
    $pers_kurzbz=$db->db_result($erg_stpl,$i,"lektor");
    $titelpre=$db->db_result($erg_stpl,$i,"titelpre");
    $titelpost=$db->db_result($erg_stpl,$i,"titelpost");
    $pers_vorname=$db->db_result($erg_stpl,$i,"vorname");
    $pers_nachname=$db->db_result($erg_stpl,$i,"nachname");
    $pers_email=$db->db_result($erg_stpl,$i,"uid").'@'.DOMAIN;
    $stgkurzbz=strtoupper(trim($db->db_result($erg_stpl,$i,"stg_typ").$db->db_result($erg_stpl,$i,"stg_kurzbz")));
    $semester=trim($db->db_result($erg_stpl,$i,"semester"));
    $verband=trim($db->db_result($erg_stpl,$i,"verband"));
    $gruppe=trim($db->db_result($erg_stpl,$i,"gruppe"));
    $gruppe_kurzbz=trim($db->db_result($erg_stpl,$i,"gruppe_kurzbz"));
    $anzahl_lvb=trim($db->db_result($erg_stpl,$i,"anzahl_lvb"));
    $anzahl_grp=trim($db->db_result($erg_stpl,$i,"anzahl_grp"));
	$titel=trim($db->db_result($erg_stpl,$i,"titel"));
    $gesamtanzahl = ($anzahl_grp!=0?$anzahl_grp:$anzahl_lvb);
    $ort->load($ortkurzbz);
    ?>
    <tr class="<?php echo 'liste'.$i%2; ?>">
        <td><?php echo $unr; ?></td>
        <td><A class="Item" href="mailto:<?php echo $pers_email; ?>"><?php echo $titelpre.' '.$pers_vorname.' '.$pers_nachname.' '.$titelpost; ?></A></td>
        <td  title="<?php echo $ort->bezeichnung;?>"><?php echo (!empty($ortkurzbz)?'<a href="'.RAUMINFO_PATH.trim($ortkurzbz).'.html" target="_blank">'.$ortkurzbz.'</a>':$ortkurzbz); ?></td>
        <td><?php echo $lehrfachkurzbz; ?></td>
        <td><?php echo $bezeichnung; ?></td>

       	<td title="<?php echo $stgkurzbz.$semester.mb_strtolower($verband).$gruppe; ?>">
			<?php echo (!is_null($semester) && !empty($semester)? '<A class="Item" title="'.$anzahl_lvb.' '.$p->t('lvplan/studierende').'" href="mailto:'.$stgkurzbz.$semester.mb_strtolower($verband).$gruppe.'@'.DOMAIN .'">':''); ?>
			<?php echo $stgkurzbz.'-'.$semester.$verband.$gruppe;?>
			<?php echo (!is_null($semester) && !empty($semester)?'</A>':''); ?>
		</td>

        <td><A class="Item" title="<?php echo $anzahl_grp.' Studierende';?>" href="mailto:<?php echo mb_strtolower($gruppe_kurzbz).'@'.DOMAIN; ?>">
        <?php echo $gruppe_kurzbz; ?></A></td>
		<td><?php echo $titel; ?></td>
        
    </tr>
    <?php
}
?>
</table><BR>
<?php
if ($num_rows_repl>0)
{
    echo '<h2>'.$p->t('lvplan/reservierungen').'</h2>';
    echo '<table class="stdplan">';
    echo '<tr><th>'.$p->t('global/titel').'</th><th>'.$p->t('lvplan/ort').'</th><th>'.$p->t('global/person').'</th><th>'.$p->t('global/beschreibung').'</th></tr>';
    for ($i=0; $i<$num_rows_repl; $i++)
    {
        $titel=$db->db_result($erg_repl,$i,"titel");
        $ortkurzbz=$db->db_result($erg_repl,$i,"ort_kurzbz");
        $titelpre=$db->db_result($erg_repl,$i,"titelpre");
        $titelpost=$db->db_result($erg_repl,$i,"titelpost");
   		$pers_vorname=$db->db_result($erg_repl,$i,"vorname");
   		$pers_nachname=$db->db_result($erg_repl,$i,"nachname");
    	$pers_email=$db->db_result($erg_repl,$i,"uid").'@'.DOMAIN;
    	$beschreibung=$db->db_result($erg_repl,$i,"beschreibung");
        echo '<tr class="liste'.($i%2).'">';
        echo '<td >'.$titel.'</td>';
        echo '<td>'.(!empty($ortkurzbz)?'<a href="'.RAUMINFO_PATH.trim($ortkurzbz).'.html" target="_blank">'.$ortkurzbz.'</a>':$ortkurzbz).'</td>';
        echo '<td  ><A href="mailto:'.$pers_email.'">'.$titelpre.' '.$pers_vorname.' '.$pers_nachname.' '.$titelpost.'</A></td>';
        echo '<td >'.$beschreibung.'</td></tr>';
    }
    echo '</table>';
}
?>
<P><?php echo $p->t('lvplan/fehlerUndFeedback')?> <A class="Item" href="mailto:<?php echo MAIL_LVPLAN;?>"><?php echo $p->t('lvplan/lvKoordinationsstelle')?></A>.</P>
</body></html>
