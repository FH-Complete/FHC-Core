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
	getAbschlusspruefung(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getAbschlusspruefung/' + uid
		};
	},
	addNewAbschlusspruefung(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/insertAbschlusspruefung/',
			params
		};
	},
	loadAbschlusspruefung(id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/loadAbschlusspruefung/',
			params: { id }
		};
	},
	updateAbschlusspruefung(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/updateAbschlusspruefung/',
			params
		};
	},
	deleteAbschlusspruefung(id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/deleteAbschlusspruefung/',
			params: { id }
		};
	},
	getTypenAbschlusspruefung() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getTypenAbschlusspruefung/'
		};
	},
	getTypenAntritte() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getTypenAntritte/'
		};
	},
	getBeurteilungen() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getBeurteilungen/'
		};
	},
	getAkadGrade(studiengang_kz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/getAkadGrade/',
			params: { studiengang_kz }
		};
	},
	getTypStudiengang(studiengang_kz) {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/getTypStudiengang/',
			params: { studiengang_kz }
		};
	},
	getMitarbeiter(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getMitarbeiter/' + searchString
		};
	},
	getPruefer(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getPruefer/' + searchString
		};
	},
	getNoten() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getNoten/'
		};
	},
	checkForExistingExams(uids) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/abschlusspruefung/checkForExistingExams/',
			params: { uid }
		};
	},
	getAllMitarbeiter(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getAllMitarbeiter/'
		};
	},
	getAllPersons(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/abschlusspruefung/getAllPersons/'
		};
	}
};