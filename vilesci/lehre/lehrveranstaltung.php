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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/organisationseinheit.class.php');
require_once('../../include/lvinfo.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/addon.class.php');
require_once('../../include/sprache.class.php');
require_once('../../include/lehrmodus.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

//Sprache
$sprache = getSprache();

$s=new studiengang();
$s->getAll('typ, kurzbz', false);
$studiengang=$s->result;

$user = get_uid();
$oe_studiengang='';
if (isset($_GET['stg_kz']) || isset($_POST['stg_kz']))
{
	$stg_kz=(isset($_GET['stg_kz'])?$_GET['stg_kz']:$_POST['stg_kz']);
	if($stg_kz!='')
	{
		$stg_obj = new studiengang();
		if(!$stg_obj->load($stg_kz))
			die('Studiengang kann nicht geladen werden');
		$oe_studiengang = $stg_obj->oe_kurzbz;
	}
}
else
	$stg_kz='';

if (isset($_GET['semester']) || isset($_POST['semester']))
	$semester=(isset($_GET['semester'])?$_GET['semester']:$_POST['semester']);
else
	$semester = -1;

if(!is_numeric($stg_kz) && $stg_kz!='')
	$stg_kz='';

if(!is_numeric($semester))
	$semester = -1;



$oe_fachbereich='';
if(isset($_REQUEST['fachbereich_kurzbz']))
{
	$fachbereich_kurzbz = $_REQUEST['fachbereich_kurzbz'];
	if($fachbereich_kurzbz!='')
	{
		$fb_obj = new fachbereich();
		if(!$fb_obj->load($fachbereich_kurzbz))
			die('Institut konnte nicht geladen werden');
		$oe_fachbereich = $fb_obj->oe_kurzbz;
	}
}
else
	$fachbereich_kurzbz = '';


$oe_organisationseinheit='';
if (isset($_REQUEST['oe_kurzbz']))
{
	$oe_kurzbz = $_REQUEST['oe_kurzbz'];
	if($oe_kurzbz != '')
	{
		$oe_obj = new organisationseinheit();
		if(!$oe_obj->load($oe_kurzbz))
			die('Organisationseinheit konnte nicht geladen werden');
		$oe_organisationseinheit = $oe_obj->oe_kurzbz;
	}
}
else
	$oe_kurzbz='';

if (isset($_REQUEST['orgform']))
{
	$orgform_kurzbz = $_REQUEST['orgform'];
}
else
	$orgform_kurzbz='';

if (isset($_REQUEST['lehrveranstaltung_id']))
{
	$lehrveranstaltung_id = $_REQUEST['lehrveranstaltung_id'];
}
else
	$lehrveranstaltung_id = '';

if (isset($_REQUEST['lehrveranstaltung_name']))
{
	$lehrveranstaltung_name = trim($_REQUEST['lehrveranstaltung_name']);
}
else
	$lehrveranstaltung_name = '';

if (isset($_REQUEST['lehrveranstaltung_kurzbz']))
{
	$lehrveranstaltung_kurzbz = trim($_REQUEST['lehrveranstaltung_kurzbz']);
}
else
	$lehrveranstaltung_kurzbz = '';

//Wenn kein Fachbereich und kein Studiengang gewaehlt wurde
//dann wird der Studiengang auf 0 gesetzt da sonst die zu ladende liste zu lang wird

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
$write_admin=false;
$write_low=false;
$messages='';

if(isset($_POST['lvid']))
{
	//Wenn eine LVID uebergeben wird, dann wird die berechtigung des studienganges
	//dieser LV geprueft
	$lv_obj = new lehrveranstaltung();
	$lv_obj->load($_POST['lvid']);
	$stg_obj = new studiengang();
	$stg_obj->load($lv_obj->studiengang_kz);
	$oe_studiengang = $stg_obj->oe_kurzbz;
}
if($rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_studiengang, 'suid')
|| $rechte->isBerechtigt('lehre/lehrveranstaltung', $oe_fachbereich, 'suid'))
	$write_admin=true;

if($rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_studiengang, 'suid')
|| $rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt', $oe_fachbereich, 'suid'))
	$write_low=true;

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung:begrenzt'))
	die($rechte->errormsg);

if (isset($_GET['isaktiv']) || isset($_POST['isaktiv']))
	$isaktiv=(isset($_GET['isaktiv'])?$_GET['isaktiv']:$_POST['isaktiv']);
else
	if($write_admin)
		$isaktiv='';
	else
		$isaktiv='true';

// Löschen der Daten
if(isset($_GET['delete_lvid']))
{
	if($write_admin)
	{
		$lvid=$_GET['delete_lvid'];
		$lehrveranstaltung=new lehrveranstaltung();
		if($lehrveranstaltung->load($lvid))
		{
			if(!$lehrveranstaltung->delete($lvid))
				echo $lehrveranstaltung->errormsg;
		}
		else
		{
			echo 'Fehler beim Laden der Lehrveranstaltung: '.$lehrveranstaltung->errormsg."\n";
		}
	}
	else
		echo " Keine Berechtigung, um Lehrveranstaltung zu löschen!\n";
}

// Speichern der Daten
if(isset($_POST['lvid']) && is_numeric($_POST['lvid']))
{
	// Die Aenderungen werden per Ajax Request durchgefuehrt,
	// daher wird nach dem Speichern mittels exit beendet
	if($write_admin)
	{
		//Lehrevz Speichern
		if(isset($_POST['lehrevz']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehreverzeichnis=$_POST['lehrevz'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit( 'true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Aktiv Feld setzen
		if(isset($_POST['aktiv']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->aktiv=($_POST['aktiv']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	}

	if($write_low || $write_admin)
	{
		//LVInfo kopieren
		if(isset($_POST['source_id']))
		{
			$lvinfo = new lvinfo();
			if(!$lvinfo->copy($_POST['source_id'], $_POST['lvid']))
				exit('Fehler beim Kopieren');
			else
				exit('true');
		}

		//Lehre Feld setzen
		if(isset($_POST['lehre']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehre=($_POST['lehre']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Zeugnis Feld setzen
		if(isset($_POST['zeugnis']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->zeugnis=($_POST['zeugnis']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Sort Speichern
		if(isset($_POST['sort']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->sort=$_POST['sort'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Incoming Speichern
		if(isset($_POST['incoming']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->incoming=$_POST['incoming'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//FBK Speichern
		if(isset($_POST['fbk']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->koordinator=$_POST['fbk'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Lehrform Speichern
		if(isset($_POST['lf']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehrform_kurzbz = $_POST['lf'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Lehrtyp Speichern
		if(isset($_POST['lt']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehrtyp_kurzbz = $_POST['lt'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Lehrmodus Speichern
		if(isset($_POST['lm']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->lehrmodus_kurzbz = $_POST['lm'];
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}

		//Projektarbeit Feld setzen
		if(isset($_POST['projektarbeit']))
		{
			$lv_obj = new lehrveranstaltung();
			if($lv_obj->load($_POST['lvid']))
			{
				$lv_obj->projektarbeit=($_POST['projektarbeit']=='true'?false:true);
				$lv_obj->updateamum = date('Y-m-d H:i:s');
				$lv_obj->updatevon = $user;
				if($lv_obj->save(false))
					exit('true');
				else
					exit('Fehler beim Speichern:'.$lv_obj->errormsg);
			}
			else
				exit('Fehler beim Laden der LV:'.$lv_obj->errormsg);
		}
	}
	else
	{
		exit('Sie haben keine Schreibrechte fuer diese Seite');
	}
}

//Lehrformen holen
$qry = "
SELECT
	lehrform_kurzbz,
	bezeichnung
FROM
	lehre.tbl_lehrform ORDER BY lehrform_kurzbz";

$lf = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$lf[$row->lehrform_kurzbz]['lehrform_kurzbz']=$row->lehrform_kurzbz;
		$lf[$row->lehrform_kurzbz]['bezeichnung']=$row->bezeichnung;
	}
}

//Lehrtypen holen
$qry = "
SELECT
	lehrtyp_kurzbz,
	bezeichnung
FROM
	lehre.tbl_lehrtyp ORDER BY lehrtyp_kurzbz";

$lt = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$lt[$row->lehrtyp_kurzbz]['lehrtyp_kurzbz']=$row->lehrtyp_kurzbz;
		$lt[$row->lehrtyp_kurzbz]['bezeichnung']=$row->bezeichnung;
	}
}

//Lehrmodus holen
$qry = "
SELECT
	lehrmodus_kurzbz,
	bezeichnung_mehrsprachig
FROM
	lehre.tbl_lehrmodus ORDER BY lehrmodus_kurzbz";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
	//	$lm[$row->lehrmodus_kurzbz]['lehrmodus_kurzbz']=$row->lehrmodus_kurzbz;
		$lm_beschr = new lehrmodus();
		$lm_beschr ->load($row->lehrmodus_kurzbz);
		$lm[$row->lehrmodus_kurzbz]['bezeichnung_mehrsprachig'] = $lm_beschr->bezeichnung_mehrsprachig[$sprache];
	}
}


//Fachbereichskoordinatoren holen
$fb_kurzbz='';
if($stg_kz!='')
{
	$where = "oe_kurzbz=(SELECT oe_kurzbz FROM public.tbl_studiengang
						WHERE studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER)." LIMIT 1)";
	$where2="studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);
	$tables='lehre.tbl_lehrveranstaltung';
}
else
{
	if($fachbereich_kurzbz != '')
		$fb_kurzbz=$fachbereich_kurzbz;
	else
	{
		$fachb=new fachbereich();
		$fachb->loadOE($oe_kurzbz);
		$fb_kurzbz=$fachb->fachbereich_kurzbz;
	}
	$where = "fachbereich_kurzbz=".$db->db_add_param($fb_kurzbz);
	$where2 = $where." AND
	          tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
	          tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz";
	$tables='lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_fachbereich';
}

$qry = "
SELECT
	distinct
	vorname,
	nachname,
	uid
FROM
	campus.vw_mitarbeiter JOIN
	(SELECT uid FROM public.tbl_benutzerfunktion WHERE funktion_kurzbz='fbk' AND $where
	 UNION
	 SELECT tbl_lehrveranstaltung.koordinator as uid FROM $tables WHERE $where2) as a USING(uid) ORDER BY nachname, vorname";

$fbk = array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$fbk[$row->uid]['vorname']=$row->vorname;
		$fbk[$row->uid]['nachname']=$row->nachname;
	}
}

//Lehrveranstaltungen mit OEs holen
$sql_query = "
	SELECT
	tbl_lehrveranstaltung.*, tbl_organisationseinheit.organisationseinheittyp_kurzbz,
	tbl_organisationseinheit.bezeichnung as oe_bezeichnung
	FROM
		lehre.tbl_lehrveranstaltung
		LEFT JOIN lehre.tbl_lehreinheit USING (lehrveranstaltung_id)
		LEFT JOIN lehre.tbl_lehrveranstaltung  as lehrfach on (lehre.tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id)
		LEFT JOIN public.tbl_organisationseinheit ON (public.tbl_organisationseinheit.oe_kurzbz = lehre.tbl_lehrveranstaltung.oe_kurzbz)
	where
		true
";

if($stg_kz!='')
	$sql_query.= " AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($stg_kz, FHC_INTEGER);

if($oe_kurzbz!='')
	$sql_query.= " AND tbl_lehrveranstaltung.oe_kurzbz=".$db->db_add_param($oe_kurzbz);

if($semester != -1)
	$sql_query.=" AND tbl_lehrveranstaltung.semester=".$db->db_add_param($semester, FHC_INTEGER);

if($orgform_kurzbz != -1)
	if($orgform_kurzbz == 'none')
		$sql_query.=" AND (tbl_lehrveranstaltung.orgform_kurzbz IS NULL OR tbl_lehrveranstaltung.orgform_kurzbz='')";
	else
		$sql_query.=" AND tbl_lehrveranstaltung.orgform_kurzbz=".$db->db_add_param($orgform_kurzbz, FHC_STRING);

if($lehrveranstaltung_id != '')
	$sql_query.= " AND tbl_lehrveranstaltung.lehrveranstaltung_id=".$db->db_add_param($lehrveranstaltung_id, FHC_INTEGER);

if($lehrveranstaltung_name != '')
{
	$sql_query.= " AND (UPPER(tbl_lehrveranstaltung.bezeichnung) LIKE UPPER(".$db->db_add_param('%'.$lehrveranstaltung_name.'%', FHC_STRING).")";
	$sql_query.= " OR UPPER(tbl_lehrveranstaltung.bezeichnung_english) LIKE UPPER(".$db->db_add_param('%'.$lehrveranstaltung_name.'%', FHC_STRING).")) ";
}

if($lehrveranstaltung_kurzbz != '')
{
	$sql_query.= " AND (UPPER(tbl_lehrveranstaltung.kurzbz) LIKE UPPER(".$db->db_add_param('%'.$lehrveranstaltung_kurzbz.'%', FHC_STRING).")) ";
}

//Wenn nicht admin, werden erst nur die aktiven angezeigt, es koennen aber auch die inaktiven eingeblendet werden

$aktiv = '';
$isaktiv = trim($isaktiv);

if($isaktiv == 'true')
{
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=true';
}
elseif($isaktiv=='false')
{
	$aktiv = ' AND tbl_lehrveranstaltung.aktiv=false';
}
else
{
	$aktiv='';
}

$sql_query .= " GROUP BY tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_organisationseinheit.organisationseinheittyp_kurzbz, tbl_organisationseinheit.bezeichnung";

$sql_query .= " ORDER BY tbl_lehrveranstaltung.bezeichnung";

if($fb_kurzbz=='' && $stg_kz=='' && $semester=='0' && $oe_kurzbz=='')
	$result_lv='';
else
{
	if(!$result_lv = $db->db_query($sql_query))
		die("Lehrveranstaltung not found!");
}

//Studiengang DropDown
$outp = '';
$s = array();
$outp .= "<form action='".$_SERVER['PHP_SELF']."' method='GET' onsubmit='return checksubmit();'>";
$outp .= " Studiengang <SELECT name='stg_kz' id='select_stg_kz'>";
$outp .= "<OPTION value='' ".($stg_kz == ''?'selected':'').">-- Alle --</OPTION>";
$stg_berechtigt = $rechte->getStgKz('lehre/lehrveranstaltung:begrenzt');

foreach ($studiengang as $stg)
{
	if(in_array($stg->studiengang_kz, $stg_berechtigt))
	{
		$outp.="<OPTION value='$stg->studiengang_kz' ".($stg->studiengang_kz==$stg_kz?'selected':'').">".$db->convert_html_chars(strtoupper($stg->typ.$stg->kurzbz).' - '.$stg->bezeichnung)."</OPTION>";
	}
	if(!isset($s[$stg->studiengang_kz]))
		$s[$stg->studiengang_kz]=new stdClass();
	$s[$stg->studiengang_kz]->max_sem=9; // $stg->max_semester;
	$s[$stg->studiengang_kz]->kurzbz=$stg->kurzbzlang;
}
if(!isset($s['']))
	$s['']=new stdClass();
$s['']->max_sem=9;

$outp.='</SELECT>';

//Semester DropDown
$outp.= ' Semester <SELECT name="semester"><option value="-1">--Alle--</option>';
for ($i=0;$i<=$s[$stg_kz]->max_sem;$i++)
	$outp.="<OPTION value='$i' ".($i==$semester?'selected':'').">$i</OPTION>";
$outp.='</SELECT>';

//Orgform DropDown
$outp.= ' Orgform <SELECT name="orgform" id="select_orgform"><option value="-1">--Alle--</option>';
$outp.= '<OPTION value="none" '.($orgform_kurzbz=='none'?'selected':'').'>Ohne Orgform</OPTION>';
$orgform = new organisationsform();
$orgform->getOrgformLV();
foreach ($orgform->result as $of)
{
	if($orgform_kurzbz==$of->orgform_kurzbz)
		$selected = 'selected';
	else
		$selected = '';

		$outp.= '<OPTION value="'.$db->convert_html_chars($of->orgform_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($of->orgform_kurzbz).' - '.$db->convert_html_chars($of->bezeichnung).'</OPTION>';
}
$outp.='</SELECT>';

//if($write_admin) Von kindlm am 12.04.2013 auskommentiert, da Assistentinnen auch bei inaktiven LV's die Lehrform aendern koennen sollen
//{
	//Aktiv DropDown
	$outp.= ' Aktiv <SELECT name="isaktiv" id="isaktiv">';
	$outp.= "<OPTION value=''".($isaktiv==''?' selected':'').">-- Alle --</OPTION>";
	$outp.= "<OPTION value='true '".($isaktiv=='true'?'selected':'').">-- Aktiv --</OPTION>";
	$outp.= "<OPTION value='false '".($isaktiv=='false'?'selected':'').">-- Nicht aktiv --</OPTION>";
	$outp.= '</SELECT>';

	$outp.= '<input type="submit" style="margin-left:20px;" value="Anzeigen">';
//}
/*else
{
	$isaktiv='aktiv';
}*/


$outp .= '</hr><details id="detailTag" style="margin-top: 10px;"><summary style="float:right">Erweiterte Suchoptionen</summary><hr></hr>';

	//Organisationseinheit Dropdown
	$outp .= '<br>Organisationseinheit <select name="oe_kurzbz" style="width: 450px" id="select_oe_kurzbz"><option value="">-- Alle --</option>';
	$oe = new organisationseinheit();
	$oe->getAll();
	foreach($oe->result as $row)
	{
	if($oe_kurzbz == $row->oe_kurzbz)
		$selected = 'selected';
	else
		$selected = '';
	$outp .= '<option value="'.$db->convert_html_chars($row->oe_kurzbz).'" '.$selected.'>'.$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->bezeichnung).'</option>';
	}
	$outp .= '</select>';

	//Lehrveranstaltung ID Input
	$outp.= ' ID <input type="text" name="lehrveranstaltung_id" style="width: 70px" id="lehrveranstaltung_id" value="'.$lehrveranstaltung_id.'">';

	//Lehrveranstaltung Suche Kurzbezeichnung
	$outp.= ' Kurzbz <input type="text" name="lehrveranstaltung_kurzbz" style="width: 80px" id="lehrveranstaltung_kurzbz" 
					maxlength="16" value="'.$lehrveranstaltung_kurzbz.'" title="">';

	//Lehrveranstaltung Suche Bezeichnung
	$outp.= ' Name <input type="text" name="lehrveranstaltung_name" style="width: 450px" id="lehrveranstaltung_name"
					value="'.$lehrveranstaltung_name.'" placeholder="Mind. 3 Zeichen. Deutsche oder Englische Bezeichnung"
					title="Platzhalter _ (EIN beliebiges Zeichen) und % (beliebig viele Zeichen) möglich">';

	$outp.= ' <input type="submit" style="margin-left:20px" value="Anzeigen">';
	$outp.= '<hr></hr></details>';
	$outp.= '</form>';


echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">';

	include('../../include/meta/jquery.php');
	include('../../include/meta/jquery-tablesorter.php');


	// ADDONS laden
	$addon_obj = new addon();
	$addon_obj->loadAddons();
	foreach($addon_obj->result as $addon)
	{
		if(file_exists('../../addons/'.$addon->kurzbz.'/vilesci/init.js.php'))
			echo '<script type="application/x-javascript" src="../../addons/'.$addon->kurzbz.'/vilesci/init.js.php" ></script>';
	}

	// Wenn Seite fertig geladen ist Addons aufrufen
	echo '
	<script>
	$( document ).ready(function()
	{
		if(typeof addon  !== \'undefined\')
		{
			for(i in addon)
			{
				addon[i].init("vilesci/lehre/lehrveranstaltung.php", {uid:\''.$user.'\'});
			}
		}
	});
	</script>
	';

echo '

	<script type="text/javascript">
		$(document).ready(function()
			{
				openDetailTag();
				$("#t1").tablesorter(
				{
					sortList: [[2,0]],
					widgets: ["saveSort", "zebra", "filter", "stickyHeaders"],
					headers: {	4: {sorter: false, filter: false},
								5: {sorter: false, filter: false},
								6: {sorter: false, filter: false},
								13: {sorter: false, filter: false},
								15: {sorter: false, filter: false},
								16: {sorter: false, filter: false},
								19: {sorter: false, filter: false},
								20: {sorter: false, filter: false},
								22: {sorter: false, filter: false},
								23: {sorter: false, filter: false},
								24: {sorter: false, filter: false}},
					widgetOptions : {filter_functions : {
										// Add select menu to this column
										12 : {
										"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
										"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
										},
										14 : {
										"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
										"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
										},
										17 : {
										"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
										"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
										},
										18 : {
										"True" : function(e, n, f, i, $r, c, data) { return /t/.test(e); },
										"False" : function(e, n, f, i, $r, c, data) { return /f/.test(e); }
										}
									}
								}
				});
			});

			var isaktiv="'.$isaktiv.'";
			function checksubmit()
			{
				if(document.getElementById("select_stg_kz").value==\'\'
				&& document.getElementById("select_orgform").value==\'-1\'
				&& document.getElementById("select_oe_kurzbz").value==\'\'
				&& document.getElementById("lehrveranstaltung_id").value==\'\'
				&& document.getElementById("lehrveranstaltung_name").value==\'\'
				&& document.getElementById("lehrveranstaltung_kurzbz").value==\'\')
				{
					alert("Die Felder Studiengang, Orgform, Organisationseinheit, ID, Kurzbz und Name dürfen nicht gleichzeitig auf \'Alle\' gesetzt, bzw. leer sein");
					return false;
				}
				else if(document.getElementById("lehrveranstaltung_name").value !=\'\')
				{
					// Trim whitespace characters
					var searchstring = document.getElementById("lehrveranstaltung_name").value.trim();
					if (searchstring.length < 3)
					{
						alert("Geben Sie mindestens 3 Zeichen für die Namenssuche ein");
						return false;
					}
				}
				else
					return true;
			}
			function changelehrevz(lvid, lehrevz)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "lehrevz": lehrevz },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lehrevzok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lehrevzok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}
			function changesort(lvid, sort)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "sort": sort },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#sortok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#sortok"+lvid).css("background-color", "");}, 500);
						}
					},
					error: function() { alert("error"); }
				});
			}
			function changeincoming(lvid, incoming)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "incoming": incoming },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#incomingok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#incomingok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changefbk(lvid, fbk)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "fbk": fbk },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#fbkok"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#fbkok"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changelehrform(lvid, lf)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "lf": lf },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lf"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lf"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changelehrtyp(lvid, lt)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "lt": lt },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lt"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lt"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function changelehrmodus(lvid, lm)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "lm": lm },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lm"+lvid).css("background-color", "lightgreen");
							window.setTimeout(function(){$("#lm"+lvid).css("background-color", "");}, 500);
						}

					},
					error: function() { alert("error"); }
				});
			}

			function copylvinfo(lvid, source_id)
			{
				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:{ "lvid": lvid, "source_id": source_id },
					success: function(data)
					{
						if(data!="true")
							alert("ERROR:"+data)
						else
						{
							$("#lvinfo"+lvid).html("vorhanden");
						}
					},
					error: function() { alert("error"); }
				});
			}

			function changeboolean(lvid, name)
			{
				value=document.getElementById(name+lvid).value;

				var dataObj = {};
				dataObj["lvid"]=lvid;
				dataObj[name]=value;

				$.ajax({
					type:"POST",
					url:"lehrveranstaltung.php",
					data:dataObj,
					success: function(data)
					{
						if(data=="true")
						{
							//Image und Value aendern
							if(value=="true")
								value="false";
							else
								value="true";
							document.getElementById(name+lvid).value=value;
							document.getElementById(name+"img"+lvid).src="../../skin/images/"+value+".png";
						}
						else
							alert("ERROR:"+data)
					},
					error: function() { alert("error"); }
				});
			}
			function checkInput(ele)
			{
			    var string = ele.value;
			    string.split("_");
			    string = string.replace(/\ä/g, "ae")
				    .replace(/\ö/g, "oe")
				    .replace(/\ü/g, "ue")
				    .replace(/\ß/g, "sz")
				    .replace(/\Ä/g, "ae")
				    .replace(/\Ö/g, "oe")
				    .replace(/\Ü/g, "ue")
				    .replace(/[^a-z_0-9\s]/gi, "");
			    ele.value = string;
			}
			function conf_del()
			{
				return confirm("Diese Lehrveranstaltung wirklich löschen?");
			}
			function openDetailTag()
			{
				var details = document.getElementById("detailTag");
				if(document.getElementById("lehrveranstaltung_name").value!=""
								|| document.getElementById("select_oe_kurzbz").value!=""
								|| document.getElementById("lehrveranstaltung_id").value!=""
								|| document.getElementById("lehrveranstaltung_kurzbz").value!="")
				{
					details.open = true;
					return false;
				}
			}
		</script>

		<style>
		.tablesorter-default input.tablesorter-filter
		{
			padding: 0 4px;
		}
		table.tablesorter tbody td
		{
			padding: 0 4px;
		}
		.tablesorter-default select.tablesorter-filter
		{
			padding: 0 4px;
		}
		</style>
	</head>
	<body class="Background_main">
	';

if(isset($s[$stg_kz]->kurzbz))
	$header=$s[$stg_kz]->kurzbz;
else if($fachbereich_kurzbz!='')
	$header=$fachbereich_kurzbz;
else
{
	$oe=new organisationseinheit();
	$oe->load($oe_kurzbz);
	$header=$oe->organisationseinheittyp_kurzbz.' '.$oe->bezeichnung;
}
//$header .= ' - ';
if($semester!='-1')
	$header .= ' - '.$semester;

if($orgform_kurzbz!='-1')
	if($orgform_kurzbz=='none')
		$header .= ' - Ohne Orgform';
	else
		$header .= ' - '.$orgform_kurzbz;

echo "<H2>Lehrveranstaltung Verwaltung (".$db->convert_html_chars($header).")</H2>";
echo $messages;
echo '<table width="100%"><tr><td>';
echo $outp;

echo '</td><td valign="top">';
//Neu Button
if($write_admin || $rechte->isBerechtigt('lehre/lehrveranstaltungAnlegen',null,'suid'))
	echo '<input type="button" onclick="parent.lv_detail.location=\'lehrveranstaltung_details.php?neu=true&stg_kz='.$db->convert_html_chars($stg_kz).'&semester='.$db->convert_html_chars($semester).'\'" value="Neu"/>';
echo '</td></tr></table>';

if ($result_lv!=0)
{
	//Organisationsformen laden
	$orgform_obj = new organisationsform();
	if(!$orgform_obj->getOrgformLV())
		die('Organisationsformen konnten nicht geladen werden');

	$num_rows=$db->db_num_rows($result_lv);
	echo '<h3>&Uuml;bersicht - '.$num_rows.' LVAs</h3>
	<table class="tablesorter" id="t1">
	<thead>
	<tr>';
	echo "<th>ID</th>
		  <th>Kurzbz</th>
		  <th>Bezeichnung</th>
		  <th>Bezeichnung English</th>
		  <th>Lehrform</th>
		  <th>Lehrtyp</th>
		  <th>Lehrmodus</th>
		  <th>Stg</th>\n
		  <th>Orgform</th>
		  <th>Organisationseinheit</th>
		  <th title='Semesterstunden'>SS</th>
		  <th>ECTS</th>
		  <th>Lehre</th>
		  <th title='Verzeichnisname im Filesystem'>LehreVz</th>
		  <th>Aktiv</th>
		  <th title='Sortierreihenfolge der LV am Zeugnis'>Sort</th>
		  <th title='Anzahl der Incoming die an dieser LV teilnehmen duerfen'>Incoming</th>
		  <th>Zeugnis</th>
		  <th title='Soll diese Lehrveranstaltung bei Diplom-/Bachelorarbeit ausgewaehlt werden koennen?'>BA/DA</th>
		  <th>Koordinator</th>
		  <th class=\"lvinfo\" >LV-Info</th>
		  <th>Template</th>\n";

		  if($write_admin)
		  {
			  echo "<th>LV-Angebot</th>
			  <th>kompatible LV</th>
			  <th>Aktion</th>";
		  }

	echo "</tr></thead>";
	echo "<tbody>";
	for($i=0;$i<$num_rows;$i++)
	{
		$row=$db->db_fetch_object($result_lv);
		echo "<tr>";
		//ID
		echo "<td align='right'>";
		if($write_admin)
			echo '<a href="lehrveranstaltung_details.php?lv_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">'.$db->convert_html_chars($row->lehrveranstaltung_id).'</a>';
		else
			echo $db->convert_html_chars($row->lehrveranstaltung_id);
		echo '</td>';
		//Kurzbz
		echo '<td>',$db->convert_html_chars($row->kurzbz).'</td>';
		//Bezeichnung
		echo '<td>';
		if($write_admin)
			echo '<a href="lehrveranstaltung_details.php?lv_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">'.$db->convert_html_chars($row->bezeichnung).'</a>';
		else
			echo $db->convert_html_chars($row->bezeichnung);
		echo '</td>';

		//Bezeichnung Englisch
		echo '<td>';
		echo $db->convert_html_chars($row->bezeichnung_english);
		echo '</td>';

		//Lehrform
		if($write_admin)
		{
			echo '<td style="white-space:nowrap;">';
			echo '<SELECT style="width:80px;" id="lf'.$row->lehrveranstaltung_id.'">';
			echo '<option value="">--</option>';
			foreach ($lf as $lehrform=>$lf_kz)
			{
				if($lehrform == $row->lehrform_kurzbz)
					$selected='selected';
				else
					$selected='';
				echo '<option value="'.$db->convert_html_chars($lehrform).'" '.$selected.'>'.$db->convert_html_chars($lf_kz['lehrform_kurzbz']).' '.$db->convert_html_chars($lf_kz['bezeichnung']).'</option>';
			}
			echo '</SELECT><input type="button" value="ok" id="lf'.$row->lehrveranstaltung_id.'" onclick="changelehrform(\''.$row->lehrveranstaltung_id.'\',$(\'#lf'.$row->lehrveranstaltung_id.'\').val())">';
			echo '</td>';
		}
		else
		{
			echo '<td>';
			foreach ($lf as $lehrform=>$lf_kz)
			{
				if($lehrform == $row->lehrform_kurzbz)
					echo $db->convert_html_chars($lf_kz['lehrform_kurzbz']). ' '. $db->convert_html_chars($lf_kz['bezeichnung']);
			}
			echo '</td>';
		}

		//Lehrtyp
		if($write_admin)
		{
			echo '<td style="white-space:nowrap;">';
			echo '<SELECT id="lt'.$row->lehrveranstaltung_id.'">';
			echo '<option value="">--</option>';
			foreach ($lt as $lehrtyp=>$lt_kz)
			{
				if($lehrtyp == $row->lehrtyp_kurzbz)
					$selected='selected';
				else
					$selected='';
				echo '<option value="'.$db->convert_html_chars($lehrtyp).'" '.$selected.'>'.$db->convert_html_chars($lt_kz['bezeichnung']).'</option>';
			}
			echo '</SELECT><input type="button" value="ok" id="lf'.$row->lehrveranstaltung_id.'" onclick="changelehrtyp(\''.$row->lehrveranstaltung_id.'\',$(\'#lt'.$row->lehrveranstaltung_id.'\').val())">';
			echo '</td>';
		}
		else
		{
			echo '<td>';
			foreach ($lt as $lehrtyp=>$lt_kz)
			{
				if($lehrtyp == $row->lehrtyp_kurzbz)
					echo $db->convert_html_chars($lt_kz['bezeichnung']);
			}
			echo '</td>';
		}

		//lehrmodus
		echo '<td style="white-space:nowrap;">';
		echo '<SELECT id="lm'.$row->lehrveranstaltung_id.'">';
		echo '<option value="">--</option>';
		foreach ($lm as $lehrmodus => $lm_kz)
		{
			if($lehrmodus == $row->lehrmodus_kurzbz)
				$selected = 'selected';
			else
				$selected = '';

			echo '<option value="'.$db->convert_html_chars($lehrmodus).'" '.$selected.'>'
			.$db->convert_html_chars($lm_kz['bezeichnung_mehrsprachig']).'</option>';
		}
		echo '</SELECT><input type="button" value="ok" id="lf'.$row->lehrveranstaltung_id.'" onclick="changelehrmodus(\''.$row->lehrveranstaltung_id.'\',$(\'#lm'.$row->lehrveranstaltung_id.'\').val())">';
		echo '</td>';

		//Studiengang
		echo '<td>'.$db->convert_html_chars($s[$row->studiengang_kz]->kurzbz).'</td>';

		//Organisationsform
		echo '<td style="white-space:nowrap;">';
		echo ($row->orgform_kurzbz!=''?$db->convert_html_chars($row->orgform_kurzbz):'&nbsp;');
		echo '</td>';

		//Organisationseinheit
		echo '<td>'.($row->oe_kurzbz != ''?$db->convert_html_chars($row->organisationseinheittyp_kurzbz.' '.$row->oe_bezeichnung):'-').'</td>';

		//Semesterstunden
		echo '<td>'.($row->semesterstunden!=''?$db->convert_html_chars($row->semesterstunden):'-').'</td>';
		//ECTS
		echo '<td>'.($row->ects!=''?$db->convert_html_chars($row->ects):'-').'</td>';
		//Lehre
		echo '<td align="center">
		<div style="display: none">'.$db->convert_html_chars($row->lehre).'</div>
		<a href="Lehre" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'lehre\'); return false">
		<input type="hidden" id="lehre'.$row->lehrveranstaltung_id.'" value="'.($row->lehre=='t'?'true':'false').'">
		<img id="lehreimg'.$row->lehrveranstaltung_id.'" title="Lehre" src="../../skin/images/'.($row->lehre=='t'?'true.png':'false.png').'" height="20">
		</a></td>';
		//LehreVz
		echo '<td  style="white-space:nowrap;">';
		if($write_admin)
		{
			echo '<input type="text" id="lehrevz'.$row->lehrveranstaltung_id.'" onkeyup="checkInput(this);" value="'.$db->convert_html_chars($row->lehreverzeichnis).'" size="4" name="lehrevz_'.$db->convert_html_chars($row->lehreverzeichnis).'">
			<input type="button" id="lehrevzok'.$row->lehrveranstaltung_id.'" value="ok" onclick="changelehrevz(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'lehrevz'.$row->lehrveranstaltung_id.'\').value);">';
		}
		else
			echo $db->convert_html_chars($row->lehreverzeichnis);

		echo '</td>';
		//Aktiv
		echo '<td align="center" style="white-space:nowrap;">';
		if($write_admin)
		{
			echo '<div style="display: none">'.$db->convert_html_chars($row->aktiv).'</div>';
			echo '<a href="Aktiv" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'aktiv\'); return false">
				<input type="hidden" id="aktiv'.$row->lehrveranstaltung_id.'" value="'.($row->aktiv=='t'?'true':'false').'">
				<img id="aktivimg'.$row->lehrveranstaltung_id.'" title="Aktiv" src="../../skin/images/'.($row->aktiv=='t'?'true.png':'false.png').'" height="20">
				</a>
			';
		}
		else
			echo ($row->aktiv=='t'?'Ja':'Nein');
		echo '</td>';
		//Sort
		echo '<td style="white-space:nowrap;">';
		echo '<div style="display: none">'.$db->convert_html_chars($row->sort).'</div>';
		echo '<input type="text" id="sort'.$row->lehrveranstaltung_id.'" value="'.$db->convert_html_chars($row->sort).'" size="4">
			<input type="button" id="sortok'.$row->lehrveranstaltung_id.'"value="ok" onclick="changesort(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'sort'.$row->lehrveranstaltung_id.'\').value);">';
		echo "</td>";
		//Incoming
		echo '<td style="white-space:nowrap;">';
		echo '<div style="display: none">'.$db->convert_html_chars($row->incoming).'</div>';
		echo '<input type="text" id="incoming'.$row->lehrveranstaltung_id.'" value="'.$db->convert_html_chars($row->incoming).'" size="4">
			<input type="button" value="ok" id="incomingok'.$row->lehrveranstaltung_id.'" onclick="changeincoming(\''.$row->lehrveranstaltung_id.'\',document.getElementById(\'incoming'.$row->lehrveranstaltung_id.'\').value);">';
		echo '</td>';
		//Zeugnis
		echo '<td align="center">
				<div style="display: none">'.$db->convert_html_chars($row->zeugnis).'</div>
				<a href="Zeugnis" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'zeugnis\'); return false">
				<input type="hidden" id="zeugnis'.$row->lehrveranstaltung_id.'" value="'.($row->zeugnis=='t'?'true':'false').'">
				<img id="zeugnisimg'.$row->lehrveranstaltung_id.'" title="Zeugnis" src="../../skin/images/'.($row->zeugnis=='t'?'true.png':'false.png').'" height="20">
				</a>
			</td>';
		//Projektarbeit
		echo '<td align="center">
				<div style="display: none">'.$db->convert_html_chars($row->projektarbeit).'</div>
				<a href="Projektarbeit" onclick="changeboolean(\''.$row->lehrveranstaltung_id.'\',\'projektarbeit\'); return false">
				<input type="hidden" id="projektarbeit'.$row->lehrveranstaltung_id.'" value="'.($row->projektarbeit=='t'?'true':'false').'">
				<img id="projektarbeitimg'.$row->lehrveranstaltung_id.'" title="Projektarbeit" src="../../skin/images/'.($row->projektarbeit=='t'?'true.png':'false.png').'" height="20">
				</a>
			</td>';
		//FBK
		echo '<td style="white-space:nowrap;">';
		echo '<SELECT id="fbk'.$row->lehrveranstaltung_id.'">';
		echo '<option value="">-- Keine Auswahl --</option>';
		foreach ($fbk as $fb_uid=>$fb_k)
		{
			if($fb_uid==$row->koordinator)
				$selected='selected';
			else
				$selected='';
			echo '<option value="'.$db->convert_html_chars($fb_uid).'" '.$selected.'>'.$db->convert_html_chars($fb_k['nachname']." ".$fb_k['vorname']).'</option>';
		}
		echo '</SELECT><input type="button" value="ok" id="fbkok'.$row->lehrveranstaltung_id.'" onclick="changefbk(\''.$row->lehrveranstaltung_id.'\',$(\'#fbk'.$row->lehrveranstaltung_id.'\').val())">';
		echo '</td>';
		echo '<td class="lvinfo" nowrap>';
		//LVInfo
		$lvinfo = new lvinfo();
		if(!$lvinfo->exists($row->lehrveranstaltung_id))
		{
			echo '<span id="lvinfo'.$row->lehrveranstaltung_id.'">
					kopieren von id: <input type="text" size="3" id="source_id'.$row->lehrveranstaltung_id.'" value="" />
					<input type="button" value="ok" onclick="copylvinfo(\''.$row->lehrveranstaltung_id.'\',$(\'#source_id'.$row->lehrveranstaltung_id.'\').val())">
				</span>';
		}
		else
			echo 'vorhanden';
		echo '</td>';
		//Template
		echo "<td align='right'>";
		echo $db->convert_html_chars($row->lehrveranstaltung_template_id);
		echo '</td>';
		if($write_admin)
		{
			echo '
				<td nowrap>
					<a href="lehrveranstaltung_lvangebot.php?lehrveranstaltung_id='.$db->convert_html_chars($row->lehrveranstaltung_id).'" target="lv_detail">LV-Angebot</a>
				</td>';

			echo '<td><a href="lehrveranstaltung_kompatibel.php?lehrveranstaltung_id='.$row->lehrveranstaltung_id.'&type=edit" target="lv_detail">Kompatible LV</a></td>';
			echo '<td><a href="'.$_SERVER['PHP_SELF'].'?delete_lvid='.$row->lehrveranstaltung_id.'&stg_kz='.$stg_kz.'&semester='.$semester.'&fachbereich_kurzbz='.$oe_fachbereich.'&isaktiv='.$isaktiv.'&oe_kurzbz='.$oe_kurzbz.'&orgform='.$orgform_kurzbz.'" onclick="return conf_del()">löschen</a></td>';
			echo "</tr>\n";
		}
	}
}
else
	echo 'Kein Eintrag gefunden!';

echo '</tbody>
	</table>';

?>
	</body>
</html>
