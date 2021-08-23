<?php
/* Copyright (C) 2011 FH Technikum-Wien
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
 * 			Andreas Österreicher <andreas.oesterreicher@technikum-wien.at>
 */
/**
 * Klasse projekt
 *
 * Verwaltet die Projekte
 * @param string $projekt_kurzbz primary key Projektname.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class projekt extends basis_db
{
	public $new;            // boolean
	public $result = array();    // projekt Objekt

	//Tabellenspalten
	public $projekt_kurzbz;    // string
	public $nummer;            // string
	public $titel;            // string
	public $beschreibung;    // string
	public $beginn;            // date
	public $ende;            // date
	public $oe_kurzbz;        // string
	public $insertamum;        // timestamp
	public $insertvon;        // string
	public $updateamum;        // timestamp
	public $updatevon;        // string
	public $budget;
	public $farbe;
	public $anzahl_ma;        // integer
	public $aufwand_pt;        // integer


	/**
	 * Konstruktor
	 * @param string $projekt_kurzbz ID der Projektarbeit, die geladen werden soll (Default=null).
	 */
	public function __construct($projekt_kurzbz = null)
	{
		parent::__construct();

		if ($projekt_kurzbz != null)
			$this->load($projekt_kurzbz);
	}

	/**
	 * Laedt die Projek mit der Kurzbezeichnung $projekt_kurzbz
	 * @param string $projekt_kurzbz Kurzbz des Projekts.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($projekt_kurzbz)
	{
		$qry = "SELECT * FROM fue.tbl_projekt WHERE projekt_kurzbz=" . $this->db_add_param($projekt_kurzbz);

		if ($this->db_query($qry)) {
			if ($row = $this->db_fetch_object()) {
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->nummer = $row->nummer;
				$this->titel = $row->titel;
				$this->beschreibung = $row->beschreibung;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->budget = $row->budget;
				$this->farbe = $row->farbe;
				$this->anzahl_ma = $row->anzahl_ma;
				$this->aufwand_pt = $row->aufwand_pt;

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
	 * Laedt alle aktuellen Projekte
	 * @param bool $filter_kommende Lädt auch alle zukünftigen.
	 * @param string $oe Organisationseinheit.
	 * @return bool
	 */
	public function getProjekteAktuell($filter_kommende = false, $oe = null)
	{
		$qry = 'SELECT * FROM fue.tbl_projekt WHERE ';

		if ($filter_kommende)
			$qry .= " ((beginn < CURRENT_TIMESTAMP AND ende > CURRENT_TIMESTAMP) OR beginn > CURRENT_TIMESTAMP)";
		else
			$qry .= " (beginn < CURRENT_TIMESTAMP AND ende > CURRENT_TIMESTAMP)";


		if (!is_null($oe))
			$qry .= ' AND oe_kurzbz=' . $this->db_add_param($oe);

		$qry .= ' ORDER BY oe_kurzbz;';
		if ($this->db_query($qry)) {
			while ($row = $this->db_fetch_object()) {
				$obj = new projekt();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->budget = $row->budget;
				$obj->farbe = $row->farbe;
				$obj->aufwandstyp_kurzbz = $row->aufwandstyp_kurzbz;
				$obj->anzahl_ma = $row->anzahl_ma;
				$obj->aufwand_pt = $row->aufwand_pt;

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
	 * Laedt alle Projekte die zwischen beginn und ende liegen
	 * @param date $beginn Anfang.
	 * @param date $ende Ende.
	 * @param string $oe Organisationseinheit.
	 * @return bool
	 */
	public function getProjekteInZeitraum($beginn, $ende, $oe = null)
	{
		$qry = 'select * from fue.tbl_projekt where beginn <= ' . $this->db_add_param($ende) . ' and ende >= ' . $this->db_add_param($beginn);
		if (!is_null($oe))
			$qry .= " AND oe_kurzbz=" . $this->db_add_param($oe);
		$qry .= ' ORDER BY oe_kurzbz;';
		//echo $qry;
		if ($this->db_query($qry)) {
			while ($row = $this->db_fetch_object()) {
				$obj = new projekt();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->budget = $row->budget;
				$obj->farbe = $row->farbe;
				$obj->anzahl_ma = $row->anzahl_ma;
				$obj->aufwand_pt = $row->aufwand_pt;

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
	 * Laedt die Projeke einer Organisationseinheit
	 * @param string $oe Organisationseinheit.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getProjekte($oe = null)
	{
		$qry = 'SELECT * FROM fue.tbl_projekt';
		if (!is_null($oe))
			$qry .= " WHERE oe_kurzbz=" . $this->db_add_param($oe);
		$qry .= ' ORDER BY oe_kurzbz;';
		//echo $qry;
		if ($this->db_query($qry)) {
			while ($row = $this->db_fetch_object()) {
				$obj = new projekt();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;
				$obj->budget = $row->budget;
				$obj->farbe = $row->farbe;
				$obj->aufwandstyp_kurzbz = $row->aufwandstyp_kurzbz;
				$obj->anzahl_ma = $row->anzahl_ma;
				$obj->aufwand_pt = $row->aufwand_pt;

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
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{
		//Gesamtlaenge pruefen
		if ($this->projekt_kurzbz == null) {
			$this->errormsg = 'Projekt kurzbz darf nicht NULL sein!';
		}
		if ($this->oe_kurzbz == null) {
			$this->errormsg = 'OE kurbz darf nicht NULL sein!';
		}
		if (mb_strlen($this->projekt_kurzbz) > 16) {
			$this->errormsg = 'Projektyp_kurzbz darf nicht länger als 16 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->nummer) > 8) {
			$this->errormsg = 'Nummer darf nicht länger als 8 Zeichen sein';
			return false;
		}
		if (mb_strlen($this->titel) > 256) {
			$this->errormsg = 'Titel darf nicht länger als 256 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}

	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $projekt_kurzbz aktualisiert
	 * @param bool $new Neu ja/nein.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save($new = null)
	{
		//Variablen pruefen
		if (!$this->validate())
			return false;

		if ($new == null)
			$new = $this->new;

		if ($new) {
			//Neuen Datensatz einfuegen

			$qry = 'INSERT INTO fue.tbl_projekt (projekt_kurzbz, nummer, titel,beschreibung, beginn, ende, budget, farbe, oe_kurzbz, aufwand_pt, anzahl_ma, aufwandstyp_kurzbz) VALUES(' .
				$this->db_add_param($this->projekt_kurzbz) . ', ' .
				$this->db_add_param($this->nummer) . ', ' .
				$this->db_add_param($this->titel) . ', ' .
				$this->db_add_param($this->beschreibung) . ', ' .
				$this->db_add_param($this->beginn) . ', ' .
				$this->db_add_param($this->ende) . ', ' .
				$this->db_add_param($this->budget) . ', ' .
				$this->db_add_param($this->farbe) . ', ' .
				$this->db_add_param($this->oe_kurzbz) . ',' .
				$this->db_add_param($this->aufwand_pt) . ',' .
				$this->db_add_param($this->anzahl_ma) . ',' .
				$this->db_add_param($this->aufwandstyp_kurzbz) . ');';
		}
		else
		{
			//Updaten des bestehenden Datensatzes

			$qry = 'UPDATE fue.tbl_projekt SET ' .
				'projekt_kurzbz=' . $this->db_add_param($this->projekt_kurzbz) . ', ' .
				'nummer=' . $this->db_add_param($this->nummer) . ', ' .
				'titel=' . $this->db_add_param($this->titel) . ', ' .
				'beschreibung=' . $this->db_add_param($this->beschreibung) . ', ' .
				'beginn=' . $this->db_add_param($this->beginn) . ', ' .
				'ende=' . $this->db_add_param($this->ende) . ', ' .
				'budget=' . $this->db_add_param($this->budget) . ', ' .
				'farbe=' . $this->db_add_param($this->farbe) . ', ' .
				'oe_kurzbz=' . $this->db_add_param($this->oe_kurzbz) . ', ' .
				'anzahl_ma=' . $this->db_add_param($this->anzahl_ma) . ', ' .
				'aufwand_pt=' . $this->db_add_param($this->aufwand_pt) . ', ' .
				'aufwandstyp_kurzbz=' . $this->db_add_param($this->aufwandstyp_kurzbz) . ' ' .
				'WHERE projekt_kurzbz=' . $this->db_add_param($this->projekt_kurzbz) . ';';
		}

		if ($this->db_query($qry)) {
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Daten';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz
	 * @param string $projekt_kurzbz Projekt das geloescht werden soll.
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($projekt_kurzbz)
	{
		$qry = "DELETE FROM lehre.tbl_projek WHERE projekt_kurzbz=" . $this->db_add_param($projekt_kurzbz);

		if ($this->db_query($qry)) {
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Loeschen des Datensatzes';
			return false;
		}
	}

	/**
	 * Liefert die Projekte zu denen ein Mitarbeiter zugeordnet ist.
	 * Optional auch mit den Zuteilungen zu Projektphasen.
	 * @param string $mitarbeiter_uid MitarbeiterUID.
	 * @param bool $projektphasen Default false. Wenn true, werden auch Zuteilungen zu Projektphasen geliefert.
	 * @return true wenn ok, false im Fehlerfall
	 */
	function getProjekteMitarbeiter($mitarbeiter_uid, $projektphasen = false)
	{
		$qry = "SELECT DISTINCT
					tbl_projekt.*
				FROM
					fue.tbl_ressource
					JOIN fue.tbl_projekt_ressource USING(ressource_id)
					JOIN fue.tbl_projekt USING(projekt_kurzbz)
				WHERE (beginn<=now() or beginn is null)
				AND (ende + interval '1 month 1 day' >=now() OR ende is null)
				AND
				(
					mitarbeiter_uid=" . $this->db_add_param($mitarbeiter_uid) . " OR
					student_uid=" . $this->db_add_param($mitarbeiter_uid) . "
				)";

		if ($projektphasen == true)
			$qry .= "UNION

                             SELECT DISTINCT
                                        tbl_projekt.*
                                FROM
                                        fue.tbl_projektphase
                                        JOIN fue.tbl_projekt USING (projekt_kurzbz)
                                        JOIN fue.tbl_projekt_ressource USING (projektphase_id)
                                        JOIN fue.tbl_ressource ON (tbl_ressource.ressource_id=tbl_projekt_ressource.ressource_id)
                                WHERE
                                (
									(
										(tbl_projekt.beginn<=now() or tbl_projekt.beginn is null)
										AND (tbl_projekt.ende + interval '1 month 1 day' >=now() OR tbl_projekt.ende is null)
									) OR (
										(tbl_projektphase.start<=now() or tbl_projektphase.start is null)
										AND (tbl_projektphase.ende + interval '1 month 1 day' >=now() OR tbl_projektphase.ende is null)
									)
								)
                                AND mitarbeiter_uid=" . $this->db_add_param($mitarbeiter_uid);

		if ($result = $this->db_query($qry)) {
			while ($row = $this->db_fetch_object($result)) {
				$obj = new projekt();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;

				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->erromsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Liefert Ein Array mit Porjekten von allen Projekten des Mitarbeiters mit UID.
	 * Optional auch mit den Zuteilungen zu Projektphasen.
	 * @param string $mitarbeiter_uid MitarbeiterUID.
	 * @param bool $projektphasen Default false. Wenn true, werden auch Zuteilungen zu Projektphasen geliefert.
	 * @return array wenn ok, false im Fehlerfall
	 */
	function getProjekteListForMitarbeiter($mitarbeiter_uid, $projektphasen = false)
	{
		$projectList = array();
		$qry = "SELECT DISTINCT
					tbl_projekt.*
				FROM
					fue.tbl_ressource
					JOIN fue.tbl_projekt_ressource USING(ressource_id)
					JOIN fue.tbl_projekt USING(projekt_kurzbz)
				WHERE (beginn<=now() or beginn is null)
				AND (ende + interval '1 month 1 day' >=now() OR ende is null)
				AND
				(
					mitarbeiter_uid=" . $this->db_add_param($mitarbeiter_uid) . " OR
					student_uid=" . $this->db_add_param($mitarbeiter_uid) . "
				)";

		if ($projektphasen == true)
			$qry .= "UNION

                             SELECT DISTINCT
                                        tbl_projekt.*
                                FROM
                                        fue.tbl_projektphase
                                        JOIN fue.tbl_projekt USING (projekt_kurzbz)
                                        JOIN fue.tbl_projekt_ressource USING (projektphase_id)
                                        JOIN fue.tbl_ressource ON (tbl_ressource.ressource_id=tbl_projekt_ressource.ressource_id)
                                WHERE
                                (
									(
										(tbl_projekt.beginn<=now() or tbl_projekt.beginn is null)
										AND (tbl_projekt.ende + interval '1 month 1 day' >=now() OR tbl_projekt.ende is null)
									) OR (
										(tbl_projektphase.start<=now() or tbl_projektphase.start is null)
										AND (tbl_projektphase.ende + interval '1 month 1 day' >=now() OR tbl_projektphase.ende is null)
									)
								)
                                AND mitarbeiter_uid=" . $this->db_add_param($mitarbeiter_uid);

		if ($result = $this->db_query($qry)) {
			while ($row = $this->db_fetch_object($result)) {
				$obj = new projekt();

				$obj->projekt_kurzbz = $row->projekt_kurzbz;
				$obj->nummer = $row->nummer;
				$obj->titel = $row->titel;
				$obj->beschreibung = $row->beschreibung;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->oe_kurzbz = $row->oe_kurzbz;

				$this->result[] = $obj;

				array_push($projectList, $obj);
			}
			return $projectList;
		}
		else
		{
			$this->erromsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	public function getProjektFromBestellung($bestellung_id)
	{
		$qry = "select * from fue.tbl_projekt
				join wawi.tbl_projekt_bestellung USING (projekt_kurzbz)
				where bestellung_id= " . $this->db_add_param($bestellung_id);

		if ($this->db_query($qry)) {
			if ($row = $this->db_fetch_object()) {
				$this->projekt_kurzbz = $row->projekt_kurzbz;
				$this->nummer = $row->nummer;
				$this->titel = $row->titel;
				$this->beschreibung = $row->beschreibung;
				$this->beginn = $row->beginn;
				$this->ende = $row->ende;
				$this->oe_kurzbz = $row->oe_kurzbz;
				$this->budget = $row->budget;
				$this->farbe = $row->farbe;
				$this->anzahl_ma = $row->anzahl_ma;
				$this->aufwand_pt = $row->aufwand_pt;

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
	 * Liefert True zurück wenn die angegebenen Start und Endzeitpunkt der Arbeitsdauer in die Projektdauer fallen
	 * @param string $mitarbeiter_uid MitarbeiterUID.
	 * @param bool $projektphasen Default false. Wenn true, werden auch Zuteilungen zu Projektphasen geliefert.
	 * @return array wenn ok, false im Fehlerfall
	 */
	public function checkProjectInCorrectTime($projekt_kurzbz, $give_project_start, $give_projekt_ende)
	{
		if(empty($projekt_kurzbz))
			return true;
		try
		{
			$projekt = $this->getProjectByKurzbz($projekt_kurzbz);
			if(strtotime($projekt->beginn))
				$projekt_start = date('Y-m-d', strtotime($projekt->beginn));
			else
				$projekt_start = NULL;
			if(strtotime($projekt->ende))
				$projekt_ende = date('Y-m-d', strtotime($projekt->ende));
			else
				$projekt_ende = NULL;

			$given_start = date('Y-m-d', strtotime($give_project_start));
			$given_ende = date('Y-m-d', strtotime($give_projekt_ende));

			if ((empty($projekt_start) || $given_start >= $projekt_start) && (empty($projekt_ende) || $given_ende <= $projekt_ende))
				return true;
			else
				return false;

		}
		catch (Exception $e)
		{
      		error_log('Exception abgefangen: ',  $e->getMessage(), "\n");
		}
	}

	public function getProjectByKurzbz($projekt_kurzbz)
	{
		$qry = "SELECT * FROM fue.tbl_projekt
				WHERE projekt_kurzbz=".$this->db_add_param($projekt_kurzbz);
		if ($result = $this->db_query($qry))
		{
			$row = $this->db_fetch_object($result);
			$obj = new projekt();

			$obj->projekt_kurzbz = $row->projekt_kurzbz;
			$obj->nummer = $row->nummer;
			$obj->titel = $row->titel;
			$obj->beschreibung = $row->beschreibung;
			$obj->beginn = $row->beginn;
			$obj->ende = $row->ende;
			$obj->oe_kurzbz = $row->oe_kurzbz;

			return $obj;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
}

?>
