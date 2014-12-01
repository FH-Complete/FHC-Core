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
 * Klasse projekttask
 * @create 2011-05-23
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projekttask extends basis_db
{
	public $new;       		// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $projekttask_id; // integer
	public $projektphase_id;// integer
	public $bezeichnung;	// string
	public $beschreibung;	// string
	public $aufwand;	    // string
	public $mantis_id;	    // integer
	public $scrumsprint_id; // bigint
	public $insertamum;	    // timestamp
	public $insertvon;	    // string
	public $updateamum;	    // timestamp
	public $updatevon;	    // string
	public $erledigt;		// boolean
	public $projekttask_fk;	// integer
	public $ende = null; 			// timestamp
	public $ressource_id = null; 	// integer


	/**
	 * Konstruktor
	 * @param $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null)
	 */
	public function __construct($projekttask_id=null)
	{
		parent::__construct();

		if($projekttask_id != null) 	
			$this->load($projekttask_id);
	}

	/**
	 * Laedt den Projekttask mit der ID $projekttask_id
	 * @param  $projekttask_id ID der zu ladenden Projektarbeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projekttask_id)
	{
		if(!is_numeric($projekttask_id))
		{
			$this->errormsg = 'Projekttask_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM fue.tbl_projekttask WHERE projekttask_id=".$this->db_add_param($projekttask_id, FHC_INTEGER);
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->projekttask_id = $row->projekttask_id;
				$this->projektphase_id = $row->projektphase_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->beschreibung = $row->beschreibung;
				$this->aufwand = $row->aufwand;
				$this->mantis_id = $row->mantis_id;
				$this->scrumsprint_id = $row->scrumsprint_id;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->erledigt = $this->db_parse_bool($row->erledigt);
				$this->projekttask_fk = $row->projekttask_fk;
				$this->ende = $row->ende; 
				$this->ressource_id = $row->ressource_id; 

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
	 * Laedt die Projekttasks für den Statusbericht -> 3 nächsten Tasks eines Projektes
	 * @param  $projektphase_id ID der Projektphase, wenn null greift $projekt_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekttasksForStatusbericht($projekt_kurzbz)
	{
			$qry ="SELECT task.* FROM fue.tbl_projekttask task
			JOIN fue.tbl_projektphase phase ON(phase.projektphase_id = task.projektphase_id)
			JOIN fue.tbl_projekt projekt USING(projekt_kurzbz) where projekt_kurzbz = ".$this->db_add_param($projekt_kurzbz)."
			and erledigt = false and task.ende >= CURRENT_DATE ORDER BY ende LIMIT 3;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projekttask();
				     	 	 	 	 	 	
				$obj->projekttask_id = $row->projekttask_id;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->aufwand = $row->aufwand;
				$obj->mantis_id = $row->mantis_id;
				$obj->scrumsprint_id = $row->scrumsprint_id;
				//$obj->beginn = $row->beginn;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->erledigt = $this->db_parse_bool($row->erledigt);
				$obj->projekttask_fk = $row->projekttask_fk;
				$obj->ende = $row->ende; 
				$obj->ressource_id = $row->ressource_id; 

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
	 * Laedt die Projektarbeit mit der ID $projekt_kurzbz
	 * @param  $projektphase_id ID der Projektphase, wenn null greift $projekt_kurzbz
	 * @param  $projekt_kurzbz ID des Projekts wenn keine Projektphase angegeben
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekttasks($projektphase_id,$projekt_kurzbz=null,$filterErledigt=null)
	{
		
		// lade tasks zu projekt
		if(!is_null($projekt_kurzbz))
		{
			$qry ="SELECT task.* FROM fue.tbl_projekttask task
			JOIN fue.tbl_projektphase phase ON(phase.projektphase_id = task.projektphase_id)
			JOIN fue.tbl_projekt projekt USING(projekt_kurzbz) where projekt_kurzbz = ".$this->db_add_param($projekt_kurzbz);
			
		}elseif (!is_null($projektphase_id))
			$qry = "SELECT * FROM fue.tbl_projekttask WHERE projektphase_id=".$this->db_add_param($projektphase_id, FHC_INTEGER);
		else
        	$qry='';

		if(!is_null($filterErledigt))
		{
			if($filterErledigt == 'offen')
				$qry .= " and erledigt = 'false'";
			else if ($filterErledigt == 'erledigt')
				$qry .= " and erledigt = 'true'";
		}	
		
		$qry .=';';
        
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new projekttask();
				     	 	 	 	 	 	
				$obj->projekttask_id = $row->projekttask_id;
				$obj->projektphase_id = $row->projektphase_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->beschreibung = $row->beschreibung;
				$obj->aufwand = $row->aufwand;
				$obj->mantis_id = $row->mantis_id;
				$obj->scrumsprint_id = $row->scrumsprint_id;
				//$obj->beginn = $row->beginn;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->erledigt = $this->db_parse_bool($row->erledigt);
				$obj->projekttask_fk = $row->projekttask_fk;
				$obj->ende = $row->ende; 
				$obj->ressource_id = $row->ressource_id; 

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

		//Gesamtlaenge pruefen}
		if (!is_numeric($this->projektphase_id))
		{
			$this->errormsg='Projektphase_id muss eine Zahl sein!';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 256 Zeichen sein!';
			return false;
		}
		
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * 
	 * Wechselt die Projektphase des übergebenen Task
	 * @param $projekttask_id
	 * @param $projektphase_id
	 */
	public function changePhase($projekttask_id, $projektphase_id)
	{
		if(!is_numeric($projekttask_id) || !is_numeric($projektphase_id))
		{
			$this->errormsg = "Ungültige ID."; 
			return false; 
		}
		
		$qry ="UPDATE fue.tbl_projekttask SET projektphase_id = ".$this->db_add_param($projektphase_id, FHC_INTEGER)." 
		WHERE projekttask_id = ".$this->db_add_param($projekttask_id, FHC_INTEGER)." OR projekttask_fk =".$this->db_add_param($projekttask_id, FHC_INTEGER); 
		
		if($this->db_query($qry))
			return true; 
		else
		{
			$this->errormsg = "Fehler bei der Abfrage aufgetreten."; 
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
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;
			
		if($new)
		{
			//Neuen Datensatz einfuegen

			$qry='BEGIN; INSERT INTO fue.tbl_projekttask (projektphase_id, bezeichnung, beschreibung, aufwand, mantis_id, scrumsprint_id, projekttask_fk, ende, ressource_id, erledigt, insertamum, 
				insertvon, updateamum, updatevon) VALUES('.
			     $this->db_add_param($this->projektphase_id).', '.
			     $this->db_add_param($this->bezeichnung).', '.
			     $this->db_add_param($this->beschreibung).', '.
			     $this->db_add_param($this->aufwand).', '.
			     $this->db_add_param($this->mantis_id).','.
			     $this->db_add_param($this->scrumsprint_id).','.
			     $this->db_add_param($this->projekttask_fk).','.
			     $this->db_add_param($this->ende).','.
			     $this->db_add_param($this->ressource_id).','.
			     $this->db_add_param($this->erledigt, FHC_BOOLEAN).',  
			     now(), '.
			     $this->db_add_param($this->insertvon).', 
			     now(), '.
			     $this->db_add_param($this->updatevon).');';
		}
		else
		{
			$qry='UPDATE fue.tbl_projekttask SET '.
				'projektphase_id='.$this->db_add_param($this->projektphase_id).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'beschreibung='.$this->db_add_param($this->beschreibung).', '.
				'aufwand='.$this->db_add_param($this->aufwand).', '.
				'mantis_id='.$this->db_add_param($this->mantis_id).', '.
				'scrumsprint_id='.$this->db_add_param($this->scrumsprint_id).', '.
				'projekttask_fk='.$this->db_add_param($this->projekttask_fk).', '.
				'ende='.$this->db_add_param($this->ende).', '.
				'ressource_id='.$this->db_add_param($this->ressource_id).', '.
				'erledigt='.$this->db_add_param($this->erledigt, FHC_BOOLEAN).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE projekttask_id='.$this->db_add_param($this->projekttask_id).';';
		}
		
		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence auslesen
				$qry = "SELECT currval('fue.seq_projekttask_projekttask_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->projekttask_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern der Daten'.$qry.$this->db_last_error();
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $projekttask_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($projekttask_id)
	{
		if(!is_numeric($projekttask_id))
		{
			$this->errormsg = 'Projekttask_id ist ungueltig';
			return true;
		}
		
		$qry = "DELETE FROM fue.tbl_projekttask WHERE projekttask_id=".$this->db_add_param($projekttask_id, FHC_INTEGER);
		
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
}
?>
