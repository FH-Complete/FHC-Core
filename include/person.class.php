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
/*
 * Benoetigt functions.inc.php
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/datum.class.php');

class person extends basis_db
{
	public $new;				// boolean
	public $personen = array();	// person Objekt
	public $done = false;		// boolean

	//Tabellenspalten
	public $person_id;			// integer
	public $sprache;			// varchar(16)
	public $anrede;				// varchar(16)
	public $titelpost;			// varchar(32)
	public $titelpre;			// varchar(64)
	public $nachname;			// varchar(64)
	public $vorname;			// varchar(32)
	public $vornamen;			// varchar(128)
	public $gebdatum;			// date
	public $gebort;				// varchar(128)
	public $gebzeit;			// time
	public $foto;				// text
	public $anmerkungen;		// varchar(256)
	public $homepage;			// varchar(256)
	public $svnr;				// char(10)
	public $ersatzkennzeichen;	// char(10)
	public $familienstand;		// char(1)
	public $anzahlkinder;		// smalint
	public $aktiv = true;		// boolean
	public $insertamum;			// timestamp
	public $insertvon;			// varchar(16)
	public $updateamum;			// timestamp
	public $updatevon;			// varchar(16)
	public $geschlecht = 'u';		// varchar(1) - Default: undefined
	public $staatsbuergerschaft;// varchar(3)
	public $geburtsnation;		// varchar(3);
	public $ext_id;				// bigint
	public $kurzbeschreibung;	// text
	public $zugangscode = null; // varchar(32)
	public $foto_sperre = false;	// boolean
	public $matr_nr;			//varchar(32)
	public $bpk; 				//varchar(255)

	/**
	 * Konstruktor - Uebergibt die Connection und laedt optional eine Person
	 * @param	int	$personId      Person die geladen werden soll (default=null).
	 */
	public function __construct($personId = null)
	{
		parent::__construct();

		if ($personId != null)
			$this->load($personId);
	}

	/**
	 * Laedt Person mit der uebergebenen ID
	 * @param	int	$personId	ID der Person die geladen werden soll.
	 * @return bool
	 **/
	public function load($personId)
	{
		//person_id auf gueltigkeit pruefen
		if (is_numeric($personId) && $personId != '')
		{
			$qry = "SELECT person_id, sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
							gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
							familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon, ext_id,
							geschlecht, staatsbuergerschaft, geburtsnation, kurzbeschreibung, zugangscode, foto_sperre,
							matr_nr, bpk
					  FROM public.tbl_person
					 WHERE person_id = " . $this->db_add_param($personId, FHC_INTEGER);

			if (!$this->db_query($qry))
			{
				$this->errormsg = "Fehler beim Lesen der Personendaten\n";
				return false;
			}

			if ($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->sprache = $row->sprache;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->gebdatum = $row->gebdatum;
				$this->gebort = $row->gebort;
				$this->gebzeit = $row->gebzeit;
				$this->foto = $row->foto;
				$this->anmerkungen = $row->anmerkung;
				$this->homepage = $row->homepage;
				$this->svnr = $row->svnr;
				$this->ersatzkennzeichen = $row->ersatzkennzeichen;
				$this->familienstand = $row->familienstand;
				$this->anzahlkinder = $row->anzahlkinder;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->ext_id = $row->ext_id;
				$this->geschlecht = $row->geschlecht;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->geburtsnation = $row->geburtsnation;
				$this->kurzbeschreibung = $row->kurzbeschreibung;
				$this->zugangscode = $row->zugangscode;
				$this->foto_sperre = $this->db_parse_bool($row->foto_sperre);
				$this->matr_nr = $row->matr_nr;
				$this->bpk = $row->bpk;
			}
			else
			{
				$this->errormsg = "Es ist kein Personendatensatz mit dieser ID vorhanden";
				return false;
			}
			$this->new = false;
			return true;
		}
		else
		{
			$this->errormsg = "Die person_id muss eine gueltige Zahl sein";
			return false;
		}
	}

	/**
	 *
	 * Löscht den Datensatz mit der übergebenen person_id
	 * @param	int		$personId	PK aus tbl_person.
	 * @return	bool
	 */
	public function delete($personId)
	{
		$qry = "DELETE from public.tbl_person where person_id = ".$this->db_add_param($personId, FHC_INTEGER).";";

		if ($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = "Es ist ein Fehler beim Löschen der Person aufgetreten";
			return false;
		}
	}

	/**
	 * Prueft die Variablen vor dem Speichern
	 * auf Gueltigkeit.
	 * @return true wenn ok, false im Fehlerfall
	 **/
	public function validate()
	{
		$this->nachname = trim($this->nachname);
		$this->vorname = trim($this->vorname);
		$this->vornamen = trim($this->vornamen);
		$this->anrede = trim($this->anrede);
		$this->titelpost = trim($this->titelpost);
		$this->titelpre = trim($this->titelpre);

		if (mb_strlen($this->sprache) > 16)
		{
			$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->anrede) > 16)
		{
			$this->errormsg = 'Anrede darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->titelpost) > 32)
		{
			$this->errormsg = 'Titelpost darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->titelpre) > 64)
		{
			$this->errormsg = 'Titelpre darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->nachname) > 64)
		{
			$this->errormsg = 'Nachname darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if ($this->nachname == '' || is_null($this->nachname))
		{
			$this->errormsg = 'Nachname muss eingegeben werden';
			return false;
		}

		if (mb_strlen($this->vorname) > 32)
		{
			$this->errormsg = 'Vorname darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->vornamen) > 128)
		{
			$this->errormsg = 'Vornamen darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/*if (strlen($this->gebdatum) == 0 || is_null($this->gebdatum))
		{
			$this->errormsg = "Geburtsdatum muss eingegeben werden\n";
			return false;
		}*/
		if (mb_strlen($this->gebort) > 128)
		{
			$this->errormsg = 'Geburtsort darf nicht laenger als 128 Zeichen sein';
			return false;
		}

		if (mb_strlen($this->homepage) > 256)
		{
			$this->errormsg = 'Homepage darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->svnr) > 16)
		{
			$this->errormsg = 'SVNR darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if (mb_strlen($this->matr_nr) > 32)
		{
			$this->errormsg = 'Matrikelnummer darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		if ($this->svnr != '' && mb_strlen($this->svnr) != 16 && mb_strlen($this->svnr) != 10)
		{
			$this->errormsg = 'SVNR muss 10 oder 16 Zeichen lang sein';
			return false;
		}

		if ($this->svnr != '' && mb_strlen($this->svnr) == 10)
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3, 7, 9, 0, 5, 8, 4, 2, 1, 6);
			$erg = 0;
			//Quersumme bilden
			for ($i = 0; $i < 10; $i++)
			{
				$erg += $gewichtung[$i] * $this->svnr{$i};
			}

			if ($this->svnr{3} != ($erg % 11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				$this->errormsg = 'SVNR ist ungueltig';
				return false;
			}

			if (mb_strlen($this->bpk) > 255)
			{
				$this->errormsg = 'BPK darf nicht laenger als 255 Zeichen sein';
				return false;
			}
		}

		if ($this->svnr != '')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE svnr=".$this->db_add_param($this->svnr);
			if ($this->db_query($qry))
			{
				if ($row = $this->db_fetch_object())
				{
					if ($row->person_id != $this->person_id)
					{
						$this->errormsg = 'Es existiert bereits eine Person mit dieser SVNR! Daten wurden NICHT gepeichert.';
						return false;
					}
				}
			}
		}

		if (mb_strlen($this->ersatzkennzeichen) > 10)
		{
			$this->errormsg = 'Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}

		if ($this->ersatzkennzeichen != '')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE ersatzkennzeichen=".$this->db_add_param($this->ersatzkennzeichen);
			if ($this->db_query($qry))
			{
				if ($row = $this->db_fetch_object())
				{
					if ($row->person_id != $this->person_id)
					{
						$this->errormsg = 'Es existiert bereits eine Person mit diesem Ersatzkennzeichen! Daten wurden NICHT gepeichert.';
						return false;
					}
				}
			}
		}

		if (mb_strlen($this->familienstand) > 1)
		{
			$this->errormsg = 'Familienstand ist ungueltig';
			return false;
		}
		if ($this->anzahlkinder != '' && !is_numeric($this->anzahlkinder))
		{
			$this->errormsg = 'Anzahl der Kinder ist ungueltig';
			return false;
		}
		if (!is_bool($this->aktiv))
		{
			$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if (mb_strlen($this->insertvon) > 32)
		{
			$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->updatevon) > 32)
		{
			$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if ($this->ext_id != '' && !is_numeric($this->ext_id))
		{
			$this->errormsg = 'Ext_ID ist keine gueltige Zahl';
			return false;
		}
		if (mb_strlen($this->geschlecht) > 1)
		{
			$this->errormsg = 'Geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->geburtsnation) > 3)
		{
			$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->staatsbuergerschaft) > 3)
		{
			$this->errormsg = 'Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}

		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if ($this->svnr != '' && $this->gebdatum != '')
		{
			if (mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $this->gebdatum, $regs))
			{
				//$day = sprintf('%02s',$regs[1]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[3],2,2);
			}
			elseif (mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $this->gebdatum, $regs))
			{
				//$day = sprintf('%02s',$regs[3]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[1],2,2);
			}
			else
			{
				$this->errormsg = 'Format des Geburtsdatums ist ungueltig';
				return false;
			}

			/* das muss nicht immer so sein
			$day_svnr = mb_substr($this->svnr, 4, 2);
			$month_svnr = mb_substr($this->svnr, 6, 2);
			$year_svnr = mb_substr($this->svnr, 8, 2);

			if ($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			{
				$this->errormsg = 'SVNR und Geburtsdatum passen nicht zusammen';
				return false;
			}
			*/
		}

		return true;
	}

	/**
	 * Speichert die Personendaten in die Datenbank
	 * Wenn $new auf true gesetzt ist wird ein neuer Datensatz
	 * angelegt, ansonsten der Datensatz mit $personId upgedated
	 * @return true wenn erfolgreich, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen auf Gueltigkeit pruefen
		if (!person::validate())
			return false;

		if ($this->new) //Wenn new true ist dann ein INSERT absetzen ansonsten ein UPDATE
		{
			$qry = 'INSERT INTO public.tbl_person (sprache, anrede, titelpost, titelpre, nachname, vorname, vornamen,
			                    gebdatum, gebort, gebzeit, foto, anmerkung, homepage, svnr, ersatzkennzeichen,
			                    familienstand, anzahlkinder, aktiv, insertamum, insertvon, updateamum, updatevon,
			                    geschlecht, geburtsnation, staatsbuergerschaft, kurzbeschreibung, zugangscode,
								foto_sperre, matr_nr, bpk)
			        VALUES('.$this->db_add_param($this->sprache).','.
						$this->db_add_param($this->anrede).','.
						$this->db_add_param($this->titelpost).','.
				        $this->db_add_param($this->titelpre).','.
				        $this->db_add_param($this->nachname).','.
				        $this->db_add_param($this->vorname).','.
				        $this->db_add_param($this->vornamen).','.
				        $this->db_add_param($this->gebdatum).','.
				        $this->db_add_param($this->gebort).','.
				        $this->db_add_param($this->gebzeit).','.
				        $this->db_add_param($this->foto).','.
				        $this->db_add_param($this->anmerkungen).','.
				        $this->db_add_param($this->homepage).','.
				        $this->db_add_param($this->svnr).','.
				        $this->db_add_param($this->ersatzkennzeichen).','.
				        $this->db_add_param($this->familienstand).','.
				        $this->db_add_param($this->anzahlkinder).','.
				        $this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
				        'now(),'.
				        $this->db_add_param($this->insertvon).','.
				        'now(),'.
				        $this->db_add_param($this->updatevon).','.
				        $this->db_add_param($this->geschlecht).','.
				        $this->db_add_param($this->geburtsnation).','.
				        $this->db_add_param($this->staatsbuergerschaft).','.
				        $this->db_add_param($this->kurzbeschreibung).','.
				        $this->db_add_param($this->zugangscode).','.
				        $this->db_add_param($this->foto_sperre, FHC_BOOLEAN).','.
						$this->db_add_param($this->matr_nr).','.
						$this->db_add_param($this->bpk).');';
		}
		else
		{
			//person_id auf gueltigkeit pruefen
			if (!is_numeric($this->person_id))
			{
				$this->errormsg = "person_id muss eine gueltige Zahl sein";
				return false;
			}

			$qry = 'UPDATE public.tbl_person SET'.
			       ' sprache='.$this->db_add_param($this->sprache).','.
			       ' anrede='.$this->db_add_param($this->anrede).','.
			       ' titelpost='.$this->db_add_param($this->titelpost).','.
			       ' titelpre='.$this->db_add_param($this->titelpre).','.
			       ' nachname='.$this->db_add_param($this->nachname).','.
			       ' vorname='.$this->db_add_param($this->vorname).','.
			       ' vornamen='.$this->db_add_param($this->vornamen).','.
			       ' gebdatum='.$this->db_add_param($this->gebdatum).','.
			       ' gebort='.$this->db_add_param($this->gebort).','.
			       ' gebzeit='.$this->db_add_param($this->gebzeit).','.
			       ' foto='.$this->db_add_param($this->foto).','.
			       ' anmerkung='.$this->db_add_param($this->anmerkungen).','.
			       ' homepage='.$this->db_add_param($this->homepage).','.
			       ' svnr='.$this->db_add_param($this->svnr).','.
			       ' ersatzkennzeichen='.$this->db_add_param($this->ersatzkennzeichen).','.
			       ' familienstand='.$this->db_add_param($this->familienstand).','.
			       ' anzahlkinder='.$this->db_add_param($this->anzahlkinder).','.
			       ' aktiv='.$this->db_add_param($this->aktiv, FHC_BOOLEAN).','.
			       ' updateamum=now(),'.
			       ' updatevon='.$this->db_add_param($this->updatevon).','.
			       ' geschlecht='.$this->db_add_param($this->geschlecht).','.
			       ' geburtsnation='.$this->db_add_param($this->geburtsnation).','.
			       ' staatsbuergerschaft='.$this->db_add_param($this->staatsbuergerschaft).','.
			       ' kurzbeschreibung='.$this->db_add_param($this->kurzbeschreibung).','.
				   ' foto_sperre='.$this->db_add_param($this->foto_sperre, FHC_BOOLEAN).','.
				   ' zugangscode='.$this->db_add_param($this->zugangscode).','.
				   ' matr_nr ='.$this->db_add_param($this->matr_nr).','.
				   ' bpk = '.$this->db_add_param($this->bpk).
			       ' WHERE person_id='.$this->person_id.';';
		}

		if ($this->db_query($qry))
		{
			if ($this->new)
			{
				$qry = "SELECT currval('public.tbl_person_person_id_seq') AS id;";
				if ($this->db_query($qry))
				{
					if ($row = $this->db_fetch_object())
						$this->person_id = $row->id;
					else
					{
						$this->errormsg = "Sequence konnte nicht ausgelesen werden";
						return false;
					}
				}
				else
				{
					$this->errormsg = "Fehler beim Auslesen der Sequence";
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = "Fehler beim Speichern des Person-Datensatzes";
			return false;
		}
	}

	/**
	 * Liefert die Tabellenelemente die den Kriterien der Parameter entsprechen
	 * @param string $filter String mit Vorname oder Nachname.
	 * @param string $order Sortierkriterium.
	 * @return array mit LPersonen oder false=fehler
	 */
	public function getTab($filter, $order = 'person_id')
	{
		//Filterstring trimmen und Umwandlung mittels generateSpecialCharacterString um auch Namen mit Sonderzeichen zu erhalten
		$filter = generateSpecialCharacterString(trim($filter));
		$sqlQuery = "
			SELECT
				distinct on (person_id) *
			FROM
				public.tbl_person
				LEFT JOIN public.tbl_benutzer USING(person_id)
			WHERE true ";

		if ($filter != '')
		{
			$sqlQuery .= " AND 	UPPER(nachname) ~* UPPER(".$this->db_add_param($filter).") OR
								UPPER (vorname) ~* UPPER(".$this->db_add_param($filter).") OR
								UPPER (nachname || ' ' || vorname) ~* UPPER(".$this->db_add_param($filter).") OR
								UPPER (vorname || ' ' || nachname) ~* UPPER(".$this->db_add_param($filter).") OR
								uid=".$this->db_add_param($filter);
		}

		$sqlQuery .= " ORDER BY $order";
		if ($filter == '')
			$sqlQuery .= " LIMIT 30";

		if ($this->db_query($sqlQuery))
		{
			while ($row = $this->db_fetch_object())
			{
				$l = new person();
				$l->person_id = $row->person_id;
				$l->staatsbuergerschaft = $row->staatsbuergerschaft;
				$l->geburtsnation = $row->geburtsnation;
				$l->sprache = $row->sprache;
				$l->anrede = $row->anrede;
				$l->titelpost = $row->titelpost;
				$l->titelpre = $row->titelpre;
				$l->nachname = $row->nachname;
				$l->vorname = $row->vorname;
				$l->vornamen = $row->vornamen;
				$l->gebdatum = $row->gebdatum;
				$l->gebort = $row->gebort;
				$l->gebzeit = $row->gebzeit;
				$l->foto = $row->foto;
				$l->anmerkungen = $row->anmerkung;
				$l->homepage = $row->homepage;
				$l->svnr = $row->svnr;
				$l->ersatzkennzeichen = $row->ersatzkennzeichen;
				$l->familienstand = $row->familienstand;
				$l->geschlecht = $row->geschlecht;
				$l->anzahlkinder = $row->anzahlkinder;
				$l->aktiv = $this->db_parse_bool($row->aktiv);
				$l->updateamum = $row->updateamum;
				$l->updatevon = $row->updatevon;
				$l->insertamum = $row->insertamum;
				$l->insertvon = $row->insertvon;
				$l->ext_id = $row->ext_id;
				$l->kurzbeschreibung = $row->kurzbeschreibung;
				$l->foto_sperre = $this->db_parse_bool($row->foto_sperre);
				$l->matr_nr = $row->matr_nr;
				$l->bpk = $row->bpk;
				$this->personen[] = $l;
			}
		}
		else
		{
			$this->errormsg = $this->db_last_error();
			return false;
		}
		return true;
	}


	/**
	 * Laedt alle standorte zu einer Person die dem Standort zugeordnet sind
	 * @param	int	$standortId ID des Standortes.
	 * @param	int	$personId	ID der Person die Zugeordnet ist.
	 * @param	int	$firmaId	ID der Firma zu der die standortn geladen werden sollen.
	 * @param	string	$funktionKurzbz	Funktion der Person.
	 * @param	int	$personfunktionstandortId	ID des Standorts zur Personfunktion.
	 * @return	bool	true wenn ok, false im Fehlerfall
	 */
	public function load_personfunktion($standortId = '', $personId = '', $firmaId = '', $funktionKurzbz = '', $personfunktionstandortId = '')
	{
		$this->result = array();
		$this->errormsg = '';

		//Lesen der Daten aus der Datenbank
		$qry = " SELECT tbl_person.*,
				tbl_personfunktionstandort.personfunktionstandort_id,
				tbl_personfunktionstandort.person_id,
				tbl_personfunktionstandort.funktion_kurzbz,
				tbl_personfunktionstandort.standort_id,
				tbl_personfunktionstandort.position,
				tbl_personfunktionstandort.anrede, tbl_standort.adresse_id,
				tbl_standort.kurzbz, tbl_standort.bezeichnung,
				tbl_standort.firma_id, tbl_funktion.beschreibung as funktion_beschreibung,
				tbl_funktion.aktiv as funktion_aktiv, tbl_funktion.fachbereich as funktion_fachbereich, tbl_funktion.semester as funktion_semester";
		$qry .= " FROM public.tbl_person,public.tbl_personfunktionstandort
				LEFT JOIN public.tbl_standort USING(standort_id)
				LEFT JOIN public.tbl_funktion USING(funktion_kurzbz)
			";
		$qry .= " WHERE tbl_person.person_id=tbl_personfunktionstandort.person_id";

		if ($personfunktionstandortId != '')
			$qry .= " and tbl_personfunktionstandort.personfunktionstandort_id=".$this->db_add_param($personfunktionstandortId, FHC_INTEGER);
		if (is_numeric($standortId))
			$qry .= " and tbl_personfunktionstandort.standort_id=".$this->db_add_param($standortId, FHC_INTEGER);
		if (is_numeric($personId))
			$qry .= " and tbl_personfunktionstandort.person_id=".$this->db_add_param($personId, FHC_INTEGER);
		if (is_numeric($firmaId))
			$qry .= " and public.tbl_standort.firma_id=".$this->db_add_param($firmaId, FHC_INTEGER);
		if ($funktionKurzbz != '')
			$qry .= " and tbl_personfunktionstandort.funktion_kurzbz=".$this->db_add_param($funktionKurzbz);

		if (!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		while ($row = $this->db_fetch_object())
		{
			$adrObj = new person();
			$adrObj->person_id = $row->person_id;
			$adrObj->staatsbuergerschaft = $row->staatsbuergerschaft;
			$adrObj->geburtsnation = $row->geburtsnation;
			$adrObj->sprache = $row->sprache;
			$adrObj->anrede = $row->anrede;
			$adrObj->titelpost = $row->titelpost;
			$adrObj->titelpre = $row->titelpre;
			$adrObj->nachname = $row->nachname;
			$adrObj->vorname = $row->vorname;
			$adrObj->vornamen = $row->vornamen;
			$adrObj->gebdatum = $row->gebdatum;
			$adrObj->gebort = $row->gebort;
			$adrObj->gebzeit = $row->gebzeit;
			$adrObj->foto = $row->foto;
			$adrObj->anmerkungen = $row->anmerkung;
			$adrObj->homepage = $row->homepage;
			$adrObj->svnr = $row->svnr;
			$adrObj->ersatzkennzeichen = $row->ersatzkennzeichen;
			$adrObj->familienstand = $row->familienstand;
			$adrObj->geschlecht = $row->geschlecht;
			$adrObj->anzahlkinder = $row->anzahlkinder;
			$adrObj->aktiv = $this->db_parse_bool($row->aktiv);
			$adrObj->updateamum = $row->updateamum;
			$adrObj->updatevon = $row->updatevon;
			$adrObj->insertamum = $row->insertamum;
			$adrObj->insertvon = $row->insertvon;
			$adrObj->ext_id = $row->ext_id;
			$adrObj->kurzbeschreibung = $row->kurzbeschreibung;
			$adrObj->foto_sperre = $this->db_parse_bool($row->foto_sperre);

			$adrObj->standort_id		= $row->standort_id;
			$adrObj->adresse_id		= $row->adresse_id;
			$adrObj->kurzbz			= $row->kurzbz;
			$adrObj->bezeichnung		= $row->bezeichnung;
			$adrObj->firma_id			= $row->firma_id;

			$adrObj->personfunktionstandort_id	= $row->personfunktionstandort_id;

			$adrObj->funktion_kurzbz	= $row->funktion_kurzbz;

			$adrObj->position	= $row->position;
			$adrObj->anrede	= $row->anrede;

			$adrObj->funktion_beschreibung	= $row->funktion_beschreibung;
			$adrObj->funktion_aktiv	= $this->db_parse_bool($row->funktion_aktiv);
			$adrObj->funktion_fachbereich	= $row->funktion_fachbereich;
			$adrObj->funktion_semester	= $row->funktion_semester;

			$this->result[] = $adrObj;
		}
		return true;
	}

	/**
	 *
	 * Überprüfut ob der übergebene Zugangscode einer Person zugeordnet ist und
	 * retuniert im Erfolgsfall dessen person_id
	 * @param	string	$zugangscode	Zugangscode aus tbl_person.
	 * @return bool
	 */
    public function checkZugangscode($zugangscode)
    {
        $qry = "SELECT person_id
                FROM public.tbl_person
                WHERE zugangscode=".$this->db_add_param($zugangscode, FHC_STRING);

        if ($this->db_query($qry))
        {
            if ($row = $this->db_fetch_object())
            {
                return $row->person_id;
            }
            else
                return false;
        }
        else
        {
            $this->errormsg = "Fehler bei der Abfrage aufgetreten";
            return false;
        }
    }

	/**
	 *
	 * Überprüft den übergebenen Zugangscode und retuniert die aktuelle incoming_id
	 * @param	string	$zugangscode	Zugangscode aus tbl_person.
	 * @return bool
	 */
	public function checkZugangscodeIncoming($zugangscode)
	{
		$qry = "
			SELECT
				preincoming_id
			FROM
				public.tbl_preincoming
			WHERE
				person_id = (SELECT person_id FROM public.tbl_person
							 WHERE zugangscode = " . $this->db_add_param($zugangscode) . ")
			ORDER BY insertamum DESC;";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				return $row->preincoming_id;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 *
	 * Überprüft den übergebenen Zugangscode und retuniert die aktuelle incoming_id
	 * @param	string	$zugangscode	Zugangscode aus tbl_person.
	 * @return bool
	 */
	public function checkZugangscodePerson($zugangscode)
	{
		$qry = "
			SELECT
				person_id
			FROM
				public.tbl_person
			WHERE
				zugangscode=".$this->db_add_param($zugangscode).';';

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				return $row->person_id;
			}
			else
				return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}
	}

	/**
	 *
	 * Lädt eine Person zum übergebenen Zugangscode
	 * @param	string	$zugangscode	Zugangscode aus tbl_person.
	 * @return bool
	 */
	public function getPersonFromZugangscode($zugangscode)
	{
		$qry = "SELECT * FROM public.tbl_person WHERE zugangscode=".$this->db_add_param($zugangscode).";";

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->geburtsnation = $row->geburtsnation;
				$this->sprache = $row->sprache;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->gebdatum = $row->gebdatum;
				$this->gebort = $row->gebort;
				$this->gebzeit = $row->gebzeit;
				$this->foto = $row->foto;
				$this->anmerkungen = $row->anmerkung;
				$this->homepage = $row->homepage;
				$this->svnr = $row->svnr;
				$this->ersatzkennzeichen = $row->ersatzkennzeichen;
				$this->familienstand = $row->familienstand;
				$this->geschlecht = $row->geschlecht;
				$this->anzahlkinder = $row->anzahlkinder;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->kurzbeschreibung = $row->kurzbeschreibung;
				$this->zugangscode = $row->zugangscode;
				$this->foto_sperre = $this->db_parse_bool($row->foto_sperre);
				$this->matr_nr = $row->matr_nr;
				$this->bpk = $row->bpk;
			}
			else
			{
				$this->errormsg = 'Keine Person zu Zugangscode gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage.';
			return false;
		}
		return true;
	}

	/**
	 * Prueft ob eine SVNR bereits vergeben ist, Optional kann eine Person übergeben werden die nicht
	 * beruecksichtigt werden soll
	 * @param	int	$svnr		SVNR aus tbl_person.
	 * @param	int	$personId	PK aus tbl_person.
	 * @return	bool	True wenn bereits vorhanden sonst false.
	 */
	public function checkSvnr($svnr, $personId = null)
	{
		$qry = "Select 1 from public.tbl_person where svnr =".$this->db_add_param($svnr);
		if (!is_null($personId))
			$qry .= " AND person_id!=".$this->db_add_param($personId);

		if ($result = $this->db_query($qry))
		{
			if ($this->db_num_rows($result) > 0)
				return true;
			else
				return false;
		}
	}

	/**
	 * Generiert den vollen Namen einer Person
	 * @param	bool	$allFirstnames	TRUE wenn alle Vornamen eingebunden werden sollen.
	 * @return	bool
	 */
	public function getFullName($allFirstnames = false)
	{
	    $fullname = "";
	    if ((!is_null($this->titelpre)) && ($this->titelpre != ""))
		$fullname .= $this->titelpre." ";

	    $fullname .= $this->vorname." ";

	    if (($allFirstnames) && ($this->vornamen != "") && (!is_null($this->vornamen)))
		$fullname .= $this->vornamen." ";

	    $fullname .= $this->nachname;

	    if ((!is_null($this->titelpost)) && ($this->titelpost != ""))
			$fullname .= " ".$this->titelpost;

	    return $fullname;
	}

	/**
	 * Laedt Personendaten eines Benutzers
	 * @param	string	$uid	DB-Attr: tbl_benutzer.uid .
	 * @return	bool
	 */
	public function getPersonFromBenutzer($uid)
	{
		$qry = "SELECT
					*
				FROM
					public.tbl_person
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					uid=".$this->db_add_param($uid, FHC_STRING);

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				$this->person_id = $row->person_id;
				$this->staatsbuergerschaft = $row->staatsbuergerschaft;
				$this->geburtsnation = $row->geburtsnation;
				$this->sprache = $row->sprache;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->gebdatum = $row->gebdatum;
				$this->gebort = $row->gebort;
				$this->gebzeit = $row->gebzeit;
				$this->foto = $row->foto;
				$this->anmerkungen = $row->anmerkung;
				$this->homepage = $row->homepage;
				$this->svnr = $row->svnr;
				$this->ersatzkennzeichen = $row->ersatzkennzeichen;
				$this->familienstand = $row->familienstand;
				$this->geschlecht = $row->geschlecht;
				$this->anzahlkinder = $row->anzahlkinder;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->kurzbeschreibung = $row->kurzbeschreibung;
				$this->zugangscode = $row->zugangscode;
				$this->foto_sperre = $this->db_parse_bool($row->foto_sperre);
				$this->matr_nr = $row->matr_nr;
				$this->uid = $row->uid;
				$this->aktiv = $this->db_parse_bool($row->aktiv);
				$this->alias = $row->alias;
				$this->updateaktivvon = $row->updateaktivvon;
				$this->updateaktivam = $row->updateaktivam;
				$this->aktivierungscode = $row->aktivierungscode;
				$this->bpk = $row->bpk;
				return true;
			}
			else
			{
				$this->errormsg = 'Keine Personendaten zu dieser UID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Personendaten";
			return false;
		}
	}

	/**
	 * Laedt eine Person anhand der Matrikelnummer
	 *
	 * @param $matr_nr Matrikelnummer
	 * @return boolean true wenn ok, false im Fehlerfall.
	 */
	public function getPersonByMatrNr($matr_nr)
	{
		$qry = "SELECT person_id FROM public.tbl_person WHERE matr_nr=".$this->db_add_param($matr_nr);

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				return $this->load($row->person_id);
			}
			else
			{
				$this->errormsg = 'Person nicht gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function getFullNameFromBenutzer($uid)
	{
		$qry = "SELECT
					vorname, nachname
				FROM
					public.tbl_person
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					uid=".$this->db_add_param($uid, FHC_STRING);

		if ($this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				return (string)$row->vorname.' '.$row->nachname;
			}
			else
			{
				$this->errormsg = 'Keine Personendaten zu dieser UID gefunden';
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler beim Laden der Personendaten";
			return false;
		}
	}
}
