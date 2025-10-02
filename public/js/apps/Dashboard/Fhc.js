import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import PluginsPhrasen from '../../plugins/Phrasen.js';
import Theme from '../../plugins/Theme.js';
import contrast from '../../directives/contrast.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers.js";
import LvPlan from "../../components/Cis/LvPlan/Lehrveranstaltung.js";
import MyLvPlan from "../../components/Cis/LvPlan/Personal.js";
import MylvStudent from "../../components/Cis/Mylv/Student.js";
import Profil from "../../components/Cis/Profil/Profil.js";
import Raumsuche from "../../components/Cis/Raumsuche/Raumsuche.js";
import CmsNews from "../../components/Cis/Cms/News.js";
import CmsContent from "../../components/Cis/Cms/Content.js";
import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info.js";
import RoomInformation, {DEFAULT_MODE_RAUMINFO} from "../../components/Cis/Mylv/RoomInformation.js";
import AbgabetoolStudent from "../../components/Cis/Abgabetool/AbgabetoolStudent.js";
import AbgabetoolMitarbeiter from "../../components/Cis/Abgabetool/AbgabetoolMitarbeiter.js";
import DeadlineOverview from "../../components/Cis/Abgabetool/DeadlineOverview.js";
import Studium from "../../components/Cis/Studium/Studium.js";

import ApiRenderers from '../../api/factory/renderers.js';
import ApiRouteInfo from '../../api/factory/routeinfo.js';

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}`),
	routes: [
		{
			path: `/Cis/Studium`,
			name: 'Studium',
			component: Studium,
			props: true
		},
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
			path: `/Cis/Abgabetool/Student/:student_uid_prop?`,
			name: 'AbgabetoolStudent',
			component: AbgabetoolStudent,
			props: true
		},
		{
			path: `/Cis/Abgabetool/Mitarbeiter`,
			name: 'AbgabetoolMitarbeiter',
			component: AbgabetoolMitarbeiter,
			props: true
		},
		{
			path: `/Cis/Abgabetool/Deadlines/:person_uid_prop?`,
			name: 'DeadlineOverview',
			component: DeadlineOverview,
			props: true
		},
		{
			path: `/Cis/Raumsuche`,
			name: 'Raumsuche',
			component: Raumsuche,
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
			path: `/Cis/MyLv/:studiensemester?`,
			name: 'MyLv',
			component: MylvStudent,
			props: true,
		},
		{
			path: `/Cis/MyLv/Info/:studien_semester/:lehrveranstaltung_id`,
			name: 'LvInfo',
			component: Info,
			props: true
		},
		// Redirect old links to new format
		{
			// only trigger on first param being numeric to avoid paths like "LvPlan/Month" entering here
			path: "/Cis/LvPlan/:lv_id(\\d+)", 
			name: "LvPlanOld",
			component: LvPlan,
			redirect(to) {
				const route = Vue.unref(router.currentRoute);
				const { mode, focus_date } = route.params; // keep mode and focus_date if available
				return { // redirect to longer LvPlan url and map params
					name: "LvPlan",
					params: {
						mode,
						focus_date,
						lv_id: to.params.lv_id
					},
				};
			},
		},
		{
			path: `/Cis/LvPlan/:mode?/:focus_date?/:lv_id?`,
			name: 'LvPlan',
			component: LvPlan,
			props(route) {
				return {
					propsViewData: route.params
				};
			}
		},
		{
			path: `/Cis/MyLvPlan/:mode?/:focus_date?`,
			name: 'MyLvPlan',
			component: MyLvPlan,
			props(route) {
				return {
					propsViewData: route.params
				};
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

const app = Vue.createApp({
	name: 'FhcApp',
	data: () => ({
		appSideMenuEntries: {},
		renderers: null,
	}),
	components: {},
	computed: {
		isMobile() {
			const smallScreen = window.matchMedia("(max-width: 767px)").matches;
			const touchCapable = ("ontouchstart" in window) || navigator.maxTouchPoints > 0;
			return smallScreen;// && touchCapable;
		}	
	},
	provide() {
		return { // provide injectable & watchable language property
			language: Vue.computed(() => this.$p.user_language),
			renderers: Vue.computed(() => this.renderers),
			isMobile: this.isMobile
		}	
	},
	methods: {
		isInternalRoute(href) {
			const internalBase = window.location.origin
			return href.startsWith(internalBase);
		},
		handleClick(event) {
			const target = event.target.closest('a');

			if(target?.id == 'skiplink') return
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
	async created(){
		await this.$api
			.call(ApiRenderers.loadRenderers())
			.then(res => res.data)
			.then(data => {
				for (let rendertype of Object.keys(data)) {
					let modalTitle = null;
					let modalContent = null;
					let calendarEvent = null;
					if (data[rendertype].modalTitle)
						modalTitle = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].modalTitle)));
					if (data[rendertype].modalContent) 	
						modalContent = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].modalContent)));
					if (data[rendertype].calendarEvent) 	
						calendarEvent = Vue.markRaw(Vue.defineAsyncComponent(() => import(data[rendertype].calendarEvent)));

					if (data[rendertype].calendarEventStyles){
						var head = document.head;
						if(!head.querySelector(`link[href="${data[rendertype].calendarEventStyles}"]`)){
							var link = document.createElement("link");
							link.type = "text/css";
							link.rel = "stylesheet";
							link.href = data[rendertype].calendarEventStyles;
							head.appendChild(link);
						}
					}

					if(this.renderers === null) {
						this.renderers = {};
					}
					if (!this.renderers[rendertype]) {
						this.renderers[rendertype] = {}
					}
					this.renderers[rendertype].modalTitle = modalTitle;
					this.renderers[rendertype].modalContent = modalContent;
					this.renderers[rendertype].calendarEvent = calendarEvent;
				}
			});
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
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.directive('tooltip', primevue.tooltip);
app.use(PluginsPhrasen);
app.use(Theme);
app.directive('contrast', contrast);
app.mount('#fhccontent');

router.afterEach((to, from, failure) => {
	app.config.globalProperties.$api.call(ApiRouteInfo.info('cis4', to.fullPath));
});