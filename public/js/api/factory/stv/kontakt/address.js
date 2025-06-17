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
			url: 'api/frontend/v1/stv/kontakt/getAdressen/' + uid
		};
	},
	add(uid, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/addNewAddress/' + uid,
			params
		};
	},
	load(address_id){
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/loadAddress/',
			params: { address_id }
		};
	},
	update(address_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/updateAddress/' + address_id,
			params
		};
	},
	delete(address_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/deleteAddress/',
			params: { address_id }
		};
	},
	getTypes() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/kontakt/getAdressentypen/'
		};
	},
	getPlaces(plz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/address/getPlaces/' + plz
		};
	},
	getNations() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/address/getNations/'
		};
	},
	getAllFirmen(){
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/kontakt/getAllFirmen/'
		}
	}
};