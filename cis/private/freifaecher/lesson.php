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
    require_once('../../../include/lehrveranstaltung.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die("Fehler beim herstellen der Datenbankverbindung");

	$user = get_uid();

	if(check_lektor($user,$sql_conn))
       $is_lector=true;
    else
       $is_lector=false;

    if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
    	die('Fehlerhafte Parameteruebergabe');
    else
    	$lvid = $_GET['lvid'];

	$sql_query = "SELECT DISTINCT lehreverzeichnis, bezeichnung FROM lehre.tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$lvid'";

	if(!$result_lessons = pg_query($sql_conn, $sql_query))
		die('Freifach konnte nicht aufgeloest werden');

	$num_rows_lessons = pg_num_rows($result_lessons);

	if(!$row = pg_fetch_object($result_lessons))
		die('Freifach konnte nicht aufgeloest werden');

	$short_name = $row->bezeichnung;
	$short_short_name = $row->lehreverzeichnis;

	$rechte=new benutzerberechtigung($sql_conn);
	$rechte->getBerechtigungen($user);
	$user_is_allowed_to_upload=false;
	
	$lv_obj = new lehrveranstaltung($sql_conn);
	$lv_obj->load($lvid);
	$lv=$lv_obj;

	$course_id = $lv->studiengang_kz;
	$term_id = $lv->semester;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>

<body>
<table class="tabcontent" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;
		<?php
			echo $short_name;

		?></font></td>
	</tr>
	<tr>
		<td valign="top">&nbsp;</td>
		<td>
		<?php
			$qry = "SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) WHERE lehrveranstaltung_id='$lvid' ORDER BY ende DESC LIMIT 1";
			$angezeigtes_stsem ='';
			if($result = pg_query($sql_conn, $qry))
				if($row = pg_fetch_object($result))
					$angezeigtes_stsem = $row->studiensemester_kurzbz;

			$qry = "SELECT distinct vorname, nachname, tbl_benutzer.uid as uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person WHERE tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND tbl_person.person_id=tbl_benutzer.person_id AND lehrveranstaltung_id='$lvid' AND tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND tbl_person.aktiv=true AND studiensemester_kurzbz='$angezeigtes_stsem' ORDER BY nachname, vorname";
			if(!$result = pg_query($sql_conn, $qry))
				die('Fehler bei Abfrage'.$qry);

			$num_rows_result = pg_num_rows($result);

			if(!($num_rows_result > 0))
			{
				echo 'Derzeit sind keine Lektoren f&uuml;r dieses Fach zugeteilt.';
			}
			else
			{
				$i=1;
				while($row_lector=pg_fetch_object($result))
				{
					if($row_lector==$user)
						$user_is_allowed_to_upload=true;

					echo '<a class="Item2" href="mailto:'.$row_lector->uid.'@technikum-wien.at">'.$row_lector->vorname.' '.$row_lector->nachname.'</a>';
					if(!($i == $num_rows_result))
					{
						echo ',';
					}

					$i++;
				}
			}
		?></td>
	</tr>
	<tr>
		<td class='tdvertical'>&nbsp;</td>
		<td class='tdvertical'>&nbsp;</td>
	</tr>
	<tr>
		<td class='tdvertical'>&nbsp;</td>
		<td class='tdvertical'>
		<table class="tabcontent">
			<tr>
			    <td class='tdvertical' align="center">
				  <?php
				  	$dest_dir = @dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/semesterplan');

					if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/freifaecher'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/semesterplan'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'/semesterplan"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'/semesterplan"');
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
					if($is_lector > 0 ) //islector=True
					{
						if($user_is_allowed_to_upload || $rechte->isBerechtigt('admin',0)|| $rechte->isBerechtigt('lehre',0))
						{
							echo '<br><a onClick="javascript:window.open(\'semupload.php?lvid='.$lvid.'\',\'_blank\',\'width=400,height=300,location=no,menubar=no,status=no,toolbar=no\');">Upload</a>';
							echo '&nbsp;&nbsp;&nbsp;<a href="semdownhlp.php" >Vorlage</a>';
						}
					}
			    ?>
				  <p>&nbsp;</p>
				</td>
				<td class='tdvertical' align="center">
				<?php
					$dest_dir = @dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/download');

					if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/freifaecher'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/download'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'/download"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'/download"');
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
						echo '<img src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
						echo '<strong>Download</strong>';
						echo '</a>';
					}
					else
					{
						echo '<img src="../../../skin/images/button_dl.jpg" width="67" height="45"><br>';
						echo '<strong>Download</strong>';
					}
			    ?>
			      <p>&nbsp;</p>
			    </td>
			</tr>

			<tr>
				<td class='tdvertical' align="center">
				<?php
					/*$dest_dir = @dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/leistung');

					if(!@is_dir($dest_dir->path))
					{
						if(!is_dir('../../../documents/freifaecher'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name)))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'"');
						}
						if(!is_dir('../../../documents/freifaecher/'.strtolower($short_short_name).'/'.strtolower($short_short_name).'_leistung'))
						{
							@exec('mkdir -m 775 "../../../documents/freifaecher/'.strtolower($short_short_name).'/'.strtolower($short_short_name).'_leistung"');
							exec('sudo chown www-data:teacher "'.$GLOBALS["DOCUMENT_ROOT"].'/documents/freifaecher/'.strtolower($short_short_name).'/'.strtolower($short_short_name).'_leistung"');
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
						echo '<img src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
						echo '<strong>Leistungsbeurteilung</strong>';
						echo '</a>';
					}
					else
					{
						echo '<img src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
						echo '<strong>Leistungsbeurteilung</strong>';
					}*/
			    ?>
				
				
<?php
 			echo '<img src="../../../skin/images/button_lb.jpg" width="67" height="45"><br>';
          	if($is_lector)
          	{
				//Anwesenheitsliste

				echo "<b><a href='../lehre/anwesenheitsliste.php?stg_kz=$course_id&sem=$term_id&lvid=$lvid&stsem=$angezeigtes_stsem' class='Item'>Anwesenheits- und Notenlisten</a></b><br>";
          	}
?>				
				<p>&nbsp;</p>
				</td>

          <td class='tdvertical' align="center">&nbsp;</td>
			</tr>
			<tr>
				<td class='tdvertical' align="center"><?php

				  echo "<a href=\"../lehre/feedback.php?lvid=$lvid\" target=\"_blank\"><img src=\"../../../skin/images/button_fb.jpg\" width=\"67\" height=\"45\"><br>
				     <strong>Feedback</strong></a>";

				?>
            <p>&nbsp;</p>
				</td>
				<td class='tdvertical' align="center">
				<a href="<?php
				  			echo 'news://cis.technikum-wien.at/'.strtolower($short_short_name);
				  		   ?>">
				<img src="../../../skin/images/button_ng.jpg" width="67" height="45"><br>
				<strong>Newsgroups</strong>
				</a>
				<p>&nbsp;</p>
				</td>
			</tr>
		</table>
		</td>
		<td class="tdwidth30">&nbsp;</td>
	</tr>
</table>

</body>
</html>
