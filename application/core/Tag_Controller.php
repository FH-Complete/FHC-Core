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
	}

	public function getTag()
	{
		$id = $this->input->get('id');

		$this->NotizModel->addSelect(
			'tbl_notiz.titel, 
			tbl_notiz.text, 
			array_to_json(bezeichnung_mehrsprachig::varchar[])->>0 as bezeichnung,
			tbl_notiz.notiz_id,
			tbl_notiz_typ.style,
			tbl_notiz.erledigt as done,
			tbl_notiz.insertamum,
			tbl_notiz.updateamum,
			tbl_notiz.insertvon,
			tbl_notiz.updatevon
			'
		);
		$this->NotizModel->addJoin('public.tbl_notiz_typ', 'public.tbl_notiz.typ = public.tbl_notiz_typ.typ_kurzbz');
		$notiz = $this->NotizModel->loadWhere(array('notiz_id' => $id));

		$this->terminateWithSuccess(hasData($notiz) ? getData($notiz)[0] : array());
	}

	public function getTags()
	{
		$this->NotiztypModel->addSelect(
			'typ_kurzbz as tag_typ_kurzbz,
			array_to_json(bezeichnung_mehrsprachig::varchar[])->>0 as bezeichnung,
			style,
			beschreibung,
			tag
			'
		);
		$notiztypen = $this->NotiztypModel->loadWhere(array('aktiv' => true));
		$this->terminateWithSuccess(hasData($notiztypen) ? getData($notiztypen) : array());
	}

	public function addTag($withZuordnung = true)
	{
		$postData = $this->getPostJson();

		$checkTyp = $this->NotiztypModel->loadWhere(array('typ_kurzbz' => $postData->tag_typ_kurzbz));

		if (!hasData($checkTyp))
			$this->terminateWithError('Error occurred', self::ERROR_TYPE_GENERAL);


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
	public function updateTag()
	{
		$postData = $this->getPostJson();
		$updateData = $this->NotizModel->update(array('notiz_id' => $postData->id),
			array('text' => $postData->notiz)
		);
		$this->terminateWithSuccess($updateData);
	}
	public function doneTag()
	{
		$postData = $this->getPostJson();
		$updateData = $this->NotizModel->update(array('notiz_id' => $postData->id),
			array('erledigt' => !$postData->done)
		);

		$this->terminateWithSuccess($updateData);
	}

	public function deleteTag($withZuordnung = true)
	{
		$postData = $this->getPostJson();

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


}