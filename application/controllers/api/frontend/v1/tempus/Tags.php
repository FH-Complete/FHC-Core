<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Tags extends Tag_Controller
{
	const BERECHTIGUNG_KURZBZ = ['admin:rw', 'assistenz:rw'];

	public function __construct()
	{
		parent::__construct([
			'getTag' => self::BERECHTIGUNG_KURZBZ,
			'getTags' => self::BERECHTIGUNG_KURZBZ,
			'getTagsByCalendar' => self::BERECHTIGUNG_KURZBZ,
			'addTag' => self::BERECHTIGUNG_KURZBZ,
			'updateTag' => self::BERECHTIGUNG_KURZBZ,
			'doneTag' => self::BERECHTIGUNG_KURZBZ,
			'deleteTag' => self::BERECHTIGUNG_KURZBZ
		]);

	$this->config->load('tempus');
	$this->load->model('ressource/KalenderNotiz_model', 'KalenderNotizModel');
	}

	public function getTag($readonly_tags = null)
	{
		parent::getTag($this->config->item('tempus_tags'));
	}
	public function getTags($tags = null)
	{
		parent::getTags($this->config->item('tempus_tags'));
	}

	public function getTagsByCalendar($eindeutige_kalender_gruppen_id)
	{
		$language = $this->_getLanguageIndex();
		$index_bezeichnung_mehrsprachig = $language - 1;

		$this->KalenderNotizModel->addSelect(
			'tbl_kalender_notiz.notiz_id as notiz_id,
			typ_kurzbz as tag_typ_kurzbz,
			array_to_json(bezeichnung_mehrsprachig::varchar[])->>'. $index_bezeichnung_mehrsprachig. ' as bezeichnung,
			style,
			beschreibung,
			tag,
			tbl_notiz.erledigt as done
			'
		);

		$this->KalenderNotizModel->addJoin('public.tbl_notiz', 'tbl_kalender_notiz.notiz_id = public.tbl_notiz.notiz_id');
		$this->KalenderNotizModel->addJoin('public.tbl_notiz_typ', 'public.tbl_notiz.typ = public.tbl_notiz_typ.typ_kurzbz');

		$this->KalenderNotizModel->addOrder('prioritaet');

		$notiztypen = $this->KalenderNotizModel->loadWhere(array('aktiv' => true, 'eindeutige_kalender_gruppen_id' => $eindeutige_kalender_gruppen_id));
		$this->terminateWithSuccess(hasData($notiztypen) ? getData($notiztypen) : array());
	}

	public function addTag($withZuordnung = true, $updatable_tags = null)
	{
		$postData = $this->getPostJson();

		$return = array();
		foreach ($postData->values as $value)
		{
			$insertResult = parent::addTag(false, $this->config->item('tempus_tags'));

			$insertZuordnung = $this->KalenderNotizModel->insert(array(
				'eindeutige_kalender_gruppen_id' => $value,
				'notiz_id' => $insertResult,
			));

			if (isError($insertZuordnung))
				$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);
			$return[] = ['eindeutige_kalender_gruppen_id' => $value, 'id' => $insertResult];
		}
		$this->terminateWithSuccess($return);
	}

	public function updateTag($updatable_tags = null)
	{
		parent::updateTag($this->config->item('tempus_tags'));
	}
	public function deleteTag($withZuordnung = true, $updatable_tags = null)
	{
		parent::deleteTag(true, $this->config->item('tempus_tags'));
	}
	public function doneTag($updatable_tags = null)
	{
		parent::doneTag($this->config->item('tempus_tags'));
	}

	public function _getLanguageIndex()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->SpracheModel->addSelect('index');
		$result = $this->SpracheModel->loadWhere(array('sprache' => getUserLanguage()));

		return hasData($result) ? getData($result)[0]->index : 1;
	}
}
