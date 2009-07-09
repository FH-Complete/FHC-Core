<?php
/* Copyright (C) 2006 Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
		require_once('../../../config/vilesci.config.inc.php');
		require_once('../../../include/basis_db.class.php');
		if (!$db = new basis_db())
			die('Es konnte keine Verbindung zum Server aufgebaut werden.');
			

// *****************************************
// * Script zum Entfernen Doppelter LF
// * Es werden zwei listen mit LF angezeigt
// * Links wird das LF markiert das entfernt
// * werden soll, rechts das durch welches 
// * es ersetzt wird.
// ************************************
	//DB Verbindung herstellen

	require_once('../../../include/lehrfach.class.php');

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
		$order_1='lehrfach_id';

	if(!isset($order_2))
		$order_2='lehrfach_id';

	function kuerze($string)
	{
		if(strlen($string)>18)
			return substr($string,0,15)."...";
		else
			return $string;
	}
	$msg='';
	
	//Lehrfach Loeschen
	if(isset($radio_1) && isset($radio_2))
	{
		if($radio_1==$radio_2)
			$msg="Die Datensaetze duerfen nicht die gleiche id haben";
		else
		{
			$sql_query_upd1="UPDATE lehre.tbl_lehreinheit SET lehrfach_id='$radio_2' WHERE lehrfach_id='$radio_1';";
			$sql_query_upd1.=" DELETE FROM lehre.tbl_lehrfach WHERE lehrfach_id='$radio_1';";
			
			if($db->db_query($sql_query_upd1))
			{
				$msg = "Daten Erfolgreich gespeichert<br>";
			}
			$msg .= $sql_query_upd1 ."<br>";

		}
	}

	if((isset($radio_1) && !isset($radio_2))||(!isset($radio_1) && isset($radio_2)))
	{
			$msg="Es muessen beide Radio Buttons angeklickt werden";
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
	 $sql_query_stg = "SELECT UPPER(typ::varchar(1) || kurzbz) as kurzbz, studiengang_kz FROM public.tbl_studiengang ORDER BY kurzbz";
	 $result_stg = $db->db_query($sql_query_stg);
	 while($row_stg=$db->db_fetch_object($result_stg))
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
	 $sql_query_stg = "SELECT UPPER(typ::varchar(1) || kurzbz) as kurzbz, studiengang_kz FROM public.tbl_studiengang ORDER BY kurzbz";
	 $result_stg = $db->db_query($sql_query_stg); 
	 while($row_stg=$db->db_fetch_object($result_stg))
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
	 echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=bezeichnung&order_2=$order_2'>Bezeichnung</a></th>";
	 echo "<th>Sprache</th></tr>";

	 $lf  = new lehrfach();
	 $lf->getTab($stg_1,$sem_1, $order_1);
	 $i=0;
	 foreach($lf->lehrfaecher as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td><input type='radio' name='radio_1' value='$l->lehrfach_id' ".((isset($radio_1) && $radio_1==$l->lehrfach_id)?'checked':'')."></td>";
	 	echo "<td>$l->lehrfach_id</td>";
	 	echo "<td>$l->kurzbz</td>";
	 	echo "<td title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
	 	echo "<td>$l->sprache</td>";
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
	 echo "<th><a href='wartung.php?stg_1=$stg_1&stg_2=$stg_2&sem_1=$sem_1&sem_2=$sem_2&order_1=$order_1&order_2=bezeichnung'>Bezeichnung</a></th>";
	 echo "<th>Sprache</th></tr>";

	 $lf  = new lehrfach();
	 $lf->getTab($stg_2,$sem_2, $order_2);
	 $i=0;
	 foreach($lf->lehrfaecher as $l)
	 {
	 	echo "<tr class='liste".($i%2)."'>";
	 	echo "<td><input type='radio' name='radio_2' value='$l->lehrfach_id' ".((isset($radio_2) && $radio_2==$l->lehrfach_id)?'checked':'')."></td>";
	 	echo "<td>$l->lehrfach_id</td>";
	 	echo "<td>$l->kurzbz</td>";
	 	echo "<td title='$l->bezeichnung'>".kuerze($l->bezeichnung)."</td>";
	 	echo "<td>$l->sprache</td>";
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




