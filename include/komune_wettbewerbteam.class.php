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
* @classe komune_wettbewerbteam
*
* @param connectSQL Datenbankverbindung
* @param uid Angemeleter Anwender
* @param team_kurzbz Team des Angemeleter Anwenders zum Wettbewerb
* @param wettbewerb_kurzbz Wettbewerbssubgruppen Key 
*
* @return - kein Retourn des Konstruktors
*
*/

require_once(dirname(__FILE__).'/basis_db.class.php'); 
class komune_wettbewerbteam extends basis_db 
{
		public $result;
		public $new=false;      					// boolean
	   	

       	public $wbtyp_kurzbz;
	   
// tbl_team			   
	 	public $team_kurzbz;	//character varying(16)
		public $bezeichnung;	//character varying(128)
		public $beschreibung;	//text
		public $logo;		//text	

//	tbl_teambenutzer 
		public $uid; // varying(16) 
		// im tbl_team		public $team_kurzbz //character 
	   
//tbl_wettbewerbteam
		// im tbl_team		public $team_kurzbz;		// character varying(16) 
		public $rang;				// smallint		  Alter Drop  
		public $punkte;				// numeric(8,2)   Alter Drop 
	    public $wettbewerb_kurzb; // character varying(16)
	   
	public $schemaSQL="kommune"; // string Datenbankschema
	   
//-----Konstruktor    
       function __construct($wbtyp_kurzbz="",$wettbewerb_kurzbz="",$uid="",$team_kurzbz="") 
       {
	   		parent::__construct();
			
			$this->InitWettbewerb();
			
			$this->wbtyp_kurzbz=$wbtyp_kurzbz;
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
			$this->uid=$uid;
			$this->team_kurzbz=$team_kurzbz;
       }
//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerb() 
       {
			$this->new=false;
			$this->errormsg='';
	       	$this->result=array();

			$this->wbtyp_kurzbz='';
	       	$this->wettbewerb_kurzbz='';
			$this->uid='';
			
	
			$this->team_kurzbz='';	
			$this->bezeichnung='';	
			$this->beschreibung='';	
			$this->logo='';								

			$this->rang='1';	
			$this->punkte='0';	
       }
	   
//-------------------------------------------------------------------------------------------------
//	------------------------ Wettbewerbteam
//-------------------------------------------------------------------------------------------------

//-------------------------------------------------------------------------------------------------
       function saveWettbewerbteam()
       {
		// Initialisieren
		$this->errormsg='';
		$qry="";
			
		$fildsList='';
		$fildsValue='';
		
/*		
	tbl_wettbewerbteam
		public $team_kurzbz;		// character varying(16) 
	    public $wettbewerb_kurzb; // character varying(16)
		public $rang;				// smallint		  Alter Drop  
		public $punkte;				// numeric(8,2)   Alter Drop 
*/	   		
		
		if (empty($this->wettbewerb_kurzb) || $this->wettbewerb_kurzb==null )
		{
			$this->errormsg='Wettbewerb fehlt!';
			return false;
		}
		if (empty($this->team_kurzbz) || $this->team_kurzbz==null )
		{
			$this->errormsg='Teambezeichnung fehlt!';
			return false;
		}
		if (!is_numeric($this->rang))
			$this->rang=
		if (!is_numeric($this->punkte))
			$this->punkte=0;
			
		if($this->new)
		{

			$fildsList.='team_kurzbz,';
			$fildsList.='wettbewerb_kurzb,';
			$fildsList.='rang,';
			$fildsList.='punkte';

			$fildsValue.="'".addslashes($this->team_kurzbz)."',"; 
			$fildsValue.="'".addslashes($this->wettbewerb_kurzb)."',";
			$fildsValue.="".addslashes($this->rang).",";
			$fildsValue.="".addslashes($this->punkte)."";
	
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
		
		
		
		return false;
		
		if (!is_array($this->getNewWettbewerbteam()))
		{
			$this->errormsg='Kein Wettbewerbsteam &uuml;bergeben';
			return false;
		}	
		$newWettbewerbteam=$this->getNewWettbewerbteam();
		
		// Daten uebernahme 
		$cSchemaSQL=$this->getSchemaSQL();
		$this->team_kurzbz=$this->getTeam_kurzbz();		

		 // Aenderungen muessen mit dem Team_kurzbz_old gekennzeichnet werden. Ansonst koennten falsche Daten geaendert werden
		$this->team_kurzbz_old=$this->getTeam_kurzbz_old();		
		
		$cUserUID=$this->uid; // Vor der Verarbeitung sicherstellen das Alle Anwender gelesen werden 
		$this->uid='';
		
		$this->setNewRecord(false);
		if (!$origWettbewerbteam=$this->loadWettbewerbteam())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	
		$this->setUid($cUserUID);
		unset($cUserUID);

		if ($origWettbewerbteam && empty($this->team_kurzbz_old)) // Datenrec bereits vorhanden
		{
			$this->setError('Das Team '.$this->team_kurzbz.' ist bereits angelegt!');
			return false;
		}			 
		$bTmpNewRecord=$this->getNewRecord(); // Neuanlage Switch sichern
		
		
		// Aus dem Array newWettbewerbteam die Teaminformationen heraus holen
		$this->wettbewerb_kurzbz=(isset($newWettbewerbteam['wettbewerb_kurzbz']) ? $newWettbewerbteam['wettbewerb_kurzbz'] : '');
		if (empty($this->wettbewerb_kurzbz)) 
			$this->wettbewerb_kurzbz=(isset($newWettbewerbteam[0]['wettbewerb_kurzbz']) ? $newWettbewerbteam[0]['wettbewerb_kurzbz'] : '');
  	    	$team_bezeichnung=(isset($newWettbewerbteam['bezeichnung']) ? $newWettbewerbteam['bezeichnung'] : '');
		$team_beschreibung=(isset($newWettbewerbteam['beschreibung']) ? $newWettbewerbteam['beschreibung'] : '');

		$team_logo=(isset($newWettbewerbteam['logo']) ? $newWettbewerbteam['logo'] : null);
		$team_rang=(isset($newWettbewerbteam['rang']) ? $newWettbewerbteam['rang'] : null);
		
		// Ermitteln der Tabelle der Teamspieler
		if (isset($newWettbewerbteam['array_userUID']))
			$array_userUID=$newWettbewerbteam['array_userUID'];
		else
		{
			$this->setError('Die Spieler / Team wurde nicht gefunden! ');
			return false;
		}	
		
		
		$qry="select * from ".$this->schemaSQL.".tbl_teambenutzer , ".$this->schemaSQL.".tbl_wettbewerbteam "; 
		$qry.=" where tbl_wettbewerbteam.team_kurzbz =tbl_teambenutzer.team_kurzbz  ";       
		$qry.=" and not ( UPPER(tbl_teambenutzer.team_kurzbz)=E'".addslashes(trim(strtoupper($this->team_kurzbz_old)))."' and UPPER(tbl_teambenutzer.team_kurzbz)=E'".addslashes(trim(strtoupper($this->team_kurzbz)))."' )  ";       
		$qry.=" and UPPER(tbl_teambenutzer.uid) in ('".strtoupper(implode("','",$array_userUID))."') "; 
		$qry.=" and UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=E'".addslashes(trim(strtoupper($this->wettbewerb_kurzbz)))."'  ; ";
   // Datenbankabfrage
            $this->setStringSQL($qry);
       	   	unset($qry);
           	$this->setResultSQL(null);
	   		if ($this->fetch_all()) 
			{
			   	$qry=$this->getResultSQL();
    		   	$this->setResultSQL(null);
#				exit(kommune_Test($qry));
				if (is_array($qry))
				{
					for ($zeileIND=0;$zeileIND<count($qry);$zeileIND++)
						$this->setError(sprintf('Der Spieler %s wurde bereits im Team %s im Wettbewerb %s gefunden ! ',$qry[$zeileIND]['uid'],$qry[$zeileIND]['team_kurzbz'],$qry[$zeileIND]['wettbewerb_kurzbz']));
					return false;
				}	
			}
		
		$arrTmpCheckUID=array(); // Aus dem Array nur die Belegten UIDs suchen
		for ($zeileIND=0;$zeileIND<count($array_userUID);$zeileIND++)
       {
			if (!empty($array_userUID[$zeileIND])) // Leere Daten entfernen (Inputmasken befuellung kann Wahllos erfolgen)
			{
				$array_userUID[$zeileIND]=trim($array_userUID[$zeileIND]);
				$arrTmpCheckUID[]=$array_userUID[$zeileIND];
			}
		}			
		$array_userUID=$arrTmpCheckUID;
		if (isset($arrTmpCheckUID)) unset($arrTmpCheckUID);

		$this->setNewRecord($bTmpNewRecord);
		
       	$qry="BEGIN;  ";
		// Neuanlage - Insert 
		if ($this->getNewRecord())
		{
       		$qry.=" INSERT into ".$this->schemaSQL.".tbl_team (team_kurzbz,bezeichnung,beschreibung,logo) VALUES (E'".addslashes(trim($this->team_kurzbz))."',E'".addslashes(trim($team_bezeichnung))."',E'".addslashes(trim($team_beschreibung))."',E'".addslashes(trim($team_logo))."'); ";
			for ($zeileIND=0;$zeileIND<count($array_userUID);$zeileIND++)
			{
				if (!empty($array_userUID[$zeileIND]))
					$qry.=" INSERT into ".$this->schemaSQL.".tbl_teambenutzer (uid, team_kurzbz) VALUES (E'".addslashes(trim($array_userUID[$zeileIND]))."',E'".addslashes(trim($this->team_kurzbz))."'); ";
			}
			$qry.=" INSERT into ".$this->schemaSQL.".tbl_wettbewerbteam (team_kurzbz, wettbewerb_kurzbz,rang,punkte) VALUES (E'".$this->team_kurzbz."',E'".addslashes(trim($this->wettbewerb_kurzbz))."',(select 1+count(wettbewerb_kurzbz) from ".$this->schemaSQL.".tbl_wettbewerbteam where rang<9999 and upper(wettbewerb_kurzbz)=upper(E'".addslashes(trim($this->wettbewerb_kurzbz))."')),0); ";
			
		}
		else
		{
   		// Update Logo nur wenn Daten uebergeben wurden, sonst Logo auslassen
#	  	if ($team_logo==null) $team_logo=(isset($origWettbewerbteam[0]['logo']) ? $origWettbewerbteam[0]['logo'] : null);
#	  	if ($team_rang==null) $team_rang=(isset($origWettbewerbteam[0]['rang']) ? $origWettbewerbteam[0]['rang'] : null);

	       	$qry.=" UPDATE ".$this->schemaSQL.".tbl_team ";
		    $qry.=" set team_kurzbz=E'".addslashes(trim($this->team_kurzbz))."',bezeichnung=E'".addslashes(trim($team_bezeichnung))."',beschreibung=E'".addslashes(trim($team_beschreibung))."'".($team_logo!=null?",logo=E'".addslashes(trim($team_logo))."'":"");
	       	$qry.=" WHERE upper(team_kurzbz)=upper(E'".$this->team_kurzbz_old."'); ";

			$qry.=" UPDATE ".$this->schemaSQL.".tbl_wettbewerbteam set team_kurzbz=E'".addslashes(trim($this->team_kurzbz))."'".($team_rang!=null?",rang=".$team_rang:"")." WHERE upper(team_kurzbz)=upper(E'".$this->team_kurzbz_old."'); ";

			// Alle bisher bestehenden DB-Eintraege in Array lesen fuer spaeteren vergleich ob Update/Delete
			reset($origWettbewerbteam);

			$arrTmpCheckUID=new stdClass;;	
			for ($zeileIND=0;$zeileIND<count($origWettbewerbteam);$zeileIND++)
			{
				$cTmpUID=trim($origWettbewerbteam[$zeileIND]['uid']);
				$arrTmpCheckUID->$cTmpUID=$cTmpUID;
			}	


			// Suchen Neuanlage - Update (nach Update den Array Eintrag entleeren sonst wird er nachher geloescht)	
			for ($zeileIND=0;$zeileIND<count($array_userUID);$zeileIND++)
       	       {
				$cTmpUID=trim($array_userUID[$zeileIND]);	
				if (isset($arrTmpCheckUID->$cTmpUID))
				{
		    			  $qry.=" UPDATE ".$this->schemaSQL.".tbl_teambenutzer set team_kurzbz=E'".addslashes(trim($this->team_kurzbz))."' WHERE UPPER(uid)=UPPER(E'".addslashes($cTmpUID)."') AND  upper(team_kurzbz)=upper(E'".$this->team_kurzbz_old."'); ";
					  unset($arrTmpCheckUID->$cTmpUID);
				}	  
				elseif (!isset($arrTmpCheckUID->$cTmpUID))
				{
					$qry.=" INSERT into ".$this->schemaSQL.".tbl_teambenutzer (uid, team_kurzbz) VALUES (E'".addslashes(trim($cTmpUID))."',E'".addslashes(trim($this->team_kurzbz))."'); ";
				}					
			}	

			if (isset($zeileIND)) unset($zeileIND);
			if (isset($array_userUID)) unset($array_userUID);				
			// Alle die noch in der DB-Alt Array sind muessen geloeschte sein
			while (list( $key, $value ) = each($arrTmpCheckUID) )
				$qry.=" DELETE from ".$this->schemaSQL.".tbl_teambenutzer WHERE UPPER(uid)=UPPER(E'".addslashes($value)."') AND upper(team_kurzbz)=upper(E'".$this->team_kurzbz_old."'); ";

			if (isset($key)) unset($key);			
			if (isset($value)) unset($value);			
			if (isset($arrTmpCheckUID)) unset($arrTmpCheckUID);
		}	
		$qry.=" COMMIT; ";       
# exit("<br />".$qry);

   // Datenbankabfrage
            $this->setStringSQL($qry);
       	   	unset($qry);

           	$this->setResultSQL(null);
             if (!$this->dbQuery())
	         	return false;
		if (!$this->loadWettbewerbteam())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	
    
       $this->setResultSQL(null);       
	   return $this->getWettbewerbteam();
	}
//-------------------------------------------------------------------------------------------------
       function loadWettbewerbteam($wbtyp_kurzbz=null,$wettbewerb_kurzbz=null,$uid=null,$team_kurzbz=null)
       {
		// Initialisierung	
		$this->result=array();
		$this->errormsg='';
					  
		if (!is_null($wbtyp_kurzbz))
			$this->wbtyp_kurzbz=$wbtyp_kurzbz;

		if (!is_null($wettbewerb_kurzbz))
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;

		if (!is_null($uid))
			$this->uid=$uid;
			
		if (!is_null($team_kurzbz))
			$this->team_kurzbz=$team_kurzbz;
              
	    	$qry="";
			$qry.="SELECT * FROM ".$this->schemaSQL.".tbl_teambenutzer,".$this->schemaSQL.".tbl_team,".$this->schemaSQL.".tbl_wettbewerbteam ";
	     
		   	$qry.=" WHERE UPPER(tbl_team.team_kurzbz)=UPPER(tbl_teambenutzer.team_kurzbz) ";
			$qry.=" AND UPPER(tbl_wettbewerbteam.team_kurzbz)=UPPER(tbl_team.team_kurzbz) ";
			// Check wie Postgre darauf reagiert Performenc
			$qry.=" AND UPPER(tbl_wettbewerbteam.team_kurzbz)=UPPER(tbl_teambenutzer.team_kurzbz) ";
			
	       	if (!empty($this->team_kurzbz))
	           		$qry.=" AND UPPER(tbl_teambenutzer.team_kurzbz)=UPPER(E'".$this->team_kurzbz."') ";

		   	if (!empty($this->uid)) 
				$qry.=" AND UPPER(tbl_teambenutzer.uid)=UPPER(E'".addslashes($this->uid)."') ";

			// Suche nach einem einzigen Wettbewerb
			if (!is_array($this->wettbewerb_kurzbz) && !empty($this->wettbewerb_kurzbz) )
			{
				$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".$this->wettbewerb_kurzbz."') ";	
			}
			elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
			{
				$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz) in (E'".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
			}
	    	$qry.="ORDER BY tbl_wettbewerbteam.rang;";	
	   
	   // Entfernen der Temporaeren Variablen aus dem Speicher
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
       function loadMaxRang($wettbewerb_kurzbz=null)
       {
		// Initialisierung	
		$this->result=array();
		$this->errormsg='';
					  

		if (!is_null($wettbewerb_kurzbz))
			$this->wettbewerb_kurzbz=$wettbewerb_kurzbz;
             
    	$qry="";
		$qry.="SELECT max(rang) as max FROM ".$this->schemaSQL.".tbl_teambenutzer  ";
	   	$qry.=" WHERE rang > 0 ";
		// Suche nach einem einzigen Wettbewerb
		if (!is_array($this->wettbewerb_kurzbz) && !empty($this->wettbewerb_kurzbz) )
		{
			$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER(E'".$this->wettbewerb_kurzbz."') ";	
		}
		elseif (is_array($this->wettbewerb_kurzbz) && count($this->wettbewerb_kurzbz)>0 )
		{
			$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz) in (E'".strtoupper(implode("','",$this->wettbewerb_kurzbz))."') ";	
		}

exit($qry);

	   // Entfernen der Temporaeren Variablen aus dem Speicher
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$this->result=$row->max;
			}
			return $this->result;
		}
		else
		{
			if (empty($this->errormsg))
				$this->errormsg = 'Fehler beim lesen des letzten Range im Wettbewerb ';
			return 0;
		}	
		return 0;
       }

	   
} 
?>