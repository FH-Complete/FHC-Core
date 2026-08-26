<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class CmsAdmin extends FHCAPI_Controller
{
	const MAX_TREE_DEPTH = 50;

	public function __construct()
	{
		parent::__construct([
			'getTree'                   => ['basis/cms:r'],
			'getContent'                => ['basis/cms:r'],
			'getContentsprache'         => ['basis/cms:r'],
			'getTemplates'              => ['basis/cms:r'],
			'getOrganisationseinheiten' => ['basis/cms:r'],
			'getSprachen'               => ['basis/cms:r'],
			'getUsage'                  => ['basis/cms:r'],
			'postContent'               => ['basis/cms:rw'],
			'postTranslation'           => ['basis/cms:rw'],
			'postVersion'               => ['basis/cms:rw'],
			'putProperties'             => ['basis/cms:rw'],
			'deleteContent'             => ['basis/cms:rw'],
			'deleteContentsprache'      => ['basis/cms:rw']
		]);

		$this->load->library('CmsAdminLib');
		$this->load->library('PermissionLib');
		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->model('content/Contentsprache_model', 'ContentspracheModel');
		$this->load->model('content/Contentchild_model', 'ContentchildModel');
		$this->load->model('content/Contentgruppe_model', 'ContentgruppeModel');
		$this->load->model('content/Template_model', 'TemplateModel');

		$this->loadPhrases(['global', 'cms']);
	}

	public function getTree()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('menu', 'Menu', 'required|in_list[content,news]');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$menu = $this->input->get('menu', TRUE);
		$filter = $this->input->get('filter', TRUE);

		$entitledResult = $this->cmsadminlib->getEntitledOe();
		$entitledOe = isError($entitledResult) ? [] : getData($entitledResult);

		$nodes = [];

		if ($menu === 'news')
		{
			$newsResult = $this->ContentModel->getNewsContent();
			if (!isError($newsResult) && getData($newsResult))
			{
				foreach (getData($newsResult) as $row)
				{
					$titel = '';
					$csResult = $this->ContentspracheModel->getOne(
						(int) $row->content_id, DEFAULT_LANGUAGE
					);
					if (!isError($csResult))
						$titel = getData($csResult)->titel;

					$node = new stdClass();
					$node->content_id = (int) $row->content_id;
					$node->titel = mb_substr($titel, 0, 15) . ' '
						. date('d.m.Y', strtotime($row->insertamum));
					$node->template_kurzbz = $row->template_kurzbz;
					$node->oe_kurzbz = $row->oe_kurzbz;
					$node->aktiv = ($row->aktiv === 't' || $row->aktiv === true);
					$node->entitled = false;
					$node->groups = [];
					$node->children = [];
					$nodes[] = $node;
				}
			}
		}
		else
		{
			if (!empty($filter))
			{
				$clean = str_replace(['!', '.', '?', ','], '', $filter);
				$searchItems = preg_split('/\s+/', $clean, -1, PREG_SPLIT_NO_EMPTY);
				$rootsResult = $this->ContentModel->searchCms($searchItems);
			}
			else
			{
				$rootsResult = $this->ContentModel->getRootContent();
			}

			if (!isError($rootsResult) && getData($rootsResult))
			{
				$visited = [];
				foreach (getData($rootsResult) as $row)
				{
					$node = $this->buildTreeNode(
						(int) $row->content_id, 0, $visited
					);
					if ($node !== null)
						$nodes[] = $node;
				}
			}
		}

		$allIds = [];
		$this->collectContentIds($nodes, $allIds);

		$groupMap = [];
		if (!empty($allIds))
		{
			$gResult = $this->ContentgruppeModel->getGruppenForContents($allIds);
			if (!isError($gResult))
				$groupMap = getData($gResult);
		}

		$this->applyMeta($nodes, $entitledOe, $groupMap);

		$result = [];
		foreach ($nodes as $node)
		{
			$pruned = $this->pruneUnentitled($node);
			if ($pruned !== null)
				$result[] = $pruned;
		}

		$this->terminateWithSuccess($result);
	}

	public function getContent()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);

		$entitledResult = $this->cmsadminlib->isEntitledForContent($content_id);
		$entitled = $this->getDataOrTerminateWithError($entitledResult);

		if (!$entitled)
			$this->terminateWithError('cms/keineBerechtigungFuerDiesenEintrag');

		$this->ContentModel->resetQuery();
		$contentResult = $this->ContentModel->load($content_id);
		$contentData = $this->getDataOrTerminateWithError($contentResult);
		if (empty($contentData))
			$this->terminateWithError('cms/contentNichtGefunden');
		$content = $contentData[0];

		$langResult = $this->ContentspracheModel->getLanguages($content_id);
		$languages = $this->getDataOrTerminateWithError($langResult);

		$versions = new stdClass();
		foreach ($languages as $sprache)
		{
			$vResult = $this->ContentspracheModel->getVersions($content_id, $sprache);
			if (!isError($vResult) && getData($vResult))
			{
				$versions->$sprache = array_map(function ($v) {
					return (int) $v->version;
				}, getData($vResult));
			}
		}

		$result = new stdClass();
		$result->content_id = (int) $content->content_id;
		$result->template_kurzbz = $content->template_kurzbz;
		$result->oe_kurzbz = $content->oe_kurzbz;
		$result->aktiv = ($content->aktiv === 't' || $content->aktiv === true);
		$result->menu_open = ($content->menu_open === 't' || $content->menu_open === true);
		$result->beschreibung = $content->beschreibung ?: '';
		$result->entitled = true;
		$result->languages = $languages;
		$result->versions = $versions;

		$this->terminateWithSuccess($result);
	}

	public function getContentsprache()
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

		$result = $this->ContentspracheModel->getOne($content_id, $sprache, $version);
		if (isError($result))
			$this->terminateWithError('cms/versionNichtGefunden');

		$data = getData($result);
		$data->sichtbar = ($data->sichtbar === 't' || $data->sichtbar === true);

		$this->terminateWithSuccess($data);
	}

	public function getTemplates()
	{
		$this->TemplateModel->addSelect('template_kurzbz, bezeichnung');
		$this->TemplateModel->addOrder('bezeichnung');
		$result = $this->TemplateModel->loadWhere([]);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getOrganisationseinheiten()
	{
		$this->load->model(
			'organisation/Organisationseinheit_model', 'OrganisationseinheitModel'
		);
		$this->OrganisationseinheitModel->addSelect(
			'oe_kurzbz, bezeichnung, organisationseinheittyp_kurzbz, aktiv'
		);
		$this->OrganisationseinheitModel->addOrder('bezeichnung');
		$result = $this->OrganisationseinheitModel->loadWhere([]);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getSprachen()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->SpracheModel->addSelect('sprache, bezeichnung');
		$result = $this->SpracheModel->loadWhere([]);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function getUsage()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$content_id = $this->input->get('content_id', TRUE);
		$result = $this->ContentModel->getUsage($content_id);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function postContent()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'i'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('parent_content_id', 'Parent', 'is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$parent = $this->input->post('parent_content_id');
		if (empty($parent))
			$parent = null;

		$result = $this->cmsadminlib->createContent($parent);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function postTranslation()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'i'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		$this->form_validation->set_rules('target_sprache', 'Target Sprache', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->cmsadminlib->createTranslation(
			$this->input->post('content_id'),
			$this->input->post('sprache'),
			$this->input->post('version'),
			$this->input->post('target_sprache')
		);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function postVersion()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'i'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->cmsadminlib->createVersion(
			$this->input->post('content_id'),
			$this->input->post('sprache')
		);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function putProperties()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'u'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		$this->form_validation->set_rules('template_kurzbz', 'Template', 'required');
		$this->form_validation->set_rules('oe_kurzbz', 'OE', 'required');
		$this->form_validation->set_rules('titel', 'Titel', 'required|max_length[256]');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$daten = [
			'content_id'      => $this->input->post('content_id'),
			'sprache'         => $this->input->post('sprache'),
			'version'         => $this->input->post('version'),
			'template_kurzbz' => $this->input->post('template_kurzbz'),
			'oe_kurzbz'       => $this->input->post('oe_kurzbz'),
			'titel'           => $this->input->post('titel'),
			'beschreibung'    => $this->input->post('beschreibung') ?: '',
			'aktiv'           => filter_var($this->input->post('aktiv'), FILTER_VALIDATE_BOOLEAN),
			'menu_open'       => filter_var($this->input->post('menu_open'), FILTER_VALIDATE_BOOLEAN),
			'sichtbar'        => filter_var($this->input->post('sichtbar'), FILTER_VALIDATE_BOOLEAN)
		];

		$result = $this->cmsadminlib->saveProperties($daten);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function deleteContent()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'd'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());

		$result = $this->cmsadminlib->deleteContent($this->input->post('content_id'));
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	public function deleteContentsprache()
	{
		if (!$this->permissionlib->isBerechtigt('basis/cms', 'd'))
			$this->terminateWithError('cms/keineBerechtigung');

		$this->validateContentSpracheVersion();

		$result = $this->cmsadminlib->deleteVersion(
			$this->input->post('content_id'),
			$this->input->post('sprache'),
			$this->input->post('version')
		);
		$this->terminateWithSuccess($this->getDataOrTerminateWithError($result));
	}

	// --- Private helpers ---

	private function validateContentSpracheVersion()
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_POST);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural');
		$this->form_validation->set_rules('sprache', 'Sprache', 'required');
		$this->form_validation->set_rules('version', 'Version', 'required|is_natural');
		if ($this->form_validation->run() == FALSE)
			$this->terminateWithValidationErrors($this->form_validation->error_array());
	}

	private function buildTreeNode($content_id, $depth, &$visited)
	{
		if ($depth > self::MAX_TREE_DEPTH || isset($visited[$content_id]))
			return null;

		$visited[$content_id] = true;

		$this->ContentModel->resetQuery();
		$contentResult = $this->ContentModel->load($content_id);
		if (isError($contentResult) || empty(getData($contentResult)))
			return null;
		$content = getData($contentResult)[0];

		$titel = '';
		$csResult = $this->ContentspracheModel->getOne($content_id, DEFAULT_LANGUAGE);
		if (!isError($csResult))
			$titel = getData($csResult)->titel;

		$childrenResult = $this->ContentchildModel->getChilds($content_id, DEFAULT_LANGUAGE);
		$children = [];
		if (!isError($childrenResult) && getData($childrenResult))
		{
			foreach (getData($childrenResult) as $child)
			{
				$childNode = $this->buildTreeNode(
					(int) $child->child_content_id, $depth + 1, $visited
				);
				if ($childNode !== null)
					$children[] = $childNode;
			}
		}

		$node = new stdClass();
		$node->content_id = (int) $content_id;
		$node->titel = $titel;
		$node->template_kurzbz = $content->template_kurzbz;
		$node->oe_kurzbz = $content->oe_kurzbz;
		$node->aktiv = ($content->aktiv === 't' || $content->aktiv === true);
		$node->entitled = false;
		$node->groups = [];
		$node->children = $children;

		return $node;
	}

	private function collectContentIds($nodes, &$ids)
	{
		foreach ($nodes as $node)
		{
			$ids[] = $node->content_id;
			if (!empty($node->children))
				$this->collectContentIds($node->children, $ids);
		}
	}

	private function applyMeta(&$nodes, $entitledOe, $groupMap)
	{
		foreach ($nodes as $node)
		{
			$node->entitled = in_array($node->oe_kurzbz, $entitledOe);
			$node->groups = isset($groupMap[$node->content_id])
				? $groupMap[$node->content_id]
				: [];
			if (!empty($node->children))
				$this->applyMeta($node->children, $entitledOe, $groupMap);
		}
	}

	private function pruneUnentitled($node)
	{
		$kept = [];
		foreach ($node->children as $child)
		{
			$pruned = $this->pruneUnentitled($child);
			if ($pruned !== null)
				$kept[] = $pruned;
		}
		$node->children = $kept;

		if ($node->entitled || !empty($node->children))
			return $node;

		return null;
	}
}
