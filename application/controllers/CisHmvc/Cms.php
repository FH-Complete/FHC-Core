<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
		$content = $this->cmslib->getContent($content_id, $version, $sprache, $sichtbar);

		if (isError($content))
			return $this->load->view('CisHmvc/Error', ['error' => getError($content)]);

		$this->load->view('CisHmvc/Cms/Content', ['content' => getData($content)]);
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
		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, $edit, $sichtbar);

		if (isError($news))
			return $this->load->view('CisHmvc/Error', ['error' => getError($news)]);

		$this->load->view('CisHmvc/Cms/Content', ['content' => getData($news)]);
	}
}
