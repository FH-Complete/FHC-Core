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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
// **
// * @brief Uebersicht der Zeitsperren fuer Lektorengruppen

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');

	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	$datum_obj = new datum();

	$datum_beginn=date('Y-m-d');
	$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
	$ts_ende=$datum_obj->jump_week($ts_beginn,1);
	$datum_ende=date('Y-m-d',$ts_ende);

	// Lektoren holen
	$ma=new mitarbeiter($conn);
	$mitarbeiter=$ma->getMitarbeiterZeitsperre($datum_beginn,$datum_ende);
?>

<html>
<head>
	<title>Zeitsperren</title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>

<body>
	<H2>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>&nbsp;Zeitsperren</td>
				<td align="right">
					<A onclick="window.open('zeitwunsch_help.html','Hilfe', 'height=320,width=480,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');" class="hilfe" target="_blank">HELP&nbsp;</A>
				</td>
			</tr>
		</table>
	</H2>

	<H3>Zeitsperren von <?php echo $datum_beginn.' bis '.$datum_ende; ?></H3>
	<TABLE id="zeitsperren">
    <TR>
    	<?php
	  	echo '<th>Monat<br>Tag</th>';
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$wt=date('w',$ts);
			$monat=date('M',$ts);
			if ($wt==0)
				$class='feiertag';
			else
				$class='';
			echo "<th class='$class'><div align=\"center\">$monat<br>$tag</div></th>";
		}
		?>
	</TR>

	<?php
	$zs=new zeitsperre($conn);
	foreach ($mitarbeiter as $ma)
	{
		$zs->getzeitsperren($ma->uid);
		echo '<TR>';
		echo "<td>$ma->nachname $ma->vorname</td>";
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$monat=date('M',$ts);
			$grund=$zs->getTyp($ts);
			$erbk=$zs->getErreichbarkeit($ts);
			echo "<td>$grund<br>$erbk</td>";
		}
		echo '</TR>';
	}
	?>

  </TABLE>
</body>
</html>
