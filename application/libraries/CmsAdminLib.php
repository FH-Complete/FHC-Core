<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class CmsAdminLib
{
	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();

		$this->ci->load->model('content/Content_model', 'ContentModel');
		$this->ci->load->model('content/Contentsprache_model', 'ContentspracheModel');
		$this->ci->load->model('content/Contentchild_model', 'ContentchildModel');
		$this->ci->load->model('content/Contentgruppe_model', 'ContentgruppeModel');
		$this->ci->load->model('content/Contentlog_model', 'ContentlogModel');
		$this->ci->load->model('content/Template_model', 'TemplateModel');

		$this->ci->load->library('XsdSchemaLib');
		$this->ci->load->library('PermissionLib');
		$this->ci->config->load('cms');
	}

	/**
	 * @return object success(array) entitled oe_kurzbz values
	 */
	public function getEntitledOe()
	{
		$oe = $this->ci->permissionlib->getOE_isEntitledFor('basis/cms');

		if ($oe === false || !is_array($oe))
			return success([]);

		return success($oe);
	}

	// LEGACY-QUIRK: the legacy code checks the organisational unit only when it loads the
	// page. It does not check on each write. This method exists, but it is called only
	// where the legacy code calls it. See Q7 in the contract.
	/**
	 * @param int $content_id
	 * @return object success(bool)
	 */
	public function isEntitledForContent($content_id)
	{
		$oeResult = $this->ci->ContentModel->getOeKurzbz($content_id);
		if (isError($oeResult))
			return $oeResult;

		$entitledResult = $this->getEntitledOe();
		if (isError($entitledResult))
			return $entitledResult;

		return success(in_array(getData($oeResult), getData($entitledResult)));
	}

	/**
	 * @return object success(string) or error
	 */
	public function getDefaultOe()
	{
		$default = $this->ci->config->item('default_oe_kurzbz');

		$entitledResult = $this->getEntitledOe();
		if (isError($entitledResult))
			return $entitledResult;
		$entitled = getData($entitledResult);

		if (empty($entitled))
			return error($this->ci->p->t('cms', 'keineBerechtigteOe'));

		if (in_array($default, $entitled))
			return success($default);

		return success($entitled[0]);
	}

	/**
	 * @param int|null $parent_content_id
	 * @return object success(array) with content_id, sprache, version
	 */
	public function createContent($parent_content_id = null)
	{
		$templatesResult = $this->ci->TemplateModel->loadWhere([]);
		if (isError($templatesResult))
			return $templatesResult;
		$templates = getData($templatesResult);
		if (empty($templates))
			return error($this->ci->p->t('cms', 'keineVorlageVorhanden'));
		$template = $templates[0];

		$oeResult = $this->getDefaultOe();
		if (isError($oeResult))
			return $oeResult;
		$oe = getData($oeResult);

		$titel = $this->ci->p->t('cms', 'neuerEintrag');

		$now = date('Y-m-d H:i:s');
		$uid = getAuthUID();

		$this->ci->db->trans_start();

		$contentResult = $this->ci->ContentModel->insert([
			'template_kurzbz' => $template->template_kurzbz,
			'oe_kurzbz' => $oe,
			'aktiv' => true,
			'menu_open' => false,
			'beschreibung' => '',
			'insertamum' => $now,
			'insertvon' => $uid
		]);
		if (isError($contentResult))
			return $contentResult;
		$content_id = getData($contentResult);

		$spracheResult = $this->ci->ContentspracheModel->insert([
			'content_id' => $content_id,
			'sprache' => DEFAULT_LANGUAGE,
			'version' => 1,
			'sichtbar' => true,
			'titel' => $titel,
			'content' => '<?xml version="1.0" encoding="UTF-8" ?><content></content>',
			'insertamum' => $now,
			'insertvon' => $uid
		]);
		if (isError($spracheResult))
			return $spracheResult;

		if ($parent_content_id !== null)
		{
			$maxSortResult = $this->ci->ContentchildModel->getMaxSort($parent_content_id);
			if (isError($maxSortResult))
				return $maxSortResult;

			$childResult = $this->ci->ContentchildModel->insert([
				'content_id' => $parent_content_id,
				'child_content_id' => $content_id,
				'sort' => getData($maxSortResult) + 1,
				'insertamum' => $now,
				'insertvon' => $uid
			]);
			if (isError($childResult))
				return $childResult;
		}

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'speichernFehlgeschlagen'));

		return success([
			'content_id' => $content_id,
			'sprache' => DEFAULT_LANGUAGE,
			'version' => 1
		]);
	}

	/**
	 * @param int $content_id
	 * @param string $sprache source language
	 * @param int $version source version
	 * @param string $neueSprache target language
	 * @return object success(array) with contentsprache_id
	 */
	public function createTranslation($content_id, $sprache, $version, $neueSprache)
	{
		$existsResult = $this->ci->ContentspracheModel->exists($content_id, $neueSprache);
		if (isError($existsResult))
			return $existsResult;
		if (getData($existsResult))
			return error($this->ci->p->t('cms', 'uebersetzungExistiert'));

		$sourceResult = $this->ci->ContentspracheModel->getOne($content_id, $sprache, $version);
		if (isError($sourceResult))
			return $sourceResult;
		$source = getData($sourceResult);

		$now = date('Y-m-d H:i:s');
		$uid = getAuthUID();

		$insertResult = $this->ci->ContentspracheModel->insert([
			'content_id' => $content_id,
			'sprache' => $neueSprache,
			'version' => $source->version,
			'sichtbar' => true,
			'titel' => $source->titel,
			'content' => $source->content,
			// The review feature is gone from this admin, but the columns stay and the
			// legacy admin still writes them. A copied row must not inherit the review
			// stamp of its source, so we clear it here.
			'reviewvon' => null,
			'reviewamum' => null,
			'gesperrt_uid' => null,
			'insertamum' => $now,
			'insertvon' => $uid,
			'updateamum' => $now,
			'updatevon' => $uid
		]);
		if (isError($insertResult))
			return $insertResult;

		return success(['contentsprache_id' => getData($insertResult)]);
	}

	/**
	 * @param int $content_id
	 * @param string $sprache
	 * @return object success(array) with version
	 */
	public function createVersion($content_id, $sprache)
	{
		$sourceResult = $this->ci->ContentspracheModel->getOne($content_id, $sprache, null);
		if (isError($sourceResult))
			return $sourceResult;
		$source = getData($sourceResult);

		$maxResult = $this->ci->ContentspracheModel->getMaxVersion($content_id, $sprache);
		if (isError($maxResult))
			return $maxResult;
		$newVersion = getData($maxResult) + 1;

		$now = date('Y-m-d H:i:s');
		$uid = getAuthUID();

		$insertResult = $this->ci->ContentspracheModel->insert([
			'content_id' => $content_id,
			'sprache' => $sprache,
			'version' => $newVersion,
			'sichtbar' => false,
			'titel' => $source->titel,
			'content' => $source->content,
			// The review feature is gone from this admin, but the columns stay and the
			// legacy admin still writes them. A copied row must not inherit the review
			// stamp of its source, so we clear it here.
			'reviewvon' => null,
			'reviewamum' => null,
			'gesperrt_uid' => null,
			'insertamum' => $now,
			'insertvon' => $uid,
			'updateamum' => $now,
			'updatevon' => $uid
		]);
		if (isError($insertResult))
			return $insertResult;

		return success(['version' => $newVersion]);
	}

	/**
	 * @param array $daten fields from contract 7.7
	 * @return object success(true) or error
	 */
	public function saveProperties($daten)
	{
		$versionResult = $this->ci->ContentspracheModel->getOne(
			$daten['content_id'], $daten['sprache'], $daten['version']
		);
		if (isError($versionResult))
			return $versionResult;
		$row = getData($versionResult);

		$now = date('Y-m-d H:i:s');
		$uid = getAuthUID();

		$this->ci->db->trans_start();

		$this->ci->ContentModel->update($daten['content_id'], [
			'template_kurzbz' => $daten['template_kurzbz'],
			'oe_kurzbz' => $daten['oe_kurzbz'],
			'aktiv' => $daten['aktiv'],
			'menu_open' => $daten['menu_open'],
			'beschreibung' => $daten['beschreibung'],
			'updateamum' => $now,
			'updatevon' => $uid
		]);

		// LEGACY-QUIRK: prefs_save sets updateamum and updatevon in tbl_content only.
		// It does not touch tbl_contentsprache. Kept as a functional copy.
		$this->ci->ContentspracheModel->update($row->contentsprache_id, [
			'titel' => $daten['titel'],
			'sichtbar' => $daten['sichtbar']
		]);

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'speichernFehlgeschlagen'));

		return success(true);
	}

	// LEGACY-QUIRK: the legacy XSDFormPrinter_XML branch checks neither the permission
	// type nor the lock. The form appears only when the user holds the lock, but the POST
	// itself is unprotected. See Q3 in the contract.
	/**
	 * @param int $content_id
	 * @param string $sprache
	 * @param int $version
	 * @param array $values field name => value
	 * @return object success(true) or error
	 */
	public function saveContentXml($content_id, $sprache, $version, $values)
	{
		$versionResult = $this->ci->ContentspracheModel->getOne($content_id, $sprache, $version);
		if (isError($versionResult))
			return $versionResult;
		$row = getData($versionResult);

		$contentResult = $this->ci->ContentModel->load($content_id);
		if (isError($contentResult))
			return $contentResult;
		$content = getData($contentResult);
		if (empty($content))
			return error($this->ci->p->t('cms', 'contentNichtGefunden'));

		$templateResult = $this->ci->TemplateModel->load($content[0]->template_kurzbz);
		if (isError($templateResult))
			return $templateResult;
		$template = getData($templateResult);
		if (empty($template))
			return error($this->ci->p->t('cms', 'keineVorlageVorhanden'));

		$schemaResult = $this->ci->xsdschemalib->parseSchema(
			$template[0]->xsd, $content[0]->template_kurzbz
		);
		if (isError($schemaResult))
			return $schemaResult;

		$xmlResult = $this->ci->xsdschemalib->buildXml(getData($schemaResult), $values);
		if (isError($xmlResult))
			return $xmlResult;

		$updateResult = $this->ci->ContentspracheModel->update(
			$row->contentsprache_id,
			['content' => getData($xmlResult)]
		);
		if (isError($updateResult))
			return $updateResult;

		return success(true);
	}

	// DEVIATION: content::sperren overwrites a live lock without a check (Q2). Locking now
	// refuses a live foreign lock and takes over only an expired one.
	/**
	 * Locks a version for the current user, or takes over an expired lock.
	 * @param int $contentsprache_id
	 * @return object success(true) or error
	 */
	public function lock($contentsprache_id)
	{
		$uid = getAuthUID();

		$rowResult = $this->ci->ContentspracheModel->load($contentsprache_id);
		if (isError($rowResult))
			return $rowResult;
		$rowData = getData($rowResult);
		if (empty($rowData))
			return error($this->ci->p->t('cms', 'versionNichtGefunden'));

		$holder = ($rowData[0]->gesperrt_uid === '') ? null : $rowData[0]->gesperrt_uid;

		if ($holder !== null && $holder !== $uid)
		{
			$openResult = $this->ci->ContentlogModel->getOpenLock($contentsprache_id, $holder);
			$openEntry = (!isError($openResult)) ? getData($openResult) : null;

			if (!$this->isLockExpired($holder, $openEntry))
				return error($this->ci->p->t('cms', 'bereitsGesperrt'));
		}

		$this->ci->db->trans_start();

		// Close every open entry: a take-over ends the previous one, and the legacy leaks them.
		$this->ci->ContentlogModel->closeOpenEntries($contentsprache_id);

		$logResult = $this->ci->ContentlogModel->insert([
			'uid' => $uid,
			'contentsprache_id' => $contentsprache_id,
			'start' => date('Y-m-d H:i:s')
		]);
		if (isError($logResult))
			return $logResult;

		$updateResult = $this->ci->ContentspracheModel->update($contentsprache_id, [
			'gesperrt_uid' => $uid
		]);
		if (isError($updateResult))
			return $updateResult;

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'speichernFehlgeschlagen'));

		return success(true);
	}

	// DEVIATION: content::freigabeUser releases ALL locks of the user (Q1). Releasing now
	// ends one lock. The uid predicate stops a user releasing a lock of somebody else.
	/**
	 * Releases the lock of the current user on one version.
	 * @param int $contentsprache_id
	 * @return object success(true)
	 */
	public function releaseOwnLock($contentsprache_id)
	{
		$uid = getAuthUID();

		$this->ci->db->trans_start();

		$this->ci->ContentlogModel->closeOpenEntries($contentsprache_id, $uid);
		$this->ci->ContentspracheModel->releaseLock($contentsprache_id, $uid);

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'speichernFehlgeschlagen'));

		return success(true);
	}

	/**
	 * @param int $contentsprache_id
	 * @return object success(true)
	 */
	public function forceRelease($contentsprache_id)
	{
		$this->ci->db->trans_start();

		$this->ci->ContentlogModel->closeOpenEntries($contentsprache_id);
		$this->ci->ContentspracheModel->releaseLock($contentsprache_id);

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'speichernFehlgeschlagen'));

		return success(true);
	}

	/**
	 * @param int $content_id
	 * @param string $sprache
	 * @param int $version
	 * @return object success(array) lock state per contract 7.8
	 */
	public function getLockState($content_id, $sprache, $version)
	{
		$versionResult = $this->ci->ContentspracheModel->getOne($content_id, $sprache, $version);
		if (isError($versionResult))
			return $versionResult;
		$row = getData($versionResult);

		// LEGACY-QUIRK: admin.php inserts an empty string, releasing writes NULL. Both mean
		// free. The contract reports NULL.
		$gesperrt_uid = ($row->gesperrt_uid === '') ? null : $row->gesperrt_uid;

		$logEntry = null;
		if ($gesperrt_uid !== null)
		{
			$lockResult = $this->ci->ContentlogModel->getOpenLock(
				$row->contentsprache_id, $gesperrt_uid
			);
			$logEntry = (!isError($lockResult)) ? getData($lockResult) : null;
		}

		return success([
			'contentsprache_id' => (int) $row->contentsprache_id,
			'gesperrt_uid' => $gesperrt_uid,
			'start' => $logEntry ? $logEntry->start : null,
			'own' => ($gesperrt_uid !== null && $gesperrt_uid === getAuthUID()),
			'expired' => $this->isLockExpired($gesperrt_uid, $logEntry),
			'expires' => $this->lockExpiryDate($logEntry),
			'may_force' => $this->ci->permissionlib->isBerechtigt('basis/cms_sperrfreigabe', 'su')
		]);
	}

	/**
	 * End of the lock window, null if the entry carries no start.
	 * @param stdClass|null $logEntry
	 * @return string|null
	 */
	private function lockExpiryDate($logEntry)
	{
		if ($logEntry === null || empty($logEntry->start))
			return null;

		$ttlHours = (int) $this->ci->config->item('lock_ttl_hours');

		// lock() writes start with PHP time, so stay on PHP time.
		return date('Y-m-d H:i:s', strtotime($logEntry->start) + $ttlHours * 3600);
	}

	// DEVIATION: the legacy holds a lock until its owner or a superuser releases it, so a
	// forgotten lock blocks a page forever. Locks now age out after lock_ttl_hours.
	/**
	 * A lock without a log entry has no age and counts as expired.
	 * @param string|null $gesperrt_uid
	 * @param stdClass|null $logEntry
	 * @return bool
	 */
	private function isLockExpired($gesperrt_uid, $logEntry)
	{
		if ($gesperrt_uid === null)
			return false;

		$expires = $this->lockExpiryDate($logEntry);

		return $expires === null || strtotime($expires) < time();
	}

	// DEVIATION: admin.php ignores the return value of deleteContent and tells the user
	// nothing. This code reports the error. See Q5 in the contract.
	/**
	 * @param int $content_id
	 * @return object success(true) or error
	 */
	public function deleteContent($content_id)
	{
		$this->ci->db->trans_start();

		$this->ci->ContentchildModel->deleteByContent($content_id);
		$this->ci->ContentlogModel->deleteByContent($content_id);
		$this->ci->ContentspracheModel->deleteByContent($content_id);
		$this->ci->ContentgruppeModel->deleteByContent($content_id);
		$this->ci->ContentModel->delete($content_id);

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'loeschenNichtMoeglichReferenzen'));

		return success(true);
	}

	/**
	 * @param int $content_id
	 * @param string $sprache
	 * @param int $version
	 * @return object success(true) or error
	 */
	public function deleteVersion($content_id, $sprache, $version)
	{
		if ($sprache === DEFAULT_LANGUAGE)
		{
			$countResult = $this->ci->ContentspracheModel->getNumberOfVersions($content_id, $sprache);
			if (isError($countResult))
				return $countResult;
			if (getData($countResult) === 1)
				return error($this->ci->p->t('cms', 'letzteVersionNichtLoeschbar'));
		}

		$versionResult = $this->ci->ContentspracheModel->getOne($content_id, $sprache, $version);
		if (isError($versionResult))
			return $versionResult;
		$row = getData($versionResult);

		$this->ci->db->trans_start();

		$this->ci->ContentlogModel->deleteByContentsprache($row->contentsprache_id);
		$this->ci->ContentspracheModel->deleteVersion($content_id, $sprache, $version);

		$this->ci->db->trans_complete();

		if ($this->ci->db->trans_status() === false)
			return error($this->ci->p->t('cms', 'loeschenFehlgeschlagen'));

		return success(true);
	}
}
