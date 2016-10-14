<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

/**
 * Library to manage the sending of the email
 */
class MailLib
{
	private $sended; // Sended email counter
	
	// Properties for storing the configuration
	private $email_number_to_sent;
	private $email_number_per_time_range;
	private $email_time_range;
	private $email_from_system;
	
	/**
	 * Class constructor
	 */
	public function __construct()
    {
		// Set the counter to 0
		$this->sended = 0;
		
		// Get CI instance
		$this->ci =& get_instance();
		
		// The second parameter is used to avoiding name collisions in the config array
		$this->ci->config->load("mail", true);
		
		// CI Email library
		$this->ci->load->library("email");
		
		// Initializing email library with the loaded configurations
		$this->ci->email->initialize($this->ci->config->config["mail"]);
		
		// Set the configuration properties with the standard configuration values
		$this->email_number_to_sent = $this->getEmailCfgItem("email_number_to_sent");
		$this->email_number_per_time_range = $this->getEmailCfgItem("email_number_per_time_range");
		$this->email_time_range = $this->getEmailCfgItem("email_time_range");
		$this->email_from_system = $this->getEmailCfgItem("email_from_system");
	}
	
	/**
	 * Sends a single email
	 */
	public function send($from, $to, $subject, $message, $alias = "", $cc = null, $bcc = null, $altMessage = '')
	{
		// If from is not specified then use the standard one
		if (is_null($from) || $from == "")
		{
			$from = $this->email_from_system;
		}
		
		$this->ci->email->from($from, $alias);
		
		// Check if the email address of the debug recipient is a valid one
		$recipient = $to;
		$recipientCC = $cc;
		$recipientBCC = $bcc;
		if ($this->validateEmailAddress(MAIL_DEBUG))
		{
			// if is it valid use it!!!
			$recipient = MAIL_DEBUG;
			$recipientCC = MAIL_DEBUG;
			$recipientBCC = MAIL_DEBUG;
		}
		
		$this->ci->email->to($recipient);
		if (!is_null($recipientCC)) $this->ci->email->cc($recipientCC);
		if (!is_null($recipientBCC)) $this->ci->email->bcc($recipientBCC);
		$this->ci->email->subject($subject);
		$this->ci->email->message($message);
		if (!empty($altMessage)) $this->ci->email->set_alt_message($altMessage);
		
		// Avoid printing on standard output ugly error messages
		$result = @$this->ci->email->send();
		
		// If the email was succesfully sended then increment the counter
		// and checks if it has to wait until the sending of the next
		if ($result)
		{
			$this->sended++;
			$this->wait();
		}
		
		return $result;
	}
	
	/**
	 * To ovveride the configurations
	 */
	public function overrideConfigs($cfg)
	{
		if (!is_null($cfg))
		{
			if (isset($cfg->email_number_to_sent) && is_numeric($cfg->email_number_to_sent))
			{
				$this->email_number_to_sent = $cfg->email_number_to_sent;
			}
			if (isset($cfg->email_number_per_time_range) && is_numeric($cfg->email_number_per_time_range))
			{
				$this->email_number_per_time_range = $cfg->email_number_per_time_range;
			}
			if (isset($cfg->email_time_range) && is_numeric($cfg->email_time_range))
			{
				$this->email_time_range = $cfg->email_time_range;
			}
			if (isset($cfg->email_from_system) && filter_var($cfg->email_from_system, FILTER_VALIDATE_EMAIL))
			{
				$this->email_from_system = $cfg->email_from_system;
			}
		}
	}
	
	/**
	 * Returns the current configuration
	 */
	public function getConfigs()
	{
		$cfg = new stdClass();
		$cfg->email_number_to_sent = $this->email_number_to_sent;
		$cfg->email_number_per_time_range = $this->email_number_per_time_range;
		$cfg->email_time_range = $this->email_time_range;
		$cfg->email_from_system = $this->email_from_system;
		
		return $cfg;
	}
	
	/**
	 * Validates an email address
	 */
	public function validateEmailAddress($emailAddress)
	{
		$valid = false;
		
		if (!empty($emailAddress))
		{
			$valid = filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
		}
		
		return $valid;
	}
	
	/**
	 * Checks if it has to wait until the sending of the next
	 */
	private function wait()
	{
		if ($this->sended == $this->email_number_per_time_range)
		{
			sleep($this->email_time_range); // Wait!!!
		}
	}
	
	/**
	 * Gets an item from the email configuration array
	 */
	private function getEmailCfgItem($itemName)
	{
		return $this->ci->config->item($itemName, EMAIL_CONFIG_INDEX);
	}
}