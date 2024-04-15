<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class Cms extends FHC_Controller
{
	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct();

		// Loads Libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
		$this->load->library('CmsLib');

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param int		$content_id
	 * @param int		$version
	 * @param string	$sprache
	 * @param boolean	$sichtbar
	 *
	 * @return void
	 */
	public function content($content_id, $version = null, $sprache = null, $sichtbar = true)
	{
		$content = $this->cmslib->getContent($content_id, $version, $sprache, $sichtbar);

		if (isError($content))
			return $this->load->view('CisHtml/Error', ['error' => getError($content)]);

		$this->load->view('CisHtml/Cms/Content', ['content' => getData($content)]);
	}

	/**
	 * @param boolean			$infoscreen
	 * @param string | null		$studiengang_kz
	 * @param int | null		$semester
	 * @param boolean			$mischen
	 * @param string			$titel
	 * @param boolean			$edit
	 * @param boolean			$sichtbar
	 *
	 * @return void
	 */
	public function news($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true)
	{
		$this->load->view('CisHtml/Cms/Content', ['infoscreen' => $infoscreen, 'studiengang_kz' => $studiengang_kz, 'semester' => $semester, 'mischen' => $mischen, 'titel' => $titel, 'edit' => $edit, 'sichtbar' => $sichtbar]);
	}

	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true)
	{
		$page = intval($this->input->get('page', true));
		$pagination_size = 10;
		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, $edit, $sichtbar, $page, $pagination_size);

		if (isError($news)) {
			echo json_encode(getError($news));
		}
		echo json_encode(getData($news));
	}
}
