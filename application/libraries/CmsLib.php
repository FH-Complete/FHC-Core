<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

use \DateTime as DateTime;
use \DateInterval as DateInterval;
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
	 * @return stdClass
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
			//DomDocument getElementsByTagName returns a DomNodeList
			$betreff = $XML->getElementsByTagName('betreff');
			//check if any betreff was found and if it is not empty
			if($betreff->length > 0 && !empty($betreff->item(0)->nodeValue))
			{
				//DomNodeList item() return a DomNode, property nodeValue contains the value of the node
				$betreff = $betreff->item(0)->nodeValue;

			}
			else
			{
				return error('no betreff found for the content');
			}
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
	public function getNews($infoscreen = false, $studiengang_kz = null, $semester = null, $mischen = true, $titel = '', $edit = false, $sichtbar = true, $page = 1, $page_size = 10, $sprache, $filterForDegreePrograms = false, $allowedDegreePrograms = [], $active = true)

	{
		$this->ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		list($studiengang_kz, $semester) = $this->getStgAndSem($studiengang_kz, $semester);

		$this->ci->load->model('content/News_model', 'NewsModel');
	
		
		$news = $this->ci->NewsModel->getNewsWithContent($sprache, $studiengang_kz, $semester, null, $sichtbar, $page, $page_size, $active, $mischen, $filterForDegreePrograms, $allowedDegreePrograms);
		$news = getData($news) ?? [];
		$newsWrappers = [];

		foreach ($news as $newsobj) {
			if ($studiengang_kz && $edit && !$newsobj->studiengang_kz)
				continue;

			$newsWrappers[] = $this->createNewsWrapper($newsobj, $edit);
		}

		$content = $this->createNewsContent($newsWrappers, $infoscreen, $titel);
		if (isError($content))
			return $content;

		return success([
			"content" => getData($content),
			"row_count" => $news[0]->row_count ?? 0
		]);
	}

	/**
	 * Creates a newswrapper XML element from a news item.
	 *
	 * @param stdClass	$newsobj
	 * @param boolean	$edit
	 *
	 * @return DOMElement
	 */
	public function createNewsWrapper($newsobj, $edit)
	{
		$id = $edit ? '<news_id><![CDATA[' . $newsobj->news_id . ']]></news_id>' : '';
		$isPublished = $edit ? '<is_published><![CDATA[' . ($newsobj->sichtbar ? 'true' : 'false') . ']]></is_published>' : '';

		$date = new DateTime($newsobj->datum);
		$datum = '<datum><![CDATA[' . $date->format('d.m.Y') . ']]></datum>';
		$datum .= '<datumdetail><![CDATA[' . $date->format('Y-m-d H:i') . ']]></datumdetail>';

		$hasVisibleToDate = isset($newsobj->datum_bis) && $newsobj->datum_bis !== '';
		$datumTo = $hasVisibleToDate
			? new DateTime($newsobj->datum_bis)
			: (clone $date)->add(new DateInterval('P' . MAXNEWSALTER . 'D'));
		if ($hasVisibleToDate)
			$datumTo->setTime(23, 59, 59);

		$now = new DateTime();
		$isActive = '<is_active><![CDATA[' . ($datumTo >= $now ? 'true' : 'false') . ']]></is_active>';

		$newsWrapper = new DOMDocument(); 
		$newsWrapper->loadXML('<newswrapper>' . $newsobj->content . $datum . $id . $isPublished . $isActive . '</newswrapper>');

		return $newsWrapper->documentElement;
	}

	/**
	 * Creates rendered news content from one or more newswrapper XML elements.
	 *
	 * @param DOMElement|array	$newsWrappers
	 * @param boolean				$infoscreen
	 * @param string				$titel
	 *
	 * @return stdClass
	 */
	public function createNewsContent($newsWrappers, $infoscreen, $titel)
	{
		if (!is_array($newsWrappers))
			$newsWrappers = [$newsWrappers];

		$XML = new DOMDocument('1.0', 'UTF-8');
		$contentElement = $XML->createElement('content');
		$XML->appendChild($contentElement);

		foreach ($newsWrappers as $newsWrapper)
			$contentElement->appendChild($XML->importNode($newsWrapper, true));

		if ($titel != '') {
			$titleElement = $XML->createElement('news_titel');
			$titleElement->appendChild($XML->createTextNode($titel));
			$contentElement->appendChild($titleElement);
		}

		//XSLT Vorlage laden
		$template = $this->ci->TemplateModel->load($infoscreen ? 'news_infoscreen' : 'news');
		if (isError($template))
			return $template;
		$template = current(getData($template));

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
