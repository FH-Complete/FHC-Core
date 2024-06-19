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
		
		$this->load->view('CisHtml/Cms/Content', ['content_id' => $content_id, 'version' => $version, 'sprache' => $sprache, 'sichtbar' => $sichtbar]);
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
		$this->load->view('CisHtml/Cms/Content', ['infoscreen' => $infoscreen, 'studiengang_kz' => $studiengang_kz, 'semester' => $semester, 'mischen' => $mischen, 'titel' => $titel, 'edit' => $edit, 'sichtbar' => $sichtbar]);
	}

	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true)
	{
		$get_page = intval($this->input->get('page', true));
		$get_page_size = intval($this->input->get('page_size', true));
		if ($get_page) {
			$page = $get_page;
		}
		if ($get_page_size) {
			$page_size = $get_page_size;
		} else {
			$page_size = $this->page_size;
		}
		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, $edit, $sichtbar, $page, $page_size);

		if (isError($news)) {
			$this->terminateWithJsonError(getError($news));
		}
		$news = hasData($news) ? getData($news) : null;
		if ($news) {
			echo json_encode($news);
		} else {
			show_error("News: No data found");
		}

	}

	public function getNewsRowCount($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $fachbereich_kurzbz = null, $maxalter = 0, $edit = false, $sichtbar = true, $page = 1, $page_size = 10)
	{
		list($studiengang_kz, $semester) = $this->cmslib->getStgAndSem($studiengang_kz, $semester);
		$all = $edit;
		$num_rows = $this->NewsModel->countNewsWithContent(getSprache(), $studiengang_kz, $semester, $fachbereich_kurzbz, $sichtbar, $maxalter, $page, $this->page_size, $all, $mischen);
		if (isError($num_rows)) {
			$this->terminateWithJsonError(getError($num_rows));
		}
		$num_rows = hasData($num_rows) ? getData($num_rows) : null;
		if ($num_rows) {
			echo json_encode($num_rows);
		} else {
			show_error("News number rows: No data found");
		}
	}
}
