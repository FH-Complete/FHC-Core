<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Issues extends Auth_Controller
{
	private $_uid;

	const FUNKTION_KURZBZ = 'ass'; // // user having this funktion can see issues for oes assigned with this funktion
	const BERECHTIGUNG_KURZBZ = 'system/issues_verwalten'; // user having this permission can see issues for oes assigned with this permission

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => array(self::BERECHTIGUNG_KURZBZ.':r'),
				'changeIssueStatus' => array(self::BERECHTIGUNG_KURZBZ.':r')
			)
		);

		// Load libraries
		$this->load->library('IssuesLib');
		$this->load->library('PermissionLib');
		$this->load->library('WidgetLib');

		$this->loadPhrases(
			array(
				'global',
				'ui',
				'filter'
			)
		);

		// Load models
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		$this->_setAuthUID(); // sets property uid
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
		$verarbeitetvon = $this->_uid;

		$errors = array();
		foreach ($issue_ids as $issue_id)
		{
			$issueRes = $this->issueslib->changeIssueStatus($issue_id, $status_kurzbz, $verarbeitetvon);

			if (isError($issueRes))
				$errors[] = getError($issueRes);
		}

		if (!isEmptyArray($errors))
			$this->outputJsonError(implode(", ", $errors));
		else
			$this->outputJsonSuccess("Status erfolgreich aktualisiert");
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
		$all_oe_kurzbz_with_funktionen = array();
		$oe_kurzbz_for_funktion = array();
		$benutzerfunktionRes = $this->BenutzerfunktionModel->getBenutzerFunktionByUid($this->_uid, null, date('Y-m-d'), date('Y-m-d'));

		if (isError($benutzerfunktionRes))
			show_error(getError($benutzerfunktionRes));

		if (hasData($benutzerfunktionRes))
		{
			foreach (getData($benutzerfunktionRes) as $benutzerfunktion)
			{
				$all_oe_kurzbz_with_funktionen[$benutzerfunktion->oe_kurzbz][] = $benutzerfunktion->funktion_kurzbz;
				if ($benutzerfunktion->funktion_kurzbz == self::FUNKTION_KURZBZ) // separate oes for the funktion needed for displaying issues
					$oe_kurzbz_for_funktion[] = $benutzerfunktion->oe_kurzbz;
			}
		}

		// add oes for which there is the issues_verwalten Berechtigung
		if (!$oe_kurzbz_berechtigt = $this->permissionlib->getOE_isEntitledFor(self::BERECHTIGUNG_KURZBZ))
			show_error('Keine Berechtigung oder Fehler bei Berechtigungsprüfung');

		$all_oe_kurzbz_berechtigt = array_unique(array_merge($oe_kurzbz_for_funktion, $oe_kurzbz_berechtigt));

		return array(
			'all_oe_kurzbz_with_funktionen' => $all_oe_kurzbz_with_funktionen,
			'all_oe_kurzbz_berechtigt' => $all_oe_kurzbz_berechtigt
		);
	}
}
