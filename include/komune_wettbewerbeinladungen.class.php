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
	public $new;
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
		
	public $schemaSQL="kommune."; // string Datenbankschema		
//-----Konstruktor       
       function __construct($match_id="",$team_forderer="",$team_gefordert="",$wettbewerb_kurzbz="",$uid="") 
       {
		parent::__construct();
	
           $this->InitWettbewerbeinladungen();   

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
//-----NewRecord--------------------------------------------------------------------------------------------
       function getNewRecord() 
       {
           return $this->new;
       }
       function setNewRecord($switch) 
       {
           $this->new=$switch;
       }		
//-----Error--------------------------------------------------------------------------------------------
       function getError() 
       {
           return $this->errormsg;
       }
       function setError($err) 
       {
           $this->errormsg=$err;
       }		
//-----schemaSQL--------------------------------------------------------------------------------------------
       function getSchemaSQL() 
       {
           return $this->schemaSQL;
       }
       function setSchemaSQL($schemaSQL) 
       {
           	$this->schemaSQL=$schemaSQL;
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

		if (!$origWettbewerbeinladungen=$this->loadWettbewerbeinladungen())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	

		$this->db_query(" BEGIN; ");
    		$qry="";
		
		if ($this->getNewRecord()) // Neuanlage - Insert 
		{
/*
team_sieger	character varying(16)				Durchsuchen	Ändern	Löschen	
wettbewerb_kurzbz	character varying(16)	NOT NULL			Durchsuchen	Ändern	Löschen	
team_gefordert	character varying(16)	NOT NULL			Durchsuchen	Ändern	Löschen	
team_forderer	character varying(16)	NOT NULL			Durchsuchen	Ändern	Löschen	
gefordertvon	character varying(16)	NOT NULL			Durchsuchen	Ändern	Löschen	
matchdatumzeit	timestamp without time zone				Durchsuchen	Ändern	Löschen	
matchort	character varying(32)				Durchsuchen	Ändern	Löschen	
ergebniss	character varying(16)				Durchsuchen	Ändern	Löschen	
bestaetigtvon	character varying(16)				Durchsuchen	Ändern	Löschen	
bestaetigtamum	timestamp without time zone				Durchsuchen	Ändern	Löschen	
match_id	integer	NOT NULL	nextval('tbl_match_match_id_seq'::regclass)		Durchsuchen	Ändern	Löschen	
matchbestaetigtvon	character varying(16)				Durchsuchen	Ändern	Löschen	
matchbestaetigtamum	timestamp without time zone				Durchsuchen	Ändern	Löschen	
gefordertamum	timestamp without time zone
*/		
			$fildsList="";
			$fildsValue="";
			
			$fildsList.='team_sieger,';
			$fildsList.='wettbewerb_kurzbz,';
			$fildsList.='team_gefordert,';
			$fildsList.='team_forderer,';
			$fildsList.='gefordertvon,';
			$fildsList.='matchdatumzeit,';
			$fildsList.='matchort,';
			$fildsList.='ergebniss,';
			$fildsList.='bestaetigtvon,';
			$fildsList.='bestaetigtamum,';
#			$fildsList.='match_id,';
			$fildsList.='matchbestaetigtvon,';
			$fildsList.='matchbestaetigtamum,';
			$fildsList.='gefordertamum';
			
			if (!$this->getGefordertamum())
				$this->setGefordertamum(time());		
																				
			$fildsValue.=($this->team_sieger?"'".addslashes($this->team_sieger)."'":'null').","; 
			
			$fildsValue.="'".addslashes($this->wettbewerb_kurzbz)."',";
			$fildsValue.="'".addslashes($this->team_gefordert)."',";
			$fildsValue.="'".addslashes($this->team_forderer)."',";
			$fildsValue.="'".addslashes($this->gefordertvon)."',";

			$fildsValue.=(empty($this->matchdatumzeit)?'null,':"'".addslashes(date('Y-m-d H:i:s',$this->matchdatumzeit))."',");
			
			$fildsValue.="'".addslashes($this->matchort)."',";
			$fildsValue.="'".addslashes($this->ergebniss)."',";
			
			$fildsValue.=($this->bestaetigtvon?"'".addslashes($this->bestaetigtvon)."'":'null').",";
			$fildsValue.=(empty($this->bestaetigtamum)?'null,':"'".addslashes(date('Y-m-d H:i:s',$this->bestaetigtamum))."',");
#			$fildsValue.="".addslashes($this->match_id).",";
			$fildsValue.=($this->matchbestaetigtvon?"'".addslashes($this->matchbestaetigtvon)."'":'null').",";
			
			$fildsValue.=(empty($this->matchbestaetigtamum)?'null,':"'".addslashes(date('Y-m-d H:i:s',$this->matchbestaetigtamum))."',");
			$fildsValue.=(empty($this->gefordertamum)?'null':"'".addslashes(date('Y-m-d H:i:s',$this->gefordertamum))."'");
	
	   		$qry=" insert into ".$this->schemaSQL."tbl_match (".$fildsList.") values (".$fildsValue."); ";
	
		}
		else
		{
			if (!$this->getMatch_id())
				$this->setMatch_id($origWettbewerbeinladungen[0]['match_id']);
			$qry.=" update ".$cSchemaSQL."tbl_match set ";
			$fildsValue='';
																		
			if ($this->team_sieger)
				$fildsValue.="team_sieger=".($this->team_sieger?"'".addslashes($this->team_sieger)."'":'null').",";

			
			$fildsValue.="wettbewerb_kurzbz='".addslashes($this->wettbewerb_kurzbz)."',";
			$fildsValue.="team_gefordert='".addslashes($this->team_gefordert)."',";
			$fildsValue.="team_forderer='".addslashes($this->team_forderer)."',";

			if (!is_null($this->gefordertvon) && $this->gefordertvon)
				$fildsValue.="gefordertvon=".($this->gefordertvon?"'".addslashes($this->gefordertvon)."'":'null').",";

			if (!is_null($this->matchdatumzeit) && $this->matchdatumzeit)
				$fildsValue.="matchdatumzeit=".($this->matchdatumzeit?"'".addslashes(date('Y-m-d H:i:s',$this->matchdatumzeit))."'":'null').",";

			if (!is_null($this->matchort) && $this->matchort)
				$fildsValue.="matchort='".addslashes($this->matchort)."',";
			if (!is_null($this->ergebniss) && $this->ergebniss)
				$fildsValue.="ergebniss='".addslashes($this->ergebniss)."',";
			
			if (!is_null($this->ergebniss) && $this->bestaetigtvon)
				$fildsValue.="bestaetigtvon=".($this->bestaetigtvon?"'".addslashes($this->bestaetigtvon)."'":'null').",";
			if (!is_null($this->ergebniss) && $this->bestaetigtamum)			
				$fildsValue.="bestaetigtamum=".($this->bestaetigtamum?"'".addslashes(date('Y-m-d H:i:s',$this->bestaetigtamum))."'":'null').",";
	
			if (!is_null($this->ergebniss) && $this->matchbestaetigtvon)
				$fildsValue.="matchbestaetigtvon=".($this->matchbestaetigtvon?"'".addslashes($this->matchbestaetigtvon)."'":'null').",";
			if (!is_null($this->matchbestaetigtamum) && $this->matchbestaetigtamum)
				$fildsValue.="matchbestaetigtamum=".($this->matchbestaetigtamum?"'".addslashes(date('Y-m-d H:i:s',$this->matchbestaetigtamum))."'":'null').",";
			if (!is_null($this->gefordertamum) && $this->gefordertamum)
				$fildsValue.="gefordertamum=".($this->gefordertamum?"'".addslashes(date('Y-m-d H:i:s',$this->gefordertamum))."'":'null').",";
	
			$fildsValue.="match_id=".addslashes($this->match_id)."";
			
			$qry.=$fildsValue." where tbl_match.match_id='".$this->getMatch_id()."'; ";  
			
			$cWettbewerb_kurzbz=trim($origWettbewerbeinladungen[0]['wettbewerb_kurzbz']);
			$origWettbewerbeinladungen[0]['matchbestaetigtvon']=trim($origWettbewerbeinladungen[0]['matchbestaetigtvon']);
			$origWettbewerbeinladungen[0]['team_sieger']=trim($origWettbewerbeinladungen[0]['team_sieger']);

			if ($this->getMatchbestaetigtvon() && empty($origWettbewerbeinladungen[0]['matchbestaetigtvon'])
			&& trim($origWettbewerbeinladungen[0]['team_sieger'])==trim($this->team_forderer))  // Der Forderer ist der Siehter den Rangtauschen
			{
			// Rang des Geforderten ermitteln
				$iTmpRangGeforderter=0;
				$qry_tmp="select rang from ".$cSchemaSQL."tbl_wettbewerbteam where upper(team_kurzbz)=upper('".addslashes($this->team_gefordert)."') and  upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."') ;";
				if($res=$this->db_query($qry_tmp))
				{
					$iTmpRangGeforderter=$this->db_result($res,0,'rang');
				}	
				else
				{
					$this->errormsg = $this->db_last_error();
					return false;
				}			  
			  

				$iTmpRangFrorderer=0;	
				// Rang des Geforderten ermitteln
				$qry_tmp="select rang from ".$cSchemaSQL."tbl_wettbewerbteam where upper(team_kurzbz)=upper('".$this->team_forderer."') and  upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."') ;";
				if($res=$this->db_query($qry_tmp))
				{
					$iTmpRangFrorderer=$this->db_result($res,0,'rang');
				}	
				else
				{
					$this->errormsg = $this->db_last_error();
					return false;
				}			  

				$qry.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$qry.="set rang=0 ";
				$qry.=" where upper(team_kurzbz)=upper('".addslashes($this->team_gefordert)."') and  upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."'); ";

				$qry.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$qry.="set rang=".$iTmpRangGeforderter;
				$qry.=" where upper(team_kurzbz)=upper('".addslashes($this->team_forderer)."') and upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."'); ";

				$qry.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$qry.="set rang=".$iTmpRangFrorderer;
				$qry.=" where upper(team_kurzbz)=upper('".addslashes($this->team_gefordert)."') and  upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."'); ";

				$qry.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$qry.="set punkte=3+punkte ";
				$qry.="where upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."') and upper(team_kurzbz)=upper('".addslashes($origWettbewerbeinladungen[0]['team_sieger'])."'); ";

				$qry.=" update ".$cSchemaSQL."tbl_wettbewerbteam  ";
				$qry.="set punkte=1+punkte ";
				$qry.="where upper(wettbewerb_kurzbz)=upper('".addslashes($this->wettbewerb_kurzbz)."') and upper(team_kurzbz)=upper('". addslashes(($origWettbewerbeinladungen[0]['team_sieger']==$this->team_gefordert?$this->team_forderer:$this->team_gefordert))."'); ";
			}	
		}				
		
		
		if($this->db_query($qry))
		{
			$this->db_query(" COMMIT; ");
			return $this->loadWettbewerbeinladungen();
		}	
		else
		{
			$this->setError($qry.' '.$this->db_last_error());
			$this->db_query(" ROLLBACK; ");
			return false;
		}
		return true;		
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

			$qry.=" FROM ".$cSchemaSQL."tbl_match ";

			if (empty($match_id))
			   	$qry.=" WHERE tbl_match.match_id>0 ";
			else
			   	$qry.=" WHERE tbl_match.match_id='".addslashes(trim($match_id))."' ";

#			if (!empty($cGefordertvon))
#			   	$qry.=" and tbl_match.gefordertvon='".addslashes(trim($cGefordertvon))."' ";

			// Forderer	
			if (!is_array($cTeam_forderer) && !empty($cTeam_forderer) )
			{
           		$qry.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($cTeam_forderer))."') ";
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
				$qry.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$cTeam_forderer))."') ";	
			}
			// Aaufforderungen - Einladung
			
			if (!is_array($cTeam_kurzbz_einladungen) && !empty($cTeam_kurzbz_einladungen) )
			{
	           		$qry.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($cTeam_kurzbz_einladungen))."') ";
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
				$qry.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$cTeam_kurzbz_einladungen))."') ";	
			}
			
			// GEForderte 
			
			if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
			{
	           		$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($cWettbewerb_kurzbz))."') ";
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
				$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$cWettbewerb_kurzbz))."') ";	
			}

		// Listenformen bestimmen 
       	if ($bSwitchGewinner=='0')
           		$qry.=" AND ( tbl_match.matchbestaetigtvon <='' or (tbl_match.matchbestaetigtvon IS NULL) ) ";
       	elseif ($bSwitchGewinner=='1')
           		$qry.=" AND tbl_match.matchbestaetigtvon > '' ";

#echo $qry;

		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$rows[]=$row;
			}
			$this->setWettbewerbeinladung($rows);
		}	
		else
		{
			$this->setError($this->db_last_error());
			return false;
		}
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
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) as gefordertamum_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum,".$cSchemaSQL."tbl_match.gefordertamum))) as bestaetigtamum_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit,".$cSchemaSQL."tbl_match.bestaetigtamum))) as matchdatumzeit_diff 
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtamum,".$cSchemaSQL."tbl_match.matchdatumzeit))) as matchbestaetigtamum_diff 

					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.gefordertamum))) as gefordertamum_tag_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.bestaetigtamum))) as bestaetigtamum_tag_diff
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchdatumzeit))) as matchdatumzeit_tag_diff 
					,1+extract('day' from (age(".$cSchemaSQL."tbl_match.matchbestaetigtamum))) as matchbestaetigtamum_tag_diff 


				";

			
			$qry.="  from ".$cSchemaSQL."tbl_wettbewerbtyp,".$cSchemaSQL."tbl_wettbewerb,".$cSchemaSQL."tbl_match
			
				where ".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz=".$cSchemaSQL."tbl_wettbewerb.wbtyp_kurzbz
				and ".$cSchemaSQL."tbl_match.wettbewerb_kurzbz=".$cSchemaSQL."tbl_wettbewerb.wettbewerb_kurzbz
				and (".$cSchemaSQL."tbl_match.matchbestaetigtvon IS NULL or ".$cSchemaSQL."tbl_match.matchbestaetigtvon<='')
			
			";

			if (!empty($match_id))
			   	$qry.=" and tbl_match.match_id='".addslashes(trim($match_id))."' ";

			if (!empty($cUid))
			   	$qry.=" and tbl_wettbewerb.uid='".addslashes(trim($cUid))."' ";

				
			// Forderer	
			if (!is_array($cTeam_forderer) && !empty($cTeam_forderer) )
			{
           		$qry.=" AND UPPER(tbl_match.team_forderer)=UPPER('".addslashes(trim($cTeam_forderer))."') ";
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
				
				$qry.=" AND UPPER(tbl_match.team_forderer) in ('".strtoupper(implode("','",$cTeam_forderer))."') ";	
			}
			// Aaufforderungen - Einladung
			
			if (!is_array($cTeam_kurzbz_einladungen) && !empty($cTeam_kurzbz_einladungen) )
			{
	           		$qry.=" AND UPPER(tbl_match.team_gefordert)=UPPER('".addslashes(trim($cTeam_kurzbz_einladungen))."') ";
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
				$qry.=" AND UPPER(tbl_match.team_gefordert) in ('".strtoupper(implode("','",$cTeam_kurzbz_einladungen))."') ";	
			}
			
			// GEForderte 
			
			if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
			{
	           		$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz)=UPPER('".addslashes(trim($cWettbewerb_kurzbz))."') ";
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
				$qry.=" AND UPPER(tbl_match.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$cWettbewerb_kurzbz))."') ";	
			}
		
#    		$qry.=" OFFSET 0 LIMIT ALL  FOR SHARE OF tbl_match ;";	
		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$rows[]=$row;
			}
			$this->setWettbewerbeinladung($rows);
		}	
		else
		{
			$this->setError($this->db_last_error());

			return false;
		}
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
	    	$qry="";
		$qry.="delete from ".$cSchemaSQL."tbl_match	";		
		$qry.=" * ";
	   	$qry.=" where tbl_match.match_id='".addslashes(trim($match_id))."' ";

		if($this->db_query($qry))
		{
			$this->setWettbewerbeinladung(null);
			return true;
		}	
		else
		{
			$this->setError($this->db_last_error());

			return false;
		}
		return true;
	   }
} // Class komune_wettbewerb Ende 

?>