<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
class Cms extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'content' => 'user:r'
			)
		);

		// Loads WidgetLib
		$this->load->library('WidgetLib');

		// Load Models
		$this->load->model('content/Content_model', 'ContentModel');
		$this->load->model('content/Contentgruppe_model', 'ContentgruppeModel');
		$this->load->model('content/Template_model', 'TemplateModel');
		if (defined('LOG_CONTENT') && LOG_CONTENT)
			$this->load->model('system/Webservicelog_model', 'WebservicelogModel');

		// Loads phrases system
		$this->loadPhrases(
			array()
		);
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @param int		$content_id
	 * @param int		$version
	 * @param string	$sprache
	 * @param boolean	$sichtbar
	 * 
	 * @return void
	 */
	public function content($content_id, $version = null, $sprache = null, $sichtbar = true)
	{
		if(!is_numeric($content_id))
			return $this->load->view('cis/Error.php', ['error' => 'ContentID ist ungueltig']);

		if ($sprache === null)
			$sprache = getUserLanguage();
		
		$islocked = $this->ContentgruppeModel->loadWhere(['content_id' => $content_id]);
		if (isError($islocked))
			return $this->load->view('cis/Error.php', ['error' => getError($islocked)]);
		
		if (getData($islocked)) {
			$uid = getAuthUID();
			$isberechtigt = $this->ContentgruppeModel->berechtigt($content_id, $uid);
			if (isError($isberechtigt))
				return $this->load->view('cis/Error.php', ['error' => getError($isberechtigt)]);

			if (!getData($isberechtigt))
				return $this->load->view('cis/Error.php', ['error' => 'global/keineBerechtigungFuerDieseSeite']);
		}

		$content = $this->ContentModel->getContent($content_id, $sprache, $version, $sichtbar, true);

		if (isError($content))
			return $this->load->view('cis/Error.php', ['error' => getError($content)]);

		// Legt einen Logeintrag für die Klickstatistik an
		if (defined('LOG_CONTENT') && LOG_CONTENT)
		{
			// Nur eingeloggte User werden geloggt, das sonst auch alle Infoscreenaufrufe und dgl. mitgeloggt werden
			if (isLogged())
			{
				$request_data = 'content_id=' . $content_id;
				if ($version !== null)
					$request_data .= '&version=' . $version;
				if ($sichtbar !== true)
					$request_data .= '&sichtbar=' . $sichtbar;
				$this->WebservicelogModel->insert([
					'webservicetyp_kurzbz' => 'content',
					'request_id' => $content_id,
					'beschreibung' => 'content',
					'request_data' => $request_data . '&sprache=' . $sprache,
					'execute_time' => 'now()',
					'execute_user' => getAuthUID()
				]);
			}
		}

		$content = getData($content);

		//XSLT Vorlage laden
		$template = $this->TemplateModel->load($content->template_kurzbz);
		if (isError($template))
			return $this->load->view('cis/Error.php', ['error' => getError($template)]);
		$template = current(getData($template));

		$XML = new \DOMDocument();
		$XML->loadXML($content->content);

		$xsltemplate = new \DOMDocument();
		$xsltemplate->loadXML($template->xslt_xhtml_c4);

		//Transformation
		$processor = new \XSLTProcessor();
		$processor->importStylesheet($xsltemplate);

		$content = $processor->transformToXML($XML);
		$content = str_replace('dms.php', APP_ROOT . 'cms/dms.php', $content);

		$this->load->view('cis/cms/Content.php', ['content' => $content]);
	}
}
