<?php

require_once(dirname(__FILE__). '/basis_db.class.php');
require_once(dirname(__FILE__). '/studiensemester.class.php');

class zeitwunsch_gueltigkeit extends basis_db
{
    public $new;                        // boolean
    public $result = array();

    public $zeitwunsch_gueltigkeit_id;  // integer
    public $mitarbeiter_uid;            // varchar 32
    public $von;                        // date
    public $bis;                        // date
    public $insertamum;                 // timestamp
    public $insertvon;                  // varchar 32
    public $updateamum;                 // timestamp
    public $updatevon;                  // varchar 32


    public function __construct()
    {
        parent::__construct($zeitwunsch_gueltigkeit_id = null);

        if (!is_null($zeitwunsch_gueltigkeit_id))
        {
            $this->load($zeitwunsch_gueltigkeit_id);
        }
    }

    /**
     * Ladet eine Zeitwunschgueltigkeit.
     * @param $zeitwunsch_gueltigkeit_id
     * @return bool
     */
    public function load($zeitwunsch_gueltigkeit_id)
    {
        if (!is_numeric($zeitwunsch_gueltigkeit_id))
        {
            $this->errormsg = 'Wrong parameter zeitwunsch_gueltigkeit_id.';
            return false;
        }

        $qry = '
            SELECT *
            FROM campus.tbl_zeitwunsch_gueltigkeit
            WHERE zeitwunsch_gueltigkeit_id = '.$this->db_add_param($zeitwunsch_gueltigkeit_id). '
            ORDER BY von DESC
        ';

        if ($result = $this->db_query($qry))
        {
            while ($row = $this->db_fetch_object($result))
            {
                $this->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
                $this->von = $row->von;
                $this->bis = $row->bis;
                $this->insertamum = $row->insertamum;
                $this->insertvon = $row->insertvon;
                $this->updateamum = $row->updateamum;
                $this->updatevon = $row->updatevon;
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Datenbankabfrage';
            return false;
        }

    }

    /**
     * Speichert eine Zeitwunschgueltigkeit
     */
    public function save()
    {
        if($this->new)
        {
            $qry = '
                INSERT INTO campus.tbl_zeitwunsch_gueltigkeit (mitarbeiter_uid, von, bis, insertvon) 
                VALUES ('.
                    $this->db_add_param($this->mitarbeiter_uid).', '.
                    $this->db_add_param($this->von).', '.
                    $this->db_add_param($this->bis).', '.
                    $this->db_add_param($this->insertvon). ')
                RETURNING zeitwunsch_gueltigkeit_id;  
                ';
        }
        else
        {
            $qry = '
                UPDATE campus.tbl_zeitwunsch_gueltigkeit SET'.
                ' bis = '. $this->db_add_param($this->bis). ', '.
                ' updateamum = NOW(), '.
                ' updatevon = '.$this->db_add_param($this->updatevon).
                ' WHERE zeitwunsch_gueltigkeit_id = ' .$this->db_add_param($this->zeitwunsch_gueltigkeit_id, FHC_INTEGER);

        }

        if($result = $this->db_query($qry))
        {
            // Wenn neuer Eintrag
            if ($this->new)
            {
                if($row = $this->db_fetch_object($result))
                {
                    // ZWG ID des neuen ZWG Eintrags zurueckgeben
                    $this->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
                }
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler beim Speichern der Zeitwunschgueltigkeit';
            return false;
        }
    }

    /**
     * Ladet Zeitwunschgueltigkeiten einer UID mitsamt den zugehoerigen Studiensemestern.
     * @param $uid
     * @param numeric $limit    limit = null liefert alle ZWG; limit = 1 liefert die letztgueltige Zeitwunsch-Gueltigkeit.
     * @param bool $activeOnly   Wenn während des laufenden Semesters der Zeitwunsch geaendert werden, werden mehrere ZWG im Semester hinterlegt.
     *                                          true liefert pro Studiensemester nur die letztgueltigen ZWG;
     *                                          false liefert alle ZWG pro Studiensemester
     * @param string $bis       string date, z.B. 2022-01-31
     * @return bool
     */
    public function getByUID($uid, $limit = null, $activeOnly = true, $bis = null)
    {
        $studiensemester = new Studiensemester();
        $studiensemester->getNextStudiensemester();

        $qry = '
            SELECT zwg.*, studiensemester_kurzbz, start, ende
            FROM campus.tbl_zeitwunsch_gueltigkeit zwg, public.tbl_studiensemester
            WHERE zwg.mitarbeiter_uid = '.$this->db_add_param($uid);

        // Wenn Bis-Datum angegeben
        if (!is_null($bis))
        {
            // Zeitwuensche nur bis zum angegebenen Bis-Datum
            $qry.= '
                AND (von < ende AND '. $this->db_add_param($bis). '::date > start)
            ';
        }
        else
        {
            // Alle Zeitwuensche
            $qry.= '
                AND (von < ende AND COALESCE(bis, '. $this->db_add_param($studiensemester->ende).'::date ) > start)
            ';
        }

        // Nach Gueltigkeits-Startdatum sortieren, zuerst die zuletzt gueltigen
        $qry.= '
                ORDER BY zeitwunsch_gueltigkeit_id DESC, start DESC 
            ';

        // Wenn nur aktive Zeitwunschgueltigkeiten angezeigt werden sollen
        if ($activeOnly)
        {
            // ...mit distinct die zuletzt erstellten pro Studiensemester filtern
            $qry = '
                SELECT DISTINCT ON (start, studiensemester_kurzbz) start, studiensemester_kurzbz, 
                    ende, zeitwunsch_gueltigkeit_id, mitarbeiter_uid, von, bis, 
                    insertamum, insertvon, updateamum, updatevon 
                FROM ('. $qry. ') temp
                ORDER BY start DESC, studiensemester_kurzbz 
            ';
        }

        // Wenn Limit angegeben
        if (!is_null($limit))
        {
            // Ausgabe limitieren
            $qry.= 'LIMIT '.$this->db_add_param($limit);
        }

        if ($result = $this->db_query($qry))
        {
            $this->result = array();

            while ($row = $this->db_fetch_object($result))
            {
                $obj = new StdClass();
                $obj->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
                $obj->von = $row->von;
                $obj->bis = $row->bis;
                $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $obj->start = $row->start;
                $obj->ende = $row->ende;
                $obj->insertamum = $row->insertamum;
                $obj->insertvon = $row->insertvon;
                $obj->updateamum = $row->updateamum;
                $obj->updatevon = $row->updatevon;

                $this->result[]= $obj;
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Datenbankabfrage';
            return false;
        }
    }

    /**
     * Ladet Zeitwunschgueltigkeiten einer UID und eines bestimmten Semesters (defaultmaeßig nur die letztgueltige)
     * @param $uid
     * @param $studiensemester_kurzbz
     * @param null $limit   limit = null liefert alle ZWG des Studiensemesters; limit = 1 liefert die letztgueltige ZWG.
     * @return bool
     */
    public function getByStudiensemester($uid, $studiensemester_kurzbz, $limit = 1)
    {
        $qry = '
            WITH studiensemester AS
            (
                SELECT studiensemester_kurzbz, start, ende
                FROM public.tbl_studiensemester
                WHERE studiensemester_kurzbz = '.$this->db_add_param($studiensemester_kurzbz). '
            )
            
            SELECT zwg.*, studiensemester_kurzbz, start, ende
            FROM campus.tbl_zeitwunsch_gueltigkeit zwg, studiensemester ss
            WHERE zwg.mitarbeiter_uid = '.$this->db_add_param($uid). '
            AND (zwg.von < ss.ende AND COALESCE(zwg.bis, ss.ende) >= ss.start)
            ORDER BY von DESC
        ';

        // Wenn Limit angegeben
        if (!is_null($limit))
        {
            // Ausgabe limitieren
            $qry.= 'LIMIT '.$this->db_add_param($limit);
        }

        if ($result = $this->db_query($qry))
        {
            $this->result = array();

            while ($row = $this->db_fetch_object($result))
            {
                $obj = new StdClass();
                $obj->zeitwunsch_gueltigkeit_id = $row->zeitwunsch_gueltigkeit_id;
                $obj->von = $row->von;
                $obj->bis = $row->bis;
                $obj->studiensemester_kurzbz = $row->studiensemester_kurzbz;
                $obj->start = $row->start;
                $obj->ende = $row->ende;
                $obj->insertamum = $row->insertamum;
                $obj->insertvon = $row->insertvon;
                $obj->updateamum = $row->updateamum;
                $obj->updatevon = $row->updatevon;

                $this->result[]= $obj;
            }
            return true;
        }
        else
        {
            $this->errormsg = 'Fehler bei der Datenbankabfrage';
            return false;
        }
    }
}

