<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Klasse projekt
 * 
 * Verwaltet die Projekte
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projekt extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// projekt Objekt

	//Tabellenspalten
	public $projekt_kurzbz;	// string
	public $nummer;			// string
	public $titel;			// string
	public $beschreibung;	// string
	public $beginn;			// date 	
	public $ende;			// date 	
	public $oe_kurzbz;		// string
	public $insertamum;		// timestamp
	public $insertvon;		// string
	public $updateamum;		// timestamp
	public $updatevon;		// string
	public $budget; 
    public $farbe; 


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projekt_kurzbz=null)
	{
		parent::__construct();

		if($projekt_kurzbz != null) 	
			$this->load($projekt_kurzbz);
	}

	/**
	 * Laedt die Projek mit der Kurzbezeichnung $projekt_kurzbz
	 * @param  $projekt_kurzbz Kurzbz des Projekts
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projekt_kurzbz)
	{
		$qry = "SELECT * FROM fue.tbl_projekt WHERE projekt_kurzbz='".addslashes($projekt_kurzbz)."'";
		
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
				$this->budget= $row->budget;
                $this->farbe= $row->farbe;

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
     * Laedt alle aktuellen Projekte
     * @param $kommend lädt auch alle zukünftigen
     * @return boolean 
     */
    public function getProjekteAktuell($filter_kommende = false, $oe=null)
    {
        $qry = 'SELECT * FROM fue.tbl_projekt WHERE ';
        
        if($filter_kommende)
            $qry.= " ((beginn < CURRENT_TIMESTAMP AND ende > CURRENT_TIMESTAMP) OR beginn > CURRENT_TIMESTAMP)";
        else
            $qry.=" (beginn < CURRENT_TIMESTAMP AND ende > CURRENT_TIMESTAMP)";
        
        
        if(!is_null($oe))
            $qry.= ' AND oe_kurzbz='.$this->db_add_param($oe);
        
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
				$obj->budget = $row->budget;
                $obj->farbe = $row->farbe;
                $obj->aufwandstyp_kurzbz = $row->aufwandstyp_kurzbz;

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
     * Laedt alle Projekte die zwischen beginn und ende liegen
     * @param $beginn 
     * @param $ende 
     * @param $oe 
     * @return boolean 
     */
    public function getProjekteInZeitraum($beginn, $ende, $oe=null)
    {
		$qry = 'select * from fue.tbl_projekt where beginn <= '.$this->db_add_param($ende).' and ende >= '.$this->db_add_param($beginn);
		if (!is_null($oe))
			$qry.= " AND oe_kurzbz='".addslashes($oe)."'";
		$qry.= ' ORDER BY oe_kurzbz;';
		//echo $qry;
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
				$obj->budget = $row->budget;
                $obj->farbe = $row->farbe;

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
	 * Laedt die Projeke einer Organisationseinheit
	 * @param  $projekt_kurzbz Kurzbezeichnung des Projekts
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekte($oe=null)
	{
		$qry = 'SELECT * FROM fue.tbl_projekt';
		if (!is_null($oe))
			$qry.= " WHERE oe_kurzbz='".addslashes($oe)."'";
		$qry.= ' ORDER BY oe_kurzbz;';
		//echo $qry;
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
				$obj->budget = $row->budget;
                $obj->farbe = $row->farbe;
                $obj->aufwandstyp_kurzbz = $row->aufwandstyp_kurzbz;

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

			$qry='INSERT INTO fue.tbl_projekt (projekt_kurzbz, nummer, titel,beschreibung, beginn, ende, budget, farbe, oe_kurzbz, aufwandstyp_kurzbz) VALUES('.
		     $this->addslashes($this->projekt_kurzbz).', '.
		     $this->addslashes($this->nummer).', '.
		     $this->addslashes($this->titel).', '.
		     $this->addslashes($this->beschreibung).', '.
		     $this->addslashes($this->beginn).', '.
		     $this->addslashes($this->ende).', '.
		     $this->addslashes($this->budget).', '.
             $this->addslashes($this->farbe).', '.
		     $this->addslashes($this->oe_kurzbz).','.
		     $this->addslashes($this->aufwandstyp_kurzbz).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			$qry='UPDATE fue.tbl_projekt SET '.
				'projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).', '.
				'nummer='.$this->addslashes($this->nummer).', '.
				'titel='.$this->addslashes($this->titel).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'beginn='.$this->addslashes($this->beginn).', '.
				'ende='.$this->addslashes($this->ende).', '.
				'budget='.$this->addslashes($this->budget).', '.
                'farbe='.$this->addslashes($this->farbe).', '.
				'oe_kurzbz='.$this->addslashes($this->oe_kurzbz).', '.
				'aufwandstyp_kurzbz='.$this->addslashes($this->aufwandstyp_kurzbz).' '.
				'WHERE projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).';';
		}
		
		if($this->db_query($qry))
		{			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry;
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz
	 * @param $projekt_kurzbz Projekt das geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($projekt_kurzbz)
	{

		$qry = "DELETE FROM lehre.tbl_projek WHERE projekt_kurzbz='".addslashes($projekt_kurzbz)."'";
		
		if($this->db_query($qry))
		{
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}		
	}
	
	/**
	 * Liefert die Projekte zu denen ein Mitarbeiter zugeordnet ist
	 * @param $mitarbeiter_uid
	 */
	function getProjekteMitarbeiter($mitarbeiter_uid)
	{
		$qry = "SELECT 
					distinct tbl_projekt.* 
				FROM 
					fue.tbl_ressource 
					JOIN fue.tbl_projekt_ressource USING(ressource_id)
					JOIN fue.tbl_projekt USING(projekt_kurzbz) 
				WHERE (beginn<=now() or beginn is null) 
				AND (ende>=now() OR ende is null) 
				AND mitarbeiter_uid='".addslashes($mitarbeiter_uid)."'";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
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
			$this->erromsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	public function getProjektFromBestellung($bestellungID)
	{
		$qry ="select * from fue.tbl_projekt 
				join wawi.tbl_projekt_bestellung USING (projekt_kurzbz) 
				where bestellung_id= '".addslashes($bestellungID)."'"; 
		
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
				$this->budget= $row->budget;	
                $this->farbe= $row->farbe;	

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
	
}
?>
