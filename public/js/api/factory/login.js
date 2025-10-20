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
	loginLDAP(params) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Login/loginLDAP',
			params
		};
	},
	loginASByUid(params) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Login/loginASByUid',
			params
		};
	},
	loginASByPersonId(params) {
		return {
			method: 'post',
			url: '/api/frontend/v1/Login/loginASByPersonId',
			params
		};
	},
	whoAmI() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Login/whoAmI'
		};
	},
	logout() {
		return {
			method: 'get',
			url: '/system/Logout'
		};
	},
	searchUser(query) {
		return {
			method: 'get',
			url: '/api/frontend/v1/Login/searchUser?query=' + encodeURIComponent(query)
		};
	}
};

