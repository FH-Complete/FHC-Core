<?php
	/***************************************************
	 *@author Andras Österreicher
	 *@brief Formular zum Uploaden und Loeschen von 
	 *       Semesterplaenen.
	 *@date 31.Aug. 2005
	 ***************************************************/
	include("../../../include/functions.inc.php");
    include("../../config.inc.php");
    //Connection Herstellen
    if(!$sql_conn = pg_pconnect(CONN_STRING))
       die("Fehler beim öffnen der Datenbankverbindung");
    
    
	$user = $_SERVER["REMOTE_USER"];
	
	if(check_lektor($user,$sql_conn))
       $is_lector=true;
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/cis.css" rel="stylesheet" type="text/css">
<script language="JavaScript">

/****************************************************
 * @brief Zeigt eine Sicherheitsabfrage ob die Datei
 * 		  wirklich gelöscht werden soll
 ****************************************************/
function ConfirmFile(handle)
{			
	return confirm("Wollen Sie die ausgewählten Dateien wirklich löschen? Dieser Vorgang ist unwiderruflich!");
}

</script>
</head>
<title>Upload Semesterplan</title>
<body>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td width="10">&nbsp;</td>
		<td class="ContentHeader"><font class="ContentHeader">Upload Semesterplan</font></td>

	</tr>
	<tr>		
		<td width="10">&nbsp;</td>		
		<td width="10">&nbsp;</td>
	</tr>
		
<?php
   
	
	if($is_lector<=0)
		die("<tr><td width=\"10\">&nbsp;</td><td>Sie haben keine Berechtigung für diesen Bereich</td></tr>");
		
	echo "<tr><td width=\"10\">&nbsp;</td><td>";
	if(isset($inhalt))
	{
		if($inhalt!="____Ordnerinhalt____")
		{
			if(is_file($openpath . $inhalt))
			{
				exec("rm -r $openpath$inhalt");
				echo "<center>Datei erfolgreich gelöscht</center>";
			}
			else 
			{
			   echo "<center>Die Datei $openpath$inhalt konnte nicht gefunden werden.</center>";
			}
		}
		else
		{
			echo "<center>Bitte zuerst eine Datei auswählen</center>";
		}	
	}
	if(isset($userfile))
	{
		if(is_uploaded_file($userfile))
		{
			$fn = $_FILES['userfile']['name']; //Original Dateiname
			
			if(!(substr_count($fn, '.php') > 0 || substr_count($fn, '.php3') > 0 || 
				 substr_count($fn,'.php4') > 0 || substr_count($fn, '.php5') > 0 || 
				 substr_count($fn, '.cgi') > 0 || substr_count($fn, '.pl') > 0))
			{				
				
				copy($userfile,$openpath . $fn);
				echo "<center>Das File wurde erfolgreich hochgeladen</center>";
			}
			else 
			{
				echo "<center>Dieser Dateityp ist nicht erlaubt <center>";
			}
		}
	}
	
	echo "</tr></td>";

  //Anzeigefeld für Ordnerinhalt
  //Auskommentiert fürs Testen 
   // $sql_query = "SELECT DISTINCT ON(bz2, lehrfachzuteilung_kurzbz) lehrfachzuteilung_kurzbz AS kuerzel, (bezeichnung || '; XX') AS bezeichnung, SUBSTRING(bezeichnung || '; XX', 1, CHAR_LENGTH(bezeichnung || '; XX') - 4) AS bz2 FROM lehre.tbl_lehrfachzuteilung WHERE studiengang_kz='$course_id' AND semester='$term_id' AND NOT(lehrfachzuteilung_kurzbz='') AND lektor_uid='$user' ORDER BY bz2, lehrfachzuteilung_kurzbz";			
   $sql_query = "SELECT uid from tbl_mitarbeiter WHERE uid='$user'";
	$result_path_elements = pg_exec($sql_conn, $sql_query);
					
	if(!$result_path_elements)
		die('<p align="center"><strong<font size="2" face="Arial, Helvetica, sans-serif">Der Benutzer <strong>'.$user.'</strong> konnte nicht zugeordnet werden!</font></p>');
						
	$num_rows_path_elements = pg_numrows($result_path_elements);

	if(!($num_rows_path_elements > 0))
	{
		die('<p align="center"><strong<font>Es konnten keine Pfadeintr&auml;ge gefunden werden.</font></p>');						
	}
					
  $row = pg_fetch_object($result_path_elements, 0);

  
  echo "<tr><td width=\"10\">&nbsp;</td><td><form name=\"form1\"  method=\"POST\" action=\"semupload.php?openpath=$openpath&course_id=$course_id&term_id=$term_id\"  onSubmit=\"return ConfirmFile(this);\">";
  echo "<select name=\"inhalt\" size=5>";
  echo "<option selected>____Ordnerinhalt____</option>";
  //Inhalt des Semesterplan Ordners Auslesen
  if(is_dir($openpath))
  {  	
  	  $dest_dir = dir($openpath);
	  while($entry = $dest_dir->read())
	  {
	     if(!is_dir($entry))
		      echo "<option>$entry</option>";
	  }
  }
  echo "</select>";
  echo "<input type=\"submit\" value=\"Datei Löschen\">";
  echo "</form></td><td>";
  
    //FileAuswahlfeld
  echo "<tr><td width=\"10\">&nbsp;</td><td><br><form enctype=\"multipart/form-data\" method=\"POST\" action = \"semupload.php?openpath=$openpath&course_id=$course_id&term_id=$term_id\">";
  echo " <input type=\"file\" name = \"userfile\" size = \"30\">";
  echo " <input type=\"submit\" name=\"upload\" value=\"Upload\">";
  echo "</form></td><td>";
  
?>
</body>
</html>