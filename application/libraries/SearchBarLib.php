<?php
/**
 * Copyright (C) 2022 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \stdClass as stdClass;

/**
 *
 */
class SearchBarLib
{
	// Error constats
	const ERROR_WRONG_JSON = 'ERR001';
	const ERROR_WRONG_SEARCHSTR = 'ERR002';
	const ERROR_NO_TYPES = 'ERR003';
	const ERROR_WRONG_TYPES = 'ERR004';

	// List of allowed types of search
	const ALLOWED_TYPES = ['mitarbeiter', 'organisationunit', 'raum', 'person', 'student', 'prestudent', 'document', 'cms'];

	const PHOTO_IMG_URL = '/cis/public/bild.php?src=person&person_id=';

	private $_ci; // Code igniter instance

	/**
	 * Gets the CI instance and loads model
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get code igniter instance

		// It is loaded only to have the DB_Model available
		$this->_ci->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * It performes the search of the given search string using the specified search types
	 */
	public function search($searchstr, $types)
	{
		// Checks if the given parameters are fine
		$search = $this->_checkParameters($searchstr, $types);

		// If the check was successful then perform the search
		if (isSuccess($search)) $search = $this->_search($searchstr, $types);

		return $search; // return the result
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Checks:
	 * - The given searchstr is a not empty string
	 * - The given types is a not empty array and contains allowed search types
	 */
	private function _checkParameters($searchstr, $types)
	{
		// If searchstr is empty
		if (isEmptyString($searchstr)) return error(self::ERROR_WRONG_SEARCHSTR);

		// If types is not an array or it is empty
		if (isEmptyArray($types)) return error(self::ERROR_NO_TYPES);

		// If all the elements in types are allowed search types
		if (!isEmptyArray(array_diff($types, self::ALLOWED_TYPES))) return error(self::ERROR_WRONG_TYPES);

		return success(); // The check is fine!
	}

	/**
	 * Loops on types and perform the search of that type using searchstr
	 * Then it collects all the returned data into an array as property of an object
	 */
	private function _search($searchstr, $types)
	{
		// Object to be returned
		$result = new stdClass();
		$result->data = array();

		// For each search type
		foreach ($types as $type)
		{
			// Perform the search and then add the result to data
			$result->data = array_merge($result->data, $this->{'_'.$type}($searchstr, $type));
		}

		return $result;
	}

	/**
	 * Search for employees
	 */
	private function _mitarbeiter($searchstr, $type)
	{
		$dbModel = new DB_Model();

		$employees = $dbModel->execReadOnlyQuery('
			SELECT
				\''.$type.'\' AS type,
				b.uid AS uid,
				p.person_id AS person_id,
				p.vorname || \' \' || p.nachname AS name,
				ARRAY_AGG(DISTINCT(org.bezeichnung)) AS organisationunit_name,
				COALESCE(b.alias, b.uid) || \''.'@'.DOMAIN.'\' AS email,
				TRIM(COALESCE(k.kontakt, \'\') || \' \' || COALESCE(m.telefonklappe, \'\')) AS phone,
				\''.base_url(self::PHOTO_IMG_URL).'\' || p.person_id AS photo_url, 
				ARRAY_AGG(DISTINCT(stdkst.bezeichnung)) AS standardkostenstelle 
			  FROM public.tbl_mitarbeiter m
			  JOIN public.tbl_benutzer b ON(b.uid = m.mitarbeiter_uid)
			  JOIN (
				SELECT o.bezeichnung, bf.uid
				  FROM public.tbl_benutzerfunktion bf
				  JOIN public.tbl_organisationseinheit o USING(oe_kurzbz)
				 WHERE bf.funktion_kurzbz = \'kstzuordnung\'
				   AND (bf.datum_von IS NULL OR bf.datum_von <= NOW())
				   AND (bf.datum_bis IS NULL OR bf.datum_bis >= NOW())
				GROUP BY o.bezeichnung, bf.uid
			) stdkst ON stdkst.uid = b.uid 
			  JOIN public.tbl_person p USING(person_id)
			  JOIN (
				SELECT o.bezeichnung, bf.uid
				  FROM public.tbl_benutzerfunktion bf
				  JOIN public.tbl_organisationseinheit o USING(oe_kurzbz)
				 WHERE bf.funktion_kurzbz = \'oezuordnung\'
				   AND (bf.datum_von IS NULL OR bf.datum_von <= NOW())
				   AND (bf.datum_bis IS NULL OR bf.datum_bis >= NOW())
				GROUP BY o.bezeichnung, bf.uid
			) org ON org.uid = b.uid 
		     LEFT JOIN (
				SELECT kontakt, standort_id
				  FROM public.tbl_kontakt
				 WHERE kontakttyp = \'telefon\'
			) k ON(k.standort_id = m.standort_id)
			 WHERE b.uid ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
			    OR p.vorname ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
			    OR p.nachname ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
			    OR org.bezeichnung ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
			    OR stdkst.bezeichnung ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
		      GROUP BY type, b.uid, p.person_id, name, email, m.telefonklappe, phone
		');

		// If something has been found then return it
		if (hasData($employees)) return getData($employees);

		// Otherwise return an empty array
		return array();
	}

	/**
	 * Seach for organisation units
	 */
	private function _organisationunit($searchstr, $type)
	{
		$dbModel = new DB_Model();

		$ous = $dbModel->execReadOnlyQuery('
			SELECT
				\''.$type.'\' AS type,
				o.oe_kurzbz AS oe_kurzbz,
				o.bezeichnung AS name,
				oParent.oe_kurzbz AS parentoe_kurzbz,
				oParent.bezeichnung AS parentoe_name,
				ARRAY_AGG(DISTINCT(bfLeader.uid)) AS leader_uid,
				ARRAY_AGG(DISTINCT(bfLeader.vorname || \' \' || bfLeader.nachname)) AS leader_name,
				COUNT(bfCount.benutzerfunktion_id) AS number_of_people,
				(CASE WHEN o.mailverteiler = TRUE THEN o.oe_kurzbz || \''.'@'.DOMAIN.'\' END) AS mailgroup
			  FROM public.tbl_organisationseinheit o
		     LEFT JOIN public.tbl_organisationseinheit oParent ON(oParent.oe_kurzbz = o.oe_parent_kurzbz)
		     LEFT JOIN (
				SELECT benutzerfunktion_id, oe_kurzbz
				  FROM public.tbl_benutzerfunktion
				 WHERE funktion_kurzbz = \'oezuordnung\'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
			) bfCount ON(bfCount.oe_kurzbz = o.oe_kurzbz)
		     LEFT JOIN (
				SELECT bf.oe_kurzbz, bf.uid, p.vorname, p.nachname
				  FROM public.tbl_benutzerfunktion bf
				  JOIN public.tbl_benutzer b USING(uid)
				  JOIN public.tbl_person p USING(person_id)
				 WHERE funktion_kurzbz = \'Leitung\'
				   AND (datum_von IS NULL OR datum_von <= NOW())
				   AND (datum_bis IS NULL OR datum_bis >= NOW())
				   AND b.aktiv = TRUE
			) bfLeader ON(bfLeader.oe_kurzbz = o.oe_kurzbz)
			 WHERE o.oe_kurzbz ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
			    OR o.bezeichnung ILIKE \'%'.$dbModel->escapeLike($searchstr).'%\'
		      GROUP BY type, o.oe_kurzbz, o.bezeichnung, oParent.oe_kurzbz, oParent.bezeichnung
		');

		// If something has been found
		if (hasData($ous))
		{
			// Loop through the returned dataset
			foreach (getData($ous) as $ou)
			{
				// Create the new property leaders as an empty array
				$ou->leaders = array();

				// Loop through the found leaders for this organisation unit
				for ($i = 0; $i < count($ou->leader_uid); $i++)
				{
					// If a leader exists for this organisationunit and has a name :D
					if (!isEmptyString($ou->leader_uid[$i]) && !isEmptyString($ou->leader_name[$i]))
					{
						// Empty object that will contains the leader uid and name
						$leader = new stdClass();
						// Set the properties name and uid
						$leader->uid = $ou->leader_uid[$i];
						$leader->name = $ou->leader_name[$i];
						// Add the leader object to the leaders array
						$ou->leaders[] = $leader;
					}
				}

				// Remove the not needed properties leader_uid and leader_name
				unset($ou->leader_uid);
				unset($ou->leader_name);
			}

			// Returns the changed dataset
			return getData($ous);
		}

		// Otherwise return an empty array
		return array();
	}

	/**
	 * Search for persons
	 */
	private function _person($searchstr, $type)
	{
		return array();
	}

	/**
	 * Search for students
	 */
	private function _student($searchstr, $type)
	{
		return array();
	}

	/**
	 * Search for prestudents
	 */
	private function _prestudent($searchstr, $type)
	{
		return array();
	}

	/**
	 * Search for documents
	 */
	private function _document($searchstr, $type)
	{
		return array();
	}

	/**
	 * Search for CMSs
	 */
	private function _cms($searchstr, $type)
	{
		return array();
	}

	/**
	 * Search for rooms
	 */
	private function _raum($searchstr, $type)
	{
		return array();
	}
}

