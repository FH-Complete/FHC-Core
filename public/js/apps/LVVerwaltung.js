import LVVerwaltung from "../components/LVVerwaltung/LVVerwaltung.js";
import Phrasen from "../plugins/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(`/${ciPath}/LVVerwaltung`),
	routes: [
		{
			name: 'index',
			path: `/`,
			component: LVVerwaltung
		},
		{
			name: `byEmp`,
			path: `/emp/:emp/:stg?`,
			component: LVVerwaltung
		},
		/*{
			name: `byFachbereich`,
			path: `/fachbereich/:fachbereich/:emp?`,
			component: LVVerwaltung
		},*/
		{
			name: `byStg`,
			path: `/stg/:stg/:semester?`,
			component: LVVerwaltung
		},
		{
			path: '/:pathMatch(.*)*',
			redirect: '/'
		}
	]
});

const app = Vue.createApp();

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');
