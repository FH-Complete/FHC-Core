<?php
echo "\n\n<!--Menue-->\n";
echo "<br>\n";
echo "<table cellpadding='3'>\n";
echo "	<tr>\n";
echo "		<td><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;�bungen</font></a></td>\n";
echo "		<td><a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Benotung</font></a></td>\n";
echo "		<td><a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Anwesenheits- und �bersichtstabelle</font></a></td>\n";
echo "		<td><a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'><img src='../../../../skin/images/menu_item.gif' width='7' height='9'>&nbsp;Statistik</font></a></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='4'>";
$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1]; 
if ($pfile == "verwaltung.php")
	echo "&nbsp;&nbsp;&nbsp;&nbsp;�bersicht";
else if ($pfile == "verwaltung_listen.php")
{
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>�bersicht</a> \n";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "		-&gt�bung \n";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "		-&gt;<a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>�bung</a>-&gt;Liste \n";
}

if ($pfile == "studentenpunkteverwalten.php")
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>�bersicht</a> -&gt;<a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> -&gt;<a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "legesamtnoteverwalten.php")
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>�bersicht</a> -&gt; LE-Noten verwalten -&gt;<a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "lvgesamtnoteverwalten.php")
	echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>�bersicht</a> -&gt;<a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> -&gt;LV-Noten verwalten";
	
echo "		<br><br>\n";
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<!--Menue Ende--><br>\n\n";
?>
