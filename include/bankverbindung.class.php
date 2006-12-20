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
 * Klasse bankverbindung 
 * @create 20-12-2006
 */

class bankverbindung
{
	var $conn;     // @var resource DB-Handle
	var $new;       // @var boolean
	var $errormsg;  // @var string
	var $result = array(); // @var adresse Objekt
	var $done=false;	// @var boolean
	
	//Tabellenspalten
	Var $bankverbindung_id;	// @var integer
	var $person_id;		// @var integer
	var $name;			// @var string
	var $anschrift;		// @var string
	var $bic;			// @var string
	var $blz;			// @var string
	var $iban;			// @var string
	var $kontonr;			// @var string
	var $typ;			// @var p=Privatkonto, f=Firmenkonto
	var $verrechnung;		// @var boolean
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $kontakt_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function bankverbindung($conn,$bankverbindung_id=null, $unicode=false)
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
		
	}
	
	/**
	 * Laedt die Bankverbindung mit der ID $bankverbindung_id
	 * @param  $bankverbindung_id ID der zu ladenden  Email
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($bankverbindung_id)
	{
		//noch nicht implementiert
	}
			
	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{		
		//Gesamtlaenge pruefen
		//$this->errormsg = 'Eine der Maximiallaengen wurde ueberschritten';
		if(strlen($this->name)>64)
		{
			$this->errormsg = 'Name darf nicht länger als 64 Zeichen sein';
			return false;
		}
		if(strlen($this->anschrift)>128) 
		{
			$this->errormsg = 'Anschrift darf nicht länger als 128 Zeichen sein';
			return false;
		}
		if(strlen($this->blz)>16)
		{
			$this->errormsg = 'BLZ darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->bic)>16)
		{
			$this->errormsg = 'BIC darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->kontonr)>16)
		{
			$this->errormsg = 'KontoNr darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(strlen($this->iban)>32)
		{
			$this->errormsg = 'IBAN darf nicht länger als 32 Zeichen sein';
			return false;
		}
		
		//Zahlenwerte ueberpruefen
		$this->errormsg = 'Ein Zahlenfeld enthaelt ungueltige Zeichen';
		if(!is_numeric($this->person_id))         return false;
		
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
	 * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	function save()
	{
		$this->done=false;
		//Variablen pruefen
		if(!$this->checkvars())
			return false;
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
					
			$qry = 'INSERT INTO tbl_bankverbindung  (person_id, name, anschrift, blz, bic,
			       kontonr, iban, typ, ext_id, verrechnung, insertamum, insertvon, updateamum, updatevon) VALUES('.
			       $this->addslashes($this->person_id).', '.
			       $this->addslashes($this->name).', '.
			       $this->addslashes($this->anschrift).', '.
			       $this->addslashes($this->blz).', '.
			       $this->addslashes($this->bic).', '. 
			       $this->addslashes($this->kontonr).', '.
			       $this->addslashes($this->iban).', '.
			       $this->addslashes($this->typ).', '.
			       $this->addslashes($this->ext_id).', '.
			      ($this->verrechnung?'true':'false').',  now(), '.
			       $this->addslashes($this->insertvon).', now(), '.
			       $this->addslashes($this->updatevon).');';	
			$this->done=true;		
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			
			//Pruefen ob bankverbindung_id eine gueltige Zahl ist
			if(!is_numeric($this->bankverbindung_id))
			{
				$this->errormsg = 'bankverbindung_id muss eine gueltige Zahl sein: '.$this->bankverbindung_id.' ('.$this->person_id.')';
				return false;
			}
			if(!is_numeric($this->person_id))
			{
				$this->errormsg = 'person_id muss eine gueltige Zahl sein: '.$this->person_id.'';
				return false;
			}
			$qry="SELECT * FROM tbl_bankverbindung WHERE bankverbindung_id='$this->bankverbindung_id';";
			if($resultz = pg_query($this->conn, $qry))
			{
				while($rowz = pg_fetch_object($resultz))
				{
					$update=false;			
					if($rowz->person_id!=$this->person_id) 				$update=true;
					if($rowz->name!=$this->name)	 				$update=true;
					if($rowz->anschrift!=$this->anschrift)				$update=true;
					if($rowz->bic!=$this->bic)						$update=true;
					if($rowz->blz!=$this->blz)	 					$update=true;
					if($rowz->iban!=$this->iban) 					$update=true;
					if($rowz->kontonr!=$this->kontonr)					$update=true;
					if($rowz->typ!=$this->typ)	 					$update=true;
					if($rowz->verrechnung!=$this->verrechnung)			$update=true;
					if($rowz->ext_id!=$this->ext_id) 					$update=true;
				
					if($update)
					{
						$qry='UPDATE bankverbindung SET '.
						'person_id='.$this->addslashes($this->person_id).', '. 
						'name='.$this->addslashes($this->name).', '.
			     			'anschrift='.$this->addslashes($this->anschrift).', '.
			     			'blz='.$this->addslashes($this->blz).', '. 
			     			'bic='.$this->addslashes($this->bic).', '.
			     			'kontonr='.$this->addslashes($this->kontonr).', '.
			     			'iban='.$this->addslashes($this->iban).', '.
			     			'typ='.$this->addslashes($this->typ).', '.
			     			'zustellung='.($this->zustellung?'true':'false').', '.
			     			'ext_id='.$this->addslashes($this->ext_id).' '.
			     			'WHERE bankverbindung_id='.$this->addslashes($this->bankverbindung_id).';';
						$this->done=true;
					}
				}
			}
		}
		//echo $qry;
		if ($this->done)
		{
			if(pg_query($this->conn, $qry))
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
				$this->errormsg = 'Fehler beim Speichern der Daten';
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
	 * @param $bankverbindung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($bankverbindung_id)
	{
		//noch nicht implementiert!	
	}
}
?>