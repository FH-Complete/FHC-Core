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
			'index' => self::PERM_ANONYMOUS
		]);

		$this->load->config('theme');
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
			return show_error('Auth data not provided');
		}
		
		$this->load->model('person/Person_model', 'PersonModel');
		$return = $this->PersonModel->getUIDByCardNumber($cardNumber);
		if (isError($return))
			return show_error('Issue encountered! Try again later.');

		if (!hasData($return))
		show_error('Card number does not match any user');

		$uid = getData($return)[0]->uid;
		if (isEmptyString($uid))
			show_error('Card number does not match any user');

		$result = $this->authlib->loginByUID($uid);
		if (!hasData($result))
			show_error('User not found');

		$this->load->view('Cis/InfoTerminal.php', []);
	}
}
