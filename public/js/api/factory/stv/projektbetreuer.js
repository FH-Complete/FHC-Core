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
	getProjektbetreuer(projektarbeit_id ) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektbetreuer/getProjektbetreuer',
			params: { projektarbeit_id }
		};
	},
	getBetreuerarten() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektbetreuer/getBetreuerarten'
		};
	},
	getDefaultStundensaetze(person_id, studiensemester_kurzbz) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektbetreuer/getDefaultStundensaetze',
			params: { person_id, studiensemester_kurzbz }
		};
	},
	getNoten() {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektbetreuer/getNoten'
		};
	},
	saveProjektbetreuer(projektarbeit_id, projektbetreuerListe) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektbetreuer/saveProjektbetreuer',
			params: { projektarbeit_id, projektbetreuerListe }
		};
	},
	deleteProjektbetreuer(projektarbeit_id, person_id, betreuerart_kurzbz) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektbetreuer/deleteProjektbetreuer',
			params: { projektarbeit_id, person_id, betreuerart_kurzbz }
		};
	},
	getProjektbetreuerBySearchQuery(searchString) {
		return {
			method: 'get',
			url: 'api/frontend/v1/stv/projektbetreuer/getProjektbetreuerBySearchQuery',
			params: { searchString }
		};
	},
	validateProjektbetreuer(projektbetreuer) {
		return {
			method: 'post',
			url: 'api/frontend/v1/stv/projektbetreuer/validateProjektbetreuer',
			params: { projektbetreuer }
		};
	}
};
