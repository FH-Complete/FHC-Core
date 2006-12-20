<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiengang.class.php');
	require_once('../../../include/news.class.php');
    
    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');
    
	$user = get_uid();
	
	if(check_lektor($user,$conn))
		$is_lector=true;
    else
    	$is_lector=false;
	
	if((!isset($_GET['course_id']) || !isset($_GET['term_id'])))
		die('Fehlerhafte Parameteruebergabe');
	else 
	{
		$course_id = $_GET['course_id'];
		$term_id = $_GET['term_id'];
	}
		
	$stg_obj = new studiengang($conn, $course_id);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<?php
	if(isset($_POST['news_submit']) && (!isset($_POST['message_sent']) || $_POST['message_sent'] == "no"))
	{
		$author = chop($_POST['txtAuthor']);
		$title = chop($_POST['txtTitle']);
		$news_message = chop(str_replace("\r\n", "<br>", $_POST['txtNewsMessage']));
		
		if($author != "" && $title != "" && $news_message != "" && isset($course_id) && isset($term_id))
		{
			//Einfache Hochkomma Konvertieren
			if(isset($news_id) && $news_id != "")
			{
				$news_obj = new news($conn);
				
				$news_obj->verfasser = $author;
				$news_obj->uid = $user;
				$news_obj->studiengang_kz = $course_id;
				
				$news_obj->semester = $term_id;
				$news_obj->betreff = $title;
				$news_obj->text = $news_message;
				$news_obj->updatevon = $user;
				$news_obj->news_id = $news_id;
				$news_obj->new=false;
				
				if($news_obj->save())
				{
					echo '<script language="JavaScript">';
					echo "	document.location.href = 'pinboard_entry.php?course_id=$course_id&term_id=$term_id' + \"&message_sent=yes&changed=yes\";";
					echo '</script>';
				}
				else 
				{
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'pinboard_entry.php?course_id=$course_id&term_id=$term_id' + \"&message_sent=no\";";
					echo "</script>";
				}
			}
			else
			{
				$news_obj = new news($conn);
				
				$news_obj->verfasser = $author;
				$news_obj->uid = $user;
				$news_obj->studiengang_kz = $course_id;
				$news_obj->semester = $term_id;
				$news_obj->betreff = $title;
				$news_obj->text = $news_message;
				$news_obj->updatevon = $user;
				$news_obj->new=true;
				
				if($news_obj->save())
				{
					echo '<script language="JavaScript">';
					echo "	document.location.href = 'pinboard_entry.php?course_id=$course_id&term_id=$term_id' + \"&message_sent=yes\";";
					echo '</script>';
				}
				else 
				{
					echo "<script language=\"JavaScript\">";
					echo "	document.location.href = 'pinboard_entry.php?course_id=$course_id&term_id=$term_id' + \"&message_sent=no\";";
					echo "</script>";
				}
			}
		}
		else
		{
			echo "<script language=\"JavaScript\">";
			echo "	document.location.href = 'pinboard_entry.php?course_id=$course_id&term_id=$term_id' + \"&message_sent=no\";";
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

<script language="JavaScript">
<!--        
	function MM_jumpMenu(targ, selObj, restore)
	{
	  var help;
	  var zeilenumbruch = String.fromCharCode(10);
	  var msg;
	  var i;
	  
	  help =targ + ".location='" + selObj.options[selObj.selectedIndex].value;	  
	  msg = document.NewsEntry.txtNewsMessage.value.replace(zeilenumbruch,'<br>');
	  for(i=0;i<20;i++)
	  	msg=msg.replace(zeilenumbruch,'<br>')
	  help = help + "&txt=" + msg;
	  help = help + "&aut=" + document.NewsEntry.txtAuthor.value;
	  <?php 
	  if(isset($news_id))
	  	echo 'help = help + "&news_id='.$news_id.'";
	  	';
	  ?>
	  help = help + "&tit=" + document.NewsEntry.txtTitle.value + "'";
	  
	  eval(help);
	  
	  if(restore) 
	  {
	  	selObj.selectedIndex = 0;
	  }
	}
  //-->
</script>

<?php
	echo "<script language=\"JavaScript\">";
	echo "	parent.news_window.location.href = 'pinboard_show.php?course_id=$course_id&term_id=$term_id'";
	echo "</script>";
?>
</head>

<body onLoad="focusFirstElement();">
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><form method="post" name="NewsEntry">
	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;Lektorenbereich - Pinboardverwaltung 
		  <?php
			if(isset($course_id) && isset($term_id))
			{
				if($course_id == 0 && $term_id == 0)
				{
					echo 'Alle Studieng&auml;nge, Alle Semester';
				}
				else if($course_id == 0)
				{
					echo 'Alle Studieng&auml;nge, '.$term_id.'. Semester';
				}
				else if($term_id == 0)
				{
					echo $stg_obj->kurzbz.', Alle Semester';
				}
				else
				{
					echo $stg_obj->kurzbz.', '.$term_id.'. Semester';
				}
			}
		  ?></font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  <tr>
	  	<?php	
			if(!$is_lector)
			{
				die('<td>Sie haben leider keine Berechtigung f&uuml;r diese Seite.</td>');
			}
			
			if(isset($message_sent) && $message_sent == "yes")
			{
				if(isset($changed) && $changed == "yes")
				{
					echo "  <td>";
					echo "<script language=\"JavaScript\">";
					echo "	parent.news_window.location.href = 'pinboard_show.php?course_id=$course_id&term_id=$term_id'";
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
					echo "	parent.news_window.location.href = 'pinboard_show.php?course_id=$course_id&term_id=$term_id'";
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
				echo "  <td><font class=\"headline\">Die Neuigkeit wurde nicht eingetragen!</font><br>";
				echo "<font class=\"subline\">Es wurden nicht alle erforderlichen Felder ausgef&uuml;llt.</font></td>";
				echo "</tr>";
				
				exit;
			}
			
			echo '<td class="ContentHeader2">&nbsp;';
			
			$verfasser = '';
			$betreff = '';
			$text = '';
				
			if(isset($news_id) && $news_id != "")
			{
				$news_obj = new news($conn, $news_id);
				$verfasser = $news_obj->verfasser;
				$betreff = $news_obj->betreff;
				$text = $news_obj->text;
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
			  <?php
			  if (isset($aut) AND $aut!="")
			     $value='value="'.$aut.'"';
			  else
			  {
			    if(isset($news_id) && $news_id != "")
			        $value='value="'.$verfasser.'"';
			    else
			        $value='';  
			  }			   
			   ?>
			   
			  <td width="218"><input type="text" class="TextBox" name="txtAuthor" size="30" <?php echo $value; ?>>
			  </td>
			  <td width="81">Studiengang: </td>
			  <td>
			  	<select name="course" onChange="MM_jumpMenu('self',this,0)" class="TextBox">
			  	<?php
			  		$studiengaenge = new studiengang($conn);
				
			  		$studiengaenge->getAll('kurzbz, kurzbzlang');
			  		
					foreach($studiengaenge->result AS $row_course)
					{						
						if(isset($course_id))
						{
							if($course_id == $row_course->studiengang_kz)
							{
								if($row_course->studiengang_kz == 0)
								{
									echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'" selected>Alle Studieng&auml;nge</option>';
								}
								else
								{
									echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'" selected>'.$row_course->kurzbz.' ('.$row_course->kurzbzlang.')</option>';
								}
							}
							else
							{
								if($row_course->studiengang_kz == 0)
								{
									echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'">Alle Studieng&auml;nge</option>';
								}
								else
								{
									echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'">'.$row_course->kurzbz.' ('.$row_course->kurzbzlang.')</option>';
								}
							}
						}
						else
						{
							if($row_course->studiengang_kz == 0)
							{
								echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'">Alle Studieng&auml;nge</option>';
							}
							else
							{
								echo '<option value="pinboard_entry.php?course_id='.$row_course->studiengang_kz.'&term_id='.$term_id.'">'.$row_course->kurzbz.' ('.$row_course->kurzbzlang.')</option>';
							}
						}
					}
				?>
			  	</select>
			  </td>
		    </tr>
			<tr>
			  <td>Titel:</td>
			  <?php
			  if (isset($tit) AND $tit!="")
			     $value='value="'.$tit.'"';
			  else
			  {
			    if(isset($news_id) && $news_id != "")
			        $value='value="'.$betreff.'"';
			    else
			        $value='';
			  }			   
			   ?>
			  <td><input type="text" class="TextBox" name="txtTitle" size="30" <?php echo $value; ?>></td>
			  <td>Semester: </td>
			  <td>
			  	<select name="term" onChange="MM_jumpMenu('self',this,0)" class="TextBox">
				<?php
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=1" '.($term_id==1?'selected':'').'>1. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=2" '.($term_id==2?'selected':'').'>2. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=3" '.($term_id==3?'selected':'').'>3. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=4" '.($term_id==4?'selected':'').'>4. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=5" '.($term_id==5?'selected':'').'>5. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=6" '.($term_id==6?'selected':'').'>6. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=7" '.($term_id==7?'selected':'').'>7. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=8" '.($term_id==8?'selected':'').'>8. Semester</option>';
					echo '<option value="pinboard_entry.php?course_id='.$course_id.'&term_id=0" '.($term_id==0?'selected':'').'>Alle Semester</option>';
				?>
			  	</select>
			  </td>
		    </tr>
		</table>
		</td>
	  </tr>
	  <tr>
	  	<td>&nbsp;</td>
	  </tr>
	  <tr>
	  	<td>Bitte geben Sie hier Ihre Nachricht ein:<br>
	  	<?php
			  if (isset($txt) AND $txt!="")
			     $value=str_replace("<br>","\r\n",$txt);
			  else
			  {
			    if(isset($news_id) && $news_id != "")
			        $value=str_replace("<br>", "\r\n", $text);
			    else
			        $value='';  
			  }
		?>
			<textarea class="TextBox" style="width: 99%; heigth: 166px" name="txtNewsMessage" rows="10" cols="70" maxlength="2000"><?php echo $value; ?></textarea></td>
	  </tr>
	  <tr>
	  	<td nowrap>
		  <input type="hidden" name="news_submit">
	      <input type="submit" name="btnSend" value="Abschicken">&nbsp;
		  <?php
		  if(isset($news_id) && $news_id != "")
		  {
		  	echo "<input type=\"reset\" name=\"btnCancel\" value=\"Abbrechen\" onClick=\"document.location.href='pinboard_entry.php?course_id=$course_id&term_id=$term_id';\"></td>";
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
