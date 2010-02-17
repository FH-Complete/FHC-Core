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
/**
 * LV Details fuer CIS Seite
 * diese Datei wird von /cis/private/lehre/lesson.php inkludiert
 */
  if (!isset($db)) 
 {
	// ---------------- CIS Include Dateien einbinden
		require_once('../../../config/cis.config.inc.php');
	// ------------------------------------------------------------------------------------------
	//	Datenbankanbindung 
	// ------------------------------------------------------------------------------------------
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
 }
?>
<table class="tabcontent">
	<tr>
	<td class="tdvertical" align="center">

		<?php

		//Lehrveranstaltungsinformation
		   echo "<img src=\"../../../skin/images/button_i.jpg\" width=\"67\" height=\"45\"><br><strong>Lehrveranstaltungsinformation</strong><br>";
		   $qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lvid' AND genehmigt=true AND sprache='German' AND aktiv=true";
		   $need_br=false;

		   if($result=$db->db_query($qry))
		   {
		      if($db->db_num_rows($result)>0)
		      {
			     echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=de','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">Deutsch&nbsp;</a>";
			     $need_br=true;
		      }
		   }
		   $qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id='$lvid' AND genehmigt=true AND sprache='English' AND aktiv=true";
		   if($result=$db->db_query($qry))
		   {
		      if($db->db_num_rows($result)>0)
		      {
		      	 $row1=$db->db_fetch_object($result);
			     echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=en','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">Englisch</a>";
			     $need_br=true;
		      }
		   }

		   if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))
		   {
		   		if($need_br)
		   			echo "<br>";
		   		echo "<a href='ects/index.php?lvid=$lvid' target='_blank' class='Item'>Bearbeiten</a>";
		   }
		?>

    <p>&nbsp;</p>
		</td>
	    <td class="tdvertical" align="center">
		  <?php
			if (!isset($DOC_ROOT) || empty($DOC_ROOT))
				$DOC_ROOT='../../..';

		  	$dir_name=$DOC_ROOT.'/documents';
			if(!is_dir($dir_name))
			{
					exec('mkdir -m 775 "'.$dir_name.'"');
					exec('chown www-data:teacher "'.$dir_name.'"');
			}					
							
		  	/*
		  	if(!@is_dir($DOC_ROOT.'/documents'))
			{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents'.'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');				
			}
			*/					
					
		  //SEMESTERPLAN
		  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan';
			/*if(!is_dir($dir_name))
			{
					exec('mkdir -m 775 "'.$dir_name.'"');
					exec('chown www-data:teacher "'.$dir_name.'"');
			}*/
		  	$dest_dir = @dir($dir_name);
		  	if(!@is_dir($dest_dir->path))
			{
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan'))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan"');
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
				echo '<img src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
				echo '<strong>Semesterplan</strong>';
				echo '</a>';
			}
			else
			{
				echo '<img src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
				echo '<strong>Semesterplan</strong>';
			}

			if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
			{
				echo '<br><a class="Item" href="#" onClick="javascript:window.open(\'semupload.php?lvid='.$lvid.'\',\'_blank\',\'width=400,height=300,location=no,menubar=no,status=no,toolbar=no\');return false;">';
				echo "Upload</a>";

				echo '&nbsp;&nbsp;&nbsp;<a class="Item" href="semdownhlp.php" >';
			    echo 'Vorlage [hml]';
			    echo '</a>';
			    echo '&nbsp;<a class="Item" href="semdownhlp.php?format=doc" >';
			    echo '[doc]';
			    echo '</a>';
			    echo '&nbsp;<a href="#" onClick="showSemPlanHelp()";>(hilfe)</a>';
			}


	    ?>
		  <p>&nbsp;</p>
		</td>
		<td class="tdvertical" align="center">
		<?php
		//DOWNLOAD
		  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download';
			/*if(!is_dir($dir_name))
			{
					exec('mkdir -m 775 "'.$dir_name.'"');
					exec('chown www-data:teacher "'.$dir_name.'"');
			}*/
		  	$dest_dir = @dir($dir_name);
		  	if(!@is_dir($dest_dir->path))
			{
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download'))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download"');
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
				echo '<a href="'.$dest_dir->path.'/" target="_blank" class="Item">';
				echo '<img src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
				echo '<strong>Download</strong>';
				echo '</a>';
			}
			else
			{
				echo '<img src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
				echo '<strong>Download</strong>';
			}
			
			//Wenn user eine Lehrfachzuteilung fuer dieses Lehrfach hat wird
			//Ein Link zum Upload angezeigt und ein Link um das Download-Verzeichnis
			//als Zip Archiv herunterzuladen
			if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
			{
				echo "<br>".mb_strtolower("$kurzbz/$semester/$short/download");
				echo '<br>';
				echo "<a class='Item' target='_blank' href='upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short'>Upload</a>";
				echo '&nbsp;&nbsp;&nbsp;';
				if(isset($dir_empty) && $dir_empty == false)
					echo "<a class='Item' title='Alle Dateien im Download Verzeichnis als Zip-Archiv herunterladen' href='zipdownload.php?stg=$studiengang_kz&sem=$semester&short=$short' target='_blank'>Zip-Archiv</a>";
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

  <td class="tdvertical" align="center">

  <?php
    echo '<img src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
  	if($is_lector)
  	{
		//Anwesenheitsliste

		echo "<b><a href='anwesenheitsliste.php?stg_kz=$studiengang_kz&sem=$semester&lvid=$lvid&stsem=$angezeigtes_stsem' class='Item'>Anwesenheits- und Notenlisten</a></b><br>";
  	}

  	//Leistungsuebersicht
  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung';
	/*if(!@is_dir($dir_name))
	{
		exec('mkdir -m 775 "'.$dir_name.'"');
		exec('chown www-data:teacher "'.$dir_name.'"');
	}*/
  	$dest_dir = @dir($dir_name);
  	if(!@is_dir($dest_dir->path))
	{
		if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
		{
			exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
			exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
		}
		if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
		{
			exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
			exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
		}
		if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
		{
			exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
			exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
		}
		if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung'))
		{
			exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung"');
			exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung"');
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

	if(isset($dest_dir) && isset($dir_empty) && $dir_empty == false)
	{
		echo '<a href="'.$dest_dir->path.'" target="_blank">';
		echo '<strong>Leistungs&uuml;bersicht</strong>';
		echo '</a>';
	}
	else
	{
		echo '<strong>Leistungs&uuml;bersicht</strong>';
	}
   ?>

  <p>&nbsp;</p>
	  </td>
	  <td class="tdvertical" align="center">
		<?php
		//Keine Newsgroups fuer Studiengang '0' (Freifaecher) anzeigen
		if($studiengang_kz!='0')
		{
			echo '<a href="news://news.technikum-wien.at/'.mb_strtolower($stg_obj->kurzbzlang).'.'.$semester.'sem.'.mb_strtolower($short_short_name).'" class="Item">
					<img src="../../../skin/images/button_ng.jpg" width="67" height="45"><br>
					<strong>Newsgroups</strong>
				</a>';
		}
		?>
		<p>&nbsp;</p>
		</td>
		
		
		  <td class="tdvertical" align="center">
		<?php
		//FEEDBACK
		echo '<a href="feedback.php?lvid='.$lvid.'" target="_blank" class="Item"><img border="0" src="../../../skin/images/button_fb.jpg" width="67" height="45"><br><strong>Feedback</strong></a>';
		?>

		<p>&nbsp;</p>
		</td>
	</tr>
	<tr>
		

  <td class="tdvertical" align="center">
<?php

	$show=false;
	
	//wenn kein Moodle Kurs existiert dann KT anzeigen
	$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE 
			(lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."')
			OR
			(lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
								WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND 
								studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."'))";
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)==0)
		{
			$show=true;
		}
	}
	//wenn eine Kreuzerlliste existiert dann den Link immer anzeigen
	$qry = "SELECT 1 FROM campus.tbl_uebung 
			WHERE lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
									WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND
									studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."')";
	if($result = $db->db_query($qry))
	{
		if($db->db_num_rows($result)>0)
		{
			$show=true;
		}
	}
	
	if($show)
	{
		//Kreuzerltool
		if($is_lector)
		{
			if(isset($angezeigtes_stsem))
				$studiensem = '&stsem='.$angezeigtes_stsem;
			else
				$studiensem = '';
		
				echo '<a href="benotungstool/verwaltung.php?lvid='.$lvid.$studiensem.'" class="Item">
	    			<img src="../../../skin/images/button_kt.jpg" width="67" height="45"><br>
	    			<strong>&Uuml;bungstool<br>("Kreuzerl"-Tool)</strong></a><br>
	    			<a href="'.APP_ROOT.'cis/cisdocs/handbuch_benotungstool.pdf" class="Item" target="_blank">Handbuch [PDF]</a>';
		} 
		else 
		{
			if(isset($angezeigtes_stsem))
				$studiensem = '&stsem='.$angezeigtes_stsem;
			else
				$studiensem = '';

			echo '<a href="benotungstool/studentenansicht.php?lvid='.$lvid.$studiensem.'" class="Item">
	    			<img src="../../../skin/images/button_kt.jpg" width="67" height="45"><br>
	    			<strong>&Uuml;bungstool<br>("Kreuzerl"-Tool)</strong></a>';
	
		}
	}
	else 
	{
		if($is_lector)
		{
			echo '<a href="#" onclick="alert(\'Das &Uuml;bungstool kann nicht gleichzeitig mit Moodle verwendet werden.\nWenn Sie das &Uuml;bungstool verwenden wollen, m&uuml;ssen Sie den Moodle Kurs entfernen. Wenden Sie sich hierzu bitte an den Lektorensupport\');" class="Item">
	    			<img src="../../../skin/images/button_kt.jpg" width="67" height="45"><br>
	    			<strong>&Uuml;bungstool<br>("Kreuzerl"-Tool)</strong></a><br>
	    			<a href="'.APP_ROOT.'cis/cisdocs/handbuch_benotungstool.pdf" class="Item" target="_blank">Handbuch [PDF]</a>';
		}
	}
	
?>
    <p>&nbsp;</p>
	</td>
	<td class="tdvertical" align="center">
<?php 
	//Moodle
	$showmoodle=false;
	//Schauen ob Moodle fuer diesen Studiengang freigeschaltet ist
	$qry = "SELECT moodle FROM public.tbl_studiengang JOIN lehre.tbl_lehrveranstaltung USING(studiengang_kz) WHERE lehrveranstaltung_id='".addslashes($lvid)."'";
	if($result = $db->db_query($qry))
	{
		if($row = $db->db_fetch_object($result))
		{
			if($row->moodle=='t')
			{
				$showmoodle=true;
			}
		}
	}
	
	if(MOODLE)
	{
	//wenn bereits eine Kreuzerlliste existiert, dann den Moodle link nicht anzeigen
	$qry = "SELECT * FROM campus.tbl_uebung WHERE 
			lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
								WHERE lehrveranstaltung_id='".addslashes($lvid)."' 
								AND studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."')";
	
	if($result = $db->db_query($qry))
		if($db->db_num_rows($result)>0)
			$showmoodle=false;
	
	$mdlcourse = new moodle_course();
	$mdlcourse->getAll($lvid, $angezeigtes_stsem);
	if(count($mdlcourse->result)>0)
		$showmoodle=true;
	}
	else 
		$showmoodle=false;
	if($showmoodle)
	{
		$link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
		if(count($mdlcourse->result)>0)
		{
			if(!$is_lector)
			{
				$course = $mdlcourse->getCourse($lvid, $angezeigtes_stsem, $user);
				if(count($course)==1)
					$link = MOODLE_PATH.'course/view.php?id='.$course[0];
				else 
					$link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
			}
			else 
			{
				//$mdlcourse->getAll($lvid, $angezeigtes_stsem);
				if(count($mdlcourse->result)==1)
					$link = MOODLE_PATH.'course/view.php?id='.$mdlcourse->result[0]->mdl_course_id;
				else 
					$link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
			}
			echo '<a href="'.$link.'" target="_blank" class="Item" >
			    	<img src="../../../skin/images/button_moodle.jpg" width="68" height="45"><br>
			    	<strong>Moodle</strong></a><br>';
		}
		else 
		{
			echo '<img src="../../../skin/images/button_moodle.jpg" width="68" height="45"><br>
			    	<strong>Moodle</strong><br>';
		}
	    if($is_lector)
	    	echo '<a href="moodle_wartung.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item">Wartung</a>';			
	}
	else 
	{
		if($is_lector)
			echo '<a href="#" onclick="alert(\'Moodle kann nicht gleichzeitig mit dem &Uuml;bungstool verwendet werden.\nWenn Sie Moodle verwenden wollen, m&uuml;ssen Sie die &Uuml;bungen im &Uuml;bungstool entfernen\'); return false"  class="Item" >
			    	<img src="../../../skin/images/button_moodle.jpg" width="68" height="45"><br>
			    	<strong>Moodle</strong></a><br>';
	}
	?>
    <p>&nbsp;</p>
	</td>
	
<?php 
	//Gesamtnote
	if($is_lector)
	{
		echo '<td class="tdvertical" align="center">';
		echo '<a href="benotungstool/lvgesamtnoteverwalten.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item" >
    		<img src="../../../skin/images/button_endnote.jpg" width="68" height="45"><br>
    		<strong>Gesamtnote</strong></a><br>';
		echo '<p>&nbsp;</p>
			</td>';
	}
	?>
    
	<?php
		//Studentenupload 
		
			if($is_lector)
				echo '</tr><tr>';
			echo '<td class="tdvertical" align="center">';
			//Studentenabgabe
		  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload';
			/*if(!@is_dir($dir_name))
			{
				exec('mkdir -m 775 "'.$dir_name.'"');
				exec('chown www-data:student "'.$dir_name.'"');
			}*/
		  	$dest_dir = @dir($dir_name);
		  	if(!@is_dir($dest_dir->path))
			{
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
					exec('chown www-data:teacher "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'"');
				}
				if(!@is_dir($DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload'))
				{
					exec('mkdir -m 775 "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload"');
					exec('chown www-data:student "'.$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload"');
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
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
						  <strong>Studenten Abgabe</strong>
						  </a>";
				}
				else
				{
					echo "<a href=\"upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short\" target=\"_blank\">";
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
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
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
						  <strong>Studenten Abgabe</strong>";
				}
				else
				{
					echo "<a href=\"upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short\" target=\"_blank\">";
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
						  <strong>Studenten Abgabe</strong>
						  </a>";
				}
			}
			echo '<p>&nbsp;</p>
				</td>';
		
		  ?>
	</tr>
</table>