<?php
class Zeitwunsch_gueltigkeit_model extends DB_Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->dbTable = 'campus.tbl_zeitwunsch_gueltigkeit';
        $this->pk = 'zeitwunsch_gueltigkeit_id';
    }
}