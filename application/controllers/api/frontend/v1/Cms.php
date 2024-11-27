<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * This controller operates between (interface) the JS (GUI) and the SearchBarLib (back-end)
 * Provides data to the ajax get calls about the searchbar component
 * This controller works with JSON calls on the HTTP GET and the output is always JSON
 */
class Cms extends FHCAPI_Controller
{
	
	/**
	 * Object initialization
	 */
	public function __construct()
	{
		// NOTE(chris): additional permission checks will be done in SearchBarLib
		parent::__construct([
			'ContentID' => self::PERM_LOGGED,
			'getOrtKurzbzContent' => self::PERM_LOGGED,
            'content' => self::PERM_LOGGED,
			'news' => self::PERM_LOGGED,
			'getNewsRowCount' => self::PERM_LOGGED,
			'getNews' => self::PERM_LOGGED,

		]);

		$this->load->model('content/News_model', 'NewsModel');

		// setting up the papgination_size
		$this->page_size = 10;

		$this->load->library('CmsLib');

		// Loads phrases system
		$this->loadPhrases([
			'global'
		]);

	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods


	//------------------------------------------------------------------------------------------------------------------
	// Public methods

    /**
	 * fetches the content with the content_id and additional parameters
	 */
    public function content()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id','Content ID','required|is_natural');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// getting the get parameters
        $content_id = $this->input->get("content_id",TRUE);
        $version = $this->input->get("version",TRUE);
        $sprache = $this->input->get("sprache",TRUE);
        $sichtbar = $this->input->get("sichtbar",TRUE);

		$content = $this->cmslib->getContent($content_id, $version, $sprache, $sichtbar);
		$content = $this->getDataOrTerminateWithError($content);

		$this->terminateWithSuccess($content);
	}

	/**
	 * Gets a JSON body via HTTP POST and provides the parameters
	 */
	public function ContentID()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('ort_kurzbz', 'Ort', 'required');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$ort_kurzbz = $this->input->get('ort_kurzbz',TRUE);
		
		$content_id = $this->OrtModel->getContentID($ort_kurzbz);

		$content_id = current($this->getDataOrTerminateWithError($content_id))->content_id;
		
		$this->terminateWithSuccess($content_id);
	}

	//todo: there is the method news and getNews but only one should exist
	public function news()
	{

		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('limit','Limit','required|is_natural_no_zero');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());
		
		$this->load->model('content/news_model', 'NewsModel');
		
		$limit =  $this->input->get('limit',TRUE);
		
		//query the news
		$news = $this->NewsModel->getAll($limit);

		//get the data or terminate with error
		$news = $this->getDataOrTerminateWithError($news);

		// collect the content of the news
		foreach($news as $news_element){
			$this->addMeta("content_id",$news_element->content_id);
			
			//todo: quick fix, for query builder error when fetching content
			$this->NewsModel->resetQuery();
			$content = $this->cmslib->getContent($news_element->content_id);

			$content = $this->getDataOrTerminateWithError($content);
			
			$news_element->content_obj = $content;
		}

		$this->terminateWithSuccess($news);
        
	}

	public function getNewsRowCount($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $fachbereich_kurzbz = null, $maxalter = 0, $edit = false, $sichtbar = true, $page = 1, $page_size = 10)
	{
		list($studiengang_kz, $semester) = $this->cmslib->getStgAndSem($studiengang_kz, $semester);
		$all = $edit;
		
		$this->load->model('content/News_model','NewsModel');

		$num_rows = $this->NewsModel->countNewsWithContent(getSprache(), $studiengang_kz, $semester, $fachbereich_kurzbz, $sichtbar, $maxalter, $page, $page_size, $all, $mischen);
		
		$num_rows = $this->getDataOrTerminateWithError($num_rows);
		
		$this->terminateWithSuccess($num_rows);
		
	}


	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true)
	{
		//form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('page','Page','required|is_natural');
		$this->form_validation->set_rules('page_size', 'PageSize', 'is_natural');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		// getting the GET parameters
		$page = intval($this->input->get('page', true));
		$page_size = intval($this->input->get('page_size', true));

		// default value for the page_size is 10
		$page_size = $page_size ?? 10;
		
		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, $edit, $sichtbar, $page, $page_size);

		$news = $this->getDataOrTerminateWithError($news);

		$this->addMeta('test', $this->p->t('global', 'studiengangsleitung'));
		$this->addMeta('phrases', json_decode($this->p->getJson()));
		$this->terminateWithSuccess($news);

	}

	
}

