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
class komune_wettbewerbteam  extends basis_db
{
	public $new;
       public $newWettbewerbteam;
       public $wettbewerbteam;

       public $uid;
       public $team_kurzbz;
       public $team_kurzbz_old;
       public $wettbewerb_kurzbz;

	public $schemaSQL="kommune."; // string Datenbankschema	
//-----Konstruktor       
       function __construct($uid="",$team_kurzbz="",$wettbewerb_kurzbz="") 
       {
		parent::__construct();
		
		$this->InitWettbewerbteam();
	
		$this->setuid($uid);
		$this->setTeam_kurzbz($team_kurzbz);
		$this->setWettbewerb_kurzbz($wettbewerb_kurzbz);
       }

//-----Initialisierung--------------------------------------------------------------------------------------------
       function InitWettbewerbteam() 
       {
		$this->setError('');

	    	$this->setNewWettbewerbteam('');
       	$this->setWettbewerbteam('');
		
		$this->setuid('');
		$this->setTeam_kurzbz('');
		$this->setTeam_kurzbz_old('');
		$this->setWettbewerb_kurzbz('');
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
//-----Neuer Datensatz--------------------------------------------------------------------------------------------
       function getNewWettbewerbteam()
       {
	       return $this->newWettbewerbteam;
	}	   
       function setNewWettbewerbteam($newWettbewerbteam)
       {
		$this->newWettbewerbteam=$newWettbewerbteam;
	}	
//-----Aenderung Datensatz wird wie Neuanlage gehandhabt -------------------------------------------------------------
       function getUpdWettbewerbteam()
       {
	       return $this->newWettbewerbteam;
	}	   
       function setUpdWettbewerbteam($newWettbewerbteam)
       {
		$this->newWettbewerbteam=trim($newWettbewerbteam);
	}		   		
//-----Aktueller Datensatz--------------------------------------------------------------------------------------------
       function getWettbewerbteam() 
       {
           return $this->wettbewerbteam;
       }
       function setWettbewerbteam($wettbewerbteam) 
       {
           $this->wettbewerbteam=$wettbewerbteam;
       }
//-----team_kurzbz--------------------------------------------------------------------------------------------
       function getTeam_kurzbz() 
       {
           return $this->team_kurzbz;
       }
       function setTeam_kurzbz($team_kurzbz) 
       {
           $this->team_kurzbz=trim($team_kurzbz);
       }
//-----team_kurzbz--------------------------------------------------------------------------------------------
       function getTeam_kurzbz_old() 
       {
		return $this->team_kurzbz_old;
       }
       function setTeam_kurzbz_old($team_kurzbz_old) 
       {
           $this->team_kurzbz_old=trim($team_kurzbz_old);
       }
//-----uid--------------------------------------------------------------------------------------------
       function getUid() 
       {
           return $this->uid;
       }
       function setUid($uid) 
       {
           $this->uid=trim($uid);
       }	   
//-----wettbewerb_kurzbz--------------------------------------------------------------------------------------------
       function getWettbewerb_kurzbz() 
       {
           return $this->wettbewerb_kurzbz;
       }
       function setWettbewerb_kurzbz($wettbewerb_kurzbz="") 
       {
           $this->wettbewerb_kurzbz=trim($wettbewerb_kurzbz);
       }
//-------------------------------------------------------------------------------------------------
       function saveWettbewerbteam($newWettbewerbteam="")
       {
		// Initialisierung	
		$this->setError('');

		// Plausib			
		if (!empty($newWettbewerbteam)) 
			$this->setNewWettbewerbteam($newWettbewerbteam);

		if (!is_array($this->getNewWettbewerbteam()))
		{
			$this->setError('Kein Wettbewerbsteam &uuml;bergeben');
			return false;
		}	
		$newWettbewerbteam=$this->getNewWettbewerbteam();
		
		// Daten uebernahme 
		$cSchemaSQL=$this->getSchemaSQL();
		$cTeam_kurzbz=$this->getTeam_kurzbz();		

		 // Aenderungen muessen mit dem Team_kurzbz_old gekennzeichnet werden. Ansonst koennten falsche Daten geaendert werden
		$cTeam_kurzbz_old=$this->getTeam_kurzbz_old();		
		
		$cUserUID=$this->getUid(); // Vor der Verarbeitung sicherstellen das Alle Anwender gelesen werden 
		$this->setUid('');
		
		$this->setNewRecord(false);
		if (!$origWettbewerbteam=$this->loadWettbewerbteam())
		{
			if ($this->getError()) // Beim Lesen ist ein Fehler aufgetreten
				return false;
			$this->setNewRecord(true);
		}	
		$this->setUid($cUserUID);
		unset($cUserUID);

		if ($origWettbewerbteam && empty($cTeam_kurzbz_old)) // Datenrec bereits vorhanden
		{
			$this->setError('Das Team '.$cTeam_kurzbz.' ist bereits angelegt!');
			return false;
		}			 
		$bTmpNewRecord=$this->getNewRecord(); // Neuanlage Switch sichern
		
		
		// Aus dem Array newWettbewerbteam die Teaminformationen heraus holen
		$cWettbewerb_kurzbz=(isset($newWettbewerbteam['wettbewerb_kurzbz']) ? $newWettbewerbteam['wettbewerb_kurzbz'] : '');
		if (empty($cWettbewerb_kurzbz)) 
			$cWettbewerb_kurzbz=(isset($newWettbewerbteam[0]['wettbewerb_kurzbz']) ? $newWettbewerbteam[0]['wettbewerb_kurzbz'] : '');
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
		
		
		$qry="select * from ".$cSchemaSQL."tbl_teambenutzer , ".$cSchemaSQL."tbl_wettbewerbteam "; 
		$qry.=" where tbl_wettbewerbteam.team_kurzbz =tbl_teambenutzer.team_kurzbz  ";       
		$qry.=" and not ( UPPER(tbl_teambenutzer.team_kurzbz)='".addslashes(trim(strtoupper($cTeam_kurzbz_old)))."' and UPPER(tbl_teambenutzer.team_kurzbz)='".addslashes(trim(strtoupper($cTeam_kurzbz)))."' )  ";       
		$qry.=" and UPPER(tbl_teambenutzer.uid) in ('".strtoupper(implode("','",$array_userUID))."') "; 
		$qry.=" and UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)='".addslashes(trim(strtoupper($cWettbewerb_kurzbz)))."'  ; ";
   // Datenbankabfrage
		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$this->setError(sprintf('Der Spieler %s wurde bereits im Team %s im Wettbewerb %s gefunden ! ',$row['uid'],$row['team_kurzbz'],$row['wettbewerb_kurzbz']));
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
		

		$this->db_query(" BEGIN; ");

       	$qry="";
		// Neuanlage - Insert 
		if ($this->getNewRecord())
		{
       		$qry.=" INSERT into ".$cSchemaSQL."tbl_team (team_kurzbz,bezeichnung,beschreibung,logo) VALUES ('".addslashes(trim($cTeam_kurzbz))."','".addslashes(trim($team_bezeichnung))."','".addslashes(trim($team_beschreibung))."','".addslashes(trim($team_logo))."'); ";
			for ($zeileIND=0;$zeileIND<count($array_userUID);$zeileIND++)
			{
				if (!empty($array_userUID[$zeileIND]))
					$qry.=" INSERT into ".$cSchemaSQL."tbl_teambenutzer (uid, team_kurzbz) VALUES ('".addslashes(trim($array_userUID[$zeileIND]))."','".addslashes(trim($cTeam_kurzbz))."'); ";
			}
			$qry.=" INSERT into ".$cSchemaSQL."tbl_wettbewerbteam (team_kurzbz, wettbewerb_kurzbz,rang,punkte) VALUES ('".$cTeam_kurzbz."','".addslashes(trim($cWettbewerb_kurzbz))."',(select 1+count(wettbewerb_kurzbz) from ".$cSchemaSQL."tbl_wettbewerbteam where rang<9999 and upper(wettbewerb_kurzbz)=upper('".addslashes(trim($cWettbewerb_kurzbz))."')),0); ";
			
		}
		else
		{
   		// Update Logo nur wenn Daten uebergeben wurden, sonst Logo auslassen
#	  	if ($team_logo==null) $team_logo=(isset($origWettbewerbteam[0]['logo']) ? $origWettbewerbteam[0]['logo'] : null);
#	  	if ($team_rang==null) $team_rang=(isset($origWettbewerbteam[0]['rang']) ? $origWettbewerbteam[0]['rang'] : null);

	       	$qry.=" UPDATE ".$cSchemaSQL."tbl_team ";
		    	$qry.=" set team_kurzbz='".addslashes(trim($cTeam_kurzbz))."',bezeichnung='".addslashes(trim($team_bezeichnung))."',beschreibung='".addslashes(trim($team_beschreibung))."'".($team_logo!=null?",logo='".addslashes($team_logo)."'":"");
	     	 	$qry.=" WHERE upper(team_kurzbz)=upper('".$cTeam_kurzbz_old."'); ";

			$qry.=" UPDATE ".$cSchemaSQL."tbl_wettbewerbteam set team_kurzbz='".addslashes(trim($cTeam_kurzbz))."'".($team_rang!=null?",rang=".$team_rang:"")." WHERE upper(team_kurzbz)=upper('".$cTeam_kurzbz_old."'); ";

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
		    			  $qry.=" UPDATE ".$cSchemaSQL."tbl_teambenutzer set team_kurzbz='".addslashes(trim($cTeam_kurzbz))."' WHERE UPPER(uid)=UPPER('".addslashes($cTmpUID)."') AND  upper(team_kurzbz)=upper('".$cTeam_kurzbz_old."'); ";
					  unset($arrTmpCheckUID->$cTmpUID);
				}	  
				elseif (!isset($arrTmpCheckUID->$cTmpUID))
				{
					$qry.=" INSERT into ".$cSchemaSQL."tbl_teambenutzer (uid, team_kurzbz) VALUES ('".addslashes(trim($cTmpUID))."','".addslashes(trim($cTeam_kurzbz))."'); ";
				}					
			}	

			if (isset($zeileIND)) unset($zeileIND);
			if (isset($array_userUID)) unset($array_userUID);				
			// Alle die noch in der DB-Alt Array sind muessen geloeschte sein
			while (list( $key, $value ) = each($arrTmpCheckUID) )
				$qry.=" DELETE from ".$cSchemaSQL."tbl_teambenutzer WHERE UPPER(uid)=UPPER('".addslashes($value)."') AND upper(team_kurzbz)=upper('".$cTeam_kurzbz_old."'); ";

			if (isset($key)) unset($key);			
			if (isset($value)) unset($value);			
			if (isset($arrTmpCheckUID)) unset($arrTmpCheckUID);
		}	
		if($this->db_query($qry))
		{
			$this->db_query(" COMMIT; ");
			if (!$this->loadWettbewerbteam())
			{
				return false;
			}	
		}	
		else
		{
			$this->db_query(" ROLLBACK; ");
			$this->setError($this->db_last_error());
			return false;
		}
		return $this->getWettbewerbteam();
	}
//-------------------------------------------------------------------------------------------------
       function loadWettbewerbteam()
       {
	    $this->setError('');
					  
           	$cSchemaSQL=$this->getSchemaSQL();
           	$tmpUid=$this->getUid();

		$cTeam_kurzbz=$this->getTeam_kurzbz_old();			
		if (empty($cTeam_kurzbz))
	            $cTeam_kurzbz=$this->getTeam_kurzbz();			
			
           	$cWettbewerb_kurzbz=$this->getWettbewerb_kurzbz();
              
	    	$qry="";
			$qry.="SELECT * FROM ".$cSchemaSQL."tbl_teambenutzer,".$cSchemaSQL."tbl_team,".$cSchemaSQL."tbl_wettbewerbteam ";
	     
		   	$qry.=" WHERE UPPER(tbl_team.team_kurzbz)=UPPER(tbl_teambenutzer.team_kurzbz) ";
			$qry.=" AND UPPER(tbl_wettbewerbteam.team_kurzbz)=UPPER(tbl_team.team_kurzbz) ";

			// Check wie Postgre darauf reagiert Performenc
			$qry.=" AND UPPER(tbl_wettbewerbteam.team_kurzbz)=UPPER(tbl_teambenutzer.team_kurzbz) ";
			
	       	if (!empty($cTeam_kurzbz))
	           		$qry.=" AND UPPER(tbl_teambenutzer.team_kurzbz)=UPPER('".$cTeam_kurzbz."') ";
	     
		   	if (!empty($tmpUid)) 
				$qry.=" AND UPPER(tbl_teambenutzer.uid)=UPPER('".addslashes($tmpUid)."') ";

			// Suche nach einem einzigen Wettbewerb
			if (!is_array($cWettbewerb_kurzbz) && !empty($cWettbewerb_kurzbz) )
			{
				$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz)=UPPER('".$cWettbewerb_kurzbz."') ";	
			}
			elseif (is_array($cWettbewerb_kurzbz) && count($cWettbewerb_kurzbz)>0 )
			{
				if (isset($cWettbewerb_kurzbz[0]['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$tmpWETTBEWERB=array();
					for ($indZEILE=0;$indZEILE<count($selectWETTBEWERB);$indZEILE++)
						$tmpWETTBEWERB[]=trim($selectWETTBEWERB[$indZEILE]['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$tmpWETTBEWERB;
					unset($tmpWETTBEWERB);	
				}
				elseif (isset($cWettbewerb_kurzbz['wettbewerb_kurzbz'])) // Check ob nicht kpl. Tablestruck in Array
				{
					$tmpWETTBEWERB=array();
					$tmpWETTBEWERB[]=trim($selectWETTBEWERB['wettbewerb_kurzbz']);
					$cWettbewerb_kurzbz=$tmpWETTBEWERB;
					unset($tmpWETTBEWERB);	
				}
				$qry.=" AND UPPER(tbl_wettbewerbteam.wettbewerb_kurzbz) in ('".strtoupper(implode("','",$cWettbewerb_kurzbz))."') ";	
			}
	     	$qry.="ORDER BY tbl_wettbewerbteam.rang ;";	
		if($this->db_query($qry))
		{
			$rows=array();
			while($row = $this->db_fetch_array())
			{
				$rows[]=$row;
			}
			$this->setWettbewerbteam($rows);
		}	
		else
		{
			$this->setError($this->db_last_error());

			return false;
		}
   		return $this->getWettbewerbteam();
       }
} // Class komune_wettbewerbteam Ende 

?>