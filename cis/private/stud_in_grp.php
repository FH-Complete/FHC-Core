<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../skin/style.css.php" rel="stylesheet" type="text/css">
</head>
<title>Personen im Mailverteiler</title>
<body id="inhalt">
<?php
    require_once('../config.inc.php');

    //Connection Herstellen
    if(!$conn = pg_pconnect(CONN_STRING))
       die('Fehler beim öffnen der Datenbankverbindung');

    if(!isset($_GET['kz']))
    	die('Fehlerhafte parameteruebergabe');
?>
<table class="tabcontent">
	      <tr>
	        <td class="ContentHeader"><font class="ContentHeader">Nachname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">Vorname</font></td>
	        <td class="ContentHeader"><font class="ContentHeader">E-Mail</font></td>
	      </tr>
<?php
	if(isset($_GET['all']))
	{
		$qry = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE studiengang_kz='".addslashes($_GET['kz'])."' AND semester<10 ORDER BY nachname, vorname";
	}
	else
	{
		$qry = "SELECT vorname, nachname, uid FROM campus.vw_student WHERE aktiv=true AND studiengang_kz='".addslashes($_GET['kz'])."'";

		if(isset($_GET['sem']))
			$qry.=" AND semester='".addslashes($_GET['sem'])."'";

		if(isset($_GET['verband']))
			$qry.=" AND verband='".addslashes($_GET['verband'])."'";

		if(isset($_GET['grp']))
			$qry.=" AND gruppe='".addslashes($_GET['grp'])."'";

		$qry.= ' ORDER BY nachname, vorname';
	}

	if($result=pg_query($conn, $qry))
	{
		while($row=pg_fetch_object($result))
		{
			echo "<tr>";
			echo "  <td>$row->nachname</td>";
			echo "  <td>$row->vorname</td>";
			echo "  <td><a href='mailto:$row->uid@technikum-wien.at' class='Item'>$row->uid@technikum-wien.at</a></td>";
			echo "</tr>";
		}
	}
	else
		echo 'Fehler beim Auslesen der Studentendaten';
?>
</table>
</body>
</html>