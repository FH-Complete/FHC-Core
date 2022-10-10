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

import {CoreNavigationAPIs} from './API.js';
import {CoreRESTClient} from '../../RESTClient.js';
import {CoreFetchCmpt} from '../../components/Fetch.js';

/**
 *
 */
export const CoreNavigationCmpt = {
	components: {
		CoreFetchCmpt
	},
	props: {
		addHeaderMenuEntries: Object, // property used to add new header menu entries from another app/component
		addSideMenuEntries: Object, // property used to add new side menu entries from another app/component
		hideTopMenu: Boolean,
                leftNavCssClasses: {
                    type: String,
                    default: 'navbar navbar-left-side'
                }
	},
	data: function() {
		return {
			headerMenu: {}, // header menu entries
			sideMenu: {} // side menu entries
		};
	},
	computed: {
		/**
		 *
		 */
		headerMenuEntries: function() {
			//
			if (this.headerMenu != null && this.addHeaderMenuEntries != null && Object.keys(this.addHeaderMenuEntries).length > 0)
			{
				this.headerMenu[this.addHeaderMenuEntries.description] = this.addHeaderMenuEntries;
			}
			return this.headerMenu;
		},
		/**
		 *
		 */
		sideMenuEntries: function() {
			//
			if (this.sideMenu != null && this.addSideMenuEntries != null && Object.keys(this.addSideMenuEntries).length > 0)
			{
				this.sideMenu[this.addSideMenuEntries.description] = this.addSideMenuEntries;
			}
			return this.sideMenu;
		}
	},
	methods: {
		/**
		 *
		 */
		getNavigationPage: function() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		/**
		 *
		 */
		fetchCmptApiFunctionHeader: function() {
			return CoreNavigationAPIs.getHeader(this.getNavigationPage());
		},
		/**
		 *
		 */
		fetchCmptApiFunctionSideMenu: function() {
			return CoreNavigationAPIs.getMenu(this.getNavigationPage());
		},
		/**
		 *
		 */
		fetchCmptDataFetchedHeader: function(data) {
			if (CoreRESTClient.hasData(data)) this.headerMenu = CoreRESTClient.getData(data);
		},
		/**
		 *
		 */
		fetchCmptDataFetchedMenu: function(data) {
			if (CoreRESTClient.hasData(data)) this.sideMenu = CoreRESTClient.getData(data);
		},
		/**
		 *
		 */
		getDataBsToggle: function(header) {
			return !header.children ? null : 'dropdown';
		}
	},
	template: `
		<!-- Load head menu -->
		<core-fetch-cmpt v-bind:api-function="fetchCmptApiFunctionHeader" @data-fetched="fetchCmptDataFetchedHeader"></core-fetch-cmpt>
		<!-- Load side menu -->
		<core-fetch-cmpt v-bind:api-function="fetchCmptApiFunctionSideMenu" @data-fetched="fetchCmptDataFetchedMenu"></core-fetch-cmpt>

		<!-- Top menu -->
		<nav class="navbar navbar-expand-lg navbar-header" v-if="!hideTopMenu">
			<ul class="navbar-nav">
				<!-- 1st level -->
				<template v-for="header in headerMenuEntries">
					<li class="nav-item dropdown">
						<a class="nav-link header-menu-link-entry"
							v-bind:data-bs-toggle="this.getDataBsToggle(header)"
							v-bind:class="{ 'dropdown-toggle': header.children }"
							v-bind:href="header.link"
						>
							<i class="fa-solid fa-fw header-menu-icon" v-bind:class="'fa-' + header.icon" v-if="header.icon"></i> {{ header.description }}
						</a>
						<ul class="dropdown-menu" v-if="header.children">
							<!-- 2nd level -->
							<template v-for="child in header.children">
								<li><a class="dropdown-item" v-bind:href="child.link">{{ child.description }}</a></li>
							</template>
						</ul>
					</li>
				</template>
			</ul>
			<slot></slot>
		</nav>

		<!-- Left side menu -->
		<nav :class="leftNavCssClasses">
			<ul class="navbar-nav">
				<!-- 1st level -->
				<template v-for="menu in sideMenuEntries">
					<li class="nav-item">
						<a class="nav-link left-side-menu-link-entry" v-bind:href="menu.link" @click=menu.onClickCall>
							<i class="fa fa-fw" v-bind:class="'fa-'+ menu.icon"></i> {{ menu.description }}
						</a>
						<ul class="nav-link left-side-menu-second-level" v-if="menu.children">
							<!-- 2nd level -->
							<template v-for="child in menu.children">
								<li>
								<span>
									<a class="nav-link left-side-menu-link-entry" v-bind:href="child.link" @click=child.onClickCall>
										&emsp;&emsp;{{ child.description }}
									</a>
									<a
										class="nav-link left-side-menu-link-entry"
										v-bind:class="child.subscriptLinkClass"
										v-if="child.subscriptDescription"
										v-bind:href="child.link"
										@click=child.onClickSubscriptCall
									>
										{{ child.subscriptDescription }}
									</a>
								</span>
								</li>
							</template>
						</ul>
					</li>
				</template>
			</ul>
		</nav>
	`
};

