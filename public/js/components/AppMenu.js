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

import ApiNavigation from '../api/factory/navigation.js';

export default {
	name: 'AppMenu',
	props: {
		appIdentifier: {
			type: String,
			required: true
		},
		navigationPage: {
			type: String,
			default: 'apps'
		}
	},
	data() {
		return {
			items: []
		};
	},
	watch: {
		navigationPage() {
			this.getItems();
		}
	},
	methods: {
		getItems() {
			this.$api
				.call(ApiNavigation.getMenu(this.navigationPage))
				.then(result => {
					this.items = result.data;
				})
				.catch(this.$fhcAlert.handleSystemError);
		}
	},
	created() {
		this.getItems();
	},
	template: /* html */`
	<ul class="fhc-app-menu">
		<li v-for="(menu, key) in items" :key="key">
			<a
				:href="menu.link"
				@click="menu.onClickCall"
				:class="{ active: key === appIdentifier }"
			>
				<i v-if="menu.icon" class="fa fa-fw" :class="'fa-'+ menu.icon" />
				{{ menu.description }}
			</a>
		</li>
		<slot />
	</ul>`
};
