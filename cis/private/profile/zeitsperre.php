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

	$uid = get_uid();

	if(isset($_GET['funktion']))
		$funktione=$_GET['funktion'];
	else
		$funktione='lkt';
	if(isset($_GET['stg_kz']))
		$stg_kz=$_GET['stg_kz'];
	$stge=array();
	$stge[]=$stg_kz;

	if(isset($_GET['studiensemester']))
		$studiensemester=$_GET['studiensemester'];
	else
		$studiensemester=null;


	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	$datum_obj = new datum();

	// Studiensemester setzen
	$ss=new studiensemester($conn,$studiensemester);
	if ($studiensemester==null)
	{
		$studiensemester=$ss->getaktorNext();
		$ss->load($studiensemester);
	}
	$datum_beginn=$ss->start;
	$datum_ende=$ss->ende;
	$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
	$ts_ende=$datum_obj->mktime_fromdate($datum_ende);

	// Lektoren holen
	$ma=new mitarbeiter($conn);
	$mitarbeiter=$ma->getMitarbeiterStg(true,null,$stge,$funktion);


?>

<html>
<head>
	<title>Zeitsperren <?php echo $studiensemester; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../../skin/cis.css" type="text/css">
</head>

<body>
	<H2>
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td>&nbsp;Zeitsperren <?php echo $studiensemester; ?></td>
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
			echo "<td>$grund</td>";
		}
		echo '</TR>';
	}
	?>

  </TABLE>
</body>
</html>
