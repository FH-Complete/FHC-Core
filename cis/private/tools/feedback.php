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
    require_once('../../../include/functions.inc.php');
    require_once('../../../include/mail.class.php');

	$user = get_uid();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	//Mail versenden
	if(isset($feedback_submit))
	{
		$destination = MAIL_CIS;

		$sql_query = "SELECT DISTINCT vorname, nachname, (uid || '@".DOMAIN."') AS emailtw FROM campus.vw_benutzer WHERE uid='$user' LIMIT 1";

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
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Kommunikation - Feedback</font></td>
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
				echo "<h3>Vielen Dank f&uuml;r Ihr Feedback!</h3>";
		  		echo "<h3>Ihre Nachricht wurde an das zust&auml;ndige Personal weitergeleitet.</h3></td>";

				exit;
			}
			else if(isset($message_sent) && !$message_sent)
			{
				echo "<h3>Ihr Feedback wurde nicht weitergeleitet!</h3>";
		  		echo "<h3>Bitte wenden Sie sich an die Administration.</h3></td>";

				exit;
			}
		  ?>
		  <h3>Ihre Meinung z&auml;hlt!</h3>
		  <h3>Hier k&ouml;nnen Sie uns Feedback geben.</h3>
		  <p>Helfen Sie mit, unseren Service zu verbessern und geben Sie uns hier Ihr Feedback. Haben Sie spezielle W&uuml;nsche und Anregungen f&uuml;r uns, vermissen Sie wichtige Informationen oder wollen Sie uns mal richtig die Meinung sagen? F&uuml;r Ihre Beitr&auml;ge haben wir immer ein offenes Ohr. Denn nur wenn wir Ihre Meinung kennen, k&ouml;nnen wir auf Ihre Belange und W&uuml;nsche eingehen.</p>
		  <p>Für technische Gebrechen verwenden Sie bitte das <a href='https://bug.technikum-wien.at' target="_blank" class='Item'>Bugtracking-System</a></p>
		  <table class="tabcontent">
		  	<tr>
			  <td nowrap><br>
			    Bitte geben Sie hier Ihr Feedback ein:<br>
				<textarea style="width: 99%; heigth: 166px" name="txtFeedbackMessage" rows="10" cols="70" maxlength="2000"></textarea></td>
			</tr>
			<tr>
			  <td nowrap>
			  	<input type="hidden" name="feedback_submit">
			  	<input type="submit" name="btnSend" value="Abschicken">&nbsp;
				<input type="reset" name="btnCancel" value="Zur&uuml;cksetzen" onClick="document.FeedbackFormular.txtFeedbackMessage.focus();"></td>
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