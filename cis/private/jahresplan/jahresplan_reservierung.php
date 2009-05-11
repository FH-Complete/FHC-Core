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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */
 
 #-------------------------------------------------------------------------------------------	
/* 
*	Reservierung zur Veranstaltung - Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*
*/
 
// ---------------- CIS Include Dateien einbinden
	require_once('../../config.inc.php');
	// Datenbankverbindung - ohne erfolg kann hier bereits beendet werden
	if (!$conn=pg_pconnect(CONN_STRING))
	{
		die('Jahresplan<br>Keine Veranstaltungen zurzeit Online.<br>Bitte etwas Geduld.<br>Danke'); 
	}
	require_once('../../../include/functions.inc.php');
	require_once('../../../include/globals.inc.php');

// ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	
// ---------------- Jahresplan Classe und Allg.Funktionen		
	include_once('../../../include/jahresplan.class.php');
	include_once('jahresplan_funktionen.inc.php');
 
// ------------------------------------------------------------------------------------------
//	Request Parameter 
// ------------------------------------------------------------------------------------------
	if (!$userUID=get_uid())
	{
		die('Es wurde keine Benutzer UID gefunden ?');
	}

	// Parameter Veranstaltung
   	$veranstaltung_id=trim((isset($_REQUEST['veranstaltung_id']) ? $_REQUEST['veranstaltung_id']:''));
	if (empty($veranstaltung_id))
	{
		exit('keine Veranstaltungs ID &uuml;bergeben');
	}

  	$openfirst=trim((isset($_REQUEST['openfirst']) ? $_REQUEST['openfirst']:''));
  	$start=trim((isset($_REQUEST['start']) ? $_REQUEST['start']:mktime(12,0,0,date("m"),date("d"),date("y")) ));
   	$ende=trim((isset($_REQUEST['ende']) ? $_REQUEST['ende']:mktime(13,0,0,date("m"),date("d"),date("y")) ));
	// Verarbeiten einer Reservierung
   	$work=trim((isset($_REQUEST['work']) ? $_REQUEST['work']:''));
   	$veranstaltung_id_zuordnen=trim((isset($_REQUEST['veranstaltung_id_zuordnen']) ? $_REQUEST['veranstaltung_id_zuordnen']:''));
   	$reservierung_id=trim((isset($_REQUEST['reservierung_id']) ? $_REQUEST['reservierung_id']:''));
   	$reservierung_key=trim((isset($_REQUEST['reservierung_key']) ? $_REQUEST['reservierung_key']:''));

// ------------------------------------------------------------------------------------------
//	Personen Classe 
//		Anwernderdaten ermitteln
// ------------------------------------------------------------------------------------------
	$userNAME=$userUID;
	$unicode=null; // Standart Encoding der Datenbank
	$pers = new benutzer($conn,$userUID,$unicode); // Lesen Person - Benutzerdaten
	if (isset($pers->nachname))
	{
		$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
		$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
		$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
		$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
	}
	
// ------------------------------------------------------------------------------------------
//	Benutzer Classe 
//		Berechtigungen ermitteln
// ------------------------------------------------------------------------------------------
	$is_lector=false;
	$is_wartungsberechtigt=false;
	if (isset($pers->nachname))
	{
	
		$benutzerberechtigung = new benutzerberechtigung($conn,$userUID);
		$benutzerberechtigung->getBerechtigungen($userUID,true);
		// Nur Lektoren oder Mitarbeiter duerfen alle Termine sehen , Studenten nur Freigegebene Kategorien
		if($benutzerberechtigung->fix || $benutzerberechtigung->lektor)
			$is_lector=true;
		else
			$is_lector=false;

		// Kennzeichen setzen fuer Berechtigungspruefung
		$berechtigung='veranstaltung';
		$studiengang_kz=null;
		$art='suid';
		$fachbereich_kurzbz=null;
		// Berechtigungen abfragen
		$is_wartungsberechtigt=$benutzerberechtigung->isBerechtigt($berechtigung,$studiengang_kz,$art, $fachbereich_kurzbz);
		if (!$is_wartungsberechtigt)
		{
			$is_wartungsberechtigt=false;
		}	
		unset($benutzerberechtigung); // Klasse Berechtigungen entfernen 
	}	

	if (!$is_wartungsberechtigt)
	{
		exit('Sie sind nicht berechtigt f&uuml;r diese Seite !');
	}

// ------------------------------------------------------------------------------------------
//	Jahresplan Classe 
//		Reservierungen
// ------------------------------------------------------------------------------------------
	$Jahresplan = new jahresplan($conn);
	$error="";
	if ($work=='save' || $work=='del')
	{
		// Der Reservierung die Veranstaltungsnummer eintragen bzw. leer wenn die Zuordnung aufgehoben wird
		if (!empty($reservierung_id))
		{
			$reservierung_id=explode('|',$reservierung_id);
			if (is_array($reservierung_id))
			{
				for ($updRes=0;$updRes<count($reservierung_id);$updRes++)
				{
					$Jahresplan->InitReservierung();	
					$Jahresplan->setVeranstaltung_id($veranstaltung_id_zuordnen);			
					$Jahresplan->setReservierung_id($reservierung_id[$updRes]);
					if (!$Jahresplan->saveReservierung())
					{
						$error.=($error?'<br>':'')."Fehler ".$Jahresplan->getError();
					}
				}
			}
			else
			{	
				$Jahresplan->InitReservierung();	
				$Jahresplan->setVeranstaltung_id($veranstaltung_id_zuordnen);			
				$Jahresplan->setReservierung_id($reservierung_id);				
				if (!$Jahresplan->saveReservierung())
				{
					$error.=($error?'<br>':'')."Fehler ".$Jahresplan->getError();
				}
			}	
		}
	}	
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Reservierungen zu ID <?php echo $veranstaltung_id.' - '.$userNAME;?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script language="JavaScript" type="text/javascript">
	<!--
		var openfirst='<?php echo $openfirst; ?>';
//		if (window.opener && !window.opener.closed && openfirst!='1') {
//			if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
//			window.opener.location.reload();
//			}	
//		}	

		if (window.opener && !window.opener.closed && openfirst!='1') {
			if (confirm("Soll die Hauptseite neu aufgebaut werden?")) {
			window.opener.location.selVeranstaltung.submit();
			}	
		}	

	//-->
	</script>

	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
		form {display:inline;}
		.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
		.ausblenden {display:none;}
		.footer_zeile {color: silver;}
		
	
	/* Listen */
		table.reservierungen_liste {border:0px;background-color:#E5E5E5;}

		tr.header_liste_titelzeile  {background-color: #F0F0F0;text-align:center;}
		tr.header_liste_row_0  {background:#FEFFEC;vertical-align: top;}
		tr.header_liste_row_1  {background:#F7F7F7;vertical-align: top;}
	
		td.zahlen {text-align:right;}
	-->
	</style>

</head>
<body onunload="reloadOpener();">
	<h1>Reservierung</h1>
<?php
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige der
//	 Veranstaltungen mit ID Selektionsbedingung lesen
// ------------------------------------------------------------------------------------------
	$showHTML='';
	
	$Jahresplan->InitReservierung();	
	// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
	$Jahresplan->setVeranstaltungskategorieMitarbeiter($is_lector);
	// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	
	if (!$is_wartungsberechtigt)	
	{
		$Jahresplan->setFreigabe(true);
	}	
	else
	{
		$Jahresplan->setFreigabe(false);
	}	
	$Jahresplan->setVeranstaltung_id('');
	$Jahresplan->setReservierung_id('');

#	echo "<br>1111 Start : $start ".Date("Y-m-d H:s",$start)." - Ende :$ende ".Date("Y-m-d H:s",$ende);
	if (Date("H",$ende)>2) // Plausib das mit den 2h abzug nicht die 00:00 Grenze unterschritten wird
	{
		$RESstart=$start-7200;
	}
	else
	{
		$RESstart=@mktime(0, 1, 0, date("m",$start),date("d",$start),date("Y",$start));
	}

	if (Date("H",$ende)<22) // Plausib das mit den 2h dazu nicht die 24:00 Grenze ueberschritten wird
	{
		$RESende=$ende+7200;
	}
	else
	{
		$RESende=@mktime(23, 59, 0, date("m",$ende),date("d",$ende),date("Y",$ende));
	}
#	echo "<br>2222 Start : $start ".Date("Y-m-d H:s",$start)." - Ende :$ende ".Date("Y-m-d H:s",$ende);

	$Jahresplan->setStart($RESstart);
	$Jahresplan->setEnde($RESende);	

	$reservierungierung=array();
	if ($reservierungierung_bak=$Jahresplan->loadReservierung())
	{
		$reservierungierung=$Jahresplan->getReservierung();
		if (is_array($reservierungierung) && count($reservierungierung)>0)
		{
			
			for ($iTmpZehler=0;$iTmpZehler<count($reservierungierung);$iTmpZehler++)
			{				
				$key='';
				$key.=$reservierungierung[$iTmpZehler]['ort_kurzbz'];
				$key.=$reservierungierung[$iTmpZehler]['titel'];
				$key.=$reservierungierung[$iTmpZehler]['reservierung_id'];

				$reservierungierung[$iTmpZehler]['key']=$reservierungierung[$iTmpZehler]['ort_kurzbz'].'|'.$reservierungierung[$iTmpZehler]['titel'].'|'.$reservierungierung[$iTmpZehler]['datum_anzeige'];
				
				$reservierungierung_sort[$key][]=$reservierungierung[$iTmpZehler];
			}	
			if (sort($reservierungierung_sort))
			{
				$reservierungierung=array();
				while (list( $tmp_key, $tmp_value ) = each($reservierungierung_sort) ) 
				{
					$reservierungierung[]=$tmp_value[0];			
				}
			}	
		}
	}
	
#echo "<br>".$Jahresplan->getStringSQL().Test($reservierungierung_bak);
	
	if (is_array($reservierungierung_bak) && (!is_array($reservierungierung) || count($reservierungierung)<1) )
	{	
		$reservierungierung=$reservierungierung_bak;
	}	
	
	$showHTML.=$Jahresplan->getError();		

	$showHTML.='<table class="reservierungen_liste" cellpadding="1" cellspacing="1">
			<tr>
				<td>Reservierung ID</td>
				<td>Ort</td>
				<td>Titel</td>
				<td colspan="2">Datum</td>
				<td colspan="2">Anlage</td>				
				<td>Veranstaltung</td>				
			</tr>';

	$lastkey=null;		
	$alleReservierung_id=null;		

	for ($iTmpZehler=0;$iTmpZehler<count($reservierungierung);$iTmpZehler++)
	{			
			$unicode=null;
			$userNAME=$reservierungierung[$iTmpZehler]['uid'];
			$pers = new benutzer($conn,$userNAME,$unicode); // Lesen Person - Benutzerdaten
			if (isset($pers->nachname))
			{
				$userNAME=(isset($pers->anrede) ? $pers->anrede.' ':'');
				$userNAME.=(isset($pers->titelpre) ? $pers->titelpre.' ':'');
				$userNAME.=(isset($pers->vorname) ? $pers->vorname.' ':'');
				$userNAME.=(isset($pers->nachname) ? $pers->nachname.' ':'');		
				if ($pers->foto)
				{
					$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($pers->foto)<800?'heximg='.$pers->foto:'userUID='.$pers->uid);
					$reservierungierung[$iTmpZehler]["bild"]='<img width="16" border="0" title="'.$userNAME.'" alt="Reservierung von Benutzer" src="'.$cURL.'">';
				}
			}			

		if ($lastkey && $lastkey !=$reservierungierung[$iTmpZehler]['key'])
		{	
			$showHTML.='
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>
			<form name="selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle" target="_self" action="'. $_SERVER['PHP_SELF'] .'"  method="post" enctype="multipart/form-data">
				<td align="center"><a href="javascript:window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.submit();">alle</a></td>
				<td>'. implode("</td><td>",explode('|',$lastkey)).'</td>
					<td colspan="4">
					<input class="ausblenden" name="reservierung_id" value="'.$alleReservierung_id.'">
					<input class="ausblenden" name="start" value="'.$start.'">
					<input class="ausblenden" name="ende" value="'.$ende.'">
					<input class="ausblenden" name="veranstaltung_id" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="veranstaltung_id_old" value="'.$veranstaltung_id.'">
					
					<input class="ausblenden" name="veranstaltung_id_zuordnen" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="reservierung_key" value="'.$reservierungierung[$iTmpZehler]['key'].'">
					<input class="ausblenden" name="work" value="nix">
					</td>
				';	
				$showHTML.='<td class="zahlen"><a href="javascript:window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'_alle.submit();">alle</a></td>';
				$showHTML.='</tr>';
			$showHTML.='
			</form>
			</tr>
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'><td colspan="9"><hr></td></tr>
			';
			$alleReservierung_id=null;
		}
			
		
		$showHTML.='
			<tr '.($iTmpZehler%2? ' class="header_liste_row_0" ':' class="header_liste_row_1" ').'>
			<form name="selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'" target="_self" action="'. $_SERVER['PHP_SELF'] .'"  method="post" enctype="multipart/form-data">
				<td>'.$reservierungierung[$iTmpZehler]['reservierung_id'].'
					<input class="ausblenden" name="reservierung_id" value="'.$reservierungierung[$iTmpZehler]['reservierung_id'].'">
					<input class="ausblenden" name="start" value="'.$start.'">
					<input class="ausblenden" name="ende" value="'.$ende.'">
					<input class="ausblenden" name="veranstaltung_id" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="veranstaltung_id_old" value="'.$veranstaltung_id.'">
					<input class="ausblenden" name="work" value="nix">
					</td>';
				
				if ($lastkey ==$reservierungierung[$iTmpZehler]['key'])
				{	
					$showHTML.='<td colspan="3"><hr></td>';
				}
				else
				{	
				$showHTML.='	
					<td>'.$reservierungierung[$iTmpZehler]['ort_kurzbz'].'</td>
					<td>'.$reservierungierung[$iTmpZehler]['titel'].'</td>
					<td>'.$reservierungierung[$iTmpZehler]['datum_anzeige'].'</td>
					';	
				}	
		
				$showHTML.='<td>'.$reservierungierung[$iTmpZehler]['beginn_anzeige'].'-'.$reservierungierung[$iTmpZehler]['ende_anzeige'].'</td>';				
				$showHTML.='<td>'.(isset($userNAME)?$userNAME:$reservierungierung[$iTmpZehler]['uid']).'</td><td>'.(isset($reservierungierung[$iTmpZehler]["bild"])?$reservierungierung[$iTmpZehler]["bild"]:'').'</td>';
				
				$showHTML.='<td class="zahlen">'.($reservierungierung[$iTmpZehler]['veranstaltung_id']!=$veranstaltung_id?$reservierungierung[$iTmpZehler]['veranstaltung_id']:'').'</td>';
				$cTmpResScript=' onclick="window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'.work.value=\'save\';window.document.selJahresplanReservierung'.($iTmpZehler<0?'':$iTmpZehler).'.submit();" ' ;	
	
				// Checkbox Reservierung zuteilen oder aufheben
				$showHTML.='<td>';
				$showHTML.='<input '.(empty($reservierungierung[$iTmpZehler]['veranstaltung_id'])?'':' checked="checked " ').' '.$cTmpResScript.' type="checkbox" value="'.$veranstaltung_id.'" name="veranstaltung_id_zuordnen">';
				if (!empty($reservierungierung[$iTmpZehler]['veranstaltung_id']) &&  $reservierungierung[$iTmpZehler]['veranstaltung_id']!=$veranstaltung_id)
				{
					$showHTML.='&nbsp;bereits zugeordnet zu Veranstaltung '.$reservierungierung[$iTmpZehler]['veranstaltung_id'];
				}	
				$showHTML.='</td>';
				$showHTML.='</tr>';
		$showHTML.='
		</form>
		</tr>
		';
			$lastkey=$reservierungierung[$iTmpZehler]['key'];
			$alleReservierung_id.=($alleReservierung_id?'|':'').$reservierungierung[$iTmpZehler]['reservierung_id'];		

		}
		$showHTML.='		
		</table>';
	$showHTML.=$error;
	$showHTML.='<p><span class="footer_zeile">Bei Fragen geben Sie bitte immer die Veranstaltungs ID '.$veranstaltung_id.' an.</span></p>';
	echo $showHTML;
?>
</body>
</html>