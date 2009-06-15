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
// * @brief bietet die Moeglichkeit zur Anzeige und
// * Aenderung der Zeitwuensche und Zeitsperren

	require_once('../../config.inc.php');
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/zeitsperre.class.php');
	require_once('../../../include/datum.class.php');
	require_once('../../../include/resturlaub.class.php');
	require_once('../../../include/person.class.php');
	require_once('../../../include/benutzer.class.php');
	require_once('../../../include/mitarbeiter.class.php');
	require_once('../../../include/mail.class.php');
	require_once('../../../include/benutzerberechtigung.class.php');

	$uid = get_uid();
	
	$PHP_SELF = $_SERVER['PHP_SELF'];

	if(isset($_GET['type']))
		$type=$_GET['type'];

	if (!$conn = @pg_pconnect(CONN_STRING))
	   	die("Es konnte keine Verbindung zum Server aufgebaut werden.");

	//Wenn User Administrator ist und UID uebergeben wurde, dann die Zeitsperren 
	//des uebergebenen Users anzeigen
	if(isset($_GET['uid']))
	{
		$rechte = new benutzerberechtigung($conn);
		$rechte->getBerechtigungen($uid);
		if($rechte->isBerechtigt('admin'))
		{
			$uid = $_GET['uid'];
		}
		else 
		{
			die('Fuer diese Aktion benoetigen Sie Administratorenrechte');
		}
	}
	$datum_obj = new datum();
	$ma= new mitarbeiter($conn);

	//Stundentabelleholen
	if(! $result_stunde=pg_query($conn, "SELECT * FROM lehre.tbl_stunde ORDER BY stunde"))
		die(pg_last_error($conn));
	$num_rows_stunde=pg_num_rows($result_stunde);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd"><html>
<head>
<title>Zeitsperre</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" href="../../../skin/style.css.php" type="text/css">
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
function berechnen()
{
	document.getElementById('summe').value = parseInt(document.getElementById('resturlaubstage').value)+parseInt(document.getElementById('anspruch').value);
}

function checkdatum()
{
	if(document.getElementById('vondatum').value.length<10)
	{
		alert('Von-Datum ist ungültig. Bitte beachten Sie das führende nullen angegeben werden müssen (Beispiel: 01.01.2008)');
		return false;
	}

	if(document.getElementById('bisdatum').value.length<10)
	{
		alert('Bis-Datum ist ungültig. Bitte beachten Sie das führende nullen angegeben werden müssen (Beispiel: 01.01.2008)');
		return false;
	}

      var Datum, Tag, Monat,Jahr,vonDatum,bisDatum; 
	  
	  Datum=document.getElementById('vondatum').value;
      Tag=Datum.substring(0,2); 
      Monat=Datum.substring(3,5); 
      Jahr=Datum.substring(6,10); 
	  
	  vonDatum=Jahr+''+Monat+''+Tag;
	  
	  Datum=document.getElementById('bisdatum').value;
      Tag=Datum.substring(0,2); 
      Monat=Datum.substring(3,5); 
      Jahr=Datum.substring(6,10); 
	  
	  bisDatum=Jahr+''+Monat+''+Tag;	  	
	
	  if (vonDatum>bisDatum)  {
		alert('Von-Datum '+ document.getElementById('vondatum').value+ ' ist groesser als das Bis-Datum '+document.getElementById('bisdatum').value);
		document.getElementById('vondatum').focus();
	  	return false;
	  }
	
	return true;
}
</script>
</head>

<body id="inhalt">
<!--<H2><table class="tabcontent">
	<tr>
	<td>
		&nbsp;<a class="Item" href="index.php">Userprofil</a> &gt;&gt;
		&nbsp;Zeitsperren
	</td>
	<td align="right"></td>
	</tr>
	</table>
</H2>-->
<table class="tabcontent" id="inhalt">
  <tr>
    <td class="tdwidth10">&nbsp;</td>
    <td>
    <table class="tabcontent">
      <tr>
		<td class="ContentHeader"><font class="ContentHeader">&nbsp;Zeitsperren</font></td>
	  </tr>
	</table>
	<br>

<!-- ************* ZEITSPERREN *****************-->

<?php

//Zeitsperre Speichern
if(isset($_GET['type']) && ($_GET['type']=='edit_sperre' || $_GET['type']=='new_sperre'))
{
	$error=false;
	$error_msg='';
	//von-datum pruefen
	if(isset($_POST['vondatum']) && !ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$_POST['vondatum']))
	{
		$error=true;
		$error_msg .= 'Von-Datum ist ung&uuml;ltig ';
	}
	//bis-datum pruefen
	if(isset($_POST['bisdatum']) && !ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$_POST['bisdatum']))
	{
		$error=true;
		$error_msg .= 'Bis-Datum ist ung&uuml;ltig ';
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
		}	 
		else
		{
			$error=true;
			$error_msg .= 'kein g&uuml;ltiges VON-Datum ';
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
			$error_msg .= 'kein g&uuml;ltiges BIS-Datum ';
		}
	}
	else
	{
		$error=true;
	}	
	
	if($vondatum > $bisdatum)
	{
		$error=true;
		$error_msg .= 'VON-Datum gr&ouml;&szlig;er als Bis-Datum! ';
	}
	
	
	
	$zeitsperre = new zeitsperre($conn);

	if($_GET['type']=='edit_sperre')
	{
		if(!is_numeric($_GET['id']))
		{
			$error=true;
			$error_msg.='Invalid id ';
		}
		else
		{
			//wenn die zeitsperre bereits existiert, dann wird sie geladen
			$zeitsperre->load($_GET['id']);
			$zeitsperre->new=false;
			$zeitsperre->zeitsperre_id = $_GET['id'];

			//pruefen ob die geladene id auch von der person ist die angemeldet ist
			if($zeitsperre->mitarbeiter_uid!=$uid)
				die('Sie haben keine Berechtigung fuer diese Zeitsperre');
		}
	}
	else
	{
		$zeitsperre->new=true;
		$zeitsperre->insertamum = date('Y-m-d H:i:s');
		$zeitsperre->insertvon = $uid;
	}

	if(!$error && $zeitsperre->freigabeamum!='')
	{
		$error = true;
		$error_msg.='Dieser Urlaub kann nicht mehr editiert werden, da er schon freigegeben wurde';
	}
	if(!$error)
	{
		$zeitsperre->zeitsperretyp_kurzbz = $_POST['zeitsperretyp_kurzbz'];
		$zeitsperre->mitarbeiter_uid = $uid;
		$zeitsperre->bezeichnung = $_POST['bezeichnung'];
		$zeitsperre->vondatum = $_POST['vondatum'];
		$zeitsperre->vonstunde = $_POST['vonstunde'];
		$zeitsperre->bisdatum = $_POST['bisdatum'];
		$zeitsperre->bisstunde = $_POST['bisstunde'];
		$zeitsperre->erreichbarkeit_kurzbz = $_POST['erreichbarkeit'];
		$zeitsperre->vertretung_uid = $_POST['vertretung_uid'];
		$zeitsperre->updateamum = date('Y-m-d H:i:s');
		$zeitsperre->updatevon = $uid;

		if($zeitsperre->save())
		{
			echo "<h3>Daten wurden erfolgreich gespeichert</h3>";
			if(URLAUB_TOOLS)
			{
				if($zeitsperre->new && $zeitsperre->zeitsperretyp_kurzbz=='Urlaub')
				{
					//Beim Anlegen von neuen Urlauben wird ein Mail an den Vorgesetzten versendet um diesen Freizugeben
					$vorgesetzter = $ma->getVorgesetzte($uid);
					if($vorgesetzter)
					{
						$to='';
						foreach($ma->vorgesetzte as $vg)
						{
							if (!empty($to))
								$to.=',';
							$to.=trim($vg.'@'.DOMAIN);
						}
						$to_len=mb_strlen($to,'UTF-8')-1;
						$to = mb_substr($to, 0,$to_len,'UTF-8');

						//$to = 'oesi@technikum-wien.at';
						$benutzer = new benutzer($conn);
						$benutzer->load($uid);
						$message = "Dies ist eine automatische Mail! \n".
								   "$benutzer->nachname $benutzer->vorname hat einen neuen Urlaub eingetragen:\n".
								   "$zeitsperre->bezeichnung von $zeitsperre->vondatum bis $zeitsperre->bisdatum\n\n".
								   "Sie können diesen unter folgender Adresse freigeben:\n".
								   APP_ROOT."cis/private/profile/urlaubsfreigabe.php?uid=$uid&year=".$datum_obj->formatDatum($zeitsperre->vondatum, 'Y');
						$from='vilesci@'.DOMAIN;
						$mail = new mail($to, $from, 'Freigabeansuchen', $message);
						if($mail->send())
						{
							echo "<br><b>Freigabemail wurde an $to versandt</b>";
						}
						else
						{
							echo "<br><span class='error'>Fehler beim Senden des Freigabemails an $to</span>";
						}
					}
					else
					{
						echo "<br><span class='error'>Es konnte keine Freigabemail versendet werden da kein Vorgesetzter eingetragen ist</span>";
					}
				}
			}
		}
		else
			echo "<span class='error'>Fehler beim Speichern der Daten</span>";
	}
	else
		echo "<span class='error'>$error_msg</span>";
}

//loeschen einer zeitsperre
if(isset($_GET['type']) && $_GET['type']=='delete_sperre')
{
	$zeit = new zeitsperre($conn);
	$zeit->load($_GET['id']);
	//pruefen ob die person die den datensatz loeschen will auch der
	//besitzer dieses datensatzes ist
	if($zeit->mitarbeiter_uid==$uid)
	{
		if($zeit->delete($_GET['id']))
		{
			echo "Eintrag wurde geloescht";
		}
		else
			echo "<span class='error'>Fehler beim loeschen des Eintrages</span>";
	}
	else
		echo "<span class='error'>Sie haben keine Berechtigung diesen Datensatz zu loeschen</span>";
}

//zeitsperren des users laden
$zeit = new zeitsperre($conn);
$zeit->getzeitsperren($uid);
$content_table='<br><br>';

$qry = "SELECT * FROM campus.tbl_erreichbarkeit";
$erreichbarkeit_arr=array();
if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		$erreichbarkeit_arr[$row->erreichbarkeit_kurzbz]=$row->beschreibung;
	}
}
//liste aller zeitsperren ausgeben
if(count($zeit->result)>0)
{
	$content_table.= '<table><tr class="liste"><th>Bezeichnung</th><th>Grund</th><th>Von</th><th>Bis</th><th>Vertretung</th><th>Erreichbarkeit</th><th>Freigegeben</th></tr>';
	$i=0;
	foreach ($zeit->result as $row)
	{
		$i++;
		//name der vertretung holen
		$qry = "SELECT vorname || ' ' || nachname as kurzbz FROM public.tbl_mitarbeiter, public.tbl_benutzer, public.tbl_person WHERE tbl_benutzer.uid=tbl_mitarbeiter.mitarbeiter_uid AND tbl_benutzer.person_id=tbl_person.person_id AND mitarbeiter_uid='$row->vertretung_uid'";
		$result_vertretung = pg_query($conn, $qry);
		$row_vertretung = pg_fetch_object($result_vertretung);
		$content_table.= "<tr class='liste".($i%2)."'>
							<td>$row->bezeichnung</td>
							<td>$row->zeitsperretyp_kurzbz</td>
							<td nowrap>".$datum_obj->convertISODate($row->vondatum)." ".($row->vonstunde!=''?'('.$row->vonstunde.')':'')."</td>
							<td nowrap>".$datum_obj->convertISODate($row->bisdatum)." ".($row->bisstunde!=''?'('.$row->bisstunde.')':'')."</td>
							<td>".(isset($row_vertretung->kurzbz)?$row_vertretung->kurzbz:'')."</td>
							<td>".(isset($erreichbarkeit_arr[$row->erreichbarkeit])?$erreichbarkeit_arr[$row->erreichbarkeit]:'')."</td>
							<td align='center'>".($row->freigabeamum!=''?'Ja':'')."</td>";
		if($row->freigabeamum=='' || $row->zeitsperretyp_kurzbz!='Urlaub')
		{
			$content_table.="<td><a href='$PHP_SELF?type=edit&id=$row->zeitsperre_id' class='Item'>edit</a></td>
							 <td><a href='$PHP_SELF?type=delete_sperre&id=$row->zeitsperre_id' onclick='return conf_del()' class='Item'>delete</a></td>";
		}
		$content_table.="</tr>";
	}
	$content_table.= '</table>';
}
else
	$content_table.= "Derzeit sind keine Zeitsperren eingetragen!";

$zeitsperre = new zeitsperre($conn);
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
			die("<span class='error'>Sie haben keine Berechtigung diese Zeitsperre zu aendern</span>");
		}
		$action = "$PHP_SELF?type=edit_sperre&id=".$_GET['id'];
	}
	else
	{
		die("<span class='error'>Fehlerhafte Parameteruebergabe</span>");
	}
}
//formular zum editieren und neu anlegen der zeitsperren
$content_form='';
$content_form.= '<form method="POST" action="'.$action.'" onsubmit="return checkdatum()">';
$content_form.= "<table>\n";
$content_form.= '<tr><td>Grund</td><td><SELECT name="zeitsperretyp_kurzbz">';
//dropdown fuer zeitsperretyp
$qry = "SELECT * FROM campus.tbl_zeitsperretyp ORDER BY zeitsperretyp_kurzbz";
if($result = pg_query($conn, $qry))
{
	while($row=pg_fetch_object($result))
	{
		if($zeitsperre->zeitsperretyp_kurzbz == $row->zeitsperretyp_kurzbz)
			$content_form.= "<OPTION value='$row->zeitsperretyp_kurzbz' selected>$row->zeitsperretyp_kurzbz - $row->beschreibung</OPTION>";
		else
			$content_form.= "<OPTION value='$row->zeitsperretyp_kurzbz'>$row->zeitsperretyp_kurzbz - $row->beschreibung</OPTION>";
	}
}
$content_form.= '</SELECT>';
$content_form.= '<tr><td>Bezeichnung</td><td><input type="text" name="bezeichnung" maxlength="32" value="'.$zeitsperre->bezeichnung.'"></td></tr>';
$content_form.= '<tr><td>von</td><td><input type="text" size="10" maxlength="10" name="vondatum" id="vondatum" value="'.($zeitsperre->vondatum!=''?date('d.m.Y',$datum_obj->mktime_fromdate($zeitsperre->vondatum)):(!isset($_POST['vondatum'])?date('d.m.Y'):$_POST['vondatum'])).'"> ';
//dropdown fuer vonstunde
$content_form.= "Stunde (inklusive)";

$content_form.= "<SELECT name='vonstunde'>\n";
if($zeitsperre->vonstunde=='')
	$content_form.= "<OPTION value='' selectd>*</OPTION>\n";
else
	$content_form.= "<OPTION value=''>*</OPTION>\n";

for($i=0;$i<$num_rows_stunde;$i++)
{
	$row = pg_fetch_object($result_stunde, $i);

	if($zeitsperre->vonstunde==$row->stunde)
		$content_form.= "<OPTION value='$row->stunde' selected>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
	else
		$content_form.= "<OPTION value='$row->stunde'>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
}

$content_form.= "</SELECT></td></tr>";

$content_form.= '<tr><td>bis</td><td><input type="text" size="10" maxlength="10" name="bisdatum" id="bisdatum" value="'.($zeitsperre->bisdatum!=''?date('d.m.Y',$datum_obj->mktime_fromdate($zeitsperre->bisdatum)):(!isset($_POST['bisdatum'])?date('d.m.Y'):$_POST['bisdatum'])).'"> ';
//dropdown fuer bisstunde
$content_form.= "Stunde (inklusive)";
$content_form.= "<SELECT name='bisstunde'>\n";

if($zeitsperre->bisstunde=='')
	$content_form.= "<OPTION value='' selectd>*</OPTION>\n";
else
	$content_form.= "<OPTION value=''>*</OPTION>\n";

for($i=0;$i<$num_rows_stunde;$i++)
{
	$row = pg_fetch_object($result_stunde, $i);
	if($zeitsperre->bisstunde==$row->stunde)
		$content_form.= "<OPTION value='$row->stunde' selected>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
	else
		$content_form.= "<OPTION value='$row->stunde'>$row->stunde (".date('H:i',strtotime($row->beginn)).' - '.date('H:i',strtotime($row->ende))." Uhr)</OPTION>\n";
}

$content_form.= "</SELECT></td></tr>";

$content_form.= "<tr><td>Erreichbarkeit</td><td><SELECT name='erreichbarkeit'>";
foreach ($erreichbarkeit_arr as $erreichbarkeit_key=>$erreichbarkeit_beschreibung)
{
	if($zeitsperre->erreichbarkeit_kurzbz == $erreichbarkeit_key)
		$content_form.= "<OPTION value='$erreichbarkeit_key' selected>$erreichbarkeit_beschreibung</OPTION>\n";
	else
		$content_form.= "<OPTION value='$erreichbarkeit_key'>$erreichbarkeit_beschreibung</OPTION>\n";
}

$content_form.= '</SELECT></td></tr>';

$content_form.= "<tr><td>Vertretung</td><td><SELECT name='vertretung_uid' id='vertretung_uid'>";
//dropdown fuer vertretung
$qry = "SELECT * FROM campus.vw_mitarbeiter WHERE uid not LIKE '\\\_%' ORDER BY nachname, vorname";

$content_form.= "<OPTION value=''>-- Auswahl --</OPTION>\n";

if($result = pg_query($conn, $qry))
{
	while($row = pg_fetch_object($result))
	{
		if($zeitsperre->vertretung_uid == $row->uid)
			$content_form.= "<OPTION value='$row->uid' selected>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
		else
			$content_form.= "<OPTION value='$row->uid'>$row->nachname $row->vorname ($row->uid)</OPTION>\n";
	}
}
$content_form.= '</SELECT></td></tr>';
$content_form.= '<tr><td>&nbsp;</td><td>';

if(isset($_GET['type']) && $_GET['type']=='edit')
	$content_form.= "<input type='submit' name='submit_zeitsperre' value='Speichern'>";
else
	$content_form.= "<input type='submit' name='submit_zeitsperre' value='Hinzufügen'>";
$content_form.= '</td></tr>';
$content_form.= "<tr><td>&nbsp;</td><td style='color:red'>Achtung: Es werden alle eingegeben Tage berücksichtigt, daher müssen mehrtägige Zeitsperren<br> an Unterbrechungen wie Wochenenden oder Feiertagen unterteilt werden!</td</tr>";
$content_form.= '</table></form>';

// ******* RESTURLAUB ******** //
$content_resturlaub = '';
$resturlaubstage = '0';
$mehrarbeitsstunden = '0';
$anspruch = '25';
/*
if(isset($_GET['type']) && $_GET['type']=='save_resturlaub')
{
	$_POST['mehrarbeitsstunden'] = str_replace(',','.',$_POST['mehrarbeitsstunden']);

	$resturlaub = new resturlaub($conn);
	if($resturlaub->load($uid))
	{
		$resturlaub->new = false;
	}
	else
	{
		$resturlaub->new = true;
		$resturlaub->insertamum = date('Y-m-d H:i:s');
		$resturlaub->insertvon = $uid;
	}
	$resturlaub->mitarbeiter_uid = $uid;
	$resturlaub->updateamum = date('Y-m-d H:i:s');
	$resturlaub->updatevon = $uid;
	if(isset($_POST['resturlaubstage']))
		$resturlaub->resturlaubstage = $_POST['resturlaubstage'];
	if(isset($_POST['anspruch']))
		$resturlaub->urlaubstageprojahr = $_POST['anspruch'];
	$resturlaub->mehrarbeitsstunden = $_POST['mehrarbeitsstunden'];

	if($resturlaub->save())
	{
		$content_resturlaub .= '<b>Daten wurden gespeichert!</b>';
	}
	else
	{
		$content_resturlaub .= "<b>Fehler beim Speichern der Daten: $resturlaub->errormsg</b>";
	}

	$resturlaubstage = htmlspecialchars($resturlaub->resturlaubstage,ENT_QUOTES);
	$mehrarbeitsstunden = htmlspecialchars($resturlaub->mehrarbeitsstunden,ENT_QUOTES);
	$anspruch = htmlspecialchars($resturlaub->urlaubstageprojahr,ENT_QUOTES);
}
else
{*/
	$resturlaub = new resturlaub($conn);

	if($resturlaub->load($uid))
	{
		$resturlaubstage = $resturlaub->resturlaubstage;
		$mehrarbeitsstunden = $resturlaub->mehrarbeitsstunden;
		$anspruch = $resturlaub->urlaubstageprojahr;
	}
/*}
if($anspruch=='')
	$anspruch=25;

//Eingabefelder am 15.12.2007 deaktivieren
if((date('d')>=15 && date('m')>=12 && date('Y')>=2007) || date('Y')>2007)
	$disabled='disabled="true"';
else
	$disabled='';
*/
//Den Bereich fuer die Resturlaubstage nur anzeigen wenn dies
//im config angegeben ist
if(URLAUB_TOOLS)
{
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
	$content_resturlaub.="<h3>Urlaub im Gesch&auml;ftsjahr $geschaeftsjahr</h3>";
	/*
	$content_resturlaub.='<form method="POST" action="'.$PHP_SELF.'?type=save_resturlaub"><table>';
	$content_resturlaub.='<tr><td>Resturlaubstage (31.08.)</td><td><input type="text" size="6" '.$disabled.' id="resturlaubstage" name="resturlaubstage" value="'.$resturlaubstage.'" oninput="berechnen()"/></td></tr>';
	$content_resturlaub.='<tr><td>Anspruch (01.09.)</td><td><input type="text" size="6" '.$disabled.' id="anspruch" name="anspruch" value="'.$anspruch.'" oninput="berechnen()"/></td></tr>';
	$content_resturlaub.='<tr><td>Gesamturlaub</td><td><input type="text" disabled="true" size="6" name="summe" id="summe" value="'.($anspruch+$resturlaubstage).'" /></td></tr>';
	$content_resturlaub.='<tr><td>&nbsp;</td></tr>';
	$content_resturlaub.='<tr><td>Aktuelle Mehrarbeitsstunden:</td><td><input type="text" size="6" name="mehrarbeitsstunden" value="'.$mehrarbeitsstunden.'" /></td></tr>';
	$content_resturlaub.='<tr><td></td><td><input type="submit" name="save_resturlaub" value="Speichern" /></td></tr></table>';
	*/
	$gebuchterurlaub=0;
	//Urlaub berechnen (date_part('month', vondatum)>9 AND date_part('year', vondatum)='".(date('Y')-1)."') OR (date_part('month', vondatum)<9 AND date_part('year', vondatum)='".date('Y')."')
	$qry = "SELECT sum(bisdatum-vondatum+1) as anzahltage FROM campus.tbl_zeitsperre
				WHERE zeitsperretyp_kurzbz='Urlaub' AND mitarbeiter_uid='$uid' AND
				(
					vondatum>='$datum_beginn_iso' AND bisdatum<='$datum_ende_iso'
				)";
	$tttt="\n";
	$result = pg_query($conn, $qry);
	$row = pg_fetch_object($result);
	$gebuchterurlaub = $row->anzahltage;
	if($gebuchterurlaub=='')
		$gebuchterurlaub=0;
	$content_resturlaub.="<table><tr><td nowrap>Anspruch</td><td align='right'  nowrap>$anspruch Tage</td><td nowrap class='grey'>&nbsp;&nbsp;&nbsp( j&auml;hrlich )</td></tr>";
	$content_resturlaub.="<tr><td nowrap>+ Resturlaub</td><td align='right'  nowrap>$resturlaubstage Tage</td><td nowrap class='grey'>&nbsp;&nbsp;&nbsp;( Stichtag: $datum_beginn )</td></tr>";
	$content_resturlaub.="<tr><td nowrap>- aktuell gebuchter Urlaub&nbsp;</td><td align='right'  nowrap>$gebuchterurlaub Tage</td><td nowrap class='grey'>&nbsp;&nbsp;&nbsp;( $datum_beginn - $datum_ende )</td></tr>";
	$content_resturlaub.="<tr><td style='border-top: 1px solid black;'  nowrap>aktueller Stand</td><td style='border-top: 1px solid black;' align='right' nowrap>".($anspruch+$resturlaubstage-$gebuchterurlaub)." Tage</td><td nowrap class='grey'>&nbsp;&nbsp;&nbsp;( Stichtag: $datum_ende )</td></tr>";
	$content_resturlaub .="<tr></tr><tr><td><a href='../../cisdocs/AblaufUrlaubserfassung.pdf'> [AblaufUrlaubserfassung.pdf] </a></td><td><button type='button' name='hilfe' value='Hilfe' onclick='alert(\"Anspruch: Anzahl der Urlaubstage, auf die in diesem Geschäftsjahr (1.9. bis 31.8) ein Anrecht ensteht. \\nResturlaub: Anzahl der Urlaubstage, aus vergangenen Geschäftsjahren, die noch nicht verbraucht wurden. \\naktuell gebuchter Urlaub: Anzahl aller eingetragenen Urlaubstage. \\nAchtung: Als Urlaubstag gelten ALLE Tage zwischen von-Datum und bis-Datum d.h. auch alle Wochenenden, Feiertage und arbeitsfreie Tage. Beispiel: Ein Kurzurlaub beginnt mit einem Donnerstag und endet am darauffolgenden Dienstag, so wird zuerst eine Eintragung mit dem Datum des Donnerstags im von-Feld und dem Datum des letzten Urlaubstag vor dem Wochenende, meistens der Freitag, eingegeben. Danach wird eine Eintagung des zweiten Teils, von Montag bis Dienstag vorgenommen.\\naktueller Stand: Die zur Zeit noch verfügbaren Urlaubstage.\");'>Hilfe</button></td></tr>";
	$content_resturlaub .='<tr><td></td></tr>';
	$content_resturlaub.="</table>";
}
echo '<table width="100%">';
echo '<tr>';
echo "<td class='tdvertical'>";
echo $content_form;
echo '</td>';
echo "<td class='tdvertical'>$content_resturlaub</td>";
echo '</tr><tr><td colspan=2>';
echo $content_table;
echo '</td>';
echo '</tr>';
echo '</table>';

?>
</td></tr></table>
<body>
</html>