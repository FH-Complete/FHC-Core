import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import contrast from '../../directives/contrast.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers.js";
import Stundenplan, {DEFAULT_MODE_STUNDENPLAN} from "../../components/Cis/Stundenplan/Stundenplan.js";
import MylvStudent from "../../components/Cis/Mylv/Student.js";
import Profil from "../../components/Cis/Profil/Profil.js";
import CmsNews from "../../components/Cis/Cms/News.js";
import CmsContent from "../../components/Cis/Cms/Content.js";
import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info.js";
import RoomInformation, { DEFAULT_MODE_RAUMINFO } from "../../components/Cis/Mylv/RoomInformation.js";

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
		
		// Redirect old links to new format
		{
			path: "/CisVue/Cms/getRoomInformation/:ort_kurzbz",
			name: "RoomInformationOld",
			component: RoomInformation,
			redirect: (to) => {
				return { // redirect to longer Rauminfo url and map params
					name: "RoomInformation",
					params: { // in this case always populate other params since they are not optional
						ort_kurzbz: to.params.ort_kurzbz,
						mode: DEFAULT_MODE_RAUMINFO,
						focus_date: new Date().toISOString().split("T")[0]
					},
				};
			},
		},
		{
			path: `/CisVue/Cms/getRoomInformation/:mode/:focus_date/:ort_kurzbz`,
			name: 'RoomInformation',
			component: RoomInformation,
			props: (route) => { // validate and set mode/focus date if for some reason missing
				const validModes = ["Month", "Week", "Day"];

				// check mode string
				const mode = route.params.mode &&
				validModes.includes(route.params.mode.charAt(0).toUpperCase() + route.params.mode.slice(1).toLowerCase())
					? route.params.mode.charAt(0).toUpperCase() + route.params.mode.slice(1).toLowerCase()
					: DEFAULT_MODE_RAUMINFO;

				// default to today date if not provided
				const d = new Date(route.params.focus_date)
				const focus_date = !isNaN(d) ? route.params.focus_date : new Date().toISOString().split("T")[0];

				// for consistency reasons format the props into one object but actually use a new name to we dont collide with
				// existing viewData declaration written from codeigniter 3 into routerview tag
				return {
					propsViewData: {
						mode,
						focus_date,
						ort_kurzbz: route.params.ort_kurzbz
					}
				};
			},
			beforeEnter: (to, from, next) => {
				//  missing mode or focus_date -> set defaults
				if (!to.params.mode || !to.params.focus_date) {
					next({
						name: "RoomInformation",
						params: {
							mode: to.params.mode || DEFAULT_MODE_RAUMINFO,
							focus_date: to.params.focus_date || new Date().toISOString().split("T")[0],
							ort_kurzbz: route.params.ort_kurzbz
						}
					});
				} else {
					next();
				}
			}
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
		// Redirect old links to new format
		{
			// only trigger on first param being numeric to avoid paths like "Stundenplan/Month" entering here
			path: "/Cis/Stundenplan/:lv_id(\\d+)", 
			name: "StundenplanOld",
			component: Stundenplan,
			redirect: (to) => {
				return { // redirect to longer Stundenplan url and map params
					name: "Stundenplan",
					params: {
						lv_id: to.params.lv_id
					},
				};
			},
		},
		{
			path: `/Cis/Stundenplan/:mode?/:focus_date?/:lv_id?`,
			name: 'Stundenplan',
			component: Stundenplan,
			props: (route) => { // validate and set mode/focus date if for some reason missing
				const validModes = ["Month", "Week", "Day"];

				// check mode string
				const mode = route.params.mode &&
					validModes.includes(route.params.mode.charAt(0).toUpperCase() + route.params.mode.slice(1).toLowerCase())
						? route.params.mode.charAt(0).toUpperCase() + route.params.mode.slice(1).toLowerCase()
						: DEFAULT_MODE_STUNDENPLAN;

				// default to today date if not provided or string forms invalid date
				const d = new Date(route.params.focus_date)
				const focus_date = !isNaN(d) ? route.params.focus_date : new Date().toISOString().split("T")[0];
				// for consistency reasons format the props into one object but actually use a new name to we dont collide with
				// existing viewData declaration written from codeigniter 3 into routerview tag
				return {
					propsViewData: {
						mode,
						focus_date,
						lv_id: route.params.lv_id
					}
				};
			},
			beforeEnter: (to, from, next) => {
				//  missing mode or focus_date -> set defaults
				if (!to.params.mode || !to.params.focus_date) {
					next({
						name: "Stundenplan",
						params: {
							mode: to.params.mode || DEFAULT_MODE_STUNDENPLAN,
							focus_date: to.params.focus_date || new Date().toISOString().split("T")[0],
							lv_id: to.params.lv_id
						}
					});
				} else {
					next();
				}
			}
		},
		{
			path: `/Cis4`,
			name: 'Cis4',
			component: FhcDashboard,
			props: {dashboard: 'CIS'},
		},
		{
			path: `/`,
			name: 'FhcDashboard',
			component: FhcDashboard,
			props: {dashboard: 'CIS'},
		},
		{
			path: '/:pathMatch(.*)*',
			name: 'Fallback',
			component: FhcDashboard,
			props: {dashboard: 'CIS'},
			redirect: () => {
				return {
					name: "Cis4",
					params: {
						dashboard: 'CIS'
					},
				};
			},
		},
	]
})

router.beforeEach((to, from) => {
	// this avoids redundant routing navigation in place due to router.replace on a route with param function and
	// beforeEnter navigation guard
	
	// TODO: manage the infinite forward navigation issue somehow
	// https://stackoverflow.com/questions/28028297/how-can-i-delete-a-window-history-state?rq=3
	if (to.fullPath === from.fullPath) {
		return false
	}
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
				if(!res?.matched?.length || res.name === 'Fallback') return
				
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