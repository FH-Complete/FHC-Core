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
			name: 'stdsem',
			path: '/stdsem/:stdsem',
			component: LVVerwaltung,
			children: [
				{ name: 'emp', path: 'emp/:emp/:treemenu(.*)*', component: LVVerwaltung },
				{ name: 'treemenu', path: ':treemenu(.*)*', component: LVVerwaltung }
			]
		},
		{
			path: '/:pathMatch(.*)*',
			redirect: '/'
		},

	]
});

FhcApps.router.makeExtendable(router);

const app = Vue.createApp({
	name: 'LvVwApp'
});

FhcApps.makeExtendable(app);

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');
