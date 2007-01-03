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

/* @author Andres Oesterreicher
   @date 20.10.2005
   @brief Formular zum Freigeben der LV Informationen aus der tabelle tbl_lvinfo
   
   @edit	08-11-2006 Versionierung entfernt. Studiensemester = WS2007
   			03-01-2006 Anpassung an neue DB
*/
	require_once('../../../config.inc.php');
	require_once('../../../../include/functions.inc.php');
	require_once('../../../../include/studiensemester.class.php');
	require_once('../../../../include/lvinfo.class.php');
	
	if(!$conn=pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zur Datenbank hergestellt werden');

	$user = get_uid();

	/* WriteLog($qry,$uid)
	 * @brief Schreib die Querys im format: uid - datum - qry ins LogFile
	 * @param $qry Query anweisung
	 *        $uid Username
	 * @return true wenn ok false wenn fehler beim oeffnen
	 */    
	function WriteLog($qry,$uid)
	{

		if($fp=fopen(LVINFO_LOG_PATH,"a"))
		{
			fwrite($fp,"\n");
			fwrite($fp,$uid." ". date("d.m.Y - H:i:s") . " ". $qry);
			fclose($fp);
			return true;
		}
		else 
			return false;   	  
	}
   
	if(!check_lektor($user,$conn))
	{
		die('<center>Sie haben keine Berechtigung fuer diesen Bereich</center>');
	}
   
	if(isset($_POST['stg'])) //Studiengang der Angezeigt werden soll
		$stg=$_POST['stg'];
	else if(isset($_GET['stg']))
		$stg=$_GET['stg'];
	else
		$stg='';

	if(isset($_POST['sem'])) //Semester das angezeigt werden soll
		$sem=$_POST['sem'];
	else if(isset($_GET['sem']))
		$sem = $_GET['sem'];
	else
		$sem='';
        
	if(isset($_POST["lv"])) //Id des DS der freigegeben/nicht freigegeben werden soll
		$id=$_POST["lv"];

	if(isset($_GET["del"])) //Wenn diese Variable gesetzt ist dann wird DS mit $idde und $iden geloescht
		$del=$_GET["del"];        
   
	if(isset($_POST["changestat"])) //Wenn diese Variable gesetzt ist dann wird DS mit $id freigegeben/nicht freigegeben
		$changestat=$_POST["changestat"];

	if(!isset($_GET['lv']) && !isset($_POST['lv']))
		$lv='';
		
	if(isset($_POST["status"]) && $_POST["status"] =='changestg')
		unset($sem);

	if(isset($del) && isset($id))
	{
		//Loeschen der beiden Datensaetze
   	  
		$lvinfo_obj = new lvinfo($conn);
		pg_query('BEGIN');
		if($lvinfo_obj->delete($lv,ATTR_SPRACHE_DE))
		{
			if($lvinfo_obj->delete($lv, ATTR_SPRACHE_EN))
			{
				if(!WriteLog($lvinfo_obj->lastqry,$user) || !WriteLog($lvinfo_obj,$user))
				{
					echo "<br>Fehler beim Schreiben des Log-files<br>";
				}
				pg_query('COMMIT');				
			}
			else 
			{
				pg_query('ROLLBACK');
				echo "<br>Fehler beim loeschen<br>";				
			}
		}
		else 
		{
			pg_query('ROLLBACK');
			echo "<br>Fehler beim loeschen<br>";
		}
	}

	if(isset($changestat) && isset($lv) && isset($_GET['lang']))
	{
		//Setzt die Spalte genehmigt auf den entsprechenden Wert
		//=Wenn Hackerl angeklickt wird

		$qry="SELECT genehmigt FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lv' AND sprache=";
		if($_GET['lang']=='de')
			$qry.="'".ATTR_SPRACHE_DE."'";
		else 
			$qry.="'".ATTR_SPRACHE_EN."'";

		if($result=pg_query($conn,$qry))
		{
			if($row=pg_fetch_object($result))
			{
				$wert = $row->genehmigt=='t'?'false':'true';
				$qry="UPDATE campus.tbl_lvinfo SET genehmigt=$wert WHERE lehrveranstaltung_id=$lv AND sprache=";
				if($_GET['lang']=='de')
					$qry.="'".ATTR_SPRACHE_DE."'";
				else 
					$qry.="'".ATTR_SPRACHE_EN."'";

				if(pg_query($conn,$qry))
					WriteLog($qry,$user);
				else 
					echo "Fehler beim Datenbankzugriff";
			}
			else 
				echo "Fehler beim Datenbankzugriff";
		}
		else 
			echo "Fehler beim Datenbankzugriff";  
	}
   
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../../skin/cis.css" rel="stylesheet" type="text/css">
<title>ECTS - LV INFO</title>
<style type="text/css">
<!--
td {
font-family:verdana,arial,helvetica;
font-size:10pt;
}

textarea {
font-family:verdana,arial,helvetica;
font-size:10pt;
border:1px dashed #000000;
}
//-->
</style>
<script language="JavaScript">
function ask() {
	if(confirm("Wollen sie diese LV-Information wirklich loeschen ?"))
	  return true;
	else
	  return false;
}
</script>
</head>
<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="3%">&nbsp;</td>
    <td>
    	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">&nbsp;LV Info - Freigabe</font></td>
	      </tr>	
		  <tr>
			   <td>
			      <table width='100%'  border='0' cellspacing='0' cellpadding='0'>
			         <tr>
			               <td width="85%">
				              &nbsp;
						    </td>
							<td>
								<ul>
								<li>&nbsp;<a href='index.php?<?php echo "stg=$stg&sem=".(isset($sem)?$sem:'')."&lv=$lv"?>'><font size='3'>Bearbeiten</font></a></li>
								<li>&nbsp;<a href='freigabe.php?<?php echo "stg=$stg&sem=".(isset($sem)?$sem:'')."&lv=$lv"?>'><font size='3'>Freigabe</font></a></li>
								<li>&nbsp;<a href='beispiele.html'><font size='3'>Beispiele</font></a></li>
								<li>&nbsp;<a href='terminologie.html'><font size='3'>Terminologie</font></a></li>
				 				</ul>
							</td>
			          </tr>
			       </table>
			   </td>
		  </tr>
		</table>
               
       <?php
       //DropDown Menues zur Auswahl von Studiengang und Semester anzeigen
       
       echo "<form name='auswFrm' action='$PHP_SELF' method='POST'>";
       echo "<input type='hidden' name='status' value='a'>";
       echo "<input type='hidden' name='lv' value='$lv'>";
       //stg Drop Down
       $qry = "SELECT distinct tbl_studiengang.studiengang_kz, kurzbzlang FROM campus.tbl_lvinfo, lehre.tbl_lehrveranstaltung, tbl_studiengang 
       			WHERE tbl_lvinfo.aktiv=true 
       			AND tbl_lvinfo.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
       			AND tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz
       			ORDER by kurzbzlang";
       if(!$result=pg_query($conn,$qry))
          die ('<center>Fehler bei einer Datenbankabfrage</center>');
       
       echo "Studiengang   <SELECT name='stg' onChange='javascript:window.document.auswFrm.status.value=\"changestg\";window.document.auswFrm.submit();'>";
       $firststg;
       $vorhanden=false;
       
       while($row=pg_fetch_object($result))
       {
       	   if(!isset($firststg))
       	      $firststg=$row->studiengang_kz; 
       	      
       	   if(!isset($stg))
       	      $stg=$row->studiengang_kz;
       	   
       	   if($stg==$row->studiengang_kz)
       	   {
       	      echo "<option value='$row->studiengang_kz' selected>$row->kurzbzlang</option>";
       	      $vorhanden=true;
       	   }
       	   else 
       	      echo "<option value='$row->studiengang_kz'>$row->kurzbzlang</option>";
       }
       echo "</SELECT>";
       
       if(!$vorhanden) //Wenn $stg einen Wert enthaelt der nicht in der Liste vorkommt wird der erste Eintrag der Liste ausgewaehlt
          $stg=$firststg;
          
       //Semester Drop Down
       $qry = "SELECT distinct semester FROM campus.tbl_lvinfo, lehre.tbl_lehrveranstaltung
       			WHERE tbl_lvinfo.aktiv=true 
       			AND tbl_lvinfo.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
       			AND tbl_lehrveranstaltung.studiengang_kz='$stg'
       			ORDER by semester";
       if(!$result=pg_query($conn,$qry))
          die ("<center>Fehler bei einer Datenbankabfrage</center>");
       
       echo " Semester   <SELECT name='sem' onChange='javascript:window.document.auswFrm.submit();'>";

       $firstsem;
       $vorhanden=false;
       
       while($row=pg_fetch_object($result))
       {
       	   if(!isset($firstsem))
       	      $firstsem = $row->semester;
       	      
       	   if(!isset($sem))
       	      $sem=$row->semester;
       	   
       	   if($sem==$row->semester)
       	   {
       	      echo "<option value='$row->semester' selected>$row->semester</option>";
       	      $vorhanden=true;
       	   }
       	   else 
       	      echo "<option value='$row->semester'>$row->semester</option>";
       }
       echo "</SELECT>";
       if(!$vorhanden) //Wenn $sem einen Wert enthaelt der nicht in der Liste vorkommt wird der erste Eintrag der Liste ausgewaehlt
           $sem=$firstsem;

       //Anzeigen der Liste mit den LV - Informationen
       ?> 
       <br><br>
        <table width="900"  border="0" cellspacing="0" cellpadding="0" style="border: 1px solid black">
	        <tr>
	          <td>
	              <table width="100%"  border="0" cellspacing="0" cellpadding="0">
	              <tr class='liste'>
	                 <th>x</th>
	                 <th>Lehrfach</th>
	                 <th>Bearbeitet von</th>
	                 <th>Update am</th>
	                 <th>Anzeigen</th>
	                 <th>Online<br>de &nbsp; en</th>
	              </tr>
	              
		             <?php 
						$qry="SELECT tbl_lehrveranstaltung.bezeichnung as bezeichnung, to_char(tbl_lvinfo.updateamum,'DD-MM-YYYY HH24:MI') as amum,tbl_lvinfo.updateamum as updateamum, tbl_lvinfo.updatevon as updatevon, 	* FROM campus.tbl_lvinfo JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE studiengang_kz=$stg AND semester=$sem AND tbl_lvinfo.aktiv=true AND tbl_lvinfo.sprache='".ATTR_SPRACHE_DE."' ORDER BY tbl_lehrveranstaltung.bezeichnung ASC";

		                if(!$result=pg_query($conn,$qry))
		                    die("<center>Fehler bei einer Datenbankabfrage</center>");

		                $i=-1;		                
		                while($row=pg_fetch_object($result))
		                {
							$i++;
							$qry1="SELECT tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lvinfo.updatevon as updatevon, * FROM campus.tbl_lvinfo JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE tbl_lvinfo.sprache='".ATTR_SPRACHE_EN."' AND lehrveranstaltung_id='$row->lehrveranstaltung_id'";
	
		                	if(!$result1=pg_query($conn,$qry1))
			                    die("<center>Fehler bei einer Datenbankabfrage</center>");

                            if(!$row1=pg_fetch_object($result1))
								die("<center>Fehler bei einer Datenbankabfrage</center>");
                               
                            $qry2="SELECT vorname, nachname FROM campus.vw_mitarbeiter WHERE uid='$row->updatevon'";
	
                            $bearbeitet=$row->updatevon;
		                	if($result2=pg_query($conn,$qry2))
			                   if($row2=pg_fetch_object($result2))
                                   $bearbeitet=$row2->vorname.' '.$row2->nachname;
                            echo "\n";   
		                	echo "<tr class='liste".($i%2)."'>"."\n";
		                    echo "<td align='center'><a href='$PHP_SELF?del=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id' onClick='return ask();'>Delete</a></td>"."\n";
		                    echo "<td align='center'>$row->bezeichnung</td>"."\n";
		                    //echo "<td align='center'>$row->studiensemester_kurzbz</td>"."\n";
		                    echo "<td align='center'>$bearbeitet</td>"."\n";
		                    echo "<td align='center'>".$row->amum."</td>"."\n";
		                    echo "<td align='center'><a href='#' onClick='javascript:window.open(\"preview.php?lv=$row->lehrveranstaltung_id&language=de\",\"Preview\",\"width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\");'><img src='../../../../skin/images/flagge-aut.gif' border=0 width=30 ></a>&nbsp;";
		                    echo "<a href='#' onClick='javascript:window.open(\"preview.php?lv=$row1->lehrveranstaltung_id&language=en\",\"Preview\",\"width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\");'><img src='../../../../skin/images/flagge-eng.gif' border=0 width=30 ></a></td>"."\n";
		                    echo "<td align='center'><input type='checkbox' onClick='javascript:window.location.href=\"$PHP_SELF?changestat=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id&lang=de\";' ".($row->genehmigt=='t'?'checked':'').">"."\n";
		                    echo "<input type='checkbox' onClick='javascript:window.location.href=\"$PHP_SELF?changestat=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id&lang=en\";' ".($row1->genehmigt=='t'?'checked':'')."></td>"."\n";
		                    echo "</tr>";
		                }
	             ?>
		             
		          </table>
		      </td>
		    </tr>
	    </table>
	</td>
   </tr>
</table>
</body>
</html>