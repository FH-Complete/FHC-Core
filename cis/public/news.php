<?php
    require_once('../config.inc.php');
    require_once('../../include/news.class.php');
    
    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die("Fehler beim öffnen der Datenbankverbindung");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/cis.css" rel="stylesheet" type="text/css">
</head>

<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="10">&nbsp;</td>
    <td><table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="ContentHeader"><font class="ContentHeader">&nbsp;News</font></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
      </tr>
	  	<td>
		  <?php
		  
		  	$news = new news($conn);

		  	$news->getnews(MAXNEWSALTER,0,null);
		  	$zaehler=0;
		  	
		  	foreach ($news->result as $row)
		  	{		  		
		  		$zaehler++;
		  		//no comment
		  		$datum = date('d.m.Y - h:i',strtotime(strftime($row->updateamum)));
		  		
				echo $datum.'&nbsp;'.$row->verfasser.'<br><br><strong>'.$row->betreff.'</strong><br>'.$row->text.'<br><br><br>
				';
			}
				
			
			if($zaehler==0)
				echo 'Zur Zeit gibt es keine aktuellen News!';
		  ?>
		</td>
	  </tr>
    </table></td>
	<td width="30">&nbsp;</td>
  </tr>
</table>
</body>
</html>
