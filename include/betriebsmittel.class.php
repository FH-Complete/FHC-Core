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
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmittel extends basis_db 
{
	public $new;       		// boolean
	public $result;
	
	//Tabellenspalten
	public $betriebsmittel_id;	// integer
	public $betriebsmitteltyp;	// string
	public $nummer;				// string
	public $nummerintern;		// string
	public $reservieren;		// boolean
	public $ort_kurzbz;			// string
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// string
	public $updateamum;			// timestamp
	public $updatevon;			// string
	
	/**
	 * Konstruktor
	 * @param $betriebsmittel_id ID des Betrtiebsmittels, das geladen werden soll (Default=null)
	 */
	public function __construct($betriebsmittel_id=null)
	{
		parent::__construct();
		
		if(!is_null($betriebsmittel_id))
			$this->load($betriebsmittel_id);
	}
		
	/**
	 * Laedt das Betriebsmittel mit der ID $betriebsmittel_id
	 * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($betriebsmittel_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_betriebsmittel WHERE betriebsmittel_id='".addslashes($betriebsmittel_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
				return $this->result=$row;				
			}
			else 
			{
				$this->errormsg = 'Betriebsmittel wurde nicht gefunden';
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
	 * Prueft die Daten vor dem Speichern 
	 * auf Gueltigkeit
	 */
	protected function validate()
	{
		return true;
	}
		
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank	 
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
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

		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('public.tbl_betriebsmittel_betriebsmittel_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
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
	public function delete($betriebsmittel_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_betriebsmittel WHERE betriebsmittel_id='".addslashes($betriebsmittel_id)."'";
		
		if($this->db_query($qry))
			return true;
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt die Betriebsmittel
	 *
	 * @param $betriebsmitteltyp
	 * @param $nummer
	 * @return boolean
	 */
	public function getBetriebsmittel($betriebsmitteltyp, $nummer)
	{
		$qry = "SELECT * FROM public.tbl_betriebsmittel 
				WHERE betriebsmitteltyp='".addslashes($betriebsmitteltyp)."' AND nummer='".addslashes($nummer)."' 
				ORDER BY updateamum DESC";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bm = new betriebsmittel();
				
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
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}
?>