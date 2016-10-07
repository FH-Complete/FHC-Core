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

class Redirect extends FHC_Controller
{
	/**
	 * API constructor
	 */
	public function __construct()
	{
		parent::__construct();
		
		// The second parameter is used to avoiding name collisions in the config array
		$this->config->load('fhcomplete');
	}
	
	public function redirectByToken($token)
	{
		if (isset($token))
		{
			redirect($this->config->item('addons_aufnahme_url') . '?token=' . $token);
		}
		else
		{
			$this->response();
		}
	}
}