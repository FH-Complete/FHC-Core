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
echo "<br><div><ul><li><a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>Lehrveranstaltung benoten</font></a></li><li><a href='".APP_ROOT."cis/cisdocs/handbuch_benotungstool.pdf' class='Item' target='_blank'><font size='3'>Handbuch Benotungstool (PDF)</font></a></li></ul></div>";
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
	echo "<b>Übersicht</b>";
else if ($pfile == "verwaltung_listen.php")
{
	echo "<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>Übersicht</a> \n";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "		| <b>Übung</b> \n";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "		| <a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>Übung</a> | <b>Liste</b> \n";
}

if ($pfile == "studentenpunkteverwalten.php" && (!isset($_GET["uid"]) || $_GET["uid"]==""))
	echo "<b>Übungsnoten verwalten: Übersicht</b> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "studentenpunkteverwalten.php")
	echo "<b>Übungsnoten verwalten: Detail</b> / <a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>Übersicht</a> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
	
else if ($pfile == "legesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>Übungsnoten verwalten</a> | <b>LE-Noten verwalten</b> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LV-Noten verwalten</a>";
else if ($pfile == "lvgesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>Übungsnoten verwalten</a> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>LE-Noten verwalten</a> | <b>LV-Noten verwalten</b>";

if	($pfile == 'anwesenheitstabelle.php')
	echo "&nbsp;";
if	($pfile == 'statistik.php')
	echo "&nbsp;";
	
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<!--Menue Ende--><br>\n\n";
?>
