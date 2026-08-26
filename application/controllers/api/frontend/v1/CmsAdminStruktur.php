<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CmsAdminStruktur extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getGruppen'        => ['basis/cms:r'],
			'getAllGruppen'     => ['basis/cms:r'],
			'getChilds'        => ['basis/cms:r'],
			'getPossibleChilds' => ['basis/cms:r'],
			'postGruppe'       => ['basis/cms:rw'],
			'deleteGruppe'     => ['basis/cms:rw'],
			'postChild'        => ['basis/cms:rw'],
			'deleteChild'      => ['basis/cms:rw'],
			'putChildSort'     => ['basis/cms:rw']
		]);

		$this->load->library('PermissionLib');
		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->model('content/Contentchild_model', 'ContentchildModel');
		$this->load->model('content/Contentgruppe_model', 'ContentgruppeModel');

		$this->loadPhrases(['global', 'cms']);
	}

	public function getGruppen()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);

		$result = $this->ContentgruppeModel->getGruppen($content_id);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getAllGruppen()
	{
		$result = $this->ContentgruppeModel->getAllGruppen();
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getChilds()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);

		if (empty($sprache))
			$sprache = DEFAULT_LANGUAGE;

		$result = $this->ContentchildModel->getChilds($content_id, $sprache);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getPossibleChilds()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);

		if (empty($sprache))
			$sprache = DEFAULT_LANGUAGE;

		$result = $this->ContentModel->getPossibleChilds($content_id, $sprache);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function postGruppe()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('gruppe_kurzbz', 'Gruppe', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->ContentgruppeModel->insert([
			'content_id'    => $this->input->post('content_id'),
			'gruppe_kurzbz' => $this->input->post('gruppe_kurzbz'),
			'insertamum'    => date('Y-m-d H:i:s'),
			'insertvon'     => getAuthUID()
		]);

		if (isError($result))
		{
			$this->terminateWithError('cms/gruppeBereitsZugeordnet');
		}

		$this->terminateWithSuccess(true);
	}

	public function deleteGruppe()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('gruppe_kurzbz', 'Gruppe', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->ContentgruppeModel->delete([
			$this->input->post('gruppe_kurzbz'),
			$this->input->post('content_id')
		]);
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}

	public function postChild()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('child_content_id', 'Child Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->post('content_id');

		$sortResult = $this->ContentchildModel->getMaxSort($content_id);
		$sort = $this->getDataOrTerminateWithError($sortResult) + 1;

		// LEGACY-QUIRK: tbl_contentchild has no unique index on (content_id,
		// child_content_id). The legacy code does not check for a duplicate, so a child can be
		// attached more than once. Kept as a functional copy. See section 3 of the contract.
		$result = $this->ContentchildModel->insert([
			'content_id'       => $content_id,
			'child_content_id' => $this->input->post('child_content_id'),
			'sort'             => $sort,
			'insertamum'       => date('Y-m-d H:i:s'),
			'insertvon'        => getAuthUID()
		]);
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}

	public function deleteChild()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('contentchild_id', 'Contentchild ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->ContentchildModel->delete($this->input->post('contentchild_id'));
		$this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess(true);
	}

	public function putChildSort()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('contentchild_id', 'Contentchild ID', 'required|is_natural');
		$this->form_validation->set_rules('direction', 'Direction', 'required|in_list[up,down]');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->ContentchildModel->swapSort(
			$this->input->post('contentchild_id'),
			$this->input->post('direction')
		);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}
}
