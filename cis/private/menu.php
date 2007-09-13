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
require_once('../config.inc.php');
require_once('../../include/globals.inc.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/funktion.class.php');
require_once('../../include/studiensemester.class.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/lehrveranstaltung.class.php');

//Connection Herstellen
if(!$db_conn = pg_pconnect(CONN_STRING))
	die('Fehler beim oeffnen der Datenbankverbindung');

$user=get_uid();

$rechte=new benutzerberechtigung($db_conn);
$rechte->getBerechtigungen($user);

$fkt=new funktion($db_conn);
$fkt->getAll($user);

$stg_obj = new studiengang($db_conn);

if($stg_obj->getAll('kurzbzlang', false))
{
	$stg = array();
	foreach($stg_obj->result as $row)
		$stg[$row->studiengang_kz] = $row->kurzbzlang;
}
else
	die('Fehler beim Auslesen der Studiengaenge');


if(check_lektor($user,$db_conn))
   $is_lector=true;
else
   $is_lector=false;
  
if(check_student($user,$db_conn))
   $is_student=true;
else
   $is_student=false;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript">
<!--
	__js_page_array = new Array();

    function js_toggle_container(conid)
    {
		if (document.getElementById)
		{
        	var block = "table-row";
			if (navigator.appName.indexOf('Microsoft') > -1)
				block = 'block';
            var status = __js_page_array[conid];
            if (status == null)
            	status=document.getElementById(conid).style.display; //status = "none";
            if (status == "none")
            {
            	document.getElementById(conid).style.display = block;
            	__js_page_array[conid] = "visible";
            }
            else
            {
            	document.getElementById(conid).style.display = 'none';
            	__js_page_array[conid] = "none";
            }
            return false;
     	}
     	else
     		return true;
  	}
//-->
</script>

</head>

<body>
<table class="tabcontent">
<tr>
	<td width="159" class='tdvertical' nowrap>
		<table class="tabcontent">
		<tr>
          <td nowrap><a class="HyperItem" href="../index.html" target="_top">&lt;&lt; HOME</a></td>
  		</tr>
  		<tr>
			<td>&nbsp;</td>
		</tr>
		<!-- ************* Meine CIS ******************* -->
  		<tr>
			<td nowrap><a class="MenuItem" href="?MeineCIS" onClick="return(js_toggle_container('MeineCIS'));" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mein CIS</a></td>
		</tr>

		<tr>
	       	<td nowrap>
		  	<table class="tabcontent" id="MeineCIS" style="display: visible;">
		  	<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="profile/index.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Profil</a></td>
			</tr>
			<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
			</tr>
			<?php			
			if ($is_student)
				echo '<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="profile/dokumente.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Dokumente</a></td>
			</tr>'
			?>		  	
		  	<tr>
				<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class='tdwrap'><a class="Item" href="lvplan/stpl_week.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-Plan</a></td>
			</tr>
			<?php			
			if ($is_student)
				echo '<tr>
			  	<td class="tdwidth10" nowrap>&nbsp;</td>
				<td class="tdwrap"><a class="Item" href="lehre/notenliste.php" target="content"><img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Leistungsbeurteilung</a></td>
			</tr>'
			?>
			
			<?php
			//Eigene LVs des eingeloggten Lektors anzeigen
			if($is_lector)
			{
				?>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/zeitwunsch.php?uid=<?php echo $user; ?>" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitw&uuml;nsche
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/lva_liste.php?uid=<?php echo $user; ?>" class="Item" target="content">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV-&Uuml;bersicht
				    	</a>
				    </td>
				</tr>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="?Location" class="MenuItem" onClick="return(js_toggle_container('MeineLVs'));">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV
				    	</a>
				    </td>
				</tr>
				<tr>
          			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class='tdwrap'>
		  			<table class="tabcontent" id="MeineLVs" style="display: visible;">
					<tr>
					  	<td class='tdwrap'>
							<ul style="margin-top: 0px; margin-bottom: 0px;">
							<?php
							$stsemobj = new studiensemester($db_conn);
							$stsem = $stsemobj->getAktorNext();
							$qry = "SELECT distinct tbl_lehrveranstaltung.bezeichnung,typ, tbl_studiengang.kurzbz, tbl_lehrveranstaltung.studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id
										FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_studiengang
								        WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
									        tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz AND
									        mitarbeiter_uid='$user' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem' ORDER BY typ, tbl_studiengang.kurzbz, semester, lehreverzeichnis";

							if($result = pg_query($db_conn,$qry))
							{
								while($row = pg_fetch_object($result))
								{
									if($row->studiengang_kz==0 AND $row->semester==0)
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">FF '.$row->lehreverzeichnis.'</a></li>';
									else
										echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="lehre/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">'.$stg[$row->studiengang_kz].' '.$row->semester.' '.$row->lehreverzeichnis.'</a></li>';
								}
							}
							else
								echo "Fehler beim Auslesen des Lehrfaches";
							?>
							</ul>
						</td>
					</tr>
					</table>
		  			</td>
				</tr>
			<?php
			}
			if ($rechte->isFix())
			{
				?>
				<tr>
					<td class="tdwidth10" nowrap>&nbsp;</td>
				    <td class='tdwrap'>
				    	<a href="profile/zeitsperre_days.php?days=12" target="content" class="MenuItem" onClick="js_toggle_container('Zeitsperren');">
				    		<img src="../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Zeitsperren
				    	</a>
				    </td>
				</tr>
				<tr>
          			<td class="tdwidth10" nowrap>&nbsp;</td>
					<td nowrap>
		  			<table class="tabcontent" id="Zeitsperren" style="display: none;">
					<tr>
					  	<td class='tdwrap'>
							<ul style="margin-top: 0px; margin-bottom: 0px;">
							<?php
							if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter'))
								echo '<li><a class="Item2" href="profile/resturlaub.php" target="content">Resturlaub</a></li>';
							if ($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('mitarbeiter') || $fkt->checkFunktion('stglstv')|| $fkt->checkFunktion('stgl') || $fkt->checkFunktion('ass'))
							{
								echo '<li><a class="Item2" href="profile/zeitsperre.php?fix=true" target="content">Fix-Angestellte</a></li>';
								echo '<li><a class="Item2" href="profile/zeitsperre.php?fix=true&lektor=true" target="content">Fixe Lektoren</a></li>';
							}
							$stge=$rechte->getStgKz('admin');
							foreach($stg_obj->result as $row)
								if (in_array($row->studiengang_kz,$stge))
									echo '<li><a class="Item2" href="profile/zeitsperre.php?funktion=lkt&stg_kz='.$row->studiengang_kz.'" target="content">Lektoren '.$row->kurzbzlang.'</a></li>';
							?>
							</ul>
						</td>
					</tr>
					</table>
		  			</td>
				</tr>

			<?php
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
