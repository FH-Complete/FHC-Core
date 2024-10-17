<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------
/**
 * Klasse Zeitaufzeichnung Geteilte Dienste
 * @create 13-06-2019
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
class zeitaufzeichnung_gd extends basis_db
{
    public $new;		                // boolean
    public $result = array();	        // object array

    // Table columns
    public $zeitaufzeichnungs_gd_id;	// integer
    public $uid;                        // varchar(32)
    public $studiensemester_kurzbz;		// varchar(16)
    public $selbstverwaltete_pause;		// boolean
    public $insertamum;				    // timestamp
    public $insertvon;				    // varchar(32)
    public $updateamum;				    // timestamp
    public $updatevon;				    // varchar(32)

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

	/**
     * Loads entry for specific user and semester
     * @return boolean  True, if entry is found.
     */
    public function load($user, $sem)
    {
        if ($user && $sem)
        {
            $qry = '
                SELECT * FROM campus.tbl_zeitaufzeichnung_gd
                    WHERE uid = '.$this->db_add_param($user).
					' AND studiensemester_kurzbz = ' . $this->db_add_param($sem) .
					'limit 1';

			if(!$this->db_query($qry))
			{
				$this->errormsg = 'Fehler bei einer Datenbankabfrage';
				return false;
			}
			if($row = $this->db_fetch_object())
			{
				$this->zeitaufzeichnung_gd_id = $row->zeitaufzeichnung_gd_id;
				$this->uid = $row->uid;
				$this->studiensemester_kurzbz = $row->studiensemester_kurzbz;
				$this->insertamum = $row->insertamum;
				$this->insertvon = $row->insertvon;
				$this->updateamum = $row->updateamum;
				$this->updatevon = $row->updatevon;
				$this->selbstverwaltete_pause = $this->db_parse_bool($row->selbstverwaltete_pause);
				return true;
			}
			else
			{
				$this->errormsg = 'Es ist kein Datensatz vorhanden';
				return false;
			}
        }
        else
        {
            $this->errormsg = 'Falsche Parameterübergabe';
            return false;
        }
    }

    /**
     * Saves decision about self-managing breaks during parted working times.
     * @return boolean  True, if saving succeeded.
     */
    public function save()
    {
        if (is_string($this->uid) &&
            is_string($this->studiensemester_kurzbz) &&
            is_bool($this->selbstverwaltete_pause))
        {
			$qry = '
                INSERT INTO campus.tbl_zeitaufzeichnung_gd (
                    uid,
                    studiensemester_kurzbz,
                    selbstverwaltete_pause,
                    insertvon
                )
                VALUES ('.
                    $this->db_add_param($this->uid). ', '.
                    $this->db_add_param($this->studiensemester_kurzbz). ', '.
                    $this->db_add_param($this->selbstverwaltete_pause, FHC_BOOLEAN). ', '.
                    $this->db_add_param($this->uid). '
                );
            ';

            if ($this->db_query($qry))
            {
                return true;
            }
            else
            {
                $this->errormsg = 'Fehler beim Speichern der selbstverwalteten Pause';
                return false;
            }
        }
        else
        {
            $this->errormsg = 'Falsche Parameterübergabe';
            return false;
        }
    }
}
