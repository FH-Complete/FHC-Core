<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 
 */
class Mylv extends Auth_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'Student' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Studiensemester' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Lvs' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Info' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'Pruefungen' => ['student/anrechnung_beantragen:r','user:r'] // TODO(chris): permissions?
		]);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 */
	public function Student()
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudentWithGrades(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Studiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->StudiensemesterModel->getWhereStudentHasLvs(get_uid());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Lvs($studiensemester_kurzbz)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');

		$result = $this->LehrveranstaltungModel->getLvsByStudentWithGrades(get_uid(), $studiensemester_kurzbz, getUserLanguage());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

	/**
	 */
	public function Info($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$this->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$result = $this->LehrveranstaltungModel->load($lehrveranstaltung_id);

		if (isError($result))
			return $this->outputJsonError(getError($result));
		$lv = current(getData($result) ?: []);

		if (!$lv)
			return $this->outputJsonError('Could\'t find Lehrveranstaltung with id: ' . $lehrveranstaltung_id);


		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');

		$result = $this->LehreinheitmitarbeiterModel->getForLv($lehrveranstaltung_id, $studiensemester_kurzbz);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$lvinfo = [];
		$lvinfo['lektoren'] = getData($result) ?: [];

		$kollisionsfreie_user = unserialize(KOLLISIONSFREIE_USER);
		$lvinfo['lektoren'] = array_values(array_filter($lvinfo['lektoren'], function ($v) use ($kollisionsfreie_user) {
			return !in_array($v->uid, $kollisionsfreie_user);
		}));

		$lvinfo['lvLeitung'] = array_values(array_filter($lvinfo['lektoren'], function ($v) {
			return $v->lehrfunktion_kurzbz == 'LV-Leitung';
		}));


		$this->load->model('organisation/Organisationseinheit_model', 'OrganisationseinheitModel');
		$result = $this->OrganisationseinheitModel->getWithType($lv->oe_kurzbz);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$lvinfo['oe'] = current(getData($result) ?: []);


		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$result = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('Leitung', $lv->oe_kurzbz);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$lvinfo['oeLeitung'] = getData($result) ?: [];


		$result = $this->LehrveranstaltungModel->getKoordinator($lehrveranstaltung_id, $studiensemester_kurzbz);

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$lvinfo['koordinator'] = getData($result) ?: [];

		if (defined('ACTIVE_ADDONS') && in_array('lvinfo', explode(';', ACTIVE_ADDONS)) && file_exists(FHCPATH . 'addons/lvinfo/include/lvinfo.class.php'))
		{
			require_once(FHCPATH . 'addons/lvinfo/include/lvinfo.class.php');
			$lvinfoObj = new lvinfo();
			$lvinfoObj->loadLVinfo($lehrveranstaltung_id, $studiensemester_kurzbz, null, true);
			if (is_array($lvinfoObj->result))
			{
				$oldP = property_exists($this, 'p') ? $this->p : null;
				$result = [];
				$lvinfos = $lvinfoObj->result;
				$lvinfoSet = new lvinfo();
				$lvinfoSet->load_lvinfo_set($studiensemester_kurzbz);
				foreach ($lvinfos as $lvi)
				{
					$this->p = null;
					$this->loadPhrases('ui', $lvi->sprache);
					$result[$lvi->sprache] = [];
					foreach ($lvinfoSet->result as $set)
					{
						$key = $set->lvinfo_set_kurzbz;
						if (!isset($lvi->data[$key]))
							continue;
						$info = [
							'header' => $set->lvinfo_set_bezeichnung[$lvi->sprache]
						];
						if (isset($set->einleitungstext[$lvi->sprache]))
							$info['subheader'] = $set->einleitungstext[$lvi->sprache];
						switch ($set->lvinfo_set_typ)
						{
							case 'boolean':
								$info['body'] = $this->p->t('ui', $lvi->data[$key] === true ? 'ja' : 'nein');
								break;
							case 'array':
								$info['body'] = array_map('htmlspecialchars', $lvi->data[$key]);
								break;
							case 'editor':
								$info['body'] = $lvi->data[$key];
								break;
							default:
								$info['body'] = htmlspecialchars($lvi->data[$key]);
						}
						if ($info['body'])
							$result[$lvi->sprache][] = $info;
					}
				}
				if ($result)
				{
					$lvinfo['lvinfo'] = $result;
					$lvinfo['lvinfoDefaultLang'] = getUserLanguage();

					$this->load->model('system/Sprache_model', 'SpracheModel');
					$result = $this->SpracheModel->loadMultiple(array_keys($result));
					if (!isError($result))
					{
						$result = getData($result);
						$lvinfo['sprachen'] = [];
						foreach ($result as $sprache) {
							$lvinfo['sprachen'][$sprache->sprache] = $sprache;
						}
					}
				}
				$this->p = $oldP;
			}
		}


		$this->outputJsonSuccess($lvinfo);
	}

	/**
	 */
	public function Pruefungen($lehrveranstaltung_id)
	{
		$this->load->model('education/Pruefung_model', 'PruefungModel');

		$result = $this->PruefungModel->getByStudentAndLv(get_uid(), $lehrveranstaltung_id, getUserLanguage());

		if (isError($result))
			return $this->outputJsonError(getError($result));

		$this->outputJsonSuccess(getData($result));
	}

}
