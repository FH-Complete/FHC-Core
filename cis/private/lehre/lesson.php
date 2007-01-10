<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
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
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once('../../../include/studiensemester.class.php');
    require_once('../../../include/lehrveranstaltung.class.php');
    require_once('../../../include/studiengang.class.php');
    
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');
    
	$user = get_uid();
	
	$user_is_allowed_to_upload=false;
	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
	else 
	   $is_lector=false;
		
	if(!isset($_GET['lvid']))
		die('Fehlerhafte Parameteruebergabe');
	else 
		$lvid = addslashes($_GET['lvid']);
		
	$lv_obj = new lehrveranstaltung($sql_conn);
	$lv_obj->load($lvid);
	$lv=$lv_obj;
	
	$course_id = $lv->studiengang_kz;
	$term_id = $lv->semester;
	$short = $lv->lehreverzeichnis;
	
	$stg_obj = new studiengang($sql_conn);
	$stg_obj->load($lv->studiengang_kz);
	
	$kurzbz = $stg_obj->kurzbz;
	
	$short_name = $lv->bezeichnung;
	//$fachbereich_id = $row->fachbereich_id;
	$short_short_name = $lv->lehreverzeichnis;
	
	$rechte = new benutzerberechtigung($sql_conn);
	$rechte->getBerechtigungen($user);
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<body>
<table border="0" cellspacing="0" cellpadding="0" height="100%" width="100%">
	<tr>
		<td width="10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;
		<?php
		echo $lv_obj->bezeichnung;
		
		$qry = "SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) WHERE lehrveranstaltung_id='$lvid' ORDER BY ende DESC LIMIT 1";

		if($result_stsem=pg_query($sql_conn, $qry))
		{
			if(pg_num_rows($result_stsem)<=0)
			{
				echo '</font></td>
	                  </tr>
	                  <tr>
		              <td valign="top">&nbsp;</td>
		              <td>';
				echo 'Derzeit sind keine Lektoren f&uuml;r dieses Fach zugeteilt.';
			}
			else 
			{						
				$row_stsem=pg_fetch_object($result_stsem);
			    $angezeigtes_stsem=$row_stsem->studiensemester_kurzbz;
			    
			    echo "&nbsp;($angezeigtes_stsem)";
			    echo '</font></td>
	                  </tr>
	                  <tr>
		              <td valign="top">&nbsp;</td>
		              <td>';
				
			    $qry = "SELECT distinct vorname, nachname, tbl_benutzer.uid as uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person WHERE tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_person.person_id=tbl_benutzer.person_id AND lehrveranstaltung_id='$lvid' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND tbl_person.aktiv=true AND studiensemester_kurzbz='$angezeigtes_stsem' ORDER BY nachname, vorname";
								
				if(!$result = pg_query($sql_conn, $qry))
				{
					echo 'Es konnten keine Lektoren zugeordnet werden';
				}
				else 
				{
					$num_rows_result = pg_num_rows($result);
					
					if(!($num_rows_result > 0))
					{
						echo 'Derzeit sind keine Lektoren f&uuml;r dieses Fach zugeteilt.';
					}
					else
					{
						$i=0;
						while($row_lector = pg_fetch_object($result))
						{	
							$i++;						
							if($user==$row_lector->uid)
								$user_is_allowed_to_upload=true;
								
							echo '<a class="Item2" href="mailto:'.$row_lector->uid.'@technikum-wien.at">'.$row_lector->vorname.' '.$row_lector->nachname.'</a>';
							if($i!=$num_rows_result)
								echo ', ';
						}
					}
				}
			}
		}
		?></td>
	</tr>
	<tr>
		<td valign="top">&nbsp;</td>
		<td valign="top">&nbsp;</td>
	</tr>	
	<tr>
		<td valign="top">&nbsp;</td>
		<td valign="top">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
			    <td valign="top" align="center">
				  <?php
				  //Berechtigungen auf Fachbereichsebene
				  $qry = "SELECT distinct fachbereich_kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrfach USING (lehrfach_id) WHERE lehrveranstaltung_id='$lvid'";
				  if(isset($angezeigtes_stsem) && $angezeigtes_stsem!='')
				  	$qry .= " AND studiensemester_kurzbz='$angezeigtes_stsem'";
				  	
				  if($result = pg_query($sql_conn, $qry))
				  {
				  	while($row = pg_fetch_object($result))
				  	{
				  		if($rechte->isBerechtigt('lehre',null,null,$row->fachbereich_kurzbz))
				  			$user_is_allowed_to_upload=true;
				  	}
				  }
				  
				  //SEMESTERPLAN
				  	$dest_dir = @dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/semesterplan');
					
				  	if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/'.strtolower($kurzbz))) 
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/semesterplan'))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/semesterplan"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/semesterplan"');
						}
					}
					
					if($dest_dir)
					{
						$dir_empty = true;
						
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
					}
					
					if(isset($dir_empty) && $dir_empty == false)
					{
						echo '<a href="'.$dest_dir->path.'/" target="_blank">';
						echo '<img border="0" src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
						echo '<strong>Semesterplan</strong>';
						echo '</a>';
					}
					else
					{						
						echo '<img border="0" src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
						echo '<strong>Semesterplan</strong>';
					}
																
					if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
					{
						echo '<br><a class="Item" href="#" onClick="javascript:window.open(\'semupload.php?lvid='.$lvid.'\',\'_blank\',\'width=400,height=300,location=no,menubar=no,status=no,toolbar=no\');return false;">';
						echo "Upload</a>";
																
						echo '&nbsp;&nbsp;&nbsp;<a class="Item" href="semdownhlp.php" >';
					    echo 'Vorlage';
					    echo '</a>';
					}
					

			    ?>
				  <p>&nbsp;</p>
				</td>
				<td valign="top" align="center">
				<?php
				//DOWNLOAD
					$dest_dir = @dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/download');
					
					if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/'.strtolower($kurzbz)))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/download'))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/download"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/download"');
						}
					}
					
					if($dest_dir)
					{
						$dir_empty = true;
						
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
					}
										
					if(isset($dir_empty) && $dir_empty == false)
					{
						echo '<a href="'.$dest_dir->path.'/" target="_blank">';
						echo '<img border="0" src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
						echo '<strong>Download</strong>';
						echo '</a>';
					}
					else
					{						
						echo '<img border="0" src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
						echo '<strong>Download</strong>';
					}
					
					//Wenn user eine Lehrfachzuteilung fuer dieses Lehrfach hat wird 
					//Ein Link zum Upload angezeigt und ein Link um das Download-Verzeichnis
					//als Zip Archiv herunterzuladen	
					if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
					{
						echo '<br>';
						echo "<a class='Item' target='_blank' href='upload.php?course_id=$course_id&term_id=$term_id&short=$short'>Upload</a>";
						echo '&nbsp;&nbsp;&nbsp;';
						if(isset($dir_empty) && $dir_empty == false)
							echo "<a class='Item' title='Alle Dateien im Download Verzeichnis als Zip-Archiv herunterladen' href='zipdownload.php?stg=$course_id&sem=$term_id&short=$short' target='_blank'>Zip-Archiv</a>";
						else 
							echo "Zip-Archiv";
					}
			    ?>
			      <p>&nbsp;</p>
			    </td>
			    <td>		
			    </td>
			</tr>
			<tr>
				
          <td valign="top" align="center">
          
          <?php
          	if($is_lector)
          	{
				//Anwesenheitsliste	
				echo '<img border="0" src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
				echo "<b><a href='anwesenheitsliste.php?stg_kz=$course_id&sem=$term_id&lvid=$lvid' class='Item'>Anwesenheits- und Notenlisten</a></b>";
          	}

		   ?>
          
          <p>&nbsp;</p>
			  </td>
				<td valign="top" align="center">
				<?php
				//Studentenabgabe
					$dest_dir = @dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/upload');
			
					if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/'.strtolower($kurzbz)))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/upload'))
						{
							@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/upload"');
							exec('sudo chown www-data:mysql "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/upload"');
						}
					}
					
					if($dest_dir)
					{
						$dir_empty = true;
						
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
					}
					
					if(isset($dir_empty) && $dir_empty == false)
					{
						if($is_lector > 0)
						{
							$islector = true;
						}
						else
						{
							$islector = false;
						}
						
						if($islector == true)
						{
							echo "<a href=\"lector_choice.php?lvid=$lvid\" target=\"_blank\">";
							echo "<img border=\"0\" src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
								  <strong>Studenten Abgabe</strong>
								  </a>";
						}
						else
						{
							echo "<a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" target=\"_blank\">";
							echo "<img border=\"0\" src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
								  <strong>Studenten Abgabe</strong>
								  </a>";
						}
					}
					else
					{
						if($is_lector > 0)
						{
							$islector = true;
						}
						else
						{
							$islector = false;
						}
						
						if($islector == true)
						{
							echo "<img border=\"0\" src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
								  <strong>Studenten Abgabe</strong>";
						}
						else
						{
							echo "<a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" target=\"_blank\">";
							echo "<img border=\"0\" src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
								  <strong>Studenten Abgabe</strong>
								  </a>";
						}
					}	
				  ?>
				  <p>&nbsp;</p>
				</td>
			</tr>
			<tr>
				<td valign="top" align="center">
				<?php
				//FEEDBACK				    
				echo '<a href="feedback.php?lvid='.$lvid.'" target="_blank"><img border="0" src="../../../skin/images/button_fb.jpg" width="67" height="45"><br><strong>Feedback</strong></a>';
				?>
				
				<p>&nbsp;</p>
				</td>
				
          <td valign="top" align="center"> 
	  <?php if($is_lector) { ?>
	  <a href="kreuzerltool/verwaltung.php?<?php echo "lvid=$lvid"?>" > 
            <img src="../../../skin/images/button_kt.jpg" border="0" width="67" height="45"><br>
            <strong>"Kreuzerl"-Tool</strong></a>
	    <?php } else { ?>
	  <a href="kreuzerltool/result_student.php?<?php echo "course_id=$course_id&term_id=$term_id&short=$short"?>" > 
            <img src="../../../skin/images/button_kt.jpg" border="0" width="67" height="45"><br>
            <strong>"Kreuzerl"-Tool</strong></a>

	    <?php } ?>
            <p>&nbsp;</p>
				</td>
			</tr>
			<tr>
				<td valign="top" align="center">
				
				<?php
				
				//Lehrveranstaltungsinformation
				
				   echo "<img border=\"0\" src=\"../../../skin/images/button_i.jpg\" width=\"67\" height=\"45\"><br><strong>Lehrveranstaltungsinformation</strong><br>";
				   
				   $qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lvid' AND genehmigt=true AND sprache='German' AND aktiv=true";
				   $need_br=false;
				   
				   if($result=pg_query($sql_conn,$qry))
				   { 
				      if(pg_num_rows($result)>0)
				      {				      	 
					     echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=de','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">Deutsch&nbsp;</a>";
					     $need_br=true;
				      }
				   }
				   $qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lvid' AND genehmigt=true AND sprache='English' AND aktiv=true";
				   if($result=pg_query($sql_conn,$qry))
				   { 
				      if(pg_num_rows($result)>0)
				      {
				      	 $row1=pg_fetch_object($result);
					     echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=en','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">Englisch</a>";
					     $need_br=true;
				      }
				   }
				   
				   if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id))
				   {
				   		if($need_br)
				   			echo "<br>";
				   		echo "<a href='ects/index.php?lvid=$lvid' target='_blank' class='Item'>Bearbeiten</a>";
				   }
				?>
								
            <p>&nbsp;</p>
				</td>
				<td valign="top" align="center">
				<a href="<?php
				  			echo 'news://cis.technikum-wien.at/'.strtolower($kurzbz).'.'.$term_id.'sem.'.strtolower($short_short_name);
				  		   ?>">
				<img border="0" src="../../../skin/images/button_ng.jpg" width="67" height="45"><br>
				<strong>Newsgroups</strong>
				</a>
				<p>&nbsp;</p>
				</td>
			</tr>
			
		</table>
		</td>
		<td width="30">&nbsp;</td>
	</tr>
</table>
</body>
</html>
