<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<title>Personen im Mailverteiler</title>
<body id="inhalt">
<?php
    require_once('../../include/functions.inc.php');
	require_once('../../include/studiensemester.class.php');
    require_once('../config.inc.php');

    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');

    $user=get_uid();

	$stsem_obj = new studiensemester($conn);
	$stsem = $stsem_obj->getaktorNext();

	if(check_lektor($user,$conn))
       $is_lector=true;
?>
<table class="tabcontent">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">Nachname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">Vorname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">E-Mail</font></td>
	      </tr>


<?php
 		  //$sql_query = "SELECT vornamen AS vn,nachname AS nn,a.uid as uid FROM public.tbl_personmailgrp AS a, public.tbl_person AS b WHERE a.uid=b.uid AND a.mailgrp_kurzbz='$grp' ORDER BY nachname";
	  $qry = "SELECT uid, vorname, nachname FROM campus.vw_benutzer JOIN tbl_benutzergruppe USING (uid) WHERE gruppe_kurzbz='".addslashes($_GET['grp'])."' AND (studiensemester_kurzbz IS NULL OR studiensemester_kurzbz='$stsem') ORDER BY nachname, vorname";
	  if($result=pg_query($conn, $qry))
	  {
	  	while($row = pg_fetch_object($result))
	  	{
		  	echo "<tr>";
	      	echo "  <td>$row->nachname</td>";
	      	echo "  <td>$row->vorname</td>";
	      	echo "  <td><a href='mailto:$row->uid@".DOMAIN."' class='Item'>$row->uid@".DOMAIN."</a></td>";
	      	echo "</tr>";
		}
	  }
?>
</body></html>
