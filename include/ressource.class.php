<?php
/* Copyright (C) 2011 Technikum-Wien
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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>.
 */
 
require_once(dirname(__FILE__).'/basis_db.class.php');

class ressource extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $ressource_id;    	//integer
	public $bezeichnung;	    //string
	public $beschreibung;	    //string
	public $mitarbeiter_uid;	//string
	public $student_uid;	    //string
	public $betriebsmittel_id;	//integer 	
	public $firma_id;		    //integer 	
	public $insertamum;	   	 	//timestamp
	public $insertvon;	    	//string
	public $updateamum;	    	//timestamp
	public $updatevon;	    	//string
	
	// zwischentabelle projekt_ressource
	public $projekt_ressource_id;
	public $projektphase_id;
	public $projekt_kurzbz;
	public $funktion_kurzbz;


	/**
	 * Konstruktor
	 * @param $ressource_id ID der Ressource, die geladen werden soll (Default=null)
	 */
	public function __construct($ressource_id=null)
	{
		parent::__construct();

		if($ressource_id != null) 	
			$this->load($ressource_id);
	}

	/**
	 * Laedt die Ressource mit der ID $ressource_id
	 * @param  $ressource_id ID der zu ladenden Ressource
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($ressource_id)
	{
		if(!is_numeric($ressource_id))
		{
			$this->errormsg = 'Ressource_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_ressource WHERE ressource_id='".addslashes($ressource_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->ressource_id = $row->ressource_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->student_uid = $row->student_uid;
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->firma_id = $row->firma_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
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
	 * Laedt alle Ressourcen
	 * @param $projekt_kurzbz, wenn null -> werden alle ressourcen geladen 
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAllRessourcen()
	{
		$qry = "SELECT * FROM fue.tbl_ressource order by ressource_id";
			
		$this->result=array();
			
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ressource();
				
				$obj->ressource_id = $row->ressource_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->student_uid = $row->student_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Lädt alle Ressourcen zu einem Projekt
	 * @param $project_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjectRessourcen($project_kurzbz)
	{
		$qry = "SELECT ressource.* FROM fue.tbl_ressource as ressource
		JOIN fue.tbl_projekt_ressource project ON(project.ressource_id = ressource.ressource_id) 
		WHERE project.projekt_kurzbz ='".addslashes($project_kurzbz)."';";
		
		$this->result=array();
			
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ressource();
				
				$obj->ressource_id = $row->ressource_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->student_uid = $row->student_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * 
	 * Lädt alle Ressourcen zu einer Phase
	 * @param $project_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getPhaseRessourcen($projektphase_id)
	{
		$qry = "SELECT ressource.* FROM fue.tbl_ressource as ressource
		JOIN fue.tbl_projekt_ressource project ON(project.ressource_id = ressource.ressource_id) 
		WHERE project.projektphase_id ='".addslashes($projektphase_id)."';";
		
		$this->result=array();
			
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new ressource();
				
				$obj->ressource_id = $row->ressource_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->student_uid = $row->student_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$this->result[] = $obj;
			}
			//var_dump($this->result);
			return true;
		}
		else 
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveProjektRessource($new=null)
	{		
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN; INSERT INTO fue.tbl_projekt_ressource (projektphase_id, projekt_kurzbz, 
				ressource_id, funktion_kurzbz, beschreibung) VALUES ('.
			     $this->addslashes($this->projektphase_id).', '.
				 $this->addslashes($this->projekt_kurzbz).', '.
			     $this->addslashes($this->ressource_id).', '.
			     $this->addslashes($this->funktion_kurzbz).', '.
			     $this->addslashes($this->beschreibung).'); ';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry='UPDATE fue.tbl_projekt_ressource SET '.
				'projektphase_id='.$this->addslashes($this->projektphase_id).', '.
				'projekt_kurzbz='.$this->addslashes($this->projekt_kurzbz).', '.
				'ressource_id='.$this->addslashes($this->ressource_id).', '.
				'funktion_kurzbz='.$this->addslashes($this->funktion_kurzbz).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).' '.
				'WHERE projekt_ressource_id='.$this->addslashes($this->projekt_ressource_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.seq_projekt_ressource_projekt_ressource_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->projekt_ressource_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
					
			return true;
		}
		else
		{
			$this->errormsg = $qry;
			return false;
		}
	}	

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{		
		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN; INSERT INTO fue.tbl_ressource (bezeichnung, beschreibung, 
				mitarbeiter_uid, student_uid, betriebsmittel_id, firma_id, insertvon, insertamum, updatevon, updateamum) VALUES ('.
			     $this->addslashes($this->bezeichnung).', '.
				 $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->mitarbeiter_uid).', '.
			     $this->addslashes($this->student_uid).', '.
			     $this->addslashes($this->betriebsmittel_id).', '.
			     $this->addslashes($this->firma_id).', '.
			     $this->addslashes($this->insertvon).', now(), '.
			     $this->addslashes($this->updatevon).', now()); ';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry='UPDATE fue.tbl_ressource SET '.
				'bezeichnung='.$this->addslashes($this->bezeichnung).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'mitarbeiter_uid='.$this->addslashes($this->mitarbeiter_uid).', '.
				'student_uid='.$this->addslashes($this->student_uid).', '.
				'betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).', '.
				'firma_id='.$this->addslashes($this->firma_id).', '.
				'updateamum= now(), '.
				'updatevon='.$this->addslashes($this->updatevon).' '.
				'WHERE ressource_id='.$this->addslashes($this->ressource_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.seq_ressource_ressource_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->ressource_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else 
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK;');
						return false;
					}
				}
				else 
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK;');
					return false;
				}
			}
					
			return true;
		}
		else
		{
			$this->errormsg = $qry;
			return false;
		}
	}
	
	
	/**
	 * Laedt die Ressource mit der ID $ressource_id
	 * @param  $ressource_id ID der zu ladenden Ressource
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadProjektRessource($projekt_ressource_id)
	{
		if(!is_numeric($projekt_ressource_id))
		{
			$this->errormsg = 'Ressource_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_projekt_ressource WHERE projekt_ressource_id='".addslashes($projekt_ressource_id)."'";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekt_ressource_id = $row->projekt_ressource_id;
				$this->projektphase_id = $row->projektphase_id;
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->ressource_id = $row->ressource_id;
				$this->funktion_kurzbz = $row->funktion_kurzbz;
				$this->beschreibung = $row->beschreibung;

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
	 * Liefert die Ressourcen aller Projekte die zu einem bestimmten Datum aktiv sind
	 * 
	 * @param $datum
	 */
	public function getProjektRessourceDatum($datum)
	{
		$qry = "
		SELECT 
			distinct
			tbl_projekt_ressource.*, tbl_projekt.beginn as start, tbl_projekt.ende, 
			tbl_ressource.student_uid, tbl_ressource.mitarbeiter_uid, tbl_ressource.betriebsmittel_id, tbl_ressource.firma_id, 
			tbl_ressource.bezeichnung, tbl_ressource.beschreibung
		FROM 
			fue.tbl_ressource
			LEFT JOIN fue.tbl_projekt_ressource USING(ressource_id)
			LEFT JOIN fue.tbl_projekt USING(projekt_kurzbz)
		WHERE
			(tbl_projekt.beginn<='".addslashes($datum)."' OR tbl_projekt.beginn is null) AND 
			(tbl_projekt.ende>='".addslashes($datum)."' OR tbl_projekt.ende is null)  ";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ressource();
				$obj->projekt_ressource_id = $row->projekt_ressource_id;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
								
				$obj->ressource_id = $row->ressource_id;
				$obj->student_uid = $row->student_uid;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
		}		
	}
	
	/**
	 * Liefert die Ressourcen aller Projektphasen die zu einem bestimmten Datum aktiv sind
	 * 
	 * @param $datum
	 */
	public function getProjektphaseRessourceDatum($datum)
	{
		$qry = "
		SELECT 
			distinct
			tbl_projekt_ressource.*, tbl_projektphase.start, tbl_projektphase.ende, 
			tbl_ressource.student_uid, tbl_ressource.mitarbeiter_uid, tbl_ressource.betriebsmittel_id, tbl_ressource.firma_id, 
			tbl_ressource.bezeichnung, tbl_ressource.beschreibung
		FROM 
			fue.tbl_ressource
			LEFT JOIN fue.tbl_projekt_ressource USING(ressource_id)
			LEFT JOIN fue.tbl_projektphase USING(projektphase_id)
		WHERE
			(tbl_projektphase.start<='".addslashes($datum)."' OR tbl_projektphase.start is null) AND 
			(tbl_projektphase.ende>='".addslashes($datum)."' OR tbl_projektphase.ende is null)  
		ORDER BY tbl_ressource.bezeichnung";
		
		//echo $qry;
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new ressource();
				$obj->projekt_ressource_id = $row->projekt_ressource_id;
				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->start = $row->start;
				$obj->ende = $row->ende;
								
				$obj->ressource_id = $row->ressource_id;
				$obj->student_uid = $row->student_uid;
				$obj->mitarbeiter_uid = $row->mitarbeiter_uid;
				$obj->betriebsmittel_id = $row->betriebsmittel_id;
				$obj->firma_id = $row->firma_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				
				$this->result[] = $obj;
			}
		}
		else
		{
			$this->errorsmg = 'Fehler beim Laden';
			return false;
		}
	}
    
    /**
     * Löscht eine Ressource zu Projekt Zuordnung
     * @param type $ressource_id
     * @param type $projekt_kurzbz
     * @return boolean 
     */
    public function deleteFromProjekt($ressource_id, $projekt_kurzbz)
    {
        if($ressource_id == '' || !is_numeric($ressource_id))
        {
            $this->errormsg = 'Ressource Id ist keine gültige Zahl'; 
            return false; 
        }
        
        $qry="DELETE FROM fue.tbl_projekt_ressource WHERE ressource_id =".$this->db_add_param($ressource_id, FHC_INTEGER, false)." 
                AND projekt_kurzbz=".$this->db_add_param($projekt_kurzbz, FHC_STRING, false).';'; 
        
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
     * Löscht eine Ressource zu Phase Zuordnung
     * @param type $ressource_id
     * @param type $projekt_kurzbz
     * @return boolean 
     */
    public function deleteFromPhase($ressource_id, $projektphase_id)
    {
        if($ressource_id == '' || !is_numeric($ressource_id))
        {
            $this->errormsg = 'Ressource Id ist keine gültige Zahl'; 
            return false; 
        }
        
        if($projektphase_id == '' || !is_numeric($projektphase_id))
        {
            $this->errormsg = 'Ressource Id ist keine gültige Zahl'; 
            return false; 
        }        
        
        $qry="DELETE FROM fue.tbl_projekt_ressource WHERE ressource_id =".$this->db_add_param($ressource_id, FHC_INTEGER, false)." 
                AND projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER, false).';'; 
        
        if($this->db_query($qry))
            return true; 
        else
        {
            $this->errormsg = 'Fehler beim Löschen der Daten'; 
            return false; 
        }
    }
}
?>
