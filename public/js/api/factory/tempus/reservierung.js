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
	getInformation() {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Reservierung/getInformation',
		};
	},
	searchTeilnehmer(query)
	{
		return {
			method: 'get',
			url: `/api/frontend/v1/tempus/Reservierung/getLektor?query=${encodeURIComponent(query)}`
		};
	},
	searchGroup(query)
	{
		return {
			method: 'get',
			url: `/api/frontend/v1/tempus/Reservierung/searchGroup?query=${encodeURIComponent(query)}`
		};
	},
	getGruppen(query) {
		return {
			method: 'get',
			url: `/api/frontend/v1/tempus/Reservierung/getGruppen?query=${encodeURIComponent(query)}`
		};
	},
	getRollen() {
		return {
			method: 'get',
			url: '/api/frontend/v1/tempus/Reservierung/getRollen',
		};
	},
	addReservierung(titel, beschreibung, ort_kurzbz, start_date, end_date, teilnehmer, specialFinalGroups, specialGroups, groups) {
		return {
			method: 'post',
			url: '/api/frontend/v1/tempus/Reservierung/addReservierung',
			params: { titel, beschreibung, ort_kurzbz, start_date, end_date, teilnehmer, specialFinalGroups, specialGroups, groups}
		};
	},

};