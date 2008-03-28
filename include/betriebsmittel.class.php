<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Klasse Betriebsmittel 
 * @create 22-01-2007
 */

class betriebsmittel
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $result;
	var $done=false;		// @var boolean
	
	//Tabellenspalten
	var $betriebsmittel_id;	// @var integer
	var $betriebsmitteltyp;	// @var string
	var $nummer;			// @var string
	var $nummerintern;		// @var string
	var $reservieren;		// @var boolean
	var $ort_kurzbz;		// @var string
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;		// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;		// @var bigint
	
	/**
	 * Konstruktor
	 * @param $conn      Connection
	 *        $betriebsmittel_id ID des Betrtiebsmittels, das geladen werden soll (Default=null)
	 */
	function betriebsmittel($conn,$betriebsmittel_id=null, $unicode=false)
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
				$this->errormsg= "Encoding konnte nicht gesetzt werden";
				return false;
			}
		}
	}
	
	// **************************************************************
	// * Laedt das Betriebsmittel mit der ID $betriebsmittel_id
	// * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittel
	// * @return true wenn ok, false im Fehlerfall
	// ***************************************************************
	function load($betriebsmittel_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_betriebsmittel WHERE betriebsmittel_id='$betriebsmittel_id'";
		
		if($result = pg_query($this->conn, $qry))
		{
			if($row = pg_fetch_object($result))
			{
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->udpatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
				return true;				
			}
			else 
			{
				$this->errormsg = 'Betriebsmittel wurde nicht gefunden';
				return false;
			}
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
	
	// *************************************
	// * Prueft die Daten vor dem Speichern 
	// * auf Gueltigkeit
	// *************************************
	function validate()
	{
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
	
	// *******************************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank	 
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	// * @return true wenn ok, false im Fehlerfall
	// *******************************************************************************
	function save($new=null)
	{
		if($new==null)
			$new=$this->new;
		
		if(!$this->validate())
			return false;
		
		if($new)
		{
			//Neuen Datensatz einfuegen					
			$qry='INSERT INTO public.tbl_betriebsmittel (beschreibung, betriebsmitteltyp, nummer, nummerintern, reservieren, ort_kurzbz,
				ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->betriebsmitteltyp).', '.
			     $this->addslashes($this->nummer).', '.
			     $this->addslashes($this->nummerintern).', '.
			     ($this->reservieren?'true':'false').', '. 
			     $this->addslashes($this->ort_kurzbz).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
			 $this->done=true;			
		}
		else
		{			
			if(!is_numeric($this->betriebsmittel_id))
			{
				$this->errormsg = 'Betriebsmittel_id muss eine gueltige Zahl sein';
				return false;
			}
			
			$qry='UPDATE public.tbl_betriebsmittel SET '.
				'betriebsmitteltyp='.$this->addslashes($this->betriebsmitteltyp).', '. 
				'beschreibung='.$this->addslashes($this->beschreibung).', '. 
				'nummer='.$this->addslashes($this->nummer).', '.  
				'nummerintern='.$this->addslashes($this->nummerintern).', '.  
				'reservieren='.($this->reservieren?'true':'false').', '.
				'ort_kurzbz='.$this->addslashes($this->ort_kurzbz).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '. 
			     	'updateamum= now(), '.
			     	'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).';';
		}
		
		if(pg_query($this->conn, $qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_betriebsmittel_betriebsmittel_id_seq') as id;";
				if($result = pg_query($this->conn, $qry))
				{
					if($row = pg_fetch_object($result))
					{
						$this->betriebsmittel_id = $row->id;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence';
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Lesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else 
		{
			$this->errormsg = "Fehler beim Speichern des Betriebsmittel-Datensatzes";
			return false;
		}
	}
	
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $betriebsmittel_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	function delete($betriebsmittel_id)
	{
		//noch nicht implementiert!	
	}
	
	function getBetriebsmittel($betriebsmitteltyp, $nummer)
	{
		$qry = "SELECT * FROM public.tbl_betriebsmittel WHERE betriebsmitteltyp='".addslashes($betriebsmitteltyp)."' AND nummer='".addslashes($nummer)."' ORDER BY updateamum DESC";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$bm = new betriebsmittel($this->conn, null, null);
				
				$bm->betriebsmittel_id = $row->betriebsmittel_id;
				$bm->beschreibung = $row->beschreibung;
				$bm->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bm->nummer = $row->nummer;
				$bm->nummerintern = $row->nummerintern;
				$bm->reservieren = $row->reservieren;
				$bm->ort_kurzbz = $row->ort_kurzbz;
				$bm->updateamum = $row->updateamum;
				$bm->updatevon = $row->updatevon;
				$bm->insertamum = $row->insertamum;
				$bm->insertvon = $row->insertvon;
				
				$this->result[] = $bm;
			}
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim laden der Daten';
			return false;
		}
	}
}
?>