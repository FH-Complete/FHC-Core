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
 *			Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 *			Rudolf Hangl <rudolf.hangl@technikum-wien.at> and.
 *			Gerald Simane-Sequens < gerald.simane-sequens@technikum-wien.at>.
 */
/**
 * Klasse firma
 * @create 18-12-2006
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/organisationseinheit.class.php');

class firma extends basis_db
{
	public $new;				// boolean
	public $result = array(); 	// adresse Objekt

	//Tabellenspalten
	public $firma_id;		// integer
	public $name;			// string
	public $anmerkung;		// string
	public $lieferbedingungen;	// string
	public $ext_id;			// integer
	public $insertamum;		// timestamp
	public $insertvon;		// bigint
	public $updateamum;		// timestamp
	public $updatevon;		// bigint
	public $firmentyp_kurzbz;
	public $schule; 		// boolean
	public $steuernummer; 	// string
	public $gesperrt; 		// boolean
	public $aktiv; 			// boolean
	public $finanzamt; 		// string
	public $partner_code;	// varchar(20)
	public $lieferant;		// boolean

	// firma_organisationseinheit
	public $oe_kurzbz; 		// string
	public $oe_parent_kurzbz; 	// string
	public $firma_organisationseinheit_id;	// integer
	public $organisationseinheittyp_kurzbz; // string

	public $bezeichnung; 		// string
	public $kundennummer; 		// integer
	public $oe_aktiv; 		// boolean
	public $mailverteiler; 		// string
	public $tags = array();


	/**
	 * Konstruktor
	 * @param $firma_id ID der Firma die geladen werden soll (Default=null)
	 */
	public function __construct($firma_id=null)
	{
		parent::__construct();

		if(!is_null($firma_id))
			$this->load($firma_id);
	}

	/**
	 * Laedt die Firma mit der ID $firma_id
	 * @param  $firma_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($firma_id)
	{
		if(!is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}

		$qry = "SElECT * FROM public.tbl_firma WHERE firma_id=".$this->db_add_param($firma_id, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->firma_id = $row->firma_id;
				$this->name = $row->name;
				$this->anmerkung = $row->anmerkung;
				$this->lieferbedingungen = $row->lieferbedingungen;
				$this->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->schule = $this->db_parse_bool($row->schule);
				$this->steuernummer = $row->steuernummer;
				$this->gesperrt = $this->db_parse_bool($row->gesperrt);
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->finanzamt = $row->finanzamt;
				$this->partner_code = $row->partner_code;
				$this->lieferant = $this->db_parse_bool($row->lieferant);

				$qry = "SELECT tag FROM public.tbl_firmatag WHERE firma_id=".$this->db_add_param($firma_id,FHC_INTEGER).';';
				if($resulttag = $this->db_query($qry))
				{
					while($rowtag = $this->db_fetch_object($resulttag))
					{
						$this->tags[]=$rowtag->tag;
					}
				}
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
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if(mb_strlen($this->name)>128)
		{
			$this->errormsg = 'Name darf nicht länger als 128 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->anmerkung)>256)
		{
			$this->errormsg = 'Anmerkung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->lieferbedingungen)>256)
		{
			$this->errormsg = 'Lieferbedingungen darf nicht länger als 256 Zeichen sein';
			return false;
		}
		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
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
			$qry='INSERT INTO public.tbl_firma (name,  anmerkung, lieferbedingungen,
					firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon, schule,steuernummer,
					gesperrt,aktiv,finanzamt, partner_code, lieferant) VALUES('.
				$this->db_add_param($this->name).', '.
				$this->db_add_param($this->anmerkung).', '.
				$this->db_add_param($this->lieferbedingungen).', '.
				$this->db_add_param($this->firmentyp_kurzbz).', '.
				$this->db_add_param($this->updateamum).', '.
				$this->db_add_param($this->updatevon).', '.
				$this->db_add_param($this->insertamum).', '.
				$this->db_add_param($this->insertvon).', '.
				$this->db_add_param($this->schule, FHC_BOOLEAN).','.
				$this->db_add_param($this->steuernummer).', '.
				$this->db_add_param($this->gesperrt, FHC_BOOLEAN).','.
				$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
				$this->db_add_param($this->finanzamt, FHC_INTEGER).','.
				$this->db_add_param($this->partner_code).','.
				$this->db_add_param($this->lieferant, FHC_BOOLEAN).'); ';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_firma SET '.
				'firma_id='.$this->db_add_param($this->firma_id).', '.
				'name='.$this->db_add_param($this->name).', '.
				'anmerkung='.$this->db_add_param($this->anmerkung).', '.
				'lieferbedingungen='.$this->db_add_param($this->lieferbedingungen).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).', '.
				'firmentyp_kurzbz='.$this->db_add_param($this->firmentyp_kurzbz).', '.
				'schule='.$this->db_add_param($this->schule, FHC_BOOLEAN).', '.
				'steuernummer='.$this->db_add_param($this->steuernummer).', '.
				'gesperrt='.$this->db_add_param($this->gesperrt, FHC_BOOLEAN).', '.
				'aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).', '.
				'finanzamt='.$this->db_add_param($this->finanzamt, FHC_INTEGER).', '.
				'partner_code='.$this->db_add_param($this->partner_code).', '.
				'lieferant='.$this->db_add_param($this->lieferant, FHC_BOOLEAN).' '.
				'WHERE firma_id='.$this->db_add_param($this->firma_id, FHC_INTEGER).';';
		}

		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_firma_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->firma_id = $row->id;
						$this->db_query('COMMIT;');
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
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Firma-Datensatzes';
			return false;
		}
		return $this->firma_id;
	}

	/**
	 * Speichert die Tags in $tags zur Firma
	 *
	 */
	public function savetags()
	{
		if(!is_numeric($this->firma_id) || $this->firma_id=='')
		{
			$this->errormsg = 'FirmaID ist ungueltig';
			return false;
		}

		foreach($this->tags as $tag)
		{
			if($tag!='')
			{
				$qry = "
					SELECT
						(SELECT true FROM public.tbl_firmatag WHERE tag=".$this->db_add_param($tag)." AND firma_id=".$this->db_add_param($this->firma_id, FHC_INTEGER).") as zugewiesen,
						(SELECT true FROM public.tbl_tag WHERE tag=".$this->db_add_param($tag).") as vorhanden";
				if($result = $this->db_query($qry))
				{
					if($row = $this->db_fetch_object($result))
					{
						if($row->vorhanden!='t')
						{
							//Tag neu anlegen
							$qry = "INSERT INTO public.tbl_tag(tag) VALUES(".$this->db_add_param($tag).");";
							if(!$this->db_query($qry))
							{
								$this->errormsg='Fehler beim Anlegen des Tags';
								return false;
							}
						}

						if($row->zugewiesen!='t')
						{
							//Tag zuweisen
							$qry = "INSERT INTO public.tbl_firmatag(firma_id, tag, insertamum, insertvon)
									VALUES(".$this->db_add_param($this->firma_id,FHC_INTEGER).",".
										$this->db_add_param($tag).",".
										$this->db_add_param($this->insertamum).",".
										$this->db_add_param($this->insertvon).");";
							if(!$this->db_query($qry))
							{
								$this->errormsg='Fehler beim Anlegen des Tags';
								return false;
							}
						}
					}
					else
					{
						$this->errormsg='Fehler beim Laden der Tags';
						return false;
					}
				}
				else
				{
					$this->errormsg='Fehler beim Laden der Tags';
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Loescht die Tag-Zuordnung zur Firma
	 *
	 * @param $firma_id
	 * @param $tag
	 * @return boolean
	 */
	public function deletetag($firma_id, $tag)
	{
		if(!is_numeric($firma_id) || $firma_id=='')
		{
			$this->errormsg = 'FirmaID ist ungueltig';
			return false;
		}

		$qry = "DELETE FROM public.tbl_firmatag WHERE firma_id=".$this->db_add_param($firma_id, FHC_INTEGER)." AND tag=".$this->db_add_param($tag).';';

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Tags';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $firma_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($firma_id)
	{
		$qry = "DELETE FROM public.tbl_firma WHERE firma_id=".$this->db_add_param($firma_id, FHC_INTEGER).';';
		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}

	/**
	 * Laedt alle Firmen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getAll($firma_search = null)
	{
		if (!empty($firma_search))
		{
			$matchcode=mb_strtoupper(str_replace(array('<','>',' ',';','*','_','-',',',"'",'"'),"%",$firma_search));
			//Zuerst werden die Ergebnisse geliefert, die mit $filter_search beginnen
			//danach jene Ergebnisse bei denen $filter_search innerhalb des Namens vorkommt
			$qry = "

				SELECT
					firma_id, name, anmerkung, lieferbedingungen, firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon,
					ext_id, schule, steuernummer, gesperrt, aktiv, finanzamt, '1' as sort, partner_code, lieferant
				FROM public.tbl_firma
				WHERE
				UPPER(trim(public.tbl_firma.name)) like '".$this->db_escape($matchcode)."%'
				UNION
				SELECT
					firma_id, name, anmerkung, lieferbedingungen, firmentyp_kurzbz, updateamum, updatevon, insertamum, insertvon,
					ext_id, schule, steuernummer, gesperrt, aktiv, finanzamt, '2' as sort, partner_code, lieferant
				FROM public.tbl_firma
				WHERE
				UPPER(trim(public.tbl_firma.name)) like '%".$this->db_escape($matchcode)."%'
				AND UPPER(trim(public.tbl_firma.name)) NOT like '".$this->db_escape($matchcode)."%'
				ORDER BY sort, name, firma_id;";
		}
		else
		{
			$qry = "SELECT * FROM public.tbl_firma ORDER BY name;";
		}


		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert alle vorhandenen Firmentypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmenTypen()
	{
		$qry = "SELECT * FROM public.tbl_firmentyp ORDER BY firmentyp_kurzbz;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->beschreibung = $row->beschreibung;

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Auslesen der Firmentypen';
			return false;
		}
	}

	/**
	 * Laedt alle Firmen eines bestimmen Firmentyps
	 * @param boolean $aktiv Default NULL
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmen($firmentyp_kurzbz='', $aktiv=null)
	{
		$qry = "SELECT * FROM public.tbl_firma";

		if($firmentyp_kurzbz!='')
			$qry.=" WHERE firmentyp_kurzbz=".$this->db_add_param($firmentyp_kurzbz);

		if(!is_null($aktiv))
			$qry.=" AND aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		$qry.=" ORDER BY name;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt alle Firmen die in den letzten Tagen geaendert oder angelegt wurden
	 * @param $tage integer Anzahl der Tage innerhalb der nach Aenderungen gesucht wird.
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getLatestChanges($tage = 7)
	{
		$this->result = array();
		$this->errormsg = '';

		$qry ="SELECT * FROM (SElECT ";
		$qry.=" distinct on(firma_id)";
		$qry.=" tbl_firma.firma_id,tbl_firma.* ,tbl_standort.kurzbz,tbl_standort.adresse_id,tbl_standort.standort_id,tbl_standort.bezeichnung  ";
		$qry.=" ,person_id,	tbl_adresse.name as adress_name, strasse, plz, ort, gemeinde,nation,typ,heimatadresse,zustelladresse  ";
		$qry.=" FROM public.tbl_firma";
		$qry.=" LEFT JOIN public.tbl_standort USING(firma_id) ";
		$qry.=" LEFT JOIN public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
		$qry.=" WHERE 1=1";
		$qry.=")  as a  WHERE (updateamum is not null and updateamum > (now()::date - $tage)) or insertamum > (now()::date - $tage)  ORDER BY name;";
		//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);
				$fa->kurzbz = $row->kurzbz;
				$fa->adresse_id = $row->adresse_id;
				$fa->standort_id = $row->standort_id;
				$fa->bezeichnung = $row->bezeichnung;
				$fa->person_id = $row->person_id;
				$fa->adresse_id = $row->adresse_id;
				$fa->strasse = $row->strasse;
				$fa->plz = $row->plz;
				$fa->ort = $row->ort;
				$fa->gemeinde = $row->gemeinde;
				$fa->nation = $row->nation;
				$fa->typ = $row->typ;
				$fa->adress_name = $row->adress_name;
				$fa->heimatadresse = $this->db_parse_bool($row->heimatadresse);
				$fa->zustelladresse = $this->db_parse_bool($row->zustelladresse);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt die Firmen die innerhalb einer bestimmten Kalenderwoche geaendert oder angelegt wurden
	 * @param $kw integer Kalenderwoche.
	 * @param $jahr Jahr der Kalenderwoche
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function getChangesByKW($kw = 0, $jahr = 0)
	{
		$this->result = array();
		$this->errormsg = '';

		if ($kw<1 || $kw>53)
		{
			$this->errormsg = 'KW außerhalb gültigem Bereich ('.$kw.')';
			return false;
		}
		if ($jahr<1990)
		{
			$this->errormsg = 'Jahr außerhalb gültigem Bereich ('.$jahr.')';
			return false;
		}

		$qry ="SELECT * FROM (SElECT ";
		$qry.=" distinct on(firma_id)";
		$qry.=" tbl_firma.firma_id,tbl_firma.* ,tbl_standort.kurzbz,tbl_standort.adresse_id,tbl_standort.standort_id,tbl_standort.bezeichnung  ";
		$qry.=" ,person_id,	tbl_adresse.name as adress_name, strasse, plz, ort, gemeinde,nation,typ,heimatadresse,zustelladresse  ";
		$qry.=" FROM public.tbl_firma";
		$qry.=" LEFT JOIN public.tbl_standort USING(firma_id) ";
		$qry.=" LEFT JOIN public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
		$qry.=" WHERE 1=1";
		$qry.=")  as a  WHERE (updateamum is not null and date_part('week',updateamum) = $kw and date_part('year',updateamum) = $jahr) or (date_part('week',insertamum)=$kw and date_part('year',insertamum)=$jahr)  ORDER BY name;";
		//echo $qry;
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);
				$fa->kurzbz = $row->kurzbz;
				$fa->adresse_id = $row->adresse_id;
				$fa->standort_id = $row->standort_id;
				$fa->bezeichnung = $row->bezeichnung;
				$fa->person_id = $row->person_id;
				$fa->adresse_id = $row->adresse_id;
				$fa->strasse = $row->strasse;
				$fa->plz = $row->plz;
				$fa->ort = $row->ort;
				$fa->gemeinde = $row->gemeinde;
				$fa->nation = $row->nation;
				$fa->typ = $row->typ;
				$fa->adress_name = $row->adress_name;
				$fa->heimatadresse = $this->db_parse_bool($row->heimatadresse);
				$fa->zustelladresse = $this->db_parse_bool($row->zustelladresse);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Suche nur nach Firmennamen für die abgespeckte Firmenverwaltung
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function searchByName($filter)
	{
		$this->result = array();
		$this->errormsg = '';

		$qry ="SELECT * FROM (SElECT ";
		$qry.=" distinct on(firma_id)";
		$qry.=" tbl_firma.firma_id,tbl_firma.* ,tbl_standort.kurzbz,tbl_standort.adresse_id,tbl_standort.standort_id,tbl_standort.bezeichnung  ";
		$qry.=" ,person_id,	tbl_adresse.name as adress_name, strasse, plz, ort, gemeinde,nation,typ,heimatadresse,zustelladresse  ";
		$qry.=" FROM public.tbl_firma";
		$qry.=" LEFT JOIN public.tbl_standort USING(firma_id) ";
		$qry.=" LEFT JOIN public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
		$qry.=" WHERE 1=1";

		if($filter!='')
			$qry.= " and ( lower(tbl_firma.name) like lower('%".$this->db_escape($filter)."%')

				   ) ";

		//if($filter=='' && $firmentyp_kurzbz=='')
		//	$qry.=" limit 500 ";
		$qry.=") as a ORDER BY name;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);
				$fa->kurzbz = $row->kurzbz;
				$fa->adresse_id = $row->adresse_id;
				$fa->standort_id = $row->standort_id;
				$fa->bezeichnung = $row->bezeichnung;
				$fa->person_id = $row->person_id;
				$fa->adresse_id = $row->adresse_id;
				$fa->strasse = $row->strasse;
				$fa->plz = $row->plz;
				$fa->ort = $row->ort;
				$fa->gemeinde = $row->gemeinde;
				$fa->nation = $row->nation;
				$fa->typ = $row->typ;
				$fa->adress_name = $row->adress_name;
				$fa->heimatadresse = $this->db_parse_bool($row->heimatadresse);
				$fa->zustelladresse = $this->db_parse_bool($row->zustelladresse);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt alle Firmen Standorte, und Adressen nach Suchstring und/oder eines bestimmen Firmentyps, und/oder einer bestimmten Organisationseinheit
	 * @param string $filter Optional. Searchstring.
	 * @param string $firmentyp_kurzbz Optional. Firmentyp
	 * @param boolean $standorte Default false. Wenn false, findet ein distinct on auf die firma_id statt
	 * @param string $oefilter Oe_kurzbz. Optional. Filtert die Firmen auf die uebergebene oe_kurzbz.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function searchFirma($filter='',$firmentyp_kurzbz='', $standorte=false, $oefilter='')
	{
		$this->result = array();
		$this->errormsg = '';
		$oes = '';
		if ($oefilter!='')
			$oes = implode("','",$oefilter);
		$qry ="SELECT * FROM (SELECT ";
		if(!$standorte)
			$qry.=" distinct on(firma_id)";
		$qry.=" tbl_firma.firma_id,tbl_firma.* ,tbl_standort.kurzbz,tbl_standort.adresse_id,tbl_standort.standort_id,tbl_standort.bezeichnung  ";
		$qry.=" ,person_id,	tbl_adresse.name as adress_name, strasse, plz, ort, gemeinde,nation,typ,heimatadresse,zustelladresse  ";
		$qry.=" FROM public.tbl_firma";
		$qry.=" LEFT JOIN public.tbl_standort USING(firma_id) ";
		$qry.=" LEFT JOIN public.tbl_adresse  on ( tbl_adresse.adresse_id=tbl_standort.adresse_id ) ";
		if ($oes!='')
			$qry.=" LEFT JOIN public.tbl_firma_organisationseinheit ON (tbl_firma.firma_id=tbl_firma_organisationseinheit.firma_id) ";
		$qry.=" WHERE 1=1";

		if($filter!='')
			$qry.= " and ( lower(tbl_firma.name) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_standort.kurzbz) like lower('%".$this->db_escape($filter)."%')

					OR lower(tbl_adresse.name) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_adresse.plz) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_adresse.ort) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_adresse.strasse) like lower('%".$this->db_escape($filter)."%')

					OR lower(tbl_standort.bezeichnung) like lower('%".$this->db_escape($filter)."%')
					OR lower(tbl_firma.anmerkung) like lower('%".$this->db_escape($filter)."%')
					".(is_numeric($filter)?" OR tbl_firma.firma_id='".$this->db_escape($filter)."'":'')."
					OR tbl_firma.firma_id IN (SELECT firma_id FROM public.tbl_firmatag
											  WHERE firma_id=tbl_firma.firma_id AND lower(tag) like lower('%".$this->db_escape($filter)."%'))
					 ) ";

		if($firmentyp_kurzbz!='')
			$qry.=" and firmentyp_kurzbz=".$this->db_add_param($firmentyp_kurzbz);

		if($oes!='')
			$qry.=" and oe_kurzbz IN ('".$oes."') ";

		//if($filter=='' && $firmentyp_kurzbz=='')
		//	$qry.=" limit 500 ";
		$qry.=") as a ORDER BY name;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				//$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);
				$fa->kurzbz = $row->kurzbz;
				$fa->adresse_id = $row->adresse_id;
				$fa->standort_id = $row->standort_id;
				$fa->bezeichnung = $row->bezeichnung;
				$fa->person_id = $row->person_id;
				$fa->adresse_id = $row->adresse_id;
				$fa->strasse = $row->strasse;
				$fa->plz = $row->plz;
				$fa->ort = $row->ort;
				$fa->gemeinde = $row->gemeinde;
				$fa->nation = $row->nation;
				$fa->typ = $row->typ;
				$fa->adress_name = $row->adress_name;
				$fa->heimatadresse = $this->db_parse_bool($row->heimatadresse);
				$fa->zustelladresse = $this->db_parse_bool($row->zustelladresse);

				$this->result[] = $fa;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert die Kundennummer einer Firma zu einer Organisationseinheit
	 * Wenn fuer diese Organisationseinheit kein Eintrag vorhanden ist, wird
	 * in den uebergeordneten OEs gesucht
	 *
	 * @param firma_id
	 * @param oe_kurzbz
	 * @return kundennummer oder false wenn nicht vorhanden
	 */
	public function get_kundennummer($firma_id, $oe_kurzbz)
	{
		$qry = "SELECT kundennummer FROM public.tbl_firma_organisationseinheit
				WHERE firma_id=".$this->db_add_param($firma_id, FHC_INTEGER)." AND oe_kurzbz=".$this->db_add_param($oe_kurzbz).";";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $row->kundennummer;
			}
			else
			{
				$oe = new organisationseinheit();
				if($oe->load($oe_kurzbz))
				{
					if($oe->oe_parent_kurzbz!='')
						return $this->get_kundennummer($firma_id, $oe->oe_parent_kurzbz);
					else
						return false;
				}
				else
					return false;
			}
		}
	}

	/**
	 * Laedt alle Firmen -  Organisationseinheiten nach Firmen ID und/oder OE Kurzbz
	 * @param integer $firma_id ID die gelesen werden soll
	 * @param string $oe_kurzbz Organisationskurzbezeichnung
	 * @param string $firmentyp_kurzbz Optional. Firmentyp, der geliefert werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function get_firmaorganisationseinheit($firma_id='', $oe_kurzbz='', $firmentyp_kurzbz='')
	{
		$this->result = array();
		$this->errormsg = '';
		if($firma_id && !is_numeric($firma_id))
		{
			$this->errormsg = 'Firma_id ist ungueltig';
			return false;
		}

		$qry =" select tbl_firma.*  ";
		$qry.=" ,tbl_firma_organisationseinheit.firma_organisationseinheit_id ,tbl_firma_organisationseinheit.kundennummer ,tbl_firma_organisationseinheit.oe_kurzbz ";
		$qry.=" ,tbl_organisationseinheit.oe_parent_kurzbz,tbl_organisationseinheit.bezeichnung, tbl_firma_organisationseinheit.bezeichnung as fobezeichnung, ";
		$qry.=" tbl_organisationseinheit.organisationseinheittyp_kurzbz,tbl_organisationseinheit.aktiv as oe_aktiv,tbl_organisationseinheit.mailverteiler   ";
		$qry.=" FROM public.tbl_firma";
		$qry.=" JOIN public.tbl_firma_organisationseinheit USING(firma_id)";
		$qry.=" left outer join public.tbl_organisationseinheit  on ( tbl_organisationseinheit.oe_kurzbz=tbl_firma_organisationseinheit.oe_kurzbz ) ";
		$qry.=" WHERE true ";

		if($firma_id!='')
			$qry.=" AND tbl_firma_organisationseinheit.firma_id=".$this->db_add_param($firma_id, FHC_INTEGER);
		if($oe_kurzbz!='')
			$qry.=" AND tbl_firma_organisationseinheit.oe_kurzbz=".$this->db_add_param($oe_kurzbz);
		if($firmentyp_kurzbz!='')
			$qry.=" AND firmentyp_kurzbz=".$this->db_add_param($firmentyp_kurzbz);

		$qry.=" ORDER BY tbl_firma.name, tbl_firma_organisationseinheit.oe_kurzbz;";
		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fa = new firma();

				$fa->firma_id = $row->firma_id;
				$fa->name = $row->name;
				$fa->anmerkung = $row->anmerkung;
				$fa->lieferbedingungen = $row->lieferbedingungen;
				$fa->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fa->updateamum = $row->updateamum;
				$fa->updatevon = $row->updatevon;
				$fa->insertamum = $row->insertamum;
				$fa->insertvon = $row->insertvon;
				$fa->ext_id = $row->ext_id;
				$fa->schule = $this->db_parse_bool($row->schule);
				$fa->steuernummer = $row->steuernummer;
				$fa->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fa->aktiv = $this->db_parse_bool($row->aktiv);
				$fa->finanzamt = $row->finanzamt;
				$fa->partner_code = $row->partner_code;
				$fa->lieferant = $this->db_parse_bool($row->lieferant);
				$fa->oe_kurzbz = $row->oe_kurzbz;
				$fa->firma_organisationseinheit_id = $row->firma_organisationseinheit_id;
				$fa->oe_parent_kurzbz = $row->oe_parent_kurzbz;
				$fa->organisationseinheittyp_kurzbz = $row->organisationseinheittyp_kurzbz;
				$fa->bezeichnung = $row->bezeichnung;
				$fa->fobezeichnung = $row->fobezeichnung;
				$fa->kundennummer = $row->kundennummer;

				$fa->oe_aktiv = $this->db_parse_bool($row->oe_aktiv);
				$fa->mailverteiler = $this->db_parse_bool($row->mailverteiler);

				$this->result[]=$fa;
			}
			return $this->result;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}

	/**
	 * Laedt Firma -  Organisationseinheiten nach Zwischentabellen ID
	 * @param $firma_organisationseinheit_id  Zwischentabellen ID
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load_firmaorganisationseinheit($firma_organisationseinheit_id='')
	{
		$this->result = array();
		$this->errormsg = '';


		$qry =" select *  ";
		$qry.=" FROM public.tbl_firma_organisationseinheit ";
		$qry.=" WHERE tbl_firma_organisationseinheit.firma_organisationseinheit_id=".$this->db_add_param($firma_organisationseinheit_id,FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->firma_id = $row->firma_id;

				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->firma_organisationseinheit_id = $row->firma_organisationseinheit_id;
				$this->bezeichnung = $row->bezeichnung;
				$this->kundennummer = $row->kundennummer;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}


	/**
	 * Loescht den Firma/Organisations Datenensatz mit der ID die uebergeben wird
	 * @param $firma_organisationseinheit_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deleteorganisationseinheit($firma_organisationseinheit_id)
	{
		if(!is_numeric($firma_organisationseinheit_id))
		{
			$this->errormsg = 'Organisationseinheit/Firma_id ist ungueltig';
			return false;
		}
		$qry = "DELETE FROM public.tbl_firma_organisationseinheit WHERE firma_organisationseinheit_id=".$this->db_add_param($firma_organisationseinheit_id, FHC_INTEGER);

		if($this->db_query($qry))
			return true;
		else
		{
			$this->errormsg = 'Fehler beim Loeschen der Daten';
			return false;
		}
	}
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $firma_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveorganisationseinheit()
	{
		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO public.tbl_firma_organisationseinheit (firma_id,oe_kurzbz,
					bezeichnung,kundennummer, updateamum, updatevon, insertamum, insertvon) VALUES('.
				$this->db_add_param($this->firma_id, FHC_INTEGER).', '.
				$this->db_add_param($this->oe_kurzbz).', '.
				$this->db_add_param($this->bezeichnung).', '.
				$this->db_add_param($this->kundennummer).', '.
				$this->db_add_param($this->updateamum).', '.
				$this->db_add_param($this->updatevon).', '.
				$this->db_add_param($this->insertamum).', '.
				$this->db_add_param($this->insertvon).' ); ';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			//Pruefen ob firma_id eine gueltige Zahl ist
			if(!is_numeric($this->firma_id))
			{
				$this->errormsg = 'firma_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE public.tbl_firma_organisationseinheit SET '.
				'firma_id='.$this->db_add_param($this->firma_id, FHC_INTEGER).', '.
				'oe_kurzbz='.$this->db_add_param($this->oe_kurzbz).', '.
				'bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
				'kundennummer='.$this->db_add_param($this->kundennummer).', '.
				'updateamum= now(), '.
				'updatevon='.$this->db_add_param($this->updatevon).' '.
				'WHERE firma_organisationseinheit_id='.$this->db_add_param($this->firma_organisationseinheit_id, FHC_INTEGER).';';
		}
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//Sequence lesen
				$qry="SELECT currval('public.tbl_firma_organisationseinhei_firma_organisationseinheit_id_seq') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->firma_organisationseinheit_id = $row->id;
						$this->db_query('COMMIT;');
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
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Firma-Datensatzes';
			return false;
		}
		return $this->firma_organisationseinheit_id;
	}

	/**
	 * Teilt einer Firma ein Mobilitaetsprogramm zu
	 *
	 * @param $firma_id
	 * @param $mobilitaetsprogramm_code
	 * @return boolean
	 */
	function addMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code)
	{
		if(!$this->existsMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code))
		{
			$qry = "INSERT INTO public.tbl_firma_mobilitaetsprogramm(firma_id, mobilitaetsprogramm_code) VALUES(".
				$this->db_add_param($firma_id, FHC_INTEGER).','.
				$this->db_add_param($mobilitaetsprogramm_code, FHC_INTEGER).');';

			if($this->db_query($qry))
				return true;
			else
				return false;
		}
		else
			return true;
	}

	/**
	 * Prueft ob eine Mobilitaetsprogrammzuordnung zu einer Firma existiert
	 * @param $firma_id
	 * @param $mobilitaetsprogramm_code
	 * @return boolean
	 */
	function existsMobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_firma_mobilitaetsprogramm
				WHERE
					firma_id=".$this->db_add_param($firma_id, FHC_INTEGER)."
					AND mobilitaetsprogramm_code=".$this->db_add_param($mobilitaetsprogramm_code, FHC_INTEGER).';';
		if($this->db_query($qry))
		{
			if($this->db_num_rows()>0)
				return true;
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Entfernt die Zuordnung zu einem Mobilitaetsprogramm
	 * @param $firma_id
	 * @param $mobilitaetsprogramm_code
	 * @return boolean
	 */
	function deletemobilitaetsprogramm($firma_id, $mobilitaetsprogramm_code)
	{
		$qry = "DELETE FROM public.tbl_firma_mobilitaetsprogramm WHERE
				firma_id=".$this->db_add_param($firma_id, FHC_INTEGER)."
				AND mobilitaetsprogramm_code=".$this->db_add_param($mobilitaetsprogramm_code, FHC_INTEGER).';';
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
	* Lädt Alle firmen die zu einem bestimmten mobilitaetsprogramm zugeordnet sind
	* @param $mobilitaetsprogramm_code
	* @return boolean
	*/
	function getFirmenMobilitaetsprogramm($mobilitaetsprogramm_code)
	{
		$qry = 'SELECT * FROM public.tbl_firma JOIN public.tbl_firma_mobilitaetsprogramm USING(firma_id) WHERE mobilitaetsprogramm_code ='.$this->db_add_param($mobilitaetsprogramm_code, FHC_INTEGER).';';

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$fi = new firma();

				$fi->firma_id = $row->firma_id;
				$fi->name = $row->name;
				$fi->anmerkung = $row->anmerkung;
				$fi->lieferbedingungen = $row->lieferbedingungen;
				$fi->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$fi->updateamum = $row->updateamum;
				$fi->updatevon = $row->updatevon;
				$fi->insertamum = $row->insertamum;
				$fi->insertvon = $row->insertvon;
				$fi->ext_id = $row->ext_id;
				$fi->schule = $this->db_parse_bool($row->schule);
				$fi->steuernummer = $row->steuernummer;
				$fi->gesperrt = $this->db_parse_bool($row->gesperrt);
				$fi->aktiv = $this->db_parse_bool($row->aktiv);
				$fi->finanzamt = $row->finanzamt;
				$fi->partner_code = $row->partner_code;
				$fi->lieferant = $this->db_parse_bool($row->lieferant);

				$this->result[] = $fi;
			}

		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}

	}

	/**
	 * Laedt Firmen die eine PartnerCode haben
	 * @param  $firma_id ID der zu ladenden Funktion
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getFirmaPartner()
	{
		$qry = "SELECT * FROM public.tbl_firma WHERE partner_code is not null;";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$obj = new stdClass();

				$obj->firma_id = $row->firma_id;
				$obj->name = $row->name;
				$obj->anmerkung = $row->anmerkung;
				$obj->lieferbedingungen = $row->lieferbedingungen;
				$obj->firmentyp_kurzbz = $row->firmentyp_kurzbz;
				$obj->updateamum = $row->updateamum;
				$obj->updatevon = $row->updatevon;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->ext_id = $row->ext_id;
				$obj->schule = $this->db_parse_bool($row->schule);
				$obj->steuernummer = $row->steuernummer;
				$obj->gesperrt = $this->db_parse_bool($row->gesperrt);
				$obj->aktiv = $this->db_parse_bool($row->aktiv);
				$obj->finanzamt = $row->finanzamt;
				$obj->partner_code = $row->partner_code;
				$obj->lieferant = $this->db_parse_bool($row->lieferant);

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Datensatzes';
			return false;
		}
	}
}
?>
