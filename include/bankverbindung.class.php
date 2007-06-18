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
		
		if($unicode!=null)
		{
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
		
		if($bankverbindung_id!=null)
			$this->load($bankverbindung_id);

	}

	/**
	 * Laedt die Bankverbindung mit der ID $bankverbindung_id
	 * @param  $bankverbindung_id ID der zu ladenden Bankverbindung
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($bankverbindung_id)
	{
		if(!is_numeric($bankverbindung_id))
		{
			$this->errormsg = 'Bankverbindung_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE bankverbindung_id='$bankverbindung_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->bankverbindung_id = $row->bankverbindung_id;
				$this->person_id = $row->person_id;
				$this->name = $row->name;
				$this->anschrift = $row->anschrift;
				$this->bic = $row->bic;
				$this->blz = $row->blz;
				$this->iban = $row->iban;
				$this->kontonr = $row->kontonr;
				$this->typ = $row->typ;
				$this->verrechnung = ($row->verrechnung=='t'?true:false);
				$this->updateamum = $row->updateamum;
				$this->udpatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;
			}
			else 
			{
				$this->errormsg = 'Datensatz wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
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
				
		if(!is_numeric($this->person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
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
	 * andernfalls wird der Datensatz mit der ID in $bankverbindung_id aktualisiert
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

			$qry = 'BEGIN;INSERT INTO public.tbl_bankverbindung  (person_id, name, anschrift, blz, bic,
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

			$qry='UPDATE public.tbl_bankverbindung SET '.
			'person_id='.$this->addslashes($this->person_id).', '.
			'name='.$this->addslashes($this->name).', '.
 			'anschrift='.$this->addslashes($this->anschrift).', '.
 			'blz='.$this->addslashes($this->blz).', '.
 			'bic='.$this->addslashes($this->bic).', '.
 			'kontonr='.$this->addslashes($this->kontonr).', '.
 			'iban='.$this->addslashes($this->iban).', '.
 			'typ='.$this->addslashes($this->typ).', '.
 			'verrechnung='.($this->verrechnung?'true':'false').', '.
 			'ext_id='.$this->addslashes($this->ext_id).', '.
 			'updateamum='.$this->addslashes($this->updateamum).','.
 			'updatevon='.$this->addslashes($this->updatevon).' '.
 			'WHERE bankverbindung_id='.$this->addslashes($this->bankverbindung_id).';';
		}
		//echo $qry."\n";
		if(pg_query($this->conn, $qry))
		{
			if($this->new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('public.tbl_bankverbindung_bankverbindung_id_seq') as id";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->bankverbindung_id = $row->id;
						pg_query($this->conn, 'COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						pg_query($this->conn, 'ROLLBACK');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					pg_query($this->conn, 'ROLLBACK');
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $bankverbindung_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($bankverbindung_id)
	{
		if(!is_numeric($bankverbindung_id))
		{
			$this->errormsg = 'Bankverbindung_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_bankverbindung WHERE bankverbindung_id='$bankverbindung_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}
	
	/**
	 * Laedt die Bankverbindung mit der ID $bankverbindung_id
	 * @param  $bankverbindung_id ID der zu ladenden Bankverbindung
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load_pers($person_id)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_bankverbindung WHERE person_id='$person_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$obj = new bankverbindung($this->conn, null, null);
				
				$obj->bankverbindung_id = $row->bankverbindung_id;
				$obj->person_id = $row->person_id;
				$obj->name = $row->name;
				$obj->anschrift = $row->anschrift;
				$obj->bic = $row->bic;
				$obj->blz = $row->blz;
				$obj->iban = $row->iban;
				$obj->kontonr = $row->kontonr;
				$obj->typ = $row->typ;
				$obj->verrechnung = ($row->verrechnung=='t'?true:false);
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

}
?>