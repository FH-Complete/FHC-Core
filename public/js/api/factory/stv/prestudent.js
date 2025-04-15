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
	//------------- Prestudent.js------------------------------------------------------
	get(prestudent_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/prestudent/get/' + prestudent_id
		};
	},
	updatePrestudent(prestudent_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/prestudent/updatePrestudent/' + prestudent_id,
			params
		};
	},
	getBezeichnungZGV() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getBezeichnungZGV/'
		};
	},
	getBezeichnungMZgv() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getBezeichnungMZgv/'
		};
	},
	getBezeichnungDZgv() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getBezeichnungDZgv/'
		};
	},
	getStgs() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/lists/getStgs/'
		};
	},
	getAusbildung() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getAusbildung/'
		};
	},
	getAufmerksamdurch() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getAufmerksamdurch/'
		};
	},
	getBerufstaetigkeit() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getBerufstaetigkeit/'
		};
	},
	getTypenStg() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getTypenStg/'
		};
	},
	getBisstandort() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getBisstandort/'
		};
	},
	//------------- MultiStatus.js------------------------------------------------------
	getHistoryPrestudent(prestudent_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/status/getHistoryPrestudent/' + prestudent_id
		};
	},
	getMaxSem(studiengang_kzs) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/getMaxSemester/',
			params: { studiengang_kzs }
		};
	},
	advanceStatus({prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/advanceStatus/'
				+ prestudent_id + '/'
				+ status_kurzbz + '/'
				+ studiensemester_kurzbz + '/'
				+ ausbildungssemester
		};
	},
	confirmStatus({prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/confirmStatus/'
				+ prestudent_id + '/'
				+ status_kurzbz + '/'
				+ studiensemester_kurzbz + '/'
				+ ausbildungssemester
		};
	},
	isLastStatus(id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/status/isLastStatus/' + id
		};
	},
	deleteStatus({prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester}) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/deleteStatus/'
				+ prestudent_id + '/'
				+ status_kurzbz + '/'
				+ studiensemester_kurzbz + '/'
				+ ausbildungssemester
		};
	},
	getLastBismeldestichtag() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/status/getLastBismeldestichtag/'
		};
	},
	//------------- History.js------------------------------------------------------
	getHistoryPrestudents(person_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/prestudent/getHistoryPrestudents/' + person_id
		};
	},
};