<?php
/* Copyright (C) 2010 Technikum-Wien
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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
/*******************************************************************************************************
 *				projektabgabe
 * 		projektabgabe ermöglicht den Download aller Abgaben eines Stg. 
 * 			fuer Diplom- und Bachelorarbeiten
 *******************************************************************************************************/

require_once('../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
		
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/mail.class.php');
	if (!$db = new basis_db())
		$db=false;

$stg_kz=(isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:0);
$abgabetyp=(isset($_REQUEST['abgabetyp'])?$_REQUEST['abgabetyp']:'');
if(!is_numeric($stg_kz) && $stg_kz!='')
	exit();
$htmlstr='';
$datum_obj = new datum();
$user = get_uid();
$rechte =  new benutzerberechtigung();
$rechte->getBerechtigungen($user);	
$berechtigung_kurzbz = 'lehre/abgabetool:download';
if(isset($_GET['id']) && isset($_GET['uid']))
{
	if($rechte->isBerechtigt($berechtigung_kurzbz))
	{
		if(!is_numeric($_GET['id']) || $_GET['id']=='')
			die('Fehler bei Parameteruebergabe');
		
		$file = $_GET['id'].'_'.$_GET['uid'].'.pdf';
		$filename = PAABGABE_PATH.$file;
		header('Content-Type: application/octet-stream');
		header('Content-disposition: attachment; filename="'.$file.'"');
		readfile($filename);
	}
	else 
	{
		die("Sie haben hierzu keine Berechtigung!");
	}
	exit;
}
$s = new studiengang();
$s->loadArray($rechte->getStgKz($berechtigung_kurzbz),'studiengang_kz');

echo'<form method="GET" action="'.$_SERVER['PHP_SELF'].'">';

echo " Studiengang: <SELECT name='stg_kz'>";
foreach ($s->result as $stg)
{
	if($stg->studiengang_kz==$stg_kz)
		$selected='selected';
	else 	
		$selected='';
	echo '<option value="'.$stg->studiengang_kz.'" '.$selected.'>'.$stg->kuerzel.'</option>';
}
echo "</SELECT>";
echo "   Abgabetyp: <SELECT name='abgabetyp'>";
$qry_atyp="SELECT * FROM campus.tbl_paabgabetyp";
if($result_atyp=$db->db_query($qry_atyp))
{
	while($row_atyp=$db->db_fetch_object($result_atyp))
	{
		if($row_atyp->paabgabetyp_kurzbz==$abgabetyp)
			$selected='selected';
		else 	
			$selected='';
		echo '<option value="'.$row_atyp->paabgabetyp_kurzbz.'" '.$selected.'>'.$row_atyp->bezeichnung.'</option>';
	}
}
echo "</SELECT>";

echo "&nbsp;<INPUT type='submit' value='OK'></FORM>";
if($stg_kz!='')
{
	$s=new studiengang();
	if(!$s->load($stg_kz))
	{
		die("Studiengang konnte nicht geladen werden!");
	}
	if($rechte->isBerechtigt($berechtigung_kurzbz, $s->oe_kurzbz))
	{
		$qry="SELECT * 
			FROM (SELECT public.tbl_studiengang.bezeichnung as stgbez, campus.tbl_paabgabe.datum as termin,* FROM lehre.tbl_projektarbeit 
			JOIN campus.tbl_paabgabe USING(projektarbeit_id)
			LEFT JOIN public.tbl_benutzer ON(uid=student_uid) 
			LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
			LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom') 
			AND public.tbl_benutzer.aktiv 
			AND lehre.tbl_projektarbeit.note IS NULL 
			AND public.tbl_studiengang.studiengang_kz='$stg_kz'";
		$qry.=" AND campus.tbl_paabgabe.paabgabetyp_kurzbz='$abgabetyp'";
		$qry.=" ORDER BY tbl_projektarbeit.projektarbeit_id desc) as xy 
		ORDER BY nachname";	
		//echo $qry."<br>";
		if(!$erg=$db->db_query($qry))
		{
			die('Fehler beim Laden der Betreuungen!');
		}
		else
		{
			$htmlstr .= "<table id='t1' class='liste table-autosort:2 table-stripeclass:alternate table-autostripe' border=1>\n";
			$htmlstr .= "<thead><tr class='liste'>\n";
			$htmlstr .= "<th>download</th><th>Termin</th><th>Abgabetyp</th><th class='table-sortable:default'>UID</th>
						<th class='table-sortable:default'>Vorname</th>
						<th class='table-sortable:alphanumeric'>Nachname</th>";
			$htmlstr .= "<th class='table-sortable:default'>Typ</th>
						<th>Titel</th>";
			$htmlstr .= "</tr></thead><tbody>\n";
			$i = 0;
			while($row=$db->db_fetch_object($erg))
			{
				$htmlstr .= "<tr>";
				if(file_exists(PAABGABE_PATH.$row->paabgabe_id.'_'.$row->uid.'.pdf'))
				{
					$htmlstr .= "		<td align=center><a href='".$_SERVER['PHP_SELF']."?id=".$row->paabgabe_id."&uid=$row->uid' target='_blank'><img src='../../../skin/images/pdf.ico' alt='PDF' title='abgegebene Datei' border=0></a></td>";
				}
				else 
				{
					$htmlstr .= "		<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
				}
				//$htmlstr .= "<td>link</td>";
				$htmlstr .= "<td>".$datum_obj->formatDatum($row->termin,'d.m.Y')."</td>";
				$htmlstr .= "<td>$abgabetyp</td>";
				$htmlstr .= "<td>$row->uid</td>";
				$htmlstr .= "<td>".$row->vorname."</td>\n";
				$htmlstr .= "<td>".$row->nachname."</td>\n";
				$htmlstr .= "<td>".$row->projekttyp_kurzbz."</td>\n";
				$htmlstr .= "<td>".$row->titel."</td>\n";
			}
			
		}
	} 
	else 
	{
		die("Keine Zugriffsberechtigung!");
	}
}
?>
<html>
<head>
<title>Projektabgabe</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../include/js/tablesort/table.css" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
</head>
<body class="background_main">
<?php 
	echo $htmlstr;
?>

</body>
</html>