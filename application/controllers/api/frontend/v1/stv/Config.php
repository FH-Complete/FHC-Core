<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (!defined('BASEPATH')) exit('No direct script access allowed');

use CI3_Events as Events;

/**
 * This controller operates between (interface) the JS (GUI) and the back-end
 * Provides data to the ajax get calls about the StV Config
 * This controller works with JSON calls on the HTTP GET or POST and the output is always JSON
 */
class Config extends FHCAPI_Controller
{


	public function __construct()
	{
		// TODO(chris): permissions
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'set' => ['admin:r', 'assistenz:r'],
			'filter' => ['admin:r', 'assistenz:r'],
			'student' => ['admin:r', 'assistenz:r'],
			'students' => ['admin:r', 'assistenz:r']
		]);


		// Load Phrases
		$this->loadPhrases([
			'global',
			'person',
			'lehre',
			'stv',
			'konto',
			'abschlusspruefung',
			'projektarbeit'
		]);

		// Load Config
		$this->load->config('stv');
	}

	/**
	 * get App config
	 */
	public function get()
	{
		$this->load->model('system/Variable_model', 'VariableModel');
		$this->load->config('stv');

		$config = [];

		#number_displayed_past_studiensemester
		$result = $this->VariableModel->getVariables(getAuthUID(), ['number_displayed_past_studiensemester']);
		$data = $this->getDataOrTerminateWithError($result);

		$number_displayed_past_studiensemester_default = $this->config->item('number_displayed_past_studiensemester_default');

		$config['number_displayed_past_studiensemester'] = [
			"type" => "number",
			"label" => $this->p->t('stv', 'settings_no_displayed_past_sem'),
			"value" => $data['number_displayed_past_studiensemester']
				?? $number_displayed_past_studiensemester_default
		];

		#font_size
		$result = $this->VariableModel->getVariables(getAuthUID(), ['stv_font_size']);
		$data = $this->getDataOrTerminateWithError($result);
		$config['font_size'] = [
			"type" => "select",
			"label" => $this->p->t('stv', 'settings_fontsize'),
			"value" => $data['stv_font_size'] ?? "fs_normal",
			"options" => [
				"fs_xx-small" => $this->p->t('stv', 'settings_fontsize_xx-small'),
				"fs_x-small" => $this->p->t('stv', 'settings_fontsize_x-small'),
				"fs_small" => $this->p->t('stv', 'settings_fontsize_small'),
				"fs_normal" => $this->p->t('stv', 'settings_fontsize_normal'),
				"fs_big" => $this->p->t('stv', 'settings_fontsize_big'),
				"fs_huge" => $this->p->t('stv', 'settings_fontsize_huge')
			]
		];

		#others
		Events::trigger('stv_config_get', function & () use (&$config) {
			return $config;
		});

		$this->terminateWithSuccess($config);
	}

	/**
	 * set App config
	 */
	public function set()
	{
		$this->load->model('system/Variable_model', 'VariableModel');
		$this->load->library('form_validation');

		$this->form_validation->set_rules(
			'number_displayed_past_studiensemester',
			$this->p->t('stv', 'settings_no_displayed_past_sem'),
			'required|integer'
		);
		$this->form_validation->set_rules(
			'font_size',
			$this->p->t('stv', 'settings_fontsize'),
			'required|in_list[fs_xx-small,fs_x-small,fs_small,fs_normal,fs_big,fs_huge]'
		);

		Events::trigger('stv_config_validation', $this->form_validation);

		if (!$this->form_validation->run())
			$this->terminateWithValidationErrors($this->form_validation->error_array());


		$this->VariableModel->setVariable(
			getAuthUID(),
			'number_displayed_past_studiensemester',
			$this->input->post('number_displayed_past_studiensemester')
		);
		$this->VariableModel->setVariable(
			getAuthUID(),
			'stv_font_size',
			$this->input->post('font_size')
		);

		Events::trigger('stv_config_set', $this->input);

		$this->terminateWithSuccess();
	}
	
	/*
	 * Get the config for the student filters
	 *
	 * @return void
	 */
	public function filter()
	{
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);

		$this->load->model('crm/Buchungstyp_model', 'BuchungstypModel');

		$this->BuchungstypModel->addOrder('beschreibung');
		
		$result = $this->BuchungstypModel->load();

		$buchungstyp_kurzbz = $this->getDataOrTerminateWithError($result);
		$buchungstyp_kurzbz_plus_all = array_merge([[
			'buchungstyp_kurzbz' => 'all',
			'beschreibung' => $this->p->t('stv', 'konto_all_types')
		]], $buchungstyp_kurzbz);

		$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');

		$result = $this->StatusgrundModel->getAktiveGruende();
		
		$statusgruende = $this->getDataOrTerminateWithError($result);

		$result = [];

		$result[] = [
			'id' => 'filter_konto_count_0',
			'label' => $this->p->t('stv', 'filter_konto_count_0'),
			'type' => 'konto',
			'fixed' => [
				'missing' => true,
				'usestdsem' => true
			],
			'dynamic' => [
				'buchungstyp_kurzbz' => [
					'type' => 'select',
					'values' => $buchungstyp_kurzbz,
					'value_key' => 'buchungstyp_kurzbz',
					'label_key' => 'beschreibung'
				]
			]
		];
		$result[] = [
			'id' => 'filter_konto_missing_counter',
			'label' => $this->p->t('stv', 'filter_konto_missing_counter'),
			'type' => 'konto_counter',
			'dynamic' => [
				'buchungstyp_kurzbz' => [
					'type' => 'select',
					'values' => $buchungstyp_kurzbz_plus_all,
					'value_key' => 'buchungstyp_kurzbz',
					'label_key' => 'beschreibung'
				],
				'samestg' => [
					'type' => 'bool',
					'label' => $this->p->t('stv', 'filter_konto_samestg'),
					'default' => $this->variablelib->getVar('kontofilterstg') == 'true'
				]
			]
		];
		$result[] = [
			'id' => 'filter_documents',
			'label' => $this->p->t('stv', 'filter_documents'),
			'type' => 'documents'
		];
		$result[] = [
			'id' => 'filter_konto_missing_counter_past',
			'label' => $this->p->t('stv', 'filter_konto_missing_counter_past'),
			'type' => 'konto_counter',
			'fixed' => [
				'past' => true
			],
			'dynamic' => [
				'buchungstyp_kurzbz' => [
					'type' => 'select',
					'values' => $buchungstyp_kurzbz_plus_all,
					'value_key' => 'buchungstyp_kurzbz',
					'label_key' => 'beschreibung'
				],
				'samestg' => [
					'type' => 'bool',
					'label' => $this->p->t('stv', 'filter_konto_samestg'),
					'default' => $this->variablelib->getVar('kontofilterstg') == 'true'
				]
			]
		];
		$result[] = [
			'id' => 'filter_konto_missing_studiengebuehr',
			'label' => $this->p->t('stv', 'filter_konto_missing_studiengebuehr'),
			'type' => 'konto',
			'fixed' => [
				'missing' => true,
				'usestdsem' => true
			],
			'dynamic' => [
				'buchungstyp_kurzbz' => [
					'type' => 'select',
					'values' => $buchungstyp_kurzbz,
					'value_key' => 'buchungstyp_kurzbz',
					'label_key' => 'beschreibung'
				]
			]
		];
		$result[] = [
			'id' => 'filter_konto_studiengebuehrerhoeht',
			'label' => $this->p->t('stv', 'filter_konto_studiengebuehrerhoeht'),
			'type' => 'konto',
			'fixed' => [
				'usestdsem' => true
			],
			'dynamic' => [
				'buchungstyp_kurzbz' => [
					'type' => 'select',
					'values' => $buchungstyp_kurzbz,
					'value_key' => 'buchungstyp_kurzbz',
					'label_key' => 'beschreibung'
				]
			]
		];
		$result[] = [
			'id' => 'filter_zgv_without_date',
			'label' => $this->p->t('stv', 'filter_zgv_without_date'),
			'type' => 'zgv'
		];
		$result[] = [
			'id' => 'filter_statusgrund',
			'label' => $this->p->t('stv', 'filter_statusgrund'),
			'type' => 'statusgrund',
			'fixed' => [
				'usestdsem' => true
			],
			'dynamic' => [
				'statusgrund_id' => [
					'type' => 'select',
					'values' => $statusgruende,
					'value_key' => 'statusgrund_id',
					'label_key' => 'bezeichnung'
				]
			]
		];

		Events::trigger('stv_conf_filter', function & () use (&$result) {
			return $result;
		});

		$this->terminateWithSuccess($result);
	}

	public function student()
	{
		$result = [];
		$config = $this->config->item('tabs');

		$result['details'] = [
			'title' => $this->p->t('stv', 'tab_details'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Details.js'),
			'config' => $config['details']
		];

		$result['notes'] = [
			'title' => $this->p->t('stv', 'tab_notes'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Notizen.js'),
			'config'	=> $config['notes'],
			'showSuffix' => ($config['notes']['showCountNotes'] ?? false),
			'suffixhelper' => absoluteJsImportUrl('public/js/helpers/Stv/Studentenverwaltung/Details/Notizen/NotizenSuffixHelper.js')
		];

		$result['contact'] = [
			'title' => $this->p->t('stv', 'tab_contact'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Kontakt.js'),
			'config' => [
				'showBankaccount' => $this->permissionlib->isBerechtigt('mitarbeiter/bankdaten')
					|| $this->permissionlib->isBerechtigt('student/bankdaten')
			]
		];
		$result['prestudent'] = [
			'title' => $this->p->t('stv', 'tab_prestudent'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Prestudent.js'),
			'config' => $config['prestudent']
		];
		$result['status'] = [
			'title' => 'Status',
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/MultiStatus.js'),
			'config' => [
				'showStatusVorruecken' => defined('STATUS_VORRUECKEN_ANZEIGEN') ? STATUS_VORRUECKEN_ANZEIGEN : true,
			]
		];
		$result['documents'] = [
			'title' => $this->p->t('stv', 'tab_documents'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Dokumente.js')
		];
		$result['banking'] = [
			'title' => $this->p->t('stv', 'tab_banking'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Konto.js'),
			'config' => [
				'showZahlungsbestaetigung' => (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && ZAHLUNGSBESTAETIGUNG_ANZEIGEN),
				'showBuchungsnr' => $this->permissionlib->isBerechtigt('admin'),
				'showMahnspanne' => (!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE===true),
				'showCreditpoints' => (defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS == 'true'),
				'columns' => $this->kontoColumns(),
				'additionalCols' => []
			]
		];
		$result['resources'] = [
			'title' => $this->p->t('stv', 'tab_resources'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Betriebsmittel.js'),
			'showOnlyWithUid' => true
		];
		$result['groups'] = [
			'title' => $this->p->t('stv', 'tab_groups'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Groups.js'),
			'showOnlyWithUid' => true
		];
		$result['messages'] = [
			'title' => $this->p->t('stv', 'tab_messages'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Messages.js'),
		];

		$result['grades'] = [
			'title' => $this->p->t('stv', 'tab_grades'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Noten.js'),
			'showOnlyWithUid' => true,
			'config' => [
				'usePoints' => defined('CIS_GESAMTNOTE_PUNKTE') && CIS_GESAMTNOTE_PUNKTE,
				'edit' => 'both', // Possible values: both|header|inline
				'delete' => 'both', // Possible values: both|header|inline
				'documents' => 'both', // Possible values: both|header|inline
				'documentslist' => $this->gradesDocumentsList()
			]
		];

		$result['exam'] = [
			'title' => $this->p->t('stv', 'tab_exam'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Pruefung.js'),
			'showOnlyWithUid' => true
		];

		$result['exemptions'] = [
			'title' => $this->p->t('lehre', 'anrechnungen'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Anrechnungen.js'),
			'config' => $config['exemptions']
		];

		$result['finalexam'] = [
			'title' => $this->p->t('stv', 'tab_finalexam'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Abschlusspruefung.js'),
			'showOnlyWithUid' => true,
			'config' => $config['finalexam']
		];

		$result['projektarbeit'] = [
			'title' => $this->p->t('stv', 'tab_projektarbeit'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Projektarbeit.js'),
			'config' => array_merge(
				$config['projektarbeit'],
				['showVertragsdetails' =>
					defined('FAS_STUDIERENDE_PROJEKTARBEIT_VERTRAGSDETAILS_ANZEIGEN') && FAS_STUDIERENDE_PROJEKTARBEIT_VERTRAGSDETAILS_ANZEIGEN]
			)
		];

		$result['mobility'] = [
			'title' => $this->p->t('stv', 'tab_mobility'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Mobility.js'),
			'showOnlyWithUid' => true
		];

		$result['archive'] = [
			'title' => $this->p->t('stv', 'tab_archive'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Archiv.js'),
			'config' => [
				'showEdit' => $this->permissionlib->isBerechtigt('admin')
			]
		];

		$result['jointstudies'] = [
			'title' => $this->p->t('stv', 'tab_jointstudies'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/JointStudies.js'),
			'showOnlyWithUid' => true
		];

		$result['coursedates'] = [
			'title' => $this->p->t('stv', 'tab_courseDates'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Lehrveranstaltungstermine.js')
		];

		$result['admissionDates'] = [
			'title' => $this->p->t('stv', 'tab_admissionDates'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Aufnahmetermine.js')
		];

		$result['functions'] = [
			'title' => $this->p->t('stv', 'tab_functions'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Funktionen.js'),
			'showOnlyWithUid' => true
		];

		Events::trigger('stv_conf_student', function & () use (&$result) {
			return $result;
		});

		$sortConfig = $this->config->item('student_tab_order');

		$this->terminateWithSuccess($this->sortTabList($result, $sortConfig));
	}

	public function students()
	{
		$result = [];
		$config = $this->config->item('tabs');
		$result['banking'] = [
			'title' => $this->p->t('stv', 'tab_banking'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Konto.js'),
			'config' => [
				'showZahlungsbestaetigung' => (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && ZAHLUNGSBESTAETIGUNG_ANZEIGEN),
				'showBuchungsnr' => $this->permissionlib->isBerechtigt('admin'),
				'showMahnspanne' => (!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE===true),
				'showCreditpoints' => (defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS == 'true'),
				'columns' => $this->kontoColumnsMultiPerson(),
				'additionalCols' => []
			]
		];
		$result['groups'] = [
			'title' => $this->p->t('stv', 'tab_groups'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Groups.js'),
			'showOnlyWithUid' => true
		];
		$result['status'] = [
			'title' => 'Status',
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/MultiStatus.js'),
			'config' => [
				'changeStatusToAbbrecherStgl' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToAbbrecherStud' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToUnterbrecher' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToDiplomand' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToAbsolvent' => $this->permissionlib->isBerechtigt('admin')
			]
		];
		$result['finalexam'] = [
			'title' => $this->p->t('stv', 'tab_finalexam'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Abschlusspruefung.js'),
			'showOnlyWithUid' => true,
			'config' => $config['finalexam']
		];
		$result['archive'] = [
			'title' => $this->p->t('stv', 'tab_archive'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Archiv.js'),
			'config' => [
				'showEdit' => $this->permissionlib->isBerechtigt('admin')
			]
		];

		if($this->permissionlib->isBerechtigt('basis/person'))
		{
			$result['combinePeople'] = [
				'title' => $this->p->t('stv', 'tab_combine_people'),
				'component' => './Stv/Studentenverwaltung/Details/CombinePeople.js',
				'config' => $config['combinePeople']
			];
		}

		$result['kontaktieren'] = [
			'title' => $this->p->t('stv', 'tab_kontaktieren'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Kontaktieren.js'),
		];

		$result['messages'] = [
			'title' => $this->p->t('stv', 'tab_messages'),
			'component' => absoluteJsImportUrl('public/js/components/Stv/Studentenverwaltung/Details/Messages.js'),
		];

		Events::trigger('stv_conf_students', function & () use (&$result) {
			return $result;
		});

		$sortConfig = $this->config->item('students_tab_order');

		$this->terminateWithSuccess($this->sortTabList($result, $sortConfig));
	}

	protected function kontoColumns()
	{
		return [
			'buchungsdatum' => [
				'field' => "buchungsdatum",
				'title' => $this->p->t('konto', 'buchungsdatum')
			],
			'buchungstext' => [
				'field' => "buchungstext",
				'title' => $this->p->t('konto', 'buchungstext')
			],
			'betrag' => [
				'field' => "betrag",
				'title' => $this->p->t('konto', 'betrag')
			],
			'studiensemester_kurzbz' => [
				'field' => "studiensemester_kurzbz",
				'title' => $this->p->t('lehre', 'studiensemester')
			],
			'buchungstyp_kurzbz' => [
				'field' => "buchungstyp_kurzbz",
				'title' => $this->p->t('konto', 'buchungstyp'),
				'visible' => false
			],
			'buchungsnr' => [
				'field' => "buchungsnr",
				'title' => $this->p->t('konto', 'buchungsnr'),
				'visible' => false
			],
			'insertvon' => [
				'field' => "insertvon",
				'title' => $this->p->t('global', 'insertvon'),
				'visible' => false
			],
			'insertamum' => [
				'field' => "insertamum",
				'title' => $this->p->t('global', 'insertamum'),
				'visible' => false
			],
			'kuerzel' => [
				'field' => "kuerzel",
				'title' => $this->p->t('lehre', 'studiengang'),
				'visible' => false
			],
			'anmerkung' => [
				'field' => "anmerkung",
				'title' => $this->p->t('global', 'anmerkung')
			],
			'actions' => [
				'title' => $this->p->t('global', 'actions'),
				'frozen' => true
			]
		];
	}
	protected function kontoColumnsMultiPerson()
	{
		return [
			'person_id' => [
				'field' => "person_id",
				'title' => $this->p->t('person', 'person_id')
			],
			'anrede' => [
				'field' => "anrede",
				'title' => $this->p->t('person', 'anrede'),
				'visible' => false
			],
			'titelpost' => [
				'field' => "titelpost",
				'title' => $this->p->t('person', 'titelpost'),
				'visible' => false
			],
			'titelpre' => [
				'field' => "titelpre",
				'title' => $this->p->t('person', 'titelpre'),
				'visible' => false
			],
			'vorname' => [
				'field' => "vorname",
				'title' => $this->p->t('person', 'vorname')
			],
			'vornamen' => [
				'field' => "vornamen",
				'title' => $this->p->t('person', 'vornamen'),
				'visible' => false
			],
			'nachname' => [
				'field' => "nachname",
				'title' => $this->p->t('person', 'nachname')
			]
		] + $this->kontoColumns();
	}

	/**
	 * Helper function to generate the default documentslist config for the
	 * grades tab.
	 *
	 * The resulting array consists of elements which are associative arrays
	 * that can have the following entries:
	 * title			(required) on the first level this can be HTML code.
	 * permissioncheck	(optional) an URL to an FHCAPI endpoint which returns
	 * 						true or false.
	 * link				(optional) an URL that will be called if "action" and
	 * 						"children" are not defined.
	 * action			(optional) an associative array that describes an
	 * 						POST action that will be called if "children" is
	 * 						not defined.
	 * 						It can have the following entries:
	 * - url			(required) an URL to an FHCAPI endpoint.
	 * - post			(optional) an associative array with the POST data to
	 * 						be sent.
	 * - response		(optional) a string that will be displayed on success.
	 * children			(optional) an array of child elements
	 *
	 * All strings that start with { and end with } in the URLs and the
	 * actions post parameter will be replaced with the corresponding
	 * attribute of the current dataset (e.G: {uid} will be replaced with the
	 * uid of the current dataset)
	 *
	 * @return array
	 */
	protected function gradesDocumentsList()
	{
		$permissioncheck = site_url("api/frontend/v1/documents/permissionAlternativeFormat/{studiengang_kz}");

		$title_ger = $this->p->t("global", "deutsch");
		$title_eng = $this->p->t("global", "englisch");
		$title_ff = $this->p->t("stv", "document_certificate");
		$title_lv = $this->p->t("stv", "document_coursecertificate");

		$link_ff = "documents/export/" .
			"zertifikat.rdf.php/" .
			"Zertifikat" .
			"?stg_kz={studiengang_kz_lv}" .
			"&uid={uid}" .
			"&ss={studiensemester_kurzbz}" .
			"&lvid={lehrveranstaltung_id}";
		$link_lv_ger = "documents/export/" .
			"lehrveranstaltungszeugnis.rdf.php/" .
			"LVZeugnis" .
			"?stg_kz={studiengang_kz}" .
			"&uid={uid}" .
			"&ss={studiensemester_kurzbz}" .
			"&lvid={lehrveranstaltung_id}";
		$link_lv_eng = "documents/export/" .
			"lehrveranstaltungszeugnis.rdf.php/" .
			"LVZeugnisEng" .
			"?stg_kz={studiengang_kz}" .
			"&uid={uid}" .
			"&ss={studiensemester_kurzbz}" .
			"&lvid={lehrveranstaltung_id}";

		$archive_url = "api/frontend/v1/documents/archiveSigned";
		$archive_response = $this->p->t("stv", "document_signed_and_archived");
		$archive_post_ff = [
			"xml" => "zertifikat.rdf.php",
			"xsl" => "Zertifikat",
			"stg_kz" => "{studiengang_kz_lv}",
			"uid" => "{uid}",
			"ss" => "{studiensemester_kurzbz}",
			"lvid" => "{lehrveranstaltung_id}"
		];
		$archive_post_lv_ger = [
			"xml" => "lehrveranstaltungszeugnis.rdf.php",
			"xsl" => "LVZeugnis",
			"stg_kz" => "{studiengang_kz}",
			"uid" => "{uid}",
			"ss" => "{studiensemester_kurzbz}",
			"lvid" => "{lehrveranstaltung_id}"
		];
		$archive_post_lv_eng = [
			"xml" => "lehrveranstaltungszeugnis.rdf.php",
			"xsl" => "LVZeugnisEng",
			"stg_kz" => "{studiengang_kz}",
			"uid" => "{uid}",
			"ss" => "{studiensemester_kurzbz}",
			"lvid" => "{lehrveranstaltung_id}"
		];

		$list = [
			[
				'title' => '<i class="fa fa-download" title="' . $this->p->t("stv", "document_download") . '"></i>',
				'children' => [
					[
						'title' => $title_ff,
						'link' => site_url($link_ff)
					],
					[
						'title' => $title_lv,
						'children' => [
							[
								'title' => $title_ger,
								'link' => site_url($link_lv_ger),
								'children' => [
									[
										'title' => 'PDF',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_ger)
									],
									[
										'title' => 'DOC',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_ger . "&output=doc")
									],
									[
										'title' => 'ODT',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_ger . "&output=odt")
									]
								]
							],
							[
								'title' => $title_eng,
								'link' => site_url($link_lv_eng),
								'children' => [
									[
										'title' => 'PDF',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_eng)
									],
									[
										'title' => 'DOC',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_eng . "&output=doc")
									],
									[
										'title' => 'ODT',
										'permissioncheck' => $permissioncheck,
										'link' => site_url($link_lv_eng . "&output=odt")
									]
								]
							]
						]
					]
				]
			],
			[
				'title' => '<i class="fas fa-archive" title="' . $this->p->t("stv", "document_archive") . '"></i>',
				'children' => [
					[
						'title' => $title_ff,
						'action' => [
							'url' => site_url($archive_url),
							'post' => $archive_post_ff,
							'response' => $archive_response
						]
					],
					[
						'title' => $title_lv,
						'children' => [
							[
								'title' => $title_ger,
								'action' => [
									'url' => site_url($archive_url),
									'post' => $archive_post_lv_ger,
									'response' => $archive_response
								]
							],
							[
								'title' => $title_eng,
								'action' => [
									'url' => site_url($archive_url),
									'post' => $archive_post_lv_eng,
									'response' => $archive_response
								]
							]
						]
					]
				]
			]
		];

		return $list;
	}

	/**
	 * Sort tab list
	 *
	 * @param array		$input
	 * @param array		$config
	 *
	 * @return array
	 */
	protected function sortTabList($input, $config)
	{
		// prepare config
		if (!$config || !is_array($config))
			$config = [];
		else
			$config = array_flip($config);

		// fill missing items in config
		foreach (array_keys($input) as $key) {
			if (!isset($config[$key]))
				$config[$key] = count($config);
		}

		// do the sorting
		uksort($input, function ($a, $b) use ($config) {
			return $config[$a] - $config[$b];
		});
		
		return $input;
	}
}
