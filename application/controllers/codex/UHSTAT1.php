<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class UHSTAT1 extends FHC_Controller
{
	const CODEX_OESTERREICH = 'A';
	const LOWER_BOUNDARY_YEARS = 160;
	const UPPER_BOUNDARY_YEARS = 20;

	private $_uhstat1Fields = array();

	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'admin:r',
				'saveUHSTAT1Data' => 'admin:rw'
			)
		);

		// load ci libs
		$this->load->library('form_validation');

		// load ci helpers
		$this->load->helper(array('form', 'url'));

		// load models
		$this->load->model('codex/Oehbeitrag_model', 'OehbeitragModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('system/Sprache_model', 'SpracheModel');
		$this->load->model('codex/Abschluss_model', 'AbschlussModel');
		$this->load->model('codex/Uhstat1daten_model', 'Uhstat1datenModel');

		$this->loadPhrases(
			array(
				'ui',
				'uhstat'
			)
		);

		// set form field information
		$this->_uhstat1Fields = array(
			'mutter_geburtsstaat' => array('name' => 'Geburtsstaat Mutter'),
			'mutter_geburtsjahr' => array('name' => 'Geburtsjahr Mutter'),
			'mutter_bildungsstaat' => array('name' => 'Bildungsstaat Mutter'),
			'mutter_bildungmax' => array(
				'name' => 'Geburtsjahr Mutter',
				'rules' => array(
					'callback_bildungsstaat_bildungmax_check[m]' => array(
						'bildungsstaat_bildungmax_check' => $this->p->t('uhstat', 'ausbildungBildungsstaatUebereinstimmung')
					)
				)
			),
			'vater_geburtsstaat' => array('name' => 'Geburtsstaat Vater'),
			'vater_geburtsjahr' => array('name' => 'Geburtsjahr Vater'),
			'vater_bildungsstaat' => array('name' => 'Bildungsstaat Vater'),
			'vater_bildungmax' => array('name' => 'Geburtsjahr Vater'),
			'vater_bildungmax' => array(
				'name' => 'Geburtsjahr Vater',
				'rules' => array(
					'callback_bildungsstaat_bildungmax_check[v]' => array(
						'bildungsstaat_bildungmax_check' => $this->p->t('uhstat', 'ausbildungBildungsstaatUebereinstimmung')
					)
				)
			)
		);
	}

	public function index()
	{
		$formMetaData = $this->_getFormMetaData();

		if (isError($formMetaData)) show_error(getError($formMetaData));

		if (!hasData($formMetaData)) show_error("No form meta data could be loaded");

		$uhstatData = $this->_getUHSTAT1Data();

		if (isError($uhstatData)) show_error(getError($uhstatData));

		$readonly = hasData($uhstatData);

		$this->load->view("codex/uhstat1.php", array(
			'formMetaData' => getData($formMetaData),
			'uhstatData' => getData($uhstatData),
			'readonly' => $readonly)
		);
	}

	/**
	 * Add or update UHSTAT1 data
	 */
	public function saveUHSTAT1Data()
	{
		$person_id = $this->input->get('person_id');

		if (!isset($person_id) || !is_numeric($person_id)) show_error("Person Id missing");

		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');

		foreach ($this->_uhstat1Fields as $field => $params)
		{
			// all fields are required
			$ruleNames = 'required';
			$ruleMessages = array('required' => $this->p->t('uhstat', 'angabeFehlt'));

			// add additional rules
			if (isset($params['rules']))
			{
				foreach ($params['rules'] as $ruleName => $ruleMessage)
				{
					$ruleNames .= '|'.$ruleName;
					$ruleMessages = array_merge($ruleMessages, $ruleMessage);
				}
			}

			$this->form_validation->set_rules(
				$field,
				$params['name'],
				$ruleNames,
				$ruleMessages
			);
		}

		$uhstat1datenRes = null;
		if ($this->form_validation->run()) // if valid
		{
			// get post fields
			$uhstatData = array();
			foreach ($this->_uhstat1Fields as $field => $params)
			{
				$uhstatData[$field] = $this->input->post($field);
			}

			$uhstat1datenloadRes = $this->Uhstat1datenModel->loadWhere(array('person_id' => $person_id));

			if (hasData($uhstat1datenloadRes))
			{
				$uhstat1datenRes = $this->Uhstat1datenModel->update(
					array('person_id' => $person_id),
					$uhstatData
				);
			}
			else
			{
				$uhstat1datenRes = $this->Uhstat1datenModel->insert(
					array_merge($uhstatData, array('person_id' => $person_id))
				);
			}
		}

		$formMetaData = $this->_getFormMetaData();

		if (isError($formMetaData)) show_error(getError($formMetaData));

		if (!hasData($formMetaData)) show_error("No form data could be loaded");

		// pass success/error messages to view
		$successMessage = isset($uhstat1datenRes) && isSuccess($uhstat1datenRes) ? $this->p->t('uhstat', 'erfolgreichGespeichert') : '';
		$errorMessage = isset($uhstat1datenRes) && isError($uhstat1datenRes) ? $this->p->t('uhstat', 'fehlerBeimSpeichern') : '';
		$readOnly = isSuccess($uhstat1datenRes);

		// load view with form data
		$this->load->view("codex/uhstat1.php", array(
			'formMetaData' => getData($formMetaData),
			'successMessage' => $successMessage,
			'errorMessage' => $errorMessage,
			'readonly' => $readOnly
		));
	}

	/**
	 * Check callback for Bildungsstaat - if Bildungsstaat is Austria, a highest education should be in Austria.
	 * @param $bildungmax
	 * @param $bildungsstaat_typ - mother (m) or father (v)
	 * @return bool true if valid, false otherwise
	 */
	public function bildungsstaat_bildungmax_check($bildungmax, $bildungsstaat_typ)
	{
		// valid if no type passed
		if (!isset($bildungsstaat_typ) || !isset($bildungmax)) return true;

		// get correct input
		if ($bildungsstaat_typ == 'm') // mutter
			$bildungsstaat = $this->input->post('mutter_bildungsstaat');
		elseif ($bildungsstaat_typ == 'v') // vater
			$bildungsstaat = $this->input->post('vater_bildungsstaat');
		else
			return true;

		if (!isset($bildungsstaat)) return true;

		// find out if abschluss is in Austria
		$this->AbschlussModel->addSelect("in_oesterreich");
		$abschlussRes = $this->AbschlussModel->load($bildungmax);

		if (hasData($abschlussRes))
		{
			$in_oesterreich = getData($abschlussRes)[0]->in_oesterreich;
			// invalid if abschluss in Austria, but not Bildungsstaat, or abschluss not in Austria, but Bildungsstaat in Austria
			return ($in_oesterreich && $bildungsstaat == self::CODEX_OESTERREICH) || (!$in_oesterreich && $bildungsstaat != self::CODEX_OESTERREICH);
		}

		return false;
	}

	/**
	 * Gets initial data needed to display UHSTAT1 form.
	 */
	private function _getFormMetaData()
	{
		$person_id = $this->input->get('person_id');

		if (!isset($person_id) || !is_numeric($person_id)) return error("Person Id missing");

		$languageIdx = $this->_getLanguageIndex();

		$formMetaData = array(
			'nation' => array(),
			'abschluss_oesterreich' => array(),
			'abschluss_nicht_oesterreich' => array(),
			'jahre' => array(),
			'person_id' => $person_id
		);

		$nationTextFieldName = $languageIdx == 1 ? 'langtext' : 'engltext';

		// get nation list
		$this->load->model('codex/Nation_model', 'NationModel');

		$this->NationModel->addSelect("nation_code, $nationTextFieldName AS nation_text");
		$this->NationModel->addOrder("nation_text");
		$nationRes = $this->NationModel->load();

		if (isError($nationRes)) return $nationRes;

		if (hasData($nationRes)) $formMetaData['nation'] = getData($nationRes);

		// get abschluss list
		$abschlussRes = $this->AbschlussModel->getActiveAbschluesse($languageIdx);

		if (isError($abschlussRes)) return $abschlussRes;

		$abschlussData = getData($abschlussRes);

		if (hasData($abschlussRes))
		{
			foreach (getData($abschlussRes) as $abschluss)
			{
				if ($abschluss->in_oesterreich === true)
					$formMetaData['abschluss_oesterreich'][] = $abschluss;
				elseif ($abschluss->in_oesterreich === false)
					$formMetaData['abschluss_nicht_oesterreich'][] = $abschluss;
				else
				{
					$formMetaData['abschluss_oesterreich'][] = $abschluss;
					$formMetaData['abschluss_nicht_oesterreich'][] = $abschluss;
				}
			}
		}

		// get realistic birth years, dated back from current year
		$currYear = date("Y");
		$formMetaData['jahre'] = range($currYear - self::UPPER_BOUNDARY_YEARS, $currYear - self::LOWER_BOUNDARY_YEARS);

		return success($formMetaData);
	}

	/**
	 * Gets initial data needed to display UHSTAT1 form.
	 */
	private function _getUHSTAT1Data()
	{
		$person_id = $this->input->get('person_id');

		if (!isset($person_id) || !is_numeric($person_id)) return error("Person Id missing");

		$this->Uhstat1datenModel->addSelect(
			implode(', ', array_keys($this->_uhstat1Fields))
		);
		$uhstatRes = $this->Uhstat1datenModel->loadWhere(array('person_id' => $person_id));

		if (isError($uhstatRes)) return $uhstatRes;

		return success(hasData($uhstatRes) ? getData($uhstatRes)[0] : null);
	}

	/**
	 * Gets language index of currently logged in user.
	 * @return int (the index, start at 1)
	 */
	private function _getLanguageIndex()
	{
		$idx = 1;
		$this->SpracheModel->addSelect('index');
		$langRes = $this->SpracheModel->loadWhere(array('sprache' => getUserLanguage()));

		if (hasData($langRes))
		{
			$idx = getData($langRes)[0]->index;
		}

		return $idx;
	}
}
