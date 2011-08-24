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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/*
	 @author Andres Oesterreicher
   @date 20.10.2005
   @brief Formular zum Freigeben der LV Informationen aus der tabelle tbl_lvinfo

   @edit	08-11-2006 Versionierung entfernt. Studiensemester = WS2007
  	 			03-01-2006 Anpassung an neue DB
*/
	
require_once('../../../../config/cis.config.inc.php');
require_once('../../../../include/basis_db.class.php');
require_once('../../../../include/functions.inc.php');
require_once('../../../../include/studiensemester.class.php');
require_once('../../../../include/lvinfo.class.php');
require_once('../../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p = new phrasen($sprache); 

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
$user = get_uid();
if(!check_lektor($user))
	die('<center>'.$p->t('global/keineBerechtigungFuerDieseSeite').'</center>');

	/* WriteLog($qry,$uid)
	 * @brief Schreib die Querys im format: uid - datum - qry ins LogFile
	 * @param $qry Query anweisung
	 *        $uid Username
	 * @return true wenn ok false wenn fehler beim oeffnen
	 */
	function WriteLog($qry,$uid)
	{
		if($fp=fopen(LOG_PATH.'lvinfo.log',"a"))
		{
			fwrite($fp,"\n");
			fwrite($fp,$uid." ". date("d.m.Y - H:i:s") . " ". $qry);
			fclose($fp);
			return true;
		}
		else
			return false;
	}

	
	$lv=trim((isset($_REQUEST['lv']) ? $_REQUEST['lv']:''));

	 //Studiengang der Angezeigt werden soll
	$stg=trim((isset($_REQUEST['stg']) ? $_REQUEST['stg']:''));
	//Semester das angezeigt werden soll
	$sem=trim((isset($_REQUEST['sem']) ? $_REQUEST['sem']:''));

	if(isset($_GET["lv"])) //Id des DS der freigegeben/nicht freigegeben werden soll
		$id=$_GET["lv"];

	if(isset($_GET["del"])) //Wenn diese Variable gesetzt ist dann wird DS mit $idde und $iden geloescht
		$del=$_GET["del"];

	if(isset($_GET["changestat"])) //Wenn diese Variable gesetzt ist dann wird DS mit $id freigegeben/nicht freigegeben
		$changestat=$_GET["changestat"];

	if(isset($_POST["status"]) && $_POST["status"] =='changestg')
		unset($sem);

	if(isset($del) && isset($lv))
	{
		//Loeschen der beiden Datensaetze

		$lvinfo_obj = new lvinfo();
		$db->db_query('BEGIN');
		if($lvinfo_obj->delete($lv))
		{
			if(!WriteLog($lvinfo_obj->lastqry,$user))
			{
				echo "<br>".$p->t('courseInformation/fehlerBeimSchreibenDesLog')."<br>";
			}
			$db->db_query('COMMIT');
		}
		else
		{
			$db->db_query('ROLLBACK');
			echo "<br>".$p->t('global/fehleraufgetreten')."<br>";
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

		if($result=$db->db_query($qry))
		{
			if($row=$db->db_fetch_object($result))
			{
				$wert = $row->genehmigt=='t'?'false':'true';
				$qry="UPDATE campus.tbl_lvinfo SET genehmigt=$wert WHERE lehrveranstaltung_id=$lv AND sprache=";
				if($_GET['lang']=='de')
					$qry.="'".ATTR_SPRACHE_DE."'";
				else
					$qry.="'".ATTR_SPRACHE_EN."'";

				if($db->db_query($qry))
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
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../../skin/style.css.php" rel="stylesheet" type="text/css">
<title><?php echo $p->t('courseInformation/ectsLvInfo');?></title>
<script language="JavaScript" type="text/javascript">
function ask() {
	return confirm("<?php echo $p->t('global/warnungWirklichLoeschen');?>");
}
</script>
</head>
<body>
<table class="tabcontent" id="inhalt">
  <tr>
    <td width="3%">&nbsp;</td>
    <td>
    	<table class="tabcontent">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">&nbsp;<?php echo $p->t('courseInformation/lvInfoFreigabe');?></font></td>
	      </tr>
		  <tr>
			   <td>
			      <table class="tabcontent">
			         <tr>
			               <td width="85%">
				              &nbsp;
						    </td>
							<td>
								<ul>
								<li>&nbsp;<a href='index.php?<?php echo "stg=$stg&sem=".(isset($sem)?$sem:'')."&lv=$lv"?>'><font size='3'><?php echo $p->t('global/bearbeiten');?></font></a></li>
								<li>&nbsp;<a href='freigabe.php?<?php echo "stg=$stg&sem=".(isset($sem)?$sem:'')."&lv=$lv"?>'><font size='3'><?php echo $p->t('courseInformation/freigabe');?></font></a></li>
								<li>&nbsp;<a href='beispiele.html'><font size='3'><?php echo $p->t('global/beispiele');?></font></a></li>
								<li>&nbsp;<a href='terminologie.html'><font size='3'><?php echo $p->t('courseInformation/terminologie');?></font></a></li>
				 				</ul>
							</td>
			          </tr>
			       </table>
			   </td>
		  </tr>
		</table>

       <?php
       //DropDown Menues zur Auswahl von Studiengang und Semester anzeigen

       echo "<form name='auswFrm' action='".$_SERVER['PHP_SELF']."' method='POST'>";
       echo "<input type='hidden' name='status' value='a'>";
       echo "<input type='hidden' name='lv' value='$lv'>";
       //stg Drop Down
       $qry = "SELECT distinct tbl_studiengang.studiengang_kz, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbzlang FROM campus.tbl_lvinfo, lehre.tbl_lehrveranstaltung, public.tbl_studiengang
       			WHERE tbl_lvinfo.aktiv=true
       			AND tbl_lvinfo.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
       			AND tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz
       			ORDER by kurzbzlang";
       if(!$result=$db->db_query($qry))
          die ('<center>Fehler bei einer Datenbankabfrage</center>');

       echo $p->t('global/studiengang')."   <SELECT name='stg' onChange='javascript:window.document.auswFrm.status.value=\"changestg\";window.document.auswFrm.submit();'>";
       //$firststg;
       $vorhanden=false;

       while($row=$db->db_fetch_object($result))
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
       if(!$result=$db->db_query($qry))
          die ("<center>".$p->t('global/fehleraufgetreten')."</center>");

       echo " ".$p->t('global/semester')."   <SELECT name='sem' onChange='javascript:window.document.auswFrm.submit();'>";

       //$firstsem;
       $vorhanden=false;

       while($row=$db->db_fetch_object($result))
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
        <table width="900" class="tabcontent2" style="border: 1px solid black">
	        <tr>
	          <td>
	              <table class="tabcontent">
	              <tr class='liste'>
	                 <th>x</th>
	                 <th><?php echo $p->t('lvaliste/lehrfach');?></th>
	                 <th><?php echo $p->t('courseInformation/bearbeitetVon');?></th>
	                 <th><?php echo $p->t('courseInformation/updateAm');?></th>
	                 <th><?php echo $p->t('global/anzeigen');?></th>
	                 <th><?php echo $p->t('courseInformation/online');?><br>de &nbsp; en</th>
	              </tr>

		             <?php
						$qry="SELECT *, tbl_lehrveranstaltung.bezeichnung as bezeichnung, to_char(tbl_lvinfo.updateamum,'DD-MM-YYYY HH24:MI') as amum,tbl_lvinfo.updateamum as updateamum, tbl_lvinfo.updatevon as updatevon FROM campus.tbl_lvinfo JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE studiengang_kz=$stg AND semester=$sem AND tbl_lvinfo.aktiv=true AND tbl_lvinfo.sprache='".ATTR_SPRACHE_DE."' ORDER BY tbl_lehrveranstaltung.bezeichnung ASC";

		                if(!$result=$db->db_query($qry))
		                    die("<center>Fehler bei einer Datenbankabfrage</center>");

		                $i=-1;
		                while($row=$db->db_fetch_object($result))
		                {
							$i++;
							$qry1="SELECT *, tbl_lehrveranstaltung.bezeichnung as bezeichnung, tbl_lvinfo.updatevon as updatevon FROM campus.tbl_lvinfo JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id) WHERE tbl_lvinfo.sprache='".ATTR_SPRACHE_EN."' AND lehrveranstaltung_id='$row->lehrveranstaltung_id'";

		                	if(!$result1=$db->db_query($qry1))
			                    die("<center>Fehler bei einer Datenbankabfrage</center>");

                            if(!$row1=$db->db_fetch_object($result1))
								die("<center>Fehler bei einer Datenbankabfrage</center>");

                            $qry2="SELECT vorname, nachname FROM campus.vw_mitarbeiter WHERE uid='$row->updatevon'";

                            $bearbeitet=$row->updatevon;
		                	if($result2=$db->db_query($qry2))
			                   if($row2=$db->db_fetch_object($result2))
                                   $bearbeitet=$row2->vorname.' '.$row2->nachname;
                            echo "\n";
		                	echo "<tr class='liste".($i%2)."'>"."\n";
		                    echo "<td align='center'><a href='".$_SERVER['PHP_SELF']."?del=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id' onClick='return ask();'>Delete</a></td>"."\n";
		                    echo "<td align='center'>$row->bezeichnung</td>"."\n";
		                    //echo "<td align='center'>$row->studiensemester_kurzbz</td>"."\n";
		                    echo "<td align='center'>$bearbeitet</td>"."\n";
		                    echo "<td align='center'>".$row->amum."</td>"."\n";
		                    echo "<td align='center'><a href='#' onClick='javascript:window.open(\"preview.php?lv=$row->lehrveranstaltung_id&language=de\",\"Preview\",\"width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\");'><img src='../../../../skin/images/flagge-aut.gif' border=0 width=30 ></a>&nbsp;";
		                    echo "<a href='#' onClick='javascript:window.open(\"preview.php?lv=$row1->lehrveranstaltung_id&language=en\",\"Preview\",\"width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes\");'><img src='../../../../skin/images/flagge-eng.gif' border=0 width=30 ></a></td>"."\n";
		                    echo "<td align='center'><input type='checkbox' onClick='javascript:window.location.href=\"".$_SERVER['PHP_SELF']."?changestat=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id&lang=de\";' ".($row->genehmigt=='t'?'checked':'').">"."\n";
		                    echo "<input type='checkbox' onClick='javascript:window.location.href=\"".$_SERVER['PHP_SELF']."?changestat=1&stg=$stg&sem=$sem&lv=$row->lehrveranstaltung_id&lang=en\";' ".($row1->genehmigt=='t'?'checked':'')."></td>"."\n";
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