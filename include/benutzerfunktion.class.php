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
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at>.
 */
/**
 * Klasse benutzerfunktion (FAS-Online)
 * @create 04-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class benutzerfunktion extends basis_db
{

	public $new;     			//  boolean
	public $result = array(); 	//  benutzerfunktion Objekt

	//Tabellenspalten
	public $benutzerfunktion_id;//  serial
	public $fachbereich_kurzbz;	//  integer
	public $uid;				//  varchar(16)
	public $oe_kurzbz;			//  varchar(32)
	public $funktion_kurzbz;	//  varchar(16)
	public $updateamum;			//  timestamp
	public $updatevon=0;		//  string
	public $insertamum;			//  timestamp
	public $insertvon=0;		//  string
	public $ext_id;				//  bigint
	public $semester;			//  smallint
	public $datum_von;			//  date
	public $datum_bis;			//  date
	public $bezeichnung;		//  varchar(64)
	public $wochenstunden; 		//  numeric(5,2)


	/**
	 * Konstruktor
	 * @param $conn Connection zur DB
	 *        $benutzerfunktion_id ID der zu ladenden Funktion
	 */
	public function __construct($benutzerfunktion_id=null)
	{
		parent::__construct();

		if($benutzerfunktion_id != null)
			$this->load($benutzerfunktion_id);
	}

	/**
	 * Laedt alle verfuegbaren Benutzerfunktionen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll()
	{
		$qry = 'SELECT * FROM public.tbl_benutzerfunktion ORDER BY benutzerfunktion_id;';

		if(!$res = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden der Datensaetze';
			return false;
		}

		while($row = $this->db_fetch_object($res))
		{
			$pfunktion_obj = new benutzerfunktion();

			$pfunktion_obj->benutzerfunktion_id = $row->benutzerfunktion_id;
			$pfunktion_obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$pfunktion_obj->uid = $row->uid;
			$pfunktion_obj->studiengang_kz = $row->studiengang_kz;
			$pfunktion_obj->funktion_kurzbz = $row->funtion_kurzbz;
			$pfunktion_obj->insertamum = $row->insertamum;
			$pfunktion_obj->insertvon = $row->insertvon;
			$pfunktion_obj->updateamum = $row->updateamum;
			$pfunktion_obj->updatevon = $row->updatevon;
			$pfunktion_obj->semester = $row->semester;
			$pfunktion_obj->datum_von = $row->datum_von;
			$pfunktion_obj->datum_bis = $row->datum_bis;
			$pfunktion_obj->bezeichnung = $row->bezeichnung;
			$pfunktion_obj->wochenstunden = $row->wochenstunden;

			$this->result[] = $pfunktion_obj;
		}
		return true;
	}

	/**
	 * Prueft ob der Benutzer $uid die
	 * Funktion $benutzerfunktion hat. Der optionale Parameter $gueltig prüft zusätzlich auf das Gültigkeitsdatum.
	 * @param string $uid
	 * @param string $benutzerfunktion
	 * @param boolean $gueltig. Default=false. Wenn true, wird zusätzlich das Gültigkeitsdatum geprüft
	 */
	public function benutzerfunktion_exists($uid, $benutzerfunktion, $gueltig = false)
	{
		$qry = "SELECT count(*) as anzahl FROM public.tbl_benutzerfunktion
				WHERE uid=".$this->db_add_param($uid)." AND funktion_kurzbz=".$this->db_add_param($benutzerfunktion);
		
		if ($gueltig = TRUE)
			$qry .= ' AND (datum_von IS NULL OR datum_von <= now()) AND (datum_bis IS NULL OR datum_bis >= now()) ';

		if($row = $this->db_fetch_object($this->db_query($qry)))
		{
			if($row->anzahl>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Benutzerfunktionen';
			return false;
		}
	}

	/**
	 * Laedt eine BenutzerFunktion
	 * @param uid, funktion_kurzbz, oe_kurzbz
	 * @return false wenn nicht vorhanden oder fehler
	 *         sonst true
	 */
	public function getBenutzerFunktion($uid, $funktion_kurzbz, $oe_kurzbz)
	{
		$qry = "SELECT 
					bfunk.*, stg.studiengang_kz
				FROM 
					public.tbl_benutzerfunktion AS bfunk
					INNER JOIN public.tbl_studiengang AS stg ON(stg.oe_kurzbz = bfunk.oe_kurzbz)
				WHERE 
					bfunk.uid=".$this->db_add_param($uid)." 
					AND bfunk.funktion_kurzbz=".$this->db_add_param($funktion_kurzbz)."
					AND bfunk.oe_kurzbz=".$this->db_add_param($oe_kurzbz);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->benutzerfunktion_id = $row->benutzerfunktion_id;
				$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$this->uid = $row->uid;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->funktion_kurzbz = $row->funktion_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->semester = $row->semester;
				$this->datum_von = $row->datum_von;
				$this->datum_bis = $row->datum_bis;
				$this->bezeichnung = $row->bezeichnung;
				$this->wochenstunden = $row->wochenstunden;

				return true;
			}
			else
			{
				$this->errormsg = "Benutzerfunktion wurde nicht gefunden";
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Benutzerfunktionen';
			return false;
		}
	}

	/**
	 * Laedt mehrere BenutzerFunktionen
	 * @param funktion_kurzbz, studiengang_kz, semester
	 * @return false wenn nicht vorhanden oder fehler
	 *         sonst true
	 */
	public function getBenutzerFunktionen($funktion_kurzbz, $oe_kurzbz='', $semester='', $uid='')
	{
		$qry = "SELECT * FROM public.tbl_benutzerfunktion
				WHERE funktion_kurzbz=".$this->db_add_param($funktion_kurzbz)."
				AND (datum_bis >= now() OR datum_bis IS NULL)
				AND (datum_von <= now() OR datum_von IS NULL)";

		if($oe_kurzbz!='')
			$qry.=" AND oe_kurzbz=".$this->db_add_param($oe_kurzbz);
		if($semester!='')
			$qry.=" AND semester=".$this->db_add_param($semester);
		if($uid!='')
			$qry.=" AND uid=".$this->db_add_param($uid);

		$qry.=" ORDER BY funktion_kurzbz, oe_kurzbz, semester";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzerfunktion();

				$obj->benutzerfunktion_id = $row->benutzerfunktion_id;
				$obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$obj->uid = $row->uid;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->semester = $row->semester;
				$obj->datum_von = $row->datum_von;
				$obj->datum_bis = $row->datum_bis;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->wochenstunden = $row->wochenstunden;

				$this->result[] = $obj;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Benutzerfunktionen';
			return false;
		}
	}

	/**
	 * Laedt eine Benutzerfunktion
	 * @param $bnutzerfunktion_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($benutzerfunktion_id)
	{
		if($benutzerfunktion_id == '' || !is_numeric($benutzerfunktion_id))
		{
			$this->errormsg = 'benutzerfunktion_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM public.tbl_benutzerfunktion WHERE benutzerfunktion_id =".$this->db_add_param($benutzerfunktion_id, FHC_INTEGER);

		if(!$res = $this->db_query($qry))
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

		if($row=$this->db_fetch_object($res))
		{
			$this->benutzerfunktion_id = $row->benutzerfunktion_id;
			$this->fachbereich_kurzbz = $row->fachbereich_kurzbz;
			$this->uid = $row->uid;
			$this->oe_kurzbz = $row->oe_kurzbz;
			$this->funktion_kurzbz = $row->funktion_kurzbz;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->updatevon = $row->updatevon;
			$this->semester = $row->semester;
			$this->datum_von = $row->datum_von;
			$this->datum_bis = $row->datum_bis;
			$this->bezeichnung = $row->bezeichnung;
			$this->wochenstunden = $row->wochenstunden;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}

	/**
	 * Loescht einen Datensatz
	 * @param $fbenutzerfunktion_id id des Datensatzes der geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($benutzerfunktion_id)
	{
		if(!is_numeric($benutzerfunktion_id) || $benutzerfunktion_id=='')
		{
			$this->errormsg='Benutzerfunktion_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "DELETE FROM public.tbl_benutzerfunktion WHERE benutzerfunktion_id=".$this->db_add_param($benutzerfunktion_id, FHC_INTEGER);
		if(!$this->db_query($qry))
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		else
			return true;
	}

	/**
	 * Speichert den aktuellen Datensatz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new = $this->new;

		if($new)
		{
			//Neuen Datensatz anlegen
			//Pruefen ob uid vorhanden
			$qry = "SELECT uid FROM public.tbl_benutzer WHERE uid = ".$this->db_add_param($this->uid);
			if(!$resx = $this->db_query($qry))
			{
				$this->errormsg = 'Fehler beim Laden des Datensatzes';
				return false;
			}
			else
			{
				if ($this->db_num_rows($resx)==0)
				{
					$this->errormsg = "uid <b>$this->uid</b> in Tabelle tbl_benutzer nicht gefunden!";
					return false;
				}
			}
			$qry = 'BEGIN;INSERT INTO public.tbl_benutzerfunktion (fachbereich_kurzbz, uid, oe_kurzbz, funktion_kurzbz, insertamum, insertvon,
				updateamum, updatevon, semester, datum_von, datum_bis, bezeichnung, wochenstunden) VALUES ('.
				$this->db_add_param($this->fachbereich_kurzbz).', '.
				$this->db_add_param($this->uid).', '.
				$this->db_add_param($this->oe_kurzbz).', '.
				$this->db_add_param($this->funktion_kurzbz).', '.
				$this->db_add_param($this->insertamum).', '.
				$this->db_add_param($this->insertvon).', '.
				$this->db_add_param($this->updateamum).', '.
				$this->db_add_param($this->updatevon).', '.
				$this->db_add_param($this->semester).','.
				$this->db_add_param($this->datum_von).','.
				$this->db_add_param($this->datum_bis).','.
				$this->db_add_param($this->bezeichnung).','.
				$this->db_add_param($this->wochenstunden).'); ';
		}
		else
		{
			//bestehenden Datensatz akualisieren

			//Pruefen ob benutzerfunktion_id eine gueltige Zahl ist
			if(!is_numeric($this->benutzerfunktion_id) || $this->benutzerfunktion_id == '')
			{
				$this->errormsg = 'benutzerfunktion_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry = 'UPDATE public.tbl_benutzerfunktion SET '.
				'benutzerfunktion_id='.$this->db_add_param($this->benutzerfunktion_id, FHC_INTEGER).', '.
				'fachbereich_kurzbz='.$this->db_add_param($this->fachbereich_kurzbz).', '.
				'uid='.$this->db_add_param($this->uid).', '.
				'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
				'funktion_kurzbz='.$this->db_add_param($this->funktion_kurzbz).', '.
				'insertamum='.$this->db_add_param($this->insertamum).', '.
				'insertvon='.$this->db_add_param($this->insertvon).', '.
				'updateamum='.$this->db_add_param($this->updateamum).', '.
				'updatevon='.$this->db_add_param($this->updatevon).',  '.
				'datum_von='.$this->db_add_param($this->datum_von).',  '.
				'datum_bis='.$this->db_add_param($this->datum_bis).',  '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'semester='.$this->db_add_param($this->semester).',  '.
				'wochenstunden='.$this->db_add_param($this->wochenstunden).' '.
				'WHERE benutzerfunktion_id = '.$this->db_add_param($this->benutzerfunktion_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($new)
			{
				//Sequence Auslesen
				$qry = "SELECT currval('public.tbl_benutzerfunktion_benutzerfunktion_id_seq') as id";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						$this->benutzerfunktion_id = $row->id;
						$this->db_query('COMMIT;');
					}
					else
					{
						$this->errormsg = 'Fehler beim Auslesen der Sequence';
						$this->db_query('ROLLBACK');
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					$this->db_query('ROLLBACK');
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
	 * Lädt alle Benutzerfunktionen zu einer UID
	 * @param type $uid UID des Mitarbeiters
	 * @param type $funktion_kurzbz OPTIONAL Kurzbezeichnung der Funktion
	 * @param type $startZeitraum OPTIONAL Start Zeitraum in dem die Funktion aktiv ist
	 * @param type $endeZeitraum OPTIONAL Ende Zeitraum in dem die Funktion aktiv ist
	 * @return boolean
	 */
	public function getBenutzerFunktionByUid($uid, $funktion_kurzbz=null, $startZeitraum=null, $endeZeitraum=null)
	{
	    $qry = "SELECT * FROM public.tbl_benutzerfunktion
			    WHERE uid=".$this->db_add_param($uid);
	    if(!is_null($funktion_kurzbz))
	    {
		$qry .= ' AND funktion_kurzbz='.$this->db_add_param($funktion_kurzbz);
	    }
	    if(!is_null($startZeitraum))
	    {
		$qry .=' AND (datum_bis IS NULL OR datum_bis >='.$this->db_add_param($startZeitraum).")";
	    }
	    if(!is_null($endeZeitraum))
	    {
		$qry .=' AND (datum_von IS NULL OR datum_von <='.$this->db_add_param($endeZeitraum).")";
	    }

	    $qry .= ";";
	    if($result = $this->db_query($qry))
	    {
		while($row = $this->db_fetch_object($result))
		{
		    $obj = new benutzerfunktion();

		    $obj->benutzerfunktion_id = $row->benutzerfunktion_id;
		    $obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
		    $obj->uid = $row->uid;
		    $obj->oe_kurzbz = $row->oe_kurzbz;
		    $obj->funktion_kurzbz = $row->funktion_kurzbz;
		    $obj->insertamum = $row->insertamum;
		    $obj->insertvon = $row->insertvon;
		    $obj->updateamum = $row->updateamum;
		    $obj->updatevon = $row->updatevon;
		    $obj->semester = $row->semester;
		    $obj->datum_von = $row->datum_von;
		    $obj->datum_bis = $row->datum_bis;
		    $obj->bezeichnung = $row->bezeichnung;
			$obj->wochenstunden = $row->wochenstunden;

		    $this->result[] = $obj;

		}
		return true;
	    }
	    else
	    {
		$this->errormsg = 'Fehler beim Laden der Benutzerfunktionen';
		return false;
	    }
	}

	/**
	 * Laedt alle Benutzerfunktionen in einer Organisationseinheit
	 * @param $oe_kurzbz
	 * @param $funktionen_kurzbz (optional) Funktionen in der OE, kommagetrennt
	 * @param type $startZeitraum OPTIONAL Start Zeitraum in dem die Funktion aktiv ist
	 * @param type $endeZeitraum OPTIONAL Ende Zeitraum in dem die Funktion aktiv ist
	 * @return false wenn nicht vorhanden oder fehler
	 *         sonst true
	 */
	public function getOeFunktionen($oe_kurzbz, $funktionen_kurzbz=null, $startZeitraum=null, $endeZeitraum=null)
	{
		$qry = "SELECT * FROM public.tbl_benutzerfunktion
				WHERE oe_kurzbz=".$this->db_add_param($oe_kurzbz);

		if(!is_null($funktionen_kurzbz))
	    {
	    	$funktionen_kurzbz = explode(',', $funktionen_kurzbz);
	    	$qry .= ' AND funktion_kurzbz IN('.$this->implode4SQL($funktionen_kurzbz).')';
	    }
	    if(!is_null($startZeitraum))
	    {
			$qry .=' AND (datum_bis IS NULL OR datum_bis >='.$this->db_add_param($startZeitraum).')';
	    }
	    if(!is_null($endeZeitraum))
	    {
			$qry .=' AND (datum_von IS NULL OR datum_von <='.$this->db_add_param($endeZeitraum).')';
	    }

		$qry.=" ORDER BY bezeichnung, uid";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new benutzerfunktion();

				$obj->benutzerfunktion_id = $row->benutzerfunktion_id;
				$obj->fachbereich_kurzbz = $row->fachbereich_kurzbz;
				$obj->uid = $row->uid;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->funktion_kurzbz = $row->funktion_kurzbz;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->semester = $row->semester;
				$obj->datum_von = $row->datum_von;
				$obj->datum_bis = $row->datum_bis;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->wochenstunden = $row->wochenstunden;

				$this->result[] = $obj;

			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der OE-Funktionen';
			return false;
		}
	}

	
}
?>
