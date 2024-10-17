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
		// return early if the content_id for the content is missing
		if(!isset($content_id))
			$this->terminateWithError("content_id is missing");

		$content = $this->ContentModel->load($content_id);		
		if (isError($content))
			$this->terminateWithError(getError($content));	

		$content = getData($content);
		if(NULL === $content)
			$this->terminateWithError("Content not found");

		$content = current($content);

		$this->load->view('CisHtml/Cms/Content', ['content_id' => $content_id, 'template_kurzbz'=>$content->template_kurzbz , 'version' => $version, 'sprache' => $sprache, 'sichtbar' => $sichtbar]);
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
		$this->load->view('CisHtml/Cms/Content', ['infoscreen' => $infoscreen, 'studiengang_kz' => $studiengang_kz, 'semester' => $semester, 'mischen' => $mischen, 'titel' => $titel, 'edit' => $edit, 'sichtbar' => $sichtbar, "template_kurzbz"=>"news"]);
	}

}
