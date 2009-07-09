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
	public $result;
	public $new=false;      					// boolean
	   	
       public $wbtyp_kurzbz;
       public $wettbewerb_kurzbz;
	   
	public $schemaSQL="kommune"; // string Datenbankschema
	   
//-----Konstruktor    
       function __construct($wbtyp_kurzbz="",$wettbewerb_kurzbz="",$uid="",$team_kurzbz="") 
       {
	   		parent::__construct();
			
			$this->InitWettbewerb();
			$this->wbtyp_kurzbz=$wbtyp_kurzbz;
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerb() 
       {
			$this->new=false;
			$this->errormsg='';
	       	$this->result=array();

			$this->wbtyp_kurzbz='';
	       	$this->wettbewerb_kurzbz='';
			
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
	
	   		$qry=" insert into ".$this->schemaSQL.".tbl_wettbewerbtyp (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			$fildsValue.=(!empty($fildsValue)?',':'')."bezeichnung='".addslashes($this->bezeichnung)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."farbe='".addslashes($this->farbe)."'";

			$qry.=" update ".$this->schemaSQL.".tbl_wettbewerbtyp set ";
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
			
			$qry.=" delete from ".$this->schemaSQL.".tbl_wettbewerbtyp ";
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
		
       function loadWettbewerbTyp($wbtyp_kurzbz=null)
       {
		// Init
		$this->result=array();
		$this->errormsg='';
		if (!is_null($wbtyp_kurzbz))
			$this->wbtyp_kurzbz=$wbtyp_kurzbz;
	          
		$qry="";
   		$qry.="SELECT * FROM ".$this->schemaSQL.".tbl_wettbewerbtyp  ";
       	$qry.=" WHERE ".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz>'' ";

       	// Suche nach einem einzigen Wetttbewerbstypen wbtyp_kurzbz
	       if ( !empty($this->wettbewerb_kurzbz) && !is_array($this->wettbewerb_kurzbz) )
    	   	{
       			$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz)=UPPER('".addslashes($this->wettbewerb_kurzbz)."') ";	
	       }
    	  	 elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
	       {
    		   	$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz) in ('".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
			}
		$qry.=" order by wbtyp_kurzbz ";
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
			
	   		$qry=" insert into ".$this->schemaSQL.".tbl_wettbewerb (".$fildsList.") values (".$fildsValue."); ";
		}
		else
		{
			$fildsValue.=(!empty($fildsValue)?',':'')."regeln='".addslashes($this->regeln)."'";
			$fildsValue.=(!empty($fildsValue)?',':'')."forderungstage=".addslashes($this->forderungstage)."";


			$fildsValue.=(!empty($fildsValue)?',':'')."teamgroesse=".addslashes($this->teamgroesse)."";

			$fildsValue.=(!empty($fildsValue)?',':'')."uid='".addslashes($this->uid)."'";

			$fildsValue.=(!empty($fildsValue)?',':'')."icon='".addslashes($this->icon)."'";
						
			$qry.=" update ".$this->schemaSQL.".tbl_wettbewerb set ";
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
			$qry.=" delete from ".$this->schemaSQL.".tbl_wettbewerb ";
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

       function loadWettbewerb($wbtyp_kurzbz=null,$wettbewerb_kurzbz=null)
       {
		// Init
		$this->result=array();
		$this->errormsg='';

		if (!is_null($wbtyp_kurzbz))
			$this->wbtyp_kurzbz=$wbtyp_kurzbz;
		if (!is_null($wettbewerb_kurzbz))
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;

              
		$qry="";
      		$qry.="SELECT *,tbl_wettbewerbtyp.wbtyp_kurzbz,case WHEN tbl_wettbewerb.teamgroesse >1 then 'Teambewerb' else 'Einzelbewerb'  end as wettbewerbart FROM ".$this->schemaSQL.".tbl_wettbewerbtyp  ";
      		$qry.=" LEFT JOIN ".$this->schemaSQL.".tbl_wettbewerb ON UPPER(".$this->schemaSQL.".tbl_wettbewerb.wbtyp_kurzbz)=UPPER(".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz) ";
       	$qry.=" WHERE ".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz>'' ";

       	// Suche nach einem einzigen Wetttbewerbstypen wbtyp_kurzbz
       if (!is_array($this->wbtyp_kurzbz) && !empty($this->wbtyp_kurzbz) )
   	   {
      			$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz)=UPPER('".$this->wbtyp_kurzbz."') ";	
       }
     	elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
       	{
    	   		$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerbtyp.wbtyp_kurzbz) in ('".strtoupper(implode("','",$this->wbtyp_kurzbz))."') ";	
		}

    	// Suche nach Wettbewerben wettbewerb_kurzbz
       	if (!is_array($this->wettbewerb_kurzbz) && !empty($this->wettbewerb_kurzbz) )
      	{
    	   		$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerb.wettbewerb_kurzbz)=UPPER('".$this->wettbewerb_kurzbz."') ";	
       	}
   	   	elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
       	{
	    	   	$qry.=" AND UPPER(".$this->schemaSQL.".tbl_wettbewerb.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
       	}	
		$qry.=" order by tbl_wettbewerbtyp.wbtyp_kurzbz,wettbewerb_kurzbz ";
		
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
} // Class komune_wettbewerb Ende 

?>