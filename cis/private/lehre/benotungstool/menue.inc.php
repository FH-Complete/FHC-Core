<?php
echo "\n<!--Menue-->\n";
echo "<br><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;‹bungen</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und ‹bersichtstabelle</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font>";
echo "</a>";
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1]; 
if ($pfile == "verwaltung.php")
	echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;‹bersicht";
else if ($pfile == "verwaltung_listen.php")
{
	echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>‹bersicht</a>";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "-&gt‹bung";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "-&gt;<a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>‹bung</a>-&gt;Liste";
}
echo "<br><br>";
echo "\n<!--Menue Ende-->\n";
?>