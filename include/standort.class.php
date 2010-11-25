<?php
/* Copyright (C) 2006 Technikum-Wien
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and.
 *          Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Klasse standort
 * @create 02-03-2010
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class standort extends basis_db
{
	public $new;				//  boolean
	public $result = array();	//  standort Objekt

	//Tabellenspalten
	public $standort_id;		//  integer
	public $adresse_id; 		//  integer
	public $kurzbz;				//  string
	public $bezeichnung;		//  string
	public $updateamum;			//  timestamp
	public $updatevon;			//  string
	public $insertamum;      	//  timestamp
	public $insertvon;      	//  string
	public $ext_id;				//  integer
	public $firma_id;			//  integer

	public $personfunktionstandort_id;	//  integer
	public $person_id;			//  integer
	public $funktion_kurzbz;	//  string	
	public $position;			//  string	

	public $person_anrede;		//  string	
	public $titelpost;			//  string	
	public $titelpre;			//  string	
	public $nachname;			//  string	
	public $vorname;			//  string	
	public $vornamen;			//  string	

	
	public $funktion_beschreibung;	//  string	
	public $funktion_aktiv;			//  boolean	
	public $funktion_fachbereich;	//  string	
	public $funktion_semester;		//  string	
	public $anrede;				//  string	


	/**
	 * Konstruktor
	 * @param $adress_id ID der standort die geladen werden soll (Default=null)
	 */
	public function __construct($standort_id=null)
	{
		parent::__construct();
		
		if(!is_null($standort_id))
			$this->load($standort_id);
	}

	/**
	 * Laedt die standort mit der ID $standort_id
	 * @param  $adress_id ID der zu ladenden standort
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($standort_id)
	{
		$this->result=array();
		$this->errormsg = '';
		
		//Pruefen ob adress_id eine gueltige Zahl ist
		if(!is_numeric($standort_id) || $standort_id == '')
		{
			$this->errormsg = 'standort_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_standort WHERE standort_id='".addslashes($standort_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
		if($row = $this->db_fetch_object())
		{
			$this->standort_id		= $row->standort_id;
			$this->adresse_id		= $row->adresse_id;
			$this->kurzbz			= $row->kurzbz;
			$this->bezeichnung		= $row->bezeichnung;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
			$this->ext_id			= $row->ext_id;
			$this->firma_id			= $row->firma_id;
			$this->result[] = $row;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Laedt alle Standort zu der Adress ID  die uebergeben wird
	 * @param adress_id ID der Adresse zu der die standorte geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_adresse($adress_id)
	{
		$this->result=array();
		$this->errormsg = '';
	
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($adress_id) || $adress_id == '')
		{
			$this->errormsg = 'Adressen ID muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_standort WHERE adresse_id='".addslashes($adress_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$this->standort_id		= $row->standort_id;
			$this->adresse_id		= $row->adresse_id;
			$this->kurzbz			= $row->kurzbz;
			$this->bezeichnung		= $row->bezeichnung;
			$this->updateamum		= $row->updateamum;
			$this->updatevon		= $row->updatevon;
			$this->insertamum		= $row->insertamum;
			$this->insertvon		= $row->insertvon;
			$this->ext_id			= $row->ext_id;
			$this->firma_id			= $row->firma_id;
			$this->result[] 		= $row;
					

		}
		return true;
	}
	
	/**
	 * Laedt alle standorte zu der Firma die uebergeben wird
	 * @param $firma_id ID der Firma zu der die standorte geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_firma($firma_id)
	{
		$this->result=array();
		$this->errormsg = '';
			
		//Pruefen ob pers_id eine gueltige Zahl ist
		if(!is_numeric($firma_id) || $firma_id == '')
		{
			$this->errormsg = 'firma_id muss eine gültige Zahl sein';
			return false;
		}

		//Lesen der Daten aus der Datenbank
		$qry = "SELECT * FROM public.tbl_standort WHERE firma_id='".addslashes($firma_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$adr_obj = new standort();

			$adr_obj->standort_id		= $row->standort_id;
			$adr_obj->adresse_id		= $row->adresse_id;
			$adr_obj->kurzbz			= $row->kurzbz;
			$adr_obj->bezeichnung		= $row->bezeichnung;
			$adr_obj->updateamum		= $row->updateamum;
			$adr_obj->updatevon			= $row->updatevon;
			$adr_obj->insertamum		= $row->insertamum;
			$adr_obj->insertvon			= $row->insertvon;
			$adr_obj->ext_id			= $row->ext_id;
			$adr_obj->firma_id			= $row->firma_id;

			$this->result[] = $adr_obj;
		}
		return true;
	}


	
	
	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		if(!is_numeric($this->standort_id) && $this->standort_id!='')
		{
			$this->errormsg='Standort_id enthaelt ungueltige Zeichen';
			return false;
		}
		if(!is_numeric($this->adresse_id) && $this->adresse_id!='')
		{
			$this->errormsg='Adresse_id enthaelt ungueltige Zeichen';
			return false;
		}		
		if(!is_numeric($this->firma_id) && $this->firma_id!='')
		{
			$this->errormsg='Firma_id enthaelt ungueltige Zeichen';
			return false;
		}		

		if(mb_strlen($this->kurzbz)>16)
		{
			$this->errormsg = 'Kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>255)
		{
			$this->errormsg = 'bezeichnung darf nicht länger als 255 Zeichen sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $standort_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		$this->errormsg = '';

		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_standort (adresse_id,kurzbz,  bezeichnung, insertamum, insertvon
			    , updateamum, updatevon, ext_id, firma_id) VALUES('.
			      ($this->adresse_id!=null?$this->addslashes($this->adresse_id):'null').', '.				
			      $this->addslashes($this->kurzbz).', '.
			      $this->addslashes($this->bezeichnung).', now(), '.
			      $this->addslashes($this->insertvon).', now(), '.
			      $this->addslashes($this->updatevon).', '.
			      ($this->ext_id!=null?$this->addslashes($this->ext_id):'null').', '.
			      ($this->firma_id!=null?$this->addslashes($this->firma_id):'null').');'; 
		}
		else
		{
			//Pruefen ob standort_id eine gueltige Zahl ist
			if(!is_numeric($this->standort_id))
			{
				$this->errormsg = 'standort_id muss eine gültige Zahl sein: '.$this->standort_id."\n";
				return false;
			}
			$qry='UPDATE public.tbl_standort SET'.
				' adresse_id='.$this->addslashes($this->adresse_id).', '.
				' kurzbz='.$this->addslashes($this->kurzbz).', '.				
				' bezeichnung='.$this->addslashes($this->bezeichnung).', '.
		      	' firma_id='.$this->addslashes($this->firma_id).','.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->addslashes($this->updatevon).' '.
		      	'WHERE standort_id='.$this->standort_id.';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('public.tbl_standort_standort_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->standort_id = $row->id;
						$this->db_query('COMMIT');
						return true;
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
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Adress-Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $standort_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($standort_id)
	{
		//Pruefen ob standort_id eine gueltige Zahl ist
		if(!is_numeric($standort_id) || $standort_id == '')
		{
			$this->errormsg = 'standort_id muss eine gültige Zahl sein'."\n";
			return false;
		}
		//loeschen des Datensatzes
		$qry="DELETE FROM public.tbl_standort WHERE standort_id='".addslashes($standort_id)."';";
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
	 * Laedt alle Standort,Funktion zu der Adress ID  die uebergeben wird
	 * @param adress_id ID der Adresse zu der die standorte geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_personfunktionstandort($personfunktionstandort_id='',$firma_id='',$standort_id='',$adress_id='',$person_id='')
	{
		$this->result=array();
		$this->errormsg = '';
	
		//Pruefen ob xxx_ID  eine gueltige Zahl ist
		if($firma_id!='' && !is_numeric($firma_id))
		{
			$this->errormsg = 'Firma ID muss eine gültige Zahl sein';
			return false;
		}
		if($standort_id!='' && !is_numeric($standort_id))
		{
			$this->errormsg = 'Standort ID muss eine gültige Zahl sein';
			return false;
		}
		if($adress_id!='' && !is_numeric($adress_id))
		{
			$this->errormsg = 'Adressen ID muss eine gültige Zahl sein';
			return false;
		}
		if($person_id!='' && !is_numeric($person_id))
		{
			$this->errormsg = 'Person ID muss eine gültige Zahl sein';
			return false;
		}		

		//Pruefen ob pers_id eine gueltige Zahl ist
		if($personfunktionstandort_id!='' && !is_numeric($personfunktionstandort_id))
		{
			$this->errormsg = 'Personfunktionstandort ID muss eine gültige Zahl sein';
			return false;
		}
		
		//Lesen der Daten aus der Datenbank
		$qry = "SELECT tbl_standort.* 
				, personfunktionstandort_id,funktion_kurzbz,position,tbl_personfunktionstandort.anrede
				,tbl_person.person_id,tbl_person.anrede as person_anrede,tbl_person.titelpost,tbl_person.titelpre,tbl_person.nachname,tbl_person.vorname,tbl_person.vornamen				 
				FROM public.tbl_standort ,public.tbl_personfunktionstandort,public.tbl_person
				WHERE  tbl_personfunktionstandort.standort_id=tbl_standort.standort_id
				AND tbl_person.person_id=tbl_personfunktionstandort.person_id
			";
			
		if ($personfunktionstandort_id!='' && is_numeric($personfunktionstandort_id))
			$qry.="	AND personfunktionstandort_id='".addslashes($personfunktionstandort_id)."'";
		if ($firma_id!='' && is_numeric($firma_id))
			$qry.="	AND tbl_standort.firma_id='".addslashes($firma_id)."'";
		if ($standort_id!='' && is_numeric($standort_id))
			$qry.="	AND tbl_standort.standort_id='".addslashes($standort_id)."'";
		if ($adress_id!='' && is_numeric($adress_id))
			$qry.="	AND tbl_standort.adress_id='".addslashes($adress_id)."'";
		if ($person_id!='' && is_numeric($person_id))
			$qry.="	AND tbl_standort.person_id='".addslashes($person_id)."'";

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
			$adr_obj = new standort();

			$adr_obj->standort_id		= $row->standort_id;
			$adr_obj->adresse_id		= $row->adresse_id;
			$adr_obj->kurzbz			= $row->kurzbz;
			$adr_obj->bezeichnung		= $row->bezeichnung;
			$adr_obj->updateamum		= $row->updateamum;
			$adr_obj->updatevon			= $row->updatevon;
			$adr_obj->insertamum		= $row->insertamum;
			$adr_obj->insertvon			= $row->insertvon;
			$adr_obj->ext_id			= $row->ext_id;
			$adr_obj->firma_id			= $row->firma_id;

			$adr_obj->personfunktionstandort_id=$row->personfunktionstandort_id;
			$adr_obj->funktion_kurzbz=$row->funktion_kurzbz;
			$adr_obj->position=$row->position;
			$adr_obj->anrede=$row->anrede;


			
			$adr_obj->person_id=$row->person_id;
			$adr_obj->person_anrede=$row->person_anrede;
			$adr_obj->titelpost=$row->titelpost;
			$adr_obj->titelpre=$row->titelpre;
			$adr_obj->nachname=$row->nachname;
			$adr_obj->vorname=$row->vorname;
			$adr_obj->vornamen=$row->vornamen;				

			$this->result[] = $adr_obj;
		}
		return $this->result;
	}
	/**
	 * Speichert den aktuellen Personfunktionstandort Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $standort_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function savepersonfunktionstandort()
	{
		$this->errormsg = '';
			//Pruefen ob standort_id eine gueltige Zahl ist
			if($this->personfunktionstandort_id!='' && !is_numeric($this->personfunktionstandort_id))
			{
				$this->errormsg = 'personfunktionstandort_id muss eine gültige Zahl sein: '.$this->personfunktionstandort_id."\n";
				return false;
			}
			//Pruefen ob standort_id eine gueltige Zahl ist
			if(!is_numeric($this->standort_id))
			{
				$this->errormsg = 'standort_id muss eine gültige Zahl sein: '.$this->standort_id."\n";
				return false;
			}
			//Pruefen ob standort_id eine gueltige Zahl ist
			if(!is_numeric($this->person_id))
			{
				$this->errormsg = 'person_id muss eine gültige Zahl sein: '.$this->person_id."\n";
				return false;
			}
			//Pruefen ob standort_id eine gueltige Zahl ist
			if(empty($this->funktion_kurzbz))
			{
				$this->errormsg = 'funktion_kurzbz muss eingegeben werden: '.$this->funktion_kurzbz."\n";
				return false;
			}
			
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_personfunktionstandort (funktion_kurzbz,person_id,position,anrede,standort_id) 
				VALUES('.
			      ($this->funktion_kurzbz!=null?$this->addslashes($this->funktion_kurzbz):'null').', '.				
			      ($this->person_id!=null?$this->addslashes($this->person_id):'null').', '.				
			      $this->addslashes($this->position).', '.
			      $this->addslashes($this->anrede).', '.
			      ($this->standort_id!=null?$this->addslashes($this->standort_id):'null').');'; 
		}
		else
		{
			$qry='UPDATE public.tbl_personfunktionstandort SET'.
				' funktion_kurzbz='.($this->funktion_kurzbz!=null?$this->addslashes($this->funktion_kurzbz):'null').', '.
				' person_id='.($this->person_id!=null?$this->addslashes($this->person_id):'null').', '.				
				' position='.$this->addslashes($this->position).', '.
		      	' anrede='.$this->addslashes($this->anrede).','.
		      	' standort_id='.($this->standort_id!=null?$this->addslashes($this->standort_id):'null').' '.
		      	' WHERE personfunktionstandort_id='.$this->personfunktionstandort_id.';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('public.tbl_personfunktionstandort_personfunktionstandort_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->personfunktionstandort_id = $row->id;
						$this->db_query('COMMIT');
						return $this->personfunktionstandort_id;
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
			else 
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Adress-Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $standort_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deletepersonfunktionstandort($personfunktionstandort_id='',$standort_id='')
	{
		//Pruefen ob standort_id eine gueltige Zahl ist
		if(!is_numeric($personfunktionstandort_id) && $personfunktionstandort_id != '')
		{
			$this->errormsg = 'personfunktionstandort_id muss eine gültige Zahl sein'."\n";
			return false;
		}
		if(!is_numeric($standort_id) && $standort_id != '')
		{
			$this->errormsg = 'standort_id muss eine gültige Zahl sein'."\n";
			return false;
		}
		
		if($personfunktionstandort_id != '')
			$qry="DELETE FROM public.tbl_personfunktionstandort WHERE personfunktionstandort_id='".addslashes($personfunktionstandort_id)."';";
		else if($standort_id != '')
			$qry="DELETE FROM public.tbl_personfunktionstandort WHERE standort_id='".addslashes($standort_id)."';";
		else
		{
			$this->errormsg = 'personfunktionstandort_id oder standort_id muss eingegeben werden'."\n";
			return false;
		}	
		
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
	
	
}
?>