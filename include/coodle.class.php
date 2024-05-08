<?php
/* Copyright (C) 2012 FH Technikum-Wien
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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 *          Karl Burkhart <burkhart@technikum-wien.at>
 */
/**
 * Klasse Coodle
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class coodle extends basis_db
{
	public $new;
	public $result = array();

	//Tabellenspalten
	public $coodle_id;				// integer
	public $ersteller_uid;			// varchar(32)
	public $coodle_status_kurzbz;	// varchar(32)
	public $titel; 					// varchar(64)
	public $beschreibung;			// text
	public $dauer;					// smallint
	public $endedatum;            	// date
	public $updateamum;				// timestamp
	public $updatevon;				// varchar(32)
	public $insertamum;    		  	// timestamp
	public $insertvon;     	 		// varchar(32)
	public $mailversand;			// boolean
	public $teilnehmer_anonym;		// boolean
	public $termine_anonym;			// boolean

    // tbl_coodle_ressource
    public $coodle_ressource_id;    // integer
    public $uid;                    // varchar(32)
    public $ort_kurzbz;             // varchar(16)
    public $email;                  // varchar(128)
    public $name;                   // varchar(256)
    public $zugangscode;            // varchar(64)

    // tbl_coodle_termin
    public $coodle_termin_id;       // integer
    public $datum;                  // date
    public $uhrzeit;                // date
    public $auswahl;                // boolean

    public $status_arr;

	/**
	 * Konstruktor
	 * @param $coodle_id ID die geladen werden soll (Default=null)
	 */
	public function __construct($coodle_id=null)
	{
		parent::__construct();

		if(!is_null($coodle_id))
			$this->load($coodle_id);
	}

	/**
	 * Laedt einen Eintrag mit der ID $coodle_id
	 * @param  $coodle_id ID des zu ladenden Eintrags
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($coodle_id)
	{
		//Pruefen ob coodle_id eine gueltige Zahl ist
		if(!is_numeric($coodle_id) || $coodle_id == '')
		{
			$this->errormsg = 'Coodle_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM campus.tbl_coodle WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->coodle_id = $row->coodle_id;
			$this->ersteller_uid = $row->ersteller_uid;
			$this->coodle_status_kurzbz = $row->coodle_status_kurzbz;
			$this->titel = $row->titel;
			$this->beschreibung = $row->beschreibung;
			$this->dauer = $row->dauer;
			$this->endedatum = $row->endedatum;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->mailversand = $this->db_parse_bool($row->mailversand);
			$this->teilnehmer_anonym = $this->db_parse_bool($row->teilnehmer_anonym);
			$this->termine_anonym = $this->db_parse_bool($row->termine_anonym);
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if(mb_strlen($this->coodle_status_kurzbz)>32)
		{
			$this->errormsg = 'Status darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->titel)>64)
		{
			$this->errormsg = 'titel darf nicht länger als 64 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $coodle_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_coodle(ersteller_uid, coodle_status_kurzbz, titel, beschreibung,
				dauer, endedatum, insertamum, insertvon, updateamum, updatevon, mailversand, teilnehmer_anonym, termine_anonym) VALUES('.
			      $this->db_add_param($this->ersteller_uid).', '.
			      $this->db_add_param($this->coodle_status_kurzbz).', '.
			      $this->db_add_param($this->titel).', '.
			      $this->db_add_param($this->beschreibung).', '.
			      $this->db_add_param($this->dauer).', '.
			      $this->db_add_param($this->endedatum).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).', '.
			      $this->db_add_param($this->mailversand, FHC_BOOLEAN, true).', '.
			      $this->db_add_param($this->teilnehmer_anonym, FHC_BOOLEAN, true).', '.
			      $this->db_add_param($this->termine_anonym, FHC_BOOLEAN, true).');';
		}
		else
		{
			//Pruefen ob coodle_id eine gueltige Zahl ist
			if(!is_numeric($this->coodle_id))
			{
				$this->errormsg = 'coolde_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE campus.tbl_coodle SET'.
				' ersteller_uid='.$this->db_add_param($this->ersteller_uid).', '.
				' coodle_status_kurzbz='.$this->db_add_param($this->coodle_status_kurzbz).', '.
				' titel='.$this->db_add_param($this->titel).', '.
				' beschreibung='.$this->db_add_param($this->beschreibung).', '.
				' dauer='.$this->db_add_param($this->dauer).', '.
		      	' endedatum='.$this->db_add_param($this->endedatum).', '.
		      	' updateamum='.$this->db_add_param($this->updateamum).', '.
		      	' updatevon='.$this->db_add_param($this->updatevon).', '.
				' mailversand='.$this->db_add_param($this->mailversand, FHC_BOOLEAN).', '.
				' teilnehmer_anonym='.$this->db_add_param($this->teilnehmer_anonym, FHC_BOOLEAN).', '.
				' termine_anonym='.$this->db_add_param($this->termine_anonym, FHC_BOOLEAN).' '.
		      	'WHERE coodle_id='.$this->db_add_param($this->coodle_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_coodle_coodle_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->coodle_id = $row->id;
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird => Status wird auf storniert gesetzt
	 * @param $coodle_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($coodle_id)
	{
		//Pruefen ob adresse_id eine gueltige Zahl ist
		if(!is_numeric($coodle_id) || $coodle_id == '')
		{
			$this->errormsg = 'Coodle_id muss eine gültige Zahl sein'."\n";
			return false;
		}

		//loeschen des Datensatzes
		$qry="UPDATE campus.tbl_coodle SET coodle_status_kurzbz = 'storniert'
            WHERE
            coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false).';';

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim stornieren aufgetreten';
			return false;
		}
	}

    /**
     * Liefert alle Ressourcen zurück, die einer Coodleumfrage zugeteilt sind
     * @param type $coodle_id
     * @return true wenn ok, false im Fehlerfall
     */
    public function getRessourcen($coodle_id)
    {
        if(!is_numeric($coodle_id) || $coodle_id == '')
        {
            $this->errormsg = 'Coodle_id muss eine gültige Zahl sein'."\n";
            return false;
        }

        $qry ="SELECT * FROM campus.tbl_coodle_ressource WHERE coodle_id =".$this->db_add_param($coodle_id, FHC_INTEGER, false);

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten!';
            return false;
        }

        while($row = $this->db_fetch_object())
        {
            $coodle_ressource = new coodle();

            $coodle_ressource->coodle_ressource_id = $row->coodle_ressource_id;
            $coodle_ressource->coodle_id = $row->coodle_id;
            $coodle_ressource->uid = $row->uid;
            $coodle_ressource->ort_kurzbz = $row->ort_kurzbz;
            $coodle_ressource->email = $row->email;
            $coodle_ressource->name = $row->name;
            $coodle_ressource->zugangscode = $row->zugangscode;
            $coodle_ressource->insertamum = $row->insertamum;
            $coodle_ressource->insertvon = $row->insertvon;
            $coodle_ressource->updateamum = $row->updateamum;
            $coodle_ressource->updatevon = $row->updatevon;

            $this->result[] = $coodle_ressource;
        }

        return true;
    }

    /**
     * Lädt eine Ressource
     * @param type $coodle_ressource_id
     * @return true wenn ok, false im Fehlerfall
     */
    public function getRessourceFromId($coodle_ressource_id)
    {
        if(!is_numeric($coodle_ressource_id) || $coodle_ressource_id=='')
        {
            $this->errormsg = 'Coodle_id muss eine gültige Zahl sein'."\n";
            return false;
        }

        $qry = "SELECT * FROM campus.tbl_coodle_ressource WHERE coodle_ressource_id =".$this->db_add_param($coodle_ressource_id, FHC_INTEGER, false);

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten'."\n";
            return false;
        }

        if($row = $this->db_fetch_object())
        {
            $this->coodle_ressource_id = $row->coodle_ressource_id;
            $this->coodle_id = $row->coodle_id;
            $this->uid = $row->uid;
            $this->ort_kurzbz = $row->ort_kurzbz;
            $this->email = $row->email;
            $this->name = $row->name;
            $this->zugangscode = $row->zugangscode;
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
     * Liefert alle Coodle Umfragen zurück wo Benutzer entweder Ersteller oder Ressource ist
     * und das Endedatum vor 20 Tagen oder in der Zukunft liegt
     * @param type $uid
     * @return true wenn ok, false im Fehlerfall
     */
    public function getCoodleFromUser($uid)
    {
        if($uid == '')
        {
            $this->errormsg = 'keine gültige uid';
            return false;
        }

        $qry = "SELECT distinct campus.tbl_coodle.*
                    FROM campus.tbl_coodle
                    LEFT JOIN campus.tbl_coodle_ressource USING(coodle_id)
                WHERE
					(uid =".$this->db_add_param($uid, FHC_STRING, false)."
                    OR ersteller_uid =".$this->db_add_param($uid, FHC_STRING, false).")
                    AND endedatum >= CURRENT_DATE - interval '90 days';";

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }

        while($row = $this->db_fetch_object())
        {
            $coodle = new coodle();
            $coodle->coodle_id = $row->coodle_id;
            $coodle->titel = $row->titel;
            $coodle->beschreibung = $row->beschreibung;
            $coodle->coodle_status_kurzbz = $row->coodle_status_kurzbz;
            $coodle->dauer = $row->dauer;
            $coodle->insertamum = $row->insertamum;
            $coodle->insertvon = $row->insertvon;
            $coodle->updateamum = $row->updateamum;
            $coodle->updatevon = $row->updatevon;
            $coodle->endedatum = $row->endedatum;
            $coodle->ersteller_uid = $row->ersteller_uid;
            $coodle->mailversand = $row->mailversand;
            $coodle->teilnehmer_anonym = $row->teilnehmer_anonym;
            $coodle->termine_anonym = $row->termine_anonym;

            $this->result[] = $coodle;
        }

        return true;
    }

	/**
	 * Prueft ob eine Ressource bereits zu einer Umfrage zugeteilt ist
	 *
	 * @param $coodle_id ID der CoodleUmfrage
	 * @param $uid UID des Benutzers
	 * @param $ort_kurzbz Ort
	 * @param $email EMail des externen Teilnehmers
	 * @return boolean true wenn vorhanden sonst false
	 */
	public function RessourceExists($coodle_id, $uid, $ort_kurzbz='', $email='')
	{
		$qry="SELECT coodle_ressource_id FROM campus.tbl_coodle_ressource WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false);
		if($uid!='')
			$qry.=' AND uid='.$this->db_add_param($uid, FHC_STRING, false);
		if($ort_kurzbz!='')
			$qry.=' AND ort_kurzbz='.$this->db_add_param($ort_kurzbz, FHC_STRING, false);
		if($email!='')
			$qry.=' AND email='.$this->db_add_param($email, FHC_STRING, false);

        $qry.=';';

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				if($row = $this->db_fetch_object($result))
					return $row->coodle_ressource_id;
			}
			else
				return false;
		}
	}

	/**
	 * Validiert die Ressourcedaten vor dem Speichern
	 */
	public function validateRessource()
	{
		return true;
	}

	/**
	 * Speichert die aktuelle Ressource in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $coodle_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveRessource($new = null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validateRessource())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_coodle_ressource(coodle_id, uid, ort_kurzbz,
				email, name, zugangscode, insertamum, insertvon, updateamum, updatevon) VALUES('.
			      $this->db_add_param($this->coodle_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->uid).', '.
			      $this->db_add_param($this->ort_kurzbz).', '.
			      $this->db_add_param($this->email).', '.
			      $this->db_add_param($this->name).', '.
			      $this->db_add_param($this->zugangscode).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).');';
		}
		else
		{
			$this->errormsg = 'Update not Implemented';
			return false;
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_coodle_ressource_coodle_ressource_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->coodle_ressource_id = $row->id;
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
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

    /**
	 * Speichert die Terminwahl der Ressource in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $coodle_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveRessourceTermin($new = null)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validateRessource())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO campus.tbl_coodle_ressource_termin(coodle_ressource_id, coodle_termin_id, insertamum, insertvon) VALUES('.
                  $this->db_add_param($this->coodle_ressource_id, FHC_INTEGER, false).', '.
			      $this->db_add_param($this->coodle_termin_id, FHC_INTEGER, false).',
                    CURRENT_TIMESTAMP, '.
			      $this->db_add_param($this->insertvon, FHC_STRING).');';
		}
		else
		{
			$this->errormsg = 'Update not Implemented';
			return false;
		}

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
        else
            return true;
	}

    /**
     * Lädt eine Ressource zur übergebenen $coodle_id und $uid oder $zugangscode
     * @param Integer $coodle_id
     * @param varchar $uid
     * @param varchar $zugangscode
     * @return boolean
     */
    public function getRessourceFromUser($coodle_id, $uid='', $zugangscode='')
    {

        $qry ="SELECT * FROM campus.tbl_coodle_ressource
            WHERE coodle_id =".$this->db_add_param($coodle_id, FHC_INTEGER, false);

        if($uid != '')
            $qry.= " AND uid = ".$this->db_add_param($uid, FHC_STRING, false);

        if ($zugangscode != '')
            $qry.= " AND zugangscode = ".$this->db_add_param($zugangscode, FHC_STRING, false);
        $qry.= ';';

        if(!$this->db_query($qry))
        {
          $this->errormsg = 'Fehler bei einer Datenbankabfrage';
            return false;
        }

        if ($row = $this->db_fetch_object())
        {
            $this->coodle_ressource_id  = $row->coodle_ressource_id;
            $this->coodle_id            = $row->coodle_id;
            $this->uid                  = $row->uid;
            $this->ort_kurzbz           = $row->ort_kurzbz;
            $this->email                = $row->email;
            $this->name                 = $row->name;
            $this->zugangscode          = $row->zugangscode;
            $this->insertamum           = $row->insertamum;
            $this->insertvon            = $row->insertvon;
            $this->updateamum           = $row->updateamum;
            $this->updatevon            = $row->updatevon;
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten";
            return false;
        }
        return true;
    }

    /**
     * Löscht alle Termine einer Ressource zu einer übergebenen Coodleumfrage
     * @param Integer $coodle_id
     * @param Integer $ressource_id
     * @return boolean
     */
    public function deleteRessourceTermin($coodle_id, $ressource_id)
    {
        if($ressource_id == '' || !is_numeric($ressource_id) || $coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = 'Ungültige ID übergeben';
            return false;
        }

        $qry="DELETE FROM campus.tbl_coodle_ressource_termin
            WHERE coodle_ressource_id =".$this->db_add_param($ressource_id, FHC_INTEGER)."
                AND coodle_termin_id IN
                    (SELECT coodle_termin_id FROM campus.tbl_coodle_termin
                        WHERE coodle_id =".$this->db_add_param($coodle_id, FHC_INTEGER).");";

        $this->errormsg = $qry;

        if($this->db_query($qry))
            return true;
        else
            return false;
    }

    /**
     * Überprüft ob die übergebene Ressource den übergebenen Termin ausgewählt hat
     * @param Integer $termin_id
     * @param Integer $ressource_id
     * @return boolean
     */
    public function checkTermin($termin_id, $ressource_id)
    {
        if($ressource_id == '' || !is_numeric($ressource_id) || $termin_id == '' || !is_numeric($termin_id))
        {
            $this->errormsg = 'Ungültige ID übergeben';
            return false;
        }

        $qry="SELECT * FROM campus.tbl_coodle_ressource_termin
            WHERE coodle_ressource_id =".$this->db_add_param($ressource_id, FHC_INTEGER)."
                AND coodle_termin_id=".$this->db_add_param($termin_id, FHC_INTEGER).';';

        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_row($result))
            {
                return true;
            }
            return false;
        }

        return false;
    }

	/**
     * Überprüft ob der übergebenen Termin schon von einer Ressource gewählt wurde
     * @param Integer $termin_id
     * @return boolean true, wenn schon ein Termin schon gewaehlt wurde, sonst false
     */
    public function checkTerminGewaehlt($termin_id)
    {
        if( $termin_id == '' || !is_numeric($termin_id))
        {
            $this->errormsg = 'Ungültige ID übergeben';
            return false;
        }

        $qry="SELECT * FROM campus.tbl_coodle_ressource_termin
            WHERE coodle_termin_id=".$this->db_add_param($termin_id, FHC_INTEGER).';';

        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_row($result))
            {
                return true;
            }
            return false;
        }

        return false;
    }

	/**
     * Zählt, wie oft ein Termin gewählt wurde
     * @param Integer $termin_id
     * @param Integer $ressource_id
     * @return boolean
     */
    public function countTermin($termin_id)
    {
        if($termin_id == '' || !is_numeric($termin_id))
        {
            $this->errormsg = 'Ungültige ID übergeben';
            return false;
        }

        $qry="SELECT count(*) AS anzahl FROM campus.tbl_coodle_ressource_termin
            WHERE coodle_termin_id=".$this->db_add_param($termin_id, FHC_INTEGER).';';

   		if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_object($result))
            {
                $this->anzahl = $row->anzahl;
            }
            else
            {
                return false;
            }
            return true;
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten";
            return false;
        }
    }

    /**
     *
     * Laedt alle Termine einer Umfrage zu denen eine Ressource zugesagt hat
     * @param $coodle_id ID der Umfrage
     * @param $ressource_id ID der Ressource
     */
    public function getRessourceTermin($coodle_id, $ressource_id)
    {
    	$qry = "SELECT
    				*
    			FROM
    				campus.tbl_coodle_ressource_termin
    				JOIN campus.tbl_coodle_termin USING(coodle_termin_id)
    			WHERE
    				coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER)."
    				AND coodle_ressource_id=".$this->db_add_param($ressource_id, FHC_INTEGER).";";

    	if($result = $this->db_query($qry))
    	{
    		while($row = $this->db_fetch_object($result))
    		{
    			$obj = new coodle();

    			$obj->coodle_id = $row->coodle_id;
    			$obj->coodle_termin_id = $row->coodle_termin_id;
    			$obj->datum = $row->datum;
    			$obj->uhrzeit = $row->uhrzeit;
    			$obj->auswahl = $this->db_parse_bool($row->auswahl);
    			$obj->coodle_ressource_id = $row->coodle_ressource_id;
    			$obj->coodle_termin_id = $row->coodle_termin_id;
    			$obj->insertamum = $row->insertamum;
    			$obj->insertvon = $row->insertvon;

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
     * Überprüfut ob der Benutzer entweder der Ersteller oder eine Ressource der Coodleumfrage ist
     * @param Integer $coodle_id
     * @param Integer $uid
     * @param varchar $zugangscode
     * @return boolean
     */
    public function checkBerechtigung($coodle_id, $uid='', $zugangscode='')
    {
        if($coodle_id =='' ||  !is_numeric($coodle_id))
        {
            $this->errormsg = 'Ungültige Id übergeben.';
            return false;
        }

        if($uid != '' && $zugangscode != '')
        {
            $this->errormsg = 'Uid oder Zuganscode übergeben';
            return false;
        }

        $qry ="SELECT 1 FROM campus.tbl_coodle_ressource WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false).' AND ';

        if($uid != '')
            $qry.= ' uid = '.$this->db_add_param($uid, FHC_STRING, false);

        if($zugangscode != '')
            $qry.= ' zugangscode ='.$this->db_add_param($zugangscode, FHC_STRING, false);

        $qry.="UNION SELECT 1 FROM campus.tbl_coodle WHERE ersteller_uid=".$this->db_add_param($uid).";";


        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_object($result))
                return true;
            else
                return false;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }
    }

	/**
	 * Entfernt eine Ressourcezuteilung von einer Umfrage
	 * @param $coodle_ressource_id ID der Ressourcezuteilung
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function deleteRessource($coodle_ressource_id)
	{
        if($coodle_ressource_id == '' || !is_numeric($coodle_ressource_id))
        {
            $this->errormsg = 'Ungültige ID übergeben';
            return false;
        }

		$qry = "DELETE FROM campus.tbl_coodle_ressource_termin WHERE coodle_ressource_id=".$this->db_add_param($coodle_ressource_id, FHC_INTEGER, false).";
				DELETE FROM campus.tbl_coodle_ressource WHERE coodle_ressource_id=".$this->db_add_param($coodle_ressource_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Termindaten vor dem Speichern
	 */
	public function validateTermin()
	{
		return true;
	}

	/**
	 * Speichert einen Termin
     * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $coodle_termin_id aktualisiert
	 * @param new
	 * @return boolean
	 */
	public function saveTermin($new)
	{
		if(is_null($new))
			$new = $this->new;

		//Variablen pruefen
		if(!$this->validateTermin())
			return false;

		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO campus.tbl_coodle_termin(coodle_id, datum, uhrzeit, auswahl) VALUES('.
			      $this->db_add_param($this->coodle_id, FHC_INTEGER, false).', '.
			      $this->db_add_param($this->datum, FHC_STRING, false).', '.
			      $this->db_add_param($this->uhrzeit, FHC_STRING, false).', false);';
		}
		else
		{
			$qry='UPDATE campus.tbl_coodle_termin SET'.
				' datum='.$this->db_add_param($this->datum, FHC_STRING, false).','.
				' uhrzeit='.$this->db_add_param($this->uhrzeit, FHC_STRING, false).', '.
                ' auswahl='.$this->db_add_param($this->auswahl, FHC_BOOLEAN, false).
				' WHERE coodle_termin_id='.$this->db_add_param($this->coodle_termin_id, FHC_INTEGER, false).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('campus.seq_coodle_termin_coodle_termin_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->coodle_termin_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->db_query('ROLLBACK;');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK;');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Terminvorschlaege zu einer Umfrage
	 * Das Datum 1900-01-01 wird an die letzte Stelle sortiert, da es fuer "Keine Auswahl" benoetigt wird
	 * @param $coodle_id
	 * @return boolean
	 */
	public function getTermine($coodle_id)
	{
		if($coodle_id == '' || !is_numeric($coodle_id))
		{
			$this->errormsg = "Ungültige Coodle_id";
			return false;
		}

		$qry = "(SELECT * FROM campus.tbl_coodle_termin
					WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
					AND datum != '1900-01-01'
					ORDER BY datum, uhrzeit)

				UNION ALL

				(SELECT * FROM campus.tbl_coodle_termin
					WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
					AND datum = '1900-01-01');";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new coodle();
				$obj->coodle_termin_id = $row->coodle_termin_id;
				$obj->coodle_id = $row->coodle_id;
				$obj->datum = $row->datum;
				$obj->uhrzeit = $row->uhrzeit;
				$obj->auswahl = $this->db_parse_bool($row->auswahl);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}


    /**
     * Gibt die Coodle Umfrage zurück in der sich die Ressource mit dem übergebenen Zugangscode befindet
     * @param string $zugangscode
     * @return boolean
     */
    public function getCoodleFromZugangscode($zugangscode)
    {
        $qry = "SELECT tbl_coodle.* from campus.tbl_coodle
            JOIN campus.tbl_coodle_ressource USING(coodle_id)
            WHERE zugangscode =".$this->db_add_param($zugangscode, FHC_STRING).';';

        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $coodle = new coodle();

                $coodle->coodle_id = $row->coodle_id;
                $coodle->ersteller_uid = $row->ersteller_uid;
                $coodle->titel = $row->titel;
                $coodle->beschreibung = $row->beschreibung;
                $coodle->coodle_status_kurzbz = $row->coodle_status_kurzbz;
                $coodle->dauer = $row->dauer;
                $coodle->endedatum = $row->endedatum;
                $coodle->insertamum = $row->insertamum;
                $coodle->insertvon = $row->insertvon;
                $coodle->updateamum = $row->updateamum;
                $coodle->updatevon = $row->updatevon;
                $coodle->mailversand = $row->mailversand;
	            $coodle->teilnehmer_anonym = $row->teilnehmer_anonym;
	            $coodle->termine_anonym = $row->termine_anonym;

                $this->result[] = $coodle;
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
	 * Laedt einen Termin
	 * @param Integer $coodle_termin_id
	 * @return boolean
	 */
	public function loadTermin($coodle_termin_id)
	{
		$qry = "SELECT * FROM campus.tbl_coodle_termin
                    WHERE coodle_termin_id=".$this->db_add_param($coodle_termin_id, FHC_INTEGER).';';

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->coodle_termin_id = $row->coodle_termin_id;
				$this->coodle_id = $row->coodle_id;
				$this->datum = $row->datum;
				$this->uhrzeit = $row->uhrzeit;
				$this->auswahl = $this->db_parse_bool($row->auswahl);
				return true;
			}
			else
			{
				$this->errormsg = 'Termin wurde nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Loescht einen Termin
	 * @param $coodle_termin_id
	 * @return boolean
	 */
	public function deleteTermin($coodle_termin_id)
	{
        if($coodle_termin_id == '' || !is_numeric($coodle_termin_id))
        {
            $this->errormsg = 'Ungültige Id übergeben';
            return false;
        }

		$qry = "DELETE FROM campus.tbl_coodle_termin
                    WHERE coodle_termin_id=".$this->db_add_param($coodle_termin_id, FHC_INTEGER, false).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Eintrags';
		}
	}

    /**
     * Überprüft ob die übergebene Coodleumfrage den Status laufend oder abgeschlossen hat
     * @param Integer $coodle_id
     * @return boolean
     */
    public function checkStatus($coodle_id)
    {
        if($coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = 'Coodle_id muss eine Zahl sein';
            return false;
        }

        $qry ="SELECT 1 FROM campus.tbl_coodle
                WHERE coodle_id = ".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
                AND coodle_status_kurzbz IN('laufend','abgeschlossen');";

        if($result = $this->db_query($qry))
        {
            if($this->db_fetch_object($result))
                return true;
            else
            {
                $this->errormsg = "Kein passender Status vorhanden";
                return false;
            }
        }
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }
    }

    /**
     * Setzt die Auswahl aller Termine auf false
     * @param Integer $coodle_id
     * @return boolean
     */
    public function setTerminFalse($coodle_id)
    {
        if($coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = "Coodle_id muss eine gültige Zahl sein";
            return false;
        }

        $qry ="UPDATE campus.tbl_coodle_termin set auswahl='false'
                WHERE coodle_id=".$this->db_add_param($coodle_id, FHC_INTEGER, false).';';

        if($result=$this->db_query($qry))
            return true;
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }
    }

    /**
     * Gibt true zurück wenn der übergeben termin der ausgewählte ist für die übergebene coodle_id
     * @param Integer $coodle_id
     * @param Integer $coodle_termin_id
     * @return boolean
     */
    public function checkTerminAuswahl($coodle_id, $coodle_termin_id)
    {
        if($coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = "Coodle_id muss eine gültige Zahl sein";
            return false;
        }

        if($coodle_termin_id == '' || !is_numeric($coodle_termin_id))
        {
            $this->errormsg = "termin_id muss eine gültige Zahl sein";
            return false;
        }

        $qry = "Select * FROM campus.tbl_coodle_termin
                    WHERE coodle_id = ".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
                    AND coodle_termin_id = ".$this->db_add_param($coodle_termin_id, FHC_INTEGER, false)."
                    AND auswahl is true";

        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_object($result))
                return true;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }

        return false;
    }


    /**
     * Gibt die Id des ausgewählten Termines der Coodleumfrage zurück
     * @param Integer $coodle_id
     * @param Integer $coodle_termin_id
     * @return boolean
     */
    public function getTerminAuswahl($coodle_id)
    {
        if($coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = "Coodle_id muss eine gültige Zahl sein";
            return false;
        }

        $qry = "Select * FROM campus.tbl_coodle_termin
                    WHERE coodle_id = ".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
                    AND auswahl is true";

        if($result = $this->db_query($qry))
        {
            if($row = $this->db_fetch_object($result))
                return $row->coodle_termin_id;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }

        return false;
    }

    /**
     * Liefert alle Räume die als Ressource zu einer Umfrage eingetragen sind
     * @param Integer $coodle_id
     * @return boolean
     */
    public function getRaumeFromId($coodle_id)
    {
        if($coodle_id == '' || !is_numeric($coodle_id))
        {
            $this->errormsg = "Coodle_id muss eine gültige Zahl sein";
            return false;
        }

        $qry ="SELECT * FROM campus.tbl_coodle_ressource
                WHERE coodle_id =".$this->db_add_param($coodle_id, FHC_INTEGER, false)."
                    AND ort_kurzbz is not null";

        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $ressource = new coodle();

                $ressource->coodle_ressource_id = $row->coodle_ressource_id;
                $ressource->coodle_id = $row->coodle_id;
                $ressource->ort_kurzbz = $row->ort_kurzbz;
                $ressource->insertamum = $row->insertamum;
                $ressource->insertvon = $row->insertvon;
                $ressource->updateamum = $row->updateamum;
                $ressource->updatevon = $row->updatevon;

                $this->result[] = $ressource;

            }
            return true;
        }
        else
        {
            $this->erromsg = "Fehler bei der Abfrage aufgetreten";
            return false;
        }
    }

    /**
     * Laedt alle Stati
     */
    public function loadStatus()
    {
    	$qry = "SELECT * FROM campus.tbl_coodle_status";

    	if($result = $this->db_query($qry))
    	{
    		while($row = $this->db_fetch_object($result))
    		{
    			$this->status_arr[$row->coodle_status_kurzbz]=$row->bezeichnung;
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
     * Liefert alle Coodle Umfragen die das Beendigungsdatum erreicht haben
     * und noch nicht abgeschlossen sind
     * @return true wenn ok, false im Fehlerfall
     */
    public function getCoodleBeendet()
    {
        $qry = "SELECT *
                    FROM campus.tbl_coodle
                WHERE
                    endedatum = CURRENT_DATE - interval '1 day'
                    AND coodle_status_kurzbz='laufend';";

        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false;
        }

        while($row = $this->db_fetch_object())
        {
            $coodle = new coodle();
            $coodle->coodle_id = $row->coodle_id;
            $coodle->titel = $row->titel;
            $coodle->beschreibung = $row->beschreibung;
            $coodle->coodle_status_kurzbz = $row->coodle_status_kurzbz;
            $coodle->dauer = $row->dauer;
            $coodle->insertamum = $row->insertamum;
            $coodle->insertvon = $row->insertvon;
            $coodle->updateamum = $row->updateamum;
            $coodle->updatevon = $row->updatevon;
            $coodle->endedatum = $row->endedatum;
            $coodle->ersteller_uid = $row->ersteller_uid;
            $coodle->mailversand = $row->mailversand;
            $coodle->teilnehmer_anonym = $row->teilnehmer_anonym;
            $coodle->termine_anonym = $row->termine_anonym;

            $this->result[] = $coodle;
        }

        return true;
    }

	/**
	 * Führt 2 Formatierungen bestehender Contentlines durch
	 * Umwandeln (Maskieren) bestehender Breaks in \n
	 * Einfügen von breaks nach 74 Zeichen, da content line <= 75 chars sein muss
	 * @param $contentline zu formatierender Input
	 * @return param formatierter Output
	 */
	public function foldContentLine($contentline)
	{
		$linebreaksreplaced = strtr($contentline, array(
			"\r\n"	=> "\\n",
			"\r" 	=> "",
			"\n" 	=> "\\n",
			"<br>" 	=> "\\n",
			"<br />"=> "\\n"
		));

		$foldedcontentline = wordwrap($linebreaksreplaced, 74, "\r\n ", true);

		return $foldedcontentline;
	}
}
?>
