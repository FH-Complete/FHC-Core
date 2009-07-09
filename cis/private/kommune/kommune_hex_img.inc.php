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

// ---------------- Check DB ist Online, und eine Verbindung ist moeglich
    //Hex Dump Blank als Default
    $heximg ='';


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
     elseif(isset($oWettbewerb->team_kurzbz) && !empty($oWettbewerb->team_kurzbz))
     {
	  // WettbewerbTeam Classe initialisieren
		$selBILD=2;	 
	   	$WettbewerbTeam= new komune_wettbewerbteam('',$oWettbewerb->team_kurzbz,$oWettbewerb->wettbewerb_kurzbz);
		if ($WettbewerbTeam->loadWettbewerbteam())
			$arrTempWettbewerbTeam=$WettbewerbTeam->result;
    		else
  			exit($WettbewerbTeam->errormsg);	
		if (isset($arrTempWettbewerbTeam[0]->logo))	
			$heximg=$arrTempWettbewerbTeam[0]->logo;
     }
     elseif(isset($oWettbewerb->wettbewerb_kurzbz) && !empty($oWettbewerb->wettbewerb_kurzbz))
     {
		$selBILD=3;	 
		$Wettbewerb= new komune_wettbewerb($oWettbewerb->wbtyp_kurzbz,$oWettbewerb->wettbewerb_kurzbz);
		if ($Wettbewerb->loadWettbewerb())
			$arrTempWettbewerbTeam=$Wettbewerb->result;
		else
			exit($Wettbewerb->errormsg);
		if (isset($arrTempWettbewerbTeam[0]->icon))
			$heximg=$arrTempWettbewerbTeam[0]->icon;
     }
     else 
     {
		$selBILD=4;	 
	 	$heximg = (isset($_REQUEST['heximg']) ? $_REQUEST['heximg'] : '');
     }     
#exit($selBILD.Test($oWettbewerb));		 
     if (empty($heximg))
	    $heximg ='4749463839611e000a0080ff00c0c0c000000021f90401000000002c000000001e000a0040020f848fa9cbed0fa39cb4da8bb3debc00003b';

   	@ob_end_clean();
   	header("Content-type: image/gif");
	exit(kommune_hexstr($heximg));
       
