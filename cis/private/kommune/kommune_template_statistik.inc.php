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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>,
 *          Gerald Simane-Sequens <gerald.simane-sequens@technikum-wien.at>.
 */

#-------------------------------------------------------------------------------------------	
/*
*
* @showStartseite HTML Ausgabe der Wettbewerbe, Eigene Wettbewerbe , Eingeladen zu einem Wettkamp
*
* @param $oWettbewerb 	Objekt zum Wettbewerb, Team, Personen, Match
*
* @return showHTML String mit HTML Ausgabe der Wettbewerbe, Eigene Wettbewerbe 
*
*/
function showStartseite($oWettbewerb,$cTmpMenue='')
{
	// Plausib

	// Initialisierung
	$showHTML='';
	//
	//	 Anzeigenauswahl - Oberesmenue 
	//		wird nur angezeigt wenn Daten fuer die Auswahl vorhanden sind 
	//
	$iTmpAnzahl=(!is_array($oWettbewerb->Wettbewerb) || count($oWettbewerb->Wettbewerb)<1?0:count($oWettbewerb->Wettbewerb)); 
	if ($iTmpAnzahl!=0)
	{
		$cTmpMenue.=(!empty($cTmpMenue)?'&nbsp;|&nbsp;':'');		
		$cTmpMenue.='<a href="#" onclick="hide_layer(\'idWBInformation\');hide_layer(\'idWBListe\');hide_layer(\'idWBUser\');hide_layer(\'idWBEinlad\');hide_layer(\'idWBAufford\');hide_layer(\'idWBSpiele\');show_layer(\'idWBInformation\');">';
			$cTmpMenue.='Information&nbsp;';
		$cTmpMenue.='</a>';
		
		$showHTML.=showMenueFunktion($oWettbewerb,$cTmpMenue);
		$showHTML.='<div id="idWBInformation"><h1>Information</h1>'.showWettbewerbStatistik($oWettbewerb).'</div>';

	}	
	return $showHTML;
}  

#-------------------------------------------------------------------------------------------	
/* Subfunktion von getDisplayStringWettbewerb 
*
* @showWettbewerbStatistik Aufbau einer StatistikListe zu den Wettbewerben
*
* @param $oWettbewerb Array mit allen Wettbewerbs und Benutzerdaten
*
* @return HTML String in Listenform der Wettbewerbe
*
*/
function showWettbewerbStatistik($oWettbewerb)
{

#exit(Test($oWettbewerb->WettbewerbTyp));		
	$showHTML='';
	if (!is_array($oWettbewerb->WettbewerbTyp))
		return $showHTML;
	$showHTML.='<table cellpadding="1" cellspacing="1" summary="Wettbewerbstypen" style="background-color: black;">';
		$showHTML.='<tr>'; 
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Information</th>';
		$showHTML.='</tr>'; 
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->WettbewerbTyp);$iTmpZehler++)
	{
		$cTmpFarbe=(isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"]) && !empty($oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"])?$oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"]:'transparent');
		$oWettbewerb->WettbewerbTyp[$iTmpZehler]["wbtyp_kurzbz"]=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler]["wbtyp_kurzbz"]);
		$showHTML.='<tr>'; 
			$showHTML.='<td style="color:back;background-color: #FFFFB0;border:1px solid #'.$cTmpFarbe.';">'.$oWettbewerb->WettbewerbTyp[$iTmpZehler]['wbtyp_kurzbz'].'</td>';
		$showHTML.='</tr>'; 
	}
	$showHTML.='</table>';	
	$showHTML.='<br />';		
	
	$showHTML='';

	if (!is_array($oWettbewerb->Wettbewerb))
		return $showHTML;		

	$Wettbewerb=new komune_wettbewerbteam($oWettbewerb->sqlCONN,'','',$oWettbewerb->wettbewerb_kurzbz);
	$Wettbewerb->setEncodingSQL($oWettbewerb->clientENCODE);
	$Wettbewerb->setSchemaKommuneSQL($oWettbewerb->sqlSCHEMA);
	// Laden alle Teams	
	$Wettbewerb->InitWettbewerbteam();
	if ($Wettbewerb->loadWettbewerbteam())
		$oWettbewerb->TeamAnwender=$Wettbewerb->getWettbewerbteam();
   	else
		$oWettbewerb->Error[]=$Wettbewerb->getError();
	
	$iTmpAktivste=0;
	$iTmpAktivsteTeam='';
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
		if (empty($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])) // wbtyp_kurzbz=(leer=keine wettbewerbe)
			continue;

		$cSchemaSQL=$Wettbewerb->getSchemaKommuneSQL();		
    	$cTmpSQL="";
		$cTmpSQL.="SELECT tbl_wettbewerbteam.wettbewerb_kurzbz,count(distinct tbl_wettbewerbteam.team_kurzbz) as count_team_kurzbz,max(rang) as max_range,max(punkte) as max_punkte FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam ";
	   	$cTmpSQL.=" WHERE UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])."') ";	
    	$cTmpSQL.=" group by tbl_wettbewerbteam.wettbewerb_kurzbz OFFSET 0 LIMIT 1 ;";	
		
		$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']=array();
       	$Wettbewerb->setResultSQL(null);
	   	if (!$Wettbewerb->fetch_all($cTmpSQL)) 
			$oWettbewerb->Error[]=$Wettbewerb->getError();
		else	
		   	$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']=$Wettbewerb->getResultSQL();

		if (isset($oWettbewerb->Wettbewerb[$iTmpZehler]['teams'][0]))
		   	$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']=$oWettbewerb->Wettbewerb[$iTmpZehler]['teams'][0];
			
#$showHTML.=$cTmpSQL.Test($oWettbewerb->Wettbewerb[$iTmpZehler]['teams']);	

		$cSchemaSQL=$Wettbewerb->getSchemaKommuneSQL();		
	    	$cTmpSQL="";
			$cTmpSQL.="SELECT * FROM ".$oWettbewerb->sqlSCHEMA.".tbl_wettbewerbteam ";
		   	$cTmpSQL.=" WHERE UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])."') ";	
		   	$cTmpSQL.=" AND ( tbl_wettbewerbteam.rang=1 or (tbl_wettbewerbteam.punkte>0 and tbl_wettbewerbteam.punkte=".(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['teams']["punkte"])?$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']["punkte"]:0)." ))";	

	    	$cTmpSQL.=" OFFSET 0 LIMIT 2 ;";	
		
		$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner']=array();
       	$Wettbewerb->setResultSQL(null);
	   	if (!$Wettbewerb->fetch_all($cTmpSQL)) 
			$oWettbewerb->Error[]=$Wettbewerb->getError();
		else	
		   	$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner']=$Wettbewerb->getResultSQL();

#$showHTML.=$cTmpSQL.Test($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner']);	

		if (isset($oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte']) && $iTmpAktivste<$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte'])
		{
			$iTmpAktivste=$oWettbewerb->Wettbewerb[$iTmpZehler]['teams'];
			$iTmpAktivsteTeam=($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]['punkte']==$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte']?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]:(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]['team_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]:array()));
			$iTmpAktivste=array_merge($iTmpAktivste,$iTmpAktivsteTeam,$oWettbewerb->Wettbewerb[$iTmpZehler]);
		}
		
#$showHTML.=$cTmpSQL.Test($iTmpAktivste);	
	}
#exit(Test($iTmpAktivsteTeam));

	if (isset($Wettbewerb)) 
		unset($Wettbewerb);	

	$cShowImage='';
	$pers=kommune_funk_benutzerperson($iTmpAktivsteTeam['team_kurzbz'],$oWettbewerb);
	if (isset($pers->foto_image) && !empty($pers->foto_image))
      		$cShowImage=$pers->foto_image;
		
	$showHTML.='<table cellpadding="1" cellspacing="1" summary="Aktivster Spieler" style="background-color: black;">';
		$showHTML.='<tr>'; 
			$showHTML.='<th colspan="5" style="color:back;background-color:#C0C0C0;">der Aktivste in den Wettbewerben</th>';
		$showHTML.='</tr>'; 


	$cTmpFarbe=(isset($oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"]) && !empty($oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"])?$oWettbewerb->WettbewerbTyp[$iTmpZehler]["farbe"]:'transparent');
	#$oWettbewerb->WettbewerbTyp[$iTmpZehler]["wbtyp_kurzbz"]=trim($oWettbewerb->WettbewerbTyp[$iTmpZehler]["wbtyp_kurzbz"]);

	$cTmpFarbe=(isset($iTmpAktivste["farbe"]) && !empty($iTmpAktivste["farbe"])?$iTmpAktivste["farbe"]:'');

	$showHTML.='<tr>'; 
		$showHTML.='<td style="color:back;background-color: #F7AB66;">Wettbewerb</td>';
		$showHTML.='<td style="color:back;background-color: #F7AB66;">Team / Spieler</td>';
		$showHTML.='<td style="color:back;background-color: #F7AB66;">Rang</td>';
		$showHTML.='<td style="color:back;background-color: #F7AB66;">Punkte</td>';
		$showHTML.='<td style="color:back;background-color: #F7AB66;">Bild</td>';
	$showHTML.='</tr>'; 

	$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>$iTmpAktivste['wettbewerb_kurzbz']),'<img title="weiter  '.$iTmpAktivste['wettbewerb_kurzbz'].'" style="vertical-align: bottom;" alt="open'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;'.$iTmpAktivste['wettbewerb_kurzbz'].'&nbsp;','weiter');

	$showHTML.='<tr>'; 
		$showHTML.='<td style="color:back;background-color: #FFFFB0;border:2px solid #'.$cTmpFarbe.';">'.$iTmpAktivste['wbtyp_kurzbz'].' / '.$cTmpHREF.'</td>';
		$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.$iTmpAktivste['team_kurzbz'].'</td>';
		$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.$iTmpAktivste['rang'].'</td>';
		$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.$iTmpAktivste['max_punkte'].'</td>';
		$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.(!empty($cShowImage)?$cShowImage:'&nbsp;').'</td>';
	$showHTML.='</tr>'; 

	$showHTML.='</table>';	
	$showHTML.='<br />';		
		
		
//-------------------------------------------------------------------------------------------	
	$showHTML.='<table  class="tabcontent" summary="Wettbewerb Statistikdaten">';
		$showHTML.='<tr>'; 

	$showHTML.='<td><table  cellpadding="1" cellspacing="2" summary="Wettbewerb Statistik" style="background-color: black;">';
		$showHTML.='<tr>'; 
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Art</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Wettbewerb</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Teilnehmer</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">max.Punkte</th>';

			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Beste</th>';
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Aktivste</th>';
			
			
			$showHTML.='<th style="color:back;background-color:#C0C0C0;">Forderungen</th>';
		$showHTML.='</tr>'; 

	$cTmpGruppeTyp='';	
	$showHTMLicon='';
	$showHTMLspiele='';
	$showHTMLteams='';
	for ($iTmpZehler=0;$iTmpZehler<count($oWettbewerb->Wettbewerb);$iTmpZehler++)
	{
		#exit(Test($oWettbewerb));
		// Kennzeichen ob ein Record in tbl_wettbewerb angelegt wurde ist wbtyp_kurzbz 
		if (empty($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"])) // wbtyp_kurzbz=(leer=keine wettbewerbe)
		{
#			$showHTML.='<tr><td style="background : White;" colspan="10">Es sind noch keine Gruppen verf&uuml;gbar!</td></tr>'; 
			continue;
		}
		
		$cTmpBesteTeam=@($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]['rang']==1?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]['team_kurzbz']:(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]['team_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]['team_kurzbz']:''));
		$cTmpAktivsteTeam=@($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]['punkte']==$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte']?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][0]['team_kurzbz']:(isset($oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]['team_kurzbz'])?$oWettbewerb->Wettbewerb[$iTmpZehler]['gewinner'][1]['team_kurzbz']:''));		
		
		$cTmpIconPopUpID='icon'.$iTmpZehler;
		$cTmpIconPopUp=' onmouseover="show_layer(\''.$cTmpIconPopUpID.'\');" onmouseout="hide_layer(\''.$cTmpIconPopUpID.'\');" ';

		$cTmpSpielePopUpID='spiele'.$iTmpZehler;
		$cTmpSpielePopUp=' onmouseover="show_layer(\''.$cTmpSpielePopUpID.'\');" onmouseout="hide_layer(\''.$cTmpSpielePopUpID.'\');" ';

		
		$cTmpTeamPopUpID1='sTeam1'.$iTmpZehler;
		$cTmpTeamPopUp1=' onmouseover="show_layer(\''.$cTmpTeamPopUpID1.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID1.'\');" ';
		
		$cTmpTeamPopUpID2='sTeam2'.$iTmpZehler;
		$cTmpTeamPopUp2=' onmouseover="show_layer(\''.$cTmpTeamPopUpID2.'\');" onmouseout="hide_layer(\''.$cTmpTeamPopUpID2.'\');" ';
			
				
		
		$showHTMLicon.='<div style="display:none;z-index:98;" id="'.$cTmpIconPopUpID.'">'.(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"])?$oWettbewerb->Wettbewerb[$iTmpZehler]["icon_image"].'<br />':'').'</div>';
		$showHTMLspiele.='<div style="display:none;z-index:99;" id="'.$cTmpSpielePopUpID.'">'.kommune_funk_show_wettbewerbteam_spiele($oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"],'',$oWettbewerb).'</div>';
		
			$showHTMLteams.='<div style="display:none;position: absolute;z-index:97;" id="'.$cTmpTeamPopUpID1.'"  '.$cTmpTeamPopUp1.' >';
			if (isset($cTmpBesteTeam) )
				$showHTMLteams.=kommune_funk_popup_wettbewerbteam($cTmpBesteTeam,$oWettbewerb,$cTmpTeamPopUpID1.$iTmpZehler,true);
			$showHTMLteams.='</div>';			
			
			$showHTMLteams.='<div style="display:none;position: absolute;z-index:96;" id="'.$cTmpTeamPopUpID2.'"  '.$cTmpTeamPopUp2.' >';
			if (isset($cTmpAktivsteTeam) )
				$showHTMLteams.=kommune_funk_popup_wettbewerbteam($cTmpAktivsteTeam,$oWettbewerb,$cTmpTeamPopUpID2.$iTmpZehler,true);
			$showHTMLteams.='</div>';			

		
		
		$cTmpFarbe=(isset($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]) && !empty($oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"])?$oWettbewerb->Wettbewerb[$iTmpZehler]["farbe"]:'');

		// Wettbewerbstypen - Gruppenwechsel
		$oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]=trim($oWettbewerb->Wettbewerb[$iTmpZehler]["wbtyp_kurzbz"]);
		$showHTML.='<tr>'; 
		
			$cTmpHREF=kommune_funk_create_href(constKommuneAnzeigeWETTBEWERBTEAM,'',array('wettbewerb_kurzbz'=>$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"]),'<img title="weiter  '.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'" style="vertical-align: bottom;" alt="open'.$iTmpZehler.'" src="../../../skin/images/open.gif" border="0" />&nbsp;'.$oWettbewerb->Wettbewerb[$iTmpZehler]["wettbewerb_kurzbz"].'&nbsp;','weiter');
			if ($cTmpGruppeTyp!=$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'])	
				$showHTML.='<td style="color:back;background-color: #FFFFB0;border:1px solid #'.$cTmpFarbe.';">'.$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'].'</td>';
			else
				$showHTML.='<td style="color:back;background-color: #FFFFB0;"></td>';
			$cTmpGruppeTyp=$oWettbewerb->Wettbewerb[$iTmpZehler]['wbtyp_kurzbz'];
			
			$showHTML.='<td '.$cTmpIconPopUp.' style="color:back;background-color: #FFFFB0;border:2px solid #'.$cTmpFarbe.';">'.$cTmpHREF.'</td>';

			$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.@(int)$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['count_team_kurzbz'].'</td>';
			$showHTML.='<td style="color:back;background-color: #FFFFB0;">'.@(int)$oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte'].'</td>';
			
			
			$showHTML.='<td '.$cTmpTeamPopUp1.' style="color:back;background-color: #FFFFB0;">'.$cTmpBesteTeam.'</td>';
			$showHTML.='<td '.$cTmpTeamPopUp2.' style="color:back;background-color: #FFFFB0;">'.$cTmpAktivsteTeam.'</td>';
			
			
			if (!isset($oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte']) || $oWettbewerb->Wettbewerb[$iTmpZehler]['teams']['max_punkte']<1)
				$showHTML.='<td '.$cTmpSpielePopUp.'  style="color:back;background-color: #FFFFB0;">keine Forderungen</td>';
			else
				$showHTML.='<td '.$cTmpSpielePopUp.' style="color:back;background-color: #FFFFB0;"><b>Forderungen</b></td>';


		$showHTML.='</tr>'; 
	}
		$showHTML.='</table></td>';
		$showHTML.='<td>'.$showHTMLicon.$showHTMLspiele.$showHTMLteams.'</td>'; 
	$showHTML.='</tr>'; 

	$showHTML.='</table>';	

	
	$showHTML.='<br /><br /><div style="text-align:center;" class="home_logo">&nbsp;</div>'; 
	return $showHTML;                            
}
?>