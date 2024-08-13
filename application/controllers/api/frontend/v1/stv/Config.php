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
			'student' => ['admin:r', 'assistenz:r'],
			'students' => ['admin:r', 'assistenz:r']
		]);


		// Load Phrases
		$this->loadPhrases([
			'global',
			'person',
			'lehre',
			'stv',
			'konto'
		]);
	}

	public function student()
	{
		$result = [];
		$result['details'] = [
			'title' => $this->p->t('stv', 'tab_details'),
			'component' => './Stv/Studentenverwaltung/Details/Details.js'
		];
		$result['notes'] = [
			'title' => $this->p->t('stv', 'tab_notes'),
			'component' => './Stv/Studentenverwaltung/Details/Notizen.js'
		];
		$result['contact'] = [
			'title' => $this->p->t('stv', 'tab_contact'),
			'component' => './Stv/Studentenverwaltung/Details/Kontakt.js',
			'config' => [
				'showBankaccount' => $this->permissionlib->isBerechtigt('mitarbeiter/bankdaten')
					|| $this->permissionlib->isBerechtigt('student/bankdaten')
			]
		];
		$result['prestudent'] = [
			'title' => $this->p->t('stv', 'tab_prestudent'),
			'component' => './Stv/Studentenverwaltung/Details/Prestudent.js'
		];
		$result['status'] = [
			'title' => 'Status',
			'component' => './Stv/Studentenverwaltung/Details/MultiStatus.js'
		];
		$result['banking'] = [
			'title' => $this->p->t('stv', 'tab_banking'),
			'component' => './Stv/Studentenverwaltung/Details/Konto.js',
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
			'component' => './Stv/Studentenverwaltung/Details/Betriebsmittel.js'
		];
		/* TODO(chris): Ausgeblendet für Testing
		$result['grades'] = [
			'title' => $this->p->t('stv', 'tab_grades'),
			'component' => './Stv/Studentenverwaltung/Details/Noten.js'
		];
		*/

		Events::trigger('stv_conf_student', function & () use (&$result) {
			return $result;
		});

		$this->terminateWithSuccess($result);
	}

	public function students()
	{
		$result = [];
		$result['banking'] = [
			'title' => $this->p->t('stv', 'tab_banking'),
			'component' => './Stv/Studentenverwaltung/Details/Konto.js',
			'config' => [
				'showZahlungsbestaetigung' => (defined('ZAHLUNGSBESTAETIGUNG_ANZEIGEN') && ZAHLUNGSBESTAETIGUNG_ANZEIGEN),
				'showBuchungsnr' => $this->permissionlib->isBerechtigt('admin'),
				'showMahnspanne' => (!defined('FAS_KONTO_SHOW_MAHNSPANNE') || FAS_KONTO_SHOW_MAHNSPANNE===true),
				'showCreditpoints' => (defined('FAS_KONTO_SHOW_CREDIT_POINTS') && FAS_KONTO_SHOW_CREDIT_POINTS == 'true'),
				'columns' => $this->kontoColumnsMultiPerson(),
				'additionalCols' => []
			]
		];
		$result['status'] = [
			'title' => 'Status',
			'component' => './Stv/Studentenverwaltung/Details/MultiStatus.js',
			'config' => [
				'changeStatusToAbbrecherStgl' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToAbbrecherStud' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToUnterbrecher' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToDiplomand' => $this->permissionlib->isBerechtigt('admin'),
				'changeStatusToAbsolvent' => $this->permissionlib->isBerechtigt('admin')
			]
		];

		Events::trigger('stv_conf_students', function & () use (&$result) {
			return $result;
		});

		$this->terminateWithSuccess($result);
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
}
