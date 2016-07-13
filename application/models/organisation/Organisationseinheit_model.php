<?php
class Organisationseinheit_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_organisationseinheit';
		$this->pk = 'oe_kurzbz';
	}

	public function getRecursiveList($typ)
	{
		$qry = "WITH RECURSIVE tree (oe_kurzbz, bezeichnung, path, organisationseinheittyp_kurzbz) AS 
				(
					SELECT
						oe_kurzbz,
						bezeichnung||' ('||organisationseinheittyp_kurzbz||')' AS bezeichnung,
						oe_kurzbz||'|' AS path,
						organisationseinheittyp_kurzbz
					FROM tbl_organisationseinheit
					WHERE oe_parent_kurzbz IS NULL AND aktiv
					UNION ALL
					SELECT
						oe.oe_kurzbz,
						oe.bezeichnung||' ('||oe.organisationseinheittyp_kurzbz||')' AS bezeichnung,
						tree.path ||oe.oe_kurzbz||'|' AS path,
						oe.organisationseinheittyp_kurzbz
					FROM tree 
					JOIN tbl_organisationseinheit oe ON (tree.oe_kurzbz=oe.oe_parent_kurzbz)
				)
				SELECT oe_kurzbz AS value, substring(regexp_replace(path, '[A-z]+\|', '-','g')||bezeichnung,2) AS name, path FROM tree ";
		if (!empty($typ))
			$qry .= 'WHERE organisationseinheittyp_kurzbz IN ('.$typ.') ';
		$qry .= 'ORDER BY path;';

		
		if ($res = $this->db->query($qry))
			return $this->_success($res);
		else
			return $this->_error($this->db->error());
	}
}
