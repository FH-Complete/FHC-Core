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
	saveCustomFilter(wsParams) {
		return {
			method: 'post',
			url: '/api/frontend/v1/filter/saveCustomFilter',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				customFilterName: wsParams.customFilterName
			}
		};
	},
	removeCustomFilter(wsParams) {
		return {
			method: 'post',
			url: '/api/frontend/v1/filter/removeCustomFilter',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				filterId: wsParams.filterId
			}
		};
	},
	applyFilterFields(wsParams) {
		return {
			method: 'post',
			url: '/api/frontend/v1/filter/applyFilterFields',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				filterFields: wsParams.filterFields
			}
		};
	},
	addFilterField(wsParams) {
		return {
			method: 'post',
			url: '/api/frontend/v1/filter/addFilterField',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				filterField: wsParams.filterField
			}
		};
	},
	removeFilterField(wsParams) {
		return {
			method: 'post',
			url: '/api/frontend/v1/filter/removeFilterField',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				filterField: wsParams.filterField
			}
		};
	},
	getFilterById(wsParams) {
		return {
			method: 'get',
			url: '/api/frontend/v1/filter/getFilter',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType,
				filterId: wsParams.filterId
			}
		};
	},
	getFilter(wsParams) {
		return {
			method: 'get',
			url: '/api/frontend/v1/filter/getFilter',
			params: {
				filterUniqueId: wsParams.filterUniqueId,
				filterType: wsParams.filterType
			}
		};
	}
};