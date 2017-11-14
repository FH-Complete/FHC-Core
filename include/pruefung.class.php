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
 * Authors: Christian Paminger <christian.paminger@technikum-wien.at>,
 *          Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 *          Rudolf Hangl <rudolf.hangl@technikum-wien.at> and
 *          Gerald Raab <gerald.raab@technikum-wien.at>.
 */
require_once(dirname(__FILE__).'/basis_db.class.php');

class pruefung extends basis_db
{
    public $new;      				// boolean
    public $result = array();		// pruefung Objekt

    public $pruefung_id;
    public $lehreinheit_id;			// integer
    public $student_uid;			// varchar(16)
    public $mitarbeiter_uid;		// varchar(16)
    public $note;					// smallint
    public $pruefungstyp_kurzbz;	// varchar(16)
    public $datum;					// Date
    public $anmerkung;				// varchar(256)
    public $insertamum;				// timestamp)
    public $insertvon;				// varchar(16)
    public $updateamum;				// timestamp
    public $updatevon;				// varchar(16)
    public $ext_id;					// bigint
    public $pruefungsanmeldung_id;	// bigint
	public $vertrag_id;				// bigint
	public $punkte;					// numeric(8,4)

    public $lehrveranstaltung_bezeichnung;
    public $lehrveranstaltung_id;
    public $note_bezeichnung;
    public $pruefungstyp_beschreibung;
    public $studiensemester_kurzbz;

    /**
     * Konstruktor
     * @param pruefung_id ID der zu ladenden Pruefung
     */
    public function __construct($pruefung_id=null)
    {
	    parent::__construct();

	    if($pruefung_id!=null)
		    $this->load($pruefung_id);
    }

    /**
     * Laedt einen Pr&uuml;fungsdatensatz
     * @param pruefung_id ID
     * @return true wenn ok, false im Fehlerfall
     */
    public function load($pruefung_id)
    {
	    if(!is_numeric($pruefung_id))
	    {
		    $this->errormsg = 'pruefung_id muss eine gueltige Zahl sein';
		    return false;
	    }

	    $qry = "SELECT
					tbl_pruefung.*,
					tbl_lehreinheit.lehrveranstaltung_id,
					tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz
			    FROM
					lehre.tbl_pruefung
					JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				WHERE
					pruefung_id=".$this->db_add_param($pruefung_id, FHC_INTEGER);

	    if($this->db_query($qry))
	    {
		    if($row = $this->db_fetch_object())
		    {
			    $this->pruefung_id = $row->pruefung_id;
			    $this->lehreinheit_id=$row->lehreinheit_id;
			    $this->student_uid=$row->student_uid;
			    $this->mitarbeiter_uid=$row->mitarbeiter_uid;
			    $this->note=$row->note;
			    $this->pruefungstyp_kurzbz=$row->pruefungstyp_kurzbz;
			    $this->datum=$row->datum;
			    $this->anmerkung=$row->anmerkung;
			    $this->insertamum=$row->insertamum;
			    $this->insertvon=$row->insertvon;
			    $this->updateamum=$row->updateamum;
			    $this->updatevon=$row->updatevon;
			    $this->ext_id=$row->ext_id;
				$this->vertrag_id = $row->vertrag_id;
			    $this->pruefungsanmeldung_id=$row->pruefungsanmeldung_id;
			    $this->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			    $this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->punkte = $row->punkte;
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
     * Liefert alle Pruefungen
     * @return true wenn ok, false im Fehlerfall
     */
    public function getAll($order=null, $student_uid=null)
    {
	    $qry = 'SELECT * FROM lehre.tbl_pruefung';
	    if ($student_uid)
		    $qry.=" WHERE student_uid =".$this->db_add_param($student);

	    if($order!=null)
		    $qry .=" ORDER BY $order";

	    if(!$this->db_query($qry))
	    {
		    $this->errormsg = 'Datensatz konnte nicht geladen werden';
		    return false;
	    }

	    while($row = $this->db_fetch_object())
	    {
		    $pruef_obj = new pruefung();
		    $pruef_obj->lehreinheit_id=$row->lehreinheit_id;
		    $pruef_obj->student_uid=$row->student_uid;
		    $pruef_obj->mitarbeiter_uid=$row->mitarbeiter_uid;
		    $pruef_obj->note=$row->note;
		    $pruef_obj->pruefungstyp_kurzbz=$row->pruefungstyp_kurzbz;
		    $pruef_obj->datum=$row->datum;
		    $pruef_obj->anmerkung=$row->anmerkung;
		    $pruef_obj->insertamum=$row->insertamum;
		    $pruef_obj->insertvon=$row->insertvon;
		    $pruef_obj->updateamum=$row->updateamum;
		    $pruef_obj->updatevon=$row->updatevon;
		    $pruef_obj->ext_id=$row->ext_id;
		    $pruef_obj->pruefungsanmeldung_id=$row->pruefungsanmeldung_id;
			$pruef_obj->vertrag_id = $row->vertrag_id;
			$pruef_obj->punkte = $row->punkte;

		    $this->result[] = $pruef_obj;
	    }

	    return true;
    }

    /**
     * Loescht eine Pruefung
     * @param $preufung_id ID der zu loeschenden Pruefung
     * @return true wenn ok, false im Fehlerfall
     */
    public function delete($pruefung_id)
    {
	    if(!is_numeric($pruefung_id))
	    {
		    $this->errormsg = 'Pruefung_id ist ungueltig';
		    return false;
	    }

	    $qry = "DELETE FROM lehre.tbl_pruefung WHERE pruefung_id=".$this->db_add_param($pruefung_id, FHC_INTEGER);

	    if($this->db_query($qry))
	    {
		    return true;
	    }
	    else
	    {
		    $this->errormsg = 'Fehler beim Loeschen der Pruefung';
		    return false;
	    }
    }

    /**
     * Prueft die Gueltigkeit der Variablen
     * @return true wenn ok, false im Fehlerfall
     */
    public function validate()
    {
	    //Laenge Pruefen
	    if(mb_strlen($this->anmerkung)>256)
	    {
		    $this->errormsg = 'Anmerkung darf nicht laenger als 256 Zeichen sein';
		    return false;
	    }
	    $this->errormsg = '';
	    return true;
    }

    /**
     * Speichert den aktuellen Datensatz
     * @return true wenn ok, false im Fehlerfall
     */
    public function save()
    {
	    //Gueltigkeit der Variablen pruefen
	    if(!$this->validate())
	    {
		    return false;
	    }

	    if($this->new)
	    {
		    //Neuen Datensatz anlegen
		    $qry = 'BEGIN;INSERT INTO lehre.tbl_pruefung (lehreinheit_id, student_uid, mitarbeiter_uid, note, pruefungstyp_kurzbz,
				datum, anmerkung, insertamum, insertvon, updateamum, updatevon, pruefungsanmeldung_id, vertrag_id, punkte) VALUES ('.
			    $this->db_add_param($this->lehreinheit_id).', '.
			    $this->db_add_param($this->student_uid).', '.
			    $this->db_add_param($this->mitarbeiter_uid).', '.
			    $this->db_add_param($this->note).', '.
			    $this->db_add_param($this->pruefungstyp_kurzbz).', '.
			    $this->db_add_param($this->datum).', '.
			    $this->db_add_param($this->anmerkung).', '.
			    $this->db_add_param($this->insertamum).', '.
			    $this->db_add_param($this->insertvon).', '.
			    $this->db_add_param($this->updateamum).', '.
			    $this->db_add_param($this->updatevon).', '.
			    $this->db_add_param($this->pruefungsanmeldung_id).','.
				$this->db_add_param($this->vertrag_id).','.
				$this->db_add_param($this->punkte).');';
	    }
	    else
	    {
		    //bestehenden Datensatz akualisieren

		    //Pruefen ob pruefung_id gueltig ist
		    if(!is_numeric($this->pruefung_id))
		    {
			    $this->errormsg = 'pruefung_id ist ungueltig.';
			    return false;
		    }

		    $qry = 'UPDATE lehre.tbl_pruefung SET '.
			    'lehreinheit_id='.$this->db_add_param($this->lehreinheit_id, FHC_INTEGER).', '.
			    'student_uid='.$this->db_add_param($this->student_uid).', '.
			    'mitarbeiter_uid='.$this->db_add_param($this->mitarbeiter_uid).', '.
			    'note='.$this->db_add_param($this->note).', '.
			    'pruefungstyp_kurzbz='.$this->db_add_param($this->pruefungstyp_kurzbz).', '.
			    'datum='.$this->db_add_param($this->datum).', '.
			    'anmerkung='.$this->db_add_param($this->anmerkung).', '.
			    'insertamum='.$this->db_add_param($this->insertamum).', '.
			    'insertvon='.$this->db_add_param($this->insertvon).', '.
			    'updateamum='.$this->db_add_param($this->updateamum).', '.
			    'updatevon='.$this->db_add_param($this->updatevon).', '.
			    'pruefungsanmeldung_id='.$this->db_add_param($this->pruefungsanmeldung_id, FHC_INTEGER).', '.
				'vertrag_id='.$this->db_add_param($this->vertrag_id, FHC_INTEGER).', '.
				'punkte='.$this->db_add_param($this->punkte).' '.
			    'WHERE pruefung_id='.$this->db_add_param($this->pruefung_id, FHC_INTEGER).';';
	    }

	    if($this->db_query($qry))
	    {
		    if($this->new)
		    {
			    //Sequence auslesen
			    $qry = "SELECT currval('lehre.tbl_pruefung_pruefung_id_seq') as id";
			    if($this->db_query($qry))
			    {
				    if($row = $this->db_fetch_object())
				    {
					    $this->pruefung_id = $row->id;
					    $this->db_query('COMMIT');
					    return true;
				    }
				    else
				    {
					    $this->errormsg = 'Fehler beim Auslesen der Sequence';
					    $this->db_query('ROLLBACK');
					    return false;
				    }
			    }
			    else
			    {
				    $this->errormsg = 'Fehler beim Auslesen der Sequence';
				    $this->db_query('ROLLBACK');
				    return false;
			    }
		    }
		    return true;
	    }
	    else
	    {
		    $this->db_query('ROLLBACK');
		    $this->errormsg = 'Fehler beim Speichern der Pruefung:'.$this->db_last_error();
		    return false;
	    }
    }

    /**
     * Liefert alle Pruefungen eines Studenten
     * @param student_uid
     * @return true wenn ok, false wenn Fehler
     */
    public function getPruefungen($student_uid, $pruefungstyp=null,$lv_id=null,$stsem=null)
    {
	    $qry = "SELECT tbl_pruefung.*, tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
			    tbl_note.bezeichnung as note_bezeichnung, tbl_pruefungstyp.beschreibung as typ_beschreibung, tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz
			    FROM lehre.tbl_pruefung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_note, lehre.tbl_pruefungstyp
			    WHERE student_uid=".$this->db_add_param($student_uid)."
			    AND tbl_pruefung.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
			    AND tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
			    AND tbl_pruefung.note = tbl_note.note
			    AND tbl_pruefung.pruefungstyp_kurzbz=tbl_pruefungstyp.pruefungstyp_kurzbz";
	    if ($pruefungstyp != null)
		    $qry .= " AND tbl_pruefungstyp.pruefungstyp_kurzbz = ".$this->db_add_param($pruefungstyp);
	    if ($lv_id != null)
		    $qry .= " AND tbl_lehrveranstaltung.lehrveranstaltung_id = ".$this->db_add_param($lv_id);
	    if ($stsem != null)
		    $qry .= " AND tbl_lehreinheit.studiensemester_kurzbz = ".$this->db_add_param($stsem);

	    $qry .= " ORDER BY datum DESC";
	    if($this->db_query($qry))
	    {
		    while($row = $this->db_fetch_object())
		    {
			    $obj = new pruefung();

			    $obj->pruefung_id = $row->pruefung_id;
			    $obj->lehreinheit_id = $row->lehreinheit_id;
			    $obj->student_uid = $row->student_uid;
			    $obj->mitarbeiter_uid = $row->mitarbeiter_uid;
			    $obj->note = $row->note;
			    $obj->note_bezeichnung = $row->note_bezeichnung;
			    $obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
			    $obj->pruefungstyp_beschreibung = $row->typ_beschreibung;
			    $obj->datum = $row->datum;
			    $obj->anmerkung = $row->anmerkung;
			    $obj->insertamum = $row->insertamum;
			    $obj->insertvon = $row->insertvon;
			    $obj->updateamum = $row->updateamum;
			    $obj->updatevon = $row->updatevon;
			    $obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
			    $obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			    $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
			    $obj->pruefungsanmeldung_id = $row->pruefungsanmeldung_id;
				$obj->punkte = $row->punkte;

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
     * Laedt die Pruefungen zu einer Lehrveranstaltung
     *
     * @param $lv_id
     * @param $pruefungstyp
     * @param $stsem
     * @return boolean
     */
    public function getPruefungenLV($lv_id, $pruefungstyp=null, $stsem=null)
    {
	    $qry = "SELECT tbl_pruefung.*, tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, tbl_lehrveranstaltung.lehrveranstaltung_id,
			    tbl_note.bezeichnung as note_bezeichnung, tbl_pruefungstyp.beschreibung as typ_beschreibung, tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz
			    FROM lehre.tbl_pruefung, lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung, lehre.tbl_note, lehre.tbl_pruefungstyp
			    WHERE tbl_pruefung.lehreinheit_id=tbl_lehreinheit.lehreinheit_id
			    AND tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id
			    AND tbl_pruefung.note = tbl_note.note
			    AND tbl_pruefung.pruefungstyp_kurzbz=tbl_pruefungstyp.pruefungstyp_kurzbz";
	    if ($pruefungstyp != null)
		    $qry .= " AND tbl_pruefungstyp.pruefungstyp_kurzbz = ".$this->db_add_param($pruefungstyp);
	    if ($lv_id != null)
		    $qry .= " AND tbl_lehrveranstaltung.lehrveranstaltung_id = ".$this->db_add_param($lv_id);
	    if ($stsem != null)
		    $qry .= " AND tbl_lehreinheit.studiensemester_kurzbz = ".$this->db_add_param($stsem);

	    $qry .= " ORDER BY datum DESC";

	    if($this->db_query($qry))
	    {
		    while($row = $this->db_fetch_object())
		    {
			    $obj = new pruefung();

			    $obj->pruefung_id = $row->pruefung_id;
			    $obj->lehreinheit_id = $row->lehreinheit_id;
			    $obj->student_uid = $row->student_uid;
			    $obj->mitarbeiter_uid = $row->mitarbeiter_uid;
			    $obj->note = $row->note;
			    $obj->note_bezeichnung = $row->note_bezeichnung;
			    $obj->pruefungstyp_kurzbz = $row->pruefungstyp_kurzbz;
			    $obj->pruefungstyp_beschreibung = $row->typ_beschreibung;
			    $obj->datum = $row->datum;
			    $obj->anmerkung = $row->anmerkung;
			    $obj->insertamum = $row->insertamum;
			    $obj->insertvon = $row->insertvon;
			    $obj->updateamum = $row->updateamum;
			    $obj->updatevon = $row->updatevon;
			    $obj->lehrveranstaltung_bezeichnung = $row->lehrveranstaltung_bezeichnung;
			    $obj->lehrveranstaltung_id = $row->lehrveranstaltung_id;
			    $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$obj->punkte = $row->punkte;

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
     * Lädt die Prüfunge zur übergebenen Prüfungsanmeldung
     * @param int $pruefungsanmeldung_id ID der Prüfungsanmeldung
     * @return boolean
     */
    public function getPruefungByAnmeldung($pruefungsanmeldung_id)
    {
		if(!is_numeric($pruefungsanmeldung_id))
		{
			$this->errormsg = 'pruefungsanmeldung_id muss eine gueltige Zahl sein';
			return false;
		}

		$qry = "SELECT * FROM lehre.tbl_pruefung WHERE pruefungsanmeldung_id=".$this->db_add_param($pruefungsanmeldung_id).";";

		if($this->db_query($qry))
		{
			if($row = $this->db_fetch_object())
			{
				$this->pruefung_id = $row->pruefung_id;
				$this->lehreinheit_id=$row->lehreinheit_id;
				$this->student_uid=$row->student_uid;
				$this->mitarbeiter_uid=$row->mitarbeiter_uid;
				$this->note=$row->note;
				$this->pruefungstyp_kurzbz=$row->pruefungstyp_kurzbz;
				$this->datum=$row->datum;
				$this->anmerkung=$row->anmerkung;
				$this->insertamum=$row->insertamum;
				$this->insertvon=$row->insertvon;
				$this->updateamum=$row->updateamum;
				$this->updatevon=$row->updatevon;
				$this->ext_id=$row->ext_id;
				$this->pruefungsanmeldung_id=$row->pruefungsanmeldung_id;
				$this->punkte = $row->punkte;
			}
		}
		else
		{
			$this->errormsg = 'Datensatz konnte nicht geladen werden';
			return false;
		}

		return true;
    }
}
?>
