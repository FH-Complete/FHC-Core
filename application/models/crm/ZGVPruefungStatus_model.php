<?php


class ZGVPruefungStatus_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_zgvpruefungstatus_status';
		$this->pk = 'zgv_pruefung_status_id';
		$this->hasSequence = true;
	}

	public function getZgvStatus($zgvpruefung_id)
	{
		$this->addOrder('datum', 'DESC');
		$this->addLimit(1);

		return $this->loadWhere(array('zgvpruefung_id' => $zgvpruefung_id));
	}

	public function getZgvStatusByPrestudent($prestudent_id)
	{
		$this->addJoin('public.tbl_zgvpruefung', 'zgvpruefung_id');
		$this->addOrder($this->dbTable . '.datum', 'DESC');
		$this->addLimit(1);
		return $this->loadWhere(array('prestudent_id' => $prestudent_id));
	}

	public function getOpenZgvByPerson($person_id, $status)
	{
		$query = 'SELECT status.zgvpruefung_id, status.datum, status.status
					FROM public.tbl_zgvpruefungstatus_status status
					INNER JOIN
					(
						SELECT zgvpruefung_id, max(datum) as MaxDate
						FROM public.tbl_zgvpruefungstatus_status
						GROUP BY zgvpruefung_id
					) sub ON status.zgvpruefung_id = sub.zgvpruefung_id AND status.datum = sub.MaxDate
				JOIN public.tbl_zgvpruefung ON status.zgvpruefung_id = public.tbl_zgvpruefung.zgvpruefung_id
				JOIN public.tbl_prestudent USING (prestudent_id) 
				WHERE person_id = ?
				AND status.status IN ?';

		return $this->execQuery($query, array($person_id, $status));
	}
}