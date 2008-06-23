<?php
/* Copyright (C) 2008 Technikum-Wien
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

	//DB Verbindung herstellen
	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/resturlaub.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');
		
$content_resturlaub = '';
$content = '';
$resturlaubstage = '0';
$mehrarbeitsstunden = '0';
$anspruch = '25';
$zaehl=1;
$tage=array();	//Array Tage für Kalenderanzeige
$hgfarbe=array_fill(0,44,'white'); 	//Array mit Hintegrundfarben der Kalenderfelder
$monatsname = array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");
$jahre = array();	//Array Jahreszahlen für Auswahl (immer aktuelles Jahr und die 4 nächsten Jahre)
$akette=array_fill(0,2,0);
$ekette=array_fill(0,2,0);
$spmonat=array();
$wvon='';
$wbis='';
$t=getdate();
$uid = get_uid();

for($i=0;$i<5;$i++)
{
	$jahre[$i]="$t[year]"+$i;
}


if (isset($_GET['wmonat']) || isset($_POST['wmonat']))
	$wmonat=(isset($_GET['wmonat'])?$_GET['wmonat']:$_POST['wmonat']);
else
{
	$wmonat="$t[mon]"-1;
}

if (isset($_GET['wjahr']) || isset($_POST['wjahr']))
	$wjahr=(isset($_GET['wjahr'])?$_GET['wjahr']:$_POST['wjahr']);
else
{
	$wjahr=0;
}

if(isset($_GET['spmonat']) || isset($_POST['spmonat']))
{
	$spmonat=explode(",",$_GET['spmonat']);
	$mbeginn=mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]);
	$ttt=getdate($mbeginn);
	$wotag="$ttt[wday]";
	if ($wotag==0)
	{
		$wotag=7;
	}
	$mende=cal_days_in_month(CAL_GREGORIAN, ($wmonat+1), $jahre[$wjahr]);
	$wvon=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]));
	$wbis=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $mende, $jahre[$wjahr]));	
	for($i=1,$j=0;$i<44;$i++)
	{	
		//ketten bilden
		if($spmonat[$i]=='lime')
		{
			if($akette[$j]==0 || $spmonat[$i-1]!='lime')
			{
				$j++;
				$akette[$j]=$i-$wotag+1;
				$ekette[$j]=$i-$wotag+1;
			}
			elseif($spmonat[$i-1]=='lime')
			{
				$ekette[$j]=$i-$wotag+1;
			}
		}
	}
	//print_r($akette);
	//print_r($ekette);
	if($ekette[1]!=0)
	{
		//Unterscheidung anhand bestehender Einträge
		//Urlaub vom Vormonat überragend
		$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND vondatum<='".$wvon."' AND bisdatum>='".$wvon."' AND bisdatum<='".$wbis."' ;";
		if($result = pg_query($conn, $qry))
		{
			if(pg_num_rows($result)==1)
			{
				if($row = pg_fetch_object($result))
				{
					if($akette[1]==1)
					{
						$qryupd="UPDATE campus.tbl_zeitsperre SET bisdatum='".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $ekette[1], $jahre[$wjahr]))."' WHERE zeitsperre_id='".$row->zeitsperre_id."';";
						$result = pg_query($conn, $qryupd);
						$akette[1]=0;
						$ekette[1]=0;
						//echo "<br>".$qryupd;
					}
				}
			}
		}
		//Urlaub ins nächste Monat überragend
		$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND bisdatum>='".$wbis."' AND vondatum>='".$wvon."' AND vondatum<='".$wbis."' ;";
		if($result = pg_query($conn, $qry))
		{
			if(pg_num_rows($result)==1)
			{
				if($row = pg_fetch_object($result))
				{
					if($ekette[count($akette)-1]==$mende)
					{
						$qryupd="UPDATE campus.tbl_zeitsperre SET vondatum='".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $akette[count($akette)-1], $jahre[$wjahr]))."' WHERE zeitsperre_id='".$row->zeitsperre_id."';";
						$result = pg_query($conn, $qryupd);
						$akette[count($akette)-1]=0;
						$ekette[count($ekette)-1]=0;
						//print_r($akette);
						//print_r($ekette);
						//echo "<br>".$qryupd;
						
					}
				}
			}
		}
		//Urlaub überragt beide Monatsenden
		$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND bisdatum>='".$wbis."' AND vondatum<='".$wvon."' ;";
		if($result = pg_query($conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				//"Abschneiden" des Eintrags am Ende des vorigen Monats
				$qryupd="UPDATE campus.tbl_zeitsperre SET 
					bisdatum='".date("Y-m-d",mktime(0, 0, 0, ($wmonat) , cal_days_in_month(CAL_GREGORIAN, ($wmonat), $jahre[$wjahr]), $jahre[$wjahr]))."' 
					WHERE zeitsperre_id='".$row->zeitsperre_id."';";
				$result = pg_query($conn, $qryupd);
				//Einfügen eines Eintrags ab dem 1Tag des nächsten Monats
				$qryins="INSERT INTO campus.tbl_zeitsperre (
						zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,
						updateamum,updatevon,insertamum,insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon) VALUES (
						'Urlaub','".$uid."', 'Urlaub', '".date("Y-m-d",mktime(0, 0, 0, ($wmonat+2) , 1, $jahre[$wjahr]))."',
						NULL,'".$row->bisdatum."',NULL,NULL,NULL,NULL,now(),'".$uid."','n',NULL,NULL
						)";
				$result = pg_query($conn, $qryins);
				//echo "<br>".$qryupd;
				//echo "<br>"."1-".$qryins;
				//Einfügen des Urlaubs innerhalb des Monats
				for($i=0;$i<count($akette);$i++)
				{
					if($akette[$i]!=0)
					{
						$qryins="INSERT INTO campus.tbl_zeitsperre (
						zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,
						updateamum,updatevon,insertamum,insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon) VALUES (
						'Urlaub','".$uid."', 'Urlaub', '".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $akette[$i], $jahre[$wjahr]))."',
						NULL,'".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $ekette[$i], $jahre[$wjahr]))."',NULL,NULL,NULL,NULL,now(),'".$uid."','n',NULL,NULL
						)";
						$result = pg_query($conn, $qryins);
						//echo "<br>"."2-".$qryins;
					}
				}	
			}
		}
		//Urlaub innerhalb des Monats
		$qrydel="DELETE FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND vondatum>='".$wvon."' AND bisdatum<='".$wbis."'  ;";
		$result = pg_query($conn, $qrydel);
		//echo "<br>".$qrydel;
		for($i=0;$i<count($akette);$i++)
		{
			if($akette[$i]!=0)
			{
				$qryins="INSERT INTO campus.tbl_zeitsperre (
				zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,
				updateamum,updatevon,insertamum,insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon) VALUES (
				'Urlaub','".$uid."', 'Urlaub', '".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $akette[$i], $jahre[$wjahr]))."',
				NULL,'".date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $ekette[$i], $jahre[$wjahr]))."',NULL,NULL,NULL,NULL,now(),'".$uid."','n',NULL,NULL
				)";
				$result = pg_query($conn, $qryins);
				//echo "<br>"."3-".$qryins;
			}
		}
	}

}
//if ((isset($_GET['wmonat']) || isset($_POST['wmonat']))&&(isset($_GET['wjahr']) || isset($_POST['wjahr'])))
if ((isset($wmonat) || isset($wmonat))&&(isset($wjahr) || isset($wjahr)))
{
	//Urlaubstageage markieren
	$mbeginn=mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]);
	$ttt=getdate($mbeginn);
	$wotag="$ttt[wday]";
	if ($wotag==0)
	{
		$wotag=7;
	}
	$mende=cal_days_in_month(CAL_GREGORIAN, ($wmonat+1), $jahre[$wjahr]);
	$wvon=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]));
	$wbis=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $mende, $jahre[$wjahr]));
	$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND (vondatum<='".$wbis."' AND bisdatum>='".$wvon."') ";
	//echo "<br>"."db:".$qry;
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			//echo " ".$row->vondatum;
			//echo "-".$row->bisdatum;
			for($i=$wotag;$i<$mende+$wotag;$i++)
			{
				if(date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $i-$wotag+1, $jahre[$wjahr]))>=$row->vondatum 
				&& date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $i-$wotag+1, $jahre[$wjahr]))<=$row->bisdatum)
				{
					$hgfarbe[$i]='lime';
				}
				else 
				{
					if($hgfarbe[$i]!='lime')
						$hgfarbe[$i]='white';
				}
			}
		}
		//print_r($hgfarbe);
	}
}

if (isset($_GET['kastl']) || isset($_POST['kastl']))
	$kastl=(isset($_GET['kastl'])?$_GET['kastl']:$_POST['kastl']);
else
{
	$kastl=0;
}

if (isset($_GET['hgfarbe']))
{
	$hgfarbe=explode(",",$_GET['hgfarbe']);
}
else
{
	for($i=0;$i<44;$i++)
	{	
		if(!isset($hgfarbe[$i]) || $hgfarbe[$i]!='lime')
			$hgfarbe[$i]='white';
	}
}


$PHP_SELF = $_SERVER['PHP_SELF'];
$datum_obj = new datum();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd"><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>

<style type="text/css">
a:link { text-decoration:none; font-weight:bold; color:blue; }
a:visited { text-decoration:none; font-weight:bold; color:blue; }

</style>
<title>Urlaubstool</title>
</head>
<body>';
//alert("Ich bin auf Tag " + kastl);
echo "<H1>Urlaubstool (".$uid.")</H1>";
//Anzeige Resturlaubsberechnung
echo '<table width="100%">';
echo '<tr><td colspan=2>';
$resturlaub = new resturlaub($conn);

if($resturlaub->load($uid))
{
	$resturlaubstage = $resturlaub->resturlaubstage;
	$mehrarbeitsstunden = $resturlaub->mehrarbeitsstunden;
	$anspruch = $resturlaub->urlaubstageprojahr;
}
$content_resturlaub.="<table><tr><td>Anspruch</td><td align='right'>$anspruch Tage</td></tr>";
$content_resturlaub.="<tr><td>+ Resturlaub</td><td align='right'>$resturlaubstage Tage</td></tr>";
$gebuchterurlaub=0;
//Urlaub berechnen
$qry = "SELECT sum(bisdatum-vondatum+1) as anzahltage FROM campus.tbl_zeitsperre 
			WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='$uid' AND
			(
				(date_part('month', vondatum)>9 AND date_part('year', vondatum)='".(date('Y')-1)."') OR
				(date_part('month', vondatum)<9 AND date_part('year', vondatum)='".date('Y')."')
			)";	
$result = pg_query($conn, $qry);
$row = pg_fetch_object($result);
$gebuchterurlaub = $row->anzahltage;
if($gebuchterurlaub=='')
	$gebuchterurlaub=0;
$content_resturlaub.="<tr><td>- aktuell gebuchter Urlaub&nbsp;</td><td align='right'>$gebuchterurlaub Tage</td></tr>";
$content_resturlaub.="<tr><td style='border-top: 1px solid black;'>aktueller Stand</td><td style='border-top: 1px solid black;' align='right'>".($anspruch+$resturlaubstage-$gebuchterurlaub)." Tage</td></tr>";
$content_resturlaub .="<tr><td><button type='button' name='hilfe' value='Hilfe' onclick='alert(\"Anspruch: Anzahl der Urlaubstage, auf die in diesem Geschäftsjahr (1.9. bis 31.8) ein Anrecht ensteht. \\nResturlaub: Anzahl der Urlaubstage, aus vergangenen Geschäftsjahren, die noch nicht verbraucht wurden. \\naktuell gebuchter Urlaub: Anzahl aller eingetragenen Urlaubstage. \\nAchtung: Als Urlaubstag gelten ALLE Tage zwischen von-Datum und bis-Datum d.h. auch alle Wochenenden, Feiertage und arbeitsfreie Tage. Beispiel: Ein Kurzurlaub beginnt mit einem Donnerstag und endet am darauffolgenden Dienstag, so wird zuerst eine Eintragung mit dem Datum des Donnerstags im von-Feld und dem Datum des letzten Urlaubstag vor dem Wochenende, meistens der Freitag, eingegeben. Danach wird eine Eintagung des zweiten Teils, von Montag bis Dienstag vorgenommen.\\naktueller Stand: Die zur Zeit noch verfügbaren Urlaubstage.\");'>Hilfe</button></td></tr>";
$content_resturlaub.="</table>";

//Formular Auswahl Monat und Jahr für Kalender
echo '<table width="80%" align="center">';
echo "<td class='tdvertical'>$content_resturlaub</td>";
echo '</td></tr><tr height=20></tr>';
echo '<tr><td>';
$content= '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';

$content.='Monat <SELECT name="wmonat">';
for($i=0;$i<12;$i++)
{
	if($wmonat==$i)
	{
		$selected='selected';
	}
	else 
	{
		$selected='';
	}
	$content.="<option value='$i' $selected>$monatsname[$i]</option>";
}
$content.='</SELECT>';
$content.='&nbsp;Jahr <SELECT name="wjahr">';
for($i=0;$i<5;$i++)
{
	if($wjahr==$i)
	{
		$selected='selected';
	}
	else 
	{
		$selected='';
	}
	$content.="<option value='$i' $selected>$jahre[$i]</option>";
}	
$content.='</SELECT>';
$content.="&nbsp;<INPUT type='submit' value='OK'>";
$content.='</form>';

//Tage 
$mbeginn=mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]);
$ttt=getdate($mbeginn);
$wotag="$ttt[wday]";
if ($wotag==0)
{
	$wotag=7;
}
$mende = cal_days_in_month(CAL_GREGORIAN, ($wmonat+1), $jahre[$wjahr]);
//echo "monatsende:".$mende;
for($i=1;$i<43;$i++)
{
	if($i<$wotag || $zaehl>$mende)
	{
		$tage[$i]='';
	}
	else 
	{
		$tage[$i]=$zaehl;
		$zaehl++;
	}
}
//Toggle der Hintergrundfarbe
if($kastl>0)
{
	if($hgfarbe[$kastl]=="white" )
	{
		$hgfarbe[$kastl]='lime';
	}
	else 
	{
		$hgfarbe[$kastl]='white';
	}
}
ksort($hgfarbe);
$content.='</td><td>';
$content.='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
$content.='<input type="hidden" name="spmonat" value="'.implode(",",$hgfarbe).'"'; 
$content.='<input type="hidden" name="wmonat" value="'.$wmonat.'"';
$content.='<input type="hidden" name="wjahr" value="'.$wjahr.'"';
$content.='<input type="submit" name="speichern" value="Monat speichern">';
$content.='</form>';
$content.='</td></tr>';
$content.='</table>';
$content.='<table border=1 width="80%" align="center">';

$content.='<th width="14%">Montag</th><th width="14%">Dienstag</th><th width="14%">Mittwoch</th><th width="15%">Donnerstag</th><th width="14%">Freitag</th><th width="14%">Samstag</th><th width="14%">Sonntag</th>';
for ($i=0;$i<6;$i++)
{
	$content.='<tr height="80" style="font-family:Arial,sans-serif; font-size:52px; color:blue">';
	for ($j=1;$j<8;$j++)
	{
		$content.='<td align="center" valign="center" style="background-color: '.$hgfarbe[$j+7*$i].'"><a href="'.$PHP_SELF.'?kastl='.($j+7*$i).'&wmonat='.$wmonat.'&wjahr='.$wjahr.'&hgfarbe='.implode(",",$hgfarbe).'"><b>'.$tage[$j+7*$i].'</b></a></td>'; //style="background-color:lime"
	}
	$content.='</tr>';
}
$content.='</table>';
echo $content;
?>
</body>
</html>