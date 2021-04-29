<?php

class Variablenname_model extends DB_Model
{
	// Contains SQL queries retrieving default variable values if no default is set.
	private $_dynamic_defaults = array(
		'semester_aktuell' => 'SELECT studiensemester_kurzbz FROM public.tbl_studiensemester WHERE ende>now() ORDER BY start LIMIT 1',
		'infocenter_studiensemester' => 'SELECT studiensemester_kurzbz FROM (
										   SELECT DISTINCT ON (studienjahr_kurzbz) start, studiensemester_kurzbz
										   FROM public.tbl_studiensemester
										   ORDER BY studienjahr_kurzbz, start
                                   		) sem
										WHERE start > now()
										LIMIT 1;',
        'infocenter_studiensgangtyp' => 'SELECT infocenter_studiensgangtyp FROM public.tbl_variablename LIMIT 1'
	);

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_variablenname';
		$this->pk ='name';
	}

	/**
	 * Gets defaults for user variables.
	 * If no default value present in table, SQL can be executed for retrieving the value.
	 * @param $names optionally get only defaults for certain variables
	 * @return array
	 */
	public function getDefaults($names = null)
	{
		$defaults = array();

		$qry = "SELECT name, defaultwert FROM public.tbl_variablenname";

		if (!isEmptyArray($names))
		{
			$qry .= " WHERE name IN ?";
		}
		$qry .= ";";

		$defaultsres = $this->execQuery($qry, array('name' => $names));

		if (hasData($defaultsres))
		{
			$defaults = getData($defaultsres);

			foreach ($defaults as $default)
			{
				if (!isset($default->defaultwert))
				{
					if (isset($this->_dynamic_defaults[$default->name]))
					{
						$dyndefault = $this->execQuery($this->_dynamic_defaults[$default->name]);
						if (hasData($dyndefault))
						{
							$dyndefault = getData($dyndefault);

							if (count($dyndefault) === 1)
							{
								foreach ($dyndefault[0] as $value)
								{
									$default->defaultwert = $value;
									break;
								}
							}
						}
					}
				}
			}
		}

		return success($defaults);
	}
}
