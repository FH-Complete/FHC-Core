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
	getStgs() {
		return {
			method: 'get',
			url: '/api/frontend/v1/studstatus/leitung/getActiveStgs'
		};
	},
	getAntraege(url, config, params) {
		return {
			method: 'get',
			url: '/api/frontend/v1/studstatus/leitung/getAntraege/' + url
		};
	},
	getHistory(antrag_id) {
		return {
			method: 'get',
			url: '/api/frontend/v1/studstatus/leitung/getHistory/' + antrag_id
		};
	},
	getPrestudents(query) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/getPrestudents',
			params: { query }
		};
	},
	approve(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/approveAntrag',
			params: antrag
		};
	},
	reject(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/rejectAntrag',
			params: antrag
		};
	},
	reopen(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/reopenAntrag',
			params: antrag
		};
	},
	pause(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/pauseAntrag',
			params: antrag
		};
	},
	unpause(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/unpauseAntrag',
			params: antrag
		};
	},
	object(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/objectAntrag',
			params: antrag
		};
	},
	approveObjection(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/approveObjection',
			params: antrag
		};
	},
	denyObjection(antrag) {
		return {
			method: 'post',
			url: '/api/frontend/v1/studstatus/leitung/denyObjection',
			params: antrag
		};
	}
};