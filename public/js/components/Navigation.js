import {CoreFetchCmpt} from '../components/Fetch.js';

export const CoreNavigationCmpt = {
	data() {
		return {
			headerMenu: {},
			sideMenu: {}
		};
	},
	created() {},
	props: {
		addHeaderMenuEntries: Object,
		addSideMenuEntries: Object
	},
	components: {
		CoreFetchCmpt
	},
	methods: {
		getNavigationPage() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		fetchDataHeader() {
			return CoreRESTClient.get(
				'system/Navigation/header',
				{
					navigation_page: this.getNavigationPage()
				}
			);
		},
		setHeaders(data) {
			if (CoreRESTClient.hasData(data)) this.headerMenu = CoreRESTClient.getData(data);
		},
		fetchDataMenu() {
			return CoreRESTClient.get(
				'system/Navigation/menu',
				{
					navigation_page: this.getNavigationPage()
				}
			);
		},
		setSideMenu(data) {
			if (CoreRESTClient.hasData(data)) this.sideMenu = CoreRESTClient.getData(data);
		},
		getDataBsToggle(header) {
			return !header.children ? null : 'dropdown';
		}
	},
	computed: {
		headerMenuEntries() {
			if (this.headerMenu != null && this.addHeaderMenuEntries != null && Object.keys(this.addHeaderMenuEntries).length > 0)
			{
				this.headerMenu[this.addHeaderMenuEntries.description] = this.addHeaderMenuEntries;
			}
			return this.headerMenu;
		},
		sideMenuEntries() {
			
			if (this.sideMenu != null && this.addSideMenuEntries != null && Object.keys(this.addSideMenuEntries).length > 0)
			{
				this.sideMenu = this.addSideMenuEntries;
			}
			return this.sideMenu;
		}
	},
	template: `
		<!-- Load head menu -->
		<core-fetch-cmpt v-bind:api-function="fetchDataHeader" @data-fetched="setHeaders"></core-fetch-cmpt>
		<!-- Load side menu -->
		<core-fetch-cmpt v-bind:api-function="fetchDataMenu" @data-fetched="setSideMenu"></core-fetch-cmpt>

		<!-- Top menu -->
		<nav class="navbar navbar-expand-lg navbar-header">
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
		</nav>

		<!-- Left side menu -->
		<nav class="navbar navbar-left-side">
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
									<a class="nav-link left-side-menu-link-entry" v-bind:href="child.link" @click=child.onClickCall>
										&emsp;&emsp;{{ child.description }}
									</a>
								</li>
							</template>
						</ul>
					</li>
				</template>
			</ul>
		</nav>
	`
};

