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
	require_once('../../../config/cis.config.inc.php');
  require_once('../../../include/basis_db.class.php');
  if (!$db = new basis_db())
      die('Fehler beim Oeffnen der Datenbankverbindung');
  
require_once('../../../include/functions.inc.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/studiengang.class.php');
require_once('../../../include/fachbereich.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');
require_once('../../../include/datum.class.php');

echo '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Zeitaufzeichnung</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="../../../include/js/tablesort/table.css" type="text/css">
<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
function setbisdatum()
{
	var now = new Date();
	var ret = "";
	var monat = now.getMonth();
	monat++;
	ret = foo(now.getDate());
	ret = ret + "." + foo(monat);
	ret = ret + "." + now.getFullYear();
	ret = ret + " " + foo(now.getHours());
	ret = ret + ":" + foo(now.getMinutes());
	//ret = ret + ":" + foo(now.getSeconds());
		
	document.getElementById("bis").value=ret;
}

function foo(val)
{
	if(val<10)
		return "0"+val;
	else
		return val;
}

function confdel()
{
	return confirm("Wollen Sie diesen Datensatz wirklich loeschen?");
}

function loaduebersicht()
{
	projekt = document.getElementById("projekt").value;
	
	document.location.href="'.$_SERVER['PHP_SELF'].'?filter="+projekt;
}

function uebernehmen()
{
	document.getElementById("bis").value=document.getElementById("von").value;
}
</script>
</head>
<body>
';

echo '<table class="tabcontent">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    	<table class="tabcontent">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">Zeitaufzeichnung</font></td>
	      </tr>
	    </table>
	    <br>';


$user = get_uid();
$datum = new datum();
$studiengang = new studiengang();
$studiengang->getAll('typ, kurzbz', false);
$stg_arr = array();

foreach ($studiengang->result as $stg) 
{
	$stg_arr[$stg->studiengang_kz]=$stg->kuerzel;	
}

$zeitaufzeichnung_id = (isset($_GET['zeitaufzeichnung_id'])?$_GET['zeitaufzeichnung_id']:'');
$projekt_kurzbz = (isset($_POST['projekt'])?$_POST['projekt']:'');
$studiengang_kz = (isset($_POST['studiengang'])?$_POST['studiengang']:'');
$fachbereich_kurzbz = (isset($_POST['fachbereich'])?$_POST['fachbereich']:'');
$aktivitaet_kurzbz = (isset($_POST['aktivitaet'])?$_POST['aktivitaet']:'');
$von = (isset($_POST['von'])?$_POST['von']:date('d.m.Y H:i'));
$bis = (isset($_POST['bis'])?$_POST['bis']:date('d.m.Y H:i', mktime(date('H'), date('i')+10, 0, date('m'),date('d'),date('Y'))));
$beschreibung = (isset($_POST['beschreibung'])?$_POST['beschreibung']:'');

//Speichern der Daten
if(isset($_POST['save']) || isset($_POST['edit']))
{
	$zeit = new zeitaufzeichnung();
	
	if(isset($_POST['edit']))
	{
		if(!$zeit->load($zeitaufzeichnung_id))
			die('Fehler beim Laden des Datensatzes');
		
		$zeit->new = false;
	}
	else 
	{
		$zeit->new = true;
		$zeit->insertamum = date('Y-m-d H:i:s');
		$zeit->insertvon = $user;
	}
	
	$zeit->uid = $user;
	$zeit->aktivitaet_kurzbz = $aktivitaet_kurzbz;
	$zeit->start = $von;
	$zeit->ende = $bis;
	$zeit->beschreibung = $beschreibung;
	$zeit->studiengang_kz = $studiengang_kz;
	$zeit->fachbereich_kurzbz = $fachbereich_kurzbz;
	$zeit->updateamum = date('Y-m-d H:i:s');
	$zeit->updatevon = $user;
	$zeit->projekt_kurzbz = $projekt_kurzbz;
	
	if(!$zeit->save())
	{
		echo '<b>Fehler beim Speichern der Daten:'.$zeit->errormsg.'</b><br>';
	}
	else 
	{
		echo '<b>Daten wurden gespeichert</b><br>';
		$zeitaufzeichnung_id = $zeit->zeitaufzeichnung_id;
	}
}

//Datensatz loeschen
if(isset($_GET['type']) && $_GET['type']=='delete')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{
		if($zeit->uid==$user)
		{
			if($zeit->delete($zeitaufzeichnung_id))
				echo '<b>Eintrag wurde geloescht</b><br>';
			else 
				echo '<b>Fehler beim Loeschen des Eintrages</b><br>';
		}
		else 
			echo '<b>Keine Berechtigung!</b><br>';
	}
	else 
		echo '<b>Datensatz wurde nicht gefunden</b><br>';
}

//Laden der Daten zum aendern
if(isset($_GET['type']) && $_GET['type']=='edit')
{
	$zeit = new zeitaufzeichnung();
	
	if($zeit->load($zeitaufzeichnung_id))
	{
		if($zeit->uid==$user)
		{
			$uid = $zeit->uid;
			$aktivitaet_kurzbz = $zeit->aktivitaet_kurzbz;
			$von = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->start));
			$bis = date('d.m.Y H:i', $datum->mktime_fromtimestamp($zeit->ende));
			$beschreibung = $zeit->beschreibung;
			$studiengang_kz = $zeit->studiengang_kz;
			$fachbereich_kurzbz = $zeit->fachbereich_kurzbz;
			$projekt_kurzbz = $zeit->projekt_kurzbz;
		}
		else 
		{
			echo "<b> Keine Berechtigung zum Aendern des Datensatzes</b>";
			$zeitaufzeichnung_id='';
		}
	}
}

//Projekte holen fuer zu denen der Benutzer zugeteilt ist
$qry_projekt = "SELECT distinct tbl_projekt.* FROM fue.tbl_projektbenutzer JOIN fue.tbl_projekt USING(projekt_kurzbz) WHERE beginn<=now() AND (ende>=now() OR ende is null) AND uid='$user'";

if($result_projekt = $db->db_query($qry_projekt))
{
	if($db->db_num_rows($result_projekt)>0)
	{
		$bn = new benutzer();
		if(!$bn->load($user))
			die("Benutzer $user wurde nicht gefunden");
			
		echo "<table width='100%'><tr><td>Zeitaufzeichnung von <b>$bn->vorname $bn->nachname</b></td>
		      <td align='right'><a href='".$_SERVER['PHP_SELF']."' class='Item'>NEU</a></td></tr></table>";
		
		//Formular
		echo '<br><br><form action="'.$_SERVER['PHP_SELF'].'?zeitaufzeichnung_id='.$zeitaufzeichnung_id.'" method="POST">';
		
		echo '<table>';
		//Projekt
		echo '<tr><td>Projekt</td><td><SELECT name="projekt" id="projekt">';
		while($row_projekt = $db->db_fetch_object($result_projekt))
		{
			if($projekt_kurzbz == $row_projekt->projekt_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo "<option value='$row_projekt->projekt_kurzbz' $selected>$row_projekt->titel</option>";
		}
		echo '</SELECT><input type="button" value="Uebersicht" onclick="loaduebersicht();"></td>';
		
		//Studiengang
		echo '<td>Studiengang</td><td><SELECT name="studiengang">';
		$stg_obj = new studiengang();
		$stg_obj->getAll('typ, kurzbz',false);
		
		echo "<option value=''>-- keine Auswahl --</option>";
		
		foreach ($stg_obj->result as $stg)
		{
			if($stg->studiengang_kz == $studiengang_kz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo "<option value='$stg->studiengang_kz' $selected>$stg->kuerzel ($stg->kurzbzlang)</option>";
		}
		echo '</SELECT>';
		echo '</td>';
		echo '</tr>';
		
		//Aktivitaet
		echo '<tr>';
		echo '<td>Aktivit&auml;t</td><td>';
		
		$qry = "SELECT * FROM fue.tbl_aktivitaet ORDER by beschreibung";
		if($result = $db->db_query($qry))
		{
			echo '<SELECT name="aktivitaet">';
			while($row = $db->db_fetch_object($result))
			{
				if($aktivitaet_kurzbz == $row->aktivitaet_kurzbz)
					$selected = 'selected';
				else
					$selected = '';
				
				echo "<option value='$row->aktivitaet_kurzbz' $selected>$row->beschreibung</option>";
			}
			echo '</SELECT>';
		}
		//Fachbereich
		echo '</td><td>Fachbereich</td><td><SELECT name="fachbereich">';
		echo '<option value="">-- keine Auswahl --</option>';
		
		$fb_obj = new fachbereich();
		$fb_obj->getAll();
		
		foreach ($fb_obj->result as $fb) 
		{
			if($fachbereich_kurzbz == $fb->fachbereich_kurzbz)
				$selected = 'selected';
			else 
				$selected = '';
			
			echo "<option value='$fb->fachbereich_kurzbz' $selected>$fb->bezeichnung</option>";
		}
		echo '</SELECT></td></tr>';
		echo '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		//Start/Ende
		echo '
		<tr>
			<td>Von</td><td><input type="text" id="von" name="von" value="'.$von.'"><input type="button" value="->"  onclick="uebernehmen()"></td>
			<td>Bis</td><td><input type="text" id="bis" name="bis" value="'.$bis.'">&nbsp;&nbsp;<img src="../../../skin/images/refresh.png" onclick="setbisdatum()"></td>
		<tr>';
		//Beschreibung
		echo '<tr><td>Beschreibung</td><td colspan="3"><textarea name="beschreibung" cols="60">'.$beschreibung.'</textarea></td></tr>';
		echo '<tr><td></td><td></td><td></td><td align="right">';
		//SpeichernButton
		if($zeitaufzeichnung_id=='')
			echo '<input type="submit" value="Speichern" name="save"></td></tr>';
		else 
			echo '<input type="submit" value="&Auml;ndern" name="edit"></td></tr>';
		echo '</table>';
		echo '</form>';
		
		echo '<br><hr>';
		
		//Uebersichtstabelle
		echo "<table id='t1' class='liste table-autosort:4 table-stripeclass:alternate table-autostripe'>\n";
		echo "   <thead><tr class='liste'>\n";
	    echo "       <th class='table-sortable:numeric'>ID</th><th class='table-sortable:default'>Projekt</th>";
	    echo "<th class='table-sortable:default'>Aktivitaet</th><th class='table-sortable:default'>User</th>";
	    echo "<th class='table-sortable:default'>Start</th>";
	    echo "<th class='table-sortable:default'>Ende</th>";
	    echo "<th class='table-sortable:default'>Dauer</th>";
	    echo "<th class='table-sortable:default'>Beschreibung</th><th class='table-sortable:default'>Stg</th>";
	    echo "<th class='table-sortable:default'>FB</th><th colspan='2'>Aktion</th>";
	    echo "   </tr></thead><tbody>\n";
	    
	    if(isset($_GET['filter']))
	    	$where = "projekt_kurzbz='".addslashes($_GET['filter'])."'";
	    else 
	    	$where = "uid='$user' AND ende>(now() - INTERVAL '40 days')";
	    	//(SELECT to_char(sum(ende-start),'HH:MI:SS') 
	    $qry = "SELECT 
	    			*, to_char ((ende-start),'HH24:MI') as diff, 
	    			(SELECT (to_char(sum(ende-start),'DD')::integer)*24+to_char(sum(ende-start),'HH24')::integer || ':' || to_char(sum(ende-start),'MI')
	    			 FROM campus.tbl_zeitaufzeichnung 
	    			 WHERE $where ) as summe 	    
	    		FROM campus.tbl_zeitaufzeichnung WHERE $where
	    		ORDER BY start DESC";
	    //AND ende>(now() - INTERVAL '40 days')
	    //echo $qry;
	    if($result = $db->db_query($qry))
	    {
		    $i = 0;
		    $summe=0;
			while($row=$db->db_fetch_object($result))
		    {		        
		    	$summe = $row->summe;
				echo "   <tr>\n";
		        echo "       <td>".$row->zeitaufzeichnung_id."</td>\n";
				echo "       <td>".$row->projekt_kurzbz."</td>\n";
		        echo "       <td>$row->aktivitaet_kurzbz</td>\n";
		        echo "       <td>$row->uid</td>\n";
		        echo "       <td nowrap><div style='display: none;'>$row->start</div>".date('d.m.Y H:i', $datum->mktime_fromtimestamp($row->start))."</td>\n";
		        echo "       <td nowrap><div style='display: none;'>$row->ende</div>".date('d.m.Y H:i', $datum->mktime_fromtimestamp($row->ende))."</td>\n";
		        echo "       <td align='right'>".$row->diff."</td>\n";
		        echo "       <td title='".mb_eregi_replace("\r\n",' ',$row->beschreibung)."'>".$row->beschreibung."</td>\n";
		        echo "       <td>".(isset($stg_arr[$row->studiengang_kz])?$stg_arr[$row->studiengang_kz]:$row->studiengang_kz)."</td>\n";
		        echo "       <td>$row->fachbereich_kurzbz</td>\n";
		        echo "       <td>";
		        if(!isset($_GET['filter']) || $row->uid==$user)
		        	echo "<a href='".$_SERVER['PHP_SELF']."?type=edit&zeitaufzeichnung_id=$row->zeitaufzeichnung_id' class='Item'>edit</a>";
		        echo "</td>\n";
		        echo "       <td>";
		        if(!isset($_GET['filter']) || $row->uid==$user)
		        	echo "<a href='".$_SERVER['PHP_SELF']."?type=delete&zeitaufzeichnung_id=$row->zeitaufzeichnung_id' class='Item'  onclick='return confdel()'>delete</a>";
		        echo "</td>\n";
		        echo "   </tr>\n";
		        $i++;
		    }
	    }
	    echo "</tbody></table>\n";
	    echo "Gesamtdauer: $summe";
	}
	else 
	{
		echo 'Sie sind derzeit keinen Projekten zugeordnet';
	}
}
else 
{
	echo 'Fehler beim Ermitteln der Projekte';
}

?>
	</td>
  </tr>
</table>
</body>
</html>