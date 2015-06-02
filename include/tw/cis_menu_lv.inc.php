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

echo '
<table class="tabcontent" id="lvmenue">
	<tr>';

$eintraegeprozeile=0;

function checkZeilenUmbruch()
{
	global $eintraegeprozeile;

	if($eintraegeprozeile>=3)
	{
		echo '</tr><tr>';
		$eintraegeprozeile=0;
	}
}

	if(!defined('CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_LVINFO_ANZEIGEN)
	{
		// ** Lehrveranstaltungsinformation
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo "<img class=\"lv\" src=\"../../../skin/images/button_lvinfo.png\"><strong><br>".$p->t('lehre/lehrveranstaltungsinformation')."</strong><br>";
		$qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND genehmigt=true AND sprache='".ATTR_SPRACHE_DE."' AND aktiv=true";
		$need_br=false;

		if($result=$db->db_query($qry))
		{
			if($db->db_num_rows($result)>0)
			{
				 echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=de','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">".$p->t('global/deutsch')."&nbsp;</a>";
				 $need_br=true;
			}
		}
		$qry = "SELECT * FROM campus.tbl_lvinfo WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND genehmigt=true AND sprache='".ATTR_SPRACHE_EN."' AND aktiv=true";
		if($result=$db->db_query($qry))
		{
			if($db->db_num_rows($result)>0)
			{
				$row1=$db->db_fetch_object($result);
				echo "<a href=\"#\" class='Item' onClick=\"javascript:window.open('ects/preview.php?lv=$lvid&language=en','Lehrveranstaltungsinformation','width=700,height=750,resizable=yes,menuebar=no,toolbar=no,status=yes,scrollbars=yes');\">".$p->t('global/englisch')."</a>";
				$need_br=true;
			}
		}

		// Bearbeiten Button anzeigen wenn Lektor der LV und bearbeiten fuer Lektoren aktiviert ist
		// Oder Berechtigung zum Bearbeiten eingetragen ist
		if((!defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && $lektor_der_lv)
		  || (defined('CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_LVINFO_LEKTOR_EDIT==true && $lektor_der_lv)
		  || $rechte->isBerechtigt('lehre/lvinfo',$studiengang_kz)
		  || $rechte->isBerechtigtMultipleOe('lehre/lvinfo', $lehrfach_oe_kurzbz_arr)
		  )
		{
			if($need_br)
				echo "<br>";
			echo "<a href='ects/index.php?lvid=$lvid' target='_blank' class='Item'>".$p->t('lehre/lvInfoBearbeiten')."</a>";
		}
		
		echo '</td>';
	}
	checkZeilenUmbruch();

	if (!isset($DOC_ROOT) || empty($DOC_ROOT))
		$DOC_ROOT='../../..';

	$dir_name=$DOC_ROOT.'/documents';
	if(!is_dir($dir_name))
	{
		exec('mkdir -m 755 '.escapeshellarg($dir_name));
		exec('sudo chown www-data:teacher '.escapeshellarg($dir_name));
	}
	$angemeldet = true;
	if(defined('CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN') && CIS_LEHRVERANSTALTUNG_WENNANGEMELDET_DETAILS_ANZEIGEN && !$is_lector)
	{
	    $angemeldet = false;

		$lehrveranstaltung_obj = new lehrveranstaltung();
		$result = $lehrveranstaltung_obj->getLehreinheitenOfLv($lvid, $user, $angezeigtes_stsem);

		if(count($result)>0)
		    $angemeldet = true;
	}

	if((!defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_ANZEIGEN) && $angemeldet)
	{
		// ** Semesterplan
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';

		$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan';
			
	  	$dest_dir = @dir($dir_name);
	  	if(!@is_dir($dest_dir->path))
		{
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan'))
			{
				exec('mkdir -m 775 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan'));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/semesterplan'));
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
			echo '<img class="lv" src="../../../skin/images/button_semplan.png"><br>';
			echo '<strong>'.$p->t('lehre/semesterplan').'</strong>';
			echo '</a>';
		}
		else
		{
			echo '<img class="lv" src="../../../skin/images/button_semplan.png"><br>';
			echo '<strong>'.$p->t('lehre/semesterplan').'</strong>';
		}

		if((!defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT') && $user_is_allowed_to_upload)
		 || (defined('CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_SEMESTERPLAN_LEKTOR_EDIT==true && $user_is_allowed_to_upload)
		 || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))
		{
			echo '<br><a class="Item" href="#" onClick="javascript:window.open(\'semupload.php?lvid='.$lvid.'\',\'_blank\',\'width=400,height=300,location=no,menubar=no,status=no,toolbar=no\');return false;">';
			echo $p->t('lehre/semesterplanUpload')."</a>";

			echo '&nbsp;&nbsp;&nbsp;<a class="Item" href="semdownhlp.php" >';
			echo $p->t('lehre/semesterplanVorlage');
			echo ' [hml]';
			echo '</a>';
			echo '&nbsp;<a class="Item" href="semdownhlp.php?format=doc" >';
			echo '[doc]';
			echo '</a>';
			echo '&nbsp;<a href="#" onClick="showSemPlanHelp()";>('.$p->t('lehre/semesterplanVorlageHilfe').')</a>';
		}

		echo '</td>';
	}

	checkZeilenUmbruch();

	if((!defined('CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_DOWNLOAD_ANZEIGEN) && $angemeldet)
	{
		//DOWNLOAD
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
	

	  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download';
		
	  	$dest_dir = @dir($dir_name);

	  	if(!@is_dir($dest_dir->path))
		{
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download'))
			{
				exec('mkdir -m 775 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download'));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/download'));
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
			echo '<img class="lv" src="../../../skin/images/button_download.png"><br>';
			echo '<strong>'.$p->t('lehre/download').'</strong>';
			echo '</a>';
		}
		else
		{
			echo '<img class="lv" src="../../../skin/images/button_download.png"><br>';
			echo '<strong>'.$p->t('lehre/download').'</strong>';
		}
	
		//Wenn user eine Lehrfachzuteilung fuer dieses Lehrfach hat wird
		//Ein Link zum Upload angezeigt und ein Link um das Download-Verzeichnis
		//als Zip Archiv herunterzuladen
		if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',$studiengang_kz) || $rechte->isBerechtigt('lehre',$studiengang_kz))// || $rechte->isBerechtigt('lehre',null,null,$fachbereich_id))
		{
			echo "<br>".mb_strtolower("$kurzbz/$semester/$short/download");
			echo '<br>';
			echo "<a class='Item' target='_blank' href='upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short'>".$p->t('lehre/upload')."</a>";
			echo '&nbsp;&nbsp;&nbsp;';
			if(isset($dir_empty) && $dir_empty == false)
				echo "<a class='Item' title='".$p->t('lehre/ziparchivTitle')."' href='zipdownload.php?stg=$studiengang_kz&sem=$semester&short=$short' target='_blank'>".$p->t('lehre/ziparchiv')."</a>";
			else
				echo $p->t('lehre/ziparchiv');
		}
	
		echo '</td>';
	}
	checkZeilenUmbruch();
	

	if(CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN || $is_lector)
	{

		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';

		if((CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN && $angemeldet) || $is_lector)
			echo '<img class="lv" src="../../../skin/images/button_listen.png"><br>';
	
	  	if($is_lector)
	  	{
			//Anwesenheitsliste
			echo "<b><a href='anwesenheitsliste.php?stg_kz=$studiengang_kz&sem=$semester&lvid=$lvid&stsem=$angezeigtes_stsem' class='Item'>".$p->t('lehre/anwesenheitsUndNotenlisten')."</a></b><br>";
	  	}

	  	//Leistungsuebersicht
	  	$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung';

	  	$dest_dir = @dir($dir_name);
	  	if(!@is_dir($dest_dir->path))
		{
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung'))
			{
				exec('mkdir -m 775 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung'));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/leistung'));
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
		if(CIS_LEHRVERANSTALTUNG_LEISTUNGSUEBERSICHT_ANZEIGEN && ($angemeldet || $is_lector))
		{
			if(isset($dest_dir) && isset($dir_empty) && $dir_empty == false)
			{
				echo '<a href="'.$dest_dir->path.'" target="_blank">';
				echo '<strong>'.$p->t('lehre/leistungsuebersicht').'</strong>';
				echo '</a>';
			}
			else
			{
				echo '<strong>'.$p->t('lehre/leistungsuebersicht').'</strong>';
			}
		}

		echo '</td>';
	}	  
	checkZeilenUmbruch();

	//Keine Newsgroups fuer Studiengang '0' (Freifaecher) anzeigen
	if($studiengang_kz!='0' && CIS_LEHRVERANSTALTUNG_NEWSGROUPS_ANZEIGEN  && $angemeldet)
	{
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo '<a href="news://news.technikum-wien.at/'.mb_strtolower($stg_obj->kurzbzlang).'.'.$semester.'sem.'.mb_strtolower($short_short_name).'" class="Item">
				<img class="lv" src="../../../skin/images/button_newsgroups.png"><br>
				<strong>'.$p->t('lehre/newsgroups').'</strong>
			</a>';
		echo '</td>';
	}

	checkZeilenUmbruch();
	//FEEDBACK
	if((!defined('CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_FEEDBACK_ANZEIGEN) && $angemeldet)
	{
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo '<a href="feedback.php?lvid='.$lvid.'" target="_blank" class="Item"><img class="lv" src="../../../skin/images/button_feedback.png"><br><strong>'.$p->t('lehre/feedback').'</strong></a>';
		echo '</td>';
	}

	checkZeilenUmbruch();


	if((!defined('CIS_LEHRVERANSTALTUNG_UEBUNGSTOOL_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_UEBUNGSTOOL_ANZEIGEN) && $angemeldet)
	{
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		$show=false;
	
		//wenn kein Moodle Kurs existiert dann KT anzeigen
		$qry = "SELECT 1 FROM lehre.tbl_moodle WHERE 
				(lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem).")
				OR
				(lehreinheit_id IN (SELECT lehreinheit_id FROM lehre.tbl_lehreinheit 
									WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND 
									studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem)."))";

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
										WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
										studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem).")";
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
						<img class="lv" src="../../../skin/images/button_kreuzerltool.png"><br>
						<strong>'.$p->t('lehre/kreuzerltool').'</strong></a><br>
						<a href="'.APP_ROOT.'cms/dms.php?id='.$p->t('dms_link/benotungstoolHandbuch').'" class="Item" target="_blank">'.$p->t('lehre/benotungstoolHandbuch').' [PDF]</a>';
			} 
			else 
			{
				if(isset($angezeigtes_stsem))
					$studiensem = '&stsem='.$angezeigtes_stsem;
				else
					$studiensem = '';

				echo '<a href="benotungstool/studentenansicht.php?lvid='.$lvid.$studiensem.'" class="Item">
						<img class="lv" src="../../../skin/images/button_kreuzerltool.png"><br>
						<strong>'.$p->t('lehre/kreuzerltool').'</strong></a>';
	
			}
		}
		else 
		{
			if($is_lector)
			{
				echo '<a href="#" onclick="alert(\''.$p->t('lehre/kreuzerltoolMitMoodleInfo').'\');" class="Item">
						<img class="lv" src="../../../skin/images/button_kreuzerltool.png"><br>
						<strong>'.$p->t('lehre/kreuzerltool').'</strong></a><br>
						<a href="'.APP_ROOT.'cms/dms.php?id='.$p->t('dms_link/benotungstoolHandbuch').'" class="Item" target="_blank">'.$p->t('lehre/benotungstoolHandbuch').' [PDF]</a>';
			}
		}
	
		echo'</td>';
	}

	checkZeilenUmbruch();


	//Moodle
	$showmoodle=false;
	//Schauen ob Moodle fuer diesen Studiengang freigeschaltet ist
	$qry = "SELECT moodle FROM public.tbl_studiengang JOIN lehre.tbl_lehrveranstaltung USING(studiengang_kz) WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER);
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
									WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)."
									AND studiensemester_kurzbz=".$db->db_add_param($angezeigtes_stsem).")";
		
		if($result = $db->db_query($qry))
			if($db->db_num_rows($result)>0)
				$showmoodle=false;
		
		$moodle = new moodle();
		$moodle->getAll($lvid, $angezeigtes_stsem);
		if(count($moodle->result)>0)
			$showmoodle=true;
	}
	else 
		$showmoodle=false;
	
	if($angemeldet)
	{
	    if($showmoodle )
	    {
		    $eintraegeprozeile++;
		    echo '<td class="tdvertical" align="center">';

		    $link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
		    if(count($moodle->result)>0)
		    {
			    if(!$is_lector)
			    {
				    $moodle->result=array();
				    $moodle->getCourse($lvid, $angezeigtes_stsem, $user);

				    if(count($moodle->result)==1)
					    $link = $moodle->getPfad($moodle->result[0]->moodle_version).'course/view.php?id='.$moodle->result[0]->mdl_course_id;
				    else 
					    $link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
			    }
			    else 
			    {
				    if(count($moodle->result)==1)
				    {
					    $link = $moodle->getPfad($moodle->result[0]->moodle_version).'course/view.php?id='.$moodle->result[0]->mdl_course_id;
				    }
				    else 
					    $link = "moodle_choice.php?lvid=$lvid&stsem=$angezeigtes_stsem";
			    }
			    echo '<a href="'.$link.'" target="_blank">
				    <img class="lv" src="../../../skin/images/button_moodle.png"><br>
				    <strong>'.$p->t('lehre/moodle').'</strong></a><br>';
		    }
		    else 
		    {
			    echo '<img class="lv" src="../../../skin/images/button_moodle.png"><br>
				    <strong>'.$p->t('lehre/moodle').'</strong><br>';
		    }

			if($is_lector && (!defined('CIS_LEHRVERANSTALTUNG_MOODLE_LEKTOR_EDIT') || (defined('CIS_LEHRVERANSTALTUNG_MOODLE_LEKTOR_EDIT') && CIS_LEHRVERANSTALTUNG_MOODLE_LEKTOR_EDIT)))
			    echo '	<a href="moodle2_4_wartung.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item">'.$p->t('lehre/moodleWartung').'</a>
				    <br /><a href="'.APP_ROOT.'cms/dms.php?id='.$p->t('dms_link/moodleHandbuch24').'" class="Item" target="_blank">'.$p->t('lehre/moodleHandbuch').'</a>';

		    echo '</td>';
	    }
	    else 
	    {
		    if($is_lector)
		    {
			    $eintraegeprozeile++;
			    echo '<td class="tdvertical" align="center">';
			    echo '<a href="#" onclick="alert(\''.$p->t('lehre/moodleMitKreuzerltoolInfo').'\'); return false">
				    <img class="lv" src="../../../skin/images/button_moodle.png"><br>
				    <strong>'.$p->t('lehre/moodle').'</strong></a><br>';
			    echo '</td>';
		    }
	    }
	}
	checkZeilenUmbruch();
	
	//Gesamtnote
	if($is_lector && ((!defined('CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_GESAMTNOTE_ANZEIGEN) && $angemeldet))
	{
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo '<a href="benotungstool/lvgesamtnoteverwalten.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'" class="Item" >
    		<img class="lv" src="../../../skin/images/button_endnote.png"><br>
    		<strong>'.$p->t('lehre/gesamtnote').'</strong></a><br>';
		echo '
			</td>';
	}
	
	checkZeilenUmbruch();


	if((!defined('CIS_LEHRVERANSTALTUNG_STUDENTENUPLOAD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_STUDENTENUPLOAD_ANZEIGEN) && $angemeldet)
	{
		//Studentenupload 
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		//Studentenabgabe
		$dir_name=$DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload';

		$dest_dir = @dir($dir_name);
	  	if(!@is_dir($dest_dir->path))
		{
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)))
			{
				exec('mkdir -m 755 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
				exec('sudo chown www-data:teacher '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name)));
			}
			if(!@is_dir(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload'))
			{
				exec('mkdir -m 775 '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload'));
				exec('sudo chown www-data:student '.escapeshellarg(DOC_ROOT.'/documents/'.mb_strtolower($kurzbz).'/'.$semester.'/'.mb_strtolower($short_short_name).'/upload'));
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
				echo "<img class=\"lv\" src=\"../../../skin/images/button_studiupload.png\"><br>
					  <strong>".$p->t('lehre/studentenAbgabe')."</strong>
					  </a>";
			}
			else
			{
				echo "<a href=\"upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short\" target=\"_blank\">";
				echo "<img class=\"lv\" src=\"../../../skin/images/button_studiupload.png\"><br>
					  <strong>".$p->t('lehre/studentenAbgabe')."</strong>
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
				echo "<img class=\"lv\" src=\"../../../skin/images/button_studiupload.png\"><br>
					  <strong>".$p->t('lehre/studentenAbgabe')."</strong>";
			}
			else
			{
				echo "<a href=\"upload.php?course_id=$studiengang_kz&term_id=$semester&short=$short\" target=\"_blank\">";
				echo "<img class=\"lv\" src=\"../../../skin/images/button_studiupload.png\"><br>
					  <strong>".$p->t('lehre/studentenAbgabe')."</strong>
					  </a>";
			}
		}
		echo '</td>';
	}
	
	checkZeilenUmbruch();

	if((!defined('CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_MAILSTUDIERENDE_ANZEIGEN) && $angemeldet)
	{
		// Email an Studierende
	
		$mailto='mailto:';
		$qry = 'SELECT 
					distinct vw_lehreinheit.stg_kurzbz, vw_lehreinheit.stg_typ, vw_lehreinheit.semester, 
					vw_lehreinheit.verband, vw_lehreinheit.gruppe, vw_lehreinheit.gruppe_kurzbz, tbl_gruppe.mailgrp 
				FROM 
					campus.vw_lehreinheit 
					LEFT JOIN public.tbl_gruppe USING(gruppe_kurzbz)
				WHERE 
					lehrveranstaltung_id='.$db->db_add_param($lvid).' 
					AND studiensemester_kurzbz='.$db->db_add_param($angezeigtes_stsem);
		$nomail='';
		if($result = $db->db_query($qry))
		{
			while($row = $db->db_fetch_object($result))
			{
				if($row->gruppe_kurzbz!='')
				{
					if(!$db->db_parse_bool($row->mailgrp))
					{
						$nomail=$row->gruppe_kurzbz.' ';
					}
					else
						$mailto.=mb_strtolower($row->gruppe_kurzbz.'@'.DOMAIN.',');
				}
				else
					$mailto.=mb_strtolower($row->stg_typ.$row->stg_kurzbz.$row->semester.trim($row->verband).trim($row->gruppe).'@'.DOMAIN.',');
			}
		}
		if($nomail!='')
		{
			$nomail = 'onclick="alert(\''.$p->t('lehre/keinMailverteiler',array($nomail)).'\');"';
		}
	
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo '<a href="'.$mailto.'" '.$nomail.'><img class="lv" src="../../../skin/images/button_feedback.png"><br><strong>'.$p->t('lehre/mail').'</strong></a>';
		echo '</td>';
	}
	
	checkZeilenUmbruch();

	if((!defined('CIS_LEHRVERANSTALTUNG_PINBOARD_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_PINBOARD_ANZEIGEN) && $angemeldet)
	{
		//Pinboard
		$eintraegeprozeile++;
		echo '<td class="tdvertical" align="center">';
		echo '<img class="lv" src="../../../skin/images/button_pinboard.png"><br>';	
		echo "<b>".$p->t('lehre/pinboard')."</b></a><br>";
		echo "<a href='../../../cms/news.php?studiengang_kz=$studiengang_kz&semester=$semester' class='Item'>".$p->t('global/anzeigen')."</a>";	
		if($is_lector)
			echo "<br><a href='../../../cms/newsverwaltung.php?studiengang_kz=$studiengang_kz&semester=$semester' class='Item'>".$p->t('profil/adminstration')."</a>";
		echo '</td>';
	}
	
	checkZeilenUmbruch();

	if(!defined('CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN') || CIS_LEHRVERANSTALTUNG_ABMELDUNG_ANZEIGEN)
	{
		if(!$is_lector)
		{
			$lvangebot = new lvangebot();
			$gruppen = $lvangebot->AbmeldungMoeglich($lvid, $angezeigtes_stsem, $user);
			if(count($gruppen)>0)
			{
				//Abmelden von der Lehrveranstaltung
				$eintraegeprozeile++;
				echo '<td class="tdvertical" align="center">';
				echo '<a href="abmeldung.php?lvid='.$lvid.'&stsem='.$angezeigtes_stsem.'">';
				echo '<img class="lv" src="../../../skin/images/button_studiupload.png"><br>';

				echo "<b>".$p->t('lehre/abmelden')."</b></a><br>";
				echo '</td>';
			}
		}
	}
	
	//Anzeigen von zusaetzlichen Lehre-Tools
	
	$lehretools = new lehre_tools();
	if($lehretools->getTools($lvid, $angezeigtes_stsem))
	{
		if(count($lehretools->result)>0)
		{
			echo "\n</tr><tr>\n";
			foreach($lehretools->result as $row)
			{
				echo "\n";
				echo '<td class="tdvertical" align="center">';
				echo '<a href="'.$row->basis_url.'" target="_blank">';
				if($row->logo_dms_id!='')
					echo '<img class="lv" src="../../../cms/dms.php?id='.$row->logo_dms_id.'"><br>';
									
				echo ' <strong>'.$row->bezeichnung[$sprache].'</strong>';
				
				if($row->logo_dms_id!='')
				  echo '</a>';
				echo '</td>';
			}
		}
	}
  ?>
	</tr>
</table>
