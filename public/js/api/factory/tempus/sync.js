/**
 * Copyright (C) 2026 fhcomplete.org
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
	getSyncs(studiensemester_kurzbz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/KalenderSync/getSyncs',
			params: { studiensemester_kurzbz }
		};
	},
	loadSync(kalender_syncstatus_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/KalenderSync/loadSync',
			params: { kalender_syncstatus_id }
		};
	},
	getSyncStatus() {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/KalenderSync/getSyncStatus',
		};
	},
	add(formData) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/KalenderSync/add',
			params: { formData }
		};
	},
	delete(kalender_syncstatus_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/KalenderSync/delete',
			params: { kalender_syncstatus_id }
		};
	},
	start(formData) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/KalenderSync/start',
			params: { formData }
		};
	},
	updateSync(formData) {
		return {
			method: 'post',
			url: 'api/frontend/v1/tempus/KalenderSync/updateSync',
			params: { formData }
		};
	},
	getStudienplan(oe_kurzbz, studiensemester_kurzbz, ausbildungssemester) {
		return {
			method: 'get',
			url: 'api/frontend/v1/tempus/KalenderSync/getStudienplan',
			params: { oe_kurzbz, studiensemester_kurzbz, ausbildungssemester }
		};
	},

	getMaxSemester(studiengang_kzs) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/status/getMaxSemester/',
			params: { studiengang_kzs }
		};
	},

};