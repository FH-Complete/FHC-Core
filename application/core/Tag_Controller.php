<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Tag_Controller extends FHCAPI_Controller
{
	private $_uid;

	const BERECHTIGUNG_KURZBZ = 'admin:rw';

	public function __construct($permissions)
	{
		$default_permissions = [
			'getTag' => self::BERECHTIGUNG_KURZBZ,
			'getTags' => self::BERECHTIGUNG_KURZBZ,
			'addTag' => self::BERECHTIGUNG_KURZBZ,

			'updateTag' => self::BERECHTIGUNG_KURZBZ,
			'doneTag' => self::BERECHTIGUNG_KURZBZ,
			'deleteTag' => self::BERECHTIGUNG_KURZBZ,
		];

		$merged_permissions = array_merge($default_permissions, $permissions);

		parent::__construct($merged_permissions);

		$this->_setAuthUID();
		$this->load->model('person/Notiz_model', 'NotizModel');
		$this->load->model('system/Notiztyp_model', 'NotiztypModel');
		$this->load->model('person/Notizzuordnung_model', 'NotizzuordnungModel');

		$this->loadPhrases([
			'ui'
		]);
	}

	public function getTag($readonly_tags = null)
	{
		$language = $this->_getLanguageIndex();
		$id = $this->input->get('id');

		if (is_array($readonly_tags) && !isEmptyArray($readonly_tags))
		{
			$allowed_tags = array_keys($readonly_tags);

			$readonly_tags = $this->_filterTag($readonly_tags, true);

			foreach ($readonly_tags as $key => $tag)
			{
				$readonly_tags[$key] = $this->NotizModel->db->escape($tag);
			}
			$tags = '(' . implode(',', $readonly_tags) . ')';

			$this->NotizModel->addSelect("
						CASE 
							WHEN tbl_notiz_typ.typ_kurzbz IN $tags 
							THEN TRUE 
							ELSE FALSE 
						END as readonly
					");

			$this->NotizModel->db->where_in('tbl_notiz_typ.typ_kurzbz', $allowed_tags);
		}

		$this->NotizModel->addSelect(
			"tbl_notiz.titel, 
			tbl_notiz.text, 
			array_to_json(bezeichnung_mehrsprachig::varchar[])->>". $language. " as bezeichnung,
			tbl_notiz.notiz_id,
			tbl_notiz_typ.style,
			tbl_notiz.erledigt as done,
			tbl_notiz.insertamum,
			tbl_notiz.updateamum,
			(verfasserperson.vorname || ' ' || verfasserperson.nachname || ' ' || '(' || verfasserbenutzer.uid || ')') as verfasser,
			(bearbeiterperson.vorname || ' ' || bearbeiterperson.nachname || ' ' || '(' || bearbeiterbenutzer.uid || ')') as bearbeiter
			"
		);
		$this->NotizModel->addJoin('public.tbl_notiz_typ', 'public.tbl_notiz.typ = public.tbl_notiz_typ.typ_kurzbz');

		$this->NotizModel->addJoin('public.tbl_benutzer verfasserbenutzer', 'tbl_notiz.verfasser_uid = verfasserbenutzer.uid', 'LEFT');
		$this->NotizModel->addJoin('public.tbl_person verfasserperson', 'verfasserbenutzer.person_id = verfasserperson.person_id', 'LEFT');

		$this->NotizModel->addJoin('public.tbl_benutzer bearbeiterbenutzer', 'tbl_notiz.bearbeiter_uid = bearbeiterbenutzer.uid', 'LEFT');
		$this->NotizModel->addJoin('public.tbl_person bearbeiterperson', 'bearbeiterbenutzer.person_id = bearbeiterperson.person_id', 'LEFT');

		$notiz = $this->NotizModel->loadWhere(array('notiz_id' => $id));

		$this->terminateWithSuccess(hasData($notiz) ? getData($notiz)[0] : array());
	}

	public function getTags($tags = null)
	{
		$this->NotiztypModel->addSelect(
			'typ_kurzbz as tag_typ_kurzbz,
			array_to_json(bezeichnung_mehrsprachig::varchar[])->>0 as bezeichnung,
			style,
			beschreibung,
			tag
			'
		);
		$this->NotiztypModel->addOrder('prioritaet');

		if (is_array($tags) && !isEmptyArray($tags))
		{
			$tags = $this->_filterTag($tags, false);
			$this->NotiztypModel->db->where_in('typ_kurzbz', $tags);
		}

		$notiztypen = $this->NotiztypModel->loadWhere(array('aktiv' => true));
		$this->terminateWithSuccess(hasData($notiztypen) ? getData($notiztypen) : array());
	}

	public function addTag($withZuordnung = true, $updatable_tags = null)
	{
		$postData = $this->getPostJson();

		$checkTyp = $this->NotiztypModel->loadWhere(array('typ_kurzbz' => $postData->tag_typ_kurzbz));

		if (isError($checkTyp))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (!hasData($checkTyp))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (is_array($updatable_tags) && !isEmptyArray($updatable_tags))
		{
			$tags = $this->_filterTag($updatable_tags, false);

			if (!in_array($postData->tag_typ_kurzbz, $tags))
				$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		if ($withZuordnung)
		{
			$return = array();
			$checkZuordnungType = $this->NotizzuordnungModel->isValidType($postData->zuordnung_typ);
			if (!isSuccess($checkZuordnungType))
				$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

			$values = array_unique($postData->values);

			foreach ($values as $value)
			{
				$insertResult = $this->addNotiz($postData);

				if (isError($insertResult))
					$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

				$insertZuordnung = $this->NotizzuordnungModel->insert(array(
					'notiz_id' => $insertResult->retval,
					$postData->zuordnung_typ => $value
				));

				if (isError($insertZuordnung))
					$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

				$return[] = [$postData->zuordnung_typ => $value, 'id' => $insertResult->retval];
			}
			$this->terminateWithSuccess($return);
		}
		else
		{
			$insertResult = $this->addNotiz($postData);
			if (isError($insertResult))
				$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);

			return $insertResult->retval;
		}
	}

	public function updateTag($updatable_tags = null)
	{
		$postData = $this->getPostJson();
		$post_tag = $this->NotizModel->loadWhere(array('notiz_id' => $postData->id));

		if (isError($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (!hasData($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (is_array($updatable_tags) && !isEmptyArray($updatable_tags))
		{
			$tags = $this->_filterTag($updatable_tags, false);

			$post_tag_typ = getData($post_tag)[0]->typ;

			if (!in_array($post_tag_typ, $tags))
				$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$updateData = $this->NotizModel->update(array('notiz_id' => $postData->id),
			array('text' => $postData->notiz,
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => $this->_uid,
				'bearbeiter_uid' => $this->_uid,
			)
		);
		$this->terminateWithSuccess($updateData);
	}
	public function doneTag($updatable_tags = null)
	{
		$postData = $this->getPostJson();
		$post_tag = $this->NotizModel->loadWhere(array('notiz_id' => $postData->id));

		if (isError($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (!hasData($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (is_array($updatable_tags) && !isEmptyArray($updatable_tags))
		{
			$tags = $this->_filterTag($updatable_tags, false);

			$post_tag_typ = getData($post_tag)[0]->typ;

			if (!in_array($post_tag_typ, $tags))
				$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$updateData = $this->NotizModel->update(array('notiz_id' => $postData->id),
			array('erledigt' => !$postData->done,
				'text' => $postData->notiz,
				'updateamum' => date('Y-m-d H:i:s'),
				'updatevon' => $this->_uid,
				'bearbeiter_uid' => $this->_uid,
			)
		);
		$this->terminateWithSuccess($updateData);
	}

	public function deleteTag($withZuordnung = true, $updatable_tags = null)
	{
		$postData = $this->getPostJson();
		$post_tag = $this->NotizModel->loadWhere(array('notiz_id' => $postData->id));

		if (isError($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (!hasData($post_tag))
			$this->terminateWithError($this->p->t('ui', 'fehlerBeimLesen'));

		if (is_array($updatable_tags) && !isEmptyArray($updatable_tags))
		{
			$tags = $this->_filterTag($updatable_tags, false);

			$post_tag_typ = getData($post_tag)[0]->typ;

			if (!in_array($post_tag_typ, $tags))
				$this->terminateWithError($this->p->t('ui', 'keineBerechtigung'));
		}

		$deleteNotiz = "";
		if ($withZuordnung)
		{
			$deleteZuordnung = $this->NotizzuordnungModel->delete(array(
				'notiz_id' => $postData->id
			));

			if (isSuccess($deleteZuordnung))
			{
				$deleteNotiz = $this->NotizModel->delete(array(
					'notiz_id' => $postData->id
				));
			}
		}
		else
		{
			$deleteNotiz = $this->NotizModel->delete(array(
				'notiz_id' => $postData->id
			));
		}
		$this->terminateWithSuccess($deleteNotiz);
	}

	private function _setAuthUID()
	{
		$this->_uid = getAuthUID();

		if (!$this->_uid)
			show_error('User authentification failed');
	}

	private function _getLanguageIndex()
	{
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->SpracheModel->addSelect('index');
		$result = $this->SpracheModel->loadWhere(array('sprache' => getUserLanguage()));

		return hasData($result) ? getData($result)[0]->index : 1;
	}

	private function _filterTag($tags, $readonly = true)
	{
		$filtered_tags = array_filter($tags, function ($tag) use ($readonly)
		{
			return isset($tag['readonly']) && $tag['readonly'] === $readonly;
		});

		return array_keys($filtered_tags);
	}

	private function addNotiz($postData)
	{
		return $this->NotizModel->insert(array(
			'titel' => 'TAG', //TODO klären
			'text' => $postData->notiz,
			'verfasser_uid' => $this->_uid,
			'erledigt' => false,
			'insertamum' => date('Y-m-d H:i:s'),
			'insertvon' => $this->_uid,
			'typ' => $postData->tag_typ_kurzbz
		));
	}

}