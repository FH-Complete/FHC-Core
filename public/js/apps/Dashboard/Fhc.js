import FhcDashboard from '../../components/Dashboard/Dashboard.js';
import FhcApi from '../../plugin/FhcApi.js';
import Phrasen from '../../plugin/Phrasen.js';
import {setScrollbarWidth} from "../../helpers/CssVarCalcHelpers";
import Stundenplan from "../../components/Cis/Stundenplan/Stundenplan";
import MylvStudent from "../../components/Cis/Mylv/Student";
import Profil from "../../components/Cis/Profil/Profil";


const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}`),
	routes: [
		// {
		// 	path: `/Cis/News`,
		// 	name: 'Profil',
		// 	component: Profil,
		// 	props: true
		// },
		// {
		// 	path: `/Cis/Profil`,
		// 	name: 'Profil',
		// 	component: Profil,
		// 	props: true
		// },
		{
			path: `/Cis/MyLv`,
			name: 'MyLv',
			component: MylvStudent,
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
			props: {dashboard: 'CIS'}
		},
		{
			path: '/:catchAll(.*)',
			redirect: {name: 'FhcDashboard'},
			props: true
		}
	]
})

router.beforeEach((to, from) => {
	console.log('from', from)
	console.log('to', to)

})

router.beforeResolve(async to => {
	// TODO: check if necessary viewData params have been provided
	// if (to.meta.requiresCamera) {
	// 	try {
	// 		await askForCameraPermission()
	// 	} catch (error) {
	// 		if (error instanceof NotAllowedError) {
	// 			// ... handle the error and then cancel the navigation
	// 			return false
	// 		} else {
	// 			// unexpected error, cancel the navigation and pass the error to the global handler
	// 			throw error
	// 		}
	// 	}
	// }
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
			//  TODO: handle case of is internalRoute but not defined in vue router
			if (target && this.isInternalRoute(target.href)) {
				event.preventDefault(); // Prevent browser navigation
				const path = new URL(target.href).pathname
				const base = this.$router.options.history.base
				const route = path.replace(base, '') || '/'

				this.$router.push(route);
			}
		},
		tryCis4Navigate(e) {
			this.$router.push({name: e.detail});
		},
		getInitialRoute() {
			const el = document.getElementById('fhccontent')
			const r = el?.getAttribute('route')
			if (r) return r
			return 'FhcDashboard'
		}
	},
	mounted() {
		// window.addEventListener('beforeunload', this.beforeUnloadListener)
		document.addEventListener('click', this.handleClick);
		window.addEventListener('fhcnavigate', this.tryCis4Navigate);
		this.$router.push({name: this.getInitialRoute()});
	},
	beforeUnmount() {
		document.removeEventListener('click', this.handleClick);
	},
});

setScrollbarWidth();
app.use(router);
window.fhcVueRouter = router
app.use(FhcApi);
app.use(primevue.config.default, {
	zIndex: {
		overlay: 9000,
		tooltip: 8000
	}
})
app.use(Phrasen);
app.mount('#fhccontent');