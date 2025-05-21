<?php

class Vorlage_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlage';
		$this->pk = 'vorlage_kurzbz';
	}

	/**
	 * Returns mime types
	 */
	public function getMimeTypes()
	{
		$query = 'SELECT DISTINCT mimetype FROM public.tbl_vorlage ORDER BY mimetype';

		return $this->execQuery($query);
	}

	/**
	 * Returns all Vorlagen for archive
	 */
	public function getArchivVorlagen()
	{
		$query ="SELECT * FROM public.tbl_vorlage WHERE archivierbar=true ORDER BY bezeichnung";

		return $this->execQuery($query);
	}

	/**
	 * Returns all Vorlagen
	 * that belongs to the organisation units of the user
	 * and the parents of those organisation units until the root of the
	 * @param Array Array of $oe_kurzbz
	 * @return object Array of Vorlagen
	 */
	public function getAllVorlagenByOe($oe_kurzbz)
	{
		// Loads library OrganisationseinheitLib
		$this->load->library('OrganisationseinheitLib');

		$vorlage = success(array()); // Default value

		$table = '(
					SELECT v.vorlage_kurzbz,
							v.bezeichnung,
							vs.version,
							vs.oe_kurzbz,
							vs.aktiv,
							vs.subject,
							vs.text,
							v.mimetype
					FROM tbl_vorlagestudiengang vs
					JOIN tbl_vorlage v USING(vorlage_kurzbz)
				) templates';

		$alias = 'templates';

		$fields = array(
			'templates.vorlage_kurzbz AS id',
			'templates.bezeichnung || \' (\' || UPPER(templates.oe_kurzbz) || \')\' AS description'
		);

		$where = 'templates.aktiv = TRUE
					AND templates.subject IS NOT NULL
					AND templates.text IS NOT NULL
					AND templates.mimetype = \'text/html\'
			   GROUP BY 1, 2, 3';

		$order_by = 'description ASC';

		if (!is_array($oe_kurzbz))
		{
			$vorlage = $this->organisationseinheitlib->treeSearchEntire(
				$table,
				$alias,
				$fields,
				$where,
				$order_by,
				$oe_kurzbz
			);
		}
		else // is an array
		{
			// Get the vorlage for each organisation unit
			foreach($oe_kurzbz as $val)
			{
				$tmpVorlage = $this->organisationseinheitlib->treeSearchEntire(
					$table,
					$alias,
					$fields,
					$where,
					$order_by,
					$val
				);

				// Everything is ok and data are inside
				if (hasData($tmpVorlage))
				{
					// If it's the first vorlage copy it
					if (!hasData($vorlage))
					{
						for ($j = 0; $j < count(getData($tmpVorlage)); $j++)
						{
							if (getData($tmpVorlage)[$j]->id != '')
							{
								array_push($vorlage->retval, getData($tmpVorlage)[$j]);
							}
						}
					}
					else // checks for duplicates, if it's not already present push it into the array getData($vorlage)
					{
						for ($j = 0; $j < count(getData($tmpVorlage)); $j++)
						{
							$found = false;
							$currentTmpVorlageData = null;

							for ($i = 0; $i < count(getData($vorlage)); $i++)
							{
								$currentTmpVorlageData = getData($tmpVorlage)[$j];

								if (getData($vorlage)[$i]->id == getData($tmpVorlage)[$j]->id
									&& getData($vorlage)[$i]->_pk == getData($tmpVorlage)[$j]->_pk
									&& getData($vorlage)[$i]->_ppk == getData($tmpVorlage)[$j]->_ppk
									&& getData($vorlage)[$i]->_jtpk == getData($tmpVorlage)[$j]->_jtpk)
								{
									$found = true;
									break;
								}
							}

							if (!$found && $currentTmpVorlageData->id != '')
							{
								array_push($vorlage->retval, $currentTmpVorlageData);
							}
						}
					}
				}
			}
		}

		return $vorlage;
	}

}
