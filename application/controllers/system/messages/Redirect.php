<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * NOTE: it extends FHC_Controller instead of Auth_Controller because authentication is not needed
 */
class Redirect extends FHC_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads model MessageTokenModel
		$this->load->model('system/MessageToken_model', 'MessageTokenModel');
	}

	/**
	 * With the given token redirects the user to reply page configured in the config/message.php file
	 */
	public function redirectByToken($token)
	{
		// Retrieves the single message data using the given token
		$msg = $this->MessageTokenModel->getMessageByToken($token);
		// If it is an error or it does not contain data show an error
		if (!hasData($msg)) show_error('MSG-ERR-0001: An error occurred while redirecting, please contact the administrator');
		// else
		$oe_kurzbz = getData($msg)[0]->oe_kurzbz;

		$organisationRoot = null; // by default is null

		// If an organisation unit is present in the message tries to retrieve the root organisation unit
		// from the one found in the message
		if (!isEmptyString($oe_kurzbz))
		{
			// Retrieves the root organisation unit from the one found in the message
			$getOERoot = $this->MessageTokenModel->getOERoot($oe_kurzbz);
			// If it is an error or it does not contain data show an error
			if (!hasData($getOERoot)) show_error('MSG-ERR-0002: An error occurred while redirecting, please contact the administrator');
			// else
			$organisationRoot = getData($getOERoot)[0]->oe_kurzbz;
		}

		// Retrieves the possible redirecting URLs array from configs
		$messageRedirectUrls = $this->config->item('message_redirect_url');
		// If it is not a valid array then show an error
		if (isEmptyArray($messageRedirectUrls)) show_error('MSG-ERR-0003: An error occurred while redirecting, please contact the administrator');

		// If this organisation unit root is not configured as an entry in the possible redirecting URLs array,
		// then tries to use the default one...
		if (!isset($messageRedirectUrls[$organisationRoot]))
		{
			$organisationRoot = 'fallback';

			// ...if even the default one is not present show an error
			if (!isset($messageRedirectUrls[$organisationRoot]))
			{
				show_error('MSG-ERR-0004: An error occurred while redirecting, please contact the administrator');
			}
		}

		// Finally if everything was right then the user can be redirected
		redirect($messageRedirectUrls[$organisationRoot] . '?token=' . $token);
	}
}
