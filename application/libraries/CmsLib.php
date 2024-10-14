<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use \DateTime as DateTime;
use \DOMDocument as DOMDocument;
use \XSLTProcessor as XSLTProcessor;

/**
 * TODO(chris): NEWS: edit & delete button links and confirm
 * TODO(chris): NEWS: news_infoscreen xlst
 */
class CmsLib
{
	/**
	 * @var object
	 */
	protected $ci;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->ci =& get_instance();

		// Load Models
		$this->ci->load->model('content/Content_model', 'ContentModel');
		$this->ci->load->model('content/Contentgruppe_model', 'ContentgruppeModel');
		$this->ci->load->model('content/Template_model', 'TemplateModel');
		if (defined('LOG_CONTENT') && LOG_CONTENT)
			$this->ci->load->model('system/Webservicelog_model', 'WebservicelogModel');
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
	public function getContent($content_id, $version = null, $sprache = null, $sichtbar = true)
	{
		if (!is_numeric($content_id))
			return error('ContentID ist ungueltig');

		if ($sprache === null)
			$sprache = getUserLanguage();

		$islocked = $this->ci->ContentgruppeModel->loadWhere(['content_id' => $content_id]);
		if (isError($islocked))
			return $islocked;

		if (getData($islocked)) {
			$uid = getAuthUID();
			$isberechtigt = $this->ci->ContentgruppeModel->berechtigt($content_id, $uid);
			if (isError($isberechtigt))
				return $isberechtigt;

			if (!getData($isberechtigt))
				return error('global/keineBerechtigungFuerDieseSeite');
		}
		$content = $this->ci->ContentModel->getContent($content_id, $sprache, $version, $sichtbar, true);

		if (isError($content))
			return $content;

		// Legt einen Logeintrag für die Klickstatistik an
		if (defined('LOG_CONTENT') && LOG_CONTENT) {
			// Nur eingeloggte User werden geloggt, das sonst auch alle Infoscreenaufrufe und dgl. mitgeloggt werden
			if (isLogged()) {
				$request_data = 'content_id=' . $content_id;
				if ($version !== null)
					$request_data .= '&version=' . $version;
				if ($sichtbar !== true)
					$request_data .= '&sichtbar=' . $sichtbar;
				$this->ci->WebservicelogModel->insert([
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
		$template = $this->ci->TemplateModel->load($content->template_kurzbz);
		if (isError($template))
			return $template;
		$template = current(getData($template));

		$XML = new DOMDocument();
		$XML->loadXML($content->content);

		if($content->titel){
			$betreff = $content->titel;
		}else{
			$betreff = $XML->getElementsByTagName('betreff');
		}

		$xsltemplate = new DOMDocument();
		$xsltemplate->loadXML($template->xslt_xhtml_c4);

		//Transformation
		$processor = new XSLTProcessor();
		$processor->importStylesheet($xsltemplate);

		
		$transformed_content = $processor->transformToXML($XML);
		//replaces all the dms.php with the new CIS4 Controller
		$transformed_content = str_replace('dms.php', APP_ROOT . 'cms/dms.php', $transformed_content);
		//replaces all the cms.php with the new CIS4 Controller
		$transformed_content = preg_replace('/content\.php\?content\_id\=([0-9]+)/', APP_ROOT.'cis.php/CisVue/Cms/content/$1', $transformed_content);
		
		return success([
			"betreff"=>$betreff,
			"type"=>$content->template_kurzbz,
			"content"=>$transformed_content
		]);
	}

	/**
	 * @param stdClass		$stg_obj
	 * 
	 * @return stdClass
	 */
	protected function getNewsExtras($stg_obj, $semester)
	{
		$this->ci->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');

		$stg_ltg = $this->ci->StudiengangModel->getLeitungDetailed($stg_obj->studiengang_kz);
		if (isError($stg_ltg))
			return $stg_ltg;
		$stg_ltg = getData($stg_ltg) ?: [];

		$gf_ltg = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('gLtg', $stg_obj->oe_kurzbz);
		if (isError($gf_ltg))
			return $gf_ltg;
		$gf_ltg = getData($gf_ltg) ?: [];

		$stv_ltg = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('stvLtg', $stg_obj->oe_kurzbz);
		if (isError($stv_ltg))
			return $stv_ltg;
		$stv_ltg = getData($stv_ltg) ?: [];

		$ass = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('ass', $stg_obj->oe_kurzbz);
		if (isError($ass))
			return $ass;
		$ass = getData($ass) ?: [];

		$hochschulvertr = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('hsv');
		if (isError($hochschulvertr))
			return $hochschulvertr;
		$hochschulvertr = getData($hochschulvertr) ?: [];

		$stdv = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('stdv', $stg_obj->oe_kurzbz);
		if (isError($stdv))
			return $stdv;
		$stdv = getData($stdv) ?: [];

		$jahrgangsvertr = $this->ci->BenutzerfunktionModel->getBenutzerFunktionenDetailed('jgv', $stg_obj->oe_kurzbz, $semester);
		if (isError($jahrgangsvertr))
			return $jahrgangsvertr;
		$jahrgangsvertr = getData($jahrgangsvertr) ?: [];

		return success($this->ci->load->view('Cis/Cms/News/Xml/NewsExtras', [
			'studiengang' => $stg_obj,
			'semester' => $semester,
			'stg_ltg' => $stg_ltg,
			'gf_ltg' => $gf_ltg,
			'stv_ltg' => $stv_ltg,
			'ass' => $ass,
			'hochschulvertr' => $hochschulvertr,
			'stdv' => $stdv,
			'jahrgangsvertr' => $jahrgangsvertr
		], true));
	}

	/**
	 * @param string			$studiengang_kz
	 * @param string			$semester
	 * 
	 * @return array			queried studiengang_kz and semester
	 */
	public function getStgAndSem($studiengang_kz, $semester)
	{
		$this->ci->load->model('crm/Student_model', 'StudentModel');

		//Zum anzeigen der Studiengang-Details neben den News
		$student = $this->ci->StudentModel->loadWhere(['student_uid' => getAuthUID()]);
		if (isError($student))
			return $student;
		if (getData($student)) {
			$student = current(getData($student));
			if ($studiengang_kz === null)
				$studiengang_kz = $student->studiengang_kz;
			if ($semester === null)
				$semester = $student->semester;
		}
		return [$studiengang_kz, $semester];
	}

	/**
	 * @param boolean			$infoscreen
	 * @param string | null		$studiengang_kz
	 * @param int | null		$semester
	 * @param boolean			$mischen
	 * @param string			$titel
	 * @param boolean			$edit
	 * @param boolean			$sichtbar
	 * 
	 * @return void
	 */
	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true, $page = 1, $page_size = 10)
	{
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		list($studiengang_kz, $semester) = $this->getStgAndSem($studiengang_kz, $semester);
		$all = $edit;

		$xml = '<?xml version="1.0" encoding="UTF-8"?><content>';

		$this->ci->load->model('content/News_model', 'NewsModel');
		$news = $this->ci->NewsModel->getNewsWithContent(getSprache(), $studiengang_kz, $semester, null, $sichtbar, 0, $page, $page_size, $all, $mischen);

		if (isError($news))
			return $news;

		$news = getData($news);
		//var_dump($news->maxPageCount);
		foreach ($news as $newsobj) {
			if ($studiengang_kz && $edit && !$newsobj->studiengang_kz)
				continue;
			$date = new DateTime($newsobj->datum);
			$datum = '<datum><![CDATA[' . $date->format('d.m.Y') . ']]></datum>';
			$datum .= '<datumdetail><![CDATA[' . $date->format('Y-m-d H:i') . ']]></datumdetail>';
			$id = $edit ? '<news_id><![CDATA[' . $newsobj->news_id . ']]></news_id>' : '';
			$xml .= "<newswrapper>" . $newsobj->content . $datum . $id . "</newswrapper>";
		}

		if ($studiengang_kz != 0) {
			$stg_obj = $this->ci->StudiengangModel->load($studiengang_kz);
			if (isError($stg_obj))
				return $stg_obj;
			$stg_obj = current(getData($stg_obj) ?: []);

			if ($stg_obj) {
				if (!$edit && !$infoscreen) {
					$extras = $this->getNewsExtras($stg_obj, $semester);
					if (isError($extras))
						return $extras;
					$xml .= getData($extras);
				}
				$xml .= '<studiengang_bezeichnung><![CDATA[' . $stg_obj->bezeichnung . ']]></studiengang_bezeichnung>';
			}
		}

		if ($titel != '') {
			$xml .= '<news_titel>' . $titel . '</news_titel>';
		}

		$xml .= '</content>';

		//XSLT Vorlage laden
		$template = $this->ci->TemplateModel->load($infoscreen ? 'news_infoscreen' : 'news');
		if (isError($template))
			return $template;
		$template = current(getData($template));

		$XML = new DOMDocument();
		$XML->loadXML($xml);

		$xsltemplate = new DOMDocument();
		$xsltemplate->loadXML($template->xslt_xhtml_c4);

		//Transformation
		$processor = new XSLTProcessor();
		$processor->importStylesheet($xsltemplate);

		$content = $processor->transformToDoc($XML);
		$content->formatOutput = true;

		$content = $content->saveHTML();
		$content = str_replace('dms.php', APP_ROOT . 'cms/dms.php', $content);

		return success($content);
	}
}
