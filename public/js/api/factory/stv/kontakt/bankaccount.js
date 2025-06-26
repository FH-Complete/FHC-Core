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
			url: 'api/frontend/v1/stv/kontakt/getBankverbindung/' + uid
		};
	},
	add(uid, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/addNewBankverbindung/' + uid,
			params
		};
	},
	load(bankverbindung_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/loadBankverbindung/',
			params: { bankverbindung_id }
		};
	},
	update(bankverbindung_id, params) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/updateBankverbindung/' + bankverbindung_id,
			params
		};
	},
	delete(bankverbindung_id) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/kontakt/deleteBankverbindung/',
			params: { bankverbindung_id }
		};
	}
};