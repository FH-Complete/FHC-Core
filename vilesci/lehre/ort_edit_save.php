<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	$conn_string = "host=calva.technikum-wien.at port=5432 dbname=vilescitest user=pam password=v1le5ci";
	
	if (!$conn = @pg_pconnect($conn_string)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
		
	if ($type=='save')
	{
		$sql_query="\lo_import $file";
		//$sql_query="UPDATE ort SET picture=lo_import('$file') WHERE id=$id";
		if(!($erg=pg_exec($conn, $sql_query)))
			die(pg_errormessage($conn));
		echo $erg.'<BR>';
	//echo $sql_query;
	}
	
?>

<html>
<head>
<title>Ort &auml;ndern</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
</head>

<body class="background_main">
<h4>Ort &auml;ndern</h4>

<FORM name="form1" method="post" action="ort_edit_save.php">
  ID:
  <INPUT type="text" name="id" size="3">
  <INPUT type="file" name="file">
  <INPUT type="submit" name="Abschicken" value="Save">
  <INPUT type="hidden" name="type" value="save">
</FORM>
</body>
</html>
