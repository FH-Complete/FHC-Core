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
* @classe Jahresplan 
*
* @param connectSQL Datenbankverbindung
* @param veranstaltungskategorie_kurzbz Veranstaltungskategorie Key	
* @param veranstaltung_id Veranstaltung Key 
* @param reservierung_id Reservierung Key
*
* @return - kein Retourn des Konstruktors
*
*/

include_once(dirname(__FILE__)."/postgre_sql.class.php"); 
class jahresplan extends postgre_sql
{
    	protected $veranstaltungkategorie;
		protected $veranstaltungskategorie_kurzbz;
		protected $veranstaltungkategorie_struck;	
		protected $veranstaltungkategorie_mitarbeiter;	
	   
		protected $veranstaltung_struck;	
       	protected $veranstaltung;
       	protected $veranstaltung_id;
       
		protected $start;
       	protected $ende;	

       	protected $start_jahr;
       	protected $ende_jahr;	
       	protected $start_jahr_monat;
       	protected $ende_jahr_monat;	
       	protected $start_jahr_woche;
       	protected $ende_jahr_woche;	
		
       	protected $suchtext;
	   
	    protected $freigabe;
	
       	protected $reservierung;
       	protected $reservierung_id;	   
		protected $reservierung_struck;	

//-----Konstruktor    

       function jahresplan($connectSQL,$veranstaltungskategorie_kurzbz="",$veranstaltung_id="",$reservierung_id="") 
       {
	
		$this->InitJahresplan();
	
		$this->setSchemaSQL('campus');
		$this->setConnectSQL($connectSQL);   
		$this->setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz);
		$this->setVeranstaltung_id($veranstaltung_id);
		$this->setReservierung_id($reservierung_id);
       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitJahresplan() 
       {
			$this->setError('');
			$this->InitVeranstaltungskategorie();
			$this->InitVeranstaltung();
       }
//-----Initialisierung Veranstaltungskategorie--------------------------------------------------------------------------------------------
       function InitVeranstaltungskategorie() 
       {
	      	$this->setVeranstaltungskategorie_kurzbz(''); 
	      	$this->setVeranstaltungskategorie('');
			$this->getStruckturVeranstaltungskategorie();

			$this->setVeranstaltungskategorieMitarbeiter('');
	}	
//-----veranstaltungskategorie_kurzbz--------------------------------------------------------------------------------------------
       function getVeranstaltungskategorie_kurzbz() 
       {
           return $this->veranstaltungskategorie_kurzbz;
       }
       function setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz) 
       {
           $this->veranstaltungskategorie_kurzbz=$veranstaltungskategorie_kurzbz;
       }
//-----veranstaltungskategorie Daten--------------------------------------------------------------------------------------------
       function getVeranstaltungskategorie() 
       {
           return $this->veranstaltungskategorie;
       }
       function setVeranstaltungskategorie($veranstaltungskategorie) 
       {
           $this->veranstaltungskategorie=$veranstaltungskategorie;
       }
//-----Kategorien nur fuer Mitarbeiter--------------------------------------------------------------------------------------------
       function getVeranstaltungskategorieMitarbeiter() 
       {
           return $this->veranstaltungkategorie_mitarbeiter;
       }
       function setVeranstaltungskategorieMitarbeiter($veranstaltungkategorie_mitarbeiter) 
       {
           $this->veranstaltungkategorie_mitarbeiter=$veranstaltungkategorie_mitarbeiter;
       }
//-----Veranstaltungskategorie Datenstrucktur--------------------------------------------------------------------------------------------
       function getStruckturVeranstaltungskategorie() 
       {
		$this->setTableSQL('tbl_veranstaltungskategorie');
		if ($this->veranstaltungkategorie_struck)	
			return $this->veranstaltungkategorie_struck;
		return $this->veranstaltungkategorie_struck=$this->setTableStruckturSQL();
       }
//-------------------------------------------------------------------------------------------------
       function saveVeranstaltungskategorie($param="")
       {
		// Initialisieren
		$this->setError('');
		$cSchemaSQL=$this->getSchemaSQL();
		
		// Konstante
		$arrTmpTableStrucktur=$this->getStruckturVeranstaltungskategorie();
		$constTableStrukturSQL=$this->getTableSQL();
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableStrukturSQL.'") gefunden !');
			return false;
		}	
		if (!is_array($param) || count($param)<1 )
			return false; // Fehler : es wurden keine Datenuebergeben 
		
		$this->setNewRecord(true);	
		if (isset($param['veranstaltungskategorie_kurzbz_old']) && !empty($param['veranstaltungskategorie_kurzbz_old']))
			$this->setNewRecord(false);


		$param_kurzbz=(isset($param['veranstaltungskategorie_kurzbz_old'])&& !empty($param['veranstaltungskategorie_kurzbz_old'])?$param['veranstaltungskategorie_kurzbz_old']:$param['veranstaltungskategorie_kurzbz']);
		$this->setVeranstaltungskategorie_kurzbz($param_kurzbz);
		
		// Check ob Daten vorhanden sind - Update wenn key_old belegt, 
		// oder Neuanlage und es duerfen keine Daten vorhanden sein
		$bTmpMerkNew=$this->getNewRecord();	
		if ($origVeranstaltungskategorie=$this->loadVeranstaltungskategorie())
		{
			if ($this->getNewRecord())
			{
				$this->setError($param_kurzbz .' bereits vorhanden !');
				return false;
			}	
		}
		elseif (!$this->getNewRecord()) // Keine Daten gefunden 
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
		}	
		$this->setNewRecord($bTmpMerkNew);	

		
		$cTmpSQL="";

		if ($this->getNewRecord())
		{
			$fildsList="";
			$fildsValue="";
			reset($arrTmpTableStrucktur);			
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				$cTmpWert='';
				if (isset($param[$arrTmpTableStrucktur[$fildIND]['name']]) 
				&& $param[$arrTmpTableStrucktur[$fildIND]['name']]!='')
					$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']];
				
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
       		$cTmpSQL=" insert into ".$cSchemaSQL.$constTableStrukturSQL." (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			$cTmpSQL.=" update ".$cSchemaSQL.$constTableStrukturSQL." set ";
			$fildsValue='';
			$fildsWhere="";			
			reset($arrTmpTableStrucktur);			
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				$cTmpWert='';
				if (isset($param[$arrTmpTableStrucktur[$fildIND]['name']]))
				{				
						$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']];
						switch ($arrTmpTableStrucktur[$fildIND]['type']) 
						{
					        case 'timestamp':
								if (empty($cTmpWert))
									$cTmpWert='null';
								else	
									$cTmpWert="to_timestamp('".$cTmpWert."')";						
			    	  	       	break;		
					        case 'time':
								if (empty($cTmpWert))
									$cTmpWert='null';
								else	
									$cTmpWert="to_timestamp('".$cTmpWert."')";						
					            break;					
	      					 default:
								if (empty($cTmpWert))
									$cTmpWert='null';
								else	
									$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
				          		break;
						}	
					$fildsValue.= (!empty($fildsValue)?',':'').$arrTmpTableStrucktur[$fildIND]['name']."=".$cTmpWert;
			       }						
			}
			$param['veranstaltungskategorie_kurzbz_old']=(isset($param['veranstaltungskategorie_kurzbz_old']) && !empty($param['veranstaltungskategorie_kurzbz_old'])?$param['veranstaltungskategorie_kurzbz_old']:$param['veranstaltungskategorie_kurzbz']);
			
			reset($arrTmpTableStrucktur);
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				if (!stristr($arrTmpTableStrucktur[$fildIND]['flag'],'primary') )
					continue;
				if (!isset($param[$arrTmpTableStrucktur[$fildIND]['name']."_old"]))
					continue;
				$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']."_old"];
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
				}
				$fildsWhere.=(empty($fildsWhere)?" where ": " and ") . $arrTmpTableStrucktur[$fildIND]['name']."=".$cTmpWert;  
			}			
			if (empty($fildsWhere)) return "Fehler ".$constTableStrukturSQL." Datenbearbeitung ";
			$cTmpSQL.=$fildsValue.$fildsWhere.";";  
		}
		$this->setStringSQL($cTmpSQL);
       	$this->setResultSQL(null);
       	if (!$this->dbQuery())
		{
			$this->setError($cTmpSQL);
 	        	return false;
		}
		// Beim Lesen ist ein Fehler aufgetreten
		if (!$this->loadVeranstaltungskategorie()) 
		{ 
			if ($this->getError())
				return false;
			$this->setNewRecord(true);
		}	
	$this->setStringSQL($cTmpSQL);
   	unset($cTmpSQL);

	$this->setResultSQL(null);       
	return $this->getVeranstaltungskategorie();
}
//-------------------------------------------------------------------------------------------------
       function deleteVeranstaltungskategorie($param="")
       {
		// Initialisieren
		$this->setError('');
		
		//Init
		$cTmpVeranstaltungskategorie_kurzbz="";
		$cSchemaSQL=$this->getschemaSQL();
		if (is_array($param) && isset($param['veranstaltungskategorie_kurzbz']))
			$this->setVeranstaltungskategorie_kurzbz($param['veranstaltungskategorie_kurzbz']);
       	else if (is_array($param) && !empty($param) )
			$this->setVeranstaltungskategorie_kurzbz($param);
		$cTmpVeranstaltungskategorie_kurzbz=$this->getVeranstaltungskategorie_kurzbz();
		
		// Konstante
		$arrTmpTableStrucktur=$this->getStruckturVeranstaltungskategorie();
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableStrukturSQL.'") gefunden !');
			return false;
		}	
		$constTableStrukturSQL=$this->getTableSQL();
		if (!is_array($param) || count($param)<1 )
			return false; // Fehler : es wurden keine Datenuebergeben 

		$cTmpSQL="";
		if ($origVeranstaltungskategorie=$this->loadVeranstaltungskategorie($cTmpVeranstaltungskategorie_kurzbz))
		{
       		$cTmpSQL="delete from ".$cSchemaSQL.$constTableStrukturSQL;
			if (is_array($cTmpVeranstaltungskategorie_kurzbz))
				$cTmpSQL.=" WHERE UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) in ('".strtoupper(   implode("','",$cTmpVeranstaltungskategorie_kurzbz))."'); ";
			else	
				$cTmpSQL.=" WHERE UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) = E'".strtoupper($cTmpVeranstaltungskategorie_kurzbz)."'; ";
		}
		if (empty($cTmpSQL))
			return 'bereits gel&ouml;scht';

		$this->setStringSQL($cTmpSQL);
       	$this->setResultSQL(null);
       	if (!$this->dbQuery())
		{
		#	$this->setError($cTmpSQL);
			if ($this->getError())
       	  		return false;
		}
		// Beim Lesen ist ein Fehler aufgetreten
		if (!$this->loadVeranstaltungskategorie()) 
		{ 
			if ($this->getError())
				return false;
			$this->setNewRecord(true);
		}	
	$this->setStringSQL($cTmpSQL);
   	unset($cTmpSQL);

	$this->setResultSQL(null);       
	return $this->getVeranstaltungskategorie();
}
//-------------------------------------------------------------------------------------------------
       function loadVeranstaltungskategorie($veranstaltungskategorie_kurzbz="")
       {
		//Init
		$this->setError('');

		$cTmpVeranstaltungskategorie_kurzbz="";
		$cSchemaSQL=$this->getschemaSQL();
       	if (!empty($veranstaltungskategorie_kurzbz) )
			$this->setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz);
		$cTmpVeranstaltungskategorie_kurzbz=$this->getVeranstaltungskategorie_kurzbz();
		// Kategorien nur fuer Mietarbeiter		
		$Veranstaltungkategorie_mitarbeiter=$this->getVeranstaltungskategorieMitarbeiter();
		
		$cTmpSQL="";
     		$cTmpSQL.="SELECT * FROM ".$cSchemaSQL."tbl_veranstaltungskategorie  ";
	       	$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz>'' ";

       	// Suche nach einer einzigen Veranstaltungskategorie_kurzbz
	       if (!is_array($cTmpVeranstaltungskategorie_kurzbz) && !empty($cTmpVeranstaltungskategorie_kurzbz) )
    	   	{
       		$cTmpSQL.=" AND UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz)=UPPER('".addslashes(trim($cTmpVeranstaltungskategorie_kurzbz))."') ";	
	       }
    	   	elseif (is_array($cTmpVeranstaltungskategorie_kurzbz) && count($cTmpVeranstaltungskategorie_kurzbz)>0 )
	       	{
      			if (isset($cTmpVeranstaltungskategorie_kurzbz[0]['veranstaltungskategorie_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$cTmpVeranstaltungskategorie_kurzbzE=array();
      				for ($indZEILE=0;$indZEILE<count($cTmpVeranstaltungskategorie_kurzbz);$indZEILE++)
      					$cTmpVeranstaltungskategorie_kurzbzE[]=addslashes(trim($cTmpVeranstaltungskategorie_kurzbz[$indZEILE]['veranstaltungskategorie_kurzbz']));
				$cTmpVeranstaltungskategorie_kurzbz=$cTmpVeranstaltungskategorie_kurzbzE;	
       		}
      			elseif (isset($cTmpVeranstaltungskategorie_kurzbz['veranstaltungskategorie_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   		$cTmpVeranstaltungskategorie_kurzbzE=array();
				$cTmpVeranstaltungskategorie_kurzbzE[]=addslashes(trim($cTmpVeranstaltungskategorie_kurzbz['veranstaltungskategorie_kurzbz']));
				$cTmpVeranstaltungskategorie_kurzbz=$cTmpVeranstaltungskategorie_kurzbzE;	
       		}
    	   		$cTmpSQL.=" AND UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) in ('".strtoupper(implode("','",$cTmpVeranstaltungskategorie_kurzbz))."') ";	
		}
		
		if ($Veranstaltungkategorie_mitarbeiter)
    	   		$cTmpSQL.=" AND UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) like '*%' ";	
		
       $cTmpSQL.=" ORDER BY ".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz ";	
       $cTmpSQL.=" OFFSET 0 LIMIT ALL FOR SHARE;";	
#	exit($cTmpSQL);
		   
       // Entfernen der Temporaeren Variablen aus dem Speicher
	    if (isset($cSchemaSQL)) unset($cSchemaSQL);
       	if (isset($cTmpVeranstaltungskategorie_kurzbzE)) unset($cTmpVeranstaltungskategorie_kurzbzE);
       	if (isset($cTmpVeranstaltungskategorie_kurzbz)) unset($cTmpVeranstaltungskategorie_kurzbz);
	   
       // Datenbankabfrage
		$this->setStringSQL($cTmpSQL);
		unset($cTmpSQL);

		$this->setResultSQL(null);
#		if (!$this->fetch_object()) 
		if (!$this->fetch_all()) 
			return false;    
		$this->setVeranstaltungskategorie($this->getResultSQL());
		$this->setResultSQL(null);
		return $this->getVeranstaltungskategorie();
       }
//-------------------------------------------------------------------------------------------------
//	------------------------ VERANSTALTUNG
//-----Initialisierung Veranstaltung--------------------------------------------------------------------------------------------

       function InitVeranstaltung() 
       {

	       	$this->setVeranstaltung_id('');
	       	$this->setVeranstaltung(''); 
	   
	       	$this->setStart(''); 
	       	$this->setEnde(''); 

	       	$this->setStart_jahr(''); 
	       	$this->setEnde_jahr(''); 

	       	$this->setStart_jahr_monat(''); 
	       	$this->setEnde_jahr_monat(''); 

	       	$this->setStart_jahr_woche(''); 
	       	$this->setEnde_jahr_woche(''); 
			
	       	$this->setStart_jahr_monat_tag(''); 
	       	$this->setEnde_jahr_monat_tag(''); 

			$this->setFreigabe(''); 
			$this->setSuchtext(''); 
			
			$this->getStruckturVeranstaltung(); 
       }
//-----veranstaltung_id--------------------------------------------------------------------------------------------
       function getVeranstaltung_id() 
       {
           return $this->veranstaltung_id;
       }
       function setVeranstaltung_id($veranstaltung_id="") 
       {
           $this->veranstaltung_id=$veranstaltung_id;
       }
//-----veranstaltung Daten--------------------------------------------------------------------------------------------
       function getVeranstaltung() 
       {
           return $this->veranstaltung;
       }
       function setVeranstaltung($veranstaltung) 
       {
           $this->veranstaltung=$veranstaltung;
       }

//-----start Jahr--------------------------------------------------------------------------------------------
       function getStart_jahr() 
       {
           return $this->start_jahr;
       }
       function setStart_jahr($start_jahr="") 
       {
		$this->start_jahr=$start_jahr;
       }		
//-----ende Jahr--------------------------------------------------------------------------------------------
       function getEnde_jahr() 
       {
           return $this->ende_jahr;
       }
       function setEnde_jahr($ende_jahr="") 
       {
		$this->ende_jahr=$ende_jahr;
       }		
//-----start JahrMonat--------------------------------------------------------------------------------------------
       function getStart_jahr_monat() 
       {
           return $this->start_jahr_monat;
       }
       function setStart_jahr_monat($start_jahr_monat="") 
       {
		$this->start_jahr_monat=$start_jahr_monat;
       }		
//-----ende JahrMonat--------------------------------------------------------------------------------------------
       function getEnde_jahr_monat() 
       {
           return $this->ende_jahr_monat;
       }
       function setEnde_jahr_monat($ende_jahr_monat="") 
       {
		$this->ende_jahr_monat=$ende_jahr_monat;
       }			

//-----start JahrMonat--------------------------------------------------------------------------------------------
       function getStart_jahr_woche() 
       {
           return $this->start_jahr_woche;
       }
       function setStart_jahr_woche($start_jahr_woche="") 
       {
		$this->start_jahr_woche=$start_jahr_woche;
       }		
//-----ende JahrMonat--------------------------------------------------------------------------------------------
       function getEnde_jahr_woche() 
       {
           return $this->ende_jahr_woche;
       }
       function setEnde_jahr_woche($ende_jahr_woche="") 
       {
		$this->ende_jahr_woche=$ende_jahr_woche;
       }			
//-----start JahrMonat--------------------------------------------------------------------------------------------
       function getStart_jahr_monat_tag() 
       {
           return $this->start_jahr_monat_tag;
       }
       function setStart_jahr_monat_tag($start_jahr_monat_tag="") 
       {
		$this->start_jahr_monat_tag=$start_jahr_monat_tag;
       }		

//-----ende JahrMonat--------------------------------------------------------------------------------------------
       function getEnde_jahr_monat_tag() 
       {
           return $this->ende_jahr_monat_tag;
       }
       function setEnde_jahr_monat_tag($ende_jahr_monat_tag="") 
       {
		$this->ende_jahr_monat_tag=$ende_jahr_monat_tag;
       }			
//-----start--------------------------------------------------------------------------------------------
       function getStart() 
       {
           return $this->start;
       }
       function setStart($start="") 
       {
		if (!empty($start) && !is_numeric($start)) // Start wurde als Datum Zeit uebergeben
		{	
			$start=str_replace('-','.',$start);
			$param=explode(' ',$start);
			$date=explode('.',$param[0]);
			if (!isset($param[1])) $param[1]='00:01:00';
			$time=explode(':',$param[1]);
			if (@checkdate($date[1], $date[0], $date[2]) )
			{			
				if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], 0, $date[1],$date[0],$date[2] )))
					$start=$cTmpTimeStampWert;	
			}	
		}	
		$this->start=$start;
       }		

//-----ende--------------------------------------------------------------------------------------------
       function getEnde() 
       {
           return $this->ende;
       }
       function setEnde($ende="") 
       {
		if (!empty($ende) && !is_numeric($ende)) // Start wurde als Datum Zeit uebergeben
		{	
			$ende=str_replace('-','.',$ende);
			$param=explode(' ',$ende);
			$date=explode('.',$param[0]);
			if (!isset($param[1])) $param[1]='23:59:59';
			$time=explode(':',$param[1]);
			if (@checkdate($date[1], $date[0], $date[2]) )
			{			
				if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], 0, $date[1],$date[0],$date[2] )))
					$ende=$cTmpTimeStampWert;	
			}	
		}	
		$this->ende=$ende;
       }	
//-----freigabe--------------------------------------------------------------------------------------------
       function getFreigabe() 
       {
           return $this->freigabe;
       }
       function setFreigabe($freigabe="") 
       {
           $this->freigabe=$freigabe;
       }		      
//-----freigabe--------------------------------------------------------------------------------------------
       function getSuchtext() 
       {
           return $this->suchtext;
       }
       function setSuchtext($suchtext="") 
       {
           $this->suchtext=$suchtext;
       }		      

//-----Veranstaltung Daten--------------------------------------------------------------------------------------------
       function getStruckturVeranstaltung() 
       {
		$this->setTableSQL('tbl_veranstaltung');
		if ($this->veranstaltung_struck)	
			return $this->veranstaltung_struck;
		return $this->veranstaltung_struck=$this->setTableStruckturSQL();
       }	
	   
//-------------------------------------------------------------------------------------------------
       function saveVeranstaltung($param="")
       {
		// Initialisieren
		$this->setError('');
		$cSchemaSQL=$this->getSchemaSQL();
		
		// Konstante
		$arrTmpTableStrucktur=$this->getStruckturVeranstaltung();
		$constTableStrukturSQL=$this->getTableSQL();
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableStrukturSQL.'") gefunden !');
			return false;
		}	

		if (!is_array($param) || count($param)<1 )
			return false; // Fehler : es wurden keine Datenuebergeben 
		
		$this->setNewRecord(true);	
		if (isset($param['veranstaltung_id_old']) && $param['veranstaltung_id_old']>0)
			$this->setNewRecord(false);

		$param_id=(isset($param['veranstaltung_id_old'])&& !empty($param['veranstaltung_id_old'])?$param['veranstaltung_id_old']:$param['veranstaltung_id']);
		$this->setVeranstaltung_id($param_id);

		if ( (empty($param['veranstaltung_id']) && !empty($param['veranstaltung_id_old'])) ) 
		{
			$this->setError('Keine Veranstaltungs ID gefunden !');
			return false;
		}	

		if (!isset($param['veranstaltungskategorie_kurzbz']) || empty($param['veranstaltungskategorie_kurzbz']) ) 
			$param['veranstaltungskategorie_kurzbz']=$this->getVeranstaltungskategorie_kurzbz();
		if (empty($param['veranstaltungskategorie_kurzbz']) ) 
		{
			$this->setError('Keine Veranstaltungskategorie gefunden !');
			return false;
		}	
		$this->setVeranstaltungskategorie_kurzbz($param['veranstaltungskategorie_kurzbz']);
		
		$bTmpMerkNew=$this->getNewRecord();	
		// Check ob Daten vorhanden sind - Update wenn key_old belegt, 
		// oder Neuanlage und es duerfen keine Daten vorhanden sein
		if ($origVeranstaltungskategorie=$this->loadVeranstaltung())
		{

			if ($this->getNewRecord())
			{
				$this->setError($param_id .' bereits vorhanden !');
				return false;
			}	
		}
		elseif (!$this->getNewRecord()) // Keine Daten gefunden 
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
		}	
		$this->setNewRecord($bTmpMerkNew);		

		$cTmpSQL="";

		if ($this->getNewRecord())
		{
		
			$fildsList="";
			$fildsValue="";
			reset($arrTmpTableStrucktur);			
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				$cTmpWert='';
				if (isset($param[$arrTmpTableStrucktur[$fildIND]['name']]) 
				&& $param[$arrTmpTableStrucktur[$fildIND]['name']]!='')
					$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']];
				
				if ($cTmpWert!='')
				{	
					switch ($arrTmpTableStrucktur[$fildIND]['type']) 
					{
			        case 'timestamp':

						if (!empty($cTmpWert) && !is_numeric($cTmpWert)) // Start wurde als Datum Zeit uebergeben
						{	
							$cTmpWert=str_replace('-','.',$cTmpWert);
							$dateparam=explode(' ',$cTmpWert);
							$date=explode('.',$dateparam[0]);
							if (!isset($param[1])) $dateparam[1]='00:01:00';
							$time=explode(':',$dateparam[1]);
							if (@checkdate($date[1], $date[0], $date[2]) )
							{			
								if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], 0, $date[1],$date[0],$date[2] )))
								{
									$cTmpWert=$cTmpTimeStampWert;	
								}
							}	
						}	
						$cTmpWert="to_timestamp(".$cTmpWert.")";						
			            break;		
			        case 'time':
						$cTmpWert="to_timestamp(".$cTmpWert.")";						
			            break;					
			        case 'date':
						$cTmpWert="to_date('".$cTmpWert."')";						
			            break;					
				     
      				 default:
						$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
			          	break;
				    }						
					$fildsList.=(!empty($fildsList)?',':'').$arrTmpTableStrucktur[$fildIND]['name'];	
					$fildsValue.=(!empty($fildsValue)?',':'').$cTmpWert;
				}	
			}
       		$cTmpSQL.=" insert into ".$cSchemaSQL.$constTableStrukturSQL." (".$fildsList.") values (".$fildsValue."); ";
			$cTmpSQL.=" select max(".$cSchemaSQL.$constTableStrukturSQL.".veranstaltung_id) from ".$cSchemaSQL.$constTableStrukturSQL."; "; 

		}
		else
		{
			$cTmpSQL.=" update ".$cSchemaSQL.$constTableStrukturSQL." set ";
			$fildsValue='';
			$fildsWhere="";			
			reset($arrTmpTableStrucktur);			
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				$cTmpWert='';
				if (isset($param[$arrTmpTableStrucktur[$fildIND]['name']]))
				{				
					$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']];
					
					if (stristr($arrTmpTableStrucktur[$fildIND]['type'],'int'))
						$arrTmpTableStrucktur[$fildIND]['type']='int';		
					switch ($arrTmpTableStrucktur[$fildIND]['type']) 
					{
					        case 'timestamp':
								if (!empty($cTmpWert) && !is_numeric($cTmpWert)) // Start wurde als Datum Zeit uebergeben
								{	
									$cTmpWert=str_replace('-','.',$cTmpWert);
									$dateparam=explode(' ',$cTmpWert);
									$date=explode('.',$dateparam[0]);
									if (!isset($dateparam[1])) $dateparam[1]='00:01:00';
									$time=explode(':',$dateparam[1]);
									if (@checkdate($date[1], $date[0], $date[2]) )
									{			
										if (is_numeric($cTmpTimeStampWert=@mktime($time[0], $time[1], 0, $date[1],$date[0],$date[2] )))
										{
											$cTmpWert=$cTmpTimeStampWert;	
										}
									}	
								}		
								if (empty($cTmpWert))	
									$cTmpWert='null';	
								else
									$cTmpWert="to_timestamp(".$cTmpWert.")";	
			    	  	       	break;		
					        case 'time':
								if (empty($cTmpWert))	
									$cTmpWert='null';	
								else							
									$cTmpWert="to_timestamp(".$cTmpWert.")";	
					            break;					
					        case 'int':
								$cTmpWert="0$cTmpWert";						
				            break;					
      					 default:
								if (empty($cTmpWert))	
									$cTmpWert='null';
								else
									$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
				          		break;
						}	
						$fildsValue.= (!empty($fildsValue)?',':'').$arrTmpTableStrucktur[$fildIND]['name']."=".$cTmpWert;
				}						
			}
			$param['veranstaltung_id_old']=(isset($param['veranstaltung_id_old']) && !empty($param['veranstaltung_id_old'] )?$param['veranstaltung_id_old']:$param['veranstaltung_id']);
			
			reset($arrTmpTableStrucktur);
			for ($fildIND=0;$fildIND<count($arrTmpTableStrucktur);$fildIND++)
			{
				if (!stristr($arrTmpTableStrucktur[$fildIND]['flag'],'primary') )
					continue;
				if (!isset($param[$arrTmpTableStrucktur[$fildIND]['name']."_old"]))
					continue;
				$cTmpWert=$param[$arrTmpTableStrucktur[$fildIND]['name']."_old"];
				if ($cTmpWert=='')
					continue;
				
					if (stristr($arrTmpTableStrucktur[$fildIND]['type'],'int'))
						$arrTmpTableStrucktur[$fildIND]['type']='int';
					switch ($arrTmpTableStrucktur[$fildIND]['type']) 
					{
				        case 'timestamp':
							$cTmpWert="to_timestamp(".$cTmpWert.")";						
		    	  	       	break;		
				        case 'time':
							$cTmpWert="to_timestamp(".$cTmpWert.")";						
				            break;					
				        case 'int':
							$cTmpWert=$cTmpWert;						
				            break;					
      					 default:
							$cTmpWert="E'".addslashes(trim($cTmpWert))."'";
			          		break;
					}	
				$fildsWhere.=(empty($fildsWhere)?" where ": " and ") . $arrTmpTableStrucktur[$fildIND]['name']."=".$cTmpWert;  
			}			
			if (empty($fildsWhere)) 
			{
				$this->setError(" Fehler ".$constTableStrukturSQL." Datenbearbeitung ");
				return false;
			}	
			$cTmpSQL.=$fildsValue.$fildsWhere.";";  
		}
	
		$this->setStringSQL($cTmpSQL);
       	$this->setResultSQL(null);
		if ($this->getNewRecord())
		{
			if (!$this->fetch_object())
			{
				if ($this->getError())
	        	 	return false;
			}
			$iTmpMax=$this->getResultSQL();
			$this->setVeranstaltung_id($iTmpMax->max);
			$param['veranstaltung_id']=$this->getVeranstaltung_id();
		}
       	elseif (!$this->dbQuery())
		{
			if ($this->getError())
	         	return false;
		}
		// Beim Lesen ist ein Fehler aufgetreten
		if (!$this->loadVeranstaltung()) 
		{ 
			if ($this->getError())
				return false;
			$this->setNewRecord(true);
		}	
	$this->setStringSQL($cTmpSQL);
   	unset($cTmpSQL);

	$this->setResultSQL(null);       
	return $this->getVeranstaltung();
}
//-------------------------------------------------------------------------------------------------
       function deleteVeranstaltung($param="")
       {
		// Initialisieren
		$this->setError('');
		
		//Init
		$cTmpVeranstaltung_id="";
		$cSchemaSQL=$this->getschemaSQL();
		if (is_array($param) && isset($param['veranstaltung_id']))
			$this->setVeranstaltung_id($param['veranstaltung_id']);
       	else if (!is_array($param) && !empty($param) )
			$this->setVeranstaltung_id($param);
		$cTmpVeranstaltung_id=$this->getVeranstaltung_id();
		
		$cTmpVeranstaltungskategorie_kurzbz="";
		if (!isset($param['veranstaltungskategorie_kurzbz']) || empty($param['veranstaltungskategorie_kurzbz']) ) 
			$param['veranstaltungskategorie_kurzbz']=$this->getVeranstaltungskategorie_kurzbz();
		if (is_array($param) && isset($param['veranstaltungskategorie_kurzbz']))
			$this->setVeranstaltungskategorie_kurzbz($param['veranstaltungskategorie_kurzbz']);
		$cTmpVeranstaltungskategorie_kurzbz=$this->getVeranstaltungskategorie_kurzbz();
	
		// Konstante
		$arrTmpTableStrucktur=$this->getStruckturVeranstaltung();
		$constTableStrukturSQL=$this->getTableSQL();
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableStrukturSQL.'") gefunden !');
			return false;
		}	

		if (!is_array($param) || count($param)<1 )
			return false; // Fehler : es wurden keine Datenuebergeben 

		$cTmpSQL="";

			$cTmpSQL.="BEGIN;  ";
       		$cTmpSQL.="update  ".$cSchemaSQL."tbl_reservierung set veranstaltung_id=null ";
			if (is_array($cTmpVeranstaltung_id))
				$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_reservierung.veranstaltung_id in (".implode(",",$cTmpVeranstaltung_id)."); ";
			else if (!empty($cTmpVeranstaltung_id))	
				$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_reservierung.veranstaltung_id =".$cTmpVeranstaltung_id."; ";
			else 
				$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_reservierung.veranstaltung_id =".$cTmpVeranstaltung_id."; ";

       		$cTmpSQL.="delete from ".$cSchemaSQL.$constTableStrukturSQL;
			if (is_array($cTmpVeranstaltung_id))
				$cTmpSQL.=" WHERE ".$cSchemaSQL.$constTableStrukturSQL.".veranstaltung_id in (".implode(",",$cTmpVeranstaltung_id)."); ";
			else if (!empty($cTmpVeranstaltung_id))	
				$cTmpSQL.=" WHERE ".$cSchemaSQL.$constTableStrukturSQL.".veranstaltung_id =".$cTmpVeranstaltung_id."; ";
			else 
				$cTmpSQL.=" WHERE ".$cSchemaSQL.$constTableStrukturSQL.".veranstaltung_id =".$cTmpVeranstaltungskategorie_kurzbz."; ";
			$cTmpSQL.=" COMMIT; ";   
	
		$this->setStringSQL($cTmpSQL);
       	$this->setResultSQL(null);
       	if (!$this->dbQuery())
		{
			if ($this->getError())
        	 	return false;
		}
		// Beim Lesen ist ein Fehler aufgetreten
		if (!$this->loadVeranstaltung()) 
		{ 
			if ($this->getError())
				return false;
			$this->setNewRecord(true);
		}	
	$this->setStringSQL($cTmpSQL);
   	unset($cTmpSQL);

	$this->setResultSQL(null);       
	return true;
}

	   
//-------------------------------------------------------------------------------------------------
       function loadVeranstaltung($veranstaltungskategorie_kurzbz="",$veranstaltung_id="",$freigabe="")
       {
		//Init
		$this->setError('');
		$cSchemaSQL=$this->getschemaSQL();

		$cTmpVeranstaltung_id='';
       	if ($veranstaltung_id!='')
			$this->setVeranstaltung_id($veranstaltung_id);
		$cTmpVeranstaltung_id=$this->getVeranstaltung_id();
				
		$cTmpVeranstaltungskategorie_kurzbz="";
       	if ($veranstaltungskategorie_kurzbz!='')
			$this->setVeranstaltungskategorie_kurzbz($veranstaltungskategorie_kurzbz);
		$cTmpVeranstaltungskategorie_kurzbz=$this->getVeranstaltungskategorie_kurzbz();
		
		$cTmpFreigabe="";
       	if ($freigabe!='')
			$this->setFreigabe($freigabe);
		$cTmpFreigabe=$this->getFreigabe();
		

		$cTmpSuchtext=$this->getSuchtext();		

		$cTmpStart=$this->getStart();
		$cTmpEnde=$this->getEnde();

		// Selektion
		$cTmpStart_jahr=$this->getStart_jahr();
		$cTmpEnde_jahr=$this->getEnde_jahr();

		$cTmpStart_jahr_monat=$this->getStart_jahr_monat();
		$cTmpEnde_jahr_monat=$this->getEnde_jahr_monat();
	
		$cTmpStart_jahr_woche=$this->getStart_jahr_woche();
		$cTmpEnde_jahr_woche=$this->getEnde_jahr_woche();

		$cTmpStart_jahr_monat_tag=$this->getStart_jahr_monat_tag();
		$cTmpEnde_jahr_monat_tag=$this->getEnde_jahr_monat_tag();
	
		$Veranstaltungkategorie_mitarbeiter=$this->getVeranstaltungskategorieMitarbeiter();
		
		$cTmpSQL="";
     		$cTmpSQL.="SELECT tbl_veranstaltung.* ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'YYYYMMDD') as \"start_jjjjmmtt\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'YYYYMMDD') as \"ende_jjjjmmtt\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'YYYYMM') as \"start_jahr_monat\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'YYYYMM') as \"ende_jahr_monat\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'YYYY') as \"start_jahr\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'YYYY') as \"ende_jahr\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'MM') as \"start_monat\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'MM') as \"ende_monat\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'DD') as \"start_tag\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'DD') as \"ende_tag\" ";
			
			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'Day') as \"start_tagname\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'Day') as \"ende_tagname\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'IW') as \"start_woche\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'IW') as \"ende_woche\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'Q') as \"start_quartal\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'Q') as \"ende_quartal\" ";
			
			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_veranstaltung.start) as \"start_timestamp\" ";
			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_veranstaltung.ende) as \"ende_timestamp\" ";


			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'DD.MM.YYYY') as \"start_datum\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'DD.MM.YYYY') as \"ende_datum\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.start, 'HH24:MI') as \"start_zeit\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.ende, 'HH24:MI') as \"ende_zeit\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.insertamum, 'DD.MM.YYYY') as \"insertamum_datum\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.insertamum, 'HH24:MI') as \"insertamum_zeit\" ";
			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_veranstaltung.insertamum) as \"insertamum_timestamp\" ";

			$cTmpSQL.=", to_char(tbl_veranstaltung.updateamum, 'DD.MM.YYYY') as \"updateamum_datum\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.updateamum, 'HH24:MI') as \"updateamum_zeit\" ";
			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_veranstaltung.updateamum) as \"updateamum_timestamp\" ";
		
			$cTmpSQL.=", to_char(tbl_veranstaltung.freigabeamum, 'DD.MM.YYYY') as \"freigabeamum_datum\" ";
			$cTmpSQL.=", to_char(tbl_veranstaltung.freigabeamum, 'HH24:MI') as \"freigabeamum_zeit\" ";
			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_veranstaltung.freigabeamum) as \"freigabeamum_timestamp\" ";

     		$cTmpSQL.=",tbl_veranstaltungskategorie.*,tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz as kategorie_kurzbz  ";

   		$cTmpSQL.=" FROM ".$cSchemaSQL."tbl_veranstaltungskategorie  ";

   		$cTmpSQL.=" LEFT JOIN ".$cSchemaSQL."tbl_veranstaltung ON UPPER(".$cSchemaSQL."tbl_veranstaltung.veranstaltungskategorie_kurzbz)=UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) ";
    	if (!is_array($cTmpVeranstaltung_id) && $cTmpVeranstaltung_id!='' )
		{
      				$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.veranstaltung_id=".$cTmpVeranstaltung_id." ";	
		}			
 
    	$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz>'' ";

		if ($cTmpFreigabe)
			$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.freigabevon>E'' ";
		
       	// Suche nach einer einzigen Veranstaltung_id
	    if (!is_array($cTmpVeranstaltung_id) && !empty($cTmpVeranstaltung_id) )
    		{
       		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.veranstaltung_id=".$cTmpVeranstaltung_id." ";	
       	}
   	   	elseif (is_array($cTmpVeranstaltung_id) && count($cTmpVeranstaltung_id)>0 )
       	{
      			if (isset($cTmpVeranstaltung_id[0]['veranstaltung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$cTmpVeranstaltung_idE=array();
      				for ($indZEILE=0;$indZEILE<count($cTmpVeranstaltung_id);$indZEILE++)
      					$cTmpVeranstaltung_idE[]=trim($cTmpVeranstaltung_id[$indZEILE]['veranstaltung_id']);
				$cTmpVeranstaltung_id=$cTmpVeranstaltung_idE;	
       		}
      			elseif (isset($cTmpVeranstaltung_id['veranstaltung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   		$cTmpVeranstaltung_idE=array();
				$cTmpVeranstaltung_idE[]=trim($cTmpVeranstaltung_id['veranstaltung_id']);
				$cTmpVeranstaltung_id=$cTmpVeranstaltung_idE;	
       		}
    	   		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.veranstaltung_id in (".strtoupper(implode(",",$cTmpVeranstaltung_id)).") ";	
		}		
		
       	// Suche nach einer einzigen Veranstaltungskategorie_kurzbz
	       if (!is_array($cTmpVeranstaltungskategorie_kurzbz) && $cTmpVeranstaltungskategorie_kurzbz!='' )
    	   	{
       		$cTmpSQL.=" AND UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz)=UPPER('".addslashes(trim($cTmpVeranstaltungskategorie_kurzbz))."') ";	
	       }
    	   	elseif (is_array($cTmpVeranstaltungskategorie_kurzbz) && count($cTmpVeranstaltungskategorie_kurzbz)>0 )
	       	{
      			if (isset($cTmpVeranstaltungskategorie_kurzbz[0]['veranstaltungskategorie_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$cTmpVeranstaltungskategorie_kurzbzE=array();
      				for ($indZEILE=0;$indZEILE<count($cTmpVeranstaltungskategorie_kurzbz);$indZEILE++)
      					$cTmpVeranstaltungskategorie_kurzbzE[]=addslashes(trim($cTmpVeranstaltungskategorie_kurzbz[$indZEILE]['veranstaltungskategorie_kurzbz']));
				$cTmpVeranstaltungskategorie_kurzbz=$cTmpVeranstaltungskategorie_kurzbzE;	
       		}
      			elseif (isset($cTmpVeranstaltungskategorie_kurzbz['veranstaltungskategorie_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
   	   			$cTmpVeranstaltungskategorie_kurzbzE=array();
				$cTmpVeranstaltungskategorie_kurzbzE[]=trim($cTmpVeranstaltungskategorie_kurzbz['veranstaltungskategorie_kurzbz']);
				$cTmpVeranstaltungskategorie_kurzbz=$cTmpVeranstaltungskategorie_kurzbzE;	
       		}
    	   		$cTmpSQL.=" AND UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) in ('".strtoupper(implode("','",$cTmpVeranstaltungskategorie_kurzbz))."') ";	
		}


		if (!empty($cTmpStart) && empty($cTmpEnde) )
	   		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.start=to_timestamp(".$cTmpStart.") ";	
		else if (empty($cTmpStart) && !empty($cTmpEnde) )
	   		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_veranstaltung.ende=to_timestamp(".$cTmpEnde.") ";	
		else if (!empty($cTmpStart) && !empty($cTmpEnde) )
		{
	   		$cTmpSQL.=" AND to_timestamp(".$cTmpStart.") >=to_timestamp(".$cSchemaSQL."tbl_veranstaltung.start) ";	
	   		$cTmpSQL.=" AND to_timestamp(".$cTmpEnde.") <= to_timestamp(".$cSchemaSQL."tbl_veranstaltung.ende) ";	
		}	

		if (!empty($cTmpStart_jahr) && empty($cTmpEnde_jahr))
	   		$cTmpSQL.=" AND to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYY')='".$cTmpStart_jahr."'";	
		elseif (empty($cTmpStart_jahr) && !empty($cTmpEnde_jahr) )
	   		$cTmpSQL.=" AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYY')='".$cTmpEnde_jahr."'";	
		elseif (empty($cTmpStart_jahr) && !empty($cTmpEnde_jahr) )
		{
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYY') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYY') ";	
	   		$cTmpSQL.=" AND '".$cTmpEnde_jahr."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYY') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYY') ";	
		}

			
		if (!empty($cTmpStart_jahr_monat) && empty($cTmpEnde_jahr_monat) )
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_monat."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMM') and  to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMM')";	
		elseif (empty($cTmpStart_jahr_monat) && !empty($cTmpEnde_jahr_monat) )
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_monat."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMM') and  to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMM')";	
		elseif (!empty($cTmpStart_jahr_monat) && !empty($cTmpEnde_jahr_monat) )
		{
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_monat."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMM') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMM') ";	
	   		$cTmpSQL.=" AND '".$cTmpEnde_jahr_monat."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMM') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMM') ";	
		}
			
		if (!empty($cTmpStart_jahr_woche) && empty($cTmpEnde_jahr_woche) )
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_woche."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYIW'') and  to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYIW'')";	
		elseif (empty($cTmpStart_jahr_woche) && !empty($cTmpEnde_jahr_woche) )
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_woche."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYIW'') and  to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYIW'')";	
		elseif (!empty($cTmpStart_jahr_woche) && !empty($cTmpEnde_jahr_woche) )
		{
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_woche."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYIW'') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYIW'') ";	
	   		$cTmpSQL.=" AND '".$cTmpEnde_jahr_woche."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYIW'') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYIW'') ";	
		}			
					
		if (!empty($cTmpStart_jahr_monat_tag) && empty($cTmpEnde_jahr_monat_tag) )
	   		$cTmpSQL.=" AND to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMMDD')>='".$cTmpStart_jahr_monat_tag."'";	
		elseif (empty($cTmpStart_jahr_monat_tag) && !empty($cTmpEnde_jahr_monat_tag) )
	   		$cTmpSQL.=" AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMMDD')<='".$cTmpEnde_jahr_monat_tag."'";	
		elseif (!empty($cTmpStart_jahr_monat_tag) && !empty($cTmpEnde_jahr_monat_tag) )
		{
	   		$cTmpSQL.=" AND '".$cTmpStart_jahr_monat_tag."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMMDD') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMMDD') ";	
	   		$cTmpSQL.=" AND '".$cTmpEnde_jahr_monat_tag."' between to_char(".$cSchemaSQL."tbl_veranstaltung.start, 'YYYYMMDD') AND to_char(".$cSchemaSQL."tbl_veranstaltung.ende, 'YYYYMMDD') ";	
		}

		if ($cTmpSuchtext)
		{
			$cTmpSuchtext="%$cTmpSuchtext%";
			$cTmpSuchtext=str_replace(' ','%',$cTmpSuchtext);
			$cTmpSuchtext=str_replace('%%','%',addslashes(strtoupper(trim($cTmpSuchtext))));
	   		$cTmpSQL.=" AND ( UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) like E'".$cTmpSuchtext."'
						OR   UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.bezeichnung) like E'".$cTmpSuchtext."'
						OR UPPER(".$cSchemaSQL."tbl_veranstaltung.beschreibung) like E'".$cTmpSuchtext."'
						OR UPPER(".$cSchemaSQL."tbl_veranstaltung.inhalt) like E'".$cTmpSuchtext."' ) ";	
		}		

		// Kategorien nur fuer Mietarbeiter		
		if (!$Veranstaltungkategorie_mitarbeiter)
    	   	$cTmpSQL.=" AND NOT UPPER(".$cSchemaSQL."tbl_veranstaltungskategorie.veranstaltungskategorie_kurzbz) like '*%' ";	
	
		if (!empty($cTmpStart) || !empty($cTmpEnde) || !empty($cTmpStart_jahr) || !empty($cTmpEnde_jahr) || !empty($cTmpStart_jahr_monat) || !empty($cTmpEnde_jahr_monat)  || !empty($cTmpStart_jahr_monat_tag) || !empty($cTmpEnde_jahr_monat_tag) )
    	   		$cTmpSQL.=" ORDER BY ".$cSchemaSQL."tbl_veranstaltung.start, tbl_veranstaltungskategorie.bezeichnung  ";	
		else
	     		$cTmpSQL.=" ORDER BY ".$cSchemaSQL."tbl_veranstaltungskategorie.bezeichnung, tbl_veranstaltung.start  ";	

	$cTmpSQL.=" OFFSET 0 LIMIT ALL ;";	

       // Entfernen der Temporaeren Variablen aus dem Speicher
	if (isset($cSchemaSQL)) unset($cSchemaSQL);
	if (isset($cTmpVeranstaltungskategorie_kurzbzE)) unset($cTmpVeranstaltungskategorie_kurzbzE);
     	if (isset($cTmpVeranstaltungskategorie_kurzbz)) unset($cTmpVeranstaltungskategorie_kurzbz);
	 
       // Datenbankabfrage
		$this->setStringSQL($cTmpSQL);
		unset($cTmpSQL);
		
		$this->setResultSQL(null);
#		if (!$this->fetch_object()) 
		if (!$this->fetch_all()) 
			return false;   
			 
		$this->setVeranstaltung($this->getResultSQL());
		$this->setResultSQL(null);
	   
		return $this->getVeranstaltung();
       }


//-----Initialisierung Veranstaltungskategorie--------------------------------------------------------------------------------------------
       function InitReservierung() 
       {
	      	$this->setReservierung_id(''); 
	      	$this->setReservierung('');
	}	
	
//-----reservierung_id--------------------------------------------------------------------------------------------
       function getReservierung_id() 
       {
           return $this->reservierung_id;
       }
       function setReservierung_id($reservierung_id="") 
       {
           $this->reservierung_id=$reservierung_id;
       }	   
//-----reservierung Daten--------------------------------------------------------------------------------------------
       function getReservierung() 
       {
           return $this->reservierung;
       }
       function setReservierung($reservierung="") 
       {
           $this->reservierung=$reservierung;
       }	
//-----Veranstaltung Daten--------------------------------------------------------------------------------------------
       function getStruckturReservierung() 
       {
		$this->setTableSQL('tbl_reservierung');
		if ($this->reservierung_struck)	
			return $this->reservierung_struck;
		return $this->reservierung_struck=$this->setTableStruckturSQL();
       }	   	      
	
	

//-------------------------------------------------------------------------------------------------
       function saveReservierung($param="")
       {
		// Initialisieren
		$this->setError('');
		$cSchemaSQL=$this->getSchemaSQL();
		
		// Konstante
		$arrTmpTableStrucktur=$this->getStruckturReservierung();
		$constTableStrukturSQL=$this->getTableSQL();
		if (!is_array($arrTmpTableStrucktur)) 
		{
			$this->setError('Kein Tabellenstrucktur ("'.$constTableStrukturSQL.'") gefunden !');
			return false;
		}	

		if (is_array($param) && count($param)>0 && isset($param['reservierung_id'])) 
			$this->setReservierung_id($param['reservierung_id']);
		if (!is_array($param) && !empty($param)) 
			$this->setReservierung_id($param);
		$reservierung_id=$this->getReservierung_id();
		if ( empty($reservierung_id) ) 
		{
			$this->setError('Keine Reservierung ID gefunden !');
			return false;
		}	
		if (is_array($param) && count($param)>0 && isset($param['veranstaltung_id'])) 
		{
			$this->setVeranstaltung_id($param['veranstaltung_id']);
		}	
		$veranstaltung_id=$this->getVeranstaltung_id();

		$cTmpSQLS="";
		$cTmpSQLS.=" update ".$cSchemaSQL."tbl_reservierung set veranstaltung_id=".(!empty($veranstaltung_id)?"'$veranstaltung_id'":"null")." WHERE reservierung_id='".$reservierung_id."'; " ;

#exit($cTmpSQLS);

		$this->setStringSQL($cTmpSQLS);
       	$this->setResultSQL(null);
       	if (!$this->dbQuery())
		{
			if ($this->getError())
        	 	return false;
		}
		
		// Beim Lesen ist ein Fehler aufgetreten
		if (!$this->loadReservierung()) 
		{ 
			if ($this->getError())
				return false;
			$this->setNewRecord(true);
		}	
	$this->setStringSQL($cTmpSQLS);
   	unset($cTmpSQLS);

	$this->setResultSQL(null);       
	return $this->getReservierung();

	}	
//-------------------------------------------------------------------------------------------------
       function loadReservierung($reservierung_id="",$veranstaltung_id="")
       {
		//Init
		$this->setError('');
		$cSchemaSQL=$this->getschemaSQL();

		$cTmpReservierung_id='';
       	if ($reservierung_id!='')
			$this->setreservierung_id($reservierung_id);
		$cTmpReservierung_id=$this->getreservierung_id();

		$cTmpVeranstaltung_id='';
       	if ($veranstaltung_id!='')
			$this->setVeranstaltung_id($veranstaltung_id);
		$cTmpVeranstaltung_id=$this->getVeranstaltung_id();
		
		
		$cTmpStart=$this->getStart();
		if (!empty($cTmpStart) && is_numeric($cTmpStart))
			$cTmpStart=strftime('%Y%m%d',$cTmpStart);
		$cTmpEnde=$this->getEnde();
		if (!empty($cTmpEnde) && is_numeric($cTmpEnde))
			$cTmpEnde=strftime('%Y%m%d',$cTmpEnde);
			
		$cTmpStartZeit=$this->getStart();
		if (!empty($cTmpStart) && is_numeric($cTmpStartZeit))
			$cTmpStartZeit=date('Hi',$cTmpStartZeit);
		$cTmpEndeZeit=$this->getEnde();
		if (!empty($cTmpEndeZeit) && is_numeric($cTmpEndeZeit))
			$cTmpEndeZeit=date('Hi',$cTmpEndeZeit);			
		
		// Selektion
		
			$cTmpSQL="";
     		$cTmpSQL.="SELECT tbl_reservierung.* ";
     		
			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'YYYYMMDD') as \"datum_jjjjmmtt\" ";
			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'YYYYMM') as \"datum_jahr_monat\" ";
			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'YYYY') as \"datum_jahr\" ";

			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'WW') as \"datum_woche\" ";

			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'Q') as \"datum_quartal\" ";
			
			$cTmpSQL.=", to_char(tbl_reservierung.datum, 'DD.MM.YYYY') as \"datum_anzeige\" ";
		
			$cTmpSQL.=", lehre.tbl_stunde.beginn, lehre.tbl_stunde.ende ";	
			$cTmpSQL.=", to_char(lehre.tbl_stunde.beginn, 'HH24:MI') as \"beginn_anzeige\" ";
			$cTmpSQL.=", to_char(lehre.tbl_stunde.ende, 'HH24:MI') as \"ende_anzeige\" ";

			$cTmpSQL.=", EXTRACT(EPOCH FROM tbl_reservierung.datum) as \"datum_timestamp\" ";

			
     		$cTmpSQL.=" FROM ".$cSchemaSQL."tbl_reservierung  ";
   			$cTmpSQL.=" RIGHT JOIN lehre.tbl_stunde ON lehre.tbl_stunde.stunde=".$cSchemaSQL."tbl_reservierung.stunde ";

		if (!empty($cTmpStartZeit) && empty($cTmpEndeZeit) )
	   		$cTmpSQL.=" AND to_char(lehre.tbl_stunde.beginn, 'HH24MI')='".$cTmpStartZeit."' ";	
		else if (empty($cTmpStartZeit) && !empty($cTmpEndeZeit) )
	   		$cTmpSQL.=" to_char(lehre.tbl_stunde.ende, 'HH24MI')='".$cTmpEndeZeit."' ";	
		else if (!empty($cTmpStartZeit) && !empty($cTmpEndeZeit) )
		{
	   		$cTmpSQL.=" AND to_char(lehre.tbl_stunde.beginn, 'HH24MI') >='".$cTmpStartZeit."' ";	
	   		$cTmpSQL.=" AND to_char(lehre.tbl_stunde.ende, 'HH24MI') <= '".$cTmpEndeZeit.",' ";	
		}	
   	    	$cTmpSQL.=" WHERE ".$cSchemaSQL."tbl_reservierung.titel>'' ";

       	// Suche nach einer einzigen reservierung_id
	    if (!is_array($cTmpReservierung_id) && !empty($cTmpReservierung_id) )
    		{
       		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_reservierung.reservierung_id=".$cTmpReservierung_id." ";	
       	}
   	   	elseif (is_array($cTmpReservierung_id) && count($cTmpReservierung_id)>0 )
       	{
      			if (isset($cTmpReservierung_id[0]['reservierung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$cTmpReservierung_idE=array();
      				for ($indZEILE=0;$indZEILE<count($cTmpReservierung_id);$indZEILE++)
      					$cTmpReservierung_idE[]=trim($cTmpReservierung_id[$indZEILE]['reservierung_id']);
				$cTmpReservierung_id=$cTmpReservierung_idE;	
       		}
      			elseif (isset($cTmpReservierung_id['reservierung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   		$cTmpReservierung_idE=array();
				$cTmpReservierung_idE[]=trim($cTmpReservierung_id['reservierung_id']);
				$cTmpReservierung_id=$cTmpReservierung_idE;	
       		}
    	   		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_reservierung.reservierung_id in (".strtoupper(implode(",",$cTmpReservierung_id)).") ";	
		}

		
       	// Suche nach einer einzigen Veranstaltung_id
	    if (!is_array($cTmpVeranstaltung_id) && !empty($cTmpVeranstaltung_id) )
    	{
       		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_reservierung.veranstaltung_id=".$cTmpVeranstaltung_id." ";	
       	}
   	   	elseif (is_array($cTmpVeranstaltung_id) && count($cTmpVeranstaltung_id)>0 )
       	{
      			if (isset($cTmpVeranstaltung_id[0]['veranstaltung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$cTmpVeranstaltung_idE=array();
      				for ($indZEILE=0;$indZEILE<count($cTmpVeranstaltung_id);$indZEILE++)
      					$cTmpVeranstaltung_idE[]=trim($cTmpVeranstaltung_id[$indZEILE]['veranstaltung_id']);
				$cTmpVeranstaltung_id=$cTmpVeranstaltung_idE;	
       		}
      			elseif (isset($cTmpVeranstaltung_id['veranstaltung_id'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   		$cTmpVeranstaltung_idE=array();
				$cTmpVeranstaltung_idE[]=trim($cTmpVeranstaltung_id['veranstaltung_id']);
				$cTmpVeranstaltung_id=$cTmpVeranstaltung_idE;	
       		}
    	   		$cTmpSQL.=" AND ".$cSchemaSQL."tbl_reservierung.veranstaltung_id in (".strtoupper(implode(",",$cTmpVeranstaltung_id)).") ";	
		}		
		
		
		if (!empty($cTmpStart) && empty($cTmpEnde) )
	   		$cTmpSQL.=" AND to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD')='".$cTmpStart."' ";	
		else if (empty($cTmpStart) && !empty($cTmpEnde) )
	   		$cTmpSQL.=" to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD')='".$cTmpEnde."' ";	
		else if (!empty($cTmpStart) && !empty($cTmpEnde) )
		{
	   		$cTmpSQL.=" AND '".$cTmpStart."' between to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD') AND to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD') ";	
	   		$cTmpSQL.=" AND '".$cTmpEnde."' between to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD') AND to_char(".$cSchemaSQL."tbl_reservierung.datum, 'YYYYMMDD') ";	
		}	

   		$cTmpSQL.=" ORDER BY ".$cSchemaSQL."tbl_reservierung.datum,tbl_reservierung.stunde  ";	
   	  	$cTmpSQL.=" OFFSET 0 LIMIT ALL ;";	

       // Entfernen der Temporaeren Variablen aus dem Speicher
	 if (isset($cSchemaSQL)) unset($cSchemaSQL);
	   
       // Datenbankabfrage
		$this->setStringSQL($cTmpSQL);
		unset($cTmpSQL);

		$this->setResultSQL(null);
#		if (!$this->fetch_object()) 
		if (!$this->fetch_all()) 
			return false;   
			 
		$this->setReservierung($this->getResultSQL());
		$this->setResultSQL(null);
	   
		return $this->getReservierung();
       }
	
	
} // Class jahresplan Ende 

?>