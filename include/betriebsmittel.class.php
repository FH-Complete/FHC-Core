<?php
/* Copyright (C) 2007 Technikum-Wien
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
 * Authors: Christian Paminger 	< christian.paminger@technikum-wien.at >
 *          Andreas Oesterreicher 	< andreas.oesterreicher@technikum-wien.at >
 *          Rudolf Hangl 		< rudolf.hangl@technikum-wien.at >
 *          Gerald Simane-Sequens 	< gerald.simane-sequens@technikum-wien.at >
 */

/**
 * Klasse Betriebsmittel
 * @create 22-01-2007
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class betriebsmittel extends basis_db
{
	public $schema_inventar='wawi';
#	private $schema_inventar='public';	
	public $schema_wawi='public';

	public $default_afa_jahre=5;
	
	
	public $debug=false;   	// boolean
	public $new;       		// boolean
	public $result;

	
	
	//Tabellenspalten
	public $betriebsmittel_id;	// integer
	public $betriebsmitteltyp;	// string
	public $nummer;				// string
	public $nummerintern;		// string
	public $reservieren;		// boolean
	public $ort_kurzbz;			// string
	public $ext_id;				// integer
	public $insertamum;			// timestamp
	public $insertvon;			// string
	public $updateamum;			// timestamp
	public $updatevon;			// string
	// Neu wegen Inventar seq 2009-12
	public $beschreibung;		// string
	public $oe_kurzbz;			// string
	public $hersteller;			// string

	public $seriennummer;		// string
	public $bestellung_id;		// integer
	public $bestelldetail_id;	// integer

	public $afa;				// string

	public $verwendung;			// string
	public $anmerkung;			// string
	public $leasing_bis;		// date


	/**
	 * Konstruktor
	 * @param $betriebsmittel_id ID des Betrtiebsmittels, das geladen werden soll (Default=null)
	 */
	public function __construct($betriebsmittel_id=null)
	{
		parent::__construct();

		if(!is_null($betriebsmittel_id))
			$this->load($betriebsmittel_id);
	}

	/**
	 * Laedt das Betriebsmittel mit der ID $betriebsmittel_id
	 * @param  $betriebsmittel_id ID des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($betriebsmittel_id)
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM ".$this->schema_inventar.".tbl_betriebsmittel WHERE betriebsmittel_id='".addslashes($betriebsmittel_id)."'";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{

				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
	// Neu wegen Inventar seq 2009-12
				$this->beschreibung = $row->beschreibung;		// string
				$this->oe_kurzbz = $row->oe_kurzbz;			// string
				$this->hersteller = $row->hersteller;			// string
				$this->seriennummer = $row->seriennummer;		// string
				$this->bestellung_id = $row->bestellung_id;		// integer
				$this->bestelldetail_id = $row->bestelldetail_id;	// integer
				$this->afa = $row->afa;				// string
				$this->verwendung = $row->verwendung;		// string
				$this->anmerkung = $row->anmerkung;		// string

				$this->leasing_bis = $row->leasing_bis;		// string

				return $this->result=$row;
			}
			else
			{
				$this->errormsg = 'Betriebsmittel wurde nicht gefunden '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}
	/**
	 * Prueft die Daten vor dem Speichern
	 * auf Gueltigkeit
	 */
	protected function validate()
	{
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $betriebsmittel_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		if($new==null)
			$new=$this->new;

		if(!$this->validate())
			return false;
		if ($this->nummer)
			$this->nummer=trim($this->nummer);
		

		
		$this->afa=(!isset($this->afa) || empty($this->afa)?$this->default_afa_jahre:$this->afa);
		if($new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO '.$this->schema_inventar.'.tbl_betriebsmittel (beschreibung, betriebsmitteltyp, nummer
				, nummerintern, reservieren, ort_kurzbz
				,ext_id, insertamum, insertvon, updateamum, updatevon,oe_kurzbz,hersteller,seriennummer
				,bestellung_id,bestelldetail_id,afa,verwendung,anmerkung,leasing_bis) VALUES('.
			     $this->addslashes($this->beschreibung).', '.
			     $this->addslashes($this->betriebsmitteltyp).', '.
			     $this->addslashes($this->nummer).', '.
			     $this->addslashes($this->nummerintern).', '.
			     ($this->reservieren?'true':'false').', '.
			     $this->addslashes($this->ort_kurzbz).', '.
			     $this->addslashes($this->ext_id).', '.
			     ($this->insertamum?$this->addslashes($this->insertamum):'now()').', '.
			     $this->addslashes($this->insertvon).', '.
			    ($this->updateamum?$this->addslashes($this->updateamum):'now()').', '.
			     $this->addslashes((empty($this->updatevon)?$this->updatevon:$this->insertvon)).', '.
				 $this->addslashes($this->oe_kurzbz).', '.
				 $this->addslashes($this->hersteller).', '.
				 $this->addslashes($this->seriennummer).', '.
				 $this->addslashes($this->bestellung_id).', '.
				 $this->addslashes($this->bestelldetail_id).', '.
				 $this->addslashes($this->afa).', '.
				 $this->addslashes($this->verwendung).', '.
				 $this->addslashes($this->anmerkung) .', '.
				 ($this->leasing_bis?$this->addslashes($this->leasing_bis):'null') .');' ;
				
		}
		else
		{
			if(!is_numeric($this->betriebsmittel_id))
			{
				$this->errormsg = 'Betriebsmittel_id muss eine gueltige Zahl sein';
				return false;
			}

			$qry='UPDATE '.$this->schema_inventar.'.tbl_betriebsmittel SET '.
				'betriebsmitteltyp='.$this->addslashes($this->betriebsmitteltyp).', '.
				'beschreibung='.$this->addslashes($this->beschreibung).', '.
				'nummer='.$this->addslashes($this->nummer).', '.
				'nummerintern='.$this->addslashes($this->nummerintern).', '.
				'reservieren='.($this->reservieren?'true':'false').', '.
				'ort_kurzbz='.$this->addslashes($this->ort_kurzbz).', '.
				'ext_id='.$this->addslashes($this->ext_id).', '.
				'updateamum='.($this->updateamum?$this->addslashes($this->updateamum):'now()').', '.
				'updatevon='.$this->addslashes($this->updatevon).', '.
	// Neu wegen Inventar seq 2009-12
				'oe_kurzbz='.$this->addslashes($this->oe_kurzbz).', '.
				'hersteller='.$this->addslashes($this->hersteller).', '.
				'seriennummer='.$this->addslashes($this->seriennummer).', '.
				'bestellung_id='.$this->addslashes($this->bestellung_id).', '.
				'bestelldetail_id='.$this->addslashes($this->bestelldetail_id).', '.
				'afa='.($this->afa && is_numeric($this->afa)?$this->afa:$this->default_afa_jahre).', '.
				'verwendung='.$this->addslashes($this->verwendung).', '.
				'anmerkung='.$this->addslashes($this->anmerkung).', '.
				'leasing_bis='.($this->leasing_bis?$this->addslashes($this->leasing_bis):'null').' '.
				'WHERE betriebsmittel_id='.$this->addslashes($this->betriebsmittel_id).';';
		}
##echo $qry;
		if($this->db_query($qry))
		{
			if($new)
			{
				$qry = "SELECT currval('".$this->schema_inventar.".tbl_betriebsmittel_betriebsmittel_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->betriebsmittel_id = $row->id;
					}
					else
					{
						$this->errormsg = 'Fehler beim Lesen der Sequence '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
						return false;
					}
				}
				else
				{
					$this->errormsg = 'Fehler beim Lesen der Sequence '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
					return false;
				}
			}
			return $this->betriebsmittel_id;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Betriebsmittel-Datensatzes '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}
	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $betriebsmittel_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($betriebsmittel_id)
	{
		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM ".$this->schema_inventar.".tbl_betriebsmittel WHERE betriebsmittel_id='".addslashes($betriebsmittel_id)."'";
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}

	/**
	 * Laedt die Betriebsmittel
	 *
	 * @param $betriebsmitteltyp
	 * @param $nummer
	 * @return boolean
	 */
	public function getBetriebsmittel($betriebsmitteltyp, $nummer)
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		$qry = '';
		$qry.= 'SELECT * ';
		$qry.= ' FROM '.$this->schema_inventar.'.tbl_betriebsmittel ';
		$qry.= " WHERE betriebsmitteltyp='".addslashes($betriebsmitteltyp)."' AND nummer='".addslashes($nummer)."'";
		$qry.= " ORDER BY updateamum DESC";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bm = new betriebsmittel();

				$bm->betriebsmittel_id = $row->betriebsmittel_id;
				$bm->beschreibung = $row->beschreibung;
				$bm->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bm->nummer = $row->nummer;
				$bm->nummerintern = $row->nummerintern;
				$bm->reservieren = $row->reservieren;
				$bm->ort_kurzbz = $row->ort_kurzbz;
				$bm->updateamum = $row->updateamum;
				$bm->updatevon = $row->updatevon;
				$bm->insertamum = $row->insertamum;
				$bm->insertvon = $row->insertvon;
	// Neu wegen Inventar seq 2009-12
				$bm->beschreibung = $row->beschreibung;		// string
				$bm->oe_kurzbz = $row->oe_kurzbz;			// string
				$bm->hersteller = $row->hersteller;			// string
				$bm->seriennummer = $row->seriennummer;		// string
				$bm->bestellung_id = $row->bestellung_id;		// integer
				$bm->bestelldetail_id = $row->bestelldetail_id;	// integer
				$bm->afa = $row->afa;				// string
				$bm->verwendung = $row->verwendung;		// string
				$bm->anmerkung = $row->anmerkung;		// string
				$bm->leasing_bis = $row->leasing_bis;		// date

				$this->result[] = $bm;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}

	/**
	 * Laedt ALLE Betriebsmittel
	 *
	 * @return boolean
	 */
	public function getALL()
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		$qry = '';
		$qry.= 'SELECT * ';
		$qry.= ' FROM '.$this->schema_inventar.'.tbl_betriebsmittel ';
		$qry.= " ORDER BY nummer";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$bm = new betriebsmittel();

				$bm->betriebsmittel_id = $row->betriebsmittel_id;
				$bm->beschreibung = $row->beschreibung;
				$bm->betriebsmitteltyp = $row->betriebsmitteltyp;
				$bm->nummer = $row->nummer;
				$bm->nummerintern = $row->nummerintern;
				$bm->reservieren = $row->reservieren;
				$bm->ort_kurzbz = $row->ort_kurzbz;
				$bm->updateamum = $row->updateamum;
				$bm->updatevon = $row->updatevon;
				$bm->insertamum = $row->insertamum;
				$bm->insertvon = $row->insertvon;
	// Neu wegen Inventar seq 2009-12
				$bm->oe_kurzbz = $row->oe_kurzbz;			// string
				$bm->hersteller = $row->hersteller;			// string
				$bm->seriennummer = $row->seriennummer;		// string
				$bm->bestellung_id = $row->bestellung_id;		// integer
				$bm->bestelldetail_id = $row->bestelldetail_id;	// integer
				$bm->afa = $row->afa;				// string
				$bm->verwendung = $row->verwendung;		// string
				$bm->anmerkung = $row->anmerkung;		// string
				$bm->leasing_bis = $row->leasing_bis;		// date
				$this->result[] = $bm;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');;
			return false;
		}
	}



// ------------------------------------------------------------------------------------------
//	Inventardaten lesen
// ------------------------------------------------------------------------------------------
	/**
	 * Laedt das Betriebsmittel mit der Nummer des Betriebsmittels $nummer
	 * @param  $nummer Nummer des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_nummer($nummer)
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		$qry='';
		$qry.=' select * ';
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel ';
		$qry.=' where nummer='.$this->addslashes($nummer);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
	// Neu wegen Inventar seq 2009-12
				$this->beschreibung = $row->beschreibung;			// string
				$this->oe_kurzbz = $row->oe_kurzbz;					// string
				$this->hersteller = $row->hersteller;				// string
				$this->seriennummer = $row->seriennummer;			// string
				$this->bestellung_id = $row->bestellung_id;			// integer
				$this->bestelldetail_id = $row->bestelldetail_id;	// integer
				$this->afa = $row->afa;								// string
				$this->verwendung = $row->verwendung;				// string
				$this->anmerkung = $row->anmerkung;					// string
				$this->leasing_bis = $row->leasing_bis;				// date
				return $this->result=$row;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br /> $qry <br />":'');
			return false;
		}
	}
	/**
	 * Laedt die Organisation des Betriebsmittels $bestellung_id
	 * @param  $bestellung_id Bestellnummer des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_betriebsmittel_oe($betriebsmittel_id)
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		if(!is_numeric($betriebsmittel_id))
		{
			$this->errormsg = 'Betriebsmittel_id ist ungueltig';
			return false;
		}

		$qry = "SELECT * FROM ".$this->schema_inventar.".tbl_betriebsmittel WHERE betriebsmittel_id='".addslashes($betriebsmittel_id)."'";
		if($res=$this->db_query($qry))
		{
			if($row = $this->db_fetch_object($res))
			{
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
	// Neu wegen Inventar seq 2009-12
				$this->beschreibung = $row->beschreibung;		// string
				$this->hersteller = $row->hersteller;			// string
				$this->seriennummer = $row->seriennummer;		// string
				$this->bestellung_id = $row->bestellung_id;		// integer
				$this->bestelldetail_id = $row->bestelldetail_id;	// integer
				$this->afa = $row->afa;						// string
				$this->verwendung = $row->verwendung;		// string
				$this->anmerkung = $row->anmerkung;			// string
				$this->leasing_bis = $row->leasing_bis;		// string
				$this->oe_kurzbz = trim($row->oe_kurzbz);	// string

				if (empty($this->oe_kurzbz))
				{
					$qry="SELECT uid "; 
					$qry.=" FROM ".$this->schema_inventar.".tbl_betriebsmittel  ";
					$qry.=" JOIN ".$this->schema_inventar.".tbl_betriebsmittelperson USING(betriebsmittel_id) ";
					$qry.=" JOIN campus.vw_benutzer USING(person_id) ";

					$qry.=" WHERE tbl_betriebsmittel.betriebsmittel_id='".addslashes($this->betriebsmittel_id)."' ";
					$qry.=" order by retouram asc limit 1 ";					

					$qry1 = "SELECT 
						*, tbl_benutzerfunktion.oe_kurzbz as oe_kurzbz, tbl_organisationseinheit.bezeichnung as oe_bezeichnung,
						 tbl_benutzerfunktion.semester, tbl_benutzerfunktion.bezeichnung as bf_bezeichnung
					FROM 
						public.tbl_benutzerfunktion 
						JOIN public.tbl_funktion USING(funktion_kurzbz) 
						JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
					WHERE 
						uid=($qry)
						AND tbl_organisationseinheit.aktiv 
						AND (tbl_benutzerfunktion.datum_von is null OR tbl_benutzerfunktion.datum_von<=now()) 
						AND	(tbl_benutzerfunktion.datum_bis is null OR tbl_benutzerfunktion.datum_bis>=now())
						limit 1
					";
					if($res1=$this->db_query($qry1))
					{
						$rows =array();
						if($rows = $this->db_fetch_object($res1))
							$this->oe_kurzbz = $rows->oe_kurzbz;
						$row->oe_kurzbz=$this->oe_kurzbz;		
					}
				}					
				return $this->result=$row;
			}
			else
			{
				$this->errormsg = 'Betriebsmittel wurde nicht gefunden '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}
	
	
	/**
	 * Laedt das Betriebsmittel mit der Bestell ID des Betriebsmittels $bestellung_id
	 * @param  $bestellung_id Bestellnummer des zu ladenden Betriebsmittel
	 * @param  $bestelldetail_id Bestellposition des zu ladenden Betriebsmittel
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_bestellung_id($bestellung_id,$bestelldetail_id=null)
	{
		// Initialisieren
		$this->result=array();
		$this->errormsg = '';

		$qry='';
		$qry.=' select * ';
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel ';
		$qry.=' where bestellung_id='.$this->addslashes($bestellung_id);
		if (!is_null($bestelldetail_id) && !empty($bestelldetail_id) && is_numeric($bestelldetail_id) )
			$qry.=' and bestelldetail_id='.$this->addslashes($bestelldetail_id);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->betriebsmittel_id = $row->betriebsmittel_id;
				$this->beschreibung = $row->beschreibung;
				$this->betriebsmitteltyp = $row->betriebsmitteltyp;
				$this->nummer = $row->nummer;
				$this->nummerintern = $row->nummerintern;
				$this->reservieren = ($row->reservieren=='t'?true:false);
				$this->ort_kurzbz = $row->ort_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertvon = $row->insertvon;
				$this->insertamum = $row->insertamum;
				$this->ext_id = $row->ext_id;
	// Neu wegen Inventar seq 2009-12
				$this->beschreibung = $row->beschreibung;			// string
				$this->oe_kurzbz = $row->oe_kurzbz;					// string
				$this->hersteller = $row->hersteller;				// string
				$this->seriennummer = $row->seriennummer;			// string
				$this->bestellung_id = $row->bestellung_id;			// integer
				$this->bestelldetail_id = $row->bestelldetail_id;	// integer
				$this->afa = $row->afa;								// string
				$this->verwendung = $row->verwendung;				// string
				$this->anmerkung = $row->anmerkung;					// string
				$this->leasing_bis = $row->leasing_bis;				// date
				return $this->result=$row;
			}
			else
			{
				$this->errormsg = 'Betriebsmittel wurde nicht gefunden '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten '.($this->debug?$this->db_last_error()."<br />$qry<br />":'');
			return false;
		}
	}

	/**
	 * Laedt INVENTARTABELLE
	 * @param order_by SQL Sortierung der Abfrage
	 * @param nummer  ID eines Inventars
	 * @param ort_kurzbz ort_kurzbz eines Inventars
	 * @param betriebsmitteltyp ort_kurzbz eines Inventars
	 * @param betriebsmittelstatus_kurzbz STATUS eines Inventars
	 * @param nummer Datensatzid eines Inventars
	 * @param bestellnr WAWI Bestellnummer des Inventars
	 * @param bestellung_id WAWI Rechnungsnummer des Inventars
	 * @param afa AfA Datum
	 * @param Jahr_Monat der  WAWI Bestellerfassung
	 * @param firma_id der WAWI Bestellerfassung

	 * @param inventur_jahr der Status Jahr - Status = inventur
	 * @param beschreibung der Inventarbeschreibung
	 * @param oe_kurzbz der Organisatzion

	 * @return Daten Objekt wenn ok, false im Fehlerfall
	 */
	function betriebsmittel_inventar($order=null,$nummer=null,$ort_kurzbz=null,$betriebsmittelstatus_kurzbz=null,$betriebsmitteltyp=null,$bestellung_id=null,$bestelldetail_id=null,$bestellnr=null,$hersteller=null,$afa=null,$jahr_monat=null,$firma_id=null,$inventur_jahr=null,$beschreibung=null,$oe_kurzbz=null,$seriennummer=null,$person_id=null,$betriebsmittel_id=null)
	{
		// Init
		$this->errormsg='';
		$this->result=array();

		$nummer=trim($nummer);
		$ort_kurzbz=trim($ort_kurzbz);
		$firma_id=trim($firma_id);

		$qry='';
		$qry.='select distinct(tbl_betriebsmittel.nummer) ';
#		$qry.=',tbl_betriebsmittelperson.person_id ';
		$qry.=',tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id ';
		$qry.=',tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz ';
		$qry.=',tbl_betriebsmittel_betriebsmittelstatus.datum as betriebsmittelstatus_datum ';
		$qry.=',tbl_betriebsmittelstatus.beschreibung as betriebsmittelstatus_beschreibung ';
		$qry.=',tbl_betriebsmitteltyp.beschreibung as betriebsmitteltyp_beschreibung ';
		$qry.=', tbl_betriebsmittel.*';
		$qry.=', wawi_be.*';
		$qry.=", trim(to_char(date_part('year', tbl_betriebsmittel_betriebsmittelstatus.datum) + tbl_betriebsmittel.afa , '9999') || '-' || to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'MM-DD')) as betriebsmittelstatus_datum_afa ";
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel';

		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmitteltyp on (tbl_betriebsmitteltyp.betriebsmitteltyp=tbl_betriebsmittel.betriebsmitteltyp ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus on (tbl_betriebsmittel_betriebsmittelstatus.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelstatus on (tbl_betriebsmittelstatus.betriebsmittelstatus_kurzbz=tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz ) ';
		$qry.=' left outer join public.tbl_ort on (tbl_ort.ort_kurzbz=tbl_betriebsmittel.ort_kurzbz ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelperson on (tbl_betriebsmittelperson.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';
		
	// Verbindung zum WAWI aufbauen
		$qry.=' left outer join  dblink(\''.CONN_STRING_WAWI.'\',\'
		select distinct bestellung.bestellung_id,bestellung.bestellnr,bestellung.titel,bestellung.firma_id,firma.firmenname from public.bestellung
			left join public.firma on ( firma.firma_id=bestellung.firma_id ) \' )
			 as wawi_be(bestellung_id int, bestellnr char(32), titel char(120), firma_id int, firmenname char(160) )
			 	 on ( cast(wawi_be.bestellung_id as INTEGER)=cast(tbl_betriebsmittel.bestellung_id as INTEGER) and cast(tbl_betriebsmittel.bestellung_id as INTEGER)>0 ) ';

		$qry.=" where not tbl_betriebsmittel.betriebsmittel_id is null ";
		$where=$this->betriebsmittel_inventar_get_where($nummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer,$person_id,$betriebsmittel_id);
		if ($where!='' && !$where)
			return $this->result;
			
	//	Select und Bedingung zusammen fuehren zu SQL Abfrage
		$qry.=$where.(!is_null($order) && !empty($order)?' ORDER BY '. $order:''). (!$where?' limit 100 ':' limit 300 ');
## echo "<hr>$qry <hr>";
		if(!$result=$this->db_query($qry))
		{
			$this->errormsg ='Probleme beim lesen der Betriebsmittel '.($this->debug?$this->db_last_error() ."<br />$qry<br />":'') ;
			return false;
		}
		while($row = $this->db_fetch_object($result))
			$this->result[]=$row;
##var_dump($this->result);
		return $this->result;
	}
// Ende ===========================================================================================

	/**
	 * Laedt INVENTARTABELLE BESTELLUNG_ID
	 * @param nummer  ID eines Inventars
	 * @param ort_kurzbz ort_kurzbz eines Inventars
	 * @param betriebsmitteltyp ort_kurzbz eines Inventars
	 * @param betriebsmittelstatus_kurzbz STATUS eines Inventars
	 * @param nummer Datensatzid eines Inventars
	 * @param bestellnr WAWI Bestellnummer des Inventars
	 * @param bestellung_id WAWI Rechnungsnummer des Inventars
	 * @param afa AfA Datum
	 * @param Jahr_Monat der  WAWI Bestellerfassung
	 * @param firma_id der WAWI Bestellerfassung

	 * @param inventur_jahr der Status Jahr - Status = inventur
	 * @param beschreibung der Inventarbeschreibung
	 * @param oe_kurzbz der Organisatzion

	 * @return Daten Objekt wenn ok, false im Fehlerfall
	 */
	function betriebsmittel_inventar_bestellung_id($order=null,$nummer=null,$ort_kurzbz=null,$betriebsmittelstatus_kurzbz=null,$betriebsmitteltyp=null,$bestellung_id=null,$bestelldetail_id=null,$bestellnr=null,$hersteller=null,$afa=null,$jahr_monat=null,$firma_id=null,$inventur_jahr=null,$beschreibung=null,$oe_kurzbz=null,$seriennummer=null,$person_id=null,$betriebsmittel_id=null)
	{
		// Init
		$this->errormsg='';
		$this->result=array();

		$ort_kurzbz=trim($ort_kurzbz);
		$qry='';
		$qry.='select distinct(tbl_betriebsmittel.bestellung_id) ';
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel';

		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmitteltyp on (tbl_betriebsmitteltyp.betriebsmitteltyp=tbl_betriebsmittel.betriebsmitteltyp ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus on (tbl_betriebsmittel_betriebsmittelstatus.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelstatus on (tbl_betriebsmittelstatus.betriebsmittelstatus_kurzbz=tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz ) ';
		$qry.=' left outer join public.tbl_ort on (tbl_ort.ort_kurzbz=tbl_betriebsmittel.ort_kurzbz ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelperson on (tbl_betriebsmittelperson.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';

	// Verbindung zum WAWI aufbauen
		if ( $bestellnr || $firma_id || $beschreibung )
			$qry.=' left outer join  dblink(\''.CONN_STRING_WAWI.'\',\'
			select distinct bestellung.bestellung_id,bestellung.bestellnr,bestellung.titel,bestellung.firma_id,firma.firmenname from public.bestellung
				left join public.firma on ( firma.firma_id=bestellung.firma_id ) \' )
				 as wawi_be(bestellung_id int, bestellnr char(32), titel char(120), firma_id int, firmenname char(160) )
			 	 on ( cast(wawi_be.bestellung_id as INTEGER)=cast(tbl_betriebsmittel.bestellung_id as INTEGER) and cast(tbl_betriebsmittel.bestellung_id as INTEGER)>0 ) ';

		$qry.=" where not tbl_betriebsmittel.bestellung_id is null ";
		$where='';
		$where=$this->betriebsmittel_inventar_get_where($nummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer);

	//	Select und Bedingung zusammen fuehren zu SQL Abfrage
		if (is_null($order) || empty($order) )
			$order='tbl_betriebsmittel.bestellung_id';

		$qry.=$where.(!is_null($order) && !empty($order)?' ORDER BY '. $order:'').(!$where?' limit 20 ':' limit 50 ');
		if(!$result=$this->db_query($qry))
		{
			$this->errormsg ='Probleme beim lesen der Betriebsmittel '.($this->debug?$this->db_last_error() ."<br />$qry<br />":'') ;
			return false;
		}
		while($row = $this->db_fetch_object($result))
		{
			$this->result[]=$row;
		 }
#var_dump($this->result);
#exit;
		return $this->result;
	}
// -------------------------------------------

	function betriebsmittel_inventar_get_where($nummer=null,$ort_kurzbz=null,$betriebsmittelstatus_kurzbz=null,$betriebsmitteltyp=null,$bestellung_id=null,$bestelldetail_id=null,$bestellnr=null,$hersteller=null,$afa=null,$jahr_monat=null,$firma_id=null,$inventur_jahr=null,$beschreibung=null,$oe_kurzbz=null,$seriennummer=null,$person_id=null,$betriebsmittel_id=null)
	{
##	 echo " betriebsmittel_inventar($nummer<br>,$ort_kurzbz<br>,StatusKurz $betriebsmittelstatus_kurzbz<br>,$betriebsmitteltyp<br>,Bestellung ID $bestellung_id<br>,$bestelldetail_id<br>,Bestellnr $bestellnr<br>,$hersteller<br>,AFA $afa<br>,JJMM $jahr_monat<br>,Firma $firma_id<br>,Invent $inventur_jahr<br>,Beschr $beschreibung<br>,OE $oe_kurzbz<br>Seriennr $seriennummer<br>, person_id $person_id <br>, betriebsmittel_id $betriebsmittel_id <hr>";
#exit;
		$where='';
		// Inventarnummer oder Betriebsmittelnummer
		if (!is_null($nummer) && !empty($nummer) )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($nummer))));
##			$where.=" AND UPPER(trim(tbl_betriebsmittel.nummer))  like '%".$matchcode."%' " ;
			$where.=" AND UPPER(trim(tbl_betriebsmittel.nummer))  like '".$matchcode."' " ;
		}
		if (!is_null($betriebsmittel_id) && !empty($betriebsmittel_id) )
			$where.=" AND tbl_betriebsmittel.betriebsmittel_id = ".$this->addslashes(trim($betriebsmittel_id));
		
		// Inventarnummer oder Betriebsmittelnummer
		if (!is_null($seriennummer) && !empty($seriennummer) )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($seriennummer))));
			$where.=" AND UPPER(trim(tbl_betriebsmittel.seriennummer))  like '%".$matchcode."%' " ;
		}

		if (!is_null($hersteller) && $hersteller!='' )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($hersteller))));
			$where.=" AND UPPER(trim(tbl_betriebsmittel.hersteller))  like '%".$matchcode."%' " ;
		}

		if (!is_null($beschreibung) && $beschreibung!='' )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($beschreibung))));
			$where.=" AND ( UPPER(trim(tbl_betriebsmittel.beschreibung)) like '%".$matchcode."%' ";
			$where.=" or UPPER(trim(tbl_betriebsmittel.verwendung)) like '%".$matchcode."%'  ";
			if ( $bestellnr || $firma_id || $beschreibung )
					$where.=" or UPPER(trim(wawi_be.titel)) like '%". $matchcode ."%' " ;
			$where.=" or UPPER(trim(tbl_betriebsmittel.anmerkung)) like '%".$matchcode."%' ) ";

		}

		if (!is_null($bestellung_id) && $bestellung_id!='' && is_numeric($bestellung_id))
			$where.=" AND tbl_betriebsmittel.bestellung_id=". addslashes(trim($bestellung_id));
		elseif (!is_null($bestellung_id) && $bestellung_id!='')
			$where.=" AND UPPER(trim(to_char(tbl_betriebsmittel.bestellung_id,'999999999'))) like '". mb_strtoupper(addslashes(str_replace(array('*',';',' ',"'",'"'),'%',trim($bestellung_id)))) ."%' " ;

		if (!is_null($bestelldetail_id) && $bestelldetail_id!='' && is_numeric($bestelldetail_id))
			$where.=" AND tbl_betriebsmittel.bestelldetail_id=". addslashes(trim($bestelldetail_id));
		elseif (!is_null($bestelldetail_id) && $bestelldetail_id!='')
				$where.=" AND UPPER(trim(to_char(tbl_betriebsmittel.bestelldetail_id,'999999999'))) like '". mb_strtoupper(addslashes(str_replace(array('*',';',' ',"'",'"'),'%',trim($bestelldetail_id)))) ."%' " ;

		if (!is_null($person_id) && $person_id!='' )
		{
			$pWhere='';
			if (is_numeric($person_id) )
				$pWhere.=" AND (  tbl_betriebsmittelperson.person_id=". addslashes(trim($person_id));
			else
			{
				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($person_id))));
				$pWhere.=" AND ( tbl_betriebsmittelperson.person_id in (select person_id from campus.vw_benutzer where aktiv ";
				$pWhere.="	and (UPPER(trim(uid)) like '%".$matchcode."%'  ";
				$pWhere.="	or UPPER(trim(to_char(person_id,'999999999'))) like '%".$matchcode."%' ";
				$pWhere.="	or	UPPER(trim(nachname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(nachname || ' ' || vorname)) like '%".addslashes($matchcode)."%'  ";
				$pWhere.="	or	UPPER(trim(vorname || ' ' || nachname)) like '%".addslashes($matchcode)."%' ) )";				
			}
			$where.=$pWhere;
			
			if (!is_null($oe_kurzbz) && $oe_kurzbz!='')
			{
##					where funktion_kurzbz='oezuordnung'  

				$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($oe_kurzbz))));
				$where.=" AND ( upper(trim(tbl_betriebsmittel.oe_kurzbz)) like '%". $matchcode."%' " ;
				$where.=" or  tbl_betriebsmittelperson.person_id in ( SELECT distinct vw_benutzer.person_id
					FROM public.tbl_benutzerfunktion JOIN campus.vw_benutzer USING(uid) 
					where not funktion_kurzbz=null
					and	oe_kurzbz IN(
						WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
						(
							SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
							WHERE upper(trim(oe_kurzbz)) like '". $matchcode."'
							UNION ALL
							SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
							WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
						)
						SELECT oe_kurzbz
						FROM oes
						GROUP BY oe_kurzbz  ))
					 )";			
			}
			$where.=" )";
		}
		// Organisation
		else if (!is_null($oe_kurzbz) && $oe_kurzbz!='')
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($oe_kurzbz))));
			$where.=" AND ( upper(trim(tbl_betriebsmittel.oe_kurzbz)) like '". $matchcode."' " ;
			$where.=" or  tbl_betriebsmittelperson.person_id in ( SELECT distinct vw_benutzer.person_id
				FROM public.tbl_benutzerfunktion JOIN campus.vw_benutzer USING(uid) 
				where not funktion_kurzbz=null
				and	oe_kurzbz IN(
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
					(
						SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
						WHERE upper(trim(oe_kurzbz)) like '". $matchcode."'
						UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
						WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes
					GROUP BY oe_kurzbz  ))
				 )";				
		}
		
/*
			
				and tbl_betriebsmittelperson.person_id= ( SELECT distinct vw_benutzer.person_id
				FROM public.tbl_benutzerfunktion JOIN campus.vw_benutzer USING(uid) 
				and (funktion_kurzbz='oezuordnung' OR funktion_kurzbz='Leitung') 
				and	oe_kurzbz IN(
					WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as 
					(
						SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit 
						WHERE upper(trim(oe_kurzbz))=upper(trim('".addslashes($oe_kurzbz)."'))
						UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes 
						WHERE o.oe_parent_kurzbz=oes.oe_kurzbz
					)
					SELECT oe_kurzbz
					FROM oes
					GROUP BY oe_kurzbz  limit 1 )
					
*/			
		// Ort
		if (!is_null($ort_kurzbz) && $ort_kurzbz!='')
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($ort_kurzbz))));
			$where.=" AND ( upper(trim(tbl_betriebsmittel.ort_kurzbz)) like '%". $matchcode."%' " ;
			$where.="  OR upper(trim(tbl_ort.bezeichnung)) like '%".$matchcode."%' )" ;
		}

		if (!is_null($betriebsmitteltyp) && !empty($betriebsmitteltyp) )
			$where.=" AND upper(trim(tbl_betriebsmittel.betriebsmitteltyp)) = ".$this->addslashes(mb_strtoupper(trim($betriebsmitteltyp)));

		// Datum Check
		if (!is_null($afa) && $afa!='')
		{
			$afa=mb_strtoupper(trim(addslashes(str_replace(array('-',',',';','.','/','*','%',"'",'"'),'',trim($afa)))));
			if (!empty($afa) && is_numeric($afa) && strlen($afa)>4)
				$where.=" and not afa is null and trim(to_char(date_part('year', tbl_betriebsmittel_betriebsmittelstatus.datum) + tbl_betriebsmittel.afa , '9999')  || to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'MM')) = '".substr($afa,0,6)."'";
			else if (!empty($afa) && is_numeric($afa) && strlen($afa)>2)
				$where.=" and not afa is null and trim(to_char(date_part('year', tbl_betriebsmittel_betriebsmittelstatus.datum)  + tbl_betriebsmittel.afa ,'9999')) = '".substr($afa,0,4)."'";
			else
				$where.=" and not afa is null and trim(to_char(date_part('year', tbl_betriebsmittel_betriebsmittelstatus.datum)  + tbl_betriebsmittel.afa ,'9999')) <= '".Date('Y')."'";
			if (is_null($betriebsmittelstatus_kurzbz) || $betriebsmittelstatus_kurzbz==''  )
				$betriebsmittelstatus_kurzbz=mb_strtoupper('vorhanden');
			$where.=" and tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id in ( select max(betriebsmittelbetriebsmittelstatus_id) from wawi.tbl_betriebsmittel_betriebsmittelstatus ".($betriebsmittelstatus_kurzbz?" where  not betriebsmittelbetriebsmittelstatus_id is null and upper(tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz) = ".$this->addslashes(mb_strtoupper(trim($betriebsmittelstatus_kurzbz))):'')." group by  betriebsmittel_id ) ";

		}
		elseif (!is_null($inventur_jahr) && $inventur_jahr!='')
		{
			$inventur_jahr=mb_strtoupper(trim(addslashes(str_replace(array('.','/','*','%',"'",'"'),'',trim($inventur_jahr)))));
			if ($inventur_jahr>0)
			{
				$where.=" and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYY') = '".($inventur_jahr)."'";
				$where.=" and tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id in ( select max(betriebsmittelbetriebsmittelstatus_id) from wawi.tbl_betriebsmittel_betriebsmittelstatus where not betriebsmittelbetriebsmittelstatus_id is null
						and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYY')='".$inventur_jahr."' ".($betriebsmittelstatus_kurzbz?"  and upper(trim(betriebsmittelstatus_kurzbz))=".$this->addslashes(mb_strtoupper(trim($betriebsmittelstatus_kurzbz))):'')." group by  betriebsmittel_id ) ";
			}
			else
			{
				$inventur_jahr=($inventur_jahr * -1);
#				$where.=" and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYY') = '".$inventur_jahr."'";
				$where.=" and not tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id in ( select max(betriebsmittelbetriebsmittelstatus_id) from wawi.tbl_betriebsmittel_betriebsmittelstatus where not betriebsmittelbetriebsmittelstatus_id is null
						and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYY')='".$inventur_jahr."' ".($betriebsmittelstatus_kurzbz?"  and upper(trim(betriebsmittelstatus_kurzbz))=".$this->addslashes(mb_strtoupper(trim($betriebsmittelstatus_kurzbz))):'')." group by  betriebsmittel_id ) ";
				$betriebsmittelstatus_kurzbz='vorhanden';
			}
		}
		elseif (!is_null($jahr_monat) && $jahr_monat!='')
		{
			$jahr_monat=mb_strtoupper(trim(addslashes(str_replace(array('-','.','/','*','%',"'",'"'),'',trim($jahr_monat)))));
			$jm='';
			if (!empty($jahr_monat) && is_numeric($jahr_monat) && strlen($jahr_monat)>6)
				$jm=" and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYYMMDD') = '".$jahr_monat."' ";
			elseif (!empty($jahr_monat) && is_numeric($jahr_monat) && strlen($jahr_monat)>4)
				$jm=" and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYYMM') = '".$jahr_monat."' ";
			elseif (!is_null($jahr_monat) && !empty($jahr_monat))
				$jm=" and to_char(tbl_betriebsmittel_betriebsmittelstatus.datum, 'YYYY') = '".$jahr_monat."' ";
			$where.=$jm;
			$where.=" and tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id in ( select max(betriebsmittelbetriebsmittelstatus_id) from wawi.tbl_betriebsmittel_betriebsmittelstatus where not betriebsmittelbetriebsmittelstatus_id is null ". $jm ." group by  betriebsmittel_id ) ";
		}
		else if (!is_null($betriebsmittelstatus_kurzbz) && $betriebsmittelstatus_kurzbz!='')
			$where.=" and tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelbetriebsmittelstatus_id in ( select max(betriebsmittelbetriebsmittelstatus_id) from wawi.tbl_betriebsmittel_betriebsmittelstatus where not betriebsmittelbetriebsmittelstatus_id is null  group by  betriebsmittel_id ) ";

		// Bestellnummer
		if (!is_null($bestellnr) && !empty($bestellnr) )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($bestellnr))));
			$where.=" AND UPPER(trim(wawi_be.bestellnr)) like '%".$matchcode."%' " ;
		}
		// Lieferant
		if (!is_null($firma_id) && $firma_id!='' && is_numeric($firma_id))
		{
			$where.=" AND wawi_be.firma_id=". trim($firma_id) ;
		}
		elseif (!is_null($firma_id) && $firma_id!='' )
		{
			$matchcode=mb_strtoupper(addslashes(str_replace(array('*','%',',',';',"'",'"',' '),'%',trim($firma_id))));
			$where.=" AND UPPER(trim(wawi_be.firmenname)) like '%". $matchcode ."%'  " ;
		}
		
		if (!is_null($betriebsmittelstatus_kurzbz) && !empty($betriebsmittelstatus_kurzbz) )
			$where.=" and upper(trim(tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz)) = ".$this->addslashes(mb_strtoupper(trim($betriebsmittelstatus_kurzbz))) ;
##echo "<br>  $where <br>";
			
		return $where;
	}


	/**
	 * Laedt INVENTARTABELLE ORTE
	 * @param nummer  ID eines Inventars
	 * @param ort_kurzbz ort_kurzbz eines Inventars
	 * @param betriebsmitteltyp ort_kurzbz eines Inventars
	 * @param betriebsmittelstatus_kurzbz STATUS eines Inventars
	 * @param nummer Datensatzid eines Inventars
	 * @param bestellnr WAWI Bestellnummer des Inventars
	 * @param bestellung_id WAWI Rechnungsnummer des Inventars
	 * @param afa AfA Datum
	 * @param Jahr_Monat der  WAWI Bestellerfassung
	 * @param firma_id der WAWI Bestellerfassung

	 * @param inventur_jahr der Status Jahr - Status = inventur
	 * @param beschreibung der Inventarbeschreibung
	 * @param oe_kurzbz der Organisatzion

	 * @return Daten Objekt wenn ok, false im Fehlerfall
	 */
/*
	function betriebsmittel_inventar_orte($order=null,$nummer=null,$ort_kurzbz=null,$betriebsmittelstatus_kurzbz=null,$betriebsmitteltyp=null,$bestellung_id=null,$bestelldetail_id=null,$bestellnr=null,$hersteller=null,$afa=null,$jahr_monat=null,$firma_id=null,$inventur_jahr=null,$beschreibung=null,$oe_kurzbz=null,$seriennummer=null)
	{
		// Init
		$this->errormsg='';
		$this->result=array();

		$ort_kurzbz=trim($ort_kurzbz);
		$qry='';
		$qry.='select distinct(tbl_betriebsmittel.ort_kurzbz),tbl_ort.bezeichnung ';
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmitteltyp on (tbl_betriebsmitteltyp.betriebsmitteltyp=tbl_betriebsmittel.betriebsmitteltyp ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus on (tbl_betriebsmittel_betriebsmittelstatus.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelstatus on (tbl_betriebsmittelstatus.betriebsmittelstatus_kurzbz=tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz ) ';
		$qry.=' left outer join public.tbl_ort on (tbl_ort.ort_kurzbz=tbl_betriebsmittel.ort_kurzbz ) ';
	// Verbindung zum WAWI aufbauen
		if ( $bestellnr || $firma_id || $beschreibung )
			$qry.=' left outer join  dblink(\''.CONN_STRING_WAWI.'\',\'
			select distinct bestellung.bestellung_id,bestellung.bestellnr,bestellung.titel,bestellung.firma_id,firma.firmenname from public.bestellung
				left join public.firma on ( firma.firma_id=bestellung.firma_id ) \' )
				 as wawi_be(bestellung_id int, bestellnr char(32), titel char(120), firma_id int, firmenname char(160) )
			 	 on ( cast(wawi_be.bestellung_id as INTEGER)=cast(tbl_betriebsmittel.bestellung_id as INTEGER) and cast(tbl_betriebsmittel.bestellung_id as INTEGER)>0 ) ';
		$qry.=" where not tbl_betriebsmittel.ort_kurzbz is null ";

		$where='';
		$where=$this->betriebsmittel_inventar_get_where($nummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer);

	//	Select und Bedingung zusammen fuehren zu SQL Abfrage
		if (is_null($order) || empty($order) )
			$order='tbl_betriebsmittel.ort_kurzbz';

		$qry.=$where.(!is_null($order) && !empty($order)?' ORDER BY '. $order:'').(!$where?' limit 30 ':' limit 90 ');
		if(!$result=$this->db_query($qry))
		{
			$this->errormsg ='Probleme beim lesen der Betriebsmittel '.($this->debug?$this->db_last_error() ."<br />$qry<br />":'') ;
			return false;
		}
		while($row = $this->db_fetch_object($result))
			$this->result[]=$row;
#var_dump($this->result);
		return $this->result;
	}
*/

	/**
	 * Laedt INVENTARTABELLE HERSTELLER
	 * @param nummer  ID eines Inventars
	 * @param ort_kurzbz ort_kurzbz eines Inventars
	 * @param betriebsmitteltyp ort_kurzbz eines Inventars
	 * @param betriebsmittelstatus_kurzbz STATUS eines Inventars
	 * @param nummer Datensatzid eines Inventars
	 * @param bestellnr WAWI Bestellnummer des Inventars
	 * @param bestellung_id WAWI Rechnungsnummer des Inventars
	 * @param afa AfA Datum
	 * @param Jahr_Monat der  WAWI Bestellerfassung
	 * @param firma_id der WAWI Bestellerfassung

	 * @param inventur_jahr der Status Jahr - Status = inventur
	 * @param beschreibung der Inventarbeschreibung
	 * @param oe_kurzbz der Organisatzion

	 * @return Daten Objekt wenn ok, false im Fehlerfall
	 */
/*	 
	function betriebsmittel_inventar_hersteller($order=null,$nummer=null,$ort_kurzbz=null,$betriebsmittelstatus_kurzbz=null,$betriebsmitteltyp=null,$bestellung_id=null,$bestelldetail_id=null,$bestellnr=null,$hersteller=null,$afa=null,$jahr_monat=null,$firma_id=null,$inventur_jahr=null,$beschreibung=null,$oe_kurzbz=null,$seriennummer=null)
	{
		// Init
		$this->errormsg='';
		$this->result=array();

		$ort_kurzbz=trim($ort_kurzbz);
		$qry='';
		$qry.='select distinct(tbl_betriebsmittel.hersteller) ';
		$qry.=' from '.$this->schema_inventar.'.tbl_betriebsmittel';

		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmitteltyp on (tbl_betriebsmitteltyp.betriebsmitteltyp=tbl_betriebsmittel.betriebsmitteltyp ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittel_betriebsmittelstatus on (tbl_betriebsmittel_betriebsmittelstatus.betriebsmittel_id=tbl_betriebsmittel.betriebsmittel_id ) ';
		$qry.=' left outer join '.$this->schema_inventar.'.tbl_betriebsmittelstatus on (tbl_betriebsmittelstatus.betriebsmittelstatus_kurzbz=tbl_betriebsmittel_betriebsmittelstatus.betriebsmittelstatus_kurzbz ) ';
		$qry.=' left outer join public.tbl_ort on (tbl_ort.ort_kurzbz=tbl_betriebsmittel.ort_kurzbz ) ';
	// Verbindung zum WAWI aufbauen
		if ( $bestellnr || $firma_id || $beschreibung )
			$qry.=' left outer join  dblink(\''.CONN_STRING_WAWI.'\',\'
			select distinct bestellung.bestellung_id,bestellung.bestellnr,bestellung.titel,bestellung.firma_id,firma.firmenname from public.bestellung
				left join public.firma on ( firma.firma_id=bestellung.firma_id ) \' )
				 as wawi_be(bestellung_id int, bestellnr char(32), titel char(120), firma_id int, firmenname char(160) )
			 	 on ( cast(wawi_be.bestellung_id as INTEGER)=cast(tbl_betriebsmittel.bestellung_id as INTEGER) and cast(tbl_betriebsmittel.bestellung_id as INTEGER)>0 ) ';

		$qry.=" where not tbl_betriebsmittel.hersteller is null ";

		$where=$this->betriebsmittel_inventar_get_where($nummer,$ort_kurzbz,$betriebsmittelstatus_kurzbz,$betriebsmitteltyp,$bestellung_id,$bestelldetail_id,$bestellnr,$hersteller,$afa,$jahr_monat,$firma_id,$inventur_jahr,$beschreibung,$oe_kurzbz,$seriennummer);

	//	Select und Bedingung zusammen fuehren zu SQL Abfrage
		if (is_null($order) || empty($order) )
			$order='tbl_betriebsmittel.hersteller';

		$qry.=$where.(!is_null($order) && !empty($order)?' ORDER BY '. $order:'').(!$where?' limit 20 ':' limit 50 ');

#echo "<hr> $qry <hr>";

		if(!$result=$this->db_query($qry))
		{
			$this->errormsg ='Probleme beim lesen der Betriebsmittel '.($this->debug?$this->db_last_error() ."<br />$qry<br />":'') ;
			return false;
		}
		while($row = $this->db_fetch_object($result))
		{
			$this->result[]=$row;
		 }
#var_dump($this->result);
#exit;
		return $this->result;
	}
*/

}
?>
