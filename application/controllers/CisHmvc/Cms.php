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
				'debug' => 'user:r',
				'legacy' => 'user:r',
				'content' => 'user:r',
				'news' => 'user:r'
			)
		);

		// Loads WidgetLib
		$this->load->library('CmsLib');
		#$this->load->library('WidgetLib');

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param stdClass	$content
	 *
	 * @return void
	 */
	public function debug($content)
	{
		$msg = $content->template_kurzbz . ' not yet implemented';
		if ($content->template_kurzbz == 'redirect') {
			$msg .= '<pre class="card p-1 mt-3">' . htmlentities($content->content) . '</pre>';
		}
		$this->load->view('CisHmvc/Error', ['error' => $msg]);
	}

	/**
	 * @param string	$url
	 *
	 * @return void
	 */
	public function legacy($url)
	{
		$this->load->view('CisHmvc/Cms/Legacy', ['url' => $url]);
	}

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

		$this->load->view('CisVue/Cms/Content', ['content_id' => $content_id, 'template_kurzbz'=>$content->template_kurzbz , 'version' => $version, 'sprache' => $sprache, 'sichtbar' => $sichtbar]);
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
		$this->load->view('CisHmvc/Cms/Content', ['infoscreen' => $infoscreen, 'studiengang_kz' => $studiengang_kz, 'semester' => $semester, 'mischen' => $mischen, 'titel' => $titel, 'edit' => $edit, 'sichtbar' => $sichtbar, "template_kurzbz"=>"news"]);
	}
}
