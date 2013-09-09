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
echo "<div><ul><li><a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>".$p->t('benotungstool/lehrveranstaltungBenoten')."</font></a></li><li><a href=".APP_ROOT."cms/dms.php?id=".$p->t('dms_link/benotungstoolHandbuch')." class='Item' target='_blank'><font size='3'>".$p->t('benotungstool/handbuchBenotungstool')."</font></a></li></ul></div>";
echo "<br>\n";
echo "<table cellpadding='3' width='100%'>\n";
echo "	<tr>\n";
echo "		<td class='$class_uebung'><a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>".$p->t('benotungstool/uebungen')."</font></a></td>\n";
echo "		<td class='$class_benotung'><a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>".$p->t('benotungstool/benotung')."</font></a></td>\n";
echo "		<td class='$class_anwesenheit'><a href='anwesenheitstabelle.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&uebung_id=$uebung_id' class='Item'><font size='3'>".$p->t('benotungstool/anwesenheitstabelle')."</font></a></td>\n";
echo "		<td class='$class_statistik'><a href='statistik.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'><font size='3'>".$p->t('benotungstool/statistik')."</font></a></td>\n";
echo "	</tr>\n";
echo "	<tr>\n";
echo "		<td colspan='4' class='benotungstool_subtab'>";

if ($pfile == "verwaltung.php")
	echo "<b>".$p->t('benotungstool/uebersicht')."</b>";
else if ($pfile == "verwaltung_listen.php")
{
	echo "<a href='verwaltung.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/uebersicht')."</a> \n";
	if (isset($_GET["liste_id"]) && !isset($_GET["uebung_id"]))
		echo "		| <b>".$p->t('benotungstool/uebung')."</b> \n";
	if (isset($_GET["liste_id"]) && isset($_GET["uebung_id"]))
		echo "		| <a href='verwaltung_listen.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id&liste_id=$liste_id'>".$p->t('benotungstool/uebung')."</a> | <b>".$p->t('benotungstool/liste')."</b> \n";
}

if ($pfile == "studentenpunkteverwalten.php" && (!isset($_GET["uid"]) || $_GET["uid"]==""))
	echo "<b>".$p->t('benotungstool/uebungsnotenVerwalten').": ".$p->t('benotungstool/uebersicht')."</b> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/leNotenVerwalten')."</a> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/lvNotenVerwalten')."</a>";
else if ($pfile == "studentenpunkteverwalten.php")
	echo "<b>".$p->t('benotungstool/uebungsnotenVerwalten').": ".$p->t('benotungstool/detail')."</b> / <a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>".$p->t('benotungstool/uebersicht')."</a> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/leNotenVerwalten')."</a> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/lvNotenVerwalten')."</a>";
	
else if ($pfile == "legesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>".$p->t('benotungstool/uebungsnotenVerwalten')."</a> | <b>".$p->t('benotungstool/leNotenVerwalten')."</b> | <a href='lvgesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/lvNotenVerwalten')."</a>";
else if ($pfile == "lvgesamtnoteverwalten.php")
	echo "<a href='studentenpunkteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id' class='Item'>".$p->t('benotungstool/uebungsnotenVerwalten')."</a> | <a href='legesamtnoteverwalten.php?lvid=$lvid&stsem=$stsem&lehreinheit_id=$lehreinheit_id'>".$p->t('benotungstool/leNotenVerwalten')."</a> | <b>".$p->t('benotungstool/lvNotenVerwalten')."</b>";

if	($pfile == 'anwesenheitstabelle.php')
	echo "&nbsp;";
if	($pfile == 'statistik.php')
	echo "&nbsp;";
	
echo "		</td>\n";
echo "	</tr>\n";
echo "</table>\n";
echo "<!--Menue Ende--><br>\n\n";
?>
