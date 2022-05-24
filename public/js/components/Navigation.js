const Navigation = {
	data() {
		return {
			headerMenu: {},
			sideMenu: {}
		};
	},
	created() {
		this.fetchDataHeader();
		this.fetchDataMenu();
	},
	props: {
		addHeaderMenuEntries: Object,
		addSideMenuEntries: Object
	},
	methods: {
		getNavigationPage() {
			return FHC_JS_DATA_STORAGE_OBJECT.called_path + "/" + FHC_JS_DATA_STORAGE_OBJECT.called_method;
		},
		fetchDataHeader() {
			// Retrives the header menu array
			FHC_AjaxClient.ajaxCallGet(
				'system/Navigation/header',
				{
					navigation_page: this.getNavigationPage()
				},
				{
					successCallback: this.setHeaders
				}
			);
		},
		setHeaders(data) {
			if (FHC_AjaxClient.hasData(data)) this.headerMenu = FHC_AjaxClient.getData(data);
		},
		fetchDataMenu() {
			// Retrives the side menu array
			FHC_AjaxClient.ajaxCallGet(
				'system/Navigation/menu',
				{
					navigation_page: this.getNavigationPage()
				},
				{
					successCallback: this.setSideMenu
				}
			);
		},
		setSideMenu(data) {
			if (FHC_AjaxClient.hasData(data)) this.sideMenu = FHC_AjaxClient.getData(data);
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
				this.sideMenu[this.addSideMenuEntries.description] = this.addSideMenuEntries;
			}
			return this.sideMenu;
		}
	},
	template: `
		<!-- Top menu -->
		<nav class="navbar navbar-expand-lg navbar-header">
			<ul class="navbar-nav">
				<!-- 1st level -->
				<template v-for="header in headerMenuEntries">
					<li class="nav-item dropdown">
						<a class="nav-link header-menu-link-entry" v-bind:data-bs-toggle="this.getDataBsToggle(header)" v-bind:class="{ 'dropdown-toggle': header.children }" v-bind:href="header.link">
							<i class="fa-solid" v-bind:class="'fa-' + header.icon" v-if="header.icon"></i> {{ header.description }}
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
		<nav class="navbar sidebar">
			<ul class="navbar-nav">
				<!-- 1st level -->
				<template v-for="menu in sideMenuEntries">
					<li class="nav-item">
						<a class="nav-link" v-bind:href="menu.link">
							<i class="fa fa-fw" v-bind:class="'fa-'+ menu.icon"></i> {{ menu.description }}
						</a>
					</li>
				</template>
			</ul>
		</nav>
	`
};

