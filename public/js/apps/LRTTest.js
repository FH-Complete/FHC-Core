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

import PluginsPhrasen from '../plugins/Phrasen.js';
import {CoreNavigationCmpt} from '../components/navigation/Navigation.js'

import ApiLrt from "../api/LRTTEst.js";

const lrtTestApp = Vue.createApp({
	data: function() {
		return {
		};
	},
	components: {
		CoreNavigationCmpt,
	},
	methods: {
		addNew1MinLrt() {
			this.$api.call(ApiLrt.addNew1MinLrt())
			.then(result => {
				this.dropdowns.studiensemester_array = result.data;
			})
			.catch(this.$fhcAlert.handleSystemError);
		}
	},
	template: `
		<button type="button" class="btn btn-primary" @click="addNew1MinLrt()">Add a 1 min LRT</button>
	`
});

FhcApps.makeExtendable(lrtTestApp);

lrtTestApp.use(PluginsPhrasen).mount('#main');

