<?php
	include('../config.inc.php');
	include('../../include/functions.inc.php');
	if (!$conn = @pg_pconnect($conn_string))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	if(!($erg_std=pg_exec($conn, "SELECT * FROM stunde ORDER BY id")))
		die(pg_errormessage($conn));
	$num_rows_std=pg_numrows($erg_std);
	for ($t=1;$t<7;$t++)
		for ($i=0;$i<$num_rows_std;$i++)
		{
			$var='wunsch'.$t.'_'.$i;
			//echo $$var;
			$gewicht=$$var;
			$stunde=$i+1;
			$query="SELECT * FROM zeitwunsch WHERE lektor_id=$lkid AND stunde_id=$stunde AND tag=$t";
			if(!($erg_wunsch=pg_exec($conn, $query)))
				die(pg_errormessage($conn));
			$num_rows_wunsch=pg_numrows($erg_wunsch);
			if ($num_rows_wunsch==0)
			{
				$query="INSERT INTO zeitwunsch (lektor_id, stunde_id, tag, gewicht) VALUES ($lkid, $stunde, $t, $gewicht)";
				if(!($erg=pg_exec($conn, $query)))
					die(pg_errormessage($conn));
			}
			elseif ($num_rows_wunsch==1)
			{
				$id=pg_result($erg_wunsch,0,"id");
				$query="UPDATE zeitwunsch SET lektor_id=$lkid, stunde_id=$stunde, tag=$t, gewicht=$gewicht WHERE id=$id";
				if(!($erg=pg_exec($conn, $query)))
					die(pg_errormessage($conn));
			}
			else
				die("Zuviele Eintraege fuer!");
		}

?>

<html>
<head>
<title>Profil</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../include/styles.css" type="text/css">
<META http-equiv="refresh" content="2;URL=zeitwunsch.php?lkid=<?php echo $lkid.'&vornamen='.$vornamen.'&nachname='.$nachname.'&titel='.$titel; ?>">
</head>

<body class="background_main">
<h4>Zeitw&uuml;nsche von
  <?php echo $titel.' '.$vornamen.' '.$nachname; ?>
  sind aktualisiert!</h4>
<A href="zeitwunsch.php?lkid=<?php echo $lkid.'&vornamen='.$vornamen.'&nachname='.$nachname.'&titel='.$titel; ?>">&lt;&lt;
Zur&uuml;ck</A><br>


</body>
</html>
