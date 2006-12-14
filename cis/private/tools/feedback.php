<?php	
    include('../../config.inc.php');
    include('../../../include/functions.inc.php');
    
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');
        
	$user = get_uid();
	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	if(isset($feedback_submit) && (!isset($message_sent) || $message_sent == "no"))
	{
		$destination = 'cis@technikum-wien.at';
		
		$sql_query = "SELECT DISTINCT vorname, nachname, (uid || '@technikum-wien.at') AS emailtw FROM campus.vw_benutzer WHERE uid='$user' LIMIT 1";
		
		$feedback_message = chop($txtFeedbackMessage);

		if($feedback_message != "")
		{
			if($result = pg_query($sql_conn, $sql_query))
			{			
				if($row = pg_fetch_object($result))
				{			
					if(mail($destination, "[CIS-Feedback]", $feedback_message, "FROM: feedback@technikum-wien.at\nREPLY-TO: $row->emailtw\n\n"))
					{					
						echo '<script language="JavaScript">';
						echo '	document.location.href = document.location.href + "?message_sent=yes";';
						echo '</script>';
					}
					else 
					{
						echo '<script language="JavaScript">';
						echo '	document.location.href = document.location.href + "?message_sent=no";';
						echo '</script>';
					}
				}
				else 
				{
					echo '<script language="JavaScript">';
					echo '	document.location.href = document.location.href + "?message_sent=no";';
					echo '</script>';
				}
			}
			else 
			{
				echo '<script language="JavaScript">';
				echo '	document.location.href = document.location.href + "?message_sent=no";';
				echo '</script>';
			}
		}
		else
		{
			echo '<script language="JavaScript">';
			echo '	document.location.href = document.location.href + "?message_sent=no";';
			echo '</script>';
		}
		
		exit;
	}
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">

<script language="JavaScript">

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
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
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
			if(isset($message_sent) && $message_sent == "yes")
			{
				echo "<font class=\"headline\">Vielen Dank f&uuml;r Ihr Feedback!</font><br>";
		  		echo "<font class=\"subline\">Ihre Nachricht wurde an das zust&auml;ndige Personal weitergeleitet.</font></td>";
				
				exit;
			}
			else if(isset($message_sent) && $message_sent == "no")
			{
				echo "<font class=\"headline\">Ihr Feedback wurde nicht weitergeleitet!</font><br>";
		  		echo "<font class=\"subline\">Bitte wenden Sie sich an die Administration.</font></td>";
				
				exit;
			}
		  ?>
		  <font class="headline">Ihre Meinung z&auml;hlt!</font><br>
		  <font class="subline">Hier k&ouml;nnen Sie uns Feedback geben.</font>
		  <p>Helfen Sie mit, unseren Service zu verbessern und geben Sie uns hier Ihr Feedback. Haben Sie spezielle W&uuml;nsche und Anregungen f&uuml;r uns, vermissen Sie wichtige Informationen oder wollen Sie uns mal richtig die Meinung sagen? F&uuml;r Ihre Beitr&auml;ge haben wir immer ein offenes Ohr. Denn nur wenn wir Ihre Meinung kennen, k&ouml;nnen wir auf Ihre Belange und W&uuml;nsche eingehen.</p>
		  <p>Für technische Gebrechen verwenden Sie bitte das <a href='http://bug.technikum-wien.at' target="_blank" class='Item'>Bugtracking-System</a></p>
		  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
		  	<tr>
			  <td nowrap><br>
			    Bitte geben Sie hier Ihr Feedback ein:<br>
				<textarea class="TextBox" style="width: 99%; heigth: 166px" name="txtFeedbackMessage" rows="10" cols="70" maxlength="2000"></textarea></td>
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
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>