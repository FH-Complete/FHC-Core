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
* @classe kommune_wettbewerb 
*
* @param connectSQL Datenbankverbindung
* @param wbtyp_kurzbz Wettbewerbsgruppen Key	
* @param wettbewerb_kurzbz Wettbewerbssubgruppen Key 
*
* @return - kein Retourn des Konstruktors
*
*/
require_once(dirname(__FILE__).'/basis_db.class.php');
class komune_wettbewerb extends basis_db
{
		
	
      	public $wettbewerb;
      	public $wbtyp_kurzbz;
      	public $wettbewerb_kurzb;
		   
		public $result;
		public $new=false;      					// boolean
	   	
		public $regeln;
		public $forderungstage;

		public $teamgroesse;
		public $uid;
		public $icon;	
		   
		   
		public $schemaSQL="kommune."; // string Datenbankschema
	
//-----Konstruktor    
       function __construct($wbtyp_kurzbz="",$wettbewerb_kurzbz="") 
       {
			parent::__construct();
			
			$this->InitWettbewerb();
  
			$this->setWbtyp_kurzbz($wbtyp_kurzbz);
			$this->setWettbewerb_kurzbz($wettbewerb_kurzbz);
       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerb() 
       {
			$this->setError('');

	       	$this->setWettbewerb('');
	       	$this->setWbtyp_kurzbz(''); 
	       	$this->setWettbewerb_kurzbz('');
			
			
			
			$this->new=false;

	       	$this->result=array();

			$this->wbtyp_kurzbz='';
	       	$this->wettbewerb_kurzbz='';
			
	       	$this->regeln='';
	       	$this->forderungstage=1;

	       	$this->teamgroesse=1;
	       	$this->uid='';
	       	$this->icon='';		
						
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
//-----wbtyp_kurzbz--------------------------------------------------------------------------------------------
       function getWettbewerb() 
       {
           return $this->wettbewerb;
       }
       function setWettbewerb($wettbewerb) 
       {
           $this->wettbewerb=$wettbewerb;
       }
//-----wbtyp_kurzbz--------------------------------------------------------------------------------------------
       function getWbtyp_kurzbz() 
       {
           return $this->wbtyp_kurzbz;
       }
       function setWbtyp_kurzbz($wbtyp_kurzbz) 
       {
           $this->wbtyp_kurzbz=$wbtyp_kurzbz;
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
	   
	   

//-------------------------------------------------------------------------------------------------
//	------------------------ Wettbewerbstypen
//-------------------------------------------------------------------------------------------------
      
//-------------------------------------------------------------------------------------------------
	/**
	 * Speichert bzw. Aendert eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	
	public function saveWettbewerbTyp()
    	{
		// Initialisieren
		$this->errormsg='';
		$qry="";
			
		$fildsList='';
		$fildsValue='';
		
		
		if (empty($this->wbtyp_kurzbz) || $this->wbtyp_kurzbz==null )
		{
			$this->errormsg='Wettbewerb - Typ fehlt!';
			return false;
		}

		if (empty($this->bezeichnung))
		{
			$this->errormsg='Wettbewerbstyp - Bezeichnung fehlt!';
			return false;
		}
		
		if($this->new)
		{

			$fildsList.='wbtyp_kurzbz,';
			$fildsList.='bezeichnung,';
			$fildsList.='farbe';

			$fildsValue.="'".addslashes($this->wbtyp_kurzbz)."',"; 
			$fildsValue.="'".addslashes($this->bezeichnung)."',";
			$fildsValue.="'".addslashes($this->farbe)."'";
	
	   		$qry=" insert into ".$this->schemaSQL."tbl_wettbewerbtyp (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			if ($this->bezeichnung)
				$fildsValue.=(!empty($fildsValue)?',':'')."bezeichnung='".addslashes($this->bezeichnung)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."farbe='".addslashes($this->farbe)."'";

			$qry.=" update ".$this->schemaSQL."tbl_wettbewerbtyp set ";
			$qry.=$fildsValue;
			$qry.=" where wbtyp_kurzbz='".addslashes($this->wbtyp_kurzbz)."' ";
		}	
		if($resurce=$this->db_query($qry))
			return $resurce;
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim speichern des Datensatzes ';
			return false;
		}	
	}

//-------------------------------------------------------------------------------------------------
	/**
	 * Loescht eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	      
	   public function deleteWettbewerbTyp($wbtyp_kurzbz="")
       {

			// Initialisieren
			$qry="";
			$this->errormsg='';
	
			// Parameter
			if (!empty($wbtyp_kurzbz))
				$this->wbtyp_kurzbz=$wbtyp_kurzbz;
	
			// Plausib
			if (empty($this->wbtyp_kurzbz) || $this->wbtyp_kurzbz==null )
			{
				$this->errormsg='Wettbewerb - Typ fehlt!';
				return false;
			}
			
			// Abfrage
			$qry.=" BEGIN; ";
			$qry.=" delete from ".$this->schemaSQL.".tbl_wettbewerb ";
			if (is_array($this->wbtyp_kurzbz))
				$qry.=" where wbtyp_kurzbz in ('".implode("','",$this->wbtyp_kurzbz)."') ";
			else	
				$qry.=" where wbtyp_kurzbz='".addslashes($this->wbtyp_kurzbz)."' ";
			$qry.="; ";
			
			$qry.=" delete from ".$this->schemaSQL."tbl_wettbewerbtyp ";
			if (is_array($this->wbtyp_kurzbz))
				$qry.=" where wbtyp_kurzbz in ('".implode("','",$this->wbtyp_kurzbz)."') ";
			else	
				$qry.=" where wbtyp_kurzbz='".addslashes($this->wbtyp_kurzbz)."' ";
				
			if($this->db_query($qry))
			{
				if($this->db_query('COMMIT;'))
					return true;
				else
					return false;	
			}	
			else
			{
				$this->db_query('ROLLBACK;');
				if (empty($this->errormsg))
					$this->errormsg = 'Fehler beim Veranstaltungskategorie löschen';
				return false;
			}	
		}	   
	   
//-------------------------------------------------------------------------------------------------
       function loadWettbewerbTyp()
       {
	
		$cSchemaSQL=$this->getSchemaSQL();
		$tmpwbtyp_kurzbz=$this->getWbtyp_kurzbz();
	          
		$qry="";
   		$qry.="SELECT * FROM ".$cSchemaSQL."tbl_wettbewerbtyp  ";
       	$qry.=" WHERE ".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz>'' ";

       	// Suche nach einem einzigen Wetttbewerbstypen wbtyp_kurzbz
	       if (!is_array($tmpwbtyp_kurzbz) && !empty($tmpwbtyp_kurzbz) )
    	   {
       		$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz)=UPPER('".$tmpwbtyp_kurzbz."') ";	
	       }
    	   elseif (is_array($tmpwbtyp_kurzbz) && count($tmpwbtyp_kurzbz)>0 )
	       {
      			if (isset($tmpwbtyp_kurzbz[0]['wbtyp_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       			{
    	   			$tmpwbtyp_kurzbzE=array();
      				for ($indZEILE=0;$indZEILE<count($tmpwbtyp_kurzbz);$indZEILE++)
      					$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz[$indZEILE]['wbtyp_kurzbz']);
					$tmpwbtyp_kurzbz=$tmpwbtyp_kurzbzE;	
       			}
      		elseif (isset($tmpwbtyp_kurzbz['wbtyp_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   		$tmpwbtyp_kurzbzE=array();
				$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz['wbtyp_kurzbz']);
				$tmpwbtyp_kurzbz=$tmpwbtyp_kurzbzE;	
       		}
    	   	$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz) in ('".strtoupper(implode("','",$tmpwbtyp_kurzbz))."') ";	
		}
		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$rows[]=$row;
			}
			$this->setWettbewerb($rows);
		}	
		else
		{
			$this->setError($this->db_last_error());

			return false;
		}
		return $this->getWettbewerb();
       }

	   
	   
	   
//-------------------------------------------------------------------------------------------------
//	------------------------ Wettbewerbe
//-------------------------------------------------------------------------------------------------
//-------------------------------------------------------------------------------------------------
	/**
	 * Speichert bzw. Aendert eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	
	public function saveWettbewerb()
    	{
		// Initialisieren
		$this->errormsg='';
		$qry="";
			
		$fildsList='';
		$fildsValue='';
		
		
		// Plausib
		if (empty($this->wbtyp_kurzbz) || $this->wbtyp_kurzbz==null )
		{
			$this->errormsg='Wettbewerb - Type fehlt!';
			return false;
		}
		if (empty($this->wettbewerb_kurzbz) || $this->wettbewerb_kurzbz==null )
		{
			$this->errormsg='Wettbewerb - Kurzbz. fehlt!';
			return false;
		}

		if (empty($this->regeln))
		{
			$this->errormsg='Wettbewerb - Regeln fehlen!';
			return false;
		}

		if (empty($this->forderungstage) || is_null($this->forderungstage) )
			$this->forderungstage=7;

		if (!is_numeric($this->forderungstage) )
		{
			$this->errormsg='Forderungstage nur Nummerisch';
			return false;
		}


		if (empty($this->teamgroesse) || is_null($this->teamgroesse))
			$this->teamgroesse=1;

		if (!is_numeric($this->teamgroesse) )
		{
			$this->errormsg='Forderungstage nur Nummerisch';
			return false;
		}

		if($this->new)
		{

			$fildsList.='wbtyp_kurzbz,';
			$fildsList.='wettbewerb_kurzbz,';
			$fildsList.='regeln,';
			$fildsList.='forderungstage,';

			$fildsList.='teamgroesse,';
			$fildsList.='uid,';
			$fildsList.='icon';
			
			$fildsValue.="'".addslashes($this->wbtyp_kurzbz)."',"; 
			$fildsValue.="'".addslashes($this->wettbewerb_kurzbz)."',"; 
			
			$fildsValue.="'".addslashes($this->regeln)."',";
			$fildsValue.="".addslashes($this->forderungstage).",";
	
			$fildsValue.="".addslashes($this->teamgroesse).",";
			$fildsValue.="'".addslashes($this->uid)."',";
			
			$fildsValue.="'".addslashes($this->icon)."'";
			
	   		$qry=" insert into ".$this->schemaSQL."tbl_wettbewerb (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			if (!is_null($this->regeln) && $this->regeln)
				$fildsValue.=(!empty($fildsValue)?',':'')."regeln='".addslashes($this->regeln)."'";
				
			if (!is_null($this->forderungstage) && $this->forderungstage)
				$fildsValue.=(!empty($fildsValue)?',':'')."forderungstage=".addslashes($this->forderungstage)."";
			if (!is_null($this->teamgroesse) && $this->teamgroesse)
				$fildsValue.=(!empty($fildsValue)?',':'')."teamgroesse=".addslashes($this->teamgroesse)."";
			if (!is_null($this->icon) && $this->icon)
				$fildsValue.=(!empty($fildsValue)?',':'')."icon='".addslashes($this->icon)."'";
						
			$fildsValue.=(!empty($fildsValue)?',':'')."uid='".addslashes($this->uid)."'";

			$qry.=" update ".$this->schemaSQL."tbl_wettbewerb set ";
			$qry.=$fildsValue;
			$qry.=" where wbtyp_kurzbz='".addslashes($this->wbtyp_kurzbz)."' and wettbewerb_kurzbz='".addslashes($this->wettbewerb_kurzbz)."' ";
		}	


		if($this->db_query($qry))
			return true;
		else
		{
			if (empty($qry))
				$this->errormsg = 'Fehler beim speichern des Datensatzes ';
			$this->errormsg .=' '.$qry;
			return false;
		}	
	}

//-------------------------------------------------------------------------------------------------
	/**
	 * Loescht eine Veranstaltungskategorie
	 * @return true wenn ok, false im Fehlerfall
	 */	      
	   public function deleteWettbewerb($wbtyp_kurzbz="",$wettbewerb_kurzbz=null)
       {

			// Initialisieren
			$qry="";

			$this->result=array();
			$this->errormsg='';

			// Parameter
			if (!is_null($wbtyp_kurzbz))
				$this->wbtyp_kurzbz=$wbtyp_kurzbz;
			if (!is_null($wettbewerb_kurzbz))
				$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
	
			// Plausib
			if (empty($this->wbtyp_kurzbz) || $this->wbtyp_kurzbz==null )
			{
				$this->errormsg='Wettbewerb - Typ fehlt!';
				return false;
			}
			if (empty($this->wettbewerb_kurzbz) || $this->wettbewerb_kurzbz==null )
			{
				$this->errormsg='Wettbewerb - Kurzbz. fehlt!';
				return false;
			}
			
			// Abfrage
			$qry.=" delete from ".$this->schemaSQL."tbl_wettbewerb ";
			if (is_array($this->wbtyp_kurzbz))
				$qry.=" where wbtyp_kurzbz in ('".implode("','",$this->wbtyp_kurzbz)."') ";
			else	
				$qry.=" where wbtyp_kurzbz='".addslashes($this->wbtyp_kurzbz)."' ";
			
			if (is_array($this->wettbewerb_kurzbz))
				$qry.=" and wettbewerb_kurzbz in ('".implode("','",$this->wettbewerb_kurzbz)."') ";
			else	
				$qry.=" and wettbewerb_kurzbz='".addslashes($this->wettbewerb_kurzbz)."' ";

			if($this->db_query($qry))
			{
				return true;	
			}	
			else
			{
				if (empty($this->errormsg))
					$this->errormsg = 'Fehler beim Veranstaltungskategorie löschen';
				return false;
			}	
		}	   
	   
	   
       function loadWettbewerb()
       {
		$cSchemaSQL=$this->getSchemaSQL();
		$tmpwbtyp_kurzbz=$this->getWbtyp_kurzbz();
		$cWettbewerb_kurzbz=$this->getWettbewerb_kurzbz();
              
		$qry="";
      		$qry.="SELECT *,tbl_wettbewerbtyp.wbtyp_kurzbz,case WHEN tbl_wettbewerb.teamgroesse >1 then 'Teambewerb' else 'Einzelbewerb'  end as wettbewerbart FROM ".$cSchemaSQL."tbl_wettbewerbtyp  ";
      		$qry.=" LEFT JOIN ".$cSchemaSQL."tbl_wettbewerb ON UPPER(".$cSchemaSQL."tbl_wettbewerb.wbtyp_kurzbz)=UPPER(".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz) ";
       	$qry.=" WHERE ".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz>'' ";

       	// Suche nach einem einzigen Wetttbewerbstypen wbtyp_kurzbz
       if (!is_array($tmpwbtyp_kurzbz) && !empty($tmpwbtyp_kurzbz) )
   	   {
      		$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz)=UPPER('".$tmpwbtyp_kurzbz."') ";	
       }
     	elseif (is_array($tmpwbtyp_kurzbz) && count($tmpwbtyp_kurzbz)>0 )
       	{
      		if (isset($tmpwbtyp_kurzbz[0]['wbtyp_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$tmpwbtyp_kurzbzE=array();
      				for ($indZEILE=0;$indZEILE<count($tmpwbtyp_kurzbz);$indZEILE++)
      					$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz[$indZEILE]['wbtyp_kurzbz']);
					$tmpwbtyp_kurzbz=$tmpwbtyp_kurzbzE;	
       		}
      			elseif (isset($tmpwbtyp_kurzbz['wbtyp_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
       		{
    	   			$tmpwbtyp_kurzbzE=array();
					$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz['wbtyp_kurzbz']);
					$tmpwbtyp_kurzbz=$tmpwbtyp_kurzbzE;	
       		}
    	   		$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerbtyp.wbtyp_kurzbz) in ('".strtoupper(implode("','",$tmpwbtyp_kurzbz))."') ";	
		}

    	// Suche nach Wettbewerben wettbewerb_kurzbz
       	if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
	      	{
    	   		$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerb.wettbewerb_kurzbz)=UPPER('".$cWettbewerb_kurzbz."') ";	
	       }
    	   	elseif (is_array($cWettbewerb_kurzbz) && count($cWettbewerb_kurzbz)>0 )
       	{
       		if (isset($cWettbewerb_kurzbz[0]['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
	       	{
	   	  		$tmpwbtyp_kurzbzE=array();
       			for ($indZEILE=0;$indZEILE<count($tmpwbtyp_kurzbz);$indZEILE++)
       				$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz[$indZEILE]['wettbewerb_kurzbz']);
				$cWettbewerb_kurzbz=$tmpwbtyp_kurzbzE;
				unset($tmpwbtyp_kurzbzE);
	       	}
       		elseif (isset($cWettbewerb_kurzbz['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
	       	{
	   	   		$tmpwbtyp_kurzbzE=array();
      				$tmpwbtyp_kurzbzE[]=trim($tmpwbtyp_kurzbz['wettbewerb_kurzbz']);
				$cWettbewerb_kurzbz=$tmpwbtyp_kurzbzE;
				unset($tmpwbtyp_kurzbzE);
	       	}				
	    	   	$qry.=" AND UPPER(".$cSchemaSQL."tbl_wettbewerb.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$tmpwbtyp_kurzbzE))."') ";	
       	}	
       // Datenbankabfrage
		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$rows[]=$row;
			}
			$this->setWettbewerb($rows);
		}	
		else
		{
			$this->setError($this->db_last_error());

			return false;
		}
		return $this->getWettbewerb();
       }
} // Class komune_wettbewerb Ende 

?>