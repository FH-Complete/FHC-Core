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
 * Klasse Betriebsmittelperson 
 * @create 13-01-2007
 */

class betriebsmittelperson
{
	var $conn;     			// @var resource DB-Handle
	var $new;       		// @var boolean
	var $errormsg;  		// @var string
	var $result = array();
	var $done=false;		// @var boolean
	
	//Tabellenspalten
	var $betriebsmittel_id;	// @var integer
	var $person_id;			// @var integer
	var $betriebsmittel_id_old;	// @var integer
	var $person_id_old;			// @var integer
	var $anmerkung;			// @var string
	var $kaution;			// @var numeric(5,2)
	var $ausgegebenam;		// @var date
	var $retouram;			// @var date
	var $ext_id;			// @var integer
	var $insertamum;		// @var timestamp
	var $insertvon;			// @var bigint
	var $updateamum;		// @var timestamp
	var $updatevon;			// @var bigint
	
	var $nummer;
	var $betriebsmitteltyp;
	var $beschreibung;
	
	// ************************************
	// * Konstruktor
	// * @param $conn      Connection
	// *        $betriebsmittel_id
	// *        $person_id
	// *        $unicode
	// ************************************
	function betriebsmittelperson($conn,$betriebsmittel_id=null,$person_id=null, $unicode=false)
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
		if($betriebsmittel_id!=null && $person_id!=null)
			$this->load($betriebsmittel_id, $person_id);
	}
	
	// *********************************************************************
	// * Laedt das Betriebsmittel mit der ID $betriebsmittel_id, person_id
	// * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittels
	// *         $person_id ID der zu ladenden Person
	// * @return true wenn ok, false im Fehlerfall
	// *********************************************************************
	function load($betriebsmittel_id, $person_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_betriebsmittel JOIN public.tbl_betriebsmittelperson USING(betriebsmittel_id) 
				WHERE betriebsmittel_id='$betriebsmittel_id' AND person_id='$person_id'";
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
				$this->person_id = $row->person_id;
				$this->anmerkung = $row->anmerkung;
				$this->kaution = $row->kaution;
				$this->ausgegebenam = $row->ausgegebenam;
				$this->retouram = $row->retouram;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
				return true;
			}
			else 
			{
				$this->errormsg = 'Es wurde kein passender Datensatz gefunden';
				return false;
			}
		}	
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}		
	}
	
	function validate()
	{
		if(!is_numeric($this->kaution))
		{
			$this->errormsg = 'Kaution ist ungueltig';
			return false;
		}		
		
		if($this->ausgegebenam!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->ausgegebenam)
								   && !ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$this->ausgegebenam))
		{
			$this->errormsg = 'Ausgegeben am Datum ist ungueltig';
			return false;
		}
		
		if($this->retouram!='' && !ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})",$this->retouram)
							   && !ereg("([0-9]{2}).([0-9]{2}).([0-9]{4})",$this->retouram))
		{
			$this->errormsg = 'Ausgegeben am Datum ist ungueltig';
			return false;
		}
		
		if(strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		
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
	
	// ******************************************************************************************
	// * Speichert den aktuellen Datensatz in die Datenbank	 
	// * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	// * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id, $person_id aktualisiert
	// * @param $new
	// * @return true wenn ok, false im Fehlerfall
	// ******************************************************************************************
	function save($new=null)
	{
		if(!$this->validate())
			return false;
			
		if($new==null)
			$new = $this->new;

		//Pruefen ob dieses Betriebsmittel dieser Person schon zugeordnet ist
		$qry = "SELECT 1 FROM public.tbl_betriebsmittelperson 
				WHERE person_id='".addslashes($this->person_id)."' AND 
					betriebsmittel_id='".addslashes($this->betriebsmittel_id)."'";
		if($result = pg_query($this->conn, $qry))
		{
			if(pg_num_rows($result)>0)
			{
				$this->errormsg = 'Dieses Betriebsmittel ist der Person bereits zugeordnet';
				return false;
			}
		}
		
		if($new)
		{
			//Neuen Datensatz einfuegen
					
			$qry='INSERT INTO public.tbl_betriebsmittelperson (betriebsmittel_id, person_id, anmerkung, kaution, 
			ausgegebenam, retouram, ext_id, insertamum, insertvon, updateamum, updatevon) VALUES('.
			     $this->addslashes($this->betriebsmittel_id).', '.
			     $this->addslashes($this->person_id).', '.
			     $this->addslashes($this->anmerkung).', '.
			     $this->addslashes($this->kaution).', '.
			     $this->addslashes($this->ausgegebenam).', '.
			     $this->addslashes($this->retouram).', '.
			     $this->addslashes($this->ext_id).',  now(), '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).');';
		}
		else
		{	
			//Pruefen ob betriebsmittel_id eine gueltige Zahl ist
			if(!is_numeric($this->betriebsmittel_id) || !is_numeric($this->person_id))
			{
				$this->errormsg = "betriebsmittel_id und Person_id muessen gueltige Zahlen sein: ".$this->betriebsmittel_id." (".$this->person_id.")\n";
				return false;
			}
			if($this->betriebsmittel_id_old=='')
				$this->betriebsmittel_id_old = $this->betriebsmittel_id;
			if($this->person_id_old=='')
				$this->person_id_old = $this->person_id;
			
			$qry='UPDATE public.tbl_betriebsmittelperson SET '.
				'betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).', '. 
				'person_id='.$this->addslashes($this->person_id).', '. 
				'anmerkung='.$this->addslashes($this->anmerkung).', '. 
				'kaution='.$this->addslashes($this->kaution).', '. 
				'ausgegebenam='.$this->addslashes($this->ausgegebenam).', '.
				'retouram='.$this->addslashes($this->retouram).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '. 
				'updateamum= now(), '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id_old).
				' AND person_id='.$this->addslashes($this->person_id_old).";";
		}
		
		if(pg_query($this->conn, $qry))
		{			
			return true;		
		}
		else 
		{
			$this->errormsg = "Fehler beim Speichern der Betriebsmittelperson";			
			return false;
		}
	}
	
	// ********************************************************
	// * Loescht den Datenensatz mit der ID die uebergeben wird
	// * @param $betriebsmittel_id ID die geloescht werden soll
	// * @param $person_id ID die geloescht werden soll
	// * @return true wenn ok, false im Fehlerfall
	// ********************************************************
	function delete($betriebsmittel_id, $person_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_betriebsmittelperson WHERE betriebsmittel_id='$betriebsmittel_id' AND person_id='$person_id'";
		
		if(pg_query($this->conn, $qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	// *****************************************
	// * Laedt alle Betriebsmittel einer Person
	// * @param person_id, $betriebsmittel_id
	// * @return true wenn ok, false wenn Fehler
	// *****************************************
	function getBetriebsmittelPerson($person_id, $betriebsmitteltyp=null)
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_betriebsmittel JOIN public.tbl_betriebsmittelperson USING(betriebsmittel_id) WHERE person_id='".addslashes($person_id)."'";
		if(!is_null($betriebsmitteltyp))
			$qry.=" AND betriebsmitteltyp='".addslashes($betriebsmitteltyp)."'";
		$qry.=" ORDER BY betriebsmitteltyp, nummer";
		
		if($result = pg_query($this->conn, $qry))
		{
			while($row = pg_fetch_object($result))
			{
				$bm = new betriebsmittelperson($this->conn, null, null, null);
				
				$bm->betriebsmittel_id = $row->betriebsmittel_id;
				$bm->beschreibung = $row->beschreibung;
				$bm->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bm->nummer = $row->nummer;
				$bm->nummerintern = $row->nummerintern;
				$bm->reservieren = ($row->reservieren=='t'?true:false);
				$bm->ort_kurzbz = $row->ort_kurzbz;
				$bm->person_id = $row->person_id;
				$bm->anmerkung = $row->anmerkung;
				$bm->kaution = $row->kaution;
				$bm->ausgegebenam = $row->ausgegebenam;
				$bm->retouram = $row->retouram;
				$bm->insertamum = $row->insertamum;
				$bm->insertvon = $row->insertvon;
				$bm->updateamum = $row->updateamum;
				$bm->updatevon = $row->updatevon;
				$bm->ext_id = $row->ext_id;
				
				$this->result[] = $bm;
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