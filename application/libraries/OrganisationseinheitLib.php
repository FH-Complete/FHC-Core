<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class OrganisationseinheitLib
{
	public function __construct()
	{
		$this->ci =& get_instance();
		
		// Loads model Organisationseinheit_model
		$this->ci->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		
		// Loads helper message to manage returning messages
		$this->ci->load->helper('Message');
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
}