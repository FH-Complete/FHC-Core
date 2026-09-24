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
			'getParents'       => ['basis/cms:r'],
			'getPossibleChilds' => ['basis/cms:r'],
			'postGruppe'       => ['basis/cms:rw'],
			'deleteGruppe'     => ['basis/cms:rw'],
			'postChild'        => ['basis/cms:rw'],
			'deleteChild'      => ['basis/cms:rw'],
			'putChildSort'     => ['basis/cms:rw'],
			'putChildOrder'    => ['basis/cms:rw']
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

	public function getParents()
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

		$result = $this->ContentchildModel->getParents($content_id, $sprache);
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
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

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
			$this->terminateWithError($this->p->t('cms', 'gruppeBereitsZugeordnet'));
		}

		$this->terminateWithSuccess(true);
	}

	public function deleteGruppe()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

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
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('child_content_id', 'Child Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->post('content_id');

		$sortResult = $this->ContentchildModel->getMaxSort($content_id);
		$sort = $this->getDataOrTerminateWithError($sortResult) + 1;
		
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
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

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
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('contentchild_id', 'Contentchild ID', 'required|is_natural');
		$this->form_validation->set_rules('direction', 'Direction', 'required|in_list[up,down]');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$direction = $this->input->post('direction');

		$result = $this->ContentchildModel->swapSort(
			$this->input->post('contentchild_id'),
			$direction
		);

		if (isError($result))
		{
			// The child already sits at the end it was moved towards. That is a normal
			// answer, not a failure, and the direction decides the wording.
			if (getError($result) === Contentchild_model::NO_NEIGHBOUR)
			{
				$this->terminateWithError($this->p->t('cms',
					$direction === 'up' ? 'bereitsGanzOben' : 'bereitsGanzUnten'));
			}

			$this->terminateWithError($this->p->t('cms', 'sortierungFehlgeschlagen'));
		}

		$this->terminateWithSuccess(true);
	}

	// Writes the whole order at once. The drag and drop moves a child over any distance,
	// and putChildSort would need one request per position.
	public function putChildOrder()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError($this->p->t('cms', 'keineBerechtigung'));

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$ids = $this->input->post('contentchild_ids');

		if (is_string($ids))
			$ids = json_decode($ids, true);

		if (!is_array($ids) || empty($ids))
			$this->terminateWithError($this->p->t('cms', 'sortierungFehlgeschlagen'));

		$result = $this->ContentchildModel->setSortOrder(
			$this->input->post('content_id'),
			$ids
		);

		if (isError($result))
			$this->terminateWithError($this->p->t('cms', 'sortierungFehlgeschlagen'));

		$this->terminateWithSuccess(true);
	}
}
