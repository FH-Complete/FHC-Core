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

import {LogsViewerTabulatorOptions} from './TabulatorSetup.js';
import {LogsViewerTabulatorEventHandlers} from './TabulatorSetup.js';

import {CoreFilterCmpt} from '../../components/filter/Filter.js';
import {CoreNavigationCmpt} from '../../components/navigation/Navigation.js';

const logsViewerApp = Vue.createApp({
	data: function() {
		return {
			appSideMenuEntries: {},
			logsViewerTabulatorOptions: LogsViewerTabulatorOptions,
			logsViewerTabulatorEventHandlers: LogsViewerTabulatorEventHandlers
		};
	},
	components: {
		CoreNavigationCmpt,
		CoreFilterCmpt
	},
	methods: {
		newSideMenuEntryHandler: function(payload) {
			this.appSideMenuEntries = payload;
		}
	}
});

logsViewerApp.mount('#main');

