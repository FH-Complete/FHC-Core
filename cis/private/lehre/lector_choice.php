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
require_once('../../../include/studiengang.class.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);
if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$user = get_uid();

$rechte= new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(isset($_GET['lvid']) && is_numeric($_GET['lvid']))
	$lvid=$_GET['lvid'];
else
	die($p->t('global/fehlerBeiDerParameteruebergabe'));

$lv_obj = new lehrveranstaltung();
if(!$lv_obj->load($lvid))
	die($p->t('upload/fehlerBeimLadenDerLv'));

$stg_obj=new studiengang();
$stg_obj->load($lv_obj->studiengang_kz);

$openpath="../../../documents/".strtolower($stg_obj->kuerzel)."/".$lv_obj->semester."/".strtolower($lv_obj->lehreverzeichnis)."/upload/";

$stsem_obj = new studiensemester();
$stsem = $stsem_obj->getaktorNext();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">

	var del = false;

	function ConfirmDir(handle)
	{
		if(del)
		{
			del = false;

			return confirm("<?php echo $p->t('upload/wollenSieUploadWirklichLeeren');?>!");
		}
	}
</script>
</head>

<body>
<table class="tabcontent" id="inhalt">
	<tr>
		<td class="tdwidth10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;<?php echo $p->t('upload/studentenUploadVerwalten');?></font>
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
				$qry = "SELECT distinct oe_kurzbz FROM lehre.tbl_lehreinheit JOIN lehre.tbl_lehrveranstaltung as lehrfach ON(tbl_lehreinheit.lehrfach_id=lehrfach.lehrveranstaltung_id) JOIN public.tbl_fachbereich USING(oe_kurzbz) WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER);
				if($result = $db->db_query($qry))
				{
					while($row = $db->db_fetch_object($result))
					{
						if($rechte->isBerechtigt('lehre',$row->oe_kurzbz,null))
							$is_berechtigt=true;
					}
				}
				else
					die($p->t('global/fehlerBeimLesenAusDatenbank'));

				if($rechte->isBerechtigt('lehre',$lv_obj->studiengang_kz))
					$is_berechtigt=true;
				if($rechte->isBerechtigt('admin',$lv_obj->studiengang_kz))
					$is_berechtigt=true;

				$sql_query = "SELECT DISTINCT vorname, nachname, uid FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, campus.vw_mitarbeiter
								WHERE tbl_lehreinheit.lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." AND
								tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
								mitarbeiter_uid=uid AND uid=".$db->db_add_param($user)." ORDER BY nachname, vorname, uid";
								//studiensemester_kurzbz='$stsem' AND

				if($result = $db->db_query($sql_query))
				{
					if($db->db_num_rows($result)>0)
					{
						$is_berechtigt=true;
					}
				}
				else
					die($p->t('global/fehlerBeimLesenAusDatenbank'));

				if(!$is_berechtigt)
					die($p->t('global/keineBerechtigungFuerDieseSeite'));

				//echo '<span class="error">ACHTUNG: Der Studentenupload steht nur noch bis zum Ende des Wintersemesters 2008 zur Verfügung</span><br><br>';
				echo "<form accept-charset=\"UTF-8\" method=\"POST\" action=\"lector_choice.php?lvid=$lvid\" enctype=\"multipart/form-data\" onSubmit=\"return ConfirmDir(this);\">";

				if(isset($_POST['delete_dir']))
				{
					if(@is_dir($openpath))
					{
						if(chdir($openpath))
						{
							exec('rm -r *');
							writeCISlog('DELETE', "rm -r $openpath/*");
						}
						else
							echo $p->t('upload/fehlerBeimLoeschenDesOrdners');
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
							echo "<li><a class=\"Item2\" href=\"$openpath\" target=\"_blank\">".$p->t('upload/studentenUploadEinsehen')."</a></li>";
							echo "<li>".$p->t('upload/studentenUploadverzeichnis')."&nbsp;<input type=\"submit\" name=\"delete_dir\" value=\"".$p->t('upload/leeren')."\" onClick=\"del=true;\"></li>";
						}
						else
						{
							echo '<li>'.$p->t('upload/studentenUploadEinsehen').'</li>';
							echo '<li>'.$p->t('upload/studentenUploadverzeichnisLeeren').'</li>';
						}
					}
					else
					{
						echo '<li>'.$p->t('upload/studentenUploadEinsehen').'</li>';
						echo '<li>'.$p->t('upload/studentenUploadverzeichnisLeeren').'</li>';
					}
				}
				else
				{
					die('<p align="center">'.$p->t('upload/esWurdeKeinPfadDefiniert').'</p>');
				}

				echo '</form>';
			?>
		</td>
		<td class="tdwidth30">&nbsp;</td>
	</tr>
</table>
</body>
</html>
