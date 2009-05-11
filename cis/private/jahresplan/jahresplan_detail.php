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
	$userUID=(isset($_REQUEST['userUID']) ? $_REQUEST['userUID'] :get_uid());
	if (empty($userUID))
	{
		die('Es wurde keine Benutzer UID gefunden ?');
	}
	// Parameter Veranstaltungskategorie
  	$veranstaltung_kurzbz=trim((isset($_REQUEST['veranstaltungskategorie_kurzbz']) ? $_REQUEST['veranstaltungskategorie_kurzbz']:''));
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
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Veranstaltungsdetail ID <?php echo $veranstaltung_id.' - '.$userNAME;?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<script language="JavaScript" type="text/javascript">
	<!--
		if (window.opener) {
				window.resizeTo(500,600);
			}
	-->
	</script>				
	<link href="../../../skin/style.css.php" rel="stylesheet" type="text/css">
	<style type="text/css">
	<!-- 
		/* Kategorien Abrundungen im Detail */
		b.rtop, b.rbottom{display:block;background: transparent;}
		b.rtop b, b.rbottom b{display:block;height: 1px; overflow: hidden; background: #E5E5E5;}
		b.r1{margin: 0 5px}
		b.r2{margin: 0 3px}
		b.r3{margin: 0 2px}
		b.rtop b.r4, b.rbottom b.r4{margin: 0 1px;height: 2px}
		
		.footer_zeile {color: silver;}
		@media print {
			.userinfo {display:none;}		
		}	
	-->
	</style>
</head>
<body>
<?php
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige der
//	 Veranstaltungen mit ID Selektionsbedingung lesen
// ------------------------------------------------------------------------------------------

	$Jahresplan = new jahresplan($conn);
	$Jahresplan->InitVeranstaltung();
	// Nur Berechtigte duerfen alle Informationen sehen (Mitarbeiter)	
	$Jahresplan->setVeranstaltungskategorieMitarbeiter($is_lector);
	// Nur Berechtigte duerfen auch noch nicht freigegebene Sehen	

	if (!$is_wartungsberechtigt)	
		$Jahresplan->setFreigabe(true);
	else
		$Jahresplan->setFreigabe(false);
		
		
	$Jahresplan->setVeranstaltung_id($veranstaltung_id);
	$veranstaltung=array();
	if ($Jahresplan->loadVeranstaltung())
	{
		$veranstaltung=$Jahresplan->getVeranstaltung();
	}

	// Ausgabe der Veranstaltungsdaten bzw Hinweisstext
	if (is_array($veranstaltung) && isset($veranstaltung[0]))
	{
		echo jahresplan_veranstaltung_detailanzeige($conn,$veranstaltung,$is_wartungsberechtigt);
	}
	else
	{
		echo '<h1>Veranstaltungsdetail ID '.$veranstaltung_id.' wurde nicht gefunden!</h1>';
		echo $Jahresplan->getError();

	}	
?>
</body>
</html>