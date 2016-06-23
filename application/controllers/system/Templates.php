<?php
if (! defined('BASEPATH')) exit('No direct script access allowed');

class Templates extends FHC_Controller 
{

	public function __construct()
    {
        parent::__construct();
        $this->load->library('VorlageLib');
    }

	public function index()
	{
		$this->load->view('system/templates.php');
	}

	public function table()
	{
		$mimetype = $this->input->post('mimetype', TRUE);
		if (is_null($mimetype))
			$mimetype = 'text/html';
		if ($mimetype == '')
			$mimetype = null;
		$vorlage = $this->vorlagelib->getVorlageByMimetype($mimetype);
		if ($vorlage->error)
			show_error($vorlage->retval);
		//var_dump($vorlage);
		
		$data = array
		(
			'mimetype' => $mimetype,
			'vorlage' => $vorlage->retval
		);
		$v = $this->load->view('system/templatesList.php', $data);
	}

	public function view($vorlage_kurzbz = null)
	{
		if (empty($vorlage_kurzbz))
			exit;
		$vorlagentext = $this->vorlagelib->getVorlagetextByVorlage($vorlage_kurzbz);
		if ($vorlagentext->error)
			show_error($vorlagentext->retval);
		//var_dump($vorlage);
		
		$data = array
		(
			'vorlage_kurzbz' => $vorlage_kurzbz,
			'vorlagentext' => $vorlagentext->retval
		);
		$v = $this->load->view('system/templatetextList.php', $data);
	}

	public function edit($vorlage_kurzbz = null)
	{
		if (empty($vorlage_kurzbz))
			exit;
		$vorlage = $this->vorlagelib->getVorlage($vorlage_kurzbz);
		//var_dump($vorlage);
		if ($vorlage->error)
			show_error($vorlage->retval);
		if (count($vorlage->retval) != 1)
			show_error('Nachricht nicht vorhanden! ID: '.$vorlage_kurzbz);

		$data = array
		(
			'vorlage' => $vorlage->retval[0]
		);
		//var_dump($data['message']);
		$v = $this->load->view('system/templatesEdit', $data);
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
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz', TRUE);
		$data['bezeichnung'] = $this->input->post('bezeichnung', TRUE);
		$data['anmerkung'] = $this->input->post('anmerkung', TRUE);
		$data['mimetype'] = $this->input->post('mimetype', TRUE);
		$data['attribute'] = $this->input->post('attribute', TRUE);
		$vorlage = $this->vorlagelib->saveVorlage($vorlage_kurzbz, $data);
		if ($vorlage->error)
			show_error($vorlage->retval);
		$vorlage_kurzbz = $vorlage->retval;

		redirect('/system/Templates/edit/'.$vorlage_kurzbz);
	}

	public function newText()
	{
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz', TRUE);
		$data = array
		(
			'vorlage_kurzbz' => $vorlage_kurzbz,
			'studiengang_kz' => 0,
			'version' => 1,
			'oe_kurzbz' => 'etw'
		);
		$vorlagetext = $this->vorlagelib->insertVorlagetext($data);
		if ($vorlagetext->error)
			show_error($vorlagetext->retval);
		$vorlagestudiengang_id = $vorlagetext->retval;

		redirect('/system/Templates/editText/'.$vorlagestudiengang_id);
	}

	public function editText($vorlagestudiengang_id)
	{
		$vorlagetext = $this->vorlagelib->getVorlagetextById($vorlagestudiengang_id);
		if ($vorlagetext->error)
			show_error($vorlagetext->retval);
		$data = $vorlagetext->retval[0];

		// Preview-Data
		$schema = $this->vorlagelib->getVorlage($data->vorlage_kurzbz);
		$data->schema = $schema->retval[0]->attribute;

		$this->load->view('system/templatetextEdit', $data);
	}

	public function saveText()
	{
		$vorlagestudiengang_id = $this->input->post('vorlagestudiengang_id', TRUE);
		$data['studiengang_kz'] = $this->input->post('studiengang_kz', TRUE);
		$data['version'] = $this->input->post('version', TRUE);
		$data['oe_kurzbz'] = $this->input->post('oe_kurzbz', TRUE);
		if ($this->input->post('orgform_kurzbz') == '')
			$data['orgform_kurzbz'] = null;
		else
			$data['orgform_kurzbz'] = $this->input->post('orgform_kurzbz', TRUE);
		$data['text'] = $this->input->post('text', TRUE);
		$data['aktiv'] = $this->input->post('aktiv', TRUE);
		$vorlagetext = $this->vorlagelib->updateVorlagetext($vorlagestudiengang_id, $data);
		if ($vorlagetext->error)
			show_error($vorlagetext->retval);
		$data['vorlagestudiengang_id'] = $vorlagestudiengang_id;
		redirect('/system/Templates/editText/'.$vorlagestudiengang_id);
		//$this->load->view('system/templatetextEdit', $data);
	}

	public function preview($vorlagestudiengang_id)
	{
		$formdata = $this->input->post('formdata', FALSE);
		$daten = json_decode($formdata, TRUE);
		$vorlagetext = $this->vorlagelib->getVorlagetextById($vorlagestudiengang_id);
		if ($vorlagetext->error)
			show_error($vorlagetext->retval);
		$data = array
		(
			'text' => $this->vorlagelib->parseVorlagetext($vorlagetext->retval[0]->text, $daten)
		);
		$this->load->view('system/templatetextPreview', $data);
	}
}
