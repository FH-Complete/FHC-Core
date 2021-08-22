<?php
/* Copyright (C) 2006 fhcomplete.org
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
// * @brief bietet die Moeglichkeit zur Anzeige und
// * Aenderung der Zeitwuensche und Zeitsperren

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/zeitsperre.class.php');
require_once('../../../include/datum.class.php');
require_once('../../../include/person.class.php');
require_once('../../../include/benutzer.class.php');
require_once('../../../include/mitarbeiter.class.php');
require_once('../../../include/mail.class.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../../../include/phrasen.class.php');
require_once('../../../include/zeitaufzeichnung.class.php');

$sprache = getSprache();
$p = new phrasen($sprache);

if (!$db = new basis_db())
	die($p->t('global/fehlerBeimOeffnenDerDatenbankverbindung'));

$uid = get_uid();

$PHP_SELF = $_SERVER['PHP_SELF'];

$typen_arr = array("Urlaub", "PflegeU", "ZA", "Krank", "DienstF", "DienstV");

if(isset($_GET['type']))
	$type=$_GET['type'];

//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeitsperren
//des uebergebenen Users anzeigen
if(isset($_GET['uid']))
{
	$rechte = new benutzerberechtigung();
	$rechte->getBerechtigungen($uid);
	if($rechte->isBerechtigt('admin'))
	{
		$uid = $_GET['uid'];
	}
	else
	{
		die($p->t('global/FuerDieseAktionBenoetigenSieAdministrationsrechte'));
	}
}
$datum_obj = new datum();
$ma= new mitarbeiter();

// definiert bis zu welchem Datum die Eintragung nicht mehr möglich ist
$zasperre = new zeitaufzeichnung();
if ($sperrdat = $zasperre->getEintragungGesperrtBisForUser($uid))
	$gesperrt_bis = $sperrdat;
else if (defined('CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS') && CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS != '')
	$gesperrt_bis = CIS_ZEITAUFZEICHNUNG_GESPERRT_BIS;
else
	$gesperrt_bis = '2015-08-31';

//echo $gesperrt_bis;

//Stundentabelleholen
if(! $result_stunde=$db->db_query("SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
	die($db->db_last_error());
$num_rows_stunde=$db->db_num_rows($result_stunde);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd"><html>
<head>
<title><?php echo $p->t('zeitsperre/zeitsperre');?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
<script type="text/javascript" src="../../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>
<script type="text/javascript" src="../../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="../../../vendor/components/jqueryui/jquery-ui.min.js"></script>
<script type="text/javascript" src="../../../include/js/jquery.ui.datepicker.translation.js"></script>
<script src="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.js" type="text/javascript" ></script>
<link href="../../../skin/jquery.css" rel="stylesheet" type="text/css"/>
<link href="../../../vendor/fgelinas/timepicker/jquery.ui.timepicker.css" rel="stylesheet" type="text/css"/>
<link href="../../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet"  type="text/css">
<?php
// ADDONS laden
$addon_obj = new addon();
$addon_obj->loadAddons();
foreach($addon_obj->result as $addon)
{
	if(file_exists('../../../addons/'.$addon->kurzbz.'/cis/init.js.php'))
		echo '<script type="application/x-javascript" src="../../../addons/'.$addon->kurzbz.'/cis/init.js.php" ></script>';
}

// Wenn Seite fertig geladen ist Addons aufrufen
echo '
<script>
let holiDays =[];
$( document ).ready(function()
{
	if(typeof addon  !== \'undefined\')
	{
		for(i in addon)
		{
			addon[i].init("cis/private/profile/zeitsperre_resturlaub.php", {uid:\''.$uid.'\', holiDays: holiDays});
		}
	}

	$( ".datepicker_datum" ).datepicker({
		 changeMonth: true,
		 changeYear: true,
		 dateFormat: "dd.mm.yy",
		 beforeShowDay: setHoliDays
		 });

	$( ".timepicker" ).timepicker({
			showPeriodLabels: false,
			hourText: "'.$p->t("global/stunde").'",
			minuteText: "'.$p->t("global/minute").'",
			hours: {starts: 7,ends: 22},
			rows: 4,
			});

});
// set holidays function which is configured in beforeShowDay
 function setHoliDays(date) {
   for (i = 0; i < holiDays.length; i++) {
     if (date.getFullYear() == holiDays[i][0]
    	  && date.getMonth() == holiDays[i][1] - 1
          && date.getDate() == holiDays[i][2]) {
        return [true, "holiday", ""];
     }
   }
  return [true, ""];
}
</script>';

?>
<style type="text/css">
	.dd_breit
	{
		width:460px;
	}

	.ui-datepicker td.holiday a, .ui-datepicker td.holiday a:hover
	{
		background: none #FFEBAF;
		border: 1px solid #BF5A0C;
	}
</style>
<script language="Javascript">
function conf_del()
{
	return confirm('<?php echo $p->t('global/warnungWirklichLoeschen');?>');
}

function checkval()
{
	if(document.getElementById('vertretung_uid').value=='')
	{
		alert('<?php echo $p->t('zeitsperre/bitteZuerstVertretungAuswaehlen');?>');
		return false;
	}
	else
		return true;
}
function berechnen()
{
	document.getElementById('summe').value = parseInt(document.getElementById('resturlaubstage').value)+parseInt(document.getElementById('anspruch').value);
}

function checkdatum()
{
	if(document.getElementById('vondatum').value.length<10)
	{
		alert('<?php echo $p->t('zeitsperre/vonDatumIstUngueltigNullenAngeben');?>');
		return false;
	}

	if(document.getElementById('bisdatum').value.length<10)
	{
		alert('<?php echo $p->t('zeitsperre/bisDatumIstUngueltigNullenAngeben');?>');
		return false;
	}

      var Datum, Tag, Monat,Jahr,vonDatum,bisDatum, diff;

	  Datum=document.getElementById('vondatum').value;
      Tag=Datum.substring(0,2);
      Monat=Datum.substring(3,5);
	  if (parseInt(Monat,10)<1 || parseInt(Monat,10)>12)
	  {
		alert('<?php echo $p->t('zeitsperre/vonDatumMonat');?>'+ document.getElementById('vondatum').value+ ' <?php echo $p->t('zeitsperre/istNichtRichtig');?>.');
		document.getElementById('vondatum').focus();
	  	return false;
	  }

      Jahr=Datum.substring(6,10);

	  vonDatum=Jahr+''+Monat+''+Tag;

	  Datum=document.getElementById('bisdatum').value;
      Tag=Datum.substring(0,2);
      Monat=Datum.substring(3,5);
	  if (parseInt(Monat,10)<1 || parseInt(Monat,10)>12)
	  {
		alert('<?php echo $p->t('zeitsperre/bisDatumMonat');?>'+ document.getElementById('bisdatum').value+ ' <?php echo $p->t('zeitsperre/istNichtRichtig');?>.');
		document.getElementById('bisdatum').focus();
	  	return false;
	  }

      Jahr=Datum.substring(6,10);

	  bisDatum=Jahr+''+Monat+''+Tag;

	  diff=bisDatum-vonDatum;

	  if (vonDatum>bisDatum)
	  {
		alert('<?php echo $p->t('zeitsperre/vonDatum');?> '+ document.getElementById('vondatum').value+ ' <?php echo $p->t('zeitsperre/istGroesserAlsBisDatum');?> '+document.getElementById('bisdatum').value);
		document.getElementById('vondatum').focus();
	  	return false;
	  }
      else if (diff>14)
      {
      	Check = confirm('<?php echo $p->t('zeitaufzeichnung/zeitraumAuffallendHoch');?>');
		document.getElementById('bisdatum').focus();
	      if (Check == false)
		      return false;
	      else
		      return true;
	  }

	return true;
}

function showHideBezeichnungDropDown()
{
	var dd = document.zeitsperre_form.zeitsperretyp_kurzbz;
	var sp = document.getElementById('dienstv_span');
	if (dd.options[dd.selectedIndex].value == 'DienstV')
	{
		var str = '<select name="bezeichnung" class="dd_breit">';
		str += '<option value="Eheschließung">a) Eigene Eheschließung</option>';
		str += '<option value="Geburt eigenes Kind">b) Geburt eines Kindes der Ehefrau/Lebensgefährtin</option>';
		str += '<option value="Heirat Kind/Geschwister">c) Eheschließung eines Kindes/eigener Geschwister</option>';
		str += '<option value="Eigene Sponsion/Promotion">d) Teilnahme an eigener Sponsion/Promotion</option>';
		str += '<option value="Lebensbedr. Erkrankung P/K/E">e) Lebensbedrohliche Erkrankung Partner/Kinder/Eltern</option>';
		str += '<option value="Ableben P/K/E">f) Ableben Partner/Kinder/Elternteil</option>';
		str += '<option value="Bestattung G/S/G">g) Teilnahme an Bestattung Geschwister/Schwiegereltern/eigener Großeltern</option>';
		str += '<option value="Wohnungswechsel">h) Wohnungswechsel in eigenen Haushalt</option>';
		str += '<option value="Bundesheer">i) Einberufung Bundesheer</option>';
		str += '</select>';

		sp.innerHTML = str;

	}
	else if (dd.options[dd.selectedIndex].value == 'DienstF')
	{
		sp.innerHTML = '<font color="red"><b>Dienstfreistellungen</b> nur in Absprache mit Personalservice eintragen!</font><br><input type="text" name="bezeichnung" maxlength="32" size="32" value="">';
	}
	else
	{

		sp.innerHTML = '<input type="text" name="bezeichnung" maxlength="32" size="32" value="">';
	}
	if (dd.options[dd.selectedIndex].value == 'Urlaub')
		document.getElementById('resturlaub').style.visibility = 'visible';
	else
		document.getElementById('resturlaub').style.visibility = 'hidden';

	showHideStudeDropDown()
}

function setBisDatum()
{
	document.zeitsperre_form.bisdatum.value = document.zeitsperre_form.vondatum.value;
}

function showHideStudeDropDown()
{
	var dd = document.zeitsperre_form.zeitsperretyp_kurzbz;

	if (dd.options[dd.selectedIndex].value == 'ZA'
	|| dd.options[dd.selectedIndex].value == 'Urlaub'
	|| dd.options[dd.selectedIndex].value == 'Krank'
	|| dd.options[dd.selectedIndex].value == 'DienstF'
	|| dd.options[dd.selectedIndex].value == 'DienstV')
	{
		document.getElementById('vonStd').style.visibility = 'hidden';
		document.getElementById('bisStd').style.visibility = 'hidden';
	}
	else
	{
		document.getElementById('vonStd').style.visibility = 'visible';
		document.getElementById('bisStd').style.visibility = 'visible';
	}
}

</script>
</head>

<body id="inhalt">
<div class="flexcroll" style="outline: none;">
<table id="inhalt">
  <tr>
    <td>
	<h1><?php echo $p->t('zeitsperre/zeitsperren');?></h1>
	<br>

<!-- ************* ZEITSPERREN *****************-->

<?php


//Zeitsperre Speichern
if(isset($_GET['type']) && ($_GET['type']=='edit_sperre' || $_GET['type']=='new_sperre'))
{
	$error=false;
	$error_msg='';


	//von-datum pruefen
	if(isset($_POST['vondatum']) && !$datum_obj->checkDatum($_POST['vondatum']))
	{
		$error=true;
		$error_msg .= $p->t('zeitsperre/vonDatumUngueltig').' ';
	}
	//bis-datum pruefen $datum_obj->formatDatum($_POST['bisdatum']
	if(isset($_POST['bisdatum']) && !$datum_obj->checkDatum($_POST['bisdatum']))
	{
		$error=true;
		$error_msg .= $p->t('zeitsperre/bisDatumUngueltig').' ';
	}

	//von - bis-datum pruefen von darf nicht groesser als bis sein
	// 09.02.2009 simane
	$vondatum=0;
	if(isset($_POST['vondatum']))
	{
		$date=explode('.',$_POST['vondatum']);
		if (@checkdate($date[1], $date[0], $date[2]))
		{
			 $vondatum=$date[2].$date[1].$date[0];
			 $vondatum_iso = $date[2].'-'.$date[1].'-'.$date[0];
		}
		else
		{
			$error=true;
			$error_msg .= $p->t('zeitsperre/vonDatumUngueltig').' ';
		}
	}
	else
	{
		$error=true;
	}

	$bisdatum=0;
	if(isset($_POST['bisdatum']))
	{
		$date=explode('.',$_POST['bisdatum']);
		if (@checkdate($date[1], $date[0], $date[2]))
		{
			 $bisdatum=$date[2].$date[1].$date[0];
		}
		else
		{
			$error=true;
			$error_msg .= $p->t('zeitsperre/bisDatumUngueltig').' ';
		}
	}
	else
	{
		$error=true;
	}

	if($vondatum > $bisdatum)
	{
		$error=true;
		$error_msg .= $p->t('zeitsperre/vonDatumGroesserAlsBisDatum').'! ';
	}

	//von-datum pruefen TODO
	if($vondatum_iso < $gesperrt_bis && in_array($_POST['zeitsperretyp_kurzbz'],$typen_arr))
	{
		$error=true;
		$error_msg .= $p->t('zeitsperre/vorSperrdatum');
	}

	$zeitsperre = new zeitsperre();

	if($_GET['type']=='edit_sperre')
	{
		if(!is_numeric($_GET['id']))
		{
			$error=true;
			$error_msg.=$p->t('zeitsperre/ungueltigeId').' ';
		}
		else
		{
			//wenn die zeitsperre bereits existiert, dann wird sie geladen
			$zeitsperre->load($_GET['id']);
			$zeitsperre->new=false;
			$zeitsperre->zeitsperre_id = $_GET['id'];

			//pruefen ob die geladene id auch von der person ist die angemeldet ist
			if($zeitsperre->mitarbeiter_uid!=$uid)
				die($p->t('zeitsperre/sieHabenKeineBerechtigung'));
		}
	}
	else
	{
		$zeitsperre->new=true;
		$zeitsperre->insertamum = date('Y-m-d H:i:s');
		$zeitsperre->insertvon = $uid;
	}

	if(!$error && $_POST['zeitsperretyp_kurzbz']=='Urlaub')
	{
		if($zeitsperre->zeitsperre_id!='')
			$id = $zeitsperre->zeitsperre_id;
		else
			$id = null;
		if($zeitsperre->UrlaubEingetragen($uid, $datum_obj->formatDatum($_POST['vondatum']),$datum_obj->formatDatum($_POST['bisdatum']), $id))
		{
			$error = true;
			$error_msg.=$p->t('zeitsperre/urlaubBereitsEingetragen');
		}
	}
	if(!$error)
	{
		$zeitsperre->zeitsperretyp_kurzbz = $_POST['zeitsperretyp_kurzbz'];
		$zeitsperre->mitarbeiter_uid = $uid;
		$zeitsperre->bezeichnung = $_POST['bezeichnung'];
		$zeitsperre->vondatum = $datum_obj->formatDatum($_POST['vondatum']);
		$zeitsperre->vonstunde = $_POST['vonstunde'];
		$zeitsperre->bisdatum = $datum_obj->formatDatum($_POST['bisdatum']);
		$zeitsperre->bisstunde = $_POST['bisstunde'];
		$zeitsperre->erreichbarkeit_kurzbz = $_POST['erreichbarkeit'];
		$zeitsperre->vertretung_uid = $_POST['vertretung_uid'];
		$zeitsperre->updateamum = date('Y-m-d H:i:s');
		$zeitsperre->updatevon = $uid;

		if($zeitsperre->save())
		{
			echo "<h3>".$p->t('global/erfolgreichgespeichert')."</h3>";
			if(URLAUB_TOOLS)
			{
				if($zeitsperre->new && $zeitsperre->zeitsperretyp_kurzbz=='Urlaub')
				{
					//Beim Anlegen von neuen Urlauben wird ein Mail an den Vorgesetzten versendet um diesen Freizugeben
					$prsn = new person();

                    $vorgesetzter = $ma->getVorgesetzte($uid);
					if($vorgesetzter)
					{
						$to='';
						$fullName='';
						foreach($ma->vorgesetzte as $vg)
						{
							if (!empty($to))
                            {
								$to.=',';
								$fullName.=',';
                            }
							$to.=trim($vg.'@'.DOMAIN);
							$name = $prsn->getFullNameFromBenutzer($vg);
							$fullName.=$name;
						}

						$benutzer = new benutzer();
						$benutzer->load($uid);
						if($datum_obj->formatDatum($zeitsperre->vondatum, 'm')>=9)
							$jahr = $datum_obj->formatDatum($zeitsperre->vondatum, 'Y')+1;
						else
							$jahr = $datum_obj->formatDatum($zeitsperre->vondatum, 'Y');

						$message = "Dies ist eine automatische Mail! \n".
								   "$benutzer->nachname $benutzer->vorname hat einen neuen Urlaub eingetragen:\n".
								   "$zeitsperre->bezeichnung von ".$datum_obj->formatDatum($zeitsperre->vondatum,'d.m.Y')." bis ".$datum_obj->formatDatum($zeitsperre->bisdatum,'d.m.Y')."\n\n".
								   "Sie können diesen unter folgender Adresse freigeben:\n".
								   APP_ROOT."cis/private/profile/urlaubsfreigabe.php?uid=$uid&year=".$jahr;
						$from='vilesci@'.DOMAIN;
						$mail = new mail($to, $from, 'Freigabeansuchen', $message);
						if($mail->send())
						{
							echo "<br><b>".$p->t('urlaubstool/freigabemailWurdeVersandt',array($fullName))."</b>";
						}
						else
						{
							echo "<br><span class='error'>".$p->t('urlaubstool/fehlerBeimSendenAufgetreten',array($fullName))."</span>";
						}
					}
					else
					{
						echo "<br><span class='error'>".$p->t('urlaubstool/konnteKeinFreigabemailVersendetWerden')."</span>";
					}
				}
			}
		}
		else
			echo "<span class='error'>".$p->t('global/fehleraufgetreten')."</span>";
	}
	else
		echo "<span class='error'>$error_msg</span>";
}

//loeschen eines bereits freigegebenen Urlaubs
if((isset($_GET['type']) && $_GET['type']=='delete_sperre' && isset($_GET['informSupervisor'])))
{
    $zeitsperre = new zeitsperre();
    $zeitsperre->load($_GET['id']);

    $vondatum = $zeitsperre->getVonDatum();
    $bisdatum = $zeitsperre->getBisDatum();

    if(!$zeitsperre->delete($_GET['id']))
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
            echo "<br><b>".$p->t('urlaubstool/VorgesetzteInformiert',array($fullName))."</b>";
        }
        else
        {
            echo "<br><span class='error'>".$p->t('urlaubstool/fehlerBeimSendenAufgetreten',array($fullName))."!</span>";
        }
    }
    else
    {
        $vgmail="<br><span class='error'>".$p->t('urlaubstool/konnteKeinFreigabemailVersendetWerden')."</span>";
    }
}

//loeschen einer zeitsperre
if(isset($_GET['type']) && $_GET['type']=='delete_sperre' && !isset($_GET['informSupervisor'])   )
{
	$zeit = new zeitsperre();
	$zeit->load($_GET['id']);
	//pruefen ob die person die den datensatz loeschen will auch der
	//besitzer dieses datensatzes ist
	if($zeit->mitarbeiter_uid==$uid)
	{
		if ($zeit->vondatum < $gesperrt_bis  && in_array($zeit->zeitsperretyp_kurzbz,$typen_arr))
		{
			echo "<span class='error'>".$p->t('zeitsperre/vorSperrdatum')."</span>";
		}
		else if($zeit->delete($_GET['id']))
		{
			echo $p->t('global/erfolgreichgelöscht');
		}
		else
			echo "<span class='error'>".$p->t('global/fehleraufgetreten')."</span>";
	}
	else
		echo "<span class='error'>".$p->t('zeitsperre/keineBerechtigungDatensatzLoeschen')."</span>";
}

//zeitsperren des users laden
$zeit = new zeitsperre();
$zeit->getzeitsperren($uid);
$content_table='<br><br>';

$qry = "SELECT * FROM campus.tbl_erreichbarkeit";
$erreichbarkeit_arr=array();
if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		$erreichbarkeit_arr[$row->erreichbarkeit_kurzbz]=$row->beschreibung;
	}
}

//liste aller zeitsperren ausgeben
if(count($zeit->result)>0)
{
	$content_table.= '<table><tr class="liste"><th>'.$p->t('global/bezeichnung').'</th><th>'.$p->t('zeitsperre/grund').'</th><th>'.$p->t('global/von').'</th><th>'.$p->t('global/bis').'</th><th>'.$p->t('urlaubstool/vertretung').'</th><th>'.$p->t('urlaubstool/erreichbarkeit').'</th><th>'.$p->t('zeitsperre/freigegeben').'</th><th colspan="2"></th></tr>';
	$i=0;
	foreach ($zeit->result as $row)
	{
		$i++;
		//name der vertretung holen
		$qry = "SELECT vorname || ' ' || nachname as kurzbz FROM public.tbl_mitarbeiter, public.tbl_benutzer, public.tbl_person
				WHERE tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid
					AND tbl_benutzer.person_id=tbl_person.person_id
					AND mitarbeiter_uid=".$db->db_add_param($row->vertretung_uid);

		$result_vertretung = $db->db_query($qry);
		$row_vertretung = $db->db_fetch_object($result_vertretung);
		$content_table.= "<tr class='liste".($i%2)."'>
							<td>$row->bezeichnung</td>
							<td>$row->zeitsperretyp_beschreibung</td>
							<td nowrap>".$datum_obj->convertISODate($row->vondatum)." ".($row->vonstunde!=''?'('.$row->vonstunde.')':'')."</td>
							<td nowrap>".$datum_obj->convertISODate($row->bisdatum)." ".($row->bisstunde!=''?'('.$row->bisstunde.')':'')."</td>
							<td>".(isset($row_vertretung->kurzbz)?$row_vertretung->kurzbz:'')."</td>
							<td>".(isset($erreichbarkeit_arr[$row->erreichbarkeit])?$erreichbarkeit_arr[$row->erreichbarkeit]:'')."</td>
							<td align='center'>".($row->freigabeamum!=''?'Ja':'')."</td>";
		if ($row->zeitsperretyp_kurzbz == 'DienstV')
			$content_table .= '<td>&nbsp;</td>';
		else if ($row->vondatum < $gesperrt_bis AND in_array($row->zeitsperretyp_kurzbz,$typen_arr))
			$content_table .= '<td>&nbsp;</td>';
		else
			$content_table.="<td><a href='$PHP_SELF?type=edit&id=$row->zeitsperre_id' class='Item'>".$p->t('zeitsperre/edit')."</a></td>";
		if ($row->vondatum < $gesperrt_bis AND in_array($row->zeitsperretyp_kurzbz,$typen_arr))
			$content_table .= '<td>&nbsp;</td>';
		else if($row->vondatum>=date("Y-m-d",time()) && $row->zeitsperretyp_kurzbz=='Urlaub')
		{
			$content_table.="\n<td><a href='$PHP_SELF?type=delete_sperre&id=$row->zeitsperre_id&informSupervisor=True' onclick='return conf_del()' class='Item'>".$p->t('zeitsperre/loeschen')."</a></td>";
		}
		elseif($row->zeitsperretyp_kurzbz!='Urlaub')
        {
            $content_table.="\n<td><a href='$PHP_SELF?type=delete_sperre&id=$row->zeitsperre_id' onclick='return conf_del()' class='Item'>".$p->t('zeitsperre/loeschen')."</a></td>";
        }
		else
			$content_table .= '<td>&nbsp;</td>';
		$content_table.="</tr>";
	}
	$content_table.= '</table>';
}
else
	$content_table.= $p->t('zeitsperre/keineZeitsperrenEingetragen')."!";

$zeitsperre = new zeitsperre();
$action = "$PHP_SELF?type=new_sperre";
//wenn ein datensatz editiert werden soll, dann diesen laden
if(isset($_GET['type']) && $_GET['type']=='edit')
{
	if(isset($_GET['id']) && is_numeric($_GET['id']))
	{
		$zeitsperre->load($_GET['id']);
		//pruefen ob dieser datensatz auch dem angemeldeten user gehoert
		if($zeitsperre->mitarbeiter_uid!=$uid)
		{
			die("<span class='error'>".$p->t('zeitsperre/sieHabenKeineBerechtigungZuAendern')."</span>");
		}
		$action = "$PHP_SELF?type=edit_sperre&id=".$_GET['id'];
	}
	else
	{
		die("<span class='error'>".$p->t('global/fehlerBeiDerParameteruebergabe')."</span>");
	}
}

if($zeitsperre->freigabeamum!='' && $zeitsperre->zeitsperretyp_kurzbz=='Urlaub')
{
	$readonly=' readonly="readonly"';	//für Textfelder
	$disabled=' disabled';				//für select-options
	$style=' style="border: 1px solid #999; color: #999;"';	//disabled-Optik
	$class = '';
}
else
{
	$readonly='';
	$disabled='';
	$style='';
	$class = ' class="datepicker_datum"';
}

//formular zum editieren und neu anlegen der zeitsperren
$content_form='';
$content_form.= '<form method="POST" name="zeitsperre_form" action="'.$action.'" onsubmit="return checkdatum()">';
$content_form.= "<table>\n";
$content_form.= '<tr><td style="width:150px">'.$p->t('zeitsperre/grund').'</td><td colspan="2" style="width:450px"><SELECT name="zeitsperretyp_kurzbz"'.$style.' onchange="showHideBezeichnungDropDown()" class="dd_breit">';
//dropdown fuer zeitsperretyp
$qry = "SELECT * FROM campus.tbl_zeitsperretyp ORDER BY beschreibung";
if($result = $db->db_query($qry))
{
	while($row=$db->db_fetch_object($result))
	{
		if($zeitsperre->zeitsperretyp_kurzbz == $row->zeitsperretyp_kurzbz)
			$content_form.= "<OPTION value='$row->zeitsperretyp_kurzbz' selected>$row->beschreibung</OPTION>";
		else
			$content_form.= "<OPTION value='$row->zeitsperretyp_kurzbz'$disabled>$row->beschreibung</OPTION>";
	}
}
$content_form.= '</SELECT></td></tr>';
$content_form.= '<tr><td>'.$p->t('global/bezeichnung').'</td><td colspan="2"><span id="dienstv_span"><input'.$style.' type="text" size="32" name="bezeichnung" maxlength="32" value="'.$zeitsperre->bezeichnung.'"'.$readonly.'></span></td></tr>';
$content_form.= '<tr><td>'.$p->t('global/von').'</td><td><input'.$style.' type="text" '.$class.' size="10" maxlength="10" name="vondatum" id="vondatum" value="'.($zeitsperre->vondatum!=''?date('d.m.Y',$datum_obj->mktime_fromdate($zeitsperre->vondatum)):(!isset($_POST['vondatum'])?date('d.m.Y'):$_POST['vondatum'])).'"'.$readonly.'> <a href="javascript:void(0);" onClick="setBisDatum()">&dArr;</a></td><td id="vonStd"  style="text-align:right;"> ';
//dropdown fuer vonstunde
$content_form.= $p->t('zeitsperre/stundeInklusive');

$content_form.= " <SELECT name='vonstunde'$style>\n";
if($zeitsperre->vonstunde=='')
	$content_form.= "<OPTION value='' selectd>*</OPTION>\n";
else
	$content_form.= "<OPTION value=''$disabled>*</OPTION>\n";

for($i=0;$i<$num_rows_stunde;$i++)
{
	$row = $db->db_fetch_object($result_stunde, $i);

	if($zeitsperre->vonstunde==$row->stunde)
		$content_form.= "<OPTION value='$row->stunde' selected>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
	else
		$content_form.= "<OPTION value='$row->stunde'$disabled>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
}

$content_form.= "</SELECT></td></tr>";

$content_form.= '<tr><td>'.$p->t('global/bis').'</td><td><input'.$style.' type="text" '.$class.' size="10" maxlength="10" name="bisdatum" id="bisdatum" value="'.($zeitsperre->bisdatum!=''?date('d.m.Y',$datum_obj->mktime_fromdate($zeitsperre->bisdatum)):(!isset($_POST['bisdatum'])?date('d.m.Y'):$_POST['bisdatum'])).'"'.$readonly.'></td><td id="bisStd"  style="text-align:right;"> ';
//dropdown fuer bisstunde
$content_form.= $p->t('zeitsperre/stundeInklusive');
$content_form.= " <SELECT name='bisstunde'$style>\n";

if($zeitsperre->bisstunde=='')
	$content_form.= "<OPTION value='' selectd>*</OPTION>\n";
else
	$content_form.= "<OPTION value=''$disabled>*</OPTION>\n";

for($i=0;$i<$num_rows_stunde;$i++)
{
	$row = $db->db_fetch_object($result_stunde, $i);
	if($zeitsperre->bisstunde==$row->stunde)
		$content_form.= "<OPTION value='$row->stunde' selected>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
	else
		$content_form.= "<OPTION value='$row->stunde'$disabled>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
}

$content_form.= "</SELECT></td></tr>";

$content_form.= "<tr><td>".$p->t('urlaubstool/vertretung')."</td><td colspan='2'><SELECT name='vertretung_uid' id='vertretung_uid' class='dd_breit'>";
//dropdown fuer vertretung
$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";

$content_form.= "<OPTION value=''>-- ".$p->t('benotungstool/auswahl')." --</OPTION>\n";

if($result = $db->db_query($qry))
{
	while($row = $db->db_fetch_object($result))
	{
		if($zeitsperre->vertretung_uid == $row->uid)
			$content_form.= "<OPTION value='$row->uid' selected>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		else
			$content_form.= "<OPTION value='$row->uid'>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
	}
}
$content_form.= '</SELECT></td></tr>';

$content_form.= "<tr><td>".$p->t('urlaubstool/erreichbarkeit')."</td><td><SELECT name='erreichbarkeit'>";
foreach ($erreichbarkeit_arr as $erreichbarkeit_key=>$erreichbarkeit_beschreibung)
{
	if($zeitsperre->erreichbarkeit_kurzbz == $erreichbarkeit_key)
		$content_form.= "<OPTION value='$erreichbarkeit_key' selected>$erreichbarkeit_beschreibung</OPTION>\n";
	else
		$content_form.= "<OPTION value='$erreichbarkeit_key'>$erreichbarkeit_beschreibung</OPTION>\n";
}

$content_form.= '</SELECT></td>';

$content_form.= '<td style="text-align:right;">';

if(isset($_GET['type']) && $_GET['type']=='edit')
	$content_form.= "<input type='submit' name='submit_zeitsperre' value='".$p->t('global/speichern')."'>";

else
	$content_form.= "<input type='submit' name='submit_zeitsperre' value='".$p->t('global/hinzufuegen')."'>";
$content_form.= '</td></tr>';

$content_form .= '<tr><td colspan="3">&nbsp;</td></tr>';
$content_form.= "<tr><td colspan='3' style='color:#ff0000'>" .$p->t('zeitsperre/achtungEsWerdenAlleEingegebenenTage')."</td></tr>";
$content_form.= '</table></form>';

echo '<table width="100%">';
echo '<tr>';
echo "<td class='tdvertical'>";
echo $content_form;
echo '</td>';
echo "<td class='tdvertical'><div id='resturlaub' style='visibility:hidden;'></div></td>";
echo '</tr><tr><td colspan=2>';
echo $content_table;
echo '</td>';
echo '</tr>';
echo '</table>';

?>
</td></tr></table>
</div>
<body>
</html>
<?php echo '<script>showHideStudeDropDown();</script>'; ?>
