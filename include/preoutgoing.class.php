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
 * Authors: Karl Burkhart <burkhart@technikum-wien.at>
 *          
 */
/**
 * Klasse PreOutgoing
 * @create 13-04-2012
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class preoutgoing extends basis_db
{
	public $new;                    //  boolean
	public $result = array();       //  preoutgoing objekte

	// Tabellenspalten tbl_preoutgoing
	public $preoutgoing_id;         // serial
    public $uid;                    // uid
    public $dauer_von;              // date
    public $dauer_bis;              // date
    public $ansprechperson;         // uid
    public $bachelorarbeit;         // boolean
    public $masterarbeit;           // boolean
    public $projektarbeittitel;     // varchar (128)
    public $betreuer;               // varchar(256)
    public $sprachkurs;             // boolean
    public $intensivsprachkurs;     // boolean
    public $sprachkurs_von;         // date
    public $sprachkurs_bis;         // date
    public $praktikum;              // boolean
    public $praktikum_von;          // date
    public $praktikum_bis;          // date
    public $behinderungszuschuss;   // boolean
    public $studienbeihilfe;        // boolean
    public $anmerkung_student;      // text
    public $anmerkung_admin;        // text
    public $studienrichtung_gastuniversitaet; // varchar(64)
    public $insertamum;             // timestamp
    public $insertvon;              // uid
    public $updateamum;             // timestamp
    public $updatevon;              // uid
            
    /* Tabellenspalten tbl_preoutgoing_lehrveranstaltung */
    public $lehrveranstaltungen = array();      // preoutgoing objekte
    public $preoutgoing_lehrveranstaltung_id;   // serial
    // public $preoutgoing_id;                  // integer
    public $bezeichnung;                        // varchar(256)
    public $ects;                               // numeric(5,2)
    public $endversion;                         // boolean
    public $wochenstunden;                       // numeric(5,2)
    public $unitcode;                           // varchar(16)                  
    
    /* Tabellenspalten tbl_preoutgoing_status */
    public $stati = array();                    // preoutgoing objekte
    public $status_id;                          // serial
    public $preoutgoing_status_kurzbz;           // varchar(32)
    // public $bezeichnung;                     // varchar(256)
    // public $preoutgoing_id;                  // integer
    public $datum;                              // date
    
    /* Tabellenspalten tbl_preoutgoing_firma */
    public $firmen = array();                   // preoutgoing objekte
    public $preoutgoing_firma_id;               // serial
    // public $preoutgoing_id;                  // integer
    public $mobilitaetsprogramm_code;           // integer
    public $firma_id;                           // integer
    public $name;                               // varchar(256)
    public $auswahl;                            // boolean
    

	/**
	 * Konstruktor
	 * @param $preoutgoing ID des Datensatzes der geladen werden soll (Default=null)
	 */
	public function __construct($preoutgoing_id=null)
	{
		parent::__construct();
		
		if(!is_null($preoutgoing_id))
			$this->load($preoutgoing_id);
	}

	/**
	 * Laedt den Preoutgoing Datensatz mit der uebergebenen ID
	 * @param  $preoutgoing_id ID des zu ladenden Studenten
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($preoutgoing_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_preoutgoing WHERE preoutgoing_id=".$this->db_add_param($preoutgoing_id,FHC_INTEGER);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
            $this->preoutgoing_id = $row->preoutgoing_id;
            $this->uid = $row->uid;
            $this->dauer_von = $row->dauer_von;
            $this->dauer_bis = $row->dauer_bis;
            $this->ansprechperson = $row->ansprechperson; 
            $this->bachelorarbeit = $this->db_parse_bool($row->bachelorarbeit);
            $this->masterarbeit = $this->db_parse_bool($row->masterarbeit);
            $this->projektarbeittitel = $row->projektarbeittitel; 
            $this->betreuer = $row->betreuer; 
            $this->sprachkurs = $this->db_parse_bool($row->sprachkurs); 
            $this->intensivsprachkurs = $this->db_parse_bool($row->intensivsprachkurs); 
            $this->sprachkurs_von = $row->sprachkurs_von; 
            $this->sprachkurs_bis = $row->sprachkurs_bis; 
            $this->praktikum = $this->db_parse_bool($row->praktikum); 
            $this->praktikum_von = $row->praktikum_von; 
            $this->praktikum_bis = $row->praktikum_bis; 
            $this->behinderungszuschuss = $this->db_parse_bool($row->behinderungszuschuss);
            $this->studienbeihilfe = $this->db_parse_bool($row->studienbeihilfe);
            $this->anmerkung_student = $row->anmerkung_student; 
            $this->anmerkung_admin = $row->anmerkung_admin; 
            $this->studienrichtung_gastuniversitaet = $row->studienrichtung_gastuniversitaet; 
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
     * Liefert alle Outgoings zurück
    */
    public function getAll()
    {
        $qry = "SELECT * FROM public.tbl_preoutgoing;";
        if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
            $preoutgoing= new preoutgoing();
            
            $preoutgoing->preoutgoing_id = $row->preoutgoing_id;
            $preoutgoing->uid = $row->uid;
            $preoutgoing->dauer_von = $row->dauer_von;
            $preoutgoing->dauer_bis = $row->dauer_bis;
            $preoutgoing->ansprechperson = $row->ansprechperson; 
            $preoutgoing->bachelorarbeit = $this->db_parse_bool($row->bachelorarbeit);
            $preoutgoing->masterarbeit = $this->db_parse_bool($row->masterarbeit);
            $preoutgoing->projektarbeittitel = $row->projektarbeittitel; 
            $preoutgoing->betreuer = $row->betreuer; 
            $preoutgoing->sprachkurs = $this->db_parse_bool($row->sprachkurs); 
            $preoutgoing->intensivsprachkurs = $this->db_parse_bool($row->intensivsprachkurs); 
            $preoutgoing->sprachkurs_von = $row->sprachkurs_von; 
            $preoutgoing->sprachkurs_bis = $row->sprachkurs_bis; 
            $preoutgoing->praktikum = $this->db_parse_bool($row->praktikum); 
            $preoutgoing->praktikum_von = $row->praktikum_von; 
            $preoutgoing->praktikum_bis = $row->praktikum_bis; 
            $preoutgoing->behinderungszuschuss = $this->db_parse_bool($row->behinderungszuschuss);
            $preoutgoing->studienbeihilfe = $this->db_parse_bool($row->studienbeihilfe);
            $preoutgoing->anmerkung_student = $row->anmerkung_student; 
            $preoutgoing->anmerkung_admin = $row->anmerkung_admin; 
            $preoutgoing->studienrichtung_gastuniversitaet = $row->studienrichtung_gastuniversitaet; 
            $preoutgoing->insertamum = $row->insertamum; 
            $preoutgoing->insertvon = $row->insertvon; 
            $preoutgoing->updateamum = $row->updateamum; 
            $preoutgoing->updatevon = $row->updatevon; 
            
            $this->result[] = $preoutgoing; 
		}       
        return true; 
    }
    
    
    /**
	 * Laedt die Preoutgoing Datensätze mit der uebergebenen uid es wird immer der neueste zurückgegeben
	 * @param  $uid uid des zu ladenden Studenten
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadUid($uid)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if($uid == '')
		{
			$this->errormsg = 'Ungültige UID';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM public.tbl_preoutgoing WHERE uid=".$this->db_add_param($uid).' ORDER BY insertamum DESC LIMIT 1;';

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
            
            $this->preoutgoing_id = $row->preoutgoing_id;
            $this->uid = $row->uid;
            $this->dauer_von = $row->dauer_von;
            $this->dauer_bis = $row->dauer_bis;
            $this->ansprechperson = $row->ansprechperson; 
            $this->bachelorarbeit = $this->db_parse_bool($row->bachelorarbeit);
            $this->masterarbeit = $this->db_parse_bool($row->masterarbeit);
            $this->projektarbeittitel = $row->projektarbeittitel; 
            $this->betreuer = $row->betreuer; 
            $this->sprachkurs = $this->db_parse_bool($row->sprachkurs); 
            $this->intensivsprachkurs = $this->db_parse_bool($row->intensivsprachkurs); 
            $this->sprachkurs_von = $row->sprachkurs_von; 
            $this->sprachkurs_bis = $row->sprachkurs_bis; 
            $this->praktikum = $this->db_parse_bool($row->praktikum); 
            $this->praktikum_von = $row->praktikum_von; 
            $this->praktikum_bis = $row->praktikum_bis; 
            $this->behinderungszuschuss = $this->db_parse_bool($row->behinderungszuschuss);
            $this->studienbeihilfe = $this->db_parse_bool($row->studienbeihilfe);
            $this->anmerkung_student = $row->anmerkung_student; 
            $this->anmerkung_admin = $row->anmerkung_admin; 
            $this->studienrichtung_gastuniversitaet = $row->studienrichtung_gastuniversitaet; 
            $this->insertamum = $row->insertamum; 
            $this->insertvon = $row->insertvon; 
            $this->updateamum = $row->updateamum; 
            $this->updatevon = $row->updatevon; 
		}
        else
            return false; 

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
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_preoutgoing (uid, dauer_von, dauer_bis, ansprechperson, bachelorarbeit, masterarbeit, projektarbeittitel,
                betreuer, sprachkurs, intensivsprachkurs, sprachkurs_von, sprachkurs_bis, praktikum, praktikum_von, praktikum_bis, behinderungszuschuss,
                studienbeihilfe, anmerkung_student, anmerkung_admin, studienrichtung_gastuniversitaet, insertamum, insertvon, updateamum, updatevon)
				  VALUES('.
                $this->db_add_param($this->uid).', '.
                $this->db_add_param($this->dauer_von).', '.
                $this->db_add_param($this->dauer_bis).', '.
                $this->db_add_param($this->ansprechperson).', '.
                $this->db_add_param($this->bachelorarbeit, FHC_BOOLEAN).', '.
                $this->db_add_param($this->masterarbeit, FHC_BOOLEAN).', '.
                $this->db_add_param($this->projektarbeittitel).', '.
                $this->db_add_param($this->betreuer).', '.
                $this->db_add_param($this->sprachkurs, FHC_BOOLEAN).', '.
                $this->db_add_param($this->intensivsprachkurs, FHC_BOOLEAN).', '.
                $this->db_add_param($this->sprachkurs_von).', '.
                $this->db_add_param($this->sprachkurs_bis).', '.
                $this->db_add_param($this->praktikum, FHC_BOOLEAN).', '.
                $this->db_add_param($this->praktikum_von).', '.
                $this->db_add_param($this->praktikum_bis).', '.
                $this->db_add_param($this->behinderungszuschuss, FHC_BOOLEAN).', '.
                $this->db_add_param($this->studienbeihilfe, FHC_BOOLEAN).', '.
                $this->db_add_param($this->anmerkung_student).', '.
                $this->db_add_param($this->anmerkung_admin).', '.
                $this->db_add_param($this->studienrichtung_gastuniversitaet).', '.
                ' now(), '.
                $this->db_add_param($this->insertamum).' , '.
                ' now(), '.
                $this->db_add_param($this->updatevon).');';

		}
		else
		{
			$qry='UPDATE public.tbl_preoutgoing SET'.
				' uid='.$this->db_add_param($this->uid).', '.
				' dauer_von='.$this->db_add_param($this->dauer_von).', '.
				' dauer_bis='.$this->db_add_param($this->dauer_bis).', '.
		      	' ansprechperson='.$this->db_add_param($this->ansprechperson).', '.
		      	' bachelorarbeit='.$this->db_add_param($this->bachelorarbeit, FHC_BOOLEAN).', '.
		      	' masterarbeit='.$this->db_add_param($this->masterarbeit, FHC_BOOLEAN).', '.
				' projektarbeittitel='.$this->db_add_param($this->projektarbeittitel).', '.                    
				' betreuer='.$this->db_add_param($this->betreuer).', '.
				' sprachkurs='.$this->db_add_param($this->sprachkurs, FHC_BOOLEAN).', '.
		      	' intensivsprachkurs='.$this->db_add_param($this->intensivsprachkurs, FHC_BOOLEAN).', '.
		      	' sprachkurs_von='.$this->db_add_param($this->sprachkurs_von).', '.
				' sprachkurs_bis='.$this->db_add_param($this->sprachkurs_bis).', '.
				' praktikum='.$this->db_add_param($this->praktikum, FHC_BOOLEAN).', '.
		      	' praktikum_von='.$this->db_add_param($this->praktikum_von).', '.
		      	' praktikum_bis='.$this->db_add_param($this->praktikum_bis).', '.
				' behinderungszuschuss='.$this->db_add_param($this->behinderungszuschuss, FHC_BOOLEAN).', '.
				' studienbeihilfe='.$this->db_add_param($this->studienbeihilfe, FHC_BOOLEAN).', '.
                ' anmerkung_student='.$this->db_add_param($this->anmerkung_student).', '.
                ' anmerkung_admin='.$this->db_add_param($this->anmerkung_admin).', '.
                ' studienrichtung_gastuniversitaet='.$this->db_add_param($this->studienrichtung_gastuniversitaet).', '.
				' updateamum= now(), '.
				' updatevon='.$this->db_add_param($this->updatevon).' 
                WHERE preoutgoing_id = '.$this->db_add_param($this->preoutgoing_id, FHC_INTEGER).';';
		}
	
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle ID aus der Sequence holen
				$qry="SELECT currval('seq_preoutgoing_preoutgoing_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->preoutgoing_id = $row->id;
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
            $this->errormsg = $qry; 
			return false;
		}
		return true;
	}


    public function saveLv()
    {
        if($this->new)
        {
            $qry='BEGIN;INSERT INTO public.tbl_preoutgoing_lehrveranstaltung (preoutgoing_id, bezeichnung, wochenstunden, unitcode, ects, endversion, insertamum, insertvon, updateamum, updatevon)
            VALUES('.$this->db_add_param($this->preoutgoing_id, FHC_INTEGER).', '
            .$this->db_add_param($this->bezeichnung).', '
            .$this->db_add_param($this->wochenstunden).', '
            .$this->db_add_param($this->unitcode).', '
            .$this->db_add_param($this->ects).', false, now(), '
            .$this->db_add_param($this->insertvon).', null, null );';
        }
        else
        {
            $qry='UPDATE public.tbl_preoutgoing_lehrveranstaltung SET'.
				' preoutgoing_id='.$this->db_add_param($this->preoutgoing_id, FHC_INTEGER).', '.
				' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				' wochenstunden='.$this->db_add_param($this->wochenstunden).', '.
				' unitcode='.$this->db_add_param($this->unitcode).', '.                    
				' ects='.$this->db_add_param($this->ects).', '.
		      	' endversion='.$this->db_add_param($this->endversion, FHC_BOOLEAN).', '.
                ' updatevon ='.$this->db_add_param($this->updatevon).', '.
                ' updateamum = now() 
                WHERE preoutgoing_lehrveranstaltung_id = '.$this->db_add_param($this->preoutgoing_lehrveranstaltung_id, FHC_INTEGER).';';
        }
        
        if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle ID aus der Sequence holen
				$qry="SELECT currval('seq_preoutgoing_lehrveranstaltung_preoutgoing_lehrveranstaltung') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->preoutgoing_lehrveranstaltung_id = $row->id;
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
     *
     * @param type $preoutgoing_id
     * @return boolean 
     */
    public function loadLvs($preoutgoing_id)
    {
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}        
        $qry = "SELECT * FROM public.tbl_preoutgoing_lehrveranstaltung WHERE preoutgoing_id =".$this->db_add_param($preoutgoing_id);
        
        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $outLv = new preoutgoing(); 
                $outLv->preoutgoing_lehrveranstaltung_id = $row->preoutgoing_lehrveranstaltung_id; 
                $outLv->preoutgoing_id = $row->preoutgoing_id; 
                $outLv->bezeichnung = $row->bezeichnung; 
                $outLv->wochenstunden = $row->wochenstunden; 
                $outLv->unitcode = $row->unitcode; 
                $outLv->ects = $row->ects; 
                $outLv->endversion = $row->endversion; 
                $outLv->insertamum = $row->insertamum; 
                $outLv->insertvon = $row->insertvon; 
                $outLv->updateamum = $row->updateamum; 
                $outLv->updatevon = $row->updatevon; 
                
                $this->lehrveranstaltungen[] =$outLv; 
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
     * @param type $preoutgoing_lehrveranstaltung_id
     * @return boolean 
     */
    public function deleteLv($preoutgoing_lehrveranstaltung_id)
    {
        //Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_lehrveranstaltung_id) || $preoutgoing_lehrveranstaltung_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		} 
        
        $qry = "DELETE FROM public.tbl_preoutgoing_lehrveranstaltung 
            WHERE preoutgoing_lehrveranstaltung_id =".$this->db_add_param($preoutgoing_lehrveranstaltung_id, FHC_INTEGER).";";
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten";
            return false; 
        }
        
        return true; 
    }
    
    /**
     *
     * @param type $preoutgoing_lehrveranstaltung_id
     * @param type $preoutgoing_id 
     */
    public function checkLv($preoutgoing_lehrveranstaltung_id, $preoutgoing_id)
    {
        $qry ="SELECT 1 FROM public.tbl_preoutgoing_lehrveranstaltung 
            WHERE preoutgoing_lehrveranstaltung_id =".$this->db_add_param($preoutgoing_lehrveranstaltung_id, FHC_INTEGER)." AND
                preoutgoing_id =".$this->db_add_param($preoutgoing_id, FHC_INTEGER).";";
        
        if($result = $this->db_query($qry))
        {
            if($this->db_fetch_object($result))
                return true;
            else
                return false; 
        }
    }
    
    

    /**
     *
     * @param type $preoutgoing_id
     * @return boolean 
     */
    public function setAuswahlFirmaFalse($preoutgoing_id)
    {
        $qry = "UPDATE public.tbl_preoutgoing_firma SET auswahl = 'false'
            WHERE preoutgoing_id = ".$this->db_add_param($preoutgoing_id, FHC_INTEGER).";";
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
            return false; 
        }
        
        return true; 
    }
    
    /**
	 * Laedt die Firmen die der Outgoing ausgesucht hat
	 * @param  $uid uid des zu ladenden Studenten
	 * @return true wenn ok, false im Fehlerfall
	 */
    public function loadAuswahlFirmen($preoutgoing_id)
    {
        //Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}
        
        $qry = "SELECT * FROM public.tbl_preoutgoing_firma 
            JOIN public.tbl_preoutgoing USING(preoutgoing_id) 
            WHERE preoutgoing_id = ".$this->db_add_param($preoutgoing_id, FHC_INTEGER)." ORDER BY preoutgoing_firma_id";
        
        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $firma = new preoutgoing(); 
                
                $firma->preoutgoing_firma_id = $row->preoutgoing_firma_id; 
                $firma->preoutgoing_id = $row->preoutgoing_id; 
                $firma->firma_id = $row->firma_id; 
                $firma->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
                $firma->name = $row->name;
                $firma->auswahl = $this->db_parse_bool($row->auswahl);
                
                $this->firmen[] = $firma;
            }
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
            return false; 
        }
    }
    
    
    /**
     * Lädt die Ausgewählte Firma eines Outgoings
    */
    public function loadAuswahl($preoutgoing_id)
    {
        //Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}        
        
        $qry = "SELECT firma.* FROM public.tbl_preoutgoing_firma firma
            JOIN public.tbl_preoutgoing USING(preoutgoing_id) 
            WHERE preoutgoing_id = ".$this->db_add_param($preoutgoing_id, FHC_INTEGER)." AND auswahl = 'true';";
        
        if($result = $this->db_query($qry))
        {
            if($row= $this->db_fetch_object($result))
            {
                $this->preoutgoing_firma_id = $row->preoutgoing_firma_id; 
                $this->preoutgoing_id = $row->preoutgoing_id; 
                $this->firma_id = $row->firma_id; 
                $this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
                $this->name = $row->name; 
                $this->auswahl = $this->db_parse_bool($row->auswahl); 
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
     * Lädt eine firma mit übergebener firma_id vom outgoing
     * @param type $preoutgoing_firma_id
     * @return boolean 
     */
    public function loadFirma($preoutgoing_firma_id)
    {
        $qry='SELECT * FROM public.tbl_preoutgoing_firma WHERE preoutgoing_firma_id = '.$this->db_add_param($preoutgoing_firma_id, FHC_INTEGER).';';
        
        if($this->db_query($qry))
        {
            if($row=$this->db_fetch_object())
            {
                $this->preoutgoing_firma_id = $row->preoutgoing_firma_id; 
                $this->preoutgoing_id = $row->preoutgoing_id; 
                $this->mobilitaetsprogramm_code = $row->mobilitaetsprogramm_code; 
                $this->firma_id = $row->firma_id; 
                $this->name = $row->name; 
                $this->auswahl = $this->db_parse_bool($row->auswahl);
            }
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
            return false;
        }
        
        return true; 
    }
    
    
    /**
     * Speichert eine übergebene Firma zu einer preoutgoing_id
     */
    public function saveFirma()
    {
        if($this->new)
        {
            $qry='BEGIN;INSERT INTO public.tbl_preoutgoing_firma (preoutgoing_id, mobilitaetsprogramm_code, firma_id, name, auswahl)
            VALUES('.$this->db_add_param($this->preoutgoing_id, FHC_INTEGER).', '
            .$this->db_add_param($this->mobilitaetsprogramm_code, FHC_INTEGER).', '
            .$this->db_add_param($this->firma_id, FHC_INTEGER).', '
            .$this->db_add_param($this->name).', false );';
        }
        else
        {
            $qry='UPDATE public.tbl_preoutgoing_firma SET'.
				' mobilitaetsprogramm_code='.$this->db_add_param($this->mobilitaetsprogramm_code).', '.
				' firma_id='.$this->db_add_param($this->firma_id, FHC_INTEGER).', '.
				' name='.$this->db_add_param($this->name).', '.
		      	' auswahl='.$this->db_add_param($this->auswahl, FHC_BOOLEAN).'
                WHERE preoutgoing_firma_id = '.$this->db_add_param($this->preoutgoing_firma_id, FHC_INTEGER).';';
        }
        
        if($this->db_query($qry))
		{
			if($this->new)
			{
				//aktuelle ID aus der Sequence holen
				$qry="SELECT currval('seq_preoutgoing_firma_preoutgoing_firma_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->preoutgoing_firma_id = $row->id;
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
	 * Liefert die Anzahl der eingetragen Firmen zu einem Outgoing zurück
	 * @param  $preoutgoing id des zu ladenden Studenten
	 */
    public function getAnzahlFirma($preoutgoing_id)
    {
        //Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}
        
        $qry = "SELECT count(*) as anzahl FROM public.tbl_preoutgoing_firma 
            WHERE preoutgoing_id =".$this->db_add_param($preoutgoing_id, FHC_INTEGER).';';
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten";
            return false; 
        }
        if($row = $this->db_fetch_object())
        {
            return $row->anzahl; 
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten"; 
            return false;
        }
    }
    
    
    /**
	 * Löscht die übergebene Firma
	 * @param  $preoutgoing_firma_id id der zu löschenden Firma
	 */    
    public function deleteFirma($preoutgoing_firma_id)
    {
        //Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_firma_id) || $preoutgoing_firma_id == '')
		{
			$this->errormsg = 'id muss eine Zahl sein';
			return false;
		}
        
        $qry ="DELETE FROM public.tbl_preoutgoing_firma WHERE preoutgoing_firma_id =".$this->db_add_param($preoutgoing_firma_id, FHC_INTEGER).';';
        
        if(!$this->db_query($qry))
        {
            $this->errormsg ="Fehler beim löschen aufgetreten";
            return false; 
        }
        return true; 
    }
    
	/**
	 * Loescht den Outgoing Datenensatz mit der ID die uebergeben wird
	 * @param $preoutgoing_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($preoutgoing_id)
	{
		//Pruefen ob preoutgoing_id eine gueltige Zahl ist
		if(!is_numeric($preoutgoing_id) || $preoutgoing_id == '')
		{
			$this->errormsg = 'ID muss eine gültige Zahl sein';
			return false;
		}

		//loeschen des Datensatzes
		$qry='DELETE FROM public.tbl_preoutgoing WHERE preoutgoing_id='.$this->db_add_param($preoutgoing_id, FHC_INTEGER).';';

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
     * Speichert einen Status zu einem Outgoing
     * @param $preoutgoing_id id des Outgoings
     * @param $status_kurzbz Kurzbz des Status der eingefügt wird
     * @return true bei erfolg
     * @return false bei einem Fehler
    */
	public function setStatus($preoutgoing_id, $status_kurzbz)
    {
        if($this->checkStatus($preoutgoing_id, $status_kurzbz))
            return true; 
        
        $qry='BEGIN;INSERT INTO public.tbl_preoutgoing_preoutgoing_status (preoutgoing_status_kurzbz, preoutgoing_id, datum, insertamum, insertvon, updateamum, updatevon)
            VALUES('.$this->db_add_param($status_kurzbz).', '
            .$this->db_add_param($preoutgoing_id, FHC_INTEGER).', 
            now(), now(), null,  null, null);';
         
        if($this->db_query($qry))
		{
				//aktuelle ID aus der Sequence holen
				$qry="SELECT currval('seq_preoutgoing_preoutgoing_status_status_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->status_id = $row->id;
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
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Datensatzes';
			return false;
		}
		return true;  
    }
    
	/**
	 * Prueft ob der Status bereits zu dem Preoutging zugeordnet ist
	 * 
	 * @param $preoutgoing_id
	 * @param $status_kurzbz
	 * @return boolean true wenn bereits zugeordnet, sonst false 
	 */
	public function checkStatus($preoutgoing_id, $status_kurzbz)
	{
		$qry = "SELECT 1 FROM public.tbl_preoutgoing_preoutgoing_status 
				WHERE 
					preoutgoing_id=".$this->db_add_param($preoutgoing_id, FHC_INTEGER)." 
					AND preoutgoing_status_kurzbz =".$this->db_add_param($status_kurzbz).";";

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
     * Liefert Alle Stati eines Outgoings zurück
     * @param type $preoutgoing_id
     * @return boolean 
     */
    public function getAllStatus($preoutgoing_id)
    {
        $qry = "SELECT * FROM public.tbl_preoutgoing_preoutgoing_status 
				WHERE 
					preoutgoing_id=".$this->db_add_param($preoutgoing_id, FHC_INTEGER);
        
        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $out = new preoutgoing(); 
                $out->status_id = $row->status_id; 
                $out->preoutgoing_status_kurzbz = $row->preoutgoing_status_kurzbz; 
                $out->preoutgoing_id = $row->preoutgoing_id; 
                $out->datum = $row->datum; 
                $out->insertamum = $row->insertamum; 
                $out->insertvon = $row->insertvon; 
                $out->updateamum = $row->updateamum; 
                $out->updatevon = $row-> updatevon; 
                
                $this->stati[]=$out; 
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
     * Liefert Outgoings anhand bestimmter Kriterien zurück
     * 
     * @param $name
     * @param $von
     * @param $bis
     * @param $status
     * @return boolean 
     */
    public function getOutgoingFilter($name ='', $von ='', $bis='', $status='')
    {
        $qry ="SELECT distinct(pre.preoutgoing_id), person.vorname, person.nachname, pre.* FROM public.tbl_preoutgoing pre 
                LEFT JOIN public.tbl_preoutgoing_preoutgoing_status status USING(preoutgoing_id) 
                JOIN public.tbl_benutzer benutzer USING(uid)
                JOIN public.tbl_person person USING(person_id)
                WHERE (vorname LIKE '%".$name."%' OR nachname LIKE'%".$name."%')";
        
        if($von != '')
            $qry.=" AND pre.dauer_von >=".$this->db_add_param($von, FHC_STRING);
        
        if($bis != '')
            $qry.= " AND pre.dauer_bis <=".$this->db_add_param ($bis, FHC_STRING);
        
        if($status != '')
            $qry.= "AND status.preoutgoing_status_kurzbz =".$this->db_add_param ($status, FHC_STRING);      

        if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while($row = $this->db_fetch_object())
		{
            $preoutgoing= new preoutgoing();
            
            $preoutgoing->preoutgoing_id = $row->preoutgoing_id;
            $preoutgoing->uid = $row->uid;
            $preoutgoing->dauer_von = $row->dauer_von;
            $preoutgoing->dauer_bis = $row->dauer_bis;
            $preoutgoing->ansprechperson = $row->ansprechperson; 
            $preoutgoing->bachelorarbeit = $this->db_parse_bool($row->bachelorarbeit);
            $preoutgoing->masterarbeit = $this->db_parse_bool($row->masterarbeit);
            $preoutgoing->projektarbeittitel = $this->projektarbeittitel; 
            $preoutgoing->betreuer = $row->betreuer; 
            $preoutgoing->sprachkurs = $this->db_parse_bool($row->sprachkurs); 
            $preoutgoing->intensivsprachkurs = $this->db_parse_bool($row->intensivsprachkurs); 
            $preoutgoing->sprachkurs_von = $row->sprachkurs_von; 
            $preoutgoing->sprachkurs_bis = $row->sprachkurs_bis; 
            $preoutgoing->praktikum = $this->db_parse_bool($row->praktikum); 
            $preoutgoing->praktikum_von = $row->praktikum_von; 
            $preoutgoing->praktikum_bis = $row->praktikum_bis; 
            $preoutgoing->behinderungszuschuss = $this->db_parse_bool($row->behinderungszuschuss);
            $preoutgoing->studienbeihilfe = $this->db_parse_bool($row->studienbeihilfe);
            $preoutgoing->anmerkung_student = $row->anmerkung_student; 
            $preoutgoing->anmerkung_admin = $row->anmerkung_admin; 
            $preoutgoing->studienrichtung_gastuniversitaet = $row->studienrichtung_gastuniversitaet; 
            $preoutgoing->insertamum = $row->insertamum; 
            $preoutgoing->insertvon = $row->insertvon; 
            $preoutgoing->updateamum = $row->updateamum; 
            $preoutgoing->updatevon = $row->updatevon; 
            
            $this->result[] = $preoutgoing; 
		}       
        return true; 
    }
    
    /**
     * Liefert alle Status_kurzbz zurück
     * @return boolean 
     */
    public function getAllStatiKurzbz()
    {
        $qry = "SELECT * FROM public.tbl_preoutgoing_status";
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false; 
        }
        
        while($row= $this->db_fetch_object())
        {
            $preoutgoing = new preoutgoing(); 
            $preoutgoing->preoutgoing_status_kurzbz = $row->preoutgoing_status_kurzbz; 
            $preoutgoing->bezeichnung = $row->bezeichnung; 
            
            $this->stati[] = $preoutgoing; 
        }
        return true; 
    }
    
    
    /**
     * Liefert alle Aktuell im Ausland befindlichen Outgoings zurück
     * @return boolean 
     */
    public function getAktuellOutgoing()
    {
        $qry = "SELECT * FROM public.tbl_preoutgoing where now() between dauer_von and dauer_bis;";
        //$qry = "SELECT * FROM public.tbl_preoutgoing WHERE dauer_von < CURRENT_DATE AND dauer_bis > CURRENT_DATE;";
        
        if(!$this->db_query($qry))
        {
            $this->errormsg = 'Fehler bei der Abfrage aufgetreten';
            return false; 
        }
        
        while($row = $this->db_fetch_object())
        {
            $preoutgoing= new preoutgoing();
            
            $preoutgoing->preoutgoing_id = $row->preoutgoing_id;
            $preoutgoing->uid = $row->uid;
            $preoutgoing->dauer_von = $row->dauer_von;
            $preoutgoing->dauer_bis = $row->dauer_bis;
            $preoutgoing->ansprechperson = $row->ansprechperson; 
            $preoutgoing->bachelorarbeit = $this->db_parse_bool($row->bachelorarbeit);
            $preoutgoing->projektarbeittitel = $row->projektarbeittitel; 
            $preoutgoing->masterarbeit = $this->db_parse_bool($row->masterarbeit);
            $preoutgoing->betreuer = $row->betreuer; 
            $preoutgoing->sprachkurs = $this->db_parse_bool($row->sprachkurs); 
            $preoutgoing->intensivsprachkurs = $this->db_parse_bool($row->intensivsprachkurs); 
            $preoutgoing->sprachkurs_von = $row->sprachkurs_von; 
            $preoutgoing->sprachkurs_bis = $row->sprachkurs_bis; 
            $preoutgoing->praktikum = $this->db_parse_bool($row->praktikum); 
            $preoutgoing->praktikum_von = $row->praktikum_von; 
            $preoutgoing->praktikum_bis = $row->praktikum_bis; 
            $preoutgoing->behinderungszuschuss = $this->db_parse_bool($row->behinderungszuschuss);
            $preoutgoing->studienbeihilfe = $this->db_parse_bool($row->studienbeihilfe);
            $preoutgoing->anmerkung_student = $row->anmerkung_student; 
            $preoutgoing->anmerkung_admin = $row->anmerkung_admin; 
            $preoutgoing->studienrichtung_gastuniversitaet = $row->studienrichtung_gastuniversitaet; 
            $preoutgoing->insertamum = $row->insertamum; 
            $preoutgoing->insertvon = $row->insertvon; 
            $preoutgoing->updateamum = $row->updateamum; 
            $preoutgoing->updatevon = $row->updatevon; 
            
            $this->result[] = $preoutgoing; 
        }       
        return true; 
    }
		

}
?>