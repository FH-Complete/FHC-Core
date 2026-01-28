<?php
defined('BASEPATH') || exit('No direct script access allowed');

class IssueChecker extends FHCAPI_Controller
{
	const DEFAULT_PERMISSION = 'system/issues_verwalten:r';

	protected $person_id;
	protected $_extensionName = null;
	protected $_fehlercodes = [];
	//protected $_app = null; TODO possible to check for all fehler of app?

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
		$this->load->model('system/Fehler_model', 'FehlerModel');
		$this->load->model('person/Person_model', 'PersonModel');

		// get fehler kurzbz from fehlercodes
		$this->FehlerModel->addSelect('fehler_kurzbz');
		if (!isEmptyArray($this->_fehlercodes)) $this->FehlerModel->db->where_in('tbl_fehler.fehlercode', $this->_fehlercodes);
		$fehlerKurzbzRes = $this->FehlerModel->load();

		if (isError($fehlerKurzbzRes)) $this->terminateWithError(getError($fehlerKurzbzRes), self::ERROR_TYPE_GENERAL);

		$fehlerKurzbz = hasData($fehlerKurzbzRes) ? array_column(getData($fehlerKurzbzRes), 'fehler_kurzbz') : [];

		// load producer and checker libraries with fehler kurbz and fehlercode list
		$this->load->library(
			'issues/PlausicheckProducerLib',
			array(
				'fehlerKurzbz' => $fehlerKurzbz
			),
			'PlausicheckProducerLib'
		);

		$this->load->library(
			'issues/PlausicheckResolverLib',
			array(
				'fehlercodes' => $this->_fehlercodes
			),
			'PlausicheckResolverLib'
		);
	}

	public function checkPerson()
	{
		$person_id = $this->input->post('person_id', true);
		$hauptzustaendig = filter_var($this->input->post('hauptzustaendig', true), FILTER_VALIDATE_BOOLEAN);

		if (!is_numeric($person_id)) $this->terminateWithError($this->p->t('ui', 'error_invalidId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);

		$this->person_id = intval($person_id);
		$this->hauptzustaendig = $hauptzustaendig;

		$persRes = $this->PersonModel->load($this->person_id);
		if (!hasData($persRes)) $this->terminateWithError('Person with id ' . $this->person_id . ' not found.', self::ERROR_TYPE_GENERAL);

		$this->_produceIssues();
		$this->_resolveIssues();
		$this->_produceIssues();

		$openIssueCountRes = $this->_countOpenIssues();
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
		$hauptzustaendig = filter_var($this->input->get('hauptzustaendig', true), FILTER_VALIDATE_BOOLEAN);

		if (!is_numeric($person_id)) $this->terminateWithError($this->p->t('ui', 'error_invalidId', ['id'=> 'Person ID']), self::ERROR_TYPE_GENERAL);

		$this->person_id = intval($person_id);
		$this->hauptzustaendig = $hauptzustaendig;

		$persRes = $this->PersonModel->load($this->person_id);

		if (!hasData($persRes)) $this->terminateWithError('Person with id ' . $this->person_id . ' not found.', self::ERROR_TYPE_GENERAL);

		$openIssueCountRes = $this->_countOpenIssues();
		if (isError($openIssueCountRes)) $this->terminateWithError(getError($openIssueCountRes), self::ERROR_TYPE_GENERAL);

		$data = array(
			'person_id' => $this->person_id,
			'openissues' => hasData($openIssueCountRes) ? getData($openIssueCountRes) : 0
		);

		$this->addMeta('errors', $this->errors);
		$this->addMeta('infos', $this->infos);

		$this->terminateWithSuccess($data);
	}

	protected function _countOpenIssues()
	{
		if (isEmptyArray($this->_fehlercodes)) return success([]);

		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(
			$this->_fehlercodes,
			$this->person_id,
			$oe_kurzbz = null,
			$fehlercode_extern = null,
			$this->hauptzustaendig
		);

		// log error if occured
		if (isError($openIssuesRes)) return $openIssuesRes;

		$issues = hasData($openIssuesRes) ? getData($openIssuesRes) : [];
		$issuescount = is_array($issues) || $issues instanceof Countable ? count($issues) : 0;

		return success($issuescount);
	}

	protected function _produceIssues()
	{
		$result = $this->PlausicheckProducerLib->producePlausicheckIssues(
			array('person_id' => $this->person_id)
		);

		// log if error, or log info if inserted new issue
		if (isset($result->errors))
			$this->errors = array_merge($this->errors, $result->errors);
		if (isset($result->infos))
			$this->infos = array_merge($this->infos, $result->infos);
	}

	protected function _resolveIssues()
	{
		// load open issues with given errorcodes
		$openIssuesRes = $this->IssueModel->getOpenIssues(
			$this->_fehlercodes,
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
