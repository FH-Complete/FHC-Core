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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Klasse Adresse (FAS-Online)
 * @create 13-03-2006
 */

class adresse
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	var $done=false; //@ boolean
	
	//Tabellenspalten
	var $adresse_id;	// @var integer
	var $person_id;	// @var integer
	var $name; 		// @var string
	var $strasse;		// @var string
	var $plz;		// @var string
	var $ort;            	// @var string
	var $gemeinde;	// @var string
	var $nation;          	// @var string
	var $typ;		// @var integer
	var $heimatadresse;	// @var boolean
	var $zustelladresse;	// @var boolean
	var $firma_id;		// @var integer
	var $updateamum;	// @var timestamp
	var $updatevon=0;	// @var string
	var $insertamum;      // @var timestamp
	var $insertvon=0;      // @var string
	var $ext_id;		// @var integer
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $adress_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function adresse($conn,$adress_id=null,$unicode=false)
	{
		$this->conn = $conn;
		if ($unicode)
		{
			$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
		}
		else 
		{
			$qry="SET CLIENT_ENCODING TO 'LATIN9';";
		}
		if(!pg_query($conn,$qry))
		{
			$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
			return false;
		}
		//if($adress_id != null) 	$this->load($adress_id);
	}
	
	/**
	 * Laedt die Funktion mit der ID $adress_id
	 * @param  $adress_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($adress_id)
	{
		
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($adress_id) || $adress_id == '')
		{
			$this->errormsg = 'Adress_id muss eine Zahl sein';
			return false;
		}
		
		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM tbl_adresse WHERE adresse_id=$adress_id";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		if($row = pg_fetch_object($res))
		{
			$this->adresse_id      = $row->adresse_pk;
			$this->heimatadresse = ($row->heimatadresse=='J'?true:false);
			$this->zustelladresse = ($row->zustelladresse=='J'?true:false);
			$this->gemeinde        = $row->gemeinde;
			$this->name            = $row->name;
			$this->nation          = $row->nation;
			$this->ort             = $row->ort;
			$this->person_id       = $row->person_id;
			$this->plz             = $row->plz;
			$this->strasse         = $row->strasse;
			$this->typ             = $row->typ;
			$this->updateamum      = $row->updateamum;
			$this->updatevon       = $row->updatevon;
			$this->updateamum      = $row->insertamum;
			$this->updatevon       = $row->inservon;
			$this->firma_id=$row->firma_id;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}
		
		return true;
	}
	
	/**
	 * Laedt alle adressen zu der Person die uebergeben wird
	 * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($pers_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}
		
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM adresse WHERE person_fk=$pers_id";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$adr_obj = new adresse($this->conn);
		
			$adr_obj->adresse_id      = $row->adresse_pk;
			$adr_obj->bismeldeadresse = ($row->bismeldeadresse=='J'?true:false);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_fk;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->updateamum      = $row->creationdate;
			$adr_obj->updatevon       = $row->creationuser;
			$adr_obj->zustelladresse  = ($row->zustelladresse=='J'?true:false);
			
			$this->result[] = $adr_obj;
		}
		return true;
	}
	
	/**
	 * Laedt alle Adressen aus der Datenbank
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SELECT * FROM adresse";
		
		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		
		while($row = pg_fetch_object($res))
		{
			$adr_obj = new adresse($this->conn);
		
			$adr_obj->adresse_id      = $row->adresse_pk;
			$adr_obj->bismeldeadresse = ($row->bismeldeadresse=='J'?true:false);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_fk;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->updateamum      = $row->creationdate;
			$adr_obj->updatevon       = $row->creationuser;
			$adr_obj->zustelladresse  = ($row->zustelladresse=='J'?true:false);
			
			$this->result[] = $adr_obj;
		}
		
		return true;
	}
	
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
		//Zahlenfelder pruefen
		if(!is_numeric($this->person_id))
		{
			$this->errormsg='person_id enthaelt ungueltige Zeichen:'.$this->person_id.' - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(!is_numeric($this->typ))   
		{
			$this->errormsg='Typ enthaelt ungueltige Zeichen - adresse: '.$row->adresse_id."\n";
			return false;
		}		
		
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht länger als 255 Zeichen sein  - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(strlen($this->strasse)>255)
		{
			$this->errormsg = 'Strasse darf nicht länger als 255 Zeichen sein - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(strlen($this->plz)>10)
		{
			$this->errormsg = 'Plz darf nicht länger als 10 Zeichen sein - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(strlen($this->ort)>255)           
		{
			$this->errormsg = 'Ort darf nicht länger als 255 Zeichen sein - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(strlen($this->nation)>3)          
		{
			$this->errormsg = 'Nation darf nicht länger als 3 Zeichen sein - adresse: '.$row->adresse_id."\n";
			return false;
		}
		if(strlen($this->gemeinde)>255)
		{
			$this->errormsg = 'Gemeinde darf nicht länger als 255 Zeichen sein - adresse: '.$row->adresse_id."\n";
			return false;
		}
				
		$this->errormsg = '';
		return true;		
	}
	// ************************************************
	// * wenn $var '' ist wird "null" zurueckgegeben
	// * wenn $var !='' ist werden datenbankkritische 
	// * Zeichen mit backslash versehen und das Ergebnis
	// * unter Hochkomma gesetzt.
	// ************************************************
	function addslashes($var)
	{
		return ($var!=''?"'".addslashes($var)."'":'null');
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			
			//naechste ID aus der Sequence holen
			$qry="SELECT nextval('tbl_adresse_adresse_id_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn,$qry)))
			{
				$this->errormsg = 'Fehler beim auslesen der Sequence'."\n";
				return false;
			}
			$this->adresse_id = $row->id;
			
			$qry='INSERT INTO tbl_adresse (adresse_id, person_id, name, strasse, plz, typ, ort, nation, insertamum, insertvon,
			     gemeinde, heimatadresse, zustelladresse, firma_id, updateamum, updatevon, ext_id) VALUES('.
			      $this->addslashes($this->adresse_id).', '.
			      $this->addslashes($this->person_id).', '.
			      $this->addslashes($this->name).', '.
			      $this->addslashes($this->strasse).', '.
			      $this->addslashes($this->plz).', '.
			      $this->addslashes($this->typ).', '.
			      $this->addslashes($this->ort).', '.
			      $this->addslashes($this->nation).', now(), '.
			      $this->addslashes($this->insertvon).', '.
			      $this->addslashes($this->gemeinde).', '.
			      ($this->heimatadresse?'true':'false').', '.
			      ($this->zustelladresse?'true':'false').', '.
			      ($this->firma_id!=null?$this->addslashes($this->firma_id):'null').', now(), '.
			      $this->addslashes($this->updatevon).', '.
			      $this->addslashes($this->ext_id).');';	
			      $this->done=true;		
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->adresse_id))
			{
				$this->errormsg = 'adresse_id muss eine gueltige Zahl sein: '.$this->adresse_id."\n";
				return false;
			}
			$qryz="SELECT * FROM tbl_adresse WHERE adresse_id='$this->adresse_id';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->person_id!=$this->person_id) 				$update=true;
					if($rowz->name!=$this->name) 					$update=true;
					if($rowz->strasse!=$this->strasse) 					$update=true;
					if($rowz->plz!=$this->plz)	 					$update=true;
					if($rowz->typ!=$this->typ)		 				$update=true;
					if($rowz->ort!=$this->ort)		 				$update=true;
					if($rowz->nation!=$this->nation)	 				$update=true;
					if($rowz->gemeinde!=$this->gemeinde) 				$update=true;
					if($rowz->heimatadresse!=$this->heimatadresse?'true':'false')	$update=true;
					if($rowz->zustelladresse!=$this->zustelladresse?'true':'false') 	$update=true;
								
					if($update)
					{
						$qry='UPDATE tbl_adresse SET'.
							' person_id='.$this->addslashes($this->person_id).', '.
							' name='.$this->addslashes($this->name).', '.
							' strasse='.$this->addslashes($this->strasse).', '.
							' plz='.$this->addslashes($this->plz).', '.
						      	' typ='.$this->addslashes($this->typ).', '.
						      	' ort='.$this->addslashes($this->ort).', '.
						      	' nation='.$this->addslashes($this->nation).', '.
						      	' gemeinde='.$this->addslashes($this->gemeinde).', '. 
						      	' updateamum= now(), '.
						      	' updatevon='.$this->addslashes($this->updatevon).', '. 
						      	' heimatadresse='.($this->heimatadresse?'true':'false').', '.
						      	' zustelladresse='.($this->zustelladresse?'true':'false').' '.
						      	'WHERE adresse_id='.$this->adresse_id.';';
						      	$this->done=true;
					}
				}
			}
		}
		//echo $qry;
		if ($this->done)
		{
			if(pg_query($this->conn,$qry))
			{
				//Log schreiben
				/*$sql = $qry;
				$qry = "SELECT nextval('log_seq') as id;";
				if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
				{
					$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
					return false;
				}
							
				$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
				if(pg_query($this->conn, $qry))
					return true;
				else 
				{
					$this->errormsg = 'Fehler beim Speichern des Log-Eintrages';
					return false;
				}	*/
				return true;		
			}
			else 
			{
				$this->errormsg = "*****\nFehler beim Speichern des Adress-Datensatzes: ".$this->person_id."\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
				return false;
			}
		}
		else 
		{
			return true;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $adress_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($adress_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($adress_id) || $adress_id == '')
		{
			$this->errormsg = 'adresse_id muss eine gueltige Zahl sein'."\n";
			return false;
		}
		
		//loeschen des Datensatzes
		$qry="DELETE FROM adresse WHERE adresse_pk=$adress_id;";
		
		if(pg_query($this->conn,$qry))
		{
			//Log schreiben
			$sql = $qry;
			$qry = "SELECT nextval('log_seq') as id;";
			if(!$row = pg_fetch_object(pg_query($this->conn, $qry)))
			{
				$this->errormsg = 'Fehler beim Auslesen der Log-Sequence';
				return false;
			}
						
			$qry = "INSERT INTO log(log_pk, creationdate, creationuser, sql) VALUES('$row->id', now(), '$this->updatevon', '".addslashes($sql)."')";
			if(pg_query($this->conn, $qry))
				return true;
			else 
			{
				$this->errormsg = "Fehler beim Speichern des Log-Eintrages\n";
				return false;
			}		
		}
		else 
		{
			$this->errormsg = 'Fehler beim loeschen der Daten'."\n";
			return false;
		}		
	}
}
?>