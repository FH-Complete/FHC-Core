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

// Holt den Hexcode eines Bildes aus der DB wandelt es in Zeichen
// um und gibt das ein Bild zurueck.
// Aufruf mit <img src='kommune_hex_img.php?src=frage&frage_id=1

function createIMGfromHEX($oWettbewerb)
{
// ---------------- Check DB ist Online, und eine Verbindung ist moeglich
    //Hex Dump Blank als Default
    $heximg ='';

    $wbtyp_kurzbz = (isset($_REQUEST['wbtyp_kurzbz']) ? $_REQUEST['wbtyp_kurzbz'] : '');
    $team_kurzbz = (isset($_REQUEST['team_kurzbz']) ? $_REQUEST['team_kurzbz'] : '');
    $wettbewerb_kurzbz = (isset($_REQUEST['wettbewerb_kurzbz']) ? $_REQUEST['wettbewerb_kurzbz'] : '');
	
    $personen_id = (isset($_REQUEST['src']) ? $_REQUEST['src'] : '');
    if (empty($personen_id)) $personen_id = (isset($_REQUEST['person']) ? $_REQUEST['person'] : '');
    if (empty($personen_id)) $personen_id = (isset($_REQUEST['personid']) ? $_REQUEST['personid'] : '');
    if (empty($personen_id)) $personen_id = (isset($_REQUEST['person_id']) ? $_REQUEST['person_id'] : '');
    $person_id=trim($personen_id);

	
	
     $selBILD=0;	
     if (isset($personen_id) && !empty($personen_id))
     {
		$selBILD=1;	 
		$pers = new person($personen_id); // Lesen PersonenBenutzer
		if (isset($pers->uid) && !isset($oWettbewerb->PersonenBenutzer[$pers->uid]))
			$oWettbewerb->PersonenBenutzer[$pers->uid]=$pers;
		if (isset($pers->foto))
			$heximg=$pers->foto;
			
     }
     elseif(isset($team_kurzbz) && !empty($team_kurzbz))
     {
		$selBILD=2;	 
		if (isset($oWettbewerb->TeamBenutzer[$team_kurzbz][0]['logo']))
			$heximg=$oWettbewerb->TeamBenutzer[$team_kurzbz][0]['logo'];
		else
		{
		  // WettbewerbTeam Classe initialisieren
		   	$WettbewerbTeam= new komune_wettbewerbteam('',$oWettbewerb->team_kurzbz,$oWettbewerb->wettbewerb_kurzbz);
			if ($WettbewerbTeam->loadWettbewerbteam())
				$arrTempWettbewerbTeam=$WettbewerbTeam->getWettbewerbteam();
    			else
	  			exit($WettbewerbTeam->errormsg);	
				
			if (isset($arrTempWettbewerbTeam[0]['logo']))	
				$heximg=$arrTempWettbewerbTeam[0]['logo'];
		}		
     }
     elseif(isset($wettbewerb_kurzbz) && !empty($wettbewerb_kurzbz))
     {
		$selBILD=3;	 
		if (isset($oWettbewerb->Wettbewerb[0]))
			$heximg=$oWettbewerb->Wettbewerb[0]['icon'];
		elseif (isset($oWettbewerb->Wettbewerb[$wettbewerb_kurzbz]))
			$heximg=$oWettbewerb->Wettbewerb[$wettbewerb_kurzbz]['icon'];
		else
		{
			$Wettbewerb= new komune_wettbewerb($oWettbewerb->wbtyp_kurzbz,$oWettbewerb->wettbewerb_kurzbz);
			if ($Wettbewerb->loadWettbewerb())
				$arrTempWettbewerbTeam=$Wettbewerb->getWettbewerb();
			else
				exit($Wettbewerb->getError());
			if (isset($arrTempWettbewerbTeam[0]['icon']))
				$heximg=$arrTempWettbewerbTeam[0]['icon'];
		}				
     }
     else 
     {
		$selBILD=4;	 
	 	$heximg = (isset($_REQUEST['heximg']) ? $_REQUEST['heximg'] : '');
     }     
#exit($selBILD.Test($oWettbewerb));		 
     if (empty($heximg))
	    $heximg ='R0lGODlhHgAKAID/AMDAwAAAACH5BAEAAAAALAAAAAAeAAoAQAIPhI+py+0Po5y02ouz3rwAADs=';
		
   	@ob_end_clean();
   	header("Content-type: image/gif");
	exit(kommune_hexstr($heximg));
}       
?>