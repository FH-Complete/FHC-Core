<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Phrase extends API_Controller
{
	/**
	 * Phrase API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Phrase' => 'system/phrase:rw', 'Phrases' => 'system/phrase:r'));

		$this->load->library('PhrasesLib');
	}

	/**
	 *
	 */
	public function getPhrase()
	{
		$phrase_id = $this->get('phrase_id');

		if (isset($phrase_id))
		{
			$result = $this->phraseslib->getPhrase($phrase_id);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	public function getPhrases()
	{
		$app = $this->get('app');
		$sprache = $this->get('sprache');
		$phrase = $this->get('phrase');
		$orgeinheit_kurzbz = $this->get('orgeinheit_kurzbz');
		$orgform_kurzbz = $this->get('orgform_kurzbz');
		$blockTags = $this->get('blockTags');

		if (isset($app) && isset($sprache))
		{
			$result = $this->phraseslib->getPhrases($app, $sprache, $phrase, $orgeinheit_kurzbz, $orgform_kurzbz, $blockTags);

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	public function postPhrase()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['phrase_id']))
			{
				$result = $this->PhraseModel->update($this->post()['phrase_id'], $this->post());
			}
			else
			{
				$result = $this->PhraseModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 *
	 */
	private function _validate($phrase = null)
	{
		return false;
	}
}
