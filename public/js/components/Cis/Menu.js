import CisMenuEntry from "./Menu/Entry.js";
import FhcSearchbar from "../searchbar/searchbar.js";
import CisSprachen from "./Sprachen.js"
import ThemeSwitch from "./ThemeSwitch.js";
import ApiCisMenu from '../../api/factory/cis/menu.js';
import ApiSearchbar from '../../api/factory/searchbar.js';
import ApiLvPlan from "../../api/factory/lvPlan.js";

export default {
    components: {
        CisMenuEntry,
        FhcSearchbar,
		CisSprachen,
		ThemeSwitch,
    },
    props: {
		rootUrl: {
			type: String,
			default: () => document.getElementById('cis-header')?.dataset.rootUrl ?? ''
		},
		logoUrl: {
			type: String,
			default: () => document.getElementById('cis-header')?.dataset.logoUrl ?? ''
		},
		avatarUrl: {
			type: String,
			default: () => document.getElementById('cis-header')?.dataset.avatarUrl ?? ''
		},
		logoutUrl: {
			type: String,
			default: () => document.getElementById('cis-header')?.dataset.logoutUrl ?? ''
		},
    },
    data: function() {
        return {
            entries: [],
			activeEntry:null,
			url:null,
			urlMatchRankings:[],
			navUserDropdown:null,
			menuOpen:true,
			searchbaroptions: {
				origin: "cis",
				cssclass: "",
				calcheightonly: true,
				types: {
					employee: Vue.computed(() => this.$p.t("search/type_employee")),
					student: Vue.computed(() => this.$p.t("search/type_student")),
					room: Vue.computed(() => this.$p.t("search/type_room")),
					organisationunit: Vue.computed(() => this.$p.t("search/type_organisationunit")),
					cms: Vue.computed(() => this.$p.t("search/type_cms")),
					dms: Vue.computed(() => this.$p.t("search/type_dms"))
				},
				actions: {
					employee: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Profil/View/" + data.uid;
							}
						},
						childactions: []
					},
					student: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									"/Cis/Profil/View/" + data.uid;
							}
						},
						childactions: []
					},
					room: {
						defaultaction: {
							type: "link",
							renderif: function(data) {
								return data.content_id !== null;
							},
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									'/CisVue/Cms/content/' + data.content_id;
							}
						},
						childactions: [
							{
								label: "LV-Plan",
								icon: "fas fa-bookmark",
								type: "link",
								action: function(data) {
									return FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										'/CisVue/Cms/getRoomInformation/' + data.ort_kurzbz;
								}
							},
							{
								label: "Rauminformation",
								icon: "fas fa-info-circle",
								type: "link",
								renderif: function(data) {
									return data.content_id !== null;
								},
								action: function(data) {
									return FHC_JS_DATA_STORAGE_OBJECT.app_root +
										FHC_JS_DATA_STORAGE_OBJECT.ci_router +
										'/CisVue/Cms/content/' + data.content_id;
								}
							},
						]
					},
					organisationunit: {
						defaultaction: {
							type: "link",
							renderif: function(data) {
								return !!data.mailgroup;
							},
							action: function(data) {
								return 'mailto:' + data.mailgroup;
							}
						},
						childactions: []
					},
					cms: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root +
									FHC_JS_DATA_STORAGE_OBJECT.ci_router +
									'/CisVue/Cms/content/' + data.content_id;
							}
						},
						childactions: []
					},
					dms: {
						defaultaction: {
							type: "link",
							action: function(data) {
								return FHC_JS_DATA_STORAGE_OBJECT.app_root +
									'cms/dms.php?id=' + data.dms_id;
							}
						},
						childactions: []
					}
				}
			},
        };
    },
	inject: ["isNarrow", "isMobile"],
	provide(){
		return{
			setActiveEntry: this.setActiveEntry,
			addUrlCount: this.addUrlCount,
			makeParentContentActive: this.makeParentContentActive,
		}
	},
	computed:{
		menuCollapseAriaLabel(){
			if(this.menuOpen){
				return this.$p.t('global', 'collapseMenu');
			}else{
				return this.$p.t('global', 'extendMenu');
			}
		},
		highestMatchingUrlCount(){
			// gets the hightest ranking inside the array
			let highestMatch = Math.max(...this.urlMatchRankings);

			if(this.urlMatchRankings.length > 0){
				// if more than one entry has the same ranking, none should be active
				return this.urlMatchRankings.filter((value)=>value == highestMatch).length > 1 ? null : highestMatch;
			}

			return null;
		},
		site_url(){
			return FHC_JS_DATA_STORAGE_OBJECT.app_root + FHC_JS_DATA_STORAGE_OBJECT.ci_router;
		},
	},
	methods: {
		fetchMenu() {
			return this.$api
				.call(ApiCisMenu.getMenu())
				.then(res => res.data)
				.then(menu => {
					this.entries = menu;
				});
		},
		checkSettingsVisibility: function (event) {
			// hides the settings collapsible if the user clicks somewhere else
			if (!this.$refs.navUserDropdown.contains(event.target)) {
				this.navUserDropdown.hide();
			}
		},
		handleShowNavUser(){
			document.addEventListener("click", this.checkSettingsVisibility);
		},
		handleHideNavUser(){
			document.removeEventListener("click", this.checkSettingsVisibility);
		},
		makeParentContentActive(content_id, collection=this.entries, parent=null){
			if(!collection) return;
			if (typeof collection == 'object' && !Array.isArray(collection) && Object.entries(collection).length > 0) {
				collection = Object.values(collection);
			}
			for(let entry of collection){
				if(entry.content_id == content_id){
					this.activeEntry = parent;
				}
				this.makeParentContentActive(content_id, entry.childs, entry.content_id);
			}
			
		},
		addUrlCount(count){
			this.urlMatchRankings.push(count);
		},

		setActiveEntry(content_id){
			this.activeEntry = content_id;
		},
		searchfunction(searchsettings) {
			return this.$api.call(ApiSearchbar.searchCis(searchsettings));
		},
	},
	created(){
		this.fetchMenu();
	},
	async mounted(){
		this.$p.loadCategory(['ui', 'global', 'profilUpdate'])
		this.navUserDropdown = new bootstrap.Collapse(this.$refs.navUserDropdown,{
			toggle: false
		});

		const openOtherLvPlanAction = {
			label: Vue.computed(() => this.$p.t("lehre/stundenplan")),
			icon: "fas fa-calendar-days",
			type: "link",
			action: function(data) {
				const uid = JSON.parse(data.data).uid;
				return FHC_JS_DATA_STORAGE_OBJECT.app_root +
					FHC_JS_DATA_STORAGE_OBJECT.ci_router +
					"/Cis/OtherLvPlan/" + uid;
			},
		};
		let result = await this.$api.call(ApiLvPlan.checkPermissionOtherLvPlan());
		if (result.meta.status === "success" && result.data) {
			this.searchbaroptions.actions.employee.childactions.push(openOtherLvPlanAction);
			this.searchbaroptions.actions.student.childactions.push(openOtherLvPlanAction);
		}
	},
    template: /*html*/`
	<div id="cis-header-bar" class="d-flex flex-row flex-grow-1">
		<div id="nav-logo" class="d-none d-lg-block">
			<div class="d-flex h-100 justify-content-between">
				<a :href="rootUrl">
					<img :src="logoUrl" alt="Corporate Identity Logo">
				</a>
			</div>
		</div>

		<div
			v-if="isNarrow"
			:class="{'collapse multi-collapse collapse-horizontal show': isMobile}"
			id="navbar-toggler-collapsible"
		>
			<div class="d-flex flex-row align-items-center h-100" style="width: 35px">
				<button id="nav-main-btn" class="navbar-toggler rounded-0 px-2 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#nav-main" aria-controls="nav-main" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
			</div>
		</div>

		<fhc-searchbar
			:searchoptions="searchbaroptions"
			:searchfunction="searchfunction"
			ref="searchbar"
			id="nav-search"
			class="fhc-searchbar flex-grow-1 py-1 py-lg-2"
		>
			<template #collapseToggler="{ isSearchShownInMobileView }">
				<span
					v-if="isMobile"
					type="button"
					data-bs-toggle="collapse"
					data-bs-target=".multi-collapse"
					aria-controls="searchbar-collapsible navbar-toggler-collapsible options-collapsible"
					aria-expanded="false"
			 		class="d-flex flex-row align-items-center pe-1"
					style="color: white"
				>
					<i v-if="isSearchShownInMobileView" class="fa-solid fa-chevron-left ps-3"></i>
					<i v-else class="fa-solid fa-magnifying-glass ps-2"></i>
				</span>
			</template>
		</fhc-searchbar>
		
		<div
			id="options-collapsible"
			:class="{'collapse multi-collapse collapse-horizontal show': isMobile}"
		>
			<div :style="!isMobile ? '' : 'width: 105px'" class="d-flex flex-row ps-3 justify-content-end">
				<span class="d-flex flex-row align-items-center">
					<theme-switch></theme-switch>
				</span>
				<div id="nav-user">
					<button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
						<img :src="avatarUrl" :alt="$p.t('profilUpdate/profilBild')" class="bg-dark avatar rounded-circle border border-dark"/>
					</button>
					<ul ref="navUserDropdown"
					@[\`shown.bs.collapse\`]="handleShowNavUser"
					@[\`hide.bs.collapse\`]="handleHideNavUser"
					id="nav-user-menu" class="top-100 end-0 collapse list-unstyled" aria-labelledby="nav-user-btn">
						<li><a class="fhc-dark-bg btn rounded-0 d-block" :href="site_url + '/Cis/Profil'" id="menu-profil">Profil</a></li>
						<li >
							<cis-sprachen @languageChanged="fetchMenu"></cis-sprachen>
						</li>
						<li><hr class="dropdown-divider m-0 "></li>
						<li ><a class="fhc-dark-bg btn rounded-0 d-block" :href="logoutUrl">Logout</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

    <nav id="nav-main" class="offcanvas offcanvas-start" tabindex="-1" aria-labelledby="nav-main-btn" data-bs-backdrop="false">
		<div id="nav-main-sticky">
			<div class="d-flex flex-row h-100">
				<div class="offcanvas-body p-0">
					<div id="nav-main-menu" class="nav-menu-collapse collapse collapse-horizontal show">
						<div class="flex-grow-1">
							<cis-menu-entry :highestMatchingUrlCount="highestMatchingUrlCount" :activeContent="activeEntry" v-for="entry in entries" :key="entry.content_id" :entry="entry" />
						</div>
					</div>
				</div>
				<div id="nav-main-toggle" class="d-none d-lg-block">
					<div
						@click="menuOpen = !menuOpen"
						:aria-label="menuCollapseAriaLabel"
						type="button"
						class="h-100 d-flex align-items-center px-2"
						data-bs-toggle="collapse"
						data-bs-target=".nav-menu-collapse"
						aria-expanded="true"
						aria-controls="nav-sprachen nav-main-menu"
					>
						<i aria-hidden="true" class="fa-solid fa-chevron-left fhc-text"></i>
					</div>
				</div>
			</div>
		</div>
    </nav>`
};
