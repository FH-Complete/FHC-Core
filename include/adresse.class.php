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
 * Klasse Adresse
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
	var $typ;		// @var string
	var $heimatadresse;	// @var boolean
	var $zustelladresse;	// @var boolean
	var $firma_id;		// @var integer
	var $updateamum;	// @var timestamp
	var $updatevon;	// @var string
	var $insertamum;      // @var timestamp
	var $insertvon;      // @var string
	var $ext_id;		// @var integer

	// *************************************************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $adress_id ID der Adresse die geladen werden soll (Default=null)
	// *        $unicode   wenn false dann wird das Encoding auf LATIN9 gesetzt
	// *                   wenn true dann auf UNICODE
	// *                   wenn null dann wird das Encoding nicht veraendert
	// *************************************************************************
	function adresse($conn,$adresse_id=null,$unicode=false)
	{
		$this->conn = $conn;

		if($unicode!=null)
		{
			if ($unicode)
				$qry = "SET CLIENT_ENCODING TO 'UNICODE';";
			else
				$qry = "SET CLIENT_ENCODING TO 'LATIN9';";

			if(!pg_query($conn,$qry))
			{
				$this->errormsg	 = "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}

		if($adresse_id != null)
			$this->load($adresse_id);
	}

	// ***********************************************
	// * Laedt die Adresse mit der ID $adresse_id
	// * @param  $adress_id ID der zu ladenden Adresse
	// * @return true wenn ok, false im Fehlerfall
	// ***********************************************
	function load($adresse_id)
	{

		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($adresse_id) || $adresse_id == '')
		{
			$this->errormsg = 'Adresse_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_adresse WHERE adresse_id='$adresse_id'";

		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = pg_fetch_object($res))
		{
			$this->adresse_id		= $row->adresse_id;
			$this->heimatadresse 	= ($row->heimatadresse=='t'?true:false);
			$this->zustelladresse	= ($row->zustelladresse=='t'?true:false);
			$this->gemeinde		= $row->gemeinde;
			$this->name			= $row->name;
			$this->nation			= $row->nation;
			$this->ort			= $row->ort;
			$this->person_id		= $row->person_id;
			$this->plz			= $row->plz;
			$this->strasse		= $row->strasse;
			$this->typ			= $row->typ;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
			$this->firma_id		= $row->firma_id;

		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	// *************************************************************************
	// * Laedt alle adressen zu der Person die uebergeben wird
	// * @param $pers_id ID der Person zu der die Adressen geladen werden sollen
	// * @return true wenn ok, false im Fehlerfall
	// *************************************************************************
	function load_pers($pers_id)
	{
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($pers_id) || $pers_id == '')
		{
			$this->errormsg = 'person_id muss eine gueltige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_adresse WHERE person_id='$pers_id'";

		if(!$res = pg_query($this->conn,$qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = pg_fetch_object($res))
		{
			$adr_obj = new adresse($this->conn, null, null);

			$adr_obj->adresse_id      = $row->adresse_id;
			$adr_obj->heimatadresse = ($row->heimatadresse=='t'?true:false);
			$adr_obj->gemeinde        = $row->gemeinde;
			$adr_obj->name            = $row->name;
			$adr_obj->nation          = $row->nation;
			$adr_obj->ort             = $row->ort;
			$adr_obj->person_id       = $row->person_id;
			$adr_obj->plz             = $row->plz;
			$adr_obj->strasse         = $row->strasse;
			$adr_obj->typ             = $row->typ;
			$adr_obj->firma_id		  = $row->firma_id;
			$adr_obj->updateamum      = $row->updateamum;
			$adr_obj->updatevon       = $row->updatevon;
			$adr_obj->insertamum      = $row->insertamum;
			$adr_obj->insertvon       = $row->insertvon;
			$adr_obj->zustelladresse  = ($row->zustelladresse=='t'?true:false);

			$this->result[] = $adr_obj;
		}
		return true;
	}

	// *******************************************
	// * Prueft die Variablen auf Gueltigkeit
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************
	function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->person_id))
		{
			$this->errormsg='person_id enthaelt ungueltige Zeichen:'.$this->person_id.' - adresse: '.$this->adresse_id."\n";
			return false;
		}
		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>255)
		{
			$this->errormsg = 'Name darf nicht länger als 255 Zeichen sein  - adresse: '.$this->adresse_id."\n";
			return false;
		}
		if(strlen($this->strasse)>255)
		{
			$this->errormsg = 'Strasse darf nicht länger als 255 Zeichen sein - adresse: '.$this->adresse_id."\n";
			return false;
		}
		if(strlen($this->plz)>10)
		{
			$this->errormsg = 'Plz darf nicht länger als 10 Zeichen sein - adresse: '.$this->adresse_id."\n";
			return false;
		}
		if(strlen($this->ort)>255)
		{
			$this->errormsg = 'Ort darf nicht länger als 255 Zeichen sein - adresse: '.$this->adresse_id."\n";
			return false;
		}
		if(strlen($this->nation)>3)
		{
			$this->errormsg = 'Nation darf nicht länger als 3 Zeichen sein - adresse: '.$this->adresse_id."\n";
			return false;
		}
		if(strlen($this->gemeinde)>255)
		{
			$this->errormsg = 'Gemeinde darf nicht länger als 255 Zeichen sein - adresse: '.$this->adresse_id."\n";
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

	// ***********************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// ***********************************************************************
	function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_adresse (person_id, name, strasse, plz, typ, ort, nation, insertamum, insertvon,
			     gemeinde, heimatadresse, zustelladresse, firma_id, updateamum, updatevon, ext_id) VALUES('.
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
			$qryz="SELECT * FROM public.tbl_adresse WHERE adresse_id='$this->adresse_id';";
			if($resultz = pg_query($this->conn, $qryz))
			{
				if($rowz = pg_fetch_object($resultz))
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
					if($rowz->firma_id!=$this->firma_id) 	$update=true;

					if($update)
					{
						$qry='UPDATE public.tbl_adresse SET'.
							' person_id='.$this->addslashes($this->person_id).', '.
							' name='.$this->addslashes($this->name).', '.
							' strasse='.$this->addslashes($this->strasse).', '.
							' plz='.$this->addslashes($this->plz).', '.
					      	' typ='.$this->addslashes($this->typ).', '.
					      	' ort='.$this->addslashes($this->ort).', '.
					      	' nation='.$this->addslashes($this->nation).', '.
					      	' gemeinde='.$this->addslashes($this->gemeinde).', '.
					      	' firma_id='.$this->addslashes($this->firma_id).','.
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
				if($this->new)
				{
					//naechste ID aus der Sequence holen
					$qry="SELECT currval('public.tbl_adresse_adresse_id_seq') as id;";
					if($result = pg_query($this->conn, $qry))
					{
						if($row = pg_fetch_object($result))
						{
							$this->adresse_id = $row->id;
							pg_query($this->conn, 'COMMIT');
							return true;
						}
						else
						{
							pg_query($this->conn, 'ROLLBACK');
							$this->errormsg = "Fehler beim Auslesen der Sequence";
							return false;
						}
					}
					else
					{
						pg_query($this->conn, 'ROLLBACK');
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						return false;
					}
				}
			}
			else
			{
				//echo $qry;
				$this->errormsg = "*****\nFehler beim Speichern des Adress-Datensatzes: ".$this->person_id."\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
				return false;
			}
			return true;
		}
		else
		{
			return true;
		}
	}

	// ********************************************************
	// * Loescht den Datenensatz mit der ID die uebergeben wird
	// * @param $adresse_id ID die geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function delete($adresse_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($adresse_id) || $adresse_id == '')
		{
			$this->errormsg = 'adresse_id muss eine gueltige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_adresse WHERE adresse_id='$adresse_id';";

		if(pg_query($this->conn,$qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim loeschen der Daten'."\n";
			return false;
		}
	}

	// ********************************************************
	// * Datenbank-Check
	// * @param $adresse_id ID die geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function check_db($conn)
	{
		$qry='SELECT adresse_id,person_id,name,strasse,plz,ort,gemeinde,nation,typ,heimatadresse,zustelladresse,firma_id,updateamum,updatevon,insertamum,insertvon
			FROM public.tbl_adresse LIMIT 1';

		if(pg_query($conn,$qry))
			return true;
		else
			return false;
	}

}
?>