<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Class VariableLib
 * Provides functionality for managing uservariables for currently logged in user.
 * Preloads variables for a user, so variables can be retrieved from views without getting uid again.
 */
class VariableLib
{
	private $_variables; // Contains the retrieved variables

	/**
	 * VariableLib constructor.
	 * Loads variable of logged in user.
	 * @param $loggeduid
	 */
	public function __construct($loggeduid)
	{
		$this->_ci =& get_instance();

		$this->_variables = null;

		$this->_ci->load->model('system/Variable_model', 'VariableModel');
		$this->_ci->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		if (isset($loggeduid['uid']) && !isEmptyString($loggeduid['uid']))
			$this->_setVariables($loggeduid['uid']);
		else
		{
			show_error('uid of logged user not passed!');
		}
	}

	/**
	 * Gets an already loaded user variable by variable name.
	 * @param $name
	 * @return mixed|null
	 */
	public function getVar($name)
	{
		return isset($this->_variables[$name]) ? $this->_variables[$name] : null;
	}

	/**
	 * Changes variables having Studiensemester as value. Sets variable value to next or previous Semester.
	 * @param $uid variable is set for this user
	 * @param $name variable name
	 * @param $change if positive, variable value is set to next semester, negative - previous semester
	 * @return array if change was successfull, uid and variable name. Infotext otherwise.
	 */
	public function changeStudiensemesterVar($uid, $name, $change)
	{
		$result = error('error when setting variable!');
		$notchangedtext = "Studiensemester variable not changed.";

		if (!isEmptyString($uid) && !isEmptyString($name) && is_numeric($change))
		{
			$change = (int)$change;
			$varres = $this->_ci->VariableModel->getVariables($uid, array($name));

			if (isSuccess($varres))
			{
				if (hasData($varres))
				{
					$currStudiensemester = getData($varres);

					if ($change === 0)
					{
						$result = success($notchangedtext);
					}
					else
					{
						if ($change > 0)
						{
							$changedsem = $this->_ci->StudiensemesterModel->getNextFrom($currStudiensemester[$name]);
						}
						elseif ($change < 0)
						{
							$changedsem = $this->_ci->StudiensemesterModel->getPreviousFrom($currStudiensemester[$name]);
						}

						if (hasData($changedsem))
						{
							$changedsem = getData($changedsem);

							$result = $this->_ci->VariableModel->setVariable($uid, $name, $changedsem[0]->studiensemester_kurzbz);
							//update property
							$this->_setVariable($uid, $name);
						}
						else
						{
							$result = success($notchangedtext);
						}
					}
				}
			}
		}
		return $result;
	}

	public function changeStudengangsTypVar($uid, $name, $change)
	{
		$result = error('error when setting variable!');

		if (isEmptyString($uid) || isEmptyString($name) || isEmptyString($change))
            return $result;

		$result = $this->_ci->VariableModel->setVariable($uid, $name, $change);
        $this->_setVariable($uid, $name);
        return $result;
	}

	/**
	 * "Refreshes" variable value with given name by retrieving current value from db and saving it.
	 * @param $uid
	 * @param $name
	 */
	private function _setVariable($uid, $name)
	{
		$variable = $this->_ci->VariableModel->getVariables($uid, array($name));

		if (hasData($variable))
		{
			$variable = getData($variable);
			$this->_variables[$name] = $variable[$name];
		}
	}

	/**
	 * "Refreshes" all variable values by retrieving current values from db and saving them.
	 * @param $uid
	 */
	private function _setVariables($uid)
	{
		$variables = $this->_ci->VariableModel->getVariables($uid);
		if (hasData($variables))
		{
			$this->_variables = getData($variables);
		}
	}
}
