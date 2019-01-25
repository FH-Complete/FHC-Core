<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FASMessages extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'write' => 'basis/message:rw'
			)
		);

		// Loads the message library
		$this->load->library('MessageLib');

		// Loads the widget library
		$this->load->library('WidgetLib');

		$this->load->model('system/Message_model', 'MessageModel');
		$this->load->model('person/Person_model', 'PersonModel');

		$this->loadPhrases(
			array(
				'global',
				'ui'
			)
		);
	}

	/**
	 * Write
	 */
	public function write($sender_id = null, $msg_id = null, $receiver_id = null)
 	{
		$prestudent_id = $this->input->post('prestudent_id');
		$oe_kurzbz = array(); // a person may belongs to more organisation units
		$msg = null;

		if (!is_numeric($sender_id))
 		{
 			$personByUID = $this->PersonModel->getByUid(getAuthUID());

 			if (isError($personByUID) || !hasData($personByUID))
 			{
 				show_error($personByUID->retval);
 			}
			else
			{
				$sender_id = $personByUID->retval[0]->person_id;
			}
 		}

 		// Get message data if possible
 		if (is_numeric($msg_id) && is_numeric($receiver_id))
 		{
 			$msg = $this->messagelib->getMessage($msg_id, $receiver_id);
 			if (isError($msg) || !hasData($msg))
 			{
 				show_error($msg->retval);
 			}
 			else
 			{
 				$msg = $msg->retval[0];
 			}
 		}

		// Retrieves message vars from view vw_msg_vars
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
		if (isError($msgVarsData))
		{
			show_error($msgVarsData->retval);
		}

		// Retrieves message vars for a person from view view vw_msg_vars_person
		$variablesArray = $this->messagelib->getMessageVarsPerson();

 		// Organisation units used to get the templates
 		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
 		$benutzerResult = $this->BenutzerfunktionModel->getByPersonId($sender_id);
		if (isError($benutzerResult))
		{
			show_error($benutzerResult->retval);
		}
 		elseif (hasData($benutzerResult))
 		{
 			foreach ($benutzerResult->retval as $val)
 			{
 				$oe_kurzbz[] = $val->oe_kurzbz;
 			}
 		}

 		// Admin or commoner?
 		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
 		$isAdmin = $this->BenutzerrolleModel->isAdminByPersonId($sender_id);
 		if (isError($isAdmin))
 		{
 			show_error($isAdmin->retval);
 		}

 		$data = array (
 			'sender_id' => $sender_id,
 			'receivers' => $msgVarsData->retval,
 			'message' => $msg,
 			'variables' => $variablesArray,
 			'oe_kurzbz' => $oe_kurzbz, // used to get the templates
 			'isAdmin' => $isAdmin->retval,
 			'personOnly' => false // indicates if sent only to persons
 		);

 		$this->load->view('system/messageWrite', $data);
 	}
}
