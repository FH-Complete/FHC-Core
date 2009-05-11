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

// ---------------- CIS Include Dateien einbinden
	require_once('../../config.inc.php');
	// Datenbankverbindung - ohne erfolg kann hier bereits beendet werden

	if (!$conn=pg_pconnect(CONN_STRING))
	{
		die('Jahresplan<br />Keine Veranstaltungen zurzeit Online.<br />Bitte etwas Geduld.<br />Danke'); 
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
	// Parameter Veranstaltungskategorie
  	$veranstaltungskategorie_kurzbz=trim((isset($_REQUEST['veranstaltungskategorie_kurzbz']) ? $_REQUEST['veranstaltungskategorie_kurzbz']:''));
	// Parameter Veranstaltung
   	$veranstaltung_id=trim((isset($_REQUEST['veranstaltung_id']) ? $_REQUEST['veranstaltung_id']:''));
   	$Jahr=trim((isset($_REQUEST['Jahr']) ? $_REQUEST['Jahr']:date("Y", mktime(0,0,0,date("m"),date("d"),date("y")))));
   	$Monat=trim((isset($_REQUEST['Monat']) ? $_REQUEST['Monat']:date("m", mktime(0,0,0,date("m"),date("d"),date("y")))));
	$Suchtext=trim((isset($_REQUEST['Suchtext']) ? $_REQUEST['Suchtext']:''));
 
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
//	Benutzerberechtigung Classe 
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
	}	
// ------------------------------------------------------------------------------------------
// 	Alle Kategoriedaten lesen fuer Selektfeld
// ------------------------------------------------------------------------------------------
	$Jahresplan = new jahresplan($conn);
	$Jahresplan->InitVeranstaltungskategorie();
	if ($Jahresplan->loadVeranstaltungskategorie())
	{
		$veranstaltungskategorie=$Jahresplan->getVeranstaltungskategorie();
	}	
	else // Es gibt keine Kategorie oder Fehler beim Lesen - keine weitere Anzeige mehr moeglich
	{
		die($Jahresplan->getError());
	}
	
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Jahresplan</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
<style type="text/css">
	<!-- 
	form {display:inline;}
				
	/* Kategorien Abrundungen im Detail */
	b.rtop, b.rbottom{display:block;background: transparent;}
	b.rtop b, b.rbottom b{display:block;height: 1px; overflow: hidden; background: #E5E5E5;}
	b.r1{margin: 0 5px}
	b.r2{margin: 0 3px}
	b.r3{margin: 0 2px}
	b.rtop b.r4, b.rbottom b.r4{margin: 0 1px;height: 2px}
	
	.cursor_hand {cursor:pointer;vertical-align: top;white-space : nowrap;}
	.ausblenden {display:none;}
	.footer_zeile {color: silver;}
		
	/* Kalender */	kalender_kpl_monat
		tr.kalender_header {text-align:left;}

		/* Monat Container */
		table.kalender_kpl_monat {border:0px;background-color:#E5E5E5;}
		.kalender_kpl_monatkalender_kpl_monat th { font-weight: normal;width: 13%;}
		#kalender_kpl_monat { font-weight: normal;width: 2%;}

		/* Tages Row */
		.kalender_kpl_monat_tagname tr {text-align:center;}
		.kalender_kpl_monat_tagname th {border:0px; font-weight: normal;}
		#kalender_kpl_monat_woche {font-weight: normal;width: 2%;}
		

		/* Woche - Tages - Rows */
		td.kalender_woche_on_of_container  {vertical-align:top;width: 2%;}
		td.kalender_woche_tages_kpl_container {vertical-align:top;width: 13%;border:1px solid #E5E5E5;background-color:#FFFFFF;}
		
		.kalender_woche_anzeigen {white-space: nowrap;cursor:pointer;vertical-align:top;}
		.kalender_woche_verbergen {white-space: nowrap;cursor:pointer;display:none;vertical-align:top;}
		

		table.kalender_woche_tages_container {width: 100%;background-color:#FFFFFF;}
		td.kalender_woche_tages_container {vertical-align:top;}
		
		
		/* - Header Tagesansicht im Kalender - */
		div.kalender_woche_tag_falscher_monat {width:100%;text-align:left;border:0px solid #B2B2B2;color:silver;background-color:#E5E5E5;}
		div.kalender_woche_tag_ohne_termin {width:100%;text-align:left;border:0px solid #B2B2B2;color:black;background-color:#E5E5E5;}
		div.kalender_woche_tag_mit_termin {width:100%;text-align:left;border:0px solid  #B2B2B2;color:black;background-color:#E5E5E5;font-weight: bold;}
		
		div.kalender_tages_container_on {width: 100%;border:0px;padding: 1px 0px 1px 0px;}
		div.kalender_tages_container_off {width: 100%;display:none;border:0px;padding: 1px 0px 1px 0px;}
		
		table.kalender_tages_info {width: 100%;border:0px;text-align:left;}		
		tr.kalender_tages_info {text-align:left;vertical-align:top;cursor:pointer;}		
		td.kalender_tages_info {text-align:left;}		
		
	/* Listen */
		tr.header_liste_titelzeile  {background-color: #F0F0F0;text-align:center;}
		tr.header_liste_row_0  {background:#FEFFEC;vertical-align: top;}
		tr.header_liste_row_1  {background:#F7F7F7;vertical-align: top;}
		
	-->
	</style>
	
	<script language="JavaScript1.2" type="text/javascript">
	<!--
	function show_layer(x)
	{
 		if (document.getElementById && document.getElementById(x)) 
		{  
			document.getElementById(x).style.visibility = 'visible';
			document.getElementById(x).style.display = 'inline';
		} else if (document.all && document.all[x]) {      
		   	document.all[x].visibility = 'visible';
			document.all[x].style.display='inline';
	      	} else if (document.layers && document.layers[x]) {                          
	           	 document.layers[x].visibility = 'show';
			 document.layers[x].style.display='inline';
	          }

	}

	function hide_layer(x)
	{
		if (document.getElementById && document.getElementById(x)) 
		{                       
		   	document.getElementById(x).style.visibility = 'hidden';
			document.getElementById(x).style.display = 'none';
       	} else if (document.all && document.all[x]) {                                
			document.all[x].visibility = 'hidden';
			document.all[x].style.display='none';
       	} else if (document.layers && document.layers[x]) {                          
	           	 document.layers[x].visibility = 'hide';
			 document.layers[x].style.display='none';
	          }
	}			
	
	var InfoWin;  
	function callWindows(url,nameID)
	{
		 // width=(Pixel) - erzwungene Fensterbreite 
		 // height=(Pixel) - erzwungene Fensterh&ouml;he 
		 // resizable=yes/no - Gr&ouml;&szlig;e fest oder ver&auml;nderbar 
		 // scrollbars=yes/no - fenstereigene Scrollbalken 
		 // toolbar=yes/no - fenstereigene Buttonleiste 
		 // status=yes/no - fenstereigene Statuszeile 
		 // directories=yes/no - fenstereigene Directory-Buttons (Netscape) 
		 // menubar=yes/no - fenstereigene Men&uuml;leiste 
		 // location=yes/no - fenstereigenes Eingabe-/Auswahlfeld f&uuml;r URLs 
		 
		if (InfoWin) {
			InfoWin.close();
	 	}
	       InfoWin=window.open(url,nameID,"copyhistory=no,directories=no,location=no,dependent=no,toolbar=yes,menubar=no,status=no,resizable=yes,scrollbars=yes, width=500,height=600,left=60, top=15");  
		InfoWin.focus();
		InfoWin.setTimeout("window.close()",800000);
	}
-->
</script>				

</head>
<body>
<?php 
 // Wartungsberechtigt bekommen noch ein Spezielles Menue
	if ($is_wartungsberechtigt)
	{
// wunsch keine Veranstaltungs Menue		echo '[&nbsp;<a href="index.php">Veranstaltung</a>&nbsp;|&nbsp;<a href="jahresplan_veranstaltung.php">Veranstaltung bearbeiten</a>&nbsp;|&nbsp;<a href="jahresplan_kategorie.php">Kategorie</a>&nbsp;]&nbsp;'.$userNAME;
		echo '[&nbsp;<a href="index.php">Veranstaltung</a>&nbsp;|&nbsp;<a href="jahresplan_kategorie.php">Kategorie</a>&nbsp;]&nbsp;'.$userNAME;
		
	} // Ende Wartungsberechtigt
?>
	<h1>&nbsp;Veranstaltungen&nbsp;</h1>
	<form name="selJahresplan" target="_self" action="<?php echo $_SERVER['PHP_SELF'];?>"  method="post" enctype="multipart/form-data">
		<table cellpadding="0" cellspacing="0">
		<tr>	
			<!-- Jahresauswahl -->			
			<td title="1 Jahr zur&uuml;ck" ><img onclick="if (window.document.selJahresplan.Jahr.options.selectedIndex==0) {window.document.selJahresplan.Jahr.options.selectedIndex=(window.document.selJahresplan.Jahr.options.length - 1);} else { window.document.selJahresplan.Jahr.options.selectedIndex--; }; window.document.selJahresplan.submit();" alt="1 Jahr zur&uuml;ck" src="../../../skin/images/left.gif" border="0"></td>
			<td><select name="Jahr" onchange="window.document.selJahresplan.submit();" >
			<?php
				$cTmpStart=date("Y", mktime(0,0,0,date("m"),date("d"),date("y")-3));
				for ($iTmpZehler=1;$iTmpZehler<=7;$iTmpZehler++)
				{
					$cTmpStart++;
					echo '<option '.($Jahr==$cTmpStart?' selected="selected" ':'').' value="'.$cTmpStart.'">'.$cTmpStart.'</option>';
				}
			?>	
			</select></td>
			<td title="1 Jahr vor" ><img onclick="if (window.document.selJahresplan.Jahr.options.selectedIndex==(window.document.selJahresplan.Jahr.options.length - 1)) {window.document.selJahresplan.Jahr.options.selectedIndex=0} else {window.document.selJahresplan.Jahr.options.selectedIndex++;};window.document.selJahresplan.submit();" alt="1 Jahr vor" src="../../../skin/images/right.gif" border="0"></td>
			<td>&nbsp;</td>
			<!-- Monatsauswahl -->			
			<td title="1 Monat zur&uuml;ck" ><img onclick="if (window.document.selJahresplan.Monat.options.selectedIndex==0) {window.document.selJahresplan.Monat.options.selectedIndex=(window.document.selJahresplan.Monat.options.length - 1);} else { window.document.selJahresplan.Monat.options.selectedIndex--; }; window.document.selJahresplan.veranstaltung_id.value='';window.document.selJahresplan.Suchtext.value='';window.document.selJahresplan.submit();" alt="1 Monat zur&uuml;ck" src="../../../skin/images/left.gif" border="0"></td>
			<td><select name="Monat" onchange="window.document.selJahresplan.veranstaltung_id.value='';window.document.selJahresplan.Suchtext.value='';window.document.selJahresplan.submit();" >
			<?php
				for ($iTmpZehler=0;$iTmpZehler<=12;$iTmpZehler++)
				{
					echo '<option '.($Monat==$iTmpZehler || $Monat=='0'.$iTmpZehler?' selected="selected" ':'').' value="'.(!empty($iTmpZehler)?strftime ("%m", mktime(0, 0, 0, $iTmpZehler, 1,date("y"))):'').'">'.(!empty($iTmpZehler)?strftime ("%B", mktime(0, 0, 0, $iTmpZehler, 1,date("y"))):'gesamtes Jahr').'</option>';
				}
			?>	
			</select></td>
			<td title="1 Monat vor" ><img onclick="if (window.document.selJahresplan.Monat.options.selectedIndex==(window.document.selJahresplan.Monat.options.length - 1)) {window.document.selJahresplan.Monat.options.selectedIndex=0} else {window.document.selJahresplan.Monat.options.selectedIndex++;};window.document.selJahresplan.veranstaltung_id.value='';window.document.selJahresplan.Suchtext.value='';window.document.selJahresplan.submit();" alt="1 Monat vor" src="../../../skin/images/right.gif" border="0"></td>
			<td>&nbsp;</td>
			<!-- Kategorieauswahl -->
			<td><select name="veranstaltungskategorie_kurzbz" onchange="window.document.selJahresplan.submit();" >
			<?php
			
				echo '<option '.(empty($veranstaltungskategorie_kurzbz)?' selected="selected" ':'').' value="">alle Kategorien</option>';
				// Init Direktzugriffstabelle der Kategorien fuer Kalender - Key:veranstaltungskategorie_kurzbz
				// Verarbeitungskategorie - Auswahl.- Selektliste
			  	if  (is_array($veranstaltungskategorie) || count($veranstaltungskategorie)>0)
				{
					reset($veranstaltungskategorie);
				  	for ($iTmpZehler=0;$iTmpZehler<count($veranstaltungskategorie);$iTmpZehler++)
					{
						// Check Space
						$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]=trim($veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
						$veranstaltungskategorie[$iTmpZehler]["bezeichnung"]=trim($veranstaltungskategorie[$iTmpZehler]["bezeichnung"]);
						// Kategoriebild erzeugen (wird spaeter verwendet)
						$cURL='jahresplan_bilder.php?time='.time().'&'.(strlen($veranstaltungskategorie[$iTmpZehler]["bild"])<800?'heximg='.$veranstaltungskategorie[$iTmpZehler]["bild"]:'veranstaltungskategorie_kurzbz='.$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]);
						$veranstaltungskategorie[$iTmpZehler]["bild_image"]='<img height="20" border="0" alt="Kategoriebild" titel="'.$veranstaltungskategorie[$iTmpZehler]["bezeichnung"].'" src="'.$cURL.'" />';
						echo '<option  '.(!empty($veranstaltungskategorie[$iTmpZehler]["farbe"])?' style="background-color:#'.$veranstaltungskategorie[$iTmpZehler]["farbe"].'" ':'').'  '.($veranstaltungskategorie_kurzbz==$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"]?' selected="selected" ':'').' value="'.$veranstaltungskategorie[$iTmpZehler]["veranstaltungskategorie_kurzbz"].'">'.$veranstaltungskategorie[$iTmpZehler]["bezeichnung"].'</option>';
					}	
				}
			?>
			</select></td>
			<td>&nbsp;</td>
			<!-- Veranstaltungs ID  -->
			<td>ID</td>
			<td><input onblur="if (this.value!='') { window.document.selJahresplan.Monat.options.selectedIndex=0;window.document.selJahresplan.Suchtext.value='';window.document.selJahresplan.submit(); } " name="veranstaltung_id" type="text" size="4" maxlength="10" title="Veranstaltungs ID" value="<?php echo $veranstaltung_id;?>"></td>
			<td>&nbsp;</td>
			<!-- Textsuche  -->
			<td>Suche</td>
			<td><input onblur="if (this.value!='') { window.document.selJahresplan.Monat.options.selectedIndex=0;window.document.selJahresplan.submit(); } "  name="Suchtext" type="text" size="15" maxlength="30" title="Suchtext" value="<?php echo $Suchtext;?>"></td>
			<td>&nbsp;</td>
			<!-- Datenanzeige Startknopf  -->
			<td  title="Veranstaltungen anzeigen">
				<input type="Submit" value="anzeigen">
			</td>
			<td>&nbsp;</td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		</table>
	</form>
<?php
// ------------------------------------------------------------------------------------------
// 	Datenanzeige - Varianten sind Detail,Listen und Kalenderform
// ------------------------------------------------------------------------------------------

	// Veranstaltung Initialisieren der Klasse
	$Jahresplan->InitVeranstaltung();
	// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
	$Jahresplan->setVeranstaltungskategorieMitarbeiter($is_lector);
	// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	
	if (!$is_wartungsberechtigt)	
		$Jahresplan->setFreigabe(true);
	else
		$Jahresplan->setFreigabe(false);
	$Jahresplan->setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz);
	$Jahresplan->setVeranstaltung_id($veranstaltung_id);
	$Jahresplan->setSuchtext(str_replace('*','%',$Suchtext));

	//  Datum setzen ausser wenn eine eindeutige ID selektiert wurde. Diese soll in allen Perioden gesucht werden
	if (empty($veranstaltung_id)) 
	{
		if (empty($Jahr))
		{
			$Jahr=date("Y", mktime(0,0,0,date("m"),date("d"),date("y")));	
		}	

		$Jahresplan->setStart_jahr($Jahr);
		if (!empty($Woche))
		{
			$iTmpMinKW=date("W",mktime(0, 0, 0,(empty($Monat) || $Monat>12?'01':$Monat),1, $Jahr));
			$iTmpMaxKW=date("W",mktime(0, 0, 0,(empty($Monat) || $Monat>12?'01':$Monat),$iTmpMaxTage, $Jahr));
			$iTmpMaxKW=number_format($iTmpMaxKW);
			if ($iTmpMaxKW<2 && $iTmpMonat==12)
				$iTmpMaxKW=53;
			$Jahresplan->setStart_jahr_woche($Jahr.$iTmpMinKW);
			$Jahresplan->setEnde_jahr_woche($Jahr.$iTmpMaxKW);
		}
		elseif (!empty($Monat))
		{
			$Jahresplan->setStart_jahr_monat($Jahr.(empty($Monat) || $Monat>12?'01':$Monat));
			$Jahresplan->setEnde_jahr_monat($Jahr.(empty($Monat) || $Monat>12?'01':$Monat));
		}	
	}	

	// **************************************
	// Veranstaltungen zu Selektion - lesen
	// **************************************
	$veranstaltung=array();
	if ($Jahresplan->loadVeranstaltung())
	{
		$veranstaltung=$Jahresplan->getVeranstaltung();
	}	
#echo $Jahresplan->getStringSQL();

	// Ausgabe der Veranstaltungsdaten bzw Hinweisstext
	if (is_array($veranstaltung) && isset($veranstaltung[0]))
	{
		// Detailanzeige
		if (!empty($veranstaltung_id))
		{
			echo jahresplan_veranstaltung_detailanzeige($conn,$veranstaltung,$is_wartungsberechtigt);
		}
		// Listenanzeige
		elseif (!empty($Suchtext))
		{
			echo jahresplan_veranstaltung_listenanzeige($conn,$veranstaltung,$is_wartungsberechtigt);
		}
		// Kalenderanzeige
		else
		{
			echo jahresplan_veranstaltungskategorie_kalenderanzeige($conn,$veranstaltung,$is_wartungsberechtigt,$Jahr,$Monat);
		}
	}
	// Keine Daten gefunden
	elseif (empty($veranstaltung_id) && empty($Suchtext))
	{
		echo jahresplan_veranstaltungskategorie_kalenderanzeige($conn,$veranstaltung,$is_wartungsberechtigt,$Jahr,$Monat);
	}
	else
	{
		echo "<br />keine Daten gefunden ".(!empty($Suchtext)? ' Suchtext '.$Suchtext:'' ).(!empty($veranstaltung_id)? ' ID '.$veranstaltung_id:'' );
	}
	// Fehlerausgabe 
	echo '<p>'.$Jahresplan->getError().'</p>';
?>
</body>
</html>

