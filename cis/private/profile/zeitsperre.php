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
	require_once('../../../include/globals.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	require_once('../../../include/studiensemester.class.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/fachbereich.class.php');

	$uid = get_uid();

	if(isset($_GET['lektor']))
		$lektor=$_GET['lektor'];
	else
		$lektor=null;

	if(isset($_GET['fix']))
		$fix=$_GET['fix'];
	else
		$fix=null;
	if ($fix=='false') $fix=false;
	if ($fix=='true' || $fix=='1') $fix=true;

	if(isset($_GET['funktion']))
		$funktion=$_GET['funktion'];
	else
		$funktion=null;

	if(isset($_GET['institut']))
		$institut = $_GET['institut'];
	else
		$institut = null;

	$stge=array();
	if(isset($_GET['stg_kz']))
	{
		$stg_kz=$_GET['stg_kz'];
		$stge[]=$stg_kz;
	}

	if(isset($_GET['studiensemester']))
		$studiensemester=$_GET['studiensemester'];
	else
		$studiensemester=null;

	// Link fuer den Export
	$export_link='zeitsperre_export.php?';

	if(!is_null($institut))
		$export_link.="institut=$institut";
	else
	{
		if ($fix==true)
			$export_link.='fix=true';
			//&lektor=$lektor&funktion=$funktion";
	}


	if (!$conn = pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	$datum_obj = new datum();

	// Studiensemester setzen
	$ss=new studiensemester($conn,$studiensemester);
	if ($studiensemester==null)
	{
		$studiensemester = $ss->getaktorNext();
		$ss->load($studiensemester);
	}

	$datum_beginn='2008-06-01'; // $ss->start;
	$datum_ende='2008-08-31';	//$ss->ende;

	$dTmpCheck=date("Y.m.d", mktime(0,0,0,date("m"),date("d"),date("y")));
	if ($datum_ende<$dTmpCheck)
	{

		$dTmpAktuellerMontag=date("Y-m-d",strtotime(date('Y')."W".date('W')."1")); // Montag der Aktuellen Woche
		$dTmpAktuellesDatum=explode("-",$dTmpAktuellerMontag);
		$dTmpMontagPlus=date("Y-m-d", mktime(0,0,0,date($dTmpAktuellesDatum[1]),date($dTmpAktuellesDatum[2])+14,date($dTmpAktuellesDatum[0])));

		$datum_beginn=$dTmpAktuellerMontag; 
		$datum_ende=$dTmpMontagPlus;
	
	}	


	$ts_beginn=$datum_obj->mktime_fromdate($datum_beginn);
	$ts_ende=$datum_obj->mktime_fromdate($datum_ende);

	// Lektoren holen
	$ma=new mitarbeiter($conn);

	if(!is_null($institut))
	{
		$mitarbeiter = $ma->getMitarbeiterInstitut($institut);
	}
	else
	{
		if (is_null($funktion))
			$mitarbeiter=$ma->getMitarbeiter($lektor,$fix);
		else
			$mitarbeiter=$ma->getMitarbeiterStg(true,null,$stge,$funktion);
	}

?>

<html>
<head>
	<title>Zeitsperren <?php echo $studiensemester; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
</head>

<body id="inhalt">
	<H2>
		<table class="tabcontent">
			<tr>
				<td>&nbsp;Zeitsperren <?php echo $studiensemester; ?></td>
				<td align="right">
					<A class="hilfe" onclick="window.open('zeitwunsch_help.html','Hilfe', 'height=320,width=480,left=0,top=0,hotkeys=0,resizable=yes,status=no,scrollbars=yes,toolbar=no,location=no,menubar=no,dependent=yes');" target="_blank">HELP&nbsp;</A>
				</td>
			</tr>
		</table>
	</H2>

	<H3>Zeitsperren von <?php echo $datum_beginn.' bis '.$datum_ende; ?></H3>
	<?php
		if(isset($_GET['institut']))
		{
			echo '<br>';
			echo '<FORM action="'.$_SERVER['PHP_SELF'].'" method="GET">Institut: <SELECT name="institut">';
			$fachbereich = new fachbereich($conn);
			$fachbereich->getAll();
			echo "<option value='' ".(is_null($institut)?'selected':'').">-- Auswahl --</option>";
			foreach ($fachbereich->result as $fb)
			{
				if($fb->aktiv)
				{
					if($fb->fachbereich_kurzbz==$institut)
						$selected='selected';
					else
						$selected='';

					echo "<option value='$fb->fachbereich_kurzbz' $selected>$fb->bezeichnung</option>";
				}
			}
			echo '</SELECT><input type="submit" value="Anzeigen"></FORM>';
			echo '<br>';
		}
	?>
	<a class="Item" href="<?php echo $export_link; ?>">Excel</a>
	<TABLE id="zeitsperren">
    <TR>
    	<?php
	  	echo '<th>Monat<br>Tag</th>';
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$wt=date('w',$ts);
			$monat=date('M',$ts);
			if ($wt==0 || $wt==6)
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
		$zs->getzeitsperren($ma->uid, false);
		echo '<TR>';
		echo "<td>$ma->nachname $ma->vorname</td>";
		for ($ts=$ts_beginn;$ts<$ts_ende; $ts+=$datum_obj->ts_day)
		{
			$tag=date('d',$ts);
			$monat=date('M',$ts);
			$wt=date('w',$ts);
			if ($wt==0 || $wt==6)
				$class='feiertag';
			else
				$class='';
			$grund=$zs->getTyp($ts);
			$erbk=$zs->getErreichbarkeit($ts);
			echo "<td class='$class'>$grund<br>$erbk</td>";
		}
		echo '</TR>';
	}
	?>

  </TABLE>
</body>
</html>
