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

//Bereits freigegebenen Eintrag löschen
//Eintragung löschen
if((isset($_GET['delete'])  && isset($_GET['informSupervisor'])) || (isset($_POST['delete']) && isset($_POST['informSupervisor'])))
{
    $zeitsperre = new zeitsperre();
    $zeitsperre->load($_GET['delete']);

    $vondatum = $zeitsperre->getVonDatum();
    $bisdatum = $zeitsperre->getBisDatum();

    if(!$zeitsperre->delete($_GET['delete']))
        echo $zeitsperre->errormsg;

        //Mail an Vorgesetzten
        $prsn = new person();

        $vorgesetzter = $ma->getVorgesetzte($uid);
        if($vorgesetzter)
        {
            $to='';
            $fullName ='';
            foreach($ma->vorgesetzte as $vg)
            {
                if($to!='')
                {
                    $to.=', '.$vg.'@'.DOMAIN;
                    $name = $prsn->getFullNameFromBenutzer($vg);
                    $fullName.=', '.$name;
                }
                else
                {
                    $to.=$vg.'@'.DOMAIN;
                    $name = $prsn->getFullNameFromBenutzer($vg);
                    $fullName.=$name;
                }
            }

        $benutzer = new benutzer();
        $benutzer->load($uid);
        $message = $p->t('urlaubstool/diesIstEineAutomatischeMail')."\n".
            $p->t('urlaubstool/xHatUrlaubGeloescht',array($benutzer->nachname,$benutzer->vorname)).":\n";
        $message.= $p->t('urlaubstool/von')." ".date("d.m.Y", strtotime($vondatum))." ".$p->t('urlaubstool/bis')." ".date("d.m.Y", strtotime($bisdatum))."\n";


        $mail = new mail($to, 'vilesci@'.DOMAIN,$p->t('urlaubstool/freigegebenerUrlaubGeloescht'), $message);
        if($mail->send())
        {
            $vgmail="<span style='color:green;'>".$p->t('urlaubstool/VorgesetzteInformiert',array($fullName))."</span>";
        }
        else
        {
            $vgmail="<br><span class='error'>".$p->t('urlaubstool/fehlerBeimSendenAufgetreten',array($fullName))."!</span>";
        }
    }
    else
    {
        $vgmail="<br><span class='error'>".$p->t('urlaubstool/konnteKeinFreigabemailVersendetWerden')."</span>";
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
	$vertretung = $_GET['vertretung_uid'];

	$bn = new benutzer();
	if($vertretung != '' && !$bn->load($vertretung))
	{
		$vgmail.='<br><span class="error">'.$p->t('zeitsperre/vertretungNichtKorrekt').'</span>';
		$error = true;
	}
	else
	{
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
			{
				$error = true;
				echo $zeitsperre->errormsg;
			}

		}
		if(!$error)
		{
			//Mail an Vorgesetzten
            $prsn = new person();

			$vorgesetzter = $ma->getVorgesetzte($uid);
			if($vorgesetzter)
			{
				$to='';
				$fullName ='';
				foreach($ma->vorgesetzte as $vg)
				{
					if($to!='')
					{
						$to.=', '.$vg.'@'.DOMAIN;
						$name = $prsn->getFullNameFromBenutzer($vg);
						$fullName.=', '.$name;
					}
					else
					{
						$to.=$vg.'@'.DOMAIN;
						$name = $prsn->getFullNameFromBenutzer($vg);
						$fullName.=$name;
					}
				}

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
					$vgmail="<span style='color:green;'>".$p->t('urlaubstool/freigabemailWurdeVersandt',array($fullName))."</span>";
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

			if($vertretung=='')
			{
				$vtmail="<br><span>".$p->t('urlaubstool/keineVertretungEingetragen')."</span>";
			}
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
	$qry="SELECT * FROM campus.tbl_zeitsperre
		WHERE zeitsperretyp_kurzbz='Urlaub'
		 AND mitarbeiter_uid=".$db->db_add_param($uid)."
		 AND (vondatum<=".$db->db_add_param($wbis)."
		 AND bisdatum>".$db->db_add_param($wvon).") ";

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
					if($row->freigabevon!='' && $row->vondatum<=date("Y-m-d",time()))
					{
						$hgfarbe[$i]='#bbb';
					}
					elseif ($row->freigabevon!=''  && $row->vondatum>date("Y-m-d",time()))
                    {
						$hgfarbe[$i]='#CDDDEE';
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
					if($hgfarbe[$i]!='#FFFC7F' && $hgfarbe[$i]!='#bbb' && $hgfarbe[$i]!='#CDDDEE')
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
?><!DOCTYPE HTML>
<html>
	<head>
		<meta charset="UTF-8">
		<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
		<link rel="stylesheet" href="../../../skin/jquery-ui-1.9.2.custom.min.css" type="text/css">
		<script src="../../../include/js/tablesort/table.js" type="text/javascript"></script>
		<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
		<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
		<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
		<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<?php
// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
	{
		echo '
		<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
	}
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
		<script>
		$( document ).ready(function()
		{
			if(typeof addon  !== \'undefined\')
			{
				for(i in addon)
				{
					addon[i].init("cis/private/profile/urlaubstool.php", {uid:\''.$uid.'\'});
				}
			}
		});
		</script>';
?>
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

//Formular Auswahl Monat und Jahr für Kalender
echo '
<table width="95%" align="left">
	<tr>
		<td class="tdvertical" align="left" colspan="2"><div id="resturlaub"></div></td>
		<td style="vertical-align:top; width: 20%;">
			<table cellspacing="0" cellpadding="0">
				<tr>
					<td class="menubox" height="10px">
					<p><a href="zeitsperre_resturlaub.php">'.$p->t("urlaubstool/meineZeitsperren").'</a></p>';

if ($p->t("dms_link/handbuchUrlaubsverwaltung")!='')
{
	echo '
	<p>
		<a href="../../../cms/dms.php?id='.$p->t("dms_link/handbuchUrlaubsverwaltung").'">
		'.$p->t("urlaubstool/handbuchUrlaubserfassung").'</a>
	</p>';
}

echo '
		<p>
			<a href="#" onclick="alert(\''.$p->t('urlaubstool/anspruchAnzahlDerUrlaubstage').'\');">
				'.$p->t("urlaubstool/hilfe").'
			</a>
		</p>
		</td>
	</tr>
	</table>
</td>
</tr>
<tr>
	<td colspan="3">'.$vgmail.' '.$vtmail.'</td>
</tr>
<tr>
	<td nowrap>
	<form  action="'.$_SERVER['PHP_SELF'].'" method="GET">
	<INPUT name="links" type="image" src="../../../skin/images/left_lvplan.png"
		style="vertical-align: middle;" alt="links">&nbsp;
<SELECT name="wmonat">';

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
	echo "<option value='$i' $selected>".$monatsname[$lang->index][$i]."</option>";
}
echo "</SELECT>\n";

echo '&nbsp;<INPUT name="rechts" type="image" src="../../../skin/images/right_lvplan.png" style="vertical-align: middle;" alt="rechts">';
echo '&nbsp;<SELECT name="wjahr">';
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
	echo "<option value='$i' $selected>$jahre[$i]</option>";
}
echo '</SELECT>
	<INPUT type="submit" name="ok" value="'.$p->t('urlaubstool/ok').'">
</form>
</td>
<td colspan="2">
	<form action="'.$_SERVER['PHP_SELF'].'" method="GET">
	'.$p->t('urlaubstool/vertretung').':
	<input type="text" id="vertretung" placeholder="'.$p->t('lvplan/nameEingeben').'"
		name="vertretung_uid" value="'.$vertretung.'">
<SELECT name="erreichbar" id="erreichbarkeit_kurzbz">';

//dropdown fuer Erreichbarkeit
$qry = "SELECT * FROM campus.tbl_erreichbarkeit ORDER BY erreichbarkeit_kurzbz";

echo  "<OPTION value=''>-- ".$p->t('urlaubstool/erreichbarkeit')." --</OPTION>\n";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($erreichbar == $row->erreichbarkeit_kurzbz)
		{
			echo  "<OPTION value='$row->erreichbarkeit_kurzbz' selected>$row->beschreibung</OPTION>\n";
		}
		else
		{
			echo  "<OPTION value='$row->erreichbarkeit_kurzbz'>$row->beschreibung</OPTION>\n";
		}
	}
}
echo  '</SELECT>';

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

echo '	<input type="submit" name="speichern" value="'.$p->t('urlaubstool/eintragungenSpeichern').'">
		<input type="hidden" name="wmonat" value="'.$wmonat.'">
		<input type="hidden" name="wjahr" value="'.$wjahr.'">
	</td>
	</tr>
</table>
<table border=0 width="95%" align="left" class="urlaube">
	<tr>';

for($i=1;$i<=7;$i++)
	echo "\n".'<th style="width:14%; background-color: #A5AFB6">'.$tagbez[$lang->index][$i].'</th>';

echo '</tr>';
for ($i=0;$i<6;$i++)
{
	echo "\n".'<tr height="50" style="font-family:Arial,sans-serif; font-size:30px; color:black">';
	for ($j=1;$j<8;$j++)
	{
		echo "\n";
		if(strlen(stristr($tage[$j+7*$i],"."))>0)
		{
			if($j%6==0 || $j==7)
			{
				echo '<td align="center" valign="center" style="font-size:16px; color:grey; background-color:#A5AFB6">';
			}
			else
			{
				echo '<td align="center" valign="center" style="font-size:16px; color:grey; background-color: ' . $hgfarbe[$j + 7 * $i] . '">';
			}
		}
		else
		{
			if($j%6==0 || $j==7)
			{
				echo '<td align="center" valign="center" style="font-size:; color:; background-color:#A5AFB6">';
			}
			else
			{
				echo '<td align="center" valign="center" style="background-color: ' . $hgfarbe[$j + 7 * $i] . '">';
			}
		}
		if($tage[$j+7*$i]!='')
		{
			if($hgfarbe[$j+7*$i]=='#FFFC7F' )//|| $hgfarbe[$j+7*$i]=='#CDDDEE')
			{
				echo '<b title='.$p->t('urlaubstool/vertretung').': '.$vertretung_uid[$j+7*$i].' - '.$p->t('urlaubstool/erreichbar').': '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';;
				$k=$j+7*$i;
				echo "<a href='$PHP_SELF?wmonat=$wmonat&wjahr=$wjahr&delete=$datensatz[$k]' onclick='return conf_del()'>";
				echo '<img src="../../../skin/images/delete_x.png" alt="loeschen" title="'.$p->t('urlaubstool/eintragungLoeschen').'"></a></td>';
			}
			elseif($hgfarbe[$j+7*$i]=='#E9ECEE')
			{
				echo '<b>'.$tage[$j+7*$i].'</b><br>';
				if(strlen(stristr($tage[$j+7*$i],"."))>0)
				{
					echo '<input type="checkbox" name="wtag[]" 
					value="'.date("Y-m-d",mktime(0, 0, 0, substr($tage[$j+7*$i],3,2) , substr($tage[$j+7*$i],0,2), substr($tage[$j+7*$i],6,4))).'" 
					id="'.date("d.m.Y",mktime(0, 0, 0, substr($tage[$j+7*$i],3,2) , substr($tage[$j+7*$i],0,2), substr($tage[$j+7*$i],6,4))).'"></td>';
				}
				else
				{
					echo '<input type="checkbox" name="wtag[]" value="'.date("Y-m-d",mktime(0, 0, 0, ($wmonat+1) , $tage[$j+7*$i], $jahre[$wjahr])).'" 
                    id="'.date("d.m.Y",mktime(0, 0, 0, ($wmonat+1) , $tage[$j+7*$i], $jahre[$wjahr])).'"></td>';
				}
			}
			else
			{
				echo '<b title="'.$p->t('urlaubstool/vertretung').': '.$vertretung_uid[$j+7*$i].' - '.$p->t('urlaubstool/erreichbar').': '.$erreichbarkeit_kurzbz[$j+7*$i].'">'.$tage[$j+7*$i].'</b><br>';
				if(!isset($freigabeamum[$j+7*$i]) && !isset($freigabevon[$j+7*$i]))
				{
					echo '<img src="../../../skin/images/flag-red.png" alt="nicht freigegeben" title="'.$p->t('urlaubstool/freigabeFehlt').'"></td>';
				}
				elseif(isset($freigabeamum[$j+7*$i]))
				{
                    echo '<img src="../../../skin/images/flag-green.png" alt="freigegeben" title="'.$p->t('urlaubstool/freigegebenDurch', array($freigabevon[$j+7*$i])).': '.$freigabevon[$j+7*$i].'"><span> </span>';
					if($hgfarbe[$j+7*$i]=='#CDDDEE')
					{
						$k=$j+7*$i;
						echo "<a href='$PHP_SELF?wmonat=$wmonat&wjahr=$wjahr&delete=$datensatz[$k]&informSupervisor=True' onclick='return conf_del()'>";
                        echo '<img src="../../../skin/images/delete_x.png" alt="loeschen" title="'.$p->t('urlaubstool/eintragungLoeschen').'"></a></td>';
					}
				}
				else
				{
					echo '<img src="../../../skin/images/flag-green.png" alt="freigegeben" title="'.$p->t('urlaubstool/freigegebenDurch', array($freigabevon[$j+7*$i])).': '.$freigabevon[$j+7*$i].'"></td>';
				}
			}
		}
		else
		{
			echo '<b>&nbsp;</b><br>';
		}
	}
	echo '</tr>';
}
echo '</table></form>';


?>
</body>
</html>
