import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";
import Stundenplan from "../../components/Cis/Stundenplan/Stundenplan";
import MylvStudent from "../../components/Cis/Mylv/Student";
import Profil from "../../components/Cis/Profil/Profil";
import CmsNews from "../../components/Cis/Cms/News";
import CmsContent from "../../components/Cis/Cms/Content";
import Info from "../../components/Cis/Mylv/Semester/Studiengang/Lv/Info";

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
		},
		// only use the catchAll route if every cis4 Route is being handled in vue router, currently Profil is being
		// codeigniter routed
		// {
		// 	path: '/:catchAll(.*)',
		// 	redirect: {name: 'FhcDashboard'},
		// 	props: true
		// }
	]
})

router.beforeEach((to, from) => {
	console.log('from', from)
	console.log('to', to)
})

const app = Vue.createApp({
	name: 'FhcApp',
	data: () => ({
		appSideMenuEntries: {}
	}),
	components: {},
	methods: {
		isInternalRoute(href) {
			const internalBase = window.location.origin
			return href.startsWith(internalBase);
		},
		handleClick(event) {
			const target = event.target.closest('a');
			
			if (target && this.isInternalRoute(target.href)) {
				
				const path = new URL(target.href).pathname
				const base = this.$router.options.history.base
				const route = path.replace(base, '') || '/'
				
				// let click event propagate normally if we dont route internally
				const res = this.$router.resolve(route)
				if(!res?.matched?.length) return
				
				event.preventDefault(); // Prevent browser navigation
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
app.mount('#fhccontent');