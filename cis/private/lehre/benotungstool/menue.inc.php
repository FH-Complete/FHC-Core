<?php
echo "\n<!--Menue-->\n";
echo "<br>";
echo "<table>";
echo "<tr>";
echo "<td><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;‹bungen</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
echo "<td><a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und ‹bersichtstabelle</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
echo "<td><a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Studentenpunkte verwalten</font>";
echo "</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
echo "<td><a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>";
echo "<font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font>";
echo "</a></td>";
echo "</tr>";
echo "<tr>";
echo "<td colspan='4'>";
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1]; 
if ($pfile == "verwaltung.php")
	echo "&nbsp;&nbsp;&nbsp;&nbsp;‹bersicht";
else if ($pfile == "verwaltung_listen.php")
{
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>‹bersicht</a>";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "-&gt‹bung";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "-&gt;<a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>‹bung</a>-&gt;Liste";
}
echo "<br><br>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "\n<!--Menue Ende-->\n";
?>