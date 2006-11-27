<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/

    include('../../config.inc.php');
	include('../../../include/lv_verteilung.class.php');
	if (!$conn = @pg_pconnect(CONN_STRING)) 
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");
	
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">
<script language="JavaScript">

function conf_del()
{
	return confirm("Wollen Sie diesen Datensatz wirklich löschen?");
}
</script>
<title>LV-Verteilung</title>
</head>
<body>

<H1>LV Verteilung</h1>
<?php	   	
	if(!isset($order))
		$order="lehrveranstaltung_id";
	if(!isset($stsem))
	{
		$sql_query="select studiensemester_kurzbz from tbl_studiensemester where start<now() ORDER BY start DESC LIMIT 1";
		$result=pg_exec($conn,$sql_query);
		if($row=pg_fetch_object($result))
			$stsem=$row->studiensemester_kurzbz;
		else 
			$stsem=-1;
	}
	if(!isset($stg))
	{
		$stg=227;
	}
	if(!isset($lektor))
	{
		$lektor=-1; 
	}
	if(!isset($sem))
	{
		$sem=-1;
	}
	
	if(isset($saved))
	{
		echo "<br><h2>Daten wurden gespeichert</h2><br>";
	}
	
	if(isset($lfnr) && isset($lvz)) //ändern des LVZ
	{
		$sql_query = "Update tbl_lehrfach SET lehrevz='$lvz' WHERE lehrfach_nr='$lfnr' ";
		pg_exec($conn,$sql_query);
		echo "<br><h2>Update durchgeführt</h2><br>";
	}
	
	if(isset($lfnr) && isset($_POST['stb']) && isset($_GET['lvnr'])) //ändern der Stundenblockung
	{
		$sql_query = "Update tbl_lehrveranstaltung SET stundenblockung='". $_POST['stb']."' WHERE lvnr='". $_GET['lvnr']."'";
		if(pg_exec($conn,$sql_query))
			echo "<br><h2>Update durchgeführt</h2><br>";
		else 
			echo "<br><h2>Update Fehlgeschlagen, Bitte erneut versuchen</h2><br>";
	}
	
	if(isset($lvid) && isset($lehre)) //ändern von Lehre
	{
		$sql_query = "Update tbl_lehrveranstaltung SET lehre=not lehre WHERE lehrveranstaltung_id ='$lvid'";
		if(pg_exec($conn,$sql_query))
			echo "<br><h2>Update durchgeführt</h2><br>";
	    else
	    	echo "<br><h2><font color='#FF0000'>Fehler beim Update</font></h2><br>";
	}

	if(isset($del) && isset($lvid))
	{
		
			$sql_query = "DELETE FROM tbl_lehrveranstaltung WHERE lehrveranstaltung_id='$lvid'";
			pg_exec($conn,$sql_query);
			echo "<br><h2>DELETE durchgeführt</h2><br>";
		
	}
	$sql_query = "SELECT studiensemester_kurzbz FROM public.tbl_studiensemester order by start DESC";
	$result = pg_exec($conn, $sql_query);
	$numrows = pg_num_rows($result);
	$aktrows=0;
	echo '<table width="600" border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">';
	//Ausgeben der Studiensemester zb WS2005, SS2006 etc
	echo "<a href='lv_verteilung.php?stsem=-1&stg=$stg&sem=$sem&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'>Alle </a>-";
	while($row=pg_fetch_object($result))
	{
		$aktrows++;
		if($aktrows==$numrows)
		   echo "<a href='lv_verteilung.php?stsem=$row->studiensemester_kurzbz&stg=$stg&sem=$sem&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'> $row->studiensemester_kurzbz </a>";
		else 
		   echo "<a href='lv_verteilung.php?stsem=$row->studiensemester_kurzbz&stg=$stg&sem=$sem&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'> $row->studiensemester_kurzbz </a>-";
	}
	echo "</td><td align='center'>";
	echo "<form action='lv_edit.php?new=true&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=$order' method='POST'><input type='submit' value='NEU'></form>";
	echo "</td></tr></table>";
	$sql_query = "SELECT studiengang_kz, kurzbz FROM public.tbl_studiengang ORDER BY kurzbz";
	$result = pg_exec($conn,$sql_query);
	echo "\n";
	echo '<table border="0" cellspacing="0" cellpadding="0"><tr>';
	echo "<td>Studiengang:</td><td>Lektor:</td></tr>";
	echo "<tr><td><form name='f_stg' action='lv_verteilung.php?stsem=$stsem&lektor=$lektor".(isset($order)?"&order=$order":"")."' method='POST'>";
	echo "<SELECT name='stg' onChange='javascript:document.f_stg.submit();'>";
	
	if($stg==-1)
		echo "<option value='-1' selected>--Alle anzeigen--</option>";
	else 
		echo "<option value='-1'>--Alle anzeigen--</option>";
		
	//Ausgeben der Studiengänge zb BEL, DVT etc
	while($row=pg_fetch_object($result))
	{
		if($row->studiengang_kz==$stg)
		   echo "<option value='$row->studiengang_kz' selected>$row->kurzbz</option>";
		else 
		   echo "<option value='$row->studiengang_kz'>$row->kurzbz</option>";
	}
	echo "</SELECT></form></td>";
	echo "\n";
	
	$sql_query = "SELECT a.uid, nachname, vornamen FROM public.tbl_mitarbeiter a, public.tbl_person b where a.uid=b.uid AND a.lektor='true' ORDER BY b.nachname"; 
	$result = pg_exec($conn,$sql_query);
	echo "\n";
	echo "<td><form name='f_lek' action='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=$sem".(isset($order)?"&order=$order":"")."' method='POST'>";
	echo "<SELECT name='lektor' onChange='javascript:document.f_lek.submit();'>";
	if(!strcmp($lektor,"-1"))
	   echo "<option value='-1' selected>--Alle anzeigen--</option>";
	else 
	   echo "<option value='-1'>--Alle anzeigen--</option>";
	   
	//Ausgeben der Lektoren
	
	while($row=pg_fetch_object($result))
	{		
		if(!strcmp($lektor,$row->uid))
		   echo "<option value='$row->uid' selected>$row->nachname $row->vornamen ($row->uid)</option>";
		else 
		   echo "<option value='$row->uid'>$row->nachname $row->vornamen ($row->uid)</option>";
	}
	echo "</SELECT></form></td></tr><tr><td>";
	echo "\n";
	
	if($stg!=-1) //Wenn ein Studiengang ausgewählt wurde
	{
		//Anzeigen der Semester
		echo "Semester:</td>";
		echo "<td><a href='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=-1&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'>Alle </a>";
		
		$sql_query = "SELECT max_semester FROM public.tbl_studiengang WHERE studiengang_kz='$stg' LIMIT 1";
		//echo $sql_query;
		$result = pg_exec($conn,$sql_query);
		if($row=pg_fetch_object($result))
		{
			for($i=1;$i<($row->max_semester+1);$i++)
			{
				echo "-<a href='lv_verteilung.php?stsem=$stsem&stg=$stg&sem=$i&lektor=$lektor".(isset($order)?"&order=$order":"")."' class='linkgreen'> $i </a>";
			}
		}
		
	}
	echo "&nbsp;</td></tr></table><br>";
	
	echo "Aktuelle Auswahl:";
	if($stsem!=-1)
	   echo " Studiensemester: $stsem";
	if($stg!=-1)
	{
		$sql_query = "SELECT kurzbz FROM tbl_studiengang where studiengang_kz='$stg'";
		$result=pg_exec($conn,$sql_query);
		$row = pg_fetch_object($result);
	    echo " Studiengang: $row->kurzbz";
	}
	if($sem!=-1)
	   echo " Semester: $sem";
	if($lektor!=-1)
	   echo " Lektor: $lektor";
	   
	echo "<br>";
	//Tabelle aufbauen
	
	//Daten holen
	$lvobj = new lv_verteilung($conn);
	
	
	if($lvobj->getTab($stsem,$sem,$stg,$lektor,$order))
	{
		echo "\n";
		echo '<table class="liste">';
		echo "\n";
		echo '  <tr class="liste">';
		//Kopfzeile der Tabelle
		echo "<td>&nbsp;</td><td>&nbsp;</td><td>S</td><td>V</td><td>G</td><td>Einheit</td>";
		echo "<td><a href='lv_verteilung.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=lektor'>Lektor</a></td>";
		echo "<td>Raumtyp</td><td>SS</td><td>Blockung</td><td>WR</td><td>LFKZ</td><td>Lehre</td>";
		echo "<td><a href='lv_verteilung.php?stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&order=lehrevz'>LVZ</td>";
		echo "<td>Lehrfachbezeichnung</tr>";
		echo "\n";
		
		
		//Tabellenelemente rausschreiben
		for($i=0;$i<$lvobj->anz;$i++)
		{
			$fe = $lvobj->retwert[$i];
			echo "\n";
			echo '  <tr class="liste'.($i%2).'">';
			echo "<td><a href='lv_edit.php?lvid=$fe->lehrveranstaltung_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?'&order='.$order:'')."' class='linkgreen'>edit</a></td>";
			echo "<td><a href='lv_verteilung?lvid=$fe->lehrveranstaltung_id&del=1&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?"&order=$order":"")."' onClick='javascript:return conf_del();' class='linkgreen'>delete</a></td>";
			echo "<td>$fe->semester</td>";
			echo "<td>$fe->verband</td>";
			echo "<td>$fe->gruppe</td>";
			echo "<td>$fe->einheit_kurzbz</td>";
			echo "<td>$fe->lektor</td>";
			echo "<td nowrap>$fe->raumtyp / $fe->raumtypalternativ</td>";
			echo "<td>$fe->semesterstunden</td>";
			//echo "<td>$fe->stundenblockung</td>";
			echo "<td nowrap><form action='lv_verteilung.php?lfnr=$fe->lehrfach_nr&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&lvnr=$fe->lvnr".(isset($order)?"&order=$order":"")."' method='POST'><input type='text' value='$fe->stundenblockung' size='2' name='stb'><input type='submit' value='ok'></form></td>";
			echo "<td>$fe->wochenrythmus</td>";
			echo "<td>$fe->lehrfach_kurzbz</td>";
			echo "<td><form action='lv_verteilung.php?lvid=$fe->lehrveranstaltung_id&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem&lehre=$fe->lehre".(isset($order)?"&order=$order":"")."' method='POST'><input type='image' src='../../../skin/images/".($fe->lehre=='t'?'true.gif':'false.gif')."'></form></td>";
			echo "<td nowrap><form action='lv_verteilung.php?lfnr=$fe->lehrfach_nr&stg=$stg&stsem=$stsem&lektor=$lektor&sem=$sem".(isset($order)?"&order=$order":"")."' method='POST'><input type='text' value='$fe->lehrevz' size='5' name='lvz'><input type='submit' value='ok'></form></td>";
			echo "<td>$fe->lehrfach_bz</td>";
			echo "</tr>";
		}
	}
	else 
	{
		echo "<br>Keine Daten mit diesen Kriterien Vorhanden";
	}
	
?>
</body>
</html>