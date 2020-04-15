<?php
class Variable_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_variable';
		$this->pk = array('uid', 'name');
		$this->hasSequence = false;

		$this->load->model('system/Variablenname_model', 'VariablennameModel');
	}

	/**
	 * Gets user variables and values for a uid.
	 * If no value found in tbl_variable, default as defined in variablename_model is retrieved.
	 * @param $uid
	 * @param null $names optionally get only certain variables
	 * @return array
	 */
	public function getVariables($uid, $names = null)
	{
		if (isEmptyString($uid))
			$result = error('wrong parameters passed');
		else
		{
			$vardata = array();
			$parametersArray = array($uid);

			$qry = "SELECT name, wert FROM public.tbl_variable WHERE uid = ?";

			if (!isEmptyArray($names))
			{
				$qry .= " AND name IN ?";
				$parametersArray[] = $names;
			}
			$qry .= ";";

			$varresults = $this->execQuery($qry, $parametersArray);

			if (hasData($varresults))
			{
				$varresults = getData($varresults);
				foreach ($varresults as $varresult)
				{
					if (isset($varresult->wert))
						$vardata[$varresult->name] = $varresult->wert;
				}
			}

			$vardefaults = $this->VariablennameModel->getDefaults($names);

			if (hasData($vardefaults))
			{
				$vardefaults = getData($vardefaults);

				foreach ($vardefaults as $vardefault)
				{
					if (!isset($vardata[$vardefault->name]) && isset($vardefault->defaultwert))
					{
						$vardata[$vardefault->name] = $vardefault->defaultwert;
					}
				}
			}
			$result = success($vardata);
		}

		return $result;
	}

	/**
	 * Sets a variable value for a uid. Adds new entry if not present, updates entry otherwise.
	 * @param $uid
	 * @param $name
	 * @param $wert
	 * @return array
	 */
	public function setVariable($uid, $name, $wert)
	{
		$result = error('error when setting variable!');
		if (!isEmptyString($uid) && !isEmptyString($name) && !isEmptyString($wert))
		{
			$varres = $this->loadWhere(array('uid' => $uid, 'name' => $name));

			if (isSuccess($varres))
			{
				if (hasData($varres))
				{
					$result = $this->VariableModel->update(array('uid' => $uid, 'name' => $name), array('wert' => $wert));
				}
				else
					$result = $this->VariableModel->insert(array('uid' => $uid, 'name' => $name, 'wert' => $wert));
			}
		}

		return $result;
	}
}
