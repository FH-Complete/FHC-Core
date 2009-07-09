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

	require_once('../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
			
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once('../../../include/studiensemester.class.php');
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/lehrveranstaltung.class.php');

    $cutlength=10;
	// Variablen setzen
	$user = get_uid();

	$rechte=new benutzerberechtigung();
	if (!$rechte->getBerechtigungen($user))
		die('Fehler beim Berechtigungen lesen vom Anwender '.$user);
	if(check_lektor($user))
       $is_lector=true;
    else
       $is_lector=false;

	$lvid=(isset($_REQUEST['lvid'])?$_REQUEST['lvid']:(isset($_REQUEST['lvid'])?$_REQUEST['lvid']:null));
	$studiengang_kz=(isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:(isset($_REQUEST['studiengang_kz'])?$_REQUEST['studiengang_kz']:null));
	$semester=(isset($_REQUEST['semester'])?$_REQUEST['semester']:(isset($_REQUEST['semester'])?$_REQUEST['semester']:null));
	function CutString($strVal, $limit)
	{
		if(strlen($strVal) > $limit+3)
		{
			return substr($strVal, 0, $limit) . "...";
		}
		else
		{
			return $strVal;
		}
	}

	if(is_null($studiengang_kz) && is_null($semester))
	{
		$studiengang_kz = 227;
		$semester = 1;
		if(!$is_lector)
		{
			$sql_query = "SELECT studiengang_kz, semester FROM campus.vw_student WHERE uid='$user' LIMIT 1";
			$result_student = $db->db_query($sql_query);
			$num_rows_student = $db->db_num_rows($result_student);
			if($num_rows_student > 0)
			{
				$row = $db->db_fetch_object($result_student, 0);
				$studiengang_kz = $row->studiengang_kz;
				$semester = $row->semester;
			}
		}
	}
	// Plausib Studiengang und Semester
	if(is_null($studiengang_kz) || $studiengang_kz==0)
		$studiengang_kz = 227;
	if(is_null($semester) || $semester==0)
		$semester = 1;
	
	if (!$stg_obj = new studiengang())
		die('Fehler beim Studiengang er&ouml;ffnen !');
	if($stg_obj->getAll(null,false))
	{
		$stg = array();
		foreach($stg_obj->result as $row)
			$stg[$row->studiengang_kz] = $row->kurzbzlang;
	}
	else
		echo "Fehler beim Auslesen der Studiengaenge";
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">
<!--
__js_page_array = new Array();

function js_toggle_container(conid)
{
	if (document.getElementById)
	{
		var block = "table-row";

		if (navigator.appName.indexOf('Microsoft') > -1)
		{
			block = 'block';
		}

		var status = __js_page_array[conid];
		if (status == null)
		{
			status = "none";
		}

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
   {
     return true;
   }
}
  //-->
</script>

<script language="JavaScript" type="text/javascript">
<!--
	function MM_jumpMenu(targ, selObj, restore)
	{
	  eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

	  if(restore)
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
  //-->
</script>

<?php
	echo '<script language="JavaScript" type="text/javascript">';
		echo '	parent.content.location.href="pinboard.php?studiengang_kz='.$studiengang_kz.'&semester='.$semester.'"';
	echo '</script>';
?>
</head>

<body>
<table class="tabcontent">
  <tr>
    <td width="159" class="tdvertical" nowrap>
	  <table class="tabcontent" frame="rhs">
	    <form method="post" action="menu.php">
		<tr>
          <td class="tdwrap"><a class="HyperItem" href="../../index.php" target="_top">&lt;&lt; HOME</a></td>
  		</tr>
		<tr>
          <td class="tdwrap">&nbsp;</td>
  		</tr>
		<tr>
		  <td>
		  	<table class="tabcontent">
			  <tr>
			  	<td width="81" class="tdwrap">Studiengang: </td>
			  	<td class="tdwrap">
			  	<select name="course" onChange="MM_jumpMenu('self',this,0)">
				<?php

					$stg_obj = new studiengang();
					$stg_obj->getAll('typ, kurzbz');

					$sel_kurzbzlang='';
					foreach($stg_obj->result as $row)
					{
						if($row->studiengang_kz!=0)
						{
							if(isset($studiengang_kz) AND $studiengang_kz == $row->studiengang_kz)
							{
								echo '<option value="menu.php?studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'" selected>'.$row->kuerzel .' ('.$row->kurzbzlang.')</option>';
								$sel_kurzbzlang=$row->kurzbzlang;
							}
							else
							{
								echo '<option value="menu.php?studiengang_kz='.$row->studiengang_kz.'&semester='.$semester.'">'.$row->kuerzel .' ('.$row->kurzbzlang.')</option>';
							}
						}
					}
				?>
			  	</select>
			  	</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap">&nbsp;</td>
			  </tr>
			  <tr>
			  	<td class="tdwrap">Semester: </td>
			  	<td class="tdwrap">
			  	<select name="term" onChange="MM_jumpMenu('self',this,0)">
				<?php
					$short = 'Fehler Stg.Kz '.$studiengang_kz;
				    $max = 1;
					if ($stg_obj=new studiengang($studiengang_kz))
					{
						$short = $stg_obj->kuerzel;
					    $max = $stg_obj->max_semester;
					}
				    if($semester>$max)
				       $semester=1;
					for($i=0;$i<$max;$i++)
					{
						if(($i+1)==$semester)
						   echo '<option value="menu.php?studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'" selected >'.($i+1).'. Semester</option>';
						else
						   echo '<option value="menu.php?studiengang_kz='.$studiengang_kz.'&semester='.($i+1).'">'.($i+1).'. Semester</option>';

					}
				?>
			  	</select>
			  	</td>
			  </tr>
			</table>
		  </td>
		</tr>
		</form>
		<tr>
		  <td class="tdwrap">&nbsp;</td>
		</tr>

		<?php
			$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung where studiengang_kz='".addslashes($studiengang_kz)."' AND semester='".addslashes($semester)."' AND aktiv AND lehre ORDER BY orgform_kurzbz DESC, bezeichnung";
			$lastform=null;
			if($result = $db->db_query($qry))
			{
				while($row = $db->db_fetch_object($result))
				{
					if($row->orgform_kurzbz!=$lastform)
					{
						$qry_orgform = "SELECT * FROM bis.tbl_orgform WHERE orgform_kurzbz='$row->orgform_kurzbz'";
						if($result_orgform = $db->db_query($qry_orgform))
						{
							if($row_orgform = $db->db_fetch_object($result_orgform))
								echo "<tr><td><b>$row_orgform->bezeichnung</b></td></tr>";			
						}
						$lastform=$row->orgform_kurzbz;						
					}
					echo '<tr>';
					echo '	<td class="tdwrap"><ul style="margin: 0px; padding: 0px; padding-left: 20px;">';
					echo "<li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".CutString($row->bezeichnung, 21).' '.$row->lehrform_kurzbz."</a></li>";
					echo '	</ul></td>';
					echo '</tr>';
				}
			}
			echo '<tr>';
			echo '	<td class="tdwrap">&nbsp;</td>';
			echo '</tr>';
			
			//Zusatzmenue nur Anzeigen wenn im Config angegeben
			if(CIS_EXT_MENU)
			{
				if(!$is_lector)
				{
					echo '	<tr>
					    <td class="tdwrap">
					    	<a href="?Location" class="MenuItem" onClick="return(js_toggle_container(\'MeineLVs\'));">
					    		<img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV
					    	</a>
					    </td>
					</tr>

					<tr>
						<td class="tdwrap">
			  			<table class="tabcontent" id="MeineLVs" style="display: none;">
						<tr>
						  	<td class="tdwrap">
								<ul style="margin-top: 0px; margin-bottom: 0px;">';
								
								if ($stsemobj = new studiensemester())
								{
									$stsem = $stsemobj->getAktorNext();
									$qry = "SELECT distinct lehrveranstaltung_id, bezeichnung, studiengang_kz, semester, lehre, lehreverzeichnis from campus.vw_student_lehrveranstaltung WHERE uid='$user' AND studiensemester_kurzbz='$stsem' AND lehre=true AND lehreverzeichnis<>'' ORDER BY studiengang_kz, semester, bezeichnung";
									if($result = $db->db_query($qry))
									{
										while($row = $db->db_fetch_object($result))
										{
											if($row->studiengang_kz==0 && $row->semester==0) // Freifach
												echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="../freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">FF '.CutString($row->bezeichnung, $cutlength).'</a></li>';
											else
												echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">'.$stg[$row->studiengang_kz].$row->semester.' '.CutString($row->bezeichnung, $cutlength).'</a></li>';
										}
									}
									else
										echo "Fehler beim Auslesen der LV";
								}
								else
								{
									echo "Fehler Semester beim Auslesen der LV";
								}		
					echo '
								</ul>
							</td>
						</tr>
						</table>
			  			</td>
					</tr>';
				}
	
				//Eigenen LV des eingeloggten Lektors anzeigen
			if($is_lector || $rechte->isBerechtigt('admin'))
			{
			?>
			<tr>
	          <td class="tdwrap"><a href="?Eigene" class="MenuItem" onClick="return(js_toggle_container('Eigene'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Meine LV</a></td>
	  		</tr>
			<tr>
	          <td class="tdwrap">
			  	<table class="tabcontent" id="Eigene" style="display: none;">
				  <tr>
				  	<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap">
					<ul style="margin-top: 0px; margin-bottom: 0px;">
					<?php
					
					if ($stsemobj = new studiensemester())
					{
						$stsem = $stsemobj->getAktorNext();
						$qry = "SELECT distinct bezeichnung, studiengang_kz, semester, lehreverzeichnis, tbl_lehrveranstaltung.lehrveranstaltung_id  FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter
					        WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
					        tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					        mitarbeiter_uid='$user' AND tbl_lehreinheit.studiensemester_kurzbz='$stsem'";

						if($result = $db->db_query($qry))
						{
							while($row = $db->db_fetch_object($result))
							{
								if($row->studiengang_kz==0 AND $row->semester==0)
								{
									echo '<li><a class="Item2" title="'.$row->bezeichnung.'" href="../freifaecher/lesson.php?lvid='.$row->lehrveranstaltung_id.'" target="content">FF '.CutString($row->lehreverzeichnis, $cutlength).' ?</a></li>';
								}	
								else
								{
									$stg_obj = new studiengang();
									$stg_obj->load($row->studiengang_kz);
									$kurzbz = $stg_obj->kuerzel.'-'.$row->semester;
									// Altes Kuerzel $kurzbz=$stg[$row->studiengang_kz].$row->semester;
									echo "<li><a class=\"Item2\" title=\"".$row->bezeichnung."\" href=\"lesson.php?lvid=$row->lehrveranstaltung_id\" target=\"content\">".$kurzbz.' '.CutString($row->bezeichnung, $cutlength)."</a></li>";
								}	
							}
						}
						else
							echo "Fehler beim Auslesen des Lehrfaches";
					}
					else
					{
						echo "Fehler Semester beim Auslesen der LV";
					}	
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
			<tr>
	          <td class="tdwrap"><a class="MenuItem" href="pinboard.php?studiengang_kz=<?php echo $studiengang_kz; ?>&semester=<?php echo $semester; ?>" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboard</a></td>
	  		</tr>
	  		<tr>
	  			<td class="tdwrap">
	  		<?php
	                	$path = '../../../documents/'.strtolower($short).'/download';
						if(!$dest_dir = is_dir($path))
						{
	
							if(!is_dir($path))
							{
								if(!is_dir('../../../documents/'.strtolower($short)))
									exec('mkdir -m 775 "../../../documents/'.strtolower($short).'"');
								exec('mkdir -m 775 "../../../documents/'.strtolower($short).'/download"');
								exec('sudo chgrp teacher ../../../documents/'.strtolower($short).'/download');
							}
						}
						if(is_dir($path))
						{
							$dest_dir = @dir($path);
							echo '<a href="'.$dest_dir->path.'/" class="MenuItem" target="_blank"><img src="../../../skin/images/seperator.gif">&nbsp;Allgemeiner Download</a>';
						}
					?>
				</td>
			
			<tr>
	          <td class="tdwrap"><a href="?Info &amp; Kommunikation" class="MenuItem" onClick="return(js_toggle_container('Info &amp; Kommunikation'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Info &amp; Kommunikation</a></td>
	  		</tr>
			<tr>
	          <td class="tdwrap">
			  	<table class="tabcontent" id="Info &amp; Kommunikation" style="display: none;">
				  <tr>
				  	<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="../lvplan/" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Stundenplan</a></td>
				  </tr>
		    	  <tr>
				  	<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="https://webmail.technikum-wien.at" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webmail</a></td>
				  </tr>
				  <tr>
				  	<td class="tdwidth10" nowrap>&nbsp;</td>
					<td class="tdwrap"><a class="Item" href="../../public/faq_upload.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;FAQ</a></td>
				  </tr>
				</table>
			  </td>
	  		</tr>
			<?php
				if($is_lector || $rechte->isBerechtigt('admin'))
				{
					echo '<tr>';
					echo '  <td class="tdwrap"><a href="?Lektorenbereich" class="MenuItem" onClick="return(js_toggle_container(\'Lektorenbereich\'));"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Lektorenbereich</a></td>';
					echo '</tr>';
					echo '<tr>';
					echo '  <td class="tdwrap">';
					echo '  	<table class="tabcontent" id="Lektorenbereich" style="display: none;">';
	
	
					echo '	  <tr>';
					echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
					echo '		<td class="tdwrap"><a class="Item" href="ects/index.php?stg='.$studiengang_kz.'&amp;sem='.$semester.'&amp;lvid='.$lvid.'" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;LV Info</a></td>';
					echo '	  </tr>';
	
	
					echo '	  <tr>';
					echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
					echo '		<td class="tdwrap"><a class="Item" href="fernlehrunterlagen.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Fernlehrunterlagen</a></td>';
					echo '	  </tr>';
					echo '	  <tr>';
					echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
					echo '		<td class="tdwrap"><a class="Item" href="dokumentenvorlagen.html" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Vorlagen f&uuml;r die<br>&nbsp;&nbsp;&nbsp;Dokumentenerstellung</a></td>';
					echo '	  </tr>';
					echo '	  <tr>';
					echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
					echo '		<td class="tdwrap"><a class="Item" href="pinboardverwaltung.php?studiengang_kz='.$studiengang_kz.'&amp;semester='.$semester.'&amp;lvid='.$lvid.'" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Pinboardverwaltung</a></td>';
					echo '	  </tr>';
					echo '	  <tr>';
					echo '	  	<td class="tdwidth10" nowrap>&nbsp;</td>';
					echo '		<td class="tdwrap"><a class="Item" href="upload.php" target="_blank"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Webupload</a></td>';
					echo '	  </tr>';
					echo '	</table>';
					echo '  </td>';
					echo '</tr>';
				}
			?>
			<tr>
	          <td class="tdwrap"><a class="MenuItem" href="../mailverteiler.php?kbzl=<?php echo $sel_kurzbzlang.'#'.$studiengang_kz; ?>" target="content"><img src="../../../skin/images/menu_item.gif" width="7" height="9">&nbsp;Mailverteiler</a></td>
	  		</tr>
	  		<?php
			}
		?>
	  </table>
	</td>
  </tr>
</table>
</body>
</html>
