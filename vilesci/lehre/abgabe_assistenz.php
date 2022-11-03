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
 * Authors: Christian Paminger 		< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 			< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/*******************************************************************************************************
 *				               abgabe_assistenz
 * 		abgabe_assistenz ist die Assistenzoberfläche des Abgabesystems
 * 			            für Diplom- und Bachelorarbeiten
 *******************************************************************************************************/
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/datum.class.php');
require_once('../../include/person.class.php');
require_once('../../include/benutzer.class.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/mitarbeiter.class.php');
require_once('../../include/variable.class.php');
require_once('../../include/phrasen.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');

if (!$getuid = get_uid())
	die('Keine UID gefunden !  <a href="javascript:history.back()">Zur&uuml;ck</a>');

$p=new phrasen(DEFAULT_LANGUAGE);
$htmlstr = "";
$erstbegutachter='';
$zweitbegutachter='';
$fachbereich_kurzbz='';
//$p2id='';

$stg_kz=(isset($_REQUEST['stg_kz'])?$_REQUEST['stg_kz']:'');
if(!is_numeric($stg_kz) && $stg_kz!='')
	die('Bitte vor dem Aufruf Studiengang ausw&auml;hlen!');
$stgbez='';

$trenner='';
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($getuid);

if(!$rechte->isBerechtigt('admin', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', $stg_kz, 'suid') && !$rechte->isBerechtigt('assistenz', null, 'suid', $fachbereich_kurzbz) )
	die('Sie haben keine Berechtigung f&uuml;r diesen Studiengang  <a href="javascript:history.back()">Zur&uuml;ck</a>');

function showFarbcodes()
{
 	$farbcodes = '';

	$farbcodes.= "<table>";
	$farbcodes.="<tr><td style=\"background-color:#FFFFFF; width:35px;\"></td><td style=\"padding-left:5px;\">Termin noch mehr als 12 Tage entfernt</tr>";
	$farbcodes.="<tr><td style=\"background-color:#FFFF00;\"></td><td style=\"padding-left:5px;\">Termin innerhalb der nächsten 12 Tage</tr>";
	$farbcodes.="<tr><td style=\"background-color:#FF0000;\"></td><td style=\"padding-left:5px;\">Termin überschritten / keine Abgabe</tr>";
	$farbcodes.="<tr><td style=\"background-color:#00FF00;\"></td><td style=\"padding-left:5px;\">abgegeben</tr>";
	$farbcodes.="<tr><td style=\"background-color:#EA7B7B;\"></td><td style=\"padding-left:5px;\">Abgabe nach Termin</tr>";
	$farbcodes.="</table>";
	return $farbcodes;
}

$trenner = new variable();
$trenner->loadVariables($getuid);

$sql_query = "SELECT *,
			(SELECT orgform_kurzbz
			FROM tbl_prestudentstatus
			WHERE prestudent_id=(Select prestudent_id from tbl_student where student_uid=xy.uid limit 1)
			ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1
			) as organisationsform
			FROM (SELECT DISTINCT ON(tbl_projektarbeit.projektarbeit_id) public.tbl_studiengang.bezeichnung as stgbez,tbl_projekttyp.bezeichnung AS prjbez,* FROM lehre.tbl_projektarbeit
			LEFT JOIN public.tbl_benutzer on(uid=student_uid)
			LEFT JOIN public.tbl_person on(tbl_benutzer.person_id=tbl_person.person_id)
			LEFT JOIN lehre.tbl_lehreinheit using(lehreinheit_id)
			LEFT JOIN lehre.tbl_lehrveranstaltung using(lehrveranstaltung_id)
			LEFT JOIN public.tbl_studiengang using(studiengang_kz)
			LEFT JOIN lehre.tbl_projekttyp USING (projekttyp_kurzbz)
			WHERE (projekttyp_kurzbz='Bachelor' OR projekttyp_kurzbz='Diplom')
			AND public.tbl_benutzer.aktiv
			AND lehre.tbl_projektarbeit.note IS NULL
			AND public.tbl_studiengang.studiengang_kz=".$db->db_add_param($stg_kz)."
			ORDER BY tbl_projektarbeit.projektarbeit_id desc) as xy
		ORDER BY nachname";

if(!$erg=$db->db_query($sql_query))
{
	$errormsg='Fehler beim Laden der Betreuungen';
}
else
{
	//$htmlstr .= "<form name='formular'><input type='hidden' name='check' value=''></form>";
	$htmlstr .= "<form name='multitermin' action='abgabe_assistenz_multitermin.php' target='al_detail' method='POST'>";
	//$htmlstr .= "<table id='t1' class='liste table-autosort:5 table-stripeclass:alternate table-autostripe'>\n";
	$htmlstr .= "<table id='t1' class='tablesorter'>\n";
	$htmlstr .= "<thead><tr class='liste'>\n";
	$htmlstr .= "<th></th><th class='table-sortable:default'>UID</th>
				<th>Email</th>
				<th class='table-sortable:default'>Sem.</th>
				<th class='table-sortable:default'>Vorname</th>
				<th class='table-sortable:alphanumeric'>Nachname</th>
				<th class='table-sortable:default'>Orgform</th>";
	$htmlstr .= "<th class='table-sortable:default'>Typ</th>
				<th>Titel</th>
				<th class='table-sortable:alphanumeric'>1.Begutachter(in)</th>
				<th>1</th>
				<th>2</th>
				<th class='table-sortable:alphanumeric'>2.Begutachter(in)</th>";
	$htmlstr .= "</tr></thead><tbody>\n";
	$i = 0;
	$erstbegutachter='';
	$zweitbegutachter='';
	$muid='';
	$muid2='';
	$mituid='';
	$p2id='';
	while($row=$db->db_fetch_object($erg))
	{
		$erstbegutachter='';
		$zweitbegutachter='';
		$muid='';
		$muid2='';
		$mituid='';
		$p2id='';
		$stgbez=$row->stgbez;
		//Betreuer suchen
		$qry_betr="SELECT trim(COALESCE(nachname, '') || ', ' || COALESCE(titelpre, '') || ' ' || COALESCE(vorname, '') || ' ' || COALESCE(titelpost, '')) AS first,
						'' AS second,
						PUBLIC.tbl_mitarbeiter.mitarbeiter_uid,
						'' AS kontakt,
						PUBLIC.tbl_person.person_id
					FROM PUBLIC.tbl_person
					JOIN lehre.tbl_projektbetreuer ON (lehre.tbl_projektbetreuer.person_id = PUBLIC.tbl_person.person_id)
					LEFT JOIN PUBLIC.tbl_benutzer ON (PUBLIC.tbl_benutzer.person_id = PUBLIC.tbl_person.person_id)
					LEFT JOIN PUBLIC.tbl_mitarbeiter ON (PUBLIC.tbl_benutzer.uid = PUBLIC.tbl_mitarbeiter.mitarbeiter_uid)
					WHERE projektarbeit_id = ".$db->db_add_param($row->projektarbeit_id, FHC_INTEGER)."
						AND (
							tbl_projektbetreuer.betreuerart_kurzbz = 'Erstbegutachter'
							OR tbl_projektbetreuer.betreuerart_kurzbz = 'Betreuer'
							OR tbl_projektbetreuer.betreuerart_kurzbz = 'Begutachter'
							)

					UNION

					SELECT '' AS first,
						trim(COALESCE(nachname, '') || ', ' || COALESCE(titelpre, '') || ' ' || COALESCE(vorname, '') || ' ' || COALESCE(titelpost, '')) AS second,
						PUBLIC.tbl_mitarbeiter.mitarbeiter_uid,
						(
							SELECT kontakt
							FROM PUBLIC.tbl_kontakt
							WHERE person_id = tbl_person.person_id
								AND kontakttyp = 'email'
								AND zustellung LIMIT 1
							) AS kontakt,
						PUBLIC.tbl_person.person_id
					FROM PUBLIC.tbl_person
					JOIN lehre.tbl_projektbetreuer ON (lehre.tbl_projektbetreuer.person_id = PUBLIC.tbl_person.person_id)
					LEFT JOIN PUBLIC.tbl_benutzer ON (PUBLIC.tbl_benutzer.person_id = PUBLIC.tbl_person.person_id)
					LEFT JOIN PUBLIC.tbl_mitarbeiter ON (PUBLIC.tbl_benutzer.uid = PUBLIC.tbl_mitarbeiter.mitarbeiter_uid)
					WHERE projektarbeit_id = ".$db->db_add_param($row->projektarbeit_id, FHC_INTEGER)."
						AND tbl_projektbetreuer.betreuerart_kurzbz = 'Zweitbegutachter'
		";

		if(!$betr=$db->db_query($qry_betr))
		{
			$errormsg='Fehler beim Laden der Betreuer';
		}
		else
		{
			while($row_betr=$db->db_fetch_object($betr))
			{
				if($row_betr->first!='' && $row_betr->mitarbeiter_uid!=NULL)
				{
					if(trim($erstbegutachter==''))
					{
						$erstbegutachter=$row_betr->first;
						$muid=$row_betr->mitarbeiter_uid."@".DOMAIN;
						$mituid=$row_betr->mitarbeiter_uid;
					}
					else
					{
						$erstbegutachter.=$trenner->variable->emailadressentrennzeichen." ".$row_betr->first;
						$muid.=$trenner->variable->emailadressentrennzeichen." ".$row_betr->mitarbeiter_uid."@".DOMAIN;
					}
				}
				if($row_betr->second!='')
				{
					$zweitbegutachter=$row_betr->second;
					$p2id=$row_betr->person_id;
					if($row_betr->mitarbeiter_uid!='' && $row_betr->mitarbeiter_uid!=NULL)
					{
						$muid2=$row_betr->mitarbeiter_uid."@".DOMAIN;
					}
					else
					{
						if($row_betr->kontakt!='' && $row_betr->kontakt!=NULL)
						{
							$muid2=$row_betr->kontakt;
						}
					}
				}

			}
		}
		$htmlstr .= "   <tr >\n";//class='liste".($i%2)."'
		$htmlstr .= "		<td><input type='checkbox' id='mc_".$row->projektarbeit_id."' name='mc_".$row->projektarbeit_id."' ></td>";
		//Anzeige
		$qry_end="SELECT * FROM campus.tbl_paabgabe WHERE paabgabetyp_kurzbz='end' AND projektarbeit_id=".$db->db_add_param($row->projektarbeit_id, FHC_INTEGER)." ORDER BY datum DESC";
		if(!$result_end=$db->db_query($qry_end))
		{
			$htmlstr .= "       <td><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&erst=".$mituid."&p2id=".$p2id."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
		}
		else
		{
			if($db->db_num_rows($result_end)>0)
			{
				$bgcol='';
				if($row_end=$db->db_fetch_object($result_end))
				{
					if($row_end->abgabedatum==NULL)
					{
						if ($row_end->datum<date('Y-m-d'))
						{
							$bgcol='#FF0000';
						}
						elseif (($row_end->datum>=date('Y-m-d')) && ($row_end->datum<date('Y-m-d',mktime(0, 0, 0, date("m")  , date("d")+11, date("Y")))))
						{
							$bgcol='#FFFF00';
						}
						else
						{
							$bgcol='#FFFFFF';
						}
					}
					else
					{
						if($row_end->abgabedatum>$row_end->datum)
						{
							$bgcol='#EA7B7B';
						}
						else
						{
							$bgcol='#00FF00';
						}
					}
					if($bgcol!='')
					{
						$htmlstr .= "       <td style='background-color:".$bgcol."'><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&erst=".$mituid."&p2id=".$p2id."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
					}
					else
					{
						$htmlstr .= "       <td><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&erst=".$mituid."&p2id=".$p2id."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
					}
				}
				else
				{
					$htmlstr .= "       <td><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&erst=".$mituid."&p2id=".$p2id."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
				}
			}
			else
			{
				$htmlstr .= "       <td><a href='abgabe_assistenz_details.php?uid=".$row->uid."&projektarbeit_id=".$row->projektarbeit_id."&erst=".$mituid."&p2id=".$p2id."' target='al_detail' title='Details anzeigen'>".$row->uid."</a></td>\n";
			}
		}
		$htmlstr .= "	    <td align= center><input type='hidden' name='st_".$row->projektarbeit_id."' value='$row->uid@".DOMAIN."'><a href='mailto:$row->uid@".DOMAIN."?subject=Betreuung ".$row->prjbez." bei Studiengang $row->stgbez'><img src='../../skin/images/email.png' alt='email' title='Email an StudentIn'></a></td>";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->studiensemester_kurzbz)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->vorname)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->nachname)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->organisationsform)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->prjbez)."</td>\n";
		$htmlstr .= "       <td>".$db->convert_html_chars($row->titel)."</td>\n";

		//$htmlstr.="<a href='mailto:$row->uid@".DOMAIN."?subject=".$row->projekttyp_kurzbz."arbeitsbetreuung%20von%20".$row->vorname."%20".$row->nachname."'>
		//<img src='../../../skin/images/email.png' alt='email' title='Email an Betreuer schreiben'></a>";

		if($muid != NULL && $muid !='')
		{
			$htmlstr .= "       <td><input type='hidden' name='b1_".$row->projektarbeit_id."' value='$muid'><a href='mailto:$muid?subject=Betreuung%20".$row->prjbez."%20von%20".$row->vorname."%20".$row->nachname." bei Studiengang $row->stgbez' title='Email an Erstbegutachter'>".$erstbegutachter."</a></td>\n";
		}
		else
		{
			$htmlstr .= "       <td>".$erstbegutachter."</td>\n";
		}
		$htmlstr .= "		<td align='center'><input type='checkbox' id='m1_".$row->projektarbeit_id."' name='m1_".$row->projektarbeit_id."'></td>";
		$htmlstr .= "		<td align='center'><input type='checkbox' id='m2_".$row->projektarbeit_id."' name='m2_".$row->projektarbeit_id."'></td>";
		if($muid2 != NULL && $muid2 !='')
		{
			$htmlstr .= "       <td><input type='hidden' name='b2_".$row->projektarbeit_id."' value='$muid2'><a href='mailto:".$muid2."?subject=Betreuung%20".$row->prjbez."%20von%20".$row->vorname."%20".$row->nachname." bei Studiengang $row->stgbez' title='Email an Zweitbegutachter'>".$zweitbegutachter."</a></td>\n";
		}
		else
		{
			$htmlstr .= "       <td>".$zweitbegutachter."</td>\n";
		}
		$htmlstr .= "   </tr>\n";
		$i++;
	}
	$htmlstr .= "</tbody></table>\n";
	$htmlstr .= '<input type="hidden" name="stg_kz" value="'.$db->convert_html_chars($stg_kz).'">';
	$htmlstr .= '<input type="hidden" name="p2id" value="'.$db->convert_html_chars($p2id).'">';
	$htmlstr .= "<table width='100%'><tr><td>";
	$htmlstr .= "<table><tr><td><input type='checkbox' name='alle' id='alle' onclick='markiere()'> alle markieren  </td></tr><tr><td>&nbsp;</td></tr><tr>\n";
	$htmlstr .= "<td rowspan=2><input type='submit' name='multi' value='Terminserie anlegen' title='Termin f&uuml;r mehrere Personen anlegen.'></td>";
	$htmlstr .= "<td rowspan=2><input type='button' name='stmail' value='E-Mail Studierende' title='E-Mail an mehrere Studierende schicken' onclick='stserienmail(\"".$trenner->variable->emailadressentrennzeichen."\",\"".$stgbez."\")'></td>";
	$htmlstr .= "<td rowspan=2><input type='button' name='btmail' value='E-Mail Begutachter(innen)' title='E-Mail an mehrere BegutachterInnen schicken' onclick='btserienmail(\"".$trenner->variable->emailadressentrennzeichen."\",\"".$stgbez."\")'></td></tr></table>\n";
	$htmlstr .="</td><td align='right'>".showFarbcodes().'</td></tr></table>';
	$htmlstr .= "</form>";


}

?>
<html>
<head>
<title>Abgabesystem_Assistenzsicht</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
<link rel="stylesheet" type="text/css" href="../../skin/jquery-ui-1.9.2.custom.min.css">
<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>
<script language="JavaScript" type="text/javascript">

$(document).ready(function()
	{
	    $("#t1").tablesorter(
		{
			sortList: [[5,0]],
			widgets: ['zebra']
		});
	}
);

function confdel()
{
	if(confirm("Diesen Datensatz wirklick loeschen?"))
		return true;
	return false;
}
function markiere()
{
	var items=document.getElementsByTagName('input');
	var alle=document.getElementById('alle');
	jQuery.each(items, function(index, item) {
		if(item.type == 'checkbox')
		{
			item.checked = alle.checked;
		}
	});
}
function stserienmail(trenner, stgbez)
{
	//E-Mail an mehrere ausgewaehlte Studenten
	var studenten=document.getElementsByTagName('input');
	var adressen='';
	jQuery.each(studenten, function(index, students) {
		if(students.type=='hidden' && students.name.substr(0,3)=="st_")
		{
			var id = "mc_"+students.name.substr(3);
			if(document.getElementById(id).checked)
			{
				if(adressen=='')
				{
					adressen=students.value;
				}
				else
				{
					if(adressen.search(students.value)==-1)
					{
						adressen=adressen+trenner+students.value;
					}
				}
			}
		}
	});
	window.location.href="mailto:"+adressen+"?subject=Betreuungen Bachelorarbeit bzw. Master Thesis bei Studiengang "+stgbez;
}
function btserienmail(trenner, stgbez)
{
	//Mail an mehrere ausgewählte Betreuer
	var lektoren=document.getElementsByTagName('input');
	var adressen='';

	jQuery.each(lektoren, function(index, personen) {
		if(personen.type=='hidden' && personen.name.substr(0,3)=="b1_" && personen.value!='')
		{
			var id = "mc_"+personen.name.substr(3);
			if(document.getElementById(id).checked)
			{
				temp=personen.value.split(trenner);
				for(i=0;i<temp.length;i++)
				{
					if(adressen=='')
					{
						adressen=temp[i];
					}
					else
					{
						if(adressen.search(temp[i])==-1)
						{
							adressen=adressen+trenner+temp[i];
						}
					}
				}
			}
		}
		if(personen.type=='hidden' && personen.name.substr(0,3)=="b2_" && personen.value!='')
		{
			var id = "mc_"+personen.name.substr(3);
			if(document.getElementById(id).checked)
			{
				temp=personen.value.split(trenner);
				for(i=0;i<temp.length;i++)
				{
					if(adressen=='')
					{
						adressen=temp[i];
					}
					else
					{
						if(adressen.search(temp[i])==-1)
						{
							adressen=adressen+trenner+temp[i];
						}
					}
				}
			}
		}
	});
	window.location.href="mailto:"+adressen+"?subject=Betreuungen Bachelorarbeit bzw. Master Thesis bei Studiengang "+stgbez;
}
</script>
</head>

<body class="background_main">
<?php
echo "<h2><div style='float:left'>Bachelor-/Masterarbeitsbetreuungen (Studiengang $stg_kz, $stgbez)</div><div style='text-align: right;'><a href='".$p->t('dms_link/abgabetoolAssistenzHandbuch')."' target='_blank'><img src='../../skin/images/information.png' alt='Anleitung' title='Anleitung Abgabetool' border=0>&nbsp;Handbuch</a></div></h2>";

echo $htmlstr;
?>

</body>
</html>
