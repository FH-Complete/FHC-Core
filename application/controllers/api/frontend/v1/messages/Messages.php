<?php


if (!defined('BASEPATH')) exit('No direct script access allowed');

class Messages extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getMessages' => ['admin:r', 'assistenz:r'],
			'getVorlagen' => ['admin:r', 'assistenz:r'],
			'getMessageVarsPerson' => ['admin:r', 'assistenz:r'],
			'getMsgVarsPrestudent' => ['admin:r', 'assistenz:r'],
			'getMsgVarsLoggedInUser' => ['admin:r', 'assistenz:r'],
			'getNameOfDefaultRecipient' => ['admin:r', 'assistenz:r'],
			'sendMessage' => ['admin:r', 'assistenz:r'],
			'deleteMessage' => ['admin:r', 'assistenz:r'],
			'getVorlagentext' => ['admin:r', 'assistenz:r'],
			'getPreviewText' => ['admin:r', 'assistenz:r'],
			'getReplyData' => ['admin:r', 'assistenz:r'],
			'getPersonIdFromUid' => ['admin:r', 'assistenz:r'],
		]);

		//Load Models
		$this->load->model('system/Message_model', 'MessageModel');
		$this->load->model('CL/Messages_model', 'MessagesModel');

		// Additional Permission Checks
		//TODO(manu) check permissions

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');
		$this->load->library('MessageLib');

		// Load language phrases
		$this->loadPhrases([
			'ui'
		]);
	}

	public function getMessages($id, $type_id)
	{
		switch($type_id)
		{
			case 'uid':
				$id = $this->_getPersonIdFromUid($id);
				break;
			case 'person_id':
				$id = $id;
				break;
			default:
				$this->terminateWithError("MESSAGES::getMessages logic for type_id " . $type_id . " not defined yet", self::ERROR_TYPE_GENERAL);
				break;
		}

		$result = $this->MessageModel->getMessagesForTable($id);

		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getVorlagen()
	{
		//get oe of user
		$uid = getAuthUID();
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$result = $this->BenutzerfunktionModel->getBenutzerfunktionByUid($uid, 'oezuordnung');

		$data = $this->getDataOrTerminateWithError($result);
		$oe_kurzbz = current($data);

		$this->load->model('system/Vorlage_model', 'VorlageModel');

		//39 Stück Variante OE
		$result = $this->VorlageModel->getAllVorlagenByOe($oe_kurzbz->oe_kurzbz);
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);

		//IF ADMIN 167
		$this->VorlageModel->addOrder('vorlage_kurzbz', 'ASC');
		//only HTML-vorlagen -> for admin
		$result = $this->VorlageModel->loadWhere(
			array(
				'mimetype' => 'text/html'
			));


		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

	public function getVorlagentext($vorlage_kurzbz)
	{
		//$this->terminateWithError("vor " . $vorlage_kurzbz, self::ERROR_TYPE_GENERAL);
		//$studiengang_kz = 227; //TODO(Manu) check dynamisieren NULL
		$studiengang_kz = 0;
		$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
		$this->VorlagestudiengangModel->addOrder('version', 'DESC');

		$result = $this->VorlagestudiengangModel->loadWhere(
			[
				'vorlage_kurzbz' =>$vorlage_kurzbz,
				'studiengang_kz' => $studiengang_kz
			]);

		$data = $this->getDataOrTerminateWithError($result);

		//not correct with Vorlage
		$vorlage = current($data);

		//$this->terminateWithSuccess($data);
		$this->terminateWithSuccess($vorlage->text);
	}

	public function getMessageVarsPerson()
	{
		$result = $this->MessageModel->getMessageVarsPerson();

		$data = $this->getDataOrTerminateWithError($result);


		$this->terminateWithSuccess($data);
	}

	public function getMsgVarsPrestudent($uid)
	{
		$prestudent_id = $this-> _getPrestudentIdFromUid($uid);

		$result = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);

		$data = $this->getDataOrTerminateWithError($result);


		$this->terminateWithSuccess($data);
	}

	public function getMsgVarsLoggedInUser()
	{
		$uid = getAuthUID();

		$result = $this->MessageModel->getMsgVarsLoggedInUser();

		$data = $this->getDataOrTerminateWithError($result);


		$this->terminateWithSuccess($data);
	}

	public function getNameOfDefaultRecipient($id, $type_id)
	{
		switch($type_id)
		{
			case 'uid':
				$id = $this->_getPersonIdFromUid($id);
				break;
			case 'person_id':
				$id = $id;
				break;
			default:
				$this->terminateWithError("MESSAGES::getNameOfDefaultRecipient logic for type_id " . $type_id . " not defined yet", self::ERROR_TYPE_GENERAL);
				break;
		}

		$this->load->model('person/Person_model', 'PersonModel');

		$result = $this->PersonModel->load($id);
		//$this->terminateWithSuccess($result);
		$data = $this->getDataOrTerminateWithError($result);
		$name = current($data);

		$this->terminateWithSuccess($name->vorname . " " . $name->nachname );
	}

	public function sendMessage($recipient_id)
	{
		//default setting
		$receiversPersonId = $this->_getPersonIdFromUid($recipient_id);

		$uid = getAuthUID();
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$result = $this->BenutzerModel->loadWhere(
			['uid' => $uid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$benutzer = current($data);

		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		$this->load->library('form_validation');

		$this->form_validation->set_rules('subject', 'Betreff', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Betreff'])
		]);

		$this->form_validation->set_rules('body', 'Text', 'required', [
			'required' => $this->p->t('ui', 'error_fieldRequired', ['field' => 'Text'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$subject = $this->input->post('subject');
		$body = $this->input->post('body');



		$typeId = $this->input->post('type_id');
		$id = $this->input->post('id');

		if($typeId == 'uid')
		{
			//$this->terminateWithError("uid ", self::ERROR_TYPE_GENERAL);
			$prestudent_id = $this-> _getPrestudentIdFromUid($id);

			//parseMessagetext for variables Prestudent
			$result = $this->MessagesModel->parseMessageTextPrestudent($prestudent_id, $body);
			$bodyParsed = $this->getDataOrTerminateWithError($result);
		}
		elseif($typeId == 'person_id')
		{
			$this->terminateWithError("person_id ", self::ERROR_TYPE_GENERAL);

			$result = $this->MessagesModel->parseMessageTextPerson($id, $body);
			$bodyParsed = $this->getDataOrTerminateWithError($result);
		}
		elseif($typeId == 'prestudent_id')
		{
			$this->terminateWithError("prestudent_id ", self::ERROR_TYPE_GENERAL);

			$result = $this->MessagesModel->parseMessageTextPrestudent($id, $body);
			$bodyParsed = $this->getDataOrTerminateWithError($result);
		}
		else
		{
			$this->terminateWithError("type_id " . $typeId . " not valid", self::ERROR_TYPE_GENERAL);
		}

		$result = $this->messagelib->sendMessageUser($receiversPersonId, $subject, $bodyParsed, $benutzer->person_id);

		$this->terminateWithSuccess($result);
	}

	public function getPreviewText($id, $type_id)
	{
		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);

		}
		else
			$this->terminateWithError("Textbody missing ", self::ERROR_TYPE_GENERAL);

		switch($type_id)
		{
			case 'uid':
				$prestudent_id = $this->_getPrestudentIdFromUid($id);
				$result = $this->MessagesModel->parseMessageTextPrestudent($prestudent_id, $data);

				break;
			case 'person_id':
				$id = $id;
				break;
			default:
				$this->terminateWithError("MESSAGES::getPreviewText logic for type_id " . $type_id . " not defined yet", self::ERROR_TYPE_GENERAL);
				break;
		}

		//$this->terminateWithSuccess($result);
		$bodyParsed = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($bodyParsed);
	}

	public function getReplyData($messageId)
	{
	//	return $this->terminateWithError("in get ReplyBody" . $messageId, self::ERROR_TYPE_GENERAL);
		//TODO(Manu) validation of messageId: if number


		$this->MessageModel->addSelect('public.tbl_msg_message.*');
		$this->MessageModel->addSelect('r.*');
		$this->MessageModel->addSelect('p.nachname');
		$this->MessageModel->addSelect('p.vorname');
		$this->MessageModel->addJoin('public.tbl_msg_recipient r', 'ON (r.message_id = public.tbl_msg_message.message_id)');
		$this->MessageModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_msg_message.person_id)');

		$result = $this->MessageModel->loadWhere(
			array('r.message_id' => $messageId)
		);

	//	$this->terminateWithSuccess((getData($result) ?: []));
		$dataMessage = $this->getDataOrTerminateWithError($result);

		$prefix = "Re: "; // reply subject prefix

/*		$body = current($dataMessage->body);
		$subject = $dataMessage->subject;*/

		$subject = $dataMessage[0]->subject;
		$body = $dataMessage[0]->body;


		$replyBody = $this->_getReplyBody($body, $dataMessage[0]->nachname, $dataMessage[0]->vorname, $dataMessage[0]->insertamum);

		$dataMessage[0]->replyBody = $replyBody;
		$dataMessage[0]->rest = "Help Manu";
		$dataMessage[0]->replySubject = $prefix . $subject;

		$this->terminateWithSuccess($dataMessage);


	}

	public function deleteMessage($messageId)
	{
		// Start DB transaction
		$this->db->trans_begin();

		$result = $this->MessageModel->deleteMessageRecipient($messageId);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);

		}

		$result = $this->MessageModel->deleteMessageStatus($messageId);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$result = $this->MessageModel->deleteMessage($messageId);
		if (isError($result)) {
			return $this->terminateWithError($result, self::ERROR_TYPE_GENERAL);
		}

		$this->db->trans_commit();

		$this->terminateWithSuccess($result);
	}

	public function getPersonIdFromUid($uid)
	{
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$result = $this->BenutzerModel->loadWhere(
			['uid' => $uid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$benutzer = current($data);

		$this->terminateWithSuccess($benutzer->person_id);
	}

	private function _getPersonIdFromUid($uid)
	{
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$result = $this->BenutzerModel->loadWhere(
			['uid' => $uid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$benutzer = current($data);

		return $benutzer->person_id;
	}

	private function _getPrestudentIdFromUid($uid)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(
			['student_uid' => $uid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		$student = current($data);

		return $student->prestudent_id;
	}

	private function _getReplyBody($body, $receiverName, $receiverSurname, $sentDate)
	{
		// To quote a reply body message
		$bodyFormat = "<br>
					<br>
					<blockquote>
						<i>
							On %s %s %s wrote:
						</i>
					</blockquote>
					<blockquote style='border-left:2px solid; padding-left: 8px'>
						%s
					</blockquote>";
		return sprintf(
			$bodyFormat,
			date_format(date_create($sentDate), 'd.m.Y H:i'), $receiverName, $receiverSurname, $body
		);
	}
}