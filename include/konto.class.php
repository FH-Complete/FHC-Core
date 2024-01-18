<?php
/* Copyright (C) 2007 fhcomplete.org
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
 * Klasse Konto
 * @create 2007-05-14
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/'.EXT_FKT_PATH.'/generateZahlungsreferenz.inc.php');

class konto extends basis_db
{
	public $new;
	public $result = array();
	public $buch_nr = array();
	public $buch_date = array();

	//Tabellenspalten
	public $buchungsnr;
	public $person_id;
	public $studiengang_kz;
	public $studiensemester_kurzbz;
	public $buchungsnr_verweis;
	public $betrag;
	public $buchungsdatum;
	public $buchungstext;
	public $mahnspanne;
	public $buchungstyp_kurzbz;
	public $updateamum;
	public $updatevon;
	public $insertamum;
	public $insertvon;
	public $ext_id;
	public $anrede;
	public $titelpost;
	public $titelpre;
	public $nachname;
	public $vorname;
	public $vornamen;
	public $standardbetrag;
	public $standardtext;
	public $aktiv;
	public $credit_points;
	public $zahlungsreferenz;
	public $anmerkung;

	/**
	 * Konstruktor
	 * @param $buchungsnr Nr der zu ladenden Buchung (default=null)
	 */
	public function __construct($buchungsnr=null)
	{
		parent::__construct();

		if($buchungsnr!=null)
			$this->load($buchungsnr);
	}

	/**
	 * Laedt die Funktion mit der ID $buchungsnr
	 * @param  $buchungsnr ID der zu ladenden  Email
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($buchungsnr)
	{
		if(!is_numeric($buchungsnr))
		{
			$this->errormsg = 'Buchungsnr muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT tbl_konto.*, anrede, titelpost, titelpre, nachname, vorname, vornamen, credit_points
			FROM public.tbl_konto JOIN public.tbl_person USING (person_id) WHERE buchungsnr=".$this->db_add_param($buchungsnr, FHC_INTEGER);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->buchungsnr = $row->buchungsnr;
				$this->person_id = $row->person_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->buchungsnr_verweis = $row->buchungsnr_verweis;
				$this->betrag = $row->betrag;
				$this->buchungsdatum = $row->buchungsdatum;
				$this->buchungstext = $row->buchungstext;
				$this->mahnspanne = $row->mahnspanne;
				$this->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$this->updatamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->ext_id = $row->ext_id;
				$this->anrede = $row->anrede;
				$this->titelpost = $row->titelpost;
				$this->titelpre = $row->titelpre;
				$this->nachname = $row->nachname;
				$this->vorname = $row->vorname;
				$this->vornamen = $row->vornamen;
				$this->credit_points = $row->credit_points;
				$this->zahlungsreferenz = $row->zahlungsreferenz;
				$this->anmerkung = $row->anmerkung;
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
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Prueft die Variablen auf gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		$this->betrag = str_replace(',','.',$this->betrag);
		if(!is_numeric($this->betrag))
		{
			$this->errormsg = "Betrag muss eine gueltige Zahl sein";
			return false;
		}

		if(!is_numeric($this->studiengang_kz))
		{
			$this->errormsg = "Die Studiengangskennzahl ist ungueltig";
			return false;
		}

		if($this->buchungstyp_kurzbz=='')
		{
			$this->errormsg = "Es wurde kein Buchungstyp angegeben";
			return false;
		}

		if(!is_numeric($this->person_id))
		{
			$this->errormsg = "Person_id ist ungueltig";
			return false;
		}

		if($this->mahnspanne!='' && !is_numeric($this->mahnspanne))
		{
			$this->errormsg = "Die Mahnspanne muss eine gueltige Zahl sein";
			return false;
		}

		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz aktualisiert
	 * @param $new true wenn insert false wenn update
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new=null)
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($new==null)
			$new = $this->new;

		if($new)
		{

			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO public.tbl_konto (person_id, studiengang_kz, studiensemester_kurzbz, buchungsnr_verweis, betrag, buchungsdatum, buchungstext, mahnspanne, buchungstyp_kurzbz, updateamum, updatevon, insertamum, insertvon, credit_points, zahlungsreferenz, anmerkung) VALUES('.
			     $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			     $this->db_add_param($this->studiengang_kz, FHC_INTEGER).', '.
			     $this->db_add_param($this->studiensemester_kurzbz).', '.
			     $this->db_add_param($this->buchungsnr_verweis, FHC_INTEGER).', '.
			     $this->db_add_param($this->betrag).', '.
			     $this->db_add_param($this->buchungsdatum).', '.
			     $this->db_add_param($this->buchungstext).', '.
			     $this->db_add_param($this->mahnspanne, FHC_INTEGER).', '.
			     $this->db_add_param($this->buchungstyp_kurzbz).', '.
			     $this->db_add_param($this->updateamum).', '.
			     $this->db_add_param($this->updatevon).', '.
			     $this->db_add_param($this->insertamum).', '.
			     $this->db_add_param($this->insertvon).', '.
			     $this->db_add_param($this->credit_points).', '.
			     $this->db_add_param($this->zahlungsreferenz).', '.
			     $this->db_add_param($this->anmerkung).');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes
			$qry = 'UPDATE public.tbl_konto SET '.
				   ' person_id='.$this->db_add_param($this->person_id, FHC_INTEGER).','.
				   ' studiengang_kz='.$this->db_add_param($this->studiengang_kz, FHC_INTEGER).','.
				   ' studiensemester_kurzbz='.$this->db_add_param($this->studiensemester_kurzbz).','.
				   ' buchungsnr_verweis='.$this->db_add_param($this->buchungsnr_verweis, FHC_INTEGER).','.
				   ' betrag='.$this->db_add_param($this->betrag).','.
				   ' buchungsdatum='.$this->db_add_param($this->buchungsdatum).','.
				   ' buchungstext='.$this->db_add_param($this->buchungstext).','.
				   ' mahnspanne='.$this->db_add_param($this->mahnspanne, FHC_INTEGER).','.
				   ' buchungstyp_kurzbz='.$this->db_add_param($this->buchungstyp_kurzbz).','.
				   ' updateamum='.$this->db_add_param($this->updateamum).','.
				   ' updatevon='.$this->db_add_param($this->updatevon).','.
				   ' insertamum='.$this->db_add_param($this->insertamum).','.
				   ' insertvon='.$this->db_add_param($this->insertvon).','.
				   ' credit_points='.$this->db_add_param($this->credit_points).','.
				   ' zahlungsreferenz='.$this->db_add_param($this->zahlungsreferenz).','.
				   ' anmerkung='.$this->db_add_param($this->anmerkung).
				   " WHERE buchungsnr='".$this->db_add_param($this->buchungsnr, FHC_INTEGER)."';";

		}

		if($this->db_query($qry))
		{
				if($new)
				{
					$qry = "SELECT currval('public.tbl_konto_buchungsnr_seq') as id";
					if($this->db_query($qry))
					{
						if($row = $this->db_fetch_object())
						{
							$this->buchungsnr = $row->id;

							//Zahlungsreferenz generieren
							if(strlen($this->buchungsnr_verweis) == 0)
							{
								if(!$this->addZahlungsreferenz($this->buchungsnr))
								{
									$this->db_query("ROLLBACK;");
									return false;
								}
							}
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
				return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return true;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param buchungsnr ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($buchungsnr)
	{
		//Pruefen ob Verweise auf diese Buchung Vorhanden sind
		$qry = "SELECT count(*) as anzahl FROM public.tbl_konto WHERE buchungsnr_verweis=".$this->db_add_param($buchungsnr, FHC_INTEGER);
		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Bitte zuerst die zugeordneten Buchungen loeschen';
					return false;
				}
				else
				{
					//Wenn keine Verweise Vorhanden sind, dann die Buchung loeschen
					$qry = "DELETE FROM public.tbl_konto WHERE buchungsnr=".$this->db_add_param($buchungsnr, FHC_INTEGER);
					if($this->db_query($qry))
						return true;
					else
					{
						$this->errormsg = 'Fehler beim Loeschen der Buchung';
						return false;
					}
				}
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Verweise';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Verweise';
			return false;
		}
	}

	/**
	 * Laedt alle Buchungen einer Person
	 * und legt diese geordnet in ein Array
	 * @param person_id, filter
	 * @return true wenn ok, false wenn fehler
	 */
	public function getBuchungen($person_id, $filter='alle', $studiengang_kz='')
	{
		if(!is_numeric($person_id))
		{
			$this->errormsg = 'Person_id muss eine gueltige Zahl sein';
			return false;
		}

		if($studiengang_kz!='')
			$stgwhere = " AND tbl_konto.studiengang_kz=".$this->db_add_param($studiengang_kz, FHC_INTEGER)." ";
		else
			$stgwhere = '';

		if($filter=='offene')
		{
			//Alle Buchungen und 'darunterliegende' holen die noch offen sind
			$qry = "SELECT tbl_konto.*, anrede, titelpost, titelpre, nachname, vorname, vornamen
					FROM public.tbl_konto JOIN public.tbl_person USING (person_id)
					WHERE (buchungsnr in (SELECT buchungsnr FROM public.tbl_konto as konto_a WHERE
									(betrag + (SELECT CASE WHEN sum(betrag) is null THEN 0
											            ELSE sum(betrag) END
										         FROM public.tbl_konto WHERE buchungsnr_verweis=konto_a.buchungsnr))<>0
									AND person_id=".$this->db_add_param($person_id, FHC_INTEGER).") OR
					buchungsnr_verweis in (SELECT buchungsnr FROM public.tbl_konto as konto_a WHERE
									(betrag + (SELECT CASE WHEN sum(betrag) is null THEN 0
														ELSE sum(betrag) END
												 FROM public.tbl_konto WHERE buchungsnr_verweis=konto_a.buchungsnr))<>0
									AND person_id=".$this->db_add_param($person_id, FHC_INTEGER).")) $stgwhere ORDER BY buchungsdatum";
		}
		else
			$qry = "SELECT tbl_konto.*, anrede, titelpost, titelpre, nachname, vorname, vornamen
					FROM public.tbl_konto JOIN public.tbl_person USING (person_id)
					WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER)." $stgwhere ORDER BY buchungsdatum";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$buchung = new konto();

				$buchung->buchungsnr = $row->buchungsnr;
				$buchung->person_id = $row->person_id;
				$buchung->studiengang_kz = $row->studiengang_kz;
				$buchung->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$buchung->buchungsnr_verweis = $row->buchungsnr_verweis;
				$buchung->betrag = $row->betrag;
				$buchung->buchungsdatum = $row->buchungsdatum;
				$buchung->buchungstext = $row->buchungstext;
				$buchung->mahnspanne = $row->mahnspanne;
				$buchung->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$buchung->updatamum = $row->updateamum;
				$buchung->updatevon = $row->updatevon;
				$buchung->insertamum = $row->insertamum;
				$buchung->insertvon = $row->insertvon;
				$buchung->ext_id = $row->ext_id;
				$buchung->anrede = $row->anrede;
				$buchung->titelpost = $row->titelpost;
				$buchung->titelpre = $row->titelpre;
				$buchung->nachname = $row->nachname;
				$buchung->vorname = $row->vorname;
				$buchung->vornamen = $row->vornamen;
				$buchung->credit_points = $row->credit_points;
				$buchung->zahlungsreferenz = $row->zahlungsreferenz;
				$buchung->anmerkung = $row->anmerkung;

				if($buchung->buchungsnr_verweis!='')
				{
					$this->result[$buchung->buchungsnr_verweis]['childs'][] = $buchung;
				}
				else
				{
					$this->result[$buchung->buchungsnr]['parent'] = $buchung;
				}
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
	 * Liefert alle Buchungstypen
	 * @return true wenn ok, false wenn Fehler
	 */
	public function getBuchungstyp($aktiv=null, $typ=null)
	{
		$qry = "SELECT * FROM public.tbl_buchungstyp";

		if(!is_null($aktiv))
			$qry.=" WHERE aktiv=".$this->db_add_param($aktiv, FHC_BOOLEAN);

		if(!is_null($typ) && is_null($aktiv))
			$qry.=" WHERE buchungstyp_kurzbz=".$this->db_add_param($typ, FHC_STRING);

		if(!is_null($typ) && !is_null($aktiv))
			$qry.=" AND buchungstyp_kurzbz=".$this->db_add_param($typ, FHC_STRING);

		$qry.=" ORDER BY beschreibung";

		if($this->db_query($qry))
		{
			while($row = $this->db_fetch_object())
			{
				$typ = new konto();

				$typ->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$typ->beschreibung = $row->beschreibung;
				$typ->standardbetrag = $row->standardbetrag;
				$typ->standardtext = $row->standardtext;
				$typ->credit_points = $row->credit_points;
				$typ->aktiv = $this->db_parse_bool($row->aktiv);

				$this->result[] = $typ;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Berechnet den offenen Betrag
	 * einer Buchung
	 */
	public function getDifferenz($buchungsnr)
	{
		$qry = "SELECT
					sum(betrag) as differenz FROM public.tbl_konto
				WHERE
					(buchungsnr=".$this->db_add_param($buchungsnr, FHC_INTEGER)." OR buchungsnr_verweis=".$this->db_add_param($buchungsnr, FHC_INTEGER).")
				OR
					(buchungsnr=(SELECT buchungsnr_verweis FROM public.tbl_konto WHERE buchungsnr=".$this->db_add_param($buchungsnr, FHC_INTEGER).") OR buchungsnr_verweis=(SELECT buchungsnr_verweis FROM public.tbl_konto WHERE buchungsnr=".$this->db_add_param($buchungsnr, FHC_INTEGER)."))";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
				return $row->differenz*(-1);
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Differenz';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Differenz';
			return false;
		}
	}


	/**
	 * ueberprueft, ob studiengebuehr gebucht ist fuer
	 * student_uid und studiensemester
	 * gibt true/false zurueck und setzt bei true das buchungsdatum $this->buchungsdatum
	 */
	public function checkStudienbeitrag($uid, $stsem)
	{
		$subqry = "SELECT tbl_konto.buchungsnr, tbl_konto.buchungsdatum FROM public.tbl_konto, public.tbl_benutzer, public.tbl_student
					WHERE
						tbl_konto.studiensemester_kurzbz = ".$this->db_add_param($stsem)."
						AND tbl_benutzer.uid = ".$this->db_add_param($uid)."
						AND tbl_benutzer.uid = tbl_student.student_uid
						AND tbl_benutzer.person_id = tbl_konto.person_id
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz = 'Studiengebuehr' ORDER BY buchungsnr";

		if($this->db_query($subqry))
		{
			if ($this->db_num_rows()==0)
				return false;
			else
			{
				while ($subrow = $this->db_fetch_object())
				{
						$buch_nr[] = $subrow->buchungsnr;
						$buch_date[] = $subrow->buchungsdatum;
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}


		$qry = "SELECT sum(betrag) as differenz FROM public.tbl_konto
				WHERE buchungsnr=".$this->db_add_param($buch_nr[0])." OR buchungsnr_verweis=".$this->db_add_param($buch_nr[0]);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				if ($row->differenz >= 0)
				{
					$this->buchungsdatum = isset($buch_date[1])?$buch_date[1]:'';
					return true;
				}
				else
					return false;
			}
			else
			{
				$this->errormsg = 'Fehler beim Ermitteln der Differenz';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Ermitteln der Differenz';
			return false;
		}
	}

	/**
	 * Überprüft, ob das Konto einer Person ausgeglichen ist, oder ob noch Zahlungen offen sind
	 * @param int $person_id ID der Person, die geprüft werden soll.
	 * @param bool $aktuelleBuchungenOnly True, wenn nur Zahlungen mit Buchungsdatum <= aktuelles Datum berücksichtigt werden sollen.
	 * @return boolean true wenn ausgeglichen, false wenn Zahlungen offen, false mit errormsg wenn ein Fehler aufgetreten ist
	 */
	public function checkKontostand($person_id, $aktuelleBuchungenOnly = false)
	{
		$qry = "SELECT sum(betrag) as summe
				FROM public.tbl_konto
				WHERE person_id=".$this->db_add_param($person_id);

		if($aktuelleBuchungenOnly)
			$qry .= " AND buchungsdatum <= now()";

		if ($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object())
			{
				if ($row->summe >= 0)
					return true;
				else
					return false;
			}
			else
			{
				$this->errormsg = "Fehler beim Holen der Daten";
				return false;
			}
		}
		else
		{
			$this->errormsg = "Fehler bei der Datenbankabfrage";
			return false;
		}
	}


    /**
	 * ueberprueft, ob studiengebuehr gebucht ist fuer
	 * student_uid und studiensemester
	 * gibt true/false zurueck und setzt bei true das buchungsdatum $this->buchungsdatum
	 */
	public function getLastStudienbeitrag($uid)
	{
		$subqry = "SELECT tbl_konto.buchungsnr, tbl_konto.buchungsdatum, tbl_konto.buchungsnr_verweis, tbl_konto.studiensemester_kurzbz FROM public.tbl_konto, public.tbl_benutzer, public.tbl_student
					WHERE
						tbl_benutzer.uid = ".$this->db_add_param($uid)."
						AND tbl_benutzer.uid = tbl_student.student_uid
						AND tbl_benutzer.person_id = tbl_konto.person_id
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz = 'Studiengebuehr' ORDER BY buchungsnr DESC";

		if($result = $this->db_query($subqry))
		{
			if ($this->db_num_rows($result)==0)
				return false;
			else
			{
				while ($subrow = $this->db_fetch_object($result))
				{
                    if($subrow->buchungsnr_verweis != '')
                    {
                        $qry = "SELECT sum(betrag) as differenz FROM public.tbl_konto
                            WHERE buchungsnr=".$this->db_add_param($subrow->buchungsnr_verweis, FHC_INTEGER)." OR buchungsnr_verweis=".$this->db_add_param($subrow->buchungsnr_verweis, FHC_INTEGER).";";

                        if($result_test = $this->db_query($qry))
                        {
                            if($row = $this->db_fetch_object($result_test))
                            {
                                if ($row->differenz == 0)
                                {
                                    return $subrow->studiensemester_kurzbz;
                                }
                            }

                        }
                        else
                        {
                            $this->errormsg = 'Fehler beim Ermitteln der Differenz';
                            return false;
                        }
                    }
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 * ueberprueft, ob studiengebuehr gebucht ist fuer
	 * student_uid und studiensemester
	 * gibt true/false zurueck und setzt bei true das buchungsdatum $this->buchungsdatum
	 */
	public function getLastStSemBuchungstypen($uid, $buchungstyp_kurzbz_array, $studiensemester_kurzbz=null)
	{
		$subqry = "SELECT tbl_konto.buchungsnr, tbl_konto.buchungsdatum, tbl_konto.buchungsnr_verweis, tbl_konto.studiensemester_kurzbz
					FROM
						public.tbl_konto
						JOIN public.tbl_benutzer USING(person_id)
						JOIN public.tbl_student ON(uid=student_uid)
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						tbl_benutzer.uid = ".$this->db_add_param($uid)."
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz in(".$this->db_implode4SQL($buchungstyp_kurzbz_array).")";
		if(!is_null($studiensemester_kurzbz))
			$subqry.=" AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz);

		$subqry.="
					ORDER BY tbl_studiensemester.start DESC";

		if($result = $this->db_query($subqry))
		{
			if ($this->db_num_rows($result)==0)
				return false;
			else
			{
				while ($subrow = $this->db_fetch_object($result))
				{
                    if($subrow->buchungsnr_verweis != '')
                    {
                        $qry = "SELECT sum(betrag) as differenz FROM public.tbl_konto

                            WHERE buchungsnr=".$this->db_add_param($subrow->buchungsnr_verweis, FHC_INTEGER)." OR buchungsnr_verweis=".$this->db_add_param($subrow->buchungsnr_verweis, FHC_INTEGER).";";

                        if($result_test = $this->db_query($qry))
                        {
                            if($row = $this->db_fetch_object($result_test))
                            {
                                if ($row->differenz == 0)
                                {
                                    return $subrow->studiensemester_kurzbz;
                                }
                            }

                        }
                        else
                        {
                            $this->errormsg = 'Fehler beim Ermitteln der Differenz';
                            return false;
                        }
                    }
				}
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	/**
	 *
	 * Gibt den Betrag der Bezahlten Studiengebühr eines Semesters zurück
	 * @param $uid StudentUID
	 * @param $stsem Studiensemester_kurzbz
	 * @param $studiengang_kz Studiengang kurzbz
	 */
	public function getStudiengebuehrGesamt($uid, $stsem, $studiengang_kz = null)
	{
		$qry = "select sum(betrag) as betrag from public.tbl_konto
				join public.tbl_benutzer benutzer using(person_id)
				where uid=".$this->db_add_param($uid)." and studiensemester_kurzbz = ".$this->db_add_param($stsem)."
				and buchungstyp_kurzbz in('Studiengebuehr','StudiengebuehrAnzahlung','StudiengebuehrRestzahlung') and betrag > 0";
		if($studiengang_kz!= null)
		$qry.=" and studiengang_kz = ".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->betrag;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Abfrage aufgetreten';
			return false;
		}
	}

	/**
	 * Gibt den Betrag des bezahlten ÖH-Beitrags eines Semesters zurück
	 * @param $uid StudentUID
	 * @param $stsem Studiensemester_kurzbz
	 * @param $studiengang_kz Studiengang kurzbz
	 */
	public function getOehBeitragGesamt($uid, $stsem, $studiengang_kz = null)
	{
		$qry = "select sum(betrag) as betrag from public.tbl_konto
				join public.tbl_benutzer benutzer using(person_id)
				where uid=".$this->db_add_param($uid)." and studiensemester_kurzbz = ".$this->db_add_param($stsem)."
				and LOWER(buchungstyp_kurzbz) = LOWER('OEH') and betrag > 0";
		if($studiengang_kz!= null)
		$qry.=" and studiengang_kz = ".$this->db_add_param($studiengang_kz, FHC_INTEGER).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				return $row->betrag;
			}
			return false;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Abfrage aufgetreten';
			return false;
		}
	}

	/**
	 * Liefert die CreditPoints die dem Studierenden noch zur Verfuegung stehen
	 * falls dieser einschraenkungen eingetragen hat. Wenn keine Einschraenkung vorhanden ist,
	 * wird false zurueckgeliefert. Es werden die Creditpoint der Belastungen herangezogen.
	 * Die Gegenbuchung wird nicht beruecksichtigt.
	 * @return Anzahl der Verfuegbaren CreditPoints oder false falls unbeschraenkt
	 */
	public function getCreditPoints($uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT sum(credit_points) as cp
				FROM
					public.tbl_konto
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					uid=".$this->db_add_param($uid)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND buchungsnr_verweis is null
					AND credit_points is not null";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$creditpoints = $row->cp;

				if($creditpoints!='')
				{
					// Bereits verwendete CreditPoints ermitteln
					$lehrveranstaltung = new lehrveranstaltung();
					$verwendet = $lehrveranstaltung->getUsedECTS($uid, $studiensemester_kurzbz);
					return ($creditpoints-($verwendet));
				}
				else
					return false;
			}
			else
			{
				// keine Einschraenkung vorhanden
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
	 * Liefert die CreditPoints die ein Studierender für ein Studiensemester zur Verfügung hat
	 * falls dieser einschraenkungen eingetragen hat. Wenn keine Einschraenkung vorhanden ist,
	 * wird false zurueckgeliefert. Es werden die Creditpoint der Belastungen herangezogen.
	 * Die Gegenbuchung wird nicht beruecksichtigt.
	 * @return Anzahl der gekauften CreditPoints oder false falls unbeschraenkt
	 */
	public function getCreditPointsOfStudiensemester($uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT sum(credit_points) as cp
				FROM
					public.tbl_konto
					JOIN public.tbl_benutzer USING(person_id)
				WHERE
					uid=".$this->db_add_param($uid)."
					AND studiensemester_kurzbz=".$this->db_add_param($studiensemester_kurzbz)."
					AND buchungsnr_verweis is null
					AND credit_points is not null";

		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$creditpoints = $row->cp;

				if($creditpoints!='')
				{
					return $creditpoints;
				}
				else
					return false;
			}
			else
			{
				// keine Einschraenkung vorhanden
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
	 * Fügt zur erstellten Buchung eine Zahlungsreferenz hinzu
	 * @param $buchungsnr Die ID der erstellten Buchung in der Datenbank
	 * @return boolean true im Erfolgsfall, ansonsten false
	 */
	private function addZahlungsreferenz($buchungsnr)
	{
		$this->zahlungsreferenz = generateZahlungsreferenz($this->studiengang_kz, $buchungsnr);

		$qry = "UPDATE public.tbl_konto ".
				"SET zahlungsreferenz=".$this->db_add_param($this->zahlungsreferenz).
				"WHERE buchungsnr=".$this->db_add_param($buchungsnr).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim speichern der Zahlungsreferenz aufgetreten';
			return false;
		}

	}

	/**
	 * Laedt eine Buchung anhand ihrer Zahlungsreferenz
	 * @param $zahlungsreferenz
	 */
	public function loadFromZahlungsreferenz($zahlungsreferenz)
	{
		$qry = "SELECT * FROM public.tbl_konto WHERE zahlungsreferenz=".$this->db_add_param($zahlungsreferenz);

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->buchungsnr = $row->buchungsnr;
				$this->person_id = $row->person_id;
				$this->studiengang_kz = $row->studiengang_kz;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->buchungsnr_verweis = $row->buchungsnr_verweis;
				$this->betrag = $row->betrag;
				$this->buchungsdatum = $row->buchungsdatum;
				$this->buchungstext = $row->buchungstext;
				$this->mahnspanne = $row->mahnspanne;
				$this->buchungstyp_kurzbz = $row->buchungstyp_kurzbz;
				$this->updatamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->credit_points = $row->credit_points;
				$this->zahlungsreferenz = $row->zahlungsreferenz;
				$this->anmerkung = $row->anmerkung;
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
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * ueberprueft, ob leistungsstipendium gebucht ist fuer
	 * student_uid und studiensemester
	 * @param string $uid UserID
	 * @param string $stsem Studiensemester
	 * @return boolean
	 */
	public function checkLeistungsstipendium($uid, $stsem)
	{
		$subqry = "SELECT tbl_konto.buchungsnr, tbl_konto.buchungsdatum FROM public.tbl_konto, public.tbl_benutzer, public.tbl_student
					WHERE
						tbl_konto.studiensemester_kurzbz = ".$this->db_add_param($stsem)."
						AND tbl_benutzer.uid = ".$this->db_add_param($uid)."
						AND tbl_benutzer.uid = tbl_student.student_uid
						AND tbl_benutzer.person_id = tbl_konto.person_id
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz = 'Leistungsstipendium' ORDER BY buchungsnr";

		if($this->db_query($subqry))
		{
			if ($this->db_num_rows()==0)
				return false;
			else
			{
				return true;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	public function checkDoppelteBuchung($person_ids, $stsem, $typen)
	{
		$qry = "SELECT person.vorname, person.nachname
				FROM public.tbl_konto konto
					JOIN public.tbl_person person USING(person_id)
				WHERE konto.person_id IN (".$this->implode4SQL(array_filter($person_ids)).")
					AND studiensemester_kurzbz = ".$this->db_add_param($stsem)."
					AND buchungstyp_kurzbz IN (".$this->implode4SQL($typen) .")
				GROUP BY person.vorname, person.nachname
				ORDER BY person.nachname, person.vorname";
		if ($result = $this->db_query($qry))
		{
			$persons = array();
			while ($row = $this->db_fetch_object($result))
			{
				$persons[] = $row->nachname . ' ' . $row->vorname;
			}
			return $persons;
		}
		else
		{
			$this->errormsg = 'Fehler bei der Abfrage aufgetreten';
			return false;
		}
	}
}

?>
