<?php
//-------------------------------------------------------------------------------------------------
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
#--------------------------------------------------------------------------------------------------
/*
*
* @classe kommune_wettbewerbeinladungen 
*
* @param connectSQL Datenbankverbindung
* @param team_kurzbz Einladung zum Wettbewerb
* @param team_gefordert Einladung zum Wettbewerb
* @param wettbewerb_kurzbz Wettbewerbssubgruppen Key 
*
* @return - kein Retourn des Konstruktors
*
*/
include_once(dirname(__FILE__)."/postgre_sql.class.php"); 
class komune_wettbewerbeinladungen extends postgre_sql
{
       protected $wettbewerbeinladungen="";
	
       protected $uid="";
	
       protected $match_id="";
       protected $wettbewerb_kurzbz="";

       protected $team_kurzbz="";
       protected $team_gefordert="";

       protected $gefordertvon="";
       protected $gefordertamum="";	   

       protected $matchdatumzeit="";
       protected $matchort="";

       protected $bestaetigtvon="";
       protected $bestaetigtamum="";

       protected $ergebniss="";
       protected $team_sieger="";	   

       protected $matchbestaetigtamum="";
       protected $matchbestaetigtvon="";
	
       protected $switchGewinner='';	   
		
//-----Konstruktor       
       function komune_wettbewerbeinladungen($connectSQL,$match_id="",$team_forderer="",$team_gefordert="",$wettbewerb_kurzbz="",$uid="") 
       {
           $this->InitWettbewerbeinladungen();   

           $this->setConnectSQL($connectSQL);   
           $this->setMatch_id($match_id);
           $this->setTeam_forderer($team_forderer);
           $this->setTeam_gefordert($team_gefordert);
           $this->setWettbewerb_kurzbz($wettbewerb_kurzbz);
           $this->setGefordertvon($uid);
       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerbeinladungen() 
       {
		$this->setError('');
		// Ergebniss-Liste der Spiele
       	$this->setWettbewerbeinladung('');
		// Liste der Spiele mit Ergebniss "True" , oder Ohne "False"
		$this->setSwitchGewinner('');
	
		// Step 1	
	       $this->setMatch_id('');
		$this->setWettbewerb_kurzbz('');

      		$this->setGefordertvon(''); 
		$this->setGefordertamum('');		

		$this->setTeam_kurzbz('');
		$this->setTeam_gefordert('');

		$this->setMatchdatumzeit('');
		$this->setMatchort('');
		// Step 2		
		$this->setBestaetigtvon('');
		$this->setBestaetigtamum(0);
		// Step 3		
		$this->setErgebniss('');
		$this->setTeam_sieger('');		
		// Step 4		
		$this->setMatchbestaetigtamum('');
		$this->setMatchbestaetigtvon('');
       }
//-----Wettbewerb Matchdaten--------------------------------------------------------------------------------------------
       function getWettbewerbeinladung() 
       {
           return $this->wettbewerbeinladung;
       }
       function setWettbewerbeinladung($wettbewerbeinladung) 
       {
           $this->wettbewerbeinladung=$wettbewerbeinladung;
       }
//-----match_id--------------------------------------------------------------------------------------------
       function getMatch_id() 
       {
           return $this->match_id;
       }
       function setMatch_id($match_id) 
       {
           $this->match_id=$match_id;
       }
//-----gefordertvon--------------------------------------------------------------------------------------------
       function getGefordertvon() 
       {
           return $this->gefordertvon;
       }
       function setGefordertvon($gefordertvon) 
       {
           $this->gefordertvon=$gefordertvon;
       }

//-----UID--------------------------------------------------------------------------------------------
// Match - Wettbewerb uid = Moderator
       function getUid() 
       {
           return $this->uid;
       }
       function setUid($uid) 
       {
           $this->uid=$uid;
       }

//-----gefordertam--------------------------------------------------------------------------------------------
       function getGefordertamum() 
       {
           return $this->gefordertamum;
       }
       function setGefordertamum($gefordertamum) 
       {
           $this->gefordertamum=$gefordertamum;
       }
//-----team_forderer--------------------------------------------------------------------------------------------
       function getTeam_forderer() 
       {
           return $this->team_forderer;
       }
       function setTeam_forderer($team_forderer) 
       {
           $this->team_forderer=$team_forderer;
       }
//-----team_kurzbz--kompilitaet------------------------------------------------------------------------------------------
       function getTeam_kurzbz() 
       {
           return $this->getTeam_forderer();
       }
       function setTeam_kurzbz($team_kurzbz) 
       {
           $this->setTeam_forderer($team_kurzbz);
       }
	
//-----team_gefordert--------------------------------------------------------------------------------------------
       function getTeam_gefordert() 
       {
           return $this->team_gefordert;
       }
       function setTeam_gefordert($team_gefordert) 
       {
           $this->team_gefordert=$team_gefordert;
       }	   
//-----team_sieger--------------------------------------------------------------------------------------------
       function getTeam_sieger() 
       {
           return $this->team_sieger;
       }
       function setTeam_sieger($team_sieger) 
       {
           $this->team_sieger=$team_sieger;
       }	   
//-----wettbewerb_kurzbz--------------------------------------------------------------------------------------------
       function getWettbewerb_kurzbz() 
       {
           return $this->wettbewerb_kurzbz;
       }
       function setWettbewerb_kurzbz($wettbewerb_kurzbz="") 
       {
           $this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
       }
//-----matchdatumzeit--------------------------------------------------------------------------------------------
       function getMatchdatumzeit() 
       {
           return $this->matchdatumzeit;
       }
       function setMatchdatumzeit($matchdatumzeit) 
       {
           $this->matchdatumzeit=$matchdatumzeit;
       }	 	   
//-----matchort--------------------------------------------------------------------------------------------
       function getMatchort() 
       {
           return $this->matchort;
       }
       function setMatchort($matchort) 
       {
           $this->matchort=$matchort;
       }	
//-----ergebniss--------------------------------------------------------------------------------------------
       function getErgebniss() 
       {
           return $this->ergebniss;
       }
       function setErgebniss($ergebniss) 
       {
           $this->ergebniss=$ergebniss;
       }	
//-----bestaetigtvon--------------------------------------------------------------------------------------------
       function getBestaetigtvon() 
       {
           return $this->bestaetigtvon;
       }
       function setBestaetigtvon($bestaetigtvon) 
       {
           $this->bestaetigtvon=$bestaetigtvon;
       }	
//-----bestaetigtamum--------------------------------------------------------------------------------------------
       function getBestaetigtamum() 
       {
           return $this->bestaetigtamum;
       }
       function setBestaetigtamum($bestaetigtamum) 
       {
           $this->bestaetigtamum=$bestaetigtamum;
       }	
//-----matchbestaetigtamum--------------------------------------------------------------------------------------------
       function getMatchbestaetigtamum() 
       {
           return $this->matchbestaetigtamum;
       }
       function setMatchbestaetigtamum($matchbestaetigtamum) 
       {
           $this->matchbestaetigtamum=$matchbestaetigtamum;
       }	 	   
//-----matchbestaetigtvon--------------------------------------------------------------------------------------------
       function getMatchbestaetigtvon() 
       {
           return $this->matchbestaetigtvon;
       }
       function setMatchbestaetigtvon($matchbestaetigtvon) 
       {
           $this->matchbestaetigtvon=$matchbestaetigtvon;
       }	
//-----switchGewinner--------------------------------------------------------------------------------------------
// Selektion des Datenlesen 0 nur nicht Gewonnene, 1 sind alle Gewonnene , leer alle
       function getSwitchGewinner() 
       {
           return $this->switchGewinner;
       }
       function setSwitchGewinner($switchGewinner) 
       {
           $this->switchGewinner=$switchGewinner;
       }
	
//-------------------------------------------------------------------------------------------------
       function saveWettbewerbeinladung($team_forderer="",$team_gefordert="",$match_id="")
       {
		// Initialisieren
		$this->setError('');
		// Konstante
		$constTableMatch='tbl_match';
		
		// Parameteruebernahme
		if (!empty($team_forderer)) 
			$this->setTeam_forderer($team_forderer);
		if (!empty($team_gefordert)) 
			$this->setTeam_gefordert($team_gefordert);
		if (!empty($match_id)) 
			$this->setMatch_id($match_id);

		// Verarbeitungsvariablen	
		$cSchemaSQL=$this->getschemaSQL();
  	   	$cMatch_id=$this->getMatch_id();			
       	$cTeam_forderer=$this->getTeam_forderer();			
       	$cTeam_gefordert=$this->getTeam_gefordert();		
			
		// Plausib - Pruefen ob Eingeladente Team nicht als Array ubergeben wurde ( gebraucht wird nur die Kurzbezeichnung)
	       if (is_array($cTeam_forderer) && isset($cTeam_forderer['team_forderer']))		
		      $cTeam_forderer=$cTeam_forderer['team_forderer'];		
	       elseif (is_array($cTeam_forderer) && isset($cTeam_forderer[0]['team_forderer']) )		
		      $cTeam_forderer=$cTeam_forderer[0]['team_forderer'];		
	       elseif (is_array($cTeam_forderer) && isset($cTeam_forderer['team_forderer']))		
		      $cTeam_forderer=$cTeam_forderer['team_kurzbz'];		
	       elseif (is_array($cTeam_forderer) && isset($cTeam_forderer[0]['team_forderer']) )		
		      $cTeam_forderer=$cTeam_forderer[0]['team_forderer'];		
		
		// Plausib - Pruefen ob Eingeladene Team nicht als Array ubergeben wurde ( gebraucht wird nur die Kurzbezeichnung)
	       if (is_array($cTeam_gefordert) && isset($cTeam_gefordert['team_kurzbz']))		
		      $cTeam_gefordert=$cTeam_gefordert['team_kurzbz'];		
	       elseif (is_array($cTeam_gefordert) && isset($cTeam_gefordert[0]['team_kurbz']))		
		      $cTeam_gefordert=$cTeam_gefordert[0]['team_kurzbz'];		
		
		$cTeam_forderer=trim($cTeam_forderer);	  
		if (empty($cTeam_forderer) )
		{
			$this->setError('Kein Einladung (Einladenter fehlt) m&ouml;glich !');
			return false;
		}	
		$cTeam_gefordert=trim($cTeam_gefordert);
		if (empty($cTeam_gefordert) )
		{
			$this->setError('Kein Einladung (Eingeladener fehlt) m&ouml;glich !');
			return false;
		}	
		$cMatch_id=trim($cMatch_id);
		if (empty($cTeam_gefordert) && empty($cMatch_id) )
		{
			$this->setError('Kein Einladung (Bearbeitung) m&ouml;glich !');
			return false;
		}	

	    $this->setTableStruckturSQL($constTableMatch);
		if (!$origWettbewerbeinladungen=$this->loadWettbewerbeinladungen())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	
		
		$arrTmpTableStrucktur=$this->getTableStruckturSQL();		
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableMatch.'") gefunden !');
			return false;
		}	

    		$cTmpSQL="BEGIN;  ";
		if ($this->getNewRecord()) // Neuanlage - Insert 
		{
			if (!$this->getGefordertamum())
				$this->setGefordertamum(time());
					
			$fildsList="";
			$fildsValue="";
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				
				$cTmpWert='';
				if (isset($this->$arrTmpTableStrucktur[$fildIND]['name']) && $this->$arrTmpTableStrucktur[$fildIND]['name']!='')
					$cTmpWert=$this->$arrTmpTableStrucktur[$fildIND]['name'];
				if ($cTmpWert!='')
				{	
					switch ($arrTmpTableStrucktur[$fildIND]['type']) 
					{
			        case 'timestamp':
						$cTmpWert="to_timestamp('".$cTmpWert."')";						
			            break;		
			        case 'time':
						$cTmpWert="to_timestamp('".$cTmpWert."')";						
			            break;					
      				 default:
						$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
			          	break;
				    }						
					$fildsList.=(!empty($fildsList)?',':'').$arrTmpTableStrucktur[$fildIND]['name'];	
					$fildsValue.=(!empty($fildsValue)?',':'').$cTmpWert;
				}	
			}
       		$cTmpSQL=" insert into ".$cSchemaSQL."tbl_match (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			if (!$this->getMatch_id())
				$this->setMatch_id($origWettbewerbeinladungen[0]['match_id']);
			$cTmpSQL.=" update ".$cSchemaSQL."tbl_match set ";
			$fildsValue='';
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				$cTmpWert='';
				if (isset($this->$arrTmpTableStrucktur[$fildIND]['name']) 
  				&& $this->$arrTmpTableStrucktur[$fildIND]['name']!='' 
				&& $arrTmpTableStrucktur[$fildIND]['name']!='match_id' )
				{				
					$cTmpWert=$this->$arrTmpTableStrucktur[$fildIND]['name'];
					if ($cTmpWert!='')
					{
						switch ($arrTmpTableStrucktur[$fildIND]['type']) 
						{
					        case 'timestamp':
								$cTmpWert="to_timestamp('".$cTmpWert."')";						
			    	  	       	break;		
					        case 'time':
								$cTmpWert="to_timestamp('".$cTmpWert."')";						
					            break;					
	      					 default:
								$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
				          		break;
						}	
						$fildsValue.= (!empty($fildsValue)?',':'').$arrTmpTableStrucktur[$fildIND]['name']."=".$cTmpWert;
					}
			       }						
			}
			$cTmpSQL.=$fildsValue." where tbl_match.match_id='".$this->getMatch_id()."'; ";  
			$cWettbewerb_kurzbz=trim($origWettbewerbeinladungen[0]['wettbewerb_kurzbz']);
			$origWettbewerbeinladungen[0]['matchbestaetigtvon']=trim($origWettbewerbeinladungen[0]['matchbestaetigtvon']);
			$origWettbewerbeinladungen[0]['team_sieger']=trim($origWettbewerbeinladungen[0]['team_sieger']);

			if ($this->getMatchbestaetigtvon() && empty($origWettbewerbeinladungen[0]['matchbestaetigtvon'])
			&& trim($origWettbewerbeinladungen[0]['team_sieger'])==trim($cTeam_forderer))  // Der Forderer ist der Siehter den Rangtauschen
			{
			// Rang des Geforderten ermitteln
				$cTmpSQL_tmp="select rang from ".$cSchemaSQL."tbl_wettbewerbteam where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."')   FOR UPDATE ;";
			       $this->fetch_object($cTmpSQL_tmp);
				$iTmpRangGeforderter=$this->getResultSQL();
				if (isset($iTmpRangGeforderter->rang))
					$iTmpRangGeforderter=trim($iTmpRangGeforderter->rang);
					
				// Rang des Geforderten ermitteln
				$cTmpSQL_tmp="select rang from ".$cSchemaSQL."tbl_wettbewerbteam where upper(team_kurzbz)=upper('".$cTeam_forderer."') and  upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."')   FOR UPDATE ;";
		
			       $this->fetch_object($cTmpSQL_tmp);
				$iTmpRangFrorderer=$this->getResultSQL();
				if (isset($iTmpRangFrorderer->rang))
					$iTmpRangFrorderer=trim($iTmpRangFrorderer->rang);
				
				$cTmpSQL.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$cTmpSQL.="set rang=0 ";
				$cTmpSQL.=" where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."'); ";

				$cTmpSQL.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$cTmpSQL.="set rang=".$iTmpRangGeforderter;
				$cTmpSQL.=" where upper(team_kurzbz)=upper('".$cTeam_forderer."') and upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."'); ";

				$cTmpSQL.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$cTmpSQL.="set rang=".$iTmpRangFrorderer;
				$cTmpSQL.=" where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."'); ";

				$cTmpSQL.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$cTmpSQL.="set punkte=3+punkte ";
				$cTmpSQL.="where upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."') and upper(team_kurzbz)=upper('".$origWettbewerbeinladungen[0]['team_sieger']."'); ";

				$cTmpSQL.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$cTmpSQL.="set punkte=1+punkte ";
				$cTmpSQL.="where upper(wettbewerb_kurzbz)=upper('".$cWettbewerb_kurzbz."') and upper(team_kurzbz)=upper('". ($origWettbewerbeinladungen[0]['team_sieger']==$cTeam_gefordert?$cTeam_forderer:$cTeam_gefordert)."'); ";
			}	
		}				
		$cTmpSQL.=" COMMIT; ";       
#exit($cTmpSQL);
   // Datenbankabfrage
            	$this->setStringSQL($cTmpSQL);
      	   	unset($cTmpSQL);

            	$this->setResultSQL(null);
		if (!$this->dbQuery())
	      		return false;
		if (!$origWettbewerbeinladungen=$this->loadWettbewerbeinladungen())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	
		$this->setResultSQL(null);       
		return $origWettbewerbeinladungen;
	}

//-------------------------------------------------------------------------------------------------
       function loadWettbewerbeinladungen()
       {
		$this->setError('');
					  
       	$cSchemaSQL=$this->getschemaSQL();
            	
		$match_id=$this->getMatch_id();
		$cTeam_forderer=$this->getTeam_forderer();			
		$cTeam_kurzbz_einladungen=$this->getTeam_gefordert();			
		$cWettbewerb_kurzbz=$this->getWettbewerb_kurzbz();
		$cGefordertvon=$this->getGefordertvon();

		$bSwitchGewinner=$this->getSwitchGewinner();
		
		#SELECT TIMESTAMP WITHOUT TIME ZONE 'epoch' + 982384720 * INTERVAL '1 second';
		#select to_char(TIMESTAMP '2007-03-27 10:48:50.022', 'DD.MM.YYYY');
		#select to_char(TIMESTAMP '2007-03-27 10:48:50.022', 'HH24:MI:SS');
	    	$cTmpSQL="";
		$cTmpSQL.="SELECT * ";

			$cTmpSQL.=", to_char(matchdatumzeit, 'DD.MM.YYYY') as \"matchdatum\" ";
			$cTmpSQL.=", to_char(matchdatumzeit, 'HH24:MI') as \"matchzeit\" ";

			$cTmpSQL.=", to_char(gefordertamum, 'DD.MM.YYYY') as \"gefordertamumdatum\" ";
			$cTmpSQL.=", to_char(gefordertamum, 'HH24:MI') as \"gefordertamumzeit\" ";

			$cTmpSQL.=", to_char(bestaetigtamum, 'DD.MM.YYYY') as \"bestaetigtdatum\" ";
			$cTmpSQL.=", to_char(bestaetigtamum, 'HH24:MI') as \"bestaetigtzeit\" ";

			$cTmpSQL.=", to_char(matchbestaetigtamum, 'DD.MM.YYYY') as \"matchbestaetigtdatum\" ";
			$cTmpSQL.=", to_char(matchbestaetigtamum, 'HH24:MI') as \"matchbestaetigtzeit\" ";

			$cTmpSQL.=" FROM ".$cSchemaSQL."tbl_match ";

			if (empty($match_id))
			   	$cTmpSQL.=" WHERE tbl_match.match_id>0 ";
			else
			   	$cTmpSQL.=" WHERE tbl_match.match_id='".addslashes(trim($match_id))."' ";

#			if (empty($cGefordertvon))
#			   	$cTmpSQL.=" and tbl_match.gefordertvon='".addslashes(trim($cGefordertvon))."' ";

			// Forderer	
			if (!is_array($cTeam_forderer) && !empty($cTeam_forderer) )
			{
           		$cTmpSQL.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($cTeam_forderer))."') ";
			}
			elseif (is_array($cTeam_forderer) && count($cTeam_forderer)>0 )
			{
				if (isset($cTeam_forderer[0]['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cTeam_forderer);$indZEILE++)
						$arrTmpTeam_kurzbz[]=trim($cTeam_forderer[$indZEILE]['team_kurzbz']);
					$cTeam_forderer=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				elseif (isset($cTeam_forderer['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					$arrTmpTeam_kurzbz[]=trim($cTeam_forderer['team_kurzbz']);
					$cTeam_forderer=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				$cTmpSQL.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$cTeam_forderer))."') ";	
			}
			// Aaufforderungen - Einladung
			
			if (!is_array($cTeam_kurzbz_einladungen) && !empty($cTeam_kurzbz_einladungen) )
			{
	           		$cTmpSQL.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($cTeam_kurzbz_einladungen))."') ";
			}
			elseif (is_array($cTeam_kurzbz_einladungen) && count($cTeam_kurzbz_einladungen)>0 )
			{
				if (isset($cTeam_kurzbz_einladungen[0]['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cTeam_kurzbz_einladungen);$indZEILE++)
						$arrTmpTeam_kurzbz[]=trim($cTeam_kurzbz_einladungen[$indZEILE]['team_kurzbz']);
					$cTeam_kurzbz_einladungen=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				elseif (isset($cTeam_kurzbz_einladungen['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					$arrTmpTeam_kurzbz[]=trim($cTeam_kurzbz_einladungen['team_kurzbz']);
					$cTeam_kurzbz_einladungen=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				$cTmpSQL.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$cTeam_kurzbz_einladungen))."') ";	
			}
			
			// GEForderte 
			
			if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
			{
	           		$cTmpSQL.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($cWettbewerb_kurzbz))."') ";
			}
			elseif (is_array($cWettbewerb_kurzbz) && count($cWettbewerb_kurzbz)>0 )
			{
				if (isset($cWettbewerb_kurzbz[0]['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrWettbewerb_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cWettbewerb_kurzbz);$indZEILE++)
						$arrWettbewerb_kurzbz[]=trim($cTeam_kurzbz_einladungen[$indZEILE]['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$arrWettbewerb_kurzbz;
					unset($arrWettbewerb_kurzbz);	
				}
				elseif (isset($cWettbewerb_kurzbz['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrWettbewerb_kurzbz=array();
					$arrWettbewerb_kurzbz[]=trim($cTeam_kurzbz_einladungen['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$arrWettbewerb_kurzbz;
					unset($arrWettbewerb_kurzbz);	
				}
				$cTmpSQL.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$cWettbewerb_kurzbz))."') ";	
			}

		// Listenformen bestimmen 
       	if ($bSwitchGewinner=='0')
           		$cTmpSQL.=" AND ( tbl_match.matchbestaetigtvon <='' or (tbl_match.matchbestaetigtvon IS NULL) ) ";
       	elseif ($bSwitchGewinner=='1')
           		$cTmpSQL.=" AND tbl_match.matchbestaetigtvon > '' ";

    	$cTmpSQL.=" OFFSET 0 LIMIT ALL   FOR SHARE ;";	
       
	   // Entfernen der Temporaeren Variablen aus dem Speicher
       	unset($cSchemaSQL);
       	unset($cTeam_kurzbz);
       	unset($cTeam_kurzbz_einladungen);
       	unset($cWettbewerb_kurzbz);
       	unset($cGefordertvond);	   
       // Datenbankabfrage
       	$this->setStringSQL($cTmpSQL);
	   	unset($cTmpSQL);

       	$this->setResultSQL(null);
	   	$this->setWettbewerbeinladung(null);

	   	if (!$this->fetch_all()) 
			return false;    
	   	$this->setWettbewerbeinladung($this->getResultSQL());
       	$this->setResultSQL(null);
	   
		return $this->getWettbewerbeinladung();
       }
//-------------------------------------------------------------------------------------------------
       function loadWettbewerbeinladungenForderungstage()
       {
		$this->setError('');
		$cSchemaSQL=$this->getschemaSQL();

		$match_id=$this->getMatch_id();
		$cTeam_forderer=$this->getTeam_forderer();			
		$cTeam_kurzbz_einladungen=$this->getTeam_gefordert();			
		$cWettbewerb_kurzbz=$this->getWettbewerb_kurzbz();
		
		$cUid=$this->getGefordertvon();
						
	    	$cTmpSQL="";
			$cTmpSQL.="SELECT * ";

			$cTmpSQL.=", to_char(matchdatumzeit, 'DD.MM.YYYY') as \"matchdatum\" ";
			$cTmpSQL.=", to_char(matchdatumzeit, 'HH24:MI') as \"matchzeit\" ";

			$cTmpSQL.=", to_char(gefordertamum, 'DD.MM.YYYY') as \"gefordertamumdatum\" ";
			$cTmpSQL.=", to_char(gefordertamum, 'HH24:MI') as \"gefordertamumzeit\" ";

			$cTmpSQL.=", to_char(bestaetigtamum, 'DD.MM.YYYY') as \"bestaetigtdatum\" ";
			$cTmpSQL.=", to_char(bestaetigtamum, 'HH24:MI') as \"bestaetigtzeit\" ";

			$cTmpSQL.=", to_char(matchbestaetigtamum, 'DD.MM.YYYY') as \"matchbestaetigtdatum\" ";
			$cTmpSQL.=", to_char(matchbestaetigtamum, 'HH24:MI') as \"matchbestaetigtzeit\" ";
		
		
			$cTmpSQL.="
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) as gefordertamum_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum,".$cSchemaSQL."tbl_match.gefordertamum))) as bestaetigtamum_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit,".$cSchemaSQL."tbl_match.bestaetigtamum))) as matchdatumzeit_diff 
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtamum,".$cSchemaSQL."tbl_match.matchdatumzeit))) as matchbestaetigtamum_diff 

					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) as gefordertamum_tag_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum))) as bestaetigtamum_tag_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit))) as matchdatumzeit_tag_diff 
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtamum))) as matchbestaetigtamum_tag_diff 


				";

			
			$cTmpSQL.="  from ".$cSchemaSQL."tbl_wettbewerbtyp,".$cSchemaSQL."tbl_wettbewerb,".$cSchemaSQL."tbl_match
			
				where ".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz=".$cSchemaSQL."tbl_wettbewerb.wbtyp_kurzbz
				and ".$cSchemaSQL."tbl_match.wettbewerb_kurzbz=".$cSchemaSQL."tbl_wettbewerb.wettbewerb_kurzbz
				and (".$cSchemaSQL."tbl_match.matchbestaetigtvon IS NULL or ".$cSchemaSQL."tbl_match.matchbestaetigtvon<='')
			
			";
#				and  ( extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) >=".$cSchemaSQL."tbl_wettbewerb.forderungstage
#					 or  extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum,".$cSchemaSQL."tbl_match.gefordertamum)))>=".$cSchemaSQL."tbl_wettbewerb.forderungstage
#					 or  extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit,".$cSchemaSQL."tbl_match.bestaetigtamum)))>=".$cSchemaSQL."tbl_wettbewerb.forderungstage
#					 or  extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtamum,".$cSchemaSQL."tbl_match.matchdatumzeit)))>=".$cSchemaSQL."tbl_wettbewerb.forderungstage
#					 ) 
		

			if (!empty($match_id))
			   	$cTmpSQL.=" and tbl_match.match_id='".addslashes(trim($match_id))."' ";

			if (!empty($cUid))
			   	$cTmpSQL.=" and tbl_wettbewerb.uid='".addslashes(trim($cUid))."' ";

				
			// Forderer	
			if (!is_array($cTeam_forderer) && !empty($cTeam_forderer) )
			{
           		$cTmpSQL.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($cTeam_forderer))."') ";
			}
			elseif (is_array($cTeam_forderer) && count($cTeam_forderer)>0 )
			{
				if (isset($cTeam_forderer[0]['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cTeam_forderer);$indZEILE++)
						$arrTmpTeam_kurzbz[]=trim($cTeam_forderer[$indZEILE]['team_kurzbz']);
					$cTeam_forderer=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				elseif (isset($cTeam_forderer['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					$arrTmpTeam_kurzbz[]=trim($cTeam_forderer['team_kurzbz']);
					$cTeam_forderer=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				
				$cTmpSQL.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$cTeam_forderer))."') ";	
			}
			// Aaufforderungen - Einladung
			
			if (!is_array($cTeam_kurzbz_einladungen) && !empty($cTeam_kurzbz_einladungen) )
			{
	           		$cTmpSQL.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($cTeam_kurzbz_einladungen))."') ";
			}
			elseif (is_array($cTeam_kurzbz_einladungen) && count($cTeam_kurzbz_einladungen)>0 )
			{
				if (isset($cTeam_kurzbz_einladungen[0]['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cTeam_kurzbz_einladungen);$indZEILE++)
						$arrTmpTeam_kurzbz[]=trim($cTeam_kurzbz_einladungen[$indZEILE]['team_kurzbz']);
					$cTeam_kurzbz_einladungen=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				elseif (isset($cTeam_kurzbz_einladungen['team_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrTmpTeam_kurzbz=array();
					$arrTmpTeam_kurzbz[]=trim($cTeam_kurzbz_einladungen['team_kurzbz']);
					$cTeam_kurzbz_einladungen=$arrTmpTeam_kurzbz;
					unset($arrTmpTeam_kurzbz);	
				}
				$cTmpSQL.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$cTeam_kurzbz_einladungen))."') ";	
			}
			
			// GEForderte 
			
			if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
			{
	           		$cTmpSQL.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($cWettbewerb_kurzbz))."') ";
			}
			elseif (is_array($cWettbewerb_kurzbz) && count($cWettbewerb_kurzbz)>0 )
			{
				if (isset($cWettbewerb_kurzbz[0]['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrWettbewerb_kurzbz=array();
					for ($indZEILE=0;$indZEILE<count($cWettbewerb_kurzbz);$indZEILE++)
						$arrWettbewerb_kurzbz[]=trim($cTeam_kurzbz_einladungen[$indZEILE]['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$arrWettbewerb_kurzbz;
					unset($arrWettbewerb_kurzbz);	
				}
				elseif (isset($cWettbewerb_kurzbz['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$arrWettbewerb_kurzbz=array();
					$arrWettbewerb_kurzbz[]=trim($cTeam_kurzbz_einladungen['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$arrWettbewerb_kurzbz;
					unset($arrWettbewerb_kurzbz);	
				}
				$cTmpSQL.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$cWettbewerb_kurzbz))."') ";	
			}
		
    		$cTmpSQL.=" OFFSET 0 LIMIT ALL  FOR SHARE OF tbl_match ;";	
#exit($cTmpSQL.Test($cTeam_kurzbz_einladungen).implode("','",$cTeam_kurzbz_einladungen));
	
	   // Entfernen der Temporaeren Variablen aus dem Speicher
       	unset($cSchemaSQL);
       	unset($cTeam_kurzbz);
       	unset($cTeam_kurzbz_einladungen);
       	unset($cWettbewerb_kurzbz);
       	unset($cUid);	   
       // Datenbankabfrage
       	$this->setStringSQL($cTmpSQL);
	   	unset($cTmpSQL);

       	$this->setResultSQL(null);
	   	$this->setWettbewerbeinladung(null);

	   	if (!$this->fetch_all()) 
			return false;    
	   	$this->setWettbewerbeinladung($this->getResultSQL());
       	$this->setResultSQL(null);
		return $this->getWettbewerbeinladung();
	   }

//-------------------------------------------------------------------------------------------------
       function unloadWettbewerbeinladungen()
       {
		$this->setError('');
		$cSchemaSQL=$this->getschemaSQL();

		$match_id=$this->getMatch_id();
		if (empty($match_id))
		{
			$this->setError('Keine Match ID gefunden!'); 
			return false;
		}	
	    	$cTmpSQL="";
		$cTmpSQL.="delete from ".$cSchemaSQL."tbl_match	";		
		$cTmpSQL.=" * ";
	   	$cTmpSQL.=" where tbl_match.match_id='".addslashes(trim($match_id))."' ";
	
	   // Entfernen der Temporaeren Variablen aus dem Speicher
       	unset($cSchemaSQL);
       	unset($match_id);	   
       // Datenbankabfrage
       	$this->setStringSQL($cTmpSQL);
	   	unset($cTmpSQL);

       	$this->setResultSQL(null);
	   	$this->setWettbewerbeinladung(null);
	   	if (!$this->fetch_object()) 
			return false;    
       	$this->setResultSQL(null);
		return true;
	   }

	   
} // Class komune_wettbewerb Ende 

?>