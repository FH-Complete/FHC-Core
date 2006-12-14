<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="JavaScript">
	
	function deleteEntry(id)
	{
		if(confirm("Soll dieser Eintrag wirklich gel�scht werden?") == true)
		{
			document.location.href = 'news_show.php?remove_id=' + id;
		}
	}
	
	function editEntry(id)
	{
		parent.news_entry.location.href = 'news_entry.php?news_id=' + id;
	}
</script>
</head>

<body>
<?php
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
    require_once('../../../include/benutzerberechtigung.class.php');
    require_once('../../../include/news.class.php');
		
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die('Fehler beim �ffnen der Datenbankverbindung');
    
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
		
	if($berechtigt)
	{
		if(isset($remove_id) && $remove_id != "")
		{
			$news = new news($sql_conn);
			if($news->delete($remove_id))
			{
				echo '<script language="JavaScript">';
				echo '	document.location.href = "news_show.php"';
				echo '</script>';
				exit;
			}
			else 
				echo 'Fehler beim L&ouml;schen des Eintrages';
		}
	}
?>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
        <tr>
	  	  <?php
		  	if(!$berechtigt)
				exit;
		  ?>
          <td>
		  	<table width="100%"  border="0" cellspacing="0" cellpadding="0">
			  <?php
						  
				$news = new news($sql_conn);			
				$news->getnews(MAXNEWSALTER,0,null);
				
				$zaehler=0;
				$i=0;
				foreach($news->result as $row)
				{	
					$i++;					
					$zaehler++;
					$datum = date('d.m.Y - h:i',strtotime(strftime($row->updateamum)));
									
					echo "<tr>";
					
					if($i % 2 != 0)
					{
						echo '<td class="MarkLine">';
					}
					else
					{
						echo '<td>';
					}
					
					echo '  <table width="100%"  border="0" cellspacing="0" cellpadding="0">';
					echo '    <tr>';
					echo '      <td nowarp>';
					echo         $datum.'&nbsp;'.$row->verfasser;
					echo '      </td>';
					echo '		<td align="right" nowrap>';
					echo '		  <a onClick="editEntry('.$row->news_id.');">Editieren</a>, <a onClick="deleteEntry('.$row->news_id.');">L&ouml;schen</a>';
					echo '		</td>';
					echo '    </tr>';
					echo '	  <tr>';
					echo '		<td>&nbsp;</td>';
					echo '	  </tr>';
					echo '  </table>';
					echo '  <strong>'.$row->betreff.'</strong><br>'.$row->text.'</td>';
					echo "</tr>";
					echo '<tr>';
					echo '  <td>&nbsp;</td>';
					echo '</tr>';
					echo '<tr>';
					echo '  <td>&nbsp;</td>';
					echo '</tr>';
				
				}
				if($zaehler==0)
					echo 'Zur Zeit gibt es keine aktuellen News!';
			  ?>
			</table>
		  </td>
        </tr>
    </table></td>
    <td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
