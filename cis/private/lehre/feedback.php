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
/* @date 27.10.2005
   @brief Zeigt die Daten aus der tbl_lvinfo an

   @edit	08-11-2006 Versionierung wurde entfernt. Alle eintraege werden jetzt im WS2007
   					   abgespeichert
   			03-02-2006 Anpassung an die neue Datenbank
*/

	require_once('../../../config/cis.config.inc.php');
// ------------------------------------------------------------------------------------------
//	Datenbankanbindung 
// ------------------------------------------------------------------------------------------
	require_once('../../../include/basis_db.class.php');
	if (!$db = new basis_db())
			die('Fehler beim Herstellen der Datenbankverbindung');
			
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../../../include/feedback.class.php');

	$user = get_uid();
	if(check_lektor($user))
	       $is_lector=true;
	if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
	   die('Fehler bei der Uebergabe der Parameter');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
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
	if(isset($POST["feedback_message"]))
	   $feedback_message=$POST["feedback_message"];

?>

<table class="tabcontent">
	<tr>
		<td width="3%">&nbsp;</td>
			<?php
				echo '<form accept-charset="UTF-8" method="POST" action="feedback.php?lvid='.$lvid.'" enctype="multipart/form-data">';
			?>
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
          <td class='ContentHeader'><font class='ContentHeader'>&nbsp;<?php echo $short_name; ?> - Feedback
            an:
			<?php
			$qry = "SELECT studiensemester_kurzbz FROM lehre.tbl_lehreinheit JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) WHERE lehrveranstaltung_id='$lvid' ORDER BY ende DESC LIMIT 1";
			$result = $db->db_query($qry);
			$row = $db->db_fetch_object($result);
			$qry = "SELECT distinct vorname, nachname, uid FROM campus.vw_mitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE uid=mitarbeiter_uid AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND lehrveranstaltung_id='$lvid' AND studiensemester_kurzbz='$row->studiensemester_kurzbz'";
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
      <p><b>Betreff:&nbsp;</b>
	    <?php
			if(isset($edit_id) && $edit_id != "" && !isset($edit_break))
			{
				$fb_obj = new feedback();
				if($fb_obj->load($edit_id))
				{
					echo '<input type="text" name="feedback_subject" value="'.$fb_obj->betreff.'" size="54"><br>';
					echo '<textarea rows="7" name="feedback_message" cols="47">'.$fb_obj->text.'</textarea><br>';
					echo '<input type="submit" value="&Auml;ndern" name="edit_feedback">&nbsp;';
					echo '<input type="submit" value="Abbrechen" name="edit_break">';
				}
				else
					echo $fb_obj->errormsg.'<br>';
			}
			else
			{
				echo '<input type="text" name="feedback_subject" size="54"><br>';
				echo '<textarea rows="7" name="feedback_message" cols="47"></textarea><br>';
				echo '<input type="submit" value="Abschicken" name="send_feedback">';
			}
		?>
        &nbsp;
        <input type="reset" value="Zur&uuml;cksetzen" name="reset_message">
      </p>
      <?php
		if(isset($feedback_message) && $feedback_message != "")
		{
			if(isset($edit_feedback))
			{
				$fb_obj = new feedback();
				$fb_obj->betreff = $feedback_subject;
				$fb_obj->text = $feedback_message;
				$fb_obj->feedback_id = $edit_id;
				$fb_obj->datum = date('Y-m-d');
				$fb_obj->uid = $user;
				$fb_obj->lehrveranstaltung_id = $lvid;
				$fb_obj->new = false;

				if($fb_obj->save())
					echo "<script language=\"JavaScript\">document.location = document.location + \"&message_sent=true\"</script>";
				else
					echo $fb_obj->errormsg."<br>";
			}

			if(!isset($edit_id) && !isset($edit_break) && !isset($edit_feedback))
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
		}

		if(isset($message_sent) && $message_sent == true)
		{
			echo 'Die Nachricht wurde erfolgreich eingetragen.<br><br><br>';
		}

		$fb_obj = new feedback();
		if($fb_obj->load_feedback($lvid))
		{
			echo '<table class="tabcontent">';

			foreach($fb_obj->result as $row)
			{
				$sql_query = "SELECT vorname, nachname FROM campus.vw_benutzer WHERE uid='$row->uid'";

				if($result_person = $db->db_query($sql_query))
				{
					if($row_pers=$db->db_fetch_object($result_person))
					{

						echo '<tr>';
						echo '	<td class="ContentHeader" width="90%"><font class="ContentHeader"><strong>&nbsp;'.$row->betreff.'</font></td>';
						//echo '	<td class="ContentHeader" width="30%"><font class="ContentHeader">&nbsp;</font></td>'; //'.$row_pers->vorname.' '.$row_pers->nachname.'
						echo '  <td class="ContentHeader" align="right"><font class="ContentHeader">'.$row->datum.'</font></td>';

						echo '</tr>';
						echo '<tr>';
						echo '	<td class="MarkLine" colspan=2>'.nl2br($row->text).'</td>';
						//echo '	<td class="MarkLine">&nbsp;</td>';
						//echo '	<td class="MarkLine" colspan=2>&nbsp;</td>';
						//echo '	<td class="MarkLine">&nbsp;</td>';
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
			echo 'Fehler beim laden der Daten '.$fb_obj->errormsg;
	?>
    </td>
	</form>
	</tr>
</table>

</body>
</html>