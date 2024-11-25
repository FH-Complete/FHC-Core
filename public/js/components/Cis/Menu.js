import CisMenuEntry from "./Menu/Entry.js";
import FhcSearchbar from "../searchbar/searchbar.js";

export default {
    components: {
        CisMenuEntry,
        FhcSearchbar
    },
    props: {
		menu: Array,
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
		getLanguageButtonClass(lang) {
			let classString = 'btn btn-level-2 rounded-0'
			const langCookie = (function(lang) {
				const cookieString = document.cookie;
				const cookies = cookieString.split('; ');

				for (let cookie of cookies) {
					const [key, value] = cookie.split('=');
					if (key === lang) {
						return decodeURIComponent(value);
					}
				}

				return null; // Return null if the cookie is not found
			})('sprache');
			if(langCookie === lang) classString += ' fhc-active';
			return classString
		},
		toggleCollapsibles(target){
			switch(target){
				case 'settings':
					this.navUserDropdown?.hide();
					break;
				case 'navUserDropdown':
					this.$refs.searchbar?.settingsDropdown?.hide();
					break;
			}
		},
		makeParentContentActive(content_id, collection=this.entries, parent=null){
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
		handleChangeLanguage(lang) {
			this.$p.setLanguage(lang, this.$fhcApi)
			const gerButton = this.$refs.ger
			const engButton = this.$refs.eng
			
			if(lang === 'German') {
				gerButton.classList.add('fhc-active')
				engButton.classList.remove('fhc-active')
			} else if(lang === 'English') {
				engButton.classList.add('fhc-active')
				gerButton.classList.remove('fhc-active')
			}
			
		}
	},
	mounted(){
		this.entries = this.menu;
		this.$p.loadCategory(['ui', 'global'])
		this.navUserDropdown = new bootstrap.Collapse(this.$refs.navUserDropdown,{
			toggle: false
		});
	},
    template: /*html*/`
	<!--<p>CISVUE HEADER</p>
	<p>highest count : {{highestMatchingUrlCount}}</p>
	<p>active entry content_id : {{activeEntry}}</p>
	-->
	<button id="nav-main-btn" class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#nav-main" aria-controls="nav-main" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
	<fhc-searchbar @showSettings="toggleCollapsibles" ref="searchbar" id="nav-search" class="fhc-searchbar w-100" :searchoptions="searchbaroptions" :searchfunction="searchfunction"></fhc-searchbar>
    <a id="nav-logo" class="d-none d-lg-block" :href="rootUrl">
        <img :src="logoUrl" alt="Logo">
    </a>
	<div id="nav-user">
		<button id="nav-user-btn" class="btn btn-link rounded-0" type="button" data-bs-toggle="collapse" data-bs-target="#nav-user-menu" aria-expanded="false" aria-controls="nav-user-menu">
			<img :src="avatarUrl" class="avatar rounded-circle"/>
		</button>
		<ul ref="navUserDropdown" @[\`show.bs.collapse\`]="toggleCollapsibles('navUserDropdown')" id="nav-user-menu" class="top-100 end-0 collapse list-unstyled" aria-labelledby="nav-user-btn">
			<li class="btn-level-2"><a class="btn btn-level-2 rounded-0 d-block" :href="site_url + '/Cis/Profil'" id="menu-profil">Profil</a></li>
			<li class="fhc-languages btn-level-2" style="text-align: center;">
				<div class="btn-group">
					<a :class="getLanguageButtonClass('German')" ref="ger" href="#" @click="handleChangeLanguage('German')">Deutsch</a>
					<a :class="getLanguageButtonClass('English')" ref="eng" href="#" @click="handleChangeLanguage('English')">English</a>
				</div>
			</li>
			<li class="btn-level-2"><hr class="dropdown-divider p-0 "></li>
			<li><a class="btn btn-level-2 rounded-0 d-block" :href="logoutUrl">Logout</a></li>
		</ul>
	</div>
    <nav id="nav-main" class="offcanvas offcanvas-start bg-dark" tabindex="-1" aria-labelledby="nav-main-btn" data-bs-backdrop="false">
		<div id="nav-main-sticky">
			<div id="nav-main-toggle" class="position-static d-none d-lg-block bg-dark">
				<button type="button" class="btn bg-dark text-light rounded-0 p-1 d-flex align-items-center" data-bs-toggle="collapse" data-bs-target="#nav-main-menu" aria-expanded="true" aria-controls="nav-main-menu">
					<i class="fa fa-arrow-circle-left"></i>
				</button>
			</div>
			<div class="offcanvas-body p-0">
				<div id="nav-main-menu" class="collapse collapse-horizontal show">
					<div>
						<cis-menu-entry :highestMatchingUrlCount="highestMatchingUrlCount" :activeContent="activeEntry" v-for="entry in entries" :key="entry.content_id" :entry="entry" />
					</div>
				</div>
			</div>
		</div>
    </nav>`
};