<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/lehrveranstaltung.class.php');
	require_once('../../../include/feedback.class.php');
    
    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die('Fehler beim oeffnen der Datenbankverbindung');
    
	$user = get_uid();
	
	if(check_lektor($user, $conn))
       $is_lector=true;
    
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php	
	if(!isset($_GET['lvid']) || !is_numeric($_GET['lvid']))
	   die('Fehler bei der Uebergabe der Parameter');
	$lvid = $_GET['lvid'];
	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();
	if(isset($POST["feedback_message"]))
	   $feedback_message=$POST["feedback_message"];
	   
	if(isset($feedback_message))	
		echo $feedback_message;  

?>

<table border="0" cellpadding="0" width="100%" cellspacing="0">
	<tr>
		<td width="3%">&nbsp;</td>
			<?php
				echo '<form method="POST" action="feedback.php?lvid='.$lvid.'" enctype="multipart/form-data">';
			?>
		<td width="97%">
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			<?php
				$lv_obj = new lehrveranstaltung($conn);
				if($lv_obj->load($lvid))
				{
					$short_name = $lv_obj->bezeichnung;
				}
				else 
					die($lv_obj->errormsg);
			?>
          <td bgcolor="#008381"><font color="#FFFFFF"><strong>&nbsp;<?php echo $short_name; ?> - Feedback 
            an: </strong>
			<?php
			$qry = "SELECT vorname, nachname, uid FROM campus.vw_mitarbeiter, lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter WHERE uid=mitarbeiter_uid AND tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND lehrveranstaltung_id='$lvid' AND studiensemester_kurzbz='$stsem'";
			if(!$result=pg_query($conn, $qry))
				die('Fehler beim Auslesen der Lektoren');
			$rows = pg_num_rows($result);
			$i=0;
			while($row = pg_fetch_object($result))
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
      <p><b>Betreff:&nbsp; 
	    <?php
			if(isset($edit_id) && $edit_id != "" && !isset($edit_break))
			{
				$fb_obj = new feedback($conn);
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
				$fb_obj = new feedback($conn);
				$fb_obj->betreff = htmlentities($feedback_subject);
				$fb_obj->text = htmlentities($feedback_message);
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
				$fb_obj = new feedback($conn);
				$fb_obj->betreff = htmlentities($feedback_subject);
				$fb_obj->text = htmlentities($feedback_message);
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
	
		$fb_obj = new feedback($conn);
		if($fb_obj->load_feedback($lvid))
		{
			echo '<table width="100%" border="0" cellspacing="0" cellpadding="0">';
								
			foreach($fb_obj->result as $row)
			{								
				$sql_query = "SELECT vorname, nachname FROM campus.vw_benutzer WHERE uid='$row->uid'";
				
				if($result_person = pg_query($conn, $sql_query))
				{
					if($row_pers=pg_fetch_object($result_person))
					{
				
						echo '<tr>';
						echo '	<td bgcolor="#008381"><font color="#FFFFFF"><strong>&nbsp;'.$row->betreff.'</font></td>';
						echo '	<td bgcolor="#008381" width="30%"><font color="#FFFFFF">'.$row_pers->vorname.' '.$row_pers->nachname.'</font></td>';
						echo '	<td bgcolor="#008381" width="20%"><font color="#FFFFFF">'.$row->datum.'</font></td>';
						echo '	<td bgcolor="#008381" width="20%"><font color="#FFFFFF">&nbsp;</font></td>';
						
						echo '  <td bgcolor="#008381" align="right"><font color="#FFFFFF">&nbsp;</font></td>';
						
						echo '</tr>';
						echo '<tr>';
						echo '	<td bgcolor="#F2F2F2"><font color="#000000">'.nl2br($row->text).'</font></td>';
						echo '	<td bgcolor="#F2F2F2"><font color="#000000">&nbsp;</font></td>';
						echo '	<td bgcolor="#F2F2F2" colspan=2><font color="#000000">&nbsp;</font></td>';
						echo '	<td bgcolor="#F2F2F2"><font color="#000000">&nbsp;</font></td>';
						echo '</tr>';
						echo '<tr>';
						echo '	<td><font color="#FFFFFF">&nbsp;</font></td>';
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