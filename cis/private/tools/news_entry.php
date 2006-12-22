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
	require_once('../../../include/news.class.php');
    
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die("Fehler beim öffnen der Datenbankverbindung");
    
	$user = get_uid();
	
	$rechte = new benutzerberechtigung($sql_conn);
    $rechte->getBerechtigungen($user);
    
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
	
	$sql_query = "SELECT count(*) as anzahl FROM tbl_benutzerfunktion WHERE uid='$user' AND funktion_kurzbz='infr'";
				
	if(!$row=pg_fetch_object(pg_query($sql_conn, $sql_query)))
		die('Fehler beim lesen aus der Datenbank');
	
	if($row->anzahl>0 || $rechte->isBerechtigt('admin'))
		$berechtigt=true;
	else 
		$berechtigt=false;
		
	if(isset($_GET['news_id']))
		$news_id=$_GET['news_id'];
	else 
		unset($news_id);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	if($berechtigt && isset($news_submit) && (!isset($message_sent) || $message_sent == "no"))
	{		
		$author = chop($txtAuthor);
		$title = chop($txtTitle);
		$news_message = chop(str_replace("\r\n", "<br>", $txtNewsMessage));
		
		if($author != "" && $title != "" && $news_message != "")
		{
			if(isset($news_id) && $news_id != "")
			{
				$news = new news($sql_conn);
				
				$news->news_id = $news_id;
				$news->betreff = $title;
				$news->verfasser = $author;
				$news->text = $news_message;
				$news->studiengang_kz = '0';
				$news->semester = null;
				$news->datum = $datum;
				$news->uid=$user;
				$news->updatevon=$user;
				$news->updateamum=date('Y-m-d');
				$news->new=false;
				
				if($news->save())
				{				
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php' + \"?message_sent=yes&changed=yes\";";
					echo "</script>";
				}
				else 
				{					
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php' + \"?message_sent=no\";";
					echo "</script>";
				}
			}
			else
			{
				$news = new news($sql_conn);
				
				$news->betreff = $title;
				$news->verfasser = $author;
				$news->text = $news_message;
				$news->studiengang_kz = '0';
				$news->updatevon=$user;
				$news->semester = null;
				$news->uid = $user;
				$news->updateamum=date('Y-m-d');
				$news->datum=$datum;
				$news->new=true;
				
				if($news->save())
				{				
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php' + \"?message_sent=yes&changed=yes\";";
					echo "</script>";
				}
				else 
				{
					echo "test:".$news->errormsg;
					//echo "<script language=\"JavaScript\">";
					//echo "	document.location.href = 'news_entry.php' + \"?message_sent=no\";";
					//echo "</script>";
				}
			}
		}
		else
		{
			echo "<script language=\"JavaScript\">";
			echo "	document.location.href = 'news_entry.php' + \"?message_sent=no\";";
			echo "</script>";
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
		if(document.NewsEntry.txtAuthor != null)
		{
			document.NewsEntry.txtAuthor.focus();
		}
	}

</script>
</head>

<body onLoad="focusFirstElement();">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><form method="post" name="NewsEntry">
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Verwaltungstools - Newsverwaltung</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<?php	
			if(!$berechtigt)
			{
				die("<td>Sie haben keine Berechtigung f&uuml;r diese Seite.</td>");
			}
			
			if(isset($message_sent) && $message_sent == "yes")
			{
				if(isset($changed) && $changed == "yes")
				{
					echo "  <td>";
					echo "<script language=\"JavaScript\">";
					echo "	parent.news_window.location.href = 'news_show.php'";
					echo "</script>";
					echo "</td>";
					echo "</tr>";
					echo "  <td>&nbsp;</td>";
					echo "</tr>";
					echo "<tr>";
					echo "  <td><font class=\"headline\">Die Nachricht wurde erfolgreich ge&auml;ndert!</font></td>";
					echo "</tr>";
				}
				else
				{
					echo "  <td>";
					echo "<script language=\"JavaScript\">";
					echo "	parent.news_window.location.href = 'news_show.php'";
					echo "</script>";
					echo "</td>";
					echo "</tr>";
					echo "  <td>&nbsp;</td>";
					echo "</tr>";
					echo "<tr>";
					echo "  <td><font class=\"headline\">Die Neuigkeit wurde erfolgreich eingetragen!</font></td>";
					echo "</tr>";
				}
				
				exit;
			}
			else if(isset($message_sent) && $message_sent == "no")
			{
				echo "<td>&nbsp;</td>";
				echo "</tr>";
				echo "  <td><font class=\"headline\">Die Neuigkeit wurde NICHT eingetragen!</font><br>";
				echo "<font class=\"subline\">Bitte versuchen Sie es erneut</font></td>";
				echo "</tr>";
				
				exit;
			}
			
			echo '<td class="ContentHeader2">&nbsp;';
			
			if(isset($news_id) && $news_id != "")
			{
				$news = new news($sql_conn, $news_id);
				echo 'Eintrag &auml;ndern';
			}
			else
			{
				echo 'Neuen Eintrag erstellen';
			}
			
			echo '</td>';
		?>
	  </tr>
	  <tr>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>
		  <table width="100%"  border="0" cellspacing="0" cellpadding="0">
		    <tr>
			  <td width="65">Verfasser:</td>
			  <td><input type="text" class="TextBox" name="txtAuthor" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->verfasser.'"'; ?>></td>
			  <td>Sichtbar ab: <input type="text" class="TextBox" name="datum" size="10" value="<?php if(isset($news_id) && $news_id != "") echo date('d.m.Y',strtotime(strftime($news->datum))); else echo date('d.m.Y'); ?>"></td>
		    </tr>
			<tr>
			  <td>Titel:</td>
			  <td><input type="text" class="TextBox" name="txtTitle" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->betreff.'"'; ?>></td>
		    </tr>
		</table>
		</td>
	  </tr>
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td>Bitte geben Sie hier Ihre Nachricht ein:<br>
			<textarea class="TextBox" style="width: 99%; heigth: 166px" name="txtNewsMessage" rows="10" cols="70" maxlength="2000"><?php if(isset($news_id) && $news_id != "") echo str_replace("<br>", "\r\n", $news->text); ?></textarea></td>
	  </tr>
	  <tr>
	  	<td nowrap>
		  <input type="hidden" name="news_submit">
	      <input type="submit" name="btnSend" value="Abschicken">&nbsp;
		  <?php
		  if(isset($news_id) && $news_id != "")
		  {
		  	echo "<input type=\"reset\" name=\"btnCancel\" value=\"Abbrechen\" onClick=\"document.location.href='news_entry.php';\"></td>";
		  }
		  else
		  {
		  	echo '<input type="reset" name="btnCancel" value="Zur&uuml;cksetzen" onClick="document.NewsEntry.txtAuthor.focus();"></td>';
		  }
		  ?>
	  </tr>
    </table>
	</form></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>