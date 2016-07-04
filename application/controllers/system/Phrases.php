<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Phrases extends FHC_Controller
{

	public function __construct()
    {
        parent::__construct();
        $this->load->library('PhrasesLib');
    }

	public function index()
	{
		$this->load->view('system/phrases.php');
	}

	public function table()
	{
		$phrases = $this->phraseslib->getPhraseByApp('aufnahme');
		if ($phrases->error)
			show_error($phrases->retval);
		//var_dump($vorlage);

		$data = array
		(
			'app' => 'aufnahme',
			'phrases' => $phrases->retval
		);
		$v = $this->load->view('system/phrasesList.php', $data);
	}

	public function view($phrase_id = null)
	{
		if (empty($phrase_id))
			exit;
		$phrase_inhalt = $this->phraseslib->getPhraseInhalt($phrase_id);
		$phrase = $this->phraseslib->getPhrase($phrase_id);
		if ($phrase_inhalt->error)
			show_error($phrase_inhalt->retval);
		//var_dump($vorlage);

		$data = array
		(
			'phrase_id' => $phrase_id,
			'phrase' => $phrase->retval[0]->phrase,
			'phrase_inhalt' => $phrase_inhalt->retval
		);
		$v = $this->load->view('system/phrasesinhaltList.php', $data);
	}

	public function deltext($phrasentext_id=null, $phrase_id = null)
	{
		if (empty($phrase_id) or empty($phrasentext_id))
			exit;
		$phrase_inhalt = $this->phraseslib->delPhrasentext($phrasentext_id);
		if ($phrase_inhalt->error)
			show_error($phrase_inhalt->retval);
		//var_dump($vorlage);

		redirect('/system/Phrases/view/'.$phrase_id);
	}

	public function edit($phrase_id = null)
	{
		if (empty($phrase_id))
			exit;
		$phrase = $this->phraseslib->getPhrase($phrase_id);
		//var_dump($vorlage);
		if ($phrase->error)
			show_error($phrase->retval);
		if (count($phrase->retval) != 1)
			show_error('Phrase nicht vorhanden! ID: '.$phrase_id);

		$data = array
		(
			'phrase' => $phrase->retval[0]
		);
		//var_dump($data['message']);
		$v = $this->load->view('system/phrasesEdit', $data);
	}

	public function write($vorlage_kurzbz = null)
	{
		$data = array
		(
			'subject' => 'TestSubject',
			'body' => 'TestDevelopmentBodyText'
		);
		$v = $this->load->view('system/messageWrite', $data);
	}

	public function save()
	{
		$phrase_id = $this->input->post('phrase_id', TRUE);
		$data['phrase'] = $this->input->post('phrase', TRUE);
		$phrase = $this->phraseslib->savePhrase($phrase_id, $data);
		if ($phrase->error)
			show_error($phrase->retval);
		$phrase_id = $phrase->retval;

		redirect('/system/Phrases/edit/'.$phrase_id);
	}

	public function newText()
	{
		$phrase_id = $this->input->post('phrase_id', TRUE);
		$data = array
		(
			'phrase_id' => $phrase_id,
			'sprache' => 'German',
			'text' => '',
			'description' => '',
			'orgeinheit_kurzbz' => 'etw'
		);
		$phrase_inhalt = $this->phraseslib->insertPhraseinhalt($data);
		if ($phrase_inhalt->error)
			show_error($phrase_inhalt->retval);
		$phrase_inhalt_id = $phrase_inhalt->retval;

		redirect('/system/Phrases/editText/'.$phrase_inhalt_id);
	}

	public function editText($phrasentext_id)
	{
		$phrase_inhalt = $this->phraseslib->getPhrasentextById($phrasentext_id);
		if ($phrase_inhalt->error)
			show_error($phrase_inhalt->retval);
		$data = $phrase_inhalt->retval[0];

		$this->load->view('system/phraseinhaltEdit', $data);
	}

	public function saveText()
	{
		$phrase_inhalt_id = $this->input->post('phrase_inhalt_id', TRUE);
		$data['orgeinheit_kurzbz'] = $this->input->post('oe_kurzbz', TRUE);
		$data['text'] = $this->input->post('text', TRUE);
		$data['description'] = $this->input->post('description', TRUE);
		$data['sprache'] = $this->input->post('sprache', TRUE);
		$phrase_inhalt = $this->phraseslib->updatePhraseInhalt($phrase_inhalt_id, $data);
		if ($phrase_inhalt->error)
			show_error($phrase_inhalt->retval);
		$data['phrase_inhalt_id'] = $phrase_inhalt_id;
		redirect('/system/Phrases/editText/'.$phrase_inhalt_id);
		//$this->load->view('system/templatetextEdit', $data);
	}

}
