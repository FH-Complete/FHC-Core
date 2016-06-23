<?php
	if (! defined('BASEPATH'))
		exit('No direct script access allowed');
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
    public function __construct($params = null)
    {
        //require_once APPPATH.'config/message.php';

		$this->ci =& get_instance();
		$this->ci->load->library('parser');
		
		$this->ci->load->model('system/Phrase_model', 'PhraseModel');
		$this->ci->load->model('system/Phrase_inhalt_model', 'PhraseInhaltModel');
		
		if (is_array($params) && isset($params['uid']))
		{
			$this->ci->PhraseModel->setUID($params['uid']);
			$this->ci->PhraseInhaltModel->setUID($params['uid']);
		}
		
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

        $phrase_inhalt = $this->ci->PhraseInhaltModel->loadWhere(array('phrase_id' => $phrase_id));
        return $phrase_inhalt;
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
    function getPhraseInhaltById($phrase_inhalt_id)
	{
        if (empty($phrase_inhalt_id))
        	return $this->_error($this->ci->lang->line('fhc_'.FHC_INVALIDID, false));

        $phrase_inhalt = $this->ci->PhraseInhaltModel->loadWhere(array('phrase_inhalt_id' =>$phrase_inhalt_id));
        return $phrase_inhalt;
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
        $phrase_inhalt = $this->ci->PhraseInhaltModel->insert($data);
        return $phrase_inhalt;
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
    function updatePhraseInhalt($phrase_inhalt_id, $data)
	{
        $phrase_inhalt = $this->ci->PhraseInhaltModel->update($phrase_inhalt_id, $data);
        return $phrase_inhalt;
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