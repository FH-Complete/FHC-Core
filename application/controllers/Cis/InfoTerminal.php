<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class InfoTerminal extends Auth_Controller
{
	private $_uid; // contains the UID of the logged user

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => self::PERM_ANONYMOUS,
		]);

		$this->load->config('theme');
		$this->load->config('info_terminal');

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');

		$this->load->helper('hlp_common_helper');

		if (!defined('CI_ENVIRONMENT') || CI_ENVIRONMENT !== 'deveslopment') 
			$this->_checkIpWhitelist();
 	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$cardNumber = $this->input->get("key_input");
		if (empty($cardNumber)) {
			return $this->load->view('Cis/InfoTerminalInitialPage.php');
		}
		
		$this->load->model('person/Person_model', 'PersonModel');
		$return = $this->PersonModel->getUIDByCardNumber($cardNumber);
		if (isError($return))
			return $this->load->view('Cis/InfoTerminalError.php', [
				"message" => 'Issue encountered! Try again later.'
			]);

		if (!hasData($return))
			return $this->load->view('Cis/InfoTerminalError.php', [
				"message" => 'Card number does not match any user'
			]);

		$uid = getData($return)[0]->uid;
		if (isEmptyString($uid))
			return $this->load->view('Cis/InfoTerminalError.php', [
				"message" => 'Card number does not match any user'
			]);

		$this->load->view('Cis/InfoTerminal.php');
	}

	/**
	 * Ends the request with a 403 if the client IP is not in the whitelist
	 */
	private function _checkIpWhitelist()
	{
		$clientIp = $this->input->ip_address();
		$allowed = $this->config->item('info_terminal_allowed_ips') ?: [];

		foreach ($allowed as $rule)
		{
			if (is_ip_allowed_by_rule($clientIp, $rule))
				return;
		}

		log_message('error', 'InfoTerminalAPI: access denied for IP ' . $clientIp);

		return $this->load->view('Cis/InfoTerminalError.php', [
				"message" => 'Access denied'
			]);
	}
}
