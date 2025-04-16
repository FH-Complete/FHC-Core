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
	getAllBetriebsmittel(type, id) {
		return {
			method: 'get',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/getAllBetriebsmittel/' + type + '/' + id
		};
	},
	addNewBetriebsmittel(person_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/addNewBetriebsmittel/' + person_id,
			params
		};
	},
	loadBetriebsmittel(betriebsmittelperson_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/loadBetriebsmittel/' + betriebsmittelperson_id
		};
	},
	updateBetriebsmittel(betriebsmittelperson_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/updateBetriebsmittel/' + betriebsmittelperson_id,
			params
		};
	},
	deleteBetriebsmittel(betriebsmittelperson_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/deleteBetriebsmittel/' +	betriebsmittelperson_id
		};
	},
	getTypenBetriebsmittel() {
		return {
			method: 'get',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/getTypenBetriebsmittel/'
		};
	},
	loadInventarliste(query) {
		return {
			method: 'get',
			url: 'api/frontend/v1/betriebsmittel/betriebsmittelP/loadInventarliste/' +	query
		};
	}
};
