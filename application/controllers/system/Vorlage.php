<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Vorlage extends Auth_Controller
{
	public function __construct()
    {
        parent::__construct(
			array(
				'index' => 'system/vorlage:r',
				'table' => 'system/vorlage:r',
				'view' => 'system/vorlage:r',
				'edit' => 'system/vorlage:rw',
				'write' => 'system/vorlage:rw',
				'save' => 'system/vorlage:rw',
				'newText' => 'system/vorlage:rw',
				'editText' => 'system/vorlage:rw',
				'newText' => 'system/vorlage:rw',
				'linkDocuments' => 'system/vorlage:r',
				'saveDocuments' => 'system/vorlage:rw',
				'deleteDocumentLink' => 'system/vorlage:rw',
				'saveText' => 'system/vorlage:rw',
				'preview' => 'system/vorlage:r'
			)
		);

		// Loads the vorlage library
		$this->load->library('VorlageLib');

		// Loads the widget library
		$this->load->library('WidgetLib');
    }

	public function index()
	{
		$this->load->view('system/vorlage/templates.php');
	}

	public function table()
	{
		$mimetype = $this->input->post('mimetype');

		if (is_null($mimetype))
			$mimetype = 'text/html';
		if ($mimetype == '')
			$mimetype = null;

		$vorlage = $this->vorlagelib->getVorlageByMimetype($mimetype);

		if ($vorlage->error)
			show_error(getError($vorlage));

		$data = array (
			'mimetype' => $mimetype,
			'vorlage' => $vorlage->retval
		);

		$v = $this->load->view('system/vorlage/templatesList.php', $data);
	}

	public function view($vorlage_kurzbz = null)
	{
		if (isEmptyString($vorlage_kurzbz)) exit;

		$vorlagentext = $this->vorlagelib->getVorlagetextByVorlage($vorlage_kurzbz);

		if ($vorlagentext->error)
			show_error(getError($vorlagentext));

		$data = array (
			'vorlage_kurzbz' => $vorlage_kurzbz,
			'vorlagentext' => $vorlagentext->retval
		);

		$v = $this->load->view('system/vorlage/templatetextList.php', $data);
	}

	public function edit($vorlage_kurzbz = null)
	{
		if (isEmptyString($vorlage_kurzbz)) exit;

		$vorlage = $this->vorlagelib->getVorlage($vorlage_kurzbz);

		if ($vorlage->error)
			show_error(getError($vorlage));

		if (count($vorlage->retval) != 1)
			show_error('Nachricht nicht vorhanden! ID: '.$vorlage_kurzbz);

		$data = array (
			'vorlage' => $vorlage->retval[0]
		);

		$v = $this->load->view('system/vorlage/templatesEdit', $data);
	}

	public function write($vorlage_kurzbz = null)
	{
		$data = array (
			'subject' => 'TestSubject',
			'body' => 'TestDevelopmentBodyText'
		);

		$v = $this->load->view('system/vorlage/messageWrite', $data);
	}

	public function save()
	{
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz');

		$data = array(
			'bezeichnung' => $this->input->post('bezeichnung'),
			'anmerkung' => $this->input->post('anmerkung'),
			'mimetype' => $this->input->post('mimetype'),
			'attribute' => $this->input->post('attribute')
		);

		$vorlage = $this->vorlagelib->saveVorlage($vorlage_kurzbz, $data);

		if ($vorlage->error)
			show_error(getError($vorlage));

		$vorlage_kurzbz = $vorlage->retval;

		redirect('/system/vorlage/edit/'.$vorlage_kurzbz);
	}

	public function newText()
	{
		$vorlage_kurzbz = $this->input->post('vorlage_kurzbz');

		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$this->OrganisationseinheitModel->addLimit(1);
		$this->OrganisationseinheitModel->addOrder('oe_kurzbz');

		$resultOE = $this->OrganisationseinheitModel->loadWhere(array('aktiv' => true, 'oe_parent_kurzbz' => null));

		if ($resultOE->error)
			show_error(getError($resultOE));

		if (hasData($resultOE))
		{
			$orgeinheit_kurzbz = $resultOE->retval[0]->oe_kurzbz;

			$data = array (
				'vorlage_kurzbz' => $vorlage_kurzbz,
				'studiengang_kz' => 0,
				'version' => 1,
				'oe_kurzbz' => $orgeinheit_kurzbz
			);

			$vorlagetext = $this->vorlagelib->insertVorlagetext($data);

			if ($vorlagetext->error)
				show_error(getError($vorlagetext));

			$vorlagestudiengang_id = $vorlagetext->retval;

			redirect('/system/vorlage/editText/'.$vorlagestudiengang_id);
		}
		else
		{
			show_error('No valid organisation unit found');
		}
	}

	public function editText($vorlagestudiengang_id)
	{
		$vorlagetext = $this->vorlagelib->getVorlagetextById($vorlagestudiengang_id);

		if ($vorlagetext->error)
			show_error(getError($vorlagetext));

		$data = $vorlagetext->retval[0];

		// Preview-Data
		$schema = $this->vorlagelib->getVorlage($data->vorlage_kurzbz);

		$data->schema = $schema->retval[0]->attribute;

		$this->load->view('system/vorlage/templatetextEdit', $data);
	}

	public function linkDocuments($vorlagestudiengang_id)
	{
		$data = array();

		$this->load->model('system/Vorlagedokument_model', 'VorlagedokumentModel');

		$return = $this->VorlagedokumentModel->loadDokumenteFromVorlagestudiengang($vorlagestudiengang_id);

		$data['documents'] = $return->retval;

		$this->load->model('system/Dokument_model', 'DokumentModel');
		$this->DokumentModel->addOrder('bezeichnung');

		$return = $this->DokumentModel->load();

		$data['allDocuments'] = $return->retval;
		$data['vorlagestudiengang_id'] = $vorlagestudiengang_id;

		$this->load->view('system/vorlage/templateLinkDocuments', $data);
	}

	public function saveDocuments($vorlagestudiengang_id, $dokument_kurzbz, $sort)
	{
		$insert = array();

		$insert['vorlagestudiengang_id'] = $vorlagestudiengang_id;
		$insert['dokument_kurzbz'] = $dokument_kurzbz;
		$insert['sort'] = $sort;

		$this->load->model('system/Vorlagedokument_model', 'VorlagedokumentModel');

		$this->VorlagedokumentModel->insert($insert);
	}

	public function deleteDocumentLink($vorlagestudiengang_id)
	{
		$this->load->model('system/Vorlagedokument_model', 'VorlagedokumentModel');

		$this->VorlagedokumentModel->delete($vorlagestudiengang_id);
	}

	public function changeSort($vorlagestudiengang_id, $sort)
	{
		$this->load->model('system/Vorlagedokument_model', 'VorlagedokumentModel');

		$this->VorlagedokumentModel->update($vorlagestudiengang_id, array('sort' => $sort));
	}

	public function saveText()
	{
		$data = array(
			'studiengang_kz' => $this->input->post('studiengang_kz'),
			'version' => $this->input->post('version'),
			'oe_kurzbz' => $this->input->post('oe_kurzbz'),
			'aktiv' => $this->input->post('aktiv'),
			'text' => $this->input->post('text'),
			'vorlagestudiengang_id' => $this->input->post('vorlagestudiengang_id')
		);

		if ($this->input->post('sprache') == '')
			$data['sprache'] = null;
		else
			$data['sprache'] = $this->input->post('sprache');

		if ($this->input->post('orgform_kurzbz') == '')
			$data['orgform_kurzbz'] = null;
		else
			$data['orgform_kurzbz'] = $this->input->post('orgform_kurzbz');

		$vorlagetext = $this->vorlagelib->updateVorlagetext($data['vorlagestudiengang_id'], $data);

		if ($vorlagetext->error)
			show_error(getError($vorlagetext));

		redirect('/system/vorlage/editText/'.$data['vorlagestudiengang_id']);
	}

	public function preview($vorlagestudiengang_id)
	{
		$jsonDecodedForm = json_decode($this->input->post('formdata'), true);

		$vorlagetext = $this->vorlagelib->getVorlagetextById($vorlagestudiengang_id);

		if ($vorlagetext->error)
			show_error(getError($vorlagetext));

		$data = array(
			'text' => parseText($vorlagetext->retval[0]->text, $jsonDecodedForm)
		);

		$this->load->view('system/vorlage/templatetextPreview', $data);
	}
}
