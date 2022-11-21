<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Issues extends Auth_Controller
{
	private $_uid;

	const FUNKTION_KURZBZ = 'ass'; // user having this funktion can see issues for oes assigned with this funktion
	const BERECHTIGUNG_KURZBZ = 'system/issues_verwalten'; // user having this permission can see issues for oes assigned with this permission

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array(self::BERECHTIGUNG_KURZBZ.':r'),
				'changeIssueStatus' => array(self::BERECHTIGUNG_KURZBZ.':rw')
			)
		);

		// Load libraries
		$this->load->library('IssuesLib');
		$this->load->library('PermissionLib');
		$this->load->library('WidgetLib');

		// Load models
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter',
				'lehre',
				'person',
				'fehlermonitoring'
			)
		);

		$this->_setAuthUID(); // sets property uid
		$this->setControllerId(); // sets the controller id
	}

	public function index()
	{
		$oes_for_issues = $this->_getOesForIssues();

		$this->load->view(
			'system/issues/issues',
			$oes_for_issues
		);
	}

	/**
	 * Initializes issues status change
	 */
	public function changeIssueStatus()
	{
		$issue_ids = $this->input->post('issue_ids');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$user = $this->_uid;

		$errors = array();
		foreach ($issue_ids as $issue_id)
		{
			switch ($status_kurzbz)
			{
				case IssuesLib::STATUS_NEU:
					$changeIssueMethod = 'setNeu';
					break;
				case IssuesLib::STATUS_IN_BEARBEITUNG:
					$changeIssueMethod = 'setInBearbeitung';
					break;
				case IssuesLib::STATUS_BEHOBEN:
					$changeIssueMethod = 'setBehoben';
					break;
				default:
					$changeIssueMethod = null;
					break;
			}

			if (isEmptyString($changeIssueMethod))
				$errors[] = "Invalid issue status given";
			else
			{
				$issueRes = $this->issueslib->{$changeIssueMethod}($issue_id, $user);

				if (isError($issueRes))
					$errors[] = getError($issueRes);
			}
		}

		if (!isEmptyArray($errors))
			$this->outputJsonError(implode(", ", $errors));
		else
			$this->outputJsonSuccess("Status successfully refreshed");
	}

	/**
	 * Retrieve the UID of the logged user and checks if it is valid
	 */
	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid) show_error('User authentification failed');
	}

	/**
	 * Gets oes of logged in user, which are needed to display issues of the user.
	 * This includes oes assigned by a funktio and as the issue permission.
	 * @return array
	 */
	private function _getOesForIssues()
	{
		// get oes of uid for which there is a current funktion
		$all_funktionen_oe_kurzbz = array();
		$oe_kurzbz_for_funktion = array();
		$benutzerfunktionRes = $this->BenutzerfunktionModel->getBenutzerFunktionByUid($this->_uid, null, date('Y-m-d'), date('Y-m-d'));

		if (isError($benutzerfunktionRes))
			show_error(getError($benutzerfunktionRes));

		if (hasData($benutzerfunktionRes))
		{
			foreach (getData($benutzerfunktionRes) as $benutzerfunktion)
			{
				$all_funktionen_oe_kurzbz[$benutzerfunktion->oe_kurzbz][] = $benutzerfunktion->funktion_kurzbz;

				// separate oes for the additional funktion which enables displaying issues
				if ($benutzerfunktion->funktion_kurzbz == self::FUNKTION_KURZBZ)
				{
					$oe_kurzbz_for_funktion[] = $benutzerfunktion->oe_kurzbz;

					// permission also for all oes under the oe for which funktion is assigend
					$childOesFunktionRes = $this->OrganisationseinheitModel->getChilds($benutzerfunktion->oe_kurzbz);

					if (isError($childOesFunktionRes))
						show_error(getError($childOesFunktionRes));

					if (hasData($childOesFunktionRes))
					{
						$childOesFunktion = getData($childOesFunktionRes);

						foreach ($childOesFunktion as $childOeFunktion)
						{
							if (!in_array($childOeFunktion->oe_kurzbz, $oe_kurzbz_for_funktion))
								$oe_kurzbz_for_funktion[] = $childOeFunktion->oe_kurzbz;
						}
					}
				}
			}
		}

		// add oes for which there is the "manage issues" Berechtigung
		$oe_kurzbz_berechtigt = $this->permissionlib->getOE_isEntitledFor(self::BERECHTIGUNG_KURZBZ);

		if (!$oe_kurzbz_berechtigt)
			show_error('No permission or error when checking permissions');

		$all_oe_kurzbz_berechtigt = array_unique(array_merge($oe_kurzbz_for_funktion, $oe_kurzbz_berechtigt));

		return array(
			'all_funktionen_oe_kurzbz' => $all_funktionen_oe_kurzbz,
			'all_oe_kurzbz_berechtigt' => $all_oe_kurzbz_berechtigt
		);
	}
}
