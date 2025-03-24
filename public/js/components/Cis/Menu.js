import CisMenuEntry from "./Menu/Entry.js";
import FhcSearchbar from "../searchbar/searchbar.js";
import CisSprachen from "./Sprachen.js"
import ThemeSwitch from "./ThemeSwitch.js";

export default {
    components: {
        CisMenuEntry,
        FhcSearchbar,
		CisSprachen,
		ThemeSwitch,
    },
    props: {
		rootUrl: String,
        logoUrl: String,
        avatarUrl: String,
        logoutUrl: String,
		selectedtypes: Array,
        searchbaroptions: Object,
        searchfunction: Function
    },
    data: () => {
        return {
            entries: [],
			activeEntry:null,
			url:null,
			urlMatchRankings:[],
			navUserDropdown:null,
        };
    },
	provide(){
		return{
			setActiveEntry: this.setActiveEntry,
			addUrlCount: this.addUrlCount,
			makeParentContentActive: this.makeParentContentActive,
		}
	},
	computed:{
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
		}
	},
	methods: {
		fetchMenu: function(){
			return this.$fhcApi.factory.menu.getMenu()
			.then(res => res.data)
			.then(menu => {
				this.entries = menu;
			})	
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
	},
	created(){
		this.fetchMenu();
	},
	mounted(){
		this.$p.loadCategory(['ui', 'global'])
		this.navUserDropdown = new bootstrap.Collapse(this.$refs.navUserDropdown,{
			toggle: false
		});
	},
    template: /*html*/`
	<button id="nav-main-btn" class="navbar-toggler rounded-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#nav-main" aria-controls="nav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
	<fhc-searchbar ref="searchbar" id="nav-search" class="fhc-searchbar w-100 py-1 py-lg-2" :searchoptions="searchbaroptions" :searchfunction="searchfunction"></fhc-searchbar>
    <div id="nav-logo" class="d-none d-lg-block">
		<div class="d-flex h-100">
			<a :href="rootUrl">
				<img :src="logoUrl" alt="Logo">
			</a>
			<theme-switch></theme-switch>
		</div>
    </div>
	<div id="nav-user">
		<button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
			<img :src="avatarUrl" class="bg-dark avatar rounded-circle border border-dark"/>
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
    <nav id="nav-main" class="offcanvas offcanvas-start" tabindex="-1" aria-labelledby="nav-main-btn" data-bs-backdrop="false">
		<div id="nav-main-sticky">
			<div id="nav-main-toggle" class="position-static d-none d-lg-block ">
				<button type="button" class="btn text-light rounded-0 p-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target=".nav-menu-collapse" aria-expanded="true" aria-controls="nav-sprachen nav-main-menu">
					<i class="fa fa-arrow-circle-left fhc-text"></i>
				</button>
			</div>
			<div class="offcanvas-body p-0">
				<div id="nav-main-menu" class="nav-menu-collapse collapse collapse-horizontal show">
					<div>
						<cis-menu-entry :highestMatchingUrlCount="highestMatchingUrlCount" :activeContent="activeEntry" v-for="entry in entries" :key="entry.content_id" :entry="entry" />
					</div>
				</div>
			</div>
		</div>
    </nav>`
};
