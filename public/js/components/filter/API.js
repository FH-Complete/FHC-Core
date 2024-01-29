/**
 * Copyright (C) 2022 fhcomplete.org
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

import {CoreRESTClient} from '../../RESTClient.js';

// 
const CORE_FILTER_CMPT_TIMEOUT = 7000;

/**
 *
 */
export const CoreFilterAPIs = {
	/**
	 *
	 */
	saveCustomFilter: function(wsParams) {
		return CoreRESTClient.post(
                        'components/Filter/saveCustomFilter',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                customFilterName: wsParams.customFilterName
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	removeCustomFilter: function(wsParams) {
		return CoreRESTClient.post(
                        'components/Filter/removeCustomFilter',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                filterId: wsParams.filterId
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	applyFilterFields: function(wsParams) {
		return CoreRESTClient.post(
                        'components/Filter/applyFilterFields',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                filterFields: wsParams.filterFields
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	addFilterField: function(wsParams) {
		return CoreRESTClient.post(
                        'components/Filter/addFilterField',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                filterField: wsParams.filterField
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	removeFilterField: function(wsParams) {
		return CoreRESTClient.post(
                        'components/Filter/removeFilterField',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                filterField: wsParams.filterField
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	getFilterById: function(wsParams) {
		return CoreRESTClient.get(
                        'components/Filter/getFilter',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType,
                                filterId: wsParams.filterId
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	},
	/**
	 *
	 */
	getFilter: function(wsParams) {
		return CoreRESTClient.get(
                        'components/Filter/getFilter',
                        {
                                filterUniqueId: wsParams.filterUniqueId,
                                filterType: wsParams.filterType
                        },
                        {
                                timeout: CORE_FILTER_CMPT_TIMEOUT
                        }
                );
	}
};

