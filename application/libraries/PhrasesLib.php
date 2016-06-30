<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:        Messaging Library for FH-Complete
*
*
*/
class PhrasesLib
{
	/*
	 * 
	 */
    public function __construct()
    {
        //require_once APPPATH.'config/message.php';

		$this->ci =& get_instance();
		
		$this->ci->load->library('parser');
		
		$this->ci->load->model('system/Phrase_model', 'PhraseModel');
		$this->ci->load->model('system/Phrasentext_model', 'PhrasentextModel');
		
        $this->ci->load->helper('language');
		$this->ci->load->helper('Message');
        //$this->ci->lang->load('fhcomplete');
    }

   	/**
     * getPhrase() - will load a spezific Phrase
     *
     * @param   integer  $vorlage_kurzbz    REQUIRED
     * @return  struct
     */
    function getPhrase($phrase_id)
    {
        if (empty($phrase_id))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);

        $phrase = $this->ci->PhraseModel->load($phrase_id);
        return $phrase;
    }

    /**
     * getSubMessages() - will return all Messages subordinated from a specified message.
     *
     * @param   integer  $msg_id    REQUIRED
     * @return  array
     */
    function getPhraseByApp($app = null)
    {
	    $phrases = $this->ci->PhraseModel->loadWhere(array('app' => $app));
        return $phrases;
    }

	function getPhraseInhalt($phrase_id)
    {
        if (empty($phrase_id))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);

        $phrasentext = $this->ci->PhrasentextModel->loadWhere(array('phrase_id' => $phrase_id));
        return $phrasentext;
    }

	/**
     * savePhrase() - will save a spezific Phrase.
     *
     * @param   array  $data    REQUIRED
     * @return  array
     */
    function savePhrase($phrase_id, $data)
    {
        if (empty($data))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);

        $phrase = $this->ci->PhraseModel->update($phrase_id, $data);
        return $phrase;
    }


	/**
     * getVorlagetextByVorlage() - will load tbl_vorlagestudiengang for a spezific Template.
     *
     * @param   string  $vorlage_kurzbz    REQUIRED
     * @return  array
     */
    function getPhrasentextById($phrasentext_id)
	{
        if (empty($phrasentext_id))
        	return $this->_error($this->ci->lang->line('fhc_'.FHC_INVALIDID, false));

        $phrasentext = $this->ci->PhrasentextModel->load($phrasentext_id);
        return $phrasentext;
    }
	
	/**
     * getPhrases() - 
     *
     * @return  struct
     */
    function getPhrases($app, $sprache, $phrase = null, $orgeinheit_kurzbz = null, $orgform_kurzbz = null)
    {
		if (isset($app) && isset($sprache))
		{
			$result = $this->ci->PhraseModel->getPhrases($app, $sprache, $phrase, $orgeinheit_kurzbz, $orgform_kurzbz);
		}
		else
		{
			$result = $this->_error('app and sprache parameters are required');
		}
		
		return $result;
    }

	/**
     * loadVorlagetext() - will load the best fitting Template.
     *
     * @param   string  $vorlage_kurzbz REQUIRED
     * @param   string  $oe_kurzbz    	OPTIONAL
     * @param   string  $orgform_kurzbz OPTIONAL
     * @return  array
     */
    function loadVorlagetext($vorlage_kurzbz, $oe_kurzbz=null, $orgform_kurzbz=null)
	{
        if (empty($vorlage_kurzbz))
        	return $this->_error($this->ci->lang->line('fhc_'.FHC_INVALIDID, false));

        $vorlage = $this->ci->VorlageStudiengangModel->getVorlageStudiengang($vorlage_kurzbz, $oe_kurzbz, $orgform_kurzbz);
        return $vorlage;
    }

	/**
     * insertVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
     *
     * @param   string  $vorlage_kurzbz    REQUIRED
     * @return  array
     */
    function insertPhraseinhalt($data)
	{
        $phrasentext = $this->ci->PhrasentextModel->insert($data);
        return $phrasentext;
    }

	/**
     * loadVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
     *
     * @param   string  $vorlage_kurzbz    REQUIRED
     * @return  array
     */
    function getVorlagetextById($vorlagestudiengang_id)
	{
        $vorlagetext = $this->ci->VorlageStudiengangModel->load($vorlagestudiengang_id);
        return $vorlagetext;
    }

	/**
     * saveVorlagetext() - will load tbl_vorlagestudiengang for a spezific Template.
     *
     * @param   string  $vorlage_kurzbz    REQUIRED
     * @return  array
     */
    function updatePhraseInhalt($phrasentext_id, $data)
	{
        $phrasentext = $this->ci->PhrasentextModel->update($phrasentext_id, $data);
        return $phrasentext;
    }

	/**
     * parseVorlagetext() - will parse a Vorlagetext.
     *
     * @param   string  $text    REQUIRED
     * @param   array  $data    REQUIRED
     * @return  string
     */
    function parseVorlagetext($text, $data = array())
	{
        if (empty($text))
        	return $this->_error($this->ci->lang->line('fhc_'.FHC_INVALIDID, false));
		$text = $this->ci->parser->parse_string($text, $data, TRUE);
		return $text;
    }
	
	/*
	 * 
	 */
	protected function _error($retval = '', $message = EXIT_ERROR)
	{
		return error($retval, $message);
	}
	
	/*
	 * 
	 */
	protected function _success($retval, $message = EXIT_SUCCESS)
	{
		return success($retval, $message);
	}
}
