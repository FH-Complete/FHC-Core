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
    require_once('../../../include/studiengang.class.php');
    require_once('../../../include/studiensemester.class.php');
    require_once('../../../include/lehrveranstaltung.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');
    
	$user = get_uid();
	
	$rechte= new benutzerberechtigung($sql_conn);
	$rechte->getBerechtigungen($user);

	if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
		$lvid=$_GET['lvid'];
	else 
		die('Fehler bei der Parameteruebergabe');
	
	$lv_obj = new lehrveranstaltung($sql_conn);
	$lv_obj->load($lvid);
	
	$stg_obj=new studiengang($sql_conn);
	$stg_obj->load($lv_obj->studiengang_kz);
	
	$openpath="../../../documents/".strtolower($stg_obj->kuerzel)."/".$lv_obj->semester."/".strtolower($lv_obj->lehreverzeichnis)."/upload/";

	$stsem_obj = new studiensemester($sql_conn);
	$stsem = $stsem_obj->getaktorNext();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">

<script language="JavaScript">
	
	var del = false;
	
	function ConfirmDir(handle)
	{
		if(del)
		{
			del = false;
			
			return confirm("Wollen Sie das Uploadverzeichnis wirklich leeren? Dieser Vorgang ist unwiderruflich!");
		}
	}
</script>
</head>

<body>
<table border="0" cellspacing="0" cellpadding="0"width="100%">
	<tr>
		<td width="10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;Studenten-Upload verwalten</font>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			<?php
				$is_berechtigt=false;
				$qry = "SELECT distinct fachbereich_kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrfach USING(lehrfach_id) WHERE lehrveranstaltung_id='$lvid'";
				if($result = pg_query($sql_conn, $qry))
				{
					while($row = pg_fetch_object($result))
					{
						if($rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz,null,$row->fachbereich_kurzbz))
							$is_berechtigt=true;
					}
				}
				else 
					die('Fehler beim Lesen aus der Datenbank');
				
				if($rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
					$is_berechtigt=true;
				if($rechte->isBerechtigt('admin',$lv_obj->studiengang_kz))
					$is_berechtigt=true;
				
				$sql_query = "SELECT DISTINCT vorname, nachname, uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, campus.vw_mitarbeiter 
								WHERE tbl_lehreinheit.lehrveranstaltung_id='$lvid' AND
								tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
								mitarbeiter_uid=uid AND uid='$user' ORDER BY nachname, vorname, uid";
								//studiensemester_kurzbz='$stsem' AND
			
				if($result = pg_query($sql_conn, $sql_query))
				{
					if(pg_num_rows($result)>0)
					{
						$is_berechtigt=true;
					}
				}
				else 
					die('Fehler beim Lesen aus der Datenbank');
				
				if(!$is_berechtigt)
					die('Sie haben keine Berechtigung fuer diesen Bereich');
				
				echo "<form method=\"POST\" action=\"lector_choice.php?lvid=$lvid\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";
				
				if(isset($delete_dir))
				{
					if(@is_dir($openpath))
					{
						if(chdir($openpath))
						{
							exec('rm -r *');
							writeCISlog('DELETE', "rm -r $openpath/*");
						}
						else 
							echo 'Fehler beim loeschen des Ordners';
					}
					echo "<script language=\"JavaScript\">document.location = \"lector_choice.php?lvid=$lvid\"</script>";
				}
				
				if(isset($openpath) && $openpath != "")
				{
					if(@is_dir($openpath))
					{
						$dest_dir = @dir($openpath);
						
						$dir_empty = true;
						
						while($entry = $dest_dir->read())
						{
							if($entry != "." && $entry != "..")
							{
								$dir_empty = false;
								
								break;
							}
						}
						
						if(!$dir_empty)
						{
							echo "<li><a class=\"Item2\" href=\"$openpath\" target=\"_blank\">Studenten-Upload einsehen</a></li>";
							echo "<li>Studenten-Uploadverzeichnis&nbsp;<input type=\"submit\" name=\"delete_dir\" value=\"leeren\" onClick=\"del=true;\"></li>";
						}
						else
						{
							echo '<li>Studenten-Upload einsehen</li>';
							echo '<li>Studenten-Uploadverzeichnis leeren</li>';
						}
					}
					else
					{
						echo '<li>Studenten-Upload einsehen</li>';
						echo '<li>Studenten-Uploadverzeichnis leeren</li>';
					}
				}
				else
				{
					die('<p align="center">Es wurde kein Pfad definiert.</p>');
				}
				
				echo '</form>';
			?>
		</td>
		<td width="30">&nbsp;</td>
	</tr>
</table>
</body>
</html>