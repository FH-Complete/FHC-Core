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

	if($rechte->isBerechtigt('admin') || $rechte->isBerechtigt('assistenz') || $rechte->isBerechtigt('lehre') || $rechte->isBerechtigt('news'))
		$berechtigt=true;
	else
		$berechtigt=false;

	if(isset($_GET['news_id']))
		$news_id=$_GET['news_id'];
	if(isset($_GET['message_sent']))
		$message_sent=$_GET['message_sent'];
	if(isset($_GET['changed']))
		$changed=$_GET['changed'];
	if(isset($_POST['news_id']))
		$news_id=$_POST['news_id'];
	if(isset($_POST['news_submit']))
		$news_submit=$_POST['news_submit'];
	if(isset($_POST['txtNewsMessage']))
		$txtNewsMessage=$_POST['txtNewsMessage'];
	if(isset($_POST['txtAuthor']))
		$txtAuthor=$_POST['txtAuthor'];
	if(isset($_POST['datum']))
		$datum=$_POST['datum'];
	if(isset($_POST['datum_bis']))
		$datum_bis=$_POST['datum_bis'];
	if(isset($_POST['txtTitle']))
		$txtTitle=$_POST['txtTitle'];
	if(isset($_POST['btnSend']))
		$btnSend=$_POST['btnSend'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	//var_dump($_POST);
	//echo '<BR>';
	//var_dump($_GET);
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
				if(isset($chksenat))
					$news->fachbereich_kurzbz = 'Senat';
				else
					$news->fachbereich_kurzbz = '';
				$news->datum = $datum;
				$news->datum_bis = $datum_bis;
				$news->uid=$user;
				$news->updatevon=$user;
				$news->updateamum=date('Y-m-d H:i:s');
				$news->new=false;

				if($news->save())
				{
					echo '<script language="JavaScript">';
					echo "	document.location.href = 'news_entry.php?message_sent=yes&changed=yes';";
					echo "</script>";
				}
				else
				{
					//echo $news->errormsg;
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php?message_sent=no';";
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
				if(isset($chksenat))
					$news->fachbereich_kurzbz = 'Senat';
				else
					$news->fachbereich_kurzbz = '';
				$news->uid = $user;
				$news->updateamum=date('Y-m-d H:i:s');
				$news->datum=$datum;
				$news->datum_bis=$datum_bis;
				$news->new=true;

				if($news->save())
				{
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php' + \"?message_sent=yes&changed=yes\";";
					echo "</script>";
				}
				else
				{
					//echo "test:".$news->errormsg;
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'news_entry.php' + \"?message_sent=no\";";
					echo "</script>";
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
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

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
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <form action="" method="POST" name="NewsEntry">
	<table class="tabcontent">
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
					echo "  <td><h3>Die Nachricht wurde erfolgreich ge&auml;ndert!<h3></td>";
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
					echo "  <td><h3>Die Neuigkeit wurde erfolgreich eingetragen!</h3></td>";
					echo "</tr>";
				}

				exit;
			}
			else if(isset($message_sent) && $message_sent == "no")
			{
				echo "<td>&nbsp;</td>";
				echo "</tr>";
				echo "  <td><h3>Die Neuigkeit wurde NICHT eingetragen!</h3>";
				echo "<h3>Bitte versuchen Sie es erneut</h3></td>";
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
		  <table class="tabcontent">
		    <tr>
			  <td width="65">Verfasser:</td>
			  <td><input type="text" class="TextBox" name="txtAuthor" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->verfasser.'"'; ?>></td>
			  <td>Sichtbar ab:</td>
			  <td><input type="text" class="TextBox" name="datum" size="10" value="<?php if(isset($news_id) && $news_id != "") echo date('d.m.Y',strtotime(strftime($news->datum))); else echo date('d.m.Y'); ?>"></td>
		    </tr>
			<tr>
			  <td>Titel:</td>
			  <td><input type="text" class="TextBox" name="txtTitle" size="30"<?php if(isset($news_id) && $news_id != "") echo ' value="'.$news->betreff.'"'; ?>></td>
			  <td>Sichtbar bis (optional):</td>
			  <td><input type="text" class="TextBox" name="datum_bis" size="10" value="<?php if(isset($news_id) && $news_id != "" && $news->datum_bis!='') echo date('d.m.Y',strtotime(strftime($news->datum_bis))); else echo ''; ?>"></td>
			</tr>
			<tr>
				<td colspan='2'>Bitte geben Sie hier Ihre Nachricht ein:</td>
				
<?php
			  if($rechte->isBerechtigt('admin',0) || $rechte->isBerechtigt('assistenz',0))
			  {
?>
			  <td>Senat:</td>
			  <td><input type="checkbox" name="chksenat"<?php if(isset($news_id) && $news_id!="" && $news->fachbereich_kurzbz=='Senat') echo ' checked'?>></td>
<?php
			  }
?>
		    </tr>
		</table>
		</td>
	  </tr>
<!--	<tr>
	  	<td>&nbsp;</td>
	  </tr>-->
	  <tr>
	  	<td><!--Bitte geben Sie hier Ihre Nachricht ein:<br>-->
			<textarea class="TextBox" style="width: 99%; heigth: 166px" name="txtNewsMessage" rows="10" cols="70" maxlength="2000"><?php if(isset($news_id) && $news_id != "") echo str_replace("<br>", "\r\n", $news->text); ?></textarea></td>
	  </tr>
	  <tr>
	  	<td nowrap>
		  <input type="hidden" name="news_submit" value="true">
	      <input type="submit" name="btnSend" value="Abschicken">&nbsp;
		  <?php
		  if(isset($news_id) && $news_id != "")
		  {
		  	echo "<input type='hidden' name='news_id' value='$news_id'>
		  		<input type=\"reset\" name=\"btnCancel\" value=\"Abbrechen\" onClick=\"document.location.href='news_entry.php';\"></td>";
		  }
		  else
		  {
		  	echo '<input type="reset" name="btnCancel" value="Zur&uuml;cksetzen" onClick="document.NewsEntry.txtAuthor.focus();"></td>';
		  }
		  ?>
	  </tr>
    </table>
	</form></td>
	<td class="tdwidth30">&nbsp;</td>
  </tr>
</table>
</body>
</html>