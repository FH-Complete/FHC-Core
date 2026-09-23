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
	get(uid) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/kontakt/getKontakte/' + uid
		};
	},
	add(uid, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/addNewContact/' + uid,
			params
		};
	},
	load(kontakt_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/loadContact/',
			params: { kontakt_id }
		};
	},
	update(kontakt_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/updateContact/' + kontakt_id,
			params
		};
	},
	delete(kontakt_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/deleteContact/',
			params: { kontakt_id }
		};
	},
	getTypes() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/kontakt/getKontakttypen/'
		};
	},
	getStandorteByFirma(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/kontakt/getStandorteByFirma/' + searchString
		};
	}
};