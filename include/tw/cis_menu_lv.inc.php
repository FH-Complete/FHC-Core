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
?>
<table class="tabcontent">
	<tr>
	<td class="tdvertical" align="center">

		<?php

		//Lehrveranstaltungsinformation

		   echo "<img src=\"../../../skin/images/button_i.jpg\" width=\"67\" height=\"45\"><br><strong>Lehrveranstaltungsinformation</strong><br>";

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
	    <td class="tdvertical" align="center">
		  <?php
		  

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
				echo '<img src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
				echo '<strong>Semesterplan</strong>';
				echo '</a>';
			}
			else
			{
				echo '<img src="../../../skin/images/button_semplan.jpg" width="67" height="45"><br>';
				echo '<strong>Semesterplan</strong>';
			}

			if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
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
			if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
			{
				echo "<br>".strtolower("$kurzbz/$term_id/$short/download");
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

  <td class="tdvertical" align="center">

  <?php
    echo '<img src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
  	if($is_lector)
  	{
		//Anwesenheitsliste

		echo "<b><a href='anwesenheitsliste.php?stg_kz=$course_id&sem=$term_id&lvid=$lvid&stsem=$angezeigtes_stsem' class='Item'>Anwesenheits- und Notenlisten</a></b><br>";
  	}

  	//Leistungsuebersicht
  	$dest_dir = @dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/leistung');

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
		if(!is_dir('../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/leistung'))
		{
			@exec('mkdir -m 775 "../../../documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/leistung"');
			exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/'.strtolower($kurzbz).'/'.$term_id.'/'.strtolower($short_short_name).'/leistung"');
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
		if($course_id!='0')
		{
			echo '<a href="news://cis.technikum-wien.at/'.strtolower($stg_obj->kurzbzlang).'.'.$term_id.'sem.'.strtolower($short_short_name).'" class="Item">
					<img src="../../../skin/images/button_ng.jpg" width="67" height="45"><br>
					<strong>Newsgroups</strong>
				</a>';
		}
		?>
		<p>&nbsp;</p>
		</td>
		
		<?php
		/*
			echo '<td class="tdvertical" align="center">'
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
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
						  <strong>Studenten Abgabe</strong>
						  </a>";
				}
				else
				{
					echo "<a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" target=\"_blank\">";
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
					echo "<a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" target=\"_blank\">";
					echo "<img src=\"../../../skin/images/button_ul.jpg\" width=\"67\" height=\"45\"><br>
						  <strong>Studenten Abgabe</strong>
						  </a>";
				}
			}
			echo '<p>&nbsp;</p>
				</td>';
			*/
		  ?>
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
	$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE 
			(lehrveranstaltung_id='".addslashes($lvid)."' AND studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."')
			OR
			(lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
								WHERE lehrveranstaltung_id='".addslashes($lvid)."' AND 
								studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."'))";
	if($result = pg_query($sql_conn, $qry))
	{
		if(pg_num_rows($result)==0)
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
		    			<strong>Benotungstool<br>("Kreuzerl"-Tool)</strong></a><br>
		    			<a href="lesson.php?handbuch=1&lvid='.$lvid.$studiensem.'" class="Item">Handbuch [PDF]</a>';
			} 
			else 
			{
				echo '<a href="benotungstool/studentenansicht.php?lvid='.$lvid.'" >
		    			<img src="../../../skin/images/button_kt.jpg" width="67" height="45"><br>
		    			<strong>"Kreuzerl"-Tool</strong></a>';
		
			}
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
	if($result = pg_query($sql_conn, $qry))
	{
		if($row = pg_fetch_object($result))
		{
			if($row->moodle=='t')
			{
				$showmoodle=true;
			}
		}
	}
	
	//wenn bereits eine Kreuzerlliste existiert, dann den Moodle link nicht anzeigen
	$qry = "SELECT * FROM campus.tbl_uebung WHERE 
			lehreinheit_id IN(SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
								WHERE lehrveranstaltung_id='".addslashes($lvid)."' 
								AND studiensemester_kurzbz='".addslashes($angezeigtes_stsem)."')";
	
	if($result = pg_query($sql_conn, $qry))
		if(pg_num_rows($result)>0)
			$showmoodle=false;
		
	if($showmoodle)
	{
		echo '<a href="'.MOODLE_PATH.'" target="_blank" class="Item" >
			    	<img src="../../../skin/images/button_moodle.jpg" width="68" height="45"><br>
			    	<strong>Moodle</strong></a><br>';
	    if($is_lector)
	    	echo '<a href="moodle_wartung.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item">Wartung</a>';			
	}
	?>
    <p>&nbsp;</p>
	</td>
	<td class="tdvertical" align="center">
<?php 
	//Gesamtnote
	if($is_lector)
	{
		echo '<a href="benotungstool/lvgesamtnoteverwalten.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item" >
    		<img src="../../../skin/images/button_endnote.jpg" width="68" height="45"><br>
    		<strong>Gesamtnote</strong></a><br>';
	}
	?>
    <p>&nbsp;</p>
	</td>
	</tr>
</table>