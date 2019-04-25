<?php

/**
 * This model extends CI_Model because here is just implemented logic
 * It does not represent a resource (ex. like models that extend DB_Model)
 */
class Messages_model extends CI_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads the message library
		$this->load->library('MessageLib');

		// Loads the person log library
		$this->load->library('PersonLogLib');

		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
	}

	/**
	 * Executes message sending
	 * @param $sender_id
	 * @return array wether execution was successfull - error or success
	 */
	public function send($msgVarsData, $relationmessage_id = null, $oe_kurzbz = null, $vorlage_kurzbz = null, $msgVars = null)
	{
		$subject = $this->input->post('subject');
		$body = $this->input->post('body');

		$authUser = $this->getAuthUser();

		if (isError($authUser)) return $authUser;

		$sender_id = getData($authUser)[0]->person_id;

		// Send message(s)
		if (hasData($msgVarsData))
		{
			for ($i = 0; $i < count(getData($msgVarsData)); $i++)
			{
				$parsedText = "";
				$msgVarsDataArray = $this->replaceKeys((array)getData($msgVarsData)[$i]); // replaces array keys

				// Send without vorlage
				if (isEmptyString($vorlage_kurzbz))
				{
					$parsedText = $this->messagelib->parseMessageText($body, $msgVarsDataArray);
					$msg = $this->messagelib->sendMessage($sender_id, $msgVarsDataArray['person_id'], $subject, $parsedText, PRIORITY_NORMAL, $relationmessage_id, $oe_kurzbz);
				}
				// Send with vorlage
				else
				{
					if (is_array($msgVars))
					{
						// Additional message variables
						foreach ($msgVars as $key => $msgvar)
						{
							$msgVarsDataArray[$key] = $msgvar;
						}
					}
					$msg = $this->messagelib->sendMessageVorlage($sender_id, $msgVarsDataArray['person_id'], $vorlage_kurzbz, $oe_kurzbz, $msgVarsDataArray);
				}

				if (isError($msg)) return $msg;

				// Write log entry
				$personLog = $this->personloglib->log(
					$msgVarsDataArray['person_id'],
					'Action',
					array(
						'name' => 'Message sent',
						'message' => 'Message sent from person '.$sender_id.' to '.$msgVarsDataArray['person_id'].', messageid '.getData($msg),
						'success' => 'true'
					),
					'kommunikation',
					'core',
					null,
					getAuthUID()
				);

				if (isError($personLog)) return $personLog;
			}

			return success('Messages sent successfully');
		}
		else
		{
			return $msgVarsData;
		}
	}

	/**
	 * Send a reply
	 */
	public function sendReply($subject, $body, $persons, $relationmessage_id, $token)
	{
		$relationmsg = $this->MessageTokenModel->getMessageByToken($token);
		if (!hasData($relationmsg) || $relationmessage_id !== getData($relationmsg)[0]->message_id)
		{
			show_error('Error while sending reply');
		}

		// Get sender (receiver of previous msg)
		$sender_id = getData($relationmsg)[0]->receiver_id;

		// Get message data of persons
		$data = $this->MessageTokenModel->getPersonData($persons);
		if (hasData($data))
		{
			for ($i = 0; $i < count(getData($data)); $i++)
			{
				$dataArray = (array)getData($data)[$i];

				$msg = $this->messagelib->sendMessage($sender_id, $dataArray['person_id'], $subject, $body, PRIORITY_NORMAL, $relationmessage_id, null);
				if (isError($msg)) return $msg;

				// Logs person data
				$personLog = $this->personloglib->log(
					$sender_id,
					'Action',
					array(
						'name' => 'Message sent',
						'message' => 'Message sent from person '.$sender_id.' to '.$dataArray['person_id'].', messageid '.getData($msg),
						'success' => 'true'
					),
					'kommunikation',
					'core',
					null,
					'online'
				);

				// Unpark bewerber after he sends message
				$personLog = $this->personloglib->unPark($sender_id);

				if (isError($personLog)) return $personLog;
			}
		}

		return success('Reply sent');
	}

	/**
	 *
	 */
	public function getAuthUser()
	{
		$sender_id = null;

		$this->load->model('person/Person_model', 'PersonModel');
		$authUser = $this->PersonModel->getByUid(getAuthUID());

		if (!hasData($authUser)) $authUser = error('The current logged user person_id is not defined');

		return $authUser;
	}

	/**
	 *
	 */
	public function replaceKeys($data)
	{
		$tmpData = array();

		// Replaces data array keys to a lowercase without spaces string
		foreach ($data as $key => $val)
		{
			$tmpData[str_replace(' ', '_', strtolower($key))] = $val;
		}

		return $tmpData;
	}

	/**
	 *
	 */
	public function addOeToPrestudents(&$msgVarsData, $prestudentsData)
	{
		for ($i = 0; $i < count(getData($msgVarsData)); $i++)
		{
			for ($p = 0; $p < count(getData($prestudentsData)); $p++)
			{
				if (getData($prestudentsData)[$p]->prestudent_id == getData($msgVarsData)[$i]->prestudent_id)
				{
					$msgVarsData->retval[$i]->oe_kurzbz = getData($prestudentsData)[$p]->oe_kurzbz;
					break;
				}
			}
		}
	}
}
