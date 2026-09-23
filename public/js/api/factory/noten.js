/**
 * Copyright (C) 2025 fhcomplete.org
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

export default {
	getCisConfig() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getCisConfig'
		};
	},
	getNoten() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getNoten'
		};
	},
	getBenotungstoolContext(sem_kurzbz, lv_id = null) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getBenotungstoolContext',
			params: { sem_kurzbz, lv_id }
		};
	},
	getLvForStudiengang(studiengang_kz, sem_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getLvForStudiengang',
			params: { studiengang_kz, sem_kurzbz }
		};
	},
	getLehreinheitenForLv(lv_id, sem_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getLehreinheitenForLv',
			params: { lv_id, sem_kurzbz }
		};
	},
	getLektorenForLehreinheit(lv_id, sem_kurzbz, lehreinheit_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getLektorenForLehreinheit',
			params: { lv_id, sem_kurzbz, lehreinheit_id }
		};
	},
	// -> { students, domain }
	getStudentenNoten(lv_id, sem_kurzbz) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Noten/getStudentenNoten',
			params: { lv_id, sem_kurzbz }
		};
	},
	getNoteByPunkte(lv_id, sem_kurzbz, punkte) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/getNoteByPunkte',
			params: { lv_id, sem_kurzbz, punkte }
		};
	},

	// Each write answers { <uid>: { lvgesamtnote, verlauf, pruefung } }. A rejected row of a bulk
	// write is { <uid>: { error: { code, message } } }.

	// datum: the day of Antritt 1 (YYYY-MM-DD); without it the server takes today
	saveLvNote(lv_id, sem_kurzbz, student_uid, note, punkte, datum) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveLvNote',
			params: { lv_id, sem_kurzbz, student_uid, note, punkte, datum }
		};
	},
	// lv_noten: [{ uid, note, punkte }]
	importLvNoten(lv_id, sem_kurzbz, lv_noten) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/importLvNoten',
			params: { lv_id, sem_kurzbz, lv_noten }
		};
	},
	// pruefung: { pruefung_id (null = a new Pruefung), lehreinheit_id, datum, note, punkte, mitarbeiter_uid }
	savePruefung(lv_id, sem_kurzbz, student_uid, pruefung) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/savePruefung',
			params: { lv_id, sem_kurzbz, student_uid, ...pruefung }
		};
	},
	// students: [{ uid, lehreinheit_id }]; pruefung: { datum, note, punkte, mitarbeiter_uid }
	createPruefungen(lv_id, sem_kurzbz, students, pruefung) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/createPruefungen',
			params: { lv_id, sem_kurzbz, students, ...pruefung }
		};
	},
	// pruefungen: [{ uid, lehreinheit_id, datum, note, punkte }]
	importPruefungen(lv_id, sem_kurzbz, pruefungen) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/importPruefungen',
			params: { lv_id, sem_kurzbz, pruefungen }
		};
	},
	saveFreigabe(lv_id, sem_kurzbz, password, uids) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Noten/saveFreigabe',
			params: { lv_id, sem_kurzbz, password, uids }
		};
	}
};
