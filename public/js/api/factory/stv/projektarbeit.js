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
	getProjektarbeit(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/getProjektarbeit',
			params: { uid }
		};
	},
	getTypenProjektarbeit() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/getTypenProjektarbeit'
		};
	},
	getFirmen(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/getFirmen',
			params: {searchString}
		};
	},
	getLehrveranstaltungen(student_uid, studiengang_kz, studiensemester_kurzbz, additional_lehrveranstaltung_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/getLehrveranstaltungen',
			params: { student_uid, studiengang_kz, studiensemester_kurzbz, additional_lehrveranstaltung_id }
		};
	},
	getNoten() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/getNoten'
		};
	},
	loadProjektarbeit(projektarbeit_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektarbeit/loadProjektarbeit',
			params: { projektarbeit_id }
		};
	},
	addNewProjektarbeit(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektarbeit/insertProjektarbeit',
			params
		};
	},
	updateProjektarbeit(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektarbeit/updateProjektarbeit',
			params
		};
	},
	deleteProjektarbeit(projektarbeit_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektarbeit/deleteProjektarbeit',
			params: { projektarbeit_id }
		};
	}
};