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
	
	profilViewData(uid) {
		let url = "/api/frontend/v1/Profil/profilViewData";
		if(uid){
			url += `/${uid}`;
		}
		return {
			method: 'get',
			url: url
		};
	},
	fotoSperre(value) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Profil/fotoSperre/${value}`
		};
		
	},
	isStudent(uid) {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'get',
			url: '/api/frontend/v1/Profil/isStudent',
			params: { uid }
		};
	},
	isMitarbeiter(uid) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Profil/isMitarbeiter/${uid}`
		};
	},
	getZustellAdresse() {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'get',
			url: '/api/frontend/v1/Profil/getZustellAdresse'
		};
	},
	getZustellKontakt() {
		// TODO(chris): seems to be called from nowhere?
		return {
			method: 'get',
			url: '/api/frontend/v1/Profil/getZustellKontakt'
		};
	},
	getGemeinden(nation, zip) {
		return {
			method: 'get',
			url: `/api/frontend/v1/Profil/getGemeinden/${nation}/${zip}`
		};
		
	},
	getAllNationen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/Profil/getAllNationen'
		};
	},
};