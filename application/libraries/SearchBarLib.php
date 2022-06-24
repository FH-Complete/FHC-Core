<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class SearchBarLib
{
	// 
	const ERROR_WRONG_SEARCHSTR = 'ERR001';
	const ERROR_NO_TYPES = 'ERR002';
	const ERROR_WRONG_TYPES = 'ERR003';

	// 
	const ALLOWED_TYPES = ['mitarbeiter', 'organisationunit', 'raum', 'person', 'student', 'prestudent', 'document', 'cms'];

	private $_ci; // Code igniter instance

	/**
	 * Gets the CI instance and loads model
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		//
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 *
	 */
	public function search($searchstr, $types)
	{
		//
		$search = $this->_checkParameters($searchstr, $types);
		//
		if (isSuccess($search)) $search = $this->_search($searchstr, $types);

		return $search; //
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 *
	 */
	private function _checkParameters($searchstr, $types)
	{
		//
		if (isEmptyString($searchstr))
		{
			return error(self::ERROR_WRONG_SEARCHSTR);
		}

		//
		if (isEmptyArray($types))
		{
			return error(self::ERROR_NO_TYPES);
		}
		else
		{
			// 
			if (!isEmptyArray(array_diff($types, self::ALLOWED_TYPES)))
			{
				return error(self::ERROR_WRONG_TYPES);
			}
		}

		return success(); //
	}

	/**
	 *
	 */
	private function _search($searchstr, $types)
	{
		$data = array(); //

		//
		foreach ($types as $type)
		{
			//
			$data = array_merge($data, $this->{'_'.$type}($searchstr, $type));
		}

		$result = new stdClass();
		$result->data = $data;

		return $result;
	}

	/**
	 *
	 */
	private function _mitarbeiter($searchstr, $type)
	{
		$dbModel = new DB_Model();

		$employees = $dbModel->execReadOnlyQuery('
			SELECT
				\''.$type.'\' AS type,
				b.uid,
				p.person_id,
				p.vorname || \' \' || p.nachname AS name,
				b.uid AS email,
				m.telefonklappe AS phone
			  FROM public.tbl_mitarbeiter m
			  JOIN public.tbl_benutzer b ON(b.uid = m.mitarbeiter_uid)
			  JOIN public.tbl_person p USING(person_id)
			 WHERE m.mitarbeiter_uid ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
		');

		//
		if (hasData($employees)) return getData($employees);

		//
		return array();
	}

	/**
	 *
	 */
	private function _organisationunit($searchstr, $type)
	{
		$dbModel = new DB_Model();

		$ous = $dbModel->execReadOnlyQuery('
			SELECT
				\''.$type.'\' AS type,
				o.oe_kurzbz,
				o.bezeichnung AS name,
				o.oe_parent_kurzbz AS parentoe_kurzbz,
				o.oe_parent_kurzbz AS parentoe_name
			  FROM public.tbl_organisationseinheit o
			 WHERE o.oe_kurzbz ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
		');

		//
		if (hasData($ous)) return getData($ous);

		//
		return array();
	}

	/**
	 *
	 */
	private function _person($searchstr, $type)
	{
		return array();
	}

	/**
	 *
	 */
	private function _student($searchstr, $type)
	{
		return array();
	}

	/**
	 *
	 */
	private function _prestudent($searchstr, $type)
	{
		return array();
	}

	/**
	 *
	 */
	private function _document($searchstr, $type)
	{
		return array();
	}

	/**
	 *
	 */
	private function _cms($searchstr, $type)
	{
		return array();
	}

	/**
	 *
	 */
	private function raum($searchstr, $type)
	{
		return array();
	}
}

