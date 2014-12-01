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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>
 */
/**
 * Klasse projektbenutzer
 * @create 08-08-2011
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projektbenutzer extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten 
	public $projektbenutzer_id;	// integer
	public $projekt_kurzbz;		// string
	public $uid;			// string
	public $funktion_kurzbz;	// string
	public $nummer;			// string
	public $titel;			// string
	public $beschreibung;		// string
	public $beginn;			// date 	
	public $ende;			// date 	
	public $oe_kurzbz;		// string
	public $ext_id;			// integer
	public $insertamum;		// timestamp
	public $insertvon;		// bigint
	public $updateamum;		// timestamp
	public $updatevon;		// bigint


	// Return attribute
	public $uids = array();
	public $kw = array();

	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projekt_kurzbz=null)
	{
		parent::__construct();

		if($projekt_kurzbz != null) 	
			$this->loadProjekt($projekt_kurzbz);
	}

	/**
	 * Laedt die Projektbenutzer mit der ID $projekt_kurzbz
	 * @param  $projekt_kurzbz ID der zu ladenden Projektbenutzer
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load()
	{
		$qry = "SELECT *, now() AS now FROM fue.tbl_projektbenutzer JOIN fue.tbl_projekt USING (projekt_kurzbz) 
			WHERE  (beginn<now() OR beginn IS NULL) AND (ende>now() OR ende IS NULL);";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projektbenutzer();
				$obj->projektbenutzer_id = $row->projektbenutzer_id;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->uid = $row->uid;			
				$obj->funktion_kurzbz = $row->funktion_kurzbz;	
				$obj->nummer= $row->nummer;
				$obj->titel= $row->titel;
				$obj->beschreibung= $row->beschreibung;
				$obj->beginn= $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz= $row->oe_kurzbz;
				
				$this->result[] = $obj;
			}
			return false;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Ermittelt die User aus $result und vereinfacht (Unique)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getUIDs()
	{
		foreach($this->result as $obj)
			$this->uids[]=$obj->uid;
		$this->uids=array_unique($this->uids, SORT_REGULAR);
		return asort($this->uids, SORT_REGULAR);
		//return true;
	}

	/**
	 * Ermittelt die User aus $result und vereinfacht (Unique)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjektePerUID($uid,$date=null)
	{
		$count=0;
		foreach($this->result as $obj)
			if ($uid==$obj->uid) // Wenn uid im Projekt ist dann
				if( !is_null($date) && ( $date>strtotime($obj->beginn) || is_null($obj->beginn) ) && ( $date<strtotime($obj->ende) || is_null($obj->ende) ) )
					$count++;
		return $count;
	}
	
	/**
	 * Laedt die Projektbenutzer mit der ID $projekt_kurzbz
	 * @param  $projekt_kurzbz ID der zu ladenden Projektbenutzer
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadProjekt($projekt_kurzbz)
	{
		$qry = "SELECT * FROM fue.tbl_projekt WHERE projekt_kurzbz=".$this->db_add_param($projekt_kurzbz);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->nummer= $row->nummer;
				$this->titel= $row->titel;
				$this->beschreibung= $row->beschreibung;
				$this->beginn= $row->beginn;
				$this->ende = $row->ende;
				$this->oe_kurzbz= $row->oe_kurzbz;		

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
	 * Laedt die Projektarbeit mit der ID $projekt_kurzbz
	 * @param  $projekt_kurzbz ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekte($oe=null)
	{
		$qry = 'SELECT * FROM fue.tbl_projekt';
		if (!is_null($oe))
			$qry.= " WHERE oe_kurzbz=".$this->db_add_param($oe);
		$qry.= ' ORDER BY oe_kurzbz;';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projekt();
				
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;

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

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{

		//Gesamtlaenge pruefen
		if ($this->projekt_kurzbz==null)
		{
			$this->errormsg='Projekt kurzbz darf nicht NULL sein!';
		}
		if ($this->oe_kurzbz==null)
		{
			$this->errormsg='OE kurbz darf nicht NULL sein!';
		}
		if(mb_strlen($this->projekt_kurzbz)>16)
		{
			$this->errormsg = 'Projektyp_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->nummer)>8)
		{
			$this->errormsg = 'Nummer darf nicht länger als 8 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel)>256)
		{
			$this->errormsg = 'Titel darf nicht länger als 256 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO fue.tbl_projekt (projekt_kurzbz, nummer, titel,beschreibung, beginn, ende, oe_kurzbz) VALUES('.
		     $this->db_add_param($this->projekt_kurzbz).', '.
		     $this->db_add_param($this->nummer).', '.
		     $this->db_add_param($this->titel).', '.
		     $this->db_add_param($this->beschreibung).', '.
		     $this->db_add_param($this->beginn).', '.
		     $this->db_add_param($this->ende).', '.
		     $this->db_add_param($this->oe_kurzbz).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			$qry='UPDATE fue.tbl_projekt SET '.
				'projekt_kurzbz='.$this->db_add_param($this->projekt_kurzbz).', '.
				'nummer='.$this->db_add_param($this->nummer).', '.
				'titel='.$this->db_add_param($this->titel).', '.
				'beschreibung='.$this->db_add_param($this->beschreibung).', '.
				'beginn='.$this->db_add_param($this->beginn).', '.
				'ende='.$this->db_add_param($this->ende).', '.
				'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).' '.
				'WHERE projekt_kurzbz='.$this->db_add_param($this->projekt_kurzbz).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
				$this->db_query('COMMIT');
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}
}
?>
