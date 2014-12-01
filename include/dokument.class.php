<?php
/* Copyright (C) 2006 fhcomplete.org
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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class dokument extends basis_db
{
	public $new;
	public $result = array();
	
	//Tabellenspalten
	public $dokument_kurzbz;
	public $bezeichnung;
	public $studiengang_kz;
	
	public $prestudent_id;
	public $mitarbeiter_uid;
	public $datum;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $onlinebewerbung; 
	
	/**
	 * Konstruktor - Laedt optional ein Dokument
	 * @param $dokument_kurzbz
	 * @param $prestudent_id
	 */
	public function __construct($dokument_kurzbz=null, $prestudent_id=null)
	{
		parent::__construct();
		
		if(!is_null($dokument_kurzbz) && !is_null($prestudent_id))
			$this->load($dokument_kurzbz, $prestudent_id);
	}
	
	/**
	 * Laedt eine Dokument-Prestudent Zuordnung
	 * @param dokument_kurzbz
	 *        prestudent_id
	 */
	public function load($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokumentprestudent 
				WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)." AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{								
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->prestudent_id = $row->prestudent_id;
				$this->mitarbeiter_uid = $row->mitarbeiter_uid;
				$this->datum = $row->datum;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				return true;	
			}
			else 
			{
				$this->errormsg = 'Es wurde kein Datensatz gefunden';
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
	 * Prueft die Variablen vor dem Speichern 
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'Dokument_kurzbz muss angegeben werden';
			return false;
		}
		
		if($this->prestudent_id=='')
		{
			$this->errormsg = 'Prestudent_id muss angegeben werden';
			return false;
		}
		
		if(!is_numeric($this->prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		if($this->mitarbeiter_uid=='')
		{
			$this->errormsg = 'Mitarbeiter_uid muss angegeben werden';
			return false;
		}
		return true;
	}
	
	/**
	 * Speichert ein Beispiel in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if(is_null($new))
			$new = $this->new;
					
		//Variablen auf Gueltigkeit pruefen
		if(!$this->validate())
			return false;

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_dokumentprestudent(dokument_kurzbz, prestudent_id, mitarbeiter_uid, datum, updateamum, 
			        updatevon, insertamum, insertvon, ext_id) VALUES('.
			        $this->db_add_param($this->dokument_kurzbz).','.
			        $this->db_add_param($this->prestudent_id, FHC_INTEGER).','.
			        $this->db_add_param($this->mitarbeiter_uid).','.
			        $this->db_add_param($this->datum).','.
			        $this->db_add_param($this->updateamum).','.
			        $this->db_add_param($this->updatevon).','.
			        $this->db_add_param($this->insertamum).','.
			        $this->db_add_param($this->insertvon).','.
			        $this->db_add_param($this->ext_id, FHC_INTEGER).');';
		}
		else
		{
			//never used
			return false;
		}
		
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
	 * Speichert das Dokument in der Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function saveDokument($new=null)
	{
		if(is_null($new))
			$new = $this->new;
		
		if($this->dokument_kurzbz=='')
		{
			$this->errormsg = 'Dokument_kurzbz muss angegeben werden';
			return false;
		}

		if($new)
		{
			//Prüfung, ob Eintrag bereits vorhanden
			$qry='SELECT dokument_kurzbz FROM public.tbl_dokument 
				WHERE dokument_kurzbz='.$this->db_add_param($this->dokument_kurzbz);
			if($this->db_query($qry))
			{
				if($this->db_fetch_object())
				{
					$this->errormsg = 'Eintrag bereits vorhanden';
					return false;
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
				return false;
			}
		}

		if($new)
		{
			$qry = 'INSERT INTO public.tbl_dokument(dokument_kurzbz, bezeichnung, ext_id) VALUES('.
			        $this->db_add_param($this->dokument_kurzbz).','.
			        $this->db_add_param($this->bezeichnung).','.
			        $this->db_add_param($this->ext_id, FHC_INTEGER).');';
		}
		else
		{
			$qry = 'UPDATE public.tbl_dokument SET '.
					'bezeichnung = '.$this->db_add_param($this->bezeichnung).
					'WHERE dokument_kurzbz = '.$this->db_add_param($this->dokument_kurzbz);
		}
		
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
	 * Loescht eine Zuordnung
	 * @param dokument_kurzbz
	 *        prestudent_id
	 */
	public function delete($dokument_kurzbz, $prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_dokumentprestudent 
				WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz)." AND prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER).";";
		
		if($this->db_query($qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}
	
	/**
	 * Loescht eine Zuordnung
	 * @param dokument_kurzbz
	 *        stg_kz
	 */
	public function deleteDokumentStg($dokument_kurzbz, $stg_kz)
	{
		if(!is_numeric($stg_kz))
		{
			$this->errormsg = 'stg_kz muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "DELETE FROM public.tbl_dokumentstudiengang 
				WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz)." AND studiengang_kz=".$this->db_add_param($stg_kz, FHC_INTEGER).";";
		
		if($this->db_query($qry))
			return true;
		else 	
		{
			$this->errormsg = 'Fehler beim Loeschen der Zuteilung';
			return false;
		}
	}
	
	/**
	 * Laedt alle Dokumente eines Prestudenten die
	 * er bereits abgegeben hat
	 * @param prestudent_id
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getPrestudentDokumente($prestudent_id)
	{
		if(!is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id muss eine gueltige Zahl sein';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokumentprestudent JOIN public.tbl_dokument USING(dokument_kurzbz) 
				WHERE prestudent_id=".$this->db_add_param($prestudent_id, FHC_INTEGER)." ORDER BY dokument_kurzbz;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();
				
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->prestudent_id = $row->prestudent_id;
				$dok->mitarbeiter_uid = $row->mitarbeiter_uid;
				$dok->datum = $row->datum;
				$dok->updateamum = $row->updateamum;
				$dok->updatevon = $row->updatevon;
				$dok->insertamum = $row->insertamum;
				$dok->insertvon = $row->insertvon;
				$dok->ext_id = $row->ext_id;
				
				$this->result[] = $dok;
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
	 * Laedt alle Dokumente fuer einen Stg die der
	 * Prestudent noch nicht abgegeben hat
	 * @param studiengang_kz
	 *        prestudent_id
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getFehlendeDokumente($studiengang_kz, $prestudent_id=null)
	{
		if(!is_null($prestudent_id) && !is_numeric($prestudent_id))
		{
			$this->errormsg = 'Prestudent_id ist ungueltig';
			return false;
		}
		
		if(!is_numeric($studiengang_kz))
		{
			$this->errormsg = 'Studiengang_kz ist ungueltig';
			return false;
		}
		
		$qry = "SELECT * FROM public.tbl_dokument JOIN public.tbl_dokumentstudiengang USING(dokument_kurzbz) 
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER);

		if(!is_null($prestudent_id))
		{
			$qry.="	AND dokument_kurzbz NOT IN (
					SELECT dokument_kurzbz FROM public.tbl_dokumentprestudent WHERE prestudent_id=".$this->db_add_param($prestudent_id,FHC_INTEGER).")";
		}
		
		$qry.=" ORDER BY dokument_kurzbz;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung); 
				$this->result[] = $dok;
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
	 * Liefert die Dokumente eines Studienganges
	 * @param $studiengang_kz
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getDokumente($studiengang_kz)
	{
		$qry = "SELECT * FROM public.tbl_dokumentstudiengang JOIN public.tbl_dokument USING(dokument_kurzbz) 
				WHERE studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();
				
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				$dok->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				
				$this->result[] = $dok;
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
	 * Liefert alle Dokumenttypen
	 * @return true wenn ok false im Fehlerfall
	 */
	public function getAllDokumente()
	{
		$qry = "SELECT * FROM public.tbl_dokument ORDER BY bezeichnung;";
		
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$dok = new dokument();
				
				$dok->dokument_kurzbz = $row->dokument_kurzbz;
				$dok->bezeichnung = $row->bezeichnung;
				
				$this->result[] = $dok;
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
	 * Laedt die Dokument Studiengang Zuordnung
	 *
	 * @param $dokument_kurzbz
	 * @param $studiengang_kz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadDokumentStudiengang($dokument_kurzbz, $studiengang_kz)
	{
		$qry="SELECT * FROM public.tbl_dokumentstudiengang 
				WHERE
					studiengang_kz=".$this->db_add_param($studiengang_kz)."
					AND dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->dokument_kurzbz = $row->dokument_kurzbz;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->onlinebewerbung = $this->db_parse_bool($row->onlinebewerbung);
				return true;
			}
			else
			{
				$this->errormsg='Fehler beim Laden der Daten';
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
	 * Prueft ob die Zuordnung Dokument zu Studiengang bereits vorhanden ist
	 * @param $dokument_kurzbz
	 * @param $studiengang_kz
	 * @return true wenn vorhanden, false wenn nicht vorhanden
	 */
	public function existsDokumentStudiengang($dokument_kurzbz, $studiengang_kz)
	{
		$qry='SELECT 
				dokument_kurzbz 
			FROM 
				public.tbl_dokumentstudiengang 
			WHERE 
				dokument_kurzbz='.$this->db_add_param($dokument_kurzbz).' 
				AND studiengang_kz='.$this->db_add_param($studiengang_kz,FHC_INTEGER);

		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Durchführen der Datenbankabfrage';
			return false;
		}
	}

	/**
	 * Speichert die Zuordnung Dokument zu Studiengang
	 * @return true wenn ok false im Fehlerfall
	 */
	public function saveDokumentStudiengang()
	{
		if(!$this->existsDokumentStudiengang($this->dokument_kurzbz, $this->studiengang_kz))
		{
			$qry='INSERT INTO public.tbl_dokumentstudiengang (dokument_kurzbz, studiengang_kz, onlinebewerbung) 
				VALUES ('.
					$this->db_add_param($this->dokument_kurzbz).','.
					$this->db_add_param($this->studiengang_kz,FHC_INTEGER).','.
					$this->db_add_param($this->onlinebewerbung,FHC_BOOLEAN).')';
		}
		else
		{
			$qry = 'UPDATE public.tbl_dokumentstudiengang SET
						onlinebewerbung='.$this->db_add_param($this->onlinebewerbung,FHC_BOOLEAN).'
					WHERE
						dokument_kurzbz='.$this->db_add_param($this->dokument_kurzbz).'
						AND studiengang_kz='.$this->db_add_param($this->studiengang_kz);
		}

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Zuordnung';
			return false;
		}
	}

	/**
	 * Laedt einen Dokumenttyp
	 * @param $dokument_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadDokumenttyp($dokument_kurzbz)
	{
		$qry="SELECT * FROM public.tbl_dokument 
				WHERE dokument_kurzbz =".$this->db_add_param($dokument_kurzbz).";";
		
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->dokument_kurzbz = $row->dokument_kurzbz; 
				$this->bezeichnung = $row->bezeichnung; 
				return true;
			}
		}
		else 
		{
			$this->errormsg ='Fehler beim Laden der Daten';
			return false;
		}
	}
    
    /**
     * Liefert alle Dokumente die eine Person abzugeben hat
     * ist notwendig um bei einer Bewerbung bei mehreren Studiengängen zu wissen was der Student im gesamten abzugeben hat
     * @param $person_id 
	 * @param onlinebewerbung
     */
    public function getAllDokumenteForPerson($person_id, $onlinebewerbung= false)
    {
        $qry = "SELECT distinct(dokument_kurzbz), bezeichnung FROM public.tbl_dokumentstudiengang 
            JOIN public.tbl_prestudent using (studiengang_kz) 
            JOIN public.tbl_dokument using (dokument_kurzbz)
            WHERE person_id =".$this->db_add_param($person_id, FHC_INTEGER); 
		
		if($onlinebewerbung)
			$qry.= " AND onlinebewerbung is true; "; 
		else
			$qry.=";";
        
        if($result = $this->db_query($qry))
        {
            while($row = $this->db_fetch_object($result))
            {
                $dok = new dokument(); 
                $dok->dokument_kurzbz = $row->dokument_kurzbz; 
                $dok->bezeichnung = $row->bezeichnung; 
                
                $this->result[] = $dok; 
            }
            return true; 
        }
        else
        {
            $this->errormsg="Fehler bei der Abfrage aufgetreten"; 
            return false; 
        }
        
    }
    
	/**
	 * Loescht einen Dokumenttyp
	 * @param $dokument_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	function deleteDokumenttyp($dokument_kurzbz)
	{
		$qry="DELETE FROM public.tbl_dokument WHERE dokument_kurzbz=".$this->db_add_param($dokument_kurzbz);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg='Löschen fehlgeschlagen';
			return false;
		}
	}   
}
?>
