import Vertragsverwaltung from "../components/Vertraege/Vertragsverwaltung.js";
import fhcapifactory from "./api/fhcapifactory.js";

import Phrasen from "../plugin/Phrasen.js";

const ciPath = FHC_JS_DATA_STORAGE_OBJECT.app_root.replace(/(https:|)(^|\/\/)(.*?\/)/g, '') + FHC_JS_DATA_STORAGE_OBJECT.ci_router;

const router = VueRouter.createRouter({
	history: VueRouter.createWebHistory(),
	routes: [
		{ path: `/${ciPath}/vertragsverwaltung`, component: Vertragsverwaltung },
	]
});


const app = Vue.createApp();

app
	.use(router)
	//.use(fhcapifactory)  //nicht nötig
	.use(primevue.config.default, {
		zIndex: {
			overlay: 1100
		}
	})
	.use(Phrasen)
	.mount('#main');
