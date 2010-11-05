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
	
	public $sprache; 
	public $spracheAnzahl; 
	
	
	/**
	 * Konstruktor
	 * @param $konto_id ID des Kontos das geladen werden soll (Default=null)
	 */
	public function __construct($konto_id=null)
	{
		$this->sprache = new sprache(); 
		$this->spracheAnzahl = $this->sprache->getAnzahl(); 
		parent::__construct();

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
		//Pruefen ob konto_id eine gueltige Zahl ist
		if(!is_numeric($konto_id) || $konto_id == '')
		{
			$this->errormsg = 'Konto_id muss eine Zahl sein';
			return false;
		}
		
		$qry_beschreibung = '';
		
		for($i=1; $i<=$this->spracheAnzahl; $i++)
		{
			$qry_beschreibung .= " beschreibung[".$i."] as beschreibung".$i." ";
			if($i != $this->spracheAnzahl)
			{
				$qry_beschreibung .= ",";
			} 
		}
		//Daten aus der Datenbank lesen
		$qry = "SELECT *, $qry_beschreibung FROM wawi.tbl_konto WHERE konto_id='".addslashes($konto_id)."'";

		
		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->konto_id	= $row->konto_id;
			$this->kontonr = $row->kontonr;
			
			for ($i=1; $i<=$this->spracheAnzahl; $i++)
			{
				$this->beschreibung[$i] = $row->{'beschreibung'.$i}; 		
			}

			$this->kurzbz = $row->kurzbz;
			$this->aktiv = ($row->aktiv=='t'?true:false);
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
	public function getAll($aktiv=null, $order='beschreibung DESC')
	{
		//Daten aus der Datenbank lesen
		
		$qry_beschreibung = '';
		
		for($i=1; $i<=$this->spracheAnzahl; $i++)
		{
			$qry_beschreibung .= " beschreibung[".$i."] as beschreibung".$i." ";
			if($i != $this->spracheAnzahl)
			{
				$qry_beschreibung .= ",";
			} 
		}

		$qry = "SELECT *, $qry_beschreibung FROM wawi.tbl_konto";
		if(!is_null($aktiv))
		{
			$qry.=' WHERE aktiv='.($aktiv?'true':'false');
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
			for ($i=1; $i<=$this->spracheAnzahl; $i++)
			{
				$obj->beschreibung[$i] = $row->{'beschreibung'.$i}; 	
			}
			$obj->kurzbz = $row->kurzbz;
			$obj->aktiv = ($row->aktiv=='t'?true:false);
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
		
		
		for($i=1; $i<=$this->spracheAnzahl; $i++)
		{
			if(mb_strlen($this->beschreibung[$i])>256)
			{
				$this->errormsg = 'Bezeichnung darf nicht laenger als 256 Zeichen sein.';
				return false;
			}
		}
		
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

		if($this->new)
		{
			$qry_beschreibung ='';
			
			for($i=1; $i<=$this->spracheAnzahl; $i++)
			{
				$qry_beschreibung .=$this->addslashes($this->beschreibung[$i]);
				if($i != $this->spracheAnzahl)
				{
					$qry_beschreibung .= ',';
				}
			}
		
			
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO wawi.tbl_konto (kontonr, beschreibung, kurzbz, aktiv, insertamum, 
			insertvon, updateamum, updatevon) VALUES('.
			      $this->addslashes($this->kontonr).', '.
			      "ARRAY[$qry_beschreibung],".
			      $this->addslashes($this->kurzbz).', '.
			      ($this->aktiv?'true':'false').', '.
			      $this->addslashes($this->insertamum).', '.
			      $this->addslashes($this->insertvon).', '.
				  $this->addslashes($this->updateamum).', '.
			      $this->addslashes($this->updatevon).');';
		}
		else
		{
			//Pruefen ob konto_id eine gueltige Zahl ist
			if(!is_numeric($this->konto_id))
			{
				$this->errormsg = 'konto_id muss eine gültige Zahl sein: '.$this->konto_id."\n";
				return false;
			}
			
			$qry_beschreibung = '';
			
			for($i=1; $i<=$this->spracheAnzahl; $i++)
			{
				$qry_beschreibung .= " beschreibung[$i]=".$this->addslashes($this->beschreibung[$i]).','; 
			}
			
			
			$qry='UPDATE wawi.tbl_konto SET'.
				' kontonr='.$this->addslashes($this->kontonr).', '.
				$qry_beschreibung.
				' kurzbz='.$this->addslashes($this->kurzbz).', '.
		      	' aktiv='.($this->aktiv?'true':'false').', '.
				' insertamum='.$this->addslashes($this->insertamum).', '.
				' insertvon='.$this->addslashes($this->insertvon).', '.
				' updateamum='.$this->addslashes($this->updateamum).', '.
		      	' updatevon='.$this->addslashes($this->updatevon).' '.
		      	' WHERE konto_id='.$this->konto_id.';';
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

		//loeschen des Datensatzes
		$qry="DELETE FROM wawi.tbl_konto WHERE konto_id='".addslashes($konto_id)."';";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten'."\n";
			return false;
		}
	}
	
	/**
	 * Liefert alle Konten die den Kriterien entsprechen
	 * @param $filter String nach dem gefiltert wird
	 * @param $order Sortierkriterium
	 * @return array mit Konten oder false wenn ein Fehler auftritt
	 */
	public function getKonto($filter, $order='konto_id')
	{
		$qry_beschreibung = '';	
		for($i=1; $i<=$this->spracheAnzahl; $i++)
		{	
			$qry_beschreibung .= " beschreibung[".$i."] as beschreibung".$i." ";
			if($i != $this->spracheAnzahl)
			{
				$qry_beschreibung .= ",";
			} 
		}		
		$sql_query = "SELECT 
						*, $qry_beschreibung
					  FROM 
					  	wawi.tbl_konto"; 
		
		if($filter!='')
		{
			$sql_query.=" where lower(beschreibung[1]) LIKE  lower('%".addslashes($filter)."%') OR 
							lower(kontonr) LIKE lower('%".addslashes($filter)."%') OR
							lower(kurzbz) LIKE lower('%".addslashes($filter)."%')";

		}
			
		$sql_query .= " ORDER BY $order";
		$sql_query .=";";
		/*if($filter=='')
		   $sql_query .= " LIMIT 30";*/
		
		if($this->db_query($sql_query))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new wawi_konto();
				
				$obj->konto_id = $row->konto_id; 
				$obj->kontonr = $row->kontonr;
				for ($i=1; $i<=$this->spracheAnzahl; $i++)
				{
					$obj->beschreibung[$i] = $row->{'beschreibung'.$i}; 	
				}
				$obj->kurzbz = $row->kurzbz;
				$obj->aktiv = ($row->aktiv=='t'?true:false);
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				
				$this->result[] = $obj; 

			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
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
		$msg='';
		$sql_query_upd1="BEGIN;";
		$sql_query_upd1.="UPDATE wawi.tbl_bestellung SET konto_id='$id2' WHERE konto_id='$id1'; ";
		$sql_query_upd1.="UPDATE wawi.tbl_konto_kostenstelle SET konto_id='$id2' WHERE konto_id='$id1'; ";

		$sql_query_upd1.="DELETE FROM wawi.tbl_konto WHERE konto_id='$id1';";
		
		if($this->db_query($sql_query_upd1))
		{
			$this->db_query("COMMIT;");
			return true;
		}
		else
		{
			$this->db_query("ROLLBACK;");
			$this->errormsg = "fehler beim Update aufgetreten";
			return false;
		}
		$id1=0;
		$id2=0;
	}		
	
}
?>