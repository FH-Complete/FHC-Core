<?php

class Ort_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_ort";
		$this->pk = "ort_kurzbz";
	}
	
	public function getAll($raumtyp_kurzbz)
	{
		$this->addOrder("ort_kurzbz");
		
		$this->addJoin("public.tbl_ortraumtyp", "ort_kurzbz");
		
		return $this->OrtModel->loadWhere(array("raumtyp_kurzbz" => $raumtyp_kurzbz));
	}

	public function getContentID($ort_kurzbz)
	{
	
		return $this->execReadOnlyQuery("
		SELECT content_id 
		FROM public.tbl_ort 
		WHERE ort_kurzbz = ?;
		",[$ort_kurzbz]);
		
	}

	public function getRoomsWithEmployeesAssigned($ort_kurzbz=null)
	{
		$ort_kurzbz_clause = is_null($ort_kurzbz)
			? ''
			: 'and r.ort_kurzbz = ' . $this->escape($ort_kurzbz);
		$sql = <<<EOSQL
			{$this->roomEmployeesCTEs()}

			select
				r.rauminfo as room,
				mir.ma_count as employee_count,
				mir.mas_in_room as employees
			from
				roominfo r
			join
				mas_in_room mir on r.ort_kurzbz = mir.ort_kurzbz
			where
				1=1
				{$ort_kurzbz_clause}
			order by
				mir.ma_count DESC
EOSQL;

		return $this->execReadOnlyQuery($sql);
	}

	public function getEmployeesWithRoomAssigned($mitarbeiter_uid=null)
	{
		$mtarbeiter_uid_clause = is_null($mitarbeiter_uid)
			? ''
			: 'and aer.mitarbeiter_uid = ' . $this->escape($mitarbeiter_uid);
		$sql = <<<EOSQL
			{$this->roomEmployeesCTEs()}

			select
				m.mainfo as employee,
				r.rauminfo as room
			from
				active_employee_room aer
			join
				roominfo r on aer.ort_kurzbz = r.ort_kurzbz
			join
				mainfo m on aer.mitarbeiter_uid = m.mitarbeiter_uid
			where
				1=1
				{$mtarbeiter_uid_clause}
EOSQL;

		return $this->execReadOnlyQuery($sql);
	}

	protected function roomEmployeesCTEs()
	{
		return <<<EOCTES
			with active_employee_room as (
				select
					tm.mitarbeiter_uid,
					tm.ort_kurzbz,
					td.vertragsart_kurzbz
				from
					public.tbl_mitarbeiter tm
				join
					hr.tbl_dienstverhaeltnis td
					on
					td.mitarbeiter_uid = tm.mitarbeiter_uid
					and NOW() between COALESCE(td.von, '1970-01-01') and coalesce(td.bis, '2170-12-31')
					and td.mitarbeiter_uid not like '_Dummy%'
			),
			roominfo as (
				select
					o.ort_kurzbz,
					json_build_object(
						'ort_kurzbz', o.ort_kurzbz,
						'bezeichnung', o.bezeichnung,
						'planbezeichnung', o.planbezeichnung,
						'max_person', o.max_person,
						'aktiv', o.aktiv
					) as rauminfo
				from
					public.tbl_ort o
			),
			mainfo as (
				select
					tm.mitarbeiter_uid,
					tm.ort_kurzbz,
					json_build_object(
						'mitarbeiter_uid', tm.mitarbeiter_uid,
						'vorname', tp.vorname,
						'nachname', tp.nachname,
						'vertragsart_kurzbz', td.vertragsart_kurzbz
					) as mainfo
				from
					public.tbl_mitarbeiter tm
				join
					public.tbl_benutzer b on b.uid = tm.mitarbeiter_uid and b.aktiv = true
				join
					public.tbl_person tp on tp.person_id = b.person_id
				join
					hr.tbl_dienstverhaeltnis td
					on
					td.mitarbeiter_uid = tm.mitarbeiter_uid
					and NOW() between COALESCE(td.von, '1970-01-01') and coalesce(td.bis, '2170-12-31')
					and td.mitarbeiter_uid not like '_Dummy%'
			),
			mas_in_room as (
				select
					m.ort_kurzbz,
					count(m.mitarbeiter_uid) as ma_count,
					json_agg(m.mainfo) as mas_in_room
				from
					mainfo m
				group by
					m.ort_kurzbz
				order by
					ma_count desc
			)

EOCTES;

	}
}