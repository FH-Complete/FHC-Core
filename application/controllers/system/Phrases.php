<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Phrases extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'system/phrase:r',
				'table' => 'system/phrase:r',
				'view' => 'system/phrase:r',
				'deltext' => 'system/phrase:rw',
				'edit' => 'system/phrase:rw',
				'save' => 'system/phrase:rw',
				'newText' => 'system/phrase:rw',
				'editText' => 'system/phrase:rw',
				'saveText' => 'system/phrase:rw'
			)
		);

        // Loads the phrases library
        $this->load->library('PhrasesLib');

        // Loads the widget library
		$this->load->library('WidgetLib');
    }

	/**
	 *
	 */
	public function index()
	{
		$this->load->view('system/phrases/phrases.php');
	}

	/**
	 *
	 */
	public function table()
	{
		$phrases = $this->phraseslib->getPhraseByApp('aufnahme');
		if ($phrases->error)
			show_error(getError($phrases));

		$data = array(
			'app' => 'aufnahme',
			'phrases' => $phrases->retval
		);

		$this->load->view('system/phrases/phrasesList.php', $data);
	}

	/**
	 *
	 */
	public function view($phrase_id)
	{
		if (!is_numeric($phrase_id))
			show_error('Invalid phrase_id parameter');

		$phrase = $this->phraseslib->getPhrase($phrase_id);

		$phrase_inhalt = $this->phraseslib->getPhraseInhalt($phrase_id);
		if ($phrase_inhalt->error)
			show_error(getError($phrase_inhalt));

		$data = array(
			'phrase_id' => $phrase_id,
			'phrase' => $phrase->retval[0]->phrase,
			'phrase_inhalt' => $phrase_inhalt->retval
		);

		$this->load->view('system/phrases/phrasesinhaltList.php', $data);
	}

	/**
	 *
	 */
	public function deltext($phrasentext_id, $phrase_id)
	{
		if (!is_numeric($phrasentext_id) || !is_numeric($phrase_id))
			show_error('Invalid phrasentext_id or phrase_id parameter');

		$phrase_inhalt = $this->phraseslib->delPhrasentext($phrasentext_id);
		if ($phrase_inhalt->error)
			show_error(getError($phrase_inhalt));

		redirect('/system/Phrases/view/'.$phrase_id);
	}

	/**
	 *
	 */
	public function edit($phrase_id = null)
	{
		if (!is_numeric($phrase_id)) return;

		$phrase = $this->phraseslib->getPhrase($phrase_id);
		if ($phrase->error)
			show_error(getError($phrase));

		if (count($phrase->retval) != 1)
			show_error('Phrase nicht vorhanden! ID: '.$phrase_id);

		$data = array(
			'phrase' => $phrase->retval[0]
		);

		$this->load->view('system/phrases/phrasesEdit', $data);
	}

	/**
	 *
	 */
	public function save()
	{
		$phrase_id = $this->input->post('phrase_id');
		$data = array('phrase' => $this->input->post('phrase'));

		$phrase = $this->phraseslib->savePhrase($phrase_id, $data);
		if ($phrase->error)
			show_error(getError($phrase));

		$phrase_id = $phrase->retval;

		redirect('/system/Phrases/edit/'.$phrase_id);
	}

	/**
	 *
	 */
	public function newText()
	{
		$phrase_id = $this->input->post('phrase_id');

		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');

		$this->OrganisationseinheitModel->addLimit(1);
		$this->OrganisationseinheitModel->addOrder('oe_kurzbz');

		$resultOE = $this->OrganisationseinheitModel->loadWhere(array('aktiv' => true, 'oe_parent_kurzbz' => null));
		if ($resultOE->error)
			show_error(getError($resultOE));

		if (hasData($resultOE))
		{
			$orgeinheit_kurzbz = $resultOE->retval[0]->oe_kurzbz;

			$data = array(
				'phrase_id' => $phrase_id,
				'sprache' => 'German',
				'text' => '',
				'description' => '',
				'orgeinheit_kurzbz' => $orgeinheit_kurzbz
			);

			$phrase_inhalt = $this->phraseslib->insertPhraseinhalt($data);
			if ($phrase_inhalt->error)
				show_error(getError($phrase_inhalt));

			$phrase_inhalt_id = $phrase_inhalt->retval;

			redirect('/system/Phrases/editText/'.$phrase_inhalt_id);
		}
		else
		{
			show_error('No valid organisation unit found');
		}
	}

	/**
	 *
	 */
	public function editText($phrasentext_id)
	{
		$phrase_inhalt = $this->phraseslib->getPhrasentextById($phrasentext_id);
		if ($phrase_inhalt->error)
			show_error(getError($phrase_inhalt));

		$data = $phrase_inhalt->retval[0];

		$this->load->view('system/phrases/phraseinhaltEdit', $data);
	}

	/**
	 *
	 */
	public function saveText()
	{
		$phrase_inhalt_id = $this->input->post('phrase_inhalt_id');

		$data = array(
			'orgeinheit_kurzbz' => $this->input->post('oe_kurzbz'),
			'orgform_kurzbz' => $this->input->post('orgform_kurzbz'),
			'text' => $this->input->post('text'),
			'description' => $this->input->post('description'),
			'sprache' => $this->input->post('sprache')
		);

		$phrase_inhalt = $this->phraseslib->updatePhraseInhalt($phrase_inhalt_id, $data);
		if ($phrase_inhalt->error)
			show_error(getError($phrase_inhalt));


		redirect('/system/Phrases/editText/'.$phrase_inhalt_id);
	}
}
