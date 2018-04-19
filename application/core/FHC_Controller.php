<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Controller extends CI_Controller
{
	/**
	 * Standard construct for all the controllers, loads the authentication system
	 */
    public function __construct()
	{
        parent::__construct();

		$this->load->helper('fhcauth');
	}

	/**
	 * Wrapper to load phrases using the PhrasesLib
	 * NOTE: The library is loaded with the alias 'p', so must me used with this alias in the rest of the code.
	 *		EX: $this->p->t(<category>, <phrase name>)
	 */
	public function loadPhrases($categories, $language = null)
	{
		$this->load->library('PhrasesLib', array($categories, $language), 'p');
	}
}
