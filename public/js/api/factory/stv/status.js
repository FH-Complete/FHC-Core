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
	//------------- Modal.js------------------------------------------------------
	insertStatus(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/insertStatus/' + id,
			params
		};
	},
	loadStatus({prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/loadStatus/'
				+ prestudent_id + '/'
				+ status_kurzbz + '/'
				+ studiensemester_kurzbz + '/'
				+ ausbildungssemester
		};
	},
	updateStatus({
		prestudent_id,
		status_kurzbz,
		studiensemester_kurzbz,
		ausbildungssemester
	}, params) {
		return {

			method: 'post',
			url: 'api/frontend/v1/stv/status/updateStatus/'
				+ prestudent_id + '/'
				+ status_kurzbz + '/'
				+ studiensemester_kurzbz + '/'
				+ ausbildungssemester,
			params
		};
	},
	getStudienplaene(prestudent_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getStudienplaene/' + prestudent_id
		};
	},
	getStudiengang(prestudent_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getStudiengang/' + prestudent_id
		};
	},
	getStatusgruende() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/status/getStatusgruende/'
		};
	},
	getStati() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/lists/getStati/'
		};
	},
	//------------- Dropdown.js------------------------------------------------------
	addStudent(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/addStudent/' + id,
			params
		};
	},
	changeStatus(id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/changeStatus/' + id,
			params
		};
	},
	getStatusarray() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/status/getStatusarray/'
		};
	}
};