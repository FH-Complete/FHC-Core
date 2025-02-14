import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import contrast from '../../directives/contrast.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";
import Stundenplan from "../../components/Cis/Stundenplan/Stundenplan";
import MylvStudent from "../../components/Cis/Mylv/Student";
import Profil from "../../components/Cis/Profil/Profil";
import CmsNews from "../../components/Cis/Cms/News";
import CmsContent from "../../components/Cis/Cms/Content";
import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info";
import RoomInformation from "../../components/Cis/Mylv/RoomInformation";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}`),
	routes: [
		{
			path: `/Cis/Profil/View/:uid`,
			name: 'ProfilView',
			component: Profil,
			props: true
		},
		{
			path: `/Cis/Profil`,
			name: 'Profil',
			component: Profil,
			props: true
		},
		{
			path: `/CisVue/Cms/getRoomInformation/:ort_kurzbz`,
			name: 'RoomInformation',
			component: RoomInformation,
			props: true
		},
		{
			path: `/CisVue/Cms/Content/:content_id`,
			name: 'Content',
			component: CmsContent,
			props: true
		},
		{
			path: `/CisVue/Cms/News`,
			name: 'News',
			component: CmsNews,
			props: true
		},
		{
			path: `/Cis/MyLv`,
			name: 'MyLv',
			component: MylvStudent,
			props: true
		},
		{
			path: `/Cis/MyLv/Info/:studien_semester/:lehrveranstaltung_id`,
			name: 'LvInfo',
			component: Info,
			props: true
		},
		{
			path: `/Cis/Stundenplan/:lv_id?`,
			name: 'Stundenplan',
			component: Stundenplan
		},
		{
			path: `/`,
			name: 'FhcDashboard',
			component: FhcDashboard,
			props: {dashboard: 'CIS'},
		},
		{
			path: `/Cis4`,
			name: 'Cis4',
			component: FhcDashboard,
			props: {dashboard: 'CIS'},
		}
	]
})

const app = Vue.createApp({
	name: 'FhcApp',
	data: () => ({
		appSideMenuEntries: {}
	}),
	components: {},
	computed: {
		isMobile() {
			return /Mobi|Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
		}	
	},
	provide() {
		return { // provide injectable & watchable language property
			language: Vue.computed(() => this.$p.user_language)
		}	
	},
	methods: {
		isInternalRoute(href) {
			const internalBase = window.location.origin
			return href.startsWith(internalBase);
		},
		handleClick(event) {
			const target = event.target.closest('a');
			
			if (target && this.isInternalRoute(target.href)) {
				const url = new URL(target.href)
				
				const path = url.pathname
				const base = this.$router.options.history.base
				const route = path.replace(base, '') || '/'

				// let click event propagate normally if we dont route internally
				const res = this.$router.resolve(route)
				if(!res?.matched?.length) return
				
				event.preventDefault(); // Prevent browser navigation
				
				if(this.isMobile) { // toggle the menu
					const navMain = document.getElementById('nav-main');
					// fix unwanted toggle from off to on for some links on mobile
					if(navMain.classList.contains('show')){
						document.getElementById('nav-main-btn').click();
					} 
				}
				
				this.$router.push(route);
				
			}
		}
	},
	mounted() {
		document.addEventListener('click', this.handleClick);
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClick);
	},
});

// kind of a bandaid for bad css on some pages to avoid horizontal scroll
setScrollbarWidth();
app.use(router);
app.use(FhcApi);
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(Phrasen);
app.directive('contrast', contrast);
app.mount('#fhccontent');