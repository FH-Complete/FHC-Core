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
 #-------------------------------------------------------------------------------------------	
/* 
*
* @loadIMGfromHEX speichert ein upload File in der Datenbank in eine bestimmte Tabelle
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return HTML Statusinformation
*
*/
function loadIMGfromHEX($oJahresplan)
{
	// Init
	$showHTML='';
	
   	if (!isset($_REQUEST['table'])
   	|| empty($_REQUEST['table']) )
		exit('Es wurde keine Table( Post Name ) angegeben oder ist leer !');
	
	$cTmpTable=trim($_REQUEST['table']);

	$cTmpClassFunktion='getStrucktur'.$cTmpTable;
	$arrTmpTableStrucktur=$oJahresplan->classJahresplan->$cTmpClassFunktion();
	
	if (!is_array($arrTmpTableStrucktur))
		exit("Die Table $cTmpTable wurde in der Datenbank nicht gefunden - Keine Strucktur vorhanden !");
	if (!isset($arrTmpTableStrucktur[0]['name']))
		exit('Tabellen Struktur '.$cTmpTable.' ist falsch ! Es gibt kein Array Item "name". ');
 
	$arrTmpTable=array();	
	$cTmpCounter=-1;
	while (@list ($tmp_key, $tmp_val) = @each ($_FILES)) 
	{
		$cTmpCounter++; 
			
		$filename=$tmp_val['tmp_name'];
		if (empty($filename))
			continue;

		//File oeffnen
		$fp = fopen($filename,'r');
		//auslesen
		$content = fread($fp, filesize($filename));
		$_REQUEST['heximg']=jahresplan_strhex($content);
		fclose($fp);
		if (isset($fp)) 
		  unset($fp);			
		
		$arrTmpTable=array();	
		reset($arrTmpTableStrucktur);		
		for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
		{				
			$cTmpTablenFeld=$arrTmpTableStrucktur[$fildIND]['name'];
			if ($cTmpTablenFeld=='bild' || $cTmpTablenFeld=='icon' || $cTmpTablenFeld=='logo')
				$arrTmpTable[$cTmpTablenFeld]=$_REQUEST['heximg'];
			else
				$arrTmpTable[$cTmpTablenFeld]=(isset($_REQUEST[$cTmpTablenFeld.$cTmpCounter]) ? $_REQUEST[$cTmpTablenFeld.$cTmpCounter]:'');
			$cTmpTablenFeld.='_old';
			if (isset($_REQUEST[$cTmpTablenFeld.$cTmpCounter]))
				$arrTmpTable[$cTmpTablenFeld]=(isset($_REQUEST[$cTmpTablenFeld.$cTmpCounter]) ? $_REQUEST[$cTmpTablenFeld.$cTmpCounter]:'');
		}
		
		// kein Bild zur Tabelle gefunden
		if (!is_array($arrTmpTable) || count($arrTmpTable)<1)
			continue;
					
		//  in DB Schreiben, und Ende mit Anzeige				
			if (!$veranstaltungskategorie=$oJahresplan->classJahresplan->saveVeranstaltungskategorie($arrTmpTable))
				$oJahresplan->Error=$oJahresplan->classJahresplan->getError();
			else
			{
				$oJahresplan->Error[]=$tmp_val['name'].' gespeichert';
				jahresplan_funk_veranstaltungskategorie_load_kpl($oJahresplan);					
			}	
		return $showHTML;
	}
	return $showHTML;
}
#-------------------------------------------------------------------------------------------	
/* 
*
* @createIMGfromHEX auslesen eines Bildes aus der Datenbank (HEXwerte) zu einem Parameter
*
* @param $oJahresplan Objekt mit allen Daten zur Selektion wie Veranstaltungskategorie
*
* @return - Anzeige des Bild 
*
*/

function createIMGfromHEX($oJahresplan)
{
// ---------------- Check DB ist Online, und eine Verbindung ist moeglich
    //Hex Dump Blank als Default
    $heximg ='';
    $selBILD=0;	

#exit(Test($oJahresplan));

    if (isset($oJahresplan->person_id) && !empty($oJahresplan->person_id))
    {
		$selBILD=1;	 
		$pers = new person($oJahresplan->oConn,$oJahresplan->person_id); // Lesen PersonenBenutzer
		if (isset($pers->foto))
			$heximg=$pers->foto;
			
     }
     elseif(isset($oJahresplan->veranstaltungskategorie_kurzbz) && !empty($oJahresplan->veranstaltungskategorie_kurzbz))
     {
		$selBILD=2;	 
		if (isset($oJahresplan->veranstaltungskategorie_key[$oJahresplan->veranstaltungskategorie_kurzbz]['bild']))
			$heximg=$oJahresplan->veranstaltungskategorie_key[$oJahresplan->veranstaltungskategorie_kurzbz]['bild'];
		else
		{
			$oJahresplan->classJahresplan->InitVeranstaltungskategorie();
			$oJahresplan->classJahresplan->setVeranstaltungskategorie_kurzbz($oJahresplan->veranstaltungskategorie_kurzbz);
			if (!$arrTempVeranstaltungskategorie=$oJahresplan->classJahresplan->loadVeranstaltungskategorie())
				return $oJahresplan->Error=$oJahresplan->classJahresplan->getError();	

			if (isset($arrTempVeranstaltungskategorie[0]['bild']))	
				$heximg=$arrTempVeranstaltungskategorie[0]['bild'];
		}	
     }
     else 
     {
		$selBILD=99;	 
	 	$heximg = (isset($_REQUEST['heximg']) ? $_REQUEST['heximg'] : '');
     }     
#exit($selBILD.Test($oWettbewerb));		 
     if (empty($heximg))
	    $heximg ='4749463839611e000a0080ff00c0c0c000000021f90401000000002c000000001e000a0040020f848fa9cbed0fa39cb4da8bb3debc00003b';
   	@ob_end_clean();
   	header("Content-type: image/gif");
	exit(jahresplan_hexstr($heximg));
}       
?>
