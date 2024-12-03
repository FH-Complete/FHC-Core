<?php
class Notenschluesselzuordnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_notenschluesselzuordnung';
		$this->pk = 'notenschluesselzuordnung_id';
	}

	/**
	 * Liefert den passenden Notenschluessel zu einer Lehrveranstaltung
	 *
	 * @param integer						$lehrveranstaltung_id
	 * @param string						$studiensemester_kurzbz
	 *
	 * @return integer|null
	 */
	public function getKurzbzForLv($lehrveranstaltung_id, $studiensemester_kurzbz)
	{
		$this->addSelect("notenschluessel_kurzbz");
		
		$this->db->where("lehrveranstaltung_id", $lehrveranstaltung_id);
		if ($studiensemester_kurzbz) {
			$this->db->where("studiensemester_kurzbz", $studiensemester_kurzbz);
			$this->db->or_where("studiensemester_kurzbz", null);
		} else {
			$this->db->where("studiensemester_kurzbz", null);
		}

		$result = $this->load();

		if (!isError($result) && hasData($result))
			return current(getData($result))->notenschluessel_kurzbz;


		$this->addSelect("notenschluessel_kurzbz");

		$this->addJoin("(
			WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz, depth) AS (
				SELECT oe_kurzbz, oe_parent_kurzbz, 1
				FROM public.tbl_organisationseinheit 
				WHERE oe_kurzbz = (
					SELECT 
						oe_kurzbz 
					FROM 
						lehre.tbl_lehrveranstaltung 
					WHERE 
						lehrveranstaltung_id = " . $this->escape($lehrveranstaltung_id) . "
				)
				UNION ALL
				SELECT o.oe_kurzbz, o.oe_parent_kurzbz, oes.depth+1 AS depth 
				FROM public.tbl_organisationseinheit o, oes 
				WHERE o.oe_kurzbz = oes.oe_parent_kurzbz 
				AND aktiv = true
			)
			SELECT * FROM oes
		) oes", "oe_kurzbz");
		
		$this->addOrder("depth", "ASC");
		$this->addLimit(1);

		if ($studiensemester_kurzbz) {
			$this->db->where_in("studiensemester_kurzbz", [$studiensemester_kurzbz, null]);
			$result = $this->load();
		} else {
			$result = $this->loadWhere([
				"studiensemester_kurzbz" => null
			]);
		}

		if (isError($result) || !hasData($result))
			return null;

		return current(getData($result))->notenschluessel_kurzbz;
	}
}
