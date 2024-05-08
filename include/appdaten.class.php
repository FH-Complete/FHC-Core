<?php
/*
 * appdaten.class.php
 * 
 * Copyright 2013 fhcomplete.org
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 *
 * Authors: Martin Tatzber <tatzberm@technikum-wien.at
 *          Werner Masik <werner@gefi.at>
 */

require_once(dirname(__FILE__).'/basis_db.class.php');

class appdaten extends basis_db
{
	private $new = true;			// boolean
	public $result = array();		// Objekte
	
	//Tabellenspalten
	protected $appdaten_id;				// integer (PK)
	protected $uid;						// varchar(32) FK Benutzer
	protected $app;						// varchar(64)
	protected $appversion;				// varchar(20)
	protected $version;					// smallint
	protected $bezeichnung;				// varchar(512)
	protected $daten;					// text
	protected $freigabe = false;		// boolean
	protected $updateamum;				// timestamp
	protected $updatevon;				// varchar
	protected $insertamum;				// timestamp
	protected $insertvon;				// varchar
	
	/**
	 * Konstruktor
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	public function __set($name,$value)
	{
		$this->$name=$value;
	}

	public function __get($name)
	{
		return $this->$name;
	}
	

	
	/**
	 * Laden von Appdaten
	 * @param appdaten_id ID des Datensatzes, der geladen werden soll
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function load($appdaten_id)
	{
		if(!is_numeric($appdaten_id))
		{
			$this->errormsg = 'Appdaten_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM system.tbl_appdaten WHERE appdaten_id=".$this->db_add_param($appdaten_id, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->mapRow($this, $row);
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
	}

	/**
	 * Appdaten nach bezeichnung und version laden
	 * @param type $app
	 * @param type $bezeichnung
	 * @param type $version
	 * @return boolean  true wenn keine Fehler, sonst false
	 */
	public function getByBezeichnungVersion($app, $bezeichnung, $version)
	{		
		if(!is_numeric($version))
		{
			$this->errormsg = 'version muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM system.tbl_appdaten "
				. "WHERE "
				. "app=".$this->db_add_param($app, FHC_STRING, false).' '
				. "AND bezeichnung=".$this->db_add_param($bezeichnung, FHC_STRING, false).' '
				. "AND bezeichnung<>'setup' "	
				. "AND version=".$this->db_add_param($version, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->mapRow($this, $row);
				$this->new = false;
			}
			else 
			{
				$this->new = true;
			}
		}
		else
		{
			return false;
		}

		return true;
	}
	
	/**
	 * Setup Appdaten nach UID laden. Unterscheidet sich von den normalen
	 * Appdaten nur dadurch, dass die Bezeichnung auf 'setup' fixiert ist.
	 * @param type $app
	 * @param type $bezeichnung
	 * @param type $uid
	 * @return boolean
	 */
	public function getSetupByUid($app, $uid)
	{
		
		if($uid == null || $uid === "")
		{
			$this->errormsg = 'uid darf nicht null sein';
			return false;
		}

		$qry = "SELECT * FROM system.tbl_appdaten "
				. "WHERE "
				. "app=".$this->db_add_param($app, FHC_STRING, false).' '
				. "AND bezeichnung='setup' "				
				. "AND uid=".$this->db_add_param($uid, FHC_STRING, false);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->mapRow($this, $row);
				$this->new = false;
			}
			else 
			{
				$this->new = true;
				return 0;
			}
		}
		else
		{
			return false;
		}

		return true;
	}
	
	
	/**
	 * Laden aller Appdaten sortiert nach Bezeichnung und version
	 * @param string app name
	 * @return array mit appdaten
	 */
	public function getAllByApp($app)
	{
		$result = array();
		$qry = "SELECT * FROM system.tbl_appdaten ".
		       "WHERE app=".$this->db_add_param($app, FHC_STRING, false).' '.
				"AND bezeichnung<>'setup' ".
		       "ORDER BY bezeichnung,version";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$appData = new appdaten();
				$this->mapRow($appData, $row);		
				$result[] = $appData;		
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return $result;
		
	}

	/**
	 * Helper
	 * @param type $target
	 * @param type $row
	 */
	private function mapRow($target,$row) 
	{		
		$target->appdaten_id=$row->appdaten_id;
		$target->uid=$row->uid;
		$target->app=$row->app;
		$target->appversion=$row->appversion;
		$target->version=$row->version;
		$target->bezeichnung=$row->bezeichnung;
		$target->daten=$row->daten;
		$target->freigabe=$this->db_parse_bool($row->freigabe);
		$target->insertamum=$row->insertamum;
		$target->insertvon=$row->insertvon;
		$target->updateamum=$row->updateamum;
		$target->updatevon=$row->updatevon;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Zahlenfelder pruefen
		/* version wird beim speichern automatisch gesetzt
		if(!is_numeric($this->version) && $this->version!=='')
		{
			$this->errormsg='version enthaelt ungueltige Zeichen';
			return false;
		}*/

		//Gesamtlaenge pruefen
		if(mb_strlen($this->uid)>32)
		{
			$this->errormsg = 'UID darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->app)>64)
		{
			$this->errormsg = 'App darf nicht länger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->appversion)>20)
		{
			$this->errormsg = 'Appversion darf nicht laenger als 20 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>512)
		{
			$this->errormsg = 'Bezeichnung darf nicht laenger als 512 Zeichen sein';
			return false;
		}
		
		//Boleanfelder prüfen
		if(!is_bool($this->freigabe))
		{
			$this->errormsg='Freigabe ist ungueltig';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * @param neueVersion boolean default false; wenn gesetzt, dann
	 * wird Versionsnummer auf aktuelles Maximum+1 gesetzt. (für 'Save As'
	 * Funktion bzw. zum Anlegen komplett neuer Daten)
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($neueVersion=false)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new || $neueVersion)
		{
			
			$this->db_query('BEGIN');

			if ($neueVersion) {
				// höchste Versionsnummer holen
				$qry =  "SELECT max(version) as version ".
					"FROM system.tbl_appdaten ".
					"WHERE app=".$this->db_add_param($this->app, FHC_STRING, false).
					"AND bezeichnung=".$this->db_add_param($this->bezeichnung, FHC_STRING, false);
					
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object()) 
					{
						if ($row->version != null || !is_numeric($this->version)) 
						{
							$this->version = $row->version + 1;
						}
						else
						{
							$this->version = 1;
						}
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Version";
						return false;
					}
				}
			}
			
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO system.tbl_appdaten (uid, app, appversion, version,
				bezeichnung, daten, freigabe, insertamum, insertvon) VALUES ('.
			      $this->db_add_param($this->uid).', '.
			      $this->db_add_param($this->app).', '.
			      $this->db_add_param($this->appversion).', '.
			      $this->db_add_param($this->version, FHC_INTEGER).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->daten).', '.
			      $this->db_add_param($this->freigabe, FHC_BOOLEAN).', '.
			      'now(), '.
			      $this->db_add_param($this->insertvon).');';
		}
		else
		{
			//Pruefen ob appdaten_id eine gueltige Zahl ist
			if(!is_numeric($this->appdaten_id))
			{
				$this->errormsg = 'appdaten_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE system.tbl_appdaten SET'.
				' uid='.$this->db_add_param($this->uid).', '.
				' app='.$this->db_add_param($this->app).', '.
				' appversion='.$this->db_add_param($this->appversion).', '.
				' version='.$this->db_add_param($this->version, FHC_INTEGER).', '.
		      	' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
		      	' daten='.$this->db_add_param($this->daten).', '.
		      	' freigabe='.$this->db_add_param($this->freigabe, FHC_BOOLEAN).', '.
		      	' updateamum= now(), '.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	' WHERE appdaten_id='.$this->db_add_param($this->appdaten_id, FHC_INTEGER, false).';';
		}
        
		if($this->db_query($qry))
		{
			if($this->new || $neueVersion)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('system.seq_appdaten_appdaten_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->appdaten_id = $row->id;
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
		return $this->appdaten_id;
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $appdaten_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($appdaten_id)
	{
		//Pruefen ob appdaten_id eine gueltige Zahl ist
		if(!is_numeric($appdaten_id) || $appdaten_id === '')
		{
			$this->errormsg = 'appdaten_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM system.tbl_appdaten WHERE appdaten_id=".$this->db_add_param($appdaten_id, FHC_INTEGER, false).";";

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
	
	public function deleteByBezeichnungVersion($app, $bezeichnung, $version)
	{
		//Pruefen ob appdaten_id eine gueltige Zahl ist
		if(!is_numeric($version) || $version === '')
		{
			$this->errormsg = 'Version muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM system.tbl_appdaten "
			. "WHERE "
			. "app=".$this->db_add_param($app, FHC_STRING, false).' '
			. "AND bezeichnung=".$this->db_add_param($bezeichnung, FHC_STRING, false).' '
			. "AND version=".$this->db_add_param($version, FHC_INTEGER, false);

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

/*	public function cleanResult()
	{
		$data = array();

		if(count($this->result)>0)
		{
			foreach($this->result as $row)
			{
				$obj = new stdClass();
				$obj->appdaten_id = $row->appdaten_id;
				$data[]=$obj;
			}
		}
		else
		{
			$obj = new stdClass();
			$obj->appdaten_id = $this->appdaten_id;
			$data[]=$obj;
		}
		return $data;
	} */
}
?>
