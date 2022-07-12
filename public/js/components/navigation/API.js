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
const CORE_NAVIGATION_CMPT_TIMEOUT = 2000;

/**
 *
 */
export const CoreNavigationAPIs = {
	/**
	 *
	 */
	getHeader: function(navigationPage) {
		return CoreRESTClient.get(
			'system/Navigation/header',
			{
				navigation_page: navigationPage
			},
			{
				timeout: CORE_NAVIGATION_CMPT_TIMEOUT
			}
		);
	},
	/**
	 *
	 */
	getMenu: function(navigationPage) {
		return CoreRESTClient.get(
			'system/Navigation/menu',
			{
				navigation_page: navigationPage
			},
			{
				timeout: CORE_NAVIGATION_CMPT_TIMEOUT
			}
		);
	}
};

