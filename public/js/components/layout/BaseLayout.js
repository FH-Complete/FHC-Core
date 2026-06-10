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

export default {
	props: {
		title: '',
		subtitle: '',
		mainCols: {
			type: Array,
			default: []
		},
		asideCols: {
			type: Array,
			default: []
		},
	},
	computed: {
		mainGridCols() {
			return this.mainCols.length > 0 ? `col-md-${this.mainCols[0]}` : "col-md-12";
		},
		asideGridCols() {
			return this.asideCols.length > 0 ? `col-md-${this.asideCols[0]}` : "";
		}
	},
	template: `
	<div class="overflow-hidden">
		<header v-if="title">
			<h1 class="h2 mb-5">{{ title }}<span class="fhc-subtitle">{{ subtitle }}</span></h1>
		</header>
		<div class="row gx-5">
			<main :class="mainGridCols">
				<slot name="main">{{ mainGridCols }}</slot>
			</main>
			<aside v-if="asideCols.length > 0" :class="asideGridCols">
				<slot name="aside">{{ asideGridCols }}</slot>
			</aside>
		</div>
	</div>
	`
};