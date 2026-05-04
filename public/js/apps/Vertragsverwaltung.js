import Vertragsverwaltung from "../components/Vertraege/Vertragsverwaltung.js";
import Phrasen from "../plugins/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{ path: `/${ciPath}/vertragsverwaltung`, component: Vertragsverwaltung },
	]
});

const app = Vue.createApp({
	name: 'VertragsverwaltungApp'
});

app
	.use(router)
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');
