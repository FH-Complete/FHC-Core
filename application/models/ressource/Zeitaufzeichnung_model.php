<?php
class Zeitaufzeichnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_zeitaufzeichnung';
		$this->pk = 'zeitaufzeichnung_id';
	}

    public function deleteEntriesForCurrentDay()
    {
        $today = date('Y-m-d');
        $qry = "DELETE FROM " . $this->dbTable . " 
                WHERE start >= '" . $today . " 00:00:00' 
                AND start <= '" . $today . " 23:59:59';";

        return $this->execQuery($qry);
    }

	public function getFullInterval($uid, $fromDate, $toDate)
	{
	    $qry = <<<EOL
		SELECT d.dates, z.*
		FROM
			(SELECT
				*, to_char ((ende-start),'HH24:MI') as diff,
				(SELECT (to_char(sum(ende-start),'DD')::integer)*24+to_char(sum(ende-start),'HH24')::integer || ':' || to_char(sum(ende-start),'MI')
				FROM campus.tbl_zeitaufzeichnung
				WHERE uid=? and start between ? AND ?) as summe
			FROM campus.tbl_zeitaufzeichnung
			WHERE uid=? AND (aktivitaet_kurzbz != 'DienstreiseMT' or aktivitaet_kurzbz is null) AND start between ? AND ?) as z
			
			RIGHT JOIN (select generate_series  ( ?::timestamp , ?::timestamp , '1 day'::interval) :: date as dates) d on date(z.ende) = d.dates 
			
		ORDER BY d.dates desc, z.start desc
EOL;

	   
	    return $this->execQuery($qry, array($uid, $fromDate, $toDate, $uid, $fromDate, $toDate, $fromDate, $toDate));
	}
}
