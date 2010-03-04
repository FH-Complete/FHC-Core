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
$aktion='';
if(isset($_REQUEST['aktion']))
{
	$aktion=$_REQUEST['aktion'];
}
$zipfile='';
$stg_kz=(isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'');
if(!is_numeric($stg_kz) && $stg_kz!='')
	exit();

$abgabetyp=(isset($_REQUEST['abgabetyp'])?$_REQUEST['abgabetyp']:'');
$termin=(isset($_REQUEST['termin'])?$_REQUEST['termin']:'');
	
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
	exit();
}

if($zipfile=='')
{
	?>
	<html>
	<head>
	<title>Projektabgabe</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
	<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery-ui.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery.autocomplete.js" type="text/javascript"></script>
	<script src="../../../include/js/jquery.autocomplete.min.js" type="text/javascript"></script>	

	</head>
	<body class="background_main">
	<?php 
}

if($aktion!='zip')
{
	$s = new studiengang();
	$s->loadArray($rechte->getStgKz($berechtigung_kurzbz),'typ,kurzbz');
		
	echo'<form method="GET" action="'.$_SERVER['PHP_SELF'].'" name="abgabeFrm">';

	echo " Studiengang: <SELECT onchange='set_termin();' id='stg_kz' name='stg_kz'>";
	echo '<option value="" '. (!isset($_REQUEST['stg_kz']) || empty($stg_kz)?' selected ':'') .'>-</option>';
	foreach ($s->result as $stg)
	{
		echo '<option value="'.$stg->studiengang_kz.'" '.(isset($_REQUEST['stg_kz']) && $stg->studiengang_kz==$stg_kz?' selected ':'').'>'.$stg->kuerzel.'</option>';
	}
	echo "</SELECT><input type=hidden name=aktion value=\"\">";


	echo "   Abgabetyp: <SELECT onchange='set_termin();' id='abgabetyp' name='abgabetyp'>";
	$qry_atyp="SELECT * FROM campus.tbl_paabgabetyp";
	echo '<option value="" '.(!isset($_REQUEST['abgabetyp']) || empty($abgabetyp)?' selected ':'').'>-</option>';
	if($result_atyp=$db->db_query($qry_atyp))
	{
		while($row_atyp=$db->db_fetch_object($result_atyp))
		{
			echo '<option value="'.$row_atyp->paabgabetyp_kurzbz.'" '.($row_atyp->paabgabetyp_kurzbz==$abgabetyp?' selected ':'').'>'.$row_atyp->bezeichnung.'</option>';
		}
	}
	echo "</SELECT>";
	
	
	$qry_termin="	SELECT distinct campus.tbl_paabgabe.datum as termin , to_char(campus.tbl_paabgabe.datum, 'DD-MM-YYYY') as termin_anzeige 
					FROM lehre.tbl_projektarbeit 
							JOIN campus.tbl_paabgabe USING(projektarbeit_id)
							LEFT JOIN public.tbl_benutzer ON(uid=student_uid) 
							LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
							LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
							LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
							LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
							WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom') 
							AND public.tbl_benutzer.aktiv 
							AND lehre.tbl_projektarbeit.note IS NULL 
						";
			if ($stg_kz!='')
				$qry_termin.=" AND public.tbl_studiengang.studiengang_kz='$stg_kz'";
			if ($abgabetyp!='')
				$qry_termin.=" AND campus.tbl_paabgabe.paabgabetyp_kurzbz='$abgabetyp'";
			$qry_termin.=" ORDER BY termin desc";	

	
	echo '&nbsp;Termin&nbsp;<select name="termin" id="termin">
				<option value=""  '. (!isset($_REQUEST['termin']) || empty($termin)?' selected ':'') .'>-</option> ';
	if($result_termin=$db->db_query($qry_termin))
	{
		while($row_termin=$db->db_fetch_object($result_termin))
		{
			echo '<option value="'.$row_termin->termin.'" '.($row_termin->termin==$termin?' selected ':'').'>'.$row_termin->termin_anzeige.'</option>';
		}
	}
	echo	'</select>';
	
	?>
					<script type="text/javascript">
						function set_termin()
						{
							$('#termin').children().remove().end();
							$.ajax
							(
								{
									type: "POST",
									url: 'projektabgabe_autocomplete.php',
									dataType: 'json',
									data: "work=work_termin_select" + "&stg_kz=" + $('#stg_kz').val()  + "&abgabetyp=" +  $('#abgabetyp').val(),
									success: function(json)
									{
										var output = '';
										for (p in json) 
										{
											output += '<option value=\"' + json[p].oTermin + '\">' + json[p].oTerminAnzeige + '</option>\n';
										}
										$('#termin').html(output);
										$('#termin').result(function(event, data, formatted) {}).focus();
									}
								}
							);
						}
					</script>	
<?php
	echo "&nbsp;<INPUT type='submit' name='ok' value='OK'>&nbsp;<INPUT type='button' value='ZIP' onclick=\"f=document.abgabeFrm;f.aktion.value='zip';f.submit();\"></FORM>";
	}
	
##if($stg_kz!='' || $abgabetyp!='' || $termin!='')
if(isset($_REQUEST['ok']))
{
	$s=new studiengang();
	if($stg_kz!='' && !$s->load($stg_kz))
	{
		die("Studiengang konnte nicht geladen werden!");
	}
	
	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt($berechtigung_kurzbz, $s->oe_kurzbz))
	{
		$qry="";
##		$qry.="SELECT * 	FROM (";
		$qry.="	SELECT public.tbl_studiengang.bezeichnung as stgbez, campus.tbl_paabgabe.datum as termin,* FROM lehre.tbl_projektarbeit 
			JOIN campus.tbl_paabgabe USING(projektarbeit_id)
			LEFT JOIN public.tbl_benutzer ON(uid=student_uid) 
			LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id) 
			LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) 
			LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom') 
			AND public.tbl_benutzer.aktiv 
			AND lehre.tbl_projektarbeit.note IS NULL 
			";
			if ($stg_kz!='')
				$qry.=" AND public.tbl_studiengang.studiengang_kz='$stg_kz'";
			if ($abgabetyp!='')
				$qry.=" AND campus.tbl_paabgabe.paabgabetyp_kurzbz='$abgabetyp'";
			if ($termin!='')
				$qry.=" AND campus.tbl_paabgabe.datum='$termin'";
		$qry.=" ORDER BY nachname  ";
##		$qry.=" ORDER BY tbl_projektarbeit.projektarbeit_id desc) as xy ";		
##		$qry.=" ORDER BY nachname";	
		if($stg_kz=='' && $abgabetyp=='' && $termin=='')
		{
			$qry.=" limit 100 ";				
		}
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
				$htmlstr .= "<td>".$abgabetyp."&nbsp;</td>";
				$htmlstr .= "<td>".$row->uid."&nbsp;</td>";
				$htmlstr .= "<td>".$row->vorname."&nbsp;</td>\n";
				$htmlstr .= "<td>".$row->nachname."&nbsp;</td>\n";
				$htmlstr .= "<td>".$row->projekttyp_kurzbz."&nbsp;</td>\n";
				$htmlstr .= "<td>".$row->titel."&nbsp;</td>\n";
				if($aktion=='zip')
				{
					if($zipfile=='')
					{
						$zipfile = $row->paabgabe_id.'_'.$row->uid.'.pdf';
					}
					else 
					{
						$zipfile .= " ".$row->paabgabe_id.'_'.$row->uid.'.pdf';
					}					
 				}
			}
		}
	} 
	else 
	{
		die("Keine Zugriffsberechtigung!");
	}
}

if($zipfile=='')
{
	echo $htmlstr;
	echo "</body>
	</html>";
}
else
{
	//Zip File erstellen
	chdir(PAABGABE_PATH);
	$zipausgabe=tempnam("/tmp", "PAA").".zip";
	exec("zip ".$zipausgabe." ".$zipfile);
	//echo $zipausgabe;
	//echo "<br>zip -r ".$zipausgabe." ".$zipfile;
	if(file_exists($zipausgabe))
	{
		header('Content-Type: application/octet-stream');
		header('Content-disposition: attachment; filename="Abgabe_'.$s->kuerzel.'.zip"');
		echo file_get_contents($zipausgabe);
		unlink($zipausgabe);	
	}
	else 
	{
		echo "<br>Fehler bei der Dateierstellung!";
	}
}
?>