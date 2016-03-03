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
*	Veranstaltungsdaten - Pflege
*
*		Aktionen: Anzeige, Anlage, Aenderung und Loeschen
*		Ansicht : Voll oder Popup (window.opener)	
*
*		Zusatz : Reservierungsinformationen 
*				koennen im Veranstaltungszeitraum dazu gefuegt werden 
*		
*
*/
 
// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');
// ---------------- Allg.Funktionen
	require_once('../../../include/functions.inc.php');
// ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');
	include_once('../../../include/benutzerberechtigung.class.php');
	
// ------------------------------------------------------------------------------------------
//	Jahresplan Classe 
// ------------------------------------------------------------------------------------------
	include_once('../../../include/jahresplan.class.php');
// ---------------- Check User und Jahresplan-Classe Init
	include_once('jahresplan_funktionen.inc.php');
 	
// ------------------------------------------------------------------------------------------
//	Init
// ------------------------------------------------------------------------------------------
	$error='';	
 
// ------------------------------------------------------------------------------------------
//	Request Parameter 
// ------------------------------------------------------------------------------------------
	// Parameter Veranstaltung
   	$veranstaltung_id=trim((isset($_REQUEST['veranstaltung_id']) ? $_REQUEST['veranstaltung_id']:''));
 	if (empty($veranstaltung_id))
		die ("Keine Veranstaltungs ID gew&auml;hlt ! ");
// ------------------------------------------------------------------------------------------
// Datenlesen fuer Anzeige der
//	 Veranstaltungen mit ID Selektionsbedingung lesen
// ------------------------------------------------------------------------------------------
	$Jahresplan->InitVeranstaltung();	
	$Jahresplan->veranstaltung_id=$veranstaltung_id;
	if (!$veranstaltung=$Jahresplan->loadVeranstaltung())
		die($Jahresplan->errormsg);
	
?> 
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $p->t("eventkalender/veranstaltungsdetailID");?> <?php echo $veranstaltung_id.' - '.$userNAME;?> </title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<script language="JavaScript" type="text/javascript">
	<!--
		if (window.opener) 
		{
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
	// Ausgabe der Veranstaltungsdaten bzw Hinweisstext
	if (is_array($veranstaltung) && isset($veranstaltung[0]))
	{
		echo jahresplan_veranstaltung_detailanzeige($veranstaltung,$is_wartungsberechtigt);
	}
	else
	{
		echo '<h1>'.$p->t('eventkalender/veranstaltungIdXYwurdeNichtGefunden',array($veranstaltung_id)).'!</h1>';
		echo $Jahresplan->errormsg;

	}	
?>
</body>
</html>
