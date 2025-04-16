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
	getPruefungen(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getPruefungen/' + uid
		};
	},
	loadPruefung(pruefung_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/loadPruefung/' + pruefung_id
		};
	},
	getTypenPruefungen() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getTypenPruefungen'
		};
	},
	getAllLehreinheiten(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/pruefung/getAllLehreinheiten/',
			params
		};
	},
	getLvsByStudent(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getLvsByStudent/' + uid
		};
	},
	getLvsandLesByStudent(uid, semester) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getLvsandLesByStudent/' + uid + '/' + semester
		};
	},
	getLvsAndMas(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getLvsAndMas/' + uid
		};
	},
	getMitarbeiterLv(id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getMitarbeiterLv/' + id
		};
	},
	getNoten() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/pruefung/getNoten'
		};
	},
	checkZeugnisnoteLv(params) {
		return 	{
			method: 'post',
			url: 'api/frontend/v1/stv/pruefung/checkZeugnisnoteLv/',
			params
		};
	},
	addPruefung(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/pruefung/insertPruefung/',
			params
		};
	},
	updatePruefung(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/pruefung/updatePruefung/' + id,
			params
		};
	},
	deletePruefung(id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/pruefung/deletePruefung/' + id
		};
	}
};