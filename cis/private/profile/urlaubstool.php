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
/**
 * Seite zum Eintragen von Urlaubstagen
 */
require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/resturlaub.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/globals.inc.php');
require_once('../../../include/sprache.class.php');


$sprache = getSprache(); 
$lang = new sprache(); 
$lang->load($sprache);
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$content_resturlaub = '';
$content = '';
$resturlaubstage = '0';
$mehrarbeitsstunden = '0';
$anspruch = '25';
$zaehl=1;
$tage=array();			//Array Tage für Kalenderanzeige
$hgfarbe=array_fill(0,44,'#E9ECEE'); 	//Array mit Hintegrundfarben der Kalenderfelder
$datensatz=array_fill(0,44,0);
$freigabevon=array();
$freigabeamum=array();
$vertretung_uid=array();
$erreichbarkeit_kurzbz=array();

/* Monatsnamenarray kommt aus globals.inc.php */
//$monatsname = array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");

$jahre = array();			//Array Jahreszahlen für Auswahl (immer aktuelles Jahr und die 4 nächsten Jahre)
$akette=array_fill(0,1,0);
$ekette=array_fill(0,1,0);
$links='';
$rechts='';
$tag=array();
$vertretung='';
$erreichbar='';
$vgmail='';
$vtmail='';
$spmonat=array();
$hgchange=false;
$wvon='';
$wbis='';
$datensatz='';
$t=getdate();
$uid = get_uid();
$taste=0;

$ma= new mitarbeiter();
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
			if(!isset($hgfarbe[$i]) || $hgfarbe[$i]!='#FFFC7F')
				$hgfarbe[$i]='#E9ECEE';
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
	$zeitsperre = new zeitsperre();
	if(!$zeitsperre->delete($_GET['delete']))
		echo $zeitsperre->errormsg;
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
	$akette[0]=$wtag[0];
	$ekette[0]=$wtag[0];
	for($i=1,$j=0;$i<count($wtag);$i++)
	{
		//ketten bilden
		if($wtag[$i]==date("Y-m-d",strtotime("+1 Day",strtotime($wtag[$i-1]))))
		{
			$ekette[$j]=$wtag[$i];
		}
		else
		{
			$j++;
			$akette[$j]=$wtag[$i];
			$ekette[$j]=$wtag[$i];
		}

	}
	
	//Pruefen ob bereits ein Urlaub in den markierten Bereichen vorhanden ist und ggf Abbrechen
	//Das Problem sollte nur beim manuellen Refresh der Seite auftreten 
	$error=false;
	for($i=0;$i<count($akette);$i++)
	{
		$zeitsperre = new zeitsperre();
		
		if($zeitsperre->UrlaubEingetragen($uid, $akette[$i], $ekette[$i]))
		{
			$vgmail.='<br><span class="error">'.$p->t('zeitsperre/urlaubBereitsEingetragen').'</span>';
			$error=true;
			break;
		}
	}
	
	if(!$error)
	{
		for($i=0;$i<count($akette);$i++)
		{
			$zeitsperre = new zeitsperre();
			
			$zeitsperre->new = true;
			$zeitsperre->zeitsperretyp_kurzbz='Urlaub';
			$zeitsperre->mitarbeiter_uid=$uid;
			$zeitsperre->bezeichnung='Urlaub';
			$zeitsperre->vondatum=$akette[$i];
			$zeitsperre->vonstunde='';
			$zeitsperre->bisdatum=$ekette[$i];
			$zeitsperre->bisstunde='';
			$zeitsperre->vertretung_uid=$vertretung;
			$zeitsperre->updateamum='';
			$zeitsperre->updatevon='';
			$zeitsperre->insertamum=date('Y-m-d H:i:s');
			$zeitsperre->insertvon=$uid;
			$zeitsperre->erreichbarkeit=$erreichbar;
			$zeitsperre->freigabeamum='';
			$zeitsperre->freigabevon='';
	
			if(!$zeitsperre->save())
				echo $zeitsperre->errormsg;
			
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
			//$to = 'oesi@technikum-wien.at';
			$benutzer = new benutzer();
			$benutzer->load($uid);
			$message = $p->t('urlaubstool/diesIstEineAutomatischeMail')."\n".
					   $p->t('urlaubstool/xHatNeuenUrlaubEingetragen',array($benutzer->nachname,$benutzer->vorname)).":\n";
					   
			for($i=0;$i<count($akette);$i++)
			{
				$message.= $p->t('urlaubstool/von')." ".date("d.m.Y", strtotime($akette[$i]))." ".$p->t('urlaubstool/bis')." ".date("d.m.Y", strtotime($ekette[$i]))."\n";
			}
			
			//Ab September wird das neue Jahr uebergeben
			if(date("m",strtotime($akette[0]))>=9)
		   		$jahr = date("Y", strtotime($akette[0]))+1;
		   	else 
		   		$jahr = date("Y", strtotime($akette[0]));
		   
			$message.="\n".$p->t('urlaubstool/sieKoennenDiesenUnterFolgenderAdresseFreigeben').":\n".
			APP_ROOT."cis/private/profile/urlaubsfreigabe.php?uid=$uid&year=".$jahr;
			
			$mail = new mail($to, 'vilesci@'.DOMAIN,$p->t('urlaubstool/freigabeansuchenUrlaub'), $message);
			if($mail->send())
			{
				$vgmail="<span style='color:green;'>".$p->t('urlaubstool/freigabemailWurdeVersandt',array($to))."</span>";
			}
			else
			{
				$vgmail="<br><span class='error'>".$p->t('urlaubstool/fehlerBeimSendenAufgetreten',array($to))."!</span>";
			}
		}
		else
		{
			$vgmail="<br><span class='error'>".$p->t('urlaubstool/konnteKeinFreigabemailVersendetWerden')."</span>";
		}
		//Mail an Vertretung. Wird derzeit nicht gewuenscht.
		/*
		if($vertretung!='')
		{
			$to = $vertretung.'@'.DOMAIN;
			
			$benutzer = new benutzer();
			$benutzer->load($uid);
			$datumsbereich = '';
			
			for($i=0;$i<count($akette);$i++)
			{
				$datumsbereich.="Von ".date("d.m.Y", strtotime($akette[$i]))." bis ".date("d.m.Y", strtotime($ekette[$i]))."\n";
			}
			
			$message = $p->t('urlaubstool/mailtextVertretung', array ($benutzer->nachname,$benutzer->vorname,$datumsbereich));
			//"Dies ist eine automatische Mail. \n".
			//		   "$benutzer->nachname $benutzer->vorname hat neuen Urlaub eingetragen und sie wurden als Vertretung angegeben:\n";
					   
			
			$mail = new mail($to, 'vilesci@'.DOMAIN,'Urlaubsvertretung für '.$benutzer->nachname.' '.$benutzer->vorname.'', $message);
			if($mail->send())
			{
				$vtmail="".$p->t('urlaubstool/vertretungsmailWurdeVersandt',$to)."!";
			}
			else
			{
				$vtmail="<br><span class='error'>".$p->t('urlaubstool/fehlerBeimSendenAufgetreten',$to)."!</span>";
			}
		}
		else
		{
			$vtmail="<br><span>".$p->t('urlaubstool/keineVertretungEingetragen')."</span>";
		}*/
		if($vertretung=='')
		{
			$vtmail="<br><span>".$p->t('urlaubstool/keineVertretungEingetragen')."</span>";
		}
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
	$qry="SELECT * FROM campus.tbl_zeitsperre WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='".addslashes($uid)."' AND (vondatum<='".addslashes($wbis)."' AND bisdatum>'".addslashes($wvon)."') ";
	//echo "<br>"."db:".$qry;
	if($result = $db->db_query($qry))
	{
		while($row = $db->db_fetch_object($result))
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
						$hgfarbe[$i]='#bbb';
					}
					else
					{
						$hgfarbe[$i]='#FFFC7F';
					}
					$datensatz[$i]=$row->zeitsperre_id;
					$freigabevon[$i]=$row->freigabevon;
					$freigabeamum[$i]=$row->freigabeamum;
					$vertretung_uid[$i]=$row->vertretung_uid;
					$erreichbarkeit_kurzbz[$i]=$row->erreichbarkeit_kurzbz;
				}
				else
				{
					if($hgfarbe[$i]!='#FFFC7F' && $hgfarbe[$i]!='#bbb')
					{

						$hgfarbe[$i]='#E9ECEE';
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
				$hgfarbe[$i]='#E9ECEE';
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
"http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link rel="stylesheet" href="../../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script src="../../../include/js/jquery1.9.min.js" type="text/javascript"></script>
		<script language="Javascript">
		function conf_del()
		{
			return confirm('<?php echo $p->t('urlaubstool/eintragWirklichLoeschen');?>');
		}
		
		function checkval()
		{
			if(document.getElementById('vertretung_uid').value=='')
			{
				alert('<?php echo $p->t('urlaubstool/zuerstVertretungAuswaehlen');?>');
				return false;
			}
			else
				return true;
		}
		$(document).ready(function() 
		{
			$("#vertretung").autocomplete({
			source: "urlaubstool_autocomplete.php?autocomplete=mitarbeiter",
			minLength:2,
			response: function(event, ui)
			{
				//Value und Label fuer die Anzeige setzen
				for(i in ui.content)
				{
					ui.content[i].value=ui.content[i].uid;
					ui.content[i].label=ui.content[i].vorname+" "+ui.content[i].nachname+" ("+ui.content[i].uid+")";
				}
			},
			select: function(event, ui)
			{
				//Ausgeaehlte Ressource zuweisen und Textfeld wieder leeren
				$("#vertretung").val(ui.item.uid);
			}
			});
		})
		</script>
		<style type="text/css">
		.urlaube th, .urlaube td, .urlaube
		{
			-moz-border-radius:10px;
			-khtml-border-radius:10px;
		}
		</style>
		<title><?php echo $p->t('urlaubstool/urlaubstool');?></title>
	</head>
<body>
<?php
	echo "<H1>".$p->t('urlaubstool/urlaubstool')." (".$uid.")</H1>";
	//Anzeige Resturlaubsberechnung
	echo '<table width="100%">';
	echo '<tr><td colspan=2>';
	$resturlaub = new resturlaub();

	if($resturlaub->load($uid))
	{
		$resturlaubstage = $resturlaub->resturlaubstage;
		$mehrarbeitsstunden = $resturlaub->mehrarbeitsstunden;
		$anspruch = $resturlaub->urlaubstageprojahr;
	}
	else 
	{
		$resturlaubstage=0;
		$mehrarbeitsstunden=0;
		// wenn mitarbeiter ist kein fixangestellter --> kein urlaubsanspruch
		$mitarbeiter_anspruch = new mitarbeiter(); 
		$mitarbeiter_anspruch->load($uid);
		if($mitarbeiter_anspruch->fixangestellt == true)
			$anspruch=25;
		else 
			$anspruch = 0; 
	}

	$jahr=date('Y');
	if (date('m')>8)
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
	$result = $db->db_query($qry);
	$row = $db->db_fetch_object($result);
	$gebuchterurlaub = $row->anzahltage;
	if($gebuchterurlaub=='')
		$gebuchterurlaub=0;

$content_resturlaub.="<table><tr><td   nowrap><h3>".$p->t('urlaubstool/urlaubImGeschaeftsjahr')." $geschaeftsjahr</h3></td><td></td></tr>";
$content_resturlaub.="<tr><td nowrap>".$p->t('urlaubstool/anspruch')."</td><td align='right'  nowrap>$anspruch ".$p->t('urlaubstool/tage')."</td><td class='grey'   nowrap>&nbsp;&nbsp;&nbsp( ".$p->t('urlaubstool/jaehrlich')." )</td></tr>";
$content_resturlaub.="<tr><td nowrap>+ ".$p->t('urlaubstool/resturlaub')."</td><td align='right'  nowrap>$resturlaubstage ".$p->t('urlaubstool/tage')."</td><td class='grey'   nowrap>&nbsp;&nbsp;&nbsp;( ".$p->t('urlaubstool/stichtag').": $datum_beginn )</td>";
$content_resturlaub.="<tr><td nowrap>- ".$p->t('urlaubstool/aktuellGebuchterUrlaub')."&nbsp;</td><td align='right'  nowrap>$gebuchterurlaub ".$p->t('urlaubstool/tage')."</td><td class='grey'  nowrap>&nbsp;&nbsp;&nbsp;( $datum_beginn - $datum_ende )</td>";
$content_resturlaub .="</tr>";
$content_resturlaub.="<tr><td style='border-top: 1px solid black;'  nowrap>".$p->t('urlaubstool/aktuellerStand')."</td><td style='border-top: 1px solid black;' align='right' nowrap>".($anspruch+$resturlaubstage-$gebuchterurlaub)." ".$p->t('urlaubstool/tage')."</td><td class='grey'  nowrap>&nbsp;&nbsp;&nbsp;( ".$p->t('urlaubstool/stichtag').": $datum_ende )</td></tr>";
$content_resturlaub.="</table>";

//Formular Auswahl Monat und Jahr für Kalender
echo '<table width="95%" align="left">';
echo "<td class='tdvertical' align='left' colspan='2'>$content_resturlaub</td>";
echo '</td>';
echo '<td style="vertical-align:top; width: 20%;"><table cellspacing="0" cellpadding="0"><tr>
<td class="menubox" height="10px">
<p><a href="zeitsperre_resturlaub.php">'.$p->t("urlaubstool/meineZeitsperren").'</a></p>';
if ($p->t("dms_link/handbuchUrlaubsverwaltung")!='')
{
	echo '<p><a href="../../../cms/dms.php?id='.$p->t("dms_link/handbuchUrlaubsverwaltung").'">'.$p->t("urlaubstool/handbuchUrlaubserfassung").'</a></p>';
}
echo '<p><a href="#" onclick="alert(\''.$p->t('urlaubstool/anspruchAnzahlDerUrlaubstage').'\');">'.$p->t("urlaubstool/hilfe").'</a></p>
</td></tr></table></td>';
echo '</tr>';
echo '<tr><td nowrap>';
$content= '<form  action="'.$_SERVER['PHP_SELF'].'" method="GET">';
$content.='<INPUT name="links" type="image" src="../../../skin/images/left_lvplan.png" style="vertical-align: middle;" alt="links">&nbsp;';
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
	$content.="<option value='$i' $selected>".$monatsname[$lang->index][$i]."</option>";
}
$content.='</SELECT>';


$content.='&nbsp;<INPUT name="rechts" type="image" src="../../../skin/images/right_lvplan.png" style="vertical-align: middle;" alt="rechts">';
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
$content.="&nbsp;<INPUT type='submit' name='ok' value='".$p->t('urlaubstool/ok')."'>&nbsp;&nbsp;";
$content.='</form></td>';
$content.='<form action="'.$_SERVER['PHP_SELF'].'" method="GET">';
$content.= "<td align='center' nowrap>";

$content.= $p->t('urlaubstool/vertretung').": <input type='text' id='vertretung' placeholder='".$p->t('lvplan/nameEingeben')."' name='vertretung_uid' value='".$vertretung."'>";
//dropdown fuer vertretung. Ersetzt durch AutoComplete
/*$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";

$content.= "<SELECT name='vertretung_uid' id='vertretung_uid'><OPTION value=''>-- ".$p->t('urlaubstool/vertretung')." --</OPTION>\n";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
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
$content.= '</SELECT>';*/
$content.= "&nbsp;<SELECT name='erreichbar' id='erreichbarkeit_kurzbz'>";
//dropdown fuer Erreichbarkeit
$qry = "SELECT * FROM campus.tbl_erreichbarkeit ORDER BY erreichbarkeit_kurzbz";

$content.= "<OPTION value=''>-- ".$p->t('urlaubstool/erreichbarkeit')." --</OPTION>\n";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
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
$content.='<input type="submit" name="speichern" value="'.$p->t('urlaubstool/eintragungenSpeichern').'">';
$content.='<input type="hidden" name="wmonat" value="'.$wmonat.'">';
$content.='<input type="hidden" name="wjahr" value="'.$wjahr.'">';
$content.='</td></tr><tr><td>&nbsp;</td></tr>';
$content.='</table>';
$content.='<table border=0 width="95%" align="left" class="urlaube">';

$content.='<th style="width:14%; height:20px; background-color: #A5AFB6">'.$tagbez[$lang->index][1].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][2].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][3].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][4].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][5].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][6].'</th><th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][7].'</th>';
for ($i=0;$i<6;$i++)
{
	$content.='<tr height="50" style="font-family:Arial,sans-serif; font-size:30px; color:black">';
	for ($j=1;$j<8;$j++)
	{
		if(strlen(stristr($tage[$j+7*$i],"."))>0)
		{
			$content.='<td align="center" valign="center" style="font-size:16px; color:grey; background-color: '.$hgfarbe[$j+7*$i].'">';
		}
		else 
		{
			$content.='<td align="center" valign="center" style="background-color: '.$hgfarbe[$j+7*$i].'">';
		}
		if($tage[$j+7*$i]!='')
		{
			if($hgfarbe[$j+7*$i]=='#FFFC7F')
			{
				$content.='<b title='.$p->t('urlaubstool/vertretung').': '.$vertretung_uid[$j+7*$i].' - '.$p->t('urlaubstool/erreichbar').': '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';;
				$k=$j+7*$i;
				$content.="<a href='$PHP_SELF?wmonat=$wmonat&wjahr=$wjahr&delete=$datensatz[$k]' onclick='return conf_del()'>";
				$content.='<img src="../../../skin/images/delete_x.png" alt="loeschen" title="'.$p->t('urlaubstool/eintragungLoeschen').'"></a></td>';
			}
			elseif($hgfarbe[$j+7*$i]=='#E9ECEE')
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
				$content.='<b title="'.$p->t('urlaubstool/vertretung').': '.$vertretung_uid[$j+7*$i].' - '.$p->t('urlaubstool/erreichbar').': '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';
				if(isset($freigabeamum[$j+7*$i]))
				{
					$content.='<img src="../../../skin/images/flag-green.png" alt="freigegeben" title="'.$p->t('urlaubstool/freigegebenDurchAm', array($freigabevon[$j+7*$i])).' '.date("d-m-Y",strtotime($freigabeamum[$j+7*$i])).'"></td>';
				}
				else
				{
					$content.='<img src="../../../skin/images/flag-green.png" alt="freigegeben" title="'.$p->t('urlaubstool/freigegebenDurch', array($freigabevon[$j+7*$i])).': '.$freigabevon[$j+7*$i].'"></td>';
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
echo "<table width='100%'><tr><td><br>".$vgmail;
echo "<br>".$vtmail."</td></tr></table>";
?>
</body>
</html>