<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Issues extends FHCAPI_Controller
{
	const BERECHTIGUNG_ISSUES = 'lehre/lvplan';
	const APP = 'tempus';

	private $_ci;

	public function __construct()
	{
		parent::__construct([
			'countOpenIssues' => self::BERECHTIGUNG_ISSUES . ':r',
			'getOpenIssues' => self::BERECHTIGUNG_ISSUES . ':r',
			'check' => self::BERECHTIGUNG_ISSUES . ':r',
			'akzeptieren' => self::BERECHTIGUNG_ISSUES . ':rw',
		]);

		$this->_ci =& get_instance();
		$this->_ci->load->model('system/Issue_model', 'IssueModel');
	}

	public function countOpenIssues()
	{
		$this->terminateWithSuccess(['openissues' => $this->_getCountIssues()]);
	}

	public function getOpenIssues()
	{
		$von = $this->input->get('von');
		$bis = $this->input->get('bis');

		$this->terminateWithSuccess($this->_getOffeneIssues($von, $bis));
	}

	public function check()
	{
		$von = $this->input->post('von');
		$bis = $this->input->post('bis');

		if (isEmptyString($von) || isEmptyString($bis))
			$this->terminateWithError('Von und Bis sind erforderlich');

		$errors = [];

		$offene = $this->_getOffeneIssues($von, $bis);
		if (!isEmptyArray($offene))
		{
			$this->_ci->load->library('issues/PlausicheckResolverLib', ['apps' => [self::APP]]);
			$result = $this->_ci->plausicheckresolverlib->resolvePlausicheckIssues($offene);
			$errors = array_merge($errors, $result->errors ?? []);
		}

		$this->_ci->load->library('issues/PlausicheckProducerLib', ['apps' => [self::APP]]);
		$result = $this->_ci->plausicheckproducerlib->producePlausicheckIssues(['von' => $von, 'bis' => $bis]);
		$errors = array_merge($errors, $result->errors ?? []);

		$this->addMeta('errors', $errors);

		$this->terminateWithSuccess(['openissues' => $this->_getOffeneIssues()]);
	}
	public function akzeptieren()
	{
		$issue_id = $this->input->post('issue_id');
		if (!is_numeric($issue_id))
			$this->terminateWithError('issue_id fehlt');

		$res = $this->_ci->IssueModel->loadWhere(array('issue_id' => $issue_id, 'verarbeitetamum' => NULL));

		if (isError($res))
			$this->terminateWithError(getError($res));
		if (!hasData($res))
			$this->terminateWithError('Issue nicht gefunden oder bereits verarbeitet');

		$issue = getData($res)[0];

		$oes = $this->permissionlib->getOE_isEntitledFor(self::BERECHTIGUNG_ISSUES);
		if (!is_array($oes) || !in_array($issue->oe_kurzbz, $oes))
			$this->terminateWithError('Keine Berechtigung für dieses Issue');

		$result = $this->_ci->IssueModel->update(
			[
				'fehlercode' => $issue->fehlercode,
				'behebung_parameter' => $issue->behebung_parameter,
				'verarbeitetamum' => null
			],
			[
				'status_kurzbz' => 'accepted',
				'verarbeitetvon' => getAuthUID(),
				'verarbeitetamum' => date('c'),
				'updatevon' => getAuthUID(),
				'updateamum' => date('c')
			]
		);

		if (isError($result)) $this->terminateWithError(getError($result));

		$this->terminateWithSuccess();
	}

	private function _getCountIssues()
	{
		$qry = "SELECT COUNT(*) as count ";

		list ($sql, $oes) = $this->_getSql();
		if (!is_array($oes) || isEmptyArray($oes))
			return [];
		$params = [self::APP, array_values(array_unique($oes))];
		$qry = $qry . $sql;

		$result = $this->_ci->IssueModel->execReadOnlyQuery($qry, $params);

		if (isError($result)) $this->terminateWithError(getError($result));

		return hasData($result) ? getData($result)[0]->count : [];
	}
	private function _getOffeneIssues($von = null, $bis = null)
	{
		$qry = "
			SELECT
				issue.issue_id,
				issue.person_id,
				UPPER(issue.oe_kurzbz) as oe_anzeige,
				issue.oe_kurzbz,
				issue.fehlercode,
				fehler.fehler_kurzbz,
				issue.inhalt,
				issue.datum,
				issue.status_kurzbz,
				issue.behebung_parameter,
				(issue.behebung_parameter->>'kalender_id_a')::integer AS kalender_id_a,
				(issue.behebung_parameter->>'kalender_id_b')::integer AS kalender_id_b,
				COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp) AS von
			";

		list ($sql, $oes) = $this->_getSql();
		if (!is_array($oes) || isEmptyArray($oes))
			return [];
		$params = [self::APP, array_values(array_unique($oes))];
		$qry .= $qry . $sql;

		if (!isEmptyString($von) && !isEmptyString($bis))
		{
			$qry .= " AND COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp) >= ?
			AND COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp) < ?::date + 1";
			$params[] = $von;
			$params[] = $bis;
		}

		$qry .= " ORDER BY COALESCE(termin_a.von, (issue.behebung_parameter->>'datum')::timestamp), issue.issue_id";

		$result = $this->_ci->IssueModel->execReadOnlyQuery($qry, $params);

		if (isError($result)) $this->terminateWithError(getError($result));

		return hasData($result) ? getData($result) : [];
	}

	private function _getSql()
	{
		$oes = $this->permissionlib->getOE_isEntitledFor(self::BERECHTIGUNG_ISSUES);

		$sql = " FROM system.tbl_issue issue
				JOIN system.tbl_fehler fehler ON fehler.fehlercode = issue.fehlercode
				JOIN system.tbl_fehler_app fehler_app ON fehler_app.fehlercode = issue.fehlercode AND fehler_app.app = ?
				LEFT JOIN lehre.tbl_kalender termin_a ON termin_a.kalender_id = (issue.behebung_parameter->>'kalender_id_a')::integer
				WHERE issue.verarbeitetamum IS NULL
				AND issue.oe_kurzbz IN ?";

		return array($sql, $oes);
	}
}