<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class UHSTAT1 extends FHC_Controller
{
	const CODEX_OESTERREICH = 'A';
	const LOWER_BOUNDARY_YEARS = 160;
	const UPPER_BOUNDARY_YEARS = 20;

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
	}

	public function index()
	{
		$formData = $this->_getFormData();

		if (isError($formData)) show_error(getError($formData));

		if (!hasData($formData)) show_error("No form data could be loaded");

		$this->load->view("codex/uhstat1.php", array('formData' => getData($formData)));
	}

	/**
	 * Add or update UHSTAT1 data
	 */
	public function saveUHSTAT1Data()
	{
		$person_id = $this->input->get('person_id');

		if (!isset($person_id) || !is_numeric($person_id)) show_error("Person Id missing");

		$this->form_validation->set_error_delimiters('<span class="text-danger">', '</span>');

		// check required fields
		$this->form_validation->set_rules('geburtsstaat', 'Geburtsstaat', 'required', array('required' => $this->p->t('uhstat', 'angabeFehlt')));
		$this->form_validation->set_rules(
			'mutter_geburtsstaat',
			'Geburtsstaat Mutter',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'mutter_bildungsstaat',
			'Bildungsstaat Mutter',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'mutter_geburtsjahr',
			'Geburtsjahr Mutter',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'mutter_bildungmax',
			'Höchste Ausbildung Mutter',
			'required|callback_bildungsstaat_bildungmax_check[m]',
			array(
				'required' => $this->p->t('uhstat', 'angabeFehlt'),
				'bildungsstaat_bildungmax_check' => $this->p->t('uhstat', 'ausbildungBildungsstaatUebereinstimmung')
				//'Land der höchsten Ausbildung muss mit Bildungsstaat übereinstimmen'
				// Bildungsstaat should correspond to state of bildung max
			)
		);
		$this->form_validation->set_rules(
			'vater_geburtsstaat',
			'Geburtsstaat Vater',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'vater_bildungsstaat',
			'Bildungsstaat Vater',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'vater_geburtsjahr',
			'Geburtsjahr Vater',
			'required',
			array('required' => $this->p->t('uhstat', 'angabeFehlt'))
		);
		$this->form_validation->set_rules(
			'vater_bildungmax',
			'Höchste Ausbildung Vater',
			'required|callback_bildungsstaat_bildungmax_check[v]',
			array(
				'required' => $this->p->t('uhstat', 'angabeFehlt'),
				'bildungsstaat_bildungmax_check' => $this->p->t('uhstat', 'ausbildungBildungsstaatUebereinstimmung')
			)
		);

		$uhstat1datenRes = null;
		if ($this->form_validation->run()) // if valid
		{
			// get post fields
			$geburtsstaat = $this->input->post('geburtsstaat');
			$mutter_geburtsstaat = $this->input->post('mutter_geburtsstaat');
			$mutter_geburtsjahr = $this->input->post('mutter_geburtsjahr');
			$mutter_bildungsstaat = $this->input->post('mutter_bildungsstaat');
			$mutter_bildungmax = $this->input->post('mutter_bildungmax');
			$vater_geburtsstaat = $this->input->post('vater_geburtsstaat');
			$vater_geburtsjahr = $this->input->post('vater_geburtsjahr');
			$vater_bildungsstaat = $this->input->post('vater_bildungsstaat');
			$vater_bildungmax = $this->input->post('vater_bildungmax');

			$uhstat1datenloadRes = $this->Uhstat1datenModel->loadWhere(array('person_id' => $person_id));

			if (hasData($uhstat1datenloadRes))
			{
				$uhstat1datenRes = $this->Uhstat1datenModel->update(
					array('person_id' => $person_id),
					array(
						'geburtsstaat' => $geburtsstaat,
						'mutter_geburtsstaat' => $mutter_geburtsstaat,
						'mutter_geburtsjahr' => $mutter_geburtsjahr,
						'mutter_bildungsstaat' => $mutter_bildungsstaat,
						'mutter_bildungmax' => $mutter_bildungmax,
						'vater_geburtsstaat' => $vater_geburtsstaat,
						'vater_geburtsjahr' => $vater_geburtsjahr,
						'vater_bildungsstaat' => $vater_bildungsstaat,
						'vater_bildungmax' => $vater_bildungmax
					)
				);
			}
			else
			{
				$uhstat1datenRes = $this->Uhstat1datenModel->insert(
					array(
						'geburtsstaat' => $geburtsstaat,
						'mutter_geburtsstaat' => $mutter_geburtsstaat,
						'mutter_geburtsjahr' => $mutter_geburtsjahr,
						'mutter_bildungsstaat' => $mutter_bildungsstaat,
						'mutter_bildungmax' => $mutter_bildungmax,
						'vater_geburtsstaat' => $vater_geburtsstaat,
						'vater_geburtsjahr' => $vater_geburtsjahr,
						'vater_bildungsstaat' => $vater_bildungsstaat,
						'vater_bildungmax' => $vater_bildungmax,
						'person_id' =>$person_id
					)
				);
			}
		}

		$formData = $this->_getFormData();

		if (isError($formData)) show_error(getError($formData));

		if (!hasData($formData)) show_error("No form data could be loaded");

		// pass success/error messages to view
		$successMessage = isset($uhstat1datenRes) && isSuccess($uhstat1datenRes) ? $this->p->t('uhstat', 'erfolgreichGespeichert') : '';
		$errorMessage = isset($uhstat1datenRes) && isError($uhstat1datenRes) ? $this->p->t('uhstat', 'fehlerBeimSpeichern') : '';

		// load view with form data
		$this->load->view("codex/uhstat1.php", array(
			'formData' => getData($formData),
			'successMessage' => $successMessage,
			'errorMessage' => $errorMessage
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
		if (!isset($bildungsstaat_typ)) return true;

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
	private function _getFormData()
	{
		$person_id = $this->input->get('person_id');

		if (!isset($person_id) || !is_numeric($person_id)) return error("Person Id missing");

		$formData = array(
			'nation' => array(),
			'abschluss_oesterreich' => array(),
			'abschluss_nicht_oesterreich' => array(),
			'jahre' => array(),
			'languageIdx' => $this->_getLanguageIndex(),
			'person_id' => $person_id
		);

		$nationTextFieldName = $formData['languageIdx'] == 1 ? 'langtext' : 'engltext';

		// get nation list
		$this->load->model('codex/Nation_model', 'NationModel');

		$this->NationModel->addSelect("nation_code, $nationTextFieldName AS nation_text");
		$this->NationModel->addOrder("nation_text");
		$nationRes = $this->NationModel->load();

		if (isError($nationRes)) return $nationRes;

		if (hasData($nationRes)) $formData['nation'] = getData($nationRes);

		// get abschluss list
		$abschlussRes = $this->AbschlussModel->getActiveAbschluesse();

		if (isError($abschlussRes)) return $abschlussRes;

		$abschlussData = getData($abschlussRes);

		if (hasData($abschlussRes))
		{
			foreach (getData($abschlussRes) as $abschluss)
			{
				if ($abschluss->in_oesterreich === true)
					$formData['abschluss_oesterreich'][] = $abschluss;
				elseif ($abschluss->in_oesterreich === false)
					$formData['abschluss_nicht_oesterreich'][] = $abschluss;
				else
				{
					$formData['abschluss_oesterreich'][] = $abschluss;
					$formData['abschluss_nicht_oesterreich'][] = $abschluss;
				}
			}
		}

		// get realistic birth years, dated back from current year
		$currYear = date("Y");
		$formData['jahre'] = range($currYear - self::UPPER_BOUNDARY_YEARS, $currYear - self::LOWER_BOUNDARY_YEARS);

		return success($formData);
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
