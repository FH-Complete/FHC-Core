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
 * Authors: Manfred Kindl <manfred.kindl@technikum-wien.at>.
 */
 

require_once('../../config/vilesci.config.inc.php');
require_once('../../config/global.config.inc.php');
require_once '../../include/person.class.php'; 
require_once '../../include/functions.inc.php';
require_once '../../include/phrasen.class.php';
require_once '../../include/preincoming.class.php';
require_once '../../include/studiensemester.class.php';
require_once '../../include/studiengang.class.php';
require_once '../../include/lehrveranstaltung.class.php';
require_once '../../include/studiengang.class.php';
require_once '../../include/benutzerberechtigung.class.php';

$user = get_uid();

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lang']))
	setSprache($_GET['lang']);
	
$sprache = getSprache(); 
$p=new phrasen($sprache); 	
	
$method = htmlspecialchars($_GET['method']);

$db = new basis_db();

$stsem = new studiensemester();
$stsem->getNextStudiensemester();

$stg = new studiengang();
$stg->getAll();

?>
<html>
	<head>
	<title>Lehrveranstaltungs-Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta http-equiv="expires" content="Sat, 01 Dec 2001 00:00:00 GMT">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
	<script type="text/javascript" src="../../include/js/jquery.js"></script>
	<script type="text/javascript">
			$(document).ready(function()
			{
				$("#t1").tablesorter(
						{
							sortList: [[0,0],[2,0],[3,0],[4,0]],
							widgets: ["zebra"]
						});
			});
			function conf(val1)
			{
				return confirm("Incomingplätze von '"+val1+"' auf 0 setzen?");
			}
		</script>
	
	</head>
	<body>
<?php 

if(!$rechte->isBerechtigt('inout/incoming', null, 'suid'))
	die($rechte->errormsg);

if($method=="lehrveranstaltungen")
{

	if(isset($_GET['mode']) && $_GET['mode'] == "setZero")
	{
		$id= $_GET['id']; 
		$lehrveranstaltung = new lehrveranstaltung();
		$lehrveranstaltung->load($id);
		
		$lehrveranstaltung->incoming = 0;
		
		if($lehrveranstaltung->save())
			echo $p->t('global/erfolgreichgespeichert');  
		else
			echo $p->t('global/fehleraufgetreten');  
	}
	
	// Übersicht aller LVs
	echo '<h2>Lehrveranstaltungs-Verwaltung</h2>';
	echo '
	<form name="filterSemester">
	<table border="0">
		<tr>
			<td>'.$p->t('incoming/filter').':
				<SELECT name="filterLv" onchange=selectChange()>					
				<option value="allSemester">'.$p->t('incoming/alleSemester').'</option>';
				
				// Vorauswahl der Übergebenen Filter
				$WSemesterSelected = '';
				$SSemesterSelected = '';
				
				if(isset($_GET['filter']))
					if($_GET['filter'] == 'WSemester')
						$WSemesterSelected ='selected';
					elseif($_GET['filter']=='SSemester')
						$SSemesterSelected='selected';
	
				echo '<option value="WSemester" '.$WSemesterSelected.'>'.$p->t('incoming/wintersemester').'</option>';

				echo '<option value="SSemester" '.$SSemesterSelected.'>'.$p->t('incoming/sommersemester').'</option>';

		echo'</SELECT><br>';
		echo $p->t('courseInformation/unterrichtssprache').':<SELECT name="filterUnterrichtssprache" onchange=selectChange()>
		<option value="">'.$p->t('incoming/alleSprachen').'</option>';
		
				// Vorauswahl der Übergebenen Filter
				$GermanSelected = '';
				$EnglishSelected = '';
		
				if(isset($_GET['unterrichtssprache']))
					if($_GET['unterrichtssprache'] == 'German')
						$GermanSelected ='selected';
					elseif($_GET['unterrichtssprache']=='English')
						$EnglishSelected='selected';
		
				echo '<option value="German" '.$GermanSelected.'>German</option>';
		
				echo '<option value="English" '.$EnglishSelected.'>English</option>';
		
		echo'</SELECT><br>';
		echo $p->t('global/studiengang').':<SELECT name="filterStudiengang" onchange=selectChange()>
		<option value="">Alle Studiengänge</option>';
		
				// Vorauswahl der Übergebenen Filter
				
				$studiengang = new studiengang();
				$studiengang->getAll('typ,kurzbz', true);
				
				foreach ($studiengang->result as $row)
				{
					$selected = '';
					if(isset($_GET['studiengang']) && $_GET['studiengang'] == $row->studiengang_kz)
						$selected='selected';
					
					echo '<option value="'.$row->studiengang_kz.'" '.$selected.'>'.strtoupper($row->typ.$row->kurzbz).' - '.$row->bezeichnung.'</option>';
				}
		
		echo'</SELECT>';
		echo '</td>
		</tr>
	</table>
		
<script language="JavaScript">
	function selectChange() 
	{
		filter = document.filterSemester.filterLv.options[document.filterSemester.filterLv.selectedIndex].value;
		filterSprache = document.filterSemester.filterUnterrichtssprache.options[document.filterSemester.filterUnterrichtssprache.selectedIndex].value;
		filterStudiengang = document.filterSemester.filterStudiengang.options[document.filterSemester.filterStudiengang.selectedIndex].value;
		url = [location.protocol, "//", location.host, location.pathname].join("");
		url = url+"?method=lehrveranstaltungen&filter="+filter+"&unterrichtssprache="+filterSprache+"&studiengang="+filterStudiengang;
		document.location=url;
	}
</script>	
		
		</form>';
			
		// Filter für Semester setzen
		$filterqry = '';
		
		if(isset($_GET['filter']))
			if($_GET['filter'] == "WSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (1,3,5)";
			elseif($_GET['filter'] == "SSemester")
				$filterqry= " AND tbl_lehrveranstaltung.semester IN (2,4,6)";
		
		if(isset($_GET['unterrichtssprache']) && $_GET['unterrichtssprache']!='')
			$filterqry .= " AND tbl_lehrveranstaltung.sprache='".$_GET['unterrichtssprache']."'";

		
		//Uebersicht LVs
		$qry = "SELECT 
					tbl_lehrveranstaltung.lehrveranstaltung_id, tbl_lehrveranstaltung.studiengang_kz, tbl_lehrveranstaltung.ects, 
					tbl_lehrveranstaltung.bezeichnung, tbl_lehrveranstaltung.semester, tbl_lehrveranstaltung.sprache,
					tbl_lehrveranstaltung.bezeichnung_english, tbl_lehrveranstaltung.incoming, tbl_lehrveranstaltung.orgform_kurzbz,
					(
					Select count(*) 
					FROM (
						SELECT
							tbl_benutzer.person_id
						FROM 
							campus.vw_student_lehrveranstaltung 
						JOIN public.tbl_benutzer using(uid)
						JOIN public.tbl_prestudent ON(vw_student_lehrveranstaltung.prestudent_id=tbl_prestudent.prestudent_id)
						JOIN public.tbl_prestudentstatus ON(tbl_prestudentstatus.prestudent_id=tbl_prestudent.prestudent_id)
						WHERE
							lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
							AND
							lehreinheit_id in (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
							AND 
							tbl_lehreinheit.studiensemester_kurzbz='$stsem->studiensemester_kurzbz')
							AND
							tbl_prestudentstatus.status_kurzbz='Incoming'
							AND tbl_prestudentstatus.studiensemester_kurzbz='$stsem->studiensemester_kurzbz'
						UNION
						SELECT 
							person_id 
						FROM 
							public.tbl_preincoming_lehrveranstaltung 
						JOIN public.tbl_preincoming using(preincoming_id) 
						WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id 
						AND 
						(von is null OR von <= '$stsem->start') 
						AND 
						(bis is null OR bis >= (DATE '$stsem->ende')) 
						AND aktiv = true				
						)a ) as anzahl
					FROM 
						lehre.tbl_lehrveranstaltung JOIN public.tbl_studiengang USING(studiengang_kz)
					WHERE 
						/*tbl_lehrveranstaltung.incoming>0 AND*/
						tbl_lehrveranstaltung.aktiv AND 
						tbl_lehrveranstaltung.lehre
						AND tbl_lehrveranstaltung.studiengang_kz>0 AND tbl_lehrveranstaltung.studiengang_kz<10000";
					
					if (isset($_GET['studiengang']) && $_GET['studiengang'] !='')
						$qry .= "AND tbl_lehrveranstaltung.studiengang_kz=".$_GET['studiengang'];
				
					$qry .= "AND tbl_studiengang.aktiv ".$filterqry." order by studiengang_kz
					";

		echo '<table class="tablesorter" id="t1" width="90%" border="0" align="center">
				<thead align="center">
				<tr>
					<th>'.$p->t('global/studiengang').'</th>
					<th>'.$p->t('abgabetool/typ').'</th>
					<th>'.$p->t('incoming/orgform').'</th>
					<th>'.$p->t('global/semester').'</th>
					<th>'.$p->t('global/lehrveranstaltung').'</th>
					<th>'.$p->t('global/lehrveranstaltung').' '.$p->t('global/englisch').'</th>
					<th>'.$p->t('incoming/ects').'</th>
					<th>'.$p->t('courseInformation/unterrichtssprache').'</th>
					<th>Info</th>
					<th>'.$p->t('incoming/freieplätze').'</th>
	   				<th></th>
				</tr>
				</thead>
				<tbody>';
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				$freieplaetze = $row->incoming - $row->anzahl;
				
				$studiengang = new studiengang(); 
				$studiengang->load($row->studiengang_kz);
				$studiengang_language = ($sprache == 'German') ? $studiengang->bezeichnung : $studiengang->english;  
				$typ = $studiengang->typ; 
				$style='';
				if ($row->incoming=='0')
					$style = 'style="color:grey"';
				if ($studiengang->typ == 'b')
					$typ = 'BA';
				else if ($studiengang->typ == 'm')
					$typ = 'MA';  
				echo '<tr>';
				echo '<td '.$style.'>',$studiengang_language,'</td>';
				echo '<td '.$style.'>',$typ,'</td>';
				echo '<td '.$style.'>',$row->orgform_kurzbz,'</td>';
				echo '<td '.$style.'>',$row->semester,'</td>';
				echo '<td '.$style.'>',$row->bezeichnung,'</td>';
				echo '<td '.$style.'>',$row->bezeichnung_english,'</td>';
				echo '<td '.$style.'>',$row->ects,'</td>';
				echo '<td '.$style.'>',$row->sprache,'</td>';
				echo '<td '.$style.'>
						<a href="#Deutsch" class="Item" onclick="javascript:window.open(\'../../cis/private/lehre/ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=de\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Deutsch&nbsp;</a>
						<a href="#Englisch" class="Item" onclick="javascript:window.open(\'../../cis/private/lehre/ects/preview.php?lv='.$row->lehrveranstaltung_id.'&amp;language=en\',\'Lehrveranstaltungsinformation\',\'width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\');return false;">Englisch</a>
					  </td>';
				echo '<td '.$style.'>',($freieplaetze<$row->incoming?'<strong>'.$freieplaetze.'/'.$row->incoming.'</strong>':$freieplaetze.'/'.$row->incoming),'</td>';
				echo '<td><a href="incoming_lehrveranstaltungen.php?method=lehrveranstaltungen&mode=setZero&id='.$row->lehrveranstaltung_id.'" onclick="return conf(\''.$row->bezeichnung.'\')">Plätze auf 0 setzen</a></td>';
				echo '</tr>';

			}
		}
		echo '</tbody></table>';
	}
?>
	</body>
</html>
