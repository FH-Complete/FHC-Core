<?php
defined('BASEPATH') || exit('No direct script access allowed');

class IssueChecker extends FHCAPI_Controller
{
	const DEFAULT_PERMISSION = 'system/issues_verwalten:r';

	protected $person_id;
	protected $_extensionName = null;
	protected $_codeLibMappings = [];
	protected $_codeProducerLibMappings = [];
	protected $_app = null;

	protected $errors = [];
	protected $infos = [];

	public function __construct($permissions)
	{
		$default_permissions = [
			'checkPerson' => self::DEFAULT_PERMISSION,
			'countPersonOpenIssues' => self::DEFAULT_PERMISSION
		];

		if(!is_array($permissions))
		{
		    $this->terminateWithError("Issue Checker: permissions must be an array");
		}
		
		$merged_permissions = array_merge($default_permissions, $permissions);
		
		parent::__construct($merged_permissions);

		$this->load->model('system/Issue_model', 'IssueModel');
		$this->load->model('person/Person_model', 'PersonModel');
	}

	public function checkPerson()
	{
		$person_id = $this->input->post('person_id', true);

		if (!is_numeric($person_id)) $this->terminateWithError($this->p->t('ui', 'error_invalidId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);

		$this->person_id = intval($person_id);

		$persRes = $this->PersonModel->load($this->person_id);
		if (!hasData($persRes)) $this->terminateWithError('Person with id ' . $this->person_id . ' not found.', self::ERROR_TYPE_GENERAL);

		$allCodeLibMappings = array_merge($this->_codeLibMappings, $this->_codeProducerLibMappings);

		$this->load->library(
			'issues/PlausicheckProducerLib',
			array(
				'extensionName' => $this->_extensionName,
				'codeLibMappings' => $allCodeLibMappings
			),
			'PlausicheckProducerLib'
		);

		$this->load->library(
			'issues/PlausicheckResolverLib',
			array(
				'extensionName' => $this->_extensionName,
				'codeLibMappings' => $this->_codeLibMappings,
				'codeProducerLibMappings' => $this->_codeProducerLibMappings
			),
			'PlausicheckResolverLib'
		);

		$this->produceIssues();
		$this->resolveIssues();
		$this->produceIssues();

		$openIssueCountRes = $this->countOpenIssues(array_keys($allCodeLibMappings));
		if (isError($openIssueCountRes)) $this->terminateWithError(getError($openIssueCountRes), self::ERROR_TYPE_GENERAL);

		$data = array(
			'person_id' => $this->person_id,
			'openissues' => hasData($openIssueCountRes) ? getData($openIssueCountRes) : 0
		);

		$this->addMeta('errors', $this->errors);
		$this->addMeta('infos', $this->infos);

		$this->terminateWithSuccess($data);
	}

	public function countPersonOpenIssues()
	{
		$person_id = $this->input->get('person_id', true);

		if (!is_numeric($person_id)) $this->terminateWithError($this->p->t('ui', 'error_invalidId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);

		$this->person_id = intval($person_id);

		$persRes = $this->PersonModel->load($this->person_id);

		if (!hasData($persRes)) $this->terminateWithError('Person with id ' . $this->person_id . ' not found.', self::ERROR_TYPE_GENERAL);

		$openIssueCountRes = $this->countOpenIssues(array_keys(array_merge($this->_codeLibMappings, $this->_codeProducerLibMappings)));
		if (isError($openIssueCountRes)) $this->terminateWithError(getError($openIssueCountRes), self::ERROR_TYPE_GENERAL);

		$data = array(
			'person_id' => $this->person_id,
			'openissues' => hasData($openIssueCountRes) ? getData($openIssueCountRes) : 0
		);

		$this->addMeta('errors', $this->errors);
		$this->addMeta('infos', $this->infos);

		$this->terminateWithSuccess($data);
	}

	protected function countOpenIssues($fehlercodes)
	{
		if (isEmptyArray($fehlercodes)) return success([]);

		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(
			$fehlercodes,
			$this->person_id
		);

		// log error if occured
		if (isError($openIssuesRes)) return $openIssuesRes;

		$issues = hasData($openIssuesRes) ? getData($openIssuesRes) : [];
		$issuescount = is_array($issues) || $issues instanceof Countable ? count($issues) : 0;

		return success($issuescount);
	}

	protected function produceIssues()
	{
		if (isEmptyArray($this->_codeLibMappings) && isEmptyArray($this->_codeProducerLibMappings)) return success([]);

		$allCodeLibMappings = array_merge($this->_codeLibMappings, $this->_codeProducerLibMappings);

		$result = $this->PlausicheckProducerLib->producePlausicheckIssues(
			array('person_id' => $this->person_id)
		);

		// log if error, or log info if inserted new issue
		if (isset($result->errors))
			$this->errors = array_merge($this->errors, $result->errors);
		if (isset($result->infos))
			$this->infos = array_merge($this->infos, $result->infos);
	}

	protected function resolveIssues()
	{
		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(
			array_keys(array_merge($this->_codeLibMappings, $this->_codeProducerLibMappings)),
			$this->person_id
		);

		if (hasData($openIssuesRes))
		{
			$openIssues = getData($openIssuesRes);

			$result = $this->PlausicheckResolverLib->resolvePlausicheckIssues($openIssues);

			// log if error, or log info if inserted new issue
			if (isset($result->errors))
				$this->errors = array_merge($this->errors, $result->errors);
			if (isset($result->infos))
				$this->infos = array_merge($this->infos, $result->infos);
		}
	}
}
