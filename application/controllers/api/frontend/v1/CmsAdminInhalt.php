<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CmsAdminInhalt extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getFormSchema'      => ['basis/cms:r'],
			'getFormData'        => ['basis/cms:r'],
			'getLock'            => ['basis/cms:r'],
			'getVersions'        => ['basis/cms:r'],
			'putFormData'        => ['basis/cms:rw'],
			'postLock'           => ['basis/cms:rw'],
			'deleteLock'         => ['basis/cms:rw'],
			'deleteLockForced'   => ['basis/cms:rw']
		]);

		$this->load->library('CmsAdminLib');
		$this->load->library('XsdSchemaLib');
		$this->load->library('PermissionLib');
		$this->load->model('content/Contentsprache_model', 'ContentspracheModel');
		$this->load->model('content/Template_model', 'TemplateModel');

		$this->loadPhrases(['global', 'cms']);
	}

	public function getFormSchema()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('template_kurzbz', 'Template', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$template_kurzbz = $this->input->get('template_kurzbz', TRUE);

		$this->TemplateModel->resetQuery();
		$templateResult = $this->TemplateModel->load($template_kurzbz);
		$templateData = $this->getDataOrTerminateWithError($templateResult);
		if (empty($templateData))
			$this->terminateWithError('cms/vorlageNichtGefunden');

		$schemaResult = $this->xsdschemalib->parseSchema(
			$templateData[0]->xsd, $template_kurzbz
		);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($schemaResult));
	}

	public function getFormData()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);
		$version = $this->input->get('version', TRUE);

		$versionResult = $this->ContentspracheModel->getOne($content_id, $sprache, $version);
		$row = $this->getDataOrTerminateWithError($versionResult);

		$this->load->model('content/Content_model', 'ContentModel');
		$this->ContentModel->resetQuery();
		$contentResult = $this->ContentModel->load($content_id);
		$contentData = $this->getDataOrTerminateWithError($contentResult);
		if (empty($contentData))
			$this->terminateWithError('cms/contentNichtGefunden');

		$this->TemplateModel->resetQuery();
		$templateResult = $this->TemplateModel->load($contentData[0]->template_kurzbz);
		$templateData = $this->getDataOrTerminateWithError($templateResult);
		if (empty($templateData))
			$this->terminateWithError('cms/vorlageNichtGefunden');

		$schemaResult = $this->xsdschemalib->parseSchema(
			$templateData[0]->xsd, $contentData[0]->template_kurzbz
		);
		$schema = $this->getDataOrTerminateWithError($schemaResult);

		$valuesResult = $this->xsdschemalib->extractValues($row->content, $schema);
		$values = $this->getDataOrTerminateWithError($valuesResult);

		$lockResult = $this->cmsadminlib->getLockState($content_id, $sprache, $version);
		$lock = $this->getDataOrTerminateWithError($lockResult);

		$this->terminateWithSuccess([
			'schema' => $schema,
			'values' => $values,
			'sperre' => $lock
		]);
	}

	public function getLock()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);
		$version = $this->input->get('version', TRUE);

		$result = $this->cmsadminlib->getLockState($content_id, $sprache, $version);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getVersions()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);

		$result = $this->ContentspracheModel->getVersions($content_id, $sprache);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function putFormData()
	{
		// LEGACY-QUIRK: the legacy XSDFormPrinter_XML branch in admin.php checks neither the
		// permission type nor the lock. Kept as a functional copy. See Q3 in the contract.
		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->post('content_id');
		$sprache = $this->input->post('sprache');
		$version = $this->input->post('version');
		$values = $this->input->post('values');

		if (is_string($values))
			$values = json_decode($values, true);

		if (!is_array($values))
			$values = [];

		$result = $this->cmsadminlib->saveContentXml($content_id, $sprache, $version, $values);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function postLock()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigungFuerDieseAktion');

		$contentsprache_id = $this->input->post('contentsprache_id');

		$result = $this->cmsadminlib->lock($contentsprache_id);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function deleteLock()
	{
		// LEGACY-QUIRK: releaseOwnLocks releases all locks of the user. This method ignores
		// the given contentsprache_id on purpose. The parameter stays in the API contract, so
		// that a later fix does not change the interface. See Q1 in the contract.
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigungFuerDieseAktion');

		$result = $this->cmsadminlib->releaseOwnLocks();
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function deleteLockForced()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms_sperrfreigabe', 'su'))
			$this->terminateWithError('cms/keineBerechtigungFuerDieseAktion');

		$contentsprache_id = $this->input->post('contentsprache_id');

		$result = $this->cmsadminlib->forceRelease($contentsprache_id);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}
}
