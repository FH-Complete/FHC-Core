<?php

class Reihungstest_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_reihungstest';
		$this->pk = 'reihungstest_id';
	}	
	
	/**
	 * Gets a test from a test id only if it is available
	 */
	public function checkAvailability($reihungstest_id)
	{
		$query = 'SELECT public.tbl_reihungstest.*
					FROM public.tbl_reihungstest LEFT JOIN public.tbl_rt_studienplan USING(reihungstest_id)
				   WHERE tbl_reihungstest.oeffentlich = TRUE
					 AND tbl_reihungstest.datum > NOW()
					 AND tbl_reihungstest.anmeldefrist >= NOW()
					 AND COALESCE (
							tbl_reihungstest.max_teilnehmer,
							(
								SELECT SUM(arbeitsplaetze)
								  FROM public.tbl_ort JOIN public.tbl_rt_ort USING(ort_kurzbz)
								 WHERE rt_id = tbl_reihungstest.reihungstest_id
							)
							) - (
								SELECT COUNT(*)
								  FROM public.tbl_rt_person
								 WHERE rt_id = tbl_reihungstest.reihungstest_id
							) > 0
					  AND reihungstest_id = ?';
		
		return $this->execQuery($query, array($reihungstest_id));
	}
}