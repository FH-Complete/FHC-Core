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
			'getOePersonen' => self::PERM_LOGGED,
			'getDmsKategorie' => self::PERM_LOGGED,
			'getContentChilds' => self::PERM_LOGGED,
			'getPerson' => self::PERM_LOGGED,
			'getDmsDokumente' => self::PERM_LOGGED,

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
		// array that keeps track of which news don't have a betreff and have to be removed from the news array
		$newsToRemove = array();
		// collect the content of the news
		foreach($news as $index=>$news_element){
			
			$this->NewsModel->resetQuery();
			$content = $this->cmslib->getContent($news_element->content_id);
			if(isError($content))
			{
				// removes the news from the news array, so that the response does not include a invalid news
				array_push($newsToRemove,$index);
				//add the error to the api response? visual feedback
				//$this->addError(print_r($content->retval,true));
				continue;
			}
			$content = getData($content);		
			$news_element->content_obj = $content; 
		}

		//removes all news that don't have a betreff
		foreach($newsToRemove as $removeNewsIndex)
		{
			unset($news[$removeNewsIndex]);
		}

		$withContent = function($news) {
			return $news->content_obj != null;
		}; 
		$newsWithContent = array_filter($news, $withContent);
		$this->terminateWithSuccess($newsWithContent);
        
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
		$sprache = $this->input->get('sprache', true);
		if(!$sprache)
		{
			$sprache = getUserLanguage();
		}

		// default value for the page_size is 10
		$page_size = $page_size ?? 10;
		
		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, $edit, $sichtbar, $page, $page_size, $sprache);
		$news = $this->getDataOrTerminateWithError($news);

		$this->addMeta('phrases', json_decode($this->p->getJson()));
		$this->terminateWithSuccess($news);

	}

	

	/**
	 * Lists the people assigned to an organisation unit.
	 * Used by the CMS content component "oe-personen".
	 */
	public function getOePersonen()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('oe_kurzbz', 'Organisationseinheit', 'required');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		$oe_kurzbz = $this->input->get('oe_kurzbz', TRUE);
		$mitFoto = $this->input->get('foto', TRUE) ? true : false;

		// 'oezuordnung' is the function that assigns an employee to an organisation unit.
		// The model already filters datum_von and datum_bis.
		$rows = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('oezuordnung', $oe_kurzbz);
		$rows = $this->getDataOrTerminateWithError($rows) ?: array();

		// The model returns the aktiv flag but does not filter on it.
		$rows = array_values(array_filter($rows, function ($row) {
			return $row->aktiv;
		}));

		$uids = array();
		$funktionen = array();
		foreach ($rows as $row)
		{
			$uids[] = $row->uid;
			// tbl_benutzerfunktion.bezeichnung is the free label of one assignment, for
			// example "Bibliothekarin". tbl_funktion.beschreibung is the generic name.
			$funktionen[$row->uid] = $row->bezeichnung ?: $row->beschreibung;
		}

		$this->terminateWithSuccess($this->_cmsPersonen($uids, $funktionen, $mitFoto));
	}

	/**
	 * Contact data of one person.
	 * Used by the CMS content component "person-block".
	 */
	public function getPerson()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('uid', 'UID', 'required');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$uid = $this->input->get('uid', TRUE);
		$mitFoto = $this->input->get('foto', TRUE) ? true : false;

		// The function label comes from the marker, not from the database. The component
		// passes it through as a property.
		$personen = $this->_cmsPersonen(array($uid), array(), $mitFoto);

		$this->terminateWithSuccess($personen ? current($personen) : null);
	}

	/**
	 * Lists the published documents of one DMS category.
	 * Used by the CMS content component "dms-liste".
	 */
	public function getDmsKategorie()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('kategorie_kurzbz', 'DMS-Kategorie', 'required');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$kategorie_kurzbz = $this->input->get('kategorie_kurzbz', TRUE);

		$erlaubt = $this->_dmsKategorieErlaubt($kategorie_kurzbz);

		if ($erlaubt === null)
			$this->terminateWithError('DMS category not found', self::ERROR_TYPE_404);

		// a missing entitlement answers with success and an empty list, not with
		// an error. The component sits inside a public content page. An error would raise a
		// toast for every reader who is simply not in the group.
		$dokumente = array();
		if ($erlaubt)
		{
			$dokumente = $this->DmsModel->getKategorieDokumente($kategorie_kurzbz);
			$dokumente = $this->getDataOrTerminateWithError($dokumente) ?: array();
		}

		$this->terminateWithSuccess(array(
			'zugriff' => $erlaubt,
			'dokumente' => $dokumente
		));
	}

	/**
	 * Lists named documents, in the order the caller asked for them.
	 * Used by the CMS content component "dms-dokumente".
	 */
	public function getDmsDokumente()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('dms_ids', 'DMS-IDs', 'required');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->load->model('content/Dms_model', 'DmsModel');

		$ids = array();
		foreach (explode(',', $this->input->get('dms_ids', TRUE)) as $teil)
		{
			$teil = trim($teil);
			if (is_numeric($teil))
				$ids[] = (int) $teil;
		}

		// An unbounded list would let one marker fetch the whole DMS.
		$ids = array_slice(array_unique($ids), 0, 50);

		$dokumente = $this->DmsModel->getDokumenteByIds($ids);
		$dokumente = $this->getDataOrTerminateWithError($dokumente) ?: array();

		// A hand picked list holds few categories, so deciding per category is cheap and
		// reuses the rule of getDmsKategorie().
		$erlaubte = array();
		$nachId = array();
		foreach ($dokumente as $dokument)
		{
			if (!array_key_exists($dokument->kategorie_kurzbz, $erlaubte))
				$erlaubte[$dokument->kategorie_kurzbz] = $this->_dmsKategorieErlaubt($dokument->kategorie_kurzbz);

			if ($erlaubte[$dokument->kategorie_kurzbz] === true)
				$nachId[$dokument->dms_id] = $dokument;
		}

		// The editor chose the order. Keep it.
		$sortiert = array();
		foreach ($ids as $id)
		{
			if (isset($nachId[$id]))
				$sortiert[] = $nachId[$id];
		}

		$this->terminateWithSuccess($sortiert);
	}

	/**
	 * Lists the child pages of a content, as a reader may see them.
	 * Used by the CMS content component "contentchild-menu".
	 */
	public function getContentChilds()
	{
		// form validation
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('content_id', 'Content ID', 'required|is_natural_no_zero');
		if ($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$this->load->model('content/Contentchild_model', 'ContentchildModel');

		$content_id = $this->input->get('content_id', TRUE);
		$sprache = $this->input->get('sprache', TRUE);
		if (!$sprache)
			$sprache = getUserLanguage();

		$childs = $this->ContentchildModel->getChildsForReader($content_id, $sprache, getAuthUID());

		$this->terminateWithSuccess($this->getDataOrTerminateWithError($childs) ?: array());
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Builds the shape both person content components render.
	 *
	 * The assembly follows Cis/Cms/News/Xml/Address/Detailed.php, so a person reads the
	 * same in the news extras and on a content page.
	 *
	 * @param array $uids in the order the result should keep
	 * @param array $funktionen uid => function label, may be empty
	 * @param boolean $mitFoto
	 * @return array
	 */
	private function _cmsPersonen($uids, $funktionen, $mitFoto)
	{
		if (empty($uids))
			return array();

		$this->load->model('person/Person_model', 'PersonModel');

		$kontakt = $this->PersonModel->getCmsKontakt($uids, $mitFoto);
		$kontakt = $this->getDataOrTerminateWithError($kontakt) ?: array();

		$nachUid = array();
		foreach ($kontakt as $row)
			$nachUid[$row->uid] = $row;

		$personen = array();

		// Loops over $uids, not over the result, so the caller keeps its order.
		foreach ($uids as $uid)
		{
			if (!isset($nachUid[$uid]))
				continue;

			$row = $nachUid[$uid];

			$name = trim(implode(' ', array_filter(array(
				$row->titelpre, $row->vorname, $row->nachname, $row->titelpost
			))));

			$telefon = '';
			if ($row->telefonklappe)
				$telefon = trim(($row->kontakt ?: '') . ' - ' . $row->telefonklappe);

			$email = '';
			if (defined('DOMAIN'))
				$email = ($row->alias ?: $row->uid) . '@' . DOMAIN;

			$personen[] = array(
				'uid' => $row->uid,
				'name' => $name,
				'funktion' => isset($funktionen[$uid]) ? $funktionen[$uid] : '',
				'telefon' => $telefon,
				'email' => $email,
				'ort' => $row->planbezeichnung,
				'foto' => $row->foto
			);
		}

		return $personen;
	}

	/**
	 * Decides whether the current user may see a DMS category.
	 *
	 * Same rule as cms/dms.php: an unlocked category is open to everyone. A locked one
	 * needs a granted group, or the category permission together with basis/dms.
	 *
	 * @param string $kategorie_kurzbz
	 * @return boolean|null null when the category does not exist
	 */
	private function _dmsKategorieErlaubt($kategorie_kurzbz)
	{
		$this->load->library('PermissionLib');
		$this->load->model('content/Dms_model', 'DmsModel');

		$zugriff = $this->DmsModel->getKategorieZugriff($kategorie_kurzbz, getAuthUID());
		$zugriff = $this->getDataOrTerminateWithError($zugriff);

		if (empty($zugriff))
			return null;

		$kategorie = current($zugriff);

		if (!$kategorie->gesperrt || $kategorie->in_gruppe)
			return true;

		$erlaubt = $this->permissionlib->isBerechtigt('basis/dms');

		if ($erlaubt && $kategorie->berechtigung_kurzbz != '')
			$erlaubt = $this->permissionlib->isBerechtigt($kategorie->berechtigung_kurzbz);

		return $erlaubt;
	}

}

