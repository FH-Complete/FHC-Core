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
require_once('../../../../include/studentnote.class.php');
require_once('../../../../include/datum.class.php');
require_once('../../../../include/legesamtnote.class.php');
require_once('../../../../include/lvgesamtnote.class.php');
require_once('../../../../include/zeugnisnote.class.php');
require_once('../../../../include/pruefung.class.php');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../../skin/cis.css" rel="stylesheet" type="text/css">
<title>Kreuzerltool</title>
<STYLE TYPE="text/css">

.td_datum 
{
	width:70px;
	text-align: left;
}
.td_note{
	width:50px;
	text-align:center;
}

</STYLE>
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
		return confirm('Wollen Sie die markierten Einträge wirklich löschen? Alle bereits eingetragenen Kreuzerl gehen dabei verloren!!');
	}
  //-->
function getTopOffset(){  
		  var x,y;
		if (self.pageYOffset) // all except Explorer
		{
			x = self.pageXOffset;
			y = self.pageYOffset;
		}
		else if (document.documentElement && document.documentElement.scrollTop)
			// Explorer 6 Strict
		{
			x = document.documentElement.scrollLeft;
			y = document.documentElement.scrollTop;
		}
		else if (document.body) // all other Explorers
		{
			x = document.body.scrollLeft;
			y = document.body.scrollTop;
		}
		return y;
 }
  
  
    var anfrage = null;

    function erzeugeAnfrage(){
        try {
        anfrage = new XMLHttpRequest();
        } catch (versuchmicrosoft) {
            try {
                anfrage = new ActiveXObject("Msxml12.XMLHTTP");
            } catch (anderesmicrosoft){
                try {
                    anfrage = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (fehlschlag){
                    anfrage = null;
                }

            }
        }
        if (anfrage == null) alert("Fehler beim Erstellen des Anfrageobjekts!");
    }
   
   function saveLVNote(uid){
	note = document.getElementById(uid).note.value;	
	if ((note < 0) || (note > 5 && note != 8 && note != 7))
	{
		alert("Bitte geben Sie eine Note von 1 - 5 bzw. 7 (nicht beurteilt) oder 8 (teilgenommen) ein!");
		document.getElementById(uid).note.value="";
	}
	else
	{	
		erzeugeAnfrage(); 
	    stud_uid = uid;
	    var jetzt = new Date();
		var ts = jetzt.getTime();
	    var url= '<?php echo "lvgesamtnoteeintragen.php?lvid=$lvid&stsem=$stsem"; ?>';
	    url += '&submit=1&student_uid='+uid+"&note="+note+"&"+ts;
	    anfrage.open("GET", url, true);
	    anfrage.onreadystatechange = updateSeite;
	    anfrage.send(null);
    }
   }
   
   function updateSeite(){
	    if (anfrage.readyState == 4){
	        if (anfrage.status == 200) {
	        	uid = stud_uid;
				var note = document.getElementById(uid).note.value;
	            var resp = anfrage.responseText;
	            if (resp == "neu" || resp == "update" || resp == "update_f")
	            {
					            	
	            	notentd = document.getElementById("note_"+uid);
	            	while (notentd.childNodes.length>0)
	            	{
						notentd.removeChild(notentd.lastChild);
	            	}
	            	notenode = document.createTextNode(note);
                    notentd.appendChild(notenode);
					notenstatus = document.getElementById("status_"+uid);
					if (resp == "update_f")
                    	notenstatus.innerHTML = "<img src='../../../../skin/images/changed.png'>";
                 }
                 else
             		{
                 		alert(resp);
                 		document.getElementById(uid).note.value="";
             		}
	        } else alert("Request status:" + anfrage.status);
	    }
	}
	
	function pruefungAnlegen(uid,datum,note,lehreinheit_id)
	{
	
		var str = "<form name='nachpruefung_form'><center><table style='width:95%'><tr><td colspan='2' align='right'><a href='#' onclick='closeDiv();'>X</a></td></tr>";
		
		var anlegendiv = document.getElementById("nachpruefung_div");
		var y = getTopOffset();
		y = y+50;		
		anlegendiv.style.top = y+"px";
		//var anlegendiv = document.getElementById("span_"+uid);

		str += "<tr><td colspan='2'><b>Prüfung für "+uid+" anlegen:</b></td></tr>";
		//if (lehreinheit_id != "")		
		//	str += "<tr><td>Lehreinheit:</td><td>"+lehreinheit_id+"</td></tr>";
		str += "<tr><td>Datum:</td>";
		str += "<td><input type='hidden' name='uid' value='"+uid+"'><input type='hidden' name='le_id' value='"+lehreinheit_id+"'><input type='text' name='datum' value='"+datum+"'> [YYYY-MM-DD]</td>";
		str += "</tr><tr><td>Note:</td>";
		str += "<td><input type='text' name='note' value='"+note+"'></td>";
		str += "</tr><tr><td colspan='2' align='center'><input type='button' name='speichern' value='speichern' onclick='pruefungSpeichern();'></td></tr>";
		//str += "</table></center></form>";
		str += "</table></cehter></form>";		
		//str = "foo";
		anlegendiv.innerHTML = str;	
		anlegendiv.style.visibility = "visible";	
	}
	
	function pruefungSpeichern()
	{
		var note = document.nachpruefung_form.note.value;
		if ((note < 0) || (note > 5 && note != 8 && note != 7 && note != 9 && note != ""))
		{
			alert("Bitte geben Sie eine Note von 1 - 5 bzw. 7 (nicht beurteilt), 8 (teilgenommen), 9 (noch nicht eingetragen) ein oder lassen Sie das Feld leer!");
			document.getElementById(uid).note.value="";
		}		
		var datum = 	document.nachpruefung_form.datum.value;		
		var datum_test = datum.split("-");
		if (datum_test[0].length != 4 || datum_test[1].length!=2 || datum_test[2].length!=2 || isNaN(datum_test[0]) || datum_test[1]>12 || datum_test[2]>31)
			alert("Invalid Date. Format: YYYY-MM-DD");
		else
		{
			var anlegendiv = document.getElementById("nachpruefung_div");			
			
			var note = document.nachpruefung_form.note.value;
			if (note == "" || isNaN(note))
			{
					document.nachpruefung_form.note.value = "9";
					note = "9";
			}		
			var uid = document.nachpruefung_form.uid.value;
			var lehreinheit_id = document.nachpruefung_form.le_id.value;
			
			erzeugeAnfrage(); 
		    var jetzt = new Date();
			var ts = jetzt.getTime();
		    var url= '<?php echo "nachpruefungeintragen.php?lvid=$lvid&stsem=$stsem"; ?>';
		    //&lehreinheit_id=$lehreinheit_id
		    url += '&submit=1&student_uid='+uid+'&note='+note+'&datum='+datum+'&lehreinheit_id_pr='+lehreinheit_id+'&'+ts;
		    //alert(url);
		    anfrage.open("GET", url, true);
		    anfrage.onreadystatechange = updateSeitePruefung;
		    anfrage.send(null);
	    }
		
	}

    function updateSeitePruefung()
    {
	    if (anfrage.readyState == 4)
	    {
	        if (anfrage.status == 200) 
	        {
	        	var anlegendiv = document.getElementById("nachpruefung_div");	
				var datum = 	document.nachpruefung_form.datum.value;
				var note = document.nachpruefung_form.note.value;
				var uid = document.nachpruefung_form.uid.value;
				var lehreinheit_id = document.nachpruefung_form.le_id.value;
				//var note = document.getElementById(uid).note.value;
	            var resp = anfrage.responseText;
	            
	            if (resp == "neu" || resp == "update" || resp == "update_f" || resp == "update_pr")
	            {
		     	
	            	if (resp != "update_pr")
	            	{
		                notentd = document.getElementById("note_"+uid);	            	
		            	while (notentd.childNodes.length>0)
		            	{
							notentd.removeChild(notentd.lastChild);
		            	}
		            	notenode = document.createTextNode(note);
	                    notentd.appendChild(notenode);
					}					
					notenstatus = document.getElementById("status_"+uid);
					if (resp == "update_f")
                    	notenstatus.innerHTML = "<img src='../../../../skin/images/changed.png'>";
                    document.getElementById("lvnoteneingabe_"+uid).style.visibility = "hidden";
                    
   			 		anlegendiv.innerHTML = "";
					anlegendiv.style.visibility = "hidden";
					//if (note == 9)
					//	note = " ";
					document.getElementById("span_"+uid).innerHTML = "<table><tr><td class='td_datum'>"+datum+"</td><td class='td_note'>"+note+"<td><input type='button' name='anlegen' value='ändern' onclick='pruefungAnlegen(\""+uid+"\",\""+datum+"\",\""+note+"\",\""+lehreinheit_id+"\")'></td></tr></table>"
                }
                else
         		{
             		alert(resp);
             		document.getElementById(uid).note.value="";
         		}
	        } 
	        else 
	        	alert("Request status:" + anfrage.status);
	    }
	}

 	function closeDiv()
 	{
 		var anlegendiv = document.getElementById("nachpruefung_div");
 		anlegendiv.innerHTML = "";
 		anlegendiv.style.visibility = "hidden";
 	}
 
 	function OnFreigabeSubmit()
	{
		if(document.getElementById('textbox-freigabe-passwort').value.length==0)
		{
			alert('Bitte geben Sie zuerst Ihr Passwort ein!');
			return false;
		}
		return true;
	}
</script>
<style type="text/css">
.transparent {
    filter:alpha(opacity=90);
    -moz-opacity:0.9;
    -khtml-opacity: 0.9;
    opacity: 0.9;
</style>        
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

if(isset($_GET['lvid']) && is_numeric($_GET['lvid'])) //Lehrveranstaltung_id
	$lvid = $_GET['lvid'];
else
	die('Fehlerhafte Parameteruebergabe');

if(isset($_GET['lehreinheit_id']) && is_numeric($_GET['lehreinheit_id'])) //Lehreinheit_id
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
$uid = (isset($_GET['uid'])?$_GET['uid']:'');

//Kopfzeile
echo '<table class="tabcontent" height="100%">';
echo ' <tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo '<td class="ContentHeader"><font class="ContentHeader">&nbsp;Benotungstool';
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
	$stsem_content.= "<OPTION value='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$studiensemester->studiensemester_kurzbz' $selected>$studiensemester->studiensemester_kurzbz</OPTION>\n";
}
$stsem_content.= "</SELECT>\n";

if(!$rechte->isBerechtigt('admin',0) &&
   !$rechte->isBerechtigt('admin',$lv_obj->studiengang_kz) &&
   !$rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
{
	$qry = "SELECT lehreinheit_id FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id)
			JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) 
			WHERE tbl_lehrveranstaltung.lehrveranstaltung_id='".addslashes($lvid)."' AND
			tbl_lehreinheit.studiensemester_kurzbz='".addslashes($stsem)."' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid='".addslashes($user)."'";
	if($result = pg_query($conn, $qry))
	{
		if(pg_num_rows($result)==0)
			die('Sie haben keine Berechtigung für diese Seite');
	}
	else 
	{
		die('Fehler beim Pruefen der Rechte');
	}
}

//Lehreinheiten laden
/*
if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('admin',$lv_obj->studiengang_kz) || $rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
{
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.studiensemester_kurzbz = '$stsem'";
}
else
{
	$qry = "SELECT distinct tbl_lehrfach.kurzbz as lfbez, tbl_lehreinheit.lehreinheit_id, tbl_lehreinheit.lehrform_kurzbz as lehrform_kurzbz FROM lehre.tbl_lehreinheit, lehre.tbl_lehrfach, lehre.tbl_lehreinheitmitarbeiter
			WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
			tbl_lehreinheit.lehrfach_id = tbl_lehrfach.lehrfach_id AND
			tbl_lehreinheit.lehreinheit_id = tbl_lehreinheitmitarbeiter.lehreinheit_id AND
			tbl_lehreinheit.lehrveranstaltung_id IN (SELECT lehrveranstaltung_id FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id) WHERE mitarbeiter_uid='$user') AND
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
			$qry_lektoren = "SELECT * FROM lehre.tbl_lehreinheitmitarbeiter JOIN public.tbl_mitarbeiter USING(mitarbeiter_uid) WHERE lehreinheit_id='$row->lehreinheit_id'";
			if($result_lektoren = pg_query($conn, $qry_lektoren))
			{
				$lektoren = '( ';
				$i=0;
				while($row_lektoren = pg_fetch_object($result_lektoren))
				{
					$lektoren .= $row_lektoren->kurzbz;
					$i++;
					if($i<pg_num_rows($result_lektoren))
						$lektoren.=', ';
					else
						$lektoren.=' ';
				}
				$lektoren .=')';
			}
			$qry_gruppen = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$row->lehreinheit_id'";
			if($result_gruppen = pg_query($conn, $qry_gruppen))
			{
				$gruppen = '';
				$i=0;
				while($row_gruppen = pg_fetch_object($result_gruppen))
				{
					if($row_gruppen->gruppe_kurzbz=='')
						$gruppen.=$row_gruppen->semester.$row_gruppen->verband.$row_gruppen->gruppe;
					else
						$gruppen.=$row_gruppen->gruppe_kurzbz;
					$i++;
					if($i<pg_num_rows($result_gruppen))
						$gruppen.=', ';
					else
						$gruppen.=' ';
				}
			}
			echo "<OPTION value='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$row->lehreinheit_id' $selected>$row->lfbez-$row->lehrform_kurzbz - $gruppen $lektoren</OPTION>\n";
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
}*/
echo $stsem_content;
echo '</td><tr></table>';
echo '<table width="100%"><tr>';
echo '<td class="tdwidth10">&nbsp;</td>';
echo "<td>\n";
echo "<b>$lv_obj->bezeichnung</b><br>";
/*
if($lehreinheit_id=='')
	die('Es wurde keine passende Lehreinheit in diesem Studiensemester gefunden');

if(!isset($_GET['standalone']))
{
	//Menue
	include("menue.inc.php");
}*/


// lvgesamtnote für studenten speichern
if (isset($_REQUEST["submit"]) && ($_POST["student_uid"] != '')){
	
	$jetzt = date("Y-m-d H:i:s");	
	$student_uid = $_POST["student_uid"];
	$lvid = $_REQUEST["lvid"];
	$lvgesamtnote = new lvgesamtnote($conn);
    if (!$lvgesamtnote->load($lvid, $student_uid, $stsem))
    {
		$lvgesamtnote->student_uid = $student_uid;
		$lvgesamtnote->lehrveranstaltung_id = $lvid;
		$lvgesamtnote->studiensemester_kurzbz = $stsem;
		$lvgesamtnote->note = $_POST["note"];
		$lvgesamtnote->mitarbeiter_uid = $user;
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->freigabedatum = null;
		$lvgesamtnote->freigabevon_uid = null;
		$lvgesamtnote->bemerkung = null;
		$lvgesamtnote->updateamum = null;
		$lvgesamtnote->updatevon = null;
		$lvgesamtnote->insertamum = $jetzt;
		$lvgesamtnote->insertvon = $user;
		$new = true;
    }
    else
    {
		$lvgesamtnote->note = $_POST["note"];
		$lvgesamtnote->benotungsdatum = $jetzt;
		$lvgesamtnote->updateamum = $jetzt;
		$lvgesamtnote->updatevon = $user;
		$new = false;
	}
	if (!$lvgesamtnote->save($new))
		echo "<span class='error'>".$lvgesamtnote->errormsg."</span>";
}

// eingetragene lv-gesamtnoten freigeben
if (isset($_REQUEST["freigabe"]) and ($_REQUEST["freigabe"] == 1))
{
	//Passwort pruefen
	if(checkldapuser($user, $_REQUEST['passwort']))
	{
		$jetzt = date("Y-m-d H:i:s");
		$neuenoten = 0;
		$studlist = "<table border='1'><tr><td><b>Mat. Nr.</b></td><td><b>Nachname</b></td><td><b>Vorname</b></td><td><b>Note</b></td></tr>";

		//	$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$lehreinheit_id' ORDER BY semester, verband, gruppe, gruppe_kurzbz";
			
		//	if($result_grp = pg_query($conn, $qry))
		//	{
		//		while($row_grp = pg_fetch_object($result_grp))
		//		{
		/*		
					if($row_grp->gruppe_kurzbz!='')
					{
							$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' AND studiensemester_kurzbz = '".$stsem."' ORDER BY nachname, vorname";
					}
					else
					{
							$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student
							             WHERE studiengang_kz='$row_grp->studiengang_kz' AND
							             semester='$row_grp->semester' ".
										 ($row_grp->verband!=''?" AND trim(verband)=trim('$row_grp->verband')":'').
										 ($row_grp->gruppe!=''?" AND trim(gruppe)=trim('$row_grp->gruppe')":'').
							            " ORDER BY nachname, vorname";
					}
		*/
		// studentenquery					
		$qry_stud = "SELECT DISTINCT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = '".$stsem."' and lehrveranstaltung_id = '".$lvid."' ORDER BY nachname, vorname ";
        if($result_stud = pg_query($conn, $qry_stud))
		{
			$i=1;
			
			while($row_stud = pg_fetch_object($result_stud))
			{	
				$lvgesamtnote = new lvgesamtnote($conn);
    			if ($lvgesamtnote->load($lvid,$row_stud->uid,$stsem))
    			{
					if ($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)	    				
					{	    				
    					$lvgesamtnote->freigabedatum = $jetzt;
    					$lvgesamtnote->freigabevon_uid = $user;
    					$lvgesamtnote->save($new=null);
    					$studlist .= "<tr><td>".$row_stud->matrikelnr."</td><td>".$row_stud->nachname."</td><td>".$row_stud->vorname."</td><td>".$lvgesamtnote->note."</td></tr>";
    					$neuenoten++;
    				}
    			}
			}	
		}
		
		//		}
		//	}
		
		$studlist .= "</table>";
		//mail an assistentin und den user selber verschicken	
		if ($neuenoten > 0)
		{
			$lv = new lehrveranstaltung($conn, $lvid);
			$sg = new studiengang($conn, $lv->studiengang_kz);
			$debug_adressen = $user."@".DOMAIN;
			$adressen = $sg->email.", ".$user."@".DOMAIN;
			
			$freigeber = "<b>".strtoupper($user)."</b>";
			mail($adressen,"Notenfreigabe ".$lv->bezeichnung,"<html><body><b>".$lv->bezeichnung." - ".$stsem."</b> (".$lv->semester.". Sem.) <br><br>Benutzer ".$freigeber." hat die LV-Noten f&uuml;r folgende Studenten freigegeben:<br><br>".$studlist."<br>Mail wurde verschickt an: ".$adressen."</body></html>","From: vilesci@".DOMAIN."\nContent-Type: text/html\n");
		}	
	}
	else 
	{
		echo '<span><font class="error">Fehler beim Freigeben der Noten: Das Uebergebene Passwort ist falsch</font></span>';
	}
}

echo "<h3><a href='javascript:window.history.back()'>Zurück</a></h3>";
echo "<h3>LV Gesamtnote verwalten</h3>";
echo "Noten: 1-5, 7 (nicht beurteilt), 8 (teilgenommen)";

// alle pruefungen für die LV holen
$studpruef_arr = array();
$pr_all = new Pruefung($conn);
if ($pr_all->getPruefungenLV($lvid,"Termin2",$stsem))
{
	if ($pr_all->result)
	{
		foreach ($pr_all->result as $pruefung)
		{		
			$studpruef_arr[$pruefung->student_uid][$pruefung->lehreinheit_id]["note"] = $pruefung->note;
			$studpruef_arr[$pruefung->student_uid][$pruefung->lehreinheit_id]["datum"] = $pruefung->datum;
			//echo print_r($studpruef_arr[$pruefung->student_uid]);
		}	
	}
}





//Studentenliste
echo "
<table>
";

//$qry = "SELECT * FROM lehre.tbl_lehreinheitgruppe WHERE lehreinheit_id='$lehreinheit_id' ORDER BY semester, verband, gruppe, gruppe_kurzbz";

//if($result_grp = pg_query($conn, $qry))
//{
//	while($row_grp = pg_fetch_object($result_grp))
//	{
		echo "<tr>
				<td colspan='11'>&nbsp;</td>
			</tr>
			<tr>
				<td class='ContentHeader2'></td>
				<td class='ContentHeader2'>UID</td>
				<td class='ContentHeader2'>Nachname</td>
				<td class='ContentHeader2'>Vorname</td>
				<td class='ContentHeader2'>LE-Noten (LE-ID)</td>
				<td class='ContentHeader2'></td>
				<td class='ContentHeader2'>LV-Note</td>
				<td class='ContentHeader2' align='right'>
				<form name='freigabeform' action='".$_SERVER['PHP_SELF']."?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem' method='POST' onsubmit='return OnFreigabeSubmit()'><input type='hidden' name='freigabe' value='1'>
				Passwort: <input type='password' size='8' id='textbox-freigabe-passwort' name='passwort'><br><input type='submit' name='frei' value='Freigabe'>
				</form>
				</td>
				<td class='ContentHeader2'>Zeugnisnote</td>
				<td class='ContentHeader2' colspan='2'>Nachprüfung</td>
			</tr>
			<tr>
				<td colspan='9'>&nbsp;</td>
				<td coslspan='2'><table><tr><td class='td_datum'>Datum</td><td class='td_note'>Note</td></td></td></tr></table></td>
			</tr>
			<tr>
				<td colspan='11'>&nbsp;</td>
			</tr>";
/*
		if($row_grp->gruppe_kurzbz!='')
		{
				echo "
				<tr>
					<td colspan='11' align='center'><b>$row_grp->gruppe_kurzbz</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student JOIN public.tbl_benutzergruppe USING(uid) WHERE gruppe_kurzbz='".addslashes($row_grp->gruppe_kurzbz)."' AND studiensemester_kurzbz = '".$stsem."' ORDER BY nachname, vorname";
		}
		else
		{
			echo "
				<tr>
					<td colspan='11' align='center'><b>Verband $row_grp->verband ".($row_grp->gruppe!=''?"Gruppe $row_grp->gruppe":'')."</b></td>
				</tr>";
				$qry_stud = "SELECT uid, vorname, nachname, matrikelnr FROM campus.vw_student
				             WHERE studiengang_kz='$row_grp->studiengang_kz' AND
				             semester='$row_grp->semester' ".
							 ($row_grp->verband!=''?" AND trim(verband)=trim('$row_grp->verband')":'').
							 ($row_grp->gruppe!=''?" AND trim(gruppe)=trim('$row_grp->gruppe')":'').
				            " ORDER BY nachname, vorname";
		}
*/
		// studentenquery					
		$qry_stud = "SELECT DISTINCT uid, vorname, nachname, matrikelnr FROM campus.vw_student_lehrveranstaltung JOIN campus.vw_student using(uid) WHERE  studiensemester_kurzbz = '".$stsem."' and lehrveranstaltung_id = '".$lvid."' ORDER BY nachname, vorname ";	
        if($result_stud = pg_query($conn, $qry_stud))
		{
			$i=1;
			while($row_stud = pg_fetch_object($result_stud))
			{
    				
				//$studentnote = new studentnote($conn,$lehreinheit_id,$stsem,$row_stud->uid);
				

				
				/*
    			$legesamtnote = new legesamtnote($conn, $lehreinheit_id);
    			
    			if (!$legesamtnote->load($row_stud->uid,$lehreinheit_id))
				{    				
    				$note_le = null;
    			}
    			else
    				$note_le = $legesamtnote->note;
    			if ($lvgesamtnote = new lvgesamtnote($conn, $lvid,$row_stud->uid,$stsem))
    			{
    				$note_lv = $lvgesamtnote->note;
    			}
    			else
    				$note_lv = null;
				
				if ($note_lv)
					$note_vorschlag = $note_lv;
				else
					$note_vorschlag = $note_le;
				*/
				
				echo "
				<tr class='liste".($i%2)."'>
					<td><a href='mailto:$row_stud->uid@".DOMAIN."'><img src='../../../../skin/images/button_mail.gif'></a></td>";
					//<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->uid</a></td>
					//<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->nachname</a></td>
					//<td><a href='studentenpunkteverwalten.php?lvid=$lvid&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id&uid=$row_stud->uid&stsem=$stsem' class='Item'>$row_stud->vorname</a></td>";
				echo "
					<td>$row_stud->uid</td>
					<td>$row_stud->nachname</td>
					<td>$row_stud->vorname</td>";
				
				$note_les_str = '';
				$le_anz = 0;
				$note_le = 0;
				$le = new lehreinheit($conn);
				$le->load_lehreinheiten($lvid, $stsem);
				foreach($le->lehreinheiten as $l)				
				{				
					$legesamtnote = new legesamtnote($conn, $l->lehreinheit_id);
	    			
	    			if (!$legesamtnote->load($row_stud->uid,$l->lehreinheit_id))
					{    				
	    				//$note_les_str .= "- (".$l->lehreinheit_id.")";
	    			}
	    			else
	    			{
	    				$note_le += $legesamtnote->note;
	    				$le_anz += 1;
	    				if ($legesamtnote->note == 5)
	    					$leneg = " style='color:red; font-weight:bold'";
	    				else
	    					$leneg = "";
	    				$note_les_str .= "<span".$leneg.">".$legesamtnote->note."</span> (".$l->lehreinheit_id.") ";
	    			}
	    		}
	    			
    			if ($lvgesamtnote = new lvgesamtnote($conn, $lvid,$row_stud->uid,$stsem))
    			{
    				$note_lv = $lvgesamtnote->note;
    			}
    			else
    				$note_lv = null;
				
				if ($note_lv)
					$note_vorschlag = $note_lv;
				else if ($le_anz > 0)
					$note_vorschlag = round($note_le/$le_anz);
				else
					$note_vorschlag = null;
				if ($zeugnisnote = new zeugnisnote($conn, $lvid, $row_stud->uid, $stsem))
					$znote = $zeugnisnote->note;
				else
					$znote = null;			
								
				
				echo "<td>$note_les_str</td>";
				if (key_exists($row_stud->uid,$studpruef_arr))	
					$hide = "style='visibility:hidden;'";
				else
					$hide = "style='visibility:visible;'";				
				echo "<form name='$row_stud->uid' id='$row_stud->uid' method='POST' action='".$_SERVER['PHP_SELF']."?lvid=$lvid&lehreinheit_id=$lehreinheit_id&stsem=$stsem'><td><span id='lvnoteneingabe_".$row_stud->uid."' ".$hide."><input type='hidden' name='student_uid' value='$row_stud->uid'><input type='text' size='1' value='$note_vorschlag' name='note'><input type='button' value='->' onclick='saveLVNote(\"$row_stud->uid\")'></span></td></form>";

				if ($note_lv == 5)
					$negmarkier = " style='color:red; font-weight:bold;'";
				else
					$negmarkier = "";			
				
				echo "<td align='center' id='note_$row_stud->uid'><span".$negmarkier.">$note_lv</span></td>";
				
				//status //////////////////////////////////////////////////////////////////////////////////
				echo "<td align='center' id='status_$row_stud->uid'>";				
				if (!$lvgesamtnote->freigabedatum)
					echo "<img src='../../../../skin/images/offen.png'>";				
				else if	($lvgesamtnote->benotungsdatum > $lvgesamtnote->freigabedatum)
					echo "<img src='../../../../skin/images/changed.png'>";
				else
					echo "<img src='../../../../skin/images/ok.png'>";
					
				echo "</td>";
				if (($znote) and ($note_lv != $znote))
					$stylestr = " style='color:red; border-color:red; border-style:solid; border-width:1px;'";
				else
					$stylestr ="";
				echo "<td".$stylestr." align='center'>".$znote."</td>";
				
				// prüfungen ///////////////////////////////////////////////////////////////////////////
				//$pr = new pruefung($conn);
				//$pr->getPruefungen($row_stud->uid, "Termin2", null);
				//if (count($pr->result)>0)
				if (key_exists($row_stud->uid,$studpruef_arr))			
				{

					//$pr_datum = $pr->result[0]->datum;
					//$pr_note = $pr->result[0]->note;
					//$pr_le_id = $pr->result[0]->lehreinheit_id;
					
					//echo "<td>".$pr_datum."</td>";
					//echo "<form name='nachpruefung_".$row_stud->uid."'>";
					echo "<td colspan='2'>";
					echo "<span id='span_".$row_stud->uid."'>";
					echo "<table>";
					$le_id_arr = array();					
					$le_id_arr = array_keys($studpruef_arr[$row_stud->uid]);
					foreach ($le_id_arr as $le_id_stud)
					{					
						$pr_note = $studpruef_arr[$row_stud->uid][$le_id_stud]["note"];
						$pr_datum = $studpruef_arr[$row_stud->uid][$le_id_stud]["datum"];
						$pr_le_id = $le_id_stud;
						
						echo "<tr><td class='td_datum'>";
						echo $pr_datum."</td><td class='td_note'>".$pr_note."</td><td>";
						echo "<input type='button' name='anlegen' value='ändern' onclick='pruefungAnlegen(\"".$row_stud->uid."\",\"".$pr_datum."\",\"".$pr_note."\",\"".$pr_le_id."\")'>";					
						echo "</td></tr>";
					}
					echo "</table>";			
					echo "</span>";
					//echo "<div id='nachpruefung_div_".$row_stud->uid."' style='position:relative; top:0px; left 5px; background-color:#cccccc; visibility:collapse;' class='transparent'></div>";
					echo "</td>";
					//echo "</form>";
				}
				else
				{
					if ($note_lv)				
						echo "<td colspan='2'><span id='span_".$row_stud->uid."'><input type='button' name='anlegen' value='anlegen' onclick='pruefungAnlegen(\"".$row_stud->uid."\",\"\",\"\",\"\")'></span></td>";
					else
						echo "<td colspan='2'></td>";	
				}
				
				
				echo "</tr>";
				$i++;
			}
		}
//	}
//}
echo "</table>";

?>
</td></tr>
</table>

<div id="nachpruefung_div" style="position:absolute; top:100px; left:200px; width:400px; height:150px; background-color:#cccccc; visibility:hidden; border-style:solid; border-width:1px; border-color:#333333;" class="transparent"></div>

</body>
</html>
