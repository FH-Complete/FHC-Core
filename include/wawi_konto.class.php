<?php
/* Copyright (C) 2010 Technikum-Wien
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
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Klasse WaWi Konto
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/sprache.class.php');

class wawi_konto extends basis_db
{
	public $new;					//  boolean
	public $result = array();		//  Konto Objekt
	public $user; 					//  string

	//Tabellenspalten
	public $konto_id;				//  integer
	public $kontonr;				//  string
	public $beschreibung = array();	//  string array
	public $kurzbz;					//  string
	public $aktiv;					//  boolean
	public $insertamum;        		//  timestamp
	public $insertvon;				//  string
	public $updateamum;         	//  timestamp
	public $updatevon;				//  string
	public $person_id;				//  integer
	
	public $sprache; 
	
	
	/**
	 * Konstruktor
	 * @param $konto_id ID des Kontos das geladen werden soll (Default=null)
	 */
	public function __construct($konto_id=null)
	{
		parent::__construct();

		if(!isset($this->sprache))
		{
			$this->sprache = new sprache(); 
			$this->sprache->getAll();
		}
		
		if(!is_null($konto_id))
			$this->load($konto_id);
	}

	/**
	 * Laedt das Konto mit der ID $konto_id
	 * @param  $konto_id ID des zu ladenden Kontos
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($konto_id)
	{
		if(!is_numeric($konto_id) || $konto_id == '')
		{
			$this->errormsg = 'Konto_id muss eine Zahl sein';
			return false;
		}
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		
		$qry = "SELECT *, $beschreibung FROM wawi.tbl_konto WHERE konto_id=".$this->db_add_param($konto_id).';';
		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		if($row = $this->db_fetch_object())
		{
			$this->konto_id	= $row->konto_id;
			$this->kontonr = $row->kontonr;
			$this->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
			$this->kurzbz = $row->kurzbz;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Konten
	 * @param $aktiv wenn true werden nur die aktiven Datensaetze geladen, sonst alle
	 * @param $order Sortierreihenfolge
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($aktiv=null, $order='kurzbz ASC')
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
		
		$qry = "SELECT *, $beschreibung FROM wawi.tbl_konto";

		if(!is_null($aktiv))
		{
			$qry.=' WHERE aktiv='.$this->db_add_param($aktiv, FHC_BOOLEAN);
		}
		
		if($order!='')
			$qry .= ' ORDER BY '.$order;

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$obj = new wawi_konto();
			
			$obj->konto_id = $row->konto_id;
			$obj->kontonr = $row->kontonr;
			$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);			
			$obj->kurzbz = $row->kurzbz;
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->updateamum = $row->updateamum;
			$obj->updatevon = $row->updatevon;
			
			$this->result[] = $obj;
		}
		
		return true;
	}
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if(mb_strlen($this->kontonr)>32)
		{
			$this->errormsg = 'Kontonummer darf nicht laenger als 32 Zeichen sein.';
		}
		
		/*$i = 1; 
		foreach($this->sprache->result as $s)
		{
			if($s->content == true)
			{
				if(mb_strlen($this->beschreibung[$i])>256)
				{
					$this->errormsg = 'Bezeichnung darf nicht laenger als 256 Zeichen sein.';
					return false;
				}
			$i++;
			}
		}*/
		
		if(mb_strlen($this->kurzbz)>32)
		{
			$this->errormsg = 'Kurzbezeichnung darf nicht laenger als 32 Zeichen sein.';
			return false;
		}

		if(is_bool($this->aktiv)!= true)
		{
			$this->errormsg = 'Aktiv ist nicht gesetzt.';
			return false;
		}
				
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $konto_id aktualisiert
 	 * @param $new wenn true wird ein Insert durchgefuehrt, wenn false ein Update 
 	 *        und wenn null wird das new-Objekt der Klasse verwendet
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
			
		//Variablen pruefen
		if(!$this->validate())
			return false;
			
		$sprache = new sprache();
		$sprache->loadIndexArray();
		
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO wawi.tbl_konto (kontonr, ';
			foreach($this->beschreibung as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=" beschreibung[$idx],";
			}
			$qry.=' kurzbz, aktiv, insertamum, 
			insertvon, updateamum, updatevon, person_id) VALUES('.
			      $this->db_add_param($this->kontonr).', ';
			      
			reset($this->beschreibung);
			foreach($this->beschreibung as $key=>$value)
				$qry.=$this->db_add_param($value).',';
			
			$qry.=$this->db_add_param($this->kurzbz).', '.
			      $this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
				  $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).', '.
				  $this->db_add_param($this->person_id).');';
		}
		else
		{
			//Pruefen ob konto_id eine gueltige Zahl ist
			if(!is_numeric($this->konto_id))
			{
				$this->errormsg = 'konto_id muss eine gültige Zahl sein';
				return false;
			}			
									
			$qry='UPDATE wawi.tbl_konto SET'.
				' kontonr='.$this->db_add_param($this->kontonr).', ';
			
			reset($this->beschreibung);
			foreach($this->beschreibung as $key=>$value)
			{
				$idx = sprache::$index_arr[$key];
				$qry.=' beschreibung['.$idx.'] = '.$this->db_add_param($value).',';
			}
			
			$qry.=' kurzbz='.$this->db_add_param($this->kurzbz).', '.
		      	' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				' insertamum='.$this->db_add_param($this->insertamum).', '.
				' insertvon='.$this->db_add_param($this->insertvon).', '.
				' updateamum='.$this->db_add_param($this->updateamum).', '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE konto_id='.$this->db_add_param($this->konto_id, FHC_INTEGER).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('wawi.seq_konto_konto_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->konto_id = $row->id;						
						$this->db_query('COMMIT');
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}

		}
		else
		{
			$this->errormsg = $qry;
			return false;
		}
		return $this->konto_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $konto_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($konto_id)
	{
		//Pruefen ob konto_id eine gueltige Zahl ist
		if(!is_numeric($konto_id) || $konto_id == '')
		{
			$this->errormsg = 'konto_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		$qry="DELETE FROM wawi.tbl_konto WHERE konto_id=".$this->db_add_param($konto_id, FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}
	
	/**
	 * Liefert alle Konten die den Suchstring $filter entsprechen
	 * @param $filter String nach dem gefiltert wird
	 * @param $order Sortierkriterium
	 * @return array mit Konten oder false wenn ein Fehler auftritt
	 */
	public function getKonto($filter, $order='konto_id')
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
				
		$sql_query = "SELECT 
						*, $beschreibung
					  FROM 
					  	wawi.tbl_konto"; 
		
		if($filter!='')
		{
			$sql_query.=" WHERE lower(beschreibung[1]) LIKE  lower('%".$this->db_escape($filter)."%') OR 
							lower(kontonr) LIKE lower('%".$this->db_escape($filter)."%') OR
							lower(kurzbz) LIKE lower('%".$this->db_escape($filter)."%')";
		}
			
		$sql_query .= " ORDER BY $order ;";
		
		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new wawi_konto();
				
				$obj->konto_id = $row->konto_id; 
				$obj->kontonr = $row->kontonr;
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$obj->kurzbz = $row->kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj; 

			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		return true;
	}
	/**
	 * 
	 * loescht konto mit der id1 und legt dessen bestellungen und kostenstellen auf konto mit der id2 um
	 * @param $id1 konto_id des radiobuttons 
	 * @param $id2 konto_id des radiobuttons
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function zusammenlegen($id1, $id2)
	{
		$sql_query_upd1='BEGIN;';
		$sql_query_upd1.='UPDATE wawi.tbl_bestellung SET konto_id='.$this->db_add_param($id2, FHC_INTEGER).' WHERE konto_id='.$this->db_add_param($id1, FHC_INTEGER).'; ';
		$sql_query_upd1.='UPDATE wawi.tbl_konto_kostenstelle SET konto_id='.$this->db_add_param($id2, FHC_INTEGER).' WHERE konto_id='.$this->db_add_param($id1).'; ';

		$sql_query_upd1.='DELETE FROM wawi.tbl_konto WHERE konto_id='.$this->db_add_param($id1).';';
		
		if($this->db_query($sql_query_upd1))
		{
			$this->db_query("COMMIT;");
			return true;
		}
		else
		{
			$this->db_query("ROLLBACK;");
			$this->errormsg = "Fehler beim Update aufgetreten";
			return false;
		}
	}		
	
	/**
	 * 
	 * gibt alle Konten die der übergebenen Kostenstelle zugeordnet sind zurück
	 * @param $kostenstelle_id 
	 */
	public function getKontoFromKostenstelle($kostenstelle_id)
	{
		if(is_numeric($kostenstelle_id))
		{
			$sprache = new sprache();
			$beschreibung = $sprache->getSprachQuery('beschreibung');
		
			$qry = 'SELECT konto.*, '.$beschreibung.' 
					FROM 
						wawi.tbl_konto konto, wawi.tbl_konto_kostenstelle kst 
					WHERE 
						kst.konto_id = konto.konto_id 
						AND kst.kostenstelle_id ='.$this->db_add_param($kostenstelle_id, FHC_INTEGER).' 
					ORDER by konto.beschreibung ASC;';
			
			if(!$this->db_query($qry))
			{
				$this->errormsg = "Fehler bei der Abfrage aufgetreten.";
				return false; 
				
			}
			while($row = $this->db_fetch_object())
			{
				$obj = new wawi_konto(); 
				
				$obj->konto_id = $row->konto_id; 
				$obj->kontonr = $row->kontonr;
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);				
				$obj->kurzbz = $row->kurzbz; 
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum; 
				$obj->updatevon = $row->updatevon; 
				
				$this->result[] = $obj; 
			}
			return true; 
		}
		else 
		return false; 
	}
	
	/**
	 * 
	 * gibt alle Konten die der übergebenen Organisationseinheit zugeordnet sind zurück
	 * @param $kostenstelle_id 
	 */
	public function getKontoFromOE($oe_kurzbz)
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
	
		$qry = 'SELECT distinct konto.*, '.$beschreibung.' 
				FROM 
					public.tbl_organisationseinheit as orga, 
					wawi.tbl_kostenstelle as kst, 
					wawi.tbl_konto_kostenstelle as kontokst, 
					wawi.tbl_konto as konto 
				WHERE
					orga.oe_kurzbz = kst.oe_kurzbz 
					AND	kst.kostenstelle_id = kontokst.kostenstelle_id 
					AND kontokst.konto_id = konto.konto_id
					AND	orga.oe_kurzbz = '.$this->db_add_param($oe_kurzbz).' 
				ORDER BY konto.beschreibung;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten.";
			return false; 
			
		}
		while($row = $this->db_fetch_object())
		{
			$obj = new wawi_konto(); 
			
			$obj->konto_id = $row->konto_id; 
			$obj->kontonr = $row->kontonr;
			$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
			$obj->kurzbz = $row->kurzbz; 
			$obj->aktiv = $this->db_parse_bool($row->aktiv);
			$obj->insertamum = $row->insertamum;
			$obj->insertvon = $row->insertvon;
			$obj->updateamum = $row->updateamum; 
			$obj->updatevon = $row->updatevon; 
			
			$this->result[] = $obj; 
		}
		return true; 
	}

	/**
	 * Liefert alle Konten einer Person
	 * @param $person_id Person ID
	 * @return array mit Konten oder false wenn ein Fehler auftritt
	 */
	public function getKontoPerson($person_id)
	{
		$sprache = new sprache();
		$beschreibung = $sprache->getSprachQuery('beschreibung');
				
		$sql_query = "SELECT 
						*, $beschreibung
					  FROM 
					  	wawi.tbl_konto"; 
		
		$sql_query.=" WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);

		$sql_query .= " ORDER BY kurzbz;";
		
		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new wawi_konto();
				
				$obj->konto_id = $row->konto_id; 
				$obj->kontonr = $row->kontonr;
				$obj->beschreibung = $sprache->parseSprachResult('beschreibung', $row);
				$obj->kurzbz = $row->kurzbz;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj; 

			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
		return true;
	}
}
?>
