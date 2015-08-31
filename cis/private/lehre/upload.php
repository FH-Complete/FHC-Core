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
	require_once('../../../include/basis_db.class.php');			
    require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once ('../../../include/phrasen.class.php');


    $sprache = getSprache(); 
	$p=new phrasen($sprache); 
    
	if (!$db = new basis_db())
		die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));
	$user = get_uid();

	if(isset($_GET['course_id']))
		$course_id = $_GET['course_id'];

	if(isset($_GET['term_id']))
		$term_id = $_GET['term_id'];

	if(isset($_GET['short']))
		$short = $_GET['short'];

	if(isset($_GET['subdir']))
		$subdir = $_GET['subdir'];
	if(isset($_POST['overwrite']))
		$overwrite = $_POST['overwrite'];
	if(isset($_POST['create_dir']))
		$create_dir = $_POST['create_dir'];
	if(isset($_POST['new_dir_name_text']))
		$new_dir_name_text = $_POST['new_dir_name_text'];
	if(isset($_POST['rename_dir']))
		$rename_dir = $_POST['rename_dir'];
	if(isset($_POST['confirm_rename']))
		$confirm_rename = $_POST['confirm_rename'];
	if(isset($_POST['link_cut']))
		$link_cut = $_POST['link_cut'];
	if(isset($_POST['delete_dir']))
		$delete_dir = $_POST['delete_dir'];
	if(isset($_POST['rename_file']))
		$rename_file = $_POST['rename_file'];
	if(isset($_POST['delete_file']))
		$delete_file = $_POST['delete_file'];
	
	/*
	if($course_id!='' && !is_numeric($course_id))
		die('Fehlerhafter Parameter');
		
	if($term_id!='' && !is_numeric($term_id))
		die('Fehlerhafter Parameter');
	*/
	$rechte = new benutzerberechtigung();

	$rechte->getBerechtigungen($user);

	if(check_lektor($user))
       $is_lector=true;
    else
    	$is_lector=false;

	$upload_root = DOC_ROOT.'/documents';//"../../../documents";
	$link_cut = DOC_ROOT.'/documents';
	
	if(isset($subdir))
	{
		if(substr_count($subdir, '..') > 0 || substr_count($subdir, '.') > 0)
		{
			unset($subdir);
		}
	}

	if($is_lector)
	{
		$islector = true;
	}
	else
	{
		$sql_query = "SELECT student_uid FROM public.tbl_student WHERE student_uid=".$db->db_add_param($user);
		if($result_student = $db->db_query($sql_query))
		{
			$num_rows_student = $db->db_num_rows($result_student);

			if(!($num_rows_student > 0))
			{
				die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');
			}
		}
		else
			die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');

		$islector = false;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<script language="JavaScript" type="text/javascript">
	function MM_jumpMenu(targ, selObj, restore)
	{
		eval(targ + ".location='" + selObj.options[selObj.selectedIndex].value + "'");

		if(restore)
		{
			selObj.selectedIndex = 0;
		}
	}

	var del = false;

	function ConfirmDir(handle)
	{
		if(del)
		{
			del = false;

			return confirm("<?php echo $p->t('upload/wollenSieOrdnerWirklichLoeschen'); ?>!");
		}
		else
			return true;
	}

	function ConfirmFile(handle)
	{
		if(del)
		{
			del = false;

			return confirm("<?php echo $p->t('upload/wollenSieOrdnerWirklichLoeschen'); ?>!");
		}
		else
			return true;
	}
	
	function checkvz(id)
	{
		vz = document.getElementById(id).value;
		re = new RegExp(/^(\d|\w|\s|[-_ÄÜÖäüö])*$/);
		
		if (vz.match(re))
		{
			return true;
		}
		else
		{
			alert('<?php echo $p->t('upload/verzeichnisnameDarfNurBuchstaben'); ?>');
			return false;
		}
	}
</script>

<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<base target="_self">
</head>

<body>

<table class="tabcontent">

			  <?php
				if($islector)
				{
					//AND tbl_lehrveranstaltung.studiengang_kz!=0
				   $sql_query = "SELECT DISTINCT tbl_studiengang.typ,UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz, tbl_studiengang.kurzbzlang, tbl_studiengang.studiengang_kz FROM public.tbl_studiengang, lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz AND tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$db->db_add_param($user)." AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>'' ORDER BY typ, kurzbz";

				   if(!$result_lector_dispatch = $db->db_query($sql_query))
				   		die('Fehler beim Lesen aus der Datenbank');

				   $num_rows_lector_dispatch = $db->db_num_rows($result_lector_dispatch);

				   echo '<tr>';
		            echo '<td align="middle" class="MarkLine" colSpan="5" height="49">';
					  echo '<form accept-charset="UTF-8" method="post" action="" enctype="multipart/form-data">';

					   if(!($num_rows_lector_dispatch > 0) && !$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('lehre') && !$rechte->isBerechtigt('assistenz'))
					   {
					   		die('<p align="center"><strong>'.$p->t('upload/keineStudiengaengeDefiniert').'!</strong></p>');
					   }


					   echo $p->t('global/studiengang').': ';
					   echo "\n<select name=\"course\" onChange=\"MM_jumpMenu('self',this,0)\">";

						//STUDIENGANG

						$stg_arr = array();
						//Alle Studiengaenge mit Lehrfachzuteilung holen
						while($row=$db->db_fetch_object($result_lector_dispatch))
						{
							$stg_arr[$row->studiengang_kz]=$row->kurzbz;
						}

						//Studiengaenge fuer die eine Admin Berechtigung vorhanden ist holen
						if($rechte->isBerechtigt('admin'))
						{
							$arr = $rechte->getStgKz('admin');

							if(isset($arr[0]) && $arr[0]==0) //Berechtigt fuer alle Stg
							{
								$sql_query="SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz"; // WHERE studiengang_kz<>0
								$result_stg=$db->db_query($sql_query);

								while($row = $db->db_fetch_object($result_stg))
								{
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
									{
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
									}
								}
							}
							else //Berechtigt fuer einen Teil der Studiengaenge
							{
								$ids='-1';
								foreach ($arr as $elem)
								{
									if($elem!='')
										$ids.=",'$elem'";
								}

								$sql_query = "SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang WHERE studiengang_kz IN(".$ids.")";
								if($result_stg_kurzbzlang=$db->db_query($sql_query))
								{
									while($row = $db->db_fetch_object($result_stg_kurzbzlang))
										if(!array_key_exists($row->studiengang_kz,$stg_arr))
											$stg_arr[$row->studiengang_kz]=$row->kurzbz;
								}
							}
						}

						//Lehre Berechtigungen auf Studiengangsebene
						if($rechte->isBerechtigt('lehre'))
						{
							$arr = $rechte->getStgKz('lehre');

							if(isset($arr[0]) && $arr[0]==0) //Berechtigt fuer alle Stg
							{
								$sql_query="SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz"; //WHERE studiengang_kz<>0
								$result_stg=$db->db_query($sql_query);

								while($row = $db->db_fetch_object($result_stg))
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
							}
							else //Berechtigt fuer einen Teil der Studiengaenge
							{
								$ids='-1';
								foreach ($arr as $elem)
									$ids.=",'$elem'";

								$sql_query = "SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang WHERE studiengang_kz IN(".$ids.")";

								$result_stg_kurzbzlang=$db->db_query($sql_query);
								while($row = $db->db_fetch_object($result_stg_kurzbzlang))
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
							}
						}
						
						//Assistenz Berechtigungen auf Studiengangsebene
						if($rechte->isBerechtigt('assistenz'))
						{
							$arr = $rechte->getStgKz('assistenz');

							if(isset($arr[0]) && $arr[0]==0) //Berechtigt fuer alle Stg
							{
								$sql_query="SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz"; //WHERE studiengang_kz<>0
								$result_stg=$db->db_query($sql_query);

								while($row = $db->db_fetch_object($result_stg))
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
							}
							else //Berechtigt fuer einen Teil der Studiengaenge
							{
								$ids='-1';
								foreach ($arr as $elem)
									$ids.=",'$elem'";

								$sql_query = "SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang WHERE studiengang_kz IN(".$ids.")";

								$result_stg_kurzbzlang=$db->db_query($sql_query);
								while($row = $db->db_fetch_object($result_stg_kurzbzlang))
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
							}
						}
						
						//Lehre Berechtigung auf Fachbereichsebnene
						if($rechte->isBerechtigt('lehre') || $rechte->isBerechtigt('admin'))
						{
							$arr = $rechte->getFbKz(null);

							if(isset($arr[0]) && $arr[0]=='0') //Berechtigt fuer alle Fachbereiche = Alle Studiengaenge
							{
								$sql_query="SELECT studiengang_kz, kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_studiengang ORDER BY kurzbz"; //WHERE studiengang_kz<>0
								$result_stg=$db->db_query($sql_query);

								while($row_stg = $db->db_fetch_object($result_stg))
									if(!array_key_exists($row_stg->studiengang_kz,$stg_arr))
										$stg_arr[$row_stg->studiengang_kz]=$row_stg->kurzbz;
							}
							else //Berechtigt fuer einen Teil der Fachbereiche
							{
								$ids="'-1'";
								foreach ($arr as $elem)
									$ids.=",'$elem'";

								$sql_query = "SELECT distinct tbl_lehrveranstaltung.studiengang_kz, tbl_studiengang.kurzbzlang, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_fachbereich, public.tbl_studiengang, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung WHERE fachbereich_kurzbz in(".$ids.") AND tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND tbl_studiengang.studiengang_kz=tbl_lehrveranstaltung.studiengang_kz AND lehrfach.lehrveranstaltung_id=tbl_lehreinheit.lehrfach_id AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>'' AND lehrfach.oe_kurzbz=tbl_fachbereich.oe_kurzbz";
								$result_stg_kurzbzlang=$db->db_query($sql_query);
								while($row = $db->db_fetch_object($result_stg_kurzbzlang))
									if(!array_key_exists($row->studiengang_kz,$stg_arr))
										$stg_arr[$row->studiengang_kz]=$row->kurzbz;
							}

						}

						//Array mit Studiengaengen Sortieren
						asort($stg_arr);
						foreach ($stg_arr as $stg_id=>$kurzbz)
						{
							echo "\n   ";
							if(isset($course_id) && $course_id == $stg_id) //$course_id &&
							{
								$course_short = $kurzbz;
								echo '<option value="upload.php?course_id='.$stg_id.'" selected>'.$kurzbz.'</option>';
							}
							else
							{
								echo '<option value="upload.php?course_id='.$stg_id.'">'.$kurzbz.'</option>';
							}

							if(!isset($course_short) || !$course_short)
						    {
								$row_course = $stg_id;
								$course_short = $kurzbz;
						    }
						}
						echo "\n</select>\n";
						echo '&nbsp;';


					   if(!isset($course_id))
					   {
					   		foreach ($stg_arr as $key=>$elem)
					   		{
					   			$course_id=$key;
					   			$course_short = $elem;
					   			break;
					   		}
					   }

					   //$sql_query = "SELECT DISTINCT ON(semester) semester FROM lehre.tbl_lehrfachzuteilung WHERE lektor_uid='$user' AND NOT(lehrfachzuteilung_kurzbz='') AND studiengang_kz='$course_id' ORDER BY semester";
					   $sql_query = "SELECT DISTINCT semester FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehrveranstaltung WHERE tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND mitarbeiter_uid=".$db->db_add_param($user)." AND studiengang_kz=".$db->db_add_param($course_id)." AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>'' ORDER BY semester";
					   if(!$result_lector_dispatch = $db->db_query($sql_query))
					   		die($p->t('global/fehlerBeimLesenAusDatenbank'));

					   $num_rows_lector_dispatch = $db->db_num_rows($result_lector_dispatch);

					   if(!($num_rows_lector_dispatch > 0) && !$rechte->isBerechtigt('admin') && !$rechte->isBerechtigt('lehre') && !$rechte->isBerechtigt('assistenz'))
							die('<p align="center"><strong<font size="2" face="Arial, Helvetica, sans-serif">'.$p->t('upload/keineSemesterDefiniert').'!</font></p>');


					   echo $p->t('global/semester').': ';
					   echo "\n<select name=\"term\" onChange=\"MM_jumpMenu('self',this,0)\" class=\"xxxs_black\">";
					   //SEMESTER
					   $sem_arr = array();

					   //Alle Semester mit Lehrfachzuteilung
					   while($row=$db->db_fetch_object($result_lector_dispatch))
					   		$sem_arr[]=$row->semester;

					   //Alle Semester mit admin oder lehre Rechten
					   if($rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id)|| $rechte->isBerechtigt('assistenz',$course_id))
					   {
					   		$sql_query= "SELECT max_semester FROM public.tbl_studiengang WHERE studiengang_kz=".$db->db_add_param($course_id);
							$result_studiengang_semester=$db->db_query($sql_query);
							$row_studiengang_semester=$db->db_fetch_object($result_studiengang_semester);
							for($i=1;$i<=$row_studiengang_semester->max_semester;$i++)
							{
								if(!in_array($i,$sem_arr))
								{
									$sem_arr[]=$i;
								}
							}
					   }

					   if($rechte->isBerechtigt('lehre') || $rechte->isBerechtigt('admin')) //Fachbereiche abdecken
					   {
					   		$arr = $rechte->getFbKz();

					   		if(isset($arr[0]) && $arr[0]=='0') //Berechtigt fuer alle Fachbereiche = Alle Studiengaenge = Alle Semester
							{
								$sql_query="SELECT max_semester FROM public.tbl_studiengang WHERE studiengang_kz=".$db->db_add_param($course_id);
								$result_studiengang_semester=$db->db_query($sql_query);
								$row_studiengang_semester=$db->db_fetch_object($result_studiengang_semester);
								for($i=1;$i<=$row_studiengang_semester->max_semester;$i++)
									if(!in_array($i,$sem_arr))
										$sem_arr[]=$i;
							}
							else //Berechtigt fuer einen Teil der Fachbereiche
							{
								$ids="'-1'";
								foreach ($arr as $elem)
									$ids.=",'$elem'";

								$sql_query = "SELECT distinct tbl_lehrveranstaltung.semester FROM lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_fachbereich, lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit WHERE fachbereich_kurzbz in(".$ids.") AND tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($course_id)." AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>'' AND tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id AND tbl_fachbereich.oe_kurzbz=lehrfach.oe_kurzbz";
								//echo $sql_query;
								$result=$db->db_query($sql_query);
								while($row = $db->db_fetch_object($result))
									if(!in_array($row->semester,$sem_arr))
										$sem_arr[]=$row->semester;
							}
					   }

						sort($sem_arr);
						foreach ($sem_arr as $sem)
						{
							echo "\n   ";
					   		if(isset($term_id) && $term_id == $sem)
							{
								echo '<option value="upload.php?course_id='.$course_id.'&term_id='.$sem.'" selected>'.$sem.'</option>';
							}
							else if($sem > 0)
							{
								echo '<option value="upload.php?course_id='.$course_id.'&term_id='.$sem.'">'.$sem.'</option>';
							}
						}
						echo "\n</select>\n";
						echo '&nbsp;';

						if(!isset($term_id))
							$term_id=$sem_arr[0];

						//$sql_query = "SELECT DISTINCT ON(bz2, lehrfachzuteilung_kurzbz) lehrfachzuteilung_kurzbz AS kuerzel, (bezeichnung || '; XX') AS bezeichnung, SUBSTRING(bezeichnung || '; XX', 1, CHAR_LENGTH(bezeichnung || '; XX') - 4) AS bz2 FROM lehre.tbl_lehrfachzuteilung WHERE studiengang_kz='$course_id' AND semester='$term_id' AND NOT(lehrfachzuteilung_kurzbz='') AND lektor_uid='$user' ORDER BY bz2, lehrfachzuteilung_kurzbz";
						//Nur Lehrfachzuteilungen
						//$sql_query = "SELECT DISTINCT lehrevz AS kuerzel, (bezeichnung || '; XX') AS bezeichnung, SUBSTRING(bezeichnung || '; XX', 1, CHAR_LENGTH(bezeichnung || '; XX') - 4) AS bz2 FROM tbl_lehrfach Join tbl_lehrveranstaltung using (lehrfach_nr) WHERE tbl_lehrfach.studiengang_kz='$course_id' AND tbl_lehrveranstaltung.semester='$term_id' AND lektor='$user' AND NOT(lehrevz='')";
						$sql_query = "SELECT DISTINCT lehreverzeichnis as kuerzel, tbl_lehrveranstaltung.bezeichnung FROM
						              lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, lehre.tbl_lehrveranstaltung
						              WHERE tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
						              tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
						              tbl_lehreinheitmitarbeiter.mitarbeiter_uid=".$db->db_add_param($user)." AND
						              tbl_lehrveranstaltung.semester=".$db->db_add_param($term_id)." AND
						              tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($course_id)." AND tbl_lehrveranstaltung.lehre=true";
						//Admin und Lehreberechtigung
						if($rechte->isBerechtigt('admin',$course_id) || $rechte->isBerechtigt('lehre',$course_id) || $rechte->isBerechtigt('assistenz',$course_id))
						{
							$sql_query = "SELECT DISTINCT lehreverzeichnis AS kuerzel, bezeichnung FROM lehre.tbl_lehrveranstaltung WHERE studiengang_kz=".$db->db_add_param($course_id)." AND semester=".$db->db_add_param($term_id)." AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>''";
						}
						//Fachbereichsberechtigung
						if($rechte->isBerechtigt('lehre') || $rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz'))
						{
							$arr=$rechte->getFbKz();
							$ids="'-1'";
								foreach ($arr as $elem)
									$ids.=",'$elem'";
							$sql_query = $sql_query . " UNION SELECT DISTINCT tbl_lehrveranstaltung.lehreverzeichnis AS kuerzel, tbl_lehrveranstaltung.bezeichnung
							                           FROM lehre.tbl_lehrveranstaltung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung as lehrfach, public.tbl_fachbereich
							                           WHERE tbl_lehrveranstaltung.lehrveranstaltung_id=tbl_lehreinheit.lehrveranstaltung_id AND
							                           tbl_lehreinheit.lehrfach_id = lehrfach.lehrveranstaltung_id AND
													   tbl_fachbereich.oe_kurzbz = lehrfach.oe_kurzbz AND
							                           tbl_lehrveranstaltung.studiengang_kz=".$db->db_add_param($course_id)." AND tbl_lehrveranstaltung.semester=".$db->db_add_param($term_id)." AND fachbereich_kurzbz in ($ids) AND tbl_lehrveranstaltung.lehre=true AND tbl_lehrveranstaltung.lehreverzeichnis<>''";
						}
						$sql_query .= ' ORDER BY bezeichnung, kuerzel';
						//LEHRFAECHER

						if(!$result_lector_dispatch = $db->db_query($sql_query))
							die('<p align="center"><strong>'.$p->t('upload/keineGegenstaendeDefiniert').'!</p>');

						$num_rows_lector_dispatch = $db->db_num_rows($result_lector_dispatch);
						//echo $sql_query;
						//echo '<font size="2" face="Arial, Helvetica, sans-serif">';

					   if(!($num_rows_lector_dispatch > 0))
					   {
					      die('<p align="center"><strong>'.$p->t('upload/keineGegenstaendeDefiniert').'!</p>');
					   }

					   //echo '</font>';
					   echo $p->t('global/lehrveranstaltung').': ';
					   echo "\n<select name=\"short\" onChange=\"MM_jumpMenu('self',this,0)\" class=\"xxxs_black\">";

					   for($i = 0; $i < $num_rows_lector_dispatch; $i++)
					   {
						   $row_lesson = $db->db_fetch_object($result_lector_dispatch, $i);
						   echo "\n   ";
						   if(isset($short) && $short == $row_lesson->kuerzel)
						   {
							   $short_short = $row_lesson->kuerzel;

							   echo '<option value="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$row_lesson->kuerzel.'" selected>'.$row_lesson->bezeichnung.'</option>';
						   }
						   else
						   {
							   echo '<option value="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$row_lesson->kuerzel.'">'.$row_lesson->bezeichnung.'</option>';
						   }

						   if(!isset($short) || $short == "")
						   {
						   		$short = $row_lesson->kuerzel;
						   }
					   }

					   if(!isset($short_short) || !$short_short)
					   {
					   		$row_lesson = $db->db_fetch_object($result_lector_dispatch, 0);

							$short_short = $row_lesson->kuerzel;
					   }

					   $uploaddir =mb_strtolower($course_short).'/'.$term_id.'/'.mb_strtolower($short_short).'/download';

					   echo "\n</select>\n";
					 echo '</form>';
				   echo '</td>';
				  echo '</tr>';
				}
				else
				{
					//$sql_query = "SELECT DISTINCT ON(bz2, lehrevz) tbl_student.studiengang_kz AS id, kurzbzlang, lehrevz AS kuerzel, (tbl_lehrfach.bezeichnung || '; XX') AS bezeichnung, SUBSTRING(tbl_lehrfach.bezeichnung || '; XX', 1, CHAR_LENGTH(tbl_lehrfach.bezeichnung || '; XX') - 4) AS bz2 FROM tbl_lehrfach, public.tbl_studiengang, public.tbl_student WHERE tbl_student.studiengang_kz='$course_id' AND tbl_student.semester='$term_id' AND lehrevz='$short' AND tbl_student.uid='$user' AND tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz LIMIT 1";
					$sql_query = "SELECT DISTINCT tbl_lehrveranstaltung.bezeichnung, lehreverzeichnis, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM public.tbl_student, lehre.tbl_lehrveranstaltung, public.tbl_studiengang WHERE lehreverzeichnis=".$db->db_add_param($short)." AND tbl_student.studiengang_kz=".$db->db_add_param($course_id)." AND tbl_student.semester=".$db->db_add_param($term_id)." AND  tbl_student.student_uid=".$db->db_add_param($user)." AND tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz AND tbl_lehrveranstaltung.studiengang_kz=tbl_student.studiengang_kz AND tbl_lehrveranstaltung.semester=tbl_student.semester AND tbl_lehrveranstaltung.lehre=true LIMIT 1";

					if(!$result_path_elements = $db->db_query($sql_query))
						die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');

					if(!$result_path_elements)
						die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');
					$num_rows_path_elements = $db->db_num_rows($result_path_elements);
					if(!($num_rows_path_elements > 0))
					{
						// Pruefen ob dieser Kurs ein Wahlfach ist
						$sql_query = "SELECT DISTINCT vw_student_lehrveranstaltung.bezeichnung, vw_student_lehrveranstaltung.lehreverzeichnis, UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kurzbz FROM campus.vw_student_lehrveranstaltung , public.tbl_studiengang WHERE vw_student_lehrveranstaltung.lehre=true AND vw_student_lehrveranstaltung.studiengang_kz=".$db->db_add_param($course_id)." AND vw_student_lehrveranstaltung.semester=".$db->db_add_param($term_id)." AND vw_student_lehrveranstaltung.lehreverzeichnis=".$db->db_add_param($short)." AND vw_student_lehrveranstaltung.uid=".$db->db_add_param($user)."	AND tbl_studiengang.studiengang_kz=vw_student_lehrveranstaltung.studiengang_kz LIMIT 1; ";
						if(!$result_path_elements = $db->db_query($sql_query))
							die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');
						if(!$result_path_elements)
							die('<p align="center"><strong>'.$p->t('upload/benutzerKonnteNichtZugeordnetWerden',array($user)).'</strong>!</p>');
						$num_rows_path_elements = $db->db_num_rows($result_path_elements);
						if(!($num_rows_path_elements > 0))
						{
							echo "<tr><td>";
							die('<p align="center"><strong>'.$p->t('global/keineBerechtigungFuerDieseSeite')
							.'</td></tr></table>');
						}
					}
					$row = $db->db_fetch_object($result_path_elements, 0);
					$uploaddir = mb_strtolower($row->kurzbz).'/'.$term_id.'/'.mb_strtolower($row->lehreverzeichnis).'/upload';
				}
			  ?>
			  <tr>
              <td align="center" colSpan="5" height="36">
              <center>
                <table>
                  <tr>
                    <td><div align="center"><b><font face="Arial" size="2">
                        <?php
						  if($islector)
						  {
						  	if(!isset($link_cut))
						  		$link_cut = '';
						  	$link_path = mb_substr(mb_substr($upload_root.'/'.$uploaddir, mb_strlen($link_cut)), 0, mb_strlen(mb_substr($upload_root.'/'.$uploaddir, mb_strlen($link_cut))) - mb_strlen('download')).'upload';
						  }

						  $numoffile = 5;

						  // Upload von neuen Dateien
						  if(isset($_POST['upload']) && $_POST['upload'] == "Upload")
						  {
						      for($i = 0; $i < $numoffile; $i++)
							  {
							  	  $file = "userfile_$i";
								
								  if(isset($_FILES[$file]))
								  {
								  	  $file_name = $_FILES[$file]['name'];

								  	  if(!check_filename($file_name))
								  	  {
										echo "<center><b><font color='red'>".$p->t('upload/dateinameDarfNurBuchstaben', array($i+1)).".</b></font></center>";
								  	  }
								  	  else
								  	  {
									  	  if($file_name != "")
										  {
											  if(isset($subdir) && $subdir != "")
											  {
											  	  $uploadfile = $upload_root.'/'.$uploaddir.'/'.$subdir.'/'.$file_name;
											  }
											  else
											  {
											  	  $uploadfile = $upload_root.'/'.$uploaddir.'/'.$file_name;
											  }
											
											  if(!file_exists($uploadfile))
											  {
											  	
												  if(isset($subdir) && $subdir != "")
												  {
												  	  if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
													  {
													  	  unset($subdir);
													  }
													  else
													  {
													  	  if(!stristr($uploadfile, '.php') && !stristr($uploadfile, '.cgi') && !stristr($uploadfile, '.pl') && !stristr($uploadfile, '.phtml') && !stristr($file_name,'.htaccess'))
														  {
														  	 if(copy($_FILES[$file]['tmp_name'], $uploadfile))
														  	 {
														  	 	 exec('chmod 664 '.escapeshellarg($uploadfile));
																 if($islector)
																 {
																	exec('sudo chown :teacher '.escapeshellarg($uploadfile));
																 }
																 else
																 {
																	exec('sudo chown :student '.escapeshellarg($uploadfile));
																 }
														  	 }
														  }
														  else
														  {
														  	 $unallowed_upload = true;
														  }
													  }
												  }
												  else
												  {
												  	  if(!stristr($uploadfile, '.php') && !stristr($uploadfile, '.cgi') && !stristr($uploadfile, '.pl') && !stristr($uploadfile, '.phtml') && !stristr($file_name,'.htaccess'))
													  {
														  if(copy($_FILES[$file]['tmp_name'], $uploadfile))
														  {
														  	  exec('chmod 664 '.escapeshellarg($uploadfile));
															  if($islector)
															  {
																exec('sudo chown :teacher '.escapeshellarg($uploadfile));
															  }
															  else
															  {
																exec('sudo chown :student '.escapeshellarg($uploadfile));
															  }
														  }
													  }
													  else
													  {
														 $unallowed_upload = true;
													  }
												  }
											  }
											  else
											  {
												  if(isset($overwrite))
												  {
												  	  if(isset($subdir) && $subdir != "")
												  	  {
														  if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
														  {
															  unset($subdir);
														  }
														  else
														  {
															  if(!stristr($uploadfile, '.php') && !stristr($uploadfile, '.cgi') && !stristr($uploadfile, '.pl') && !stristr($uploadfile, '.phtml') && !stristr($file_name,'.htaccess'))
															  {
															  	   if(copy($_FILES[$file]['tmp_name'], $uploadfile))
															  	   {
															  	   	   exec('chmod 664 '.escapeshellarg($uploadfile));
																	   if($islector)
																	   {
																			exec('sudo chown :teacher '.escapeshellarg($uploadfile));
																	   }
																	   else
																	   {
																			exec('sudo chown :student '.escapeshellarg($uploadfile));
																	   }
															  	   }
															  }
															  else
															  {
																   $unallowed_upload = true;
															  }
														  }
													  }
													  else
													  {
													  	  if(!stristr($uploadfile, '.php') && !stristr($uploadfile, '.cgi') && !stristr($uploadfile, '.pl') && !stristr($uploadfile, '.phtml') && !stristr($file_name,'.htaccess'))
														  {
															  if(copy($_FILES[$file]['tmp_name'], $uploadfile))
															  {
															  	  exec('chmod 664 '.escapeshellarg($uploadfile));
																  if($islector)
																  {
																	exec('sudo chown :teacher '.escapeshellarg($uploadfile));
																  }
																  else
																  {
																	exec('sudo chown :student '.escapeshellarg($uploadfile));
																  }
															  }
														  }
														  else
														  {
														  	  $unallowed_upload = true;
														  }
													  }
													  $no_overwrite_error=false;
												  }
												  else
												  	$no_overwrite_error=true;
											  }
										  }
								  	  }
								  }
							  }
						  }

						  if(isset($row_lesson) && !isset($short))
						  {
							  if(isset($subdir) && $subdir != "")
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$subdir\" enctype=\"multipart/form-data\">";
							  }
							  else
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel\" enctype=\"multipart/form-data\">";
							  }
						  }
						  else if(isset($short))
						  {
						  	  if(isset($subdir) && $subdir != "")
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$subdir\" enctype=\"multipart/form-data\">";
							  }
							  else
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" enctype=\"multipart/form-data\">";
							  }
						  }
						  else
						  {
						  	  if(isset($subdir) && $subdir != "")
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$subdir\" enctype=\"multipart/form-data\">";
							  }
							  else
							  {
								  echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id\" enctype=\"multipart/form-data\">";
							  }
						  }

						  echo '</td><td>&nbsp;</td></tr>';
						  for($i = 0; $i < $numoffile; $i++)
						  {
						    $j = $i + 1;

						    echo "  <tr>";
						    echo "    <td align=\"right\" width=\"32%\">";
						    echo "      <strong><font face=\"Arial,Helvetica,sans-serif\" size=\"2\">$j. ".$p->t('global/datei').":&nbsp;</font></strong>";
						    echo "    </td>";
						    echo "    <td align=\"left\" width=\"68%\">";
							echo "      <input type=\"file\" name=\"userfile_$i\" size=\"30\">";
						    echo "    </td>";
						    echo "  </tr>\n";
						  }
						  

						?>
						<tr><td>&nbsp;</td><td>
                        <font face="Arial" size="2">&nbsp;<input type="checkbox" name="overwrite" value="overwrite">&nbsp;<?php echo $p->t('upload/dateienAutomatischUeberschreiben');?></font>                        
                       </td>
                </tr>
              </table></center>
              <?php
                  echo "<table class='tabcontent'>";
                  echo "  <tr>";
			      echo "    <td align=\"right\" width=\"59%\">";
			      echo "<span style='font-size:8pt;'>".$p->t('upload/maxUploadgroesse').": <b>15 MB</b></span>";
			      echo "      <input id=\"btnupload\" type=\"submit\" name=\"upload\" value=\"Upload\">";
			      echo "    </td>";
			      echo "    <td width=\"41%\">&nbsp;</td>";
			      echo "  </tr>";
			      echo "</table>";
			    ?>
		      </form>
              </td>
				</tr>
				<tr>

			  <td colSpan="5" height="24">
				<p align="center"><br><b>&nbsp;<?php echo $p->t('upload/umEinenOrdnerOderEineDatei');?></b></p><br/></td>
				</tr>
				<tr>

              <td align="middle" class="MarkLine" colSpan="5" height="24">
				<p align="left">
				<?php
					// Neues Verzeichnis erstellen
					if(isset($create_dir))
					{
						if(isset($new_dir_name_text) && $new_dir_name_text != "")
						{
							if(!check_filename($new_dir_name_text))
							{
								echo '<center><b>'.$p->t('upload/verzeichnisnameDarfNurBuchstaben').'!</b></center>';
							}
							else
							{
								$new_dir_name_text = trim($new_dir_name_text);
								if(isset($subdir) && $subdir != "")
								{
									if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
									{
										unset($subdir);
	
										$dest_create_dir = @dir($upload_root.'/'.$uploaddir);
									}
									else
									{
										$dest_create_dir = @dir($upload_root.'/'.$uploaddir.'/'.$subdir);
									}
								}
								else
								{
									$dest_create_dir = @dir($upload_root.'/'.$uploaddir);
								}
	
								if($dest_create_dir)
								{
									if(!@is_dir($dest_create_dir->path.'/'.$new_dir_name_text) && !@file_exists($dest_create_dir->path.'/'.$new_dir_name_text) && $new_dir_name_text != "")
									{
										@mkdir($dest_create_dir->path.'/'.$new_dir_name_text);
										exec('chmod 775 '.escapeshellarg($dest_create_dir->path.'/'.$new_dir_name_text));
	
										if($islector)
										{
											exec('sudo chown :teacher '.escapeshellarg($dest_create_dir->path.'/'.$new_dir_name_text));
										}
										else
										{
											exec('sudo chown :student '.escapeshellarg($dest_create_dir->path.'/'.$new_dir_name_text));
										}
									}
								}
							}

							unset($new_dir_name_text);
						}

						unset($create_dir);
					}

					if(isset($row_lesson) && !isset($short))
					{
						if(isset($subdir) && $subdir != "")
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$subdir\" enctype=\"multipart/form-data\">";
						}
						else
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel\" enctype=\"multipart/form-data\">";
						}
					}
					else if(isset($short))
					{
						if(isset($subdir) && $subdir != "")
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$subdir\" enctype=\"multipart/form-data\">";
						}
						else
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" enctype=\"multipart/form-data\">";
						}
					}
					else
					{
						if(isset($subdir) && $subdir != "")
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$subdir\" enctype=\"multipart/form-data\">";
						}
						else
						{
							echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id\" enctype=\"multipart/form-data\">";
						}
					}

					echo "<b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;".$p->t('upload/verzeichnisname').":&nbsp;";
					echo "<input id='new_dir_name_text' type=\"text\" name=\"new_dir_name_text\" size=\"30\">&nbsp;<input type=\"submit\" value=\"".$p->t('upload/neuesVerzeichnisErstellen')."\" name=\"create_dir\" onclick='return checkvz(\"new_dir_name_text\")'>";
					echo "</font></b>";
					echo "</td>";

					echo "</form>";
				?>
				</tr>
				<tr>

              <td align="middle" class="ContentHeader" colSpan="5" height="24"><font class="ContentHeader">
				<?php
				if(isset($subdir) && $subdir != "")
				{
					if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
					{
						unset($subdir);
						echo '<img src="../../../skin/images/folderup.gif" border="0">';
					}
					else
					{
						// XXX

						if(isset($short))
						{
							echo '<a href="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$short.'&subdir='.mb_substr($subdir, 0, mb_strrpos($subdir, '/',0)).'"><img src="../../../skin/images/folderup.gif" border="0"></a>';
						}
						else
						{
							echo '<a href="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$row_lesson->kuerzel.'&subdir='.substr($subdir, 0, mb_strrpos($subdir, '/',0)).'"><img src="../../../skin/images/folderup.gif" border="0"></a>';
						}
					}
				}
				else
				{
					echo '<img src="../../../skin/images/folderup.gif">';
				}
				echo "<b>".$p->t('upload/unterordnerVon');
				if(isset($subdir) && $subdir != "")
				{
					if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
					{
						unset($subdir);

						echo '/';
					}
					else
					{
						echo '/'.htmlentities($subdir,ENT_QUOTES,'UTF-8');
					}
				 }
				 else
				 {
					 echo '/';
				 }
			 ?>:</b></font></td>
				</tr>
				<tr>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/auswaehlen');?></b></td>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/name');?></b></td>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/aktionen');?></b></td>
					<td align="middle" class="ContentHeader"><b># <?php echo $p->t('upload/dateien');?></b></td>
					<td align="middle" height="20" class="ContentHeader"><b><?php echo $p->t('upload/kbGespeichert');?></b></td>
				</tr>
					<?php
						if(isset($subdir) && $subdir != "")
						{
							if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
							{
								unset($subdir);

								$dest_dir = @dir($upload_root.'/'.$uploaddir);
							}
							else
							{
								$dest_dir = @dir($upload_root.'/'.$uploaddir.'/'.$subdir);
							}
					    }
					    else
					    {
						    $dest_dir = @dir($upload_root.'/'.$uploaddir);
					    }

						if(isset($dest_dir) && $dest_dir != "")
						{
							$dir_count = 1;
							$dir_empty = true;

							if(isset($row_lesson) && !isset($short))
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
							}
							else if(isset($short))
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
							}
							else
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
								}
							}

							while($entry = $dest_dir->read())
							{
								unset($check_state);
								if($entry != "." && $entry != ".." && @is_dir($dest_dir->path.'/'.$entry))
								{
									$dir_empty = false;
									if(isset($_POST['_check_state_'.$dir_count]))
										$check_state = $_POST['_check_state_'.$dir_count];
									
									
									if(isset($check_state))
									{
										echo "<tr><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;<input type=\"checkbox\" name=\"_check_state_$dir_count\" checked>&nbsp;</font>";
									}
									else
									{
										echo "<tr><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;<input type=\"checkbox\" name=\"_check_state_$dir_count\">&nbsp;</font>";
									}

									if(isset($row_lesson) && !isset($short))
									{
										if(isset($subdir) && $subdir != "")
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$subdir/$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
										else
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
									}
									else if(isset($short))
									{
										if(isset($subdir) && $subdir != "")
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$subdir/$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
										else
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
									}
									else
									{
										if(isset($subdir) && $subdir != "")
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$subdir/$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
										else
										{
											echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$entry\"><img src=\"../../../skin/images/folder.gif\" border=\"0\">&nbsp;".htmlentities($entry,ENT_QUOTES,'UTF-8')."&nbsp;</a></font>";
										}
									}

									
									if(isset($_POST["new_dir_name".$dir_count]))
										$new_dir_name_ = $_POST["new_dir_name".$dir_count];

									if(isset($rename_dir) && isset($check_state))
									{
										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"text\" name=\"new_dir_name$dir_count\" id='dir_rename_text' value=\"$entry\">&nbsp;<input type=\"submit\" name=\"confirm_rename\" value=\"OK\" onclick=\"return checkvz('dir_rename_text')\"></font>";
									}
									else if(isset($confirm_rename) && isset($check_state))
									{
										if(isset($new_dir_name_) && $new_dir_name_ != "")
										{
											if(!@is_dir($dest_dir->path.'/'.$new_dir_name_) && !@file_exists($dest_dir->path.'/'.$new_dir_name_))
											{
												rename($dest_dir->path.'/'.$entry, $dest_dir->path.'/'.$new_dir_name_);

												$b_refresh_dir = true;

												unset($check_state);
											}
											else
											{
												unset($check_state);

												$b_refresh_dir = true;
											}
										}

										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"submit\" name=\"rename_dir\" value=\"".$p->t('global/umbenennen')."\">&nbsp;<input type=\"submit\" name=\"delete_dir\" value=\"".$p->t('global/loeschen')."\" onClick=\"del=true;\"></font>";
									}
									else if(isset($delete_dir) && isset($check_state))
									{
										if(@is_dir($dest_dir->path.'/'.$entry))
										{
											writeCISlog('DELETE', 'rm -r "'.$dest_dir->path.'/'.$entry.'"');
											exec('rm -r '.escapeshellarg($dest_dir->path.'/'.$entry));
										}

										unset($check_state);
									}
									else
									{
										if(@is_dir($dest_dir->path.'/'.$entry))
										{
											$tmp_dir_entry = dir($dest_dir->path.'/'.$entry);
										}

										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"submit\" name=\"rename_dir\" value=\"".$p->t('global/umbenennen')."\">&nbsp;<input type=\"submit\" name=\"delete_dir\" value=\"".$p->t('global/loeschen')."\" onClick=\"del=true;\"></font>";
									}

									if(isset($tmp_dir_entry))
									{
										while($sub_entry = $tmp_dir_entry->read())
										{
											if(!@is_dir($tmp_dir_entry->path.'/'.$sub_entry) && $sub_entry != "")
											{
												@$sub_dir_filesize += round(filesize($tmp_dir_entry->path.'/'.$sub_entry) / 1024);
												@$sub_dir_filecount++;
											}
										}
									}

									if(!isset($sub_dir_filesize))
									{
										$sub_dir_filesize = 0;
									}

									if(!isset($sub_dir_filecount))
									{
										$sub_dir_filecount = 0;
									}

									@$total_filesize += $sub_dir_filesize;
									@$total_filecount += $sub_dir_filecount;

									echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">$sub_dir_filecount</font>
											</b></td><td align=\"middle\" height=\"20\" class='MarkLine'>
											<b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">$sub_dir_filesize</font>
											</b>
										  </td></tr>";

									$sub_dir_filesize = 0;
									$sub_dir_filecount = 0;

									$dir_count++;
								}
							}

							if(isset($delete_dir))
							{
								unset($delete_dir);
								die("<script language=\"JavaScript\">document.location = document.location</script>");
							}

							if(isset($b_refresh_dir))
							{
								die("<script language=\"JavaScript\">document.location = document.location</script>");
							}

							if(!isset($dir_empty) || $dir_empty == true)
							{
								echo "<tr><td align=\"middle\" colSpan=\"5\" height=\"20\" bgcolor=\"#F2F2F2\"><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">";
								echo "&nbsp;&nbsp;".$p->t('upload/keineOrdnerGefunden').".";
								echo "</font></b></td></tr>";
							}

							echo "</form>";
						}
						else
						{
							$dir_count = 0;

							echo "<tr><td align=\"middle\" colSpan=\"5\" height=\"20\" bgcolor=\"#F2F2F2\"><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">";
							echo "&nbsp;&nbsp;".$p->t('upload/keineOrdnerGefunden').".";
							echo "</font></b></td></tr>";
						}
					?>
				<tr>

              <td align="middle" class="ContentHeader" colSpan="5" height="24"><font class="ContentHeader">
            <?php	if(isset($subdir) && $subdir != "")
			{
				if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
				{
					unset($subdir);

					echo '<img src="../../../skin/images/folderup.gif" border="0">';
				}
				else
				{
					// XXX

					if(isset($short))
					{
						echo '<a href="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$short.'&subdir='.substr($subdir, 0, mb_strrpos($subdir, '/',0)).'"><img src="../../../skin/images/folderup.gif" border="0"></a>';
					}
					else
					{
						echo '<a href="upload.php?course_id='.$course_id.'&term_id='.$term_id.'&short='.$row_lesson->kuerzel.'&subdir='.mb_substr($subdir, 0, mb_strrpos($subdir, '/',0)).'"><img src="../../../skin/images/folderup.gif" border="0"></a>';
					}
				}
			}
			else
			{
				echo '<img src="../../../skin/images/folderup.gif" border="0">';
			}
			?>
                <b><?php echo $p->t('upload/dateienImOrdner');?>
                <?php
					 if(isset($subdir) && $subdir != "")
					{
						 if(!@is_dir($upload_root.'/'.$uploaddir.'/'.$subdir))
						 {
							 unset($subdir);

							 echo '/';
						 }
						 else
						 {
						 	 echo '/'.htmlentities($subdir,ENT_QUOTES,'UTF-8');
						 }
					}
					else
					{
						echo '/';
					}
				?>:</b></font></td>
				</tr>
				<tr>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/auswaehlen');?></b></td>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/name');?></b></td>
					<td align="middle" class="ContentHeader"><b><?php echo $p->t('upload/aktionen');?></b></td>
					<td align="middle" class="ContentHeader"><b># <?php echo $p->t('upload/dateien');?></b></td>
					<td align="middle" height="20" class="ContentHeader"><b><?php echo $p->t('upload/kbGespeichert');?></b></td>
				</tr>
				<tr>
					<?php
						$dest_dir = @dir($dest_dir->path);

						if(isset($dest_dir) && $dest_dir != "")
						{
							$file_count = 0;

							if(isset($row_lesson) && !isset($short))
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$row_lesson->kuerzel\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
							}
							else if($short)
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&short=$short\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
							}
							else
							{
								if(isset($subdir) && $subdir != "")
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id&subdir=$subdir\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
								else
								{
									echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"upload.php?course_id=$course_id&term_id=$term_id\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmFile(this);\">";
								}
							}

							while($entry = $dest_dir->read())
							{
								if(!@is_dir($dest_dir->path.'/'.$entry) && substr($entry,0,1)!=".")
								{
									unset($check_state);
									$null_file = false;
									if(isset($_POST['_check_state_f'.$file_count]))
										$check_state = $_POST['_check_state_f'.$file_count];

									if(isset($check_state))
									{
										echo "<tr><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;<input type=\"checkbox\" name=\"_check_state_f$file_count\" checked>&nbsp;</font>";
									}
									else
									{
										echo "<tr><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;<input type=\"checkbox\" name=\"_check_state_f$file_count\">&nbsp;</font>";
									}
									if(!isset($link_cut))
										$link_cut='';
									$link_path = '../../../documents'.mb_substr($dest_dir->path, mb_strlen($link_cut)).'/'.urlencode($entry);
									//+ durch %20 ersetzten damit Files mit leerzeichen geoeffnet werden koennen
									$link_path = str_replace("+","%20",$link_path);
									echo "</b></td><td align=\"left\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><a href=\"$link_path\" target=\"_blank\">&nbsp;<img src=\"../../../skin/images/file.gif\" border=\"0\">&nbsp;".htmlentities($entry, ENT_QUOTES, 'UTF-8')."&nbsp;</a></font>";

									$new_file_name_='';
									if(isset($_POST['new_file_name'.$file_count]))
										$new_file_name_ = $_POST['new_file_name'.$file_count];

									if(stristr($new_file_name_,'..'))
										die('Invalid Parameter detected');

									if(isset($rename_file) && isset($check_state))
									{
										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"text\" name=\"new_file_name$file_count\" value=\"$entry\">&nbsp;<input type=\"submit\" name=\"confirm_rename\" value=\"OK\"></font>";
									}
									else if(isset($confirm_rename) && isset($check_state))
									{
										if(isset($new_file_name_) && $new_file_name_ != "")
										{
											if(!@file_exists($dest_dir->path.'/'.$new_file_name_) && !@is_dir($dest_dir->path.'/'.$new_file_name_))
											{
												if(!stristr($new_file_name_, '.php') && !stristr($new_file_name_, '.cgi') && !stristr($new_file_name_, '.pl') && !stristr($new_file_name_, '.phtml') && !stristr($new_file_name_,'.htaccess'))
												{
													rename($dest_dir->path.'/'.$entry, $dest_dir->path.'/'.$new_file_name_);

													$b_refresh_files = true;

													unset($check_state);
												}
												else
												{
													$unallowed_rename = true;
												}
											}
											else
											{
												unset($check_state);

												$b_refresh_files = true;
											}
										}

										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"submit\" name=\"rename_file\" value=\"".$p->t('global/umbenennen')."\">&nbsp;<input type=\"submit\" name=\"delete_file\" value=\"".$p->t('global/loeschen')."\" onClick=\"del=true;\"></font>";
									}
									else if(isset($delete_file) && isset($check_state))
									{
										if(!@is_dir($dest_dir->path.'/'.$entry))
										{
											writeCISlog('DELETE', 'rm -r "'.$dest_dir->path.'/'.$entry.'"');
											exec('rm -r '.escapeshellarg($dest_dir->path.'/'.$entry));
										}

										unset($check_state);
									}
									else
									{
										echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\"><input type=\"submit\" name=\"rename_file\" value=\"".$p->t('global/umbenennen')."\">&nbsp;<input type=\"submit\" name=\"delete_file\" value=\"".$p->t('global/loeschen')."\" onClick=\"del=true;\"></font>";
									}

									if(!isset($delete_file) && !isset($b_refresh_files))
									{
										$cur_filesize = round(filesize($dest_dir->path.'/'.$entry) / 1024);
										$file_last_access = date("j F Y, H:i:s", filemtime($dest_dir->path.'/'.$entry));
									}
									else
									{
										$cur_filesize=0;
										$file_last_access='';
									}

									echo "</b></td><td align=\"middle\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">$cur_filesize</font>
											</b></td><td align=\"middle\" height=\"20\" class='MarkLine'>
											<b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">$file_last_access</font>
											</b>
										  </td></tr>";

									@$total_filesize += $cur_filesize;

									$file_count++;
								}
							}

							if(!isset($total_filesize))
							{
								$total_filesize = 0;
							}

							if(isset($delete_file))
							{
								unset($delete_file);
								die("<script language=\"JavaScript\">document.location = document.location</script>");
							}

							if(isset($b_refresh_files))
							{
								die("<script language=\"JavaScript\">document.location = document.location</script>");
							}

							if(!isset($null_file) || $null_file == true)
							{
								echo "<tr><td align=\"middle\" colSpan=\"5\" height=\"20\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">";
								echo "&nbsp;&nbsp;".$p->t('upload/keineDateienGefunden').".";
								echo "</font></b></td></tr>";
							}
							else
							{
								echo "<tr><td align=\"middle\" colSpan=\"5\" height=\"20\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">&nbsp;</font></b></td></tr>";
							}

							echo "</form>";
						}
						else
						{
							$file_count = 0;

							echo "<tr><td align=\"middle\" colSpan=\"5\" height=\"20\" class='MarkLine'><b><font face=\"Arial,Helvetica,sans-serif\" color=\"#000000\" size=\"2\">";
							echo "&nbsp;&nbsp;Es wurden keine Dateien gefunden / Hauptordner nicht gefunden.";
							echo "</font></b></td></tr>";
							echo "<script language='Javascript'>document.getElementById('btnupload').disabled=true;</script>";
						}

						@$total_filecount += $file_count;

						if(!isset($total_filesize))
						{
							$total_filesize = 0;
						}
					?>
				
				<tr>
					<td align="middle" class="MarkLine" colSpan="5" height="20"><font face="Arial,Helvetica,sans-serif" size="2"><i><?php echo $p->t('upload/dateienInOrdnern',array($total_filecount,$dir_count,$total_filesize) );?></i></font></td>
				</tr>
				<?php
					if(isset($unallowed_upload) && $unallowed_upload == true)
					{
						unset($unallowed_upload);

						echo '<td align="middle" class="MarkLine" colSpan="5" height="20"><font size="2" color="#FF0000">'.$p->t('upload/dateiAufServerDateiformat').'.</font></td>';
					}
					else if(isset($unallowed_rename) && $unallowed_rename == true)
					{
						unset($unallowed_rename);

						echo '<td align="middle" class="MarkLine" colSpan="5" height="20"><font size="2" color="#FF0000">'.$p->t('upload/formattributInEinNeues').'.</font></td>';
					}
					if(isset($no_overwrite_error) && $no_overwrite_error == true)
					{
						unset($no_overwrite_error);
						echo '<td align="middle" class="MarkLine" colSpan="5" height="20"><font size="2" color="#FF0000">'.$p->t('upload/dateiExistiertBereits').'</font></td>';
					}

				?>
		</table>
	</body>
</html>
