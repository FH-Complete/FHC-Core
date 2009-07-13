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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/*-------------------------------------------------------------------------------------------	
	Bilder aus Tabellen Person oder Veranstaltungskategorie lesen - Selektion Parameter
-------------------------------------------------------------------------------------------*/
// ---------------- CIS Include Dateien einbinden
	require_once('../../../config/cis.config.inc.php');
	
 // ---------------- Datenbank-Verbindung 
	include_once('../../../include/person.class.php');
	include_once('../../../include/benutzer.class.php');

// ---------------- Jahresplan Classe und Allg.Funktionen		
	include_once('../../../include/jahresplan.class.php');
	
	$heximg ='';
	// Es wurde bereits der Hex-String ueber geben	
	if (isset($_REQUEST['heximg']))
	{
	 	$heximg = $_REQUEST['heximg'];
  	}     
	 
	// Veranstaltungskategoriebild
	if (empty($heximg) && isset($_REQUEST['veranstaltungskategorie_kurzbz']))
	{
	
		$Jahresplan = new jahresplan();
		$Jahresplan->InitVeranstaltungskategorie();
		$Jahresplan->show_only_public_kategorie=false;
		$arrTempVeranstaltungskategorie=$Jahresplan->loadVeranstaltungskategorie($_REQUEST['veranstaltungskategorie_kurzbz']);
		if (isset($arrTempVeranstaltungskategorie[0]->bild))	
		{
			$heximg=$arrTempVeranstaltungskategorie[0]->bild;
		}	
	}

	// Personenbild
	if (empty($heximg) && isset($_REQUEST['userUID']))
	{
		$benutzer = new benutzer($_REQUEST['userUID']); // Lesen Person - Benutzerdaten
		if (isset($benutzer->foto))
		{
			$heximg=$benutzer->foto;
		}	
	}
	 
	if (empty($heximg)) // Leeres Images
   	{
	    $heximg ='R0lGODlhHgAKAID/AMDAwAAAACH5BAEAAAAALAAAAAAeAAoAQAIPhI+py+0Po5y02ouz3rwAADs=';
	}
     
	@ob_end_clean();
   	header("Content-type: image/gif");
	exit(jahresplan_hexstr($heximg));


	function jahresplan_hexstr($hex)
	{
	    return base64_decode($hex);
	}

?>
