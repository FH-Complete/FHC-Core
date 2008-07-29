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
 * Klasse firma
 * @create 18-12-2006
 */

class firma
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $result = array(); 	// @var adresse Objekt

	//Tabellenspalten
	var $firma_id;			// @var integer
	var $name;			// @var string
	var $adresse;			// @var string
	var $email;			// @var string
	var $telefon;			// @var string
	var $fax;			// @var string
	var $anmerkung;		// @var string
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	var $firmentyp_kurzbz;	// @var
	var $schule; // @var boolean

	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $firma_id ID der Adresse die geladen werden soll (Default=null)
	 */
	function firma($conn,$firma_id=null, $unicode=false)
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
		if($firma_id != null)
			$this->load($firma_id);
	}

	/**
	 * Laedt die Firma mit der ID $firma_id
	 * @param  $firma_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	function load($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}
		
		$qry = "SElECT * FROM public.tbl_firma WHERE firma_id='$firma_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->firma_id = $row->firma_id;
				$this->name = $row->name;
				$this->adresse = $row->adresse;
				$this->email  = $row->email;
				$this->telefon = $row->telefon;
				$this->fax = $row->fax;
				$this->anmerkung = $row->anmerkung;
				$this->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->schule = ($row->schule=='t'?true:false);
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
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	function checkvars()
	{

		//Gesamtlaenge pruefen
		//$this->errormsg='Eine der Gesamtlaengen wurde ueberschritten';
		if(strlen($this->name)>128)
		{
			$this->errormsg = 'Name darf nicht länger als 128 Zeichen sein  - firma_id: '.$this->firma_id.'/'.$this->name;
			return false;
		}
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein - firma_id: '.$this->firma_id.'/'.$this->name;
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
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
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
			$qry='INSERT INTO public.tbl_firma (name, adresse, email, telefon, fax, anmerkung, firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon, ext_id, schule) VALUES('.
			     $this->addslashes($this->name).', '.
			     $this->addslashes($this->adresse).', '.
			     $this->addslashes($this->email).', '.
			     $this->addslashes($this->telefon).', '.
			     $this->addslashes($this->fax).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->firmentyp_kurzbz).', '.
			     $this->addslashes($this->updateamum).', '.
			     $this->addslashes($this->updatevon).', '.
			     $this->addslashes($this->insertamum).', '.
			     $this->addslashes($this->insertvon).', '.
			     $this->addslashes($this->ext_id).','.
			     ($this->schule?'true':'false').'); ';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE public.tbl_firma SET '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'name='.$this->addslashes($this->name).', '.
				'adresse='.$this->addslashes($this->adresse).', '.
				'email='.$this->addslashes($this->email).', '.
				'telefon='.$this->addslashes($this->telefon).', '.
				'fax='.$this->addslashes($this->fax).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'updateamum= now(), '.
		     	'updatevon='.$this->addslashes($this->updatevon).', '.
		     	'firmentyp_kurzbz='.$this->addslashes($this->firmentyp_kurzbz).', '.
		     	'schule='.($this->schule?'true':'false').' '.
				'WHERE firma_id='.$this->addslashes($this->firma_id).';';
		}
		//echo $qry;
		if(pg_query($this->conn,$qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_firma_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->firma_id = $row->id;
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
			$this->errormsg = "*****\nFehler beim Speichern des Firma-Datensatzes.\n".$qry."\n".pg_errormessage($this->conn)."\n*****\n";
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $firma_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_firma WHERE firma_id='$firma_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt alle Firmen
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getAll()
	{
		$qry = "SElECT * FROM public.tbl_firma ORDER BY name";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$fa = new firma($this->conn, null, null);
				
				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->adresse = $row->adresse;
				$fa->email  = $row->email;
				$fa->telefon = $row->telefon;
				$fa->fax = $row->fax;
				$fa->anmerkung = $row->anmerkung;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = ($row->schule=='t'?true:false);
				$this->result[] = $fa;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
}
?>