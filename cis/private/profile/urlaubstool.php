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

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/resturlaub.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');

	//DB Verbindung herstellen
	if (!$conn = @pg_pconnect(CONN_STRING))
		die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$content_resturlaub = '';
$content = '';
$resturlaubstage = '0';
$mehrarbeitsstunden = '0';
$anspruch = '25';
$zaehl=1;
$tage=array();			//Array Tage für Kalenderanzeige
$hgfarbe=array_fill(0,44,'white'); 	//Array mit Hintegrundfarben der Kalenderfelder
$datensatz=array_fill(0,44,0);
$freigabevon=array();
$freigabeamum=array();
$vertretung_uid=array();
$erreichbarkeit_kurzbz=array();
$monatsname = array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");
$jahre = array();			//Array Jahreszahlen für Auswahl (immer aktuelles Jahr und die 4 nächsten Jahre)
$akette=array_fill(0,1,0);
$ekette=array_fill(0,1,0);
$links='';
$rechts='';
$tag=array();
$vertretung='';
$erreichbar='';
$vgmail='';
$spmonat=array();
$hgchange=false;
$wvon='';
$wbis='';
$datensatz='';
$t=getdate();
$uid = get_uid();
$taste=0;

function getVorgesetzten($uid)
{
	global $conn;
	$qry = "SELECT CASE WHEN fachbereich_kurzbz is not null THEN (SELECT uid FROM public.tbl_benutzerfunktion WHERE fachbereich_kurzbz=a.fachbereich_kurzbz AND funktion_kurzbz='fbl' LIMIT 1)
					    WHEN studiengang_kz is not null THEN (SELECT uid FROM public.tbl_benutzerfunktion WHERE studiengang_kz=a.studiengang_kz AND funktion_kurzbz='stgl' LIMIT 1)
					    ELSE ''
				   END as vorgesetzter
	FROM public.tbl_benutzerfunktion a WHERE funktion_kurzbz='Institut' AND uid='$uid'";
	$result = pg_query($conn, $qry);
	$row = pg_fetch_object($result);
	return (isset($row->vorgesetzter)?$row->vorgesetzter:'');
}

$ma= new mitarbeiter($conn);
for($i=0;$i<6;$i++)
{
	$jahre[$i]="$t[year]"+($i-1);
}

if (isset($_GET['wtag']) || isset($_POST['wtag']))
{
	$wtag=(isset($_GET['wtag'])?$_GET['wtag']:$_POST['wtag']);
}
else
{
	$wtag=date("d.m.Y");
}
if (isset($_GET['wmonat']) || isset($_POST['wmonat']))
{
	$wmonat=(isset($_GET['wmonat'])?$_GET['wmonat']:$_POST['wmonat']);
}
else
{
	$wmonat="$t[mon]"-1;
}
if (isset($_GET['wjahr']) || isset($_POST['wjahr']))
{
	$wjahr=(isset($_GET['wjahr'])?$_GET['wjahr']:$_POST['wjahr']);
}
else
{
	$wjahr=1;
}
if (isset($_GET['kastl']) || isset($_POST['kastl']))
{
	$kastl=(isset($_GET['kastl'])?$_GET['kastl']:$_POST['kastl']);
}
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

	if (!isset($_GET['spmonat']))
	{
		for($i=0;$i<44;$i++)
		{
			if(!isset($hgfarbe[$i]) || $hgfarbe[$i]!='lime')
				$hgfarbe[$i]='white';
		}
	}
}
if (isset($_GET['links_x']) || isset($_POST['links_x']))
{
	if ($wmonat==0)
	{
		if($wjahr>0)
		{
			$wmonat=11;
			$wjahr=$wjahr-1;
		}
	}
	else
	{
		$wmonat=$wmonat-1;
		$wjahr=$wjahr;
	}
}
if (isset($_GET['rechts_x']) || isset($_POST['rechts_x']))
{
	if($wmonat==11)
	{
		if($wjahr<4)
		{
			$wmonat=0;
			$wjahr=$wjahr+1;
		}
	}
	else
	{
		$wmonat=$wmonat+1;
		$wjahr=$wjahr;
	}
}
//Eintragung löschen
if((isset($_GET['delete']) || isset($_POST['delete'])))
{
	//print_r($_GET['delete']);
	//echo "<br>";
	$qry="DELETE FROM campus.tbl_zeitsperre WHERE zeitsperre_id=".$_GET['delete']." AND (freigabevon!='' OR freigabevon IS NULL)";
	$result = pg_query($conn, $qry);
}

//Eintragung speichern
if(isset($_GET['speichern']) && isset($_GET['wtag']))
{
	$vertretung=$_GET['vertretung_uid'];
	$erreichbar=$_GET['erreichbar'];
	if($erreichbar=='')
	{
		$erreichbar='n';
	}
	$wtag=$_GET['wtag'];
	$akette[0]=date("Y-m-d",strtotime($wtag[0]));
	$ekette[0]=date("Y-m-d",strtotime($wtag[0]));
	for($i=1,$j=0;$i<count($wtag);$i++)
	{
		//ketten bilden
		if(date("Y-m-d",strtotime($wtag[$i]))==date("Y-m-d",strtotime("+1 Day",strtotime($wtag[$i-1]))))
		{
			$ekette[$j]=date("Y-m-d",strtotime($wtag[$i]));
		}
		else
		{
			$j++;
			$akette[$j]=date("Y-m-d",strtotime($wtag[$i]));
			$ekette[$j]=date("Y-m-d",strtotime($wtag[$i]));
		}

	}
	//print_r($akette);
	//print_r($ekette);
	FOR($i=0;$i<count($akette);$i++)
	{
		if($vertretung!='')
		{
			$qryins="INSERT INTO campus.tbl_zeitsperre (
				zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,
				updateamum,updatevon,insertamum,insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon) VALUES (
				'Urlaub','".$uid."', 'Urlaub', '".date("Y-m-d",strtotime($akette[$i]))."',
				NULL,'".date("Y-m-d", strtotime($ekette[$i]))."',NULL,'".$vertretung."',NULL,NULL,now(),'".$uid."','".$erreichbar."',NULL,NULL
				)";
		}
		else
		{
			$qryins="INSERT INTO campus.tbl_zeitsperre (
				zeitsperretyp_kurzbz,mitarbeiter_uid,bezeichnung,vondatum,vonstunde,bisdatum,bisstunde,vertretung_uid,
				updateamum,updatevon,insertamum,insertvon, erreichbarkeit_kurzbz, freigabeamum, freigabevon) VALUES (
				'Urlaub','".$uid."', 'Urlaub', '".date("Y-m-d",strtotime($akette[$i]))."',
				NULL,'".date("Y-m-d", strtotime($ekette[$i]))."',NULL,NULL,NULL,NULL,now(),'".$uid."','".$erreichbar."',NULL,NULL
				)";
		}
		$result = pg_query($conn, $qryins);
	}
	//Mail an Vorgesetzten
	$vorgesetzter = $ma->getVorgesetzte($uid);
	if($vorgesetzter)
	{
		$to='';
		foreach($ma->vorgesetzte as $vg)
		{
			if($to!='')
			{
				$to.=', '.$vg.'@'.DOMAIN;
			}
			else 
			{
				$to.=$vg.'@'.DOMAIN;
			}
		}
		//$to = 'ruhan@technikum-wien.at';
		$benutzer = new benutzer($conn);
		$benutzer->load($uid);
		$message = "Dies ist eine automatische Mail! \n".
				   "$benutzer->nachname $benutzer->vorname hat neuen Urlaub eingetragen:\n";
				   for($i=0;$i<count($akette);$i++)
				   {
				   	$message.="Von ".date("d.m.Y", strtotime($akette[$i]))." bis ".date("d.m.Y", strtotime($ekette[$i]))."\n";
				   }
				   $message.="\nSie können diesen unter folgender Adresse freigeben:\n".
				   APP_ROOT."cis/private/profile/urlaubsfreigabe.php?uid=$uid&year=".date("Y", strtotime($akette[0]));
		if(mail($to, 'Freigabeansuchen Urlaub', $message,'From: vilesci@'.DOMAIN))
		{
			$vgmail="<br><b>Freigabemail wurde an $to versandt!</b>";
		}
		else
		{
			$vgmail="<br><span class='error'>Fehler beim Senden des Freigabemails an $to!</span>";
		}
	}
	else
	{
		$vgmail="<br><span class='error'>Es konnte keine Freigabemail versendet werden, da kein Vorgesetzter eingetragen ist!</span>";
	}

}

//Eintragungen laden
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
	if($wmonat==0)
	{
		$mendev = cal_days_in_month(CAL_GREGORIAN, 12, $jahre[$wjahr]-1);
	}
	else
	{
		$mendev = cal_days_in_month(CAL_GREGORIAN, ($wmonat), $jahre[$wjahr]);
	}
	//$wvon=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]));
	//$wbis=date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $mende, $jahre[$wjahr]));
	$ttt=getdate(mktime(0, 0, 0, ($wmonat+1) , $mende, $jahre[$wjahr]));
	if($wmonat==0)
	{
		$wvon=date("Y-m-d",mktime(0, 0, 0, 12 , $mendev-($wotag-1), ($jahre[$wjahr])-1));
	}
	else 
	{
		$wvon=date("Y-m-d",mktime(0, 0, 0, ($wmonat) , $mendev-($wotag-1), ($jahre[$wjahr])));
	}
	if($wmonat==11)
	{
		$wbis=date("Y-m-d",mktime(0, 0, 0, 1 , (7-($ttt['wday']==0?7:$ttt['wday'])), $jahre[$wjahr]+1));
	}
	else 
	{
		$wbis=date("Y-m-d",mktime(0, 0, 0, ($wmonat+2) , (7-($ttt['wday']==0?7:$ttt['wday'])), $jahre[$wjahr]));
	}
	$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".$uid."' AND (vondatum<='".$wbis."' AND bisdatum>'".$wvon."') ";
	//echo "<br>"."db:".$qry;
	if($result = pg_query($conn, $qry))
	{
		while($row = pg_fetch_object($result))
		{
			//echo " ".$row->vondatum;
			//echo "-".$row->bisdatum;
			for($i=1;$i<=$mende+($wotag-1)+(7-($ttt['wday']==0?7:$ttt['wday']));$i++)
			{
				if(date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $i-$wotag+1, $jahre[$wjahr]))>=$row->vondatum
				&& date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $i-$wotag+1, $jahre[$wjahr]))<=$row->bisdatum)
				{
					if($row->freigabevon!='' || $row->bisdatum<date("Y-m-d",time()))
					{
						$hgfarbe[$i]='green';
					}
					else
					{
						$hgfarbe[$i]='lime';
					}
					$datensatz[$i]=$row->zeitsperre_id;
					$freigabevon[$i]=$row->freigabevon;
					$freigabeamum[$i]=$row->freigabeamum;
					$vertretung_uid[$i]=$row->vertretung_uid;
					$erreichbarkeit_kurzbz[$i]=$row->erreichbarkeit_kurzbz;
				}
				else
				{
					if($hgfarbe[$i]!='lime' && $hgfarbe[$i]!='green')
					{

						$hgfarbe[$i]='white';
						$datensatz[$i]=0;
						$freigabevon[$i]=$row->freigabevon;
						$freigabeamum[$i]=$row->freigabeamum;
						$vertretung_uid[$i]=$row->vertretung_uid;
						$erreichbarkeit_kurzbz[$i]=$row->erreichbarkeit_kurzbz;
					}
				}
			}
			for($i=$mende+$wotag+(7-($ttt['wday']==0?7:$ttt['wday']));$i<44;$i++)
			{
				$hgfarbe[$i]='white';
				$datensatz[$i]=0;
				$freigabevon[$i]=$row->freigabevon;
				$freigabeamum[$i]=$row->freigabeamum;
				$vertretung_uid[$i]=$row->vertretung_uid;
				$erreichbarkeit_kurzbz[$i]=$row->erreichbarkeit_kurzbz;
			}
		}
	}
}

$PHP_SELF = $_SERVER['PHP_SELF'];

$datum_obj = new datum();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd"><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
<script src="../../include/js/tablesort/table.js" type="text/javascript"></script>
<script language="Javascript">
function conf_del()
{
	return confirm('Wollen Sie diesen Eintrag wirklich löschen?');
}

function checkval()
{
	if(document.getElementById('vertretung_uid').value=='')
	{
		alert('Bitte zuerst eine Vertretung auswählen');
		return false;
	}
	else
		return true;
}
</script>
<style type="text/css">
a:link { text-decoration:none; font-weight:bold; color:blue; }
a:visited { text-decoration:none; font-weight:bold; color:blue; }
</style>
<title>Urlaubstool</title>
</head>
<body>
<?php
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

	$jahr=date('Y');
	if (date('M')>8)
	{
		$datum_beginn_iso=$jahr.'-09-01';
		$datum_beginn='1.Sept.'.$jahr;
		$datum_ende_iso=($jahr+1).'-08-31';
		$datum_ende='31.Aug.'.($jahr+1);
		$geschaeftsjahr=$jahr.'/'.($jahr+1);
	}
	else
	{
		$datum_beginn_iso=($jahr-1).'-09-01';
		$datum_beginn='1.Sept.'.($jahr-1);
		$datum_ende_iso=$jahr.'-08-31';
		$datum_ende='31.Aug.'.$jahr;
		$geschaeftsjahr=($jahr-1).'/'.$jahr;
	}

	//Urlaub berechnen
	$gebuchterurlaub=0;
	$qry = "SELECT sum(bisdatum-vondatum+1) as anzahltage FROM campus.tbl_zeitsperre
				WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='$uid' AND
				(
					vondatum>='$datum_beginn_iso' AND bisdatum<='$datum_ende_iso'
				)";
	$result = pg_query($conn, $qry);
	$row = pg_fetch_object($result);
	$gebuchterurlaub = $row->anzahltage;
	if($gebuchterurlaub=='')
		$gebuchterurlaub=0;

$content_resturlaub.="<table><tr><td   nowrap><h3>Urlaub im Gesch&auml;ftsjahr $geschaeftsjahr</h3></td><td></td><td><a href='../../cisdocs/AblaufUrlaubserfassung.pdf'> [AblaufUrlaubserfassung.pdf] </a></td></tr>";
$content_resturlaub.="<tr><td nowrap>Anspruch</td><td align='right'  nowrap>$anspruch Tage</td><td class='grey'   nowrap>&nbsp;&nbsp;&nbsp( j&auml;hrlich )</td></tr>";
$content_resturlaub.="<tr><td nowrap>+ Resturlaub</td><td align='right'  nowrap>$resturlaubstage Tage</td><td class='grey'   nowrap>&nbsp;&nbsp;&nbsp;( Stichtag: $datum_beginn )</td>";
$content_resturlaub.="<tr><td nowrap>- aktuell gebuchter Urlaub&nbsp;</td><td align='right'  nowrap>$gebuchterurlaub Tage</td><td class='grey'  nowrap>&nbsp;&nbsp;&nbsp;( $datum_beginn - $datum_ende )</td>";
$content_resturlaub .="<td><button type='button' name='hilfe' value='Hilfe' onclick='alert(\"Anspruch: Anzahl der Urlaubstage, auf die in diesem Geschäftsjahr (1.9. bis 31.8) ein Anrecht ensteht. \\nResturlaub: Anzahl der Urlaubstage, aus vergangenen Geschäftsjahren, die noch nicht verbraucht wurden. \\naktuell gebuchter Urlaub: Anzahl aller eingetragenen Urlaubstage. \\nAchtung: Als Urlaubstag gelten ALLE Tage zwischen von-Datum und bis-Datum d.h. auch alle Wochenenden, Feiertage und arbeitsfreie Tage. Beispiel: Ein Kurzurlaub beginnt mit einem Donnerstag und endet am darauffolgenden Dienstag, so wird zuerst eine Eintragung mit dem Datum des Donnerstags im von-Feld und dem Datum des letzten Urlaubstag vor dem Wochenende, meistens der Freitag, eingegeben. Danach wird eine Eintagung des zweiten Teils, von Montag bis Dienstag vorgenommen.\\naktueller Stand: Die zur Zeit noch verfügbaren Urlaubstage.\");'>Hilfe</button></td></tr>";
$content_resturlaub.="<tr><td style='border-top: 1px solid black;'  nowrap>aktueller Stand</td><td style='border-top: 1px solid black;' align='right' nowrap>".($anspruch+$resturlaubstage-$gebuchterurlaub)." Tage</td><td class='grey'  nowrap>&nbsp;&nbsp;&nbsp;( Stichtag: $datum_ende )</td></tr>";
$content_resturlaub.="</table>";

//Formular Auswahl Monat und Jahr für Kalender
echo '<table width="95%" align="center">';
echo "<td class='tdvertical' align='left' colspan='2'>$content_resturlaub</td>";
echo '</td></tr>';
echo '<tr><td>';
$content= '<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
$content.='<INPUT name="links" type="image" src="../../../skin/images/left.gif" alt="links">';
$content.='<SELECT name="wmonat">';
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


$content.='<INPUT name="rechts" type="image" src="../../../skin/images/right.gif" alt="rechts">';
$content.='&nbsp;<SELECT name="wjahr">';
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
$content.="&nbsp;<INPUT type='submit' name='ok' value='OK'>";
$content.='</td></form>';
$content.='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
$content.= "<td align='center' nowrap><SELECT name='vertretung_uid' id='vertretung_uid'>";
//dropdown fuer vertretung
$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";

$content.= "<OPTION value=''>-- Vertretung --</OPTION>\n";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($vertretung == $row->uid)
		{
			$content.= "<OPTION value='$row->uid' selected>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		}
		else
		{
			$content.= "<OPTION value='$row->uid'>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		}
	}
}
$content.= '</SELECT>';
$content.= "&nbsp;<SELECT name='erreichbar' id='erreichbarkeit_kurzbz'>";
//dropdown fuer vertretung
$qry = "SELECT * FROM campus.tbl_erreichbarkeit ORDER BY erreichbarkeit_kurzbz";

$content.= "<OPTION value=''>-- Erreichbarkeit --</OPTION>\n";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($erreichbar == $row->erreichbarkeit_kurzbz)
		{
			$content.= "<OPTION value='$row->erreichbarkeit_kurzbz' selected>$row->beschreibung</OPTION>\n";
		}
		else
		{
			$content.= "<OPTION value='$row->erreichbarkeit_kurzbz'>$row->beschreibung</OPTION>\n";
		}
	}
}
$content.= '</SELECT>';
$content.='</td>';

//Tage
$mbeginn=mktime(0, 0, 0, ($wmonat+1) , 1, $jahre[$wjahr]);
$ttt=getdate($mbeginn);
$wotag="$ttt[wday]";
if ($wotag==0)
{
	$wotag=7;
}

$mende = cal_days_in_month(CAL_GREGORIAN, ($wmonat+1), $jahre[$wjahr]);
if($wmonat==0)
{
	$mendev = cal_days_in_month(CAL_GREGORIAN, 12, $jahre[$wjahr]-1);
}
else
{
	$mendev = cal_days_in_month(CAL_GREGORIAN, ($wmonat), $jahre[$wjahr]);
}
$ttt=getdate(mktime(0, 0, 0, ($wmonat+1) , $mende, $jahre[$wjahr]));
//echo "monatsende:".$mende;
for($i=1;$i<43;$i++)
{
	if($i>=$wotag && $zaehl<=$mende)
	{
		$tage[$i]=$zaehl;
		$zaehl++;
	}
	elseif ($i<$wotag)
	{
		if($wmonat==0)
		{
			$tage[$i]=date("d.m.Y", mktime(0, 0, 0, 12 , $mendev+$i-($wotag-1), $jahre[$wjahr]-1));
		}
		else 
		{
			$tage[$i]=date("d.m.Y", mktime(0, 0, 0, ($wmonat) , $mendev+$i-($wotag-1), $jahre[$wjahr]));
		}
	}
	elseif ($i>$mende && $i<=$mende+($wotag-1)+(7-($ttt['wday']==0?7:$ttt['wday'])))
	{
		if($wmonat==11)
		{
			$tage[$i]=date("d.m.Y", mktime(0, 0, 0, 1 , $i-$mende-$wotag+1, $jahre[$wjahr+1]));
		}
		else 
		{
			$tage[$i]=date("d.m.Y", mktime(0, 0, 0, ($wmonat+2) , $i-$mende-$wotag+1, $jahre[$wjahr]));
		}
	}
	else 
	{
		$tage[$i]='';	
	}
}

$content.='<td>';
$content.='<input type="submit" name="speichern" value="Eintragungen speichern">';
$content.='<input type="hidden" name="wmonat" value="'.$wmonat.'">';
$content.='<input type="hidden" name="wjahr" value="'.$wjahr.'">';
$content.='</td></tr>';
$content.='</table>';
$content.='<table border=1 width="95%" align="center">';

$content.='<th width="14%">Montag</th><th width="14%">Dienstag</th><th width="14%">Mittwoch</th><th width="15%">Donnerstag</th><th width="14%">Freitag</th><th width="14%">Samstag</th><th width="14%">Sonntag</th>';
for ($i=0;$i<6;$i++)
{
	$content.='<tr height="50" style="font-family:Arial,sans-serif; font-size:34px; color:blue">';
	for ($j=1;$j<8;$j++)
	{
		if(strlen(stristr($tage[$j+7*$i],"."))>0)
		{
			$content.='<td align="center" valign="center" style="font-size:16px; color:grey;background-color: '.$hgfarbe[$j+7*$i].'">';
		}
		else 
		{
			$content.='<td align="center" valign="center" style="background-color: '.$hgfarbe[$j+7*$i].'">';
		}
		if($tage[$j+7*$i]!='')
		{
			if($hgfarbe[$j+7*$i]=='lime')
			{
				$content.='<b title="Vertretung: '.$vertretung_uid[$j+7*$i].' - erreichbar: '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';;
				$k=$j+7*$i;
				$content.="<a href='$PHP_SELF?wmonat=$wmonat&wjahr=$wjahr&delete=$datensatz[$k]' onclick='return conf_del()'>";
				$content.='<img src="../../../skin/images/DeleteIcon.png" alt="loeschen" title="Eintragung löschen"></a></td>';
			}
			elseif($hgfarbe[$j+7*$i]=='white')
			{
				$content.='<b>'.$tage[$j+7*$i].'</b><br>';
				if(strlen(stristr($tage[$j+7*$i],"."))>0)
				{
					$content.='<input type="checkbox" name="wtag[]" value="'.date("Y-m-d",mktime(0, 0, 0, substr($tage[$j+7*$i],3,2) , substr($tage[$j+7*$i],0,2), substr($tage[$j+7*$i],6,4))).'"></td>';
				}
				else 
				{
					$content.='<input type="checkbox" name="wtag[]" value="'.date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $tage[$j+7*$i], $jahre[$wjahr])).'"></td>';
				}
			}
			else
			{
				$content.='<b title="Vertretung: '.$vertretung_uid[$j+7*$i].' - erreichbar: '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';
				if(isset($freigabeamum[$j+7*$i]))
				{
					$content.='<img src="../../../skin/images/person.gif" alt="freigegeben" title="Freigegeben durch '.$freigabevon[$j+7*$i].' am '.date("d-m-Y",strtotime($freigabeamum[$j+7*$i])).'"></td>';
				}
				else
				{
					$content.='<img src="../../../skin/images/person.gif" alt="freigegeben" title="Freigegeben durch: '.$freigabevon[$j+7*$i].'"></td>';
				}
			}
		}
		else
		{
			$content.='<b>&nbsp;</b><br>';
		}
	}
	$content.='</tr>';
}
$content.='</table></form>';
echo $content;
echo "<b style='font-family:Arial,sans-serif; font-size:12px; color:red'>".$vgmail."</b>";
?>
</body>
</html>