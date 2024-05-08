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
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *          Karl Burkhart <burkhart@technikum-wien.at>.
 */
/**
 * Klasse PreIncoming
 * @create 20-04-2011
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class preincoming extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  adresse Objekt

	//Tabellenspalten
	public $preincoming_id;		// integer
	public $person_id;			// integer
	public $mobilitaetsprogramm_code; // integer
	public $zweck_code;			// varchar(20)
	public $firma_id;			// integer
	public $anmerkung;			// text
	public $universitaet;		// varchar(256)
	public $aktiv;				// boolean
	public $bachelorthesis=false;		// boolean
	public $masterthesis=false;		// boolean
	public $von;				// date
	public $bis;				// date
	public $code; 				// varchar
	public $uebernommen=false;		// boolean
	public $updateamum;			// timestamp
	public $updatevon;			// string
	public $insertamum;      	// timestamp
	public $insertvon;      	// string
	public $zgv; 
	public $zgv_ort; 
	public $zgv_datum; 
	public $zgv_name; 
	public $zgvmaster; 
	public $zgvmaster_datum; 
	public $zgvmaster_ort; 
	public $zgvmaster_name; 
	public $program_name; 
	public $bachelor=false; 
	public $master=false; 
	public $jahre; 
	public $person_id_emergency; 
	public $person_id_coordinator_dep; 
	public $person_id_coordinator_int; 
	public $research_area;
	public $deutschkurs1;		// boolean
	public $deutschkurs2;		// boolean
	public $deutschkurs3;		// boolean

	/**
	 * Konstruktor
	 * @param $preincoming_id ID des Datensatzes der geladen werden soll (Default=null)
	 */
	public function __construct($preincoming_id=null)
	{
		parent::__construct();
		
		if(!is_null($preincoming_id))
			$this->load($preincoming_id);
	}

	/**
	 * Laedt den Preincoming Datensatz mit der uebergebenen ID
	 * @param  $preincoming_id ID des zu ladenden Preincoming
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($preincoming_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preincoming_id) || $preincoming_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_preincoming WHERE preincoming_id=".$this->db_add_param($preincoming_id, FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->preincoming_id = $row->preincoming_id;
			$this->person_id = $row->person_id;
			$this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
			$this->zweck_code = $row->zweck_code;
			$this->firma_id = $row->firma_id;
			$this->anmerkung = $row->anmerkung;
			$this->universitaet = $row->universitaet;
			$this->aktiv = $this->db_parse_bool($row->aktiv);
			$this->bachelorthesis = $this->db_parse_bool($row->bachelorthesis);
			$this->masterthesis = $this->db_parse_bool($row->masterthesis);
			$this->von = $row->von;
			$this->bis = $row->bis;
			$this->code = $row->code; 
			$this->uebernommen = $this->db_parse_bool($row->uebernommen);
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			
			$this->zgv = $row->zgv; 
			$this->zgv_ort = $row->zgv_ort; 
			$this->zgv_datum = $row->zgv_datum; 
			$this->zgv_name = $row->zgv_name; 
			$this->zgvmaster = $row->zgvmaster; 
			$this->zgvmaster_name = $row->zgvmaster_name; 
			$this->zgvmaster_datum = $row->zgvmaster_datum; 
			$this->zgvmaster_ort = $row->zgvmaster_ort; 
			$this->program_name = $row->program_name; 
			$this->bachelor = $this->db_parse_bool($row->bachelor);
			$this->master = $this->db_parse_bool($row->master); 
			$this->jahre = $row->jahre; 
			$this->person_id_emergency = $row->person_id_emergency; 
			$this->person_id_coordinator_dep = $row->person_id_coordinator_dep; 
			$this->person_id_coordinator_int = $row->person_id_coordinator_int; 
			$this->deutschkurs1 = $this->db_parse_bool($row->deutschkurs1);
			$this->deutschkurs2 = $this->db_parse_bool($row->deutschkurs2);
			$this->deutschkurs3 = $this->db_parse_bool($row->deutschkurs3);
			$this->research_area = $row->research_area; 
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt die Preincoming anhand der PersonID
	 * 
	 * @param $person_id
	 */
	public function loadFromPerson($person_id)
	{
		$qry = "SELECT * FROM public.tbl_preincoming WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER);
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new preincoming();
				
				$obj->preincoming_id = $row->preincoming_id;
				$obj->person_id = $row->person_id;
				$obj->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$obj->zweck_code = $row->zweck_code;
				$obj->firma_id = $row->firma_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->universitaet = $row->universitaet;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->bachelorthesis = $this->db_parse_bool($row->bachelorthesis);
				$obj->masterthesis = $this->db_parse_bool($row->masterthesis);
				$obj->von = $row->von;
				$obj->bis = $row->bis;
				$obj->code = $row->code; 
				$obj->uebernommen = $this->db_parse_bool($row->uebernommen);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$obj->zgv = $row->zgv; 
				$obj->zgv_ort = $row->zgv_ort; 
				$obj->zgv_datum = $row->zgv_datum; 
				$obj->zgv_name = $row->zgv_name; 
				$obj->zgvmaster = $row->zgvmaster;
				$obj->zgvmaster_name = $row->zgvmaster_name;
				$obj->zgvmaster_datum = $row->zgvmaster_datum; 
				$obj->zgvmaster_ort = $row->zgvmaster_ort; 
				$obj->program_name = $row->program_name; 
				$obj->bachelor = $this->db_parse_bool($row->bachelor); 
				$obj->master = $this->db_parse_bool($row->master); 
				$obj->jahre = $row->jahre; 
				$obj->person_id_emergency = $row->person_id_emergency; 
				$obj->person_id_coordinator_dep = $row->person_id_coordinator_dep; 
				$obj->person_id_coordinator_int = $row->person_id_coordinator_int; 
				$obj->deutschkurs1 = $this->db_parse_bool($row->deutschkurs1);
				$obj->deutschkurs2 = $this->db_parse_bool($row->deutschkurs2);
				$obj->deutschkurs3 = $this->db_parse_bool($row->deutschkurs3);
				$obj->research_area = $row->research_area; 
			
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
		if(mb_strlen($this->universitaet)>256)
		{
			$this->errormsg='Universitaet darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(!is_numeric($this->person_id))
		{
			$this->errormsg='PersonID muss eine gueltige Zahl sein';
			return false;
		}
		if($this->firma_id!='' && !is_numeric($this->firma_id))
		{
			$this->errormsg='Firma ist ungueltig';
			return false;
		}
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_preincoming (person_id, mobilitaetsprogramm_code, zweck_code, 
					firma_id, anmerkung, universitaet, aktiv, bachelorthesis, masterthesis, 
					von, bis, code, uebernommen, insertamum, insertvon, updateamum, updatevon,
					zgv, zgv_ort, zgv_datum, zgv_name, zgvmaster, zgvmaster_name, zgvmaster_datum, zgvmaster_ort, program_name,
					bachelor, master, jahre, person_id_emergency, person_id_coordinator_dep, person_id_coordinator_int,
					deutschkurs1, deutschkurs2, deutschkurs3, research_area)
				  VALUES('.
			      $this->db_add_param($this->person_id).', '.
			      $this->db_add_param($this->mobilitaetsprogramm_code).', '.
			      $this->db_add_param($this->zweck_code).', '.
			      $this->db_add_param($this->firma_id).', '.
			      $this->db_add_param($this->anmerkung).', '.
			      $this->db_add_param($this->universitaet).', '.
			      $this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->bachelorthesis, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->masterthesis, FHC_BOOLEAN).', '.
			      $this->db_add_param($this->von).', '.
			      $this->db_add_param($this->bis).', '.
			      $this->db_add_param($this->code).', '.
			      $this->db_add_param($this->uebernommen, FHC_BOOLEAN).', now(), '.
			      $this->db_add_param($this->insertvon).', now(), '.
			      $this->db_add_param($this->updatevon).', '.  
			      $this->db_add_param($this->zgv).', '. 
				  $this->db_add_param($this->zgv_ort).', '.
				  $this->db_add_param($this->zgv_datum).', '. 
				  $this->db_add_param($this->zgv_name).', '. 
				  $this->db_add_param($this->zgvmaster).', '.
				  $this->db_add_param($this->zgvmaster_name).', '.
				  $this->db_add_param($this->zgvmaster_datum).', '. 
				  $this->db_add_param($this->zgvmaster_ort).', '.
				  $this->db_add_param($this->program_name).', '.
				  $this->db_add_param($this->bachelor, FHC_BOOLEAN).', '.
				  $this->db_add_param($this->master, FHC_BOOLEAN).', '.
				  $this->db_add_param($this->jahre).', '.
				  $this->db_add_param($this->person_id_emergency).', '.
				  $this->db_add_param($this->person_id_coordinator_dep).', '.
				  $this->db_add_param($this->person_id_coordinator_int).', '.
				  $this->db_add_param($this->deutschkurs1, FHC_BOOLEAN).', '.
				  $this->db_add_param($this->deutschkurs2, FHC_BOOLEAN).', '.
				  $this->db_add_param($this->deutschkurs3, FHC_BOOLEAN).', '.				  
				  $this->db_add_param($this->research_area).' );';  		      
		}
		else
		{
			//Pruefen ob adresse_id eine gueltige Zahl ist
			if(!is_numeric($this->preincoming_id))
			{
				$this->errormsg = 'preincoming_id muss eine gültige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_preincoming SET'.
				' person_id='.$this->db_add_param($this->person_id).', '.
				' mobilitaetsprogramm_code='.$this->db_add_param($this->mobilitaetsprogramm_code).', '.
				' zweck_code='.$this->db_add_param($this->zweck_code).', '.
				' firma_id='.$this->db_add_param($this->firma_id).', '.
		      	' anmerkung='.$this->db_add_param($this->anmerkung).', '.
		      	' universitaet='.$this->db_add_param($this->universitaet).', '.
		      	' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				' bachelorthesis='.$this->db_add_param($this->bachelorthesis, FHC_BOOLEAN).', '.
				' masterthesis='.$this->db_add_param($this->masterthesis, FHC_BOOLEAN).', '.
		      	' von='.$this->db_add_param($this->von).', '.
		      	' bis='.$this->db_add_param($this->bis).','.
				' code='.$this->db_add_param($this->code).','.
				' uebernommen='.$this->db_add_param($this->uebernommen, FHC_BOOLEAN).', '.
		      	' updateamum=now() ,'.
		      	' updatevon='.$this->db_add_param($this->updatevon).', '.

				' zgv='.$this->db_add_param($this->zgv).', '. 
				' zgv_ort='.$this->db_add_param($this->zgv_ort).', '.
				' zgv_datum='.$this->db_add_param($this->zgv_datum).', '. 
				' zgv_name='.$this->db_add_param($this->zgv_name).', '. 
				' zgvmaster='.$this->db_add_param($this->zgvmaster).', '.
				' zgvmaster_name='.$this->db_add_param($this->zgvmaster_name).', '.			
				' zgvmaster_datum='.$this->db_add_param($this->zgvmaster_datum).', '. 
				' zgvmaster_ort='.$this->db_add_param($this->zgvmaster_ort).', '.
				' program_name='.$this->db_add_param($this->program_name).', '.
				' bachelor='.$this->db_add_param($this->bachelor, FHC_BOOLEAN).', '.
				' master='.$this->db_add_param($this->master, FHC_BOOLEAN).', '.
				' jahre='.$this->db_add_param($this->jahre).', '.
				' person_id_emergency='.$this->db_add_param($this->person_id_emergency).', '.
				' person_id_coordinator_dep='.$this->db_add_param($this->person_id_coordinator_dep).', '.
				' person_id_coordinator_int='.$this->db_add_param($this->person_id_coordinator_int).', '.
				' deutschkurs1='.$this->db_add_param($this->deutschkurs1, FHC_BOOLEAN).', '.
				' deutschkurs2='.$this->db_add_param($this->deutschkurs2, FHC_BOOLEAN).', '.
				' deutschkurs3='.$this->db_add_param($this->deutschkurs3, FHC_BOOLEAN).', '.			
				' research_area='.$this->db_add_param($this->research_area).' '.  	
		      	' WHERE preincoming_id='.$this->preincoming_id.';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle ID aus der Sequence holen
				$qry="SELECT currval('public.seq_preincoming_preincoming_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->preincoming_id = $row->id;
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
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return true;
	}

	/**
	 * Loescht den Preincoming Datenensatz mit der ID die uebergeben wird
	 * @param $preincoming ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($preincoming_id)
	{
		//Pruefen ob preincoming_id eine gueltige Zahl ist
		if(!is_numeric($preincoming_id) || $preincoming_id == '')
		{
			$this->errormsg = 'preincoming_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_preincoming WHERE preincoming_id=".$this->db_add_param($preincoming_id, FHC_INTEGER).";";

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
	 * Liefert ein Array mit den Lehrveranstaltungen denen der PreIncoming zugeordnet ist
	 * 
	 * @param $preincoming_id
	 * @return Array mit LehrveranstaltungIDs
	 */
	public function getLehrveranstaltungen($preincoming_id)
	{
		$lvs = array();
		
		if(!is_numeric($preincoming_id) || $preincoming_id=='')
		{
			$this->errormsg = 'ID muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_preincoming_lehrveranstaltung WHERE preincoming_id=".$this->db_add_param($preincoming_id, FHC_INTEGER).";";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$lvs[] = $row->lehrveranstaltung_id;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Lehrveranstaltungen';
			return false;
		}
		
		return $lvs;
	}
	
	/**
	 * Entfernt die Zuordnung eines Preincoming zu einer LV
	 * 
	 * @param $preincoming_id
	 * @param $lehrveranstaltung_id
	 */
	public function deleteLehrveranstaltung($preincoming_id, $lehrveranstaltung_id)
	{
		if(!is_numeric($preincoming_id) || $preincoming_id=='')
		{
			$this->errorsmg = 'Preincoming_id ist ungueltig';
			return false;
		}
		if(!is_numeric($lehrveranstaltung_id) || $lehrveranstaltung_id=='')
		{
			$this->errormsg = 'Lehrveranstaltung ist ungueltig';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_preincoming_lehrveranstaltung 
				WHERE preincoming_id=".$this->db_add_param($preincoming_id, FHC_INTEGER)." 
				AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der LV Zuordnung';
			return false;
		} 
	}
	
	/**
	 * Prueft ob die LV bereits zu dem Preincoming zugeordnet ist
	 * 
	 * @param $preincoming_id
	 * @param $lehrveranstaltung_id
	 * @return boolean true wenn bereits zugeordnet, sonst false 
	 */
	public function checkLehrveranstaltung($preincoming_id, $lehrveranstaltung_id)
	{
		$qry = "SELECT 1 FROM public.tbl_preincoming_lehrveranstaltung 
				WHERE 
					preincoming_id=".$this->db_add_param($preincoming_id, FHC_INTEGER)."
					AND lehrveranstaltung_id=".$this->db_add_param($lehrveranstaltung_id, FHC_INTEGER).";";

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Teilt eine Lehrveranstaltung zu einem Preincoming zu
	 * @param $preincoming_id
	 * @param $lehrveranstaltung_id
	 * @param $insertamum
	 * @param $insertvon
	 */
	public function addLehrveranstaltung($preincoming_id, $lehrveranstaltung_id, $insertamum, $insertvon = "")
	{
		$qry = "INSERT INTO public.tbl_preincoming_lehrveranstaltung(lehrveranstaltung_id, 
					preincoming_id, insertamum, insertvon) VALUES(".
				$this->db_add_param($lehrveranstaltung_id).','.
				$this->db_add_param($preincoming_id).','.
				$this->db_add_param($insertamum).','.
				$this->db_add_param($insertvon).');';
				
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuteilung';
			return false;
		}
	}
		
	/**
	 * Laedt die PreIncoming anhand von Suchkriterien
	 * 
	 * @param $filter
	 * @param $aktiv
	 * @param $von
	 * @param $bis
	 * @param $uebernommen
	 * @return boolean
	 */
	public function getPreincoming($filter, $aktiv=true, $von=null, $bis=null, $uebernommen=false)
	{
		$qry = "SELECT 
					titelpre, titelpost, vorname, nachname, tbl_preincoming.*
				FROM 
					public.tbl_person 
					JOIN public.tbl_preincoming USING(person_id)
				WHERE
					1=1";
		
		if($filter!='')
		{
			$qry.=" AND (lower(nachname) like lower('%".$this->db_escape($filter)."%') 
						OR lower(vorname) like lower('%".$this->db_escape($filter)."%')
						OR lower(nachname || ' ' || vorname) like lower('%".$this->db_escape($filter)."%')
						OR lower(vorname || ' ' || nachname) like lower('%".$this->db_escape($filter)."%'))";
		}
		
		if(!is_null($aktiv))
			$qry.=" AND tbl_preincoming.aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);
		if(!is_null($uebernommen))
			$qry.=" AND tbl_preincoming.uebernommen=".$this->db_add_param($uebernommen, FHC_BOOLEAN);
		if($von!='')
			$qry.=" AND tbl_preincoming.von>=".$this->db_add_param($von);
		if($bis!='')
			$qry.=" AND tbl_preincoming.bis<=".$this->db_add_param($bis);
			
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new preincoming();
				
				$obj->preincoming_id = $row->preincoming_id;
				$obj->person_id = $row->person_id;
				$obj->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code;
				$obj->zweck_code = $row->zweck_code;
				$obj->firma_id = $row->firma_id;
				$obj->anmerkung = $row->anmerkung;
				$obj->universitaet = $row->universitaet;
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->bachelorthesis = $this->db_parse_bool($row->bachelorthesis);
				$obj->masterthesis = $this->db_parse_bool($row->masterthesis);
				$obj->von = $row->von;
				$obj->bis = $row->bis;
				$obj->code = $row->code; 
				$obj->uebernommen = $this->db_parse_bool($row->uebernommen);
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				
				$obj->zgv = $row->zgv; 
				$obj->zgv_ort = $row->zgv_ort; 
				$obj->zgv_datum = $row->zgv_datum; 
				$obj->zgv_name = $row->zgv_name; 
				$obj->zgvmaster = $row->zgvmaster; 
				$obj->zgvmaster_name = $row->zgvmaster_name; 
				$obj->zgvmaster_datum = $row->zgvmaster_datum; 
				$obj->zgvmaster_ort = $row->zgvmaster_ort; 
				$obj->program_name = $row->program_name; 
				$obj->bachelor = $this->db_parse_bool($row->bachelor);
				$obj->master = $this->db_parse_bool($row->master);
				$obj->jahre = $row->jahre; 
				$obj->person_id_emergency = $row->person_id_emergency; 
				$obj->person_id_coordinator_dep = $row->person_id_coordinator_dep; 
				$obj->person_id_coordinator_int = $row->person_id_coordinator_int;
				 
				$obj->deutschkurs1 = $this->db_parse_bool($row->deutschkurs1);
				$obj->deutschkurs2 = $this->db_parse_bool($row->deutschkurs2);
				$obj->deutschkurs3 = $this->db_parse_bool($row->deutschkurs3);
				$obj->research_area = $row->research_area;
				
				$obj->vorname = $row->vorname;
				$obj->nachname = $row->nachname;
				$obj->titelpre = $row->titelpre;
				$obj->titelpost = $row->titelpost;
				
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
}
?>
