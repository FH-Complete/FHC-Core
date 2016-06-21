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

    public function __construct()
    {
        //require_once APPPATH.'config/message.php';

		$this->ci =& get_instance();
		$this->ci->load->library('parser');
		$this->ci->load->model('system/Phrase_model', 'PhraseModel');
		$this->ci->load->model('system/Phrase_inhalt_model', 'PhraseInhaltModel');
        $this->ci->load->helper('language');
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
        if (empty($vorlage_kurzbz))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);

        $Phrase = $this->ci->PhraseModel->load($phrase_id);
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
     * saveVorlage() - will save a spezific Template.
     *
     * @param   array  $data    REQUIRED
     * @return  array
     */
    function saveVorlage($vorlage_kurzbz, $data)
    {
        if (empty($data))
        	return $this->_error(MSG_ERR_INVALID_MSG_ID);

        $vorlage = $this->ci->VorlageModel->update($vorlage_kurzbz, $data);
        return $vorlage;
    }


	/**
     * getVorlagetextByVorlage() - will load tbl_vorlagestudiengang for a spezific Template.
     *
     * @param   string  $vorlage_kurzbz    REQUIRED
     * @return  array
     */
    function getVorlagetextByVorlage($vorlage_kurzbz)
	{
        if (empty($vorlage_kurzbz))
        	return $this->_error($this->ci->lang->line('fhc_'.FHC_INVALIDID, false));

        $vorlage = $this->ci->VorlageStudiengangModel->loadWhere(array('vorlage_kurzbz' =>$vorlage_kurzbz));
        return $vorlage;
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
    function insertVorlagetext($data)
	{
        $vorlagetext = $this->ci->VorlageStudiengangModel->insert($data);
        return $vorlagetext;
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
    function updateVorlagetext($vorlagestudiengang_id, $data)
	{
        $vorlagetext = $this->ci->VorlageStudiengangModel->update($vorlagestudiengang_id, $data);
        return $vorlagetext;
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
}
