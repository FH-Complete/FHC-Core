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
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/news.class.php');

    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');

		$user = get_uid();
		if(check_lektor($user,$sql_conn))
       $is_lector=true;
	  else
  	  	die('Sie haben keine Berechtigung fuer diesen Bereich');


// POST/GET Parameter uebernehmen 
	if (isset($_GET))
	{
		while (list ($tmp_key, $tmp_val) = each($_GET)) 
		{
			$$tmp_key=$tmp_val;
		}	
	}
	if (isset($_POST))
	{
		while (list ($tmp_key, $tmp_val) = each($_POST)) 
		{
			$$tmp_key=$tmp_val;
		}	
	}			

	if(isset($btnCancel))
	{
			echo "<script language=\"JavaScript\">";
				echo "	document.location.href = 'pinboard_entry.php?message_sent=no';";
			echo "</script>";
			exit;
	}
	
	if(isset($btnSend))
	{
		$author = chop($_POST['txtAuthor']);
		$title = chop($_POST['txtTitle']);
		$datum = $_POST['datum'];
		$news_message = chop($_POST['txtNewsMessage']);
		if($author != "" && $title != "" && $news_message != "")
		{
					$news_message = mb_ereg_replace("\r\n", "<br>", $news_message);

					$news_obj = new news();
		
					$news_obj->verfasser = $author;
					$news_obj->uid = $user;
					$news_obj->studiengang_kz = '0';
		
					$news_obj->semester = '0';
					$news_obj->betreff = $title;
					$news_obj->text = $news_message;
					$news_obj->datum = $datum;
					$news_obj->datum_bis = $datum_bis;
					$news_obj->updatevon = $user;
		
					if(isset($news_id) && $news_id != "")
					{
						$news_obj->new=false;
						$news_obj->news_id = $news_id;
					}
					else
						$news_obj->new=true;
		
					if(!$news_obj->save())
							exit($news_obj->errormsg);

					echo "<script language=\"JavaScript\">";
						echo "	parent.news_window.location.href = 'pinboard_show.php'";
					echo "</script>";
					
					if(!$news_obj->new)
					{
						echo " <h3>Die Nachricht wurde erfolgreich ge&auml;ndert!</h3>";
					}
					else
					{
						echo "  <h3>Die Neuigkeit wurde erfolgreich eingetragen!</h3>";
					}
					#exit;
		}
		else
		{
				$message_sent="no";
		}
		
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">

<script language="JavaScript" type="text/javascript">

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

		<form method="post" name="NewsEntry" accept-charset="UTF-8" action="<?php echo $_SERVER['PHP_SELF'];?>" enctype="application/x-www-form-urlencoded">

			<table class="tabcontent">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Lektorenbereich - Pinboardverwaltung</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<?php
			echo '<td class="ContentHeader2">&nbsp;';
			if(isset($news_id) && $news_id != "")
			{
				$news_obj = new news($news_id);
				$verfasser = $news_obj->verfasser;
				$betreff = $news_obj->betreff;
				$text = $news_obj->text;
				$text=mb_ereg_replace("<br>","\r\n", $text);

				$datum = $news_obj->datum;
				$datum = date('d.m.Y',strtotime(strftime($datum)));

				$datum_bis = $news_obj->datum_bis;
				if ($datum_bis!='')
					 $datum_bis=date('d.m.Y',strtotime(strftime($datum_bis)));				
				echo 'Eintrag &auml;ndern';
			}
			else
			{
				echo 'Neuen Eintrag erstellen';
				$verfasser = '';
				$betreff = '';
				$text = '';
				$datum = date('d.m.Y');
				$datum_bis = '';
			}
			echo '</td>';
		?>
	  </tr>
			<?php
				if (isset($message_sent) && $message_sent=="no")
				{
					echo '<tr><td>Es wurden nicht alle Felder eingegeben!</td></tr>';
					$verfasser = (isset($_POST['txtAuthor'])?$_POST['txtAuthor']:'');
					$betreff = (isset($_POST['txtTitle'])?$_POST['txtTitle']:'');
					$text = (isset($_POST['txtNewsMessage'])?$_POST['txtNewsMessage']:'');
					$datum = (isset($_POST['datum'])?$_POST['datum']:date('d.m.Y'));
					$datum_bis = (isset($_POST['datum_bis'])?$_POST['datum_bis']:'');
				}
			?>
	  <tr>
	    <td>&nbsp;</td>
	  </tr>
	  <tr>
	    <td>
		  <table class="tabcontent">
		    <tr>
			  <td width="65">Verfasser:</td>
			  <td width="218"><input type="text" class="TextBox" name="txtAuthor" size="30" value="<?php echo $verfasser; ?>">
			  </td>
			  <td width="80">Sichtbar ab</td>
			  <td><input type="text" class="TextBox" name="datum" size="10" value="<?php echo $datum; ?>"></td>
		    </tr>
			<tr>
			  <td>Titel:</td>
			  <td><input type="text" class="TextBox" name="txtTitle" size="30" value="<?php echo $betreff; ?>"></td>
			  <td width="80">Sichtbar bis</td>
			  <td><input type="text" class="TextBox" name="datum_bis" size="10" value="<?php echo $datum_bis; ?>"></td>
		    </tr>
		    <tr>
			  	<td colspan='2'>Bitte geben Sie hier Ihre Nachricht ein:</td>
				<td colspan='2'><strong><font class="error">Hinweis:</font></strong>
					Bitte beachten Sie, dass im Titel auch das jeweilige Freifach genannt wird. 
			</td>
	  </tr>
		</table>
		</td>
	  </tr>
	
	  <tr>
	  	<td>
			<textarea class="TextBox" style="width: 99%; heigth: 166px" name="txtNewsMessage" rows="10" cols="70" maxlength="2000"><?php echo $text; ?></textarea></td>
	  </tr>
	  <tr>
	  	<td nowrap>
		  	<input type="hidden" name="news_id" value="<?php echo (isset($news_id)?$news_id:'');?>" >
		  	<input type="hidden" name="news_submit" value="1" >
	      <input type="submit" name="btnSend" value="Abschicken">&nbsp;
		  <?php
		  if(isset($news_id) && $news_id != "")
		  {
		  	echo "<input type=\"reset\" name=\"btnCancel\" value=\"Abbrechen\" onClick=\"document.location.href='pinboard_entry.php';\"></td>";
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
