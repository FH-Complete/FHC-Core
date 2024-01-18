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
const CORE_BISMELDESTICHTAG_CMPT_TIMEOUT = 2000;

/**
 *
 */
export const BismeldestichtagAPIs = {
	/**
	 *
	 */
	getStudiensemester: function() {
		return CoreRESTClient.get(
			'codex/Bismeldestichtag/getStudiensemester',
			null,
			{
				timeout: CORE_BISMELDESTICHTAG_CMPT_TIMEOUT
			}
		);
	},
	getBismeldestichtage: function() {
		return CoreRESTClient.get(
			'codex/Bismeldestichtag/getBismeldestichtage',
			null,
			{
				timeout: CORE_BISMELDESTICHTAG_CMPT_TIMEOUT
			}
		);
	},
	addBismeldestichtag: function(wsParams) {
		return CoreRESTClient.post(
			'codex/Bismeldestichtag/addBismeldestichtag',
			{
				meldestichtag: wsParams.meldestichtag,
				studiensemester_kurzbz: wsParams.studiensemester_kurzbz
			},
			{
				timeout: CORE_BISMELDESTICHTAG_CMPT_TIMEOUT
			}
		);
	},
	deleteBismeldestichtag: function(wsParams) {
		return CoreRESTClient.post(
			'codex/Bismeldestichtag/deleteBismeldestichtag',
			{
				meldestichtag_id: wsParams.meldestichtag_id
			},
			{
				timeout: CORE_BISMELDESTICHTAG_CMPT_TIMEOUT
			}
		);
	}
};

