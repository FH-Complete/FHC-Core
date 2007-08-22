<?php
$class_uebung = 'benotungstool_tabs';
$class_benotung = 'benotungstool_tabs';
$class_anwesenheit = 'benotungstool_tabs';
$class_statistik = 'benotungstool_tabs';

$file = $_SERVER["SCRIPT_NAME"];
$break = Explode('/', $file);
$pfile = $break[count($break) - 1]; 

if ($pfile == 'verwaltung.php' or $pfile == 'verwaltung_listen.php')
	$class_uebung = 'benotungstool_tabs_active';
else if ($pfile == 'studentenpunkteverwalten.php' or $pfile == 'legesamtnoteverwalten.php' or $pfile == 'lvgesamtnoteverwalten.php')
	$class_benotung = 'benotungstool_tabs_active';
else if ($pfile == 'anwesenheitstabelle.php')
	$class_anwesenheit = 'benotungstool_tabs_active';
else if ($pfile == 'statistik.php')
	$class_statistik = 'benotungstool_tabs_active';
	
echo "\n\n<!--Menue-->\n";
echo "<br>\n";
echo "<table cellpadding='3' width='100%'>\n";
echo "	<tr>\n";
echo "		<td class='$class_uebung'><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>Übungen</font></a></td>\n";
echo "		<td class='$class_benotung'><a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>Benotung</font></a></td>\n";
echo "		<td class='$class_anwesenheit'><a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'>Anwesenheits- und Übersichtstabelle</font></a></td>\n";
echo "		<td class='$class_statistik'><a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>Statistik</font></a></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='4' class='benotungstool_subtab'>";

if ($pfile == "verwaltung.php")
	echo "Übersicht";
else if ($pfile == "verwaltung_listen.php")
{
	echo "<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>Übersicht</a> \n";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "		-&gtÜbung \n";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "		-&gt;<a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>Übung</a>-&gt;Liste \n";
}

if ($pfile == "studentenpunkteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>Notenübersicht</a> -&gt;<a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> -&gt;<a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "legesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>Übersicht</a> -&gt; LE-Noten verwalten -&gt;<a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "lvgesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>übersicht</a> -&gt;<a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> -&gt;LV-Noten verwalten";

if	($pfile == 'anwesenheitstabelle.php')
	echo "&nbsp;";
if	($pfile == 'statistik.php')
	echo "&nbsp;";
	
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<!--Menue Ende--><br>\n\n";
?>
