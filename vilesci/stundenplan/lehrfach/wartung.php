<?php
/*
$Header: /Pfad/Kodierrichtlinien.tex,v 1.2 2004/02/29 17:05:38 pam Exp $
$Log: Kodierrichtlinien.tex,v $
Revision 1.2 2004/02/29 17:05:38 pam
Fehler in Umlauten beseitigt.
*/
   //DB Verbindung herstellen
   include('../../config.inc.php');
   include('../../../include/lehrfach.class.php');

   if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

   //Initialisierung der Variablen
   if(!isset($stg_1))
   	  $stg_1=227;

   if(!isset($stg_2))
      $stg_2=227;

   if(!isset($sem_1))
   	  $sem_1=1;

   if(!isset($sem_2))
      $sem_2=1;

   if(!isset($order_1))
      $order_1='lehrfach_nr';

   if(!isset($order_2))
   	  $order_2='lehrfach_nr';

   function kuerze($string)
   {
   	   if(strlen($string)>18)
   	      return substr($string,0,15)."...";
   	   else
   	      return $string;
   }

   if(isset($radio_1) && isset($radio_2))
   {
      if($radio_1==$radio_2)
         $msg="Die Datensätze dürfen nicht die gleiche id haben";
      else
      {
      	$sql_query_upd1="UPDATE tbl_stundenplan Set lehrfach_nr='$radio_2' WHERE lehrfach_nr='$radio_1';";
      	$sql_query_upd1.=" UPDATE tbl_stundenplandev Set lehrfach_nr='$radio_2' WHERE lehrfach_nr='$radio_1';";
      	$sql_query_upd1.=" UPDATE tbl_lehrveranstaltung Set lehrfach_nr='$radio_2' WHERE lehrfach_nr='$radio_1';";
      	$sql_query_upd1.=" UPDATE tbl_lvinfo Set lehrfach_nr='$radio_2' WHERE lehrfach_nr='$radio_1';";
      	$sql_query_upd1.=" DELETE FROM tbl_lehrfach WHERE lehrfach_nr='$radio_1';";

      	if(pg_exec($conn,$sql_query_upd1))
      	   {
      	   	$msg = "Daten Erfolgreich gespeichert<br>";
      	   }
      	$msg .= $sql_query_upd1 ."<br>";

      }
   }

   if((isset($radio_1) && !isset($radio_2))||(!isset($radio_1) && isset($radio_2)))
   {
   	   $msg="Es müssen beide Radio Buttons angeklickt werden";
   }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="../../../skin/vilesci.css" rel="stylesheet" type="text/css">

<title>LV-Verteilung</title>
</head>
<body>

<H1>Lehrfach - Wartung</h1>

<table border="0" cellspacing="0" cellpadding="0">
<tr>
<?php
    //Filterauswahlleiste

    echo "<td>";
    echo "<form name='form_filter_1' action='wartung.php?stg_2=$stg_2&sem_2=$sem_2&order_1=$order_1&order_2=$order_2' method='POST'>";

    //Drop Down Menü für Stg Tab1
    echo "<select name='stg_1'>";
    echo "<option value='-1'>--Alle--</option>";
    $sql_query_stg = "SELECT kurzbz, studiengang_kz FROM tbl_studiengang ORDER BY kurzbz";
    $result_stg = pg_exec($conn,$sql_query_stg);
    while($row_stg=pg_fetch_object($result_stg))
    {
    	if($row_stg->studiengang_kz==$stg_1)
    		echo "<option value='$row_stg->studiengang_kz' selected>$row_stg->kurzbz</option>";
    	else
    		echo "<option value='$row_stg->studiengang_kz'>$row_stg->kurzbz</option>";
    }
    echo "</select>&nbsp;&nbsp;";

    //Drop Down Menü für Sem Tab1
    echo "<select name='sem_1'>";
    echo "<option value='-1'>--Alle--</option>";
    for($i=1;$i<=9;$i++)
    {
    	if($i==$sem_1)
    		echo "<option value='$i' selected>$i</option>";
    	else
    		echo "<option value='$i'>$i</option>";
    }
    echo "</select>&nbsp;&nbsp;";

    echo "<input type='submit' value='Akt'>";
    echo "</form>";
    echo "</td>";
    echo "<td width='50%'>&nbsp;</td>";
    echo "<td>";
    echo "<form name='form_filter_2' action='wartung.php?stg_1=$stg_1&sem_1=$sem_1&order_1=$order_1&order_2=$order_2' method='POST'>";

    //Drop Down Menü für Stg Tab2
    echo "<select name='stg_2'>";
    echo "<option value='-1'>--Alle--</option>";
    $sql_query_stg = "SELECT kurzbz, studiengang_kz FROM tbl_studiengang ORDER BY kurzbz";
    $result_stg = pg_exec($conn,$sql_query_stg);
    while($row_stg=pg_fetch_object($result_stg))
    {
    	if($row_stg->studiengang_kz == $stg_2)
    		echo "<option value='$row_stg->studiengang_kz' selected>$row_stg->kurzbz</option>";
    	else
    		echo "<option value='$row_stg->studiengang_kz'>$row_stg->kurzbz</option>";
    }
    echo "</select>&nbsp;&nbsp;";

    //Drop Down Menü für Sem Tab2
    echo "<select name='sem_2'>";
    echo "<option value='-1'>--Alle--</option>";
    for($i=1;$i<=9;$i++)
    {
    	if($i==$sem_2)
    		echo "<option value='$i' selected>$i</option>";
    	else
    		echo "<option value='$i'>$i</option>";
    }
    echo "</select>&nbsp;&nbsp;";

    echo "<input type='submit' value='Akt'>";
    echo "</form>";
    echo "</td>";
?>
</tr>
</table>
<br>
<center><h2><?php echo $msg; ?></h2></center>
<br>

<?php
   	//Tabellen anzeigen
	echo "<form name='form_table' action='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=$order_1&order_2=$order_2' method='POST'>";
	echo "<table border='0' cellspacing='0' cellpadding='0'>";
	echo "<tr>";
   	echo "<td valign='top'>Das wird geloescht:";

    //Tabelle 1
    echo "<table class='liste'><tr class='liste'>";
    echo "<th>&nbsp;</th><th>LFNr</th>";
    echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=kurzbz&order_2=$order_2'>Kurzbz</a></th>";
    echo "<th>Form</th>";
    echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=bezeichnung&order_2=$order_2'>Bezeichnung</a></th>";
    echo "<th>LVZ</th><th>ECTS</th></tr>";

    $lf  = new lehrfach($conn);
    $lf->getTab($stg_1,$sem_1, $order_1);
    $i=0;
    foreach($lf->fkterg as $l)
    {
    	echo "<tr class='liste".($i%2)."'>";
    	echo "<td><input type='radio' name='radio_1' value='$l->lehrfach_nr' ".((isset($radio_1) && $radio_1==$l->lehrfach_nr)?'checked':'')."></td>";
    	echo "<td>$l->lehrfach_nr</td>";
    	echo "<td>$l->kurzbz</td>";
    	echo "<td>$l->lehrform_kurzbz</td>";
    	echo "<td title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
    	echo "<td>$l->lehrevz</td>";
    	echo "<td>$l->ects</td>";
    	echo "</tr>";
    	$i++;
    }
    echo "</table>";
    echo "</td>";
    echo "<td valign='top'><input type='submit' value='CLEAN'></td>";
    echo "<td valign='top'>Das bleibt";

    //Tabelle 2
    echo "<table class='liste'><tr class='liste'>";
    echo "<th>&nbsp;</th><th>LFNr</th>";
    echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=$order_1&order_2=kurzbz'>Kurzbz</a></th>";
    echo "<th>Form</th>";
    echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=$order_1&order_2=bezeichnung'>Bezeichnung</a></th>";
    echo "<th>LVZ</th><th>ECTS</th></tr>";

    $lf  = new lehrfach($conn);
    $lf->getTab($stg_2,$sem_2, $order_2);
    $i=0;
    foreach($lf->fkterg as $l)
    {
    	echo "<tr class='liste".($i%2)."'>";
    	echo "<td><input type='radio' name='radio_2' value='$l->lehrfach_nr' ".((isset($radio_2) && $radio_2==$l->lehrfach_nr)?'checked':'')."></td>";
    	echo "<td>$l->lehrfach_nr</td>";
    	echo "<td>$l->kurzbz</td>";
    	echo "<td>$l->lehrform_kurzbz</td>";
    	echo "<td title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
    	echo "<td>$l->lehrevz</td>";
    	echo "<td>$l->ects</td>";
    	echo "</tr>";
    	$i++;
    }
    echo "</table>";
    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</form>";

?>
</tr>
</table>
</body>
</html>




