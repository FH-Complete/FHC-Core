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
/* 
 * Lehrveranstaltungsfeedback
*/
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/studiensemester.class.php');
require_once('../../../include/lehrveranstaltung.class.php');
require_once('../../../include/feedback.class.php');
require_once('../../../include/phrasen.class.php');

if (!$db = new basis_db())
	die('Fehler beim Herstellen der Datenbankverbindung');

$sprache = getSprache();
$p = new phrasen($sprache);

$user = get_uid();

if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
   die($p->t('global/fehlerBeiDerParameteruebergabe'));
?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<body id="inhalt">
<?php

    $lvid = $_GET['lvid'];
	$stsem_obj = new studiensemester();
	$stsem = $stsem_obj->getaktorNext();
	if(isset($_POST['feedback_message']))
	   $feedback_message=$_POST['feedback_message'];
    if(isset($_POST['feedback_subject']))
	   $feedback_subject=$_POST['feedback_subject'];

echo '<form accept-charset="UTF-8" method="POST" action="feedback.php?lvid='.$db->convert_html_chars($lvid).'" enctype="multipart/form-data">';
?>

<table class="tabcontent">
	<tr>
		<td width="3%">&nbsp;</td>
		<td width="97%">
			<table class="tabcontent">
			  <tr>
			<?php
				$lv_obj = new lehrveranstaltung();
				if($lv_obj->load($lvid))
					$short_name = $lv_obj->bezeichnung;
				else
					die($lv_obj->errormsg);
			?>
          <td class='ContentHeader'><font class='ContentHeader'>&nbsp;<?php echo $db->convert_html_chars($short_name); ?> - Feedback
            an:
			<?php
			$qry = "SELECT 
						studiensemester_kurzbz 
					FROM lehre.tbl_lehreinheit 
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) 
					WHERE lehrveranstaltung_id=".$db->db_add_param($lvid, FHC_INTEGER)." ORDER BY ende DESC LIMIT 1";

			$result = $db->db_query($qry);
			$row = $db->db_fetch_object($result);
			$qry = "SELECT distinct vorname, nachname, uid 
					FROM campus.vw_mitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter 
					WHERE uid=mitarbeiter_uid AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id 
					AND lehrveranstaltung_id=".$db->db_add_param($lvid)." 
					AND studiensemester_kurzbz=".$db->db_add_param($row->studiensemester_kurzbz);

			if(!$result=$db->db_query($qry))
				die('Fehler beim Auslesen der Lektoren');
			$rows = $db->db_num_rows($result);
			$i=0;
			while($row = $db->db_fetch_object($result))
			{
				echo $row->vorname.' '.$row->nachname;
				$i++;
				if($i<$rows)
					echo ', ';
			}
			?>
			</font>
    		</td>
		  </tr>
		</table>

	<br>
      <p><b><?php echo $p->t('global/betreff');?>:&nbsp;</b>
	    <?php
		echo '<input type="text" name="feedback_subject" size="54"><br>';
		echo '<textarea rows="7" name="feedback_message" cols="47"></textarea><br>';
		echo '<input type="submit" value="'.$p->t('global/abschicken').'" name="send_feedback">';
		?>
        &nbsp;
        <input type="reset" value="<?php echo $p->t('global/zuruecksetzen');?>" name="reset_message">
      </p>
      <?php
		if(isset($feedback_message) && $feedback_message != "")
		{
			$fb_obj = new feedback();
			$fb_obj->betreff = $feedback_subject;
			$fb_obj->text = $feedback_message;
			$fb_obj->datum = date('Y-m-d');
			$fb_obj->uid = $user;
			$fb_obj->lehrveranstaltung_id = $lvid;
			$fb_obj->new = true;

			if($fb_obj->save())
				echo "<script language=\"JavaScript\">document.location = document.location + \"&message_sent=true\"</script>";
			else
				echo $fb_obj->errormsg." save<br>";
		}

		if(isset($message_sent) && $message_sent == true)
		{
			echo $p->t('feedback/erfolgreichEingetragen').'<br><br><br>';
		}

		$fb_obj = new feedback();
		if($fb_obj->load_feedback($lvid))
		{
			echo '<table class="tabcontent">';

			foreach($fb_obj->result as $row)
			{
				$sql_query = "SELECT vorname, nachname FROM campus.vw_benutzer WHERE uid=".$db->db_add_param($row->uid);

				if($result_person = $db->db_query($sql_query))
				{
					if($row_pers=$db->db_fetch_object($result_person))
					{

						echo '<tr>';
						echo '	<td class="ContentHeader" width="90%"><font class="ContentHeader"><strong>&nbsp;'.$db->convert_html_chars($row->betreff).'</strong></font></td>';
						echo '  <td class="ContentHeader" align="right"><font class="ContentHeader">'.$db->convert_html_chars($row->datum).'</font></td>';

						echo '</tr>';
						echo '<tr>';
						echo '	<td class="MarkLine" colspan=2>'.nl2br($db->convert_html_chars($row->text)).'</td>';
						echo '</tr>';
						echo '<tr>';
						echo '	<td>&nbsp;</td>';
						echo '</tr>';
					}
				}
			}
			echo '</table>';
		}
		else
			echo $p->t('global/fehleraufgetreten').' '.$fb_obj->errormsg;
	?>
    </td>
	</tr>
</table>
</form>
</body>
</html>
