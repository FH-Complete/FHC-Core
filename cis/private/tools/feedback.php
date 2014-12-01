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
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 *
 */

/*
 * Formular zum Senden eins Feedbacks an die CIS-Administratoren
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');	  	
require_once('../../../include/functions.inc.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');

$sprache = getSprache(); 
$p=new phrasen($sprache); 

if (!$db = new basis_db())
    	  die($p->t("global/fehlerBeimOeffnenDerDatenbankverbindung"));

if (!$user=get_uid())
	die($p->t("global/nichtAngemeldet").'! <a href="javascript:history.back()">Zur&uuml;ck</a>');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	//Mail versenden
	if(isset($feedback_submit))
	{
		$destination = MAIL_CIS;

		$sql_query = "SELECT DISTINCT vorname, nachname, (uid || '@".DOMAIN."') AS emailtw FROM campus.vw_benutzer WHERE uid=".$db->db_add_param($user)." LIMIT 1";

		$feedback_message = chop($txtFeedbackMessage);

		if($feedback_message != "")
		{
			if($result = $db->db_query($sql_query))
			{
				if($row = $db->db_fetch_object($result))
				{
					$mail = new mail($destination,'feedback@'.DOMAIN, "[CIS-Feedback]", $feedback_message);
					$mail->setReplyTo($row->emailtw);
					if($mail->send())
					{
						$message_sent=true;
					}
					else
					{
						$message_sent=false;
					}
				}
				else
				{
					$message_sent=false;
				}
			}
			else
			{
				$message_sent=false;
			}
		}
		else
		{
			$message_sent=false;
		}
	}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">

	function focusFirstElement()
	{
		if(document.FeedbackFormular.txtFeedbackMessage != null)
		{
			document.FeedbackFormular.txtFeedbackMessage.focus();
		}
	}

</script>
</head>

<body onLoad="focusFirstElement();">
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td><table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;<?php echo $p->t("feedback/titelFeedback");?></font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<td>
		  <form name="FeedbackFormular" method="post">
		  <?php
			if(isset($message_sent) && $message_sent)
			{
				echo '<h3>'.$p->t("feedback/vielenDankFuerIhrFeedback").'!</h3>';
		  		echo '<h3>'.$p->t("feedback/ihreNachrichtWurdeWeitergeleitet").'.</h3></td>';

				exit;
			}
			else if(isset($message_sent) && !$message_sent)
			{
				echo '<h3>'.$p->t("feedback/feedbackNichtWeitergeleitet").'!</h3>';
		  		echo '<h3>'.$p->t("feedback/wendenSieSichAnDieAdministration").'.</h3></td>';

				exit;
			}
		  ?>
		  <?php echo $p->t("feedback/absatz1");?>
		  	<textarea style="width: 99%; heigth: 166px" name="txtFeedbackMessage" rows="10" cols="70" maxlength="2000"></textarea></td>
			</tr>
			<tr>
			  <td nowrap>
			  	<input type="hidden" name="feedback_submit">
			  	<input type="submit" name="btnSend" value="<?php echo $p->t("global/abschicken");?>">&nbsp;
				<input type="reset" name="btnCancel" value="<?php echo $p->t("global/zuruecksetzen");?>" onClick="document.FeedbackFormular.txtFeedbackMessage.focus();"></td>
			</tr>
		  </table>
	  	  </form>
		</td>
	  </tr>
    </table></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
