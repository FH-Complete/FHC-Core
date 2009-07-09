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


require_once(dirname(__FILE__).'/basis_db.class.php'); 
class komune_wettbewerbeinladungen extends basis_db 
{
	   public $result;
	   public $new=false;      					// boolean
	   	
       public $wbtyp_kurzbz;
       public $wettbewerb_kurzb;

	   public $uid;
       public $team_kurzbz;

       public $team_forderer="";
       public $team_gefordert="";
       public $match_id="";

       public $gefordertvon="";
       public $gefordertamum="";	   

       public $matchdatumzeit="";
       public $matchort="";

       public $bestaetigtvon="";
       public $bestaetigtamum="";

       public $ergebniss="";
       public $team_sieger="";	   

       public $matchbestaetigtamum="";
       public $matchbestaetigtvon="";
	
       public $switchGewinner='';	   
	   
	   public $schemaSQL="kommune"; // string Datenbankschema		
//-----Konstruktor       
       function __construct($match_id="",$team_forderer="",$team_gefordert="",$wettbewerb_kurzbz="",$uid="",$wbtyp_kurzbz="") 
       {
	   		parent::__construct();
	   
           	$this->InitWettbewerbeinladungen();   

			$this->match_id=$match_id;
			$this->team_forderer=$team_forderer;
			$this->team_gefordert=$team_gefordert;

			$this->wbtyp_kurzbz=$wbtyp_kurzbz;
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
					   
		   	$this->gefordertvon=$uid;

       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerbeinladungen() 
       {
			$this->new=false;
			$this->errormsg='';
			
	       	$this->result=array();

			$this->wbtyp_kurzbz='';
	       	$this->wettbewerb_kurzbz='';
			
			$this->match_id="";
			$this->team_forderer="";
			$this->team_gefordert="";
			
			$this->gefordertvon="";
			$this->gefordertamum="";	   
			
			$this->matchdatumzeit="";
			$this->matchort="";
			
			$this->bestaetigtvon="";
			$this->bestaetigtamum="";
			
			$this->ergebniss="";
			$this->team_sieger="";	   
			
			$this->matchbestaetigtamum="";
			$this->matchbestaetigtvon="";
			
			$this->switchGewinner='';	   
		
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
       	$this->team_forderer=$this->getTeam_forderer();			
       	$cTeam_gefordert=$this->getTeam_gefordert();		
			
		// Plausib - Pruefen ob Eingeladente Team nicht als Array ubergeben wurde ( gebraucht wird nur die Kurzbezeichnung)
	       if (is_array($this->team_forderer) && isset($this->team_forderer['team_forderer']))		
		      $this->team_forderer=$this->team_forderer['team_forderer'];		
	       elseif (is_array($this->team_forderer) && isset($this->team_forderer[0]['team_forderer']) )		
		      $this->team_forderer=$this->team_forderer[0]['team_forderer'];		
	       elseif (is_array($this->team_forderer) && isset($this->team_forderer['team_forderer']))		
		      $this->team_forderer=$this->team_forderer['team_kurzbz'];		
	       elseif (is_array($this->team_forderer) && isset($this->team_forderer[0]['team_forderer']) )		
		      $this->team_forderer=$this->team_forderer[0]['team_forderer'];		
		
		// Plausib - Pruefen ob Eingeladene Team nicht als Array ubergeben wurde ( gebraucht wird nur die Kurzbezeichnung)
	       if (is_array($cTeam_gefordert) && isset($cTeam_gefordert['team_kurzbz']))		
		      $cTeam_gefordert=$cTeam_gefordert['team_kurzbz'];		
	       elseif (is_array($cTeam_gefordert) && isset($cTeam_gefordert[0]['team_kurbz']))		
		      $cTeam_gefordert=$cTeam_gefordert[0]['team_kurzbz'];		
		
		$this->team_forderer=trim($this->team_forderer);	  
		if (empty($this->team_forderer) )
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

    		$qry="BEGIN;  ";
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
       		$qry=" insert into ".$this->schemaSQL.".tbl_match (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			if (!$this->getMatch_id())
				$this->setMatch_id($origWettbewerbeinladungen[0]['match_id']);
			$qry.=" update ".$this->schemaSQL.".tbl_match set ";
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
			$qry.=$fildsValue." where tbl_match.match_id='".$this->getMatch_id()."'; ";  
			$this->wettbewerb_kurzbz=trim($origWettbewerbeinladungen[0]['wettbewerb_kurzbz']);
			$origWettbewerbeinladungen[0]['matchbestaetigtvon']=trim($origWettbewerbeinladungen[0]['matchbestaetigtvon']);
			$origWettbewerbeinladungen[0]['team_sieger']=trim($origWettbewerbeinladungen[0]['team_sieger']);

			if ($this->getMatchbestaetigtvon() && empty($origWettbewerbeinladungen[0]['matchbestaetigtvon'])
			&& trim($origWettbewerbeinladungen[0]['team_sieger'])==trim($this->team_forderer))  // Der Forderer ist der Siehter den Rangtauschen
			{
			// Rang des Geforderten ermitteln
				$qry_tmp="select rang from ".$this->schemaSQL.".tbl_wettbewerbteam where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."')   FOR UPDATE ;";
			       $this->fetch_object($qry_tmp);
				$iTmpRangGeforderter=$this->getResultSQL();
				if (isset($iTmpRangGeforderter->rang))
					$iTmpRangGeforderter=trim($iTmpRangGeforderter->rang);
					
				// Rang des Geforderten ermitteln
				$qry_tmp="select rang from ".$this->schemaSQL.".tbl_wettbewerbteam where upper(team_kurzbz)=upper('".$this->team_forderer."') and  upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."')   FOR UPDATE ;";
		
			       $this->fetch_object($qry_tmp);
				$iTmpRangFrorderer=$this->getResultSQL();
				if (isset($iTmpRangFrorderer->rang))
					$iTmpRangFrorderer=trim($iTmpRangFrorderer->rang);
				
				$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbteam  ";
				$qry.="set rang=0 ";
				$qry.=" where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."'); ";

				$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbteam  ";
				$qry.="set rang=".$iTmpRangGeforderter;
				$qry.=" where upper(team_kurzbz)=upper('".$this->team_forderer."') and upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."'); ";

				$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbteam  ";
				$qry.="set rang=".$iTmpRangFrorderer;
				$qry.=" where upper(team_kurzbz)=upper('".$cTeam_gefordert."') and  upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."'); ";

				$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbteam  ";
				$qry.="set punkte=3+punkte ";
				$qry.="where upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."') and upper(team_kurzbz)=upper('".$origWettbewerbeinladungen[0]['team_sieger']."'); ";

				$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbteam  ";
				$qry.="set punkte=1+punkte ";
				$qry.="where upper(wettbewerb_kurzbz)=upper('".$this->wettbewerb_kurzbz."') and upper(team_kurzbz)=upper('". ($origWettbewerbeinladungen[0]['team_sieger']==$cTeam_gefordert?$this->team_forderer:$cTeam_gefordert)."'); ";
			}	
		}				
		$qry.=" COMMIT; ";       
#exit($qry);
   // Datenbankabfrage
            	$this->setStringSQL($qry);
      	   	unset($qry);

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

       function loadWettbewerbeinladungen($match_id=null,$gefordertvon=null,$team_forderer=null,$team_einladungen=null,$wettbewerb_kurzbz=null,$switchGewinner=null)
       {
	   
	// Initialisierung	
					  
			$this->result=array();
			$this->errormsg='';
		
			if (!is_null($match_id))
				$this->match_id=$match_id;
				
			if (!is_null($gefordertvon))
				$this->gefordertvon=$gefordertvon;			
				
			if (!is_null($team_forderer))
				$this->team_forderer=$team_forderer;
	
			if (!is_null($team_einladungen))
				$this->team_einladungen=$team_einladungen;			
	
			if (!is_null($wettbewerb_kurzbz))
				$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;

			if (!is_null($switchGewinner))
				$this->switchGewinner=$switchGewinner;
            	
    	$qry="";
		$qry.="SELECT * ";

			$qry.=", to_char(matchdatumzeit, 'DD.MM.YYYY') as \"matchdatum\" ";
			$qry.=", to_char(matchdatumzeit, 'HH24:MI') as \"matchzeit\" ";

			$qry.=", to_char(gefordertamum, 'DD.MM.YYYY') as \"gefordertamumdatum\" ";
			$qry.=", to_char(gefordertamum, 'HH24:MI') as \"gefordertamumzeit\" ";

			$qry.=", to_char(bestaetigtamum, 'DD.MM.YYYY') as \"bestaetigtdatum\" ";
			$qry.=", to_char(bestaetigtamum, 'HH24:MI') as \"bestaetigtzeit\" ";

			$qry.=", to_char(matchbestaetigtamum, 'DD.MM.YYYY') as \"matchbestaetigtdatum\" ";
			$qry.=", to_char(matchbestaetigtamum, 'HH24:MI') as \"matchbestaetigtzeit\" ";

			$qry.=" FROM ".$this->schemaSQL.".tbl_match ";

			if (empty($this->match_id))
			   	$qry.=" WHERE tbl_match.match_id>0 ";
			else
			   	$qry.=" WHERE tbl_match.match_id='".addslashes(trim($this->match_id))."' ";

			// Forderer	
			if (!is_array($this->team_forderer) && !empty($this->team_forderer) )
			{
           		$qry.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($this->team_forderer))."') ";
			}
			elseif (is_array($this->team_forderer) && count($this->team_forderer)>0 )
			{
				$qry.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$this->team_forderer))."') ";	
			}
			// Aaufforderungen - Einladung
			
			if (!is_array($this->team_einladungen) && !empty($this->team_einladungen) )
			{
	           		$qry.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($this->team_einladungen))."') ";
			}
			elseif (is_array($this->team_einladungen) && count($this->team_einladungen)>0 )
			{
				$qry.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$this->team_einladungen))."') ";	
			}
			
			// GEForderte 
			
			if (!is_array($this->wettbewerb_kurzbz) && !empty($this->wettbewerb_kurzbz) )
			{
	           		$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($this->wettbewerb_kurzbz))."') ";
			}
			elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
			{
				$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
			}

		// Listenformen bestimmen 
       	if ($this->switchGewinner=='0')
           		$qry.=" AND ( tbl_match.matchbestaetigtvon <='' or (tbl_match.matchbestaetigtvon IS NULL) ) ";
       	elseif ($this->switchGewinner=='1')
           		$qry.=" AND tbl_match.matchbestaetigtvon > '' ";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->result[]=$row;
			}
			return $this->result;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim lesen der Wettbewerbstypen';
			return false;
		}	
		return false;	   
       }
//-------------------------------------------------------------------------------------------------
       function loadWettbewerbeinladungenForderungstage($match_id=null,$gefordertvon=null,$team_forderer=null,$team_einladungen=null,$wettbewerb_kurzbz=null)
       {
			$this->result=array();
			$this->errormsg='';
		
			if (!is_null($match_id))
				$this->match_id=$match_id;
				
			if (!is_null($gefordertvon))
				$this->gefordertvon=$gefordertvon;			
				
			if (!is_null($team_forderer))
				$this->team_forderer=$team_forderer;
	
			if (!is_null($team_einladungen))
				$this->team_einladungen=$team_einladungen;			
	
			if (!is_null($wettbewerb_kurzbz))
				$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
			
	    	$qry="";
			$qry.="SELECT * ";
			$qry.=", to_char(matchdatumzeit, 'DD.MM.YYYY') as \"matchdatum\" ";
			$qry.=", to_char(matchdatumzeit, 'HH24:MI') as \"matchzeit\" ";

			$qry.=", to_char(gefordertamum, 'DD.MM.YYYY') as \"gefordertamumdatum\" ";
			$qry.=", to_char(gefordertamum, 'HH24:MI') as \"gefordertamumzeit\" ";

			$qry.=", to_char(bestaetigtamum, 'DD.MM.YYYY') as \"bestaetigtdatum\" ";
			$qry.=", to_char(bestaetigtamum, 'HH24:MI') as \"bestaetigtzeit\" ";

			$qry.=", to_char(matchbestaetigtamum, 'DD.MM.YYYY') as \"matchbestaetigtdatum\" ";
			$qry.=", to_char(matchbestaetigtamum, 'HH24:MI') as \"matchbestaetigtzeit\" ";
		
			$qry.="
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.gefordertamum))) as gefordertamum_diff
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.bestaetigtamum,".$this->schemaSQL.".tbl_match.gefordertamum))) as bestaetigtamum_diff
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.matchdatumzeit,".$this->schemaSQL.".tbl_match.bestaetigtamum))) as matchdatumzeit_diff 
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.matchbestaetigtamum,".$this->schemaSQL.".tbl_match.matchdatumzeit))) as matchbestaetigtamum_diff 

					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.gefordertamum))) as gefordertamum_tag_diff
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.bestaetigtamum))) as bestaetigtamum_tag_diff
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.matchdatumzeit))) as matchdatumzeit_tag_diff 
					,1+extract('day' from (age(".$this->schemaSQL.".tbl_match.matchbestaetigtamum))) as matchbestaetigtamum_tag_diff 

				";
			
			$qry.="  from ".$this->schemaSQL.".tbl_wettbewerbtyp,".$this->schemaSQL.".tbl_wettbewerb,".$this->schemaSQL.".tbl_match
			
				where ".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz=".$this->schemaSQL.".tbl_wettbewerb.wbtyp_kurzbz
				and ".$this->schemaSQL.".tbl_match.wettbewerb_kurzbz=".$this->schemaSQL.".tbl_wettbewerb.wettbewerb_kurzbz
				and (".$this->schemaSQL.".tbl_match.matchbestaetigtvon IS NULL or ".$this->schemaSQL.".tbl_match.matchbestaetigtvon<='')
			";

			if (!empty($this->match_id))
			   	$qry.=" and tbl_match.match_id='".addslashes(trim($this->match_id))."' ";

			if (!empty($this->gefordertvon))
			   	$qry.=" and tbl_wettbewerb.uid='".addslashes(trim($this->gefordertvon))."' ";

				
			// Forderer	
			if (!is_array($this->team_forderer) && !empty($this->team_forderer) )
			{
           		$qry.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($this->team_forderer))."') ";
			}
			elseif (is_array($this->team_forderer) && count($this->team_forderer)>0 )
			{
				$qry.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$this->team_forderer))."') ";	
			}

			// Aaufforderungen - Einladung
			if (!is_array($this->team_einladungen) && !empty($this->team_einladungen) )
			{
	           		$qry.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($this->team_einladungen))."') ";
			}
			elseif (is_array($this->team_einladungen) && count($this->team_einladungen)>0 )
			{
				$qry.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$this->team_einladungen))."') ";	
			}
			
			// Wettbewerb 
			if (!is_array($this->wettbewerb_kurzbz) && !empty($this->wettbewerb_kurzbz) )
			{
	           		$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($this->wettbewerb_kurzbz))."') ";
			}
			elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
			{
				$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
			}
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->result[]=$row;
			}
			return $this->result;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim lesen der Wettbewerbstypen';
			return false;
		}	
		return false;	
	   }

//-------------------------------------------------------------------------------------------------
       function unloadWettbewerbeinladungen($match_id=null)
       {
		$this->result=array();
		$this->errormsg='';
	
		if (!is_null($match_id))
			$this->match_id=$match_id;
		
		if (empty($this->match_id) || is_null($this->match_id))
		{
			$this->errormsg = 'Keine Match ID gefunden!'; 
			return false;
		}	
		
	    $qry="";
		$qry.="delete from ".$this->schemaSQL.".tbl_match	";		
		$qry.=" * ";
	   	$qry.=" where tbl_match.match_id='".addslashes(trim($this->match_id))."' ";
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim lesen der Wettbewerbstypen';
			return false;
		}	
		return false;	
	   }

	   
} // Class komune_wettbewerb Ende 

?>