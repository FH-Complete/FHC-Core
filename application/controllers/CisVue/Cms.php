<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class Cms extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
		    array(
			'content' => 'basis/cis:r',
			'getNews' => 'basis/cis:r',
			'getNewsRowCount' => 'basis/cis:r',
			'getRoomInformation' => 'basis/cis:r',
			'news' => 'basis/cis:r'
		    )
		);

		// Loads Libraries
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
		// return early if the content_id for the content is missing
		if (!isset($content_id))
			$this->terminateWithError("content_id is missing");

		$content = $this->ContentModel->load($content_id);
		if (isError($content))
			$this->terminateWithError(getError($content));

		$content = getData($content);
		if (NULL === $content)
			$this->terminateWithError("Content not found");

		$content = current($content);

		$viewData = array(
			'content_id' => $content_id,
			'template_kurzbz' => $content->template_kurzbz,
			'version' => $version,
			'sichtbar' => $sichtbar
		);
		
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'Content']);
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
		$viewData = array();
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData'=>$viewData, 'route' => 'News']);
	}
	
	public function getRoomInformation($ort_kurzbz)
	{
		// Load Config
		$this->load->config('calendar');

		$viewData = array(
			'ort_kurzbz' => $ort_kurzbz,
			'timezone' => $this->config->item('timezone')
		);
		$this->load->view('CisRouterView/CisRouterView.php', ['viewData' => $viewData, 'route' => 'CmsRoom']);
	}
}
