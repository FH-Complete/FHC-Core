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
			'getNameOfDefaultRecipients' => ['admin:r', 'assistenz:r'],
			'sendMessage' => ['admin:r', 'assistenz:r'],
			'deleteMessage' => ['admin:r', 'assistenz:r'],
			'getDataVorlage' => ['admin:r', 'assistenz:r'],
			'getPreviewText' => ['admin:r', 'assistenz:r'],
			'getReplyData' => ['admin:r', 'assistenz:r'],
			'getPersonId' => ['admin:r', 'assistenz:r'],
			'getUid' => ['admin:r', 'assistenz:r'],
			'getUids' => ['admin:r', 'assistenz:r'],
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

	public function getMessages($id, $type_id, $size, $page)
	{
		if($type_id != 'person_id'){
			$id = $this->_getPersonId($id, $type_id);
		}

		$offset = $size * ($page - 1);
		$limit = $size;

		$result = $this->MessageModel->getMessagesForTable($id, $offset, $limit);

		if (hasData($result))
		{
			$data = getData($result);
			$this->addMeta('count', $data['count']);
			$this->terminateWithSuccess($data['data']);
		}

		$this->terminateWithSuccess(array());
	}

	public function getVorlagen()
	{
		//get oe of user
		$uid = getAuthUID();
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$result = $this->BenutzerfunktionModel->getBenutzerfunktionByUid($uid, 'oezuordnung');

		if (hasData($result))
		{
			$this->load->model('system/Vorlage_model', 'VorlageModel');

			$data = getData($result);

			$oe_kurzbz = array_column($data, 'oe_kurzbz');
			$result = $this->VorlageModel->getAllVorlagenByOe($oe_kurzbz);

			$this->terminateWithSuccess(hasData($result) ? getData($result) : array());
		}

		$this->terminateWithSuccess(array());
	}

	public function getDataVorlage($vorlage_kurzbz)
	{
		$studiengang_kz = 0;
		$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
		$this->VorlagestudiengangModel->addOrder('version', 'DESC');

		$result = $this->VorlagestudiengangModel->loadWhere(
			[
				'vorlage_kurzbz' =>$vorlage_kurzbz,
				'studiengang_kz' => $studiengang_kz
			]);

		$data = $this->getDataOrTerminateWithError($result);
		$vorlage = current($data);
		$this->terminateWithSuccess($vorlage);
	}

	public function getMessageVarsPerson($typeId)
	{
		$ids = $this->input->post('ids');
		$messageVarsPerson = [];

		foreach ($ids as $id)
		{
			$person_id = ($typeId == 'mitarbeiter_uid') ? $this->_getPersonId($id, $typeId) : $id;
			$result = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
			$data = $this->getDataOrTerminateWithError($result);
			$messageVarsPerson[] = current($data);
		}

		$this->terminateWithSuccess($messageVarsPerson);
	}

	public function getMsgVarsPrestudent($typeId)
	{
		$ids = $this->input->post('ids');
		if(!is_array($ids)) {
			$ids = array($ids);
		}
		$messageVarsPrestudent = [];

		if($typeId == 'uid')
		{
			$prestudent_ids = [];
			foreach ($ids as $id)
			{
				$prestudent_ids[] =  $this->_getPrestudentIdFromUid($id);
			}
		}
		else
			$prestudent_ids = $ids;

		foreach ($prestudent_ids as $prestudent_id)
		{
			$result = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
			$data = $this->getDataOrTerminateWithError($result);
			$messageVarsPrestudent[] = current($data);
		}

		$this->terminateWithSuccess($messageVarsPrestudent);
	}

	public function getMsgVarsLoggedInUser()
	{
		$result = $this->MessageModel->getMsgVarsLoggedInUser();
		$data = $this->getDataOrTerminateWithError($result);

		$this->terminateWithSuccess($data);
	}

/*	public function getNameOfDefaultRecipient($id, $type_id)
	{
		$id = ($type_id != 'person_id') ? $this->_getPersonId($id, $type_id) : $id;

		$this->load->model('person/Person_model', 'PersonModel');

		$result = $this->PersonModel->load($id);
		$data = $this->getDataOrTerminateWithError($result);
		$name = current($data);

		$this->terminateWithSuccess($name->vorname . " " . $name->nachname );
	}*/

	public function getNameOfDefaultRecipients($type_id)
	{
		$ids = $this->input->post('ids');
		if(!is_array($ids)) {
			$ids = array($ids);
		}
		$recipients = [];

		if (empty($ids)) {
			throw new InvalidArgumentException("Keine ID(s) übergeben.");
		}

		$this->load->model('person/Person_model', 'PersonModel');
		if($type_id != 'person_id'){
			foreach ($ids as $id)
			{
				$person_id =  $this->_getPersonId($id, $type_id);
				$result = $this->PersonModel->load($person_id);
				$data = $this->getDataOrTerminateWithError($result);
				$name = current($data);
				$recipients[$id] = $name->vorname . " " . $name->nachname;
			}
		}
		else {
			foreach ($ids as $id) {
				$result = $this->PersonModel->load($id);
				$data = $this->getDataOrTerminateWithError($result);
				$name = current($data);
				$recipients[$id] = $name->vorname . " " . $name->nachname;
			}
		}

		$this->terminateWithSuccess($recipients);
	}

	public function sendMessage($typeId)
	{
		$resultReturn = [];
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
		$relationmessage_id = $this->input->post('relationmessage_id');

		if (isset($_POST['ids']))
		{
			$ids = json_decode($_POST['ids']);
			unset($_POST['ids']);
			foreach ($data as $k => $v) {
				$_POST[$k] = $v;
			}
		}

		foreach ($ids as $id)
		{
			$receiversPersonId = $typeId == "person_id" ? $id : $this->_getPersonId($id, $typeId);

			if($typeId == 'uid')
			{
				$prestudent_id = $this-> _getPrestudentIdFromUid($id);

				$result = $this->MessagesModel->parseMessageTextPrestudent($prestudent_id, $body);
				$bodyParsed = $this->getDataOrTerminateWithError($result);
			}
			if($typeId == 'mitarbeiter_uid')
			{
				$person_id = $this->_getPersonId($id, $typeId);

				$result = $this->MessagesModel->parseMessageTextPerson($person_id, $body);
				$bodyParsed = $this->getDataOrTerminateWithError($result);
			}
			elseif($typeId == 'person_id')
			{
				$result = $this->MessagesModel->parseMessageTextPerson($id, $body);
				$bodyParsed = $this->getDataOrTerminateWithError($result);
			}
			elseif($typeId == 'prestudent_id')
			{
				$result = $this->MessagesModel->parseMessageTextPrestudent($id, $body);
				$bodyParsed = $this->getDataOrTerminateWithError($result);
			}
			else
			{
				$this->terminateWithError("type_id " . $typeId . " not valid", self::ERROR_TYPE_GENERAL);
			}

			$result =$this->messagelib->sendMessageUser($receiversPersonId, $subject, $bodyParsed, $benutzer->person_id, null, $relationmessage_id);
			$data = $this->getDataOrTerminateWithError($result);
			$resultReturn[] = current($data);

		}
		$this->terminateWithSuccess($resultReturn);
	}

	public function getPreviewText($type_id)
	{
		if (isset($_POST['data']))
		{
			$data = json_decode($_POST['data']);
			unset($_POST['data']);
		}
		else
			$this->terminateWithError("Textbody missing ", self::ERROR_TYPE_GENERAL);

		if (isset($_POST['ids']))
		{
			$ids = json_decode($_POST['ids']);
			if(!is_array($ids))
			{
				$ids = array($ids);
			}
			unset($_POST['ids']);
		}
		else
			$this->terminateWithError("IDs missing ", self::ERROR_TYPE_GENERAL);

		$bodyParsed = [];

		foreach ($ids as $id)
		{
			switch($type_id)
			{
				case 'uid':
					$prestudent_id = $this->_getPrestudentIdFromUid($id);
					$result = $this->MessagesModel->parseMessageTextPrestudent($prestudent_id, $data);
					$bodyParsed[$id] = $this->getDataOrTerminateWithError($result);
					break;
				case 'prestudent_id':
					$result = $this->MessagesModel->parseMessageTextPrestudent($id, $data);
					$bodyParsed[$id] = $this->getDataOrTerminateWithError($result);
					break;
				case 'person_id':
					$result = $this->MessagesModel->parseMessageTextPerson($id, $data);
					$bodyParsed[$id] = $this->getDataOrTerminateWithError($result);
					break;
				case 'mitarbeiter_uid':
					{
						$person_id = $this->_getPersonId($id, $type_id);
						$result = $this->MessagesModel->parseMessageTextPerson($person_id, $data);
						$bodyParsed[$id] = $this->getDataOrTerminateWithError($result);
					}
					break;
				default:
					$this->terminateWithError("MESSAGES::getPreviewText logic for type_id " . $type_id . " not defined yet", self::ERROR_TYPE_GENERAL);
					break;
			}
		}

		$this->terminateWithSuccess($bodyParsed);
	}

	public function getReplyData($messageId)
	{
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

		$dataMessage = $this->getDataOrTerminateWithError($result);
		$prefix = "Re: "; // reply subject prefix

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

	public function getPersonId($id, $typeId)
	{
		if ($typeId == 'uid' || $typeId == 'mitarbeiter_uid')
		{
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$result = $this->BenutzerModel->loadWhere(
				['uid' => $id]
			);
		}
		elseif($typeId == 'prestudent_id')
		{
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->loadWhere(
				['prestudent_id' => $id]
			);
		}

		$data = $this->getDataOrTerminateWithError($result);
		$person = current($data);

		$this->terminateWithSuccess($person->person_id);
	}

	public function getUid($typeId)
	{
		if (!$typeId)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Type ID']), self::ERROR_TYPE_GENERAL);
		}
		elseif ($typeId == 'person_id')
		{
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$result = $this->BenutzerModel->loadWhere(
				['person_id' => $id]
			);
		}
		elseif($typeId == 'prestudent_id')
		{
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->loadWhere(
				['prestudent_id' => $id]
			);

			$data = $this->getDataOrTerminateWithError($result);
			$person = current($data);
			$person_id = $person->person_id;

			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$result = $this->BenutzerModel->loadWhere(
				['person_id' => $person_id]
			);
		}
		elseif($typeId == 'uid' || $typeId == 'mitarbeiter_uid')
		{
			$this->terminateWithSuccess($id);
		}
		else
		{
			$this->terminateWithError("MESSAGES::getUID logic for type_id " . $typeId . " not defined yet", self::ERROR_TYPE_GENERAL);
		}

		$data = $this->getDataOrTerminateWithError($result);
		$benutzer = current($data);

		$this->terminateWithSuccess($benutzer->uid);
	}

	public function getUids($typeId)
	{
		$ids = $this->input->post('ids');
		$benutzerIds = [];

		if (!$typeId)
		{
			$this->terminateWithError($this->p->t('ui', 'error_missingId', ['id'=> 'Type ID']), self::ERROR_TYPE_GENERAL);
		}
		elseif ($typeId == 'person_id')
		{
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			foreach ($ids as $id)
			{
				$result = $this->BenutzerModel->loadWhere(
					['person_id' => $id]
				);
				$data = $this->getDataOrTerminateWithError($result);
				$benutzer = current($data);

				$benutzerIds[$id] = $benutzer->uid;
			}
		}
		elseif($typeId == 'prestudent_id')
		{
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			foreach ($ids as $id)
			{
				$result = $this->PrestudentModel->loadWhere(
					['prestudent_id' => $id]
				);

				$data = $this->getDataOrTerminateWithError($result);
				$person = current($data);
				$person_id = $person->person_id;

				$this->load->model('person/Benutzer_model', 'BenutzerModel');
				$result = $this->BenutzerModel->loadWhere(
					['person_id' => $person_id]
				);
				$data = $this->getDataOrTerminateWithError($result);
				$benutzer = current($data);

				$benutzerIds[$id] = $benutzer->uid;
			}
		}
		elseif($typeId == 'uid' || $typeId == 'mitarbeiter_uid')
		{
			$this->terminateWithSuccess($ids);
		}
		else
		{
			$this->terminateWithError("MESSAGES::getUID logic for type_id " . $typeId . " not defined yet", self::ERROR_TYPE_GENERAL);
		}


		//$data = $this->getDataOrTerminateWithError($resultBenutzer);
		//$benutzer = current($data);

		$this->terminateWithSuccess($benutzerIds);
	}

	private function _getPersonId($id, $typeId)
	{
		if ($typeId == 'uid' || $typeId == 'mitarbeiter_uid')
		{
			$this->load->model('person/Benutzer_model', 'BenutzerModel');
			$result = $this->BenutzerModel->loadWhere(
				['uid' => $id]
			);
		}
		elseif($typeId == 'prestudent_id')
		{
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->loadWhere(
				['prestudent_id' => $id]
			);
		}

		$data = $this->getDataOrTerminateWithError($result);
		if (count($data) < 1)
		{
			$this->terminateWithError('Error: Messages API no person_id found.');
		}
		$person = current($data);

		return $person->person_id;
	}

	private function _getPrestudentIdFromUid($uid)
	{
		//	$this->terminateWithError($uid, self::ERROR_TYPE_GENERAL);
		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->loadWhere(
			['student_uid' => $uid]
		);

		$data = $this->getDataOrTerminateWithError($result);
		if (count($data) < 1)
		{
			$this->terminateWithError('Error: Messages API no prestudent_id found.');
		}
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
