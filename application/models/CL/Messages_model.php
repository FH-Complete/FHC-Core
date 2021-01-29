<?php

/**
 * Messages GUI logic
 * - This model extends CI_Model because here is just implemented logic
 * - It does not represent a resource (ex. like models that extend DB_Model)
 */
class Messages_model extends CI_Model
{
	const REPLY_SUBJECT_PREFIX = 'Re: '; // reply subject prefix
	// To quote a reply body message
	const REPLY_BODY_FORMAT = '<br>
		<br>
		<blockquote>
			<i>
				On %s %s %s wrote:
			</i>
		</blockquote>
		<blockquote style="border-left:2px solid; padding-left: 8px">
			%s
		</blockquote>';

	const NO_AUTH_UID = 'online'; // hard coded uid if no authentication is performed

	// Recipients types
	const TYPE_PERSONS = 'persons';
	const TYPE_PRESTUDENTS = 'prestudents';

	const ALT_OE = 'infocenter'; // alternative organisation unit when no one is found for a presetudent

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads the message library
		$this->load->library('MessageLib'); // MessageModel loaded here!
		// Loads the person log library
		$this->load->library('PersonLogLib');
		// Loads the widget library
		$this->load->library('WidgetLib');

		// Loads model MessageToken_model
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
		// Loads model Benutzerrolle_model
		$this->load->model('system/Benutzerrolle_model', 'BenutzerrolleModel');
		// Loads model Prestudent_model
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		// Loads model Benutzer_model
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * Set a message as read by its id ($message_id + $person_id)
	 */
	public function setMessageRead($message_id, $person_id)
	{
		// Checks parameters
		if (!is_numeric($message_id) || !is_numeric($person_id)) return error('Invalid setMessageRead parameters');

		// Loads needed models
		$this->load->model('system/MsgStatus_model', 'MsgStatusModel');

		$statuResult = $this->MsgStatusModel->loadWhere(
			array(
				'message_id' => $message_id,
				'person_id' => $person_id,
				'status' => MSG_STATUS_READ
			)
		);

		if (isError($statuResult)) return $statuResult;
		if (!hasData($statuResult))
		{
			// Set date used to insert
			return $this->MsgStatusModel->insert(
				array(
					'message_id' => $message_id,
					'person_id' => $person_id,
					'status' => MSG_STATUS_READ,
					'insertvon' => getAuthUID()
				)
			); // insert and return result
		}
		else
		{
			return success('Already set as read');
		}
	}

	/**
	 * Prepares data for the view system/messages/ajaxRead
	 */
	public function prepareAjaxRead()
	{
		$psResult = $this->PrestudentModel->loadWhere(array('person_id' => getAuthPersonId()));

		if (isError($psResult)) show_error('An error occurred while loading this page, please contact the site administrator');

		if (hasData($psResult))
		{
			return array('writeButton' => '<input id="writeMessage" type="button" value="'.$this->p->t('ui', 'nachrichtSenden').'">');
		}

		return array('writeButton' => '');
	}

	/**
	 * Prepares data for the view system/messages/ajaxWrite
	 */
	public function prepareAjaxWrite()
	{
		$ouResult = $this->PrestudentModel->getOrganisationunitsByPersonId(getAuthPersonId());

		if (isError($ouResult)) show_error('An error occurred while loading this page, please contact the site administrator');

		$ouOptions = '<option value="0">Select...</option>';

		if (hasData($ouResult))
		{
			foreach (getData($ouResult) as $ou)
			{
				$ouOptions .= sprintf(
					"\n".'<option value="%s">%s</option>',
					is_numeric($ou->prestudent_id) ? $ou->oe_kurzbz : self::ALT_OE,
					$ou->bezeichnung . (is_numeric($ou->prestudent_id) ? '' : ' *')
				);
			}
		}

		return array('organisationUnitOptions' => $ouOptions);
	}

	/**
	 * Prepares data for the view system/messages/ajaxWriteReply
	 */
	public function prepareAjaxWriteReply($token)
	{
		if (isEmptyString($token)) show_error('The given token is not valid');

		// Retrieves message using the given token
		$messageResult = $this->MessageTokenModel->getMessageByToken($token);
		if (isError($messageResult)) show_error('An error occurred while loading this page, please contact the site administrator');

		if (hasData($messageResult))
		{
			$message = getData($messageResult)[0]; // Found message data

			// Retrieves message sender information
			$senderResult = $this->MessageTokenModel->getSenderData($message->sender_id);
			if (isError($senderResult)) show_error('An error occurred while loading this page, please contact the site administrator');

			if (hasData($senderResult))
			{
				$sender = getData($senderResult)[0]; // Found sender data

				$replySubject = self::REPLY_SUBJECT_PREFIX.$message->subject;
				$replyBody = $this->_getReplyBody($message->body, $sender->vorname, $sender->nachname, $message->sent);

				return array (
					'receiver' => $sender->vorname.' '.$sender->nachname, // yep! the sender of the sent message is the receiver of the reply message
					'subject' => $replySubject,
					'body' => $replyBody,
					'receiver_id' => $message->sender_id,
					'relationmessage_id' => $message->message_id,
					'token' => $token
				);
			}
		}
	}

	/**
	 * Prepares data for the view system/messages/ajaxRead
	 * If everything is fine returns a list of received messages (objects)
	 */
	public function prepareAjaxReadReceived()
	{
		// Retrieves received messages for the logged user and its organisation units
		$receivedMessagesResult = $this->RecipientModel->getReceivedMessages(
			getAuthPersonId(),
			$this->config->item(MessageLib::CFG_OU_RECEIVERS)
		);
		// If an error occurred return it
		if (isError($receivedMessagesResult)) return $receivedMessagesResult;

		// If data were found
		if (hasData($receivedMessagesResult))
		{
			$jsonArray = array(); // array that contains all the received messages

			// Collect'em all in the array $jsonArray
			foreach (getData($receivedMessagesResult) as $receivedMessage)
			{
				$jsonRecord = new stdClass();
				$jsonRecord->message_id = $receivedMessage->message_id;
				$jsonRecord->subject = $receivedMessage->subject;
				$jsonRecord->body = $receivedMessage->body;
				$jsonRecord->from = $receivedMessage->vorname.' '.$receivedMessage->nachname;
				$sentDate = new DateTime($receivedMessage->sent);
				$jsonRecord->sent = $sentDate->format('d/m/Y H:i:s');
				$jsonRecord->status = $receivedMessage->status;
				$jsonRecord->statusPersonId = $receivedMessage->statuspersonid;
				$jsonRecord->token = $receivedMessage->token;

				$jsonArray[] = $jsonRecord;
			}

			return success(json_encode($jsonArray)); // return as an json encoded string
		}

		return success('No messages were found'); // NOT a blocking error
	}

	/**
	 * Prepares data for the view system/messages/ajaxRead
	 * If everything is fine returns a list of sent messages (objects)
	 */
	public function prepareAjaxReadSent()
	{
		// Retrieves sent messages from the logged user
		$sentMessagesResult = $this->RecipientModel->getSentMessages(getAuthPersonId());
		if (isError($sentMessagesResult)) return $sentMessagesResult; // If an error occurred return it

		if (hasData($sentMessagesResult))
		{
			$jsonArray = array();// array that contains all the sent messages

			// Collect'em all in the array $jsonArray
			foreach (getData($sentMessagesResult) as $sentMessage)
			{
				$jsonRecord = new stdClass();
				$jsonRecord->message_id = $sentMessage->message_id;
				$jsonRecord->subject = $sentMessage->subject;
				$jsonRecord->body = $sentMessage->body;
				$sentDate = new DateTime($sentMessage->sent);
				$jsonRecord->sent = $sentDate->format('d/m/Y H:i:s');
				$jsonRecord->status = $sentMessage->status;
				$jsonRecord->statusPersonId = $sentMessage->statuspersonid;
				$jsonRecord->token = $sentMessage->token;

				if ($sentMessage->person_id == $this->config->item(MessageLib::CFG_SYSTEM_PERSON_ID))
				{
					$jsonRecord->to = $sentMessage->oe;
				}
				else
				{
					$jsonRecord->to = $sentMessage->vorname.' '.$sentMessage->nachname;
				}

				$jsonArray[] = $jsonRecord;
			}

			return success(json_encode($jsonArray)); // return as an json encoded string
		}

		return success('No messages were found'); // NOT a blocking error
	}

	/**
	 * Prepares data for the view system/messages/htmlRead using a token that identifies a single message
	 */
	public function prepareHtmlRead($token)
	{
		if (isEmptyString($token)) show_error('The given token is not valid');

		// Retrieves message using the given token
		$messageResult = $this->MessageTokenModel->getMessageByToken($token);
		if (isError($messageResult)) show_error(getError($messageResult));
		if (!hasData($messageResult)) show_error('No message found with the given token');

		$message = getData($messageResult)[0]; // Found message data

		// Set message as read
		$srmsbtResult = $this->MessageTokenModel->setReadMessageStatusByToken($token);
		if (isError($srmsbtResult)) show_error(getError($srmsbtResult));

		// Retrieves message sender information
		$senderResult = $this->MessageTokenModel->getSenderData($message->sender_id);
		if (isError($senderResult)) show_error(getError($senderResult));
		if (!hasData($senderResult)) show_error('No sender information found');

		$sender = getData($senderResult)[0]; // Found sender data

		// If the sender is not the system sender and the receiver is not the system sender
		// and are present configurations to reply
		$hrefReply = '';
		if ($message->sender_id != $this->config->item(MessageLib::CFG_SYSTEM_PERSON_ID)
			&& $message->receiver_id != $this->config->item(MessageLib::CFG_SYSTEM_PERSON_ID)
			&& !isEmptyString($this->config->item(MessageLib::CFG_REDIRECT_VIEW_MESSAGE_URL)))
		{
			$hrefReply = $this->config->item(MessageLib::CFG_MESSAGE_SERVER).
				$this->config->item(MessageLib::CFG_REDIRECT_VIEW_MESSAGE_URL).
				$token;
		}

		// If the receiver is the system sender (the message was sent to an organization unit)
		// redirect the reply to an authenticated controller to reply
		if ($message->receiver_id == $this->config->item(MessageLib::CFG_SYSTEM_PERSON_ID))
		{
			$hrefReply = site_url('system/messages/MessageClient/writeReply?token='.$token);
		}

		return array (
			'sender' => $sender,
			'message' => $message,
			'hrefReply' => $hrefReply
		);
	}

	/**
	 * Prepares data for the view system/messages/htmlWriteReply using a token that identifies a single message
	 */
	public function prepareHtmlWriteReply($token)
	{
		if (isEmptyString($token)) show_error('The given token is not valid');

		// Retrieves message using the given token
		$messageResult = $this->MessageTokenModel->getMessageByToken($token);
		if (isError($messageResult)) show_error(getError($messageResult));
		if (!hasData($messageResult)) show_error('No message found with the given token');

		$message = getData($messageResult)[0]; // Found message data

		// Retrieves message sender information
		$senderResult = $this->MessageTokenModel->getSenderData($message->sender_id);
		if (isError($senderResult)) show_error(getError($senderResult));
		if (!hasData($senderResult)) show_error('No sender information found');

		$sender = getData($senderResult)[0]; // Found sender data

		$replySubject = self::REPLY_SUBJECT_PREFIX.$message->subject;
		$replyBody = $this->_getReplyBody($message->body, $sender->vorname, $sender->nachname, $message->sent);

		return array (
			'receiver' => $sender->vorname.' '.$sender->nachname, // yep! the sender of the sent message is the receiver of the reply message
			'subject' => $replySubject,
			'body' => $replyBody,
			'receiver_id' => $message->sender_id,
			'relationmessage_id' => $message->message_id,
			'token' => $token
		);
	}

	/**
	 * Prepares data for the view system/messages/htmlWriteTemplate using person ids as main parameter
	 * Wrap method to _prepareHtmlWriteTemplate
	 */
	public function prepareHtmlWriteTemplatePersons($persons, $message_id = null, $recipient_id = null)
	{
		// Retrieves persons information
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($persons);

		return $this->_prepareHtmlWriteTemplate($msgVarsData, $message_id, $recipient_id);
	}

	/**
	 * Prepares data for the view system/messages/htmlWriteTemplate using prestudent ids as main parameter
	 * Wrap method to _prepareHtmlWriteTemplate
	 */
	public function prepareHtmlWriteTemplatePrestudents($prestudents, $message_id = null, $recipient_id = null)
	{
		// Retrieves prestudents information
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudents);

		return $this->_prepareHtmlWriteTemplate($msgVarsData, $message_id, $recipient_id);
	}

	/**
	 * Sends a new message or a reply to a message (if $relationmessage_id is given)
	 * using the template stored in the subject and body
	 */
	public function sendImplicitTemplate($type, $recipients_ids, $subject, $body, $relationmessage_id = null)
	{
		// Retrieves the sender id
		$sender_id = getAuthPersonId();
		if (!is_numeric($sender_id)) show_error('The current logged user person_id is not defined');

		$msgVarsData = error('No persons nor prestudents were provided');
		// Retrieves message vars data for the given user/s
		if ($type == self::TYPE_PERSONS) // if persons were given
		{
			$msgVarsData = $this->MessageModel->getMsgVarsDataByPersonId($recipients_ids);
		}
		elseif ($type == self::TYPE_PRESTUDENTS) // otherwise prestudents were given
		{
			$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($recipients_ids);

			// Retrieve organisation unit for the recipients
			$organisationUnitsResult = $this->PrestudentModel->getOrganisationunits($recipients_ids);
			if (isError($organisationUnitsResult)) return $organisationUnitsResult;
			if (hasData($organisationUnitsResult)) $senderOUArray = getData($organisationUnitsResult);
		}
		if (isError($msgVarsData)) show_error(getError($msgVarsData));
		if (!hasData($msgVarsData)) show_error('No recipients were given');

		$senderOU = null; // sender organisation unit only for presetudents
		$receiversCounter = 0; // a counter

		// Looping on receivers data
		foreach (getData($msgVarsData) as $receiver)
		{
			// Merge receivers data with logged in user data
			$msgVarsDataArray = $this->_addMsgVarsDataOfLoggedInUser($receiver);
	
			$msgVarsDataArray = $this->_lowerReplaceSpaceArrayKeys((array)getData($msgVarsDataArray)[0]); // replaces array keys
			$parsedSubject = parseText($subject, $msgVarsDataArray);
			$parsedBody = parseText($body, $msgVarsDataArray);

			// If exist an organisation unit for this prestudent and it is valid
			if (isset($senderOUArray[$receiversCounter])
				&& isset($senderOUArray[$receiversCounter]->oe_kurzbz)
				&& !isEmptyString($senderOUArray[$receiversCounter]->oe_kurzbz))
			{
				$senderOU = $senderOUArray[$receiversCounter]->oe_kurzbz;
			}
			else
			{
				$senderOU = null;
			}

			$message = $this->messagelib->sendMessageUser(
				$msgVarsDataArray['person_id'],	// receiverPersonId
				$parsedSubject,			// subject
				$parsedBody,			// body
				$sender_id,			// sender_id
				$senderOU,			// senderOU
				$relationmessage_id,		// relationmessage_id
				MSG_PRIORITY_NORMAL		// priority
			);

			if (isError($message)) return $message;
			if (!hasData($message)) return error('No messages were saved in database');

			// Write log entry only
			$personLog = $this->_personLog($sender_id, $msgVarsDataArray['person_id'], getData($message)[0]);
			if (isError($personLog)) return $personLog;

			$receiversCounter++; // increment the counter
		}

		return success('Messages sent successfully');
	}
	
	/**
	 * Wrapper method for sendExplicitTemplateSenderId
	 * The sender id is retrieved from the authentication session, if not present an error would be raised
	 */
	public function sendExplicitTemplate($prestudents, $oe_kurzbz, $vorlage_kurzbz, $msgVars)
	{
		// Retrieves the sender id
		$sender_id = getAuthPersonId();
		if (!is_numeric($sender_id)) show_error('The current logged user person_id is not defined');

		return $this->sendExplicitTemplateSenderId($sender_id, $prestudents, $oe_kurzbz, $vorlage_kurzbz, $msgVars);
	}
	
	/**
	 * Sends a new message using the given template and information present in parameter prestudents
	 * Extra variables can be added using parameter $msgVars
	 */
	public function sendExplicitTemplateSenderId($sender_id, $prestudents, $oe_kurzbz, $vorlage_kurzbz, $msgVars)
	{
		// Retrieves message vars data for the given user/s
		$msgVarsData = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudents);
		if (isError($msgVarsData)) show_error(getError($msgVarsData));
		if (!hasData($msgVarsData)) show_error('No recipients were given');

		$prestudentsData = $this->PrestudentModel->getOrganisationunits($prestudents);
		
		// Get the senders uid (if user is an active employee)
		$this->BenutzerModel->addSelect('uid');
		$this->BenutzerModel->addJoin('public.tbl_mitarbeiter ma', 'ma.mitarbeiter_uid = uid');
		if (!$result = getData($this->BenutzerModel->getFromPersonId($sender_id)))
		{
			show_error('No sender_uid found');
		}
		$sender_uid = $result[0]->uid;

		// Adds the organisation unit to each prestudent
		if (isEmptyString($oe_kurzbz) && hasData($msgVarsData) && hasData($prestudentsData))
		{
			$this->CLMessagesModel->_addOeToPrestudents($msgVarsData, $prestudentsData);
		}

		foreach (getData($msgVarsData) as $receiver)
		{
			/**
			 * Merge receivers data with senders data
			 * NOTE: _addMsgVarsDataOfLoggedInUser usually retrieves data of the logged in user that is set in the 
			 * templates user fields. As sendExplicitTemplateSenderId is run by a job, a sender uid is passed to be used 
			 * instead the logged in user.
			 */
			$msgVarsDataArray = $this->_addMsgVarsDataOfLoggedInUser($receiver, $sender_uid);

			$msgVarsDataArray = $this->_lowerReplaceSpaceArrayKeys((array)getData($msgVarsDataArray)[0]); // replaces array keys

			// Additional message variables
			if (is_array($msgVars)) $msgVarsDataArray = array_merge($msgVarsDataArray, $msgVars);

			$message = $this->messagelib->sendMessageUserTemplate(
				$msgVarsDataArray['person_id'],	// receiversPersonId
				$vorlage_kurzbz,		// vorlage
				$msgVarsDataArray,		// parseData
				null,				// orgform
				$sender_id,			// sender_id
				$oe_kurzbz			// senderOU
			);

			if (isError($message)) return $message;

			// Write log entry
			$personLog = $this->_personLog($sender_id, $msgVarsDataArray['person_id'], getData($message)[0]);
			if (isError($personLog)) return $personLog;
		}

		return success('Messages sent successfully');
	}

	/**
	 * Send a reply to a single recipient for a message identified by a token (no templates are used)
	 * NOTE: this method could be also called from not authenticated controllers
	 */
	public function sendReply($receiver_id, $subject, $body, $relationmessage_id, $token)
	{
		// Retrieves message sender information
		$senderResult = $this->MessageTokenModel->getSenderData($receiver_id);
		if (isError($senderResult)) show_error(getError($senderResult));
		if (!hasData($senderResult)) show_error('No sender information found');

		$sender = getData($senderResult)[0]; // Found sender data

		$messageResult = $this->MessageTokenModel->getMessageByToken($token);
		if (isError($messageResult)) show_error(getError($messageResult));
		// Security check! It is possible to reply only to a received message!!
		if (!hasData($messageResult) || $relationmessage_id != getData($messageResult)[0]->message_id)
		{
			show_error('An error occurred while sending your message, please contact the site administrator');
		}

		// If the user is logged then use its person id as sender id, otherwise get the receiver id of the previous message
		$sender_id = isLogged() ? getAuthPersonId() : getData($messageResult)[0]->receiver_id;
		if (!is_numeric($sender_id)) return error('The sender id is not valid');

		$message = $this->messagelib->sendMessageUser(
			$receiver_id,			// receiverPersonId
			$subject,				// subject
			$body,					// body
			$sender_id,				// sender_id, the receiver of the previous message is the sender of the current one
			null,					// senderOU
			$relationmessage_id,	// relationmessage_id
			MSG_PRIORITY_NORMAL		// priority
		);

		if (isError($message)) return $message;
		if (!hasData($message)) return error('No messages were saved in database');

		// Write log entry
		// NOTE: $receiver_id and $sender_id are switched!!! Currently this is a workaround
		$personLog = $this->_personLog($receiver_id, $sender_id, getData($message)[0]);
		if (isError($personLog)) return $personLog;

		return success('Messages sent successfully');
	}

	/**
	 * Send a message to an organisation unit
	 */
	public function sendToOrganisationUnit($receiverOU, $subject, $body)
	{
		if (isEmptyString($receiverOU)) return error('Not a valid organisation unit');
		if (isEmptyString($subject)) return error('Subject is an empty string');
		if (isEmptyString($body)) return error('Body is an empty string');

		$sender_id = getAuthPersonId();
		if (!is_numeric($sender_id)) return error('The current logged user person_id is not defined');

		$message = $this->messagelib->sendMessageOU(
			$receiverOU,			// receiverPersonId
			$subject,				// subject
			$body,					// body
			$sender_id				// sender_id
		);

		if (isError($message)) return $message;
		if (!hasData($message)) return error('No messages were saved in database');

		// Write log entry
		$personLog = $this->_personLog($sender_id, $this->config->item(MessageLib::CFG_SYSTEM_PERSON_ID), getData($message)[0], $receiverOU);
		if (isError($personLog)) return $personLog;

		return success('Messages sent successfully');
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods called by controller system/messages/Messages

	/**
	 * Returns an object that represent a template store in database
	 * If no templates are found with the given parameter or the given parameter is an empty string,
	 * then an error is returned
	 */
	public function getVorlage($vorlage_kurzbz)
	{
		$getVorlage = error('The given vorlage_kurzbz is not valid');

		if (!isEmptyString($vorlage_kurzbz))
		{
			$this->load->model('system/Vorlagestudiengang_model', 'VorlagestudiengangModel');
			$this->VorlagestudiengangModel->addOrder('version','DESC');

			$getVorlage = $this->VorlagestudiengangModel->loadWhere(array('vorlage_kurzbz' => $vorlage_kurzbz));
		}

		return $getVorlage;
	}

	/**
	 * Parse the given given text using data from the given user
	 * Use the CI parser which performs simple text substitution for pseudo-variable
	 */
	public function parseMessageTextPerson($person_id, $text)
	{
		$parseMessageText = error('The given person_id is not a valid number');

		if (is_numeric($person_id)) $parseMessageText = $this->MessageModel->getMsgVarsDataByPersonId($person_id);
		
		// Add message vars data of the logged in user
		$parseMessageText = $this->_addMsgVarsDataOfLoggedInUser($parseMessageText);

		if (hasData($parseMessageText))
		{
			$parseMessageText = success(
				parseText(
					$text,
					$this->_lowerReplaceSpaceArrayKeys((array)getData($parseMessageText)[0])
				)
			);
		}

		return $parseMessageText;
	}

	/**
	 * Parse the given given text using data from the given user
	 * Use the CI parser which performs simple text substitution for pseudo-variable
	 */
	public function parseMessageTextPrestudent($prestudent_id, $text)
	{
		$parseMessageText = error('The given prestudent_id is not a valid number');

		if (is_numeric($prestudent_id)) $parseMessageText = $this->MessageModel->getMsgVarsDataByPrestudentId($prestudent_id);
		
		// Add message vars data of the logged in user
		$parseMessageText = $this->_addMsgVarsDataOfLoggedInUser($parseMessageText);
		
		if (hasData($parseMessageText))
		{
			$parseMessageText = success(
				parseText(
					$text,
					$this->_lowerReplaceSpaceArrayKeys((array)getData($parseMessageText)[0])
				)
			);
		}

		return $parseMessageText;
	}

	/**
	 * Outputs message data for a message (identified my msg id and receiver id) in JSON format
	 */
	public function getMessageFromIds($message_id, $receiver_id)
	{
		$getMessageFromIds = error('The given message id or receiver id are not valid');

		if (is_numeric($message_id) && is_numeric($receiver_id))
		{
			$getMessageFromIds = $this->messagelib->getMessage($message_id, $receiver_id);
		}

		if (isError($getMessageFromIds) || !hasData($getMessageFromIds))
		{
			return array();
		}
		else
		{
			return array(getData($getMessageFromIds)[0]);
		}
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Replaces data array keys to a lowercase string with underscores instead of spaces
	 */
	private function _lowerReplaceSpaceArrayKeys($data)
	{
		$tmpData = array();

		foreach ($data as $key => $val)
		{
			$tmpData[str_replace(' ', '_', strtolower($key))] = $val;
		}

		return $tmpData;
	}

	/**
	 * Add organisation unit to an array of prestudents (objects)
	 */
	private function _addOeToPrestudents(&$msgVarsData, $prestudentsData)
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

	/**
	 * Perform a person log after a message is sent
	 */
	private function _personLog($sender_id, $receiver_id, $message_id, $receiverOU = null)
	{
		// In case the message is accessed via ViewMessage controller -> no authentication
		// If no authentication is performed then use a hard coded uid
		$loggedUserUID = isLogged() ? getAuthUID() : self::NO_AUTH_UID;

		$message = 'Message sent from person '.$sender_id.' to '.$receiver_id.', message id: '.$message_id;
		if (!isEmptyString($receiverOU)) $message .= ', receiverOU: '.$receiverOU;

		return $this->personloglib->log(
			$receiver_id,
			'Action',
			array(
				'name' => 'Message sent',
				'message' => $message,
				'success' => 'true'
			),
			'kommunikation',
			'core',
			null,
			$loggedUserUID
		);
	}

	/**
	 * Quotes the previous message body
	 */
	private function _getReplyBody($body, $receiverName, $receiverSurname, $sentDate)
	{
		return sprintf(
			self::REPLY_BODY_FORMAT,
			date_format(date_create($sentDate), 'd.m.Y H:i'), $receiverName, $receiverSurname, $body
		);
	}

	/**
	 * Prepares data for the view system/messages/htmlWriteTemplate using the given parameters
	 */
	private function _prepareHtmlWriteTemplate($info, $message_id, $recipient_id)
	{
		// Checks that info parameter is valid
		if (isError($info)) show_error(getError($info));
		if (!hasData($info)) show_error('No recipients were given');

		// If the message id and recipient id are given, then both they must be valid numbers
		if ((is_numeric($message_id) && !is_numeric($recipient_id))
			|| (!is_numeric($message_id) && is_numeric($recipient_id)))
		{
			show_error('If given, message id and recipient id both must be valid numbers');
		}

		// ---------------------------------------------------------------------------------------
		// Retrieves the recipients information and builds:
		// - recipientsArray: an array that contains objects with id (person_id) and description (Vorname + Nachname) of recipient
		// - recipientsList: a string that contains all the recipients descriptions (Vorname + Nachname) separated by ;
		// - persons: a string that contains HTML input hidden with alla the receivers id (person_id)
		$recipientsArray = array();
		$recipientsList = '';
		$recipients_ids = '';

		foreach (getData($info) as $receiver)
		{
			$id = 0;
			$recipient = new stdClass();
			$recipient->description = $receiver->Vorname.' '.$receiver->Nachname;
			$recipientsList .= $receiver->Vorname.' '.$receiver->Nachname.'; ';

			// If it is a prestudent then
			if (isset($receiver->prestudent_id) && is_numeric($receiver->prestudent_id))
			{
				$recipient->id = $receiver->prestudent_id;
				$id = $receiver->prestudent_id;
			}
			else // otherwise it is a person
			{
				$recipient->id = $receiver->person_id;
				$id = $receiver->person_id;
			}

			$recipients_ids .= '<input type="hidden" name="recipients_ids[]" value="'.$id.'">'."\n";

			$recipientsArray[] = $recipient;
		}

		// ---------------------------------------------------------------------------------------
		// Retrieves the message to reply to, if it is specified by parameters $message_id and $recipient_id
		$replySubject = ''; // message reply subject
		$replyBody = ''; // message reply body
		$relationmessage = ''; // input hidden that contains the message id to be replied to
		// If both are given and they are valid
		if (is_numeric($message_id) && is_numeric($recipient_id))
		{
			// Retrieves a received message from tbl_msg_recipient
			$messageResult = $this->messagelib->getMessage($message_id, $recipient_id);
			if (isError($messageResult)) show_error(getError($messageResult));
			if (!hasData($messageResult)) show_error('The selected message does not exist');

			$message = getData($messageResult)[0];

			$replySubject = self::REPLY_SUBJECT_PREFIX.$message->subject;
			$replyBody = $this->_getReplyBody($message->body, $receiver->Vorname, $receiver->Nachname, $message->sent);
			$relationmessage = '<input type="hidden" name="relationmessage_id" value="'.$message_id.'">';
		}

		// ---------------------------------------------------------------------------------------
		// Retrieves message vars from database view vw_msg_vars/vw_msg_vars_person
		$variablesResult = null;
		$type = '';

		// If data contains a prestudent id
		// NOTE:
		// - info is checked at the beginning of this method so it is safe to use getData($info)[0]
		// - the provided data inside info are all persons or all prestudents, so it is safe to check only the first one
		if (isset(getData($info)[0]->prestudent_id) && is_numeric(getData($info)[0]->prestudent_id))
		{
			$variablesResult = $this->messagelib->getMessageVarsPrestudent();
			$type = '<input type="hidden" id="type" name="type" value="'.self::TYPE_PRESTUDENTS.'">';
		}
		else
		{
			$variablesResult = $this->messagelib->getMessageVarsPerson();
			$type = '<input type="hidden" id="type" name="type" value="'.self::TYPE_PERSONS.'">';
		}
		if (isError($variablesResult)) show_error(getError($variablesResult));

		// Then builds an array that contains objects with id (person_id) and description (Vorname + Nachname) of recipient
		$variables = array();
		foreach (getData($variablesResult) as $id => $description)
		{
			$tmpVar = new stdClass();
			$tmpVar->id = $id;
			$tmpVar->description = $description;

			$variables[] = $tmpVar;
		}
		
		// ---------------------------------------------------------------------------------------
		// Retrieves message vars of logged in user from database view vw_msg_vars_person
		$result = null;
		
		// If data contains a prestudent id
		$result = $this->messagelib->getMessageVarsLoggedInUser();
		
		if (isError($result)) show_error(getError($result));

		// Then builds an array that contains objects with field name and field description of logged in user data
		$user_fields = array();
		foreach (getData($result) as $id => $description)
		{
			$obj = new stdClass();
			$obj->id = $id;
			$obj->description = $description;
			
			$user_fields[] = $obj;
		}

		// ---------------------------------------------------------------------------------------
		// Retrieves the sender id
		$sender_id = getAuthPersonId();
		if (!is_numeric($sender_id)) show_error('The current logged user person_id is not defined');

		// ---------------------------------------------------------------------------------------
 		// Organisation units and a boolean (true if the sender is administrator) are used to get the templates
		$organisationUnits = $this->messagelib->getOeKurzbz($sender_id);
		if (isError($organisationUnits)) show_error(getError($organisationUnits));
		$senderIsAdmin = $this->BenutzerrolleModel->isAdminByPersonId($sender_id);
		if (isError($senderIsAdmin)) show_error(getError($senderIsAdmin));

		// ---------------------------------------------------------------------------------------
		// Returns data as an array
		return array (
			'recipientsList' => $recipientsList,
			'subject' => $replySubject,
			'body' => $replyBody,
			'variables' => $variables,
			'user_fields' => $user_fields,
			'organisationUnits' => getData($organisationUnits),
			'senderIsAdmin' => getData($senderIsAdmin),
			'recipientsArray' => $recipientsArray,
			'recipients_ids' => $recipients_ids,
			'relationmessage_id' => $relationmessage,
			'type' => $type
		);
	}
	
	/**
	 * Adds message vars data of the logged in user to the given object (that should also have message vars data)
	 * @param object $otherMsgVarsDataObj Can be success object or simple object.
	 * @return object Returns success object.
	 */
	public function _addMsgVarsDataOfLoggedInUser($otherMsgVarsDataObj, $uid = null)
	{
		// First check if param type is object
		if (!is_object($otherMsgVarsDataObj)) show_error('Must pass an object to merge with data of logged in user');
		
		// If it is a return object, extract the simple data object
		if (isSuccess($otherMsgVarsDataObj))
		{
			$otherMsgVarsDataObj = getData($otherMsgVarsDataObj)[0];
		}
		
		// Retrieve message vars data of the logged in user
		if (!$msgVarsDataLoggedInUser = getData($this->MessageModel->getMsgVarsDataByLoggedInUser($uid))[0])
		{
			return success($otherMsgVarsDataObj);   // If failed, return at least given object as expected success object
		}
		
		return success(array((object)(array_merge((array) $otherMsgVarsDataObj, (array) $msgVarsDataLoggedInUser))));
		
	}
}
