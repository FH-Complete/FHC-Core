<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class OrganisationseinheitLib
{
	/**
	 * Loads model OrganisationseinheitModel
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		// Loads model Organisationseinheit_model
		$this->ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
	}

	/**
     * treeSearch
     *
	 * This method searchs through the organisation tree starting from the bottom
	 * to the top, starting from the given oe_kurzbz. It stops when it finds a
	 * match with the other table, which attributes are passed as parameters:
	 * schema name, table name, fields to be selected, where conditions, orderby clause
	 *
     * @param	string	$schema		REQUIRED
     * @param	string	$table		REQUIRED
	 * @param	mixed	$fields		REQUIRED
	 * @param	string	$where		REQUIRED
	 * @param	string	$orderby	REQUIRED
	 * @param	string	$oe_kurzbz	REQUIRED
     * @return  array
     */
	public function treeSearch($schema, $table, $fields, $where, $orderby, $oe_kurzbz)
	{
		$select = "";
		if (is_array($fields))
		{
			for ($i = 0; $i < count($fields); $i++)
			{
				$select .= $fields[$i];
				if ($i != count($fields) - 1)
				{
					$select .= ", ";
				}
			}
		}
		else
		{
			$select = $fields;
		}

		$result = $this->ci->OrganisationseinheitModel->getOneLevel($schema, $table, $select, $where, $orderby, $oe_kurzbz);

		if (hasData($result))
		{
			if ($result->retval[0]->_ppk != null && $result->retval[0]->oe_kurzbz == null)
			{
				return $this->treeSearch($schema, $table, $select, $where, $orderby, $result->retval[0]->_ppk);
			}
		}

		return $result;
	}

	/**
	 * treeSearchEntire
	 *
	 * Like tree search, but it returns all the results found while travelling through the tree structure
	 */
	public function treeSearchEntire($table, $alias, $fields, $where, $orderby, $oe_kurzbz)
	{
		$select = "";
		if (is_array($fields))
		{
			for ($i = 0; $i < count($fields); $i++)
			{
				$select .= $fields[$i];
				if ($i != count($fields) - 1)
				{
					$select .= ", ";
				}
			}
		}
		else
		{
			$select = $fields;
		}

		$result = $this->ci->OrganisationseinheitModel->getOneLevelAlias($table, $alias, $select, $where, $orderby, $oe_kurzbz);

		if (hasData($result))
		{
			if ($result->retval[0]->_pk != null && $result->retval[0]->_ppk != null && $result->retval[0]->_jtpk != null)
			{
				$tmpResult = $this->treeSearchEntire($table, $alias, $select, $where, $orderby, $result->retval[0]->_ppk);

				if (hasData($tmpResult)
					&& $tmpResult->retval[0]->_pk != null
					&& $tmpResult->retval[0]->_ppk != null
					&& $tmpResult->retval[0]->_jtpk != null)
				{
					$result->retval = array_merge($result->retval, $tmpResult->retval);
				}
			}
			elseif ($result->retval[0]->_ppk != null)
			{
				$result = $this->treeSearchEntire($table, $alias, $select, $where, $orderby, $result->retval[0]->_ppk);
			}
		}

		return $result;
	}

	/**
	 * getRoot - Get the root of the organisation unit tree which belongs the given organisation unit parameter
	 */
	public function getRoot($oe_kurzbz)
	{
		$result = $this->ci->OrganisationseinheitModel->load($oe_kurzbz);

		if (hasData($result))
		{
			if ($result->retval[0]->oe_parent_kurzbz != null)
			{
				$result = $this->getRoot($result->retval[0]->oe_parent_kurzbz);
			}
		}

		return $result;
	}
}
