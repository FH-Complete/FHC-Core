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

	public function getRecursiveList($typ = null)
	{
		$qry = "WITH RECURSIVE tree (oe_kurzbz, bezeichnung, path, organisationseinheittyp_kurzbz) AS (
					SELECT oe_kurzbz,
							bezeichnung || ' (' || organisationseinheittyp_kurzbz || ')' AS bezeichnung,
							oe_kurzbz || '|' AS path,
							organisationseinheittyp_kurzbz
					  FROM tbl_organisationseinheit
					 WHERE oe_parent_kurzbz IS NULL
					   AND aktiv = true
				 UNION ALL
					SELECT oe.oe_kurzbz,
							oe.bezeichnung || ' (' || oe.organisationseinheittyp_kurzbz || ')' AS bezeichnung,
							tree.path || oe.oe_kurzbz || '|' AS path,
							oe.organisationseinheittyp_kurzbz
					  FROM tree JOIN tbl_organisationseinheit oe ON (tree.oe_kurzbz = oe.oe_parent_kurzbz)
				)
				SELECT oe_kurzbz AS id,
						SUBSTRING(REGEXP_REPLACE(path, '[A-z]+\|', '-', 'g') || bezeichnung, 2) AS description
				  FROM tree";
		
		$parametersArray = array();
		
		if (is_array($typ) && count($typ) > 0)
		{
			$parametersArray[] = $typ;
			$qry .= ' WHERE organisationseinheittyp_kurzbz IN ?';
		}
		
		$qry .= ' ORDER BY path';
		
		return $this->execQuery($qry, $parametersArray);
	}

	/**
     * getOneLevel
     *
	 * This method get one level of the organisation tree, using the given parameters.
	 * It returns even the data from another table linked by the oe_kurzbz
	 *
     * @param	string	$schema		REQUIRED
     * @param	string	$table		REQUIRED
	 * @param	mixed	$fields		REQUIRED
	 * @param	string	$where		REQUIRED
	 * @param	string	$orderby	REQUIRED
	 * @param	string	$oe_kurzbz	REQUIRED
     * @return  array
     */
	public function getOneLevel($schema, $table, $fields, $where, $orderby, $oe_kurzbz)
	{
		$query = "WITH RECURSIVE organizations(_pk, _ppk) AS
					(
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o
						  WHERE o.oe_parent_kurzbz IS NULL
					  UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
					)
					SELECT orgs._pk, orgs._ppk, _joined_table.*
					FROM organizations orgs LEFT JOIN (
						SELECT %s.oe_kurzbz as _pk, %s
						  FROM %s.%s
						 WHERE %s
					) _joined_table ON (orgs._pk = _joined_table._pk)
					WHERE orgs._pk = ?
					ORDER BY %s";
		
		$query = sprintf($query, $table, $fields, $schema, $table, $where, $orderby);
		
		return $this->execQuery($query, array($oe_kurzbz));
	}
	
	public function getOneLevelAlias($table, $alias, $fields, $where, $orderby, $oe_kurzbz)
	{
		$query = "WITH RECURSIVE organizations(_pk, _ppk) AS
					(
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o
						 WHERE o.oe_parent_kurzbz IS NULL
					 UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
					)
					SELECT orgs._pk, orgs._ppk, _joined_table.*
					  FROM organizations orgs LEFT JOIN (
							SELECT %s.oe_kurzbz as _jtpk, %s
							  FROM %s
							 WHERE %s
						) _joined_table ON (orgs._pk = _joined_table._jtpk)
					 WHERE orgs._pk = ?
				  ORDER BY %s";
		
		$query = sprintf($query, $alias, $fields, $table, $where, $orderby);
		
		return $this->execQuery($query, array($oe_kurzbz));
	}
}