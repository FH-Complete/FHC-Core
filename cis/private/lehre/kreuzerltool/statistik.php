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

require_once('../../../config.inc.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/lehrveranstaltung.class.php');
require_once('../../../../include/studiengang.class.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lehreinheit.class.php');
require_once('../../../../include/benutzerberechtigung.class.php');
require_once('../../../../include/uebung.class.php');
require_once('../../../../include/beispiel.class.php');
require_once('../../../../include/datum.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../../skin/cis.css" rel="stylesheet" type="text/css">
<title>Kreuzerltool</title>
<script language="JavaScript">
<!--        
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");
	  
	  if(restore) 
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
	function confirmdelete()
	{
		return confirm('Wollen Sie die markierten Eintr�ge wirklich l�schen? Alle bereits eingetragenen Kreuzerl gehen dabei verloren!!');
	}
  //-->
</script>
</head>

<body>
<?php
if(!$conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user = get_uid();

if(!check_lektor($user, $conn))
	die('Sie haben keine Berechtigung fuer diesen Bereich');
	
$rechte = new benutzerberechtigung($conn);
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else 
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id'])) //Lehreinheit_id
	$lehreinheit_id = $_GET['lehreinheit_id'];
else 
	$lehreinheit_id = '';

//Laden der Lehrveranstaltung
$lv_obj = new lehrveranstaltung($conn);
if(!$lv_obj->load($lvid))
	die($lv_obj->errormsg);
	
//Studiengang laden
$stg_obj = new studiengang($conn,$lv_obj->studiengang_kz);

if(isset($_GET['stsem']))
	$stsem = $_GET['stsem'];
else 
	$stsem = '';

//Vars
$datum_obj = new datum();

$uebung_id = (isset($_GET['uebung_id'])?$_GET['uebung_id']:'');

//Kopfzeile
echo '<table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">';
echo ' <tr>';
echo '<td width="10">&nbsp;</td>';
echo '<td class="ContentHeader"><font class="ContentHeader">&nbsp;"Kreuzerl"-Tool - ';
echo $lv_obj->bezeichnung.' - '.$stg_obj->kurzbz;
echo '</font></td><td  class="ContentHeader" align="right">'."\n";

//Studiensemester laden
$stsem_obj = new studiensemester($conn);
if($stsem=='')
	$stsem = $stsem_obj->getaktorNext();

$stsem_obj->getAll();

//Studiensemester DropDown
$stsem_content = "Studiensemester: <SELECT name='stsem' onChange=\"MM_jumpMenu('self',this,0)\">\n";

foreach($stsem_obj->studiensemester as $studiensemester)
{
	$selected = ($stsem == $studiensemester->studiensemester_kurzbz?'selected':'');
	$stsem_content.= "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
}
$stsem_content.= "</SELECT>\n";

//Lehreinheiten laden
if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('admin',$lv_obj->studiengang_kz))
{
	$qry = "SELECT tbl_lehrfach.bezeichnung as lfbez, * FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";
}
else 
{
	$qry = "SELECT tbl_lehrfach.bezeichnung as lfbez, * FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheitmitarbeiter.mitarbeiter_uid = '$user' AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";
}

if($result = pg_query($conn, $qry))
{
	if(pg_num_rows($result)>1)
	{
		//Lehreinheiten DropDown
		echo " Lehreinheit: <SELECT name='lehreinheit_id' onChange=\"MM_jumpMenu('self',this,0)\">\n";
		while($row = pg_fetch_object($result))
		{
			if($lehreinheit_id=='')
				$lehreinheit_id=$row->lehreinheit_id;				
			$selected = ($row->lehreinheit_id == $lehreinheit_id?'selected':'');
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN campus.vw_mitarbeiter ON(mitarbeiter_uid=uid) WHERE lehreinheit_id='$row->lehreinheit_id'";
			if($result_lektoren = pg_query($conn, $qry_lektoren))
			{
				$lektoren = '( ';
				while($row_lektoren = pg_fetch_object($result_lektoren))
					$lektoren .= $row_lektoren->kurzbz.' ';
				$lektoren .=')';
			}
			echo "<OPTION value='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez $lektoren</OPTION>\n";
		}
		echo '</SELECT> ';
	}
	else 
	{
		if($row = pg_fetch_object($result))
			$lehreinheit_id = $row->lehreinheit_id;
	}
}
else 
{
	echo 'Fehler beim Auslesen der Lehreinheiten';
}
echo $stsem_content;
echo '</td><tr></table>';
echo '<table><tr>';
echo '<td width="10">&nbsp;</td>';
echo "<td>\n";

if($lehreinheit_id=='')
	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

//Menue
echo "\n<!--Menue-->\n";
echo "<br>
<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Verwaltung</font>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und �bersichtstabelle</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font></a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a>
<br><br>
<!--Menue Ende-->\n";


echo "<h3>Statistik</h3>";

$uebung_obj = new uebung($conn);
$uebung_obj->load_uebung($lehreinheit_id);
if(count($uebung_obj->uebungen)>0)
{
	echo "W�hlen Sie bitte eine Kreuzerlliste aus: <SELECT name='uebung' onChange=\"MM_jumpMenu('self',this,0)\">\n";
	foreach ($uebung_obj->uebungen as $row)
	{
		if($uebung_id =='')
			$uebung_id = $row->uebung_id;
		
		if($uebung_id == $row->uebung_id)
			$selected = 'selected';
		else 
			$selected = '';
		echo "<OPTION value='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$row->uebung_id' $selected>";
		//Freigegeben = +
		//Nicht Freigegeben = -
		if($datum_obj->mktime_fromtimestamp($row->freigabevon)<time() && $datum_obj->mktime_fromtimestamp($row->freigabebis)>time())
			echo '+ ';
		else 
			echo '- ';
		echo $row->bezeichnung;
		echo '</OPTION>';
	}
	echo '</SELECT>';
}
else 
	echo "Derzeit gibt es keine Uebungen";

echo "<br><br><br>";
if(isset($uebung_id) && $uebung_id!='')
{
	$beispiel_obj = new beispiel($conn);
	if($beispiel_obj->load_beispiel($uebung_id))
	{
		if(count($beispiel_obj->beispiele)>0)
		{
			echo '<table border="0" cellpadding="0" cellspacing="0" width="600">
         		 <tr> 
	           		 <td>&nbsp;</td>
	           		 <td height="19" width="339" valign="bottom"> 
		           		 <table border="0" cellpadding="0" cellspacing="0" width="339" background="../../../../skin/images/bg.gif">
		                	<tr>
		                  		<td>&nbsp;</td>
		                	</tr>
		              	</table>
		             </td>
          		</tr>';
			$i=0;
			$qry_cnt = "SELECT distinct student_uid FROM campus.tbl_studentbeispiel JOIN campus.tbl_beispiel USING(beispiel_id) WHERE uebung_id='$uebung_id' GROUP BY student_uid";
				if($result_cnt = pg_query($conn,$qry_cnt))					
						$gesamt=pg_num_rows($result_cnt);
			
			foreach ($beispiel_obj->beispiele as $row)
			{
				$i++;
				$solved = 0;
				$psolved = 0;
				$qry_cnt = "SELECT count(*) as anzahl FROM campus.tbl_studentbeispiel WHERE beispiel_id=$row->beispiel_id AND vorbereitet=true";
				if($result_cnt = pg_query($conn,$qry_cnt))
					if($row_cnt = pg_fetch_object($result_cnt))
						$solved = $row_cnt->anzahl;
				
				
						
				if($solved>0)
					$psolved = $solved/$gesamt*100;
				
				echo '<tr> 
	            		<td '.($i%2?'class="MarkLine"':'').' valign="top" height="10" width="200"><font size="2" face="Arial, Helvetica, sans-serif"> 
	              			'.$row->bezeichnung.'
	              		</font></td>
						<td '.($i%2?'class="MarkLine"':'').'>
	            			<table width="339" border="0" cellpadding="0" cellspacing="0" background="../../../../skin/images/bg_.gif">
	                		<tr> 
	                  			<td valign="top"> 
	                  				<table width="100%" border="0" cellspacing="0" cellpadding="0">
	                      			<tr> 
	                        			<td nowrap><font size="2" face="Arial, Helvetica, sans-serif">
	                        			<img src="../../../../skin/images/entry.gif" width="'.($psolved*3).'" height="5" alt="" border="1" />
	                        			<span class="smallb"><b>&nbsp;'.$solved.'</b> ['.$psolved.'%]</span></font>
	                        			</td>
									</tr>
									</table>
								</td>
	                		</tr>
	              			</table>
						</td>
	          		</tr>';
			}
			echo "</table>";
			echo "<br><br>Es haben insgesamt <u>$gesamt Studenten</u> eingetragen.";
		}
	}
	else 
		echo "<span class='error'>$beispiel_obj->errormsg</span>";
}

/*
           for ($i = 0; $i < $rs->num; $i++) {
                            $text = $rs->arr[$i]["text"];
                            $id  = $rs->arr[$i]["id"];
                            $psolved =
                                 round((($solved[$id]/$count_students)*100),1);
                            $pnsolved =
                                 round((($nsolved[$id]/$count_students)*100),1);
			    $pproblems = 
				 round((($problems[$id]/$count_students)*100),1);
            
          
          } 
*/

?>
</td></tr>
</table>
</body>
</html>