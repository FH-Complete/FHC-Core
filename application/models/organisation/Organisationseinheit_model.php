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

	/**
	 * getRecursiveList
	 */
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
						SUBSTRING(REGEXP_REPLACE(path, '[A-z0-9]+\|', '-', 'g') || bezeichnung, 2) AS description
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

	/**
     * getOneLevelAlias
     */
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

	/**
	 * Liefert die ChildNodes einer Organisationseinheit
	 * @param $oe_kurzbz
	 * @param bool $includeinactive wether to include inactive parent oes
	 * @return array mit den Childs inkl dem Uebergebenen Element
	 */
	public function getChilds($oe_kurzbz, $includeinactive = false)
	{
		$query = "
		WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
		(
			SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
			WHERE oe_kurzbz=? %s
			UNION ALL
			SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
			WHERE o.oe_parent_kurzbz=oes.oe_kurzbz %s
		)
		SELECT oe_kurzbz
		FROM oes
		GROUP BY oe_kurzbz;";

		$aktivstring = $includeinactive === true ? "" :  "AND aktiv = true";
		return $this->execQuery(sprintf($query, $aktivstring, $aktivstring), array($oe_kurzbz));
	}

	/**
	 * Liefert die OEs die im Tree ueberhalb der uebergebene OE liegen
	 * @param $oe_kurzbz
	 * @param bool $includeinactive wether to include inactive parent oes
	 * @return array|null
	 */
	public function getParents($oe_kurzbz, $includeinactive = false)
	{
		$query=
		"WITH RECURSIVE oes(oe_kurzbz, oe_parent_kurzbz) as
		(
			SELECT oe_kurzbz, oe_parent_kurzbz FROM public.tbl_organisationseinheit
			WHERE oe_kurzbz=? %s
			UNION ALL
			SELECT o.oe_kurzbz, o.oe_parent_kurzbz FROM public.tbl_organisationseinheit o, oes
			WHERE o.oe_kurzbz=oes.oe_parent_kurzbz %s
		)
		SELECT oe_kurzbz
		FROM oes";

		$aktivstring = $includeinactive === true ? "" :  "AND aktiv = true";
		return $this->execQuery(sprintf($query, $aktivstring, $aktivstring), array($oe_kurzbz));
	}

    /**
     * Get one parent only.
     * Easily retrieve department of a studiengang or fakultät of department etc.
     * @param $oe_kurzbz
     * @return array|null
     */
	public function getParent($oe_kurzbz)
    {
        if (is_string($oe_kurzbz))
        {
            $condition = '
                oe_kurzbz = (
                    SELECT
                        oe_parent_kurzbz
                    FROM
                        public.tbl_organisationseinheit
                    WHERE
                        oe_kurzbz = \''. $oe_kurzbz. '\'
                )
            ';
        }
        return $this->loadWhere($condition);
    }

    /**
     * @param string $oe_kurzbz
     * 
     * @return stdClass
     */
    public function getWithType($oe_kurzbz)
    {
    	$this->addSelect($this->dbTable . '.*, t.bezeichnung AS organisationseinheittyp');
    	$this->addJoin('public.tbl_organisationseinheittyp t', 'organisationseinheittyp_kurzbz');

    	return $this->load($oe_kurzbz);
    }

}
