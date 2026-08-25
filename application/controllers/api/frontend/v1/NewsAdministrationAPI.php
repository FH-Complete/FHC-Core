<?php
/**
 * Copyright (C) 2026 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

class NewsAdministrationAPI extends FHCAPI_Controller
{
	/**
	 * News API constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'getNews' => 'basis/news:r',
			'getNewsItem' => 'basis/news:r',
			'storeNewsItem' => 'basis/news:w',
			'updateNewsItem' => 'basis/news:w',
			'deleteNewsItem' => 'basis/news:w'
		]);

		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->model('content/Contentsprache_model', 'ContentspracheModel');
		$this->load->model('content/News_model', 'NewsModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		$this->loadPhrases([
			'global'
		]);

		$this->load->library('CmsLib');

		$this->load->helper('hlp_sancho_helper');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $active = true, $sichtbar = true)
	{
		$this->load->library('form_validation');
		$this->form_validation->set_data($_GET);
		$this->form_validation->set_rules('page','Page','required|is_natural');
		$this->form_validation->set_rules('page_size', 'PageSize', 'is_natural');
		if($this->form_validation->run() == FALSE) $this->terminateWithValidationErrors($this->form_validation->error_array());

		$page = intval($this->input->get('page', true));
		$page_size = intval($this->input->get('page_size', true));
		$sprache = $this->input->get('sprache', true);
		if(!$sprache)
		{
			$sprache = getUserLanguage();
		}

		// default value for the page_size is 10
		$page_size = $page_size ?? 10;

		$passedSichtbar = $this->input->get('published', true);
		if($passedSichtbar !== null)
		{
			$sichtbar = $passedSichtbar;
		}
		$this->addMeta('published', $passedSichtbar);

		$passedIsActive = $this->input->get('isActive', true);
		if ($passedIsActive !== null)
		{
			$isActive = filter_var(
				$passedIsActive,
				FILTER_VALIDATE_BOOLEAN,
				FILTER_NULL_ON_FAILURE
			);
			if ($isActive !== null)
			{
				$active = $isActive;
			}
		}
		$this->addMeta('isActive', $active);

		$degreeProgramShortCode = $this->input->get('degreeProgramShortCode', true);
		if ($degreeProgramShortCode !== null && $degreeProgramShortCode !== '')
		{
			if (!ctype_digit((string)$degreeProgramShortCode))
			{
				$this->terminateWithValidationErrors([
					'degreeProgramShortCode' => 'A valid degree program is required'
				]);
			}
			$studiengang_kz = $degreeProgramShortCode;
		}
		$this->addMeta('degreeProgramShortCode', $studiengang_kz);

		$passedSemester = $this->input->get('semester', true);
		if ($passedSemester !== null && $passedSemester !== '')
		{
			if (
				!ctype_digit((string)$passedSemester)
				|| (int)$passedSemester < 1
				|| (int)$passedSemester > 8
			)
			{
				$this->terminateWithValidationErrors([
					'semester' => 'A valid semester is required'
				]);
			}
			$semester = filter_var($passedSemester, FILTER_VALIDATE_INT);
		}
		$this->addMeta('semester', $semester);

		$allowedDegreePrograms = $this->permissionlib->getSTG_isEntitledFor("basis/news");

		$edit = true;
		$maxAlter = MAXNEWSALTER;
		if (!$active) {
			$maxAlter = null;
		}

		log_message('error', 'getNews: edit: ' . ($edit ? "true" : "false") . ', maxAlter: ' . ($maxAlter ?? "null"));

		$news = $this->cmslib->getNews($infoscreen, $studiengang_kz, $semester, $mischen, $titel, true, $sichtbar, $page, $page_size, $sprache, $maxAlter, true, $allowedDegreePrograms, $isActive);
		$news = $this->getDataOrTerminateWithError($news);

		$this->addMeta('row_count', $news["full_count"] ?? 0);

		$this->terminateWithSuccess($news["content"]);
	}

	/**
	 * Returns one news entry with all available language versions for editing.
	 *
	 * @return void
	 */
	public function getNewsItem($newsId)
	{
		if (!is_numeric($newsId) || (int)$newsId < 1)
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'A valid news ID is required'
			]);
		}
		
		$newsId = (int)$newsId;
		$newsResult = $this->NewsModel->load($newsId);

		if (isError($newsResult))
		{
			$this->terminateWithError(getError($newsResult), self::ERROR_TYPE_DB);
		}

		if (!hasData($newsResult))
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'The requested news entry does not exist'
			]);
		}

		$news = current(getData($newsResult));
		$allowedDegreePrograms = $this->permissionlib->getSTG_isEntitledFor("basis/news");
		
		if (!in_array($news->studiengang_kz, $allowedDegreePrograms)) {
			$this->terminateWithError('You are not entitled to access this news entry', self::ERROR_TYPE_PERMISSION);
		}
		$translationsResult = $this->ContentspracheModel->loadWhere([
			'content_id' => $news->content_id
		]);

		if (isError($translationsResult))
		{
			$this->terminateWithError(getError($translationsResult), self::ERROR_TYPE_DB);
		}

		$languages = [];

		foreach ((array)getData($translationsResult) as $translation)
		{
			$languages[$translation->sprache] = true;
		}

		$translations = [];

		foreach (array_keys($languages) as $language)
		{
			$contentResult = $this->ContentModel->getContent(
				$news->content_id,
				$language,
				null,
				null,
				false
			);

			if (isError($contentResult))
			{
				$this->terminateWithError(getError($contentResult), self::ERROR_TYPE_DB);
			}

			$content = getData($contentResult);
			$document = new DOMDocument('1.0', 'UTF-8');

			if ($content->content !== '' && $document->loadXML($content->content) === false)
			{
				$this->terminateWithError(
					'The stored news content is invalid',
					self::ERROR_TYPE_GENERAL
				);
			}

			$author = $document->getElementsByTagName('verfasser')->item(0);
			$title = $document->getElementsByTagName('betreff')->item(0);
			$text = $document->getElementsByTagName('text')->item(0);

			$translations[] = [
				'language' => $language,
				'author' => $author ? $author->nodeValue : '',
				'title' => $title ? $title->nodeValue : '',
				'text' => $text ? $text->nodeValue : '',
				'isPublished' => (bool)$content->sichtbar
			];
		}

		$this->terminateWithSuccess([
			'newsId' => (int)$news->news_id,
			'visibleFrom' => $news->datum,
			'visibleTo' => $news->datum_bis,
			'degreeProgramShortCode' => $news->studiengang_kz,
			'semester' => $news->semester,
			'translations' => $translations
		]);
	}

	/**
	 * Creates a news entry and all supplied translations.
	 *
	 * @return void
	 */
	public function storeNewsItem()
	{
		$this->load->library('form_validation');
		$postData = $this->input->post(null, true);
		$this->form_validation->set_data(is_array($postData) ? $postData : []);
		$this->form_validation->set_rules('visibleFrom', 'Visible from', 'required|is_valid_date');
		$this->form_validation->set_rules('visibleTo', 'Visible to', 'is_valid_date');
		$this->form_validation->set_rules(
			'degreeProgramShortCode',
			'Degree program',
			'required|is_natural'
		);
		$this->form_validation->set_rules(
			'semester',
			'Semester',
			'is_natural_no_zero|less_than_equal_to[8]'
		);

		$errors = $this->form_validation->run() === false
			? $this->form_validation->error_array()
			: [];
		$visibleFrom = $this->input->post('visibleFrom', true);
		$visibleTo = $this->input->post('visibleTo', true);
		$translations = $this->input->post('translations', true);

		if (
			is_string($visibleFrom)
			&& is_string($visibleTo)
			&& $visibleTo !== ''
			&& is_valid_date($visibleFrom)
			&& is_valid_date($visibleTo)
			&& new DateTime($visibleTo) < new DateTime($visibleFrom)
		)
		{
			$errors['visibleTo'] = 'Visible to must not be before Visible from';
		}

		if (!is_array($translations) || count($translations) === 0)
		{
			$errors['translations'] = 'At least one translation is required';
		}
		else
		{
			foreach ($translations as $index => $translation)
			{
				if (!is_array($translation))
				{
					$errors['translations.' . $index] = 'Each translation must be an object';
				}
			}
		}

		if (count($errors) > 0)
		{
			$this->terminateWithValidationErrors($errors);
		}


		$this->_ensureUserIsEntitledForDegreeProgram($this->input->post('degreeProgramShortCode', true));

		$degreeProgramShortCode = $this->input->post('degreeProgramShortCode', true);
		$semester = $this->input->post('semester', true);
		$semester = $semester === '' ? null : $semester;
		$visibleTo = $visibleTo === '' ? null : $visibleTo;

		$studiengang = $this->_getStudiengang($degreeProgramShortCode);

		$data = [
			'studiengang_kz' => $degreeProgramShortCode,
			'semester' => $semester,
			'datum' => $visibleFrom,
			'datum_bis' => $visibleTo,
			'translations' => []
		];

		foreach ($translations as $index => $translation)
		{
			$data['translations'][$index] = [
				'sprache' => $translation['language'] ?? '',
				'verfasser' => $translation['author'] ?? '',
				'betreff' => $translation['title'] ?? '',
				'text' => $translation['text'] ?? '',
				'sichtbar' => isset($translation['isPublished']) ? (bool)$translation['isPublished'] : false
			];
		}

		$this->_validateLanguages($data['translations']);

		foreach ($data['translations'] as $index => $translation)
		{
			$data['translations'][$index]['content'] = $this->_buildNewsXml($translation);
		}

		$uid = getAuthUID();
		$now = date('Y-m-d H:i:s');

		if ($this->db->trans_begin() === false)
		{
			$this->terminateWithError($this->db->error(), self::ERROR_TYPE_DB);
		}

		$contentResult = $this->ContentModel->insert([
			'template_kurzbz' => 'news',
			'oe_kurzbz' => $studiengang->oe_kurzbz,
			'aktiv' => true,
			'menu_open' => false,
			'insertamum' => $now,
			'insertvon' => $uid
		]);
		$contentId = (int)$this->_getInsertedIdOrRollback($contentResult);

		$newsResult = $this->NewsModel->insert([
			'uid' => $uid,
			'studiengang_kz' => $data['studiengang_kz'],
			'semester' => $data['semester'],
			'datum' => $data['datum'],
			'datum_bis' => $data['datum_bis'],
			'content_id' => $contentId,
			'insertamum' => $now,
			'insertvon' => $uid,
			'updateamum' => $now,
			'updatevon' => $uid
		]);
		$newsId = (int)$this->_getInsertedIdOrRollback($newsResult);

		foreach ($data['translations'] as $translation)
		{
			$translation['content'] = $this->formatContentURLsToRelative($translation['content']);

			$translationResult = $this->ContentspracheModel->insert([
				'sprache' => $translation['sprache'],
				'content_id' => $contentId,
				'version' => 1,
				'sichtbar' => $translation['sichtbar'],
				'content' => $translation['content'],
				'titel' => $translation['betreff'],
				'insertamum' => $now,
				'insertvon' => $uid,
				'updateamum' => $now,
				'updatevon' => $uid
			]);

			$this->_getInsertedIdOrRollback($translationResult);
		}

		$newsItemResult = $this->NewsModel->load($newsId);
		$newsItem = $this->getDataOrTerminateWithError($newsItemResult);
		$newsItem = $newsItem[0];

		$this->_sendNewsItemTranslationRequiredMail(
			$newsItem
		);

		$this->addMeta("newsItem", json_encode($newsItem));
		if ($this->db->trans_status() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		if ($this->db->trans_commit() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}
		
		$this->terminateWithSuccess($newsId);
	}

	/**
	 * Updates a news entry and its supplied translations.
	 *
	 * @return void
	 */
	public function updateNewsItem($newsId)
	{
		if (!is_numeric($newsId) || (int)$newsId < 1)
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'A valid news ID is required'
			]);
		}

		$newsId = (int)$newsId;
		$existingNewsResult = $this->NewsModel->load($newsId);

		if (isError($existingNewsResult))
		{
			$this->terminateWithError(getError($existingNewsResult), self::ERROR_TYPE_DB);
		}

		if (!hasData($existingNewsResult))
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'The requested news entry does not exist'
			]);
		}

		$existingNews = current(getData($existingNewsResult));

		$this->_ensureUserIsEntitledForDegreeProgram($existingNews->studiengang_kz);

		$this->load->library('form_validation');
		$postData = $this->input->post(null, true);
		$this->form_validation->set_data(is_array($postData) ? $postData : []);
		$this->form_validation->set_rules('visibleFrom', 'Visible from', 'required|is_valid_date');
		$this->form_validation->set_rules('visibleTo', 'Visible to', 'is_valid_date');
		$this->form_validation->set_rules(
			'degreeProgramShortCode',
			'Degree program',
			'required|is_natural'
		);
		$this->form_validation->set_rules(
			'semester',
			'Semester',
			'is_natural_no_zero|less_than_equal_to[8]'
		);

		$errors = $this->form_validation->run() === false
			? $this->form_validation->error_array()
			: [];
		$visibleFrom = $this->input->post('visibleFrom', true);
		$visibleTo = $this->input->post('visibleTo', true);
		$translations = $this->input->post('translations', true);

		if (
			is_string($visibleFrom)
			&& is_string($visibleTo)
			&& $visibleTo !== ''
			&& is_valid_date($visibleFrom)
			&& is_valid_date($visibleTo)
			&& new DateTime($visibleTo) < new DateTime($visibleFrom)
		)
		{
			$errors['visibleTo'] = 'Visible to must not be before Visible from';
		}

		if (!is_array($translations) || count($translations) === 0)
		{
			$errors['translations'] = 'At least one translation is required';
		}
		else
		{
			foreach ($translations as $index => $translation)
			{
				if (!is_array($translation))
				{
					$errors['translations.' . $index] = 'Each translation must be an object';
				}
			}
		}

		if (count($errors) > 0)
		{
			$this->terminateWithValidationErrors($errors);
		}

		$degreeProgramShortCode = $this->input->post('degreeProgramShortCode', true);
		$semester = $this->input->post('semester', true);
		$semester = $semester === '' ? null : $semester;
		$visibleTo = $visibleTo === '' ? null : $visibleTo;

		$studiengang = $this->_getStudiengang($degreeProgramShortCode);

		$data = [
			'studiengang_kz' => $degreeProgramShortCode,
			'semester' => $semester,
			'datum' => $visibleFrom,
			'datum_bis' => $visibleTo,
			'translations' => []
		];

		foreach ($translations as $index => $translation)
		{
			$data['translations'][$index] = [
				'sprache' => $translation['language'] ?? '',
				'verfasser' => $translation['author'] ?? '',
				'betreff' => $translation['title'] ?? '',
				'text' => $translation['text'] ?? '',
				'sichtbar' => isset($translation['isPublished']) ? (bool)$translation['isPublished'] : false
			];
		}

		$this->_validateLanguages($data['translations']);

		foreach ($data['translations'] as $index => $translation)
		{
			$data['translations'][$index]['content'] = $this->_buildNewsXml($translation);
		}

		$uid = getAuthUID();
		$now = date('Y-m-d H:i:s');

		if ($this->db->trans_begin() === false)
		{
			$this->terminateWithError($this->db->error(), self::ERROR_TYPE_DB);
		}

		$contentResult = $this->ContentModel->update($existingNews->content_id, [
			'oe_kurzbz' => $studiengang->oe_kurzbz,
			'updateamum' => $now,
			'updatevon' => $uid
		]);

		if (isError($contentResult))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($contentResult), self::ERROR_TYPE_DB);
		}

		$newsResult = $this->NewsModel->update($newsId, [
			'studiengang_kz' => $data['studiengang_kz'],
			'semester' => $data['semester'],
			'datum' => $data['datum'],
			'datum_bis' => $data['datum_bis'],
			'updateamum' => $now,
			'updatevon' => $uid
		]);

		if (isError($newsResult))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($newsResult), self::ERROR_TYPE_DB);
		}

		$existingTranslationsResult = $this->ContentspracheModel->loadWhere([
			'content_id' => $existingNews->content_id
		]);

		if (isError($existingTranslationsResult))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($existingTranslationsResult), self::ERROR_TYPE_DB);
		}

		$existingTranslations = [];

		foreach ((array)getData($existingTranslationsResult) as $existingTranslation)
		{
			$language = $existingTranslation->sprache;

			if (
				!isset($existingTranslations[$language])
				|| (int)$existingTranslation->version > (int)$existingTranslations[$language]->version
			)
			{
				$existingTranslations[$language] = $existingTranslation;
			}
		}

		foreach ($data['translations'] as $translation)
		{
			$translation['content'] = $this->formatContentURLsToRelative($translation['content']);
			$translationData = [
				'sichtbar' => $translation['sichtbar'],
				'content' => $translation['content'],
				'titel' => $translation['betreff'],
				'updateamum' => $now,
				'updatevon' => $uid
			];

			$existingTranslation = $existingTranslations[$translation['sprache']] ?? null;

			if ($existingTranslation)
			{
				$translationResult = $this->ContentspracheModel->update(
					$existingTranslation->contentsprache_id,
					$translationData
				);
			}
			else
			{
				$translationResult = $this->ContentspracheModel->insert([
					'sprache' => $translation['sprache'],
					'content_id' => $existingNews->content_id,
					'version' => 1,
					'insertamum' => $now,
					'insertvon' => $uid
				] + $translationData);
			}

			$this->_getInsertedIdOrRollback($translationResult);
		}

		if ($this->db->trans_status() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		if ($this->db->trans_commit() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess($newsId);
	}

	/**
	 * Deletes one news entry and its linked content.
	 *
	 * @return void
	 */
	public function deleteNewsItem($newsId)
	{
		if (!is_numeric($newsId) || (int)$newsId < 1)
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'A valid news ID is required'
			]);
		}

		$newsId = (int)$newsId;
		$newsResult = $this->NewsModel->load($newsId);

		if (isError($newsResult))
		{
			$this->terminateWithError(getError($newsResult), self::ERROR_TYPE_DB);
		}

		if (!hasData($newsResult))
		{
			$this->terminateWithValidationErrors([
				'newsId' => 'The requested news entry does not exist'
			]);
		}

		$news = current(getData($newsResult));

	
		$this->_ensureUserIsEntitledForDegreeProgram($news->studiengang_kz);

		if ($this->db->trans_begin() === false)
		{
			$this->terminateWithError($this->db->error(), self::ERROR_TYPE_DB);
		}

		$newsDeleteResult = $this->NewsModel->delete($newsId);

		if (isError($newsDeleteResult))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($newsDeleteResult), self::ERROR_TYPE_DB);
		}

		$this->db->where('content_id', $news->content_id);

		if ($this->db->delete('campus.tbl_contentsprache') === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		$contentDeleteResult = $this->ContentModel->delete($news->content_id);

		if (isError($contentDeleteResult))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($contentDeleteResult), self::ERROR_TYPE_DB);
		}

		if ($this->db->trans_status() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		if ($this->db->trans_commit() === false)
		{
			$error = $this->db->error();
			$this->db->trans_rollback();
			$this->terminateWithError($error, self::ERROR_TYPE_DB);
		}

		$this->terminateWithSuccess($newsId);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Loads the requested Studiengang or reports a validation error.
	 *
	 * @param integer $studiengangKz
	 * @return stdClass
	 */
	private function _getStudiengang($studiengangKz)
	{
		$result = $this->StudiengangModel->load($studiengangKz);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		if (!hasData($result))
		{
			$this->terminateWithValidationErrors([
				'studiengang_kz' => 'The requested Studiengang does not exist'
			]);
		}

		$studiengaenge = getData($result);

		return current($studiengaenge);
	}

	/**
	 * Ensures that all supplied languages exist in tbl_sprache.
	 *
	 * @param array $translations
	 * @return void
	 */
	private function _validateLanguages($translations)
	{
		$languages = [];

		foreach ($translations as $translation)
		{
			$languages[] = $translation['sprache'];
		}

		$result = $this->SpracheModel->loadMultiple($languages);

		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		$existingLanguages = [];

		foreach ((array)getData($result) as $language)
		{
			$existingLanguages[$language->sprache] = true;
		}

		$errors = [];

		foreach ($translations as $index => $translation)
		{
			if (!isset($existingLanguages[$translation['sprache']]))
			{
				$errors['translations.' . $index . '.sprache'] = 'The requested language does not exist';
			}
		}

		if (count($errors) > 0)
		{
			$this->terminateWithValidationErrors($errors);
		}
	}

	private function _ensureUserIsEntitledForDegreeProgram($degreeProgramShortCode)
	{
		$entitledDegreePrograms = $this->permissionlib->getSTG_isEntitledFor("basis/news:w");
		if (!is_array($entitledDegreePrograms))
		{
			$this->terminateWithError('Failed to retrieve entitled degree programs for the user', self::ERROR_TYPE_GENERAL);
		}
		
		if (!in_array($degreeProgramShortCode, $entitledDegreePrograms)) {
			$this->terminateWithError("User is not entitled for the specified degree program");
		}
	}
	/**
	 * Returns an inserted identifier or rolls the transaction back on error.
	 *
	 * @param stdClass $result
	 * @return mixed
	 */
	private function _getInsertedIdOrRollback($result)
	{
		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->terminateWithError(getError($result), self::ERROR_TYPE_DB);
		}

		return getData($result);
	}

	/**
	 * Builds the XML format consumed by existing news readers.
	 *
	 * @param array $translation
	 * @return string
	 */
	private function _buildNewsXml($translation)
	{
		$document = new DOMDocument('1.0', 'UTF-8');
		$news = $document->createElement('news');
		$document->appendChild($news);

		$this->_appendCdataElement($document, $news, 'verfasser', $translation['verfasser']);
		$this->_appendCdataElement($document, $news, 'betreff', $translation['betreff']);
		$this->_appendCdataElement($document, $news, 'text', $translation['text']);

		return $document->saveXML($news);
	}

	/**
	 * Appends a CDATA element and safely preserves CDATA terminators.
	 *
	 * @param DOMDocument $document
	 * @param DOMElement $parent
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	private function _appendCdataElement($document, $parent, $name, $value)
	{
		$element = $document->createElement($name);
		$parts = explode(']]>', $value);
		$lastIndex = count($parts) - 1;

		foreach ($parts as $index => $part)
		{
			if ($index > 0)
			{
				$part = '>' . $part;
			}

			if ($index < $lastIndex)
			{
				$part .= ']]';
			}

			$element->appendChild($document->createCDATASection($part));
		}

		$parent->appendChild($element);
	}
    
	private function _sendNewsItemTranslationRequiredMail($newsItem)
	{
		$translatorsResult = $this->BenutzerfunktionModel->getBenutzerFunktionen("translate", $newsItem->studiengang_kz);
		$translators = $this->getDataOrTerminateWithError($translatorsResult);

		$newsItemContentResult = $this->ContentspracheModel->loadWhere([
			'content_id' => $newsItem->content_id,
			'sprache' => 'German'
		]);
		$newsItemContentEntry = getData($newsItemContentResult)[0] ?? null;
		if ($newsItemContentEntry) {
			$newsItem->sichtbar = $newsItemContentEntry->sichtbar ?? false;
			$newsItem->content = $newsItemContentEntry->content;
		}
		$content = getData(
			$this->cmslib->createNewsContent(
				$this->cmslib->createNewsWrapper($newsItem, true),
				false,
				""
			)
		);
		$content = str_replace('dms.php', APP_ROOT . 'cms/dms.php', $content);

		foreach ($translators as $translator) {
			sendSanchoMail(
				"Sancho_Mail_News_Translation_Req",
				[
					"newsItemUrl" => APP_ROOT . "cis.php/CisVue/Cms/newsAdministration?newsId=" . $newsItem->news_id,
					"newsItem" => $content,
					"newsItemAuthor" => $newsItem->verfasser,
					"newsItemSubject" => $newsItem->betreff
				],
				$translator->uid . "@" . DOMAIN,
				"Übersetzung erforderlich / Translation required"
			);
		}
	}

	private function formatContentURLsToRelative($content)
	{
		$content = preg_replace(
			'#https?://[^"\'>\s]*/(dms\.php(?:\?[^"\'>\s]*)?)#i',
			'$1',
			$content
		);

		$content = preg_replace(
			'#(?:\.\./)+[^"\'>\s]*/(dms\.php(?:\?[^"\'>\s]*)?)#i',
			'$1',
			$content
		);

		$content = preg_replace(
			'#/[^"\'>\s]*/(dms\.php(?:\?[^"\'>\s]*)?)#i',
			'$1',
			$content
		);

		return $content;
	}
}
