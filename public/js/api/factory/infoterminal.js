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
	getUserData(cardNumber) {
		return {
			method: 'get',
			url: '/api/frontend/v1/InfoTerminalAPI/getUserData',
			params: {
				key_input: cardNumber
			}
		};
	},
	getPersonalEvents(cardNumber, start_date, end_date) {
		return {
			method: 'post',
			url: '/api/frontend/v1/InfoTerminalAPI/getPersonalEvents',
			params: { start_date, end_date, key_input: cardNumber }
		};
	},
	getStudySemestersStartingFrom(cardNumber, start = null)
	{
		return {
			method: 'get',
			url: 'api/frontend/v1/InfoTerminalAPI/getStudySemestersStartingFrom',
			params: { key_input: cardNumber, start }
		};
	},
	getContainingOrNearestStudySemesterByDateRange(cardNumber, from, to) {
		return {
			method: 'get',
			url: 'api/frontend/v1/InfoTerminalAPI/getContainingOrNearestStudySemesterByDateRange',
			params: {
				key_input: cardNumber,
				from,
				to,
			}
		};
	},
	getHours() {
		return {
			method: 'get',
			url: '/api/frontend/v1/InfoTerminalAPI/getHours'
		};
	},
	loadInfoTerminalRenderers() {
		return {
			method: 'get',
			url: '/api/frontend/v1/InfoTerminalAPI/getInfoTerminalRenderers',
			params: {
			}
		};
	},
	getCompactibleEventTypes(){
		return {
			method: 'get',
			url: '/api/frontend/v1/InfoTerminalAPI/compactibleEventTypes',
		}
	},
	getContentPage(cardNumber, content_id, version = null, sprache = null, sichtbar = null) {
		return {
			method: 'get',
			url: '/api/frontend/v1/InfoTerminalAPI/getContentPage',
			params: {
				key_input: cardNumber,
				content_id,
				...(version ? { version } : {}),
				...(sprache ? { sprache } : {}),
				...(sichtbar ? { sichtbar } : {}),
			},
		};
	},
};
