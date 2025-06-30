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
	getMobilitaeten(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getMobilitaeten/' + uid
		};
	},
	getProgramsMobility() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getProgramsMobility/'
		};
	},
	addNewMobility(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/insertMobility/',
			params
		};
	},
	loadMobility(bisio_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/loadMobility/' + bisio_id
		};
	},
	updateMobility(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/updateMobility/',
			params
		};
	},
	deleteMobility(bisio_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/deleteMobility/',
			params: { bisio_id }
		};
	},
	getLVList(studiengang_kz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getLVList/' + studiengang_kz
		};
	},
	getAllLehreinheiten(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/getAllLehreinheiten/',
			params
		};
	},
	getLvsandLesByStudent(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getLvsandLesByStudent/' + uid
		};
	},
	getPurposes(bisio_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getPurposes/' + bisio_id
		};
	},
	getSupports(bisio_id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getSupports/' + bisio_id
		};
	},
	getListPurposes() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getListPurposes/'
		};
	},
	getListSupports() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/mobility/getListSupports/'
		};
	},
	deleteMobilityPurpose(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/deleteMobilityPurpose/',
			params
		};
	},
	addMobilityPurpose(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/addMobilityPurpose/' + params.bisio_id,
			params: params
		};
	},
	deleteMobilitySupport(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/deleteMobilitySupport/' + params.bisio_id,
			params
		};
	},
	addMobilitySupport(params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/mobility/addMobilitySupport/' + params.bisio_id,
			params
		};
	}
};